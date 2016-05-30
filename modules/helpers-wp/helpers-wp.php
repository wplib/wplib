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
	 * @var bool Get's set if doing XMLRPC.
	 */
	private static $_doing_xmlrpc = false;

	/**
	 *
	 */
	static function on_load() {

		/**
		 * Register this class as a helper for WPLib.
		 */
		WPLib::register_helper( __CLASS__ );

		self::add_class_action( 'xmlrpc_call' );

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
	 * If runmode is development or SCRIPT_DEBUG
	 *
	 * @return string
	 *
	 * @future https://github.com/wplib/wplib/issues/7
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026829
	 */
	static function is_script_debug() {

		return WPLib::is_development() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );

	}

	/**
	 * @return bool
	 */
	static function is_wp_debug() {

		return defined( 'WP_DEBUG' ) && WP_DEBUG;

	}

	/**
	 * Capture status of DOING_XMLRPC
	 */
	static function _xmlrpc_call() {

		self::$_doing_xmlrpc = true;

	}

	/**
	 * @return bool
	 */
	static function doing_xmlrpc() {

		return self::$_doing_xmlrpc;

	}

	/**
	 * @return bool
	 */
	static function doing_ajax() {

		return defined( 'DOING_AJAX' ) && DOING_AJAX;

	}

	/**
	 * @return bool
	 */
	static function doing_cron() {

		return defined( 'DOING_CRON' ) && DOING_CRON;

	}

	/**
	 * @return bool
	 */
	static function doing_autosave() {

		return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;

	}

	/**
	 * @return bool
	 */
	static function do_log_errors() {

		return defined( 'WPLIB_LOG_ERRORS' ) && WPLIB_LOG_ERRORS;

	}

	/**
	 * Return if WPLIB_TEMPLATE_GLOBAL_VARS was set to true
	 *
	 * Setting WPLIB_TEMPLATE_GLOBAL_VARS to false will cause WPLib to extract $GLOBALS before loading the WP template which normally happens in
	 * /wp-include/template-loader.php but WPLib hijacks that.
	 *
	 * @return bool
	 */
	static function use_template_global_vars() {

		return ! defined( 'WPLIB_TEMPLATE_GLOBAL_VARS' ) || ! WPLIB_TEMPLATE_GLOBAL_VARS;

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

	/**
	 * @TODO Move these below to a PHP Helpers Module.
	 */
	
	/**
	 * @param string $string
	 * @param bool|true $lowercase
	 *
	 * @return string
	 */
	static function dashify( $string, $lowercase = true ) {

		$string = str_replace( array( '_', ' ' ), '-', $string );
		if ( $lowercase ) {
			$string = strtolower( $string );
		}
		return $string;

	}

	/**
	 * Emits one or more HTTP headers to the output stream
	 *
	 * @param string|array $headers
	 */
	static function emit_headers( $headers ) {

		if ( ! is_array( $headers ) ) {
			$headers = array( $headers );
		}

		array_map( 'header', $headers );

	}

	/**
	 * Runs file_put_contents()
	 *
	 * @param string $filepath
	 * @param string $contents
	 * @return bool
	 */
	static function put_contents( $filepath, $contents ) {

		$permissions = ( fileperms( $filepath ) & 0777 );
		chmod( $filepath, 0777 );
		$result = file_put_contents( $filepath, $contents );
		chmod( $filepath, $permissions );

		return $result;

	}

}
_WPLib_WP_Helpers::on_load();
