<?php

/**
 * Class _WPLib_Html_Helpers
 *
 * Provide an HTML Helper class for WPLib.
 *
 * Basically, this class contributes static methods to the WPLib class using magic methods.
 *
 */
class _WPLib_Html_Helpers extends WPLib_Helper_Base {

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
	 * Output a hyperlink with URL, Link Text and optional title text.
	 *
	 * @param string $href
	 * @param string $link_text
	 * @param array $args
	 *
	 * @return string
	 */
	static function the_link( $href, $link_text, $args = array() ) {

		echo wp_kses_post( self::get_link( $href, $link_text, $args ) );

	}

	/**
	 * Create a hyperlink <a> with URL, Link Text and optional attributes.
	 *
	 * @param string $href
	 * @param string $link_text
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $fragment
	 *      @type string $class
	 *      @type string $target
	 *      @type string $rel
	 *      @type string $onclick
	 *      @type string $title_text
	 *      @type string $link_target
	 *      @type string[]|string $attributes
	 *      @type string $before_text
	 *      @type string $default_text
	 *      @type string $after_text
	 *      @type string $after
	 *      @type bool $is_html
	 * }
	 *
	 * @return string
	 */
	static function get_link( $href, $link_text, $args = array() ) {
		$html = false;
		if ( $href ) {
			$args = wp_parse_args( $args, array(
				'before'       => '',
				'fragment'     => '',
				'class'        => '',
				'target'       => '',
				'rel'          => '',
				'onclick'      => '',
				'title_text'   => '',
				'link_target'  => '',
				'attributes'   => array(),
				'before_text'  => '',
				'default_text' => '',
				'after_text'   => '',
				'after'        => '',
				'is_html'      => true,
			) );

			if ( empty( $link_text ) ) {
				$link_text = $args['default_text'];
			}
			if ( ! $args['title_text'] && $args['link_target'] ) {
				$args['title_text'] = esc_attr( sprintf( __( "Link to %s", 'wplib' ), $args['link_target'] ) );
			}
			if ( $args['title_text'] ) {
				$args['title_text'] = esc_attr( $args['title_text'] );
				$args['title_text'] = " title=\"{$args['title_text']}\"";
			}
			if ( $args['class'] ) {
				$args['class'] = esc_attr( $args['class'] );
				$args['class'] = " class=\"{$args['class']}\"";
			}
			if ( $args['target'] ) {
				$args['target'] = esc_attr( $args['target'] );
				$args['target'] = " target=\"{$args['target']}\"";
			}
			if ( $args['rel'] ) {
				$args['rel'] = esc_attr( $args['rel'] );
				$args['rel'] = " rel=\"{$args['rel']}\"";
			}

			$args['attributes'] = self::get_html_attributes_html( $args['attributes'] );

			if ( $args['fragment'] ) {
				$href = "{$href}#{$args['fragment']}";
			}

			$href = esc_url( $href );

			$link_text = $args['is_html'] ? wp_kses_post( $link_text ) : esc_html( $link_text );

			if ( $args['onclick'] ) {
				$args['onclick'] = ' onclick="' . esc_js( $args['onclick'] ) . '"';
			}

			$html = <<<HTML
{$args['before']}<a{$args['onclick']}{$args['target']}{$args['class']}{$args['rel']} href="{$href}" {$args['title_text']}{$args['attributes']}>{$args['before_text']}{$link_text}{$args['after_text']}</a>{$args['after']}
HTML;
		}

		return $html;
	}

	/**
	 * Returns an HTML Ordered List of <li> elements
	 *
	 * @param string[] $li_elements
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $class
	 *      @type string[]|string $attributes
	 *      @type string $before_elements
	 *      @type string $before_li
	 *      @type string $li_class
	 *      @type string[]|string $li_attributes
	 *      @type string $before_text
	 *      @type string $after_text
	 *      @type string $after_li
	 *      @type string $after_elements
	 *      @type string $after
	 *      @type callable $filter
	 *
	 * }
	 * @return string
	 */
	static function the_html_ordered_list_html( $li_elements, $args = array() ) {

		return self::_get_html_list_html( 'ol', $li_elements, $args );

	}


