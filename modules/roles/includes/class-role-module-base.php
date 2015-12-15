<?php

/**
 * Class WPLib_Role_Module_Base
 */
abstract class WPLib_Role_Module_Base extends WPLib_Module_Base {

	const ROLE = null;

	const CAPABILITIES = '';

	const INSTANCE_CLASS = null;

	/**
	 * @var array[] {
	 *     An array of information about the role as assigned in self::register_role().
	 *
	 *     @type string   $display_name  Title used to display the role to users.
	 *     @type string[] $capabilities  Array of capabilities that should be assigned to the role.
	 *     @type string   $class_name    Name of class defining the role that inherits from WPLib_Role_Module_Base.
	 * }
	 */
	private static $_roles = array();

	/**
	 * Add the code to trigger inspection of roles upon commit revision.
	 */
	static function on_load() {

		self::add_class_action( 'wplib_commit_revised' );
		self::add_class_action( 'wp_loaded', 11 );

	}

	/**
	 * @param string $display_name
	 */
	static function register_role( $display_name ) {

		self::$_roles[ static::ROLE ] = array(
			'display_name' => $display_name,
			'capabilities' => static::capabilities(),
			'class_name'   => get_called_class(),
		);

	}

	/**
	 * Release the memory used for the roles.
	 */
	static function _wp_loaded_11() {

		self::$_roles = null;

	}

	/**
	 * Initialize roles, if needed, when commits are revised.
	 *
	 * @param string $recent_commit
	 * @param string $previous_commit
	 */
	static function _wplib_commit_revised( $recent_commit, $previous_commit ) {

		self::_initialize_roles( $recent_commit, $previous_commit );

	}

	/**
	 * Runs through all the registered roles and ensures that all roles and get_capabilities
	 * are set as defined in the classes.
	 *
	 * @param string $recent_commit
	 * @param string $previous_commit
	 */
	private static function _initialize_roles( $recent_commit, $previous_commit ) {

		WPLib::autoload_all_classes();

		$app_slug = strtolower( WPLib::app_class() );

		$option = get_option( $option_name = "{$app_slug}_roles", array() );

		$wp_roles = new WP_Roles();

		$dirty = false;

		foreach( self::$_roles as $role_slug => $role ) {

			if ( preg_match( '#^WPLib_(Administrators|Editors|Contributors|Subscribers|Authors)$#', $role['class_name'] ) ) {

				/*
				 * Easier just to punt on these for right now.
				 * WordPress does some weird things with built-in roles,
				 * especially with Administrator related to Multisite
				 */
				continue;

			}

			if ( empty( $role_slug ) ) {

				/*
				 * Somehow we got an empty role slug?!?.  Carry on.
				 */
				continue;

			}

			$capabilities = call_user_func( array( $role['class_name'], 'capabilities' ) );

			if ( ! isset( $option[ $role_slug ] ) ) {

				$option[ $role_slug ] = array(

					'prior_capabilities' => $capabilities,
					'recent_commit'      => $recent_commit,
					'previous_commit'    => $previous_commit,

				);

				$dirty = true;

			}

			$display_name = self::get_role_display_name( $role_slug );

			$prior_capabilities = $option[ $role_slug ][ 'prior_capabilities' ];

			/*
			 * Get the capabilities
			 */
			if ( is_null( $current_capabilities = $wp_roles->role_objects[ $role_slug ]->capabilities ) ) {

				$current_capabilities = array();

			} else {

				/**
				 * Remove all the legacy level_0 through level_10 capabilities.
				 */
				for ( $i = 0; $i <= 10; $i ++ ) {

					unset( $current_capabilities["level_{$i}"] );

				}

			}

			if ( empty( $prior_capabilities ) ) {

				/**
				 * First time in, let's assume previous are same as current.
				 */
				$prior_capabilities = $current_capabilities;

			}

			/**
			 * Filter the capabilities that should be applied for the role.
			 *
			 * @since 0.11.0
			 *
			 * @param string[] $capabilities
			 * @param string   $role_slug {
			 * @param array    $role {
			 *     An array of information about the role as assigned in self::register_role().
			 *
			 *     @type string   $display_name  Title used to display the role to users.
			 *     @type string[] $capabilities  Array of capabilities that should be assigned to the role.
			 *     @type string   $class_name    Name of class defining the role that inherits from WPLib_Role_Module_Base.
			 * }
			 */
			$capabilities = apply_filters( 'wplib_role_capabilities', $capabilities, $role_slug, $role );

			$capabilities = array_fill_keys( $capabilities, true );

			if ( defined( 'WPLIB_UPDATE_ROLES' ) && WPLIB_UPDATE_ROLES ) {

			    $change_role = true;

			} else if ( ! isset( $wp_roles->roles[ $role_slug ] ) ) {

				/*
				 * Whelp, the role does not exists, so let's add it.
				 */
				$change_role = true;

			} else if ( isset( $merged ) || ! self::_arrays_are_equivalent( $capabilities, $current_capabilities ) ) {

				/*
				 * The new capabilities are different than the current ones, AND
				 * nobody  changed the capabilities since we last updated them.
				 *
				 * This stops manually changed capabilities from being overwritten
				 * at the expense of not containing new capabilities defined in the
				 * code. Better to respect the user's efforts and add a burden on
				 * them then to ignore the user's efforts and simply reset.
				 */
				$change_role = self::_arrays_are_equivalent( $current_capabilities, $prior_capabilities );

			} else if ( $display_name !== $wp_roles->role_names[ $role_slug ] ) {

				/*
				 * The display name has changed so let's update the role.
				 */
				$change_role = true;

			} else if ( $display_name !== $wp_roles->role_names[ $role_slug ] ) {

				/*
				 * Does not seem there is a reason to change.
				 */
				$change_role = false;

			} else {

				/*
				 * Bah. Don't change it. No evidence we need to.
				 */
				$change_role = false;

			}


			if ( $change_role ) {

				/**
				 * @note: Just FYI, this will remove the legacy get_capabilities of level_0..level_10.
				 * @note: Should not be an issue for a modern WP app. If it becomes an issue we can test for them too.
				 */
				remove_role( $role_slug );

				add_role( $role_slug, $display_name, $capabilities );

				$option[ $role_slug ]= array(
					'prior_capabilities' => $capabilities,
					'recent_commit'      => $recent_commit,
					'previous_commit'    => $previous_commit,
				);

				$dirty = true;

			}

		}
		self::$_roles = array();

		if ( $dirty ) {

			update_option( $option_name, $option, 'no' );

			/**
			 * @todo Change this to redirect to the same URL they were on
			 *       Which means adding something like WPLib::current_url().
			 *       Maybe even a WPLib::redirect_to_self().
			 *       But I want to sleep on that a few days first.
			 */
			wp_safe_redirect( home_url( '/' ) );
			exit;

		}


	}

