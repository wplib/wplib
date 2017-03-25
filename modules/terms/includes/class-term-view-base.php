<?php

/**
 * Class WPLib_Term_View_Base
 *
 * The View Base Class for Terms.
 *
 * @mixin WPLib_Term_Model_Base
 * @method WPLib_Term_Model_Base model()
 *
 * @property WPLib_Term_Base $owner
 *
 * @method void the_term_id()
 * @method void the_term_name()
 * @method void the_term_slug()
 * @method void the_id_attr()
 * @method void the_name_attr()
 * @method void the_name_html()
 * @method void the_slug_attr()
 * @method void the_term_name_attr()
 * @method void the_term_slug_attr()
 * @method void the_term_description()
 */
abstract class WPLib_Term_View_Base extends WPLib_View_Base {

	/**
	 * Provide easy access to the term object
	 *
	 * @return WP_Term|object
	 */
	function term() {

		return $this->model()->term();

	}

}
