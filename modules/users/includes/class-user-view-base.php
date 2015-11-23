<?php

/**
 * Class WPLib_User_View_Base
 *
 * The View Base Class for Users.
 *
 * @mixin WPLib_User_Model_Base
 * @method WPLib_User_Model_Base model()
 *
 * @property WPLib_User_Base $owner
 */
abstract class WPLib_User_View_Base extends WPLib_View_Base {

	/**
	 * Provide easy access to the user object
	 *
	 * @return WP_User
	 */
	function user() {

		return $this->model()->user();

	}

}
