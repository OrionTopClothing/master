<?php

/**
 * require all the lib files for generating certs
 */
require_once WPLE_DIR . 'lib/LEFunctions.php';
require_once WPLE_DIR . 'lib/LEConnector.php';
require_once WPLE_DIR . 'lib/LEAccount.php';
require_once WPLE_DIR . 'lib/LEAuthorization.php';
require_once WPLE_DIR . 'lib/LEClient.php';
require_once WPLE_DIR . 'lib/LEOrder.php';
use  LEClient\LEClient ;
use  LEClient\LEOrder ;
/**
 * WPLE_Core class
 * Responsible for handling account registration, certificate generation & install certs on cPanel
 * 
 * @since 1.0.0  
 */
class WPLE_Core
{
    protected  $email ;
    protected  $date ;
    protected  $basedomain ;
    protected  $domains ;
    protected  $mdomain = false ;
    protected  $client ;
    protected  $order ;
    protected  $pendings ;
    protected  $wcard = false ;
    protected  $dnss = false ;
    protected  $iscron = false ;
    protected  $noscriptresponse = false ;
    /**
     * construct all params & proceed with cert generation
     *
     * @since 1.0.0
     * @param array $opts
     * @param boolean $gen
     */
    public function __construct(
        $opts = array(),
        $gen = true,
        $wc = false,
        $dnsverify = false
    )
    {
        
        if ( !empty($opts) ) {
            $this->email = sanitize_email( $opts['email'] );
            $this->date = $opts['date'];
            $optss = $opts;
        } else {
            $optss = get_option( 'wple_opts' );
            $this->email = ( isset( $optss['email'] ) ? sanitize_email( $optss['email'] ) : '' );
            $this->date = ( isset( $optss['date'] ) ? $optss['date'] : '' );
        }
        
        $siteurl = site_url();
        $this->basedomain = str_ireplace( array( 'http://', 'https://' ), array( '', '' ), $siteurl );
        $this->domains = array( $this->basedomain );
        //include both www & non-www
        
        if ( isset( $optss['include_www'] ) && $optss['include_www'] == 1 ) {
            $this->basedomain = str_ireplace( 'www.', '', $this->basedomain );
            $this->domains = array( $this->basedomain, 'www.' . $this->basedomain );
        }
        
        if ( $dnsverify ) {
            //manual dns verify
            $this->dnss = true;
        }
        if ( $gen ) {
            $this->wple_generate_verify_ssl();
        }
    }
    
    /**
     * group all different steps into one function & clear debug.log intially.
     *
     * @since 1.0.0
     * @return void
     */
    public function wple_generate_verify_ssl()
    {
        update_option( 'wple_progress', 0 );
        $PRO = ( wple_fs()->can_use_premium_code__premium_only() ? 'PRO' : '' );
        $PRO .= ( $this->wcard ? ' WILDCARD SSL ' : ' SINGLE DOMAIN SSL ' );
        $this->wple_log( '<b>' . WPLE_VERSION . ' ' . $PRO . '</b>', 'success', 'w' );
        $this->wple_create_client();
        $this->wple_generate_order();
        $this->wple_verify_pending_orders();
        $this->wple_generate_certs();
        if ( isset( $_POST['wple_send_usage'] ) ) {
            $this->wple_send_usage_data();
        }
    }
    
    /**
     * create ACMEv2 client
     *
     * @since 1.0.0
     * @return void
     */
    protected function wple_create_client()
    {
        try {
            $keydir = ABSPATH . 'keys/';
            $this->client = new LEClient(
                $this->email,
                false,
                LEClient::LOG_STATUS,
                $keydir
            );
        } catch ( Exception $e ) {
            update_option( 'wple_error', 1 );
            $this->wple_log(
                "CREATE_CLIENT:" . $e,
                'error',
                'w',
                true
            );
        }
        ///echo '<pre>'; print_r( $client->getAccount() ); echo '</pre>';
    }
    
