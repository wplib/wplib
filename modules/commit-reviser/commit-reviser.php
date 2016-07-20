<?php

/**
 * Class WPLib_Commit_Reviser
 *
 * @since 0.10.0
 */
class WPLib_Commit_Reviser extends WPLib_Module_Base {

	/**
	 *
	 */
	static function on_load() {

		if ( is_admin() ) {
			/**
			 * We only need to do this in the admin.
			 * Let's not add even a tiny bit of slow to the front end.
			 */
			self::add_class_action( 'wp_loaded' );

		}

	}

	/**
	 * Inspect the RECENT_COMMIT for both WPLib and WPLib::app_class()
	 * and if changed call 'wplib_commit_revised' hook and update
	 * option in database.
	 */
	static function _wp_loaded() {

		$option_name = $recent_commit = $previous_commit = null;

		$commit_revised = false ;

		foreach ( array( 'WPLib', WPLib::app_class() ) as $class_name ) {

			$recent_commit = self::get_recent_commit( $class_name );

			if ( WPLib::is_development() ) {

				$loaded_commit = self::_maybe_update_class( $class_name );

				if ( $loaded_commit !== $recent_commit ) {

					$recent_commit = $loaded_commit;

				}

			}

			$prefix = strtolower( $class_name );

			$previous_commit = get_option( $option_name = "{$prefix}_recent_commit" );

			if ( $recent_commit !== $previous_commit ) {

				$commit_revised = true;
				break;

			}

		}


		if ( $commit_revised ) {

			update_option( $option_name, $recent_commit );

			do_action( 'wplib_commit_revised', $recent_commit, $previous_commit );


		}

	}

	/**
	 * @return null|string
	 */
	static function recent_commit() {

		return static::get_recent_commit( get_called_class() );

	}

	/**
	 * @param $class_name
	 * @param bool $defined
	 *
	 * @return mixed|null|string
	 */
	static function get_recent_commit( $class_name, &$defined = null ) {

		do {

			$recent_commit = $defined = null;

			if ( ! self::_can_have_recent_commit( $class_name ) ) {
				break;
			}

			$const_ref = "{$class_name}::RECENT_COMMIT";

			if ( $defined = defined( $const_ref ) ) {

				$recent_commit = constant( $const_ref );
				break;

			}

		} while ( false );

		return substr( $recent_commit, 0, 7 );

	}

	/**
	 * Load 7 char abbreviated hash for commit from the system (file or exec).
	 *
	 * Look for a file RECENT_COMMIT if a Git post-commit hook exists and created it
	 * otherwise call Git using shell_exec().
	 *
	 * @param string $class_name
	 *
	 * @return null
	 */
	static function load_recent_commit( $class_name ) {

		$filepath = self::_get_recent_commit_file( $class_name );

		$recent_commit = WPLib::is_found( $filepath )
			? trim( WPLib::get_contents( $filepath ) )
			: null;

		if ( is_null( $recent_commit ) && WPLib::is_development() ) {

			/**
			 * Call `git log` via exec()
			 */
			$root_dir = call_user_func( array( $class_name, 'root_dir' ) );
			do {
				$git_dir_found = false;

				if ( is_dir( "{$root_dir}/.git" ) ) {
					$git_dir_found = true;
					break;
				} else if ( DIRECTORY_SEPARATOR === $root_dir ) {
					/**
					 * This is needed to work for WPLib Box if the App's repo is the project repo.
					 */
					if ( is_dir( $hail_mary_dir = '/vagrant/.git' ) ) {
						$root_dir = $hail_mary_dir;
						$git_dir_found = true;
					}
					break;
				}

				$new_root_dir = dirname( $root_dir );
				if ( $new_root_dir === $root_dir ) {
					$new_root_dir = DIRECTORY_SEPARATOR;
				}
				$root_dir = $new_root_dir;

			} while ( true );

			if ( $git_dir_found ) {
				$command = "cd {$root_dir} && git log -1 --oneline && cd -";
				exec( $command, $output, $return_value );

				if ( 0 === $return_value && isset( $output[0] ) ) {
					/**
					 * If no git repo in dir, $return_value==127 and $output==array()
					 * If no git on system, $return_value==128 and $output==array()
					 * If good, first 7 chars of $output[0] has abbreviated hash for commit
					 */
					$recent_commit = substr( $output[0], 0, 7 );

					file_put_contents( $filepath, $recent_commit );

				}
			}

		}

		return $recent_commit;

	}

	/**
	 * Update the RECENT_COMMIT constant for WPLib or the App Class.
	 *
	 * The update does not affect the current value for RECENT_COMMIT until next page load.
	 *
	 * @param string $class_name
	 */
	private static function _maybe_update_class( $class_name ) {

		do {
			$loaded_commit = null;

			$do_update = true;

			$recent_commit = self::get_recent_commit( $class_name, $defined );

			if ( ! $defined ) {
				break;
			}

			if ( is_null( $recent_commit ) ) {
				break;
			}

			$loaded_commit = self::load_recent_commit( $class_name );

			if ( is_null( $loaded_commit ) ) {
				break;
			}

			if ( $recent_commit !== $loaded_commit ) {
				break;
			}

			$do_update = false;

		} while ( false );

		if ( $do_update ) {

			$reflector = new ReflectionClass( $class_name );

			$source_file = $reflector->getFileName();

			$source_code = WPLib::get_contents( $source_file );

			$source_size = strlen( $source_code );

			if ( preg_match( "#const\s+RECENT_COMMIT#", $source_code ) ) {

				$marker = "const\s+RECENT_COMMIT\s*=\s*'[^']*'\s*;\s*(//.*)?\s*\n";

				$replacer = "const RECENT_COMMIT = '{$loaded_commit}'; $1\n\n";

			} else {

				$marker = "class\s+{$class_name}\s+(extends\s+\w+)?\s*\{\s*\n";

				$replacer = "$0\tconst RECENT_COMMIT = '{$loaded_commit}';\n\n";

			}

			$new_code = preg_replace( "#{$marker}#", $replacer, $source_code );

			if ( $new_code && strlen( $new_code ) >= $source_size ) {

				WPLib::put_contents( $source_file, $new_code );

			}

		}

		return $loaded_commit;

	}

	/**
	 * @param string $class_name
	 *
	 * @return null
	 */
	private static function _get_recent_commit_file( $class_name ) {
		return self::_can_have_recent_commit( $class_name )
			? call_user_func( array( $class_name, 'root_dir' ) ) . '/RECENT_COMMIT'
			: null;

	}

	/**
	 * @param string $class_name
	 *
	 * @return bool
	 */
	private static function _can_have_recent_commit( $class_name ) {

		return 'WPLib' === $class_name || is_subclass_of( $class_name, 'WPLib_App_Base' );

	}

}
WPLib_Commit_Reviser::on_load();
