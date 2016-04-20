<?php
namespace Fidor\SDK;

class Authorization {
    
    /**
     * Configuration
     * 
     * @var \Fidor\SDK\Config 
     */
    protected $config;
    
    /**
     * User agent used in HTTP requests
     * 
     * @var string 
     */
    protected $user_agent = 'FidorSDK (+https://docs.fidor.de)';
    
    /**
     *
     * @var integer 
     */
    protected $connect_timeout = 15;
    
    /**
     *
     * @var integer 
     */
    protected $timeout = 60;
    
    /**
     * Stores last request log
     * 
     * @var string 
     */
    protected static $debug_log;
    
    /**
     *
     * @var boolean 
     */
    protected $debug_enabled = false;
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     * @param string $redirect_uri
     */
    public function __construct( Config $config ) {
        $this->config = $config;
    }
    
    /**
     * Turn debugging on
     * 
     * @return \Fidor\SDK\Authorization
     */
    public function enableDebug( ) {
        $this->debug_enabled = true;
        return $this;
    }
    
    /**
     * Turn debugging off
     * 
     * @return \Fidor\SDK\Authorization
     */
    public function disableDebug( ) {
        $this->debug_enabled = false;
        return $this;
    }
    
    /**
     * Defines the HTTP user agent string used in requests 
     * 
     * @param string $userAgent
     * @return \Fidor\SDK\Authorization
     */
    public function setUserAgent( $userAgent ) {
        $this->user_agent = $userAgent;
        return $this;
    }
    
    /**
     * Sets connection timeout
     * 
     * @param integer $connectTimeout
     * @return \Fidor\SDK\Authorization
     */
    public function setConnectTimeout( $connectTimeout ) {
        $this->connect_timeout = $connectTimeout;
        return $this;
    }
    
    /**
     * 
     * @param integer $timeout
     * @return \Fidor\SDK\Authorization
     */
    public function setTimeout( $timeout ) {
        $this->timeout = (int) $timeout;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRequestUrl( $redirect_uri = null, $state = null ) {
        return sprintf( '%s/authorize?response_type=code&client_id=%s&state=%s&redirect_uri=%s',  $this->config->getOAuthUrl(), $this->config->getClientId(), $state, urlencode( $redirect_uri ) );
    }
    
    /**
     * 
     * @return string
     */
    public function getTokenUrl( ) {
        return $this->config->getOAuthUrl() . '/token';
    }
    
    /**
     * 
     * @return string
     */
    public function getRevokeTokenUrl( ) {
        return $this->config->getOAuthUrl() . '/revoke';
    }
    
    /**
     * Redirect user browser to authorization server.
     * 
     * @param string $state
     */
    public function start( $state = null ) {
        $state = $state ? $state : self::makeState();
        $auth_url = $this->getRequestUrl( $this->config->getCallbackUrl(), $state );        
        header( 'Location: ' . $auth_url );
        exit;
    }
    
    /**
     * Request access token after authorization was grantd by resource owner
     * 
     * @param string $code
     * @return string
     */
    public function finish( $code ) {
        $json = $this->http_post( 
                    $this->getTokenUrl(), 
                    sprintf( 'client_id=%s&client_secret=%s&code=%s&redirect_uri=%s&grant_type=authorization_code', $this->config->getClientId(), $this->config->getClientSecret(), $code, urlencode( $this->config->getCallbackUrl() ) ),
                    array( 'auth' => true, 'auth_scheme' => 'Bearer', )
                );
        
        return json_decode( $json, true );
    }
    
    /**
     * Refresh access token
     * 
     * @param string $refresh_token
     * @return string
     */
    public function refresh( $refresh_token = null ) {
        $json = $this->http_post( 
                    $this->getTokenUrl(), sprintf( 'grant_type=refresh_token&refresh_token=%s', $refresh_token ? $refresh_token : $this->config->getRefreshToken() ),
                    array( 'auth' => true, ) 
                );
        
        return json_decode( $json, true );
    }
    
    /**
     * Revoke both access and refresh tokens. By default, it revokes access token.
     * 
     * @param string|null $token
     * @return string
     */
    public function revoke( $token = null ) {
        $json = $this->http_post( 
                    $this->getRevokeTokenUrl(), 
                    sprintf( 'token=%s', $token ? $token : $this->config->getAccessToken() ),
                    array( 'auth' => true, ) 
                );
        
        return json_decode( $json, true );
    }
    
    protected function http_post( $url, $data, $options = array() ) {
        if ( function_exists( 'curl_init' ) ) {
            $headers = array( 'Content-Type: application/x-www-form-urlencoded' );
            
            $ch = curl_init( $url );
            
            if ( array_key_exists( 'auth', $options ) && true === $options['auth'] ) {
                $scheme = ! empty( $options['auth_scheme'] ) ? $options['auth_scheme'] : 'Basic';
                $token  = base64_encode( $this->config->getClientId() . ":" . $this->config->getClientSecret() );
                $headers[] = sprintf( 'Authorization: %s %s', $scheme, $token );
            }
            
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            
            if ( preg_match( '/^https:/is', trim( $url ) ) ) {
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
            }
            
            /**
             * In case we're debugging
             */
            $fh = null;
            if ( $this->debug_enabled ) {
                curl_setopt( $ch, CURLOPT_VERBOSE, true );
                $fh = fopen( 'php://temp', 'w+' );
                curl_setopt( $ch, CURLOPT_STDERR, $fh );
            }
            
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout );
            curl_setopt( $ch, CURLOPT_TIMEOUT, $this->timeout );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_USERAGENT, $this->user_agent );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
            
            $resp = curl_exec( $ch );
            
            /**
             * Debugging was enabled
             */
            if ( $this->debug_enabled ) {
                rewind( $fh );
                self::$debug_log = ( $data ? "sent data: \n" . ( is_array( $data ) ? print_r( $data, true ) : $data ) : '' ) . "\n\nHTTP logs:\n" . stream_get_contents( $fh );
            }
            
            curl_close( $ch );
            
            return $resp;
        } else {
            $headers = "Content-type: application/x-www-form-urlencoded\r\n";
            
            if ( array_key_exists( 'auth', $options ) && true === $options['auth'] ) {
                $scheme = ! empty( $options['auth_scheme'] ) ? $options['auth_scheme'] : 'Basic';
                $token  = base64_encode( $this->config->getClientId() . ":" . $this->config->getClientSecret() );
                $headers .= sprintf( "Authorization: %s %s\r\n", $scheme, $token );
            }
            
            $options = array(
                'http' => array(
                    'header'  => $headers,
                    'method'  => 'POST',
                    'content' => $data,
                ),
            );
            $context  = stream_context_create( $options );
            return file_get_contents( $url, false, $context );
        }
    }
    
    /**
     * 
     * @return string
     */
    public static function getDebugLog( ) {
        return self::$debug_log;
    }
    
    /**
     * Generate a random string for use as state parameter
     * 
     * @return string
     */
    public static function makeState( ) {
        return md5( time() . rand( 1000000, 9999999 ) );
    }
}
