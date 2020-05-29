<?php
/**
 * @version    1.0
 * @package    WR_Custom_Attributes
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

class WR_Currency_Hook {

	/**
	 * Variable when switch currency.
	 *
	 * @var  string
	 */
	protected static $currency_current = array();

	/**
	 * Variable when switch currency.
	 *
	 * @var  string
	 */
	protected static $currency_symbols = array();

	/**
	 * Variable when switch currency.
	 *
	 * @var  string
	 */
	protected static $woocommerce_currency = array();

	/**
	 * Initialize.
	 *
	 * @return  void
	 */
	public static function initialize() {
		// Make sure WooCommerce is active.
		if (
			! is_plugin_active('woocommerce/woocommerce.php')
			||
            ! class_exists( 'WooCommerce' )
//			! is_file( current(get_plugins('woocommerce/woocommerce.php') ) )
		) {
			return;
		}

		// Get the active currency from WooCommerce.
		self::$woocommerce_currency = get_woocommerce_currency();

		if ( isset( $_COOKIE[ WR_CC ] ) ) {
			$currency = unserialize( base64_decode( $_COOKIE[ WR_CC ] ) );

			self::$currency_current = $currency;
		}

		global $pagenow;

		// Support changing currency at frontend only.
		if ( ! is_admin() || $pagenow == 'admin-ajax.php' ) {
			self::switch_currency();

			add_filter( 'woocommerce_price_format', array(__CLASS__, 'change_position') );

			add_filter( 'woocommerce_currency'       , array(__CLASS__, 'change_currency'), 999999, 2 );
			add_filter( 'woocommerce_currency_symbol', array(__CLASS__, 'change_symbol'  ), 999999, 2 );

			add_filter( 'raw_woocommerce_price'                  , array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_item_get_subtotal'    , array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_item_get_subtotal_tax', array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_get_total'            , array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_get_total_tax'        , array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_get_shipping_tax'     , array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_get_shipping_total'   , array(__CLASS__, 'change_price') );
			add_filter( 'woocommerce_order_get_total_discount'   , array(__CLASS__, 'change_price') );
		}

		// Revert currency when viewing order in backend.
		if ( is_admin() ) {
			add_filter( 'get_post_metadata', array(__CLASS__, 'revert_order_curreny'), 999999, 4 );
		}

		// Check refreshed_fragments
		if ( isset( $_COOKIE[ 'wr_cc_fragments' ] ) && $_COOKIE[ 'wr_cc_fragments' ] == 1 ) {
			setcookie( 'wr_cc_fragments', 2, time() + DAY_IN_SECONDS, '/' );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'refreshed_fragments' ) );
		}

		// Add settings link into plugins list.
		add_filter( 'plugin_action_links_' . WR_CC_BASENAME, array( __CLASS__, 'add_action_links' ) );

	}

	/**
	 * Return currently active currency.
	 *
	 * @param   string  $currency  Currency.
	 *
	 * @return  string
	 */
	public static function change_currency( $currency ) {
		if ( isset(self::$currency_current['code']) ) {
			return self::$currency_current['code'];
		}

		return $currency;
	}

	/**
	 * Get currency symbol or list symbol.
	 *
	 * @param $currency string
	 *
	 * @return  array
	 */
	public static function currency_symbol( $currency = '' ){
		$symbols = apply_filters( 'woocommerce_currency_symbols', array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&fnof;',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'DKK',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x10da;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'Kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x639;.&#x62f;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => 'KZT',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRO' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/.',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#x434;&#x438;&#x43d;.',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STD' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'L',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'Fr',
			'XCD' => '&#36;',
			'XOF' => 'Fr',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		) );

		if( $currency && isset( $symbols[ $currency ] ) ) {
			return $symbols[ $currency ];
		}

		return $symbols;
	}

	/**
	 * Script trigger for refreshed fragments
	 *
	 * @return  void
	 */
	public static function refreshed_fragments() {
		$script = '
			( function( $ ) {
				"use strict";

				$( document ).ready( function() {
					$( document.body ).trigger( "wc_fragment_refresh" );
				} );

			} )( jQuery );
		';

		wp_add_inline_script( 'wc-cart-fragments', $script );
	}
	/**
	 * Switch currency in frontend
	 *
	 * @return  void
	 */
	public static function switch_currency() {
		if( ! empty( $_POST[ WR_CC ] ) ) {
			$id_currency = absint( $_POST[ WR_CC ] );

			$currency_current = self::get_currency( $id_currency );

			if( $currency_current || $_POST[ WR_CC ] == 'normal' ) {
				self::$currency_current = base64_encode( serialize( $currency_current ) );

				add_action( 'init', array( __CLASS__, 'setcookie_current' ) );
			}
		}
	}

	/**
	 * Set cookie for currency switch
	 *
	 * @return  void
	 */
	public static function setcookie_current() {
		if( $_POST[ WR_CC ] == 'normal' && isset( $_COOKIE[ WR_CC ] ) ) {
			unset( $_COOKIE[ WR_CC ] );
			setcookie( WR_CC, null, -1, '/' );
		} else {
			setcookie( WR_CC, self::$currency_current, time()+60*60*24*1, '/');
		}

		setcookie( 'wr_cc_fragments', 1, time()+60*60*24*1, '/');

		header( 'Location: '.$_SERVER['REQUEST_URI'] );
		die;
	}

	/**
	 * Get currency by id
	 *
	 * @return  void
	 */
	public static function get_currency( $id = 0 ) {
		$list_currency = get_option( WR_CC, '' );

		if( $id == 0 ) {
			return $list_currency;
		}

		if( $list_currency ) {
			foreach( $list_currency as $val ){
				if( $val['id'] == $id ) {
					return $val;
				}
			}
		}

		return;
	}

	/**
	 * Change symbol
	 *
	 * @param string $currency_symbol
	 * @param string $currency
	 *
	 * @return  void
	 */
	public static function change_symbol( $currency_symbol, $currency ) {
		if( self::$woocommerce_currency == $currency && isset( self::$currency_current['code'] ) ) {
			return self::currency_symbol( self::$currency_current['code'] );
		}

		return $currency_symbol;
	}

	/**
	 * Change position
	 *
	 * @param string $format
	 *
	 * @return  void
	 */
	public static function change_position( $format ) {
		if( isset( self::$currency_current['position'] ) ) {
			$currency_pos = self::$currency_current['position'];

			switch ( $currency_pos ) {
				case 'left' :
					$format = '%1$s%2$s';
				break;
				case 'right' :
					$format = '%2$s%1$s';
				break;
				case 'left_space' :
					$format = '%1$s&nbsp;%2$s';
				break;
				case 'right_space' :
					$format = '%2$s&nbsp;%1$s';
				break;
			}
		}

		return $format;
	}

	/**
	 * Change price
	 *
	 * @param number $price
	 *
	 * @return  void
	 */
	public static function change_price( $price ) {
		// If filter being applied is not 'raw_woocommerce_price', make sure
		// to alter price based on the selected currency only if checking out
		// with PayPal payment gateway.
		if (
			! doing_filter('raw_woocommerce_price')
			&&
			(
				! isset($_REQUEST['wc-ajax'])
				||
				$_REQUEST['wc-ajax'] != 'checkout'
				||
				! isset($_REQUEST['payment_method'])
				||
				$_REQUEST['payment_method'] != 'paypal'
			)
		) {
			return $price;
		}

		if ( isset( self::$currency_current['rate'] ) && is_numeric( self::$currency_current['rate'] ) ) {
			$price *= self::$currency_current['rate'];

			return $price;
		}

		return $price;
	}

	/**
	 * Revert order currency.
	 *
	 * @param   mixed   $value      Current meta value.
	 * @param   int     $object_id  Object ID.
	 * @param   string  $meta_key   Meta key.
	 * @param   bool    $single     Whether to return only the first value of the specified $meta_key.
	 */
	public static function revert_order_curreny( $value, $object_id, $meta_key, $single ) {
		if ( $meta_key == '_order_currency' ) {
			return get_option( 'woocommerce_currency' );
		}
	}

	/**
	 * Get list currentcy or get currency by key
	 *
	 * @param number $key
	 *
	 * @return  array
	 */
	public static function list_currency( $key = 0 ) {

		$list = get_woocommerce_currencies();

		foreach ( $list as $code => $name ) {
			$list[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
		}

		// Get currency by key
		if( $key && isset( $list[ $key ] ) ) {
			return $list[ $key ];
		}

		// Get all currency
		return $list;
	}

	/**
	 * Method to get / set current currency data.
	 *
	 * @param   string  $prop  Property to set.
	 * @param   mixed   $vals  Value to set.
	 *
	 * @return  mixed
	 */
	public static function prop( $prop, $vals = null ) {
		static $class_props;

		if ( ! isset( $class_props ) ) {
			$class_props = get_class_vars( __CLASS__ );
		}

		if ( false === strpos( $prop, 'currency_' ) ) {
			$prop = "currency_{$prop}";
		}

		if ( isset( $class_props[ $prop ] ) ) {
			if ( $vals !== null ) {
				self::${$prop} = $vals;
			} else {
				return self::${$prop};
			}
		}

		return $vals;
	}

	/**
	 * Add link setting in list plugin.
	 *
	 * @param string 	$links		List link current.
	 *
	 * @return  array
	 */
	public static function add_action_links( $links ) {
		$links['settings'] = '<a title="' . __( 'Open Currency Settings', 'wr-currency' ) . '" href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WR_CC ) . '">' . __( 'Settings', 'wr-currency' ) . '</a>';

		return $links;
	}

}
