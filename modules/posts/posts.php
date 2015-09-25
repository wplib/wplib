<?php

/**
 * Class WPLib_Posts
 */
class WPLib_Posts extends WPLib_Module_Base {

	/**
	 * The default post type labels for those labels not set for a post type.
	 *
	 * @var array
	 */
	private static $_default_labels;

	/**
	 * The post type labels
	 *
	 * @var string[]
	 */
	private static $_labels = array();

	/**
	 * The $args saved early to later be passed to register_post_type().
	 *
	 * @var array|null
	 */
	private static $_post_type_args = array();


	/**
	 * Run on WordPress's 'init' hook to register all the post types defined in classes that extend this class.
	 */
	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		self::register_helper( __CLASS__, 'WPLib' );

		/*
		 * Process these templates once for language translation.
		 * Use this base class' name used to find the templates.
		 */
		self::$_default_labels = array(
			'add_new_item'       => _x( 'Add New %s',            'posts', 'wplib' ),
			'new_item'           => _x( 'New %s',                'posts', 'wplib' ),
			'edit_item'          => _x( 'Edit %s',               'posts', 'wplib' ),
			'view_item'          => _x( 'View %s',               'posts', 'wplib' ),
			'all_items'          => _x( 'All %s',                'posts', 'wplib' ),
			'search_items'       => _x( 'Search %s',             'posts', 'wplib' ),
			'parent_item_colon'  => _x( 'Parent %s:',            'posts', 'wplib' ),
			'not_found'          => _x( 'No %s found.',          'posts', 'wplib' ),
			'not_found_in_trash' => _x( 'No %s found in Trash.', 'posts', 'wplib' ),
		);

