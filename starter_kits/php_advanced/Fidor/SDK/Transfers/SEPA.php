<?php
namespace Fidor\SDK;

class Transfers_SEPA extends Client {
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    public function get( ) {
        return parent::get( $this->config->getApiUrl() . '/sepa_credit_transfers' );
    }
    
}