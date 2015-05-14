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

	}

}
WPLib_Editor::on_load();