	/**
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return bool
	 */
	private static function _arrays_are_equivalent( $array1, $array2 ) {

		return count( $array1 ) === count( array_intersect( $array2, $array1 ) );

	}

	/**
	 * @return array|mixed
	 */
	static function role_slug() {

		return defined( 'static::ROLE' ) ? static::ROLE : null;

	}

	/**
	 * @param null|string $role_slug
	 *
	 * @return string
	 */
	static function get_role_display_name( $role_slug = null ) {

		if ( is_null( $role_slug ) ) {

			$role_slug = static::role_slug();

		}

		return isset( self::$_roles[ $role_slug ] )
			? self::$_roles[ $role_slug ][ 'display_name' ]
			: null;

	}

	/**
	 * Return array of CAPABILITIES by merging constants with parents classes
	 *
	 * @return string[]
	 */
	static function capabilities() {

		return static::get_capabilities( get_called_class() );
	}

	/**
	 * Return array of CAPABILITIES by merging constants with parents classes
	 *
	 * @param string|null $class_name
	 * @return string[]
	 */
	static function get_capabilities( $class_name ) {

		$parent_of_called = get_parent_class( $class_name );

		$parent_capabilities = defined( $const_ref = "{$parent_of_called}::CAPABILITIES" )
			? call_user_func( array( $parent_of_called, 'capabilities' ) )
			: array();

		$capabilities = count( $parent_capabilities )
			? array_merge( $parent_capabilities, explode( '|', static::CAPABILITIES ) )
			: explode( '|', static::CAPABILITIES );

		return array_unique( $capabilities );

	}

//	/**
//	 * @param array|string $deletions A string or array of get_capabilities to remove from this role.
//	 */
//	static function remove_role_capabilities( $deletions ) {
//
//		if ( is_string( $deletions ) ) {
//
//			$deletions = explode( ',', $deletions );
//
//		}
//
//		$capabilities = self::$_roles[ static::ROLE ]['capabilities'];
//
//		self::$_roles[ static::ROLE ]['capabilities'] =
//			array_diff( $capabilities, $deletions );
//
//	}


}

WPLib_Role_Module_Base::on_load();
