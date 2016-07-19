<?php

/**
 * Class WPLib_Post_Module_Base
 */
abstract class WPLib_Post_Module_Base extends WPLib_Module_Base {

	const POST_TYPE = 'any';

	const INSTANCE_CLASS = null;

	/**
	 * Register the labels used for this post_type.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	static function register_post_type_labels( $args = array() ) {

		if ( ! isset( $args['name'] ) ) {
			/**
			 * @future Add an error message here. But, this should never happen unless developer screws up.
			 */
			$args['name'] = null;

			$labels = $args;

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
			$labels = wp_parse_args( $args, $labels );

			WPLib_Posts::_set_post_type_labels( static::POST_TYPE, $labels );
		}

		return $labels;

	}

	/**
	 * Register the post type inside of an Item classes' on_load() method.
	 *
	 * @param array $args
	 *
	 * @link  http://codex.wordpress.org/Function_Reference/register_post_type#Parameters
	 */
	static function register_post_type( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'label'  => WPLib_Posts::_get_post_type_label( static::POST_TYPE, 'name' ),
			'labels' => WPLib_Posts::_get_post_type_labels( static::POST_TYPE ),

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

		if ( isset( $args[ 'taxonomies' ] ) ) {

			$message = 'Cannot set taxonomies via WPLib::%s(). Assign this post type via WPLib::register_taxonomy()';
			WPLib::trigger_error( sprintf( $message, __METHOD__ ) );

		}

		WPLib_Posts::_set_post_type_args( static::POST_TYPE, $args );

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

		$supports = array_diff( array_keys( get_all_post_type_supports( static::POST_TYPE ) ), $to_remove );

		foreach ( $supports as $support ) {

			remove_post_type_support( static::POST_TYPE, $support );

		}

	}

	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args {
	 *
	 *      @type string $list_class        The specific class for the list, i.e. WPLib_Post_List
	 *
	 *      @type string $default_list      The default list if no $list_class, i.e. WPLib_Post_List_Default
	 *
	 *      @type string $items The array of items, or a callable that will return a list of items.
	 *
	 *      @type string $list_owner        The class "owning" the list, typically "Owner" if Owner::get_list()
	 *
	 *      @type string $instance_class    The class for items in the list, i.e. WP_Post
	 *
	 *      @type string $queried             'local' or 'queried'
	 * }
	 *
	 * @return WPLib_Post_List_Default[]
	 */
	static function get_list( $query = array(), $args = array() ) {


		$args = wp_parse_args( $args, array(

			'queried' => false,

		));

		if ( $query instanceof WP_Query ) {

			/**
			 * @future Trigger Error here if $query not
			 *       all matching static::POST_TYPE.
			 */

		} if ( $args['queried'] ) {

			$args['post_type'] = WPLib::get_queried_post_type();

		} else {

			$query['post_type'] = static::POST_TYPE;

		}

		$args = wp_parse_args( $args, array(

			'list_owner' => get_called_class(),

		));

		return WPLib_Posts::get_list( $query, $args );

	}

	/**
	 * @return mixed|null
	 */
	static function instance_class() {

		do {

			/**
			 * See if module has an INSTANCE_CLASS constant defined.
			 */
			if ( $instance_class = parent::instance_class() ) {
				break;
			}

			$instance_class = 'WPLib_Post_Default';

		} while ( false );

		return $instance_class;

	}

	/**
	 * Query the posts.  Equivalent to creating a new WP_Query which both instantiates and queries the DB.
	 *
	 * @param array $args
	 * @return WP_Post[]
	 */
	static function get_query( $args = array() ) {

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
		return $wp_the_query->is_singular( static::POST_TYPE );
	}

	/**
	 * Allows a post type to attach a taxonomy that is registered by someone else's code.
	 *
	 * @param string $taxonomy
	 *
	 * @stability 1 - Experimental
	 */
	static function attach_taxonomy( $taxonomy ) {

		$VALID_HOOK = 'wplib_post_register_taxonomies';

		if ( current_action() !== $VALID_HOOK ) {

			$class_name = get_called_class();

		    $err_msg = __( '%s::%s() will only work correctly if called within the action hook %s.', 'wplib' );

			WPLib::trigger_error( sprintf( $err_msg, $class_name, __FUNCTION__, $VALID_HOOK ) );

		}

		register_taxonomy_for_object_type( $taxonomy, static::POST_TYPE );

	}

}
