<?php

/**
 * Class WPLib_Post
 *
 * The 'post' post type
 *
 * This class is NOT the class to subclass unless you explicitly want to modify the behavior of $post_type=='post'
 *
 * @mixin WPLib_Post_View
 * @mixin WPLib_Post_Model
 *
 * @property WPLib_Post_View $view
 * @property WPLib_Post_Model $model
 */
class WPLib_Post extends WPLib_Post_Base {

	/**
	 * The post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'post';

	/**
	 * Used by the_template() to assign an instance of this class to variable with this name.
	 *
	 * @var string
	 */
	const VAR_NAME = 'post_item';
}



