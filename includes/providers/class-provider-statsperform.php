<?php
/**
 * StatsPerform Provider
 *
 * @package Sportspack
 */

namespace Sportspack\Providers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * StatsPerform data provider implementation.
 */
class Provider_StatsPerform implements Provider_Interface {

	/**
	 * API credentials.
	 *
	 * @var array
	 */
	private $credentials;

	/**
	 * Constructor.
	 *
	 * @param array $credentials API credentials.
	 */
	public function __construct( $credentials = [] ) {
		$this->credentials = $credentials;
	}

	/**
	 * Get provider name.
	 *
	 * @return string Provider name.
	 */
	public function get_name() {
		return 'statsperform';
	}

	/**
	 * Fetch events for a competition.
	 *
	 * @param string $remote_id Remote competition ID.
	 * @param int    $days      Number of days to fetch events for.
	 * @return array Array of event data.
	 */
	public function fetch_events( $remote_id, $days = 30 ) {
		// Stub implementation - to be implemented with actual API calls.
		// This would normally make HTTP requests to StatsPerform API.
		
		/**
		 * Filter to allow mock data or custom implementation.
		 *
		 * @param array  $events    Array of events.
		 * @param string $remote_id Remote competition ID.
		 * @param int    $days      Number of days.
		 */
		return apply_filters(
			'sportspack_statsperform_fetch_events',
			[],
			$remote_id,
			$days
		);
	}

	/**
	 * Validate provider configuration.
	 *
	 * @return bool True if configured properly.
	 */
	public function is_configured() {
		// Check if credentials are set.
		// This is a stub - actual implementation would validate API keys.
		return ! empty( $this->credentials );
	}
}
