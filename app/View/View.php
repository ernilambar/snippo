<?php
/**
 * View
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\View;

use Exception;

/**
 * View class.
 *
 * @since 1.0.0
 */
class View {

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Template name.
	 * @param array  $data Template data.
	 *
	 * @throws Exception Throws exception if template file do not exists.
	 */
	public static function render( string $name, array $data = [] ) {
		$filename = $name . '.php';

		$file = SNIPPO_DIR . '/templates/' . $filename;

		if ( ! file_exists( $file ) ) {
			throw new Exception( esc_html( "View \"{$name}\" found." ) );
		}

		include $file;
	}
}
