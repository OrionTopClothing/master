<?php
/**
 * @version    1.0
 * @package    Nitro
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

/**
 * Enqueue script for child theme
 */
function wr_nitro_child_enqueue_scripts() {
	wp_enqueue_style( 'wr-nitro-child-style', get_stylesheet_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'wr_nitro_child_enqueue_scripts', 1000000000 );