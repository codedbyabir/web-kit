<?php
/**
 * Core plugin class.
 *
 * Responsible for:
 * - Registering the "Web Kit" Elementor element category (so widgets
 *   group together in the panel instead of dumping into "General").
 * - Registering each widget found in /widgets.
 * - Registering (not enqueueing) front-end/editor assets so widgets can
 *   declare them via get_style_depends()/get_script_depends() and Elementor
 *   only loads them on pages that actually use the widget.
 *
 * To add a new widget later: drop the widget class file in /widgets and add
 * one line to get_widgets() below.
 *
 * @package Web_Kit
 */

namespace WebKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		// Register (don't enqueue) assets on both front-end and in the editor
		// preview iframe, so get_style_depends()/get_script_depends() on the
		// widget can pull the handle in only when the widget is present.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'register_assets' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_assets' ] );

		// Admin-side features (meta boxes etc.) live in their own classes so
		// they load regardless of whether Elementor is rendering anything.
		require_once WK_PATH . 'includes/class-wk-faq-metabox.php';
		new Faq_Metabox();

		load_plugin_textdomain( 'web-kit', false, dirname( plugin_basename( WK_FILE ) ) . '/languages' );
	}

	/**
	 * Adds a dedicated category so all Web Kit widgets are grouped
	 * together in the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'web-kit',
			[
				'title' => __( 'Web Kit', 'web-kit' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	/**
	 * Map of widget file => fully-qualified class name.
	 * Add a line here (and drop the file in /widgets) to register a new widget.
	 *
	 * @return array<string,string>
	 */
	private function get_widgets() {
		return [
			'class-wk-html-table-widget.php' => '\\WebKit\\Widgets\\Html_Table',
			'class-wk-faq-widget.php'        => '\\WebKit\\Widgets\\Faq',
		];
	}

	/**
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( $widgets_manager ) {
		foreach ( $this->get_widgets() as $file => $class ) {
			$path = WK_PATH . 'widgets/' . $file;

			if ( ! file_exists( $path ) ) {
				continue;
			}

			require_once $path;

			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}

	/**
	 * Registers (does not enqueue) every style/script handle used by any
	 * widget in this plugin. Keep one register_style()/register_script()
	 * call per asset here as the addon grows; widgets pull them in via
	 * get_style_depends() / get_script_depends().
	 */
	public function register_assets() {
		wp_register_style(
			'wk-table',
			WK_URL . 'assets/css/wk-table.css',
			[],
			WK_VERSION
		);

		wp_register_script(
			'wk-table',
			WK_URL . 'assets/js/wk-table.js',
			[],
			WK_VERSION,
			true
		);

		wp_register_style(
			'wk-faq',
			WK_URL . 'assets/css/wk-faq.css',
			[],
			WK_VERSION
		);
	}
}
