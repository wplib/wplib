<?php

/**
 * Class WPLib_Role_Module_Base
 */
abstract class WPLib_Role_Module_Base extends WPLib_Module_Base {

	const ROLE = null;

	const CAPABILITIES = array();

	const INSTANCE_CLASS = null;

	private static $_roles = array();

	/**
	 * @var array
	 */
	private static $_capabilities = array();

	/**
	 * @var string[]
	 */
	private static $_display_names = array();

	/**
	 *
	 */
	static function on_load() {

		self::add_class_action( 'wp_loaded' );

	}

	/**
	 *
	 */
	static function _wp_loaded() {

		if ( ! WPLib::cache_get( $cache_key = 'roles_initialized' ) ) {

			self::_initialize_roles();

			WPLib::cache_set( $cache_key, true );

		}
	}

	/**
	 * Runs through the roles defined by child classes of WPLib_Role_Base and ensures that all roles and get_capabilities
	 * are set as defined in the classes.
	 *
	 * @note Using this approach disables administrator changes of roles and get_capabilities.
	 *
	 */
	private static function _initialize_roles() {

		$roles = new WP_Roles();

		foreach( self::$_capabilities as $role_slug => $capabilities ) {

			if ( empty( $role_slug ) ) {

				continue;

			}

			if ( ! isset( $roles->roles[ $role_slug ] ) ) {

				$add_role = true;

			} else if ( self::get_role_name( $role_slug ) != $roles->role_names[ $role_slug ] ) {

				$add_role = true;

			} else {

				$saved_capabilities = $roles->role_objects[ $role_slug ]->capabilities;

				if ( count( $capabilities ) === count( array_intersect( $saved_capabilities, $capabilities ) ) ) {

					$add_role = false;

				} else {

					$add_role = true;

				}

			}

			if ( $add_role ) {

				/**
				 * @note: Just FYI, this will remove the legacy get_capabilities of level_0..level_10.
				 * @note: Should not be an issue for a modern WP app. If it becomes an issue we can test for them too.
				 */
				remove_role( $role_slug );

				$capabilities = array_fill_keys( $capabilities, true );

				add_role( $role_slug, self::get_role_name( $role_slug ), $capabilities );

			}

		}

	}

//	/**
//	 * @return string[]
//	 */
//	static function display_names() {
//
//		return isset( self::$_roles[ static::ROLE ] )
//			? wp_list_pluck( self::$_roles[ static::ROLE ], 'display_name' )
//			: null;
//
//	}

	/**
	 * @param string $display_name
	 */
	static function register_role( $display_name ) {

		self::$_roles[ static::ROLE ] = array(
			'display_name' => $display_name,
			'capabilities' => static::capabilities(),
		);

	}

	/**
	 * @param array|string $deletions A string or array of get_capabilities to remove from this role.
	 */
	static function remove_role_capabilities( $deletions ) {

		if ( is_string( $deletions ) ) {

			$deletions = explode( ',', $deletions );

		}

		$capabilities = self::$_roles[ static::ROLE ]['capabilities'];

		self::$_roles[ static::ROLE ]['capabilities'] =
			array_diff( $capabilities, $deletions );

	}


	/**
	 * @return array|mixed
	 */
	static function role_slug() {

		return defined( 'static::ROLE' ) ? static::ROLE : null;

	}

	/**
	 * @return array|mixed
	 * @param bool|string $role_slug
	 */
	static function get_capabilities( $role_slug = false ) {

		if ( ! $role_slug ) {

			$role_slug = static::role_slug();

		}

		if ( $role_slug && ! isset( self::$_capabilities[ $role_slug ] ) ) {

			$parent_class = get_parent_class( get_called_class() );

			if ( method_exists( $parent_class, 'get_capabilities' ) ) {

				$capabilities = call_user_func( array( $parent_class, 'get_capabilities' ), $role_slug );

			} else {

				$capabilities = array();

			}

			self::$_capabilities[ $role_slug ] = $capabilities;

		}
		return $role_slug ? self::$_capabilities[ $role_slug ] : array();

	}


	/**
	 * @param bool|string $role_slug
	 *
	 * @return string
	 */
	static function get_role_name( $role_slug = false ) {

		return self::$_display_names[ $role_slug ? $role_slug : static::role_slug() ];

	}

	/**
	 * @param string $display_name
	 * @param bool|string $role_slug
	 */
	static function set_role_name( $display_name, $role_slug = false ) {

		self::$_display_names[ $role_slug ? $role_slug : static::role_slug() ] = $display_name;

	}

	/**
	 * Return array of CAPABILITIES by merging constants with parents classes
	 */
	static function capabilities() {

		$parent_of_called = get_parent_class( get_called_class() );

		$parent_capabilities = defined( $const_ref = "{$parent_of_called}::CAPABILITIES" )
			? call_user_func( array( $parent_of_called, 'capabilities' ) )
			: array();

		return count( $parent_capabilities )
			? array_merge( $parent_capabilities, static::CAPABILITIES )
			: static::CAPABILITIES;

	}



}

WPLib_Role_Module_Base::on_load();
