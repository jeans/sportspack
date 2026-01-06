<?php
/**
 * Template for single sportspack_person posts
 *
 * @package Sportspack
 */

get_header();

while ( have_posts() ) :
	the_post();

	$logo = get_post_meta( get_the_ID(), '_sportspack_logo', true );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<?php if ( ! empty( $logo ) ) : ?>
				<div class="sportspack-logo">
					<?php
					// Check if logo is an attachment ID or URL.
					if ( is_numeric( $logo ) ) {
						echo wp_get_attachment_image( $logo, 'medium', false, [ 'alt' => get_the_title() ] );
					} else {
						echo '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_title() ) . '" />';
					}
					?>
				</div>
			<?php endif; ?>

			<div class="sportspack-header-content">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</div>
		</header>

		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				[
					'before' => '<div class="page-links">' . __( 'Pages:', 'sportspack' ),
					'after'  => '</div>',
				]
			);
			?>
		</div>
	</article>

	<?php
endwhile;

get_footer();
