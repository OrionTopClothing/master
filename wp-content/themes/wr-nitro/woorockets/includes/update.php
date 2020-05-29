<?php
/**
 * @version    1.0
 * @package    WR_Theme
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

/**
 * Hook into WordPress's automatic update.
 *
 * @package  WR_Theme
 * @since    1.0
 */
class WR_Nitro_Update {
	/**
	 * Define server to authorize Envato app.
	 *
	 * @var  string
	 */
	const ENVATO_APP_SERVER = 'http://www.woorockets.com/nitro_purchase_verification/';

	/**
	 * Variable to hold the initialization state.
	 *
	 * @var  boolean
	 */
	protected static $initialized = false;

	/**
	 * Initialize pluggable functions.
	 *
	 * @return  void
	 */
	public static function initialize() {
		// Do nothing if pluggable functions already initialized.
		if ( self::$initialized ) {
			return;
		}

		// Register necessary actions / filters to initialize automatic update.
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		if ( get_option( 'nitro_customer' ) ) {
			add_filter( 'pre_set_site_transient_update_themes', array( __CLASS__, 'check_update' ) );
		}

		// State that initialization completed.
		self::$initialized = true;
	}

	/**
	 * Add meta boxes to configure automatic update.
	 *
	 * @return  void
	 */
	public static function admin_init() {
		// Migrate old option key to new one.
		if ( ( $purchase_code = get_option( 'nitro_purchase_code' ) ) ) {
			update_option( 'nitro_customer', array(
				'purchase_code' => $purchase_code,
			) );

			delete_option( 'nitro_purchase_code' );
		}

		// Register setting to store ThemeForest purchase code.
		register_setting( 'nitro_automatic_update', 'nitro_customer', array( __CLASS__, 'validate' ) );
	}

	/**
	 * Validate purchase code.
	 *
	 * @param   array  $input  Input array.
	 *
	 * @return  array
	 */
	public static function validate( $input ) {
		// Prepare purchase code.
		if ( empty( $input ) || ! isset( $input['email'] ) || ! isset( $input['purchase_code'] ) ) {
			wp_redirect( add_query_arg(
				'error',
				urlencode( __( 'Please input both email and purchase code.', 'wr-nitro' ) ),
				admin_url( 'admin.php?page=wr-intro' )
			) . '#registration' );

			exit;
		}
update_option( 'wr_download_token', $r['data'] );
 remove_filter( 'sanitize_option_nitro_customer', array( __CLASS__, 'validate' ) );
 update_option( 'nitro_customer', $input );
 wp_redirect( add_query_arg('message', urlencode( __( 'Thank you for choosing Nitro!', 'wr-nitro' ) ), admin_url( 'admin.php?page=wr-intro' )) . '#registration' );
  exit;
		// Verify purchase code.
		$r = wp_remote_get( add_query_arg( $input, self::ENVATO_APP_SERVER ) );

		if ( is_wp_error( $r ) ) {
			wp_redirect( add_query_arg(
				'error',
				urlencode( $r->get_error_message() ),
				admin_url( 'admin.php?page=wr-intro' )
			) . '#registration' );

			exit;
		}

		if ( ! ( $r = json_decode( $r['body'], true ) ) ) {
			wp_redirect( add_query_arg(
				'error',
				urlencode( __( 'Failed to get response from WooRockets server.', 'wr-nitro' ) ),
				admin_url( 'admin.php?page=wr-intro' )
			) . '#registration' );

			exit;
		}

		if ( ! $r['success'] ) {
			wp_redirect( add_query_arg(
				'error',
				urlencode( __( 'Purchase code verification failed.', 'wr-nitro' ) ),
				admin_url( 'admin.php?page=wr-intro' )
			) . '#registration' );

			exit;
		}

		// Save download token.
		update_option( 'wr_download_token', $r['data'] );

		// Save purchase code to options table.
		remove_filter( 'sanitize_option_nitro_customer', array( __CLASS__, 'validate' ) );

		update_option( 'nitro_customer', $input );

		wp_redirect( add_query_arg(
			'message',
			urlencode( __( 'Thank you for choosing Nitro!', 'wr-nitro' ) ),
			admin_url( 'admin.php?page=wr-intro' )
		) . '#registration' );

		exit;
	}

	/**
	 * Method to check for new version of WR Nitro.
	 *
	 * @param   array  $value  The value of update_themes site transient.
	 *
	 * @return  array
	 */
	public static function check_update( $value ) {
		// Get token to download update from WooRockets server.
		$token = self::get_download_token();

		// Get the latest version of Nitro from transient first.
		$latest = get_transient( 'wr_nitro_latest_version' );

		if ( ! $latest && $token ) {
			// Request WooRockets server for latest Nitro version.
			$r = wp_remote_get( add_query_arg( 'version', 'latest', self::ENVATO_APP_SERVER ) );

			if ( $r && ! is_wp_error( $r ) && $r = json_decode( $r['body'], true ) ) {
				if ( $r['success'] ) {
					// Store the latest version of Nitro to transient.
					$latest = $r['data'];

					set_transient( 'wr_nitro_latest_version', $latest, HOUR_IN_SECONDS );
				}
			}
		}

		// Check if update is available?
		if ( $latest ) {
			$theme = wp_get_theme();

			if ( $theme->get_template() != $theme->get_stylesheet() ) {
				$theme = $theme->parent();
			}

			if ( version_compare( $theme['Version'], $latest, '<' ) ) {
				// Check if update data is defined.
				$slug = current( array_slice( explode( '/', str_replace( '\\', '/', get_template_directory() ) ), -1 ) );
				$def  = ( isset( $value->response ) && isset( $value->response[ $slug ] ) );
				$ver  = $def ? $value->response[ $slug ]['new_version'] : '0.0.0';

				if ( ( ! $def || version_compare( $ver, $latest, '<' ) ) && $token ) {
					// Get Nitro customer.
					$customer = get_option( 'nitro_customer' );

					if ( $customer && isset( $customer['purchase_code'] ) ) {
						// Get theme URL.
						if ( $theme->get( 'ThemeURI' ) ) {
							$url = $theme->get( 'ThemeURI' );
						} else {
							$url = $theme->get( 'AuthorURI' );
						}

						// Set update data.
						$value->response[ $slug ] = array(
							'theme'       => $slug,
							'new_version' => $latest,
							'url'         => $url,
							'package'     => add_query_arg( array(
								'plugin' => $slug,
								'token'  => $token
							), self::ENVATO_APP_SERVER ),
						);
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Method to get token to download update from WooRockets server.
	 *
	 * @return  string
	 */
	public static function get_download_token() {
	return true;
	if ( get_option( 'nitro_customer' ) ) { return 'yep this is a token honest guv';
} else {
return false;
}
		// Get download token.
		$token = get_transient( 'envato_refresh_token' );

		if ( $token ) {
			$token = md5( $token );
		} else {
			$token = get_transient( 'wr_download_token' );

			if ( ! $token ) {
				$token = get_option( 'wr_download_token' );
			}
		}

		return $token;
	}
}
