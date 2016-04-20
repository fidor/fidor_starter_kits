<?php
namespace Fidor\SDK;

class Customers extends Client {

    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    /**
     * 
     * @param integer $id
     * @return string
     */
    public function get( $id = null ) {
        $data = parent::get( $this->config->getApiUrl() . '/customers' );
        if ( ! empty( $data['data'] ) ) {
            $data = $data['data'];
        }
        return $data;
    }
    
}
