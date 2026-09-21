<?php
/**
 * Web Kit settings / admin dashboard.
 *
 * A single top-level "Web Kit" admin page. Currently has one tab (FAQ),
 * structured so more feature tabs can be added later without reworking the
 * option storage - everything lives under one option key, keyed by
 * feature/module ('faq', etc).
 *
 * Also the single source of truth for "is the FAQ feature enabled for this
 * post?" - both Faq_Metabox (admin) and Widgets\Faq (front-end) call
 * faq_enabled_for_post() so the rule is defined in exactly one place.
 *
 * @package Web_Kit
 */

namespace WebKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	const OPTION_KEY   = 'wk_settings';
	const SETTINGS_GRP = 'wk_settings_group';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_menu() {
		add_menu_page(
			__( 'Web Kit', 'web-kit' ),
			__( 'Web Kit', 'web-kit' ),
			'manage_options',
			'wk-settings',
			[ $this, 'render_page' ],
			'dashicons-admin-generic',
			80
		);
	}

	/**
	 * Default shape of the option. Every module's sub-array should always
	 * be written in full by its sanitize step (see sanitize() below), so a
	 * shallow merge with get_option() is enough - no deep-merge needed.
	 */
	public static function get_defaults() {
		return [
			'faq' => [
				'post_types' => [ 'post' ],
				'taxonomy'   => '',
				'terms'      => [],
			],
		];
	}

	public static function get_settings() {
		$saved = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}
		return wp_parse_args( $saved, self::get_defaults() );
	}

	public static function get_faq_settings() {
		$settings = self::get_settings();
		return $settings['faq'];
	}

	public static function get_faq_post_types() {
		$faq = self::get_faq_settings();
		return ! empty( $faq['post_types'] ) ? $faq['post_types'] : [];
	}

	/**
	 * Whether the FAQ feature (meta box + widget output) is enabled for a
	 * given post: its post type must be selected, and - if a taxonomy
	 * restriction is configured with at least one term chosen - the post
	 * must carry at least one of those terms.
	 *
	 * @param int|\WP_Post $post
	 */
	public static function faq_enabled_for_post( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		$faq = self::get_faq_settings();

		if ( empty( $faq['post_types'] ) || ! in_array( $post->post_type, $faq['post_types'], true ) ) {
			return false;
		}

		if ( ! empty( $faq['taxonomy'] ) && ! empty( $faq['terms'] ) ) {
			if ( ! taxonomy_exists( $faq['taxonomy'] ) ) {
				return true; // Taxonomy removed since saving - fail open rather than silently hiding everything.
			}
			if ( ! has_term( $faq['terms'], $faq['taxonomy'], $post ) ) {
				return false;
			}
		}

		return true;
	}

	public function register_settings() {
		register_setting(
			self::SETTINGS_GRP,
			self::OPTION_KEY,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize' ],
				'default'           => self::get_defaults(),
			]
		);
	}

	/**
	 * @param array $input Raw $_POST['wk_settings'].
	 */
	public function sanitize( $input ) {
		$output = self::get_defaults();

		$valid_post_types = array_keys( get_post_types( [ 'public' => true ] ) );
		$post_types        = isset( $input['faq']['post_types'] ) && is_array( $input['faq']['post_types'] )
			? array_map( 'sanitize_key', $input['faq']['post_types'] )
			: [];
		$output['faq']['post_types'] = array_values( array_intersect( $post_types, $valid_post_types ) );

		$valid_taxonomies = array_keys( get_taxonomies( [ 'public' => true ] ) );
		$taxonomy         = isset( $input['faq']['taxonomy'] ) ? sanitize_key( $input['faq']['taxonomy'] ) : '';
		$output['faq']['taxonomy'] = in_array( $taxonomy, $valid_taxonomies, true ) ? $taxonomy : '';

		$terms = [];
		if (
			$output['faq']['taxonomy']
			&& isset( $input['faq']['terms'][ $output['faq']['taxonomy'] ] )
			&& is_array( $input['faq']['terms'][ $output['faq']['taxonomy'] ] )
		) {
			$terms = array_map( 'absint', $input['faq']['terms'][ $output['faq']['taxonomy'] ] );
		}
		$output['faq']['terms'] = $terms;

		add_settings_error( 'wk_settings', 'wk_settings_saved', __( 'Web Kit settings saved.', 'web-kit' ), 'updated' );

		return $output;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$faq        = self::get_faq_settings();
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

		// Preload every public taxonomy's terms once; the taxonomy <select>
		// below just shows/hides the matching group client-side (no AJAX).
		$tax_terms = [];
		foreach ( $taxonomies as $tax ) {
			$terms                    = get_terms( [ 'taxonomy' => $tax->name, 'hide_empty' => false, 'number' => 200 ] );
			$tax_terms[ $tax->name ] = is_wp_error( $terms ) ? [] : $terms;
		}
		?>
		<div class="wrap wk-settings-wrap">
			<h1><?php esc_html_e( 'Web Kit', 'web-kit' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="#" class="nav-tab nav-tab-active"><?php esc_html_e( 'FAQ', 'web-kit' ); ?></a>
				<?php // Additional module tabs can be added here as the dashboard grows. ?>
			</h2>

			<form method="post" action="options.php">
				<?php settings_fields( self::SETTINGS_GRP ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable For Post Types', 'web-kit' ); ?></th>
						<td>
							<?php foreach ( $post_types as $pt ) : ?>
								<?php if ( 'attachment' === $pt->name ) { continue; } ?>
								<label style="display:inline-block;margin-right:16px;">
									<input
										type="checkbox"
										name="wk_settings[faq][post_types][]"
										value="<?php echo esc_attr( $pt->name ); ?>"
										<?php checked( in_array( $pt->name, $faq['post_types'], true ) ); ?>
									/>
									<?php echo esc_html( $pt->labels->singular_name ); ?>
								</label>
							<?php endforeach; ?>
							<p class="description">
								<?php esc_html_e( 'Post types where the FAQs box appears on the edit screen, and where the FAQ widget will output content.', 'web-kit' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wk-faq-taxonomy"><?php esc_html_e( 'Restrict By Taxonomy', 'web-kit' ); ?></label>
						</th>
						<td>
							<select id="wk-faq-taxonomy" name="wk_settings[faq][taxonomy]">
								<option value=""><?php esc_html_e( 'No restriction (all posts of the selected types)', 'web-kit' ); ?></option>
								<?php foreach ( $taxonomies as $tax ) : ?>
									<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $faq['taxonomy'], $tax->name ); ?>>
										<?php echo esc_html( $tax->labels->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Optional. Further limits the FAQ feature to posts that have at least one of the selected terms below.', 'web-kit' ); ?>
							</p>

							<?php foreach ( $tax_terms as $tax_name => $terms ) : ?>
								<div
									class="wk-faq-terms-group"
									data-taxonomy="<?php echo esc_attr( $tax_name ); ?>"
									style="margin-top:10px;<?php echo ( $faq['taxonomy'] === $tax_name ) ? '' : 'display:none;'; ?>"
								>
									<?php if ( $terms ) : ?>
										<?php foreach ( $terms as $term ) : ?>
											<label style="display:inline-block;margin-right:16px;">
												<input
													type="checkbox"
													name="wk_settings[faq][terms][<?php echo esc_attr( $tax_name ); ?>][]"
													value="<?php echo esc_attr( $term->term_id ); ?>"
													<?php checked( in_array( $term->term_id, $faq['terms'], true ) ); ?>
												/>
												<?php echo esc_html( $term->name ); ?>
											</label>
										<?php endforeach; ?>
									<?php else : ?>
										<em><?php esc_html_e( 'No terms found for this taxonomy.', 'web-kit' ); ?></em>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>

		<script>
		( function () {
			var select = document.getElementById( 'wk-faq-taxonomy' );
			if ( ! select ) {
				return;
			}
			var groups = document.querySelectorAll( '.wk-faq-terms-group' );
			select.addEventListener( 'change', function () {
				groups.forEach( function ( group ) {
					group.style.display = ( group.getAttribute( 'data-taxonomy' ) === select.value ) ? '' : 'none';
				} );
			} );
		} )();
		</script>
		<?php
	}
}