<?php

/**
 * Class WPLib_Terms
 */
class WPLib_Terms extends WPLib_Module_Base {

	const TAXONOMY = 'any';

	const INSTANCE_CLASS = null;

	/**
	 * The default term type labels for those labels not set for a term type.
	 *
	 * @var array
	 */
	private static $_default_labels;

	/**
	 * The term type labels
	 *
	 * @var string[]
	 */
	private static $_labels = array();

	/**
	 * The $args saved early to later be passed to register_taxonomy().
	 *
	 * @var array
	 */
	private static $_taxonomy_args = array();

	/**
	 * The object types to which this taxonomy is attached ( e.g. post, page, etc. )
	 *
	 * @var array
	 */
	private static $_object_types = array();

	/**
	 * Run on WordPress's 'init' hook to register all the term types defined in classes that extend this class.
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
			'add_new'                    => _x( 'Add New',                  'terms', 'wplib' ),
			'all_items'                  => _x( 'All %s',                   'terms', 'wplib' ),
			'edit_item'                  => _x( 'Edit %s',                  'terms', 'wplib' ),
			'new_item'                   => _x( 'New %s',                   'terms', 'wplib' ),
			'view_item'                  => _x( 'View %s',                  'terms', 'wplib' ),
			'update_item'                => _x( 'Update %s',                'terms', 'wplib' ),
			'add_new_item'               => _x( 'Add New %s',               'terms', 'wplib' ),
			'new_item_name'              => _x( 'New %s Name',              'terms', 'wplib' ),
			'parent_item'                => _x( 'Parent %s',                'terms', 'wplib' ),
			'parent_item_colon'          => _x( 'Parent %s:',               'terms', 'wplib' ),
			'search_items'               => _x( 'Search %s',                'terms', 'wplib' ),
			'popular_items'              => _x( 'Popular %s',               'terms', 'wplib' ),
			'separate_items_with_commas' => _x( 'Separate %s with commas',  'terms', 'wplib' ),
			'add_or_remove_items'        => _x( 'Add or remove %s',         'terms', 'wplib' ),
			'choose_from_most_used'      => _x( 'Choose from most used %s', 'terms', 'wplib' ),
			'not_found'                  => _x( 'No %s found.',             'terms', 'wplib' ),
		);

		self::add_class_action( 'init', 11 );  // Run this after priority 10 of post type
		self::add_class_action( 'init', 99 );

	}

	/**
	 * Run on WordPress's 'init' hook to register all the term types defined in classes that extend this class.
	 */
	static function _init_11() {

		foreach( self::$_taxonomy_args as $taxonomy => $taxonomy_args ) {

			$object_types = ! empty( self::$_object_types[ $taxonomy ] ) ? self::$_object_types[ $taxonomy ] : array();

			/**
			 * This filter hook is fired once per taxonomy and just
			 * before WordPress' register_taxonomy() is called.
			 *
			 * @since 0.6.6
			 *
			 * @stability 1 - Experimental
			 */
			$taxonomy_args = apply_filters( 'wplib_taxonomy_args', $taxonomy_args, $taxonomy );

			/*
			 * For each of the term types that have been previously
			 * initialized, register them for WordPress.
			 */
			register_taxonomy( $taxonomy, $object_types, $taxonomy_args );

		}

		/**
		 * This action hook fires AFTER WPLib calls register_taxonomy()
		 * (hence 'post' vs. 'pre') for all the taxonomies registered in
		 * the on_load() for a subclass of WPLib_Term_Module_Base.
		 *
		 * This hook allows the calling self::attach_taxonomy() for a
		 * subclass of WPLib_Post_Module_Base.
		 *
		 * @note The first 'post' in the hook name means "after"
		 *       vs. 'pre' which would mean "before"
		 *       (i.e. WordPress' 'pre_get_posts' hook.)
		 *
		 * @since 0.6.6
		 *
		 * @stability 1 - Experimental
		 */
		do_action( 'wplib_post_register_taxonomies' );

	}

	/**
	 * Clear out this data, we don't need them anymore
	 * But do it late so that a regular 'init' can still access them, i.e. for changing labels of 'term' term type.
	 */
	static function _init_99() {

		self::_clear_taxonomy_args();

	}

	/**
	 * Save $args for later passing to register_taxonomy().
	 *
	 * @param string $taxonomy
	 * @param array $args
	 */
	static function _set_taxonomy_args( $taxonomy, $args ) {

		self::$_taxonomy_args[ $taxonomy ] = $args;

	}

	/**
	 * Get the object types (typically post types) registered for the taxonomy.
	 *
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	static function get_taxonomy_object_types( $taxonomy ) {

		return isset( self::$_object_types[ $taxonomy ] ) && is_array( self::$_object_types[ $taxonomy ] )
			? self::$_object_types[ $taxonomy ]
			: null;

	}

	/**
	 * Set the object types (typically post types) registered for the taxonomy.
	 *
	 * @param string $taxonomy
	 * @param array $object_types
	 *
	 */
	static function set_taxonomy_object_types( $taxonomy, $object_types ) {

		self::$_object_types[ $taxonomy ] = $object_types;

	}

