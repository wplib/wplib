<?php

/**
 * Class WPLib_Users
 */
class WPLib_Subscribers extends WPLib_Module_Base {

	const ROLE = 'subscriber';

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

		self::register_role( WPLib_Subscriber::ROLE, __( 'Subscriber', 'wplib' ),array(

			'read',

		));

	}
}
WPLib_Subscribers::on_load();
