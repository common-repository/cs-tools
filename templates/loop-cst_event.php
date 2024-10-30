<?php
/**
 * The loop that displays a single post.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop-single.php.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.2
 */
 
#  ini_set('display_errors',1);
# error_reporting(E_ALL);
?>


<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php 
global $wp_query;
if ( $wp_query->max_num_pages > 1 ) : ?>
	<div id="nav-above" class="navigation">
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
	</div><!-- #nav-above -->
<?php endif; ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( 'Not Found', 'twentyten' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyten' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
				
		<?php 
		
			$title = get_the_title();
			echo '<div class="event-header">';


	
		cst_display_date();
		
		$link = get_permalink(); $event_time = cst_event_time();
		echo "<div class=\"event-title\"><h2><span class='event-time'> $event_time </span><a href=\"$link\" rel=\"bookmark\">$title</a> </h2></div>";
	        echo the_terms( get_the_ID(), 'cst_event-cats', 'Posted under:', ', ', ' ' );
	
		?>
	</div>

				<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
				
						


	</div><!-- #post-## -->

			

<?php endwhile; // end of the loop. ?>