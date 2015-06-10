<?php


/**
 * Class WPLib_Item_Base
 *
 * @mixin WPLib_Model_Base
 * @mixin WPLib_View_Base
 *
 */
abstract class WPLib_Item_Base extends WPLib_Base {

	/**
	 * @var WPLib_Model_Base
	 */
	var $model;

	/**
	 * @var WPLib_View_Base
	 */
	var $view;

	/**
	 * @param array $args
	 */
	function __construct( $args = array() ) {

		/**
		 * For both model and view, create the items.
		 */
		foreach ( array( 'model', 'view' ) as $property_name ) {

			if ( is_object( $args[ $property_name ] ) ) {
				/*
				 * If it was an object, just assign is.
				 */
				$this->{$property_name} = $args[ $property_name ];

			} else {

				if ( ! isset( $args[ $property_name ] ) ) {

					/*
					 * If no model or view class was specified then use the default.
					 */
					$args[ $property_name ] = $this->_get_property_class( $property_name );

				}

				if ( is_string( $args[ $property_name ] ) ) {

					/**
					 * It's a class name thus needs no $args
					 */
					$class_name    = $args[ $property_name ];
					$property_args = array();

				} else if ( is_array( $args[ $property_name ] ) ) {

					/**
					 * It's an array of args, so use the default class name
					 */
					$class_name    = $this->_get_property_class( $property_name );
					$property_args = $args[ $property_name ];

				}

				if ( method_exists( $class_name, 'make_new' ) ) {
					/*
					 * If this class has a make_new() method.
					 * A Class::make_new() method is needed when the class constructor has more than 1 parameter of $args.
					 */
					$this->{$property_name} = $class_name::make_new( $property_args );

				} else if ( class_exists( $class_name ) ) {

					/*
					 * If an array or string, instantiate the class.
					 */
					$this->{$property_name} = new $class_name( $property_args );

				} else {

					$this->{$property_name} = null;

				}

			}

			if ( is_object( $this->{$property_name} ) && property_exists( $this->{$property_name}, 'owner' ) ) {
				/**
				 * Set a reference back to the item for both $view and $model.
				 */
				$this->{$property_name}->owner = $this;
			}

			/**
			 * Remove processed args to prevent overwriting them later.
			 */
			unset( $args[ $property_name ] );

		}

		parent::__construct( $args );

		if ( count( $this->extra_args ) ) {
			/**
			 * If there are any leftover args, see if we can assign them to
			 */
			$this->model->set_state( $this->extra_args );

			/**
			 * Any extra args will be left in model.
			 */
			$this->extra_args = array();

		}

	}


	/**
	 * @param string $property_name
	 *
	 * @return bool
	 */
	function __isset( $property_name ) {

		return isset( $this->model ) && isset( $this->model->$property_name );

	}

	/**
	 * @param string $property_name
	 *
	 * @return mixed
	 */
	function __get( $property_name ) {

		return $this->model->{$property_name};

	}

	/**
	 * @param string $property_name
	 * @param mixed $value
	 */
	function __set( $property_name, $value ) {

		$this->model->{$property_name} = $value;

	}

	/**
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	function __call( $method_name, $args = array() ) {

		$value = null;

		if ( is_callable( $view_callable = array( $this->view, $method_name ) ) || preg_match( '#^the_#', $method_name ) ) {

			$value = call_user_func_array( $view_callable, $args );

		} else {

			$value = call_user_func_array( array( $this->model, $method_name ), $args );

		}

		if ( is_wp_error( $value ) ) {

			$message = __( 'ERROR: No method %s exists for class %s or in its Model or its View.', 'wplib' );

			trigger_error( sprintf( $message, $method_name, get_class( $this ) ) );

		}

		return $value;

	}

	/**
	 * Find the right Model and View class based on current class and property name
	 *
	 * @param string $property_name
	 *
	 * @return string
	 */
	private function _get_property_class( $property_name ) {

		$class_name = get_class( $this );

		$property_name = ucfirst( $property_name );

		do {
			/**
			 * Strip the _Base off.
			 */
			$baseless_class = preg_replace( '#^(.*)_Base$#', '$1', $class_name );

			/*
			 * Default class name appends '_Model' or '_View' to object name.
			 */
			$property_class = "{$baseless_class}_{$property_name}";


			if ( $class_name != $baseless_class ) {
				/**
				 * If it had '_Base' suffix add '_Default' suffix
				 */
				$property_class .= "_Default";
			}

			if ( $property_class && class_exists( $property_class ) ) {

					break;

			}

			$class_name = get_parent_class( $class_name );

		} while ( $class_name );

		if ( ! class_exists( $property_class ) ) {

			/*
			 * Get the default contained class name:
			 */
			$property_class = "WPLib_{$property_name}";

		}
		return $property_class;

	}

}
