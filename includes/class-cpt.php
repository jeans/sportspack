<?php
/**
 * Custom Post Type Registration
 *
 * @package Sportspack
 */

namespace Sportspack;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT registration and management.
 */
class CPT {

	/**
	 * Single instance of the class.
	 *
	 * @var CPT
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return CPT
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
	 * Register all custom post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		$this->register_unit_cpt();
		$this->register_team_cpt();
		$this->register_person_cpt();
		$this->register_venue_cpt();
	}

	/**
	 * Register the hierarchical sportspack_unit CPT.
	 *
	 * @return void
	 */
	private function register_unit_cpt() {
		$labels = [
			'name'                  => _x( 'Sports Units', 'Post Type General Name', 'sportspack' ),
			'singular_name'         => _x( 'Sports Unit', 'Post Type Singular Name', 'sportspack' ),
			'menu_name'             => __( 'Sports', 'sportspack' ),
			'name_admin_bar'        => __( 'Sports Unit', 'sportspack' ),
			'archives'              => __( 'Sports Unit Archives', 'sportspack' ),
			'attributes'            => __( 'Sports Unit Attributes', 'sportspack' ),
			'parent_item_colon'     => __( 'Parent Sports Unit:', 'sportspack' ),
			'all_items'             => __( 'All Sports Units', 'sportspack' ),
			'add_new_item'          => __( 'Add New Sports Unit', 'sportspack' ),
			'add_new'               => __( 'Add New', 'sportspack' ),
			'new_item'              => __( 'New Sports Unit', 'sportspack' ),
			'edit_item'             => __( 'Edit Sports Unit', 'sportspack' ),
			'update_item'           => __( 'Update Sports Unit', 'sportspack' ),
			'view_item'             => __( 'View Sports Unit', 'sportspack' ),
			'view_items'            => __( 'View Sports Units', 'sportspack' ),
			'search_items'          => __( 'Search Sports Unit', 'sportspack' ),
			'not_found'             => __( 'Not found', 'sportspack' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'sportspack' ),
			'featured_image'        => __( 'Featured Image', 'sportspack' ),
			'set_featured_image'    => __( 'Set featured image', 'sportspack' ),
			'remove_featured_image' => __( 'Remove featured image', 'sportspack' ),
			'use_featured_image'    => __( 'Use as featured image', 'sportspack' ),
			'insert_into_item'      => __( 'Insert into sports unit', 'sportspack' ),
			'uploaded_to_this_item' => __( 'Uploaded to this sports unit', 'sportspack' ),
			'items_list'            => __( 'Sports units list', 'sportspack' ),
			'items_list_navigation' => __( 'Sports units list navigation', 'sportspack' ),
			'filter_items_list'     => __( 'Filter sports units list', 'sportspack' ),
		];

		$args = [
			'label'               => __( 'Sports Unit', 'sportspack' ),
			'description'         => __( 'Hierarchical sports data: Sport -> Competition -> Event', 'sportspack' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields' ],
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-awards',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => [
				'slug'       => 'sports',
				'with_front' => false,
			],
		];

		register_post_type( 'sportspack_unit', $args );
	}

