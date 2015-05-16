<?php

/**
 * Class WPLib_Theme_Base
 *
 * @todo Break out some of these more prescriptive methods into a helper module so they can be ommitted if desired.
 *
 * @method void the_site_name()
 *
 */
abstract class WPLib_Theme_Base extends WPLib {

	/**
	 * Used by the_template() to assign an instance of this class to variable with this name.
	 */
	const VAR_NAME = 'theme';


	/**
	 * Return the site name as configured.
	 *
	 * @return string|void
	 */
	function the_site_url() {

		return esc_url( $this->site_url() );

	}

	/**
	 * Return the site name as configured.
	 *
	 * @return string|void
	 */
	function site_url() {

		return home_url( '/' );

	}

	/**
	 * Return the site name as configured.
	 *
	 * @return string|void
	 */
	function site_name() {

		return get_bloginfo( 'name' );

	}

	/**
	 * Return the site description as configured.
	 *
	 * @return string|void
	 */
	function the_site_description_html() {

		return wp_kses_post( $this->site_description() );

	}
	/**
	 * Return the site description as configured.
	 *
	 * @return string|void
	 */
	function site_description() {

		return get_bloginfo( 'description' );

	}

	/**
	 * Return the site name as configured.
	 *
	 * @param array $args
	 */
	function the_site_name_link( $args = array() ) {

		echo wp_kses_post( $this->site_name_link() );

	}

