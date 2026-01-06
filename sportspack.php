<?php
/**
 * Plugin Name: Sportspack
 * Plugin URI: https://github.com/jeans/sportspack
 * Description: A high-performance WordPress plugin for managing sports data hierarchies (Sport -> Competition -> Event) and entities (Teams, Persons, Venues). Designed for WordPress VIP and Newspack.
 * Version: 1.0.0
 * Author: Sportspack Team
 * Author URI: https://github.com/jeans/sportspack
 * Text Domain: sportspack
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Sportspack
 */

namespace Sportspack;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SPORTSPACK_VERSION', '1.0.0' );
define( 'SPORTSPACK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPORTSPACK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPORTSPACK_PLUGIN_FILE', __FILE__ );

// Autoload classes.
require_once SPORTSPACK_PLUGIN_DIR . 'includes/class-plugin.php';
require_once SPORTSPACK_PLUGIN_DIR . 'includes/class-cpt.php';
require_once SPORTSPACK_PLUGIN_DIR . 'includes/class-meta.php';
require_once SPORTSPACK_PLUGIN_DIR . 'includes/class-inheritance.php';
require_once SPORTSPACK_PLUGIN_DIR . 'includes/class-template-loader.php';

// Provider interface and implementations.
require_once SPORTSPACK_PLUGIN_DIR . 'includes/providers/interface-provider.php';
require_once SPORTSPACK_PLUGIN_DIR . 'includes/providers/class-provider-statsperform.php';
require_once SPORTSPACK_PLUGIN_DIR . 'includes/providers/class-provider-heimspiel.php';

// WP-CLI support.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once SPORTSPACK_PLUGIN_DIR . 'includes/cli/class-cli.php';
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function init() {
	Plugin::get_instance();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Plugin activation hook.
 *
 * @return void
 */
function activate() {
	// Initialize CPT to register post types.
	CPT::get_instance()->register_post_types();
	
	// Flush rewrite rules.
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function deactivate() {
	// Flush rewrite rules.
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate' );
