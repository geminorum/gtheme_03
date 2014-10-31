<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeTheme extends gThemeModuleCore {

	function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'cleanup' => true,
			'adminbar' => true,
			'wpcf7' => true,
			'feed_links' => true,
			'post_formats' => false,
			'html5' => true,
			'hooks' => true,
			'buddypress' => true,
		), $args ) );
		
		if ( $cleanup )
			$this->cleanup();
			
		if ( $adminbar ) {
			add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) ); 
			add_action( 'template_redirect', array( & $this, 'template_redirect_remove_styles' ), 99 ); 
		}
		
		if ( $wpcf7 && function_exists( 'wpcf7_enqueue_scripts' ) )
			add_action( 'wp_enqueue_scripts', array( & $this, 'wp_enqueue_scripts' ), 5 );
		
		if ( $feed_links )
			add_theme_support( 'automatic-feed-links' );
			
		if ( $buddypress )
			add_theme_support( 'buddypress' );
		
		if ( $post_formats )
			add_theme_support( 'post-formats', gtheme_get_info( 'support_post_formats', array( 
				'aside',
				'link',
				'gallery',
				'status',
				'quote',
				'image',
			) ) );
		
		if ( $html5 )
			add_theme_support( 'html5', gtheme_get_info( 'support_html5', array( 
				'comment-list',
				'search-form',
				'comment-form',
			) ) );
		
		if ( $hooks ) // http://justintadlock.com/archives/2011/09/01/a-better-way-for-plugins-to-hook-into-theme-templates
			add_theme_support( 'template-hooks', gtheme_get_info( 'support_template_hooks', array( 
				'before_post',
				'after_post',
				'template_body_top',
				//'template_before_loop',
				//'template_after_loop',
				//'template_after_sidebar',
				'gtheme_do_header',
				'gtheme_do_after_header',
				'gtheme_do_before_footer',
				'gtheme_do_footer',
			) ) ); 
	}
	
	public function cleanup()
	{
		foreach ( array( 
			'wp_head',
			'rss2_head',
			'commentsrss2_head',
			'rss_head',
			'rdf_header',
			'atom_head',
			'comments_atom_head',
			'opml_head',
			'app_head',
			) as $action ) remove_action( $action, 'the_generator' );
			
		// completely remove the version number from pages and feeds
		add_filter( 'the_generator', '__return_null', 99 ); 

		foreach ( array( 
			'rsd_link',
			'wlwmanifest_link',
			array( 'QMT_Hooks', 'wp_head' ), // remove query-multiple-taxonomies styles
			) as $func ) remove_action( 'wp_head', $func );


		remove_filter( 'comment_text', 'make_clickable', 9 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );
		foreach ( array( 'the_content', 'the_title' ) as $filter )
			remove_filter( $filter, 'capital_P_dangit', 11 );
	}
	
	public function template_redirect_remove_styles()
	{
		remove_action( 'wp_head', 'wp_admin_bar_header' ); // print style will added by theme
	}
	
	public function wp_enqueue_scripts()
	{
		add_filter( 'wpcf7_load_css', '__return_false', 12 ); // styles will be added by the theme
		
		if ( 'contact' == gtheme_template_base() )
			add_filter( 'wpcf7_load_js', '__return_true', 12 );
		else
			add_filter( 'wpcf7_load_js', '__return_false', 12 );

	}
}
