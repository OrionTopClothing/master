<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

use Hayageek\OAuth2\Client\Provider\Yahoo;

/**
 * Yahoo Class
 * 
 * Handles all yahoo functions
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
if( !class_exists( 'WOO_Slg_Social_Yahoo' ) ) {

	class WOO_Slg_Social_Yahoo {

		var $yahoo;
		public $requires_ssl;

		public function __construct() {
			$this->requires_ssl = true;
		}

		/**
		 * Include Yahoo Class
		 * 
		 * Handles to load yahoo class
		 * 
		 * @package WooCommerce - Social Login
	 	 * @since 1.0.0
		 */
		public function woo_slg_load_yahoo() {

			global $woo_slg_options;

			//yahoo declaration
			if( !empty( $woo_slg_options['woo_slg_enable_yahoo'] ) && !empty( $woo_slg_options['woo_slg_yh_consumer_key'] ) 
				&& !empty( $woo_slg_options['woo_slg_yh_consumer_secret'] ) ) {

				
				if( !class_exists( 'Yahoo' ) ) { // loads the OAuthToken class
					// ref from https://github.com/hayageek/oauth2-yahoo
					require_once ( WOO_SLG_SOCIAL_LIB_DIR . '/yahoo/vendor/autoload.php' ); 
				}

				$woo_domain_url = WOO_SLG_YH_REDIRECT_URL;
			   
			    // Yahoo Object
			    $this->yahoo = new Yahoo( array('clientId' => WOO_SLG_YH_CONSUMER_KEY, 'clientSecret' => WOO_SLG_YH_CONSUMER_SECRET, 'redirectUri' => $woo_domain_url ) );

				return true;
			} else {

				return false;
			}
		}

		/**
		 * Initializes Yahoo API
		 * 
		 * @package WooCommerce - Social Login
		 * @since 1.0.0
		 */
		public function woo_slg_initialize_yahoo() {
			
			//check yahoo is enable,consumer key not empty,consumer secrets not empty and app id should not empty
			if ( isset( $_GET['code'] ) && isset( $_GET['wooslg'] ) && ( $_GET['wooslg'] == 'yahoo' || $_GET['wooslg'] == 'yhoo' )  ) {

				//load yahoo class
				$yahoo = $this->woo_slg_load_yahoo();

				//check yahoo class is loaded or not
				if( !$yahoo ) return false;

			 	$yahoo_access_token = $this->yahoo->getAccessToken('authorization_code', array( 'code' => $_GET['code'] ) );

			    //check yahoo oauth access token is set or not
				 if ( !empty( $yahoo_access_token ) ) {

				 	// if session is still present ( not expired),then restore access token from session
	        		$userData = $this->yahoo->getResourceOwner( $yahoo_access_token );
	        		
	        		if( !empty( $userData ) ){

		        		$user_data = $userData->toArray();

						if( isset( $user_data['profile'] ) && !empty( $user_data['profile'] ) ) {

							\WSL\PersistentStorage\WOOSLGPersistent::set('woo_slg_yahoo_user_cache', $user_data['profile'] );
						}
					}
			    }
			}
		}

		/**
		 * Get auth url for yahoo
		 *
		 * @param WooCommerce - Social Login
		 * @since 1.0.0
		 */	
		public function woo_slg_get_yahoo_auth_url () {
			
			//load yahoo class
			$yahoo = $this->woo_slg_load_yahoo();
			
			//check yahoo is loaded or not
			if( !$yahoo ) return false;
			
			$url = $this->yahoo->getAuthorizationUrl();
			
			return $url;
		}
		 
		/**
		 * Get Yahoo user's Data
		 * 
		 * @param WooCommerce - Social Login
		 * @since 1.0.0
		 */		
		public function woo_slg_get_yahoo_user_data() {
		
			$user_profile_data = '';
			
			$user_profile_data = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_yahoo_user_cache');

			\WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_yahoo_user_cache');
			
			return $user_profile_data;
		}
		
	}
	
}
?>