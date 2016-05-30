<?php

$path = __DIR__;
while ( ! empty( $path ) ) {

    if ( is_file( $file = "{$path}/wp-load.php" ) || is_file( $file = "{$path}/wp/wp-load.php" ) ) {
        break;
    }

    $path = dirname( $path );

}

if ( empty( $path ) ) {
    echo "\nFailed to find root.\n";
    exit;
}
$dir  = new RecursiveDirectoryIterator($path);
$files  = new RecursiveIteratorIterator($dir);
echo '<pre>';
foreach ($files as $file) {
    /**
     * If a file is a .php file in an /includes/ directory inside a /modules/directory
     * and it is either an mu-plugin or in the the Composer /vendor/ directory then
     * it is a file we might want to change the name of
     */
    if ( ! is_file( $file ) ) {
        continue;
    }

    if ( is_git_dir( $file ) ) {

        continue;
    }

    if ( is_vendor_composer_file( $file ) ) {

        continue;
    }

    if ( ! is_php_in_correct_path( $file ) ) {

        continue;
    }

    $class_name = get_php_class_name( $file );

    if ( is_null( $class_name ) ) {
        continue;
    }

    $path = dirname( $file );
    $newfile = "{$path}/{$class_name}.php";

    if ( ! is_base_class( $file ) && is_wplib_file_type( $file, "Module") ) {

        strip_call_to_on_load( $file );
        continue;

    } else {

        $process_file = false;

        if ( is_wplib_app_main_file( $file ) ) {

            strip_call_to_on_load( $file );
            continue;

        } else if ( is_wplib_app_include_file( $file ) ) {

            $process_file = true;

        } else if ( is_wplib_module_include_file( $file ) ) {

            $process_file = true;

        }

        if ( ! $process_file ) {
            continue;
        }

    }


    $cmd = "/usr/local/bin/git mv {$file} {$newfile}";

    echo "{$cmd}\n";
}
echo '</pre>';



/**
 * @param string $file
 * @return bool
 */
function is_git_dir( $file ) {
    return false !== strpos( $file, '/.git/' );
}

/**
 * @param string $file
 * @return bool
 */
function is_vendor_composer_file( $file ) {
    return (bool) preg_match( '#/vendor/composer/#', $file );
}

/**
 * @param string $file
 */
function is_php_in_correct_path( $file ) {
    return (bool) preg_match( '#(/vendor/|/mu-plugins/)(.+)\.php$#', $file );
}

/**
 * @param string $file
 * @return string|null
 */
function get_php_class_name( $file ) {
    $php_code = file_get_contents( $file );
    preg_match( "#^.*\n\s*(abstract|final)?\s*class\s*([A-Za-z_][A-Za-z0-9_]*)\s*.*$#ms", $php_code, $matches );
    return ! empty( $matches[2] )
        ? $matches[2]
        : null;
}

/**
 * @param string $file
 * @return string
 */
function strip_call_to_on_load( $file ) {
    $class_name = get_php_class_name( $file );
    file_put_contents( $file, preg_replace( "#\n\s*{$class_name}::on_load\(\s*\);#", '', file_get_contents( $file ) ) );
}

/**
 * @param string $file
 * @param string $type
 * @return bool
 */
function is_wplib_file_type( $file, $type ) {
    $php_code = file_get_contents( $file );
    $is_file_type = preg_match( "#\n\s*(abstract|final)?\s*class\s+.+\s+extends\s+.+_{$type}.*?\s*\{#", $php_code, $matches );
    if ( ! $is_file_type ) {
        if ( $is_file_type = preg_match( "#\n\s*(abstract|final)?\s*class\s+WPLib\s*\{#", $php_code, $matches ) ) {
            $is_file_type = preg_match("#/wplib/wplib\.php#", $file);
        }
    }
    return $is_file_type;
}

/**
 * @param string $file
 * @return bool
 */
function is_base_class( $file ) {
    $php_code = file_get_contents( $file );
    $is_base_class = preg_match( "#\n\s*(abstract|final)?\s*class\s+.+_Base\s+#", $php_code, $matches );
    return $is_base_class;
}

/**
 * @param string $file
 * @return bool
 */
function is_wplib_module_include_file( $file ) {
    return (bool) preg_match( '#/modules/.+?/includes/[^/]+?\.php$#', $file );
}

/**
 * @param string $file
 * @return bool
 */
function has_static_on_load( $file ) {
    $php_code = file_get_contents( $file );
    return (bool) preg_match( "#\n\s*static\s+function\s+on_load\(\s*\)\s*\{#", $php_code );
}

/**
 * @param string $file
 * @return bool
 */
function is_wplib_app_include_file( $file ) {
    $is_include_file = false;
    $main_file = get_wplib_app_main_file( $file );
    if ( $main_file ) {
        $app_path = preg_quote( dirname( $main_file ) );
        $is_include_file = (bool) preg_match( "#^{$app_path}/includes/[^/]+?\.php$#", $file );
    }
    return $is_include_file;
}


/**
 * @param string $file
 * @return string
 */
function is_wplib_app_main_file( $file ) {
    return get_wplib_app_main_file( $file ) === "{$file}";
}


/**
 * @param string $file
 * @return string
 */
function get_wplib_app_main_file( $file ) {
    
    do {
        $main_file = false;
        $path = preg_match('#.+\.php$#', $file)
            ? dirname($file)
            : $file;
        $parts = explode( '/', rtrim( $path, '/' ) );
        $slug = array_pop( $parts );
        $test_file = "{$path}/{$slug}.php";
        if ( ! is_file( $test_file ) ) {
            break;
        }
        if ( ! is_wplib_file_type( $test_file, 'App') ) {
            break;
        }
        if ( ! has_static_on_load( $test_file ) ) {
            break;
        }
        $main_file = $test_file;
    } while ( false );

    if ( ! $main_file && '/' !== $file ) {
        $main_file = get_wplib_app_main_file( dirname( $file ) );
    }

    return $main_file;
}
