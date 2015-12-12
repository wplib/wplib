<?php

/**
 * Class WPLib_Runmode
 */
class WPLib_Runmode extends WPlib_Enum {

	const SLUG = 'runmode';

    const __default = self::PRODUCTION;

	const DEVELOPMENT = 1;
	const TESTING     = 2;
	const STAGING     = 3;
	const PRODUCTION  = 4;

}


