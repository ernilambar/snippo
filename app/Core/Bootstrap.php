<?php
/**
 * Bootstrap
 *
 * @package Snippo
 */

namespace Nilambar\Snippo\Core;

use Nilambar\Snippo\Snippets\Snippets_API;
use Nilambar\Snippo\Snippets\Snippets_Page;

/**
 * Bootstrap class.
 *
 * @since 1.0.0
 */
class Bootstrap {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'plugin_action_links_' . SNIPPO_BASE_FILENAME, [ $this, 'customize_action_links' ] );

		new Snippets_Page();
		new Snippets_API();
	}

	/**
	 * Customize plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Action links.
	 * @return array Modified action links.
	 */
	public function customize_action_links( $actions ) {
		$url = add_query_arg( [ 'page' => 'snippo-snippets' ], admin_url( 'index.php' ) );

		$actions = [ 'snippets' => '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Snippets', 'snippo' ) . '</a>' ] + $actions;

		return $actions;
	}
}
