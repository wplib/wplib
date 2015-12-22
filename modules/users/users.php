<?php

/**
 * Class WPLib_Users
 */
class WPLib_Users extends WPLib_Module_Base {

	const INSTANCE_CLASS = 'WPLib_User';

	/**
	 *
	 */
	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		WPLib::register_helper( __CLASS__ );

	}

	/**
	 * Checks an object to see if it has all the user-specific properties.
	 *
	 * If it does we can be almost sure it's a user.  Good enough for 99.9% of use-cases, anyway.
	 *
	 * @param object $user
	 *
	 * @return bool
	 */
	static function is_user( $user ) {

		return $user instanceof WP_User;

	}

	/**
	 * @param string $by
	 * @param string|int|object $user
	 *
	 * @return bool|WP_User
	 */
	static function get_user_by( $by, $user ) {

		$wp_user = get_user_by( $by, $user );

		return $wp_user ? static::make_user( $wp_user ) : null;

	}

	/**
	 * Log in a WordPress user programmatically
	 *
	 * @param WPLib_User_Base $user
	 *
	 * @see http://www.wprecipes.com/log-in-a-wordpress-user-programmatically
	 */
	static function do_login( $user ) {

		wp_set_current_user( $user_id = $user->ID(), $user->login() );

		wp_set_auth_cookie( $user_id );

		do_action( 'wp_login', $user->login() );

	}

	/**
	 * @param WPLib_User_Base $user
	 *
	 * @param bool|int $reassign
	 */
	static function delete_user( $user, $reassign = false ) {

		wp_delete_user( $user->ID(), $reassign );

	}

	/**
	 * @param string|int|object $user
	 * @return WP_User
	 */
	static function get_user( $user ) {

		if ( is_object( $user ) ) {

			$wp_user = $user instanceof WP_User ? $user : null;

		} else if ( is_numeric( $user ) ) {

			$wp_user = get_user_by( 'id', $user );

		} else {

			do {

				if ( ! ( $wp_user = get_user_by( 'slug', $user ) ) ) {
					break;
				}

				if ( ! ( $wp_user = get_user_by( 'login', $user ) ) ) {
					break;
				}

				if ( ! ( $wp_user = get_user_by( 'email', $user ) ) ) {
					break;
				}

				$wp_user = null;

			} while ( false );

		}

		return $wp_user ;

	}

	/**
	 * @param WP_User|int|string|null $wp_user
	 *
	 * @return mixed|null
	 */
	static function make_user( $wp_user ) {

		if ( ! ( $role_slug = self::get_assigned_role_slug( $wp_user ) ) ) {

			$user = null;

		} else {

			$role_class = WPLib_Roles::get_role_class( $role_slug );

			$user = new $role_class( $wp_user );
		}

		return $user;

	}


	/**
	 * User's role name as found in the WP_User object.
	 *
	 * @param WP_User $wp_user
	 * @return string|null
	 */
	static function get_assigned_role_slug( $wp_user ) {

		if ( ! property_exists( $wp_user, 'roles' ) || ! is_array( $wp_user->roles ) ) {

			$role_slug = null;

		} else {

			$role_slug = reset( $wp_user->roles );

		}

	 	return $role_slug ? $role_slug : WPLib_Subscriber::ROLE;

	}

}
WPLib_Users::on_load();
