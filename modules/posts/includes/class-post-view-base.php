<?php

/**
 * Class WPLib_Post_View_Base
 *
 * The View Base Class for Posts.
 *
 * @mixin WPLib_Post_Model_Base
 * @method WPLib_Post_Model_Base model()
 *
 * @property WPLib_Post_Base $owner
 */
abstract class WPLib_Post_View_Base extends WPLib_View_Base {

	/**
	 * Provide easy access to the post object
	 *
	 * @return WP_Post
	 */
	function post() {

		return $this->model()->post();

	}

	/**
	 * Returns HTML for the title hyperlinked with the post's URL.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function get_title_link( $args = array() ) {

		$model = $this->model();

		$url = $model->url();

		$title = $model->title();

		return WPLib::get_html_link( $url, $title, $args );

	}

	/**
	 * Echos HTML for the title hyperlinked with the post's URL.
	 *
	 * @param array $args
	 */
	function the_title_link( $args = array() ) {

		echo wp_kses_post( $this->get_title_link( $args ) );

	}

}
