<?php

/**
 * Shoptimizer functions.
 *
 * @package shoptimizer
 */

/**
 * Assign the Shoptimizer version to a var
 */
$theme               = wp_get_theme( 'shoptimizer' );
$shoptimizer_version = $theme['Version'];

/**
 * Global Paths
 */
define( 'SHOPTIMIZER_CORE', get_template_directory() . '/inc/core' );

/**
 * Enqueue scripts and styles.
 */
function shoptimizer_scripts() {

	global $shoptimizer_version;

	$shoptimizer_masonry_layout = '';
	$shoptimizer_masonry_layout = shoptimizer_get_option( 'shoptimizer_masonry_layout' );

	if ( true === $shoptimizer_masonry_layout ) {
		wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
	}

	wp_enqueue_script( 'shoptimizer-main', get_template_directory_uri() . '/assets/js/main.min.js', array( 'jquery' ), '20161208', true );



	wp_enqueue_style( 'shoptimizer-style', get_stylesheet_uri() );

	$shoptimizer_general_speed_minify_main_css = '';
	$shoptimizer_general_speed_minify_main_css = shoptimizer_get_option( 'shoptimizer_general_speed_minify_main_css' );

	$shoptimizer_layout_floating_button_display = '';
	$shoptimizer_layout_floating_button_display = shoptimizer_get_option( 'shoptimizer_layout_floating_button_display' );

	$shoptimizer_header_layout = '';
	$shoptimizer_header_layout = shoptimizer_get_option( 'shoptimizer_header_layout' );

	$shoptimizer_layout_search_display = '';
	$shoptimizer_layout_search_display = shoptimizer_get_option( 'shoptimizer_layout_search_display' );


	if ( isset( $_GET['header'] ) ) {
		$shoptimizer_header_layout = $_GET['header'];
	}

	if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
		wp_enqueue_style( 'shoptimizer-main-min', get_template_directory_uri() . '/assets/css/main/main.min.css', '', $shoptimizer_version );
	} else {
		wp_enqueue_style( 'shoptimizer-main', get_template_directory_uri() . '/assets/css/main/main.css', '', $shoptimizer_version );
	}

	if( is_singular( 'post' ) || is_archive() || is_author() || is_category() || is_home() ){
		if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
		wp_enqueue_style( 'shoptimizer-blog-min', get_template_directory_uri() . '/assets/css/main/blog.min.css', '', $shoptimizer_version );
		} else {
			wp_enqueue_style( 'shoptimizer-blog', get_template_directory_uri() . '/assets/css/main/blog.css', '', $shoptimizer_version );
		}
	}

	if( shoptimizer_is_woocommerce_activated() ){
		if( is_account_page() ){
			if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
				wp_enqueue_style( 'shoptimizer-account-min', get_template_directory_uri() . '/assets/css/main/my-account.min.css', '', $shoptimizer_version );
				} else {
				wp_enqueue_style( 'shoptimizer-account', get_template_directory_uri() . '/assets/css/main/my-account.css', '', $shoptimizer_version );
				}
		}
	}

	if( shoptimizer_is_woocommerce_activated() ){
		if( is_cart() || is_checkout() ){
			if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
			wp_enqueue_style( 'shoptimizer-cart-checkout-min', get_template_directory_uri() . '/assets/css/main/cart-checkout.min.css', '', $shoptimizer_version );
			} else {
				wp_enqueue_style( 'shoptimizer-cart-checkout', get_template_directory_uri() . '/assets/css/main/cart-checkout.css', '', $shoptimizer_version );
			}
		}
	}

	if (( true === $shoptimizer_layout_floating_button_display ) || ( 'header-4' === $shoptimizer_header_layout )) {
		if( shoptimizer_is_woocommerce_activated() ){
			if( is_product() || 'header-4' === $shoptimizer_header_layout ){
				if ( 'enable' === $shoptimizer_layout_search_display ) {

					if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
						wp_enqueue_style( 'shoptimizer-modal-min', get_template_directory_uri() . '/assets/css/main/modal.min.css', '', $shoptimizer_version );
					} else {
						wp_enqueue_style( 'shoptimizer-modal', get_template_directory_uri() . '/assets/css/main/modal.css', '', $shoptimizer_version );
					}
				}
			}
		}
	}

	if( shoptimizer_is_woocommerce_activated() ){
		if( is_product() ){
			if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
				wp_enqueue_style( 'shoptimizer-product-min', get_template_directory_uri() . '/assets/css/main/product.min.css', '', $shoptimizer_version );
			} else {
				wp_enqueue_style( 'shoptimizer-product', get_template_directory_uri() . '/assets/css/main/product.css', '', $shoptimizer_version );
			}
		}
	}
	
	if( is_singular() || is_page() ){
		if ( comments_open() ) {
			if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
				wp_enqueue_style( 'shoptimizer-comments-min', get_template_directory_uri() . '/assets/css/main/comments.min.css', '', $shoptimizer_version );
			} else {
				wp_enqueue_style( 'shoptimizer-comments', get_template_directory_uri() . '/assets/css/main/comments.css', '', $shoptimizer_version );
			}
		}
	}

}

