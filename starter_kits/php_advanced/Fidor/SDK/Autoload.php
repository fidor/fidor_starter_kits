<?php
namespace Fidor\SDK;

class Autoload {
    
    /**
     * Register class autoloader
     */
    public static function register() {
        spl_autoload_register( array( __CLASS__, 'load' ) );
    }
    
    /**
     * Load class
     * 
     * @param string $class
     */
    public static function load( $class ) {
        // load only Fidor classes
        if ( 'Fidor\\' === substr( $class, 0, 6 ) ) {         
            // turn namespace separator and _ to directory separtor
            $file_path = __DIR__ . '/' . preg_replace( '/[\\_]/', DIRECTORY_SEPARATOR, substr( $class, strrpos( $class, '\\' ) + 1 ) ) . '.php';
            
            // load class file if exists
            if ( is_readable( $file_path ) ) {
                require_once $file_path;
            }
        }
    }
    
}

Autoload::register();
