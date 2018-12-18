<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeEditor extends gThemeModuleCore
{

	protected $ajax = TRUE;

	function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'css'             => TRUE, // the editor style
			'buttons'         => TRUE,
			'buttons_2'       => TRUE,
			'advanced_styles' => TRUE,
			'default_content' => FALSE,
		], $args ) );

		if ( $css ) {
			add_theme_support( 'editor-style' );
			add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
			add_filter( 'mce_css', [ $this, 'mce_css' ] );
		}

		if ( $buttons )
			add_filter( 'mce_buttons', [ $this, 'mce_buttons' ] );

		if ( $buttons_2 )
			add_filter( 'mce_buttons_2', [ $this, 'mce_buttons_2' ] );

		if ( $advanced_styles )
			add_filter( 'tiny_mce_before_init', [ $this, 'tiny_mce_before_init' ], 12 );

		if ( $default_content )
			add_filter( 'default_content', [ $this, 'default_content' ], 10, 2 );
	}

	public static function style_url( $asset = 'editor' )
	{
		$file = 'front.'.$asset
			.( gThemeUtilities::isRTL() ? '-rtl' : '' )
			// .( SCRIPT_DEBUG ? '' : '.min' )
			.'.css';

		if ( file_exists( GTHEME_CHILD_DIR.'/css/'.$file ) )
			return GTHEME_CHILD_URL.'/css/'.$file;
		else
			return GTHEME_URL.'/css/'.$file;
	}

	public function enqueue_block_editor_assets()
	{
		wp_enqueue_style( GTHEME.'-blocks-style', self::style_url( 'blocks' ), FALSE, GTHEME_CHILD_VERSION, 'all' );

		$inline = gThemeColors::getAccentColorCSS();

		if ( trim( $inline ) )
			wp_add_inline_style( GTHEME.'-blocks-style', $inline );
	}

	// comma-delimited list of stylesheets to load in TinyMCE
	public function mce_css( $url )
	{
		if ( ! empty( $url ) )
			$url.= ',';

		return $url.self::style_url();
	}

	public function mce_buttons( $buttons )
	{
		$gtheme_buttons = gThemeOptions::info( 'mce_buttons', [] );

		foreach ( $gtheme_buttons as $gtheme_button )
			array_push( $buttons, $gtheme_button );

		return $buttons;
	}

	// adds `styles` drop-down for the second row
	public function mce_buttons_2( $buttons )
	{
		if ( gThemeUtilities::isRTL() )
			$buttons = array_diff( $buttons, [ 'outdent', 'indent' ] );

		$gtheme_buttons = gThemeOptions::info( 'mce_buttons_2', [ 'styleselect' ] );

		foreach ( $gtheme_buttons as $gtheme_button )
			array_unshift( $buttons, $gtheme_button );

		return $buttons;
	}

	// adds `styles` drop-down content or classes
	// @SEE: http://www.tinymce.com/wiki.php/Configuration:formats
	// @SEE: http://www.tinymce.com/tryit/custom_formats.php
	public function tiny_mce_before_init( $settings )
	{
		$style_formats = gThemeOptions::info( 'mce_style_formats', self::defaultFormats() );

		if ( count( $style_formats ) ) {

			$style_formats = wp_json_encode( $style_formats );

			if ( ! empty( $settings['style_formats'] )
				&& 'false' != $settings['style_formats'] )
					$settings['style_formats'] = gThemeUtilities::json_merge( $settings['style_formats'], $style_formats );
			else
				$settings['style_formats'] = $style_formats;
		}

		return $settings;
	}

	public static function defaultFormats( $extra = [] )
	{
		return array_merge( [
			[
				'title'   => _x( 'Blockquote', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'blockquote',
				'classes' => 'entry-quote',
			],
			[
				'title'    => _x( 'Unordered List', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'selector' => 'ul', // http://wordpress.stackexchange.com/a/85071
				'classes'  => 'entry-list',
			],
			[
				'title'    => _x( 'Ordered List', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'selector' => 'ol',
				'classes'  => 'entry-list',
			],
			[
				'title'   => _x( 'Note', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'p',
				'classes' => 'entry-note',
			],
			[
				'title'   => _x( 'Source', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'p',
				'classes' => 'entry-source',
			],
			[
				'title'   => _x( 'Greeting', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'p',
				'classes' => 'entry-greeting',
			],
			[
				'title'   => _x( 'Signature', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
				'block'   => 'p',
				'classes' => 'entry-signature',
			],
		], $extra );
	}

	public function default_content( $post_content, $post )
	{
		$default = gThemeOptions::info( 'default_content', _x( '[content not available yet]', 'Editor Default Content', GTHEME_TEXTDOMAIN ) );
		return is_null( $default ) ? $post_content : $default;
	}
}
