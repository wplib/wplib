<?php

/**
 * Class WPLib_Roles
 */
class WPLib_Roles extends WPLib_Module_Base {

	const INSTANCE_CLASS = 'WPLib_Role';

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
	 * @var string $role_slug
	 * @return array|mixed
	 */
	static function get_role_class( $role_slug ) {

		$role_classes = self::role_classes();

		return isset( $role_classes[ $role_slug ] )
			? $role_classes[ $role_slug ]
			: null;

	}

	/**
	 * @return array|mixed
	 */
	static function role_classes() {

		return WPLib::get_child_classes( 'WPLib_User_Base', 'ROLE' );

	}

	/**
	 * @param string $role_slug
	 *
	 * @return bool
	 */
	static function current_user_is( $role_slug ) {
		/**
		 * @var WP_User $current_user
		 */
		$current_user = wp_get_current_user();

		return isset( $current_user->roles )
			&& is_array( $current_user->roles )
			&& in_array( $role_slug, $current_user->roles );

	}

}
WPLib_Roles::on_load();
