<?php

/**
 * Class WPLib_User_Base
 *
 * The Base Entity Class for Users
 *
 * @mixin WPLib_User_Model_Base
 * @mixin WPLib_User_View_Base
 *
 * @property WPLib_User_Model_Base $model
 * @property WPLib_User_View_Base $view
 */
abstract class WPLib_User_Base extends WPLib_Entity_Base {

	/**
	 * Child class should define a valid value for ROLE
	 */
	const ROLE = null;

	/**
	 * @var WPLib_User_Base
	 */
	private $_user;

	/**
	 * @var array
	 */
	private static $_capabilities = array();

	/**
	 * @var string[]
	 */
	private static $_display_names = array();

	/**
	 * @param WP_User|null $user
	 * @param array $args
	 */
	function __construct( $user, $args = array() ) {

		$this->_user = $user;

		$args = wp_parse_args( $args, array(
			'model' => array( 'user' => $user ),
		));

		parent::__construct( $args );

	}

	/**
	 * Log in a WordPress user programmatically
	 *
	 * @parm $user_id
	 *
	 * @see http://www.wprecipes.com/log-in-a-wordpress-user-programmatically
	 */
	function do_login() {

		wp_set_current_user( $user_id = $this->ID(), $this->login() );

		wp_set_auth_cookie( $user_id );

		do_action( 'wp_login', $this->login() );

	}

	/**
	 * @param bool|int $reassign
	 */
	function delete_user( $reassign = false ) {

		wp_delete_user( $this->ID(), $reassign );

	}

	/**
	 * @return string[]
	 */
	static function display_names() {

		return self::$_display_names;

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

	/**
	 * Remove get_capabilities from the role inherited from the parent user class' role.
	 *
	 * Note: Must be called in the on_load() method to establish get_capabilities in a role's PHP class.
	 *
	 * @param array|object|string $deletions A string or array of get_capabilities to remove from this role.
	 * @param bool|string $role_slug
	 */
	function remove_role_capabilities( $deletions, $role_slug = false  ) {

		if ( is_string( $deletions ) ) {

			$deletions = preg_split( '#\s+#', $deletions );

		}

		static::set_capabilities( array_diff( self::get_capabilities( $role_slug ), $deletions ), $role_slug );

	}

	/**
	 * Add get_capabilities to the role, inheriting the get_capabilities from the parent user class' role.
	 *
	 * Note: Must be called in the on_load() method to establish get_capabilities in a role's PHP class.
	 *
	 * @param array|string $capabilities
	 * @param bool|string $role_slug
	 */
	static function add_role_capabilities( $capabilities = null, $role_slug = false  ) {


		if ( is_string( $capabilities ) ) {

			$capabilities = preg_split( '#\s+#m', strtolower( $capabilities ) );

		} else if ( ! is_array( $capabilities ) ) {

			$capabilities = array( 'read' );

		}

		static::set_capabilities( array_merge( $capabilities, self::get_capabilities( $role_slug ) ), $role_slug );

	}

	/**
	 * Set get_capabilities for the role.
	 *
	 * @param $capabilities
	 * @param bool|string $role_slug
	 */
	static function set_capabilities( $capabilities, $role_slug = false ) {

		self::$_capabilities[ $role_slug ? $role_slug : static::role_slug() ] = $capabilities;

	}


	/**
	 * @param string $display_name
	 * @param string[] $capabilities
	 * @param bool|string $role_slug
	 */
	static function register_role( $display_name, $capabilities, $role_slug = false  ) {

		static::set_role_name( $display_name, $role_slug );

		static::add_role_capabilities( $capabilities, $role_slug );

	}

	/**
	 * @return array|mixed
	 */
	static function role_slug() {

		return WPLib_Users::get_role_slug_by( 'class', get_called_class() );

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
	 * @param array $args
	 *
	 * @return WPLib_User_Model_Base|null
	 */
	static function make_new( $args ) {

		$user = null;

		if ( ! empty( $args['user'] ) ) {

			$user = $args['user'];

		}

		if ( ! is_null( $user ) && ! is_a( $user, 'WP_User' ) ) {

			if ( is_numeric( $user ) ) {

				$user = get_user_by( 'id', $user );

			} else if ( false !== strpos( $user, '@' ) ) {

				$user = get_user_by( 'email', $user );

			} else if ( ! ( $user = get_user_by( 'slug', $user ) ) ) {

				$user = get_user_by( 'login', $user );
			}

		}
		return $user ? WPLib_Users::make_user( $user ) : null;

	}

}

