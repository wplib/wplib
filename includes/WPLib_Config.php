<?php

/**
 * Class WPLib_Config
 *
 * Reciprocal of $IS_PRODUCTION
 *
 * @property bool $IS_DEVELOPMENT
 *
 */
class WPLib_Config extends WPLib_Base {

	/**
	 * Flag to determine if running in "production" mode.
	 * 
	 * Production mode is safest so it is the default.
	 * 
	 * @var bool
	 */
	var $IS_PRODUCTION = true;

	/**
	 * Subdirectory containing the partials path slug to use for this app.
	 *
	 * @var bool
	 */
	var $PARTIALS_SUBDIR = 'partials';

	/**
	 * Flag to determine if errors should be logged by code that choses to log errors.
	 *
	 * @var bool
	 */
	var $LOG_ERRORS = true;

	/**
	 * Setting to true will cause WPLib to extract $GLOBALS before loading template
	 * files. This normally happens in WordPress' /wp-include/template-loader.php 
	 * but WPLib necessarily hijacks that responsibility.
	 *
	 * @var bool
	 */
	var $GLOBALS_IN_TEMPLATES = true;

	/**
	 * Flag to determine if roles should be updated.
	 * 
	 * @todo: Need better docs here
	 *
	 * @var bool
	 */
	var $UPDATE_ROLES = false;

	/**
	 * Setting to allow disabling WPLib-specific object cache.
	 *
	 * @var bool
	 */
	var $BYPASS_CACHE = false;

	/**
	 * Flag to determine if a check should be made on $app_class->PREFIX 
	 * being too long and/or not having a trailing underscore.
	 * 
	 * @var bool
	 */
	var $CHECK_PREFIX_LENGTH = true;

	/**
	 * The filepath of the Config class to use.
	 *
	 * Overrides this the default config class but must be a child class of this class.
	 *
	 * @var string
	 */
	var $ALT_CONFIG = null;

	/**
	 * URL of the website root (w/o a trailing slash)
	 *
	 * @property string|null 
	 */
	var $WWW_URL = null;

	/**
	 * Directory Path of the website root (w/o a trailing slash)
	 *
	 * @property string|null 
	 */
	var $WWW_DIR = null;

	/**
	 * @param stdClass $config
	 */
	function __construct( $config ) {

		if ( isset( $config->IS_DEVELOPMENT ) ) {

			$config->IS_PRODUCTION = ! $config->IS_DEVELOPMENT;
			unset( $config->IS_DEVELOPMENT );

		}

		parent::__construct( (array) $config );

	}

	/**
	 * @param string $property_name
	 *
	 * @return bool|null
	 */
	function __get( $property_name ) {

		switch ( $property_name ) {

			case 'IS_DEVELOPMENT':
				$value = ! $this->IS_PRODUCTION;
				break;

			default:
				$value = isset( $this->extra_args[ $property_name ] )
					? $this->extra_args[ $property_name ]
					: parent::__get( $property_name );

				break;
		}

		return $value;

	}

	/**
	 * @param string $property_name
	 * @param mixed $value
	 */
	function __set( $property_name, $value ) {

		switch ( $property_name ) {

			case 'IS_DEVELOPMENT':
				$this->IS_PRODUCTION = ! $value;
				break;

			default:
				$this->extra_args[ $property_name ] = $value;
				break;
		}


	}

}

