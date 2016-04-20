<?php
namespace Fidor\SDK;

class Transfers_Global extends Client {
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    public function get( ) {
        return parent::get( $this->config->getApiUrl() . '/global_money_transfers' );
    }
    
}