<?php
/**
 * Snippets_Manager
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\Snippets;

use WP_Error;

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
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function load_snippets_from_directory(): void {
		/**
		 * Filter the list of directories to scan for snippets.
		 *
		 * @param array $snippet_directories Array of absolute directory paths.
		 */
		$snippet_directories = apply_filters( 'snippo_directories', [] );

		if ( ! is_array( $snippet_directories ) || empty( $snippet_directories ) ) {
			$snippet_directories = [ SNIPPO_DIR . '/snippets/' ];
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
				if ( is_file( $path ) && preg_match( '/^(.*)\.php$/', $file, $matches ) ) {
					$slug = $matches[1];

					$meta_result = $this->load_php_config( $path );

					if ( is_wp_error( $meta_result ) ) {
						$meta = [];
					} else {
						$meta = $meta_result;
					}

					// Process fields and categories using Snippet_Processor.
					$fields     = [];
					$categories = [];
					if ( ! empty( $meta['fields'] ) && is_array( $meta['fields'] ) ) {
						$fields = Snippet_Processor::process_fields( $meta['fields'] );
					}
					if ( ! empty( $meta['categories'] ) && is_array( $meta['categories'] ) ) {
						$category_definitions = $this->get_all_category_definitions();
						$categories           = Snippet_Processor::process_categories( $meta['categories'], $category_definitions );
					}

					$snippet_data = [
						'key'        => $slug,
						'fields'     => $fields,
						'template'   => $meta['template'] ?? '',
						'categories' => $categories,
						'meta'       => $meta,
					];

					/**
					 * Filter to customize parsed snippet.
					 *
					 * @since 1.0.0
					 *
					 * @param array  $snippet_data The parsed snippet data.
					 * @param string $slug         The snippet slug.
					 * @param array  $meta         The original PHP meta data.
					 * @param string $path         The file path of the snippet.
					 */
					$snippet_data = apply_filters( 'snippo_snippet', $snippet_data, $slug, $meta, $path );

					$this->snippets[ $slug ] = $snippet_data;
				}
			}
		}

		/**
		 * Filter to customize all parsed snippets.
		 *
		 * This filter runs after all individual snippets have been processed.
		 *
		 * @param array $snippets All parsed snippets indexed by slug.
		 */
		$this->snippets = apply_filters( 'snippo_snippets', $this->snippets );
	}

	/**
	 * Load PHP configuration file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path Path to the PHP configuration file.
	 * @return array|WP_Error Configuration data or WP_Error on failure.
	 */
	private function load_php_config( string $file_path ) {
		$validation = Snippet_Validator::validate_php_config_file( $file_path );

		if ( ! $validation['valid'] ) {
			return new WP_Error( 'config_validation_failed', 'Configuration file validation failed: ' . esc_html( implode( ', ', $validation['errors'] ) ) );
		}

		return include $file_path;
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
	 * Get snippet categories by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Snippet key.
	 *
	 * @return array List of category data or empty array if not found.
	 */
	public function get_snippet_categories( string $key ): array {
		return isset( $this->snippets[ $key ] ) ? $this->snippets[ $key ]['categories'] : [];
	}

	/**
	 * Get all available categories.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array> List of categories with their snippets.
	 */
	public function get_categories(): array {
		$categories = [];

		$category_definitions = $this->get_all_category_definitions();

		foreach ( $this->snippets as $key => $snippet ) {
			if ( ! empty( $snippet['categories'] ) && is_array( $snippet['categories'] ) ) {
				foreach ( $snippet['categories'] as $category ) {
					if ( is_array( $category ) && isset( $category['slug'] ) ) {
						$category_slug = $category['slug'];
						$definition    = $category_definitions[ $category_slug ] ?? [];

						if ( ! isset( $categories[ $category_slug ] ) ) {
							$categories[ $category_slug ] = array_merge(
								[
									'title'    => Snippet_Utils::generate_title_from_slug( $category_slug ),
									'snippets' => [],
								],
								$definition
							);
						}

						$categories[ $category_slug ]['snippets'][ $key ] = $snippet;
					}
				}
			}
		}

		return $categories;
	}

	/**
	 * Get snippets by category slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $category_slug Category slug.
	 *
	 * @return array<string, array> List of snippets in the category.
	 */
	public function get_snippets_by_category( string $category_slug ): array {
		$categories = $this->get_categories();
		return isset( $categories[ $category_slug ] ) ? $categories[ $category_slug ]['snippets'] : [];
	}

	/**
	 * Check if a snippet belongs to a specific category.
	 *
	 * @since 1.0.0
	 *
	 * @param string $snippet_key Snippet key.
	 * @param string $category_slug Category slug.
	 *
	 * @return bool True if snippet belongs to the category.
	 */
	public function snippet_has_category( string $snippet_key, string $category_slug ): bool {
		if ( ! isset( $this->snippets[ $snippet_key ] ) ) {
			return false;
		}

		$snippet_categories = $this->snippets[ $snippet_key ]['categories'];
		foreach ( $snippet_categories as $category ) {
			if ( isset( $category['slug'] ) && $category['slug'] === $category_slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all category definitions from plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array> List of all category definitions.
	 */
	public function get_all_category_definitions(): array {
		/**
		 * Filter to allow plugins to register categories.
		 *
		 * @param array $categories Array of category definitions.
		 */
		return apply_filters( 'snippo_categories', [] );
	}

	/**
	 * Get a specific category definition.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Category slug.
	 *
	 * @return array|null Category definition or null if not found.
	 */
	public function get_category_definition( string $slug ): ?array {
		$all_definitions = $this->get_all_category_definitions();

		return $all_definitions[ $slug ] ?? null;
	}

	/**
	 * Render snippet output by key and data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key  Snippet key.
	 * @param array  $data Data to replace in the snippet.
	 *
	 * @return string|WP_Error Rendered snippet content or WP_Error on failure.
	 */
	public function render_snippet( string $key, array $data ) {
		if ( empty( $this->snippets[ $key ] ) ) {
			return new WP_Error( 'snippet_not_found', 'Snippet type not found.' );
		}

		$template = $this->snippets[ $key ]['template'] ?? '';

		if ( empty( $template ) ) {
			return new WP_Error( 'template_not_found', 'Snippet template not found.' );
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
