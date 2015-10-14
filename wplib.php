<?php

/**
 * Class WPLib - Core class
 *
 * Plugin Name: WPLib
 * Plugin URI:  http://wordpress.org/plugins/wplib/
 * Description: A WordPress Website Foundation Library Agency and Internal Corporate Developers
 * Version:     0.6.3
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
 * @mixin _WPLib_Html_Helpers
 * @mixin _WPLib_WP_Helpers
 *
 * @todo Utility Modules: https://github.com/wplib/wplib/issues/6
 *
 * @todo PHPDoc - https://github.com/wplib/wplib/issues/8
 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11027141
 *
 */
class WPLib {

	const PREFIX = 'wplib_';
	const SHORT_PREFIX = 'wplib_';

	/**
	 * Runmodes
	 */
	const DEVELOPMENT = 0;
	const TESTING = 1;
	const STAGING = 2;
	const PRODUCTION = 3;

	/**
	 * @var int The current runmode.
	 */
	private static $_runmode = self::PRODUCTION;

	/**
	 * @var array $_helpers Array of class names that this class can delegate calls to.
	 *
	 * WPLib_Base::$_helpers is indexed by class name. Each element is a numerically indexed array of static methods.
	 *
	 */
	private static $_helpers = array();

	/**
	 * @var array URL of root for Lib/App/Site/Module/Theme, indexed by each's main class name.
	 */
	private static $_root_urls = array();

	/**
	 * @var array registered modules.
	 */
	private static $_modules = array();

	/**
	 * @var string[] Names of loaded classes
	 */
	private static $_module_classes = array();

	/**
	 * @var array List of classes that must be loaded on every page load.
	 */
	private static $_mustload_classes = array();

	/**
	 * @var array List of classes (as key) and filepaths (as value) to autoload.
	 */
	private static $_autoload_files = array();

	/**
	 * @var bool Get's set if doing XMLRPC.
	 */
	private static $_doing_xmlrpc = false;

	/**
	 * @var bool|string Flag to hold filename currently loading. Used by _shutdown() to report if a file failed to load.
	 */
	private static $_file_loading = false;

	/**
	 * @var array Files to autoload in the find_autoload files.
	 */
	private static $_new_files;

	/**
	 * @var int
	 */
	private static $_non_app_class_count = 0;

	/**
	 * @var WPLib_Theme_Base|bool
	 */
	private static $_theme = false;

	/**
	 * @var bool Flag to indicate all WPLib module classes have been loaded.
	 */
	private static $_init_9_ran = false;

	/**
	 *
	 */
	static function on_load() {

		if ( defined( 'WPLIB_RUNMODE' ) ) {

			$runmode = strtoupper( WPLIB_RUNMODE );

			if ( is_string( $runmode ) && defined( "self::{$runmode}" ) ) {

				$runmode = intval( self::get_constant( strtoupper( $runmode ) ) );

			}

			if ( is_int( $runmode ) && self::DEVELOPMENT <= $runmode && self::PRODUCTION >= $runmode ) {

				self::set_runmode( $runmode );

			}

		} else {

			self::set_runmode( self::PRODUCTION );

		}

		spl_autoload_register( array( __CLASS__, '_autoloader' ) );

		self::register_module( 'posts', 0 );
		self::register_module( 'terms', 0 );
		self::register_module( 'users', 0 );
		self::register_module( 'post-type-post', 0 );
		self::register_module( 'post-type-page', 0 );
		self::register_module( 'taxonomy-categories', 0 );
		self::register_module( 'taxonomy-post-tags', 0 );
		self::register_module( 'helpers-html', 0 );
		self::register_module( 'helpers-wp', 0 );
		self::register_module( 'theme', 0 );

		/**
		 * Register default User Roles
		 */
		self::register_module( 'user-role-administrator', 4 );
		self::register_module( 'user-role-contributor', 4 );
		self::register_module( 'user-role-subscriber', 4 );
		self::register_module( 'user-role-editor', 4 );
		self::register_module( 'user-role-author', 4 );

		self::add_class_action( 'init', 9 );
		self::add_class_action( 'plugins_loaded', 11 );
		self::add_class_action( 'after_setup_theme' );
		self::add_class_action( 'after_setup_theme', 11 );
		self::add_class_action( 'xmlrpc_call' );
		self::add_class_action( 'shutdown' );

		/**
		 * Set a marker to ignore classes declared before this class.
		 */
		self::$_non_app_class_count = count( get_declared_classes() ) - 1;

	}

	/**
	 * Autoload all WPLib module classes to ensure they are available for 'init' hook.
	 *
	 * @return array
	 */
	static function _init_9() {

		if ( ! WPLib::cache_get( $cache_key = 'module_classes_cached' ) ) {

			self::$_init_9_ran = true;

			self::autoload_all_classes();

			WPLib::cache_set( $cache_key, true );
		}

	}

