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
	}
}
