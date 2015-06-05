<?php

/**
 * Class WPLib_Model_Base
 *
 * @property WPLib_Item_Base $owner
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

	/**
	 * @param string $constant
	 *
	 * @return mixed|null
	 */
	function get_constant( $constant ) {

		if ( is_null( $value = parent::get_constant( $constant ) ) ) {

			$value = $this->owner->get_constant( $constant );

		}

		return $value;

	}

}