	/**
	 * Add object types to this taxonomy
	 *
	 * @param string       $taxonomy
	 * @param array|string $object_slug
	 */
	static function add_object_type( $taxonomy, $object_slug ) {
		if( ! isset( self::$_object_types[ $taxonomy ] ) ) {
			self::$_object_types[ $taxonomy ] = array();
		}

		if( is_array( $object_slug ) ) {
			self::$_object_types[ $taxonomy ] = array_merge( self::$_object_types[ $taxonomy ], $object_slug );
		}

		if( ! is_array( $object_slug ) ) {
			self::$_object_types[ $taxonomy ][] = $object_slug;
		}
	}


	/**
	 * Run on WordPress's 'init' hook to register all the term types defined in classes that extend this class.
	 */
	static function _clear_taxonomy_args() {

		/*
		 * No need to hang on to this data anymore.
		 */
		self::$_labels = null;
		self::$_taxonomy_args = null;
		self::$_object_types = null;

	}


	/**
	 * @return array
	 */
	static function default_taxonomy_labels() {

		return self::$_default_labels;

	}

	/**
	 * @param string $taxonomy
	 * @param string $label_type
	 *
	 * @return string
	 */
	static function _get_taxonomy_label( $taxonomy, $label_type ) {

		return ! empty( self::$_labels[ $taxonomy ][ $label_type ] )
			? self::$_labels[ $taxonomy ][ $label_type ]
			: null;

	}

	/**
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	static function _get_taxonomy_labels( $taxonomy ) {

		return isset( self::$_labels[ $taxonomy ] ) && is_array( self::$_labels[ $taxonomy ] )
			? self::$_labels[ $taxonomy ]
			: array();

	}

	/**
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @return array
	 */
	static function _set_taxonomy_labels( $taxonomy, $args ) {

		self::$_labels[ $taxonomy ] = $args;

	}

	/**
	 * Checks an object to see if it has all the term-specific properties.
	 *
	 * If it does we can be almost sure it's a term.  Good enough for 99.9% of use-cases, anyway.
	 *
	 * @param object $term
	 *
	 * @return bool
	 */
	static function is_term( $term ) {

		return is_object( $term ) &&
	        property_exists( $term, 'term_id' ) &&
	        property_exists( $term, 'term_group' ) &&
			property_exists( $term, 'term_taxonomy_id' ) &&
			property_exists( $term, 'taxonomy' );

	}

	/**
	 * @param object|int|string $term
	 * @param array $args
	 * @return object|null
	 */
	static function get_term( $term, $args = array() ) {

		$args = wp_parse_args( $args, array(

			'lookup_type' => 'id',
			'taxonomy'    => false,

		));

		$taxonomy = $args['taxonomy'] ? $args['taxonomy'] : get_taxonomies();

		switch ( gettype( $term ) ) {

			case 'object':
				break;

			case 'integer':

				$term = get_term_by( $args[ 'lookup_type' ], $term, $taxonomy );
				break;

			case 'string':

				do {

					if( $slug_term = get_term_by( 'slug', $term, $taxonomy ) ) {

						$term = $slug_term;
						break;

					}

					if ( $name_term = get_term_by( 'name', $term, $taxonomy ) ) {

						$term = $name_term;
						break;

					}

				} while ( false );

				break;

		}

		return $term;

	}

	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args
	 * @return WPLib_Term_List_Default[]
	 */
	static function get_list( $query = array(), $args = array() ) {

		$args = wp_parse_args( $args, array(

			'default_list'  => 'WPLib_Term_List_Default',
			'items'         =>
				function( $query ) {
					return WPLib_Terms::get_terms( $query );
				},

		));

		return parent::get_list( $query, $args );

	}

	/**
	 * @param array $args
	 * @return object|null
	 */
	static function get_terms( $args = array() ) {

		$taxonomy = empty( $args['taxonomy'] )
			? get_taxonomies()
			: $args['taxonomy'];

		unset( $args['taxonomy'] );

		if ( !isset( $args['hide_empty'] ) ) {
			$args['hide_empty'] = false;
		}

		$terms = get_terms( $taxonomy, $args );

		return $terms ? $terms : array();

	}

	/**
	 * Create new Instance of a Term Item
	 *
	 * @param WP_Term $term
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function make_new_item( $term, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'instance_class' => false,
			'list_owner' => 'WPLib_Terms',
		));

		if ( ! $args[ 'instance_class' ] ) {

			$args['instance_class'] = WPLib::get_constant( 'INSTANCE_CLASS', $args['list_owner'] );

		}

		if ( ! $args[ 'instance_class' ] ) {

			$args['instance_class'] = self::get_taxonomy_class( $term->taxonomy );

		}

		$instance_class = $args['instance_class'];

		return $instance_class ? new $instance_class( $term ) : null;

	}

	/**
	 * @param string $taxonomy
	 *
	 * @return string|null
	 *
	 * @todo Rename to get_term_class() and deprecate this name
	 */
	static function get_taxonomy_class( $taxonomy ) {

		$classes = self::taxonomy_classes();

		return ! empty( $classes[ $taxonomy ] ) ? $classes[ $taxonomy ] : null;

	}

	/**
	 * @return string[]
	 *
	 * @todo Enhance this to support multiple classes per term type
	 * @todo Rename to term_classes() and deprecate this name
	 */
	static function taxonomy_classes() {

		return WPLib::_get_child_classes( 'WPLib_Term_Base', 'TAXONOMY' );

	}

}
WPLib_Terms::on_load();
