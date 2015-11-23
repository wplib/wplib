<?php

/**
 * Class WPLib_Page
 *
 * The 'post' post type
 *
 * This class is NOT the class to subclass unless you explicitly want to modify the behavior of $post_type=='page'
 *
 * @mixin WPLib_Page_View
 * @mixin WPLib_Page_Model
 *
 * @property WPLib_Page_View $view
 * @property WPLib_Page_Model $model
 */
class WPLib_Page extends WPLib_Post_Base {

	/**
	 * The post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = WPLib_Post_Type_Page::POST_TYPE;


}




