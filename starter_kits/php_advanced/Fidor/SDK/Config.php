<?php
namespace Fidor\SDK;

/**
 * @method Fidor\SDK\Config setAccessToken( string $access_token )
 * @method Fidor\SDK\Config setRefreshToken( string $refresh_token )
 * @method Fidor\SDK\Config setUserAgent( string $user_agent )
 * @method string getClientId()
 * @method string getClientSecret()
 * @method string getCallbackUrl()
 * @method string getAccessToken( )
 * @method integer getExpiresIn( )
 * @method string getRefreshToken( ) 
 * @method string getUserAgent( ) 
 */
class Config {
    
    /**
     *
     * @var string 
     */
    protected $client_id;
    
    /**
     *
     * @var string 
     */
    protected $client_secret;
    
    /**
     *
     * @var string 
     */
    protected $callback_url;
    
    /**
     *
     * @var boolean 
     */
    protected $sandbox;
    
    /**
     *
     * @var string 
     */
    protected $access_token;

    /**
     *
     * @var integer 
     */
    protected $expires_in;
   
    /**
     *
     * @var string 
     */
    protected $refresh_token;
    
    /**
     * The base string used to build endpoints. You can override specific
     * endpoints by providing values for oauth_url and/or api_url keys in the config array
     * 
     * @var string 
     */
    protected $base_uri = 'fidor.de';
    
    /**
     *
     * @var string 
     */
    protected $user_agent = 'FidorSDK (+https://docs.fidor.de)';
    
    /**
     *
     * @var string 
     */
    protected $api_url;
    
    /**
     *
     * @var string 
     */
    protected $oauth_url;

    /**
     * Constructor
     * 
     * @param string $client_id
     * @param string $client_secret
     * @param boolean $sandbox
     */
    public function __construct( $client_id, $client_secret, $callback_url, $sandbox = false ) {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->callback_url  = $callback_url;
        $this->sandbox       = (bool) $sandbox;
    }
    
    /**
     * Handle setters/getters
     * 
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call( $name, $args ) {
        if ( preg_match( '/^get(.*)/', $name, $m ) ) {
            $property = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $m[1] ) );
            if ( property_exists( $this, $property ) ) {
                return $this->$property;
            }
        } elseif ( preg_match( '/^set(.*)/', $name, $m ) ) {
            $property = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $m[1] ) );
            if ( property_exists( $this, $property ) ) {
                $this->$property = current( $args );
                return $this;
            }
        }
    }
    
    /**
     * 
     * @param integer $expires_in
     * @return \Fidor\SDK\Config
     */
    public function setExpiresIn( $expires_in ) {
        if ( preg_match( '/^\d+$/', $expires_in ) ) {
            $this->expires_in = $expires_in;
        }
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function hasTokenExpired( ) {
        if ( $this->expires_in < time() ) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return boolean
     */
    public function is_sandbox( ) {
        return $this->sandbox;
    }
    
    /**
     * 
     * @param string $url
     * @return \Fidor\SDK\Config
     */
    public function setOAuthUrl( $url ) {
        $this->oauth_url = $url;
        return $this;
    }
    
    /**
     * Get base endpoint to OAuth authorization server
     * 
     * @return string
     */
    public function getOAuthUrl( ) {
        // maybe use user provided OAuth url
        if ( ! empty( $this->oauth_url ) ) {
            return $this->oauth_url;
        }
        
        return 'https://' . ( $this->sandbox ? 'aps' : 'apm' ) . '.fidor.de/oauth';
    }
    
    /**
     * 
     * @param string $url
     * @return \Fidor\SDK\Config
     */
    public function setApiUrl( $url ) {
        $this->api_url = $url;
        return $this;
    }
    
    /**
     * Get base endpoint used for API calls
     * 
     * @return string
     */
    public function getApiUrl( ) {
        // maybe use user provided API url
        if ( ! empty( $this->api_url ) ) {
            return $this->api_url;
        }
        
        return 'https://' . ( $this->sandbox ? 'aps' : 'api' ) . '.fidor.de';
    }
    
    /**
     * Create config instance from array
     * 
     * @param array $info
     * @return \Fidor\SDK\Config
     */
    public static function fromArray( array $info ) {
        if ( ! empty( $info['client_id'] ) && ! empty( $info['client_secret'] ) ) {
            // default to sandbox
            if ( ! $info['sandbox'] ) {
                $info['sandbox'] = true;
            }
            
            $sandbox = false;
            $callback_url = null;
            
            if ( 1 == $info['sandbox'] OR 'yes' === $info['sandbox'] OR 'true' === $info['sandbox'] OR true === $info['sandbox'] ) {
                $sandbox = true;
            }
            
            if ( ! empty( $info['callback_url'] ) ) {
                $callback_url = $info['callback_url'];
            }
            
            $obj = new self( $info['client_id'],  $info['client_secret'], $callback_url, $sandbox );
            
            $props = array( 'user_agent', 'oauth_url', 'api_url' );
            foreach ( $props as $prop ) {
                if ( empty( $info[ $prop ] ) OR ! is_string( $info[ $prop ] ) ) {
                    continue;
                }
                
                $value = $info[ $prop ];
                if ( 'user_agent' === $prop ) {
                    $obj->setUserAgent( $value );
                } elseif ( 'oauth_url' === $prop ) {
                    $obj->setOAuthUrl( $value );
                } elseif ( 'api_url' === $prop ) {
                    $obj->setApiUrl( $value );
                }
            }
            
            return $obj;
        }
    }
    
    /**
     * Create a config instance from JSON string
     * 
     * @param string $json
     * @return \Fidor\SDK\Config
     */
    public static function fromJSON( $json ) {
        $info = json_decode( $json, true );
        if ( ! empty( $info['client_id'] ) && ! empty( $info['client_secret'] ) && isset( $info['sandbox'] ) ) {
            return self::fromArray( $info );
        }
    }
    
    /**
     * Create a config instance from JSON file
     * 
     * @param string $file
     * @return \Fidor\SDK\Config
     */
    public static function fromJSONFile( $file ) {
        if ( is_readable( $file ) ) {
            return self::fromJSON( file_get_contents( $file ) );
        }
    }
    
}
