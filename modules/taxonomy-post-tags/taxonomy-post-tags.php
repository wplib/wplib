<?php

/**
 * Class WPLib_Taxonomy_Post_Tags
 */
class WPLib_Taxonomy_Post_Tags extends WPLib_Module_Base {

	const TAXONOMY = 'post_tag';

	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		self::register_helper( __CLASS__, 'WPLib' );

	}

}
WPLib_Taxonomy_Post_Tags::on_load();

