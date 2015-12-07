<?php

/**
 * Class WPLib_Term_Module_Base
 */
abstract class WPLib_Term_Module_Base extends WPLib_Module_Base {

	const TAXONOMY = null;

	const INSTANCE_CLASS = null;

	/**
	 * REgister the labels used for this taxonomy.
	 *
	 * @param array $args
	 * @todo Allow taxonomy name to be passed optionally.
	 *
	 * @return object
	 */
	static function register_taxonomy_labels( $args = array() ) {

		$taxonomy = static::TAXONOMY;

		if ( ! isset( $args['name'] ) ) {

			/**
			 * @future Add an error message here. But, this should never happen unless developer screws up.
			 */

			$args['name'] = null;
			$labels = $args;

		} else {

			/*
			 * Provide default values for each of the 3 other templates that are just singular or plural name,
			 * and for 'add_new' which does not apply the term name.
			 */
			$args = wp_parse_args( $args, array(
				'singular_name'  => $args['name'],
				'menu_name'      => $args['name'],
				'name_admin_bar' => $args['name'],
			) );

			/*
			 * Get the label templates defaults.
			 */
			$labels = WPLib_Terms::default_taxonomy_labels();

			/**
			 * Now apply the postname to the defaults and merge with the registered $args
			 */
			$labels = wp_parse_args( $args, array(
				'all_items'                  => sprintf( $labels['all_items'],                  $args['name'] ),
				'edit_item'                  => sprintf( $labels['edit_item'],                  $args['name'] ),
				'new_item'                   => sprintf( $labels['new_item'],                   $args['singular_name'] ),
				'view_item'                  => sprintf( $labels['view_item'],                  $args['singular_name'] ),
				'update_item'                => sprintf( $labels['update_item'],                $args['singular_name'] ),
				'add_new_item'               => sprintf( $labels['add_new_item'],               $args['singular_name'] ),
				'new_item_name'              => sprintf( $labels['new_item_name'],              $args['singular_name'] ),
				'parent_item'                => sprintf( $labels['parent_item'],                $args['singular_name'] ),
				'parent_item_colon'          => sprintf( $labels['parent_item_colon'],          $args['singular_name'] ),
				'search_items'               => sprintf( $labels['search_items'],               $args['name'] ),
				'popular_items'              => sprintf( $labels['popular_items'],              $args['name'] ),
				'separate_items_with_commas' => sprintf( $labels['separate_items_with_commas'], $args['name'] ),
				'add_or_remove_items'        => sprintf( $labels['add_or_remove_items'],        $args['name'] ),
				'choose_from_most_used'      => sprintf( $labels['choose_from_most_used'],      $args['name'] ),
				'not_found'                  => sprintf( $labels['not_found'],                  $args['name'] ),
			));

			/**
			 * For the calling class, merge the templates and with the singular term name, etc.
			 */
			$labels = wp_parse_args( $args, $labels );

			/**
			 * For the calling class, merge the templates and with the singular and plural term type names.
			 *
			 * @example: In this code 'DBP_Video' is the calling class:
			 *
			 *      DBP_Video::initialize_labels(
			 *          'name'           => __( 'Videos', 'dbp' ),
			 *            'singular_name'  => __( 'Video', 'dbp' ),
			 *      );
			 *
			 */

			WPLib_Terms::_set_taxonomy_labels( $taxonomy, $labels );
		}

		return $labels;

	}

	/**
	 * Register the term type inside of an Item classes' on_load() method.
	 *
	 * @param null|string|array $object_types
	 * @param array $args
	 * @todo Allow taxonomy name to be passed optionally.
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/register_taxonomy#Parameters
	 */
	static function register_taxonomy( $object_types, $args = array() ) {

		if ( is_string( $object_types ) ) {

			$object_types = explode( ',', $object_types );

		}

		$args = wp_parse_args( $args, array(

			'label'  => WPLib_Terms::_get_taxonomy_label( static::TAXONOMY , 'name' ),
			'labels' => WPLib_Terms::_get_taxonomy_labels( static::TAXONOMY ),

			/**
			 * @future add a list of all possible arguments
			 */
			/*			'public'             => true,
						'publicly_queryable' => true,
						'show_ui'            => true,
						'show_in_nav_menus'  => false,
						'capability_type'    => 'term',
						'has_archive'        => false,
						'hierarchical'       => false,
						'supports'           => array( 'title' );
			*/
		) );

		if ( is_array( $object_types ) ) {

			foreach( $object_types as $object_type ) {

				WPLib_Terms::add_object_type( static::TAXONOMY, $object_type );

			}

		}

		WPLib_Terms::_set_taxonomy_args( static::TAXONOMY, $args );

	}


	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args
	 * @return WPLib_Term_List_Default[]
	 */
	static function get_list( $query = array(), $args = array() ) {

		$query = wp_parse_args( $query );

		$query['taxonomy'] = static::TAXONOMY;

		$args = wp_parse_args( $args, array(

			'list_owner' => get_called_class(),

		));

		return WPLib_Terms::get_list( $query, $args );

	}
}
