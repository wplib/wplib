<?php

/**
 * Class WPLib_Posts
 */
class WPLib_Posts extends WPLib_Module_Base {

	/**
	 * Limit posts per page to keep from overloading memory.
	 */
	const MAX_POSTS_PER_PAGE = 999;

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
	 * @var array
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
			'add_new_item'       => __( 'Add New %s',            'wplib' ),
			'new_item'           => __( 'New %s',                'wplib' ),
			'edit_item'          => __( 'Edit %s',               'wplib' ),
			'view_item'          => __( 'View %s',               'wplib' ),
			'all_items'          => __( 'All %s',                'wplib' ),
			'search_items'       => __( 'Search %s',             'wplib' ),
			'parent_item_colon'  => __( 'Parent %s:',            'wplib' ),
			'not_found'          => __( 'No %s found.',          'wplib' ),
			'not_found_in_trash' => __( 'No %s found in Trash.', 'wplib' ),
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
	 * The $args saved early to later be passed to register_post_type().
	 * @return array
	 */
	static function post_type_args() {

		return self::$_post_type_args;

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
	 * @param string $label_type
	 *
	 * @return string
	 */
	static function get_post_type_label( $label_type ) {

		return ! empty( self::$_labels[ $label_type ] ) ? self::$_labels[ $label_type ] : null;

	}

	/**
	 * @param string $post_type_slug
	 *
	 * @return array
	 */
	static function get_post_type_labels( $post_type_slug ) {

		return isset( self::$_labels[ $post_type_slug ] ) && is_array( self::$_labels[ $post_type_slug ] )
			? self::$_labels[ $post_type_slug ]
			: array();

	}

	/**
	 * @param string $post_type_slug
	 * @param array $args
	 *
	 * @return array
	 */
	static function set_post_type_labels( $post_type_slug, $args ) {

		self::$_labels[ $post_type_slug ] = $args;

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
	 * Get child posts of the post
	 *
	 * @param array $args
	 * @param object|bool $object Object with a $query_name property
	 * @param string|bool $query_name Property of $object to accept the name of the queried object.
	 * @return WP_Post[]
	 */
	static function query_posts( $args = array(), $object = false, $query_name = false ) {

		$args = wp_parse_args( $args, array(
			'post_type'   => 'any',
			'posts_per_page' => self::MAX_POSTS_PER_PAGE,
			'index_by' => false,
		));

		$query = new WPLib_Query( $args );

		if ( ! preg_match( '#^(post_id|post_name)$#', $args[ 'index_by' ] ) ) {

			$posts = $query->posts;

		} else {

			$posts = array();
			foreach( $query->posts as $post ) {

				$posts[ $post->{$args[ 'index_by' ]} ] = $post;

			}

		}

		if ( $object && $query_name ) {
			/*
			 * This is how we return a value back when we can't use pass-by-reference.
			 */
			$object->$query_name = $query;

		}

		return $posts;
	}

}
WPLib_Posts::on_load();
