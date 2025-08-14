# Snippo

A WordPress plugin for managing and rendering code snippets with dynamic content.

## Features

- **Snippet Management**: Create and organize snippets with categories
- **Dynamic Content**: Use placeholders in templates for dynamic content
- **Field Types**: Support for various field types (text, textarea, select, etc.)
- **REST API**: Built-in REST API for snippet operations
- **PHP Configuration**: Native PHP configuration files for better performance

## Installation

1. Upload the plugin to `/wp-content/plugins/snippo/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access snippets via the admin menu

## Snippet Configuration

Snippets are configured using PHP files in the `snippets/` directory. Each file should return an array with the snippet configuration.

### Basic Structure

```php
<?php
/**
 * Snippet Name Configuration
 *
 * @package Snippo
 */

return [
    'title'      => 'Snippet Title',
    'categories' => ['category-slug'],
    'fields'     => [
        [
            'name'  => 'field_name',
            'label' => 'Field Label',
            'type'  => 'text',
        ],
    ],
    'template'   => 'Your template with {{field_name}} placeholders.',
];
```

### Configuration Options

- **`title`**: Display title of the snippet
- **`categories`**: Array of category slugs or objects with `slug` and optional `color`
- **`fields`**: Array of field definitions (optional)
- **`template`**: Content with `{{field_name}}` placeholders

### Example

```php
<?php
return [
    'title'      => 'Welcome Message',
    'categories' => ['greetings', 'beginner'],
    'fields'     => [
        [
            'name'  => 'user_name',
            'label' => 'User Name',
            'type'  => 'text',
        ],
    ],
    'template'   => "Hello {{user_name}}!
<br>
Welcome to our site!",
];
```

## API Usage

### Get All Snippets
```
GET /wp-json/snippo/v1/snippets
```

### Render Snippet
```
POST /wp-json/snippo/v1/snippets/render
Content-Type: application/json

{
    "key": "snippet-slug",
    "data": {
        "field_name": "value"
    }
}
```

## Development

### Requirements
- PHP 7.4+
- WordPress 6.6+

### Setup
```bash
composer install
pnpm install
pnpm run build
```

### Code Quality
```bash
composer run lint
composer run format
```

## License

GPL v2 or later
