<?php
/**
 * Sample mu-plugin-loader.php file for dropping into {$wp_content_dir}/mu-plugins/
 *
 * Author: The WPLib Team
 */

/**
 * Autoload any Composer-installed dependencies
 */
require( dirname( WP_CONTENT_DIR ) . '/vendor/autoload.php' );

/**
 * Initialize WPLib so it can load it can load symbols
 */
WPLib::initialize();

/**
 * Load your WPLib App here. 
 * Your App class must extend WPLib_App_Base directly or indirectly.
 */
require __DIR__ . '/your-app/your-app.php';
