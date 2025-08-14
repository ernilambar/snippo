<?php
/**
 * Snippet_Validator
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\Snippets;

/**
 * Snippet_Validator class.
 *
 * @since 1.0.0
 */
class Snippet_Validator {

	/**
	 * Validate snippet configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Snippet configuration array.
	 * @return array Validation result with 'valid' boolean and 'errors' array.
	 */
	public static function validate_snippet_config( array $config ): array {
		$errors = [];

		// Validate required fields.
		if ( empty( $config['title'] ) ) {
			$errors[] = 'Title is required.';
		}

		if ( empty( $config['template'] ) ) {
			$errors[] = 'Template is required.';
		}

		// Validate categories.
		if ( ! empty( $config['categories'] ) ) {
			$category_validation = self::validate_categories( $config['categories'] );
			if ( ! $category_validation['valid'] ) {
				$errors = array_merge( $errors, $category_validation['errors'] );
			}
		}

		// Validate fields.
		if ( ! empty( $config['fields'] ) ) {
			$field_validation = self::validate_fields( $config['fields'] );
			if ( ! $field_validation['valid'] ) {
				$errors = array_merge( $errors, $field_validation['errors'] );
			}
		}

		return [
			'valid'  => empty( $errors ),
			'errors' => $errors,
		];
	}

	/**
	 * Validate categories array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $categories Categories array.
	 * @return array Validation result.
	 */
	public static function validate_categories( array $categories ): array {
		$errors = [];

		if ( ! is_array( $categories ) ) {
			$errors[] = 'Categories must be an array.';
			return [
				'valid'  => false,
				'errors' => $errors,
			];
		}

		foreach ( $categories as $index => $category ) {
			if ( is_string( $category ) ) {
				if ( empty( $category ) ) {
					$errors[] = "Category at index {$index} cannot be empty.";
				}
			} elseif ( is_array( $category ) ) {
				if ( empty( $category['slug'] ) ) {
					$errors[] = "Category at index {$index} must have a slug.";
				}
			} else {
				$errors[] = "Category at index {$index} must be a string or array.";
			}
		}

		return [
			'valid'  => empty( $errors ),
			'errors' => $errors,
		];
	}

	/**
	 * Validate fields array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Fields array.
	 * @return array Validation result.
	 */
	public static function validate_fields( array $fields ): array {
		$errors      = [];
		$field_names = [];

		if ( ! is_array( $fields ) ) {
			$errors[] = 'Fields must be an array.';
			return [
				'valid'  => false,
				'errors' => $errors,
			];
		}

		foreach ( $fields as $index => $field ) {
			if ( ! is_array( $field ) ) {
				$errors[] = "Field at index {$index} must be an array.";
				continue;
			}

			if ( empty( $field['name'] ) ) {
				$errors[] = "Field at index {$index} must have a name.";
				continue;
			}

			$field_name = $field['name'];
			if ( in_array( $field_name, $field_names, true ) ) {
				$errors[] = "Duplicate field name '{$field_name}' at index {$index}.";
				continue;
			}

			$field_names[] = $field_name;

			// Validate field type if provided.
			if ( isset( $field['type'] ) && ! self::is_valid_field_type( $field['type'] ) ) {
				$errors[] = "Invalid field type '{$field['type']}' for field '{$field_name}'.";
			}
		}

		return [
			'valid'  => empty( $errors ),
			'errors' => $errors,
		];
	}

	/**
	 * Check if field type is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Field type.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_field_type( string $type ): bool {
		$valid_types = [
			'text',
			'textarea',
			'select',
			'checkbox',
			'radio',
			'number',
			'email',
			'url',
		];

		return in_array( $type, $valid_types, true );
	}

	/**
	 * Validate PHP configuration file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path Path to the PHP configuration file.
	 * @return array Validation result.
	 */
	public static function validate_php_config_file( string $file_path ): array {
		$errors = [];

		if ( ! file_exists( $file_path ) ) {
			$errors[] = 'Configuration file not found: ' . esc_html( $file_path );
			return [
				'valid'  => false,
				'errors' => $errors,
			];
		}

		$config = include $file_path;

		if ( ! is_array( $config ) ) {
			$errors[] = 'Configuration file must return an array: ' . esc_html( $file_path );
			return [
				'valid'  => false,
				'errors' => $errors,
			];
		}

		// Validate the configuration structure.
		$config_validation = self::validate_snippet_config( $config );
		if ( ! $config_validation['valid'] ) {
			$errors = array_merge( $errors, $config_validation['errors'] );
		}

		return [
			'valid'  => empty( $errors ),
			'errors' => $errors,
		];
	}
}
