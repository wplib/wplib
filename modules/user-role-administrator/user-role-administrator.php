<?php

/**
 * class WPLib_User_Role_Administrator
 */
class WPLib_User_Role_Administrator extends WPLib_Module_Base {

	const ROLE = 'administrator';

	/**
	 * Run on WordPress's 'init' hook to register all the user types defined in classes that extend this class.
	 */
	static function on_load() {

		/**
		 * Hook wp_loaded so roles can be initialized
		 */
		self::add_class_action( 'wp_loaded' );

	}
	/**
	 * Register all the default roles.
	 */
	static function _wp_loaded() {

		self::register_role( self::ROLE, __( 'Administrator', 'wplib' ), array(

			'activate_plugins',
			'add_users',
			'create_roles',
			'create_users',
			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_plugins',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_posts',
			'delete_roles',
			'delete_themes',
			'delete_users',
			'edit_dashboard',
			'edit_files',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_plugins',
			'edit_posts',
			'edit_private_pages',
			'edit_published_pages',
			'edit_published_posts',
			'edit_roles',
			'edit_theme_options',
			'edit_themes',
			'edit_users',
			'export',
			'import',
			'install_plugins',
			'install_themes',
			'list_roles',
			'list_users',
			'manage_categories',
			'manage_links',
			'manage_options',
			'moderate_comments',
			'promote_users',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'remove_users',
			'restrict_content',
			'switch_themes',
			'unfiltered_html',
			'unfiltered_upload',
			'update_core',
			'update_plugins',
			'update_themes',
			'upload_files',

		) );

	}
}
WPLib_User_Role_Administrator::on_load();
