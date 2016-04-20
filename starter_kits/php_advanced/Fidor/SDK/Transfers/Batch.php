<?php
namespace Fidor\SDK;

class Transfers_Batch extends Client {
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    public function get( ) {
        return parent::get( $this->config->getApiUrl() . '/batch_transfer' );
    }
    
}