    /**
     * Generate order with ACMEv2 client for given domain
     *
     * @since 1.0.0
     * @return void
     */
    protected function wple_generate_order()
    {
        ///$this->wple_log($this->basedomain . json_encode($this->domains), 'success', 'a');
        try {
            $this->order = $this->client->getOrCreateOrder( $this->basedomain, $this->domains );
        } catch ( Exception $e ) {
            update_option( 'wple_error', 1 );
            $this->wple_log(
                "CREATE_ORDER:" . $e,
                'error',
                'w',
                true
            );
        }
    }
    
    /**
     * Get all pendings orders which need domain verification
     *
     * @since 1.0.0
     * @return void
     */
    protected function wple_get_pendings( $dns = false )
    {
        $chtype = LEOrder::CHALLENGE_TYPE_HTTP;
        $http = 1;
        
        if ( $this->dnss || $dns ) {
            $chtype = LEOrder::CHALLENGE_TYPE_DNS;
            $http = 0;
        }
        
        try {
            $this->pendings = $this->order->getPendingAuthorizations( $chtype );
            
            if ( !empty($this->pendings) && $http == 1 ) {
                $opts = get_option( 'wple_opts' );
                $opts['challenge_files'] = array();
                foreach ( $this->pendings as $chlng ) {
                    $opts['challenge_files'][] = sanitize_text_field( $chlng['filename'] );
                }
                update_option( 'wple_opts', $opts );
            }
        
        } catch ( Exception $e ) {
            $this->wple_log(
                'GET_PENDING_AUTHS:' . $e,
                'error',
                'w',
                true
            );
        }
    }
    
