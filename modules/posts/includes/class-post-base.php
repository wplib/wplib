<?php

/**
 * Class WPLib_Post_Type_Base
 *
 * The Base Entity Class for Post Types
 *
 * @mixin WPLib_Post_Model_Base
 * @mixin WPLib_Post_View_Base
 *
 * @property WPLib_Post_Model_Base $model
 * @property WPLib_Post_View_Base $view
 *
 * @method void the_title_link()
 */
abstract class WPLib_Post_Base extends WPLib_Entity_Base {

	/**
	 * Child class should define a valid value for POST_TYPE
	 */
	const POST_TYPE = null;

	/**
	 * @param WP_Post|int|null $post
	 * @param array $args
	 */
	function __construct( $post, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'model' => array( 'post' => $post ),
		));

		parent::__construct( $args );

	}

	/**
	 * REgister the labels used for this post_type.
	 *
	 * @param string $post_type_slug
	 * @param array $args
	 *
	 * @return object
	 */
	static function register_post_type_labels( $post_type_slug, $args = array() ) {

		if ( ! isset( $args['name'] ) ) {
			/**
			 * @future Add an error message here. But, this should never happen unless developer screws up.
			 */
			$args['name'] = null;

		} else {

			/*
			 * Provide default values for each of the 3 other templates that are just singular or plural name,
			 * and for 'add_new' which does not apply the post name.
			 */
			$args = wp_parse_args( $args, array(
				'singular_name'  => $args['name'],
				'menu_name'      => $args['name'],
				'name_admin_bar' => $args['name'],
				'add_new'        => _x( 'Add New', 'post type', 'wplib' ),
			) );

			/*
			 * Get the label templates defaults.
			 */
			$labels = WPLib_Posts::default_post_type_labels();

			/**
			 * Now apply the postname to the defaults and merge with the registered $args
			 */
			$labels = wp_parse_args( $args, array(
				'add_new_item'       => sprintf( $labels['add_new_item'],       $args['singular_name'] ),
				'new_item'           => sprintf( $labels['new_item'],           $args['singular_name'] ),
				'edit_item'          => sprintf( $labels['edit_item'],          $args['singular_name'] ),
				'view_item'          => sprintf( $labels['view_item'],          $args['singular_name'] ),
				'all_items'          => sprintf( $labels['all_items'],          $args['name'] ),
				'search_items'       => sprintf( $labels['search_items'],       $args['name'] ),
				'parent_item_colon'  => sprintf( $labels['parent_item_colon'],  $args['singular_name'] ),
				'not_found'          => sprintf( $labels['not_found'],          $args['name'] ),
				'not_found_in_trash' => sprintf( $labels['not_found_in_trash'], $args['name'] ),
			));

			/**
			 * For the calling class, merge the templates and with the singular and plural post type names.
			 */
			$args = wp_parse_args( $args, $labels );

			WPLib_Posts::set_post_type_labels( $post_type_slug, $args );
		}

		return (object)WPLib_Posts::get_post_type_labels( $post_type_slug );

	}

	/**
	 * Register the post type inside of an Entity classes' on_load() method.
	 *
	 * @param string $post_type_slug
	 * @param array $args
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/register_post_type#Parameters
	 */
	static function register_post_type( $post_type_slug, $args = array() ) {

		$args = wp_parse_args( $args, array(

			'label'  => WPLib_Posts::get_post_type_label( 'name' ),
			'labels' => WPLib_Posts::get_post_type_labels( $post_type_slug ),

			/**
			 * @future add a list of all possible arguments
			 */
			/*			'public'             => true,
						'publicly_queryable' => true,
						'show_ui'            => true,
						'show_in_nav_menus'  => false,
						'capability_type'    => 'post',
						'has_archive'        => false,
						'hierarchical'       => false,
						'supports'           => array( 'title' );
			*/
		) );

		WPLib_Posts::_set_post_type_args( $post_type_slug, $args );

	}

	/**
	 * List post type support values given a list of values to support.
	 *
	 * Typically needed for 'post' and 'page'.
	 *
	 * @param string[] $to_remove
	 */
	static function remove_post_type_support( $to_remove ) {

		if ( ! is_array( $to_remove ) ) {

			$to_remove = array( $to_remove );

		}

		$supports = array_diff( array_keys( get_all_post_type_supports( self::POST_TYPE ) ), $to_remove );

		foreach ( $supports as $support ) {

			remove_post_type_support( self::POST_TYPE, $support );

		}

	}

	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args
	 * @return WPLib_Post_List
	 */
	static function get_list( $query = array(), $args = array() ) {

		$element_class = get_called_class();

		if ( ! class_exists( $list_class = "{$element_class}_List" ) ) {

			$list_class = 'WPLib_Post_List';

		}

		$args = wp_parse_args( $args, array(

			'element_class' => $element_class,

			'list_class'    => $list_class,

			'query'         => wp_parse_args( $query ),

		));

		$list_class = $args[ 'list_class' ];

		unset( $args[ 'list_class' ] );

		$list = new $list_class( $args );

		return $list;
	}


	/**
	 * Determines if the current query's object is a singular post URL of the calling classes' post type.
	 *
	 * @return bool
	 */
	static function is_singular() {
		/**
		 * @var WP_Query $wp_the_query
		 */
		global $wp_the_query;
		return $wp_the_query->is_singular( WPLib::constant( 'POST_TYPE', get_called_class() ) );
	}

	/**
	 * Query the posts.  Equivalent to creating a new WP_Query which both instantiates and queries the DB.
	 *
	 * @param array $args
	 * @return WP_Post[]
	 */
	function get_query( $args = array() ) {

		$args = wp_parse_args( $args );

		if ( ! is_null( static::POST_TYPE ) ) {

			$args['post_type'] = static::POST_TYPE;

		}
		/**
		 * Query the posts and set the $this->_query with the query used.
		 */
		$query = WPLib_Posts::get_query( array_filter( $args ) );

		return $query;
	}


}
