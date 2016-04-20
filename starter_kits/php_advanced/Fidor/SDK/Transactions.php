<?php
namespace Fidor\SDK;

class Transactions extends Client {
    
    /**
     * 
     * @param \Fidor\SDK\Config $config
     */
    public function __construct( Config $config ) {
        parent::__construct( $config );
    }
    
    /**
     * 
     * @param integer|null $id
     * @param integer|null $page
     * @param integer|null $per_page
     * @return string
     */
    public function get( $id = null, $page = null, $per_page = null ) {
        $url = $this->config->getApiUrl() . '/transactions';
        if ( $id ) {
            $url .= '/' . $id;
        } elseif ( is_numeric( $page ) OR is_numeric( $per_page ) ) {
            $url .= '/?page=' . ( $page ? $page : 1 ) . '&per_page=' . ( $per_page ? $per_page : 10 );
        }
        $data = parent::get( $url );
        if ( ! empty( $data['data'] ) ) {
            $data = $data['data'];
        }
        return $data;
    }
    
    
    /**
     * 
     * @return string
     */
    public static function make_external_uid(){
        if ( function_exists('com_create_guid') === true ) {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    
}