    /**
     * verify all the challenges via HTTP
     *
     * @since 1.0.0
     * @return void
     */
    protected function wple_verify_pending_orders( $forcehttpverify = false, $forcednsverify = false, $is_cron = false )
    {
        $this->iscron = $is_cron;
        ///$this->order->deactivateOrderAuthorization($this->basedomain);
        ///$this->order->revokeCertificate();
        ///exit();
        
        if ( !$this->order->allAuthorizationsValid() ) {
            $this->wple_save_dns_challenges();
            
            if ( $forcednsverify || $this->wcard ) {
                //dns verify
                $this->wple_get_pendings( true );
            } else {
                $this->wple_get_pendings();
            }
            
            
            if ( !empty($this->pendings) ) {
                $site = $this->basedomain;
                $vrfy = '';
                
                if ( $this->dnss ) {
                    $this->wple_log( esc_html__( "Verify your domain by adding the below TXT records to your domain DNS records (Refer FAQ for video tutorial on how to add these DNS records)", 'wp-letsencrypt-ssl' ) . "\n", 'success', 'a' );
                    $this->reloop_get_dns();
                } else {
                    ///$this->wple_log(json_encode($this->pendings), 'success', 'a');
                }
                
                foreach ( $this->pendings as $challenge ) {
                    
                    if ( $challenge['type'] == 'dns-01' && stripos( $challenge['identifier'], $site ) !== FALSE ) {
                        if ( $this->wcard && !$forcednsverify && !$this->dnss ) {
                        }
                        if ( $this->dnss ) {
                            //manual dns verify
                            $this->order->verifyPendingOrderAuthorization( $challenge['identifier'], LEOrder::CHALLENGE_TYPE_DNS );
                        }
                    } else {
                        if ( $challenge['type'] == 'http-01' && stripos( $challenge['identifier'], $site ) >= 0 ) {
                            
                            if ( !$this->dnss && !$forcednsverify ) {
                                ///$acmefile = site_url('/.well-known/acme-challenge/' . $challenge['filename'], 'http');
                                $acmefile = "http://" . $challenge['identifier'] . "/.well-known/acme-challenge/" . $challenge['filename'];
                                $this->wple_deploy_challenge_files( $acmefile, $challenge );
                                $rsponse = $this->wple_get_file_response( $acmefile );
                                
                                if ( $rsponse !== trim( $challenge['content'] ) ) {
                                    //try url rewriting
                                    flush_rewrite_rules( true );
                                    require_once ABSPATH . 'wp-admin/includes/misc.php';
                                    
                                    if ( !file_exists( ABSPATH . '.htaccess' ) ) {
                                        $rules = array(
                                            '<IfModule mod_rewrite.c>',
                                            'RewriteEngine On',
                                            'RewriteBase /',
                                            'RewriteRule ^.well-known/acme-challenge/(.*)$ $1 [L,R=301]',
                                            '</IfModule>'
                                        );
                                        insert_with_markers( ABSPATH . '.htaccess', 'WPEncryption', $rules );
                                    } else {
                                        save_mod_rewrite_rules();
                                    }
                                    
                                    $rsponse = $this->wple_get_file_response( $acmefile );
                                    
                                    if ( $rsponse !== trim( $challenge['content'] ) && function_exists( 'symlink' ) ) {
                                        //lets try symlink
                                        $this->wple_try_symlink_restore( $challenge, $acmefile );
                                        $rsponse = $this->wple_get_file_response( $acmefile );
                                    }
                                
                                }
                                
                                //Additional check for public access
                                // $sslContext = array(
                                //   "ssl" => array(
                                //     "verify_peer" => false,
                                //     "verify_peer_name" => false,
                                //   ),
                                // );
                                // $sslContext = stream_context_create($sslContext);
                                // $check_code = @file_get_contents($acmefile, false, $sslContext);
                                
                                if ( $rsponse != trim( $challenge['content'] ) ) {
                                    update_option( 'wple_error', 2 );
                                    $this->wple_log( esc_html__( "Could not verify challenge code in above http challenge file. Please make sure its publicly accessible or contact your hosting support to make it public.", 'wp-letsencrypt-ssl' ) . " \n", 'success', 'a' );
                                    //$this->wple_log($this->wple_kses(__("We can help you by manually resolving this issue and make it fully automated if you <strong>Upgrade to PRO</strong> and submit a support ticket at https://gowebsmarty.in. If we FAIL to do so, your full purchase will be refunded.", "wp-letsencrypt-ssl")) . "\n", 'success', 'a');
                                    //file_put_contents(ABSPATH . $challenge['filename'], trim($challenge['content']));
                                    //add_rewrite_rule('.well-known/acme-challenge/(.*)$', '$1', 'top');
                                    //flush_rewrite_rules(true);
                                    //sleep(2);
                                    //global $wp_rewrite;
                                    //$wp_rewrite->flush_rules(true);
                                    $hdrs = wp_remote_head( $acmefile );
                                    
                                    if ( is_wp_error( $hdrs ) ) {
                                        $statuscode = 0;
                                    } else {
                                        $statuscode = $hdrs['response']['code'];
                                    }
                                    
                                    
                                    if ( $statuscode != 200 ) {
                                        // not accessible
                                        //if ($this->noscriptresponse) {
                                        //$this->try_n_prompt_dns($fpath, $challenge, true);
                                        //} else {
                                        $this->try_n_prompt_dns( $fpath, $challenge );
                                        //}
                                    }
                                
                                }
                                
                                $this->order->verifyPendingOrderAuthorization( $challenge['identifier'], LEOrder::CHALLENGE_TYPE_HTTP );
                            }
                        
                        }
                    }
                
                }
            }
        
        }
    
    }
    