add_action( 'wp_enqueue_scripts', 'shoptimizer_scripts' );

function shoptimizer_single_product_shortcode_styles() {
    global $post;
    global $shoptimizer_version;
    if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'product_page') ) {
        wp_enqueue_style( 'shoptimizer-product', get_template_directory_uri() . '/assets/css/main/product.css', '', $shoptimizer_version );
		wp_enqueue_style( 'shoptimizer-modal', get_template_directory_uri() . '/assets/css/main/modal.css', '', $shoptimizer_version );
    }
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_single_product_shortcode_styles');


/**
 * Enqueue theme styles within Gutenberg.
 */
function shoptimizer_gutenberg_styles() {

	// Load the theme styles within Gutenberg.
	wp_enqueue_style( 'shoptimizer-gutenberg', get_template_directory_uri() . '/assets/css/editor/gutenberg.css' );

}
add_action( 'enqueue_block_editor_assets', 'shoptimizer_gutenberg_styles' );


// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require get_template_directory() . '/inc/compatibility/class-shoptimizer-elementor-pro.php';
}

/**
 * Show cart widget on all pages.
 */
add_filter( 'woocommerce_widget_cart_is_hidden', 'shoptimizer_always_show_cart', 40, 0 );

/**
 * Function to always show cart.
 */
function shoptimizer_always_show_cart() {
	return false;
}

/**
 * Allow shortcodes within the menu.
 */
add_filter( 'wp_nav_menu', 'shoptimizer_menu_enable_shortcode', 20, 2 );


/**
 * Returns a shortcode for the menu.
 */
function shoptimizer_menu_enable_shortcode( $menu, $args ) {
	return do_shortcode( $menu );
}

/**
 * Primary Menu Custom Walker - add a wrapper div.
 */
class submenuwrap extends Walker_Nav_Menu {

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n$indent<div class='sub-menu-wrapper'><div class='container'><ul class='sub-menu'>\n";
	}
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul></div></div>\n";
	}
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

		$shoptimizer_menu_display_description = '';
		$shoptimizer_menu_display_description = shoptimizer_get_option( 'shoptimizer_menu_display_description' );

		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' );

		// Passed Classes
		$classes     = empty( $item->classes ) ? array() : (array) $item->classes;
		$class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		// build html
		$output .= $indent . '<li id="nav-menu-item-' . $item->ID . '" class="' . $class_names . '">';

		// If 'menu-item-product' exists in classes, don't add the HTML anchor tag.
		if ( in_array( 'menu-item-product', $classes ) ) {
			$item_output = apply_filters( 'the_title', $item->title, $item->ID );

		}
		else {

			// link attributes.
			$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
			$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
			$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';
			$attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

			$description = ( ! empty ( $item->description ) and 1 == $depth )
            ? '<span class="sub">'.  $item->description . '</span>' : '';

            // Display menu descriptions
            if ( true === $shoptimizer_menu_display_description ) { 

			$item_output = sprintf(
				'%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters( 'the_title', $item->title, $item->ID ),
				$description,
				$args->link_after,
				$args->after
			);

			}

			// Do not display menu descriptions
			elseif ( false === $shoptimizer_menu_display_description ) {

				$item_output = sprintf(
				'%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				apply_filters( 'the_title', $item->title, $item->ID ),
				$args->link_after,
				$args->after
			);

			}
		}

		// build html.
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

}

