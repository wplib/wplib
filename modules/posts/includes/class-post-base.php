<?php

/**
 * Class WPLib_Post_Type_Base
 *
 * The Base Entity Class for Post Types
 *
 * @mixin WPLib_Post_Model_Base
 * @mixin WPLib_Post_View_Base
 *
 * @property WPLib_Post_Model_Base $model
 * @property WPLib_Post_View_Base $view
 *
 * @method void the_title_link()
 */
abstract class WPLib_Post_Base extends WPLib_Entity_Base {

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