	/**
	 * Register the sportspack_team CPT.
	 *
	 * @return void
	 */
	private function register_team_cpt() {
		$labels = [
			'name'                  => _x( 'Teams', 'Post Type General Name', 'sportspack' ),
			'singular_name'         => _x( 'Team', 'Post Type Singular Name', 'sportspack' ),
			'menu_name'             => __( 'Teams', 'sportspack' ),
			'name_admin_bar'        => __( 'Team', 'sportspack' ),
			'archives'              => __( 'Team Archives', 'sportspack' ),
			'attributes'            => __( 'Team Attributes', 'sportspack' ),
			'all_items'             => __( 'All Teams', 'sportspack' ),
			'add_new_item'          => __( 'Add New Team', 'sportspack' ),
			'add_new'               => __( 'Add New', 'sportspack' ),
			'new_item'              => __( 'New Team', 'sportspack' ),
			'edit_item'             => __( 'Edit Team', 'sportspack' ),
			'update_item'           => __( 'Update Team', 'sportspack' ),
			'view_item'             => __( 'View Team', 'sportspack' ),
			'view_items'            => __( 'View Teams', 'sportspack' ),
			'search_items'          => __( 'Search Team', 'sportspack' ),
			'not_found'             => __( 'Not found', 'sportspack' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'sportspack' ),
		];

		$args = [
			'label'               => __( 'Team', 'sportspack' ),
			'description'         => __( 'Sports teams', 'sportspack' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 6,
			'menu_icon'           => 'dashicons-groups',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => [
				'slug'       => 'teams',
				'with_front' => false,
			],
		];

		register_post_type( 'sportspack_team', $args );
	}

	/**
	 * Register the sportspack_person CPT.
	 *
	 * @return void
	 */
	private function register_person_cpt() {
		$labels = [
			'name'                  => _x( 'Persons', 'Post Type General Name', 'sportspack' ),
			'singular_name'         => _x( 'Person', 'Post Type Singular Name', 'sportspack' ),
			'menu_name'             => __( 'Persons', 'sportspack' ),
			'name_admin_bar'        => __( 'Person', 'sportspack' ),
			'archives'              => __( 'Person Archives', 'sportspack' ),
			'attributes'            => __( 'Person Attributes', 'sportspack' ),
			'all_items'             => __( 'All Persons', 'sportspack' ),
			'add_new_item'          => __( 'Add New Person', 'sportspack' ),
			'add_new'               => __( 'Add New', 'sportspack' ),
			'new_item'              => __( 'New Person', 'sportspack' ),
			'edit_item'             => __( 'Edit Person', 'sportspack' ),
			'update_item'           => __( 'Update Person', 'sportspack' ),
			'view_item'             => __( 'View Person', 'sportspack' ),
			'view_items'            => __( 'View Persons', 'sportspack' ),
			'search_items'          => __( 'Search Person', 'sportspack' ),
			'not_found'             => __( 'Not found', 'sportspack' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'sportspack' ),
		];

		$args = [
			'label'               => __( 'Person', 'sportspack' ),
			'description'         => __( 'Sports persons (players, coaches, etc.)', 'sportspack' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 7,
			'menu_icon'           => 'dashicons-admin-users',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => [
				'slug'       => 'persons',
				'with_front' => false,
			],
		];

		register_post_type( 'sportspack_person', $args );
	}

	/**
	 * Register the sportspack_venue CPT.
	 *
	 * @return void
	 */
	private function register_venue_cpt() {
		$labels = [
			'name'                  => _x( 'Venues', 'Post Type General Name', 'sportspack' ),
			'singular_name'         => _x( 'Venue', 'Post Type Singular Name', 'sportspack' ),
			'menu_name'             => __( 'Venues', 'sportspack' ),
			'name_admin_bar'        => __( 'Venue', 'sportspack' ),
			'archives'              => __( 'Venue Archives', 'sportspack' ),
			'attributes'            => __( 'Venue Attributes', 'sportspack' ),
			'all_items'             => __( 'All Venues', 'sportspack' ),
			'add_new_item'          => __( 'Add New Venue', 'sportspack' ),
			'add_new'               => __( 'Add New', 'sportspack' ),
			'new_item'              => __( 'New Venue', 'sportspack' ),
			'edit_item'             => __( 'Edit Venue', 'sportspack' ),
			'update_item'           => __( 'Update Venue', 'sportspack' ),
			'view_item'             => __( 'View Venue', 'sportspack' ),
			'view_items'            => __( 'View Venues', 'sportspack' ),
			'search_items'          => __( 'Search Venue', 'sportspack' ),
			'not_found'             => __( 'Not found', 'sportspack' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'sportspack' ),
		];

		$args = [
			'label'               => __( 'Venue', 'sportspack' ),
			'description'         => __( 'Sports venues', 'sportspack' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 8,
			'menu_icon'           => 'dashicons-location',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => [
				'slug'       => 'venues',
				'with_front' => false,
			],
		];

		register_post_type( 'sportspack_venue', $args );
	}
}