    /**
     * Finalize and get certificates
     *
     * @since 1.0.0
     * @return void
     */
    public function wple_generate_certs( $rectify = true )
    {
        ///$this->wple_generate_order();
        
        if ( $this->order->allAuthorizationsValid() ) {
            // Finalize the order
            
            if ( !$this->order->isFinalized() ) {
                $this->wple_log( esc_html__( 'Finalizing the order', 'wp-letsencrypt-ssl' ), 'success', 'a' );
                $this->order->finalizeOrder();
            }
            
            // get the certificate.
            
            if ( $this->order->isFinalized() ) {
                $this->wple_log( esc_html__( 'Getting SSL certificates', 'wp-letsencrypt-ssl' ), 'success', 'a' );
                $this->order->getCertificate();
            }
            
            $this->wple_clean_challenge_files();
            $cert = ABSPATH . 'keys/certificate.crt';
            
            if ( file_exists( $cert ) ) {
                $this->wple_save_expiry_date();
                update_option( 'wple_error', 0 );
                $sslgenerated = "<h2>" . esc_html__( 'SSL Certificate generated successfully', 'wp-letsencrypt-ssl' ) . "!</h2>";
                $this->wple_log( $sslgenerated, 'success', 'a' );
                $this->wple_send_usage_data();
                wp_redirect( admin_url( '/admin.php?page=wp_encryption&success=1' ), 302 );
                exit;
            }
        
        } else {
            $this->wple_log( json_encode( $this->order->authorizations ), 'success', 'a' );
            ///$auths = $this->order->authorizations;
            ///if ($rectify) {
            ///$this->wple_log('Code change detected', 'success', 'a');
            // Not much luck with this so disabled since 4.2.0
            ///$this->wple_recheck_http_challenge($auths);
            ///}
            update_option( 'wple_error', 2 );
            $this->wple_log(
                '<h2>' . esc_html__( 'There are some pending verifications. If new DNS records were added, please run this installation again after 5-10mins', 'wp-letsencrypt-ssl' ) . '</h2>',
                'success',
                'a',
                true
            );
        }
    
    }
    
    /**
     * Save expiry date of cert dynamically by parsing the cert
     *
     * @since 1.0.0
     * @return void
     */
    public function wple_save_expiry_date()
    {
        $certfile = ABSPATH . 'keys/certificate.crt';
        
        if ( file_exists( $certfile ) ) {
            $opts = get_option( 'wple_opts' );
            $opts['expiry'] = '';
            try {
                $this->wple_getRemainingDays( $certfile, $opts );
            } catch ( Exception $e ) {
                update_option( 'wple_opts', $opts );
                //echo $e;
                //exit();
            }
        }
    
    }
    
    /**
     * Utility functions
     * 
     * @since 1.0.0 
     */
    public function wple_parseCertificate( $cert_pem )
    {
        // if (false === ($ret = openssl_x509_read(file_get_contents($cert_pem)))) {
        //   throw new Exception('Could not load certificate: ' . $cert_pem . ' (' . $this->get_openssl_error() . ')');
        // }
        if ( !is_array( $ret = openssl_x509_parse( file_get_contents( $cert_pem ), true ) ) ) {
            throw new Exception( 'Could not parse certificate' );
        }
        return $ret;
    }
    
    public function wple_getRemainingDays( $cert_pem, $opts )
    {
        if ( isset( $opts['expiry'] ) && $opts['expiry'] != '' && wp_next_scheduled( 'wple_ssl_reminder_notice' ) ) {
            wp_unschedule_event( strtotime( '-10 day', strtotime( $opts['expiry'] ) ), 'wple_ssl_reminder_notice' );
        }
        $ret = $this->wple_parseCertificate( $cert_pem );
        $expiry = date( 'd-m-Y', $ret['validTo_time_t'] );
        $opts['expiry'] = $expiry;
        if ( $opts['expiry'] != '' ) {
            wp_schedule_single_event( strtotime( '-10 day', strtotime( $opts['expiry'] ) ), 'wple_ssl_reminder_notice' );
        }
        update_option( 'wple_opts', $opts );
        update_option( 'wple_show_review', 1 );
        do_action( 'cert_expiry_updated' );
    }
    
    public function wple_log(
        $msg = '',
        $type = 'success',
        $mode = 'a',
        $redirect = false
    )
    {
        $handle = fopen( WPLE_DEBUGGER . 'debug.log', $mode );
        if ( $type == 'error' ) {
            $msg = '<span class="error"><b>' . esc_html__( 'ERROR', 'wp-letsencrypt-ssl' ) . ':</b> ' . wp_kses_post( $msg ) . '</span>';
        }
        fwrite( $handle, wp_kses_post( $msg ) . "\n" );
        fclose( $handle );
        
        if ( $redirect ) {
            if ( isset( $_POST['wple_send_usage'] ) ) {
                $this->wple_send_usage_data();
            }
            wp_redirect( admin_url( '/admin.php?page=wp_encryption&error=1' ), 302 );
            die;
        }
    
    }
    
