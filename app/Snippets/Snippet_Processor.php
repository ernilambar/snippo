<?php
/**
 * Snippet_Processor
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\Snippets;

/**
 * Snippet_Processor class.
 *
 * @since 1.0.0
 */
class Snippet_Processor {

	/**
	 * Process categories array to standard format.
	 *
	 * @since 1.0.0
	 *
	 * @param array $categories Raw categories array.
	 * @param array $category_definitions Category definitions.
	 * @return array Processed categories array.
	 */
	public static function process_categories( array $categories, array $category_definitions = [] ): array {
		$processed_categories = [];

		foreach ( $categories as $category ) {
			$category_slug = is_string( $category ) ? $category : ( $category['slug'] ?? '' );

			if ( ! empty( $category_slug ) ) {
				$new_cat_item = [
					'slug'  => $category_slug,
					'title' => Snippet_Utils::generate_title_from_slug( $category_slug ),
				];

				$category_definition = $category_definitions[ $category_slug ] ?? [];
				if ( isset( $category_definition['color'] ) ) {
					$new_cat_item['color'] = $category_definition['color'];
				}

				$processed_categories[] = $new_cat_item;
			}
		}

		return $processed_categories;
	}

	/**
	 * Process fields array to standard format.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Raw fields array.
	 * @return array Processed fields array.
	 */
	public static function process_fields( array $fields ): array {
		$processed_fields = [];
		$field_names      = [];

		foreach ( $fields as $field ) {
			if ( is_array( $field ) && isset( $field['name'] ) ) {
				$field_name = $field['name'];

				if ( in_array( $field_name, $field_names, true ) ) {
					// Skip duplicate field names.
					continue;
				}

				$field_names[] = $field_name;

				// Generate label from name if not provided.
				if ( ! isset( $field['label'] ) ) {
					$field['label'] = Snippet_Utils::generate_title_from_slug( $field_name );
				}

				$processed_fields[] = $field;
			}
		}

		return $processed_fields;
	}

	/**
	 * Process snippet configuration to standard format.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Raw snippet configuration.
	 * @param array $category_definitions Category definitions.
	 * @return array Processed snippet configuration.
	 */
	public static function process_snippet_config( array $config, array $category_definitions = [] ): array {
		$processed_config = $config;

		// Process fields if present.
		if ( ! empty( $config['fields'] ) && is_array( $config['fields'] ) ) {
			$processed_config['fields'] = self::process_fields( $config['fields'] );
		}

		// Process categories if present.
		if ( ! empty( $config['categories'] ) && is_array( $config['categories'] ) ) {
			$processed_config['categories'] = self::process_categories( $config['categories'], $category_definitions );
		}

		return $processed_config;
	}
}
