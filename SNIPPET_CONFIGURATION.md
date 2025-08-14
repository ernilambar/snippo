# Snippet Configuration Format

Snippo now uses PHP configuration files instead of YAML files for better performance and native PHP integration.

## File Format

Snippet configuration files should be placed in the `snippets/` directory with a `.php` extension. Each file should return an array with the snippet configuration.

## Configuration Structure

```php
<?php
/**
 * Snippet Name Configuration
 *
 * @package Snippo
 */

return [
    'title'      => 'Snippet Title',
    'categories' => [
        'category-slug',
        // or with color
        [
            'slug'  => 'category-slug',
            'color' => '#ff0000',
        ],
    ],
    'fields'     => [
        [
            'name'  => 'field_name',
            'label' => 'Field Label',
            'type'  => 'text', // or other field types
        ],
    ],
    'template'   => 'Your template content with {{field_name}} placeholders.',
];
```

## Configuration Options

### `title` (string)
The display title of the snippet.

### `categories` (array)
Array of category slugs or category objects. Categories can be:
- Simple strings: `'category-slug'`
- Objects with additional properties: `['slug' => 'category-slug', 'color' => '#ff0000']`

### `fields` (array, optional)
Array of field definitions for the snippet. Each field should have:
- `name`: The field identifier (used in template placeholders)
- `label`: Human-readable label (auto-generated from name if not provided)
- `type`: Field type (text, textarea, select, etc.)

### `template` (string)
The template content with placeholders in the format `{{field_name}}`. These will be replaced with actual values when the snippet is rendered.

## Example

```php
<?php
/**
 * Welcome Message Configuration
 *
 * @package Snippo
 */

return [
    'title'      => 'Welcome Message',
    'categories' => [
        'greetings',
        'beginner',
    ],
    'fields'     => [
        [
            'name'  => 'user_name',
            'label' => 'User Name',
            'type'  => 'text',
        ],
        [
            'name'  => 'time_of_day',
            'label' => 'Time of Day',
            'type'  => 'select',
        ],
    ],
    'template'   => "Hello {{user_name}}!\n\nGood {{time_of_day}} and welcome to our site.",
];
```

## Migration from YAML

To migrate existing YAML files to PHP format:

1. Change the file extension from `.yml` to `.php`
2. Wrap the configuration in a PHP array structure
3. Add the `return` statement
4. Add proper PHP comments and package declaration

The API and functionality remain exactly the same - only the configuration file format has changed.
