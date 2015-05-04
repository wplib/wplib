<?php

/**
 * Class WPLib_Term_View_Base
 *
 * The View Base Class for Terms.
 *
 * @mixin WPLib_Term_Model_Base
 * @method WPLib_Term_Model_Base model()
 *
 * @property WPLib_Term_Base $owner
 */
abstract class WPLib_Term_View_Base extends WPLib_View_Base {

	/**
	 * Provide easy access to the term object
	 *
	 * @return WP_Term
	 */
	function term() {

		return $this->model()->term();

	}

}
