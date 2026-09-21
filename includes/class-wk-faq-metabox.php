<?php
/**
 * FAQ repeater meta box.
 *
 * Adds a "FAQs" box to the Post edit screen where an editor can add any
 * number of question/answer pairs. Saved as a single post meta value
 * (an array of ['question' => ..., 'answer' => ...]) so the front-end
 * widget (Widgets\Faq) can read it in one call.
 *
 * @package Web_Kit
 */

namespace WebKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Faq_Metabox {

	const META_KEY     = '_wk_faq_items';
	const NONCE_ACTION = 'wk_faq_save';
	const NONCE_NAME   = 'wk_faq_nonce';

	/**
	 * Post types that get the FAQ box. Posts only, per current scope.
	 *
	 * @var string[]
	 */
	const POST_TYPES = [ 'post' ];

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	public function add_meta_box() {
		foreach ( self::POST_TYPES as $post_type ) {
			add_meta_box(
				'wk_faq_metabox',
				__( 'FAQs', 'web-kit' ),
				[ $this, 'render' ],
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, self::POST_TYPES, true ) ) {
			return;
		}

		wp_enqueue_style(
			'wk-faq-admin',
			WK_URL . 'assets/css/wk-faq-admin.css',
			[],
			WK_VERSION
		);

		wp_enqueue_script(
			'wk-faq-admin',
			WK_URL . 'assets/js/wk-faq-admin.js',
			[],
			WK_VERSION,
			true
		);

		wp_localize_script(
			'wk-faq-admin',
			'wkFaqAdminL10n',
			[
				'newFaqLabel'    => __( 'New FAQ', 'web-kit' ),
				'confirmRemove'  => __( 'Remove this FAQ?', 'web-kit' ),
			]
		);
	}

	/**
	 * @param \WP_Post $post
	 */
	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$items = get_post_meta( $post->ID, self::META_KEY, true );
		if ( ! is_array( $items ) ) {
			$items = [];
		}
		?>
		<div id="wk-faq-repeater" class="wk-faq-repeater">
			<p class="description">
				<?php esc_html_e( 'Add the questions and answers to show for this post. Drop one or more "Web Kit FAQ" widgets on the page in Elementor to display them.', 'web-kit' ); ?>
			</p>

			<div class="wk-faq-rows">
				<?php
				if ( $items ) :
					foreach ( $items as $index => $item ) :
						$this->render_row( $index, $item );
					endforeach;
				endif;
				?>
			</div>

			<p>
				<button type="button" class="button button-secondary" id="wk-faq-add">
					<?php esc_html_e( '+ Add FAQ', 'web-kit' ); ?>
				</button>
			</p>
		</div>

		<script type="text/template" id="wk-faq-row-template">
			<?php $this->render_row( '__INDEX__', [ 'question' => '', 'answer' => '' ] ); ?>
		</script>
		<?php
	}

	/**
	 * Renders one repeater row. Also used (with a placeholder index) to
	 * build the client-side <template> for new rows, so markup only ever
	 * lives in one place.
	 *
	 * @param int|string $index
	 * @param array      $item
	 */
	private function render_row( $index, array $item ) {
		$question = isset( $item['question'] ) ? $item['question'] : '';
		$answer   = isset( $item['answer'] ) ? $item['answer'] : '';
		$title    = '' !== $question ? $question : __( 'New FAQ', 'web-kit' );
		?>
		<div class="wk-faq-row">
			<div class="wk-faq-row-header">
				<span class="wk-faq-row-title"><?php echo esc_html( $title ); ?></span>
				<button type="button" class="button-link wk-faq-remove" aria-label="<?php esc_attr_e( 'Remove FAQ', 'web-kit' ); ?>">&times;</button>
			</div>
			<div class="wk-faq-row-body">
				<p>
					<label><?php esc_html_e( 'Question', 'web-kit' ); ?></label>
					<input
						type="text"
						class="widefat wk-faq-question"
						name="wk_faq_items[<?php echo esc_attr( $index ); ?>][question]"
						value="<?php echo esc_attr( $question ); ?>"
					/>
				</p>
				<p>
					<label><?php esc_html_e( 'Answer', 'web-kit' ); ?></label>
					<textarea
						class="widefat wk-faq-answer"
						rows="4"
						name="wk_faq_items[<?php echo esc_attr( $index ); ?>][answer]"
					><?php echo esc_textarea( $answer ); ?></textarea>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * @param int $post_id
	 */
	public function save( $post_id ) {

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION )
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! in_array( get_post_type( $post_id ), self::POST_TYPES, true ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = isset( $_POST['wk_faq_items'] ) && is_array( $_POST['wk_faq_items'] )
			? wp_unslash( $_POST['wk_faq_items'] )
			: [];

		$clean = [];
		foreach ( $raw as $item ) {
			$question = isset( $item['question'] ) ? sanitize_text_field( $item['question'] ) : '';
			$answer   = isset( $item['answer'] ) ? wp_kses_post( $item['answer'] ) : '';

			if ( '' === $question && '' === trim( wp_strip_all_tags( $answer ) ) ) {
				continue; // Skip fully-empty rows (e.g. an added-then-untouched row).
			}

			$clean[] = [
				'question' => $question,
				'answer'   => $answer,
			];
		}

		if ( empty( $clean ) ) {
			delete_post_meta( $post_id, self::META_KEY );
		} else {
			update_post_meta( $post_id, self::META_KEY, $clean );
		}
	}
}
