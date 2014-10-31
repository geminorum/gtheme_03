<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeTemplate extends gThemeModuleCore {

	function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'cleanup' => true,
		), $args ) );
		
		if ( $cleanup ) {}
	}
	
	public static function logo( $context = 'header' )
	{
		printf( gtheme_get_info( 'template_logo', 
			'<a class="navbar-brand no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>' ), 
				gThemeUtilities::home(),
				get_bloginfo( 'name' ),
				esc_attr__( 'Home', GTHEME_TEXTDOMAIN )
		);
	}
}
