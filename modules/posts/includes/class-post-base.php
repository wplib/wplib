<?php

/**
 * Class WPLib_Post_Type_Base
 *
 * The Base Item Class for Post Types
 *
 * @mixin WPLib_Post_Model_Base
 * @mixin WPLib_Post_View_Base
 *
 * @property WPLib_Post_Model_Base $model
 * @property WPLib_Post_View_Base $view
 */
abstract class WPLib_Post_Base extends WPLib_Item_Base {

	/**
	 * Child class should define a valid value for POST_TYPE
	 */
	const POST_TYPE = null;

	/**
	 * @param WP_Post|int|null $post
	 * @param array $args
	 */
	function __construct( $post, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'model' => array( 'post' => $post ),
		));

		parent::__construct( $args );

	}

}
