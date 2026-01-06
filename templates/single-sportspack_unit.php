<?php
/**
 * Template for single sportspack_unit posts
 *
 * @package Sportspack
 */

use Sportspack\Inheritance;
use Sportspack\Template_Loader;

get_header();

while ( have_posts() ) :
	the_post();

	$inheritance = Inheritance::get_instance();
	$loader      = Template_Loader::get_instance();
	$level       = $inheritance->get_hierarchy_level( get_the_ID() );
	$label       = $inheritance->get_hierarchy_label( get_the_ID() );
	$logo        = $inheritance->get_inherited_logo( get_the_ID() );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( $level > 0 ) : ?>
			<div class="sportspack-breadcrumbs-wrapper">
				<?php $loader->render_breadcrumbs( get_the_ID() ); ?>
			</div>
		<?php endif; ?>

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
				<?php if ( $label ) : ?>
					<span class="sportspack-level-label"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
				
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

		<?php
		// Show child posts (e.g., competitions under a sport, events under a competition).
		$children = get_children(
			[
				'post_parent' => get_the_ID(),
				'post_type'   => 'sportspack_unit',
				'post_status' => 'publish',
				'orderby'     => 'title',
				'order'       => 'ASC',
			]
		);

		if ( ! empty( $children ) ) :
			?>
			<div class="sportspack-children">
				<?php
				$child_level = $level + 1;
				$child_label = '';
				
				switch ( $child_level ) {
					case 1:
						$child_label = __( 'Competitions', 'sportspack' );
						break;
					case 2:
						$child_label = __( 'Events', 'sportspack' );
						break;
				}
				?>
				
				<?php if ( $child_label ) : ?>
					<h2><?php echo esc_html( $child_label ); ?></h2>
				<?php endif; ?>

				<ul class="sportspack-children-list">
					<?php foreach ( $children as $child ) : ?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $child->ID ) ); ?>">
								<?php echo esc_html( get_the_title( $child->ID ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</article>

	<?php
endwhile;

get_footer();
