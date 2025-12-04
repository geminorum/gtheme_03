<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeColors extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'disable_custom' => TRUE,
			'custom_palette' => TRUE,
			'accent_color'   => self::getAccentColorDefault(), // @REF: https://richtabor.com/gutenberg-customizer-colors/
		], $args ) );

		if ( $disable_custom )
			add_theme_support( 'disable-custom-colors' );

		if ( $custom_palette )
			add_theme_support( 'editor-color-palette', self::getCustomPalette() );

		if ( $accent_color ) {
			add_action( 'customize_register', [ $this, 'customize_register' ], 11 );
			add_action( 'wp_head', [ $this, 'wp_head' ] );
		}
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			[
				'name'  => _x( 'Gray Darker', 'Editor Custom Palette', 'gtheme' ),
				'slug'  => 'gray-darker',
				'color' => '#222',
			],
			[
				'name'  => _x( 'Gray Dark', 'Editor Custom Palette', 'gtheme' ),
				'slug'  => 'gray-dark',
				'color' => '#333',
			],
			[
				'name'  => _x( 'Gray', 'Editor Custom Palette', 'gtheme' ),
				'slug'  => 'gray',
				'color' => '#555',
			],
			[
				'name'  => _x( 'Gray Light', 'Editor Custom Palette', 'gtheme' ),
				'slug'  => 'gray-light',
				'color' => '#777',
			],
			[
				'name'  => _x( 'Gray Lighter', 'Editor Custom Palette', 'gtheme' ),
				'slug'  => 'gray-lighter',
				'color' => '#eee',
			],
		], $extra );
	}

	public function wp_head()
	{
		if ( $styles = self::getAccentColorCSS() )
			printf( '<style type="text/css">%s</style>'."\n", $styles );
	}

	public static function getCustomPalette()
	{
		$defaults = gThemeOptions::info( 'editor_custom_palette', self::defaults() );

		if ( $accent = self::getAccentColorDefault() ) {
			$defaults[] = [
	 			'name'  => esc_html_x( 'Accent Color', 'Colors', 'gtheme' ),
	 			'slug'  => GTHEME.'-accent',
	 			'color' => esc_html( get_theme_mod( 'accent_color', $accent ) ),
	 		];
		}

		return $defaults;
	}

	// FALSE to disable
	public static function getAccentColorDefault()
	{
		return gThemeOptions::info( 'default_accent_color', FALSE );
	}

	public static function getAccentColorCSS()
	{
		if ( ! $default = self::getAccentColorDefault() )
			return '';

		$accent = get_theme_mod( 'accent_color', $default );

		$css = '.has-'.GTHEME.'-accent-color{color:'.esc_attr( $accent ).'!important;}';
		$css.= '.has-'.GTHEME.'-accent-background-color{background-color:'.esc_attr( $accent ).';}';

		return wp_strip_all_tags( $css );
	}

	// @REF: https://developer.wordpress.org/themes/customize-api/
	public function customize_register( $wp_customize )
	{
		if ( ! $default = self::getAccentColorDefault() )
			return;

		$wp_customize->add_setting( 'accent_color', [
			'default'           => $default,
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );

		$wp_customize->add_control( new \WP_Customize_Color_Control(
			$wp_customize, 'accent_color', [
				'section'     => 'colors',
				'label'       => esc_html_x( 'Accent Color', 'Colors', 'gtheme' ),
				'description' => esc_html_x( 'Add a color to use within the block editor color palette.', 'Colors', 'gtheme' ),
		] ) );
	}
}
