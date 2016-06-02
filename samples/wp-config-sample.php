<?php
/**
 * Sample wp-config.php to use in your root www directory or above.
 *
 * This calls wp-config-local.php and has many intelligent defaults
 *
 * @author The WPLib Team
 */

$wplib = new stdClass();

/*
 * Search for a wp-config-local.php in the current and parent directories for config overrides
 */
if ( file_exists( __DIR__ . '/wp-config-local.php' ) ) {
	require( __DIR__ . '/wp-config-local.php' );
} else if ( file_exists( dirname( __DIR__ ) . '/wp-config-local.php' ) ) {
	require( dirname( __DIR__ ) . '/wp-config-local.php' );
}

/**
 * Ensure we have $wplib->IS_DEVELOPMENT && $wplib->IS_PRODUCTION so we can use below
 */
if ( ! isset( $wplib->IS_DEVELOPMENT ) && ! isset( $wplib->IS_PRODUCTION ) ) {
	$wplib->IS_DEVELOPMENT = false;
	$wplib->IS_PRODUCTION = true;
} else if ( ! isset( $wplib->IS_DEVELOPMENT ) ) {
	$wplib->IS_DEVELOPMENT = ! $wplib->IS_PRODUCTION;
} else {
	$wplib->IS_PRODUCTION = ! $wplib->IS_DEVELOPMENT;
}	

if ( $wplib->IS_DEVELOPMENT ) {

	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ALL );

}

if ( ! isset( $wplib->SITE_DOMAIN ) ) {
	$wplib->SITE_DOMAIN = $_SERVER['HTTP_HOST'];
}

if ( ! isset( $wplib->REQUEST_PROTOCOL ) ) {
	$wplib->REQUEST_PROTOCOL = 'http';
}

if ( ! defined( 'WP_HOME' ) ) {
	define( 'WP_HOME', "{$wplib->REQUEST_PROTOCOL}://{$wplib->SITE_DOMAIN}" );
}

if ( ! defined( 'WP_SITEURL' ) ) {
	define( 'WP_SITEURL', WP_HOME . '/wp' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', __DIR__ . '/content' );
}

if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', WP_HOME . '/content' );
}

if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', 'wordpress' );
}

if ( ! defined( 'DB_USER' ) ) {
	define( 'DB_USER', 'wordpress' );
}

if ( ! defined( 'DB_PASSWORD' ) ) {
	define( 'DB_PASSWORD', 'wordpress' );
}

if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', 'localhost' );
}

if ( ! defined( 'DB_CHARSET' ) ) {
	define( 'DB_CHARSET', 'utf8' );
}

if ( ! defined( 'DB_COLLATE' ) ) {
	define( 'DB_COLLATE', '' );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', $wplib->IS_DEVELOPMENT );
}

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

if ( ! isset( $table_prefix ) ) {
	$table_prefix = 'wp_';
}

// https://api.wordpress.org/secret-key/1.1/salt/
if ( is_file( __DIR__ . '/salt.php' ) ) {
	require( __DIR__ . '/salt.php' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/wp/' );
}

require_once( ABSPATH . 'wp-settings.php' );
