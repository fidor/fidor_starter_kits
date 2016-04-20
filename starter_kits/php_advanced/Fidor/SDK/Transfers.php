<?php
namespace Fidor\SDK;

/**
 * @property \Fidor\SDK\Transfers_Internals $internal Internal transfers object
 */
class Transfers extends Client {
    
    /**
     *
     * @var integer 
     */
    protected $id;
    
    /**
     *
     * @var array 
     */
    protected $types = array( 'batch', 'global', 'internal', 'sepa' => array( 'SEPA' ) );
    
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
     * @return \Fidor\SDK\Transfers
     */
    public function setId( $id ) {
        $this->id = $id;
        return $this;
    }
    
    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        if ( in_array( $name, $this->types ) ) {
            $name = 'Transfers_' . ucfirst( $name );
        } elseif ( in_array( $name, array_keys( $this->types ) ) ) {
            $name = 'Transfers_' . current( $this->types[ $name ] );
        }
        
        return parent::__get( $name );
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
