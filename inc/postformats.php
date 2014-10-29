<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemePostFormats extends gThemeModuleCore {

	function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'cleanup' => true,
		), $args ) );
		
		if ( $cleanup ) {
		}
	}
	
}

// http://digwp.com/2013/04/post-format-archives-widget/