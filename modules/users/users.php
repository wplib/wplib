<?php

/**
 * Class WPLib_Users
 */
class WPLib_Users extends WPLib_Module_Base {

	const INSTANCE_CLASS = 'WPLib_User';

	/**
	 * Run on WordPress's 'init' hook to register all the user types defined in classes that extend this class.
	 */
	static function on_load() {

		/**
		 * Add this class as a helper to WPLib
		 */
		self::register_helper( __CLASS__, 'WPLib' );

		/**
		 * Hook wp_loaded so roles can be initialized
		 */
		self::add_class_action( 'wp_loaded' );

	}

	/**
	 * Register all the default roles.
	 */
	static function _wp_loaded() {

		self::register_role( WPLib_Administrator::ROLE, __( 'Administrator', 'newclarity' ), array(

			'activate_plugins',
			'add_users',
			'create_roles',
			'create_users',
			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_plugins',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_posts',
			'delete_roles',
			'delete_themes',
			'delete_users',
			'edit_dashboard',
			'edit_files',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_plugins',
			'edit_posts',
			'edit_private_pages',
			'edit_published_pages',
			'edit_published_posts',
			'edit_roles',
			'edit_theme_options',
			'edit_themes',
			'edit_users',
			'export',
			'import',
			'install_plugins',
			'install_themes',
			'list_roles',
			'list_users',
			'manage_categories',
			'manage_links',
			'manage_options',
			'moderate_comments',
			'promote_users',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'remove_users',
			'restrict_content',
			'switch_themes',
			'unfiltered_html',
			'unfiltered_upload',
			'update_core',
			'update_plugins',
			'update_themes',
			'upload_files',

		));

		self::register_role( WPLib_Editor::ROLE, __( 'Editor', 'newclarity' ),array(

			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_pages',
			'delete_published_posts',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_posts',
			'edit_private_pages',
			'edit_private_posts',
			'edit_published_pages',
			'edit_published_posts',
			'manage_categories',
			'manage_links',
			'moderate_comments',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'unfiltered_html',
			'upload_files',

		));

		self::register_role( WPLib_Author::ROLE, __( 'Author', 'newclarity' ),array(

			'delete_posts',
			'delete_published_pages',
			'edit_posts',
			'edit_private_posts',
			'publish_posts',
			'read',
			'upload_files',

		));

		self::register_role( WPLib_Contributor::ROLE, __( 'Contributor', 'newclarity' ),array(

			'delete_posts',
			'edit_posts',
			'read',

		));

		self::register_role( WPLib_Subscriber::ROLE, __( 'Subscriber', 'newclarity' ),array(

			'read',

		));

	}



	/**
	 * @param bool|string $role_slug
	 * @param string $display_name
	 * @param string[] $capabilities
	 */
	static function register_role( $role_slug, $display_name, $capabilities ) {

		WPLib_User_Base::set_role_name( $display_name, $role_slug );

		WPLib_User_Base::add_role_capabilities( $capabilities, $role_slug );

	}

	/**
	 * Checks an object to see if it has all the user-specific properties.
	 *
	 * If it does we can be almost sure it's a user.  Good enough for 99.9% of use-cases, anyway.
	 *
	 * @param object $user
	 *
	 * @return bool
	 */
	static function is_user( $user ) {

		return $user instanceof WP_User;

	}

	/**
	 * @param string $by
	 * @param string|int|object $user
	 *
	 * @return bool|WP_User
	 */
	static function get_user_by( $by, $user ) {

		$wp_user = get_user_by( $by, $user );

		return $wp_user ? static::make_user( $wp_user ) : null;

	}

	/**
	 * @var string $by
	 * @var string $role
	 * @return array|mixed
	 */
	static function get_role_slug_by( $by, $role ) {

		switch ( $by ) {
			case 'class':
			case 'class_name':

				$role_slug = static::get_constant( 'ROLE', $role );
				break;

			case 'name':
			case 'role_name':

				$roles = array_flip( WPLib_User_Base::display_names() );
				$role_slug = isset( $roles[ $role ] ) ? $roles[ $role ] : null;
				break;

		}
		return $role_slug;

	}

	/**
	 * @var string $role_slug
	 * @return array|mixed
	 */
	static function get_role_class( $role_slug ) {

		$role_classes = static::role_classes();

		return isset( $role_classes[ $role_slug ] ) ? $role_classes[ $role_slug ] : null;

	}

	/**
	 * @return array|mixed
	 */
	static function role_classes() {

		if ( ! ( $role_classes = WPLib::cache_get( $cache_key = 'role_classes' ) ) ) {

			WPLib::autoload_all_classes();

			$role_classes = array();

			foreach( get_declared_classes() as $user_class ){

			  if ( ! is_subclass_of( $user_class, 'WPLib_User_Base' ) ) {

			    continue;

			  }

			  if ( $role_slug = self::get_role_slug_by( 'class', $user_class ) ) {

				  $role_classes[ $role_slug ] = $user_class;
			  }

			}

			WPLib::cache_set( $cache_key, $role_classes );

		}
		return $role_classes;

	}

	/**
	 * @param string|int|object $user
	 * @return WP_User
	 */
	static function get_user( $user ) {

		if ( is_object( $user ) ) {

			$wp_user = $user instanceof WP_User ? $user : null;

		} else if ( is_numeric( $user ) ) {

			$wp_user = get_user_by( 'id', $user );

		} else {

			do {

				if( ! ( $wp_user = get_user_by( 'slug', $user ) ) ) {
					break;
				}

				if( ! ( $wp_user = get_user_by( 'login', $user ) ) ) {
					break;
				}

				if( ! ( $wp_user = get_user_by( 'email', $user ) ) ) {
					break;
				}

				$wp_user = null;

			} while ( false );

		}

		return $wp_user ;

	}

	/**
	 * @param WP_User $wp_user
	 *
	 * @return mixed|null
	 */
	static function make_user( $wp_user ) {

		if ( ! ( $role_slug = self::get_assigned_role_slug( $wp_user ) ) ) {

			$user = null;

		} else {

			$role_class = self::get_role_class( $role_slug );

			$user = new $role_class( $wp_user );
		}

		return $user;

	}

	/**
	 * User's role name as found in the WP_User object.
	 *
	 * @param WP_User $wp_user
	 * @return string|null
	 */
	static function get_assigned_role_slug( $wp_user ) {

		if ( ! property_exists( $wp_user, 'roles' ) || ! is_array( $wp_user->roles ) ) {

			$role_slug = null;

		} else {

			$role_slug = reset( $wp_user->roles );

		}

	 	return $role_slug;

	}



}
WPLib_Users::on_load();
