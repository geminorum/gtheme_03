<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeModuleCore 
{
	var $_ajax = false;
	var $_args = array();
	
	function __construct( $args = array() ) 
	{  
        if ( ( ! $this->_ajax && self::ajax() )
            || ( defined( 'WP_INSTALLING' ) && constant( 'WP_INSTALLING' ) ) )
            return;
		
		$this->_args = $args;
		$this->setup_actions( $args );	
	}
	
	public function setup_actions( $args = array() ) {}
	
	public static function ajax() 
	{
		return ( defined( 'DOING_AJAX' ) && constant( 'DOING_AJAX' ) ) ? true : false;
	}
	
}