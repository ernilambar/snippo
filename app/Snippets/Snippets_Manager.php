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
				if ( is_file( $path ) && preg_match( '/^(.*)\.yml$/', $file, $matches ) ) {
					$slug = $matches[1];
					try {
						$meta = Yaml::parseFile( $path );
					} catch ( \Exception $e ) {
						$meta = [];
					}
					// Always expect fields as an array of field definitions.
					$fields      = [];
					$field_names = [];
					if ( ! empty( $meta['fields'] ) && is_array( $meta['fields'] ) ) {
						foreach ( $meta['fields'] as $field ) {
							if ( is_array( $field ) && isset( $field['name'] ) ) {
								$field_name = $field['name'];
								if ( in_array( $field_name, $field_names, true ) ) {
									// Skip duplicate field names.
									continue;
								}
								$field_names[] = $field_name;

								// Generate label from name if not provided.
								if ( ! isset( $field['label'] ) ) {
									$field['label'] = $this->generate_title_from_slug( $field_name );
								}

								$fields[] = $field;
							}
						}
					}

					// Always expect categories as an array.
					$categories = [];

					if ( ! empty( $meta['categories'] ) && is_array( $meta['categories'] ) ) {
						foreach ( $meta['categories'] as $category ) {
							$category_slug = is_string( $category ) ? $category : ( $category['slug'] ?? '' );
							if ( ! empty( $category_slug ) ) {
								$new_cat_item = [
									'slug'  => $category_slug,
									'title' => $this->generate_title_from_slug( $category_slug ),
								];

								$category_definition = $this->get_category_definition( $category_slug );

								if ( $category_definition && isset( $category_definition['color'] ) ) {
									$new_cat_item['color'] = $category_definition['color'];
								}

								$categories[] = $new_cat_item;
							}
						}
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
					 * @param array  $meta         The original YAML meta data.
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
		 * @since 1.0.0
		 *
		 * This filter runs after all individual snippets have been processed.
		 *
		 * @param array $snippets All parsed snippets indexed by slug.
		 */
		$this->snippets = apply_filters( 'snippo_snippets', $this->snippets );
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
	 * Generate readable title from slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug to convert.
	 *
	 * @return string Readable title.
	 */
	private function generate_title_from_slug( string $slug ): string {
		return ucwords( str_replace( [ '-', '_' ], ' ', $slug ) );
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
									'title'    => $this->generate_title_from_slug( $category_slug ),
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
