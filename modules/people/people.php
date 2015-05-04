<?php

/**
 * Class WPLib_People
 */
class WPLib_People extends WPLib_Module_Base {

	static function on_load() {

		$labels = WPLib_Person::register_post_type_labels( WPLib_Person::POST_TYPE, array(
			'name'      => __( 'People', 'wplib' ),
			'menu_name' => __( 'People', 'wplib' ),
		));

		WPLib_Person::register_post_type( WPLib_Person::POST_TYPE, array(
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
WPLib_People::on_load();
