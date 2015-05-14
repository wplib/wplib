<?php

/**
 * Class WPLib_User_Model_Base
 *
 * The Model Base Class for Users
 *
 * @property WPLib_User_Base $owner
 */
abstract class WPLib_User_Model_Base extends WPLib_Model_Base {

	/**
	 * @var WP_User|object|null
	 */
	protected $_user;

	/**
	 * @param WP_User|object|null $user
	 * @param array               $args
	 */
	function __construct( $user, $args = array() ) {

		/*
		 * Find the user if possible
		 */
		$this->_user = WPLib_Users::get_user( $user );

		/*
		 * Let our parent class capture whatever properties where passed in as $args
		 */
		parent::__construct( $args );

	}

	/**
	 * @return null|object|WP_User
	 */
	function user() {

		return $this->_user;

	}

	/**
	 * Check to see if this instance has a valid user object in $_user property.
	 *
	 * @return bool
	 */
	function has_user() {

		return WPLib_Users::is_user( $this->_user );

	}

	/**
	 * @param object $user
	 * @return mixed|object
	 */
	function set_user( $user ) {

		if ( WPLib_Users::is_user( $user ) ) {

			$this->_user = $user;

		}

	}

	/**
	 * @return int|null
	 */
	function ID() {

		return $this->has_user() ? intval( $this->_user->ID ) : null;

	}

	/**
	 * @return null|string
	 */
	function email() {
		return $this->has_user() ? $this->_user->user_email : null;
	}

	/**
	 * @return null|string
	 */
	function slug() {
		return $this->has_user() ? $this->_user->user_nicename : null;
	}

	/**
	 * @return null|string
	 */
	function login() {
		return $this->has_user() ? $this->_user->user_login : null;
	}


	/**
	 * @return null|string
	 */
	function display_name() {
		return $this->has_user() ? $this->_user->display_name : null;
	}

	/**
	 * User role as declared in the class constant ROLE.
	 *
	 * @return string|null
	 */
	function role_slug() {

	 	return WPLib::get_constant( 'ROLE', get_class( $this->owner ) );

	}

	/**
	 * User role as found in the object.
	 *
	 * @return string|null
	 */
	function assigned_role_name() {

	 	return $this->has_user() ? reset( $this->_user->roles ) : null;

	}

	/**
	 * @return null|string
	 */
	function posts_url() {

		return $this->has_user() ? get_author_posts_url( $this->_user ) : null;

	}

	/**
	 * Internal function to retrieve the value of a field and to provide a default value if no _user is set.
	 *
	 * @param string $field_name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	function get_field_value( $field_name, $default = false ) {

		$value = $default;
		if ( $this->has_user() && isset( $this->_user->$field_name ) ) {
			$value = $this->_user->$field_name;
		}

		return $value;
	}

	/**
	 * Retrieve the value of user meta and provide a default value if no meta is set.
	 * Adds both a leading underscore and a short prefix to the meta name.
	 *
	 * @param string $meta_name
	 * @param mixed  $default
	 *
	 * @return mixed
	 * @todo Consider deprecating and just use get_field_value() instead.
	 */
	function get_meta_value( $meta_name, $default = false ) {

		$meta_value = $default;

		if ( $this->has_user() ) {
			// @todo Handle SHORT_PREFIX more generically
			$prefix = WPLib::SHORT_PREFIX;
			$meta_name = "_{$prefix}{$meta_name}";
			$meta_value = get_user_meta( $this->_user->ID, $meta_name, true );
			if ( '' == $meta_value ) {
				$meta_value = $default;
			}
		}

		return $meta_value;

	}


	/**
	 * Magic method for getting inaccessible properties.
	 *
	 * @param string $property_name
	 *
	 * @todo Update this to a more specific switch statement for user properties
	 *
	 * @return null|WP_User
	 */
	function __get( $property_name ) {

		$value = null;

		$has_user = $this->has_user();

		if ( method_exists( $this, $property_name ) && is_callable( $callable = array( $this, $property_name ) ) ) {

			$value = call_user_func( $callable );

		} else if ( $has_user && property_exists( $this->_user, $property_name ) ) {

			$value = $this->_user->$property_name;

		} else if ( $has_user && property_exists( $this->_user, $long_name = "user_{$property_name}" ) ) {

			$value = $this->_user->$long_name;

		} else if ( $has_user && property_exists( $this->_user->data, $property_name ) ) {

			$value = $this->_user->data->$property_name;

		} else if ( $has_user && property_exists( $this->_user->data, $long_name = "user_{$property_name}" ) ) {

			$value = $this->_user->data->$long_name;

		} else {

			$value = $this->get_meta_value( $property_name );

		}
		return $value;

	}


	/**
	 * @param array $args
	 * @return static
	 */
	static function make_new( $args ) {

		$user = ! empty( $args[ 'user' ] ) ? $args[ 'user' ] : null;

		unset( $args[ 'user' ] );

		return new static( $user, $args );

	}


}
