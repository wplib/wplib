<?php

/**
 * Class WPLib_Runmode
 */
class WPLib_Runmode extends WPlib_Enum {

    const __default = self::PRODUCTION;

    const DEVELOPMENT = 0;
   	const TESTING = 1;
   	const STAGING = 2;
   	const PRODUCTION = 3;

}


