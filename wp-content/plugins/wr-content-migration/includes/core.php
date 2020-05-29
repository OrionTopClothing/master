<?php
/**
 * @version    1.0
 * @package    WR_Content_Migration
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

/**
 * Define core class.
 */
class WR_Content_Migration {
	/**
	 * Define support post type.
	 *
	 * @var  array
	 */
	public static $supported_post_types = array( 'page', 'post', 'product' );

	/**
	 * Variable to hold class prefix supported for autoloading.
	 *
	 * @var  string
	 */
	protected static $prefix = 'WR_Content_Migration_';

	/**
	 * Initialize.
	 *
	 * @return  void
	 */
	public static function initialize() {
		// Register class autoloader.
		spl_autoload_register( array( __CLASS__, 'autoload' ) );

		// Initialize meta box.
		WR_Content_Migration_Meta_Box::initialize();

		// Initialize export / import action.
		WR_Content_Migration_Actions::initialize();

		// Register action to set placeholder for missing images.
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_footer_scripts' ) );
	}

	/**
	 * Set placeholder for missing images.
	 *
	 * @return  void
	 */
	public static function print_footer_scripts() {
		?>
		<script type="text/javascript">
			jQuery('img[src=""]').attr('src', '<?php echo esc_js( WR_CM_URL . 'assets/placeholder.jpg' ); ?>');
		</script>
		<?php
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
		$base = WR_CM_PATH . 'includes/';
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
