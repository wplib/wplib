<?php

/**
 * Class WPLib_Users
 */
class WPLib_Subscribers extends WPLib_Role_Module_Base {

	const ROLE = 'subscriber';

	static function _CAPABILITIES() {

		return array(

			'read',

		);
	}

	static function on_load() {

		self::register_role( __( 'Subscriber', 'wplib' ) );

	}

}
WPLib_Subscribers::on_load();
