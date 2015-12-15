<?php

/**
 * class WPLib_Authors
 */
class WPLib_Authors extends WPLib_Role_Module_Base {

	const ROLE = 'author';

	static $CAPABILITIES = array(

		'delete_posts',
		'delete_published_pages',
		'edit_posts',
		'edit_private_posts',
		'publish_posts',
		'read',
		'upload_files',

	);

	static function on_load() {

		self::register_role( __( 'Author', 'wplib' ) );

	}

}
WPLib_Authors::on_load();
