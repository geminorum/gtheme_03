<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeColors extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [], $childless = NULL )
	{
		extract( self::atts( [
			'customizer'     => FALSE,
			'disable_custom' => TRUE,
			'custom_palette' => TRUE,
			'accent_color'   => self::getAccentColorDefault( FALSE ),
		], $args ) );

		if ( $customizer )
			add_action( 'customize_register', [ $this, 'customize_register' ] );

		if ( $disable_custom )
			// NOTE: prevents editors from setting custom colors (via color selector) on elements.
			add_theme_support( 'disable-custom-colors' );

		if ( $custom_palette )
			add_theme_support( 'editor-color-palette', self::getCustomPalette() );

		if ( $accent_color ) {
			// @REF: https://richtabor.com/gutenberg-customizer-colors/
			add_action( 'customize_register', [ $this, 'customize_register_accent_color' ], 11 );
			add_action( 'wp_head', [ $this, 'wp_head' ] );
		}
	}

	public function customize_register( $manager )
	{
		$setting = 'theme_color_scheme';
		$manager->add_setting( $setting, [
			'default'    => gThemeOptions::info( 'color_scheme', 'light' ),
			'capability' => 'edit_theme_options',
		] );

		$manager->add_control(
			new \WP_Customize_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'section'     => 'colors',
					'settings'    => $setting,
					'type'        => 'radio',
					'label'       => esc_html_x( 'Color Scheme', 'Customizer: Setting Title', 'gtheme' ),
					'description' => esc_html_x( 'Defines the dark or light theme version.', 'Customizer: Setting Description', 'gtheme' ),
					'choices'     => [
						'light'  => esc_html_x( 'Light', 'Customizer: Setting Option', 'gtheme' ),
						'dark'   => esc_html_x( 'Dark', 'Customizer: Setting Option', 'gtheme' ),
					],
				]
			)
		);
	}

	public static function scheme( $fallback = 'light' )
	{
		return get_theme_mod(
			'theme_color_scheme',
			gThemeOptions::info( 'color_scheme', $fallback )
		);
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
		if ( $styles = self::getExtraColorsCSS() )
			printf( '<style>%s</style>'."\n", $styles );
	}

	public static function getCustomPalette()
	{
		$defaults = self::defaults();
		$palette  = gThemeOptions::info( 'editor_custom_palette', $defaults );

		if ( $accent = self::getAccentColorDefault() )
			$palette[] = [
	 			'name'  => esc_html_x( 'Accent Color', 'Colors', 'gtheme' ),
	 			'slug'  => 'theme-accent',
	 			'color' => get_theme_mod( 'accent_color', $accent ),
	 		];

		return apply_filters( 'gtheme_colors_custom_palette', $palette, $defaults, $accent );
	}

	// NOTE: if `theme_mod_custom_logo` hooked then accent-color will be enabled by default.
	// NOTE: use this on child: `'colors_default_accent' => apply_filters( 'theme_mod_accent_color', '' ) ?: $hexcolor,`
	public static function getAccentColorDefault( $customized = TRUE )
	{
		return gThemeOptions::info(
			'colors_default_accent',
			$customized
				? get_theme_mod( 'accent_color', '' )
				: apply_filters( 'theme_mod_accent_color', '' )
		);
	}

	// NOTE: DEPRECATED
	public static function getAccentColorCSS()
	{
		self::_dev_dep( 'gThemeColors::getExtraColorsCSS()' );
		return self::getExtraColorsCSS();
	}

	// @SEE: https://github.com/WordPress/gutenberg/blob/trunk/packages/components/src/utils/theme-variables.scss
	public static function getExtraColorsCSS()
	{
		$colors = apply_filters( 'gtheme_colors_extra', [
			'theme-accent' => get_theme_mod( 'accent_color', self::getAccentColorDefault() ),
		] );

		if ( ! count( $colors ) )
			return '';

		$root = $classes = [];

		foreach ( $colors as $handle => $data ) {

			if ( ! $color = sanitize_hex_color( $data ) )
				continue;

			$root[] = "\t".sprintf( '--%s-custom-color: %s;', $handle, $color );

			$classes[] = sprintf( '.has-%s-text-color { color: %s !important; }', $handle, $color );
			$classes[] = sprintf( '.has-%s-background-color { background-color: %s !important; }', $handle, $color );
		}

		if ( empty( $root ) )
			return '';

		$style = "\n".':root {'."\n".implode( "\n", $root )."\n".'}'."\n";
		$style.= implode( "\n", $classes )."\n";

		return $style;
	}

	// @REF: https://developer.wordpress.org/themes/customize-api/
	public function customize_register_accent_color( $manager )
	{
		if ( ! $default = self::getAccentColorDefault() )
			return;

		$setting = 'accent_color';
		$manager->add_setting( $setting, [
			'default'           => self::getAccentColorDefault( FALSE ), // NOTE: using `$default` will result in unclear-able default!
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );

		$manager->add_control(
			new \WP_Customize_Color_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'section'     => 'colors',
					'settings'    => $setting,
					'label'       => esc_html_x( 'Accent Color', 'Colors', 'gtheme' ),
					'description' => esc_html_x( 'Adds a color to use within the block editor color palette.', 'Colors', 'gtheme' ),
				]
			)
		);

		if ( ! $manager->is_preview() )
			return;

		add_action( 'wp_footer', [ $this, 'wp_footer_customize_preview' ], 20 );
	}

	// @REF: https://ottopress.com/2012/how-to-leverage-the-theme-customizer-in-your-own-themes/
	public function wp_footer_customize_preview()
	{
		?><script>(function($){
wp.customize('accent_color', function (value) {
	value.bind( function(to) {
		$(':root').css('--theme-accent-custom-color', to);
	});
});
})(jQuery)</script><?php
	}
}
