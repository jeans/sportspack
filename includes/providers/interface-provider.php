<?php
/**
 * Provider Interface
 *
 * @package Sportspack
 */

namespace Sportspack\Providers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for remote data providers.
 */
interface Provider_Interface {

	/**
	 * Get provider name/identifier.
	 *
	 * @return string Provider name.
	 */
	public function get_name();

	/**
	 * Fetch events for a competition from the remote provider.
	 *
	 * @param string $remote_id  Remote competition ID.
	 * @param int    $days       Number of days to fetch events for.
	 * @return array Array of event data.
	 */
	public function fetch_events( $remote_id, $days = 30 );

	/**
	 * Validate provider configuration.
	 *
	 * @return bool True if configured properly.
	 */
	public function is_configured();
}
