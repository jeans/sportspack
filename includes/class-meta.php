<?php
/**
 * Meta Field Registration and UI
 *
 * @package Sportspack
 */

namespace Sportspack;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta registration and UI management.
 */
class Meta {

	/**
	 * Single instance of the class.
	 *
	 * @var Meta
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Meta
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
	 * Register post meta fields.
	 *
	 * @return void
	 */
	public function register_meta() {
		$post_types = [ 'sportspack_unit', 'sportspack_team', 'sportspack_person', 'sportspack_venue' ];

		foreach ( $post_types as $post_type ) {
			// Logo (attachment ID or URL).
			register_post_meta(
				$post_type,
				'_sportspack_logo',
				[
					'type'              => 'string',
					'description'       => __( 'Logo attachment ID or URL', 'sportspack' ),
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				]
			);

			// Remote provider.
			register_post_meta(
				$post_type,
				'_sportspack_remote_provider',
				[
					'type'              => 'string',
					'description'       => __( 'Remote data provider', 'sportspack' ),
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => [ $this, 'sanitize_provider' ],
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				]
			);

			// Remote ID.
			register_post_meta(
				$post_type,
				'_sportspack_remote_id',
				[
					'type'              => 'string',
					'description'       => __( 'Remote ID from provider', 'sportspack' ),
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				]
			);
		}
	}

	/**
	 * Sanitize provider value.
	 *
	 * @param string $value Provider value.
	 * @return string Sanitized provider value.
	 */
	public function sanitize_provider( $value ) {
		$allowed_providers = [ 'statsperform', 'heimspiel', 'sportradar', 'custom', '' ];
		
		if ( ! in_array( $value, $allowed_providers, true ) ) {
			return '';
		}
		
		return sanitize_text_field( $value );
	}

	/**
	 * Add meta boxes to admin.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		$post_types = [ 'sportspack_unit', 'sportspack_team', 'sportspack_person', 'sportspack_venue' ];

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'sportspack_meta',
				__( 'Sportspack Settings', 'sportspack' ),
				[ $this, 'render_meta_box' ],
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render meta box content.
	 *
	 * @param \WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'sportspack_meta_box', 'sportspack_meta_box_nonce' );

		// Get current values.
		$logo             = get_post_meta( $post->ID, '_sportspack_logo', true );
		$remote_provider  = get_post_meta( $post->ID, '_sportspack_remote_provider', true );
		$remote_id        = get_post_meta( $post->ID, '_sportspack_remote_id', true );

		// Show inherited values for sportspack_unit.
		if ( 'sportspack_unit' === $post->post_type && $post->post_parent ) {
			$inherited_logo     = Inheritance::get_instance()->get_inherited_logo( $post->ID );
			$inherited_provider = Inheritance::get_instance()->get_inherited_provider( $post->ID );
			$inherited_id       = Inheritance::get_instance()->get_inherited_remote_id( $post->ID );
		}
		?>
		<div class="sportspack-meta-fields">
			<p>
				<label for="sportspack_logo">
					<strong><?php esc_html_e( 'Logo (Attachment ID or URL):', 'sportspack' ); ?></strong>
				</label>
				<input 
					type="text" 
					id="sportspack_logo" 
					name="sportspack_logo" 
					value="<?php echo esc_attr( $logo ); ?>" 
					class="widefat"
				/>
				<?php if ( ! empty( $inherited_logo ) && empty( $logo ) ) : ?>
					<small class="description">
						<?php
						/* translators: %s: inherited logo value */
						printf( esc_html__( 'Inherited: %s', 'sportspack' ), esc_html( $inherited_logo ) );
						?>
					</small>
				<?php endif; ?>
			</p>

			<p>
				<label for="sportspack_remote_provider">
					<strong><?php esc_html_e( 'Remote Provider:', 'sportspack' ); ?></strong>
				</label>
				<select id="sportspack_remote_provider" name="sportspack_remote_provider" class="widefat">
					<option value=""><?php esc_html_e( '— Select —', 'sportspack' ); ?></option>
					<option value="statsperform" <?php selected( $remote_provider, 'statsperform' ); ?>>
						<?php esc_html_e( 'StatsPerform', 'sportspack' ); ?>
					</option>
					<option value="heimspiel" <?php selected( $remote_provider, 'heimspiel' ); ?>>
						<?php esc_html_e( 'Heimspiel', 'sportspack' ); ?>
					</option>
					<option value="sportradar" <?php selected( $remote_provider, 'sportradar' ); ?>>
						<?php esc_html_e( 'Sportradar', 'sportspack' ); ?>
					</option>
					<option value="custom" <?php selected( $remote_provider, 'custom' ); ?>>
						<?php esc_html_e( 'Custom', 'sportspack' ); ?>
					</option>
				</select>
				<?php if ( ! empty( $inherited_provider ) && empty( $remote_provider ) ) : ?>
					<small class="description">
						<?php
						/* translators: %s: inherited provider value */
						printf( esc_html__( 'Inherited: %s', 'sportspack' ), esc_html( $inherited_provider ) );
						?>
					</small>
				<?php endif; ?>
			</p>

			<p>
				<label for="sportspack_remote_id">
					<strong><?php esc_html_e( 'Remote ID:', 'sportspack' ); ?></strong>
				</label>
				<input 
					type="text" 
					id="sportspack_remote_id" 
					name="sportspack_remote_id" 
					value="<?php echo esc_attr( $remote_id ); ?>" 
					class="widefat"
				/>
				<?php if ( ! empty( $inherited_id ) && empty( $remote_id ) ) : ?>
					<small class="description">
						<?php
						/* translators: %s: inherited remote ID value */
						printf( esc_html__( 'Inherited: %s', 'sportspack' ), esc_html( $inherited_id ) );
						?>
					</small>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta_box( $post_id, $post ) {
		// Check if nonce is set.
		if ( ! isset( $_POST['sportspack_meta_box_nonce'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sportspack_meta_box_nonce'] ) ), 'sportspack_meta_box' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save logo.
		if ( isset( $_POST['sportspack_logo'] ) ) {
			update_post_meta( $post_id, '_sportspack_logo', sanitize_text_field( wp_unslash( $_POST['sportspack_logo'] ) ) );
		}

		// Save remote provider.
		if ( isset( $_POST['sportspack_remote_provider'] ) ) {
			update_post_meta( $post_id, '_sportspack_remote_provider', $this->sanitize_provider( sanitize_text_field( wp_unslash( $_POST['sportspack_remote_provider'] ) ) ) );
		}

		// Save remote ID.
		if ( isset( $_POST['sportspack_remote_id'] ) ) {
			update_post_meta( $post_id, '_sportspack_remote_id', sanitize_text_field( wp_unslash( $_POST['sportspack_remote_id'] ) ) );
		}

		// Clear inheritance cache for this post.
		Inheritance::get_instance()->clear_cache( $post_id );
	}
}
