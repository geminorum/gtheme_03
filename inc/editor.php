<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeEditor extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
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
			add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_assets' ] );
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

	public static function getStyleURL( $asset = 'editor', $group = NULL )
	{
		if ( is_null( $group ) )
			$group = gThemeOptions::getGroup();

		$rtl    = gThemeUtilities::isRTL() ? '-rtl' : '';
		$target = $group.'.'.$asset.$rtl.'.css';
		$main   = 'main.'.$asset.$rtl.'.css';

		if ( file_exists( GTHEME_CHILD_DIR.'/css/'.$target ) )
			$url = GTHEME_CHILD_URL.'/css/'.$target;

		else if ( file_exists( GTHEME_CHILD_DIR.'/css/'.$main ) )
			$url = GTHEME_CHILD_URL.'/css/'.$main;

		else
			$url = GTHEME_URL.'/css/'.$main;

		return $url;
	}

	public function enqueue_block_editor_assets()
	{
		$handle = sprintf( '%s-block-styles', $this->base );

		wp_enqueue_style( $handle,
			self::getStyleURL( 'blocks' ),
			FALSE,
			GTHEME_CHILD_VERSION,
			'all'
		);

		if ( $inline = gThemeColors::getExtraColorsCSS() )
			wp_add_inline_style( $handle, $inline );
	}

	public function enqueue_block_assets()
	{
		if ( is_admin() ) {

			wp_deregister_style( 'wp-block-library-theme' );
			wp_register_style( 'wp-block-library-theme', GTHEME_URL.'/css/block-library-theme.css', [], GTHEME_VERSION );

			wp_dequeue_style( 'wp-block-library' );

		} else {

			wp_dequeue_style( [ 'wp-block-library', 'wp-block-library-theme' ] );
		}
	}

	// Comma-delimited list of stylesheets to load in `TinyMCE`
	public function mce_css( $url )
	{
		if ( ! empty( $url ) )
			$url.= ',';

		return $url.self::getStyleURL();
	}

	public function mce_buttons( $buttons )
	{
		$gtheme_buttons = gThemeOptions::info( 'mce_buttons', [] );

		foreach ( $gtheme_buttons as $gtheme_button )
			array_push( $buttons, $gtheme_button );

		return $buttons;
	}

	// Adds `styles` drop-down for the second row
	public function mce_buttons_2( $buttons )
	{
		if ( gThemeUtilities::isRTL() )
			$buttons = array_diff( $buttons, [ 'outdent', 'indent' ] );

		$gtheme_buttons = gThemeOptions::info( 'mce_buttons_2', [ 'styleselect' ] );

		foreach ( $gtheme_buttons as $gtheme_button )
			array_unshift( $buttons, $gtheme_button );

		return $buttons;
	}

	public function tiny_mce_before_init( $settings )
	{
		// Adds `styles` drop-down content or classes
		// @SEE: http://www.tinymce.com/wiki.php/Configuration:formats
		// @SEE: http://www.tinymce.com/tryit/custom_formats.php
		$style_formats = gThemeOptions::info( 'mce_style_formats', self::defaultFormats() );

		if ( count( $style_formats ) ) {

			$style_formats = wp_json_encode( $style_formats );

			if ( ! empty( $settings['style_formats'] )
				&& 'false' != $settings['style_formats'] )
					$settings['style_formats'] = gThemeUtilities::json_merge( $settings['style_formats'], $style_formats );
			else
				$settings['style_formats'] = $style_formats;
		}

		// @REF: https://wordpress.stackexchange.com/a/128392
		$body_class = empty( $settings['body_class'] ) ? [] : gThemeHTML::attrClass( $settings['body_class'] );
		$settings['body_class'] = gThemeHTML::prepClass( gTheme()->filters->body_class( $body_class ) );

		return $settings;
	}

	// @REF: http://wordpress.stackexchange.com/a/85071
	public static function defaultFormats( $extra = [] )
	{
		return array_merge( [
			[
				'title'   => _x( 'Blockquote', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'blockquote',
				'classes' => 'entry-quote',
			],
			[
				'title'    => _x( 'Unordered List', 'Editor Custom Class', 'gtheme' ),
				'selector' => 'ul',
				'classes'  => 'entry-list',
			],
			[
				'title'    => _x( 'Ordered List', 'Editor Custom Class', 'gtheme' ),
				'selector' => 'ol',
				'classes'  => 'entry-list',
			],
			[
				'title'    => _x( 'Unmarked List', 'Editor Custom Class', 'gtheme' ),
				'selector' => 'ul',
				'classes'  => 'entry-list-unmarked',
			],
			[
				'title'   => _x( 'Note', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'p',
				'classes' => 'entry-note',
			],
			[
				'title'   => _x( 'Source', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'p',
				'classes' => 'entry-source',
			],
			[
				'title'   => _x( 'Greeting', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'p',
				'classes' => 'entry-greeting',
			],
			[
				'title'   => _x( 'Signature', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'p',
				'classes' => 'entry-signature',
			],
			[
				'title'   => _x( 'Poem', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'p',
				'classes' => 'wrap-poem',
			],
			[
				'title'   => _x( 'Question', 'Editor Custom Class', 'gtheme' ),
				'block'   => 'p',
				'classes' => 'wrap-question',
			],
		], $extra );
	}

	public function default_content( $post_content, $post )
	{
		$default = gThemeOptions::info( 'default_content', _x( '[content not available yet]', 'Editor Default Content', 'gtheme' ) );
		return is_null( $default ) ? $post_content : $default;
	}
}
