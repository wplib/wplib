<?php

/**
 * Class WPLib_Term_Type_Base
 *
 * The Item Base Class for Term Types
 *
 * @mixin WPLib_Term_Model_Base
 * @mixin WPLib_Term_View_Base
 *
 * @property WPLib_Term_Model_Base $model
 * @property WPLib_Term_View_Base $view
 */
abstract class WPLib_Term_Base extends WPLib_Item_Base {

	/**
	 * Child class should define a valid value for TAXONOMY
	 */
	const TAXONOMY = null;

	/**
	 * @param WP_Term|int|null $term
	 * @param array $args
	 */
	function __construct( $term, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'model' => array( 'term' => $term ),
		));

		parent::__construct( $args );

	}

}
