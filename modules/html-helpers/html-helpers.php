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
	 *  Convenience method for setting HTTP Content Type for outputting plain text.
	 */
	static function plain_text() {

		header( 'Content-type:text/plain' );

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
				$args['title_text'] = esc_attr( sprintf( __( "Link to %s", 'sparkcity' ), $args['link_target'] ) );
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
				/**
				 * @TODO Verify that sanitize_key() is the correct method to use here for security.
				 */
				$args['fragment'] = '#' . sanitize_key( $args['fragment'] );
			}

			$href = esc_url( $href );

			if ( ! $args['is_html'] ) {
				$link_text = esc_html( $link_text );
			}

			if ( $args['onclick'] ) {
				$args['onclick'] = " onclick=\"{$args['onclick']}\"";
			}

			$html = <<<HTML
{$args['before']}<a{$args['onclick']}{$args['target']}{$args['class']}{$args['rel']} href="{$href}{$args['fragment']}" {$args['title_text']}{$args['attributes']}>{$args['before_text']}{$link_text}{$args['after_text']}</a>{$args['after']}
HTML;
		}

		return $html;
	}



}
_WPLib_Html_Helpers::on_load();
