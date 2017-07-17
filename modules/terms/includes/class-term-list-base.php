<?php

class WPLib_Term_List_Base extends WPLib_List_Base {

	/**
	 * @param array $terms
	 * @param array $args
	 */
	function __construct( $terms, $args = array() ) {

		if ( isset( $terms ) && is_array( $terms ) ) {

			$args = wp_parse_args( $args, array(
				'list_owner' => 'WPLib_Terms',
			));

			/**
			 * @var WPLib_Terms $list_owner
			 */
			$list_owner = $args['list_owner'];

			foreach ( $terms as $index => $term ) {

				/**
				 * @todo This should probably be calling make_new_term().
				 */
				$terms[ $index ] = $list_owner::make_new_item( $term, $args );

			}

		}

		parent::__construct( $terms, $args );

	}

}