	/**
	 * Return the list of classes declared after WPLib first loads.
	 * @return array
	 */
	static function site_classes() {

		if ( ! ( $site_classes = WPLib::cache_get( $cache_key = 'site_classes' ) ) ) {

			/**
			 * Make sure we have all classes loaded.
			 */
			WPLib::autoload_all_classes();

			$site_classes = array_reverse( array_slice( get_declared_classes(), self::$_non_app_class_count ) );
			$site_classes = array_filter( $site_classes, function( $element ) {
				/*
				 * Strip out WordPress core classes
				 */
				return ! preg_match( '#^(WP|wp)_?#', $element );
			});
			WPLib::cache_set( $cache_key, $site_classes );

		}

		return $site_classes;

	}

	/**
	 * @param string $class_name
	 */
	static function _autoloader( $class_name ) {

		if ( isset( self::$_autoload_files[ $class_name ] ) ) {

			require_once( self::$_autoload_files[ $class_name ] );

			/**
			 * Don't need it anymore since we loaded it.
			 */
			unset( static::$_autoload_files[ $class_name ] );

		}

	}

	/**
	 * Convert relative file paths to absolute file paths.
	 *
	 * Recognize a path with a leading slash as an absolute, a no leading slash or starting with '~/' as relative.
	 *
	 * @todo Make work for Windows - https://github.com/wplib/wplib/issues/9
	 *
	 * @param string $filepath
	 * @param bool|string $dir
	 * @return string
	 */
	static function maybe_make_absolute_path( $filepath, $dir = false ) {

		$directory_separator = DIRECTORY_SEPARATOR;

		if ( $directory_separator != $filepath[0] ) {

			if ( preg_match( "#^~({$directory_separator}.*)$#", $filepath, $match ) ) {

				$path = $match[1];

			} else {

				$path = $directory_separator . ltrim( $filepath, $directory_separator );

			}

			$filepath = $dir ? "{dir}{$path}" : static::get_root_dir( $path );

		}

		return $filepath;

	}

	/**
	 * Load all necessary files. This finds autoloading files and loads modules.
	 */
	static function _plugins_loaded_11() {

		static::_load_necessary_files();

	}

	/**
	 * If used in a theme you have to first initialize it before WPLib_Theme_Base
	 * classes will be available to extend.
	 */
	static function initialize() {

		static::_load_necessary_files();

	}

	/**
	 * Now load the theme's modules.
	 */
	static function _after_setup_theme() {

		static::_load_necessary_files();

	}

	/**
	 * Load all necessary files, i.e. modules and finds all autoloading files.
	 *
	 * This is called twice; (1) On 'plugins_loaded' and (2) on 'after_setup_theme'.
	 *
	 */
	private static function _load_necessary_files() {

		spl_autoload_register( $autoloader = array( __CLASS__, '_find_files_autoloader' ), true, true );


		/**
		 * Find all autoloading files from components that have been loaded by (1) plugins or (2) the theme.
		 */
		static::_find_autoload_files();

		/**
		 * Load the modules defined in (1) the plugins or (2) the theme.
		 */
		static::_load_modules();

		/**
		 * Find all autoloading files defined by modules specified by (1) plugins or (2) the theme.
		 */
		static::_find_autoload_files();

		spl_autoload_unregister( $autoloader );

	}

	/**
	 * Special autoloader to run only for conflicts.
	 *
	 * @param $class_name
	 */
	static function _find_files_autoloader( $class_name ) {

		$dirpath = dirname( self::$_file_loading );

		$parts = explode( '_', strtolower( $class_name ) );
		array_shift( $parts );
		$filename = implode( '-', $parts );

		$filepath = "{$dirpath}/class-{$filename}.php";

		if ( is_file( $filepath ) ) {

			require( $filepath );

			$new_files = array_flip( static::$_new_files );
			unset( $new_files[$filepath] );
			static::$_new_files = array_flip( $new_files );

		}

	}

	/**
	 * Load all registered modules, by priority
	 */
	private static function _load_modules() {

		ksort( self::$_modules );

		self::$_modules = apply_filters( 'wplib_modules', self::$_modules );

		$called_class = get_called_class();

		$module_classes = isset( self::$_module_classes[ $called_class ] ) ? self::$_module_classes[ $called_class ] : array();

		$abspath_regex = '#^' . preg_quote( ABSPATH ) . '(.+)' . DIRECTORY_SEPARATOR . '.+\.php$#';

		foreach ( self::$_modules as $priority ) {

			foreach ( $priority as $filepath ) {

				if ( WPLib::is_development() && ! is_file( $filepath ) ) {

					WPLib::trigger_error( sprintf( __( "Required file not found: %s", 'wplib' ), $filepath ) );

				}

				/**
				 * Set self::$_file_loading so 'shutdown' hook can report which file caused the load error.
				 */
				self::$_file_loading = $filepath;
				require_once $filepath;
				self::$_file_loading = false;

				$classes = get_declared_classes();
				$module_classes[ end( $classes ) ] = $module_path = preg_replace( $abspath_regex, '~/$1', $filepath );

				/**
				 * Find all autoloading files defined by the above module.
				 */
				static::_find_autoload_files();

			}

		}

		self::$_module_classes[ $called_class ] = $module_classes;

		self::$_modules = array();

	}

