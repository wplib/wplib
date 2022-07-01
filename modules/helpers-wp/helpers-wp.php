<?php

/**
 * Class _WPLib_WP_Helpers
 *
 * Provide a WordPress Helper class for WPLib.
 *
 * Basically, this class contributes static methods to the WPLib class using magic methods.
 *
 */
class _WPLib_WP_Helpers extends WPLib_Helper_Base {

	/**
	 *
	 */
	static function on_load() {

		/**
		 * Register this class as a helper for WPLib.
		 */
		self::register_helper( __CLASS__, 'WPLib' );

	}

	/**
	 * Is the Front Page configured to display a $post_type='page'?
	 *
	 * @param int $page_id
	 *
	 * @return bool
	 */
	static function is_page( $page_id ) {

		$page = get_post( $page_id );

		return $page && WPLib_Page::POST_TYPE === $page->post_type;

	}

	/**
	 * Takes a filepath and potentially returns a relative path (prefixed with '~/'), if $filepath begins with ABSPATH.
	 *
	 * @param string $filepath
	 *
	 * @return string
	 */
	static function maybe_make_abspath_relative( $filepath ) {

		return preg_replace( '#^' . preg_quote( ABSPATH ) . '(.*)$#', "~/$1", $filepath );

	}

	/**
	 * Returns the "current screen."
	 *
	 * Same as WordPress' get_current_screen() but will call set_current_screen() if is null.
	 *
	 * @since 0.9.9
	 * @return WP_Screen
	 */
	static function current_screen() {

		if ( ! function_exists( 'get_current_screen' ) ) {
			require( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
			require( ABSPATH . 'wp-admin/includes/screen.php' );
		}

		if ( is_null( $current_screen = get_current_screen() ) ) {
			/*
			 * set_current_screen() has to be called before
			 * get_current_screen() will return a non-null value.
			 */
			set_current_screen( trim( $_SERVER['REQUEST_URI'], '/' ) );
			$current_screen = get_current_screen();
		}

		return $current_screen;

	}

	/**
	 * Return true if a URL query var is not empty and optionally matches an expected value.
	 *
	 * @param string $var_name
	 * @param string|bool $expected_value
	 *
	 * @return bool
	 */
	static function has_query_var( $var_name, $expected_value = null ) {
		global $wp_the_query;

		$query_var_value = $wp_the_query->get( $var_name, null );

		return is_null( $expected_value )
			? ! empty( $query_var_value )
			: $query_var_value === $expected_value;

	}

}
_WPLib_WP_Helpers::on_load();
