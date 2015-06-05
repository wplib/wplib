<?php

/**
 * class WPLib_User_Role_Author
 */
class WPLib_User_Role_Author extends WPLib_Module_Base {

	const ROLE = 'author';

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

		self::register_role( self::ROLE, __( 'Author', 'wplib' ),array(

			'delete_posts',
			'delete_published_pages',
			'edit_posts',
			'edit_private_posts',
			'publish_posts',
			'read',
			'upload_files',

		));

	}
}
WPLib_User_Role_Author::on_load();
