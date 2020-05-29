<?php
/**
 * Shoptimizer functions.
 *
 * @package shoptimizer
 */

if ( ! function_exists( 'shoptimizer_is_woocommerce_activated' ) ) {
	/**
	 * Query WooCommerce activation
	 */
	function shoptimizer_is_woocommerce_activated() {
		return class_exists( 'WooCommerce' ) ? true : false;
	}
}


/**
 * Produces nice safe html for presentation.
 *
 * @param $input - accepts a string.
 * @return string
 */
function shoptimizer_safe_html( $input ) {

	$args = array(
		// formatting.
		'span'   => array(
			'class' => array(),
		),
		'h2'     => array(
			'class' => array(),
		),
		'del'    => array(),
		'ins'    => array(),
		'strong' => array(),
		'em'     => array(),
		'b'      => array(),
		'i'      => array(
			'class' => array(),
		),
		'img'    => array(
			'href'   => array(),
			'alt'    => array(),
			'class'  => array(),
			'scale'  => array(),
			'width'  => array(),
			'height' => array(),
			'src'    => array(),
			'srcset' => array(),
			'sizes'  => array(),
		),
		'p'      => array(),

		// links.
		'a'      => array(
			'href'  => array(),
			'class' => array(),
		),
	);

	return wp_kses( $input, $args );
}

/**
 * Checks if the current page is a product archive
 *
 * @return boolean
 */
function shoptimizer_is_product_archive() {
	if ( shoptimizer_is_woocommerce_activated() ) {
		if ( is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Call a shortcode function by tag name.
 *
 * @since  1.0.0
 *
 * @param string $tag     The shortcode whose function to call.
 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
 * @param array  $content The shortcode's content. Default is null (none).
 *
 * @return string|bool False on failure, the result of the shortcode on success.
 */

function shoptimizer_do_shortcode( $tag, array $atts = array(), $content = null ) {
	global $shortcode_tags;

	if ( ! isset( $shortcode_tags[ $tag ] ) ) {
		return false;
	}

	return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
}

/**
 * Schema type
 *
 * @return void
 */
function shoptimizer_html_tag_schema() {
	_deprecated_function( 'shoptimizer_html_tag_schema', '2.0.2' );

	$schema = 'http://schema.org/';
	$type   = 'WebPage';

	if ( is_singular( 'post' ) ) {
		$type = 'Article';
	} elseif ( is_author() ) {
		$type = 'ProfilePage';
	} elseif ( is_search() ) {
		$type = 'SearchResultsPage';
	}

	echo 'itemscope="itemscope" itemtype="' . esc_attr( $schema ) . esc_attr( $type ) . '"';
}
