<?php

/**
 * Class WPLib_Subscriber
 *
 * The user type of 'Subscriber'
 */
class WPLib_Subscriber extends WPLib_User_Base {

	/**
	 * The user role slug
	 *
	 * @var string
	 */
	const ROLE = 'subscriber';

	/**
	 *
	 */
	static function on_load() {

		self::register_role( __( 'Subscriber', 'newclarity' ),array(

			'read',

		));

	}

}
WPLib_Subscriber::on_load();
