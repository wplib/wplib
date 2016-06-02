<?php
/**
 * Sample wp-config-local.php to use in your  
 * 
 * @var WPLib_Config $wplib
 * 
 * @note:   You do NOT need both $wplib->IS_DEVELOPMENT and $wplib->IS_PRODUCTION.
 *          Either one is okay, choose your preference. Having both will result in 
 *          UNDEFINED behavior if they conflict.
 *
 * @author The WPLib Team
 */
$wplib->IS_DEVELOPMENT = true;          // Normally false; when true allows code to throw liberal errors, etc.
$wplib->IS_PRODUCTION = false;          // Normally true; when true Throw no errors and optimize for performance

$wplib->LOG_ERRORS = false;             // Normally true
$wplib->GLOBALS_IN_TEMPLATES = false;   // Normally true; when true extract()s $GLOBALS for theme template files
$wplib->UPDATE_ROLES = true;            // Normally false; when true this causes roles to updated. @TODO improve docs here           
$wplib->BYPASS_CACHE = true;            // Normally false; when true bypasses WPLib-specific object caching 
$wplib->CHECK_PREFIX_LENGTH = false;    // Normally true; when true checks $app->PREFIX being too long or no trailing underscore.

$wplib->WWW_URL = 'http://example.com/app';       // Normally set automatically, can be set to site's root URL if needed.
$wplib->WWW_DIR = '/var/www/app';                 // Normally set automatically, can be set to site's root path if needed.