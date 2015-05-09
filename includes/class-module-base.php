<?php

/**
 * Class WPLib_Module_Base
 */
class WPLib_Module_Base extends WPLib {

	/**
	 * Delegate calls to a main class if the class has a main class, otherwise delegate to WPLib
	 *
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function __callStatic( $method_name, $args ) {


		if ( ! ( $main_class = static::constant( 'MAIN_CLASS' ) ) ) {

			$main_class = preg_replace( '#^(.+)s$#', '$1', get_called_class() );

		}

		if ( ! is_callable( array( $main_class, $method_name ) ) ) {

			$main_class = null;

		} else {

			$reflector = new ReflectionMethod( $main_class, $method_name );

			if ( ! $reflector->isStatic() ) {

				$main_class = null;

			}

		}

		if ( $main_class ) {

			$value = call_user_func_array( array( $main_class, $method_name ), $args );

		} else {

			$value = parent::__callStatic( $method_name, $args );

		}

		return $value;

	}

}
