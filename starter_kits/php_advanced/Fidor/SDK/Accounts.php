<?php
namespace Fidor\SDK;

/**
 * @property Transactions $transactions Get transactions resource
 * @property Transactions $transactions Get transfer resource
 */
class Accounts extends Client {

    /**
     *
     * @var string 
     */
    protected $id;
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        $obj = parent::__get( $name );
        return $obj;
    }
    
    /**
     * 
     * @param integer $id
     * @return string
     */
    public function get( $id = null ) {
        $data = parent::get( $this->config->getApiUrl() . '/accounts' );
        if ( ! empty( $data['data'] ) ) {
            $data = $data['data'];
        }
        return $data;
    }
    
}
