<?php
/**
 * Inheritance Resolver with Caching
 *
 * @package Sportspack
 */

namespace Sportspack;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages field inheritance from parent posts with caching.
 */
class Inheritance {

	/**
	 * Single instance of the class.
	 *
	 * @var Inheritance
	 */
	private static $instance = null;

	/**
	 * Cache group name.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'sportspack_inheritance';

	/**
	 * Cache expiration in seconds (1 hour).
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = 3600;

	/**
	 * Get singleton instance.
	 *
	 * @return Inheritance
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
	 * Get inherited logo value.
	 *
	 * @param int $post_id Post ID.
	 * @return string Logo value (attachment ID or URL).
	 */
	public function get_inherited_logo( $post_id ) {
		return $this->get_inherited_value( $post_id, 'logo' );
	}

	/**
	 * Get inherited remote provider value.
	 *
	 * @param int $post_id Post ID.
	 * @return string Remote provider value.
	 */
	public function get_inherited_provider( $post_id ) {
		return $this->get_inherited_value( $post_id, 'remote_provider' );
	}

	/**
	 * Get inherited remote ID value.
	 *
	 * @param int $post_id Post ID.
	 * @return string Remote ID value.
	 */
	public function get_inherited_remote_id( $post_id ) {
		return $this->get_inherited_value( $post_id, 'remote_id' );
	}

	/**
	 * Get inherited value for a specific field.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $field   Field name (logo, remote_provider, remote_id).
	 * @return string Inherited value.
	 */
	public function get_inherited_value( $post_id, $field ) {
		// Try to get from cache first.
		$cache_key = $this->get_cache_key( $post_id, $field );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false !== $cached ) {
			return $cached;
		}

		// Resolve inheritance.
		$value = $this->resolve_inheritance( $post_id, $field );

		// Cache the result.
		wp_cache_set( $cache_key, $value, self::CACHE_GROUP, self::CACHE_EXPIRATION );

		return $value;
	}

	/**
	 * Resolve inheritance by traversing up the hierarchy.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $field   Field name.
	 * @return string Resolved value.
	 */
	private function resolve_inheritance( $post_id, $field ) {
		$meta_key = '_sportspack_' . $field;

		// Check current post first.
		$value = get_post_meta( $post_id, $meta_key, true );
		
		if ( ! empty( $value ) ) {
			return $value;
		}

		// Get post object.
		$post = get_post( $post_id );

		if ( ! $post || 'sportspack_unit' !== $post->post_type ) {
			return '';
		}

		// Traverse up the hierarchy.
		if ( $post->post_parent ) {
			return $this->resolve_inheritance( $post->post_parent, $field );
		}

		// No value found in hierarchy.
		return '';
	}

	/**
	 * Get cache key for a post and field.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $field   Field name.
	 * @return string Cache key.
	 */
	private function get_cache_key( $post_id, $field ) {
		return sprintf( 'inheritance_%d_%s', $post_id, $field );
	}

	/**
	 * Clear cache for a specific post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_cache( $post_id ) {
		$fields = [ 'logo', 'remote_provider', 'remote_id' ];

		foreach ( $fields as $field ) {
			$cache_key = $this->get_cache_key( $post_id, $field );
			wp_cache_delete( $cache_key, self::CACHE_GROUP );
		}

		// Also clear cache for children.
		$this->clear_children_cache( $post_id );
	}

	/**
	 * Clear cache for all children of a post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function clear_children_cache( $post_id ) {
		$children = get_children(
			[
				'post_parent' => $post_id,
				'post_type'   => 'sportspack_unit',
				'post_status' => 'any',
			]
		);

		foreach ( $children as $child ) {
			$this->clear_cache( $child->ID );
		}
	}

	/**
	 * Get the hierarchy level of a post (Sport = 0, Competition = 1, Event = 2).
	 *
	 * @param int $post_id Post ID.
	 * @return int Hierarchy level.
	 */
	public function get_hierarchy_level( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'sportspack_unit' !== $post->post_type ) {
			return -1;
		}

		$level = 0;
		$current_post = $post;

		while ( $current_post->post_parent ) {
			$level++;
			$current_post = get_post( $current_post->post_parent );
			
			if ( ! $current_post ) {
				break;
			}
		}

		return $level;
	}

	/**
	 * Get hierarchy label (Sport, Competition, Event).
	 *
	 * @param int $post_id Post ID.
	 * @return string Hierarchy label.
	 */
	public function get_hierarchy_label( $post_id ) {
		$level = $this->get_hierarchy_level( $post_id );

		switch ( $level ) {
			case 0:
				return __( 'Sport', 'sportspack' );
			case 1:
				return __( 'Competition', 'sportspack' );
			case 2:
				return __( 'Event', 'sportspack' );
			default:
				return __( 'Unknown', 'sportspack' );
		}
	}
}
