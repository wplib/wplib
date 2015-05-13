<?php

/**
 * Class WPLib_Editor
 *
 * The user type of 'Editor'
 */
class WPLib_Editor extends WPLib_User_Base {

	/**
	 * The user role slug
	 *
	 * @var string
	 */
	const ROLE = 'editor';

	/**
	 *
	 */
	static function on_load() {

		self::register_role( __( 'Editor', 'newclarity' ),array(

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
WPLib_Editor::on_load();