		self::add_class_action( 'init' );
		self::add_class_action( 'init', 99 );
		self::add_class_action( 'wp_loaded' );

	}

	/**
	 * Run on WordPress's 'init' hook to register all the post types defined in classes that extend this class.
	 */
	static function _init() {

		foreach( self::$_post_type_args as $post_type => $post_type_args ) {
			/*
			 * For each of the post types that have been previously
			 * initialized, register them for WordPress.
			 */
			register_post_type( $post_type, $post_type_args );
		}

	}

	/**
	 * Clear out this data, we don't need them anymore
	 * But do it late so that a regular 'init' can still access them, i.e. for changing labels of 'post' post type.
	 */
	static function _init_99() {

		self::_clear_post_type_args();

	}

	/**
	 * Add a property to post type objects so they can be self-identified.
	 */
	static function _wp_loaded() {

		global $wp_post_types;

		foreach( $wp_post_types as $post_type => $post_type_object ) {

			/**
			 * Decided not to prefix this property, because really?
			 */
			$post_type_object->is_post_type_object = true;

		}

	}

	/**
	 * Save $args for later passing to register_post_type().
	 *
	 * @param string $post_type_slug
	 * @param array $args
	 */
	static function _set_post_type_args( $post_type_slug, $args ) {

		self::$_post_type_args[ $post_type_slug ] = $args;

	}

	/**
	 * Run on WordPress's 'init' hook to register all the post types defined in classes that extend this class.
	 */
	static function _clear_post_type_args() {

		/*
		 * No need to hang on to this data anymore.
		 */
		self::$_labels = null;
		self::$_post_type_args = null;
	}

	/**
	 * @return array
	 */
	static function default_post_type_labels() {

		return self::$_default_labels;

	}

	/**
	 * @param string $post_type
	 * @param string $label_type
	 *
	 * @return string
	 */
	static function _get_post_type_label( $post_type, $label_type ) {

		$labels = static::_get_post_type_labels( $post_type );

		return ! empty( $labels[ $label_type ] ) ? $labels[ $label_type ] : null;

	}

	/**
	 * @param string $post_type
	 *
	 * @return array
	 */
	static function _get_post_type_labels( $post_type ) {

		return isset( self::$_labels[ $post_type ] ) && is_array( self::$_labels[ $post_type ] )
			? self::$_labels[ $post_type ]
			: array();

	}

	/**
	 * @param string $post_type
	 * @param array $args
	 *
	 * @return array
	 */
	static function _set_post_type_labels( $post_type, $args ) {

		self::$_labels[ $post_type ] = $args;

	}

	/**
	 * @param WP_Post|object|int|string|array|null|bool $post
	 * @param string|bool $post_type
	 *
	 * @return null|WP_Post
	 */
	static function get_post( $post, $post_type = false ) {

		switch ( gettype( $post ) ) {
			case 'integer';
				$post = get_post( $post );
				break;

			case 'string';
				if ( is_numeric( $post ) ) {
					$post = get_post( absint( $post ) );

				} else if ( $post_type ) {
					/*
					 * Get post by slug
					 */
					$post = get_page_by_path( $post, OBJECT, $post_type );

				} else {
					$post = null;

				}
				break;

			case 'array';
				if ( isset( $post['ID'] ) ) {
					$post = get_post( absint( $post['ID'] ) );

				} else if ( isset( $post['post'] ) ) {
					$post = self::get_post( $post['post'] );

				}
				break;

			case 'object';
				if ( ! is_a( $post, 'WP_Post' ) && property_exists( $post, 'ID' ) ) {
					$post = get_post( absint( $post->ID ) );

				}
				break;

			default:
				$post = null;

		}

		return $post;
	}

	/**
	 * @param string     $by
	 * @param int|string $value
	 * @param array      $args
	 *
	 * @return WP_Post
	 */
	static function get_post_by( $by, $value, $args = array() ) {
		$post     = null;
		$criteria = array( 'post_status' => 'publish' );
		switch ( $by ) {
			case 'slug':
			case 'post_name':
				$criteria['name'] = trim( $value );
				break;

			case 'post_id':
			case 'post_ID':
			case 'id':
			case 'ID':
				$criteria['p'] = intval( $value );
				break;
		}
		$query = new WP_Query( wp_parse_args( $args, $criteria ) );
		if ( count( $query->posts ) ) {
			$post = $query->post;
		}

		return $post;
	}

	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args
	 * @return WPLib_Post_List_Default[]
	 */
	static function get_list( $query = array(), $args = array() ) {

		$args = wp_parse_args( $args, array(

			'list_owner'    => get_called_class(),

		));

		$try_class = $args[ 'list_owner' ];


		$args = wp_parse_args( $args, array(

			'list_class'    => "{$try_class}_List",

			'default_list'  => 'WPLib_Post_List_Default',

		));

		if ( ! class_exists( $args['list_class'] ) ) {

			do {
				/*
				 * Check first to see if it already exists. Maybe it was passed in and thus does?
				 */
				if ( class_exists( $args['list_class'] ) ) {
					break;
				}

				/*
				 * Add '_Default' to last list class checked, i.e. WPLib_Posts_List_Default for WPLib_Posts::get_list()
				 */
				$args['list_class'] = "{$args[ 'list_class' ]}_Default";
				if ( class_exists( $args['list_class'] ) ) {
					break;
				}

				$args['list_class'] = false;

				$try_class = preg_replace( '#^(.+)_Base$#', '$1', get_parent_class( $try_class ) );

				if ( ! $try_class ) {
					break;
				}

				/*
				 * Add '_List' to element class, i.e. WPLib_Posts_List for WPLib_Posts::get_list()
				 */
				$args['list_class'] = "{$try_class}_List";
				if ( class_exists( $args['list_class'] ) ) {
					break;
				}

			} while ( $try_class );

		}

		if ( ! $args[ 'list_class' ] ) {
			/*
			 * Give up and use default, i.e. WPLib_List_Default
			 */
			$args['list_class'] = $args[ 'default_list' ];

		}

		$list_class = $args[ 'list_class' ];

		unset( $args[ 'list_class' ], $args[ 'list_default' ] );


		$args[ 'instance_class' ] = WPLib::get_constant( 'INSTANCE_CLASS', $args[ 'list_owner' ] );

		if ( ! ( $post_type = WPLib::get_constant( 'POST_TYPE', $args[ 'instance_class' ] ) ) ) {

			$post_type = 'post';

		}

		if ( ! is_array( $query ) ) {

			$query = wp_parse_args( $query );

		}

		$query = WPLib_Posts::get_query( wp_parse_args( $query, array(

			'post_type' => $post_type,

		)));

		$list = isset( $query->posts ) ? new $list_class( $query->posts, $args ) : null;

		return $list;
	}

	/**
	 * Query the posts.  Equivalent to creating a new WP_Query which both instantiates and queries the DB.
	 *
	 * @param array $args
	 * @return WP_Post[]
	 *
	 * @todo https://github.com/wplib/wplib/issues/3
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026403
	 */
	static function get_query( $args = array() ) {

		if ( $args instanceof WP_Query ) {

			$query = $args;

		} else {

			if ( isset( $args['post_type'] ) && WPLib_Post::POST_TYPE == $args['post_type'] ) {

				if ( ! isset( $args['order'] ) ) {
					$args['order'] = 'DESC';
				}

				if ( ! isset( $args['orderby'] ) ) {
					$args['orderby'] = 'ID';
				}

			}

			$args = wp_parse_args( $args, array(
				'post_type'      => 'any',
				'post_status'    => 'publish',
				'posts_per_page' => WPLib::max_posts_per_page(),
				'index_by'       => false,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			) );

			if ( ! empty( $args['post__not_in'] ) && ! is_array( $args['post__not_in'] ) ) {

				$args['post__not_in'] = array( $args['post__not_in'] );

			}

			$query = new WPLib_Query( $args );

			if ( $args['index_by'] && preg_match( '#^(post_(id|name)|id|name)$#', $args['index_by'], $match ) ) {

				$index_field = 'id' == $match[1] ? 'ID' : 'post_name';
				$posts       = array();
				foreach ( $query->posts as $post ) {

					$posts[ $post->$index_field ] = $post;

				}
				$query->posts = $posts;

			}

		}

		return $query;
	}

	/**
	 * Create new Instance of a Post MVE
	 *
	 * @param WP_Post $post
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function make_new_item( $post, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'instance_class' => false,
			'list_owner' => 'WPLib_Posts',
		));

		if ( ! $args[ 'instance_class' ] ) {

			$args['instance_class'] = WPLib::get_constant( 'INSTANCE_CLASS', $args['list_owner'] );

		}

		if ( ! $args[ 'instance_class' ] ) {

			$args['instance_class'] = self::get_post_type_class( $post->post_type );

		}

		$instance_class = $args['instance_class'];

		return new $instance_class( $post );

	}

	/**
	 * Get a list of post types that support a specific named feature.
	 *
	 * @param string $feature
	 *
	 * @return array
	 */
	static function get_post_types_supporting( $feature ) {

		global $_wp_post_type_features;

		$post_types = array_keys(

			wp_filter_object_list( $_wp_post_type_features, array( $feature => true ) )

		);

		return $post_types;
	}

	/**
	 * @param string $post_type
	 *
	 * @return string|null
	 */
	static function get_post_type_class( $post_type ) {

		$classes = self::get_post_type_classes();

		return ! empty( $classes[ $post_type ] ) ? $classes[ $post_type ] : null;

	}

	/**
	 * @return array
	 *
	 * @todo Enhance this to support multiple classes per post type
	 */
	static function get_post_type_classes() {

		if ( ! ( $post_type_classes = WPLib::cache_get( $cache_key = 'post_type_classes' ) ) ) {

			WPLib::autoload_all_classes();

			$post_type_classes = array();

			foreach ( array_reverse( get_declared_classes() ) as $class_name ) {

				if ( is_subclass_of( $class_name, 'WPLib_Post_Base' )  && $post_type = WPLib::get_constant( 'POST_TYPE', $class_name ) ) {

					$post_type_classes[ $post_type ] = $class_name;

				}

			}

			WPLib::cache_set( $cache_key, $post_type_classes );

		}

		return $post_type_classes;

	}

//	/**
//	 * @param $thing
//	 *
//	 * @return array
//	 */
//	static function get_post_and_item( $thing ) {
//
//		if ( ! $thing ) {
//
//			$thing = WPLib::theme()->post();
//
//		}
//
//		if ( is_a( $thing, 'WP_Post' ) ) {
//
//			$item = WPLib::make_new_item( $post = $thing );
//
//		} else {
//
//			$item = $thing;
//
//			if ( $thing instanceof WPLib_Post_Base ) {
//
//				$post = $thing->post();
//
//			}
//		}
//
//		return array( $post, $item );
//
//	}

}
WPLib_Posts::on_load();
