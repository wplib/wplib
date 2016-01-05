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
		WPLib::register_helper( __CLASS__ );

	}

	/**
	 * Is the Front Page configured to display a $post_type='page'?
	 *
	 * @param int $page_id
	 *
	 * @return bool
	 */
	static function is_page( $page_id ) {

		return $page = get_post( $page_id ) ? WPLib_Page::POST_TYPE === $page->post_type : false;

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

			$err_msg = __( "%s() cannot be called before WordPress' get_current_screen() is loaded.", 'wplib' );
			WPLib::trigger_error( $err_msg, __METHOD__, E_USER_ERROR );

			$current_screen = null;

		} else if ( is_null( $current_screen = get_current_screen() ) ) {
			/*
			 * set_current_screen() has to be called before
			 * get_current_screen() will return a non-null value.
			 */
			set_current_screen();
			$current_screen = get_current_screen();
		}

		return $current_screen;

	}

}
_WPLib_WP_Helpers::on_load();
