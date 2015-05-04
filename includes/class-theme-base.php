<?php

/**
 * Class WPLib_Theme_Base
 */
abstract class WPLib_Theme_Base extends WPLib {

	/**
	 * @var static
	 */
	private static $_theme;

	/**
	 * Sets hooks required by all themes.
	 */
	static function on_load() {

		self::add_class_action( 'wp_enqueue_scripts', 0 );
		self::add_class_action( 'template_include', 999 );

	}

	/**
	 * Hijack `template_include` so that we can ensure a $theme variable is defined.
	 *
	 * @param string $template;
	 *
	 * @return static
	 */
	static function _template_include_999( $template ) {

		if ( ! $template ) {

			$message = __( '<p>No template file found. You may have deleted the current theme or renamed the theme directory?</p>' .
				'<p>If you are a site admin <a href="%s">click here</a> to verify and possibly correct.</p>', 'wplib' );

			echo sprintf( $message, site_url( '/wp-admin/themes.php') );

		} else {

			global $theme;

			/*
			 * Make $theme available inside the template.
			 */
			$theme = self::instance();

			include( $template );

		}
		return false;

	}

	/**
	 * Create an instance of get_called_class()
	 *
	 * @return static
	 */
	static function instance() {

		if ( ! isset( static::$_theme ) ) {

			/**
			 * @maybe Cache this information? But we can wait to see if it is slow enough to matter.
			 */
			foreach( get_declared_classes() as $class_name ) {

				if ( is_subclass_of( $class_name, __CLASS__ )  ) {

					/*
					 * Will create instance of FIRST class found that subclasses WPLib_Theme_Base.
					 * That means sites should ONLY have ONE subclass of WPLib_Theme_Base.
					 */
					self::$_theme = new $class_name();
					break;

				}

			}

		}

		return self::$_theme;

	}

	/**
	 * Theme method for setting a theme isntance for unit test mocking.
	 *
	 * @param $theme
	 *
	 * @return mixed
	 */
	static function set_mock_theme( $theme ) {

		return static::$_theme = $theme;

	}

	/**
	 * Creates a JS variable WPLib.ajaxurl
	 *
	 *  Priority 0 ONLY so that this static function does not conflict with the instance method in child classes
	 */
	static function _wp_enqueue_scripts_0() {

		wp_localize_script( 'wplib-script', 'WPLib', array(

			'ajaxurl' => admin_url( 'admin-ajax.php' ),

		));

	}

	/**
	 * Return the site name as configured.
	 *
	 * @return string|void
	 */
	function sitename() {

		return get_bloginfo( 'name' );

	}

	/**
	 * Output the attributes for the HTML <html> element
	 */
	function the_html_attributes() {

		language_attributes();

	}

	/**
	 * Output the HTML element <meta charset="...">.
	 *
	 */
	function the_meta_charset_html() {

		echo '<meta charset="' . get_bloginfo( 'charset' ) . '">';

	}

	/**
	 * Output the content for the wp_head() method;
	 */
	function the_wp_head_html() {

		wp_head();

	}

	/**
	 * Output the attributes for the HTML <body> element
	 *
	 */
	function the_body_attributes() {

		body_class();

	}

	/**
	 * Output the HTML element <title>...</title>.
	 *
	 */
	function the_page_title_html() {

		echo'<title>';
		wp_title( '' );
		echo'</title>';

	}

	function the_header_html( $name = null ) {

		/**
		 * This is usually the first method to call in a template so...
		 * if ABSPATH is not defined it was called directly. This
		 * should only happen when someone is hacking.
		 */
		if ( ! defined( 'ABSPATH' ) ) {

			header("HTTP/1.0 404 Not Found");
			echo '<h1>Not Found</h1>';
			echo '<p>Requested URL not found.</p>'.
			exit;

		}

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		/*
		 * Make $theme visible inside header.php
		 */
		$wp_query->set( 'theme', self::$_theme );

		get_header( $name );

	}

	/**
	 * Generate the HTML for a nav menu.
	 *
	 * @param string $location  Defaults to 'primary' theme location.
	 * @param array $args
	 */
	static function the_menu_html( $location = 'primary', $args = array() ) {

		$args = wp_parse_args( $args, array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => false,
			'items_wrap'     => false,
		) );

		wp_nav_menu( $args );

	}


	function the_footer_html( $name = null ) {

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		/*
		 * Make $theme visible inside header.php
		 */
		$wp_query->set( 'theme', self::$_theme );

		get_footer( $name );

	}

	/**
	 * Output the HTML element <meta name="viewport" content="...">.
	 *
	 * @param array $args
	 */
	function the_meta_viewport_html( $args = array() ) {

		$args = wp_parse_args( $args );

		if ( count( $args ) ) {

			$attributes = esc_attr( implode( ',', array_map(

				function ($value, $key) {

					return "{$key}={$value}";
				},
				$args,
				array_keys( $args )

			)));

			echo "<meta name=\"viewport\" content=\"{$attributes}\" >";

		}

	}

	/**
	 * Enqueue JS or CSS.
	 *
	 * Auto generate version.
	 *
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param bool $in_footer
	 *
	 * @todo https://github.com/wplib/wplib/issues/2
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026274
	 */
	function enqueue_external( $handle, $src, $deps = array(), $in_footer = false ) {

		preg_match( '#\.(js|css)$#i', $src, $file_type );


		if ( '~' == $src[0] &&  '/' == $src[1] ) {

			/**
			 * Assume $src that start with ~/ are relative.
			 */
			$src = preg_replace( '#^(~/)#', '',  $src );

		}

		if ( ! ( $absolute = preg_match( '#^(/|https?://)#', $src ) ) ) {

			/**
			 * If relative, add stylesheet URL and DIR to the $src and $filepath.
			 */
			$src = static::get_root_url( $src );

			$filepath = static::get_root_dir( $src );

		}

		if ( ! static::is_script_debug() && ! $absolute ) {
			/**
			 * If script debug and not absolute URL
			 * then prefix extensions 'js' and 'css' with 'min.'
			 */

			$src = preg_replace( '#\.(js|css)$#i', '.min.$1', $src );

			$ver = rand( 1, 1000000 );

		} else {

			if ( empty( $filepath ) ) {

				$ver = false;

			} else {

				if ( $ver = self::cache_get( $cache_key = "external[{$filepath}]" ) ) {
					$ver = md5_file( $filepath );
					self::cache_get( $cache_key, $ver );
				}

			}

		}

		switch ( strtolower( $file_type[1] ) ) {

			case 'js':
				wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
				break;

			case 'css':
				wp_enqueue_style( $handle, $src, $deps, $ver, $in_footer );
				break;
		}

	}

}
WPLib_Theme_Base::on_load();
