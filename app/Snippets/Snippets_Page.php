<?php
/**
 * Snippets_Page
 *
 * @package Snippo
 */

namespace Nilambar\Snippo\Snippets;

use Nilambar\Snippo\View\View;

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
		// Check if dashmate parent page exists.
		$dashmate_exists = $this->check_parent_page_exists( 'dashmate' );

		// Determine parent slug based on dashmate existence.
		$parent_slug = $dashmate_exists ? 'dashmate' : 'index.php';

		// Add as submenu under the determined parent.
		add_submenu_page(
			$parent_slug,
			esc_html__( 'Snippets', 'snippo' ),
			esc_html__( 'Snippets', 'snippo' ),
			'manage_options',
			'snippo-snippets',
			function () {
				View::render( 'app' );
			}
		);
	}

	/**
	 * Check if parent page exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $parent_slug Parent page slug.
	 * @return bool True if parent page exists, false otherwise.
	 */
	private function check_parent_page_exists( $parent_slug ) {
		global $menu, $submenu;

		// Check if parent page exists in menu.
		foreach ( $menu as $menu_item ) {
			if ( isset( $menu_item[2] ) && $parent_slug === $menu_item[2] ) {
				return true;
			}
		}

		// Check if parent page exists in submenu.
		if ( isset( $submenu[ $parent_slug ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Load app assets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Hook name.
	 */
	public function load_react_assets( $hook ) {
		$allowed_hooks = [ 'dashmate_page_snippo-snippets', 'dashboard_page_snippo-snippets' ];

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
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
				'restUrl' => rest_url( 'snippo/v1/snippets' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			]
		);
	}
}