/**
 * Adds a plus icon for the mobile menu.
 */
function shoptimizer_mobile_menu_plus( $output, $item, $depth, $args ) {

	if ( 'primary' == $args->theme_location ) {
		if ( in_array( 'menu-item-has-children', $item->classes ) ) {
			$output .= '<span class="caret"></span>';
		}
	}
	return $output;
}

add_filter( 'walker_nav_menu_start_el', 'shoptimizer_mobile_menu_plus', 10, 4 );


add_filter( 'woocommerce_show_page_title', '__return_false' );
add_action( 'woocommerce_before_main_content', 'shoptimizer_archives_title', 20 );


/**
 * Page template without title and breadcrumbs
 */
function shoptimizer_remove_title() {
	if ( is_page_template( 'template-fullwidth-no-heading.php' ) ) {
		add_action( 'shoptimizer_before_content', 'shoptimizer_sticky_header_display', 5 );
		remove_action( 'shoptimizer_content_top', 'woocommerce_breadcrumb', 10 );
	}
}

add_action( 'shoptimizer_loop_post', 'shoptimizer_loop_wrapper_start', 8 );
add_action( 'shoptimizer_loop_post', 'shoptimizer_loop_wrapper_end', 35 );

/**
 * Blog loop. Add wrapper class start.
 */
function shoptimizer_loop_wrapper_start() {
	echo '<div class="blog-loop-content-wrapper">';
}

/**
 * Blog loop. Add wrapper class end.
 */
function shoptimizer_loop_wrapper_end() {
	echo '</div>';
}

/**
 * Enable shortcodes within category description area.
 */
add_filter( 'term_description', 'do_shortcode' );


/**
 * Adds a body class if the minimal checkout has been selected.
 */
