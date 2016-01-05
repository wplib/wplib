<?php

/**
 * class WPLib_Authors
 */
class WPLib_Authors extends WPLib_Role_Module_Base {

	const ROLE = 'author';

	static function _CAPABILITIES() {

		return array(

			'delete_posts',
			'delete_published_pages',
			'edit_posts',
			'edit_private_posts',
			'publish_posts',
			'read',
			'upload_files',

		);
	}

	static function on_load() {

		self::add_class_action( 'init' );

	}

	static function _init() {

		self::register_role( __( 'Author', 'wplib' ) );

	}

}
WPLib_Authors::on_load();
