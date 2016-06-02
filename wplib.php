<?php

/**
 * Class WPLib - Core class
 *
 * Plugin Name: WPLib
 * Plugin URI:  http://wordpress.org/plugins/wplib/
 * Description: A Foundation Library for Building WordPress-based Web Applications
 * Version:     0.13.0
 * Author:      The WPLib Team
 * Author URI:  http://wplib.org
 * Text Domain: wplib
 * License:     GPLv2 or later
 *
 * Copyright 2015 NewClarity Consulting LLC <wplib@newclarity.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @mixin WPLib_Theme
 * @mixin WPLib_Posts
 * @mixin WPLib_Terms
 * @mixin WPLib_Users
 * @mixin WPLib_Roles
 * @mixin _WPLib_Html_Helpers
 * @mixin _WPLib_WP_Helpers
 *
 * @future Utility Modules: https://github.com/wplib/wplib/issues/6
 *
 * @future PHPDoc - https://github.com/wplib/wplib/issues/8
 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11027141
 *
 */
class WPLib {

	const RECENT_COMMIT = 'cb47c6b'; 

	const PREFIX = 'wplib_';
	const SHORT_PREFIX = 'wplib_';

    /**
	 * Properties to be serialized into /autoload.php
	 * 
	 * @var object {
     * 		@type string[] $app_initialized Array of bool keyed by class name indicating App initialization
     * 		@type string[] $class_files Array of Class filenames keyed by class name
     * 		@type string[] $app_classes Array of App Class Names including 'WPLib' keyed by index, index=0 is site App.
     * 		@type string[] $app_files Array of App/Module filenames keyed by module slug keyed by App Class
     * 		@type string[] $module_classes Array of Module slugs keyed by class name
     * 		@type string[] $helpers Array of Class Names keyed by class name they help
     * 		@type string[] $helped_classes Array of Class Names keyed by class name they help
     * 		@type string[] $partials Array of Partial files keyed by ...  @TODO finish this
     * 		@type string $theme_file Theme file name
     * 		@type string $theme_class Theme Class Name
	 * }
	 */
	private static $_;

	/**
	 * @var bool|string Flag to hold filename currently loading. Used by _shutdown() to report if a file failed to load.
	 */
	private static $_file_loading = false;

	/**
	 * The count of classes returned by get_declared_classes() upon first running WPLib.
	 *
	 * @var int
	 */
	private static $_pre_wplib_class_count = 0;

	/**
	 * @var WPLib_Theme_Base|bool
	 */
	private static $_theme = false;

	/**
	 * @var array Property to use during development that collects registered helper classes and helper methods
	 *            awaiting fixup and assignment of "compiled" data stored in self::$_->helper_methods.
	 */
	private static $_helpers = array();

    /**
     * Location of file to optimize by bypassing directory scanning, etc.
     *
     * @var string
     */
    private static $_symbols_filepath;

	/**
	 * @var bool Get's set if doing XMLRPC.
	 */
	private static $_doing_xmlrpc = false;

	/**
	 *
	 */
    static function on_load() {

	    /**
		 * @var bool Flag to ensure this method is only ever called once.
		 */
		static $done = false;

		if ( $done ) {

			$err_msg = __( 'The %s::on_load() method should not call its parent class, e.g. remove parent::on_load().', 'wplib' );

			self::trigger_error( sprintf( $err_msg, get_called_class() ) );

		}

	    /**
	     * Set a marker to ignore classes declared before this class.
	     */
	    self::$_pre_wplib_class_count = count( get_declared_classes() ) - 1;

	    spl_autoload_register( array( __CLASS__, '_autoloader' ), true, true );

	    /**
	     * Add Action and Filter Hooks
	     */
	    self::add_class_action( 'xmlrpc_call' );
        self::add_class_action( 'plugins_loaded', 9 );
	    self::add_class_action( 'setup_theme', 9 );
        self::add_class_action( 'shutdown' );


	    /**
		 * Intialize object instance used to store all the discovered data
         * including module files to always autoload and class files to
         * autoload on demand. The value of these will be var_export()ed
         * to the value of self::$_symbols_filepath on shutdown, to be
         * loaded in production mode.
		 *
		 * In the `shutdown` hook method _shutdown(), the value of WPLib::$_
		 * is stored to {WP_CONTENT_DIR}/wplib-symbols.php using var_export()
		 * while in development mode so it can be loaded while in production
		 * mode and not require the time-consuming inspections required to
		 * "compile" this information on each page load.
		 */
		self::$_ = (object) array_fill_keys( array(
            'app_initialized',
            'class_files',
			'app_classes',
            'app_files',
            'module_classes',
			'helper_methods',
			'theme_file',
            'partials',
		), array() );

	    self::$_->theme_file = null;
	    self::$_->theme_class = null;

    }

	/**
	 * @param string $class_name
	 */
	static function _autoloader( $class_name ) {

		if ( isset( self::$_->class_files[ $class_name ] ) ) {

			$php_file = self::make_filepath_absolute( self::$_->class_files[ $class_name ] );

			require_once( $php_file );

		}

	}

	/**
	 * Convert relative file paths to absolute file paths.
	 *
	 * Recognize a leading tilde as a relative path, replace with www dir.
	 *
	 * @param string $filepath
	 * @return string
	 */
	static function make_filepath_absolute( $filepath ) {

		if ( '~' === $filepath[0] ) {
			global $wplib;

			$filepath = $wplib->WWW_DIR . substr( $filepath, 1 );

		}

		return $filepath;

	}

	/**
	 * Convert an full filepath starting with www dir to one where www dir is replaced with '~'.
	 *
	 * @param string $filepath
	 *
	 * @return string
	 */
	static function make_filepath_relative( $filepath ) {
		global $wplib;

		return preg_replace( '#^' . preg_quote( $wplib->WWW_DIR ) . '(.*)$#', "~$1", $filepath );

	}

	/**
	 * Load all necessary files. This loads the main class for each module.
	 * It also invokes the `::on_load()` method for each class, if one exists.
	 *
	 */
	static function _plugins_loaded_9() {

		self::_set_app_classes();

		self::_initialize_app_classes();

		self::_load_modules();

		self::_fixup_helpers();

	}

	/**
	 * Load all necessary files. This is needed for Multisites where the only option is theme code.
	 */
	static function _setup_theme_9() {

		self::load_theme_class();

	}

	/**
	 * Initialize the App
	 */
	static function _initialize_app_classes() {

		foreach( self::$_->app_classes as $app_class ) {

			call_user_func( array( $app_class, 'initialize' ) );

		}

	}

