<?php
/**
 * WooCommerce Template Functions.
 *
 * @package shoptimizer
 */

add_filter( 'woocommerce_upsell_display_args', 'shoptimizer_woocommerce_upsell_display_args' );

/**
 * Single Product Page - Upsells value via the customizer.
 */
function shoptimizer_woocommerce_upsell_display_args( $args ) {

	$shoptimizer_layout_upsells_amount = '';
	$shoptimizer_layout_upsells_amount = shoptimizer_get_option( 'shoptimizer_layout_upsells_amount' );

	$args['posts_per_page'] = $shoptimizer_layout_upsells_amount;
	$args['columns']        = $shoptimizer_layout_upsells_amount;
	return $args;
}

/**
 * Single Product Page - Related number via the customizer.
 */
add_filter( 'woocommerce_output_related_products_args', 'shoptimizer_related_products', 99, 3 );

function shoptimizer_related_products( $args ) {

	$shoptimizer_layout_related_amount = '';
	$shoptimizer_layout_related_amount = shoptimizer_get_option( 'shoptimizer_layout_related_amount' );

	$args['posts_per_page'] = $shoptimizer_layout_related_amount;
	$args['columns']        = $shoptimizer_layout_related_amount;
	return $args;
}

/**
 * Cart Page - Change Cross Sells number of columns via the customizer.
 */
function shoptimizer_cross_sells_columns( $columns ) {
	$shoptimizer_layout_cross_sells_amount = '';
	$shoptimizer_layout_cross_sells_amount = shoptimizer_get_option( 'shoptimizer_layout_cross_sells_amount' );	
	return $shoptimizer_layout_cross_sells_amount;
}

add_filter( 'woocommerce_cross_sells_total', 'shoptimizer_cross_sells_number' );

/**
 * Cart Page - Change Cross Sells number via the customizer.
 */
function shoptimizer_cross_sells_number( $columns ) {
	$shoptimizer_layout_cross_sells_amount = '';
	$shoptimizer_layout_cross_sells_amount = shoptimizer_get_option( 'shoptimizer_layout_cross_sells_amount' );
	return $shoptimizer_layout_cross_sells_amount;
}

/**
 * Remove default WooCommerce product link open
 *
 * @see get_the_permalink()
 */
function shoptimizer_remove_woocommerce_template_loop_product_link_open() {
	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
}
add_action( 'wp_head', 'shoptimizer_remove_woocommerce_template_loop_product_link_open' );


/**
 * Remove default WooCommerce product link close
 *
 * @see get_the_permalink()
 */
function shoptimizer_remove_woocommerce_template_loop_product_link_close() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
}
add_action( 'wp_head', 'shoptimizer_remove_woocommerce_template_loop_product_link_close' );


/**
 * Open link before the product thumbnail image
 *
 * @see get_the_permalink()
 */
function shoptimizer_template_loop_image_link_open() {
	echo '<a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_image_link_open', 5 );


/**
 * Close link after the product thumbnail image
 *
 * @see get_the_permalink()
 */
function shoptimizer_template_loop_image_link_close() {
	echo '</a>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_image_link_close', 20 );


/**
 * Register details product_cat meta.
 *
 * Register the details metabox for WooCommerce product categories.
 */
function shoptimizer_product_cat_register_meta() {
	register_meta( 'term', 'below_category_content', 'shoptimizer_sanitize_details' );
}
add_action( 'init', 'shoptimizer_product_cat_register_meta' );

/**
 * Sanitize the details custom meta field.
 *
 * @param  string $details The existing details field.
 * @return string          The sanitized details field
 */
function shoptimizer_sanitize_details( $details ) {
	return wp_kses_post( $details );
}

/**
 * Add a details metabox to the Add New Product Category page.
 *
 * For adding a details metabox to the WordPress admin when
 * creating new product categories in WooCommerce.
 */
function shoptimizer_product_cat_add_details_meta() {
	wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_cat_details_nonce' );
	?>
	<div class="form-field">
		<label for="shoptimizer-product-cat-details"><?php esc_html_e( 'Below Category Content', 'shoptimizer' ); ?></label>
		<textarea name="shoptimizer-product-cat-details" id="shoptimizer-product-cat-details" rows="5" cols="40"></textarea>
		<p class="description"><?php esc_html_e( 'Category information which appears below the product list', 'shoptimizer' ); ?></p>
	</div>
	<?php
}
add_action( 'product_cat_add_form_fields', 'shoptimizer_product_cat_add_details_meta' );

/**
 * Add a details metabox to the Edit Product Category page.
 *
 * For adding a details metabox to the WordPress admin when
 * editing an existing product category in WooCommerce.
 *
 * @param  object $term The existing term object.
 */
