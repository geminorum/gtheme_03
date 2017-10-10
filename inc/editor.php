<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEditor extends gThemeModuleCore
{

	protected $ajax = TRUE;

	function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'css'             => TRUE, // this is the editor style!!
			'buttons'         => TRUE,
			'buttons_2'       => TRUE,
			'advanced_styles' => TRUE,
			'default_content' => FALSE,
		), $args ) );

		if ( $css )
			add_filter( 'mce_css', array( $this, 'mce_css' ) );

		if ( $buttons )
			add_filter( 'mce_buttons', array( $this, 'mce_buttons' ) );

		if ( $buttons_2 )
			add_filter( 'mce_buttons_2', array( $this, 'mce_buttons_2' ) );

		if ( $advanced_styles )
			add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 12 );

		if ( $default_content )
			add_filter( 'default_content', array( $this, 'default_content' ), 10, 2 );
	}

	public static function style_url()
	{
		$file = gThemeUtilities::isRTL() ? 'editor-style-rtl.css' : 'editor-style.css';

		if ( file_exists( GTHEME_CHILD_DIR.'/css/'.$file ) )
			return GTHEME_CHILD_URL.'/css/'.$file;
		else
			return GTHEME_URL.'/css/'.$file;
	}

	// the comma-delimited list of stylesheets to load in TinyMCE.
	public function mce_css( $url )
	{
		if ( ! empty( $url ) )
			$url.= ',';

		return $url.self::style_url();
	}

	public function mce_buttons( $buttons )
	{
		$gtheme_buttons = gThemeOptions::info( 'mce_buttons', array() );

		foreach ( $gtheme_buttons as $gtheme_button )
			array_push( $buttons, $gtheme_button );

		return $buttons;
	}

	// add "styles" drop-down for the second row
	public function mce_buttons_2( $buttons )
	{
		if ( gThemeUtilities::isRTL() )
			$buttons = array_diff( $buttons, array( 'outdent', 'indent' ) );

		$gtheme_buttons = gThemeOptions::info( 'mce_buttons_2', array( 'styleselect' ) );

		foreach ( $gtheme_buttons as $gtheme_button )
			array_unshift( $buttons, $gtheme_button );

		return $buttons;
	}

	// add "styles" drop-down content or classes
	// SEE : http://www.tinymce.com/wiki.php/Configuration:formats
	// SEE : http://www.tinymce.com/tryit/custom_formats.php
	public function tiny_mce_before_init( $settings )
	{
		$style_formats = gThemeOptions::info( 'mce_style_formats', self::defaultFormats() );

		if ( count( $style_formats ) ) {
			$style_formats = wp_json_encode( $style_formats );
			if ( isset( $settings['style_formats'] ) ) {
				$settings['style_formats'] = gThemeUtilities::json_merge( $settings['style_formats'], $style_formats );
			} else {
				$settings['style_formats'] = $style_formats;
			}
		}

		return $settings;
	}

	public static function defaultFormats( $extra = array() )
	{
		return array_merge( array(
			array(
				'title'   => _x( 'Blockquote', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'blockquote',
				'classes' => 'entry-quote',
			),
			array(
				'title'    => _x( 'Unordered List', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'selector' => 'ul', // http://wordpress.stackexchange.com/a/85071
				'classes'  => 'entry-list',
			),
			array(
				'title'    => _x( 'Ordered List', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'selector' => 'ol',
				'classes'  => 'entry-list',
			),
			array(
				'title'   => _x( 'Note', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'p',
				'classes' => 'entry-note',
			),
			array(
				'title'   => _x( 'Source', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'p',
				'classes' => 'entry-source',
			),
		), $extra );
	}

	public function default_content( $post_content, $post )
	{
		$default = gThemeOptions::info( 'default_content', _x( '[content not available yet]', 'Editor Default Content', GTHEME_TEXTDOMAIN ) );
		return is_null( $default ) ? $post_content : $default;
	}
}
