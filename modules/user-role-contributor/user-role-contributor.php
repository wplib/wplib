<?php

/**
 * class WPLib_Contributors
 */
class WPLib_Contributors extends WPLib_Module_Base {

	const ROLE = 'contributor';

	/**
	 * Run on WordPress's 'init' hook to register all the user types defined in classes that extend this class.
	 */
	static function on_load() {

		/**
		 * Hook wp_loaded so roles can be initialized
		 */
		self::add_class_action( 'wp_loaded' );

	}
	/**
	 * Register all the default roles.
	 */
	static function _wp_loaded() {

		self::register_role( self::ROLE, __( 'Contributor', 'wplib' ),array(

			'delete_posts',
			'edit_posts',
			'read',

		));

	}
}
WPLib_Contributors::on_load();
