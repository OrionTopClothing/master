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

class WR_Currency_Settings {

	/**
	 * Initialize.
	 *
	 * @return  void
	 */
	public static function initialize() {
		add_action( 'woocommerce_settings_tabs_array',       array( __CLASS__, 'register' ), 40 );
		add_action( 'woocommerce_settings_' . WR_CC,     array( __CLASS__, 'show' ) );
		add_action( 'woocommerce_settings_save_' .WR_CC, array( __CLASS__, 'save' ) );
	}

	/**
	 * Add Currency tab into WooCommerce settings screen.
	 *
	 * @param   array  $tabs  Current tabs for the settings page.
	 *
	 * @return  array
	 */
	public static function register( $tabs ) {
		$tabs[ WR_CC ] = __( 'Currency', 'wr-currency' );

		return $tabs;
	}

	/**
	 * Print HTML for the Share for Discounts configuration page.
	 *
	 * @return  void
	 */
	public static function show() {
		include_once WR_CC_PATH . 'templates/admin/list-currency.php';
	}


	/**
	 * Method to validate and save Share for Discounts settings.
	 *
	 * @return  void
	 */
	public static function save() {
		// Verify request.
		if ( ! ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], WR_CC ) ) ) {
			return;
		}

		$data_update = array();

		if( isset( $_POST['wrcc']['code'] ) &&  $_POST['wrcc']['code'] ) {

			// Update oocommerce currency code
			update_option( 'woocommerce_currency', $_POST['wrcc']['code'][ 0 ] );

			// Update woocommerce currency pos
			update_option( 'woocommerce_currency_pos', $_POST['wrcc']['position'][ 0 ] );

			$count_item = count( $_POST['wrcc']['code'] );

			if( $count_item > 1 ) {
				for( $i = 1; $i < $count_item; $i++ ) {
					foreach( $_POST['wrcc'] as $key => $val ){
						$data_update[ $i ][ $key ] = $_POST['wrcc'][ $key ][ $i ];
					}
				}
			}
		}

		update_option( WR_CC, $data_update );
	}

}