function shoptimizer_minimal_checkout_body_class( $classes ) {

	$shoptimizer_layout_woocommerce_simple_checkout = '';
	$shoptimizer_layout_woocommerce_simple_checkout = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_simple_checkout' );

	if ( true === $shoptimizer_layout_woocommerce_simple_checkout ) {
		$classes[] = 'minimal-checkout';
	}
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_minimal_checkout_body_class' );


if ( class_exists( 'WooCommerce' ) ) {
	/**
	 * Adds a body class to just the Shop landing page.
	 */
	function shoptimizer_shop_body_class( $classes ) {
		if ( is_shop() ) {
			$classes[] = 'shoptimzer-shop-landing';
		}
		return $classes;
	}

	add_filter( 'body_class', 'shoptimizer_shop_body_class' );
}

/**
 * Adds a body class if the breadcrumbs have been disabled.
 */
function shoptimizer_breadcrumbs_body_class( $classes ) {

	$shoptimizer_layout_woocommerce_display_breadcrumbs = '';
	$shoptimizer_layout_woocommerce_display_breadcrumbs = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_breadcrumbs' );

	if ( 'disable' === $shoptimizer_layout_woocommerce_display_breadcrumbs ) {
		$classes[] = 'no-breadcrumbs';
	}
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_breadcrumbs_body_class' );

/**
 * Sets body classes depending on which sidebars have been selected.
 */
function shoptimizer_sidebar_body_class( $classes ) {

	$shoptimizer_layout_woocommerce_sidebar = '';
	$shoptimizer_layout_woocommerce_sidebar = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );

	$shoptimizer_layout_archives_sidebar = '';
	$shoptimizer_layout_archives_sidebar = shoptimizer_get_option( 'shoptimizer_layout_archives_sidebar' );

	$shoptimizer_layout_post_sidebar = '';
	$shoptimizer_layout_post_sidebar = shoptimizer_get_option( 'shoptimizer_layout_post_sidebar' );

	$shoptimizer_layout_page_sidebar = '';
	$shoptimizer_layout_page_sidebar = shoptimizer_get_option( 'shoptimizer_layout_page_sidebar' );

	$classes[] = $shoptimizer_layout_woocommerce_sidebar . ' ' . $shoptimizer_layout_archives_sidebar . ' ' . $shoptimizer_layout_page_sidebar . ' ' . $shoptimizer_layout_post_sidebar;
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_sidebar_body_class' );


/**
 * Excludes some classes from Jetpack's lazy load.
 */
function shoptimizer_lazy_exclude( $blacklisted_classes ) {
	$blacklisted_classes = array(
		'skip-lazy',
		'wp-post-image',
		'post-image',
		'custom-logo',
	);
	return $blacklisted_classes;

}
add_filter( 'jetpack_lazy_images_blacklisted_classes', 'shoptimizer_lazy_exclude' );


/**
 * Sets body classes depending on which product alignment has been selected.
 */
function shoptimizer_woocommerce_product_alignment_class( $classes ) {

	$shoptimizer_layout_woocommerce_text_alignment = '';
	$shoptimizer_layout_woocommerce_text_alignment = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_text_alignment' );

	$classes[] = $shoptimizer_layout_woocommerce_text_alignment;
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_woocommerce_product_alignment_class' );

/**
 * Disable Jetpack's Related Posts on Products.
 */
function shoptimizer_exclude_jetpack_related_from_products( $options ) {
	if ( is_product() ) {
		$options['enabled'] = false;
	}

	return $options;
}

add_filter( 'jetpack_relatedposts_filter_options', 'shoptimizer_exclude_jetpack_related_from_products' );

/**
 * TGM Plugin Activation.
 */
require_once SHOPTIMIZER_CORE . '/functions/class-tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'shoptimizer_register_required_plugins' );

/**
 * Recommended plugins
 *
 * @package Shoptimizer
 */
function shoptimizer_register_required_plugins() {
	$plugins = array(
		array(
			'name'     => esc_html__( 'Elementor', 'shoptimizer' ),
			'slug'     => 'elementor',
			'required' => false,
		),
		array(
			'name'     => esc_html__( 'Kirki', 'shoptimizer' ),
			'slug'     => 'kirki',
			'required' => true,
		),
		array(
			'name'               => 'CommerceGurus CommerceKit',
			'slug'               => 'commercegurus-commercekit',
			'source'             => 'https://files.commercegurus.com/commercegurus-commercekit.zip',
			'required'           => true,
			'version'            => '1.0.2',
		),
		array(
			'name'     => esc_html__( 'One Click Demo Import', 'shoptimizer' ),
			'slug'     => 'one-click-demo-import',
			'required' => false,
		),
		array(
			'name'     => esc_html__( 'Smart WooCommerce Search', 'shoptimizer' ),
			'slug'     => 'smart-woocommerce-search',
			'required' => false,
		),
		array(
			'name'     => esc_html__( 'WooCommerce', 'shoptimizer' ),
			'slug'     => 'woocommerce',
			'required' => false,
		),
	);

	/**
	 * Array of configuration settings.
	 */
	$config = array(
		'domain'       => 'shoptimizer',
		'default_path' => '',
		'parent_slug'  => 'themes.php',
		'menu'         => 'tgmpa-install-plugins',
		'has_notices'  => true,
		'is_automatic' => false,
		'message'      => '',
		'strings'      => array(
			'page_title'                      => esc_html__( 'Install Required Plugins', 'shoptimizer' ),
			'menu_title'                      => esc_html__( 'Install Plugins', 'shoptimizer' ),
			'installing'                      => esc_html__( 'Installing Plugin: %s', 'shoptimizer' ),
			'oops'                            => esc_html__( 'Something went wrong with the plugin API.', 'shoptimizer' ),
			'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'shoptimizer' ),
			'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'shoptimizer' ),
			'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'shoptimizer' ),
			'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'shoptimizer' ),
			'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'shoptimizer' ),
			'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'shoptimizer' ),
			'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'shoptimizer' ),
			'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'shoptimizer' ),
			'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'shoptimizer' ),
			'activate_link'                   => _n_noop( 'Activate installed plugin', 'Activate installed plugins', 'shoptimizer' ),
			'return'                          => esc_html__( 'Return to Required Plugins Installer', 'shoptimizer' ),
			'plugin_activated'                => esc_html__( 'Plugin activated successfully.', 'shoptimizer' ),
			'complete'                        => esc_html__( 'All plugins installed and activated successfully. %s', 'shoptimizer' ),
			'nag_type'                        => 'updated',
		),
	);
	tgmpa( $plugins, $config );
}


/**
 * One Click Importer Demo Data.
 */
function shoptimizer_import_files() {
	return array(
		array(
			'import_file_name'         => esc_html__( 'Shoptimizer Demo Data', 'shoptimizer' ),
			'import_file_url'          => esc_url( 'http://files.commercegurus.com/shoptimizer-demodata/shoptimizer-demodata.xml', 'shoptimizer' ),
			'import_widget_file_url'   => esc_url( 'http://files.commercegurus.com/shoptimizer-demodata/shoptimizer-widgets.wie', 'shoptimizer' ),
			'import_preview_image_url' => esc_url( 'https://themedemo.commercegurus.com/shoptimizer/wp-content/themes/shoptimizer/screenshot.png', 'shoptimizer' ),
		),
	);
}

add_filter( 'pt-ocdi/import_files', 'shoptimizer_import_files' );

/**
 * Post demo stuff.
 */
function shoptimizer_after_demo_import_setup() {

	// Menus to import and assign.
	$main_menu      = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
	$secondary_menu = get_term_by( 'name', 'Secondary Menu', 'nav_menu' );
	set_theme_mod(
		'nav_menu_locations', array(
			'primary'   => $main_menu->term_id,
			'secondary' => $secondary_menu->term_id,
		)
	);

	// Set options for front page and blog page.
	$front_page_id = get_page_by_title( 'Home' );
	$blog_page_id  = get_page_by_title( 'Blog' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id->ID );
	update_option( 'page_for_posts', $blog_page_id->ID );

	esc_html_e( 'Shoptimizer demo content imported!', 'shoptimizer' );
}

add_action( 'pt-ocdi/after_import', 'shoptimizer_after_demo_import_setup' );

/**
 * Timeout call.
 */
function shoptimizer_change_time_of_single_ajax_call() {
	return 10;
}

add_action( 'pt-ocdi/time_for_one_ajax_call', 'shoptimizer_change_time_of_single_ajax_call' );

// Disable generation of smaller images during demo data import.
add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false' );

// Remove plugin branding.
add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );

/**
 * Load the Kirki Fallback class.
 */
require get_template_directory() . '/inc/kirki-fallback.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1170;
}

$shoptimizer = (object) array(
	'version' => $shoptimizer_version,

	/**
	 * Initialize all the things.
	 */
	'main'    => require 'inc/class-shoptimizer.php',
);

require 'inc/shoptimizer-functions.php';
require 'inc/shoptimizer-template-hooks.php';
require 'inc/shoptimizer-template-functions.php';

if ( shoptimizer_is_woocommerce_activated() ) {
	$shoptimizer->woocommerce = require 'inc/woocommerce/class-shoptimizer-woocommerce.php';

	require 'inc/woocommerce/shoptimizer-woocommerce-template-hooks.php';
	require 'inc/woocommerce/shoptimizer-woocommerce-template-functions.php';
}


/**
 * Theme Help page.
 */
require_once get_template_directory() . '/inc/setup/help.php';


/**
 * Inject Critical CSS to wp_head.
 */
function ccfw_criticalcss() {
	echo '<style>';
	if ( is_front_page() || is_home() ) {
		get_template_part( 'assets/css/criticalcss/home' );
	} elseif ( is_single() ) {
		get_template_part( 'assets/css/criticalcss/single-post' );
	} elseif ( is_page() ) {
		get_template_part( 'assets/css/criticalcss/single-post' );
	} elseif ( is_archive() ) {
		get_template_part( 'assets/css/criticalcss/blog-archive' );
	} elseif ( is_shop() || is_product_category() ) {
		get_template_part( 'assets/css/criticalcss/blog-archive' );
	} elseif ( is_product() ) {
		get_template_part( 'assets/css/criticalcss/single-product' );
	} else {
		get_template_part( 'assets/css/criticalcss/single-post' );
	}
	echo '</style>';
}


function ccfw_get_css_handle() {

	// Safe Default.
	$css_handle = 'shoptimizer-main';

	$shoptimizer_general_speed_minify_main_css = '';
	$shoptimizer_general_speed_minify_main_css = shoptimizer_get_option( 'shoptimizer_general_speed_minify_main_css' );

	if ( 'yes' === $shoptimizer_general_speed_minify_main_css ) {
		$css_handle = 'shoptimizer-main-min';
	} else {
		$css_handle = 'shoptimizer-main';
	}

	return $css_handle;
}


/**
 * Replaces a stylesheet link tag with a preload tag.
 *
 * @param string $tag     The link tag as generated by WordPress.
 * @param string $handle  The handle by which the stylesheet is known to WordPress.
 * @param string $href    The URL to the stylesheet, including version number.
 * @param string $media   The media attribute of the stylesheet.
 * @return string The original tag wrapped in a noscript element, followed by the preload tag.
 */
function ccfw_filter_style_loader_tag( $tag, $handle, $href, $media ) {
	global $wp_styles;

	$shoptimizer_css_handle = ccfw_get_css_handle();

	if ( $shoptimizer_css_handle === $handle ) {

		$rel          = 'stylesheet';
		$noscript_tag = $tag;
		$tag          = sprintf(
			'<link rel="preload" as="style" onload="%s" id="%s-css" href="%s" type="text/css" media="%s" />',
			"this.onload=null;this.rel='" . esc_js( $rel ) . "'",
			esc_attr( $handle . '-preload' ),
			esc_url_raw( $href ),
			esc_attr( $media )
		);
		$tag         .= sprintf( '<noscript>%s</noscript>', $noscript_tag );

		// $rel    = 'stylesheet';
		// $footag = $tag;
		// $tag    = sprintf( '<noscript>%s</noscript>', $footag );
		// $tag   .= sprintf(
		// '<link rel="preload" as="style" onload="%s" id="%s-css" href="%s" type="text/css" media="%s" />',
		// "this.onload=null;this.rel='" . esc_js( $rel ) . "'",
		// esc_attr( $handle . ':preload' ),
		// esc_url_raw( $href ),
		// esc_attr( $media )
		// );
		// $tag .= '<script>!function(e){"use strict";var n=function(n,t,o){function i(e){if(a.body)return e();setTimeout(function(){i(e)})}function r(){l.addEventListener&&l.removeEventListener("load",r),l.media=o||"all"}var d,a=e.document,l=a.createElement("link");if(t)d=t;else{var f=(a.body||a.getElementsByTagName("head")[0]).childNodes;d=f[f.length-1]}var s=a.styleSheets;l.rel="stylesheet",l.href=n,l.media="only x",i(function(){d.parentNode.insertBefore(l,t?d:d.nextSibling)});var u=function(e){for(var n=l.href,t=s.length;t--;)if(s[t].href===n)return e();setTimeout(function(){u(e)})};return l.addEventListener&&l.addEventListener("load",r),l.onloadcssdefined=u,u(r),l};"undefined"!=typeof exports?exports.loadCSS=n:e.loadCSS=n}("undefined"!=typeof global?global:this);</script>';
		$tag .= '<script>!function(n){"use strict";n.loadCSS||(n.loadCSS=function(){});var o=loadCSS.relpreload={};if(o.support=function(){var e;try{e=n.document.createElement("link").relList.supports("preload")}catch(t){e=!1}return function(){return e}}(),o.bindMediaToggle=function(t){var e=t.media||"all";function a(){t.media=e}t.addEventListener?t.addEventListener("load",a):t.attachEvent&&t.attachEvent("onload",a),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(a,3e3)},o.poly=function(){if(!o.support())for(var t=n.document.getElementsByTagName("link"),e=0;e<t.length;e++){var a=t[e];"preload"!==a.rel||"style"!==a.getAttribute("as")||a.getAttribute("data-loadcss")||(a.setAttribute("data-loadcss",!0),o.bindMediaToggle(a))}},!o.support()){o.poly();var t=n.setInterval(o.poly,500);n.addEventListener?n.addEventListener("load",function(){o.poly(),n.clearInterval(t)}):n.attachEvent&&n.attachEvent("onload",function(){o.poly(),n.clearInterval(t)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:n.loadCSS=loadCSS}("undefined"!=typeof global?global:this);</script>';
	}

	return $tag;
}



/**
 * WPForms integration check.
 */
if ( ! defined( 'WPFORMS_SHAREASALE_ID' ) ) {
	define( 'WPFORMS_SHAREASALE_ID', '1478967' );
}

// Bring back dequeue fragment as a theme option in future.
// /**
// * Get rid of cart fragments on our homepage.
// */
// add_action( 'wp_enqueue_scripts', 'ccfw_dequeue_woocommerce_cart_fragments', 11 );
// /**
// * Get rid of cart fragments on our homepage.
// */
// function ccfw_dequeue_woocommerce_cart_fragments() {
// if ( is_front_page() || ( is_page_template( 'template-fullwidth-no-heading.php' ) ) ) {
// wp_dequeue_script( 'wc-cart-fragments' );
// }
// }
$shoptimizer_general_speed_critical_css = '';
$shoptimizer_general_speed_critical_css = shoptimizer_get_option( 'shoptimizer_general_speed_critical_css' );
if ( 'yes' === $shoptimizer_general_speed_critical_css ) {
	add_action( 'wp_head', 'ccfw_criticalcss', 5 );
	add_filter( 'style_loader_tag', 'ccfw_filter_style_loader_tag', 10, 4 );
}


// function shoptimizer_elementor_lazy_load_style( $html, $handle, $href, $media ) {
// $handlematch = false;
// if ( strpos( $handle, 'elementor-post' ) !== false ) {
// $handlematch = true;
// }
// if ( $handlematch ) {
// $inline_css = file_get_contents( $href );
// $lazy_inline_css = preg_replace('/{background-image/', '.visible{background-image', $inline_css);
// $html = sprintf(
// '<style id="shoptimizer-elementor-lazy-styles" type="text/css" media="%s">%s</style>',
// esc_attr( $media ),
// $lazy_inline_css
// );
// }
// return $html;
// }
// add_filter( 'style_loader_tag', 'shoptimizer_elementor_lazy_load_style', 10, 4 );