function shoptimizer_product_cat_edit_details_meta( $term ) {
	$product_cat_details = get_term_meta( $term->term_id, 'below_category_content', true );
	if ( ! $product_cat_details ) {
		// double check if we have something in the options table (backwards compatibility).
		$product_cat_details_deprecated_obj = get_option( 'taxonomy_' . $term->term_id );
		if ( ! empty( $product_cat_details_deprecated_obj ) ) {
			$product_cat_details_deprecated = $product_cat_details_deprecated_obj['custom_term_meta'];
			$product_cat_details            = $product_cat_details_deprecated;
		} else {
			$product_cat_details = '';
		}
	}
	$settings = array( 'textarea_name' => 'shoptimizer-product-cat-details' );
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="shoptimizer-product-cat-details"><?php esc_html_e( 'Below Category Content', 'shoptimizer' ); ?></label></th>
		<td>
			<?php wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_cat_details_nonce' ); ?>
			<?php wp_editor( shoptimizer_sanitize_details( $product_cat_details ), 'product_cat_details', $settings ); ?>
			<p class="description"><?php esc_html_e( 'Category information which appears below the product list', 'shoptimizer' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'product_cat_edit_form_fields', 'shoptimizer_product_cat_edit_details_meta' );

/**
 * Save Product Category details meta.
 *
 * Save the product_cat details meta POSTed from the
 * edit product_cat page or the add product_cat page.
 *
 * @param  int $term_id The term ID of the term to update.
 */
function shoptimizer_product_cat_details_meta_save( $term_id ) {
	if ( ! isset( $_POST['shoptimizer_product_cat_details_nonce'] ) || ! wp_verify_nonce( $_POST['shoptimizer_product_cat_details_nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	$old_details = get_term_meta( $term_id, 'below_category_content', true );
	$new_details = isset( $_POST['shoptimizer-product-cat-details'] ) ? $_POST['shoptimizer-product-cat-details'] : '';
	if ( $old_details && '' === $new_details ) {
		delete_term_meta( $term_id, 'below_category_content' );
	} elseif ( $old_details !== $new_details ) {
		update_term_meta(
			$term_id,
			'below_category_content',
			shoptimizer_sanitize_details( $new_details )
		);
	}
}
add_action( 'create_product_cat', 'shoptimizer_product_cat_details_meta_save' );
add_action( 'edit_product_cat', 'shoptimizer_product_cat_details_meta_save' );

if ( ! function_exists( 'shoptimizer_content_filter' ) ) {
	/**
	 * Default content filter
	 *
	 * @param  string $details Post content.
	 * @return string          Post content.
	 */
	function shoptimizer_content_filter( $details ) {
		return $details;
	}
}

/**
 * Adds custom filter that filters the content and is used instead of 'the_content' filter.
 */
add_filter( 'shoptimizer_content_filter', 'wptexturize' );
add_filter( 'shoptimizer_content_filter', 'convert_chars' );
add_filter( 'shoptimizer_content_filter', 'wpautop' );
add_filter( 'shoptimizer_content_filter', 'shortcode_unautop' );
add_filter( 'shoptimizer_content_filter', 'do_shortcode' );

/**
 * Display below category content on Product Category archives.
 */
function shoptimizer_product_cat_display_details_meta() {
	if ( ! is_tax( 'product_cat' ) ) {
		return;
	}
	$t_id    = get_queried_object()->term_id;
	$details = get_term_meta( $t_id, 'below_category_content', true );

	if ( '' == $details ) {
		// double check for old value.
		$product_cat_details_deprecated_obj = get_option( 'taxonomy_' . $t_id );
		if ( ! empty( $product_cat_details_deprecated_obj['custom_term_meta'] ) ) {
			$product_cat_details_deprecated = $product_cat_details_deprecated_obj['custom_term_meta'];
			$details                        = $product_cat_details_deprecated;
		} else {
			$details = '';
		}
	}

	if ( '' !== $details ) {
		?>
		<div class="below-woocommerce-category">
			<?php
				global $wp_embed;
				add_filter( 'shoptimizer_content_filter', array( $wp_embed, 'autoembed' ) );
				echo apply_filters( 'shoptimizer_content_filter', wp_kses_post( $details ) );
			?>
		</div>
		<?php
	}
}
add_action( 'woocommerce_after_shop_loop', 'shoptimizer_product_cat_display_details_meta', 40 );

/**
* Checks if ACF is active.
*
* @return boolean
*/
if ( ! function_exists( 'shoptimizer_is_acf_activated' ) ) {
    /**
     * Query ACF activation.
     */
    function shoptimizer_is_acf_activated() {
        return class_exists( 'acf' ) ? true : false;
    }
}

/**
 * Before WooCommerce Shop Loop
 * Adds an ACF category banner full width field
 *
 * @since   1.0.0
 * @return  void
 */
add_action( 'shoptimizer_before_content', 'shoptimizer_product_cat_banner', 15 );

if ( ! function_exists( 'shoptimizer_product_cat_banner' ) ) {

	function shoptimizer_product_cat_banner() { 

		if (is_product_category() ) {

		$shoptimizer_layout_woocommerce_category_position = '';
		$shoptimizer_layout_woocommerce_category_position = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_category_position' );
		
		if ( 'below-header' === $shoptimizer_layout_woocommerce_category_position ) {

		wp_enqueue_script( 'shoptimizer-lazyload-bg', get_theme_file_uri( '/assets/js/lazyload-bg.js' ), array(), '20191215', false );

	 	$term = get_queried_object();

	 	if ( shoptimizer_is_acf_activated() ) {
		 	$categorybanner = get_field('category_banner', $term);
	 	}

		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
		remove_action( 'woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description' );
		remove_action( 'woocommerce_archive_description', 'shoptimizer_category_image', 20 );
		remove_action( 'woocommerce_before_main_content', 'shoptimizer_archives_title', 20 );

		?>

			<?php if( !empty($categorybanner) ): ?>
			<style>
			.shoptimizer-category-banner.visible {
				background-image: url('<?php echo $categorybanner ?>');
			}	
			</style>
			<?php endif; ?>

			<?php if( !empty($categorybanner) ) { ?>
			<div class="shoptimizer-category-banner lazy-background">
			<?php } else { ?>

			<div class="shoptimizer-category-banner">
			<?php } ?>
				<div class="col-full">
					<h1><?php single_cat_title(); ?></h1>
					<?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
				</div>
			</div>
			<?php
			}
		}
	}
}


/**
 * After WooCommerce Shop Loop
 * Adds support for YITH Wishlist functionality
 *
 * @since   1.0.0
 * @return  void
 */
function shoptimizer_display_yith_wishlist_loop() {
	if ( class_exists( 'YITH_WCWL_Shortcode' ) ) {
		echo do_shortcode( '[yith_wcwl_add_to_wishlist]' );
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'shoptimizer_display_yith_wishlist_loop', 97 );

if ( ! function_exists( 'shoptimizer_before_content' ) ) {
	/**
	 * Before Content
	 * Wraps all WooCommerce content in wrappers which match the theme markup
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_before_content() {
		?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
		<?php
	}
}

if ( ! function_exists( 'shoptimizer_after_content' ) ) {
	/**
	 * After Content
	 * Closes the wrapping divs
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_after_content() {
		?>
			</main><!-- #main -->
		</div><!-- #primary -->

		<?php
		do_action( 'shoptimizer_sidebar' );
	}
}

if ( ! function_exists( 'shoptimizer_cart_link_fragment' ) ) {
	/**
	 * Cart Fragments
	 * Ensure cart contents update when products are added to the cart via AJAX
	 *
	 * @param  array $fragments Fragments to refresh via AJAX.
	 * @return array            Fragments to refresh via AJAX
	 */
	function shoptimizer_cart_link_fragment( $fragments ) {
		global $woocommerce;

		ob_start();
		shoptimizer_cart_link();
		$fragments['a.cart-contents'] = ob_get_clean();

		return $fragments;
	}
}



if ( ! function_exists( 'shoptimizer_cart_link' ) ) {
	/**
	 * Cart Link
	 * Displayed a link to the cart including the number of items present and the cart total
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function shoptimizer_cart_link() {

	$shoptimizer_layout_woocommerce_enable_sidebar_cart = '';
	$shoptimizer_layout_woocommerce_enable_sidebar_cart = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_enable_sidebar_cart' );

	$shoptimizer_layout_woocommerce_cart_icon = '';
	$shoptimizer_layout_woocommerce_cart_icon = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_cart_icon' );
	?>
			
	<div class="cart-click">

		<?php if ( true === $shoptimizer_layout_woocommerce_enable_sidebar_cart ) { ?>
			<a class="cart-contents" href="#" title="<?php esc_attr_e( 'View your shopping cart', 'shoptimizer' ); ?>">
		<?php } else { ?>
			<a class="cart-contents" href="<?php echo wc_get_cart_url(); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'shoptimizer' ); ?>">
		<?php } ?>

		<span class="amount"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>

		<?php if ( 'basket' === $shoptimizer_layout_woocommerce_cart_icon ) { ?>

		<span class="count"><?php echo wp_kses_post( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ); ?></span>
		<?php } else { ?>
		<span class="shoptimizer-cart-icon"><i class="ri-shopping-cart"></i><span class="mini-count"><?php echo wp_kses_data( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ); ?></span></span>
		<?php } ?>


		</a>
	</div>	
		<?php
	}
}


if ( ! function_exists( 'shoptimizer_product_search' ) ) {
	/**
	 * Display Product Search
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_product_search() {
		if ( shoptimizer_is_woocommerce_activated() ) {
			$shoptimizer_layout_search_display = '';
			$shoptimizer_layout_search_display = shoptimizer_get_option( 'shoptimizer_layout_search_display' );

			if ( isset( $_GET['header'] ) ) {
					$shoptimizer_header_layout = $_GET['header'];
			}

			?>
			
			<div class="site-search">
				<?php the_widget( 'WC_Widget_Product_Search', 'title=' ); ?>
			</div>
			
			<?php
		}
	}
}

if ( ! function_exists( 'shoptimizer_header_cart' ) ) {
	/**
	 * Display Header Cart
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_header_cart() {
		$shoptimizer_layout_woocommerce_display_cart = '';
		$shoptimizer_layout_woocommerce_display_cart = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_cart' );
		if ( shoptimizer_is_woocommerce_activated() ) {
			if ( is_cart() ) {
				$class = 'current-menu-item';
			} else {
				$class = '';
			}
			?>
			<?php if ( true === $shoptimizer_layout_woocommerce_display_cart ) { ?>
		<ul class="site-header-cart menu">
			<li><?php shoptimizer_cart_link(); ?></li>
		</ul>
		<?php } ?>
			<?php
		}
	}
}

if ( ! function_exists( 'shoptimizer_header_cart_drawer' ) ) {
	/**
	 * Display Header Cart Drawer
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_header_cart_drawer() {
		if ( shoptimizer_is_woocommerce_activated() ) {
			if ( is_cart() ) {
				$class = 'current-menu-item';
			} else {
				$class = '';
			}
			?>
		<div class="shoptimizer-mini-cart-wrap">
			<div id="ajax-loading">
				<div class="shoptimizer-loader">
					<div class="spinner">
					<div class="bounce1"></div>
					<div class="bounce2"></div>
					<div class="bounce3"></div>
					</div>
				</div>
			</div>
			<div class="close-drawer"></div>

			<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?></div>	
			<?php

				$shoptimizer_cart_drawer_js  = '';
				$shoptimizer_cart_drawer_js .= "
				( function ( $ ) {

					// Open the drawer if a product is added to the cart
					$( '.product_type_simple.add_to_cart_button' ).on( 'click', function( e ) {
						e.preventDefault();
						$( 'body' ).toggleClass( 'drawer-open' );
					} );

					// Toggle cart drawer.
					$( '.site-header-cart .cart-click' ).on( 'click', function( e ) {
						e.stopPropagation();
						e.preventDefault();
						$( 'body' ).toggleClass( 'drawer-open' );
					} );

					// Close the drawer when clicking outside it
					$( document ).mouseup( function( e ) {
						var container = $( '.shoptimizer-mini-cart-wrap' );

						if ( ! container.is( e.target ) && 0 === container.has( e.target ).length ) {
							$( 'body' ).removeClass( 'drawer-open' );
						}
					} );

					// Close drawer - click the x icon
					$( '.close-drawer' ).on( 'click', function() {
						$( 'body' ).removeClass( 'drawer-open' );
					} );

				}( jQuery ) );
				";

		wp_add_inline_script( 'shoptimizer-main', $shoptimizer_cart_drawer_js );
		}
	}
}

if ( ! function_exists( 'shoptimizer_upsell_display' ) ) {
	/**
	 * Upsells
	 * Replace the default upsell function with our own which displays the correct number product columns
	 *
	 * @since   1.0.0
	 * @return  void
	 * @uses    woocommerce_upsell_display()
	 */
	function shoptimizer_upsell_display() {
		$columns = apply_filters( 'shoptimizer_upsells_columns', 4 );
		woocommerce_upsell_display( -1, $columns );
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper' ) ) {
	/**
	 * Sorting wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_sorting_wrapper() {
		echo '<div class="shoptimizer-sorting">';
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper_end' ) ) {
	/**
	 * Sorting wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_sorting_wrapper_end() {
		echo '<div class="shoptimizer-sorting sorting-end">';
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper_close' ) ) {
	/**
	 * Sorting wrapper close
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_sorting_wrapper_close() {
		echo '</div>';
	}
}

if ( ! function_exists( 'shoptimizer_product_columns_wrapper' ) ) {
	/**
	 * Product columns wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_product_columns_wrapper() {
		$columns = shoptimizer_loop_columns();
		echo '<div class="columns-' . absint( $columns ) . '">';
	}
}

if ( ! function_exists( 'shoptimizer_loop_columns' ) ) {
	/**
	 * Default loop columns on product archives
	 *
	 * @return integer products per row
	 * @since  1.0.0
	 */
	function shoptimizer_loop_columns() {
		$columns = 3;

		if ( function_exists( 'wc_get_default_products_per_row' ) ) {
			$columns = wc_get_default_products_per_row();
		}

		return apply_filters( 'shoptimizer_loop_columns', $columns );
	}
}

if ( ! function_exists( 'shoptimizer_product_columns_wrapper_close' ) ) {
	/**
	 * Product columns wrapper close
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_product_columns_wrapper_close() {
		echo '</div>';
	}
}


if ( ! function_exists( 'shoptimizer_breadcrumbs' ) ) {
	/**
	 * Breadcrumbs
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_breadcrumbs() {
		$shoptimizer_layout_woocommerce_display_breadcrumbs = '';
		$shoptimizer_layout_woocommerce_display_breadcrumbs = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_breadcrumbs' );

		$shoptimizer_layout_woocommerce_breadcrumbs_type = '';
		$shoptimizer_layout_woocommerce_breadcrumbs_type = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_breadcrumbs_type' );

		if ( true === $shoptimizer_layout_woocommerce_display_breadcrumbs ) {

			if ( 'default' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {
				if (!is_page_template('template-fullwidth-no-heading.php') && !is_page_template('template-canvas.php')) {
					add_action( 'shoptimizer_content_top', 'woocommerce_breadcrumb', 10 );
				}
			}

			if ( 'rankmath' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {

				if (function_exists('rank_math_the_breadcrumbs')) { 
					if (!is_page_template('template-fullwidth-no-heading.php') && !is_page_template('template-canvas.php')) {
					echo '<div class="rankmath woocommerce-breadcrumb">'; 
					rank_math_the_breadcrumbs(); 
					echo '</div>';
					}
				}
			}

			if ( 'yoast' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {
				if ( function_exists( 'yoast_breadcrumb' ) ) {
					if (!is_page_template('template-fullwidth-no-heading.php') && !is_page_template('template-canvas.php')) {
						yoast_breadcrumb( '<nav class="yoast woocommerce-breadcrumb">', '</nav>' );
					}
				}
			}
		}
		
	}
}

add_filter( 'wpseo_breadcrumb_separator', function( $separator ) {
     return '<span class="breadcrumb-separator">' . $separator . '</span>';
} );


if ( ! function_exists( 'shoptimizer_shop_messages' ) ) {
	/**
	 * Shoptimizer shop messages
	 *
	 * @since   1.0.0
	 */
	function shoptimizer_shop_messages() {
		if ( ! is_checkout() ) {
			echo wp_kses_post( shoptimizer_do_shortcode( 'woocommerce_messages' ) );
		}
	}
}

if ( ! function_exists( 'shoptimizer_woocommerce_pagination' ) ) {
	/**
	 * Shoptimizer WooCommerce Pagination
	 *
	 * @since 1.0.0
	 */
	function shoptimizer_woocommerce_pagination() {
		if ( woocommerce_products_will_display() ) {
			woocommerce_pagination();
		}
	}
}

/**
 * Change Reviews tab title.
 */
function shoptimizer_woocommerce_reviews_tab_title( $title ) {
	$title = strtr(
		$title, array(
			'(' => '<span>',
			')' => '</span>',
		)
	);

	return $title;
}
add_filter( 'woocommerce_product_reviews_tab_title', 'shoptimizer_woocommerce_reviews_tab_title' );


/**
 * Display discounted % on product loop.
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_change_displayed_sale_price_html', 11 );
add_action( 'woocommerce_single_product_summary', 'shoptimizer_change_displayed_sale_price_html', 10 );
add_action( 'woocommerce_single_product_summary', 'shoptimizer_clear_product_price', 11 );

if ( ! function_exists( 'shoptimizer_clear_product_price' ) ) {
	/**
	 * Clear product price
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_clear_product_price() {
		echo '<div class="clear"></div>';
	}
}

/**
 * Shop page - Out of Stock
 */
if ( ! function_exists( 'shoptimizer_shop_out_of_stock' ) ) :
	/**
	 * Add Out of Stock to the Shop page
	 *
	 * @hooked woocommerce_before_shop_loop_item_title - 8
	 *
	 * @since 1.8.5
	 */
	function shoptimizer_shop_out_of_stock() {
		$out_of_stock        = get_post_meta( get_the_ID(), '_stock_status', true );
		$out_of_stock_string = apply_filters( 'shoptimizer_shop_out_of_stock_string', __( 'Out of stock', 'shoptimizer' ) );
		if ( 'outofstock' === $out_of_stock ) {
			?>
			<span class="product-out-of-stock"><em><?php echo esc_html( $out_of_stock_string ); ?></em></span>
			<?php
		}
	}

endif;

function shoptimizer_change_displayed_sale_price_html() {

	global $product, $price;
	$shoptimizer_sale_badge = '';

	$shoptimizer_layout_woocommerce_display_badge = '';
	$shoptimizer_layout_woocommerce_display_badge = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge' );

	if ( $product->is_on_sale() && ! is_admin() && ! $product->is_type( 'grouped' ) ) {

		if ( $product->is_type( 'variable' ) ) {
			$percentages = array();

			// Get all variation prices.
			$prices = $product->get_variation_prices();

			// Loop through variation prices.
			foreach ( $prices['price'] as $key => $price ) {
				// Only on sale variations.
				if ( $prices['regular_price'][ $key ] !== $price ) {
					// Calculate and set in the array the percentage for each variation on sale.
					$percentages[] = round( 100 - ( $prices['sale_price'][ $key ] / $prices['regular_price'][ $key ] * 100 ) );
				}
			}
			// Keep the highest value.
			$percentage = max( $percentages ) . '%';

		} else {
			$regular_price = (float) $product->get_regular_price();
			$sale_price    = (float) $product->get_price();

			$percentage = round( 100 - ( $sale_price / $regular_price * 100 ), 0 ) . '%';
		}

		$shoptimizer_sale_badge .= sprintf( __( '<span class="sale-item product-label">-%s</span>', 'shoptimizer' ), $percentage );

	}

	if ( true === $shoptimizer_layout_woocommerce_display_badge ) {
		echo shoptimizer_safe_html( $shoptimizer_sale_badge );
	}

}


add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_content_header_open', 5 );

function shoptimizer_loop_product_content_header_open() {
	echo '<div class="woocommerce-card__header">';
}

add_action( 'woocommerce_after_shop_loop_item', 'shoptimizer_loop_product_content_header_close', 60 );

function shoptimizer_loop_product_content_header_close() {
	echo '</div>';
}

/**
 * Within Product Loop - remove title hook and create a new one with the category displayed above it.
 */
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_title', 10 );

function shoptimizer_loop_product_title() {

	global $post;

	$shoptimizer_layout_woocommerce_display_category = '';
	$shoptimizer_layout_woocommerce_display_category = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_category' );
	?>
		<?php if ( true === $shoptimizer_layout_woocommerce_display_category ) { ?>
			<?php echo '<p class="product__categories">' . wc_get_product_category_list( get_the_id(), ', ', '', '' ) . '</p>'; ?>
		<?php } ?>
		<?php
		echo '<div class="woocommerce-loop-product__title"><a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">' . get_the_title() . '</a></div>';
}

/**
 * Single Product Page - Previous/Next hover feature.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_prev_next_product', 0 );

function shoptimizer_prev_next_product() {

		$shoptimizer_layout_woocommerce_prev_next_display = '';
		$shoptimizer_layout_woocommerce_prev_next_display = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_prev_next_display' );

		$shoptimizer_next = get_next_post();
		$shoptimizer_prev = get_previous_post();

		$shoptimizer_next = ( ! empty( $shoptimizer_next ) ) ? wc_get_product( $shoptimizer_next->ID ) : false;
		$shoptimizer_prev = ( ! empty( $shoptimizer_prev ) ) ? wc_get_product( $shoptimizer_prev->ID ) : false;

	?>
		<?php if ( true === $shoptimizer_layout_woocommerce_prev_next_display ) { ?>
			<div class="shoptimizer-product-prevnext">


				<?php if ( ! empty( $shoptimizer_prev ) ) : ?>
				
					<a href="<?php echo esc_url( $shoptimizer_prev->get_permalink() ); ?>"><span class="icon ri ri-chevron-left-circle"></span>
					<div class="tooltip">
						<?php echo shoptimizer_safe_html( $shoptimizer_prev->get_image() ); ?>
						<span class="title"><?php echo shoptimizer_safe_html( $shoptimizer_prev->get_title() ); ?></span>
						<span class="price"><?php echo shoptimizer_safe_html( $shoptimizer_prev->get_price_html() ); ?></span>								
					</div>
					</a>
				
				<?php endif ?>

				<?php if ( ! empty( $shoptimizer_next ) ) : ?>

					<a href="<?php echo esc_url( $shoptimizer_next->get_permalink() ); ?>"><span class="icon ri ri-chevron-right-circle"></span>
					<div class="tooltip">
						<?php echo shoptimizer_safe_html( $shoptimizer_next->get_image() ); ?>
						<span class="title"><?php echo shoptimizer_safe_html( $shoptimizer_next->get_title() ); ?></span>
						<span class="price"><?php echo shoptimizer_safe_html( $shoptimizer_next->get_price_html() ); ?></span>							
					</div>
					</a>
				
				<?php endif ?>

			</div>
			

			<?php
}
}


/**
 * Single Product Page - Call me back feature.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_feature', 80 );

if ( ! function_exists( 'shoptimizer_call_back_feature' ) ) {
function shoptimizer_call_back_feature() {

	$shoptimizer_layout_floating_button_display = '';
	$shoptimizer_layout_floating_button_display = shoptimizer_get_option( 'shoptimizer_layout_floating_button_display' );

	$shoptimizer_layout_floating_button_text = shoptimizer_get_option( 'shoptimizer_layout_floating_button_text' );

	if ( true === $shoptimizer_layout_floating_button_display ) {

		echo '
		<div class="call-back-feature"><a href="#" class="trigger">';
		echo shoptimizer_safe_html( $shoptimizer_layout_floating_button_text );
		echo '</a>

		</div>';
		echo '

	<div class="modal callback-modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close-button callback-close-button" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">';
		  dynamic_sidebar( 'floating-button-content' );
		  echo '</div>
		</div>
	  </div>
	</div>
	';


	$shoptimizer_modal_js  = '';
	$shoptimizer_modal_js .= "
			( function ( $ ) {

$( '.trigger' ).click( function( e ) {
		e.stopPropagation();
		e.preventDefault();
} );				

var modal = document.querySelector( '.callback-modal' );
var trigger = document.querySelector( '.trigger' );
var closeButton = document.querySelector( '.callback-close-button' );

function toggleModal() {
    modal.classList.toggle( 'show-modal' );
}

function windowOnClick( event ) {
    if ( event.target === modal ) {
        toggleModal();
    }
}

trigger.addEventListener( 'click', toggleModal );
closeButton.addEventListener( 'click', toggleModal );
window.addEventListener( 'click', windowOnClick );

			}( jQuery ) );
		";

		wp_add_inline_script( 'shoptimizer-main', $shoptimizer_modal_js );


	} ?>


<?php } }

/**
 * Search modal. Only present in Layout 4 - One row header.
 */
if ( ! function_exists( 'shoptimizer_search_modal' ) ) {

function shoptimizer_search_modal() {

	$shoptimizer_layout_search_display = '';
	$shoptimizer_layout_search_display = shoptimizer_get_option( 'shoptimizer_layout_search_display' );

	$shoptimizer_layout_search_title = shoptimizer_get_option( 'shoptimizer_layout_search_title' );

		if ( 'enable' === $shoptimizer_layout_search_display ) {
		echo '<a href="#" class="search-trigger"><span class="ri ri-search"></span></a>';
		?>

	<div class="modal search-main-modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close-button search-close-button" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		  <div class="site-search">
		  <?php the_widget( 'WC_Widget_Product_Search', 'title=' ); ?>

		  <div class="search-extras">
		  <h4 class="search-modal-heading"><?php echo shoptimizer_safe_html( $shoptimizer_layout_search_title ); ?></h4>
		  <?php echo shoptimizer_do_shortcode( 'recent_products', array(
			'per_page' => 3,
			'columns'  => 3,
		  ) ); ?>
		</div>
		</div>
	  </div>
	</div>
	<?php 


	$shoptimizer_modal_js  = '';
	$shoptimizer_modal_js .= "
			( function ( $ ) {

$( '.search-trigger' ).click( function( e ) {
		e.stopPropagation();
		e.preventDefault();
} );				

var modal = document.querySelector( '.search-main-modal' );
var trigger = document.querySelector( '.search-trigger' );
var closeButton = document.querySelector( '.search-close-button' );

function toggleModal() {
    modal.classList.toggle( 'show-modal' );
}

function windowOnClick( event ) {
    if ( event.target === modal ) {
        toggleModal();
    }
}

trigger.addEventListener( 'click', toggleModal );
closeButton.addEventListener( 'click', toggleModal );
window.addEventListener( 'click', windowOnClick );

			}( jQuery ) );
		";

		wp_add_inline_script( 'shoptimizer-main', $shoptimizer_modal_js );


	} ?>


<?php } }


/**
 * Single Product Page - Vertical Gallery
 */
if ( ! function_exists( 'shoptimizer_vertical_gallery' ) ) {
function shoptimizer_vertical_gallery() {

	$shoptimizer_layout_gallery_position = '';
	$shoptimizer_layout_gallery_position = shoptimizer_get_option( 'shoptimizer_layout_gallery_position' );

	if ( 'vertical' === $shoptimizer_layout_gallery_position ) {
	?>

	<?php 

	$shoptimizer_vertical_gallery_js  = '';
	$shoptimizer_vertical_gallery_js .= "
			( function ( $ ) {

			window.onload = function() { 
				$(window).resize(function() {
					var verticalgallery = $('.product .flex-viewport');
					$('.product .flex-control-thumbs').css({height:verticalgallery.height()});   
				}).resize(); 
			}

			}( jQuery ) );
		";

		wp_add_inline_script( 'shoptimizer-main', $shoptimizer_vertical_gallery_js );

	} ?>


<?php } }


/**
 * Single Product Gallery - exclude from Jetpack's Lazy Load.
 */
function is_lazyload_activated() { 
	$condition = is_product(); if ( $condition ) return false; return true; 
}

add_filter( 'lazyload_is_enabled', 'is_lazyload_activated', 10, 3 );

/**
 * Single Product Page - Inventory FOMO Shortcode creation
 */
function shoptimizer_inventory_number_shortcode( $atts ) {
    global $post;
    global $product;
    $shoptimizer_inventory_number = '';

    $args = shortcode_atts(
        array(
            'min'  => 3,
            'max' => 15,
            'id' => $post->ID,
            'hours' => 72
        ),
        $atts, 'shoptimizer_random_number'
    );

    if ( false === ( $shoptimizer_random_number = get_transient( 'random_number_'.$args['id'] ) ) ) {
        $shoptimizer_random_number = mt_rand( (int) $args['min'], (int) $args['max'] );
        set_transient( 'shoptimizer_random_number_'.$args['id'], $shoptimizer_random_number, HOUR_IN_SECONDS * $args['hours'] );
    }

    $shoptimizer_stock_quantity = $product->get_stock_quantity();

    $shoptimizer_inventory_number .= sprintf( __( '<div class="shoptimizer-inventory"><span class="title">Only %s items left in stock!</span><div class="progress-bar"><span></span></div></div>', 'shoptimizer' ), $shoptimizer_random_number );

    if ($shoptimizer_stock_quantity > $shoptimizer_random_number) { 
    	return $shoptimizer_inventory_number;
	}
}
add_shortcode( 'shoptimizer_random_number', 'shoptimizer_inventory_number_shortcode' );


/**
 * Single Product Page - Display Inventory FOMO Shortcode
 */
if ( ! function_exists( 'shoptimizer_display_inventory_counter' ) ) {

	function shoptimizer_display_inventory_counter() {
	$shoptimizer_layout_inventory_display = '';
	$shoptimizer_layout_inventory_display = shoptimizer_get_option( 'shoptimizer_layout_inventory_display' );

	global $product; ?>

	<?php if ( true === $shoptimizer_layout_inventory_display ) {
		if ( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) ) {
			echo do_shortcode( '[shoptimizer_random_number]' );
		}
	}
}
}
add_action( 'woocommerce_single_product_summary', 'shoptimizer_display_inventory_counter', 40 );


/**
 * Variation selected highlight
 *
 * @since 1.6.1
 */ 
add_action( 'woocommerce_before_add_to_cart_quantity', 'shoptimizer_highlight_selected_variation' );
 
function shoptimizer_highlight_selected_variation() {
    
   global $product;
    
   if ( $product->is_type('variable') ) {
       
      ?>
     <script>

	 jQuery(document).ready(function($) {  
	$( '.variations .label label' ).wrapInner( '<span/>' );  

	$(".value").on('change', function() {
	  var optionIndex = $(this).find('option:selected').index();
	  $(this).closest( 'tr' ).toggleClass('selected-variation', optionIndex !== 0);
	});

	});

    </script>
      <?php
    
   }
    
}

add_action( 'shoptimizer_after_footer', 'shoptimizer_masonry_layout' );
if ( ! function_exists( 'shoptimizer_masonry_layout' ) ) {
	/**
	 * Masonry Layout on Shop
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_masonry_layout() {
		if ( shoptimizer_is_woocommerce_activated() ) {
			$shoptimizer_masonry_layout = '';
			$shoptimizer_masonry_layout = shoptimizer_get_option( 'shoptimizer_masonry_layout' );
			?>
			<?php if ( true === $shoptimizer_masonry_layout ) { ?>	

				<?php
					$shoptimizer_masonry_layout_js  = '';
					$shoptimizer_masonry_layout_js .= "

					;
					( function( $ ) {
						'use strict';

						$( window ).on( 'load resize', function() {
							if ( 992 < $( window ).width() ) {

								$('.archive .products').masonry({
						           itemSelector: '.product',
						           isAnimated: true
							}); 							
						}
						} );

					}( jQuery ) );

					";

					wp_add_inline_script( 'shoptimizer-main', $shoptimizer_masonry_layout_js );
				?>

			<?php } ?>
			<?php
		}
	}
}

/**
 * Product Archives - move title.
 */
function shoptimizer_archives_title() {

	if ( is_product_category() || is_product_tag() || is_tax( 'product_brand' ) ) {
		echo '<h1 class="woocommerce-products-header__title page-title">';
		woocommerce_page_title();
		echo '</h1>';
	}

}

/**
 * Display WooCommerce product category description on all category archive pages.
 */
function shoptimizer_woocommerce_taxonomy_archive_description() {
	if ( is_tax( array( 'product_cat', 'product_tag' ) ) && get_query_var( 'paged' ) !== 0 ) {
		$description = wc_format_content( term_description() );
		if ( $description ) {
			echo '<div class="term-description">' . $description . '</div>';
		}
	}
}
add_action( 'woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description' );


/**
 * Elementor Pro header styling. Only hooked via class-shoptimizer-elementor-pro.php when a custom header is used in Elementor Pro.
 */
function shoptimizer_elementor_pro_styling() {

	echo'
	<style>
		.col-full.topbar-wrapper { border: none; }
		@media (min-width: 992px) {
			.col-full.main-header, .col-full-nav { padding-top: 0px; }
		}
	</style>';

}

/**
 * Product Archives - Mobile filters
 */
add_action( 'woocommerce_before_shop_loop', 'shoptimizer_mobile_filters', 5 );
add_action( 'woocommerce_after_shop_loop', 'shoptimizer_mobile_filters', 5 );

function shoptimizer_mobile_filters() {
	if ( is_active_sidebar( 'sidebar-1' ) ) {
		echo '<a href="#" class="mobile-filter">'; ?>
		<?php esc_html_e( 'Show Filters', 'shoptimizer' ); ?>
		<?php echo '</a>'; ?>
		<?php
	}
}


add_action( 'shoptimizer_page_before', 'shoptimizer_template_archives_mobile_filter', 20 );
add_action( 'shoptimizer_page_after', 'shoptimizer_template_archives_mobile_filter', 20 );

if ( ! function_exists( 'shoptimizer_template_archives_mobile_filter' ) ) {
	/**
	 * Mobile filters on the WooCommerce Archives Template
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_template_archives_mobile_filter() {	

		if (is_page_template('template-woocommerce-archives.php')) {
			add_action( 'shoptimizer_page_before', 'shoptimizer_mobile_filters', 30 );
			add_action( 'shoptimizer_page_after', 'shoptimizer_mobile_filters', 30 );
		}	
		
	}
}


if ( class_exists( 'WooCommerce' ) ) {
	add_action( 'get_header', 'shoptimizer_remove_product_sidebar' );

	/**
	 * Remove sidebar on a single product page.
	 */
	function shoptimizer_remove_product_sidebar() {
		if ( is_product() ) {
			remove_action( 'shoptimizer_sidebar', 'shoptimizer_get_sidebar', 10 );
		}
	}
}


add_action( 'woocommerce_before_single_product_summary', 'shoptimizer_product_content_wrapper_start', 5 );
add_action( 'woocommerce_single_product_summary', 'shoptimizer_product_content_wrapper_end', 60 );

add_action( 'woocommerce_after_single_product', 'shoptimizer_main_content_wrapper_end', 60 );

/**
 * Single Product Page - Add a section wrapper start.
 */
function shoptimizer_product_content_wrapper_start() {
	echo '<div class="product-details-wrapper">';
}

/**
 * Single Product Page - Add a section wrapper end.
 */
function shoptimizer_product_content_wrapper_end() {
	echo '</div><!--/product-details-wrapper end-->';
}

/**
 * Single Product Page - Add a section wrapper end.
 */
function shoptimizer_main_content_wrapper_end() {
	echo '</div><!--/main end-->';
}


/**
 * Single Product - Display custom content below Buy Now Button
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_product_custom_content', 45 );

/**
 * Custom markup around single product field - if in stock.
 */
function shoptimizer_product_custom_content() {
	if ( is_active_sidebar( 'single-product-field' ) ) :
		echo '<div class="product-widget">';
		dynamic_sidebar( 'single-product-field' );
		echo '</div>';
	endif;

}

add_action( 'woocommerce_after_single_product_summary', 'shoptimizer_related_content_wrapper_start', 10 );
add_action( 'woocommerce_after_single_product_summary', 'shoptimizer_related_content_wrapper_end', 60 );

/**
 * Single Product Page - Related products section wrapper start.
 */
function shoptimizer_related_content_wrapper_start() {
	echo '<section class="related-wrapper">';
}

/**
 * Single Product Page - Reorder Upsells position.
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 25 );

/**
 * Single Product Page - Related products section wrapper end.
 */
function shoptimizer_related_content_wrapper_end() {
	echo '</section>';
}

/**
 * Single Product Page - Reorder Rating position.
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );


/**
 * Single Product Page - Added to cart message.
 */

add_filter( 'wc_add_to_cart_message_html', 'shoptimizer_add_to_cart_message_filter', 10, 2 );

function shoptimizer_add_to_cart_message_filter( $message ) {

	$shoptimizer_message = sprintf(
		'<div class="message-inner"><div class="message-content">%s </div><div class="buttons-wrapper"><a href="%s" class="button checkout"><span>%s</span></a> <a href="%s" class="button cart"><span>%s</span></a></div></div>',
		shoptimizer_safe_html( $message ),
		esc_url( wc_get_page_permalink( 'checkout' ) ),
		esc_html__( 'Checkout', 'shoptimizer' ),
		esc_url( wc_get_page_permalink( 'cart' ) ),
		esc_html__( 'View Cart', 'shoptimizer' )
	);

	return $shoptimizer_message;
}


/**
 * Cart Page - Custom widget below the primary button.
 */
add_action( 'woocommerce_after_cart_totals', 'shoptimizer_cart_custom_field', 15 );

/**
 * Custom markup around cart field.
 */
function shoptimizer_cart_custom_field() {

	if ( is_active_sidebar( 'cart-field' ) ) :
		echo '<div class="cart-custom-field">';
		dynamic_sidebar( 'cart-field' );
		echo '</div>';
	endif;

}

/**
 * Cart Page - Custom widget below the summary.
 */
add_action( 'woocommerce_after_cart_table', 'shoptimizer_cart_custom_summary', 50 );

/**
 * Custom markup around cart field.
 */
function shoptimizer_cart_custom_summary() {

	if ( is_active_sidebar( 'cart-summary' ) ) :
		echo '<div class="cart-summary">';
		dynamic_sidebar( 'cart-summary' );
		echo '</div>';
	endif;

}

/**
 * Add Progress Bar to the Cart and Checkout pages.
 */
add_action( 'woocommerce_before_cart', 'shoptimizer_cart_progress' );
add_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );

if ( ! function_exists( 'shoptimizer_cart_progress' ) ) {

	/**
	 * More product info
	 * Link to product
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function shoptimizer_cart_progress() {

		$shoptimizer_layout_progress_bar_display = '';
		$shoptimizer_layout_progress_bar_display = shoptimizer_get_option( 'shoptimizer_layout_progress_bar_display' );

		if ( true === $shoptimizer_layout_progress_bar_display ) {
			?>

			<div class="checkout-wrap">
			<ul class="checkout-bar">
				<li class="active first">
				<a href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>"><?php esc_html_e( 'Shopping Cart', 'shoptimizer' ); ?></a>
				</li>
				<li class="next">
				<a href="<?php echo get_permalink( wc_get_page_id( 'checkout' ) ); ?>"><?php esc_html_e( 'Shipping and Checkout', 'shoptimizer' ); ?></a></li>
				<li>
				<?php esc_html_e( 'Confirmation', 'shoptimizer' ); ?></li>
				</li>
			</ul>
			</div>
			<?php

		}
		?>
		<?php

	}
}// End if().


add_action( 'woocommerce_review_order_after_submit', 'shoptimizer_checkout_custom_field', 15 );

/**
 * Checkout Page - Custom widget below the primary button.
 */
function shoptimizer_checkout_custom_field() {

	if ( is_active_sidebar( 'checkout-field' ) ) :
		echo '<div class="cart-custom-field">';
		dynamic_sidebar( 'checkout-field' );
		echo '</div>';
	endif;

}

/**
 * Mobile Extra. Appears below the mobile navigation area.
 */
function shoptimizer_mobile_extra_field() {

	if ( is_active_sidebar( 'mobile-extra' ) ) :
		echo '<div class="mobile-extra">';
		dynamic_sidebar( 'mobile-extra' );
		echo '</div>';
	endif;

}

/**
 * Custom coupon code start markup.
 */
function shoptimizer_coupon_wrapper_start() {
	echo '<section class="coupon-wrapper">';
}

/**
 * Custom coupon code end markup.
 */

function shoptimizer_coupon_wrapper_end() {
	echo '</section>';
}

/**
 * Single Product Page - Reorder sale message.
 */
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 3 );

add_filter( 'shoptimizer_product_thumbnail_columns', 'shoptimizer_gallery_columns' );

/**
 * Single Product Page - Change gallery thumbnails to one column.
 */
function shoptimizer_gallery_columns() {
	return 1;
}


add_filter( 'woocommerce_single_product_carousel_options', 'shoptimizer_flexslider_options' );

/**
 * Single Product Page - Include navigation arrows to the slider.
 */
function shoptimizer_flexslider_options( $options ) {
	$options['directionNav'] = true;
	return $options;
}

add_action( 'woocommerce_archive_description', 'shoptimizer_category_image', 20 );

/**
 * Display Category image on Category archive
 */
function shoptimizer_category_image() {
	if ( is_product_category() ) {
		global $wp_query;
		$cat          = $wp_query->get_queried_object();
		$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
		$image        = wp_get_attachment_url( $thumbnail_id );
		if ( $image ) {
			echo '<img src="' . $image . '" alt="' . $cat->name . '" />';
		}
	}
}

/**
 * Include product thumbnails in Checkout Summary table.
 */
add_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20, 3 );
function shoptimizer_product_thumbnail_in_checkout( $product_name, $cart_item, $cart_item_key ){
if ( is_checkout() )
    {
        $thumbnail   = $cart_item['data']->get_image(array( 40, 40));
        $image_html  = '<div class="product-item-thumbnail">'.$thumbnail.'</div> ';

        $product_name = $image_html . $product_name;
    }
    return $product_name;
}

/**
 * Cross Sells (Cart) Rearrange
 */
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );

add_filter( 'woocommerce_cross_sells_columns', 'shoptimizer_cross_sells_columns' );


/**
 * Minimal checkout template - remove several hooks.
 */
function shoptimizer_minimal_checkout() {

	$shoptimizer_layout_woocommerce_simple_checkout = '';
	$shoptimizer_layout_woocommerce_simple_checkout = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_simple_checkout' );

	$shoptimizer_header_layout = '';
	$shoptimizer_header_layout = shoptimizer_get_option( 'shoptimizer_header_layout' );

	if ( true === $shoptimizer_layout_woocommerce_simple_checkout ) {

		if ( class_exists( 'WooCommerce' ) ) {
			if ( is_checkout() ) {
				remove_action( 'shoptimizer_topbar', 'shoptimizer_top_bar', 10 );
				remove_action( 'shoptimizer_before_content', 'shoptimizer_sticky_header_display', 5 );
				remove_action( 'shoptimizer_before_site', 'shoptimizer_top_bar', 10 );
				remove_action( 'shoptimizer_header', 'shoptimizer_primary_navigation', 99 );
				remove_action( 'shoptimizer_header', 'shoptimizer_secondary_navigation', 30 );

				remove_action( 'shoptimizer_before_content', 'shoptimizer_header_widget_region', 10 );
				add_action( 'shoptimizer_header', 'shoptimizer_checkout_heading', 30 );

				remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper', 42 );
				remove_action( 'shoptimizer_navigation', 'shoptimizer_header_cart', 60 );
				remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper_close', 68 );

				function shoptimizer_checkout_heading() {
					the_title( '<h1>', '</h1>' );
				}
				
				remove_action( 'shoptimizer_header', 'shoptimizer_header_cart', 50 );
				remove_action( 'shoptimizer_header', 'shoptimizer_header_cart', 60 );
				remove_action( 'shoptimizer_header', 'shoptimizer_product_search', 25 );
				remove_action( 'shoptimizer_page_start', 'shoptimizer_page_header', 10 );
				remove_action( 'shoptimizer_before_footer', 'shoptimizer_below_content', 10 );
				remove_action( 'shoptimizer_footer', 'shoptimizer_footer_widgets', 20 );
				remove_action( 'shoptimizer_footer', 'shoptimizer_footer_copyright', 30 );

				if ( 'header-4' === $shoptimizer_header_layout ) {
					remove_action( 'shoptimizer_navigation', 'shoptimizer_search_modal', 50 );
				}
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_minimal_checkout' );

add_action( 'template_redirect', 'shoptimizer_remove_title', 10 );


if ( ! function_exists( 'shoptimizer_sticky_single_add_to_cart' ) ) {
	/**
	 * Sticky Add to Cart
	 *
	 * @since 1.0.0
	 */
	function shoptimizer_sticky_single_add_to_cart() {

		$shoptimizer_layout_woocommerce_sticky_cart_display = '';
		$shoptimizer_layout_woocommerce_sticky_cart_display = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sticky_cart_display' );
		global $woocommerce, $product;

		?>
			
			<?php if ( true === $shoptimizer_layout_woocommerce_sticky_cart_display ) { ?>

					<?php
					$shoptimizer_sticky_addtocart_js  = '';
					$shoptimizer_sticky_addtocart_js .= "
			( function ( $ ) {
				'use strict';
				 var initialTopOffset = $('.shoptimizer-archive').offset().top;
					$(window).scroll(function(event) {
					  var scroll = $(window).scrollTop();

					  if (scroll + initialTopOffset >= $('.site-main').offset().top && scroll + initialTopOffset <= $('.site-main').offset().top + $('.site-main').outerHeight()) {
					    $('.shoptimizer-sticky-add-to-cart').addClass('visible'); 
					  } else {
					    $('.shoptimizer-sticky-add-to-cart').removeClass('visible');
					  }
					});


				$(window).scroll(); 

			}( jQuery ) );
		";

					wp_add_inline_script( 'shoptimizer-main', $shoptimizer_sticky_addtocart_js );

					?>

				<?php if ( $product->is_in_stock() ) { ?>

			<section class="shoptimizer-sticky-add-to-cart">
				<div class="col-full">
					<div class="shoptimizer-sticky-add-to-cart__content">
						<?php echo wp_kses_post( woocommerce_get_product_thumbnail() ); ?>
						<div class="shoptimizer-sticky-add-to-cart__content-product-info">
							<span class="shoptimizer-sticky-add-to-cart__content-title"><?php the_title(); ?>
							<?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
							
							</span>	
						</div>

						<div class="shoptimizer-sticky-add-to-cart__content-button">
							<span class="shoptimizer-sticky-add-to-cart__content-price"><?php echo shoptimizer_safe_html( $product->get_price_html() ); ?></span>

						<?php if ( $product->is_type( 'variable' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' ) || $product->is_type( 'grouped' ) ) { ?>
							<a href="#sticky-scroll" class="variable-grouped-sticky button">
								<?php echo esc_attr__( 'Select options', 'shoptimizer' ); ?>
							</a>
							
						<?php } else { ?>									
							<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="button">							
								<?php echo esc_attr( $product->single_add_to_cart_text() ); ?>
							</a>

						<?php } ?>
						</div>
					</div>
				</div>
			</section>

					<?php
}
}// End if().
?>
		<?php
	}
}// End if().
