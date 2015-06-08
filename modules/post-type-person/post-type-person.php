<?php

/**
 * Class WPLib_People
 *
 * @mixin WPLib_Person
 * @static WPLib_Post_List_Base get_list()
 */
class WPLib_People extends WPLib_Post_Module_Base {

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

}
WPLib_People::on_load();
