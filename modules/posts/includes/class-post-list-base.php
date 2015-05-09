<?php

/**
 * Class WPLib_Post_List_Base
 */
class WPLib_Post_List_Base extends WPLib_List_Base {

	/**
	 * @var WP_Query|bool
	 */
	protected $_query = false;

	/**
	 * @var string
	 */
	private $_element_class;


	/**
	 * @param array $query
	 * @param array $args
	 *
	 * @future Improve this to use lazy loading of query and element instantiation
	 */
	function __construct( $query, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'element_class' => 'WPLib_Post_Default',
			'elements' => array(),
		));

		/*
		 * Grab element class so that its name can be used dynamically, and also used later.
		 */
		$element_class = $this->_element_class = $args[ 'element_class' ];

		unset( $args[ 'element_class' ] );


		if ( $query instanceof WP_Query ) {

			$this->_query = $query;

		} else if ( is_array( $query ) || is_string( $query ) ) {

			$query = wp_parse_args( $args['query'], array(
				'post_type'      => WPLib::constant( 'POST_TYPE', $element_class ),
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'posts_per_page' => 99,
				'no_found_rows'  => true,
			) );

			if ( ! empty( $query['post__not_in'] ) && ! is_array( $query['post__not_in'] ) ) {

				$query['post__not_in'] = array( $query['post__not_in'] );

			}
			$this->_query = new WPLib_Query( $query );

		}

		if ( isset( $this->_query->posts ) && is_array( $this->_query->posts ) ) {

			foreach ( $this->_query->posts as $post ) {

				// @todo Use make_new() here by default unless if does not exist.

				$args[ 'elements' ][ $post->ID ] = new $element_class( $post );

			}

		}

		$elements = $args[ 'elements' ];
		unset( $args[ 'elements' ] );

		parent::__construct( $elements, $args );

	}


	/**
	 * @return string
	 */
	function element_class() {

		return $this->_element_class;

	}

	/**
	 * Get index value for an object stored in a list.
	 *
	 * Can be reindex on a post property.
	 *
	 * @param WPLib_Post_Base $element
	 *
	 * @return bool|mixed
	 */
	function get_element_index( $element ) {

		/**
		 * Give the parent first change.
		 */
		if ( ! $index = parent::get_element_index( $element ) ) {

			if ( $this->_index_by && $element instanceof WPLib_Post_Base ) {

				if ( property_exists( $post = $element->post(), $index_by = $this->_index_by ) ) {

					$index = $post->$index_by;

				}

			}

		}

		return $index;

	}

}