	/**
	 * Throw error if site failed to load because of a module failing to load.
	 */
	static function _shutdown() {

		if ( self::$_file_loading ) {

			$message = __( 'File failed to load: %s.', 'wplib' );
			self::trigger_error( sprintf( $message, self::$_file_loading ), E_USER_ERROR, true );

		}

	}

	/**
	 * Returns the list of "Component" classes.  A Component is one of Lib, Site, App, Theme, Module.
	 *
	 * @return array
	 */
	static function component_classes() {

		$component_classes = array();

		foreach( get_declared_classes() as $class ) {

			if ( is_subclass_of( $class, __CLASS__ ) || __CLASS__ == $class ) {

				$component_classes[] = $class;

			}

		}

		return $component_classes;

	}

	/**
	 * Scan registered autoload files, by priority
	 *
	 * This will get called 4 times.
	 *
	 *      1 & 2: Find all autoloading files from components that have been loaded by (1) plugins or (2) the theme.
	 *      3 & 4: Find all autoloading files defined by modules specified by (1) plugins or (2) the theme.
	 */
	private static function _find_autoload_files() {
		static $class_count = 0;

		$classes = static::component_classes();

		$latest_classes = array_slice( $classes, $class_count );

		if ( count( $latest_classes ) ) {

			$class_count += count( $latest_classes );

			$class_key = implode( '|', $latest_classes );

			$class_key = WPLib::is_production() ? md5( $class_key ) : $class_key;

			if ( ! ( self::$_new_files = static::cache_get( $cache_key = "autoload_files[{$class_key}]" ) ) ) {

				$autoload_files = array();

				/**
				 * These were the files that were manually added
				 */
				$added_files = array_values( self::$_autoload_files );

				/**
				 * For each Site/App/Module/Lib/Theme class
				 */
				foreach( $latest_classes as $class_name ) {

					/**
					 * Scan the includes directory for all files.
					 */
					$found_files = glob( $autoload_dir = static::get_root_dir( 'includes', $class_name ) . '/*.php' );

					if ( 0 == count( $found_files ) ) {

						continue;

					}

					/**
					 * Find out what classes are currently defined.
					 */
					$declared_classes = get_declared_classes();

					/**
					 * Diff the manually added files with the new ones scanned to get new files.
					 */
					self::$_new_files = array_diff( $found_files, $added_files );

					/**
					 * Load all the scanned files from the /include/ directory
					 */
					do {
						self::$_file_loading = array_shift( self::$_new_files );
						require( self::$_file_loading );
						self::$_file_loading = false;

					} while ( count( self::$_new_files ) );

					/**
					 * Find the newly declared classes by comparing what was declared before with what is declared now.
					 */
					$loaded_classes = array_diff( get_declared_classes(), $declared_classes );

					if ( count( $loaded_classes ) > count( $found_files ) ) {

						$message = __( 'More than one class defined in \'/includes/\' directory of %s.', 'wplib' );
						static::trigger_error( sprintf( $message, $class_name ) );

					} else if ( count( $loaded_classes ) < count( $found_files ) ) {

						$message = __( 'Files with no classes defined in \'/includes/\' directory of %s.', 'wplib' );
						static::trigger_error( sprintf( $message, $class_name ) );

					} else {

						/**
						 * Add them in for autoloading.
						 * $loaded_classes should be in the same order as $found_files.
						 */
						$autoload_files += array_combine( $loaded_classes, $found_files );

					}

				}

				/**
				 * Now stuff into cache
				 */
				static::cache_set( $cache_key, $autoload_files );

			}

			if ( isset( $autoload_files ) ) {

				/**
				 * Set the mustload classes based on on_load() ordered by parent/child classes.
				 */
				self::_set_mustload_classes( $autoload_files );

				/**
				 * Add these new files to the list of files to autoload at the default priority.
				 */
				self::$_autoload_files = array_merge( self::$_autoload_files, $autoload_files );

			}

		}

	}

	/**
	 * Force loading of all classes if needed to find all classes with a specific constant.
	 */
	static function autoload_all_classes() {

		static $classes_loaded = false;

		if ( ! self::$_init_9_ran ) {

			$err_msg = "Cannot call WPLib::autoload_all_classes() prior to 'init' action, priority 9.";
			WPLib::trigger_error( $err_msg );

		} else if ( ! $classes_loaded ) {

			foreach ( array_keys( self::$_autoload_files ) as $autoload_class ) {

				self::_autoloader( $autoload_class );

			}

		}

	}


	/**
	 *
	 *
	 * This will get called 4 times.
	 *
	 *      1 & 2: Finding all autoloading files from components that have been loaded by (1) plugins or (2) the theme.
	 *      3 & 4: Finding all autoloading files defined by modules specified by (1) plugins or (2) the theme.
	 *
	 * Each time it is called it will have values added to self::$_mustload_classes.
	 *
	 * @param array $autoload_files
	 */
	static function _set_mustload_classes( $autoload_files ) {

		if ( $mustload_classes = static::cache_get( $cache_key = "mustload_classes" ) ) {

			self::$_mustload_classes = $mustload_classes;

		} else {

			foreach ( array_keys( $autoload_files ) as $class_name ) {

				if ( is_callable( array( $class_name, 'on_load' ) ) ) {

					self::$_mustload_classes[ $class_name ] = get_parent_class( $class_name );

				}

			}
		}

	}