	/**
	 * Return the site name as configured.
	 *
	 * @param array $args
	 * @return string
	 */
	function site_name_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'rel' => 'home'
		));

		return $this->get_link( $this->site_url(), $this->site_name(), $args );

	}

	/**
	 * Output the attributes for the HTML <html> element
	 */
	function the_html_attributes() {

		language_attributes();

	}

	/**
	 * Output the HTML element <meta charset="...">.
	 *
	 */
	function the_meta_charset_html() {

		echo '<meta charset="' . get_bloginfo( 'charset' ) . "\">\n";

	}

	/**
	 * Output the HTML element <link rel="profile" ...>.
	 * @param array $args
	 */
	function the_link_profile_html( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'href' => false,
			'url'  => false,
		));

		if ( ! $args[ 'href' ] ) {

			$args['href'] = $args[ 'url' ] ? $args['url'] : 'http://gmpg.org/xfn/11';

		}

		echo '<link rel="profile" href="' . $args[ 'href' ] . "\">\n";

	}

	/**
	 * Output the HTML element <link rel="pingback" ...>.
	 */
	function the_link_pingback_html() {

		echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . "\">\n";

	}

	/**
	 * Output the content for the wp_head() method;
	 */
	function the_wp_head_html() {

		wp_head();

	}


	/**
	 * Output the content for the wp_footer() method;
	 */
	function the_wp_footer_html() {

		wp_footer();
	}


	/**
	 * Output the attributes for the HTML <body> element
	 *
	 */
	function the_body_attributes() {

		body_class();

	}

	/**
	 * Output the HTML element <title>...</title>.
	 *
	 */
	function the_page_title_html() {

		echo'<title>';
		wp_title( '' );
		echo'</title>';

	}

	function the_header_html( $name = null ) {

		/**
		 * This is usually the first method to call in a template so...
		 * if ABSPATH is not defined it was called directly. This
		 * should only happen when someone is hacking.
		 */
		if ( ! defined( 'ABSPATH' ) ) {

			header("HTTP/1.0 404 Not Found");
			echo '<h1>Not Found</h1>';
			echo '<p>Requested URL not found.</p>'.
			exit;

		}

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		/*
		 * Make $theme visible inside header.php
		 */
		$wp_query->set( 'theme', WPLib::theme() );

		get_header( $name );

	}

	/**
	 * Generate the HTML for a nav menu.
	 *
	 * @param string $location  Defaults to 'primary' theme location.
	 * @param array $args
	 */
	function the_menu_html( $location = 'primary', $args = array() ) {

		$args = wp_parse_args( $args, array(
			'theme_location' => $location,
			'menu_id'        => "{$location}-menu",
			'container'      => false,
			'menu_class'     => false,
			'items_wrap'     => false,
		) );

		wp_nav_menu( $args );

	}

	/**
	 * Generate the HTML for a screen reader skip link.
	 *
	 * @param array $args
	 */
	function the_screen_reader_skip_link( $args = array() ) {

		echo $this->get_screen_reader_skip_link( $args );

	}

	/**
	 * Generate the HTML for a screen reader skip link.
	 *
	 * @param array $args
	 * @return string
	 */
	function get_screen_reader_skip_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'href'       => false,
			'url'        => false,
			'class'      => 'skip-link screen-reader-text',
			'link_text'  => __( 'Skip to content', 'underscores4wplib' ),
			'is_html'    => false,
		) );

		if ( ! $args[ 'href' ] ) {

			$args['href'] = $args[ 'url' ] ? $args['url'] : '#content';
			unset( $args[ 'url' ] );

		}
		$href = $args['href'];
		$link_text = $args['link_text'];
		unset( $args['href'], $args['link_text'] );

		return $this->get_link( $href, $link_text, $args );

	}

	function the_footer_html( $name = null ) {

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		/*
		 * Make $theme visible inside footer.php
		 */
		$wp_query->set( 'theme', WPLib::theme() );

		get_footer( $name );

	}

	function the_sidebar_html( $name = null ) {

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		/*
		 * Make $theme visible inside sidebar.php
		 */
		$wp_query->set( 'theme', WPLib::theme() );

		get_sidebar( $name );

	}

	/**
	 * Output the HTML element <meta name="viewport" content="...">.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function the_meta_viewport_html( $args = array() ) {

		echo $this->get_meta_viewport_html( $args );

	}

	/**
	 * Get the HTML element <meta name="viewport" content="...">.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function get_meta_viewport_html( $args = array() ) {

		$args = wp_parse_args( $args );

		if ( 0 == count( $args ) ) {

			$meta_viewport = false;

		} else {

			$attributes = esc_attr( implode( ',', array_map(

				function ($value, $key) {

					return "{$key}={$value}";
				},
				$args,
				array_keys( $args )

			)));

			$meta_viewport = "<meta name=\"viewport\" content=\"{$attributes}\">\n";

		}
		return $meta_viewport;

	}

	/**
	 * Enqueue JS or CSS.
	 *
	 * Auto generate version.
	 *
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param bool $in_footer
	 *
	 * @todo https://github.com/wplib/wplib/issues/2
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026274
	 */
	function enqueue_external( $handle, $src, $deps = array(), $in_footer = false ) {

		preg_match( '#\.(js|css)$#i', $src, $file_type );


		if ( '~' == $src[0] &&  '/' == $src[1] ) {

			/**
			 * Assume $src that start with ~/ are relative.
			 */
			$src = preg_replace( '#^(~/)#', '',  $src );

		}

		if ( ! ( $absolute = preg_match( '#^(/|https?://)#', $src ) ) ) {

			/**
			 * If relative, add stylesheet URL and DIR to the $src and $filepath.
			 */
			$src = static::get_root_url( $src );

			$filepath = static::get_root_dir( $src );

		}

		if ( ! static::is_script_debug() && ! $absolute ) {
			/**
			 * If script debug and not absolute URL
			 * then prefix extensions 'js' and 'css' with 'min.'
			 */

			$src = preg_replace( '#\.(js|css)$#i', '.min.$1', $src );

			$ver = rand( 1, 1000000 );

		} else {

			if ( empty( $filepath ) ) {

				$ver = false;

			} else {

				if ( $ver = self::cache_get( $cache_key = "external[{$filepath}]" ) ) {
					$ver = md5_file( $filepath );
					self::cache_get( $cache_key, $ver );
				}

			}

		}

		switch ( strtolower( $file_type[1] ) ) {

			case 'js':
				wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
				break;

			case 'css':
				wp_enqueue_style( $handle, $src, $deps, $ver, $in_footer );
				break;
		}

	}

	/**
	 *
	 */
	function the_comments_popup_link() {

		echo $this->get_comments_popup_link();
	}

	/**
	 * @return string
	 */
	function get_comments_popup_link() {

		$args = wp_parse_args( $args, array(

			'zero' => __( 'Leave a comment', 'wplib' ),
			'one'  => __( '1 Comment', 'wplib' ),
			'more' => __( '% Comments', 'wplib' ),

		));

		ob_start();

		comments_popup_link(
			esc_html( $args[ 'zero' ] ),
			esc_html( $args[ 'one' ] ),
			esc_html( $args[ 'more' ] )
		);

		return ob_get_clean();

	}

	/**
	 * Return true if the site uses any categories on posts.
	 *
	 * @param array $args
	 *
	 * @note Ignores 'Uncategorized'
	 *
	 * @return int|mixed
	 */
	function has_categories( $args = array() ) {

		return 0 < $this->category_count( $args );

	}

	/**
	 * Return number of is use categories on posts.
	 *
	 * @note Ignores 'Uncategorized'
	 *
	 * @param array $args
	 *
	 * @return int|mixed
	 */
	function category_count( $args = array() ) {

		$category_count = WPLib::cache_get( $cache_key = 'category_count' );

		if ( false === $category_count ) {

			$args = wp_parse_args( $args, array(
				'fields'     => 'ids',
				/*
				 * Getonly categories are attached to posts.
				 */
				'hide_empty' => 1
			));

			$categories = get_categories( $args );

			set_transient( $cache_key, $category_count = count( $categories ) );
		}

		return intval( $category_count ) - 1;

	}

	/**
	 * @return WP_Query
	 */
	function query() {
		global $wp_the_query;

		return $wp_the_query;
	}

	/**
	 * @return WP_Post[]
	 */
	function posts() {

		return $this->has_posts() ? $this->query()->posts : array();

	}

	/**
	 * @return WP_Post
	 */
	function post() {

		return $this->has_posts() ? $this->query()->post : null;

	}

	/**
	 * @return WPLib_Post_Base
	 *
	 * @todo Make work for non-posts?
	 */
	function entity() {

		return $this->has_posts() ? WPLib_Posts::make_new_entity( $this->post() ) : new WPLib_Post_Default( null );

	}

	/**
	 * @return WPLib_Page
	 */
	function page_entity() {

		return new WPLib_Page( $this->post() );

	}

	/**
	 * @return WPLib_Post
	 */
	function post_entity() {

		return new WPLib_Post( $this->post() );

	}

	/**
	 * @return bool
	 */
	function has_posts() {

		return 0 < $this->post_count();
	}

	/**
	 * @return bool
	 */
	function post_count() {

		$q = $this->query();

		return isset( $q->posts ) && is_array( $q->posts ) ? count( $q->posts ) : 0;

	}

	/**
	 * Returns a list of objects based on the queried object from $wp_the_query
	 *
	 * @param array $args
	 * @return WPLib_Post_List_Default|WPLib_Post_Base[]
	 */
	function get_post_list( $args = array() ) {

		return new WPLib_Post_List_Default( $this->posts() );

	}

	/**
	 * @param array $args
	 * @return string
	 */
	function search_query( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'escaped' => true

		));

		return get_search_query( $args[ 'escaped' ] );

	}

	/**
	 * @return bool
	 */
	function is_home() {

		global $wp_the_query;

		return (bool) $wp_the_query->is_home;

	}

	/**
	 * @return bool
	 */
	function doing_search() {

		global $wp_the_query;

		return (bool) $wp_the_query->is_search;

	}

