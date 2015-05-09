<?php

/**
 * Class WPLib_People
 *
 * @mixin WPLib_Person
 * @static WPLib_Person[] get_list()
 */
class WPLib_People extends WPLib_Module_Base {

	const INSTANCE_CLASS = 'WPLib_Person';

	static function on_load() {

		self::register_helper( 'WPLib_Person', 'WPLib' );
		self::register_helper( __CLASS__, 'WPLib' );

	}

	static function get_person_list() {

		return WPLib_Person::get_list( $query, $args );

	}

}
WPLib_People::on_load();
