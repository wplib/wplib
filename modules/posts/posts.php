<?php

/**
 * Class WPLib_Posts
 */
class WPLib_Posts extends WPLib_Module_Base {

	const INSTANCE_CLASS = 'WPLib_Post';

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
	 * Value to limit the maximum posts per page requested for any given WP_Query
	 *
	 * @var array|null
	 */
	private static $_max_posts_per_page = 999;


	/**
	 * Run on WordPress's 'init' hook to register all the post types defined in classes that extend this class.
	 */
	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		WPLib::register_helper( __CLASS__ );

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

		foreach ( self::$_post_type_args as $post_type => $post_type_args ) {

			/**
			 * This filter hook is fired once per post type and called
			 * just before WordPress' register_post_type() is called.
			 *
			 * @since 0.6.6
			 *
			 * @stability 1 - Experimental
			 */
			$post_type_args = apply_filters( 'wplib_post_type_args', $post_type_args, $post_type );

			/*
			 * For each of the post types that have been previously
			 * initialized, register them for WordPress.
			 */
			register_post_type( $post_type, $post_type_args );
		}

		/**
		 * This action hook fires AFTER WPLib calls register_post_type()
		 * for all the post types registered in the on_load() for a
		 * subclass of WPLib_Post_Module_Base.
		 *
		 * @note The first 'post' in the hook name means "after", vs.
		 *       "pre" which means before.
		 *
		 * @since 0.6.6
		 *
		 * @stability 1 - Experimental
		 */
		do_action( 'wplib_post_register_post_types' );
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

		foreach ( $wp_post_types as $post_type => $post_type_object ) {

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
	 *
	 * @param WP_Post|object|int|string|array|null|bool $_post
	 * @param string|bool $post_type
	 *
	 * @return null|WP_Post
	 */
	static function get_post( $_post, $post_type = false ) {

		switch ( gettype( $_post ) ) {
			case 'integer';
				$_post = get_post( $_post );
				break;

			case 'string';
				if ( is_numeric( $_post ) ) {

					$_post = get_post( absint( $_post ) );

				} else if ( $post_type ) {
					/*
					 * Get post by slug
					 */
					$_post = get_page_by_path( $_post, OBJECT, $post_type );

				} else {
					$_post = null;

				}
				break;

			case 'array';
				if ( isset( $_post['ID'] ) ) {
					$_post = get_post( absint( $_post['ID'] ) );

				} else if ( isset( $_post['post'] ) ) {
					$_post = self::get_post( $_post['post'] );

				}
				break;

			case 'object';
				if ( ! is_a( $_post, 'WP_Post' ) && property_exists( $_post, 'ID' ) ) {
					$_post = get_post( absint( $_post->ID ) );

				}
				break;

			default:
				$_post = null;

		}

		return $_post;
	}

	/**
	 * @param string     $by
	 * @param int|string $value
	 * @param array      $args
	 *
	 * @return WP_Post
	 */
	static function get_post_by( $by, $value, $args = array() ) {
		$_post     = null;
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
			$_post = $query->post;
		}

		return $_post;
	}

	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args
	 * @return WPLib_Post_List_Default
	 */
	static function get_list( $query = array(), $args = array() ) {

		$args = wp_parse_args( $args, array(

			'default_list'  => 'WPLib_Post_List_Default',
			'items'         =>
				function( $query ) {

					$posts = $query instanceof WP_Query
						? $query->posts
						: WPLib_Posts::get_posts( $query );

					return $posts;

				},

		));

		return parent::get_list( $query, $args );

	}

	/**
	 * Query the posts.  Equivalent to creating a new WP_Query which both instantiates and queries the DB.
	 *
	 * @param array $args
	 * @return WPLib_Query
	 *
	 * @future https://github.com/wplib/wplib/issues/3
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026403
	 */
	static function get_query( $args = array() ) {

		if ( $args instanceof WP_Query ) {

			/**
			 * @future Fix to return a WPLib_Query, not a WP_Query.
			 */
			$query = $args;

		} else {

			if ( ! isset( $args['post_type'] ) ) {

				if ( $post_type = static::get_constant( 'POST_TYPE' ) ) {

					$args['post_type'] = $post_type;

				}

			}

			if ( isset( $args['post_type'] ) && WPLib_Post::POST_TYPE === $args['post_type'] ) {

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
				'posts_per_page' => self::max_posts_per_page(),
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

				$index_field = 'id' === $match[1] ? 'ID' : 'post_name';
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
	 * Create new Instance of a Post MVI
	 *
	 * @param WP_Post|int $_post
	 * @param array $args {
	 *
	 *      @type string $instance_class
	 *      @type string $list_owner
	 *
	 *}
	 * @return mixed
	 *
	 * @future Alias this with make_new_post() so it can be called as WPLib::make_new_post( $post_id )
	 */
	static function make_new_item( $_post, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'instance_class' => false,
			'list_owner' => 'WPLib_Posts',
		));

		if ( is_numeric( $_post ) ) {

			$_post = WP_Post::get_instance( $_post );

		}

		if ( ! $args[ 'instance_class' ] ) {

			$args['instance_class'] = self::get_post_type_class( $_post->post_type );

		}

		if ( ! $args[ 'instance_class' ] ) {

			$args['instance_class'] = WPLib::get_constant( 'INSTANCE_CLASS', $args['list_owner'] );

		}

		$instance_class = $args['instance_class'];

		return $instance_class ? new $instance_class( $_post ) : null;

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
	 *
	 * @future Rename to get_post_class() and deprecate this name
	 *
	 */
	static function get_post_type_class( $post_type ) {

		$classes = self::post_type_classes();

		return ! empty( $classes[ $post_type ] ) ? $classes[ $post_type ] : null;

	}

	/**
	 * @return string[]
	 *
	 * @future Enhance this to support multiple classes per post type
	 * @future Rename to post_classes() and deprecate this name
	 */
	static function post_type_classes() {

		return WPLib::get_child_classes( 'WPLib_Post_Base', 'POST_TYPE' );

	}

	/**
	 * Query the posts, return a post list.
	 *
	 * @param array $args
	 * @return WP_Post[]
	 */
	static function get_posts( $args = array() ) {

		$query = WPLib_Posts::get_query( $args );
		return $query->posts;

	}

	/**
	 * @var array
	 */
	private static $_post_stack = array();

	/**
	 * @param WP_Post|bool $value
	 */
	static function push_post( $value = false ) {
		global $post;
		array_push( self::$_post_stack, $post );
		if ( $value instanceof WP_Post ) {
			$post = $value;
		}
	}

	/**
	 *
	 */
	static function pop_post() {
		global $post;
		if ( count( self::$_post_stack ) ) {
			$post = array_pop( self::$_post_stack );
		}
	}

	/**
	 * Return the post type of the queried object.
	 *
	 * @return false|null|string
	 */
	static function get_queried_post_type() {

		$queried_object = get_queried_object();
		return $queried_object instanceof WP_Post
			? get_post_type( $queried_object )
			: null;

	}

	/**
	 * @return int
	 */
	static function max_posts_per_page() {

		return self::$_max_posts_per_page;

	}

	/**
	 * @param int $value
	 */
	static function set_max_posts_per_page( $value ) {

		self::$_max_posts_per_page = absint( $value );

	}


}
WPLib_Posts::on_load();
