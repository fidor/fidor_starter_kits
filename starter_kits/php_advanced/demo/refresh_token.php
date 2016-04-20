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
       ->setRefreshToken( $_SESSION['oauth']['refresh_token'] );

$auth = new Fidor\Authorization( $config );
$resp = $auth->refresh();
if ( ! empty( $resp['access_token'] ) ) {
    $resp['auth_at']    = time();
    $_SESSION['oauth']  = $resp;
    header( 'Location: dashboard.php' );
} elseif ( ! empty( $resp['error'] ) ) {
    header( 'Location: error_auth.php?error=' . $resp['error'] );
}

exit;
