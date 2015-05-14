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


	}
}
WPLib_Author::on_load();
