<?php

/**
 * Class WPLib_View_Base
 *
 * @property WPLib_Entity_Base $entity
 * @property WPLib_Entity_Base $owner
 * @property WPLib_Model_Base $model
 * @mixin WPLib_Model_Base
 */
abstract class WPLib_View_Base extends WPLib_Base {

	/**
	 * Use this to ease refactoring from $entity to $owner
	 *
	 * @return WPLib_Entity_Base
	 */
	function entity() {

		return $this->owner;

	}

	/**
	 * Use this to ease refactoring from $entity to $owner
	 *
	 * @param WPLib_Entity_Base $entity
	 */
	function set_entity( $entity ) {

		$this->owner = $entity;

	}

	/**
	 * Get model
	 *
	 * @return WPLib_Model_Base
	 */
	function model() {

		return $this->owner->model;

	}

	/**
	 * @param string $template
	 * @param array $_template_vars
	 * @return string
	 */
	function get_template_html( $template, $_template_vars = array() ) {
		ob_start();
		$this->the_template( $template, $_template_vars );
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * @param string $template
	 * @param array $_template_vars
	 */
	function the_template( $template, $_template_vars = array() ) {

		WPLib::the_template( $template, $_template_vars, $this->owner );

	}


	/**
	 * Do a context controlled version of get_header()
	 *
	 * @param string $name The name of the specialised header.
	 */
	function the_header_html( $name = null ) {
		/**
		 * @future  Add context save and set
		 *          (current code does not need it, but future code will be more robust)
		 */
		get_header();
		/**
		 * @future  Add context reset
		 */
	}

	/**
	 * Do a context controlled version of get_footer()
	 *
	 * @param string $name The name of the specialised header.
	 */
	function the_footer_html( $name = null ) {
		/**
		 * @future  Add context save and set
		 *          (current code does not need it, but future code will be more robust)
		 */
		get_footer();
		/**
		 * @future  Add context reset
		 */
	}

	/**
	 * Magic method for getting inaccessible properties
	 * Examples:
	 *  $this->ID       Return ID
	 *  $this->the_ID   Output ID
	 *
	 * @param string $property_name
	 *
	 * @return mixed|null
	 */
	function __get( $property_name ) {

		$value = null;

		if ( is_callable( $property_callable = array( $this, $property_name ) ) ) {

			$value = call_user_func( $property_callable );

		} else {

			$value = $this->model()->$property_name;

		}

		return $value;
	}

	/**
	 * Magic method for setting inaccessible properties
	 *
	 * @param string $property_name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	function __set( $property_name, $value ) {

		if ( is_callable( $property_setter = array( $this, "set_{$property_name}" ) ) ) {

			call_user_func( $property_setter, $value );

		} else {

			$this->owner->model->$property_name = $value;

		}

	}

	/**
	 * Magic method for calling inaccessible methods
	 * Examples:
	 *  $this->date             Return original ISO 8601 date format from model
	 *  $this->get_date()       Return custom formatted date
	 *  $this->get_date_html()  Return custom formatted date HTML
	 *  $this->the_date()       Output custom formatted date
	 *  $this->the_date_html()  Output custom formatted date HTML
	 *
	 * @param string $method_name
	 * @param array  $args
	 *
	 * @return mixed|null
	 */
	function __call( $method_name, $args = array() ) {

		$value = null;

		if ( 0 !== strpos( $method_name, 'the_' ) ) {

			$value = call_user_func_array( array( $this->owner->model, $method_name ), $args );

		} else {

			if ( preg_match( '#^the_(.+)_template$#', $method_name, $match ) ) {

				/*
				 * Put the $template name at the beginning of the $args array
				 */
				array_unshift( $args, str_replace( '_', '-', $match[1] ) );

				/**
				 * Now call 'the_template' with $template as first element in $args
				 */
				$value = call_user_func_array( array( $this, 'the_template' ), $args );

				if ( preg_match( '#^<\{WPLib:(.+)\}>#', $value, $match ) ) {
					/**
					 * Check to see if their is a content type indicator
					 */
					switch ( $match[1] ) {

						case 'JSON':
							$suffix = '_json';
							break;

						case 'HTML':
						default:
							$suffix = '_html';
							/*
							 * Indicate that this content need not be run through wp_kses_post()
							 * since it was loaded by a template which can be reviewed for security.
							 */
							$html_suffix_exists = true;
							break;
					}
				}

			} else if ( preg_match( '#^the_(.+)$#', $method_name, $match ) ) {

				/**
				 * If it does not start with 'the_', delegage to the model class.
				 */

				$method_name = "get_{$match[ 1 ]}";

				$html_suffix_exists = false;

				list( $method_name, $suffix ) = $this->_maybe_parse_suffix( $this, $method_name, $html_suffix_exists );

				if ( is_callable( $the_callable = array( $this, $method_name ) ) ) {

					$value = call_user_func_array( $the_callable, $args );

				} else if ( ! $suffix && $html_callable = array( $this, $html_method = "{$method_name}_html" ) ) {

					list( $html_method, $suffix ) = $this->_maybe_parse_suffix( $this, $html_method, $html_suffix_exists );

					$value = call_user_func_array( $html_callable, $args );

				} else {

					list( $method_name, $suffix ) = $this->_maybe_parse_suffix( $model = $this->model(), "{$method_name}{$suffix}", $html_suffix_exists );

					$value = call_user_func_array( array( $model, $method_name ), $args );

				}
			}

			switch ( $suffix ) {

				case '_attr':
					echo $value = esc_attr( $value );
					break;

				case '_url':
					echo $value = esc_url( $value );
					break;

				case '_html':
					if ( $html_suffix_exists || preg_match( '#_html$#', $method_name ) ) {

						echo $value;

					} else {

						echo $value = wp_kses_post( $value );

					}
					break;

				default:

					echo $value = esc_html( $value );

			}

		}

		return $value;

	}


	/**
	 * Parses off suffixes  ('_attr', '_url') and prefix ('get_') from method names if they exist and are not needed.
	 *
	 * @param object $object
	 * @param string $method_name
	 * @param bool &$html_suffix_exists
	 *
	 * @return array The parsed method name and optional suffix in 2 element array. If no suffix existed, the 2nd element will be false.
	 */
	private function _maybe_parse_suffix( $object, $method_name, &$html_suffix_exists = false ) {

		$result = array( $method_name, false );

		if ( preg_match( '#^(.+?)(_attr|_url|_html)$#', $method_name, $matches ) ) {

			$suffix = $matches[ 2 ];

			$html_suffix_exists = '_html' == $suffix;

			if ( method_exists( $object, $method_name ) ) {

				$result = array( $method_name, $suffix );

			} else if ( method_exists( $object, $matches[ 1 ] ) ) {

				array_shift( $matches );

				$result = $matches;

			} else if ( method_exists( $object, $noprefix_method = preg_replace( '#^get_#', '', $matches[ 1 ] ) ) ) {

				$result = array( $noprefix_method, $suffix );

			}

		}

		if ( ! $result[ 1 ] && preg_match( '#permalink$#', $method_name ) ) {
			/*
			 * permalink() methods behave the same as _url methods.
			 */

			$result[ 1 ] = '_url';

		}

		return $result;
	}

}