    /**
     * Collect usage data to improve plugin
     *
     * @since 2.1.0
     * @return void
     */
    public function wple_send_usage_data()
    {
        $readlog = file_get_contents( WPLE_DEBUGGER . 'debug.log' );
        $handle = curl_init();
        $srvr = array(
            'challenge_folder_exists' => file_exists( ABSPATH . '.well-known/acme-challenge' ),
            'certificate_exists'      => file_exists( ABSPATH . 'keys/certificate.crt' ),
            'server_software'         => $_SERVER['SERVER_SOFTWARE'],
            'http_host'               => $_SERVER['HTTP_HOST'],
            'pro'                     => ( wple_fs()->is__premium_only() ? 'PRO' : 'FREE' ),
        );
        $curlopts = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST           => 1,
            CURLOPT_URL            => 'https://gowebsmarty.in/?catchwple=1',
            CURLOPT_HEADER         => false,
            CURLOPT_POSTFIELDS     => array(
            'response' => $readlog,
            'server'   => json_encode( $srvr ),
        ),
        );
        curl_setopt_array( $handle, $curlopts );
        curl_exec( $handle );
        curl_close( $handle );
    }
    
    /**
     * Show DNS records for domain verification
     *
     * @since 2.2.0
     * @return void
     */
    private function reloop_get_dns( $return = false )
    {
        $site = $this->basedomain;
        $vrfy = '';
        $this->wple_get_pendings( true );
        $dns_records = array();
        foreach ( $this->pendings as $challenge ) {
            
            if ( $challenge['type'] == 'dns-01' && stripos( $challenge['identifier'], $site ) !== FALSE ) {
                $vrfy .= 'Name: <b>_acme-challenge.' . $site . '</b> or <b>_acme-challenge</b>
          TTL: <b>60</b> or <b>Lowest</b> possible value
          Type: <b>TXT</b>
          Value: <b>' . esc_html( $challenge['DNSDigest'] ) . '</b><br>
          ';
                $dns_records[] = esc_html( $challenge['DNSDigest'] );
            }
        
        }
        if ( $return ) {
            return $dns_records;
        }
        $this->wple_log( $vrfy, 'success', 'a' );
    }
    
    /**
     * Try overriding http challenge access or prompt for dns challenge
     *
     * @param string $fpath
     * @param array $challenge
     * @since 2.2.0
     * @return void
     */
    private function try_n_prompt_dns( $fpath, $challenge, $noscript = false )
    {
        
        if ( $noscript ) {
            $this->wple_log( esc_html__( "HTTP verification not possible as Http challenge file returns noscript when accessed as bot(try it on onlinecurl.com). Alternatively, you can verify your domain by adding below DNS records.\n", 'wp-letsencrypt-ssl' ), 'success', 'a' );
            $this->wple_dns_promo();
            //$this->wple_newbie_promo();
            $this->reloop_get_dns();
            $dns_verify = '<h2><a href="?page=wp_encryption&dnsverify=1" style="font-weight:bold">' . esc_html__( 'Click here to continue DNS verification IF you manually added the above DNS records', 'wp-letsencrypt-ssl' ) . '</a></h2>';
            $this->wple_log(
                $dns_verify,
                'success',
                'a',
                true
            );
        } else {
            $this->wple_log( esc_html__( "Alternatively, You can manually verify your domain by adding the below TXT records to your domain DNS records (Refer FAQ for video tutorial on how to add these DNS records)\n", 'wp-letsencrypt-ssl' ), 'success', 'a' );
            $this->wple_dns_promo();
            //$this->wple_newbie_promo();
            $this->reloop_get_dns();
            $dns_verify = '<h2><a href="?page=wp_encryption&dnsverify=1" style="font-weight:bold">' . esc_html__( 'Click here to continue DNS verification IF you manually added the above DNS records', 'wp-letsencrypt-ssl' ) . '</a></h2>';
            $this->wple_log(
                $dns_verify,
                'success',
                'a',
                true
            );
        }
    
    }
    
    /**
     * PRO promo on http challenge fail
     *
     * @since 2.4.0
     * @return void
     */
    public function wple_dns_promo( $redirect = false )
    {
        $pro_automate = '<div class="wple-promo"><b>WP Encryption PRO</b> ' . esc_html__( 'can automate this DNS verification process IF your DNS is managed by your cPanel or Godaddy. Buy PRO version today & forget all these difficulties.', 'wp-letsencrypt-ssl' ) . '</div>';
        $nocpanelwc = esc_html__( "Unfortunately, this server dont seem to have cPanel installed so WP Encryption PRO cannot automate DNS verification process. You will have to manually add below DNS records for domain verification to succeed.\n", 'wp-letsencrypt-ssl' );
        $nocpanel = $this->wple_kses( __( "Unfortunately, this server dont seem to have cPanel installed so WP Encryption PRO cannot automate DNS verification process. You will have to manually add below DNS records or contact your hosting support to allow access to <b>.well-known</b> folder for domain verification to succeed.\n", 'wp-letsencrypt-ssl' ) );
        $this->wple_log(
            $pro_automate . "\n",
            'success',
            'a',
            $redirect
        );
        
        if ( function_exists( 'shell_exec' ) ) {
            
            if ( !empty(shell_exec( 'which cpapi2' )) ) {
            } else {
            }
        
        } else {
            
            if ( function_exists( 'system' ) ) {
                ob_start();
                system( "which cpapi2", $var );
                $shll = ob_get_contents();
                ob_end_clean();
                
                if ( !empty($shll) ) {
                } else {
                }
            
            } else {
                
                if ( function_exists( 'passthru' ) ) {
                    ob_start();
                    passthru( "which cpapi2", $var );
                    $shll = ob_get_contents();
                    ob_end_clean();
                    
                    if ( !empty($shll) ) {
                    } else {
                    }
                
                } else {
                    
                    if ( function_exists( 'exec' ) ) {
                        exec( "which cpapi2", $output, $var );
                        
                        if ( !empty($output) ) {
                        } else {
                        }
                    
                    } else {
                    }
                
                }
            
            }
        
        }
    
    }
    
    /**
     * Newbie promo on fail cases
     *
     * @since 2.4.0
     * @return void
     */
    public function wple_newbie_promo( $redirect = false )
    {
        //disabled since 2.5.0
        //$this->wple_log("<div class=\"wple-promo\">If you are serious about your precious time, We can manually handle this verification, SSL installation & SSL renewal task for ONE YEAR with our <a href=\"https://checkout.freemius.com/mode/dialog/plugin/5090/plan/8386/licenses/1/\">TIME SAVER PLAN</a></div>\n", 'success', 'a', $redirect);
    }
    
    /**
     * Check if noscript response
     *
     * @param string $acmefile
     * @since 2.5.0
     * @return boolean
     */
    function wple_check_if_noscript( $acmefile )
    {
        $requestURL = $acmefile;
        $remoteget = wp_remote_get( $requestURL );
        if ( is_wp_error( $remoteget ) ) {
            return true;
        }
        $response = trim( wp_remote_retrieve_body( $remoteget ) );
        
        if ( FALSE == stripos( $response, 'noscript' ) ) {
            return false;
        } else {
            $this->noscriptresponse = true;
            return true;
        }
    
    }
    
    /**
     * Deploy challenge files
     *
     * @since 3.2.0
     * @param array $challenge
     * @return void
     */
    private function wple_deploy_challenge_files( $acmefile, $challenge )
    {
        $fpath = ABSPATH . '.well-known/acme-challenge/';
        if ( !file_exists( $fpath ) ) {
            mkdir( $fpath, 0775, true );
        }
        $htaccess = ABSPATH . '.well-known/.htaccess';
        
        if ( !file_exists( $htaccess ) ) {
            $rules = array(
                '<IfModule mod_rewrite.c>',
                'RewriteCond %{REQUEST_FILENAME} !.well-known/',
                'RewriteRule "(^|/)\\.(?!well-known)" - [F]',
                '</IfModule>'
            );
            insert_with_markers( $htaccess, 'WPEncryption', $rules );
        }
        
        $this->wple_log( esc_html__( 'Creating HTTP challenge file', 'wp-letsencrypt-ssl' ) . ' ' . $acmefile, 'success', 'a' );
        
        if ( file_exists( $fpath . $challenge['filename'] ) ) {
            unlink( $fpath . $challenge['filename'] );
            //remove symbolic
        }
        
        file_put_contents( $fpath . $challenge['filename'], trim( $challenge['content'] ) );
        file_put_contents( ABSPATH . $challenge['filename'], trim( $challenge['content'] ) );
    }
    
    /**
     * Retrieve file content
     *
     * @since 3.2.0
     * @param string $acmefile
     * @return void
     */
    private function wple_get_file_response( $acmefile )
    {
        $args = array(
            'sslverify' => false,
        );
        $remoteget = wp_remote_get( $acmefile, $args );
        
        if ( is_wp_error( $remoteget ) ) {
            $rsponse = 'error';
        } else {
            $rsponse = trim( wp_remote_retrieve_body( $remoteget ) );
        }
        
        return $rsponse;
    }
    
    /**
     * Remove http challenge files from root
     *
     * @since 3.3.0
     * @return void
     */
    private function wple_clean_challenge_files()
    {
        $getopts = get_option( 'wple_opts' );
        if ( isset( $getopts['challenge_files'] ) ) {
            if ( !empty($getopts['challenge_files']) ) {
                foreach ( $getopts['challenge_files'] as $chfile ) {
                    $cfile = ABSPATH . sanitize_text_field( str_ireplace( '.', '', $chfile ) );
                    if ( file_exists( $cfile ) ) {
                        @unlink( $cfile );
                    }
                }
            }
        }
    }
    
    /**
     * remove symlink n restore original file
     *
     * @since 3.3.3
     * @param array $challenge
     * @param string $acmefile
     * @return void
     */
    private function wple_try_symlink_restore( $challenge, $acmefile )
    {
        $acmefilepath = ABSPATH . '.well-known/acme-challenge/' . $challenge['filename'];
        if ( file_exists( $acmefilepath ) ) {
            unlink( $acmefilepath );
        }
        $abspath = ABSPATH;
        $sym = @symlink( $abspath . $challenge['filename'], $acmefilepath );
        $this->wple_log(
            'Sym response ' . (int) $sym,
            'success',
            'a',
            false
        );
        $rsponse = $this->wple_get_file_response( $acmefile );
        
        if ( $rsponse !== trim( $challenge['content'] ) || !$sym ) {
            //no success with htaccess or symlink so revert back
            $this->wple_log(
                'Restoring original file',
                'success',
                'a',
                false
            );
            if ( file_exists( $acmefilepath ) ) {
                unlink( $acmefilepath );
            }
            file_put_contents( $acmefilepath, trim( $challenge['content'] ) );
        }
    
    }
    
    /**
     * Escape html but retain bold
     *
     * @since 3.3.3
     * @param string $translated
     * @param string $additional Additional allowed html tags
     * @return void
     */
    private function wple_kses( $translated, $additional = '' )
    {
        $allowed = array(
            'strong' => array(),
            'b'      => array(),
        );
        if ( $additional == 'a' ) {
            $allowed['a'] = array(
                'href'   => array(),
                'rel'    => array(),
                'target' => array(),
                'title'  => array(),
            );
        }
        return wp_kses( $translated, $allowed );
    }
    
    /**
     * Rectify http challenge code if challenge code is not correct
     *
     * @since 3.3.7
     * @param array $auths
     * @return void
     */
    private function wple_recheck_http_challenge( $auths )
    {
        $updated = 0;
        if ( is_array( $auths ) ) {
            foreach ( $auths as $key => $auth ) {
                $chl = $auth->challenges;
                if ( is_array( $chl ) ) {
                    foreach ( $chl as $idx => $arr ) {
                        
                        if ( isset( $arr['error'] ) && $arr['type'] == 'http-01' ) {
                            $er = $arr['error']['detail'];
                            $this->wple_log( $er, 'success', 'a' );
                            $sp = stripos( $er, '!=' );
                            
                            if ( $sp != FALSE ) {
                                $wrong_code = trim( str_ireplace( '"', '', substr( $er, $sp + 3 ) ) );
                                $filencode = explode( '.', $wrong_code );
                                $fname = ( isset( $filencode[0] ) ? $filencode[0] : '' );
                                ///$chcode = isset($filencode[1]) ? $filencode[1] : '';
                                $chpath = ABSPATH . '.well-known/acme-challenge/' . $fname;
                                $abpath = ABSPATH . $fname;
                                
                                if ( $fname != '' ) {
                                    $updated = 1;
                                    if ( file_exists( $chpath ) ) {
                                        unlink( $chpath );
                                    }
                                    if ( file_exists( $abpath ) ) {
                                        unlink( $abpath );
                                    }
                                    $site = str_ireplace( array( 'http://', 'https://', 'www.' ), array( '', '', '' ), site_url() );
                                    $this->wple_generate_order();
                                    $this->wple_get_pendings();
                                    $this->wple_log( json_encode( $this->pendings ), 'success', 'a' );
                                    foreach ( $this->pendings as $challenge ) {
                                        
                                        if ( $challenge['type'] == 'http-01' && stripos( $challenge['identifier'], $site ) !== FALSE ) {
                                            $new_code = $challenge['content'];
                                            $fname = $challenge['filename'];
                                            $chpath = ABSPATH . '.well-known/acme-challenge/' . $fname;
                                            $abpath = ABSPATH . $fname;
                                            @file_put_contents( $chpath, $new_code );
                                            @file_put_contents( $abpath, $new_code );
                                        }
                                    
                                    }
                                    $this->wple_log( sprintf( esc_html__( 'Updated challenge file %s with %s', 'wp-letsencrypt-ssl' ), $fname, $new_code ), 'success', 'a' );
                                }
                            
                            }
                        
                        }
                    
                    }
                }
            }
        }
        
        if ( $updated && !empty($this->pendings) ) {
            $site = str_ireplace( array( 'http://', 'https://', 'www.' ), array( '', '', '' ), site_url() );
            foreach ( $this->pendings as $challenge ) {
                if ( $challenge['type'] == 'http-01' && stripos( $challenge['identifier'], $site ) !== FALSE ) {
                    $this->order->verifyPendingOrderAuthorization( $challenge['identifier'], LEOrder::CHALLENGE_TYPE_HTTP );
                }
            }
            $this->wple_generate_certs( false );
        }
    
    }
    
    /**
     * Save DNS challenges for later use
     *
     * @since 4.6.0
     * @return void
     */
    private function wple_save_dns_challenges()
    {
        $chtype = LEOrder::CHALLENGE_TYPE_DNS;
        $dns_challenges = $this->order->getPendingAuthorizations( $chtype );
        $site = $this->basedomain;
        
        if ( !empty($dns_challenges) ) {
            $opts = ( FALSE === get_option( 'wple_opts' ) ? array() : get_option( 'wple_opts' ) );
            foreach ( $dns_challenges as $challenge ) {
                if ( $challenge['type'] == 'dns-01' && stripos( $challenge['identifier'], $site ) !== FALSE ) {
                    $opts['dns_challenges'][] = sanitize_text_field( $challenge['DNSDigest'] );
                }
            }
            update_option( 'wple_opts', $opts );
        }
    
    }

}