<?php

/**
 * Class WPLib_Term_Type_Base
 *
 * The Item Base Class for Term Types
 *
 * @mixin WPLib_Term_Model_Base
 * @mixin WPLib_Term_View_Base
 *
 * @property WPLib_Term_Model_Base $model
 * @property WPLib_Term_View_Base $view
 */
abstract class WPLib_Term_Base extends WPLib_Entity_Base {

	/**
	 * Child class should define a valid value for TAXONOMY
	 */
	const TAXONOMY = null;

	/**
	 * @param WP_Term|int|null $term
	 * @param array $args
	 */
	function __construct( $term, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'data' => array( 'term' => $term ),
		));

		parent::__construct( $args );

	}

	/**
	 * REgister the labels used for this term_type.
	 *
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @return object
	 */
	static function register_term_type_labels( $taxonomy, $args = array() ) {

		if ( ! isset( $args['name'] ) ) {
			/**
			 * @future Add an error message here. But, this should never happen unless developer screws up.
			 */
			$args['name'] = null;

		} else {

			/*
			 * Provide default values for each of the 3 other templates that are just singular or plural name,
			 * and for 'add_new' which does not apply the term name.
			 */
			$args = wp_parse_args( $args, array(
				'singular_name'  => $args['name'],
				'menu_name'      => $args['name'],
				'name_admin_bar' => $args['name'],
				'add_new'        => _x( 'Add New', 'term type', 'wplib' ),
			) );

			/*
			 * Get the label templates defaults.
			 */
			$labels = WPLib_Terms::default_term_type_labels();

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
			$args = wp_parse_args( $args, array(
				'add_new_item'       => sprintf( $labels['add_new_item'], $args['singular_name'] ),
				'new_item'           => sprintf( $labels['new_item'], $args['singular_name'] ),
				'edit_item'          => sprintf( $labels['edit_item'], $args['singular_name'] ),
				'view_item'          => sprintf( $labels['view_item'], $args['singular_name'] ),
				'all_items'          => sprintf( $labels['all_items'], $args['name'] ),
				'search_items'       => sprintf( $labels['search_items'], $args['name'] ),
				'parent_item_colon'  => sprintf( $labels['parent_item_colon'], $args['singular_name'] ),
				'not_found'          => sprintf( $labels['not_found'], $args['name'] ),
				'not_found_in_trash' => sprintf( $labels['not_found_in_trash'], $args['name'] ),
			));

			WPLib_Terms::set_term_type_labels( $taxonomy, $args );
		}

		return (object)WPLib_Terms::get_taxonomy_labels( $taxonomy );

	}

	/**
	 * Register the term type inside of an Entity classes' on_load() method.
	 *
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/register_term_type#Parameters
	 */
	static function register_taxonomy( $taxonomy, $args = array() ) {

		$args = wp_parse_args( $args, array(

			'label'  => WPLib_Terms::get_taxonomy_label( $taxonomy, 'name' ),
			'labels' => WPLib_Terms::get_taxonomy_labels( $taxonomy ),

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

		WPLib_Terms::_set_taxonomy_args( $taxonomy, $args );

		/*
		 * We don't need these anymore.
		 */
		WPLib_Terms::_set_taxonomy_object_types( $taxonomy, null );


	}

//	/**
//	 * @param array|string|WPLib_Query $query
//	 * @param array $args
//	 * @return WPLib_Term_List
//	 */
//	static function get_list( $query = array(), $args = array() ) {
//
//		$element_class = get_called_class();
//
//		if ( ! class_exists( $list_class = "{$element_class}_List" ) ) {
//
//			$list_class = 'WPLib_Term_List';
//
//		}
//
//		$args = wp_parse_args( $args, array(
//
//			'element_class' => $element_class,
//
//			'list_class'    => $list_class,
//
//			'query'         => wp_parse_args( $query ),
//
//		));
//
//		$list_class = $args[ 'list_class' ];
//
//		unset( $args[ 'list_class' ] );
//
//		$list = new $list_class( $args );
//
//		return $list;
//	}

}
