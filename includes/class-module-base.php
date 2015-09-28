<?php

/**
 * Class WPLib_Module_Base
 */
abstract class WPLib_Module_Base extends WPLib {

	const MODULE_NAME = null;

	const INSTANCE_CLASS = null;

	/**
	 * Delegate calls to an instance class if the class has a INSTANCE_CLASS constant or plural name adds 's', otherwise delegate to WPLib.
	 *
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function __callStatic( $method_name, $args ) {

		/**
		 * Get the instance class for this module
		 */
		if ( $instance_class = static::instance_class() ) {

			/**
			 * Whichever we have, INSTANCE_CLASS or singular, see if their is such a method.
			 */
			if ( ! is_callable( array( $instance_class, $method_name ) ) ) {

				/**
				 * Whichever we have, INSTANCE_CLASS or singular, see if their is such a method.
				 * If no, delegate to parent
				 */
				$instance_class = null;

			} else {

				/**
				 * Whichever we have, INSTANCE_CLASS or singular, see if their is such a method.
				 * If yes verify it is a static method.
				 */
				$reflector = new ReflectionMethod( $instance_class, $method_name );

				if ( ! $reflector->isStatic() ) {

					$instance_class = null;

				}

			}

		}

		if ( $instance_class ) {
			/**
			 * Whichever we have, INSTANCE_CLASS or singular with existing method name, call it.
			 */
			$value = call_user_func_array( array( $instance_class, $method_name ), $args );

		} else {
			/**
			 * No method, delegate to parent.
			 */

			$value = parent::__callStatic( $method_name, $args );

		}

		return $value;

	}

	/**
	 * @return mixed|null
	 */
	static function instance_class() {
		/**
		 * See if module has an INSTANCE_CLASS constant defined.
		 */
		return static::INSTANCE_CLASS;

	}

	/**
	 * @return mixed|null
	 */
	static function var_name() {

		return WPLib::get_constant( 'VAR_NAME', static::instance_class() );

	}

}
