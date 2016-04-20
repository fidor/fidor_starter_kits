<?php
namespace Fidor\SDK;

class Users extends Client {

    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    public function current( ) {
        $data = $this->get( $this->config->getApiUrl() . '/users/current' );
        if ( ! empty( $data['data'] ) ) {
            $data = $data['data'];
        }
        return $data;
    }
    
}

