<?php

/**
 * WPLib_Person
 *
 * @property WPLib_Person_Data $data
 * @property WPLib_Person_Tags $tags
 * @mixin WPLib_Person_Data
 * @mixin WPLib_Person_Tags
 */
class WPLib_Person extends WPLib_Post_Base {

	const POST_TYPE = 'nc_person';

	static function on_load() {

		$labels = self::register_post_type_labels( self::POST_TYPE, array(
			'name'      => __( 'People', 'wplib' ),
			'menu_name' => __( 'People', 'wplib' ),
		));

		self::register_post_type( self::POST_TYPE, array(
			'label'         => __( 'Person', 'wplib' ),
			'labels'        => $labels,
			'public'        => true,
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 32,
			'supports'      => array(
				'editor',
			),
		));

	}

}
Wplib_Person::on_load();
