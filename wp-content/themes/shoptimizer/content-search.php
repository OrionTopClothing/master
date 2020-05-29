<?php
/**
 * Template used to display standard search results.
 *
 * @package shoptimizer
 */

?>

<?php
while ( have_posts() ) :
	the_post();
	?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>   
	<?php the_excerpt(); ?>

</article><!-- #post-## -->

<?php endwhile; ?>
