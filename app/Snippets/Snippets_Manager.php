<?php
/**
 * Snippets_Manager
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\Snippets;

use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Snippets_Manager.
 *
 * @since 1.0.0
 */
class Snippets_Manager {
	/**
	 * Registered snippets.
	 *
	 * @var array<string, array>
	 */
	private $snippets = [];

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->load_snippets_from_directory();
		}
		return self::$instance;
	}

	/**
	 * Load all snippets from the snippets/ directories.
	 *
	 * @since 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	private function load_snippets_from_directory(): void {
		$snippet_directories = [ SNIPPO_DIR . '/snippets/' ];

		/**
		 * Filter the list of directories to scan for snippets.
		 *
		 * @param array $snippet_directories Array of absolute directory paths.
		 */
		$snippet_directories = apply_filters( 'snippo_snippet_directories', $snippet_directories );

		if ( ! is_array( $snippet_directories ) || empty( $snippet_directories ) ) {
			return;
		}

		foreach ( $snippet_directories as $snippets_dir ) {
			if ( ! is_dir( $snippets_dir ) ) {
				continue;
			}

			$files = scandir( $snippets_dir );
			if ( false === $files ) {
				continue;
			}

			foreach ( $files as $file ) {
				if ( '.' === $file || '..' === $file ) {
					continue;
				}

				$path = $snippets_dir . $file;
				if ( is_file( $path ) && preg_match( '/^(.*)\.yml$/', $file, $matches ) ) {
					$slug = $matches[1];
					try {
						$meta = Yaml::parseFile( $path );
					} catch ( \Exception $e ) {
						$meta = [];
					}
					// Normalize fields to always be an array of field definitions.
					$fields = [];
					if ( ! empty( $meta['fields'] ) && is_array( $meta['fields'] ) ) {
						// If associative (object-style), convert to array of definitions.
						$is_assoc = array_keys( $meta['fields'] ) !== range( 0, count( $meta['fields'] ) - 1 );
						if ( $is_assoc ) {
							foreach ( $meta['fields'] as $name => $def ) {
								if ( is_array( $def ) ) {
									$fields[] = array_merge( [ 'name' => $name ], $def );
								} else {
									$fields[] = [
										'name'  => $name,
										'label' => $def,
									];
								}
							}
						} else {
							$fields = $meta['fields'];
						}
					}
					$this->snippets[ $slug ] = [
						'key'      => $slug,
						'fields'   => $fields,
						'template' => $meta['template'] ?? '',
						'meta'     => $meta,
					];
				}
			}
		}
	}

	/**
	 * Get all registered snippets.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array> List of registered snippets.
	 */
	public function get_snippets(): array {
		return $this->snippets;
	}

	/**
	 * Get snippet fields by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Snippet key.
	 *
	 * @return array|null List of fields or null if not found.
	 */
	public function get_fields( string $key ): ?array {
		return isset( $this->snippets[ $key ] ) ? $this->snippets[ $key ]['fields'] : null;
	}

	/**
	 * Render snippet output by key and data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key  Snippet key.
	 * @param array  $data Data to replace in the snippet.
	 *
	 * @return string Rendered snippet content.
	 *
	 * @throws Exception If snippet type or template is not found.
	 */
	public function render_snippet( string $key, array $data ): string {
		if ( empty( $this->snippets[ $key ] ) ) {
			throw new Exception( 'Snippet type not found.' );
		}

		$template = $this->snippets[ $key ]['template'] ?? '';

		if ( empty( $template ) ) {
			throw new Exception( 'Snippet template not found.' );
		}

		// Replace placeholders.
		$content = $template;
		foreach ( $data as $field => $value ) {
			$content = str_replace( '{{' . $field . '}}', esc_html( $value ), $content );
		}

		// Remove newlines immediately after <br> tags.
		$content = preg_replace( '/<br>\s*\n/', '<br>', $content );

		return trim( $content );
	}
}
