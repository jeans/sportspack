<?php
/**
 * WP-CLI Commands
 *
 * @package Sportspack
 */

namespace Sportspack\CLI;

use Sportspack\Inheritance;
use Sportspack\Providers\Provider_StatsPerform;
use Sportspack\Providers\Provider_Heimspiel;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sportspack WP-CLI commands.
 */
class CLI {

	/**
	 * Register WP-CLI commands.
	 *
	 * @return void
	 */
	public static function register() {
		\WP_CLI::add_command( 'sportspack sync events', [ __CLASS__, 'sync_events' ] );
	}

	/**
	 * Sync events from a remote provider for a competition.
	 *
	 * ## OPTIONS
	 *
	 * --competition=<post_id>
	 * : The post ID of the competition.
	 *
	 * [--days=<days>]
	 * : Number of days to sync events for. Default: 30.
	 *
	 * [--provider=<provider>]
	 * : Override the provider. Default: use inherited provider from competition.
	 *
	 * ## EXAMPLES
	 *
	 *     wp sportspack sync events --competition=123 --days=14
	 *     wp sportspack sync events --competition=456 --provider=statsperform
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 * @return void
	 */
	public static function sync_events( $args, $assoc_args ) {
		$competition_id = isset( $assoc_args['competition'] ) ? absint( $assoc_args['competition'] ) : 0;
		$days           = isset( $assoc_args['days'] ) ? absint( $assoc_args['days'] ) : 30;
		$provider_name  = isset( $assoc_args['provider'] ) ? sanitize_text_field( $assoc_args['provider'] ) : '';

		// Validate competition ID.
		if ( ! $competition_id ) {
			\WP_CLI::error( __( 'Please provide a valid competition post ID using --competition=<post_id>.', 'sportspack' ) );
			return;
		}

		// Get competition post.
		$competition = get_post( $competition_id );

		if ( ! $competition || 'sportspack_unit' !== $competition->post_type ) {
			\WP_CLI::error( __( 'Invalid competition post ID. Must be a sportspack_unit post.', 'sportspack' ) );
			return;
		}

		// Get provider name from competition if not specified.
		if ( empty( $provider_name ) ) {
			$provider_name = Inheritance::get_instance()->get_inherited_provider( $competition_id );
		}

		if ( empty( $provider_name ) ) {
			\WP_CLI::error( __( 'No provider specified and no inherited provider found for this competition.', 'sportspack' ) );
			return;
		}

		// Get remote ID.
		$remote_id = Inheritance::get_instance()->get_inherited_remote_id( $competition_id );

		if ( empty( $remote_id ) ) {
			\WP_CLI::error( __( 'No remote ID found for this competition.', 'sportspack' ) );
			return;
		}

		// Initialize provider.
		$provider = self::get_provider( $provider_name );

		if ( ! $provider ) {
			\WP_CLI::error( sprintf( __( 'Unknown provider: %s', 'sportspack' ), $provider_name ) );
			return;
		}

		\WP_CLI::log( sprintf( __( 'Syncing events for competition %d (%s) using provider %s...', 'sportspack' ), $competition_id, get_the_title( $competition_id ), $provider_name ) );

		// Fetch events from provider.
		$events = $provider->fetch_events( $remote_id, $days );

		if ( empty( $events ) ) {
			\WP_CLI::warning( __( 'No events returned from provider.', 'sportspack' ) );
			return;
		}

		\WP_CLI::log( sprintf( __( 'Found %d events to sync.', 'sportspack' ), count( $events ) ) );

		// Process each event.
		$created = 0;
		$updated = 0;

		foreach ( $events as $event_data ) {
			$result = self::create_or_update_event( $competition_id, $event_data, $provider_name );

			if ( 'created' === $result ) {
				$created++;
			} elseif ( 'updated' === $result ) {
				$updated++;
			}
		}

		\WP_CLI::success( sprintf( __( 'Sync complete! Created: %d, Updated: %d', 'sportspack' ), $created, $updated ) );
	}

	/**
	 * Get provider instance by name.
	 *
	 * @param string $provider_name Provider name.
	 * @return object|null Provider instance or null.
	 */
	private static function get_provider( $provider_name ) {
		switch ( $provider_name ) {
			case 'statsperform':
				return new Provider_StatsPerform();
			case 'heimspiel':
				return new Provider_Heimspiel();
			default:
				return null;
		}
	}

	/**
	 * Create or update an event post.
	 *
	 * @param int    $competition_id Competition post ID.
	 * @param array  $event_data     Event data from provider.
	 * @param string $provider_name  Provider name.
	 * @return string 'created', 'updated', or 'skipped'.
	 */
	private static function create_or_update_event( $competition_id, $event_data, $provider_name ) {
		// Check if event already exists by remote ID.
		$existing_posts = get_posts(
			[
				'post_type'   => 'sportspack_unit',
				'post_parent' => $competition_id,
				'meta_query'  => [
					[
						'key'   => '_sportspack_remote_id',
						'value' => $event_data['remote_id'],
					],
				],
				'numberposts' => 1,
			]
		);

		$post_data = [
			'post_title'   => $event_data['title'],
			'post_content' => isset( $event_data['content'] ) ? $event_data['content'] : '',
			'post_status'  => 'publish',
			'post_type'    => 'sportspack_unit',
			'post_parent'  => $competition_id,
		];

		if ( ! empty( $existing_posts ) ) {
			// Update existing event.
			$post_data['ID'] = $existing_posts[0]->ID;
			wp_update_post( $post_data );

			// Update meta.
			update_post_meta( $existing_posts[0]->ID, '_sportspack_remote_provider', $provider_name );
			update_post_meta( $existing_posts[0]->ID, '_sportspack_remote_id', $event_data['remote_id'] );

			return 'updated';
		} else {
			// Create new event.
			$post_id = wp_insert_post( $post_data );

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				// Set meta.
				update_post_meta( $post_id, '_sportspack_remote_provider', $provider_name );
				update_post_meta( $post_id, '_sportspack_remote_id', $event_data['remote_id'] );

				return 'created';
			}
		}

		return 'skipped';
	}
}

// Register CLI commands if WP-CLI is active.
if ( defined( 'WP_CLI' ) && \WP_CLI ) {
	CLI::register();
}