	/**
	 * Sets the one (1) WPLib global variable, $wplib.
	 *
	 * Used for configuration in wp-config-local.php as stdClass. Converted to WPLib_Config in _set_wplib().
	 */
	static function _set_wplib() {

		global $wplib;

		$default_config_class = 'WPLib_Config';
		
		do {

			$config_filepath = self::get_root_dir( "/includes/{$default_config_class}.php" );

			if ( is_null( $wplib->ALT_CONFIG ) ) {

				$wplib->ALT_CONFIG = null;

				break;

			}

			if ( ! is_file( $wplib->ALT_CONFIG ) ) {

				self::trigger_error( '$wplib->ALT_CONFIG specifies a missing file.', 'wplib' );
				break;

			}

			$config_filepath = $wplib->ALT_CONFIG;

		} while ( false );

		do {

			/**
			 * Load WPLib_Base which WPLib_Config extends
			 */
			require ( dirname( $config_filepath ) . '/WPLib_Base.php' );

			/**
			 * Now load WPLib_Config which may be our config class or our config class may extend it.
			 */
			require ( $config_filepath );

			if ( is_null( $wplib->ALT_CONFIG ) ) {
				$config_class = $default_config_class;
				break;
			}

			$class_count = count( get_declared_classes() );

			require ( $config_filepath );

			$new_class_count = count( $declared_classes = get_declared_classes() );

			if ( $class_count === $new_class_count ) {

				$err_msg = __( 'File %s does not declare any class; should declare a class extending %s.', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $wplib->ALT_CONFIG, $default_config_class ) );
				break;

			}

			if ( $class_count + 1 < $new_class_count ) {

				$err_msg = __( 'File %s does declares more than one class; should only declare one child of %s.', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $wplib->ALT_CONFIG, $default_config_class ) );
				break;

			}

			$config_class = array_pop( $declared_classes );

