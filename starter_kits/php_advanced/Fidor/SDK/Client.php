<?php
namespace Fidor\SDK;

/**
 * @property \Fidor\SDK\Users $users Get users resource
 * @property \Fidor\SDK\Accounts $accounts Get accounts resource
 * @property \Fidor\SDK\Customers $customers Get customers resource
 * @property \Fidor\SDK\Transactions $transactions Get transactions resource
 * @property \Fidor\SDK\Transfers $transfers Get transfers resource
 * 
 */
class Client {

    /**
     *
     * @var Config 
     */
    protected $config;
    
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
     *
     * @var string 
     */
    protected $user_agent = 'FidorSDK (+https://docs.fidor.de)';
    
    /**
     *
     * @var string 
     */
    protected static $debug_log;
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        $this->config = $config;
    }
    
    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        $class = __NAMESPACE__ . '\\' . ucfirst( $name );
        if ( class_exists( $class ) && is_subclass_of( $class, __CLASS__ ) ) {
            $obj = new $class( $this->config );
            return $obj;
        }
    }
    
    /**
     * 
     * @param string $url
     * @return string
     */
    public function get( $url ) {
        return $this->request( 'get', $url );
    }
    
    /**
     * 
     * @param string $url
     * @param string|array $data
     * @return string
     */
    public function post( $url, $data ) {
        return $this->request( 'post', $url, $data );
    }
    
    /**
     * 
     * @param string $url
     * @param string|array $data
     * @return string
     */
    public function put( $url, $data ) {
        return $this->request( 'put', $url, $data );
    }
    
    /**
     * 
     * @param string $url
     * @param string|array $data
     * @return string
     */
    public function patch( $url, $data ) {
        return $this->request( 'patch', $url, $data );
    }
    
    /**
     * 
     * @param string $url
     * @return string
     */
    public function delete( $url ) {
        return $this->request( 'put', $url );
    }
    
    /**
     * 
     * @param string $method
     * @param string $url
     * @param string|null $data
     */
    public function request( $method, $url, $data = null ) {
        if ( function_exists( 'curl_init' ) ) {
            return $this->curl_request( $method, $url, $data );
        } else {
            return $this->stream_request( $method, $url, $data );
        }
    }
    
    /**
     * 
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @return string
     */
    protected function curl_request( $method, $url, $data = null ) {
        $method = trim( strtoupper( $method ) );
        
        /**
         * The empty Expect headers removes HTTP 100-continue mechanism
         */
        $headers = array(
                        'Accept: application/vnd.fidor.de; version=1,text/json',
                        'Authorization: Bearer ' . $this->config->getAccessToken(),
                        'Expect: ',
                    );
        
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_HEADER, false );

        if ( 'POST' === $method ) {
            curl_setopt( $ch, CURLOPT_POST, true );
        } elseif ( in_array( $method, array( 'HEAD', 'PUT', 'PATCH', 'DELETE' ) ) ) {
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
        }
        
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        if ( preg_match( '/^https:/is', trim( $this->uri ) ) ) {
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
        }
        
        // debugging
        curl_setopt( $ch, CURLOPT_VERBOSE, true );
        $fh = fopen( 'php://temp', 'w+' );
        curl_setopt( $ch, CURLOPT_STDERR, $fh );
        // -debugging
        
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $this->timeout );

        if ( ! empty( $data ) ) {
            if ( is_array( $data ) ) {
                $data = json_encode( $data );
            }
            if ( in_array( $method, array( 'PUT', 'PATCH', 'DELETE' ) ) ) {
                 curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $data );
            } else {
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            }

            $headers[] = 'Content-Type: application/json';
        }
        
        if ( $this->user_agent ) {
            curl_setopt( $ch, CURLOPT_USERAGENT, $this->user_agent );
        }
        
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        
        $result = curl_exec( $ch );
        
        $request_info = curl_getinfo( $ch );
        
        rewind( $fh );
        self::$debug_log = ( $data ? "SENDING DATA: \n" . ( is_array( $data ) ? print_r( $data, true ) : $data ) . "\n\n" : '' ) . stream_get_contents( $fh ) . "\n\nRESPONSE:\n" . $result;
        
        curl_close( $ch );
        
        return json_decode( $result, true );
    }
    
    /**
     * 
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @return string
     */
    protected function stream_request( $method, $url, $data = null ) {
        $headers = "Accept: application/vnd.fidor.de; version=1,text/json\r\n";
        $headers .= "Authorization: Bearer " . $this->config->getAccessToken() . "\r\n";  
        $headers .= "Expect: \r\n"; 

        $options = array();
        $options['http']['method'] = $method;
        
        if ( ! empty( $data ) ) {
            if ( is_array( $data ) ) {
                $data = json_encode( $data );
            }
            $headers .= "Content-Type: application/json\r\n"; 
            $options['http']['content'] = $data;
        }
        
        $options['http']['header'] = $headers;
        
        $context  = stream_context_create( $options );
        return file_get_contents( $url, false, $context );
    }
    
    /**
     * 
     * @return string
     */
    public static function get_debug_log( ) {
        return self::$debug_log;
    }
    
}
