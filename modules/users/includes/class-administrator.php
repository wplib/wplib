<?php

/**
 * Class WPLib_Administrator
 *
 * The user type of 'Administrator'
 */
class WPLib_Administrator extends WPLib_User_Base {

	/**
	 * The user role slug
	 *
	 * @var string
	 */
	const ROLE = 'administrator';

	static function on_load() {


	}
}
WPLib_Administrator::on_load();
