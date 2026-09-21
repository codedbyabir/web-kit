<?php
/**
 * Plugin Name:       Web Kit
 * Plugin URI:        https://example.com/web-kit
 * Description:       Custom Elementor widgets. Includes an HTML-rendered (no JS table library) repeater-driven data/comparison table widget.
 * Version:           1.0.0
 * Author:            Your Company
 * Text Domain:       web-kit
 * Requires PHP:      7.4
 * Elementor tested up to: 3.26.0
 * Elementor Pro tested up to: 3.26.0
 *
 * @package Web_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WK_VERSION', '1.0.0' );
define( 'WK_FILE', __FILE__ );
define( 'WK_PATH', plugin_dir_path( __FILE__ ) );
define( 'WK_URL', plugin_dir_url( __FILE__ ) );
define( 'WK_MIN_ELEMENTOR_VERSION', '3.5.0' );
define( 'WK_MIN_PHP_VERSION', '7.4' );

/**
 * Bootstraps the plugin once all other plugins are loaded, so we can safely
 * check for Elementor and bail out gracefully (with an admin notice) if it's
 * missing rather than fataling.
 */
function wk_run() {

	// Elementor not active at all.
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'wk_admin_notice_missing_elementor' );
		return;
	}

	// Elementor is active but too old.
	if ( ! version_compare( ELEMENTOR_VERSION, WK_MIN_ELEMENTOR_VERSION, '>=' ) ) {
		add_action( 'admin_notices', 'wk_admin_notice_minimum_elementor_version' );
		return;
	}

	// PHP too old.
	if ( version_compare( PHP_VERSION, WK_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'wk_admin_notice_minimum_php_version' );
		return;
	}

	require_once WK_PATH . 'includes/class-wk-plugin.php';

	\WebKit\Plugin::instance();
}
add_action( 'plugins_loaded', 'wk_run' );

/**
 * Admin notice: Elementor missing entirely.
 */
function wk_admin_notice_missing_elementor() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
		esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'web-kit' ),
		'<strong>' . esc_html__( 'Web Kit', 'web-kit' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor', 'web-kit' ) . '</strong>'
	);

	printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Admin notice: Elementor active but below minimum required version.
 */
function wk_admin_notice_minimum_elementor_version() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required version */
		esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'web-kit' ),
		'<strong>' . esc_html__( 'Web Kit', 'web-kit' ) . '</strong>',
		'<strong>' . esc_html__( 'Elementor', 'web-kit' ) . '</strong>',
		WK_MIN_ELEMENTOR_VERSION
	);

	printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Admin notice: PHP below minimum required version.
 */
function wk_admin_notice_minimum_php_version() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required version */
		esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'web-kit' ),
		'<strong>' . esc_html__( 'Web Kit', 'web-kit' ) . '</strong>',
		'<strong>' . esc_html__( 'PHP', 'web-kit' ) . '</strong>',
		WK_MIN_PHP_VERSION
	);

	printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
