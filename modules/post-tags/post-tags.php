<?php

/**
 * Class WPLib_Post_Tags
 */
class WPLib_Post_Tags extends WPLib_Module_Base {

	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		self::register_helper( __CLASS__, 'WPLib' );

	}

}
WPLib_Post_Tags::on_load();

