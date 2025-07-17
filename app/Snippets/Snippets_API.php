<?php
/**
 * Snippets_API
 *
 * @package Snippo
 */

declare(strict_types=1);

namespace Nilambar\Snippo\Snippets;

/**
 * Snippets_API class.
 *
 * @since 1.0.0
 */
class Snippets_API {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register endoints.
	 *
	 * @since 1.0.0
	 */
	public function register_endpoints() {
		// Get all snippets.
		register_rest_route(
			'snippo/v1',
			'/snippets',
			[
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					return Snippets_Manager::get_instance()->get_snippets();
				},
			]
		);

		// Render a snippet.
		register_rest_route(
			'snippo/v1',
			'/snippets/render',
			[
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => function ( $request ) {
					$params = $request->get_json_params();
					$key    = sanitize_text_field( $params['key'] ?? '' );
					$data   = $params['data'] ?? [];
					try {
						$output = Snippets_Manager::get_instance()->render_snippet( $key, $data );
						return [ 'output' => $output ];
					} catch ( \Exception $e ) {
						return new \WP_Error( 'snippet_render_error', $e->getMessage(), [ 'status' => 400 ] );
					}
				},
			]
		);
	}
}
