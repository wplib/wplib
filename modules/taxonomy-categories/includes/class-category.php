<?php

/**
 * Class WPLib_Category
 *
 * @mixin WPLib_Category_View
 * @mixin WPLib_Category_Model
 *
 * @property WPLib_Category_View $view
 * @property WPLib_Category_Model $model
 */
class WPLib_Category extends WPLib_Term_Base {

  const TAXONOMY = WPLib_Categories::TAXONOMY;

}

