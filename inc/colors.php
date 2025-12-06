<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeColors extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'customizer'     => FALSE,
			'disable_custom' => TRUE,
			'custom_palette' => TRUE,
			'accent_color'   => self::getAccentColorDefault(), // @REF: https://richtabor.com/gutenberg-customizer-colors/
		], $args ) );

		if ( $customizer )
			add_action( 'customize_register', [ $this, 'customize_register' ] );

		if ( $disable_custom )
			add_theme_support( 'disable-custom-colors' );

		if ( $custom_palette )
			add_theme_support( 'editor-color-palette', self::getCustomPalette() );

		if ( $accent_color ) {
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
						'dark'   => esc_html_x( 'Dark', 'Customizer: Setting Option', 'gtheme' ),
						'light'  => esc_html_x( 'Light', 'Customizer: Setting Option', 'gtheme' )
					]
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
		return gThemeOptions::info(
			'colors_default_accent',
			// NOTE: if `theme_mod_custom_logo` hooked then accent-color will be enabled by default.
			get_theme_mod( 'accent_color', FALSE )
		);
	}

	// NOTE: DEPRECATED
	public static function getAccentColorCSS()
	{
		self::_dev_dep( 'gThemeColors::getExtraColorsCSS()' );
		return self::getExtraColorsCSS();
	}

	public static function getExtraColorsCSS()
	{
		$colors = apply_filters( 'gtheme_colors_extra', [
			'theme-accent' => get_theme_mod( 'accent_color', self::getAccentColorDefault() ),
		] );

		if ( ! count( $colors ) )
			return '';

		$root = $classes = [];

		foreach ( $colors as $handle => $data ) {
			$root[] = "\t".sprintf( '--%s-custom-color: %s;', $handle, esc_attr( $data ) );

			$classes[] = sprintf( '.has-%s-text-color { color: %s !important; }', $handle, esc_attr( $data ) );
			$classes[] = sprintf( '.has-%s-background-color { background-color: %s !important; }', $handle, esc_attr( $data ) );
		}

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
			'default'           => $default,
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );

		$manager->add_control(
			new \WP_Customize_Color_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'settings'    => $setting,
					'section'     => 'colors',
					'label'       => esc_html_x( 'Accent Color', 'Colors', 'gtheme' ),
					'description' => esc_html_x( 'Add a color to use within the block editor color palette.', 'Colors', 'gtheme' ),
				]
			)
		);
	}
}
