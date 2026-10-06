<?php
/**
 * Post Reviewer meta box.
 *
 * Adds a "Post Reviewer" selector box to the Post edit screen allowing an
 * editor to designate a registered WordPress user who reviewed the post.
 * The value is stored in post meta ('_wk_post_reviewer_id') and consumed
 * by the Web Kit Author & Reviewer widget.
 *
 * @package Web_Kit
 */

namespace WebKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Reviewer_Metabox {

	const META_KEY     = '_wk_post_reviewer_id';
	const NONCE_ACTION = 'wk_reviewer_metabox_save';
	const NONCE_NAME   = 'wk_reviewer_metabox_nonce';

	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save' ], 10, 2 );
	}

	/**
	 * Register the post meta for Gutenberg / REST API support.
	 */
	public function register_meta() {
		$post_types = $this->get_supported_post_types();

		foreach ( $post_types as $post_type ) {
			register_post_meta(
				$post_type,
				self::META_KEY,
				[
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'integer',
					'auth_callback' => function() {
						return current_user_can( 'edit_posts' );
					},
				]
			);
		}
	}

	/**
	 * Get list of post types that support the Reviewer meta box.
	 *
	 * @return array
	 */
	public function get_supported_post_types() {
		return apply_filters( 'wk_reviewer_post_types', [ 'post', 'page' ] );
	}

	/**
	 * Register the meta box on supported post types.
	 *
	 * @param string   $post_type
	 * @param \WP_Post $post
	 */
	public function add_meta_box( $post_type, $post = null ) {
		if ( ! in_array( $post_type, $this->get_supported_post_types(), true ) ) {
			return;
		}

		add_meta_box(
			'wk_reviewer_metabox',
			esc_html__( 'Post Reviewer', 'web-kit' ),
			[ $this, 'render' ],
			$post_type,
			'side',
			'default'
		);
	}

	/**
	 * Render the Reviewer meta box HTML.
	 *
	 * @param \WP_Post $post
	 */
	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$selected_reviewer_id = (int) get_post_meta( $post->ID, self::META_KEY, true );
		$reviewer_user        = $selected_reviewer_id > 0 ? get_userdata( $selected_reviewer_id ) : false;
		?>
		<div class="wk-reviewer-metabox-content" style="padding-top:4px;">
			<p style="margin-top:0;margin-bottom:8px;">
				<label for="wk_post_reviewer_id" style="font-weight:600;display:block;margin-bottom:4px;">
					<?php esc_html_e( 'Select Reviewer:', 'web-kit' ); ?>
				</label>
				<?php
				wp_dropdown_users( [
					'name'              => 'wk_post_reviewer_id',
					'id'                => 'wk_post_reviewer_id',
					'selected'          => $selected_reviewer_id,
					'show_option_none'  => esc_html__( '— No Reviewer (None) —', 'web-kit' ),
					'option_none_value' => '0',
					'class'             => 'widefat',
				] );
				?>
			</p>

			<p class="description" style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.4;">
				<?php esc_html_e( 'Designate a WordPress user as the reviewer for this post. Used dynamically by the Web Kit Author & Reviewer widget.', 'web-kit' ); ?>
			</p>

			<?php if ( $reviewer_user ) : ?>
				<div class="wk-reviewer-preview" style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
					<div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;overflow:hidden;background:#e2e8f0;">
						<?php echo get_avatar( $reviewer_user->ID, 36, '', '', [ 'style' => 'width:100%;height:100%;display:block;border-radius:50%;' ] ); ?>
					</div>
					<div style="min-width:0;line-height:1.2;">
						<div style="font-weight:600;font-size:13px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
							<?php echo esc_html( $reviewer_user->display_name ); ?>
						</div>
						<div style="font-size:11px;color:#64748b;margin-top:2px;">
							<?php
							$roles = ! empty( $reviewer_user->roles ) ? implode( ', ', array_map( 'ucfirst', $reviewer_user->roles ) ) : '';
							echo esc_html( $roles ? $roles : $reviewer_user->user_login );
							?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save the Reviewer selection when saving post.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function save( $post_id, $post ) {
		// Nonce check.
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		// Bail on autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['wk_post_reviewer_id'] ) ) {
			$reviewer_id = absint( $_POST['wk_post_reviewer_id'] );

			if ( $reviewer_id > 0 ) {
				update_post_meta( $post_id, self::META_KEY, $reviewer_id );
			} else {
				delete_post_meta( $post_id, self::META_KEY );
			}
		}
	}

	/**
	 * Helper method to retrieve reviewer user ID for a post.
	 *
	 * @param int $post_id
	 * @return int
	 */
	public static function get_reviewer_id( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		return absint( get_post_meta( $post_id, self::META_KEY, true ) );
	}
}
