<?php

/**
 * Class WPLib_Commit_Reviser
 */
class WPLib_Commit_Reviser extends WPLib_Module_Base {

	const MISSING_COMMIT = '0000000';

	/**
	 *
	 */
	static function on_load() {

		self::add_class_action( 'wp_loaded' );

	}

	/**
	 *
	 */
	static function _wp_loaded() {

		foreach( array( 'WPLib', WPLib::app_class() ) as $class_name ) {

			$latest_commit = self::get_latest_commit( $class_name );

			$prefix = strtolower( $class_name );

			$previous_commit = get_option( $option_name = "{$prefix}_latest_commit" );

			if ( $latest_commit !== $previous_commit ) {

				do_action( 'wplib_commit_revised', $class_name, $latest_commit, $previous_commit );

				update_option( $option_name, $latest_commit );

			}

			if ( WPLib::is_development() ) {

				self::_maybe_update_class( $class_name );

			}

		}

	}

	/**
	 * Update the LATEST_COMMIT constant for WPLib or the App Class.
	 *
	 * The update does not affect the current value for LATEST_COMMIT until next page load.
	 *
	 * @param string $class_name
	 */
	private static function _maybe_update_class( $class_name ) {

		$latest_commit = self::get_latest_commit( $class_name, $defined );

		$not_exists = ! $defined || is_null( $latest_commit );

		$loaded_commit = self::load_latest_commit( $class_name );

		if ( $not_exists || ( ! is_null( $loaded_commit ) && $latest_commit !== $loaded_commit ) ) {

			$reflector = new ReflectionClass( $class_name );

			$source_file = $reflector->getFileName();

			$source_code = file_get_contents( $source_file );

			if ( $not_exists ) {

				$marker = 'const\s+LATEST_COMMIT\s+=\s+"([^"]*)"\s*;\s*\n';

				$replacer = "const LATEST_COMMIT = \"{$latest_commit}\"\n";

			} else {

				$marker = "class\s+{$class_name}\s+(extends\s+\w+)?\s*\{\s*\n";

				$replacer = "$0\\tconst LATEST_COMMIT = \"{$latest_commit}\"\n";

			}

			$source_code = preg_replace( $marker, $replacer, $source_code );

			file_put_contents( $source_file, $source_code );

		}

	}

	/**
	 * @return null|string
	 */
	static function latest_commit() {

		return static::get_latest_commit( get_called_class() );

	}

	/**
	 * @param $class_name
	 * @param bool $defined
	 *
	 * @return mixed|null|string
	 */
	static function get_latest_commit( $class_name, &$defined = null ) {

		do {

			$latest_commit = $defined = null;

			if ( ! self::can_have_latest_commit( $class_name ) ) {
				break;
			}

			$const_ref = "{$class_name}::LATEST_COMMIT";

			if ( $defined = defined( $const_ref ) ) {

				$latest_commit = constant( $const_ref );
				break;

			}

			$latest_commit = self::load_latest_commit( $class_name );

			if ( is_null( $latest_commit ) ) {

				$latest_commit = self::MISSING_COMMIT;
				break;

			}

		} while ( false );

		return substr( $latest_commit, 0, 7 );

	}

	/**
	 * @param string $class_name
	 *
	 * @return null
	 */
	static function load_latest_commit( $class_name ) {

		$filepath = self::get_latest_commit_file( $class_name );

		return is_file( $filepath )
			? file_get_contents( $filepath )
			: null;

	}

	/**
	 * @param string $class_name
	 *
	 * @return null
	 */
	static function get_latest_commit_file( $class_name ) {

		return self::can_have_latest_commit( $class_name )
			? $class_name::get_root_url( 'LATEST_COMMIT' )
			: null;

	}

	/**
	 * @param string $class_name
	 *
	 * @return bool
	 */
	static function can_have_latest_commit( $class_name ) {

		return 'WPLib' === $class_name || is_subclass_of( $class_name, 'WPLib_App_Base' );

	}

}
WPLib_Commit_Reviser::on_load();
