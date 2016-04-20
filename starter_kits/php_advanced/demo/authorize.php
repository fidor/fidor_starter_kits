<?php
/**
 * Startup
 */
require_once 'setup.php';

/**
 * Alias Fidor SDK namespace
 */
use Fidor\SDK as Fidor;

/**
 * Load Fido API settings
 */
$settings = include 'config.php';

/**
 * Create Config instance. Check Config class for alternative calls.
 */
$config = Fidor\Config::fromArray( $settings );

/**
 * Prepare Authorization
 */
$auth = new Fidor\Authorization( $config );

/**
 * We received authorization response from server. Validate state before proceeding.
 */
if ( ! empty( $_REQUEST['code'] ) && ! empty( $_REQUEST['state'] ) && $_REQUEST['state'] === $_SESSION['state'] ) {
    
    /**
     * Request token from authorization server
     */
    $resp = $auth->finish( $_REQUEST['code'] );
    //print_r( $resp );
    //die();
    
    /**
     * Redirect to dashboard if authorization went through otherwise report error
     */
    if ( ! empty( $resp['access_token'] ) ) {
        /**
         * Save authorization time to calculate token expiration
         */
        $resp['auth_at']    = time();
        
        /**
         * Save authoriation data to session (access token, refresh token, etc...)
         */
        $_SESSION['oauth']  = $resp;
        
        /**
         * We're removing state from session here but when using a database for example
         * you would keep it for some time and ensure it's not used.
         * 
         */
        unset( $_SESSION['state'] );
        
        /**
         * Redirect to demo dashboard in order for the user to test the authenticated calls
         */
        header( 'Location: dashboard.php' );
        
    }
    elseif ( ! empty( $resp['error'] ) ) {
        header( 'Location: error_auth.php?error=' . $resp['error'] );
    }
    
    exit;
    
} elseif ( ! empty( $_REQUEST['error'] ) ) {
    
    /**
     * User might have denied authorization request.
     */
    
    header( 'Location: error_auth.php?error=' . $_REQUEST['error'] );
    
} else {
    
    /**
     * Application is requesting resource access through OAuth.
     */
    
    /**
     * Generate state and save it to session. You will usually keep the state
     * in a persistent storage (e.g.: database) for some time to prevent reuse. 
     */
    $_SESSION['state'] = Fidor\Authorization::makeState( );
    
    if ( ! empty( $_REQUEST['action'] ) ) {
        $_SESSION['state'] = base64_encode( $_SESSION['state'] . '|' . $_REQUEST['action'] ); 
    }
    
    /**
     * Build authorization request and redirect end user to authorization server.
     * 
     * Authorization server will return the passed state code to us after user has 
     * granted access. We will then use this code to ensure we initiated the request.
     */
    $auth->start( $_SESSION['state'] );
}
