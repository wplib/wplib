<?php

/**
 * Class WPLib_Post_Model_Base
 *
 * The Model Base Class for Posts
 *
 * @property WPLib_Post_Base $owner
 *
 * @method int comment_count()
 * @method int menu_order()
 *
 * @method string date()
 * @method string date_gmt()
 * @method string modified()
 * @method string modified_gmt()
 *
 * @method string status()
 * @method string comment_status()
 * @method string ping_status()
 * @method string password()
 * @method string to_ping()
 * @method string pinged()
 *
 * @method void the_iso8601_date()
 * @method void the_datetime()
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

			$_post = $args['post'];

			$_post = $post instanceof WP_Post ? $post : get_post( $post->ID );

			unset( $args['post'] );

		} else if ( isset( $args['post'] ) && is_numeric( $args['post'] ) ) {

			$_post = get_post( $args['post'] );

			unset( $args['post'] );

		} else {

			$_post = null;

		}

		return new static( $_post, $args );

	}

	/**
	 * @return null|WPLib_Post_View_Base
	 */
	function view() {

		return is_object( $this->owner ) ? $this->owner->view() : null;

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
	 * @return int
	 */
	function ID() {

		return $this->has_post() ? intval( $this->_post->ID ) : 0;

	}

	/**
	 * @return null|string
	 */
	function title() {

		return $this->has_post() ? get_the_title( $this->_post->ID ) : null;

	}

	/**
	 * @return null|string
	 */
	function slug() {

		return $this->has_post() ? $this->_post->post_name : null;

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
	 * Returns the absolute URL for the represented post, or if not contained post object null.
	 *
	 * @see url()   permalink() is alias of url()
	 *
	 * @return int
	 */
	function permalink() {

		return $this->url();

	}

	/**
	 * Returns the absolute URL for the represented post, or if not contained post object null.
	 *
	 * @see permalink()   url() is preferred alias of permalink().
	 *
	 * @return string
	 */
	function url() {

		if ( ! $this->has_post() ) {

			$url = null;

		} else {

			switch ( $this->post_type() ) {

				case 'post':

					$url = get_permalink( $this->_post->ID );
					break;

				case 'page':

					$url = get_page_link( $this->_post->ID );
					break;

				default:

					$url = get_post_permalink( $this->_post->ID );

			}

		}

		return $url;

	}

	/**
	 * Return the post type as defined by the class.
	 *
	 * Validate against the current post if there is a current post.
	 *
	 * @return string|null
	 */
	function post_type() {

		if ( ! is_object( $this->owner ) ) {

			$post_type = null;

		} else {

			$post_type = $this->owner->get_constant( 'POST_TYPE' );

		}

		if ( $this->has_post() &&  $this->_post->post_type !== $post_type ) {

			$message = __( "Post type mismatch: %s::POST_TYPE=='%s' while \$this->_post->post_type=='%s'.", 'wplib' );
			WPLib::trigger_error( sprintf( $message, get_class( $this->owner ), $post_type, $this->_post->post_type ) );

		}

		return $post_type;

	}

	/**
	 * Determine if this is a $post_type == 'post'
	 *
	 * @return bool
	 */
	function is_blog_post() {

		return WPLib_Post::POST_TYPE === $this->post_type();

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

			$meta_name = call_user_func( array( $this->owner->app_class(), '_get_raw_meta_fieldname' ), $meta_name );

			$meta_value = get_post_meta( $this->_post->ID, $meta_name, true );

			if ( '' === $meta_value ) {

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

		/*
		 * This use of extract() is used here to counter the problems with
		 * WordPress' rampant use of global variables however, ironically,
		 * some code sniffers constantly flag extract() so it is easier to
		 * hide it than to have to constantly see it flagged.
		 *
		 * OTOH if you are using WPLib and you think we should do a direct call
		 * to extract() here please add an issue so we can discuss the pros and
		 * cons at https://github.com/wplib/wplib/issues
		 */

		$function = 'extract';
		$function( $postdata );

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

		return $this->has_post() && 'publish' === $this->status();

	}

	/**
	 * @return bool
	 */
	function is_attachment() {

		/**
		 * @todo Implement WPLib_Attachment and use WPLib_Attachment::POST_TYPE here.
		 */
	 	return 'attachment' === $this->post_type();

	}

	/**
	 * @return WP_Post
	 */
	function parent_post() {

		return $this->has_post() ? get_post( $this->parent_id() ) : null;

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_adjacent_post( $args = array() ) {

		if ( ! $this->has_post() ) {

			$adjacent_post = null;

		} else {

			$args = wp_parse_args( $args, array(
				'in_same_term'   => false,
				'excluded_terms' => '',
				'taxonomy'       => 'category',
				'previous'       => null,
			) );

			WPLib::push_post( $this->_post );

			$adjacent_post = get_adjacent_post(
				$args['in_same_term'],
				$args['excluded_terms'],
				$args['previous'],
				$args['taxonomy']
			);

			WPLib::pop_post();

		}

		return $adjacent_post;

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_previous_post( $args = array() ) {

		$args = wp_parse_args( $args );
		$args[ 'previous' ] = true;
		return $this->get_adjacent_post( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_next_post( $args = array() ) {

		$args = wp_parse_args( $args );
		$args[ 'previous' ] = false;
		return $this->get_adjacent_post( $args );

	}

	/**
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return array|mixed|null
	 */
	function __call( $method_name, $args ) {

		if ( ! $this->has_post() ) {

			$value = parent::__call( $method_name, $args );

		} else {
			/**
			 * Capture methods that identify WP_Post field names.
			 *
			 * Strip 'post_' as a prefix and recognize methods as accessing WP_Post properties.
			 *
			 * 'name' is too generic so 'post_name' is accessed via slug():
			 *
			 *       $this->slug()              => return $this->_post->post_name
			 *
			 * 'post_type' is too iconic so post_type() is used vs. type():
			 *
			 * 'post_parent' is accessed by parent_id():
			 *
			 *       $this->parent_id()         => return $this->_post->post_parent
			 *
			 * Others are accessed as method name sans 'post_' prefix, i.e.
			 *
			 *       $this->ID()                => return $this->_post->ID
			 *       $this->menu_order()        => return $this->_post->menu_order
			 *       $this->date()              => return $this->_post->post_date
			 *       $this->date_gmt()          => return $this->_post->post_date_gmt
			 *       $this->modified()          => return $this->_post->post_modified
			 *       $this->modified_gmt()      => return $this->_post->post_modified_gmt
			 *       $this->password()          => return $this->_post->password
			 *       $this->comment_count()     => return $this->_post->comment_count
			 *       $this->pinged()            => return $this->_post->pinged
			 *       $this->to_ping()           => return $this->_post->to_ping
			 *       $this->ping_status()       => return $this->_post->ping_status
			 *       $this->comment_status()    => return $this->_post->comment_status
			 *
			 * Lastly 'post_type', 'post_content' and 'post_excerpt' are accessed via more functions, so:
			 *
			 *       $this->post_type()         !=> return $this->_post->post_type
			 *       $this->content()           !=> return $this->_post->post_content
			 *       $this->excerpt()           !=> return $this->_post->post_excerpt
			 *
			 */

			switch ( preg_replace( '#^post_(.+)$#', '$1', $method_name ) ) {

				case 'ID':
				case 'menu_order';
				case 'comment_count':

					$data_type     = 'int';
					$property_name = $method_name;
					break;

				case 'date':
				case 'date_gmt':
				case 'modified':
				case 'modified_gmt':

					$data_type     = 'date';
					$property_name = "post_{$method_name}";
					break;

				case 'status':
				case 'password':

					$data_type     = 'string';
					$property_name = "post_{$method_name}";
					break;

				case 'comment_status':
				case 'ping_status':
				case 'to_ping':
				case 'pinged':
					$data_type     = 'string';
					$property_name = $method_name;
					break;

				default:
					$property_name = false;

			}

			if ( ! $property_name ) {

				$value = parent::__call( $method_name, $args );

			} else {

				$value = $this->_post->$property_name;

				switch ( $data_type ) {
					case 'int':

						$value = intval( $value );
						break;

					case 'date':
						/**
						 * @todo Verify that this is what we want to standardize on.
						 */
						$value = mysql2date( DATE_W3C, $value );

					default:
						/*
						 * No need to do anything for a string.
						 */

				}

			}

		}

		return $value;

	}

	/**
	 *  Determine if post
	 */
	function has_adjacent_posts() {

		$previous = $this->is_attachment() ? $this->parent_post() : $this->get_previous_post();

		return $previous || $this->get_next_post();

	}

	/**
	 * Determine if post has been modified since first published.
	 *
	 * @return bool True if modified since first published.
	 */
	function is_modified() {

		return $this->unix_timestamp() !== $this->modified_unix_timestamp();

	}

	/**
	 *
	 */
	function is_single() {

		return is_single( $this->_post->ID );

	}

	/**
	 * @return int
	 */
	function unix_timestamp() {

		return $this->has_post()
			? get_post_time( 'U', false, $this->_post, false )
			: 0;

	}

	/**
	 * @return int
	 */
	function unix_timestamp_gmt() {

		return $this->has_post()
			? get_post_time( 'U', true, $this->_post, false )
			: 0;

	}

	/**
	 * @return int
	 */
	function modified_unix_timestamp() {

		return $this->has_post()
			? get_post_modified_time( 'U', false, $this->_post, false )
			: 0;

	}

	/**
	 * @return int
	 */
	function modified_unix_timestamp_gmt() {

		return $this->has_post()
			? get_post_modified_time( 'U', true, $this->_post, false )
			: 0;

	}

	/**
	 * @return int
	 */
	function iso8601_date() {

		return $this->has_post()
			? get_post_time( 'c', false, $this->_post, false )
			: false;

	}

	/**
	 * @return int
	 */
	function iso8601_date_gmt() {

		return $this->has_post()
			? get_post_time( 'c', true, $this->_post, false )
			: false;

	}

	/**
	 * @return int
	 */
	function iso8601_modified_date() {

		return $this->has_post()
			? get_post_modified_time( 'c', false, $this->_post, false )
			: false;

	}

	/**
	 * @return int
	 */
	function iso8601_modified_date_gmt() {

		return $this->has_post()
			? get_post_modified_time( 'c', true, $this->_post, false )
			: false;

	}

	/**
	 * @return int
	 */
	function datetime() {

		return $this->has_post()
			? get_post_time( get_option( 'date_format' ), false, $this->_post, true )
			: false;

	}

	/**
	 * @return int
	 */
	function modified_datetime() {

		return $this->has_post()
			? get_post_modified_time( get_option( 'date_format' ), true, $this->_post, true )
			: false;

	}

	function posted_on_values() {

		return $this->has_post() ? (object) array(

			'iso8601_date'          => $this->iso8601_date(),
			'iso8601_modified_date' => $this->iso8601_modified_date(),
			'datetime'              => $this->iso8601_date(),
			'modified_datetime'     => $this->iso8601_modified_date(),

		) : false;

	}

	/**
	 * Return post's author ID
	 *
	 * @note Does not use get_the_author_meta( 'ID' ) and thus does not fire 'get_the_author_ID' hook
	 * @todo Discuss if it should?  Or is this way more not robust?
	 *
	 * @return int|bool
	 */
	function author_id() {

		return $this->has_post() ? intval( $this->_post->post_author ) : false;

	}

	/**
	 * @return null|WPLib_User_Base
	 */
	function author() {

		if ( ! ( $author_id = $this->author_id() ) ) {

		 	$author = null;

		} else {

			$author = WPLib::get_user_by( 'id', $author_id );

		}

		return $author;

	}

	/**
	 * Whether the post has been password protected.
	 *
	 * @note This is NOT the same as post_password_required() because it checks current state of cookie.
	 *       Use with
	 *
	 * @return bool|null
	 */
	function password_required() {

		$password = $this->has_post() ? $this->password() : false;

		return ! empty( $password );

	}

	/**
	 * @return int
	 */
	function comments_number() {

		return get_comments_number( $this->_post->ID );

	}

	/**
	 * @return mixed|null
	 */
	function format_slug() {

		return $this->has_post() ? get_post_format( $this->_post->ID ) : null;

	}

	/**
	 * Does this post have comments?
	 *
	 * @note Does not use have_comments() because that is specific to the wp_query and not to the post itself.
	 *
	 * @return bool
	 *
	 *
	 */
	function has_comments() {

		return $this->has_post() ? $this->comment_count() > 0 : false;

	}

	/**
	 *
	 */
	function number_of_comments() {

		return $this->has_post() ? get_comments_number( $this->_post->ID ) : 0;

	}

	/**
	 * @return bool
	 */
	function comments_open() {

		return $this->has_post() ? comments_open( $this->_post->ID ) : false;

	}

	/**
	 * @return bool
	 */
	function supports_comments() {

		return post_type_supports( $this->post_type(), 'comments' );

	}

	/**
	 * @return bool
	 */
	function comments_unavailable() {

		return ! $this->comments_open() && 0 < $this->number_of_comments() && $this->supports_comments();

	}

	/**
	 * @return int
	 */
	function number_of_comment_pages() {

		$theme = WPLib::theme();

		if ( ! $theme->uses_paged_comments() ) {

			$number = $this->has_comments() ? 1 : 0;

		} else {

			$number = $this->has_post() ? $theme->number_of_comment_pages() : 0;

		}
		return $number;

	}

	/**
	 * Can user comments?
	 *
	 * Yes if no password or password provided and comments are open.
	 *
	 * @todo We probably need to change this method. If confused info about $post with state of password entry
	 *
	 * @return bool
	 */
	function user_can_comment() {

		$post = $this->post();

		return $post && ! post_password_required( $post ) && $item->comments_open();

	}

	/**
	 * Can user see comments?
	 *
	 * Yes if no password or password provided and comments are either open or at least one comment exists.
	 *
	 * @todo We probably need to change this method. If confuses info about $post with state of password entry
	 *
	 * @return bool
	 */
	function user_can_see_comments() {

		$post = $this->post();

		return $post &&
					 ! post_password_required( $post ) &&
					 ( $this->comments_open() || $this->comments_number() );

	}

	/**
	 * Does this post represent the site's front page?
	 *
	 * @return bool True, if front of site.
	 */
	function is_front_page() {

		$page_id = $this->ID();

		return 0 !== $page_id && intval( get_option( 'show_on_front' ) ) ===  $page_id;

	}

	/**
	 * @param string $size
	 * @param array $args {
	 *      @type string $src
	 *      @type string $class
	 *      @type string $alt
	 *      @type string $height
	 *      @type string $width
	 *      @type string $title
	 * }
	 * @return string
	 */
	function get_featured_image_html( $size = 'post-thumbnail', $args = array() ) {

		return $this->has_post()
			? get_the_post_thumbnail( $this->ID(), $size, $args )
			: null;

	}

	/**
	 * @return bool
	 */
	function has_featured_image() {

		return $this->has_post() && (bool) get_post_thumbnail_id( $this->ID() );

	}

	/**
	 * @return bool
	 */
	function has_thumbnail_image() {

		return $this->has_featured_image();

	}

}
