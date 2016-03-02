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
	 * @var string
	 */
	const SLUG = null;

	/**
	 * @var mixed
	 */
	const __default = null;

	/**
	 * @var mixed
	 */
	private $_value;

	/**
	 * @var array Use for Enum values such as Runmode and Stability
	 */
	private static $_enums = array();

	/**
	 * @param mixed|null $value
	 */
	function __construct( $value = null ) {

		$default = static::__default;

		if ( is_null( $value ) ) {

			$this->set_value( $default );

		} else if ( $this->has_enum_value( $value ) ) {

			$this->set_value( $value );

		} else if ( is_string( $value ) && $this->has_enum_const( $value ) ) {

			$this->set_value( $this->get_enum_value( $const = $value ) );

		} else {

			$this->set_value( $default );

		}

		self::$_enums[ self::get_slug( $this ) ] = $this;

	}

	/**
	 * @param string|object $enum Classname or object
	 *
	 * @return mixed|null
	 *
	 * @since 0.10.0
	 */
	static function get_slug( $enum ) {

		$enum_class = is_object( $enum )
			? get_class( $enum )
			: $enum;

		return defined( $const_ref = "{$enum_class}::SLUG" )
			? constant( $const_ref )
			: null;
	}

	/**
	 * @param string $enum_name
	 *
	 * @return mixed|null
	 *
	 * @since 0.10.0
	 */
	static function get_enum( $enum_name ) {

		return isset( self::$_enums[ $enum_name ] )
			? self::$_enums[ $enum_name ]
			: null;

	}

	/**
	 * @param string $enum_slug
	 * @return string
	 *
	 * @since 0.10.0
	 */
	static function get_enum_class( $enum_slug ) {

		$enum_classes = self::get_enum_classes();
		return isset( $enum_classes[ $enum_slug ] )
			? $enum_classes[ $enum_slug ]
			: null;
	}

	/**
	 * @return string[]
	 *
	 * @since 0.10.0
	 */
	static function get_enum_classes() {

		static $enum_classes;

		if ( ! isset( $enum_classes ) ) {

			foreach ( array_reverse( get_declared_classes() ) as $class_name ) {

				if ( is_subclass_of( $class_name, __CLASS__ ) ) {

					$enum_classes[ self::get_slug( $class_name ) ] = $class_name;

				} else if ( __CLASS__ === $class_name ) {
					/**
					 * If we make it to this class there can be no more
					 * child classes of this class!
					 */
					break;

				}

			}

			$enum_classes = array_reverse( $enum_classes );
		}

		return $enum_classes;

	}
	/**
	 * Sets the Enum value based on an Enum class or slug.
	 *
	 * Will instantiate a new Enum class if needed.
	 *
	 * @param string $enum_name  Slug or classname
	 * @param mixed $value
	 *
	 * @return mixed|null
	 *
	 * @since 0.10.0
	 */
	static function set_enum( $enum_name, $value ) {

		$enum_class = class_exists( $enum_name, false )
			? $enum_name
			: null;

		$enum_slug = ! is_null( $enum_class )
			? self::get_slug( $enum_name )
			: $enum_name;

		if ( isset( self::$_enums[ $enum_slug ] ) ) {

			/**
			 * @var WPLib_Enum $enum
			 */
			$enum = self::$_enums[ $enum_slug ];
			$enum->set_value( $value );

		} else {

			$enum_class = self::get_enum_class( $enum_slug );

			self::$_enums[ $enum_slug ] = new $enum_class( $value );

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
	static function has_enum_value( $value ) {

		return array_key_exists( $value, self::get_enum_consts( get_called_class() ) );

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
	 * @param WPLib_Enum|string $enum
	 *
	 * @return array
	 */
	static function get_enum_values( $enum = null ) {

		if ( is_string( $enum ) ) {

			$class_name = $enum;

		} else {

			$class_name = ! is_null( $enum ) ? get_class( $enum ) : get_called_class();

		}

		$reflector = new ReflectionClass( $class_name );
		$enums     = $reflector->getConstants();

		unset( $enums['__default'] );

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

		return array_key_exists( strtoupper( $const ), self::get_enum_values( $this ) );

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
		$values = self::get_enum_values( $this );

		/*
		 * Look for a matching constant value first
		 */
		if ( ! array_search( $value, $values ) ) {

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
	 * @param WPLib_Enum|string $enum
	 *
	 * @return array
	 */
	static function get_enum_consts( $enum = null ) {

		return array_flip( static::get_enum_values( $enum ) );

	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	static function is_valid( $value ) {

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


