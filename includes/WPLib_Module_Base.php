<?php

/**
 * Class WPLib_Module_Base
 */
abstract class WPLib_Module_Base extends WPLib {

	const MODULE_NAME = null;

	const INSTANCE_CLASS = null;

	/**
	 * @return mixed|null
	 */
	static function instance_class() {
		/**
		 * See if module has an INSTANCE_CLASS constant defined.
		 */
		return static::INSTANCE_CLASS;

	}

	/**
	 * @return mixed|null
	 */
	static function var_name() {

		return WPLib::get_constant( 'VAR_NAME', static::instance_class() );

	}


	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args {
	 *
	 *      @type string $list_class        The specific class for the list, i.e. WPLib_Post_List
	 *
	 *      @type string $default_list      The default list if no $list_class, i.e. WPLib_Post_List_Default
	 *
	 *      @type string $items The array of items, or a callable that will return a list of items.
	 *
	 *      @type string $list_owner        The class "owning" the list, typically "Owner" if Owner::get_list()
	 *
	 *      @type string $instance_class    The class for items in the list, i.e. WP_Post
	 * }
	 *
	 * @return WPLib_List_Default[]
	 *
	 */
	static function get_list( $query = array(), $args = array() ) {

		if ( is_string( $query ) ) {

			$query = wp_parse_args( $query );

		} else if ( is_null( $query ) ) {

			$query = array();

		}

		if ( is_string( $args ) ) {

			$args = wp_parse_args( $args );

		} else if ( is_null( $args ) ) {

			$args = array();

		}

		if ( ! isset( $args['list_owner'] ) ) {

			$args['list_owner'] = get_called_class();

		}

		if ( ! isset( $args['instance_class'] ) ) {

			$args['instance_class'] = WPLib::get_constant( 'INSTANCE_CLASS', $args['list_owner'] );

		}

		$try_class = $args['instance_class'];

		$args = wp_parse_args( $args, array(

			'list_class'        => "{$try_class}_List",
			'default_list'      => 'WPLib_List_Default',
			'items'             => false,

		));

		if ( ! class_exists( $args['list_class'] ) ) {

			do {

				/**
				 * @future Provide a more robust mechanism for discovering 'list_class'
				 */

				/*
				 * Add '_Default' to last list class checked,
				 * i.e. WPLib_Post_List_Default for WPLib_Posts::get_list()
				 */
				$args['list_class'] = "{$args['list_class']}_Default";
				if ( class_exists( $args['list_class'] ) ) {
					break;
				}

				$args['list_class'] = false;

				$try_class = preg_replace( '#^(.+)_Base$#', '$1', get_parent_class( $try_class ) );

				if ( ! $try_class ) {
					break;
				}

				/*
				 * Add '_List' to element class,
				 * i.e. WPLib_Post_List for WPLib_Posts::get_list()
				 */
				$args['list_class'] = "{$try_class}_List";

				if ( class_exists( $args['list_class'] ) ) {
					break;
				}

			} while ( $try_class );

		}

		if ( ! $args['list_class'] ) {
			/*
			 * Give up and use default, i.e. WPLib_List_Default
			 */
			$args['list_class'] = $args['default_list'];

		}

		$list_class = $args['list_class'];

		$items = is_callable( $args['items'] )
			? call_user_func( $args['items'], $query, $args )
			: null;

		if ( is_null( $args['instance_class'] ) ) {

			$message = __( 'No constant %s::INSTANCE_CLASS defined.', 'wplib' );
			WPLib::trigger_error( sprintf( $message, $args['list_owner'] ) );

			$list = array();

		} else {

			$list = ! is_null( $items ) ? new $list_class( $items, $args ) : array();

		}

		unset(
			$args['list_owner'],
			$args['list_class'],
			$args['list_default'],
			$args['default_list'],
			$args['items']
		);

		return $list;
	}

}
