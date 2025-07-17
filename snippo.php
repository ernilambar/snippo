<?php
/**
 * Plugin Name: Snippo
 * Plugin URI: https://github.com/ernilambar/snippo/
 * Description: Snippet manager.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Nilambar Sharma
 * Author URI: https://nilambar.net/
 * License: GPLv2 or later
 * Text Domain: snippo
 *
 * @package Snippo
 */

use Nilambar\Snippo\Boot\Loader;

// Define.
define( 'SNIPPO_VERSION', '1.0.0' );
define( 'SNIPPO_BASE_NAME', basename( __DIR__ ) );
define( 'SNIPPO_BASE_FILEPATH', __FILE__ );
define( 'SNIPPO_BASE_FILENAME', plugin_basename( __FILE__ ) );
define( 'SNIPPO_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'SNIPPO_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

// Include autoload.
if ( file_exists( SNIPPO_DIR . '/vendor/autoload.php' ) ) {
	require_once SNIPPO_DIR . '/vendor/autoload.php';
}

// Init.
new Loader();