	/**
	 * Returns an HTML Unordered List of <li> elements
	 *
	 * @param string[] $li_elements
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $class
	 *      @type string[]|string $attributes
	 *      @type string $before_elements
	 *      @type string $before_li
	 *      @type string $li_class
	 *      @type string[]|string $li_attributes
	 *      @type string $before_text
	 *      @type string $after_text
	 *      @type string $after_li
	 *      @type string $after_elements
	 *      @type string $after
	 *      @type callable $filter
	 *
	 * }
	 * @return string
	 */
	static function the_html_unordered_list_html( $li_elements, $args = array() ) {

		return self::_get_html_list_html( 'ul', $li_elements, $args );

	}

	/**
	 * Returns an HTML Ordered List of <li> elements
	 *
	 * @param string[] $li_elements
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $class
	 *      @type string[]|string $attributes
	 *      @type string $before_elements
	 *      @type string $before_li
	 *      @type string $li_class
	 *      @type string[]|string $li_attributes
	 *      @type string $before_text
	 *      @type string $after_text
	 *      @type string $after_li
	 *      @type string $after_elements
	 *      @type string $after
	 *      @type callable $filter
	 *
	 * }
	 * @return string
	 */
	static function get_html_ordered_list_html( $li_elements, $args = array() ) {

		return self::_get_html_list_html( 'ol', $li_elements, $args );

	}

	/**
	 * Returns an HTML Unordered List of <li> elements
	 *
	 * @param string[] $li_elements
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $class
	 *      @type string[]|string $attributes
	 *      @type string $before_elements
	 *      @type string $before_li
	 *      @type string $li_class
	 *      @type string[]|string $li_attributes
	 *      @type string $before_text
	 *      @type string $after_text
	 *      @type string $after_li
	 *      @type string $after_elements
	 *      @type string $after
	 *      @type callable $filter
	 *
	 * }
	 * @return string
	 */
	static function get_html_unordered_list_html( $li_elements, $args = array() ) {

		return self::_get_html_list_html( 'ul', $li_elements, $args );

	}

