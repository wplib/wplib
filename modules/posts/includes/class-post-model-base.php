<?php

/**
 * Class WPLib_Post_Model_Base
 *
 * The Model Base Class for Posts
 *
 * @property WPLib_Post_Base $owner
 */
abstract class WPLib_Post_Model_Base extends WPLib_Model_Base {

	/**
	 * @var WP_Post|object|null
	 */
	protected $_post;

	/**
	 * Child class should define a valid value for POST_TYPE
	 */
	const POST_TYPE = null;

	/**
	 * @param WP_Post|object|null $post
	 * @param array               $args
	 */
	function __construct( $post, $args = array() ) {

		/*
		 * Find the post if possible
		 */
		$this->_post = WPLib::get_post( $post );

		/*
		 * Let our parent class capture whatever properties where passed in as $args
		 */
		parent::__construct( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return WPLib_Post_Model_Base
	 */
	static function make_new( $args ) {

		if ( isset( $args['post']->ID ) && is_numeric( $args['post']->ID ) ) {

			$post = $args['post'];

			$post = $post instanceof WP_Post ? $post : get_post( $post->ID );

			unset( $args['post'] );

		} else if ( isset( $args['post'] ) && is_numeric( $args['post'] ) ) {

			$post = get_post( $args['post'] );

			unset( $args['post'] );

		} else {

			$post = null;

		}

		return new static( $post, $args );

	}

	/**
	 * @return WP_Post|null
	 */
	function post() {

		return $this->_post;

	}


	/**
	 * @param WP_Post
	 */
	function set_post( $post ) {

		if ( $post instanceof WP_Post ) {

			$this->_post = $post;

		}

	}

	/**
	 * @return bool
	 */
	function has_post() {

		return isset( $this->_post ) && is_a( $this->_post, 'WP_Post' );

	}

	/**
	 * @return bool
	 */
	function has_parent() {

		return $this->parent_id() > 0;

	}

	/**
	 * @return int
	 */
	function parent_id() {

		return absint( $this->get_field_value( 'post_parent' ) );

	}

	/**
	 * @return string|null
	 */
	function post_type() {

		return is_object( $this->owner ) ? $this->owner->constant( 'POST_TYPE' ) : false;

	}

	/**
	 * Retrieve the value of a field and to provide a default value if no _post is set.
	 *
	 * @param string $field_name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	function get_field_value( $field_name, $default = false ) {

		$value = $default;

		if ( $this->has_post() && isset( $this->_post->$field_name ) ) {

			$value = $this->_post->$field_name;

		}

		return $value;

	}

	/**
	 * Retrieve the value of post meta and provide a default value if no meta is set.
	 * Adds both a leading underscore and a short prefix to the meta name.
	 *
	 * @param string $meta_name
	 * @param mixed  $default
	 *
	 * @return mixed
	 * @future Consider deprecating and just use get_field_value() instead.
	 */
	function get_meta_value( $meta_name, $default = false ) {

		$meta_value = $default;

		if ( $this->has_post() ) {

			$prefix = WPLib::SHORT_PREFIX;

			$meta_name = "{$prefix}{$meta_name}";

			$meta_value = get_post_meta( $this->_post->ID, $meta_name, true );

			if ( '' == $meta_value ) {

				$meta_value = $default;

			}

		}

		return $meta_value;

	}

	/**
	 * @return mixed|void
	 */
	function excerpt() {

		if ( $this->has_post() ) {

			$saved_postdata = $this->setup_postdata();

			$excerpt = apply_filters( 'the_excerpt',

				apply_filters( 'get_the_excerpt', $this->get_field_value( 'post_excerpt' ) )

			);

			$this->restore_postdata( $saved_postdata );

		}

		return $excerpt;

	}

	/**
	 * @return mixed|void
	 */
	function content() {

		if ( $this->has_post() ) {

			$saved_postdata = $this->setup_postdata();

			$content = apply_filters( 'the_content',

				apply_filters( 'get_the_content', $this->get_field_value( 'post_content' ) )

			);

			$this->restore_postdata( $saved_postdata );

		}
		return $content;

	}

	/**
	 * Saves the global values set by setup_postdata(), calls setup_postdata() and returns the saved values.
	 *
	 * The global variables include:
	 *
	 *      $id
	 *      $authordata
	 *      $currentday
	 *      $currentmonth
	 *      $page
	 *      $pages
	 *      $multipage
	 *      $more
	 *      $numpages
	 *
	 * @return array An associative array containing each of the global variables lists above which the var name as the array index.
	 */
	function setup_postdata() {

		global $id, $authordata, $currentday, $currentmonth, $page, $pages, $multipage, $more, $numpages;

		$postdata = compact( 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' );

		setup_postdata( $this->_post );

		return $postdata;

	}

	/**
	 * Restores postdata to a prior state, as returned by setup_postdata().
	 *
	 * This does NOT call wp_reset_postdata() because doing so results in bugs.
	 *
	 * This is an alternative to the fustercluck that is wp_reset_postdata().
	 *
	 * @param array $postdata
	 */
	function restore_postdata( $postdata ) {

		global $id, $authordata, $currentday, $currentmonth, $page, $pages, $multipage, $more, $numpages;

		extract( $postdata );

	}


	/**
	 * Check if a post is published
	 *
	 * @return bool
	 *
	 * @todo https://github.com/wplib/wplib/issues/5
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026544
	 */
	function is_published() {

		return $this->has_post() && 'publish' == $this->_post->post_status;

	}

	/**
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return array|mixed|null
	 */
	function __call( $method_name, $args ) {

		if ( $this->has_post() && (
			property_exists( $this->_post, $method_name ) ||
			property_exists( $this->_post, $method_name = "post_{$method_name}" ) ) ) {

			$value = $this->_post->$method_name;

		} else {

		 	$value = parent::__call( $method_name, $args );

		}

		return $value;

	}

}
