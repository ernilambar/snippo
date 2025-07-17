<?php
/**
 * Loader
 *
 * @package Snippo
 */

namespace Nilambar\Snippo\Boot;

use Nilambar\Snippo\Snippets\Snippets_API;
use Nilambar\Snippo\Snippets\Snippets_Page;

/**
 * Loader class.
 *
 * @since 1.0.0
 */
class Loader {

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
