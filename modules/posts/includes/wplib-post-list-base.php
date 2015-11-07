<?php

/**
 * Class WPLib_Post_List_Base
 *
 * @method WPLib_Post_Base[] elements()
 */
class WPLib_Post_List_Base extends WPLib_List_Base {

	private $_post_types;


	/**
	 * @param array $posts
	 * @param array $args
	 */
	function __construct( $posts, $args = array() ) {

		if ( isset( $posts ) && is_array( $posts ) ) {

			$args = wp_parse_args( $args, array(
				'list_owner' => 'WPLib_Posts',
			));

			/**
			 * @var WPLib_Posts $list_owner
			 */
			$list_owner = $args['list_owner'];

			foreach ( $posts as $index => $post ) {

				$posts[ $index ] = $list_owner::make_new_item( $post, $args );

			}

		}

		parent::__construct( $posts, $args );

	}

	/**
	 *
	 */
	function post_types() {

		foreach( $this->elements() as $element ) {

			$this->_post_types[ $element->post_type() ] = true;

		}

		return $this->_post_types = array_keys( $this->_post_types );

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
