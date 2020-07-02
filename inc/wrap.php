<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWrap extends gThemeModuleCore
{

	// non-admin only
	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'images_404' => FALSE,
		], $args ) );

		add_action( 'before_signup_header', [ $this, 'before_signup_header' ] );
		add_action( 'activate_header', [ $this, 'activate_header' ] );

		if ( $images_404 && ! is_admin() )
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

		defined( 'GTHEME_IS_WP_SIGNUP' )
			or define( 'GTHEME_IS_WP_SIGNUP', TRUE );

		defined( 'GTHEME_SOCIAL_META_DISABLED' )
			or define( 'GTHEME_SOCIAL_META_DISABLED', TRUE );
	}

	public function activate_header()
	{
		gThemeWordPress::doNotCache();

		defined( 'GTHEME_IS_WP_ACTIVATE' )
			or define( 'GTHEME_IS_WP_ACTIVATE', TRUE );

		defined( 'GTHEME_SOCIAL_META_DISABLED' )
			or define( 'GTHEME_SOCIAL_META_DISABLED', TRUE );
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
			defined( 'GTHEME_IS_SYSTEM_PAGE' )
				or define( 'GTHEME_IS_SYSTEM_PAGE', TRUE );

		self::$main_template = $template;

		self::$base_template = substr( basename( self::$main_template ), 0, -4 );

		if ( 'index' == self::$base_template )
			self::$base_template = FALSE;

		if ( in_array( self::$base_template, [ 'buddypress', 'bbpress' ] ) )
			defined( 'GTHEME_IS_SYSTEM_PAGE' )
				or define( 'GTHEME_IS_SYSTEM_PAGE', TRUE );

		$templates = [ 'base.php' ];

		if ( self::$base_template )
			array_unshift( $templates, sprintf( 'base-%s.php', self::$base_template ) );

		return locate_template( $templates );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	// FIXME: DEPRECATED // BACK COMP
	// USED IN: head.php
	public static function html_title()
	{
		echo "\t".'<title>'.wp_get_document_title().'</title>'."\n";
	}

	// USED IN: head.php
	public static function htmlOpen( $after = '' )
	{
		$atts    = [];
		$classes = [ 'no-js' ];

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

		echo '<html'.$atts.' class="'.gThemeHTML::prepClass( $classes ).'">'."\n".$after."\n";
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

		?><script type="text/javascript">var html=document.querySelector("html");html.classList.remove("no-js");</script><?php

		echo "\n".'</body>';

		echo "\n".$after;
	}
}

function gtheme_template_path() {
	return gThemeWrap::$main_template;
}

function gtheme_template_base() {
	return gThemeWrap::$base_template;
}
