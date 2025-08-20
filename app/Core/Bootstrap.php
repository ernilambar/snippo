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
		new Snippets_Page();
		new Snippets_API();

		add_filter( 'linkit_menu_bar_pages', [ $this, 'add_menu_pages' ] );
	}

	/**
	 * Add support for Linkit menu pages.
	 *
	 * @since 1.0.0
	 *
	 * @param array $pages The pages array.
	 * @return array The modified pages array.
	 */
	public function add_menu_pages( $pages ) {
		return array_merge( $pages, [ 'dashboard_page_snippo-snippets' ] );
	}
}