	/**
	 * Returns an HTML List of <li> elements, Ordered or Unordered
	 *
	 * @param string $list_element
	 * @param string[] $li_elements
	 * @param array $args {
	 *      @type string $before
	 *      @type string $class
	 *      @type string[]|string $attributes
	 *      @type string $before_elements
	 *      @type string $before_li
	 *      @type string $li_class
	 *      @type string[]|string $li_attributes
	 *      @type string $before_text
	 *      @type string $after_text
	 *      @type string $after_li
	 *      @type string $after_elements
	 *      @type string $after
	 *      @type callable $filter
	 * }
	 * @return string
	 */
	private static function _get_html_list_html( $list_element, $li_elements, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'before'          => '',
			'class'           => '',
			'attributes'      => array(),
			'before_elements' => '',
			'before_li'       => '',
			'li_class'        => '',
			'li_attributes'   => array(),
			'before_text'     => '',
			'after_text'      => '',
			'after_li'        => '',
			'after_elements'  => '',
			'after'           => '',
			'filter'          => null,
		) );

		$list_element = WPLib::sanitize_html_name( $list_element );

		if ( ! empty( $args['attributes']['class'] ) ) {

			$args['class'] = esc_attr( "{$args['attributes']['class']} {$args['class']}" );
			unset( $args['attributes']['class'] );

		}

		$attributes = WPLib::get_html_attributes_html( $args['attributes'] );

		$list_html = self::get_html_li_elements_html( $li_elements, array(
			'before'      => "{$args['before']}<{$list_element}{$attributes}" .
			                 " class=\"{$args['class']}\">{$args['before_elements']}",
			'before_li'   => $args['before_li'],
			'class'       => $args['li_class'],
			'attributes'  => $args['li_attributes'],
			'before_text' => $args['before_text'],
			'after_text'  => $args['after_text'],
			'after_li'    => $args['after_li'],
			'after'       => "{$args['after_elements']}</{$list_element}>{$args['after']}",
			'filter'      => $args['filter'],
		));

		return $list_html;

	}

	/**
	 * Outputs one or more HTML <li> elements
	 *
	 * @param string[] $li_elements
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $before_li
	 *      @type string[]|string $attributes
	 *      @type string $class
	 *      @type string $after_li
	 *      @type string $after
	 *      @type callable $filter
	 * }
	 *
	 * @return string
	 */
	static function the_html_li_elements_html( $li_elements, $args = array() ) {

		echo self::get_html_li_elements_html( $li_elements, $args );

	}

	/**
	 * Returns  one or more HTML <li> elements
	 *
	 * @param string[] $li_elements
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $before_li
	 *      @type string $before_text
	 *      @type string[]|string $attributes
	 *      @type string $class
	 *      @type string $after_text
	 *      @type string $after_li
	 *      @type string $after
	 *      @type callable $filter
	 * }
	 * @return string
	 */
	static function get_html_li_elements_html( $li_elements, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'before'      => '',
			'before_li'   => '',
			'class'       => '',
			'attributes'  => array(),
			'before_text' => '',
			'after_text'  => '',
			'after_li'    => '',
			'after'       => '',
			'filter'      => null,
		));

		$args['attributes'] = self::sanitize_html_attributes( $args['attributes'] );

		$li_args             = $args;
		$li_args['sanitize'] = false;
		$li_args['elements'] = $li_elements;
		$li_args['before']   = $li_args['before_li'];
		$li_args['after']    = $li_args['after_li'];
        unset( $li_args['before_li'], $li_args['after_li'] );

		$elements_html = '';

	    foreach ( $li_elements as $index => $element_text ) {

	        $li_args['index'] = $index;

	        $elements_html .= self::get_html_li_element_html( $element_text, $li_args );

		}

		return ! empty( $elements_html )
			? "{$args['before']}{$elements_html}{$args['after']}"
			: '';

	}

	/**
	 * Returns  one or more HTML <li> elements
	 *
	 * @param string $element_text
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $before_text
	 *      @type string[]|string $attributes
	 *      @type string $class
	 *      @type string $after_text
	 *      @type string $after
	 *      @type callable $filter
	 *      @type mixed $index
	 * }
	 * @return string
	 */
	static function get_html_li_element_html( $element_text, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'before'      => '',
			'class'       => '',
			'attributes'  => array(),
			'before_text' => '',
			'after_text'  => '',
			'after'       => '',
			'filter'      => null,
			'elements'    => array(),
			'index'       => null,
		));

		$before_text  = $args['before_text'] ? esc_html( $args['before_text'] ) : '';
		$element_text = $element_text ? esc_html( $element_text ) : '';
		$after_text   = $args['after_text'] ? esc_html( $args['after_text'] ) : '';

		if ( ! empty( $args['attributes']['class'] ) ) {

			$args['class'] = esc_attr( "{$args['attributes']['class']} {$args['class']}" );
			unset( $args['attributes']['class'] );

		}

		if ( $args['filter'] ) {

			$args = call_user_func( $args['filter'], $args, $element_text, $args['elements'] );

		}

		$attributes = count( $args['attributes'] )
		  ? $args['attributes']
		  : '';

		if ( $attributes ) {

			$attributes = ' ' . WPLib::get_html_attributes_html( $attributes );

		}

		$elements_html .=<<<HTML
{$args['before']}<li{$attributes} class="{$args['class']}">{$before_text}{$element_text}{$after_text}</li>{$args['after']}
HTML;

		return $elements_html;

	}


	/**
	 * Output a string of one of more '<li>' elements given a list of attributes
	 *
	 * @param string[]|string $attributes An associate array or URL parameter formatted string of HTML attributes.
	 *
	 * @return string
	 */
	static function the_html_attributes_html( $attributes = array() ) {

		echo self::get_html_attributes_html( $attributes );

	}

	/**
	 * Return a string of one of more '<li>' elements given a list of attributes
	 *
	 * @param string[]|string $attributes An associate array or URL parameter formatted string of HTML attributes.
	 *
	 * @return string
	 */
	static function get_html_attributes_html( $attributes = array() ) {

		$attributes = self::sanitize_html_attributes( $attributes );

		return implode( ' ', array_map(

			function( $name ) use ( $attributes ) {

				return "{$name}=\"{$attributes[ $value ]}\"";

			},
			array_keys( $attributes )

		));

	}

	/**
	 * Sanitize one or more HTML attributes
	 *
	 * @param string[]|string $attributes An associate array or URL parameter formatted string of HTML attributes.
	 *
	 * @return string[]
	 */
	static function sanitize_html_attributes( $attributes ) {

		if ( ! is_array( $attributes ) ) {

			parse_str( $attributes, $attributes );

		}

		$sanitized_attributes = array();

		if ( is_array( $attributes ) ) {

			foreach ( $attributes as $name => $value ) {

				if ( is_numeric( $name ) ) {
					/**
					 * A name is passed in $args as a value in an array like this:
					 *
					 *  array( 'class' => 'person-email', 'selected' )
					 */
					$value = null;
					$name = $value;

				}

				$name = self::sanitize_html_name( $name );

				if ( empty( $name ) ) {

					continue;

				}

				$value = $attributes[ $name ];

				if ( preg_match( '#^(object|array)$#', gettype( $value ) ) ) {
					continue;
				}

				$value = esc_attr( (string) $value );

				if ( empty( $value ) ) {
					/**
					 * A name is passed in $args as a value in a string like this:
					 *
					 *  'class=person-email&selected'
					 *
					 */
					$value = $name;

				}

				$sanitized_attributes[ $name ] = $value;

			}
		}

		return $sanitized_attributes;

	}

	/**
	 * Sanitize the name of an HTML attribute or element
	 *
	 * @param string $name
	 *
	 * @return string
	 *
	 * @see http://stackoverflow.com/a/13287707
	 */
	static function sanitize_html_name( $name ) {

		return preg_replace( '#[^\p{L}0-9_.-]#', '', $name );

	}



	/**
	 * Create a hyperlink with URL, Link Text and optional title text.
	 *
	 * @param string $src
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $class
	 *      @type string $alt_text
	 *      @type string $fragment
	 *      @type string $onclick
	 *      @type string[]|string $attributes
	 *      @type string $after
	 *
	 * }
	 *
	 * @return string
	 */
	static function the_img( $src, $args = array() ) {

		echo self::get_img( $src, $args );

	}

	/**
	 * Create a <img> take with URL and optional attributes.
	 *
	 * @param string $src
	 * @param array $args {
	 *
	 *      @type string $before
	 *      @type string $class
	 *      @type string $alt_text
	 *      @type string $fragment
	 *      @type string $onclick
	 *      @type string[]|string $attributes
	 *      @type string $after
	 *
	 * }
	 *
	 * @return string
	 */
	static function get_img( $src, $args = array() ) {
		$html = '';

		if ( $src ) {

			$args = wp_parse_args( $args, array(
				'before'     => '',
				'class'      => '',
				'alt_text'   => '',
				'fragment'   => '',
				'onclick'    => '',
				'attributes' => array(),
				'after'      => '',
			) );

			if ( $args['alt_text'] ) {
				$args['alt_text'] = ' alt="' . esc_attr( $args['alt_text'] ) . '"';
			}

			if ( $args['class'] ) {
				$args['class'] = ' class="' . esc_attr( $args['class'] ) . '"';
			}

			$args['attributes'] = self::get_html_attributes_html( $args['attributes'] );

			if ( $args['fragment'] ) {
				$src = "{$src}#{$args['fragment']}";
			}

			if ( $args['onclick'] ) {
				$args['onclick'] = ' onclick="' . esc_js( $args['onclick'] ) . '"';
			}

			$src = ' src="' . esc_url( $src ) . '"';

			$html = "{$args['before']}<img{$src}{$args['onclick']}{$args['class']}{$args['alt_text']}{$args['attributes']}>{$args['after']}";
		}

		return $html;
	}

}
_WPLib_Html_Helpers::on_load();