	/**
	 * Determine and then load the "mustload" classes
	 * They are the classes with an on_load() method.
 	 */
	static function _after_setup_theme_11() {

		$mustload_classes = self::_ordered_mustload_classes();

		self::_load_mustload_classes( $mustload_classes );

	}

	/**
	 * Loads the "mustload" classes on every page load.
	 *
	 * Mustload classes are classes with an on_load() method.
	 *
	 * @param string[] $mustload_classes
	 */
	private static function _load_mustload_classes( $mustload_classes ) {

		foreach( $mustload_classes as $mustload_class ) {

			/**
			 * This will autoload the class file if it does not already exist.
			 */
			class_exists( $mustload_class );

		}

	}

	/**
	 * Orders the Mustload classes in order of least dependency.
	 *
	 * Mustload classes are classes with an on_load() method.
	 *
	 * @return array
	 */
	private static function _ordered_mustload_classes() {

		if ( ! static::cache_get( $cache_key = "mustload_classes" ) ) {

			$mustload_classes = array();

			do {
				reset( self::$_mustload_classes );
				$key = key( self::$_mustload_classes );
				self::_flatten_array_dependency_order( self::$_mustload_classes[ $key ], $key, self::$_mustload_classes, $mustload_classes );

			} while ( count( self::$_mustload_classes ) );

			static::cache_set( $cache_key, $mustload_classes );

		}
		return $mustload_classes;
	}

	/**
	 * Flatten an array containing parent class names with array.
	 *
	 * Very specifically used for mustload classes. Uses recursion.
	 *
	 * Mustload classes are classes with an on_load() method.
	 *
	 * @param string $parent_class
	 * @param string $child_class
	 * @param array $mustload_classes
	 * @param string[] $ordered_classes
	 *
	 * @return array
	 */
	private static function _flatten_array_dependency_order( $parent_class, $child_class, &$mustload_classes, &$ordered_classes ) {

		if ( isset( $mustload_classes[ $parent_class ] ) ) {

			$child_class = $parent_class;

			$parent_class = $mustload_classes[ $parent_class ];

			self::_flatten_array_dependency_order( $parent_class, $child_class, $mustload_classes, $ordered_classes );

		}
		if (  ! class_exists( $parent_class, false ) ) {

			$ordered_classes[] = $parent_class;

		}

		$ordered_classes[] = $child_class;

		unset( $mustload_classes[ $child_class ] );

	}

	/**
	 * Capture status of DOING_XMLRPC
	 */
	static function _xmlrpc_call() {

		self::$_doing_xmlrpc = true;

	}

	/**
	 * @return int
	 */
	static function runmode() {

		return self::$_runmode;

	}

	/**
	 * @param int $runmode
	 */
	static function set_runmode( $runmode ) {

		self::$_runmode = $runmode >= self::DEVELOPMENT && $runmode <= self::PRODUCTION ? $runmode : self::PRODUCTION;

	}

	/**
	 * @return bool
	 */
	static function is_development() {

		return self::DEVELOPMENT == self::$_runmode;

	}

	/**
	 * @return bool
	 */
	static function is_testing() {

		return self::TESTING == self::$_runmode;

	}

	/**
	 * @return bool
	 */
	static function is_staging() {

		return self::STAGING == self::$_runmode;

	}

	/**
	 * @return bool
	 */
	static function is_production() {

		return self::PRODUCTION == self::$_runmode;

	}

