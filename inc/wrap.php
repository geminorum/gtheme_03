<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWrap extends gThemeModuleCore
{

	// non-admin only
	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'images_404' => TRUE,
		), $args ) );

		add_action( 'before_signup_header', array( $this, 'before_signup_header' ) );
		add_action( 'activate_header', array( $this, 'activate_header' ) );

		if ( $images_404 )
			add_filter( 'template_include', array( $this, 'template_include_404_images' ), -1 );

		add_filter( 'template_include', array( 'gThemeWrap', 'wrap' ), 99 );
	}

	// http://wpengineer.com/2377/implement-404-image-in-your-theme/
	public function template_include_404_images( $template )
	{
		if ( is_admin() )
			return $template;

		if ( ! is_404() )
			return $template;

		// @version 2011.12.23
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
		defined( 'GTHEME_IS_WP_SIGNUP' ) or define( 'GTHEME_IS_WP_SIGNUP', TRUE );
	}

	public function activate_header()
	{
		defined( 'GTHEME_IS_WP_ACTIVATE' ) or define( 'GTHEME_IS_WP_ACTIVATE', TRUE );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
	// http://scribu.net/wordpress/theme-wrappers.html
	// https://gist.github.com/1209013

	static $main_template; // stores the full path to the main template file
	static $base_template; // stores the base name of the template file; e.g. 'page' for 'page.php' etc.

	public static function wrap( $template )
	{
		self::$main_template = $template;

		self::$base_template = substr( basename( self::$main_template ), 0, -4 );

		if ( 'index' == self::$base_template )
			self::$base_template = FALSE;

		$templates = array( 'base.php' );

		if ( self::$base_template )
			array_unshift( $templates, sprintf( 'base-%s.php', self::$base_template ) );

		return locate_template( $templates );
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	// FIXME: DEPRECATED // BACK COMP // USED IN: head.php
	public static function html_title()
	{
		echo "\t".'<title>'.wp_get_document_title().'</title>'."\n";
	}

	// USED IN: head.php
	public static function htmlOpen( $after = '' )
	{
		$atts    = [];
		$classes = [ 'no-js' ];

		if ( is_admin_bar_showing() )
			$classes[] = 'html-admin-bar';

		if ( gThemeOptions::info( 'rtl', FALSE ) )
			$atts[] = 'dir="rtl"';

		if ( $lang = get_bloginfo( 'language', 'display' ) )
			$atts[] = "lang=\"$lang\"";

		if ( $font_stack = gThemeOptions::info( 'css_font_stack', FALSE ) )
			$atts[] = 'data-font-stack=\''.wp_json_encode( $font_stack ).'\'';

		if ( count( $atts ) )
			$atts = ' '.apply_filters( 'language_attributes', implode( ' ', $atts ) );
		else
			$atts = '';

		echo '<html'.$atts.' class="'.join( ' ', $classes ).'">'."\n".$after."\n";
	}

	// USED IN: head.php
	public static function bodyOpen( $before = '', $extra_atts = '' )
	{
		echo "\n".$before;

		echo '<body ';

		if ( gThemeOptions::info( 'copy_disabled', FALSE ) )
			echo 'onContextMenu="return false" '; // http://stackoverflow.com/a/3021151

		body_class();

		echo $extra_atts;

		echo '>';

		do_action( 'template_body_top' );
	}
}

function gtheme_template_path() {
	return gThemeWrap::$main_template;
}

function gtheme_template_base() {
	return gThemeWrap::$base_template;
}
