<?php

/**
 * Class WPLib_Terms
 */
class WPLib_Terms extends WPLib_Module_Base {

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

		self::add_class_action( 'init' );
		self::add_class_action( 'init', 99 );

	}

	/**
	 * Run on WordPress's 'init' hook to register all the term types defined in classes that extend this class.
	 */
	static function _init() {

		foreach( self::$_taxonomy_args as $taxonomy => $taxonomy_args ) {
			/*
			 * For each of the term types that have been previously
			 * initialized, register them for WordPress.
			 */
			register_taxonomy( $taxonomy, self::$_object_types[ $taxonomy ], $taxonomy_args );
		}

	}

	/**
	 * Clear out this data, we don't need them anymore
	 * But do it late so that a regular 'init' can still access them, i.e. for changing labels of 'term' term type.
	 */
	static function _init_99() {

		self::_clear_taxonomy_args();

	}

	/**
	 * The $args saved early to later be passed to register_term_type().
	 * @return array
	 */
	static function taxonomy_args() {

		return self::$_taxonomy_args;

	}

	/**
	 * Save $args for later passing to register_term_type().
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
	static function _set_taxonomy_object_types( $taxonomy, $object_types ) {

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
	}


	/**
	 * @return array
	 */
	static function default_taxonomy_labels() {

		return self::$_default_labels;

	}

	/**
	 * @param string $label_type
	 *
	 * @return string
	 */
	static function get_taxonomy_label( $taxonomy, $label_type ) {

		return ! empty( self::$_labels[ $taxonomy ][ $label_type ] )
			? self::$_labels[ $taxonomy ][ $label_type ]
			: null;

	}

	/**
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	static function get_taxonomy_labels( $taxonomy ) {

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
	static function set_taxonomy_labels( $taxonomy, $args ) {

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

}
WPLib_Terms::on_load();