			if ( ! class_exists( $config_class ) ) {

				$err_msg = __( 'Class %s declared from %s is not a child class of %s.', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $config_class, $wplib->ALT_CONFIG, $default_config_class ) );
				$config_class = $default_config_class;
				break;

			}


		} while ( false );

		$wplib = new $config_class( isset( $wplib ) ? $wplib : array() );

	}

	/**
	 * Set value of $wplib->WWW_DIR and $wplib->WWW_URL.
	 *
	 * WPLib will figure it out, but set these if you need to override (for some reason.)
	 *
	 */
	static function _set_www() {
		/**
		 * @var WPLib_Config
		 */
		global $wplib;

		if ( ! isset( $wplib->WWW_DIR ) ) {

			$content_path = preg_replace( '#^https?://[^/]+(.*)$#', '$1', WP_CONTENT_URL );
			$wplib->WWW_DIR = substr( WP_CONTENT_DIR, 0, - strlen( $content_path ) );

		}

		if ( ! isset( $wplib->WWW_URL ) ) {

			$wplib->WWW_URL = defined( 'WP_HOME' )
				? WP_HOME
				: home_url();

		}

		$wplib->WWW_DIR = rtrim( $wplib->WWW_DIR, '/' );
		$wplib->WWW_URL = rtrim( $wplib->WWW_URL, '/' );

	}

	/**
	 * @return array|null
	 */
	static function _set_app_classes() {

		if ( empty( self::$_->app_classes ) ) {

			self::$_->app_classes = array();

			foreach( array_slice( get_declared_classes(), self::$_pre_wplib_class_count ) as $app_class ) {

				if ( ! is_subclass_of( $app_class, 'WPLib_App_Base' ) ) {

					continue;

				}

				self::$_->app_classes[] = $app_class;

			}

			usort( self::$_->app_classes, function ( $class1, $class2 ) {

				if ( is_subclass_of( $class1, $class2 ) ) {

					$result = -1;

				} else if ( is_subclass_of( $class2, $class1 )  ) {

					$result = +1;

				} else {

					self::trigger_error( sprintf( "\nNon-related app classes loaded: %s and %s.\n", $class1, $class2 ) );

					$result = 0;
				}

				return $result;

			} );

		}

	}

	/**
	 * Return the theme class name used by this site.
	 *
	 * @return string
	 */
	static function theme_class() {

		return self::$_->theme_class;

	}

	/**
	 * Set the classes used by this site.
	 *
	 * Can be called in a theme's functions.php file or will automatically be called in 'after_setup_theme' priority 9.
	 *
	 * @TODO Set hook to clear self::$_->theme_class and run again on changing theme.
	 */
	static function load_theme_class() {

		global $wplib;

		static $loaded = false;

		if ( ! $loaded ) {

			do {

				if ( ! empty( self::$_->theme_file ) ) {

					require( self::$_->theme_file );
					break;

				}

				$class_file = get_stylesheet_directory() . '/' . get_stylesheet() . '-theme.php';

				if ( ! is_file( $class_file ) && $wplib->IS_DEVELOPMENT ) {

					self::trigger_error( sprintf( 'No theme class file found at %s.', $class_file ) );
					break;

				}

				self::$_->theme_file = $class_file;

				require( self::$_->theme_file );

				$declared_classes = get_declared_classes();

				$theme_class = array_pop( $declared_classes );

				self::$_->class_files[ $theme_class ] = self::make_filepath_relative( $class_file );

				if ( ! is_subclass_of( $theme_class, 'WPLib_Theme_Base' ) && $wplib->IS_DEVELOPMENT ) {

					self::trigger_error( sprintf( 'Theme class %s is not a child of WPLib_Theme_Base.', $theme_class ) );

					break;

				}

				self::$_->theme_class = $theme_class;


			} while ( false );

			if ( self::class_declares_method( self::$_->theme_class, 'on_load' ) ) {

				$theme_class::on_load();

			}

			$loaded = true;

		}

	}

	/**
	 * Return the list of classes declared after WPLib first loads.
	 * @return array
	 */
	static function site_classes() {

		return array_keys( self::$_->class_files );

	}

	/**
	 * Map all  module files -- main, and includes for autoloading --
	 * and register all the partials that can be used across modules.
	 *
	 * @TODO Add check to ensure this is only run for WPLib or classes extending WPLib_App_Base
	 *
	 */
	static function initialize() {

		static $done_once = false, $loaded = array();

		global $wplib;

		self::$_symbols_filepath = WP_CONTENT_DIR . '/wplib-symbols.php';

		if ( ! $done_once ) {

			/**
			 * Sets the one (1) WPLib global variable, $wplib.
			 *
			 * Used for configuration in wp-config-local.php as stdClass. Converted to WPLib_Config in _set_wplib().
			 */
			self::_set_wplib();

			/**
			 * Sets the www dir which is `/var/www` when using WPLib Box.
			 *
			 * Used by make_filepath_relative() and make_filepath_absolute() as well as potentially others.
			 */
			self::_set_www();


			if ( $wplib->IS_PRODUCTION ) {

				if ( $symbols_data = self::cache_get( 'symbols_data', 'wplib' ) ) {

					self::$_ = $symbols_data;

				} else if ( is_file(self::$_symbols_filepath ) ) {

					self::$_ = require(self::$_symbols_filepath);

				}

			}

		}

		$done_once = true;

		if ( empty( $loaded[ $app_class = get_called_class() ] ) ) {

			if ( self::class_declares_method( $app_class, 'on_load' ) ) {

				call_user_func( array( $app_class, 'on_load' ) );

			}

			$loaded[ $app_class ] = true;

		}

		if ( empty( self::$_->app_initialized[ $app_class = get_called_class() ] ) ) {

			static::_map_app_data();

			static::_register_partials();

			self::$_->app_initialized[ $app_class ] = true;

		}

	}

	/**
	 * Loads the main module classes on every page load.
	 *
	 * Optionally call an on_load() method.
	 *
	 * @TODO Generate an error stack in the case this recursive logic fails to find the right class.
	 *
	 */
	private static function _load_modules() {

		static $loaded = array();

		$loader_callables = array();

		foreach(self::$_->app_files as $app_class => $app_files ) {

			if ( ! isset( $loaded[ $app_class ] ) ) {

				foreach ($app_files as $module_slug => $module_file) {

					$module_file = self::make_filepath_absolute( $module_file );

					self::$_file_loading = $module_file;

					require($module_file);

					self::$_file_loading = false;

					$declared_classes = get_declared_classes();

					$module_class = array_pop( $declared_classes );

					self::$_->class_files[ $module_class ] = self::make_filepath_relative( $module_file );

					if ( ! ( is_subclass_of( $module_class, 'WPLib_Module_Base' ) || 'WPLib_Module_Base' === $module_class ) ) {

						self::trigger_error( sprintf( "%s must extend from WPLib_Module_Base.", $module_class ) );

					}

					self::$_->module_classes[ $app_class ][ $module_slug ] = $module_class;

					if (self::class_declares_method($module_class, 'on_load')) {

						$loader_callables[] = array($module_class, 'on_load');
					}

				}

				$loaded[$app_class] = true;

			}

		}

		/**
		 * Once all Modules are loaded then call each of their `::on_load()` methods
		 */
		foreach( $loader_callables as $loader_callable ) {

			call_user_func( $loader_callable );

		}

	}

	/**
	 * Maps all App Main Module Files and all class files in `/includes/` and Module `/includes/` directories.
	 *
	 */
	private static function _map_app_data() {

		$app_class = get_called_class();

		$root_dir = static::root_dir();

		/**
		 * Add in this App's class filepath
		 */
		self::$_->class_files[ $app_class ] = self::make_filepath_relative( self::get_class_filepath( $app_class ) );

		/**
		 * Add the include files for this App to the autoload symbols.
		 */
		static::_find_include_files( "{$root_dir}/includes" );

		/**
		 * Add the module main files for this App to the list of module files.
		 */
		self::$_->app_files[ $app_class ] = array();

		foreach( glob( "{$root_dir}/modules/*" ) as $module_dir ) {

			/**
			 * Extract the Module slug from the Module Path
			 */
			$module_slug = basename( $module_dir );

			/**
			 * Replace $wplib->WWW_DIR of the currently running machine with '~'
			 */
			$relative_dir = self::make_filepath_relative( $module_dir );

			/**
			 * Add the module's main file to the list of mustload files.
			 */
			self::$_->app_files[ $app_class ][ $module_slug ] = "{$relative_dir}/{$module_slug}.php";

			/**
			 * Add the include files for this Module to the autoload symbols.
			 */
			static::_find_include_files( "{$module_dir}/includes" );

		}

		/**
		 * Initialize the Helper methods array while we are at it.
		 */
		self::$_->helper_methods[ $app_class ] = array();

	}

	/**
	 * @param string $includes_dir
	 */
	private static function _find_include_files( $includes_dir ) {

		foreach( glob( "{$includes_dir}/*.php" ) as $filepath ) {

			$class_name = basename( $filepath, '.php' );

			self::$_->class_files[ $class_name ] = self::make_filepath_relative( $filepath );

		}
	}

	/**
	 * Throw error if site failed to load because of a module failing to load.
	 */
	static function _shutdown() {

		global $wplib;

		if ( $wplib->IS_DEVELOPMENT ) {

			if (self::$_file_loading) {

				$message = __('File failed to load: %s.', 'wplib');
				self::trigger_error(sprintf($message, self::$_file_loading), E_USER_ERROR, true);

			} else {

				$symbols_data = is_file( self::$_symbols_filepath )
					? require( self::$_symbols_filepath )
					: false;

				if ( ! $symbols_data || serialize( self::$_ ) !== serialize( $symbols_data ) ) {

					$symbols_php = "<?" . "php\n\n// Auto-generated by WPLib. DO NOT MODIFY.\n\nreturn " . var_export(self::$_, true) . ';';

					/**
					 * Get rid of stupid call to __set_state() for stdClass.
					 * What were the PHP internals guys smoking anyway?!?
					 * @see: http://stackoverflow.com/questions/16612668/php-var-export-object-forgot-to-serialize-before-storing
					 */
					$symbols_php = str_replace( 'stdClass::__set_state', '(object)', $symbols_php );

					file_put_contents(self::$_symbols_filepath, $symbols_php );

				}

				self::cache_set( 'symbols_data', self::$_, 'wplib', 60*10 );

			}

		}

	}

	/**
	 * Force loading of all classes if needed to find all classes with a specific constant.
	 */
	static function autoload_all_classes() {

		static $classes_loaded = false;

		if ( ! $classes_loaded ) {

			foreach (array_keys( self::$_->class_files ) as $autoload_class ) {

				self::_autoloader( $autoload_class );

			}

		}

	}

	/**
	 * @param string $action
	 * @param int $priority
	 */
	static function add_class_action( $action, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$action}" ) . ( 10 !== intval( $priority ) ? "_{$priority}" : '' );
		add_action( $action, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $filter
	 * @param int $priority
	 */
	static function add_class_filter( $filter, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$filter}" ) . ( 10 !== intval( $priority ) ? "_{$priority}" : '' );
		add_filter( $filter, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $action
	 * @param int $priority
	 */
	static function remove_class_action( $action, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$action}" ) . ( 10 !== intval( $priority ) ? "_{$priority}" : '' );
		remove_action( $action, array( get_called_class(), $hook ), $priority );

	}

	/**
	 * @param string $filter
	 * @param int $priority
	 */
	static function remove_class_filter( $filter, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$filter}" ) . ( 10 !== intval( $priority ) ? "_{$priority}" : '' );
		remove_filter( $filter, array( get_called_class(), $hook ), $priority );

	}

	/**
	 * Register a helper class to the specified class.
	 *
	 * @param string $helper_class The name of the helper class.
	 * @param array { 
	 *      @type string $helped_class  Name of the class adding the helper. Defaults to called class.
	 *      @type array $method_names Array of method names to contribute to the $helped_class.
	 * }
	 *
	 */
	static function register_helper( $helper_class, $args = array() ) {
		
		if ( is_string( $args ) ) {

			$err_msg = __( '%s::register_helper() method signature has changed since 0.13.0. The 2nd parameter ' .
					'now expects to be an array of $args. Change your call to one of these formats instead: ' .
					"%s::register_helper( '%s', 'helped_class=%s' ); or " .
					"%s::register_helper( '%s', array( 'helped_class' => '%s' );",
				'wplib' );

			self::trigger_error( sprintf( $err_msg,
				get_called_class(),
				get_called_class(),
				$helper_class,
				$args,
				get_called_class(),
				$helper_class,
				$args
			));

		} 
		
		$args = wp_parse_args( $args, array(

			'helped_class' => get_called_class(),
			'method_names' => null,

		));

		self::$_helpers[ $args[ 'helped_class' ] ][ $helper_class ] = $args[ 'method_names' ];

	}

	/**
	 * Delegate calls to other classes.
	 * This allows us to document a single "API" for WPLib yet
	 * structure the code more conveniently in multiple class files.
	 *
	 * @example  self::_call_helper( __CLASS__, 'register_item', array( $item ) );
	 *
	 * @param string $helped_class  Name of class that is calling the helper
	 * @param string $helper_method Name of the helper method
	 * @param array  $args          Arguments to pass to the helper method
	 *
	 * @return mixed|null
	 */
	static function _call_helper( $helped_class, $helper_method, $args ) {

		if ( empty( self::$_->helper_methods[ $helped_class ][ $helper_method ] ) ) {

			/*
			 * Oops. No helper was found after all that.  Output an error message.
			 */

			$err_msg = sprintf(
				__( 'ERROR: There is no helper method %s() for class %s. ', 'wplib' ),
				$helper_method,
				$helped_class
			);

			self::trigger_error( $err_msg, E_USER_ERROR );

			$value = null;

		} else {

			$value = call_user_func_array( self::$_->helper_methods[ $helped_class ][ $helper_method ], $args );
		}

		return $value;

	}

	/**
	 * Fixup the Helper Classes
	 */
	static function _fixup_helpers() {

		static $IGNORABLE_METHODS = array(
			'on_load',
			'make_new_item',
			'instance_class',
			'get_list',
		);

		$IGNORABLE_METHODS = apply_filters( 'wplib_ignorable_helper_methods', $IGNORABLE_METHODS );

		$app_classes = array_keys( self::$_->helper_methods );

		/**
		 * First sort to ensure parent classes come first
		 */
		usort( $app_classes, function ( $helped_class1, $helped_class2 ) {

			return is_subclass_of( $helped_class2, $helped_class1 ) ? -1 : 1;

		});

		foreach( $app_classes as $helped_class ) {

			$helper_classes = self::$_helpers[ $helped_class ];

			$class_methods = array();

			$bypass_checks = false;

			foreach( $helper_classes as $helper_class => $helper_methods ) {

				if ( is_null( $helper_methods )  ) {

					$helper_methods = self::get_public_static_methods( $helper_class );

				} else if ( ! is_array( $helper_methods ) ) {

					$err_msg = _( 'Helper methods passed to register_helper() is not an array for class %s helped by class %s.', 'wplib' );

					self::trigger_error( sprintf( $err_msg, $helped_class, $helper_class ) );

					continue;

				} else {

					/**
					 * The developer provided this list, let it go unchecked...
					 */
					$bypass_checks = true;

				}

				/**
				 * For each Helper Method check to see if we want to use it.
				 * If we want to use it check to see if a duplicate.
				 * If not a duplicate, add to list of helpers.
				 */
				foreach( $helper_methods as $helper_method ) {

					if ( ! $bypass_checks ) {

						if ( '_' === $helper_method[0] || in_array( $helper_method, $IGNORABLE_METHODS ) ) {

							continue;

						} else if ( ! self::class_declares_method( $helper_class, $helper_method ) ) {
							/**
							 * Don't add in inherited methods because there will be too many duplicates.
							 * After this loop then just merge in the parent's helpers.
							 */

							continue;
						}

					}

					if ( isset( $class_methods[ $helper_method ] ) ) {

						$err_msg = __(
							'%s already has a helper method %s when %s attempted to provide another. ' .
							'To correct this provide an explicit list of array(\'method_names\' => array(...)) '.
							'as the 2nd parameter to %s::register_helper() for one of both of these classes ' .
							'while being sure to omit %s from one of the two lists.', 'wplib' );

						self::trigger_error( sprintf( $err_msg,
							$helped_class,
							$class_methods[ $helper_method ],
							$helped_class,
							$helper_method
						));

						continue;

					}

					$class_methods[ $helper_method ] = "{$helper_class}::{$helper_method}";

				}

			}

			$current_class = $helped_class;

			while ( $parent_class = get_parent_class( $current_class ) ) {

				$current_class = $parent_class;

				/**
				 * If there is a parent class check to see if it has helper methods.
				 */
				if ( ! empty( self::$_->helper_methods[ $current_class ] ) ) {

					/**
					 * Merge in parent class helper methods but do not override existing ones in $class_methods.
					 */
					$class_methods = array_merge( self::$_->helper_methods[ $current_class ], $class_methods );

					/**
					 * Don't need to get grandparent class because parent class already got their helpers.
					 *
					 */
					break;

				}

			}

			self::$_->helper_methods[ $helped_class ] = $class_methods;
		}

		self::$_helpers = null;  // Free up the memory...
	}

	/**
	 * Retrurn array of public static methods given a class name.
	 *
	 * @param string $class_name
	 *
	 * @return string[]
	 */
	static function get_public_static_methods( $class_name ) {

		$reflector = new ReflectionClass( $class_name );

		$methods = $reflector->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC );

		$methods = array_map( function( $method ) { return $method->name; }, $methods );
		
		return $methods;

	}

	/**
	 * Return the root directory of the Lib/App/Site/Module/Theme class.
	 *
	 * @return string
	 */
	static function root_dir() {

		return static::get_root_dir( '', get_called_class() );

	}

	/**
	 * Return the root URL of the Lib/App/Site/Module/Theme class.
	 *
	 * @return string
	 */
	static function root_url() {

		return static::get_root_url( '', get_called_class() );

	}

	/**
	 * Return the root directory of the Lib/App/Site/Module/Theme class for a given class name.
	 *
	 * @param string $filepath Name of path to append to root dir.
	 * @param bool|string $class_name Name of class to return the source dir.
	 *
	 * @return string
	 */
	static function get_root_dir( $filepath, $class_name = false ) {

		if ( ! $class_name ) {

			$class_name = get_called_class();
		}

		return realpath( dirname( self::get_class_filepath( $class_name ) ) . '/' . ltrim( $filepath, '/' ) );

	}

	/**
	 * Return the filepath given a class name.
	 *
	 * @param bool|string $class_name Name of class to return the source dir.
	 *
	 * @return string
	 */
	static function get_class_filepath( $class_name ) {

		$reflector = new ReflectionClass( $class_name );

		return $reflector->getFileName();

	}

	/**
	 * Get the root URL for a given Lib/Site/App/Module/Theme.
	 *
	 * @param string $filepath Name of path to append to root URL.
	 * @param bool|string $class_name Name of class to return the root dir.
	 *
	 * @return string
	 *
	 * @TODO Calculate root_urls based on self::$_->class_files
	 *
	 */
	static function get_root_url( $filepath, $class_name = false ) {
		
		global $wplib;
		
		static $root_urls = array();

		if ( ! $class_name ) {

			$class_name = get_called_class();

		}

		if ( ! isset( $root_urls[ $class_name ] ) ) {

			if ( empty( self::$_->class_files[ $class_name ] ) && $wplib->IS_DEVELOPMENT ) {

				$err_msg = __( '%s not found in WPLib::$_->class_files.', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $class_name ) );

			}
			
			$root_urls[ $class_name ] = rtrim( dirname( $wplib->WWW_URL . substr( self::$_->class_files[ $class_name ], 1 ) ), '/' );

		}

		$filepath = '/' . ltrim( $filepath, '/' );

		return self::get_real_url( $root_urls[ $class_name ] . $filepath );

	}

	/**
	 * Like realpath() but for URLs
	 * @param string $url
	 * @return string
	 */
	static function get_real_url( $url ) {

		foreach ( array_keys( $url = explode( '/', $url ), '..' ) AS $keypos => $key) {
			array_splice( $url, $key - ($keypos * 2 + 1 ), 2 );
		}

		return str_replace( './', '', implode('/', $url ) );
	}

	/**
	 * Echo the asset path
	 *
	 * @param string $asset_path
	 * @param bool|string $class_name Name of class to return the root dir.
	 *
	 * @return string
	 */
	static function the_asset_url( $asset_path, $class_name = false ) {

		echo esc_url( static::get_asset_url( $asset_path, $class_name ) );

	}

	/**
	 * Return the simple asset path
	 *
	 * @return string
	 */
	static function assets_url() {

		return rtrim( static::get_asset_url( '' ), '/' );

	}

	/**
	 * Return the asset path
	 *
	 * @param string $asset_path
	 * @param bool|string $class_name Name of class to return the root dir.
	 *
	 * @return string
	 */
	static function get_asset_url( $asset_path, $class_name = false ) {

		if ( ! $class_name ) {

			$class_name = get_called_class();

		}

		$asset_path = ltrim( $asset_path, '/' );

		return static::get_root_url( "/assets/{$asset_path}", $class_name );

	}

	/**
	 * Given a Module slug, return the directory for the module
	 *
	 * @param string $module_slug
	 * @param  string|bool $app
	 *
	 * @return string|null
	 *
	 * @TODO Test this
	 */
	static function get_module_dir( $module_slug, $app = false ) {

		$app_class = static::get_app_class( $app );

		return ! empty( self::$_->app_files[ $app_class ][ $module_slug ] )
			? dirname( self::$_->app_files[ $app_class ][ $module_slug ] )
			: null;

	}

	/**
	 * Returns the App classes for this site.
	 *
	 * Should have a minimum of 1 class and the current App class will be at index [0].
	 *
	 * If App is a child of another App, then 3 classes like this:
	 *
	 * 		$app_classes = array(
	 * 			0 => 'Child_App',
	 * 			1 => 'Parent_of_Child_App',
	 *
	 * @return array|null
	 */
	static function app_classes() {

		return $_->app_classes;

	}

	/**
	 * Returns the site's App class.
	 *
	 * @return string|null
	 */
	static function app_class() {

		return count( self::$_->app_classes )
			? self::$_->app_classes[0]
			: null;

	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return mixed
	 */
	static function cache_get( $key, $group = '' ) {

		global $wplib;

		if ( $wplib->IS_DEVELOPMENT && ! is_string( $key ) && ! is_int( $key ) ) {

			static::trigger_error( __( 'Cache key is not string or numeric.', 'wplib' ) );

		}

		$cache = $wplib->BYPASS_CACHE
			? wp_cache_get( $key, static::_filter_group( $group ) )
			: null;

		return $cache;

	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param string $group
	 * @param int $expire
	 */
	static function cache_set( $key, $value, $group = '', $expire = 0 ) {

		wp_cache_set( $key, $value, static::_filter_group( $group ), $expire );

	}

	/**
	 * @param string $key
	 * @param string $group
	 */
	static function cache_delete( $key, $group = '' ) {

		if ( self::cache_exists( $key, $group ) ) {

			wp_cache_delete( $key, static::_filter_group( $group ) );

		}

	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return bool
	 */
	static function cache_exists( $key, $group = '' ) {

		return false !== self::cache_get( $key, $group );

	}

	/**
	 * @param $group
	 *
	 * @return string
	 */
	private static function _filter_group( $group ) {

		if ( $group ) {

			$group = static::SHORT_PREFIX . $group;

		} else {

			$group = rtrim( static::PREFIX, '_' );

		}

		return $group;
	}

	/**
	 * @param string $method
	 * @param array  $args
	 *
	 * @return mixed
	 */
	static function __callStatic( $method, $args ) {

		return self::_call_helper( get_called_class(), $method, $args );

	}

	/**
	 * Return a class constant for the called class.
	 *
	 * @param string      $constant_name
	 * @param string|bool|object $class_name
	 * @param bool $try_parent
	 *
	 * @return mixed|null
	 */
	static function get_constant( $constant_name, $class_name = false, $try_parent = true ) {

		if ( ! $class_name ) {

			$class_name = get_called_class();

		}

		if ( is_object( $class_name ) ) {

			$class_name = get_class( $class_name );

		}

		if ( defined( $constant_ref = "{$class_name}::{$constant_name}" ) ) {

			$value = constant( $constant_ref );

		} else if ( $try_parent && $parent_class = get_parent_class( $class_name ) ) {

			$value = self::get_constant( $constant_name, $parent_class );

		} else {

			$value = null;

		}

		return $value;


	}


	/**
	 * Register all partials for WPLib, an App or a module.
	 *
	 * @return array
	 */
	static function _register_partials() {

		global $wplib;

		$dir_spec = static::partials_dir() . '/*.php';

		$index = $wplib->IS_DEVELOPMENT
			? $dir_spec
			: md5( $dir_spec );

		if ( ! ( $partials = self::cache_get( $cache_key = "partials[{$index}]" ) ) ) {

			/*
			 * Scan the directory for all partial files.
			 *
			 * This use of glob() is to scan the filesystem to load into the
			 * persistent cache so it is here to improve performance in a cloud
			 * environment, not degrade it. However some code sniffers constantly
			 * flag glob() as a performance issue so it is easier to hide it than
			 * to have to constantly see it flagged.
			 *
			 * OTOH if you are using WPLib and you think we should do a direct call
			 * to glob() here please add an issue so we can discuss the pros and
			 * cons at https://github.com/wplib/wplib/issues
			 */

			self::cache_set( $cache_key, $partials = glob( $dir_spec ) );

		}

		if ( is_array( $partials ) ) {

			foreach ( $partials as $partial ) {

				/*
				 * Calculates the partial name to register.
				 *
				 * This use of basename() is to determine the partial filename so it
				 * can be registered and stored in persistent cache. However some
				 * code sniffers flag this as being part of the filesystem which is
				 * ironic since our use of this never touches the file system.
				 * Consequently it is easier to hide it than to have to constantly
				 * see it flagged.
				 *
				 * OTOH if you are using WPLib and you think we should do a direct call
				 * to basename() here please add an issue so we can discuss the pros and
				 * cons at https://github.com/wplib/wplib/issues
				 */

				static::register_partial( basename( $partial, '.php' ) );

			}

		}

	}

	/**
	 * Register a partial
	 *
	 * @param string $partial
	 * @param string|bool $app_class
	 */
	static function register_partial( $partial, $app_class = false ) {

		if ( ! $app_class ) {
			$app_class = get_called_class();
		}

		$module_slug = self::get_module_slug( $app_class );

		self::$_->partials[ $module_slug ][ $partial ] =
			self::make_filepath_relative( static::get_partials_dir( $partial ) );

	}

	/**
	 * Return the partial filepath for the passed $partial for the called class.
	 *
	 * @param string $partial
	 *
	 * @return string
	 */
	static function get_partials_dir( $partial ) {

		/*
		 * Calculates the partial directory for other code to cache.
		 *
		 * This use of basename() sis to determine the filename so it
		 * can be registered and stored in persistent cache. However some
		 * code sniffers flag this as being part of the filesystem which is
		 * ironic since our use of this never touches the file system.
		 */

		return static::partials_dir() . '/' . basename( preg_replace('#[^a-zA-Z0-9-_\\/.]#','', $partial ). '.php' ) . '.php';

	}

	/**
	 * @param string $partial_slug
	 * @param array|string $_partial_vars
	 * @param WPLib_Item_Base|object $item
	 *
	 * @see  self::the_partial()
	 *
	 * @return string
	 */
	static function get_partial_html($partial_slug, $_partial_vars = array(), $item = null ) {

		ob_start();
		static::the_partial_html( $partial_slug, $_partial_vars, $item );
		$output = ob_get_clean();
		return $output;

	}

	/**
	 * @param string $partial_slug
	 * @param array|string $_partial_vars
	 * @param WPLib_Item_Base|object $item
	 *
	 * @note This is called via an instance as well as
	 *       If this becomes deprecated we can prefix with an '_' and then
	 *       use __call() and __callStatic() to allow it to be invoked.
	 * @see  http://stackoverflow.com/a/7983863/102699
	 */
	static function the_partial_html( $partial_slug, $_partial_vars = array(), $item = null ) {

		global $wplib;


		/*
		 * Calculate the md5 value for caching this partial filename
		 */
		$_partial_index = $wplib->IS_PRODUCTION 
			? md5( serialize( array( $partial_slug, $_partial_vars, get_class( $item ) ) ) )
			: "[{$partial_slug}" . get_class( $item ) . '][' . serialize( $_partial_vars ) . ']';

		if ( ! ( $partial = self::cache_get( $_cache_key = "partial_file[{$_partial_index}]" ) ) ) {

			$partial = new stdClass();

			$partial->filenames_tried = array();

			$partial->found = false;

			/**
			 * Ensure $_partial_vars is an array
			 */
			$partial->vars = is_string( $_partial_vars ) ? wp_parse_args( $_partial_vars ) : $_partial_vars;
			if ( ! is_array( $partial->vars ) ) {
				$partial->vars = array();
			}

			/*
			 * Ensure filename does not have a leading slash ('/') but does have a trailing '.php'
			 */
			$_filename = preg_replace( '#(.+)(\.php)?$#', '$1.php', ltrim( $partial_slug, '/' ) );

			foreach ( array( 'theme', 'module', 'app' ) as $partial_type ) {

				switch ( $partial_type ) {
					case 'theme':
						$partial->dir    = get_stylesheet_directory();
						$partial->subdir = $wplib->PARTIALS_SUBDIR;
						break;

					case 'module':
						$_app_class = ! empty( $partial->vars['@app'] )
							? $partial->vars['@app']
							: self::app_class();

						$_module_slug = ! empty( $partial->vars['@module'] )
							? self::get_module_slug( $partial->vars['@module'], $_app_class )
							: get_class( $item );

						$partial->dir    = self::get_module_dir( $_module_slug );
						$partial->subdir = 'partials';
						break;

					case 'app':
						/**
						 * @note Not implemented yet.
						 */
						$_app_class = ! empty( $partial->vars['@app'] )
							? $partial->vars['@app']
							: self::app_class();

						$partial->dir    = call_user_func( array( $_app_class, 'root_dir' ) );
						$partial->subdir = 'partials';
						break;

				}

				$partial->filename = "{$partial->dir}/{$partial->subdir}/{$_filename}";

				if ( ! is_file( $partial->filename ) ) {

					$partial->filenames_tried[ $partial_type ] = $partial->filename;

				} else {

					$partial->found = true;

					$partial->var_name = self::get_constant( 'VAR_NAME', get_class( $item ) );

					$partial->comments = "<!--[PARTIAL FILE: {$partial->filename} -->";

					break;

				}

			}

			self::cache_set( $_cache_key, $partial );

		}

		$partial->ADD_COMMENTS = ! self::doing_ajax() && $wplib->IS_DEVELOPMENT;

		if ( ! $partial->found ) {

			if ( $partial->ADD_COMMENTS ) {

				/**
				 * This can be used by theme developers with view source to see which partials failed.
				 *
				 * @note FOR CODE REVIEWERS:
				 *
				 *          This will ONLY be output when $wplib->IS_DEVELOPMENT
				 *
				 */
				echo "\n<!--[FAILED PARTIAL FILE: {$partial_slug}. Tried:\n";
				foreach ( $partial->filenames_tried as $partial_type => $partial_filename ) {
					echo "\n\t{$partial_type}: {$partial_filename}";
				}
				echo "\n]-->";

			}

		} else {

			if ( $partial->ADD_COMMENTS ) {

				echo $partial->comments;

			}

			/**
			 * Extract the theme variable so it will always be available
			 */
			extract( array( 'theme' => self::theme() ) );

			extract( $partial->vars, EXTR_PREFIX_SAME, '_' );

			if ( $partial->var_name ) {

				/*
				 * Assign the $item's preferred variable name in addition to '$item', i.e. '$brand'
				 *
				 * This is absolutely critical to WPLib's theming architecture and is based on
				 * WordPress's only use of extract() in load_template(); we are using it for
				 * the exact same reasons WordPress was forced to use it.
				 *
				 * IOW: This is a very controlled use of extract(), we know what we are doing here.
				 */

				extract( array( $partial->var_name => $item ) );

			}

			unset(
				$_partial_vars,
				$_filename,
				$_cache_key,
				$_partial_index,
				$_app_class,
				$_module_class
			);

			ob_start();

			self::$_file_loading = $partial->filename;
			require( $partial->filename );
			self::$_file_loading = false;


			if ( $partial->ADD_COMMENTS ) {

				/**
				 * This can be used by theme developers with view source to see which partials failed.
				 *
				 * @note FOR CODE REVIEWERS:
				 *
				 *          This will ONLY be output when $wplib->IS_DEVELOPMENT
				 *
				 */
				echo $partial->comments;
				echo ob_get_clean();
				echo "\n<!--[END PARTIAL FILE: {$partial->filename} -->\n";


			} else {

				echo ob_get_clean();
			}

		}

	}

	/**
	 * Do "the_" Methods - Allow classes to delegate the "the_" logic processing here.
	 *
	 * The "the_" method can call virtual methods and/or delegate to a view or a model.
	 * The view and model can both be the same object if needed.
	 *
	 * @param string|object $view
	 * @param string|object $model
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function do_the_methods( $view, $model, $method_name, $args ) {

		$value = null;

		$suffix = $has_html_suffix = false;

		if ( preg_match( '#^the_(.+)_partial$#', $method_name, $match ) ) {

			/*
			 * Put the $partial name at the beginning of the $args array
			 */
			array_unshift( $args, str_replace( '_', '-', $match[1] ) );

			/**
			 * Now call 'the_partial_html' with $partial as first element in $args
			 */
			$value = call_user_func_array( array( $view, 'the_partial_html' ), $args );

			if ( preg_match( '#^<\{WPLib:(.+)\}>#', $value, $match ) ) {
				/**
				 * Check to see if their is a content type indicator
				 */
				switch ( $match[1] ) {

					case 'JSON':
						$suffix = '_json';
						break;

					case 'HTML':
					default:
						$suffix = '_html';
						/*
						 * Indicate that this content need not be run through wp_kses_post()
						 * since it was loaded by a partial which can be reviewed for security.
						 */
						$has_html_suffix = true;
						break;
				}
			}
		} else if ( method_exists( $view, $method_name ) && is_callable( $callable = array( $view, $method_name ) ) ) {

			/**
			 * Call the view method directly.
			 */
			$value = call_user_func_array( $callable, $args );

		} else if ( preg_match( '#^the_(.+?)(_attr|_url|_html|_link)?$#', $method_name, $match ) ) {

			$method_name = $match[ 1 ];
			$suffix = 3 === count( $match ) ? $match[ 2 ] : false;
			$has_html_suffix = self::has_html_suffix( $suffix );

			if ( $callable = self::get_callable( $view, "get_{$method_name}{$suffix}" ) ) {

				/*
				 * Call the $view method: 'get_whatever_suffix()'
				 */
				$value = call_user_func_array( $callable, $args );

			} else if ( $callable = self::get_callable( $view, "{$method_name}{$suffix}" ) ) {

				/*
				 * Call the $view method: 'whatever_suffix()'
				 */
				$value = call_user_func_array( $callable, $args );

			} else if ( $callable = self::get_callable( $model, "get_{$method_name}{$suffix}" ) ) {

				$has_html_suffix = self::has_html_suffix( $suffix );

				/*
				 * Call the $model method: 'get_whatever_suffix()'
				 */
				$value = call_user_func_array( $callable, $args );

			} else if ( $callable = self::get_callable( $model, "get_{$method_name}" ) ) {

				$has_html_suffix = self::has_html_suffix( $suffix );

				/*
				 * Call the $model method: 'get_whatever()'
				 */
				$value = call_user_func_array( $callable, $args );

			} else if ( ! $has_html_suffix && $callable = self::get_callable( $model, "{$method_name}{$suffix}" ) ) {

				/*
				 * Call the $model method: 'whatever_suffix()'
				 */
				$value = call_user_func_array( $callable, $args );

			} else if ( $callable = self::get_callable( $model, $method_name ) ) {

				$has_html_suffix = false;

				/*
				 * Call the $model method: "{$method_name}" (as passed)
				 */
				$value = call_user_func_array( $callable, $args );

			} else {

				$has_html_suffix = false;

				/*
				 * Not found, throw an error.
				 * $match[0] should have original $method_name
				 */
				$class_name = is_object( $view ) ? get_class( $view ) : $view;

				$message = sprintf( __( 'Method %s not found for class %s.', 'wplib' ), $match[ 0 ], $class_name );

				self::trigger_error( $message, E_USER_ERROR );

			}

		}

		/**
		 * Auto-escape output
		 */
		switch ( $suffix ) {

			case '_attr':

				echo $value = esc_attr( $value );
				break;

			case '_url':

				echo $value = esc_url( $value );
				break;

			case '_html':
			case '_link':

				echo $has_html_suffix ? $value : wp_kses_post( $value );
				break;

			default:

				echo $value = esc_html( $value );

		}

	}

	/**
	 * Given an object/class name and method name return a callable or null if can't be called.
	 *
	 * @note Are you reading this and want to know why do we use both is_callable() and method_exists()?
	 * @see "More details" section and comments of http://jmfeurprier.com/2010/01/03/method_exists-vs-is_callable/
	 *
	 * @param string|object $object
	 * @param string $method_name
	 * @return callable|null
	 */
	static function get_callable( $object, $method_name ) {

		$callable = array( $object, $method_name );

		return is_callable( $callable ) && method_exists( $object, $method_name )
			? $callable
			: null;

	}

	/**
	 * @param string|false $suffix
	 *
	 * @return bool
	 */
	static function has_html_suffix( $suffix ) {

		return (bool) ( $suffix && preg_match( '#^_(html|link)$#', $suffix ) );

	}

	/**
	 * @return string
	 */
	static function new_post_url() {

		return admin_url( 'post-new.php' );

	}

	/**
	 * @return WPLib_Theme_Base
	 */
	static function theme() {

		if ( ! self::$_theme ) {

			if ( is_null( self::$_->theme_class ) ) {

				self::$_theme = new WPLib_Theme_Default();

			} else {

				$theme_class = self::$_->theme_class;

				self::$_theme = new $theme_class;

			}

		}

		return self::$_theme;

	}

	/**
	 * @param WPLib_Theme_Base $theme
	 */
	static function set_theme( $theme ) {

		self::$_theme = $theme;

	}

	/**
	 * Returns array of class names $base_class children with positive values for $base_class::$contant_name.
	 *
	 * @param $base_class
	 *
	 * @param $constant_name
	 *
	 * @return string[]
	 */
	static function get_qualified_child_classes( $base_class, $constant_name ) {
		global $wplib;
		
		$cache_key = "classes[{$base_class}::{$constant_name}]";

		if ( $wplib->IS_PRODUCTION ) {
			$cache_key = md5( $cache_key );
		}

		if ( ! ( $child_classes = self::cache_get( $cache_key ) ) ) {

			self::autoload_all_classes();

			$child_classes = array();

			foreach ( self::site_classes() as $class_name ) {

				do {

					if ( ! is_subclass_of( $class_name, $base_class ) ) {
						continue;
					}

					if ( is_null( $constant_value = self::get_constant( $constant_name, $class_name ) ) ) {
						continue;
					}

					$child_classes[ $constant_value ] = $class_name;

				} while ( false );

			}

			self::cache_set( $cache_key, $child_classes );

		}

		return $child_classes;

	}

	/**
	 * @return bool|string
	 */
	static function short_prefix() {

		return self::get_constant( 'SHORT_PREFIX', get_called_class() );

	}

	/**
	 * Returns the raw meta fieldname given a non-prefixed field name.
	 * Adds both a leading underscore and a short prefix to the meta name.
	 *
	 * @param string $meta_name
	 *
	 * @return string
	 */
	static function _get_raw_meta_fieldname( $meta_name ) {

		$prefix = static::get_constant( 'SHORT_PREFIX' );

		return "_{$prefix}{$meta_name}";

	}

	/**
	 * Return the partials directory path for the called class.
	 *
	 * @return string
	 */
	static function partials_dir() {

		return static::get_root_dir( 'partials' );

	}

	/**
	 * Returns a file hash, but caches it in persistent cache
	 *
	 * @param string $filepath
	 *
	 * @return string
	 */
	static function file_hash( $filepath ) {

		global $wplib;

		$subscript = $wplib->IS_DEVELOPMENT
			? $filepath
			: md5( $filepath );

		if ( $file_hash = self::cache_get( $cache_key = "file_hash[{$subscript}]" ) ) {

			$file_hash = md5_file( $filepath );
			self::cache_get( $cache_key, $file_hash );

		}

		return $file_hash;

	}

	/**
	 * @param string $module_slug
	 * @param  string|bool $app
	 *
	 * @return string
	 */
	static function get_module_class( $module_slug, $app = false ) {

		$app_class = static::get_app_class( $app );

		return ( ! empty( self::$_->module_classes[ $app_class ][ $module_slug ] ) )
			? self::$_->module_classes[ $app_class ][ $module_slug ]
			: null;
	}

	/**
	 * @param string $class_name
	 * @param object|string|bool $app
	 *
	 * @return mixed|null
	 */
	static function get_module_slug( $class_name, $app = false ) {

		$module_slugs = array_flip( self::$_->module_slugs[ static::get_app_class( $app ) ] );

		return ! empty( $module_slugs[ $class_name ] ) ? $module_slugs[ $class_name ] : null;

	}

	/**
	 * Accepts an "app" parameter and return the app class.
	 *
	 * @param object|string $app Can be class name,
	 *
	 * @return string|null
	 */
	static function get_app_class( $app ) {

		do {

			if ( ! $app ) {

				$app_class = get_called_class();

			} else if ( is_object( $app ) ) {

				$app_class = get_class( $app );

			} else {

				$app_class = $app;

			}

			if ( ! is_string( $app_class ) ) {
				$err_msg = __( 'App parameter provided is not an a string.', 'wplib' );
				self::trigger_error( sprintf( $err_msg ) );
				break;
			}

			if ( ! class_exists( $app_class, false ) ) {
				$err_msg = __( 'App class %s is not a valid PHP class.', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $app_class ) );
				break;
			}

			if ( ! is_subclass_of( $app_class, 'WPLib_App_Base' ) ) {
				$err_msg = __( 'App class %s does extend WPLib_App_Base.', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $app_class ) );
				break;
			}

		} while ( false );

		return $app_class ? $app_class : null;

	}

	/**
	 * @param WPLib_Item_Base|WP_Post|WP_Term $item
	 * @param array $args
	 *
	 * @return WPLib_Term_Base|WPLib_Post_Base
	 */
	static function make_new_item( $item, $args = array() ) {

		$class = get_called_class();

		if ( self::get_constant( 'INSTANCE_CLASS', $class ) ) {

			if ( is_callable( array( $class, 'make_new_item' ) ) ) {

				$item = call_user_func( array( $class, 'make_new_item' ), $item, $args );

			} else {

				$err_msg = __ ( 'Cannot make new item. Class %s does not have make_new_item method', 'wplib' );
				self::trigger_error( sprintf( $err_msg, $class ) );

			}

		} else {

			$err_msg = __( 'Cannot make new item. Class %s does not have INSTANCE_CLASS constant.', 'wplib' );
			self::trigger_error( sprintf( $err_msg, $class ) );

		}

		return $item;

	}

	/**
	 * Returns the filepath for a theme partial file given its "local filename."
	 *
	 * Local filename means based at the root of the theme w/o leading slash.
	 *
	 * @example
	 *
	 *      FooBarApp::get_theme_file( 'single.php' )
	 *      FooBarApp::get_theme_file( 'partials/content.php' )
	 *
	 * @param string $local_file
	 *
	 * @return string
	 */
	static function get_theme_file( $local_file ) {

		return static::theme()->get_root_dir( $local_file );

	}

	/**
	 * Convert $query to an comma-separated list of name=value pairs for error output.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	static function args_to_err_msg( $args ) {

		$args = array_map( function( $element ) {

			if ( is_object(  $element  ) ) {

				$element = 'OBJECT:' . get_class( $element ) . '( ' . self::args_to_err_msg( $element ) . ' )';

			} else if ( is_array(  $element  ) ) {

				$element = 'ARRAY:[ ' . self::args_to_err_msg( $element ) . ' ]';
			}

			return $element;

		}, $args );

		return str_replace( '=', '= ', http_build_query( $args, null, ', ' ) );

	}

	/**
	 * Stub function to throw an error if not overridden in child class.
	 *
	 * @param array $query
	 * @param array $args
	 */
	static function get_list( $query, $args ) {

		$called_class = get_called_class();

		$app_class = self::app_class();

		$query = self::args_to_err_msg( $query );

		$args = self::args_to_err_msg( $args );

		$err_msg = __(
			'%s::get_list() cannot be called directly as class %s has no known context for a List; call from a class having a known context such as WPLib_Posts ' .
			'or WPLib_Terms or better, call a specific method such as %s::get_itemtype_list() where itemtype is a simply name like \'people\' or \'products\'. ' .
			'The $query that produced the error was: %s and the $args that produced the error was %s. '.
			'If you are trying to get the collection of posts generated by WordPress default query then use $theme->get_post_list() instead. ',
			'wplib' );

		if ( __CLASS__ === $called_class ) {
			/**
			 * If self::get_list() is called directly.
			 */
			$err_msg = sprintf( $err_msg, __CLASS__,  __CLASS__, __CLASS__, $query, $args );

		} elseif ( $app_class === $called_class ) {

			/**
			 * If $app_class::get_list() is called directly.
			 */
			$err_msg = sprintf( $err_msg, $app_class, $app_class, $app_class, $query, $args );

		} else {

			/**
			 * ::get_list() not called by WPLib or $app_class, but $app_class does not override ::get_list() as it should.
			 */
			$err_msg = '%s::get_list() must override self::get_List() as the latter cannot be called directly. ' .
			           'The $query that produced the error was: %s and the $args that produced the error was %s.';

			$err_msg = sprintf( __( $err_msg, 'wplib' ), $called_class, $query, $args );

		}

		self::trigger_error( $err_msg );


	}

	/**
	 * Determines is a class actually declares a method instead of just inheriting it.
	 *
	 * @param string $class_name
	 * @param string $method_name
	 * @return bool
	 */
	static function class_declares_method( $class_name, $method_name ) {

		if ( ! class_exists( $class_name ) || ! method_exists( $class_name, $method_name ) ) {

			$class_declares_method = false;

		} else {

			$reflector = new ReflectionMethod( $class_name, $method_name );
			$class_declares_method = $class_name === $reflector->getDeclaringClass()->name;
		}

		return $class_declares_method;

	}

	/**
	 * Determines if a named method exists and is_callable a given class.
	 *
	 * @param string $method_name
	 * @param string|bool $class_name
	 * @return bool
	 */
	static function can_call( $method_name, $class_name = false ) {

		if ( ! $class_name ){

			$class_name = get_called_class();
		}

		return method_exists( $class_name, $method_name ) && is_callable( array( $class_name, $method_name ) );

	}

	/**
	 * Scans to ensure that only one PHP class is declared.
	 *
	 * This is important because we assume only one class for the autoloader.
	 *
	 * @note For use ONLY during development
	 *
	 * @param $class_container
	 */
	static function _ensure_only_one_class( $class_container ) {

		global $wplib;
		
		if ( self::is_wp_debug() && $wplib->IS_DEVELOPMENT ) {

			preg_match_all(
				'#\n\s*(abstract|final)?\s*class\s*(\w+)#i',
				file_get_contents( $class_container ),
				$matches,
				PREG_PATTERN_ORDER
			);

			if ( 1 < count( $matches[2] ) ) {

				$message = __( 'Include files in WPLib Modules can can only contain one PHP class, %d found in %s: ' );

				static::trigger_error( sprintf(
					$message,
					count( $matches[2] ),
					implode( ', ', $matches[2] )
				) );

			}
		}
	}


	/**
	 * Capture status of DOING_XMLRPC
	 */
	static function _xmlrpc_call() {

		self::$_doing_xmlrpc = true;

	}

	/**
	 * @return bool
	 */
	static function doing_xmlrpc() {

		return self::$_doing_xmlrpc;

	}

	/**
	 * @return bool
	 */
	static function doing_ajax() {

		return defined( 'DOING_AJAX' ) && DOING_AJAX;

	}

	/**
	 * @return bool
	 */
	static function doing_cron() {

		return defined( 'DOING_CRON' ) && DOING_CRON;

	}

	/**
	 * Triggers error message unless doing AJAX, XMLRPC or Cron; then it logs the error but only if Development mode.
	 *
	 * @param string $error_msg
	 * @param int $error_type
	 * @param bool $echo If true use 'echo', if false use trigger_error().
	 */
	static function trigger_error( $error_msg, $error_type = E_USER_NOTICE, $echo = false ) {
		
		global $wplib;

		if ( ! self::doing_ajax() && ! self::doing_xmlrpc() && ! self::doing_cron() ) {

			if ( $wplib->IS_DEVELOPMENT ) {

				if ( $echo ) {

					echo "{$error_msg} [{$error_type}] ";

				} else {

					trigger_error( $error_msg, $error_type );

				}

			}

		} else if ( $wplib->IS_DEVELOPMENT || $wplib->LOG_ERRORS ) {

			error_log( "{$error_msg} [{$error_type}]" );

		}

	}

}

/**
 * Define global variable $wplib here for PhpStorm to know it globally
 *
 * @var WPLib_Config $wplib
 *
 */
global $wplib;
