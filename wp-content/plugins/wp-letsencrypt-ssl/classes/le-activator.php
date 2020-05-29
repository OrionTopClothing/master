<?php

class WPLE_Activator
{
    public static function activate( $networkwide )
    {
        if ( is_multisite() && $networkwide ) {
            wp_die( 'WP Encryption cannot be activated network wide. Please activate on your individual sites.' );
        }
        /**
         * Autoloader since 4.3.0
         */
        include_once plugin_dir_path( __DIR__ ) . 'autoloader.php';
        \WPEncryption\Autoloader::run();
        $opts = ( get_option( 'wple_opts' ) === FALSE ? array(
            'expiry' => '',
        ) : get_option( 'wple_opts' ) );
        //initial disable ssl forcing
        $opts['force_ssl'] = 0;
        update_option( 'wple_opts', $opts );
        if ( isset( $opts['expiry'] ) && $opts['expiry'] != '' && !wp_next_scheduled( 'wple_ssl_reminder_notice' ) ) {
            wp_schedule_single_event( strtotime( '-10 day', strtotime( $opts['expiry'] ) ), 'wple_ssl_reminder_notice' );
        }
    }

}