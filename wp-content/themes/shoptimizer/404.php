<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package shoptimizer
 */

get_header(); ?>

	<div id="primary" class="content-area">

		<main id="main" class="site-main">

			<div class="error-404 not-found">

				<div class="page-content">

					<header class="page-header">
						<h1 class="page-title"><?php esc_html_e( 'That page can&rsquo;t be found.', 'shoptimizer' ); ?></h1>
					</header><!-- .page-header -->

					<p><?php esc_html_e( 'Nothing was found at this location. Try searching, or check out the popular items below.', 'shoptimizer' ); ?></p>

					<?php

					if ( shoptimizer_is_woocommerce_activated() ) {

						echo '<section aria-label="' . esc_html__( 'Popular Products', 'shoptimizer' ) . '">';

							echo '<h2>' . esc_html__( 'Popular Products', 'shoptimizer' ) . '</h2>';

							echo shoptimizer_do_shortcode( 'best_selling_products', array(
								'per_page' => 8,
								'columns'  => 4,
							) );

						echo '</section>';

					}
					?>

				</div><!-- .page-content -->
			</div><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer();
