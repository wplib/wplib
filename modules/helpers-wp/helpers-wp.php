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

		return $page = get_post( $page_id ) ? WPLib_Page::POST_TYPE == $page->post_type : false;

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

}
_WPLib_WP_Helpers::on_load();
