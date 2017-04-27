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
				'list_owner' => false,
			) );

			/**
			 * @var WPLib_Posts $list_owner
			 */
			$list_owner = $args[ 'list_owner' ];

			if ( $list_owner ) {

				if ( WPLib::can_call( 'make_new_item', $list_owner ) ) {

					foreach ( $posts as $index => $post ) {

						$posts[ $index ] = $list_owner::make_new_item( $post, $args );

					}

				} else {

					WPLib::trigger_error( sprintf( "Since class '%s' is being used as a list owner, it should have make_new_item() callable!", $list_owner ) );

				}

			} else {

				unset( $args[ 'list_owner' ] );

				foreach ( $posts as $index => $post ) {

					$instance_class = $args[ 'instance_class' ] = WPLib_Posts::get_post_type_class( $post->post_type );

					if ( ! $instance_class ) {
						WPLib::trigger_error( sprintf( "Could not resolve default instance class for '%s' post type!", $post->post_type ) );
						continue;
					}

					if ( ! WPLib::can_call( 'make_new', $instance_class ) ) {

						if ( ! WPLib::can_call( 'make_new_item', $instance_class ) ) {
							/**
							 * Method name make_new_item() is discouraged, supported here just for compatibility with existing projects.
							 */
							WPLib::trigger_error( sprintf( "Since class '%s' is being used as an item class, it should have make_new() callable!", $instance_class ) );
							continue;
						} else {
							$posts[ $index ] = $instance_class::make_new_item( $post, $args );
						}

					} else {
						$posts[ $index ] = $instance_class::make_new( $post, $args );
					}



				}

			}
		}

		parent::__construct( $posts, $args );

	}

	/**
	 *
	 */
	function post_types() {

		foreach ( $this->elements() as $element ) {

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
