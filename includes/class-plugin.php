<?php
/**
 * Main Plugin Class
 *
 * @package Sportspack
 */

namespace Sportspack;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin bootstrap class.
 */
class Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
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
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Initialize CPT registration.
		add_action( 'init', [ CPT::get_instance(), 'register_post_types' ] );
		
		// Initialize meta registration.
		add_action( 'init', [ Meta::get_instance(), 'register_meta' ] );
		
		// Initialize template loader.
		add_action( 'template_include', [ Template_Loader::get_instance(), 'template_include' ] );
		
		// Load text domain.
		add_action( 'init', [ $this, 'load_textdomain' ] );
		
		// Admin hooks.
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', [ Meta::get_instance(), 'add_meta_boxes' ] );
			add_action( 'save_post', [ Meta::get_instance(), 'save_meta_box' ], 10, 2 );
		}
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'sportspack',
			false,
			dirname( plugin_basename( SPORTSPACK_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Check if Newspack plugin is active.
	 *
	 * @return bool
	 */
	public function is_newspack_active() {
		return class_exists( 'Newspack' );
	}
}
