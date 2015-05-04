<?php

/**
 * Class WPLib_Post_View_Base
 *
 * The View Base Class for Posts.
 *
 * @mixin WPLib_Post_Model_Base
 * @method WPLib_Post_Model_Base model()
 *
 * @property WPLib_Post_Base $owner
 */
abstract class WPLib_Post_View_Base extends WPLib_View_Base {

	/**
	 * Provide easy access to the post object
	 *
	 * @return WP_Post
	 */
	function post() {

		return $this->model()->post();

	}

}
