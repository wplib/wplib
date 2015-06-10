<?php

/**
 * Class WPLib_Users
 */
class WPLib_Editors extends WPLib_Module_Base {

	const ROLE = 'editor';

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

		self::register_role( self::ROLE, __( 'Editor', 'wplib' ),array(

			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_pages',
			'delete_published_posts',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_posts',
			'edit_private_pages',
			'edit_private_posts',
			'edit_published_pages',
			'edit_published_posts',
			'manage_categories',
			'manage_links',
			'moderate_comments',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'unfiltered_html',
			'upload_files',

		));


	}
}
WPLib_Editors::on_load();
