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
 * Define class that render a meta box to export content.
 */
class WR_Content_Migration_Meta_Box {
	/**
	 * Initialize.
	 *
	 * @return  void
	 */
	public static function initialize() {
		// Register meta box.
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register meta box.
	 *
	 * @return  void
	 */
	public static function register() {
		global $post_type;

		if ( in_array( $post_type, WR_Content_Migration::$supported_post_types ) ) {
			add_meta_box( 'wr-content-migration', 'Content Migration', array( __CLASS__, 'display' ), null, 'side', 'low' );
		}
	}

	/**
	 * Display meta box.
	 *
	 * @return  void
	 */
	public static function display() {
		do_action( 'wr_content_migration_render_form' );
	}
}
