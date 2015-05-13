<?php

/**
 * Class WPLib_Contributor
 *
 * The user type of 'Contributor'
 */
class WPLib_Contributor extends WPLib_User_Base {

	/**
	 * The user role slug
	 *
	 * @var string
	 */
	const ROLE = 'contributor';


	static function on_load() {

		self::register_role( __( 'Contributor', 'newclarity' ),array(

			'delete_posts',
			'edit_posts',
			'read',

		));

	}

}
WPLib_Contributor::on_load();
