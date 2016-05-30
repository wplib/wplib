<?php

/**
 * Class WPLib_Post_Tags
 */
class WPLib_Post_Tags extends WPLib_Term_Module_Base {

	const TAXONOMY = 'post_tag';

	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		WPLib::register_helper( __CLASS__ );

	}

}


