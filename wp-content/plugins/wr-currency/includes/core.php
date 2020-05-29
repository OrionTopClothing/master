<?php
/**
 * @version    1.0
 * @package    WR_Currency
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

/**
 * Define core class.
 */
class WR_Currency {
	/**
	 * Variable to hold class prefix supported for autoloading.
	 *
	 * @var  string
	 */
	protected static $prefix = 'WR_Currency_';

	/**
	 * Initialize.
	 *
	 * @return  void
	 */
	public static function initialize() {

		// Include function plugins if not include.
		self::include_function_plugins();

		if( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_admin_notice' ) );
			
			return;
		}

		// Register class autoloader.
		spl_autoload_register( array( __CLASS__, 'autoload' ) );

		// Load plugin textdomain.
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

		// Register action to enqueue necessary assets.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_backend_assets'  ) );
		add_action( 'wp_enqueue_scripts'   , array( __CLASS__, 'enqueue_frontend_assets' ) );

		// Initialize backend settings
		WR_Currency_Settings::initialize();
			
		// Initialize frontend
		WR_Currency_Hook::initialize();
	}

	/**
	 * Include function plugins if not include.
	 *
	 * @return  void
	 *
	 */
	public static function include_function_plugins() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
	}

	/**
	 * Show notice if WooCommerce plugin deactivate.
	 *
	 * @since 1.0.0
	 */
    public static function woocommerce_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'WR Currency plugin requires WooCommerce in order to work.', 'wr-currency' ); ?></p>
        </div>
    <?php
    }

	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'wr-currency', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Method to enqueue necessary assets for the screen to add/edit product.
	 *
	 * @return  void
	 */
	public static function enqueue_backend_assets() {
		global $pagenow;

		if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'wr-currency' ) {
			wp_enqueue_script( 'underscore' );

			wp_localize_script(
				'underscore',
				'wrls_settings_default',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'plugin_url' => WR_CC_URL,
					'security'   => wp_create_nonce( WR_CC_URL . '_nonce' ),
				)
			);


		}
	}

	/**
	 * Method to enqueue necessary assets for rendering custom attribute types in front-end.
	 *
	 * @return  void
	 */
	public static function enqueue_frontend_assets() {
/*		global $post;

		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() || ( isset( $post->post_content ) && ( has_shortcode( $post->post_content, 'nitro_product' ) || has_shortcode( $post->post_content, 'nitro_products' ) ) ) ) {
			// Enqueue stylesheet.
			wp_enqueue_style( WR_CC, WR_CC_URL . 'assets/css/front-end.css' );

			// Enqueue script.
			wp_enqueue_script( WR_CC, WR_CC_URL . 'assets/js/front-end.js', array( 'jquery' ) );
		}*/
	}

	/**
	 * Method to autoload class declaration file.
	 *
	 * @param   string  $class_name  Name of class to load declaration file for.
	 *
	 * @return  mixed
	 */
	public static function autoload( $class_name ) {
		// Verify class prefix.
		if ( 0 !== strpos( $class_name, self::$prefix ) ) {
			return false;
		}

		// Generate file path from class name.
		$base = WR_CC_PATH . 'includes/';
		$path = strtolower( str_replace( '_', '/', substr( $class_name, strlen( self::$prefix ) ) ) );

		// Check if class file exists.
		$standard    = $path . '.php';
		$alternative = $path . '/' . basename( $path ) . '.php';

		while ( true ) {
			// Check if file exists in standard path.
			if ( @is_file( $base . $standard ) ) {
				$exists = $standard;

				break;
			}

			// Check if file exists in alternative path.
			if ( @is_file( $base . $alternative ) ) {
				$exists = $alternative;

				break;
			}

			// If there is no more alternative file, quit the loop.
			if ( false === strrpos( $standard, '/' ) || 0 === strrpos( $standard, '/' ) ) {
				break;
			}

			// Generate more alternative files.
			$standard    = preg_replace( '#/([^/]+)$#', '-\\1', $standard );
			$alternative = dirname( $standard ) . '/' . substr( basename( $standard ), 0, -4 ) . '/' . basename( $standard );
		}

		// Include class declaration file if exists.
		if ( isset( $exists ) ) {
			return include_once $base . $exists;
		}

		return false;
	}
}
