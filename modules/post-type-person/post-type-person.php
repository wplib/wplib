<?php

/**
 * Class WPLib_Post_Type_Person
 *
 * @mixin WPLib_Person
 * @static WPLib_Post_List_Base get_list()
 */
class WPLib_Post_Type_Person extends WPLib_Post_Module_Base {

	const POST_TYPE = 'wplib_person';

	const INSTANCE_CLASS = 'WPLib_Person';

	static function on_load() {

		self::register_helper( __CLASS__, 'WPLib' );

		$labels = self::register_post_type_labels( array(
			'name'          => __( 'People', 'wplib' ),
			'singular_name' => __( 'Person', 'wplib' ),
			'menu_name'     => __( 'People', 'wplib' ),
		));

		self::register_post_type( array(
			'label'         => __( 'Person', 'wplib' ),
			'labels'        => $labels,
			'public'        => true,
			'menu_icon'     => 'dashicons-admin-users',
			'supports'      => array(
				'title',
				'editor',
			),
		));

	}

	/**
	 * @return WPLib_Post_List_Base
	 */
	static function get_person_list() {

		return static::get_list( $query, $args );

	}

}
WPLib_Post_Type_Person::on_load();
