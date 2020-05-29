<?php
/**
 * Plugin Name: WR Currency
 * Plugin URI:  http://www.woorockets.com
 * Description: The versatile Currency Switcher for WooCommerce. Supported unlimited currency.
 * Version:     1.0.10
 * Author:      WooRockets <admin@woorockets.com>
 * Author URI:  http://www.woorockets.com
 * License:     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wr-currency
 * WC tested up to: 4.0.1
 */

// Define plugin textdomain.
define( 'WR_CC', 'wr-currency' );

// Define path to plugin directory.
define( 'WR_CC_PATH', plugin_dir_path( __FILE__ ) );

// Define URL to plugin directory.
define( 'WR_CC_URL', plugin_dir_url( __FILE__ ) );

// Define plugin base file.
define( 'WR_CC_BASENAME', plugin_basename( __FILE__ ) );

// Load the core class.
require_once WR_CC_PATH . 'includes/core.php';

// Instantiate an object of the core class.
WR_Currency::initialize();
