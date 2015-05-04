<?php

/**
 * Class WPLib - Core class
 *
 * @mixin WPLib_Posts
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
	 * @var array List of files that must be loaded on every page load.
	 */
	private static $_mustload_files = array( 10 => array() );

	/**
	 * @var array List of classes (as key) and filepaths (as value) to autoload.
	 */
	private static $_autoload_files = array();

	/**
	 * @var bool Get's set if doing XMLRPC.
	 */
	private static $_doing_xmlrpc = false;

	/**
	 *
	 */
	static function on_load() {

		if ( defined( 'WPLIB_RUNMODE' ) ) {

			self::set_runmode( WPLIB_RUNMODE );

		}

		spl_autoload_register( array( __CLASS__, '_autoloader' ) );

		self::register_module( 'posts' );
		self::register_module( 'terms' );
		self::register_module( 'post-posts' );
		self::register_module( 'page-posts' );
		self::register_module( 'categories' );
		self::register_module( 'post-tags' );
		self::register_module( 'people' );

		self::add_class_action( 'muplugins_loaded', 11 );
		self::add_class_action( 'after_setup_theme' );
		self::add_class_action( 'xmlrpc_call' );

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
			unset( self::$_autoload_files[ $class_name ] );

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
	private static function _maybe_make_absolute( $filepath, $dir = false ) {

		if ( '/' != $filepath[0] ) {

			if ( preg_match( '#^~(/.*)$#', $filepath, $match ) ) {

				$path = $match[1];

			} else {

				$path = '/' . ltrim( $filepath, '/' );

			}

			$filepath = $dir ? "{dir}{$path}" : static::get_root_dir( $path );

		}

		return $filepath;

	}

	/**
	 * Load all necessary files. This includes items, and mustload files.
	 */
	static function _muplugins_loaded_11() {

		self::_load_necessary_files();

	}

	/**
	 * Now load the theme's modules.
	 */
	static function _after_setup_theme() {

		self::_load_necessary_files();

	}

	/**
	 * Load all necessary files, i.e. modules and mustload files.
	 */
	private static function _load_necessary_files() {

		self::_find_autoload_files();
		self::_load_modules();
		self::_load_mustload_files();
		self::_find_autoload_files();

	}

	/**
	 * Load all registered modules, by priority
	 */
	private static function _load_modules() {

		ksort( self::$_modules );

		self::$_modules = apply_filters( 'wplib_modules', self::$_modules );

		foreach ( self::$_modules as $priority ) {

			foreach ( $priority as $filepath ) {

				require_once $filepath;

			}

		}

		self::$_modules = array();

	}

	/**
	 * @param array $array
	 *
	 * @return array
	 *
	 * @see http://stackoverflow.com/a/1320156/102699
	 */
	private static function _flatten_array( array $array ) {

		$return = array();

		array_walk_recursive( $array, function( $a ) use ( &$return ) { $return[] = $a; } );

		return $return;

	}

	/**
	 * Load all registered mustload files, by priority
	 */
	private static function _load_mustload_files() {

		static::_find_mustload_files();

		ksort( self::$_mustload_files );

		self::$_mustload_files = apply_filters( 'wplib_mustload_files', self::$_mustload_files );

		foreach ( self::$_mustload_files as $priority ) {

			foreach ( $priority as $dir => $files ) {

				foreach ( $files as $file ) {

					require_once static::_maybe_make_absolute( $file, $dir );

				}

			}

		}

		self::$_mustload_files = array( 10 => array() );

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
	 * Scan directory of mustload files
	 */
	private static function _find_mustload_files() {
		static $classes = array();

		$classes = array_diff( static::component_classes(), $classes );

		$class_key = implode( '|', $classes );

		$class_key = WPLib::is_production() ? md5( $class_key ) : $class_key;

		if ( ! ( $new_files = static::cache_get( $cache_key = "mustload_files[{$class_key}]" ) ) ) {

			$mustload_files = array();

			foreach( $classes as $class_name ) {

				/**
				 * Scan the includes directory for all files.
				 */
				$found_files = glob( $mustload_dir = static::get_root_dir( 'core', $class_name ) . '/*.php' );

				if ( 0 == count( $found_files ) ) {

					continue;

				}

				$mustload_files += $found_files;

			}
			/**
			 * Flatten array of mustload files; get rid of priority so we can have a complete list to compare.
			 */
			$added_files = static::_flatten_array( self::$_mustload_files );

			/**
			 * Diff the manually added files with the new ones scanned to get new files.
			 */
			$new_files = array_diff( $mustload_files, $added_files );

			/**
			 * Now stuff into cache
			 */
			static::cache_set( 'mustload_files', $new_files );

		}
		/**
		 * Add these new files to the list of files to mustload at the default priority.
		 */
		self::$_mustload_files[ 10 ] = array_merge( self::$_mustload_files[ 10 ], $new_files );

	}

	/**
	 * Scan registered autoload files, by priority
	 */
	private static function _find_autoload_files() {
		static $class_count = 0;

		$classes = static::component_classes();

		$latest_classes = array_slice( $classes, $class_count );

		if ( count( $latest_classes ) ) {

			$class_count += count( $latest_classes );

			$class_key = implode( '|', $latest_classes );

			$class_key = WPLib::is_production() ? md5( $class_key ) : $class_key;

			if ( ! ( $new_files = static::cache_get( $cache_key = "autoload_files[{$class_key}]" ) ) ) {

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
					$new_files = array_diff( $found_files, $added_files );

					/**
					 * Load all the scanned files from the /include/ directory
					 */
					foreach( $new_files as $filepath ) {

						require( $filepath );

					}

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
				static::cache_set( 'autoload_files', $autoload_files );

			}

			if ( isset( $autoload_files ) ) {

				/**
				 * Add these new files to the list of files to autoload at the default priority.
				 */
				self::$_autoload_files = array_merge( self::$_autoload_files, $autoload_files );

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
	 * Register all files that must be loaded on page load
	 *
	 * @param string[] $mustload_files Simple array of files to load where value is relative filepath.
	 * @param int $priority
	 */
	static function register_mustload_files( $mustload_files, $priority = 10 ) {

		foreach ( $mustload_files as $mustload_file ) {

			self::register_mustload_file( $mustload_file, $priority );

		}

	}

	/**
	 * Register a file that must be loaded on page load
	 *
	 * @param string $mustload_file File path to load; filepath should be relative to
	 * @param int $priority Priority of file to load.
	 */
	static function register_mustload_file( $mustload_file, $priority = 10 ) {

		self::$_mustload_files[ $priority ][] = $mustload_file;

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

		return self::DEVELOPMENT == self::$_runmode;

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

		$hook = "_{$action}" . ( 10 != $priority ? "_{$priority}" : '' );
		add_action( $action, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $filter
	 * @param int $priority
	 */
	static function add_class_filter( $filter, $priority = 10 ) {

		$hook = "_{$filter}" . ( 10 != $priority ? "_{$priority}" : '' );
		add_filter( $filter, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $action
	 * @param int $priority
	 */
	static function remove_class_action( $action, $priority = 10 ) {

		$hook = "_{$action}" . ( 10 != $priority ? "_{$priority}" : '' );
		remove_action( $action, array( get_called_class(), $hook ), $priority, 99 );

	}

	/**
	 * @param string $filter
	 * @param int $priority
	 */
	static function remove_class_filter( $filter, $priority = 10 ) {

		$hook = "_{$filter}" . ( 10 != $priority ? "_{$priority}" : '' );
		remove_filter( $filter, array( get_called_class(), $hook ), $priority, 99 );

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
					if ( is_callable( $callable = array( $helper, $helper_method ) ) ) {

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

		$filepath = '/' . ltrim( $filepath, '/' );

		$reflector = new ReflectionClass( $class_name );

		return dirname( $reflector->getFileName() ) . $filepath;

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

			$root_dir = static::get_root_dir( $filepath, $class_name );

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

			self::$_root_urls[ $class_name ] = $root_url;

		}

		return self::$_root_urls[ $class_name ];

	}

	/**
	 * Return the asset path for a relative
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

	 	return static::get_root_url( $class_name ) . "/assets/{$asset_path}";

	}

	/**
	 * @param string $module
	 * @param int $priority
	 */
	static function register_module( $module, $priority = 10 ) {

		self::$_modules[ $priority ][] = static::root_dir() . "/modules/{$module}/{$module}.php";

	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return mixed
	 */
	static function cache_get( $key, $group = '' ) {

		$cache = false;
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
	static function do_log_errors() {

		return defined( 'WPLIB_LOG_ERRORS' ) && WPLIB_LOG_ERRORS;

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
	 */
	static function trigger_error( $error_msg, $error_type = E_USER_NOTICE ) {

		if ( ! static::doing_ajax() && ! static::doing_xmlrpc() && ! static::doing_cron() ) {

			trigger_error( $error_msg, $error_type );

		} else if ( static::is_development() || static::do_log_errors() ) {

			/**
			 * ONLY triggers errors:
			 *      IF runmode() == WPLib::DEVELOPMENT
			 *      OR define( 'WPLIB_LOG_ERRORS', true ) in /wp-config.php.
			 *
			 * For runmode() == WPLib::DEVELOPMENT define( 'WPLIB_RUNMODE', 0 ) in /wp-config.php.
			 */
			error_log( "[{$error_type}] {$error_msg}" );

		}

	}

	/**
	 * Return a class constant for the called class.
	 *
	 * @param string      $constant_name
	 * @param string|bool $class_name
	 *
	 * @return mixed|null
	 */
	static function constant( $constant_name, $class_name = false ) {

		if ( ! $class_name ) {

			$class_name = get_called_class();

		}

		return defined( $constant_ref = "{$class_name}::{$constant_name}" ) ? constant( $constant_ref ) : null;

	}

	/**
	 * @param string $template
	 * @param array $_template_vars
	 * @param WPLib_Entity_Base|object $entity
	 */
	static function the_template( $template, $_template_vars = array(), $entity = null ) {


		$_filename = preg_replace( '#(\.php)$#', '', ltrim( $template, '/' ) ) . '.php';

		$template = new stdClass();
		$template->dir = get_stylesheet_directory();
		$template->filename = "{$template->dir}/{$_filename}";

		if ( ! is_file( $template->filename )  ) {

			$template->dir = static::get_root_dir( 'templates' );

			$template->filename = "{$template->dir}/{$_filename}";

			if ( ! is_file( $template->filename ) ) {

				$template->filename = $template->dir = false;

				if ( ! WPLib::is_production() ) {

					/**
					 * This is ONLY output if constant 'WPLIB_RUNMODE' is defined in wp-config.php.
					 */
					echo "\n<!--[FAILED Tags Template File: {$template->filename} -->\n";

				}
			}

		}

		if ( ! $template->filename ) {

			$output = false;

		} else {

			if ( ! WPLib::doing_ajax() && ! WPLib::is_production() ) {

				echo "\n<!--[Tags Template File: {$template->filename} -->\n";

			}

			extract( $_template_vars, EXTR_PREFIX_SAME, '_' );

			if ( $entity && ( $_var_name = WPLib::constant( 'VAR_NAME', get_class( $entity ) ) ) ) {
				/*
				 * Assign the $entity's preferred variable name in addition to '$entity', i.e. '$brand'
				 * This is a very controlled use of extract() i.e. I know what I am doing here.
				 */
				extract( array( $_var_name => $entity ) );
			}

			$template->vars = $_template_vars;

			unset( $_template_vars, $_filename, $_cache_key, $_var_name );

			ob_start();

			require( $template->filename );

			$output = ob_get_clean();

		}

		echo $output;

	}

}
WPLib::on_load();
