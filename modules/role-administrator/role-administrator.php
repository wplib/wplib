<?php

/**
 * class WPLib_Administrator
 *
 * Contains CAPABILITIES that are unique to Single Site Administrators
 */
class WPLib_Administrators extends WPLib_Administrator_Module_Base {

	const ROLE = 'administrator';

	static function _CAPABILITIES() {

		return array(

			'update_core',
			'update_plugins',
			'update_themes',
			'install_plugins',
			'install_themes',
			'delete_themes',
			'delete_plugins',
			'edit_plugins',
			'edit_themes',
			'edit_files',
			'edit_users',
			'create_users',
			'delete_users',
			'unfiltered_html',

		);

	}

	static function on_load() {

		self::register_role( __( 'Administrator', 'wplib' ) );

	}

	/**
	 * Return array of CAPABILITIES based on is_multisite().
	 *
	 * If is_multisite() then only returns CAPABILITIES from WPLib_Administrator_Module_Base.
	 * If single site the returns the above capabilities plus the ones unique to single site.
	 *
	 * @param string|null $class_name
	 *
	 * @return string[]
	 */
	static function get_capabilities( $class_name ) {

		$parent_capabilities = WPLib_Administrator_Module_Base::get_capabilities( $class_name );

		return is_multisite()
			? $parent_capabilities
			: array_merge(
				$parent_capabilities,
				call_user_func( array( $class_name, 'get_capabilities' ), $class_name )
			  );
	}

}
WPLib_Administrators::on_load();
