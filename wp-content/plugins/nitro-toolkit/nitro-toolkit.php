<?php
/**
 * Plugin Name: Nitro Toolkit
 * Plugin URI:  http://www.woorockets.com
 * Description: Nitro toolkit for Nitro theme. Currently supports the following theme functionality: shortcodes, CPT.
 * Version:     1.1.12
 * Author:      WooRockets Team <support@www.woorockets.com>
 * Author URI:  http://www.woorockets.com
 * License:     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nitro-toolkit
 */

// Define url to this plugin file.
define( 'NITRO_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );

// Define path to this plugin file.
define( 'NITRO_TOOLKIT_PATH', plugin_dir_path( __FILE__ ) );

// Include function plugins if not include.
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.3
 */
function nitro_toolkit_load_textdomain() {
	load_plugin_textdomain( 'nitro-toolkit', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
}
add_action( 'init', 'nitro_toolkit_load_textdomain' );

// Load basic initialization
include_once( NITRO_TOOLKIT_PATH . '/includes/base.php' );

// Run shortcode in widget text
add_filter( 'widget_text', 'do_shortcode' );

// Register custom shortcodes
if ( class_exists( 'Vc_Manager' ) ) {
	include_once( NITRO_TOOLKIT_PATH . '/includes/shortcode.php' );
}

// Register custom post types
include_once( NITRO_TOOLKIT_PATH . '/includes/post-type.php' );
