<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeModuleCore 
{
	function __construct( $args = array(), $ajax_disable = true ) 
	{  
        if ( ( $ajax_disable && defined( 'DOING_AJAX' ) && constant( 'DOING_AJAX' ) )
            || ( defined( 'WP_INSTALLING' ) && constant( 'WP_INSTALLING' ) ) )
            return;
			
		$this->setup_actions( $args );	
	}
	
	public function setup_actions( $args = array() ) {}
	
}