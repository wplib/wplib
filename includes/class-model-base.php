<?php

/**
 * Class WPLib_Model_Base
 *
 * @property WPLib_Entity_Base $owner
 */
abstract class WPLib_Model_Base extends WPLib_Base {

	/**
	 * Get model
	 *
	 * @return WPLib_View_Base
	 */
	function view() {

		return $this->owner->view;

	}

}
