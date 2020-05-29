<?php
/**
 * Plugin Name: WR Content Migration
 * Plugin URI:  http://www.woorockets.com/
 * Description: Export / import WordPress post, page and custom post type.
 * Version:     1.0.0
 * Author:      WooRockets <admin@woorockets.com>
 * Author URI:  http://woorockets.com/
 * License:     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Define plugin textdomain.
define( 'WR_CM', 'wr-content-migration' );

// Define path to plugin directory.
define( 'WR_CM_PATH', plugin_dir_path( __FILE__ ) );

// Define URL to plugin directory.
define( 'WR_CM_URL', plugin_dir_url( __FILE__ ) );

// Define plugin base file.
define( 'WR_CM_BASENAME', plugin_basename( __FILE__ ) );

// Load the core class.
require_once WR_CM_PATH . 'includes/core.php';

// Instantiate an object of the core class.
WR_Content_Migration::initialize();
