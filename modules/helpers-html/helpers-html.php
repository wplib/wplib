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
	 * Create a hyperlink with URL, Link Text and optional title text.
	 *
	 * @param string $href
	 * @param string $link_text
	 * @param array $args
	 *
	 * @return string
	 */

	static function get_link( $href, $link_text, $args = array() ) {
		$html = false;
		if ( $href ) {
			$args = wp_parse_args( $args, array(
				'class'        => false,
				'target'       => false,
				'attributes'   => false,
				'title_text'   => false,
				'is_html'      => false,
				'link_target'  => false,
				'before'       => false,
				'after'        => false,
				'before_text'  => false,
				'after_text'   => false,
				'fragment'     => false,
				'onclick'      => false,
				'default_text' => false,
				'rel'          => false,
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
			if ( ! is_array( $args['attributes'] ) ) {
				parse_str( $args['attributes'], $args['attributes'] );
			}
			if ( is_array( $args['attributes'] ) ) {
				$attributes = '';
				foreach ( $args['attributes'] as $name => $value ) {
					/**
					 * @TODO Verify that sanitize_key() is the correct method to use here for security.
					 */
					$name  = sanitize_key( $name );
					$value = esc_attr( $value );
					$attributes .= " {$name}=\"{$value}\"";
				}
				$args['attributes'] = $attributes;
			}

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
	 * Return a string of attributes formatted for HTML elements from an associative array
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	static function to_html_attributes( $args = array() ) {

		$args = wp_parse_args( $args );

		$attributes = array_map(

			function( $name ) use ( $args ) {

				do {
					$attribute = '';

					if ( empty( $name ) || ! is_string( $name ) ) {
						break;
					}

					$value = $args[ $name ];

					if ( preg_match( '#^(object|array)$#', gettype( $value ) ) ) {
						break;
					}

					$value = esc_attr( (string) $value );

					if ( empty( $value ) ) {
						break;
					}

					/**
					 * Santitize Attribute Name
					 * @see http://stackoverflow.com/a/13287707
					 */
					$name = preg_replace( '#[^\p{L}0-9_.-]#', '', $name );

					if ( empty( $name ) ) {
						break;
					}

					$attribute = "{$name}=\"{$value}\"";

				} while ( false );

				return $attribute;
			},

			array_keys( $args )

		);

		$attributes = implode( ' ', $attributes );

		return $attributes;
	}

}
_WPLib_Html_Helpers::on_load();
