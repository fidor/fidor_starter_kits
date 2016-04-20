<?php
namespace Fidor\SDK;

class Transfers_Internal extends Client {
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    /**
     * Alias for post()
     * 
     * @param array $data
     * @return string
     */
    public function create( array $data ) {
        return $this->post( $this->config->getApiUrl() . '/internal_transfers', $data );
    }
    
    /**
     * 
     * @param integer $id
     * @return string
     */
    public function get( $id = null ) {
        return parent::get( $this->config->getApiUrl() . '/internal_transfers' . ( $id ? "/$id" : '' ) );
    }
    
}