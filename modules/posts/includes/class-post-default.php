<?php

/**
 * Class WPLib_Post_Default
 *
 * The Default Entity Class for Post Types
 *
 * @mixin WPLib_Post_Model_Default
 * @mixin WPLib_Post_View_Default
 *
 * @property WPLib_Post_Model_Default $model
 * @property WPLib_Post_View_Default $view
 */
abstract class WPLib_Post_Default extends WPLib_Post_Base {

	/**
	 * Child class should define a valid value for POST_TYPE
	 */
	const POST_TYPE = 'any';

}
