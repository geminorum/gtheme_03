<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEditor extends gThemeModuleCore 
{
	var $_ajax = true;
	
	function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'css' => true, // this is the editor style!!
			'buttons' => true,
			'buttons_2' => true,
			'advanced_styles' => true,
			'default_content' => false,
		), $args ) );
		
		if ( $css )
			add_filter( 'mce_css', array( & $this, 'mce_css' ) );

		if ( $buttons )
			add_filter( 'mce_buttons', array( & $this, 'mce_buttons' ) );
			
		if ( $buttons_2 ) 
			add_filter( 'mce_buttons_2', array( & $this, 'mce_buttons_2' ) );
		
		if ( $advanced_styles )
			add_filter( 'tiny_mce_before_init', array( & $this, 'tiny_mce_before_init' ), 12 );
		
		if ( $default_content )
			add_filter( 'default_content', array( & $this, 'default_content' ), 10, 2 );
	}
	
	public static function style_url() 
	{
		$file = gThemeUtilities::is_rtl() ? 'editor-style-rtl.css' : 'editor-style.css';
		
		if ( file_exists( GTHEME_CHILD_DIR.DS.'css'.DS.$file ) ) 
			return GTHEME_CHILD_URL.'/css/'.$file; 
		else
			return GTHEME_URL.'/css/'.$file; 
	}
	
	// the comma-delimited list of stylesheets to load in TinyMCE.	
	public function mce_css( $url ) 
	{
		if ( ! empty( $url ) )
			$url .= ',';
		
		return $url.self::style_url();;
	}
	
	public function mce_buttons( $buttons ) 
	{
		$gtheme_buttons = gtheme_get_info( 'mce_buttons', array( 'sup', 'sub', 'hr' ) );
		
		foreach ( $gtheme_buttons as $gtheme_button )
			array_push( $buttons, $gtheme_button );
			
		return $buttons;
	}
	
	// add "styles" drop-down for the second row
	public function mce_buttons_2( $buttons )
	{
		if ( gThemeUtilities::is_rtl() )
			$buttons = array_diff( $buttons, array( 'outdent', 'indent' ) );
			
		// must this : better to use on gpersiandate!
		// http://stackoverflow.com/questions/12416678/how-to-customize-tinymce-button-output		
		$buttons = array_diff( $buttons, array( 'justifyfull' ) );
		
		$gtheme_buttons = gtheme_get_info( 'mce_buttons_2', array( 'styleselect' ) );
		
		foreach ( $gtheme_buttons as $gtheme_button )
			array_unshift( $buttons, $gtheme_button );
			
		return $buttons;
	}
	
	// add "styles" drop-down content or classes
	// SEE : http://www.tinymce.com/wiki.php/Configuration:formats
	// SEE : http://www.tinymce.com/tryit/custom_formats.php
	public static function tiny_mce_before_init( $settings )
	{
		$style_formats = gtheme_get_info( 'mce_style_formats', array() );
		
		if ( count( $style_formats ) ) {
			$style_formats = json_encode( $style_formats );
			if ( isset( $settings['style_formats'] ) ) {
				$settings['style_formats'] = gThemeUtilities::json_merge( $settings['style_formats'], $style_formats );
			} else {
				$settings['style_formats'] = $style_formats;
			}
		}
		
		return $settings;
	}
	
	public function default_content( $post_content, $post ) 
	{
		return gtheme_get_info( 'default_content', $post_content );
	}
	
}