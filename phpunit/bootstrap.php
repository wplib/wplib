<?php
echo 'Welcome to the WPLib Test Suite' . PHP_EOL;
echo 'Version 0.1' . PHP_EOL;
echo 'Author: WPLib Team <team@wplib.org>' . PHP_EOL;

$working    = getenv( 'WPLIB_TESTS_WORKING_DIR' );
$wp_tests = getenv( 'WP_TESTS_DIR' );

if ( isset( $working ) && '' != $working ) {
    define( 'WPLIB_TESTS_WORKING_DIR', $working );
}

if (  ! empty( $wp_tests ) ) {
    define( 'WP_TESTS_DIR', $wp_tests );
}

if( ! defined( 'WPLIB_TESTS_WORKING_DIR' ) ) {
    define( 'WPLIB_TESTS_WORKING_DIR', dirname( __DIR__ ) );
}

if( ! defined( 'WP_TESTS_DIR' ) ) {
    define( 'WP_TESTS_DIR',  WPLIB_TESTS_WORKING_DIR . '/vendor/wp-tests/trunk');
}

require WP_TESTS_DIR . '/tests/phpunit/includes/functions.php';
require WP_TESTS_DIR . '/tests/phpunit/includes/bootstrap.php';

require WPLIB_TESTS_WORKING_DIR . '/vendor/wplib/tests-wplib/phpunit/framework/test-case.php';
require WPLIB_TESTS_WORKING_DIR . '/wplib.php';

require WPLIB_TESTS_WORKING_DIR . '/defines.php';

wplib_define( 'WPLib_Runmode', 'DEVELOPMENT' );
wplib_define( 'WPLib_Stability', 'EXPERIMENTAL' );