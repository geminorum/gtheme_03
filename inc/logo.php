<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeLogo extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'customizer'  => TRUE,
		], $args ) );

		if ( $customizer )
			add_action( 'customize_register', [ $this, 'customize_register' ] );
	}

	public function sanitize_callback_type_checkbox( $value )
	{
		return (bool) $value;
	}

	public function customize_register( $manager )
	{
		$setting = 'logo_with_title';
		$manager->add_setting( $setting, [
			'default'           => gThemeOptions::info( 'custom_logo_with_title', FALSE ),
			'sanitize_callback' => [ $this, 'sanitize_callback_type_checkbox' ],
			'capability'        => 'edit_theme_options',
		] );

		$manager->add_control(
			new \WP_Customize_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'settings'    => $setting,
					'type'        => 'checkbox',
					'label'       => _x( 'Append Site Title', 'Customizer: Setting Title', 'gtheme' ),
					'description' => _x( 'Keeps the site title along with the custom logo.', 'Customizer: Setting Description', 'gtheme' ),
					'section'     => 'title_tagline',
				]
			)
		);

		$setting = 'logo_with_desc';
		$manager->add_setting( $setting, [
			'default'           => gThemeOptions::info( 'custom_logo_with_desc', FALSE ),
			'sanitize_callback' => [ $this, 'sanitize_callback_type_checkbox' ],
			'capability'        => 'edit_theme_options',
		] );

		$manager->add_control(
			new \WP_Customize_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'settings'    => $setting,
					'type'        => 'checkbox',
					'label'       => _x( 'Append Site Description', 'Customizer: Setting Title', 'gtheme' ),
					'description' => _x( 'Keeps the site description along with the custom logo.', 'Customizer: Setting Description', 'gtheme' ),
					'section'     => 'title_tagline',
				]
			)
		);
	}

	public static function custom( $context = NULL, $before = '', $after = '', $fallback = NULL )
	{
		$context = $context ?? 'main';
		$title   = get_theme_mod( 'logo_with_title', gThemeOptions::info( 'custom_logo_with_title', FALSE ) );
		$desc    = get_theme_mod( 'logo_with_desc', gThemeOptions::info( 'custom_logo_with_desc', FALSE ) );

		if ( has_custom_logo() || is_customize_preview() ) {

			echo $before;

				the_custom_logo();

				if ( 'main' === $context && $title )
					gThemeTemplate::title( $context, '', '', FALSE );

				// NOTE: pulled out of `gThemeTemplate::title()` for the option of logo + description (without title).
				if ( 'main' === $context && $desc )
					gThemeTemplate::description( '<span class="site-description">', '</span>' );

			echo $after;

		} else if ( is_null( $fallback ) ) {

			gThemeTemplate::title( $context, $before, $after, 'main' === $context ? (bool) $desc : FALSE );

		} else if ( 'logo' === $fallback ) {

			echo $before.gThemeTemplate::logo( $context, NULL, FALSE ).$after;

		} else if ( 'logo-title' == $fallback ) {

			echo $before.gThemeTemplate::logo( $context, NULL, FALSE, ' <span title="{{{logo_title}}}">{{site_name}}</span>' ).$after;

		} else if ( $fallback ) {

			echo $before.$fallback.$after;
		}
	}
}
