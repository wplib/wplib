<?php

/**
 * Class WPLib_User_Base
 *
 * The Base Item Class for Users
 *
 * @mixin WPLib_User_Model_Base
 * @mixin WPLib_User_View_Base
 *
 * @property WPLib_User_Model_Base $model
 * @property WPLib_User_View_Base $view
 */
abstract class WPLib_User_Base extends WPLib_Item_Base {

	/**
	 * Child class should define a valid value for ROLE
	 */
	const ROLE = null;

	/**
	 * @var WPLib_User_Base
	 */
	private $_user;

	/**
	 * @param WP_User|int|string|null $user
	 * @param array $args
	 */
	function __construct( $user, $args = array() ) {

		if ( ! is_null( $user ) ) {

			$this->_user = WPLib_Users::get_user( $user );

		}

		$args = wp_parse_args( $args, array(
			'model' => array( 'user' => $user ),
		));

		parent::__construct( $args );

	}

	/**
	 * @param array $args
	 *
	 * @return WPLib_User_Model_Base|null
	 */
	static function make_new( $args ) {

		$user = null;

		if ( ! empty( $args['user'] ) ) {

			$user = $args['user'];

		}

		if ( ! is_null( $user ) && ! is_a( $user, 'WP_User' ) ) {

			if ( is_numeric( $user ) ) {

				$user = get_user_by( 'id', $user );

			} else if ( false !== strpos( $user, '@' ) ) {

				$user = get_user_by( 'email', $user );

			} else if ( ! ( $user = get_user_by( 'slug', $user ) ) ) {

				$user = get_user_by( 'login', $user );
			}

		}
		return $user ? WPLib_Users::make_user( $user ) : null;

	}

	/**
	 * @return bool
	 */
	function is_current_user() {

		return $this->ID() === get_current_user_id();

	}

}

