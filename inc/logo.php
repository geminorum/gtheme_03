<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeLogo extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'customizer' => FALSE,
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

		if ( ! $manager->is_preview() )
			return;

		$manager->get_setting( 'blogname' )->transport = 'postMessage';
		$manager->get_setting( 'blogdescription' )->transport = 'postMessage';

		add_action( 'wp_footer', [ $this, 'wp_footer_customize_preview' ], 20 );
	}

	// @REF: https://ottopress.com/2012/how-to-leverage-the-theme-customizer-in-your-own-themes/
	public function wp_footer_customize_preview()
	{
    	?><script type="text/javascript">(function($){
wp.customize('blogname', function (value) {
	value.bind( function(to) {
		$('a.site-title').html(to);
	});
});
wp.customize('blogdescription', function (value) {
	value.bind( function(to) {
		$('span.site-description').html(to);
	});
});
})(jQuery)</script><?php
	}

	public static function custom( $context = NULL, $before = '', $after = '', $fallback = NULL )
	{
		$context = $context ?? 'main';
		$title   = get_theme_mod( 'logo_with_title', gThemeOptions::info( 'custom_logo_with_title', FALSE ) );
		$desc    = get_theme_mod( 'logo_with_desc', gThemeOptions::info( 'custom_logo_with_desc', FALSE ) );

		// NOTE: avoid using `has_custom_logo()` since has no filter
		if ( $custom = get_custom_logo() ) {

			echo $before;

				echo $custom;

				if ( 'main' === $context && $title )
					gThemeTemplate::title( $context, '', '', FALSE );

				// NOTE: pulled out of `gThemeTemplate::title()` for the option of logo + description (without title).
				if ( 'main' === $context && $desc )
					gThemeTemplate::description( '<span class="site-description">', '</span>' );

			echo $after;

		} else if ( is_null( $fallback ) ) {

			gThemeTemplate::title( $context, $before, $after, 'main' === $context ? (bool) $desc : FALSE );

		} else if ( 'logo' === $fallback ) {

			echo $before;

				gThemeTemplate::logo( $context );

			echo $after;

		} else if ( 'logo-title' == $fallback ) {

			$append = ' <span class="site-title" title="{{{logo_title}}}">{{site_name}}</span>';

			echo $before;

				gThemeTemplate::logo( $context, NULL, TRUE, $append );

			echo $after;

		} else if ( $fallback ) {

			echo $before.$fallback.$after;
		}
	}
}
