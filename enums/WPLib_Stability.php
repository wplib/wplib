<?php

/**
 * Class WPLib_Stability
 */
class WPLib_Stability extends WPlib_Enum {

    const __default = self::STABLE;

	const DEPRECATED = 0;
	const EXPERIMENTAL = 1;
	const STABLE = 2;
	const LOCKED = 3;

	/**
	 * Call at the start of a method to check stability level.
	 *
	 * Stability levels can be one of:
	 *
	 *      WPLib_Stability::DEPRECATED (0)
	 *      WPLib_Stability::EXPERIMENTAL (1)
	 *      WPLib_Stability::STABLE (2)
	 *      WPLib_Stability::LOCKED (3)
	 *
	 * @example The follow illustrates how to check that the stability
	 *          level is low enough to support EXPERIMENTAL methods.
	 *
	 *      /**                                                                                                             `
	 *       * @stablity 1 - Experimental
	 *       * /
	 *      function foo() {
	 *          self::stability()->check_method( __METHOD__, WPLib_Stability::EXPERIMENTAL );
	 *          // Do the work of foo()
	 *          return;
	 *      }
	 *
	 * @param string $method_name
	 * @param int $stability
	 */
	static function check_method( $method_name, $stability ) {

		if ( intval( (string) WPLib::stability() ) > $stability ) {

			$err_msg = __(
				'The %s method has been marked with a stability of %d ' .
			        'but the current WPLIB_STABILITY requirement is set to %d. ' .
					'You can enable this in wp-config-local.php but BE AWARE that ',
				'wplib'
			);

			switch ( $stability ) {
				case self::DEPRECATED:
					$err_msg .= __(
						'the method has been DEPRECATED and you ' .
						'should really revise your code.', 'wplib'
					);
					break;

				case self::EXPERIMENTAL:
					$err_msg .= __(
						'the method is EXPERIMENTAL so it is likely to ' .
						'change thus forcing you to modify your own code ' .
						'when it changes when you plan to upgrade to a ' .
						'newer version of WPLib.', 'wplib'
					);
					break;

				case self::STABLE:
					$err_msg .= __(
						'the method is STABLE so it is unlikely to change ' .
						'but it has not yet been locked to it is possible ' .
						'this it could change. If so you will need to modify ' .
						'your own code when you plan to upgrade to a newer ' .
						'version of WPLib.', 'wplib'
					);
					break;

				default:
					$err_msg = false;
					break;

			}

			if ( $err_msg ) {

				$err_msg .= __(' To enable add "define( \'WPLIB_STABILITY\', %d );" to your config file.', 'wplib' );

				WPLib::trigger_error( sprintf(
					$err_msg,
					$method_name,
					$stability,
					WPLIB_STABILITY,
					$stability
				));

			}
		}

	}

}