	/**
	 * If runmode is development or SCRIPT_DEBUG
	 *
	 * @return string
	 *
	 * @todo https://github.com/wplib/wplib/issues/7
	 * @see https://github.com/wplib/wplib/commit/8dc27c368e84f7ba6e1448753e1b1f082a60ac6d#commitcomment-11026829
	 */
	static function is_script_debug() {

		return static::is_development() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );

	}

	/**
	 * @param string $action
	 * @param int $priority
	 */
	static function add_class_action( $action, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$action}" ) . ( 10 != $priority ? "_{$priority}" : '' );
		add_action( $action, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $filter
	 * @param int $priority
	 */
	static function add_class_filter( $filter, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$filter}" ) . ( 10 != $priority ? "_{$priority}" : '' );
		add_filter( $filter, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $action
	 * @param int $priority
	 */
	static function remove_class_action( $action, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$action}" ) . ( 10 != $priority ? "_{$priority}" : '' );
		remove_action( $action, array( get_called_class(), $hook ), $priority );

	}

	/**
	 * @param string $filter
	 * @param int $priority
	 */
	static function remove_class_filter( $filter, $priority = 10 ) {

		$hook = str_replace( '-', '_', "_{$filter}" ) . ( 10 != $priority ? "_{$priority}" : '' );
		remove_filter( $filter, array( get_called_class(), $hook ), $priority );

	}

	/**
	 * Register a helper class to the specified class.
	 *
	 * @param string $helper The name of the helper class.
	 * @param string|bool $class_name   Name of the class adding the helper. Defaults to called class.
	 */
	static function register_helper( $helper, $class_name  = false ) {

		if ( ! $class_name ) {

			$class_name = get_called_class();

		}

		self::$_helpers[ $class_name ][] = $helper;

	}

	/**
	 * Delegate calls to other classes.
	 * This allows us to document a single "API" for WPLib yet
	 * structure the code more conveniently in multiple class files.
	 *
	 * @example  WPLib::call_helper( __CLASS__, 'register_item', array( $item ), $found );
	 *
	 * @param string $class_name    Name of class that is calling the helper
	 * @param string $helper_method Name of the helper method
	 * @param array  $args          Arguments to pass to the helper method
	 * @param object $container     An object containing a property: 'callable'
	 *
	 * @return mixed|null
	 */
	static function call_helper( $class_name, $helper_method, $args, $container = null ) {

		$value = null;

		if ( is_null( $container ) ) {
			/**
			 * This container is needed because call_user_func() doesn't pass things by reference
			 * This is relevant when we need to call the helper of the parent class.
			 */
			$container = new stdClass();
		}

		/*
		 * Check to see if the helper callable for this class and method is cached.
		 */
		$container->callable = wp_cache_get(
			$cache_key = "{$class_name}::{$helper_method}()",
			$group = "wplib_helpers",
			false,
			$found  // This gets set by wp_cache_get()
		);

		if ( ! $found ) {

			/*
			 * If not cached, find the callable
			 */
			if ( isset( self::$_helpers[ $class_name ] ) ) {

				/*
				 * If not class has helper classes
				 */
				foreach ( self::$_helpers[ $class_name ] as $helper ) {
					/*

					 * Loop through each of the helper classes to see
					 * if the method exists in that helper class
					 */
					if ( method_exists( $helper, $helper_method ) && is_callable( $callable = array( $helper, $helper_method ) ) ) {

						/*
						 * If helper method found in helper class, set $callable and cache it.
						 */
						wp_cache_set( $cache_key, $container->callable = $callable, $group );

						$found = true;

						break;

					}

				}
			}

		}

		$parent_called = false;

		if ( ! $found ) {

			if ( $parent_class = get_parent_class( $class_name ) ) {

				/**
				 * Call the method in the parent class assuming the parent has the method.
				 */

				$value = call_user_func( array( $parent_class, 'call_helper' ),
					$parent_class,
					$helper_method,
					$args,
					$container );

				$parent_called = true;

				if ( $container->callable ) {

					/**
					 * Store it for future calls
					 */

					wp_cache_set( "{$parent_class}::{$helper_method}()", $container->callable, $group );

					$found = true;

				}

			}
		}

		if ( ! $found ) {

			/*
			 * Oops. No helper was found after all that.  Output an error message.
			 */
			$message = sprintf(
				__( 'ERROR: There is no helper method %s() for class %s. ', 'wplib' ),
				$helper_method,
				$class_name
			);

			static::trigger_error( $message, E_USER_ERROR );

			$container->callable = null;

		} else if ( ! $parent_called ) {

			/*
			 * A helper was found so call it.
			 */
			$value = call_user_func_array( $container->callable, $args );

		}

		return $value;

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

		$filepath = '/' . ltrim( $filepath, '/' );

		$reflector = new ReflectionClass( $class_name );

		return realpath( dirname( $reflector->getFileName() ) . $filepath );

	}

	/**
	 * Get the root URL for a given Lib/Site/App/Module/Theme.
	 *
	 * @param string $filepath Name of path to append to root URL.
	 * @param bool|string $class_name Name of class to return the root dir.
	 *
	 * @return string
	 *
	 */
	static function get_root_url( $filepath, $class_name = false ) {

		if ( ! $class_name ) {

			$class_name = get_called_class();

		}

		if ( ! isset( self::$_root_urls[ $class_name ] ) ) {

			$root_dir = static::get_root_dir( '', $class_name );

			if ( preg_match( '#^' . preg_quote( get_stylesheet_directory() ) . '(.*)#', $root_dir, $match ) ) {
				/**
				 * If in the theme directory
				 */
				$root_url = get_stylesheet_directory_uri() . ( isset( $match[1] ) ? $match[1] : '' );

			} else {
				/**
				 * Or if in the plugins directories
				 */
				$root_url = plugins_url( '', $root_dir . '/_.php' );

			}

			self::$_root_urls[ $class_name ] = rtrim( $root_url, '/' );

		}

		$filepath = '/' . ltrim( $filepath, '/' );

		return self::real_url( self::$_root_urls[ $class_name ] . $filepath );

	}

	/**
	 * Like realpath() but for URLs
	 * @param string $url
	 * @return string
	 */
	static function real_url( $url ) {

	    foreach( array_keys( $url = explode( '/', $url ), '..' ) AS $keypos => $key) {
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
	 * @param string $module
	 * @param int $priority
	 */
	static function register_module( $module, $priority = 10 ) {

		self::$_modules[ $priority ][] = static::get_root_dir( "modules/{$module}/{$module}.php" );

	}

	/**
	 * @return string[]
	 */
	static function module_classes() {

		return self::$_module_classes;

	}

	/**
	 * @param string $app_class
	 *
	 * @return string[]|null
	 */
	static function get_module_classes( $app_class ) {

		$module_classes = self::module_classes();

		return ! empty( $module_classes[ $app_class ] ) ? $module_classes[ $app_class ] : null;

	}

	/**
	 * @param WPLib_Item_Base $item
	 *
	 * @return string|null
	 */
	static function get_module_filepath( $item = false ) {

		if ( ! $item ) {
			$item = get_called_class();
		}

		$reflector = new ReflectionClass( $item );

		$filepath = WPLib::maybe_make_abspath_relative( $reflector->getFileName() );

		$app_class = WPLib::app_class( $item );

		foreach( self::get_module_classes( $app_class ) as $module_class => $module_filepath ) {

			if ( 0 === strpos( $filepath, $module_filepath ) ) {

				return $module_filepath;

			}

		}

		return null;

	}

	/**
	 * @return array|null
	 */
	static function app_classes() {

		if ( ! ( $app_classes = WPLib::cache_get( $cache_key = "app_classes" ) ) ) {

			$app_classes = array_filter( self::site_classes(), function( $class_name ) {
				return is_subclass_of( $class_name, 'WPLib_App_Base' );
			});

			WPLib::cache_set( $cache_key, $app_classes );

		}
		return $app_classes;

	}

	/**
	 * Returns the one app class defined.
	 *
	 * @note ASSUMES naming convention where App name is a subset of name of class, i.e. 'LawPress' and 'LawPress_Person'.
	 * @note Currently only supports one app class.
	 *
	 * @var string|object $class_name
	 * @return string|null
	 */
	static function app_class( $class_name ) {

		if ( is_object( $class_name ) ) {

			$class_name = get_class( $class_name );

		}

		foreach( self::app_classes() as $app_class ) {

			$regex = '#^_?'. preg_quote( $app_class ) . '_.+$#';

			if ( preg_match( $regex, $class_name ) ) {

				return $app_class;

			}

		}

		return 'WPLib';

	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return mixed
	 */
	static function cache_get( $key, $group = '' ) {

		if ( ! is_string( $key ) && ! is_int( $key ) && static::is_development() ) {

			static::trigger_error( __( 'Cache key is not string or numeric.', 'wplib' ) );

		}

		$cache = wp_cache_get( $key, static::_filter_group( $group ) );

		return $cache;

	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param string $group
	 */
	static function cache_set( $key, $value, $group = '' ) {

		wp_cache_set( $key, $value, static::_filter_group( $group ) );

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
	 * @return bool
	 */
	static function doing_autosave() {

		return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;

	}

	/**
	 * @return bool
	 */
	static function do_log_errors() {

		return defined( 'WPLIB_LOG_ERRORS' ) && WPLIB_LOG_ERRORS;

	}

	/**
	 * Return if WPLIB_TEMPLATE_GLOBAL_VARS was set to true
	 *
	 * Setting WPLIB_TEMPLATE_GLOBAL_VARS to false will cause WPLib to extract $GLOBALS before loading the WP template which normally happens in
	 * /wp-include/template-loader.php but WPLib hijacks that.
	 *
	 * @return bool
	 */
	static function use_template_global_vars() {

		return ! defined( 'WPLIB_TEMPLATE_GLOBAL_VARS' ) || ! WPLIB_TEMPLATE_GLOBAL_VARS;

	}

	/**
	 * @param string $method
	 * @param array  $args
	 *
	 * @return mixed
	 */
	static function __callStatic( $method, $args ) {

		return self::call_helper( get_called_class(), $method, $args );

	}

	/**
	 * Triggers error message unless doing AJAX, XMLRPC or Cron; then it logs the error but only if Development mode.
	 *
	 * @param string $error_msg
	 * @param int $error_type
	 * @param bool $echo If true use 'echo', if false use trigger_error().
	 */
	static function trigger_error( $error_msg, $error_type = E_USER_NOTICE, $echo = false ) {

		$is_development = static::is_development();

		if ( ! static::doing_ajax() && ! static::doing_xmlrpc() && ! static::doing_cron() ) {

			if ( $is_development ) {

				if ( $echo ) {

					echo "{$error_msg} [{$error_type}] ";

				} else {

					trigger_error( $error_msg, $error_type );

				}

			}

		} else if ( $is_development || static::do_log_errors() ) {

			/**
			 * ONLY triggers errors:
			 *      IF runmode() == WPLib::DEVELOPMENT
			 *      OR define( 'WPLIB_LOG_ERRORS', true ) in /wp-config.php.
			 *
			 * For runmode() == WPLib::DEVELOPMENT define( 'WPLIB_RUNMODE', 0 ) in /wp-config.php.
			 */
			error_log( "{$error_msg} [{$error_type}]" );

		}

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
	 * Return the subdir name for templates.
	 *
	 * @todo Allow different contexts (the app and different modules) to be set differently than the theme directory.
	 *
	 * @return string
	 */
	static function templates_subdir() {
		/*
		 * Allow the templates subdir to be overridden in the config file
		 */
		return defined( 'WPLIB_TEMPLATES_SUBDIR' )
			? WPLIB_TEMPLATES_SUBDIR
			: 'templates';

	}

	/**
	 * Generates output for templates that are shared by modules inside of a WPLib App.
	 *
	 * @param string $template
	 * @param array|string $_template_vars
	 * @param WPLib_Item_Base|object|string|null $item
	 * @param string $class_name
	 */
	static function the_module_template( $template, $_template_vars = array(), $item = null, $class_name = null ) {

		if ( is_null( $class_name ) ) {

			$class_name = get_called_class();

		}

		$module_filepath = static::get_module_filepath( $class_name );

		$templates_subdir = static::templates_subdir();

		$template = "{$module_filepath}/{$templates_subdir}/{$template}";

		static::the_template( $template, $_template_vars, $item );
	}

	/**
	 * Generates output for templates that are shared by a WPLib App.
	 *
	 * @param string $template
	 * @param array|string $_template_vars
	 * @param WPLib_Item_Base|object|null $item
	 */
	static function the_app_template( $template, $_template_vars = array(), $item = null ) {

		/**
		 * Get the root directory for App defined for the item
		 */
		$class_name = is_object( $item ) && method_exists( $item, 'app_class' ) ? $item->app_class() : get_called_class();

		if ( ! method_exists( $class_name, 'root_dir' ) ) {

			$err_msg = __( "Class %s does not have method root_dir(), called from: %s::%s()", 'wplib' );

			WPLib::trigger_error( sprintf( $err_msg, $class_name, __CLASS__, __METHOD__ ) );

		} else {

			$root_dir = call_user_func( array( $class_name, 'root_dir' ) );

			$templates_subdir = static::templates_subdir();

			$root_dir = WPLib::maybe_make_abspath_relative( $root_dir );

			$template = "{$root_dir}/{$templates_subdir}/{$template}";

			static::the_template( $template, $_template_vars, $item );

		}

	}

	/**
	 * @param string $template
	 * @param array|string $_template_vars
	 * @param WPLib_Item_Base|object $item
	 *
	 * @note This is called via an instance as well as
	 *       If this becomes deprecated we can prefix with an '_' and then
	 *       use __call() and __callStatic() to allow it to be invoked.
	 * @see  http://stackoverflow.com/a/7983863/102699
	 */
	static function the_template( $template, $_template_vars = array(), $item = null ) {

		$_filename = preg_replace( '#(\.php)$#', '', ltrim( $template, '/' ) ) . '.php';

		$template = new stdClass();
		$template->dir = get_stylesheet_directory();

		/*
		 * If root path (i.e. "~/wp-content/...") add ABSPATH to the template file after removing ~
		 * If not root path, assume the template file is in /templates/ inside the theme directory
		 */
		$_templates_subdir = static::templates_subdir();
		$template->filename = preg_match( '#^~[\/](.+)#', $_filename, $match )
			? ABSPATH . $match[ 1 ]
			: "{$template->dir}/{$_templates_subdir}/{$_filename}";

		if ( ! is_string( $_template_vars ) || false !== strpos( $_template_vars, '=' ) ) {

			$_specialty = false;

			if ( is_string( $_template_vars ) ) {

				$_template_vars = wp_parse_args( $_template_vars );

			}

			if ( false === $_template_vars || is_null( $_template_vars ) ) {

				$_template_vars = array();

			} else if ( ! is_array( $_template_vars ) ) {

				$message = __( 'Unexpected value for 2nd parameter passed to the_template(). Expected array, string, false or null but got %s.', 'wplib' );
				WPLib::trigger_error( sprintf( $message, gettype( $_template_vars ) ) );

			}

		} else {

			/**
			 * If a string is passed assume it is for a more specific template and behave like get_template_part().
			 */
			$_specialty = esc_attr( $_template_vars );

			$_template_vars	= array();

			$_specialty = preg_replace( '#(\.php)$#', "-{$_specialty}$1", $template->filename );

		}

		if ( $_specialty && is_file( $_specialty )  ) {
			/**
			 * We found the special template before the general one.
			 */
			$template->filename = $_specialty;

			$_specialty = true;

		}


		if ( true !== $_specialty && ! is_file( $template->filename )  ) {

			/**
			 * No speciality template and no template at all.
			 */
			$template->filename = $template->dir = false;

			if ( ! WPLib::is_production() ) {

				/**
				 * This is ONLY output if constant 'WPLIB_RUNMODE' is defined in wp-config.php.
				 */
				echo "\n<!--[FAILED Template File: {$template->filename} -->\n";

			}

		}

		if ( ! $template->filename ) {

			$output = false;

		} else {

			if ( ! WPLib::doing_ajax() && ! WPLib::is_production() ) {

				echo "\n<!--[Template File: {$template->filename} -->\n";

			}

			extract( $_template_vars, EXTR_PREFIX_SAME, '_' );

			if ( $item && ( $_var_name = WPLib::get_constant( 'VAR_NAME', get_class( $item ) ) ) ) {
				/*
				 * Assign the $item's preferred variable name in addition to '$item', i.e. '$brand'
				 * This is a very controlled use of extract() i.e. I know what I am doing here.
				 */
				extract( array( $_var_name => $item ) );
			}

			$template->vars = $_template_vars;

			unset(
				$_template_vars,
				$_templates_subdir,
				$_filename,
				$_cache_key,
				$_var_name,
				$_specialty
			);

			ob_start();

			self::$_file_loading = $template->filename;
			require( $template->filename );
			self::$_file_loading = false;

			$output = ob_get_clean();

		}

		echo $output;

	}

	/**
	 * @param string|object $view
	 * @param string|object $model
	 * @param string $method_name
	 * @param array $args
	 *
	 * @return mixed
	 */
	static function do_the_methods( $view, $model, $method_name, $args ) {

		$value = null;

		if ( preg_match( '#^the_(.+)_template$#', $method_name, $match ) ) {

			/*
			 * Put the $template name at the beginning of the $args array
			 */
			array_unshift( $args, str_replace( '_', '-', $match[1] ) );

			/**
			 * Now call 'the_template' with $template as first element in $args
			 */
			$value = call_user_func_array( array( $view, 'the_template' ), $args );

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
						 * since it was loaded by a template which can be reviewed for security.
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
			$suffix = 3 == count( $match ) ? $match[ 2 ] : false;

			if ( is_callable( $callable = array( $view, $suffix_method = "{$method_name}{$suffix}" ) ) && method_exists( $view, $suffix_method ) ) {

				/**
				 * @note Reading this and want to know why do we use both is_callable() and method_exists()?
				 * @see "More details" section and comments of http://jmfeurprier.com/2010/01/03/method_exists-vs-is_callable/
				 */

				$has_html_suffix = preg_match( '#^_(html|link)$#', $suffix );

				/*
				 * Check $view to see if the suffixed method exist.
				 */
				$value = call_user_func_array( $callable, $args );

			} else if ( is_callable( $callable = array( $model, $suffix_method ) ) && method_exists( $model, $suffix_method ) )  {

				/*
				 * Check $model to see if the $suffix_method exists.
				 */
				$value = call_user_func_array( $callable, $args );

				$has_html_suffix = '_html' === $suffix;

			} else if ( is_callable( $callable = array( $model, $method_name ) ) && method_exists( $model, $method_name ) )  {

				/*
				 * Check $model to see if the $method_name exists.
				 */
				$value = call_user_func_array( $callable, $args );

				$has_html_suffix = false;

			} else {

				/*
				 * Not found, throw an error.
				 * $match[0] should have original $method_name
				 */
				$class_name = is_object( $view ) ? get_class( $view ) : $view;

				$message = sprintf( __( 'Method %s not found for class %s.', 'wplib' ), $match[ 0 ], $class_name );

				WPLib::trigger_error( $message, E_USER_ERROR );

				$has_html_suffix = false;

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
	 * @return int
	 */
	static function max_posts_per_page() {

		return 999;

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

			foreach( WPLib::site_classes() as $class_name ) {

				if ( is_subclass_of( $class_name, 'WPLib_Theme_Base' )  ) {

					/*
					 * Will create instance of FIRST class found that subclasses WPLib_Theme_Base.
					 * That means sites should ONLY have ONE subclass of WPLib_Theme_Base.
					 */
					self::$_theme = new $class_name;
					break;

				}

			}

		}
		if ( ! self::$_theme ) {

			self::$_theme = new WPLib_Theme_Default();

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
	 * @param $type
	 * @param $constant_name
	 * @param $base_class
	 *
	 * @return string[]
	 */
	static function _get_child_classes( $type, $constant_name, $base_class ) {

		if ( ! ( $child_classes = WPLib::cache_get( $cache_key = "{$type}_classes" ) ) ) {

			$child_classes = array();

			foreach ( self::site_classes() as $class_name ) {

				do {

					if ( ! is_subclass_of( $class_name, $base_class ) ) {
						continue;
					}

					if ( ! is_null( $constant_value = WPLib::get_constant( $constant_name, $class_name ) ) ) {
						continue;
					}

					$child_classes[ $constant_value ] = $class_name;

				} while ( false );

			}

			WPLib::cache_set( $cache_key, $child_classes );

		}

		return $child_classes;

	}

	/**
	 * @return bool|string
	 */
	static function short_prefix() {

		return WPLib::get_constant( 'SHORT_PREFIX', get_called_class() );

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



}
WPLib::on_load();
