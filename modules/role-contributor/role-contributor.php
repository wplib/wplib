<?php

/**
 * class WPLib_Contributors
 */
class WPLib_Contributors extends WPLib_Role_Module_Base {

	const ROLE = 'contributor';

	static $CAPABILITIES = array(

		'delete_posts',
		'edit_posts',
		'read',

	);

	static function on_load() {

		self::register_role( __( 'Contributor', 'wplib' ) );

	}

}
WPLib_Contributors::on_load();
