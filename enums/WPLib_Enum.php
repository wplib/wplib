<?php

/**
 * Class WPLib_Enum
 *
 * Creates a better way to define and captures settings that
 * WordPress would have used constants for.
 *
 * Inspired by SplEnum from the SPL-Types PECL extension but
 * greatly enhanced and requiring no PHP extension.
 *
 * @example:
 *
 *        class MyEnum extends WPlib_Enum {
 *          const __default = null;
 *          const FOO = 'foo';
 *          const BAR = 'bar';
 *        }
 *
 *        $enum = new MyEnum(MyEnum::FOO);
 *        $enum = new MyEnum(99); //riggers error
 *
 */
abstract class WPLib_Enum {

	/**
	 * @var mixed
	 */
	const __default = null;

	/**
	 * @var mixed
	 */
	private $_value;

	/**
	 * @var array
	 */
	private static $_set_initialized = array();

	/**
	 * Set an initial value
	 *
	 * @param mixed $value
	 */
	static function initialize( $value ) {

		$called_class = get_called_class();

		if ( ! static::is_valid( $value ) ) {

			static::_trigger_error( $called_class );

		}
		self::$_set_initialized[ $called_class ] = $value;

	}

	/**
	 * Return the initialized value
	 *
	 * @return mixed
	 */
	static function initialized_value() {

		return isset( self::$_set_initialized[ get_called_class() ] )
			? self::$_set_initialized[ get_called_class() ]
			: null;

	}

	/**
	 * @param mixed|null $value
	 */
	function __construct( $value = null ) {

		$default = isset( self::$_set_initialized[ $class = get_class( $this ) ] )
			? self::$_set_initialized[ $class ]
			: self::__default;

		if ( is_null( $value ) ) {

			$this->set_value( $default );

		} else if ( $this->has_enum_value( $value ) ) {

			$this->set_value( $value );

		} else if ( is_string( $value ) && $this->has_enum_const( $value ) ) {

			$this->set_value( $this->get_enum_value( $const = $value ) );

		} else {

			$this->set_value( $default );

		}

	}

	/**
	 * Return the current value assigned.
	 *
	 * @return string
	 */
	function get_value() {

		return $this->_value;

	}

	/**
	 * Assign the current value.
	 *
	 * Validate that the valid is correct or thow an except, but allow null.
	 *
	 * We throw exception to behaves like SplEnum.
	 *
	 * @param mixed $value
	 */
	function set_value( $value ) {

		if ( ! $this->is_valid( $value ) ) {

			static::_trigger_error( get_class( $this ) );

		}

		$this->_value = $value;

	}

	/**
	 * Check if the value passed valid value for this enum
	 *
	 * @param mixed $value
	 *
	 * @return boolean
	 */
	function has_enum_value( $value ) {

		return array_key_exists( $value, static::get_enum_consts() );

	}

	/**
	 * @param string $const
	 *
	 * @return mixed|null
	 */
	function get_enum_value( $const ) {

		if ( false === $this->has_enum_const( $const ) ) {

			$value = null;

		} else {

			$const = strtoupper( $const );

			$value = constant( get_called_class() . "::{$const}" );

		}

		return $value;

	}

	/**
	 * Return the values defined in the child class.
	 *
	 * @param bool $include_default
	 *
	 * @return array
	 */
	function get_enum_values( $include_default = false ) {

		static $enums;

		if ( ! isset( $enums ) ) {

			$class = isset( $this ) ? get_class( $this ) : get_called_class();

			$reflector = new ReflectionClass( $class );
			$enums     = $reflector->getConstants();

			if ( ! $include_default ) {
				unset( $enums['__default'] );
			}

		}

		return $enums;

	}

	/**
	 * Check if the const passed is a valid const for this enum
	 *
	 * @param string $const
	 *
	 * @return boolean
	 */
	function has_enum_const( $const ) {

		return array_key_exists( strtoupper( $const ), $this->get_enum_values() );

	}

	/**
	 * Return the name of the enum const in upper case
	 *
	 * @param mixed $value
	 *
	 * @return string|boolean  Return false if no match or a non-empty string if a match
	 */
	function get_enum_const( $value ) {

		/*
		 * Get an array with names as keys and constant values as values
		 */
		$values = $this->get_enum_values();

		/*
		 * Look for a matching constant value first
		 */
		if ( ! array_key_exists( $value, $values ) ) {

			/**
			 *  The result of this function without match is false.
			 */
			$const = false;

		} else {

			/*
			 * Now get an array with constant values as keys and names as values
			 */
			$values = array_flip( $values );

			/*
			 * Found a matching constant value, return it's name and it will
			 * evaluate to true. Return as uppercase (that's our constraint.)
			 */
			$const = strtoupper( $values[ $value ] );
		}

		/**
		 * Return false if no match or a non-empty string if a match
		 */
		return $const;

	}

	/**
	 * Return the constants defined in the child class.
	 *
	 * @param bool $include_default
	 *
	 * @return array
	 */
	function get_enum_consts( $include_default = false ) {

		return array_flip( static::get_enum_values( $include_default ) );

	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	function is_valid( $value ) {

		return static::has_enum_value( $value ) && ! is_null( $value );

	}

	/**
	 * @param string|bool $message
	 */
	static function _trigger_error( $message = false ) {

		if ( ! $message ) {

			$message = sprintf( "Value not a const in enum %s", func_get_arg( 1 ) );

		} else {

			$args = func_get_args();
			array_unshift( $args, $message );

			$message = call_user_func_array( 'sprintf', $args );
		}

		trigger_error( $message );

	}

	/**
	 * @return string
	 */
	function __toString() {

		return (string) $this->_value;

	}

}


