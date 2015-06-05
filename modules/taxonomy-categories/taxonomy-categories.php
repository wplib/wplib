<?php

/**
 * Class WPLib_Taxonomy_Categories
 */
class WPLib_Taxonomy_Categories extends WPLib_Module_Base {

	const TAXONOMY = 'category';

	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		self::register_helper( __CLASS__, 'WPLib' );

	}

}
WPLib_Taxonomy_Categories::on_load();

