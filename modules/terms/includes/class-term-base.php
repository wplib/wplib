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
abstract class WPLib_Term_Base extends WPLib_Item_Base {

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
			'model' => array( 'term' => $term ),
		));

		parent::__construct( $args );

	}

	/**
	 * REgister the labels used for this taxonomy.
	 *
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @return object
	 */
	static function register_taxonomy_labels( $taxonomy, $args = array() ) {

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
			$args = wp_parse_args( $args, $labels );

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

			WPLib_Terms::set_taxonomy_labels( $taxonomy, $args );
		}

		return (object)WPLib_Terms::get_taxonomy_labels( $taxonomy );

	}

	/**
	 * Register the term type inside of an Item classes' on_load() method.
	 *
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/register_taxonomy#Parameters
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

}
