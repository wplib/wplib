<?php

/**
 * Class WPLib_Theme
 *
 * Provide support for WordPress themes
 *
 */
class WPLib_Theme extends WPLib_Module_Base {

	/**
	 * Sets hooks required by all themes.
	 */
	static function on_load() {

		/**
		 * Creates a JS variable WPLib.ajaxurl
		 */
		self::add_class_action( 'wp_enqueue_scripts', 0 );

		/**
		 * Hijack `template_include` so that we can ensure a $theme variable is defined.
		 */
		self::add_class_action( 'template_include', 999 );

		/**
		 * Adds any classes passed to $theme->set_body_class() to the classes that will be displayed in <body class="...">
		 */
		self::add_class_filter( 'body_class' );

	}

	/**
	 * Adds any classes passed to $theme->set_body_class() to the classes that will be displayed in <body class="...">
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	static function _body_class( $classes ) {

		if ( $body_class = WPLib::theme()->body_class() ) {

			if ( is_array( $body_class ) ) {

				$classes = array_unique( $body_class + array_map( 'esc_attr', $classes ) );

			} else if ( is_string( $body_class ) ) {

				$classes[] = esc_attr( $body_class );
			}

		}

		return $classes;

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

			echo sprintf( $message, esc_url( site_url( '/wp-admin/themes.php') ) );

		} else {

			global $theme;

			/*
			 * Make $theme available inside the template.
			 */
			$theme = WPLib::theme();

			if ( WPLib::use_template_global_vars() ) {

				/**
				 * For compatibility with WordPress templates we need to
				 * extract all the global variables into the current scope.
				 */

				extract( $GLOBALS );

			}

			include( $template );

		}
		return false;

	}


	/**
	 * Theme method for setting a theme isntance for unit test mocking.
	 *
	 * @param $theme
	 *
	 * @return mixed
	 */
	static function set_mock_theme( $theme ) {

		WPLib::set_theme( $theme );

	}

}

WPLib_Theme::on_load();
