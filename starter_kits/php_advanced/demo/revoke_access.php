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
 * 
 */
$config = Fidor\Config::fromArray( $settings );

/**
 * Add oauth parameters ( access token, refresh token and expiration time ) to config
 */
$config->setAccessToken( $_SESSION['oauth']['access_token'] )
       ->setRefreshToken( $_SESSION['oauth']['refresh_token'] )
       ->setExpiresIn( $_SESSION['oauth']['expires_in'] + $_SESSION['oauth']['auth_at'] );

/*if ( $config->hasTokenExpired() ) {
    header( 'Location: index.php' );
    exit;
}*/

$auth = new Fidor\Authorization( $config );
$auth->revoke();
header( 'Location: index.php' );
exit;
