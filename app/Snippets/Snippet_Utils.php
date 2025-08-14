<?php
/**
 * Snippet_Utils
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\Snippets;

/**
 * Snippet_Utils class.
 *
 * @since 1.0.0
 */
class Snippet_Utils {

	/**
	 * Generate readable title from slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug to convert.
	 * @return string Readable title.
	 */
	public static function generate_title_from_slug( string $slug ): string {
		return ucwords( str_replace( [ '-', '_' ], ' ', $slug ) );
	}
}
