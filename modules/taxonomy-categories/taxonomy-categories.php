<?php

/**
 * Class WPLib_Categories
 */
class WPLib_Categories extends WPLib_Term_Module_Base {

	const TAXONOMY = 'category';

	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		WPLib::register_helper( __CLASS__ );

	}

}


