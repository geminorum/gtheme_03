<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWrap extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [], $childless = NULL )
	{
		extract( self::atts( [
			'images_404' => FALSE,
			'customizer' => $childless ?? FALSE,
		], $args ) );

		if ( $customizer )
			add_action( 'customize_register', [ $this, 'customize_register' ] );

		if ( is_admin() )
			return;

		add_action( 'before_signup_header', [ $this, 'before_signup_header' ] );
		add_action( 'activate_header', [ $this, 'activate_header' ] );

		if ( $images_404 )
			add_filter( 'template_include', [ $this, 'template_include_404_images' ], -1 );

		add_filter( 'template_include', [ __CLASS__, 'template_include' ], 99 );
	}

	public function template_include_404_images( $template )
	{
		if ( ! is_404() )
			return $template;

		// @REF: http://wpengineer.com/2377/implement-404-image-in-your-theme/
		// matches 'img.png' and 'img.gif?hello=world'
		if ( preg_match( '~\.(jpe?g|png|gif|svg|bmp)(\?.*)?$~i', $_SERVER['REQUEST_URI'] ) ) {
			header( 'Content-Type: image/png' );
			// header( 'Content-Type: image/svg+xml' );
			locate_template( 'images/404.png', TRUE, TRUE );
			exit;
		}

		return $template;
	}

	public function before_signup_header()
	{
		gThemeWordPress::doNotCache();

		self::define( 'GTHEME_IS_WP_SIGNUP', TRUE );
		self::define( 'GTHEME_SOCIAL_META_DISABLED', TRUE );
	}

	public function activate_header()
	{
		gThemeWordPress::doNotCache();

		self::define( 'GTHEME_IS_WP_ACTIVATE', TRUE );
		self::define( 'GTHEME_SOCIAL_META_DISABLED', TRUE );
	}

	// TODO: adopt: `Storefront_Custom_Radio_Image_Control`
	public function customize_register( $manager )
	{
		$section = sprintf( '%s_%s', $this->base, 'wraps' );
		$manager->add_section( $section, [
			'title' => esc_html_x( 'Wrapping', 'Customizer: Section Title', 'gtheme' ),
		] );

		$setting = 'wrap_base_starts_with';
		$manager->add_setting( $setting, [
			'default'           => gThemeOptions::info( 'wrap_base_starts_with', 'navbar' ),
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'manage_options',
		] );

		$manager->add_control(
			new \WP_Customize_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'section'     => $section,
					'settings'    => $setting,
					'type'        => 'radio',
					'label'       => esc_html_x( 'Wrapping Starts', 'Customizer: Setting Title', 'gtheme' ),
					'description' => esc_html_x( 'Defines the block that starts on content wraps.', 'Customizer: Setting Description', 'gtheme' ),
					'choices'     => gThemeOptions::info( 'wrap_base_starts_with_choices', [
						'navbar' => esc_html_x( 'Navbar &mdash; On the Top', 'Customizer: Setting Option', 'gtheme' ),
						'band'   => esc_html_x( 'Band &mdash; On the Side', 'Customizer: Setting Option', 'gtheme' ),
						'none'   => esc_html_x( 'None', 'Customizer: Setting Option', 'gtheme' ),
					] ),
				]
			)
		);

		$setting = 'wrap_base_ends_with';
		$manager->add_setting( $setting, [
			'default'           => gThemeOptions::info( 'wrap_base_ends_with', 'end' ),
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'manage_options',
		] );

		$manager->add_control(
			new \WP_Customize_Control(
				$manager,
				sprintf( '%s_%s', $this->base, $setting ),
				[
					'section'     => $section,
					'settings'    => $setting,
					'type'        => 'radio',
					'label'       => esc_html_x( 'Wrapping Ends', 'Customizer: Setting Title', 'gtheme' ),
					'description' => esc_html_x( 'Defines the block that ends on content wraps.', 'Customizer: Setting Description', 'gtheme' ),
					'choices'     => gThemeOptions::info( 'wrap_base_ends_with_choices', [
						'spotlight' => esc_html_x( 'Spotlight &mdash; After the Content', 'Customizer: Setting Option', 'gtheme' ),
						'end'       => esc_html_x( 'End &mdash; Before the Footer', 'Customizer: Setting Option', 'gtheme' ),
						'none'      => esc_html_x( 'None', 'Customizer: Setting Option', 'gtheme' ),
					] ),
				]
			)
		);
	}

	public static function baseStartsWith( $fallback = 'navbar' )
	{
		return get_theme_mod( 'wrap_base_starts_with',
			gThemeOptions::info( 'wrap_base_starts_with', $fallback ) ) ?: 'none';
	}

	public static function baseEndsWith( $fallback = 'end' )
	{
		return get_theme_mod( 'wrap_base_ends_with',
			gThemeOptions::info( 'wrap_base_ends_with', $fallback ) ) ?: 'none';
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
	// http://scribu.net/wordpress/theme-wrappers.html
	// https://gist.github.com/1209013

	static $main_template; // stores the full path to the main template file
	static $base_template; // stores the base name of the template file; e.g. 'page' for 'page.php' etc.

	public static function template_include( $template )
	{
		if ( in_array( get_page_template_slug(), [ 'systempage.php' ] ) )
			self::define( 'GTHEME_IS_SYSTEM_PAGE', TRUE );

		self::$main_template = $template;

		self::$base_template = substr( basename( self::$main_template ), 0, -4 );

		if ( 'index' == self::$base_template )
			self::$base_template = FALSE;

		if ( in_array( self::$base_template, [ 'buddypress', 'bbpress', 'woocommerce', 'contact' ] ) )
			self::define( 'GTHEME_IS_SYSTEM_PAGE', TRUE );

		$templates = [ 'base.php' ];

		if ( self::$base_template )
			array_unshift( $templates, sprintf( 'base-%s.php', self::$base_template ) );

		return locate_template( $templates );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	// NOTE: DEPRECATED // BACK COMP
	// USED IN: head.php
	public static function html_title()
	{
		echo "\t".'<title>'.wp_get_document_title().'</title>'."\n";
	}

	// USED IN: head.php
	// @REF: `get_language_attributes()`
	public static function htmlOpen( $after = '' )
	{
		$atts    = [];
		$data    = [];
		$classes = [ 'no-js' ];

		if ( gThemeBootstrap::version() )
			$data['bs-theme'] = gThemeOptions::getColorScheme( 'bootstrap_color_scheme', 'auto' ); // dark/light/auto

		if ( gThemeUtilities::isPrint() )
			$classes[] = 'html-print';

		if ( is_admin_bar_showing() )
			$classes[] = 'html-admin-bar';

		if ( gThemeOptions::info( 'rtl', FALSE ) )
			$atts[] = 'dir="rtl"';

		if ( $lang = get_bloginfo( 'language', 'display' ) )
			$atts[] = "lang=\"$lang\"";

		if ( count( $atts ) )
			$atts = ' '.apply_filters( 'language_attributes', implode( ' ', $atts ) );
		else
			$atts = '';

		echo '<html'.$atts.gThemeHTML::propData( $data ).' class="'.gThemeHTML::prepClass( $classes ).'">'."\n".$after."\n";
	}

	// USED IN: head.php
	// @REF: https://core.trac.wordpress.org/ticket/12563
	public static function bodyOpen( $before = '', $extra_atts = '' )
	{
		echo "\n".$before;

		echo '<body ';

		if ( gThemeOptions::info( 'copy_disabled', FALSE ) )
			echo 'onContextMenu="return false" '; // http://stackoverflow.com/a/3021151

		body_class();

		echo $extra_atts;

		echo '>'."\n";

		do_action( 'gtheme_wrap_body_open' );

		do_action( 'wp_body_open' ); // @since WP 5.2.0
	}

	// USED IN: foot.php
	public static function bodyClose( $after = '' )
	{
		do_action( 'gtheme_wrap_body_close' );

		?><script>var html=document.querySelector("html");html.classList.remove("no-js");</script><?php

		echo "\n".'</body>';

		echo "\n".$after;
	}

	public static function wrapperOpen( $context, $row_class = FALSE, $container_class = '', $wrap_class = '' )
	{
		if ( empty( $context ) )
			return;

		if ( FALSE !== $wrap_class )      echo '<div class="'.gThemeHTML::prepClass( 'wrapper', '-'.$context, gThemeOptions::info( 'wrap_wrap_class', '' ), $wrap_class ).'">'."\n";
		if ( FALSE !== $container_class ) echo '<div class="'.gThemeHTML::prepClass( 'container-wrap', '-'.$context, gThemeOptions::info( 'wrap_container_class', 'container-xl' ), $container_class ).'">'."\n";
		if ( FALSE !== $row_class )       echo '<div class="'.gThemeHTML::prepClass( 'row', '-'.$context, gThemeOptions::info( 'wrap_row_class', '' ), $row_class ).'">'."\n";

		echo '<!-- OPEN: `'.$context.'` -->'."\n";
	}

	public static function wrapperClose( $context, $count = 2 )
	{
		if ( empty( $context ) )
			return;

		echo "\n".'<!-- CLOSE: `'.$context.'` -->'."\n";

		for ( $i = 0; $i < $count; $i++ )
			echo '</div>';
	}
}

function gtheme_template_path() {
	return gThemeWrap::$main_template;
}

function gtheme_template_base() {
	return gThemeWrap::$base_template;
}
