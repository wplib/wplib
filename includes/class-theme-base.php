<?php

/**
 * Class WPLib_Theme_Base
 *
 * @method void the_sitename()
 */
abstract class WPLib_Theme_Base extends WPLib {

	/**
	 * Used by the_template() to assign an instance of this class to variable with this name.
	 */
	const VAR_NAME = 'theme';

	/**
	 * @var static
	 */
	private static $_theme;

	/**
	 * Sets hooks required by all themes.
	 */
	static function on_load() {

		self::add_class_action( 'wp_enqueue_scripts', 0 );
		self::add_class_action( 'template_include', 999 );

	}

	/**
	 * Hijack `template_include` so that we can ensure a $theme variable is defined.
	 *
	 * @param string $template;
	 *
	 * @return static
	 */
	static function _template_include_999( $template ) {

		if ( ! $template ) {

			$message = __( '<p>No template file found. You may have deleted the current theme or renamed the theme directory?</p>' .
				'<p>If you are a site admin <a href="%s">click here</a> to verify and possibly correct.</p>', 'wplib' );

			echo sprintf( $message, esc_url( site_url( '/wp-admin/themes.php') ) );

		} else {

			global $theme;

			/*
			 * Make $theme available inside the template.
			 */
			$theme = self::instance();

			include( $template );

		}
		return false;

	}

	/**
	 * Create an instance of get_called_class()
	 *
	 * @return static
	 */
	static function instance() {

		if ( ! isset( static::$_theme ) ) {

			foreach( WPLib::app_classes() as $class_name ) {

				if ( is_subclass_of( $class_name, __CLASS__ )  ) {

					/*
					 * Will create instance of FIRST class found that subclasses WPLib_Theme_Base.
					 * That means sites should ONLY have ONE subclass of WPLib_Theme_Base.
					 */
					self::$_theme = new $class_name();
					break;

				}

			}

		}

		return self::$_theme;

	}

	/**
	 * Theme method for setting a theme isntance for unit test mocking.
	 *
	 * @param $theme
	 *
	 * @return mixed
	 */
	static function set_mock_theme( $theme ) {

		return static::$_theme = $theme;

	}

	/**
	 * Creates a JS variable WPLib.ajaxurl
	 *
	 *  Priority 0 ONLY so that this static function does not conflict with the instance method in child classes
	 */
	static function _wp_enqueue_scripts_0() {

		wp_localize_script( 'wplib-script', 'WPLib', array(

			'ajaxurl' => admin_url( 'admin-ajax.php' ),

		));

	}

	/**
	 * Return the site name as configured.
	 *
	 * @return string|void
	 */
	function sitename() {

		return get_bloginfo( 'name' );

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

		echo '<meta charset="' . get_bloginfo( 'charset' ) . '">';

	}

	/**
	 * Output the content for the wp_head() method;
	 */
	function the_wp_head_html() {

		wp_head();

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
		$wp_query->set( 'theme', self::$_theme );

		get_header( $name );

	}

	/**
	 * Generate the HTML for a nav menu.
	 *
	 * @param string $location  Defaults to 'primary' theme location.
	 * @param array $args
	 */
	static function the_menu_html( $location = 'primary', $args = array() ) {

		$args = wp_parse_args( $args, array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => false,
			'items_wrap'     => false,
		) );

		wp_nav_menu( $args );

	}


	function the_footer_html( $name = null ) {

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		/*
		 * Make $theme visible inside footer.php
		 */
		$wp_query->set( 'theme', self::$_theme );

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
		$wp_query->set( 'theme', self::$_theme );

		get_sidebar( $name );

	}

	/**
	 * Output the HTML element <meta name="viewport" content="...">.
	 *
	 * @param array $args
	 */
	function the_meta_viewport_html( $args = array() ) {

		$args = wp_parse_args( $args );

		if ( count( $args ) ) {

			$attributes = esc_attr( implode( ',', array_map(

				function ($value, $key) {

					return "{$key}={$value}";
				},
				$args,
				array_keys( $args )

			)));

			echo "<meta name=\"viewport\" content=\"{$attributes}\" >";

		}

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
	 * Can user comments?
	 *
	 * Yes if no password or password provided and comments are open.
	 *
	 * @param WPLib_Post_Base $post
	 * @return bool
	 */
	function user_can_comment( $post ) {

		return ! post_password_required( $post ) && $post->comments_open();

	}

	/**
	 * Can user see comments?
	 *
	 * Yes if no password or password provided and comments are either open or at least one comment exists.
	 *
	 * @param WPLib_Post_Base|WP_Post $post
	 *
*@return bool
	 */
	function user_can_see_comments( $post ) {

		if ( ! is_a( $post, 'WP_Post' ) && method_exists( $post, 'post' ) ) {

			$post = $post->post();

		}

		return ! post_password_required( $post ) && ( $post->comments_open() || $post->comments_number() );

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
	 * @note Ignores 'Uncategorized'
	 *
	 * @return int|mixed
	 */
	function has_categories_in_use() {

		return 0 < $this->in_use_category_count();

	}

	/**
	 * Return number of is use categories on posts.
	 *
	 * @note Ignores 'Uncategorized'
	 *
	 * @return int|mixed
	 */
	function in_use_category_count() {

		$category_count = WPLib::cache_get( $cache_key = 'in_use_category_count' );

		if ( false === $category_count ) {
			/*
			 * Get array of all categories are attached to posts.
			 */
			$categories = get_categories( 'fields=ids&hide_empty=1' );

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
	 * @return WPLib_Entity_Base
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
	function get_post_entity_list( $args = array() ) {

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

	function is_home() {

		global $wp_the_query;

		return (bool) $wp_the_query->is_home;

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

	 	parent::the_template( $template, $_template_vars, self::instance() );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function the_labeled_search_query( $args = array() ) {

		echo $this->get_labeled_search_query( $args  );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_labeled_search_query( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'label'         => __( 'Search Results for: %s', 'wplib' ),
			'before_query'  => '<span>',
			'after_query'   => '</span>',

		));

		$labeled_search_query = esc_html( sprintf( $args[ 'label' ], $this->search_query() ) );

		return "{$labeled_search_query}{$args[ 'before_query' ]}{$search_query}{$args[ 'after_query' ]}";

	}


}
WPLib_Theme_Base::on_load();

