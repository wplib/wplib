<?php

class WPLib_Post_Module_Base extends WPLib_Module_Base {

	/**
	 * @param array|string|WPLib_Query $query
	 * @param array $args
	 * @return WPLib_Post_List
	 */
	static function get_list( $query = array(), $args = array() ) {

		if ( ! ( $instance_class = static::instance_class() ) ) {

			$value = null;

		} else {

			$value = call_user_func( array( $instance_class, __FUNCTION__ ), $query, $args );

		}

		return $value;

	}
}