//	/**
//	 * @return string
//	 */
//	function query_type() {
//
//		if ( ! ( $queried_object = $wp_the_query->get_queried_object() ) ) {
//
//			if ( 'posts' == self::front_page_query_type() && self::is_home() ) {
//
//				$query_type = 'posts';
//
//			} else if ( 'page' == self::front_page_query_type() && self::is_page_on_front() ) {
//
//				$query_type = 'posts';
//
//			} else {
//
//				$query_type = null;
//
//			}
//
//		} else if ( property_exists( $queried_object, 'posts' ) && is_array( $queried_object->posts ) ) {
//
//			$query_type = null;
//
//		}
//
//		return $query_type;
//
//	}

	/**
	 * @return mixed|void
	 */
	function front_page_query_type() {

		return 'posts' == get_option( 'show_on_front' ) ? 'posts' : 'page';

	}

	/**
	 * Is the Front Page configured to display a $post_type='page'?
	 *
	 * @return bool
	 */
	function is_page_on_front() {

		return $front_page_id = self::front_page_id() && WPLib::is_page( $front_page_id );

	}

	/**
	 * Is the Front Page configured to display a $post_type='page'?
	 *
	 * @return bool
	 */
	function front_page_id() {

		return intval( get_option( 'page_on_front' ) );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_previous_posts_link( $args = array() ) {

		echo get_previous_posts_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_next_posts_link( $args = array() ) {

		echo $this->get_next_posts_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_previous_posts_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'format'    => '<div class="nav-previous">%link</div>',
			'link_text' => esc_html__( 'Newer posts', 'wplib' ),
		) );

		$link = get_previous_posts_link( $args[ 'label' ] );

		return $link ? str_replace( '%link', $link, $args[ 'format' ] ) : '';

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_next_posts_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'format'    => '<div class="nav-next">%link</div>',
			'link_text' => esc_html__( 'Older posts', 'wplib' ),
			'max_page'  => 0,
		) );

		$link = get_next_posts_link( $args[ 'label' ], $args[ 'max_page' ] );

		return $link ? str_replace( '%link', $link, $args[ 'format' ] ) : '';

	}

	/**
	 * @param array $args
	 *
	 * @return bool
	 */

	function has_next_posts( $args = array() ) {

		return (bool) $this->get_next_posts_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	function has_previous_posts( $args = array() ) {

		return (bool) $this->get_previous_posts_link( $args );

	}

	/**
	 * @param string $template
	 * @param array|string $_template_vars
	 * @param WPLib_Entity_Base|object $entity
	 */
	static function the_template( $template, $_template_vars = array(), $entity = null ) {

	 	parent::the_template( $template, $_template_vars, WPLib_Theme::instance() );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function the_labeled_search_query_html( $args = array() ) {

		echo $this->get_labeled_search_query_html( $args  );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_labeled_search_query_html( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'label'        => __( 'Search Results for: %s', 'wplib' ),
			'before_query' => '<span>',
			'after_query'  => '</span>',

		) );

		$labeled_search_query = esc_html( sprintf( $args['label'], $this->search_query() ) );

		return "{$labeled_search_query}{$args[ 'before_query' ]}{$search_query}{$args[ 'after_query' ]}";

	}

	/**
	 * Return HTML for an arbitrary widget.
	 *
	 * @param string $widget   The widget's PHP class name (see default-widgets.php).
	 * @param array  $args {
	 *     Optional. Array of arguments to configure the display of the widget.
	 *
	 *     @type array  $instance      The widget's instance settings. Default empty array.
	 *     @type string $before_widget HTML content that will be prepended to the widget's HTML output.
	 *                                 Default `<div class="widget %s">`, where `%s` is the widget's class name.
	 *     @type string $after_widget  HTML content that will be appended to the widget's HTML output.
	 *                                 Default `</div>`.
	 *     @type string $before_title  HTML content that will be prepended to the widget's title when displayed.
	 *                                 Default `<h2 class="widgettitle">`.
	 *     @type string $after_title   HTML content that will be appended to the widget's title when displayed.
	 *                                 Default `</h2>`.
	 * }
	 *
	 * @return string
	 */
	function get_widget_html( $widget, $args = array() ) {

		ob_start();

		$this->the_widget_html( $widget, $args );

		return ob_get_clean();

	}

	/**
	 * Output an arbitrary widget.
	 *
	 * @param string $widget   The widget's PHP class name (see default-widgets.php).
	 * @param array  $args {
	 *     Optional. Array of arguments to configure the display of the widget.
	 *
	 *     @type array  $instance      The widget's instance settings. Default empty array.
	 *     @type string $before_widget HTML content that will be prepended to the widget's HTML output.
	 *                                 Default `<div class="widget %s">`, where `%s` is the widget's class name.
	 *     @type string $after_widget  HTML content that will be appended to the widget's HTML output.
	 *                                 Default `</div>`.
	 *     @type string $before_title  HTML content that will be prepended to the widget's title when displayed.
	 *                                 Default `<h2 class="widgettitle">`.
	 *     @type string $after_title   HTML content that will be appended to the widget's title when displayed.
	 *                                 Default `</h2>`.
	 * }
	 */
	function the_widget_html( $widget,  $args = array() ) {

		$args = wp_parse_args( $args, array(

			'instance'        => array(),

		));

		$instance = wp_parse_args( $args[ 'instance' ] );

		unset( $args[ 'instance' ] );

		the_widget( $widget, $instance, $args );

	}

	function get_converted_smilies_html( $text ) {

		return convert_smilies( $text );

	}

	function the_converted_smilies_html( $text ) {

		echo $this->get_converted_smilies_html( $text );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_categories_html( $args = array() ) {

		ob_start();

		$this->the_categories_html( $args );

		return ob_get_clean();

	}

	/**
	 * @param array $args
	 */
	function the_categories_html( $args = array() ) {

		$args = wp_parse_args( $args );

		wp_list_categories( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_search_form_html( $args = array() ) {

		return get_search_form( false );

	}

	/**
	 * @param array $args
	 */
	function the_search_form_html( $args = array() ) {

		get_search_form( true );

	}

	/**
	 * @param string $capability
	 * @return bool
	 */
	function user_can( $capability ) {

		return current_user_can( $capability );

	}

	/**
	 * Output a hyperlink with URL, Link Text and optional title text.
	 *
	 * @param string $href
	 * @param string $link_text
	 * @param array $args
	 *
	 * @return string
	 */

	static function the_link( $href, $link_text, $args = array() ) {

		WPLib::the_link( $href, $link_text, $args );

	}

	/**
	 * Create a hyperlink with URL, Link Text and optional title text.
	 *
	 * @param string $href
	 * @param string $link_text
	 * @param array $args
	 *
	 * @return string
	 */

	static function get_link( $href, $link_text, $args = array() ) {

		return WPLib::get_link( $href, $link_text, $args );

	}

	/**
	 * @param int $index
	 *
	 * @return bool
	 *
	 * @todo Add a $this->is_active_sidebar() with a deprecated warning.
	 */
	function is_sidebar_active( $index ) {

		return is_active_sidebar( $index );

	}

	/**
	 * @param $index
	 *
	 * @todo Add a $this->dynamic_sidebar() with a deprecated warning.
	 */
	function the_widget_area_html( $index ) {

		dynamic_sidebar( $index );

	}

	/**
	 * @param array $args
	 */
	function the_archive_title( $args = array() ) {

		echo $this->get_archive_title( $args );

	}

	/**
	 * @param array $args
	 * @return string
	 */
	function get_archive_title( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'before' => '<h1 class="page-title">',
			'after'  => '</h1>',

		));

		ob_start();

		the_archive_title( $args[ 'before' ], $args[ 'after' ] );

		return ob_get_clean();

	}

	/**
	 * @param array $args
	 */
	function the_archive_description( $args = array() ) {

		echo $this->get_archive_description( $args );

	}

	/**
	 * @param array $args
	 * @return string
	 */
	function get_archive_description( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'before' => '<div class="taxonomy-description">',
			'after'  => '</div>',

		));

		ob_start();

		the_archive_description( $args[ 'before' ], $args[ 'after' ] );

		return ob_get_clean();

	}

	/**
	 * @return bool
	 */
	function uses_threaded_comments() {

		return (bool) get_option( 'thread_comments' );

	}

	/**
	 * @return bool
	 */
	function uses_paged_comments() {

		return (bool) get_option( 'page_comments' );

	}

	/**
	 * @return int
	 */
	function comments_per_page() {

		return (int) get_option( 'comments_per_page' );

	}

	/**
	 * @return array
	 */
	function comments() {

		$comments = $this->query()->comments;

		return is_array( $comments ) ? $comments : array();

	}

	/**
	 * @return int
	 */
	function number_of_comment_pages() {

		if ( ! $this->uses_paged_comments() ) {

			$number = 1;

		} else {

			global $wp_the_query;
			global $wp_query;

			$save_query = $wp_query;
			$wp_query   = $wp_the_query;

			get_comment_pages_count(
				$this->comments(),
				$this->comments_per_page(),
				$this->uses_threaded_comments()
			);

			$wp_query = $save_query;

		}
		return $number;

	}

}
