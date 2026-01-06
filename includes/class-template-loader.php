<?php
/**
 * Template Loader
 *
 * @package Sportspack
 */

namespace Sportspack;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles template loading for custom post types.
 */
class Template_Loader {

	/**
	 * Single instance of the class.
	 *
	 * @var Template_Loader
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Template_Loader
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Constructor intentionally left empty.
	}

	/**
	 * Load template for custom post types.
	 *
	 * @param string $template Template path.
	 * @return string Modified template path.
	 */
	public function template_include( $template ) {
		if ( is_singular( 'sportspack_unit' ) ) {
			return $this->get_template_path( 'single-sportspack_unit.php', $template );
		}

		if ( is_singular( 'sportspack_team' ) ) {
			return $this->get_template_path( 'single-sportspack_team.php', $template );
		}

		if ( is_singular( 'sportspack_person' ) ) {
			return $this->get_template_path( 'single-sportspack_person.php', $template );
		}

		if ( is_singular( 'sportspack_venue' ) ) {
			return $this->get_template_path( 'single-sportspack_venue.php', $template );
		}

		return $template;
	}

	/**
	 * Get template path, checking theme first then plugin.
	 *
	 * @param string $template_name Template file name.
	 * @param string $default       Default template to use if not found.
	 * @return string Template path.
	 */
	private function get_template_path( $template_name, $default ) {
		// Check if template exists in theme.
		$theme_template = locate_template( [ 'sportspack/' . $template_name, $template_name ] );

		if ( $theme_template ) {
			return $theme_template;
		}

		// Use plugin template.
		$plugin_template = SPORTSPACK_PLUGIN_DIR . 'templates/' . $template_name;

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		// Return default if nothing found.
		return $default;
	}

	/**
	 * Get breadcrumb trail for hierarchical posts.
	 *
	 * @param int $post_id Post ID.
	 * @return array Breadcrumb items.
	 */
	public function get_breadcrumbs( $post_id ) {
		$breadcrumbs = [];
		$post        = get_post( $post_id );

		if ( ! $post ) {
			return $breadcrumbs;
		}

		// Build breadcrumb trail.
		$current_post = $post;
		
		while ( $current_post ) {
			array_unshift(
				$breadcrumbs,
				[
					'id'    => $current_post->ID,
					'title' => get_the_title( $current_post->ID ),
					'url'   => get_permalink( $current_post->ID ),
				]
			);

			if ( $current_post->post_parent ) {
				$current_post = get_post( $current_post->post_parent );
			} else {
				break;
			}
		}

		return $breadcrumbs;
	}

	/**
	 * Render breadcrumbs HTML.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function render_breadcrumbs( $post_id ) {
		$breadcrumbs = $this->get_breadcrumbs( $post_id );

		if ( empty( $breadcrumbs ) ) {
			return;
		}

		echo '<nav class="sportspack-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'sportspack' ) . '">';
		echo '<ol>';

		foreach ( $breadcrumbs as $index => $crumb ) {
			$is_last = ( $index === count( $breadcrumbs ) - 1 );

			echo '<li>';
			
			if ( ! $is_last ) {
				echo '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['title'] ) . '</a>';
				echo ' <span class="separator">›</span> ';
			} else {
				echo '<span aria-current="page">' . esc_html( $crumb['title'] ) . '</span>';
			}
			
			echo '</li>';
		}

		echo '</ol>';
		echo '</nav>';
	}
}
