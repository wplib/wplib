<?php

/**
 * Class WPLib_Author
 *
 * The user type of 'Author'
 */
class WPLib_Author extends WPLib_User_Base {

	/**
	 * The user role slug
	 *
	 * @var string
	 */
	const ROLE = 'author';

	static function on_load() {

		self::register_role( __( 'Author', 'newclarity' ),array(

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
WPLib_Author::on_load();
