<?php
/**
 * Snippets_Page
 *
 * @package Snippo
 */

namespace Nilambar\Snippo\Snippets;

/**
 * Snippets_Page class.
 *
 * @since 1.0.0
 */
class Snippets_Page {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_react_assets' ] );
	}

	/**
	 * Add page.
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		add_dashboard_page(
			esc_html__( 'Snippets', 'snippo' ),
			esc_html__( 'Snippets', 'snippo' ),
			'manage_options',
			'snippo-snippets',
			function () {
				echo '<div class="wrap"><h1>' . esc_html__( 'Snippets', 'snippo' ) . '</h1>';
				echo '<div id="snippo-snippets-app"></div>';
				echo '</div>';
			}
		);
	}

	/**
	 * Load app assets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Hook name.
	 */
	public function load_react_assets( $hook ) {
		if ( 'dashboard_page_snippo-snippets' !== $hook ) {
			return;
		}

		$asset_file = include SNIPPO_DIR . '/build/index.asset.php';

		wp_enqueue_script(
			'snippo-snippets-app',
			SNIPPO_URL . '/build/index.js',
			$asset_file['dependencies'],
			SNIPPO_VERSION,
			true
		);

		wp_enqueue_style(
			'snippo-snippets-app',
			SNIPPO_URL . '/build/index.css',
			[],
			SNIPPO_VERSION
		);

		wp_localize_script(
			'snippo-snippets-app',
			'SnippoObj',
			[
				'restUrl'       => 'snippo/v1/snippets',
				'restUrlRender' => 'snippo/v1/snippets/render',
				'nonce'         => wp_create_nonce( 'wp_rest' ),
			]
		);
	}
}
