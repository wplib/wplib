<?php
/**
 * This file can be included in wp-config-local.php for convenience
 */

if ( ! class_exists( 'WPLib_Enum' ) ) {

	require( __DIR__ . '/enums/WPLib_Enum.php' );
	require( __DIR__ . '/enums/WPLib_Runmode.php' );
	require( __DIR__ . '/enums/WPLib_Stability.php' );

	/**
	 * @param string $enum_class
	 * @param string $setting
	 */
	function wplib_define( $enum_class, $setting ) {

		do {

			$message = false;

			if ( 'WPLib_' !== substr( $enum_class, 0, 6 ) ) {

				$enum_class = "WPLib_{$enum_class}";

			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG || defined( 'WPLIB_CHECK_ENUMS' ) ) {

				if ( ! class_exists( 'WPLib_Enum' ) ) {

					$message = 'The class WPLib_Enum has not been declared yet.';
					break;

				}

				if ( ! class_exists( $enum_class ) ) {

					$message = sprintf( "No Enum class %s.", $enum_class );
					break;

				}

				if ( ! is_subclass_of( $enum_class, 'WPLib_Enum' ) ) {

					$message = sprintf( "Class %s is not a subclass of WPLib_Enum.", $enum_class );
					break;

				}

				if ( is_null( $value = constant( "{$enum_class}::{$setting}" ) ) ) {

					$message = sprintf( "No Constant %s for Enum class %s.", $setting, $enum_class );
					break;

				}

			}

			WPLib_Enum::set_enum( $enum_class, $value );

		} while ( false );

		if ( $message ) {

			WPLib_Enum::_trigger_error( $message );

		}

	}
}
