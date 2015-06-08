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
 * @method void the_ID()
 *
 * @todo Break out some of these more prescriptive methods into a helper module so they can be ommitted if desired.
 */
abstract class WPLib_Post_View_Base extends WPLib_View_Base {

	/**
	 * @var object {
	 *      @type int $page
	 *      @type int $numpages;
	 *      @type bool $more;
	 *            }
	 */
	var $multipage;

	function __construct( $args ) {

		/**
		 * @todo Handle multipage specific to the object instance vs. via global vars.
		 */
		$this->_set_multipage_property();

		parent::__construct( $args );

	}

	/**
	 * Provide easy access to the post object
	 *
	 * @return WP_Post
	 */
	function post() {

		return $this->model()->post();

	}

	/**
	 * @param array $args
	 *
	 * @return null|string
	 */
	function the_title_html( $args = array() ) {

		echo wp_kses_post( $this->get_title_html( $args ) );

	}

	/**
	 * @param array $args
	 *
	 * @return null|string
	 */
	function get_title_html( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'before'    => '<h1 class="entry-title">',
			'after'     => '</h1>',
		) );

		$title = $this->model()->title();

		if ( 0 !== strlen( $title ) ) {

			$title = "{$args[ 'before' ]}{$title}{$args[ 'after' ]}";

		}

		return $title;

	}

	/**
	 * Echos HTML for the title hyperlinked with the post's URL.
	 *
	 * @param array $args
	 */
	function the_title_link( $args = array() ) {

		echo wp_kses_post( $this->get_title_link( $args ) );

	}

	/**
	 * Returns HTML for the title hyperlinked with the post's URL.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function get_title_link( $args = array() ) {

		return $this->get_link( $this->model()->title(), $args );

	}


	/**
	 * Returns HTML for any value hyperlinked with the post's URL.
	 *
	 * @param string $link_text
	 * @param array $args
	 *
	 * @return string
	 */
	function get_link( $link_text, $args = array() ) {

		$model = $this->model();

		$url = $model->url();

		return WPLib::get_link( $url, $link_text, $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_adjacent_post_link( $args = array() ) {

		if ( ! $this->has_post() )  {

			$adjacent_post = null;

		} else {

			global $post;

			$args = wp_parse_args( $args, array(
				'format'         => '<div class="nav-previous">%link</div>',
				'link_format'    => false,
				'in_same_term'   => false,
				'excluded_terms' => '',
				'taxonomy'       => 'category',
				'previous'       => null,
			) );

			$save_post = $post;

			$post = $this->_post;

			if ( function_exists( 'get_adjacent_post_link' ) ) {

				$adjacent_post = get_adjacent_post_link(
					$args['format'],
					$args['link_format'],
					$args['in_same_term'],
					$args['excluded_terms'],
					$args['previous'],
					$args['taxonomy']
				);

			} else {

				/**
				 * Add support to pre 3.7 WordPress
				 *
				 * @todo Add error messages when taxonomy != category
				 * @todo and when 'link_format' is not false
				 */
				$adjacent_post = get_adjacent_post_rel_link(
					$args['format'],
					$args['in_same_term'],
					$args['excluded_terms'],
					$args['previous']
				);

			}
			$post = $save_post;

		}

		return $adjacent_post;

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_previous_post_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'format' => '<div class="nav-previous">%link</div>',
		));
		$args[ 'previous' ] = true;
		return $this->get_adjacent_post_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_next_post_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'format' => '<div class="nav-next">%link</div>',
		));
		$args[ 'previous' ] = false;
		return $this->get_adjacent_post_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_previous_post_link( $args = array() ) {

		echo $this->get_previous_post_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_next_post_link( $args = array() ) {

		echo $this->get_next_post_link( $args );

	}

	function the_css_classes_attr( $classes = false ) {

		echo $this->get_css_classes_attr( $classes );

	}

	function get_css_classes_attr( $classes = false ) {

		return get_post_class( $classes, $this->model()->ID() );

	}

	/**
	 * @param array $args
	 * @return string
	 */
	function the_multipage_links_html( $args = array() ) {

		echo wp_kses_post( $this->get_multipage_links_html( $args ) );

	}

	/**
	 * @param array $args
	 * @return string
	 */
	function get_multipage_links_html( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wplib' ),
			'after'  => '</div>',

		));

		$args[ 'echo' ] = false;

		$globals = $this->_save_multipage_globals();

		if ( false !== $this->multipage ) {

			$this->_set_multipage_globals();

		}

		$links_html = wp_link_pages( $args );

		$this->_restore_multipage_globals( $globals );

		return $links_html;

	}

	/**
	 * Set $this->multipage property from the multipage global variables
	 */
	private function _set_multipage_property() {

		global $page, $numpages, $multipage, $more;
		if ( $multipage ) {

			$this->multipage = (object) array(
				'page'     => $page,
				'numpages' => $numpages,
				'more'     => $more,
			);

		}

	}

	/**
	 * Set multipage global variables from $this->multipage
	 */
	private function _set_multipage_globals() {

		global $multipage, $page, $numpages, $more;

		$multipage = true;
		$page      = $this->multipage->page;
		$numpages  = $this->multipage->numpages;
		$more      = $this->multipage->more;

	}

	/**
	 * @return array
	 */
	private function _save_multipage_globals() {

		global $page, $numpages, $multipage, $more;
		return compact( $page, $numpages, $multipage, $more );

	}

	/**
	 * @param array $globals
	 */
	private function _restore_multipage_globals( $globals ) {

		global $page, $numpages, $multipage, $more;
		call_user_func( 'extract', $globals );

	}

	/**
	 * @param bool $method_name
	 * @param string $to_output
	 */
	function the( $method_name, $to_output ) {

		if ( method_exists( $this->model, $method_name ) && is_callable( $callable = array( $this->model, $method_name ) ) ) {

			if ( call_user_func( $callable ) ) {

				echo $to_output;

			}

		}


	}

	/**
	 * @param bool $method_name
	 * @param string $to_output
	 */
	function the_not( $method_name, $to_output ) {

		if ( method_exists( $this->model, $method_name ) && is_callable( $callable = array( $this->model, $method_name ) ) ) {

			if ( ! call_user_func( $callable ) ) {

				echo $to_output;

			}

		}


	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_author_hcard_html( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'html_template' => '<span class="author vcard"><a class="url fn n" href="%s">%s</a></span>',

		));

		$author = $this->model()->author();

		return sprintf( $args[ 'html_template' ], esc_url( $author->posts_url() ), esc_html( $author->display_name() ) );

	}

	/**
	 * Output an edit link for this post.
	 *
	 * @param array $args
	 */
	function the_edit_link( $args = array() ) {

		echo wp_kses_post( $this->get_edit_link( $args ) );

	}

	/**
	 * Return an edit link for this post.
	 *
	 * @param array $args
	 * @return string
	 */
	function get_edit_link( $args = array() ) {

		$args = wp_parse_args( $args, array(

			'link_text' => __( 'Edit', 'wplib' ),
			'before' => '<span class="edit-link{{class}}">',
			'after'  => '</span>',
			'class'  => false

		));

		$args[ 'class' ] = $args[ 'class' ] ? " {$args[ 'class' ]}" : '';

		$args[ 'before' ] = str_replace( '{{class}}', $args[ 'class' ], $args[ 'before' ] );

		ob_start();

		edit_post_link( $args[ 'link_text' ], $args[ 'before' ], $args[ 'after' ], $this->ID() );

		return ob_get_clean();

	}

	/**
	 * @return bool
	 */
	function has_categories() {

		return wp_get_object_terms( $this->ID(), WPLib_Category::TAXONOMY );

	}

	/**
	 * @see Alias of $this->has_post_tags()
	 *
	 * @return bool
	 */
	function has_tags() {

		return $this->has_post_tags();
	}

	/**
	 * @canonical Has alias $this->has_tags()
	 *
	 * @return bool
	 */
	function has_post_tags() {

		return wp_get_object_terms( $this->ID(), WPLib_Post_Tag::TAXONOMY );

	}

	/**
	 * @param array $args
	 */
	function the_category_list_links_html( $args = array() ) {

		echo wp_kses_post( $this->get_category_list_links_html( $args ) );

	}

	/**
	 * @param array $args
	 *
	 * @return bool|string
	 */
	function get_category_list_links_html( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'html_template' => __( 'Posted in %1$s', 'wplib' ),
			'before'        => '<span class="cat-links{{class}}">',
		));

		$this->get_terms_list_links_html( $args );

	}

	/**
	 * @param array $args
	 */
	function the_post_tag_list_links_html( $args = array() ) {

		echo wp_kses_post( $this->get_post_tag_list_links_html( $args ) );

	}

	/**
	 * @param array $args
	 *
	 * @return bool|string
	 */
	function get_post_tag_list_links_html( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'html_template' => __( 'Tagged %1$s', 'wplib' ),
			'before'        => '<span class="tags-links{{class}}">',
		));

		$this->get_terms_list_links_html( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return bool|string
	 */
	function get_terms_list_links_html( $args = array() ) {

		$args = wp_parse_args( $args, array(
			/*
			 * translators: used between list items, there is a space after the comma
			 */
			'separator'     => __( ', ', 'wplib' ),
			'parents'       => '',
			'html_template' => __( 'Posted in %1$s', 'wplib' ),
			'before'        => '<span class="term-links{{class}}">',
			'after'         => '</span>',
			'class'         => false,
			'taxonomy'      => false,

		) );

		$args['class'] = $args['class'] ? " {$args[ 'class' ]}" : '';

		$args['before'] = str_replace( '{{class}}', $args['class'], $args['before'] );

		switch( $args[ 'taxonomy' ] ) {
			case WPLib_Category::TAXONOMY:

				$list = get_the_category_list(
					esc_html( $args['separator'] ),
					$args['parents'],
					$this->ID()
				);
				break;

			case WPLib_Post_Tag::TAXONOMY:

				$list = get_the_tag_list(
					'', // before
					esc_html( $args['separator'] ),
					'', // after
					$this->ID()
				);
				break;

			default:

				$list = get_the_term_list(
					$this->ID(),
					$args[ 'taxonomy' ],
					'', // before
					esc_html( $args['separator'] ),
					'' // after
				);
				break;

		}

		if ( ! $list ) {

			$html = false;

		} else {

			$args['html_template'] = esc_html( $args['html_template'] );

			$html = sprintf( "{$args[ 'before' ]}{$args[ 'html_template' ]}{$args[ 'after' ]}", $list );

		}

		return $html;

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_comments_html( $args = array() ) {

		ob_start();

		$this->the_comments_html( $args );

		return ob_get_clean();

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_comments_html( $args = array() ) {

		if ( ! $this->has_post() )  {

			$comments_html = null;

		} else {

			global $post;

			$args = wp_parse_args( $args, array(
				'template_file'  => '/comments.php',
				'group_by_type'  => false,
			) );

			$save_post = $post;

			$post = $this->model()->post();

			comments_template( $args[ 'template_file' ], $args[ 'group_by_type' ] );

			$post = $save_post;

		}

		return $comments_html;

	}

	/**
	 *
	 */
	function the_content_html() {

		echo wp_kses_post( $this->get_content_html() );

	}

	/**
	 * @return string
	 */
	function get_content_html() {

		return $this->model()->content();

	}

	/**
	 *
	 */
	function the_excerpt_html() {

		echo wp_kses_post( $this->get_excerpt_html() );

	}

	/**
	 * @return string
	 */
	function get_excerpt_html() {

		return $this->model()->excerpt();

	}

	/**
	 *
	 */
	function the_number_of_comments_html() {

		return $this->get_number_of_comments_html();

	}

	/**
	 * @return string
	 */
	function get_number_of_comments_html() {

		return number_format_i18n( $this->model()->number_of_comments(), 0 );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_previous_comments_link( $args = array() ) {

		echo get_previous_comments_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function the_next_comments_link( $args = array() ) {

		echo $this->get_next_comments_link( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_previous_comments_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'format'    => '<div class="nav-previous">%link</div>',
			'link_text' => esc_html__( 'Older Comments', 'wplib' ),
		) );

		$link = get_previous_comments_link( $args[ 'label' ] );

		return $link ? str_replace( '%link', $link, $args[ 'format' ] ) : '';

	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function get_next_comments_link( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'format'    => '<div class="nav-next">%link</div>',
			'link_text' => esc_html__( 'Newer Comments', 'wplib' ),
			'max_page'  => 0,
		) );

		$link = get_next_comments_link( $args[ 'label' ], $args[ 'max_page' ] );

		return $link ? str_replace( '%link', $link, $args[ 'format' ] ) : '';

	}

	/**
	 * @param array $args
	 *
	 */
	function the_comment_list_html( $args = array() ) {

		wp_list_comments( $args, WPLib::theme()->query()->comments );

	}

	/**
	 * @param array $args
	 */
	function the_comment_form_html( $args = array() ) {

		if ( $this->has_post() ) {

			comment_form( $args, $this->_post->ID );

		}

	}


}

