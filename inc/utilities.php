<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeUtilities extends gThemeModuleCore
{
	
	public static function dump( $var, $htmlSafe = true )
	{
		$result = var_export( $var, true );
		echo '<pre dir="ltr" style="text-align:left;direction:ltr;">'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>';
	}

	// http://davidwalsh.name/word-wrap-mootools-php
	public static function word_wrap( $text, $min = 2 )
	{
		$return = $text;
		$arr = explode( ' ', $text );

		if ( count( $arr ) >= $min ) {
			$arr[count( $arr ) - 2] .= '&nbsp;'.$arr[count( $arr ) - 1];
			array_pop( $arr );
			$return = implode( ' ', $arr );
		}

		return $return;
	}

	// http://bavotasan.com/2012/trim-characters-using-php/
	public static function trim_characters( $text, $length = 45, $append = '&hellip;' )
	{

		$length = (int) $length;
		$text = trim( strip_tags( $text ) );

		if ( strlen( $text ) > $length ) {
			$text = substr( $text, 0, $length + 1 );
			$words = preg_split( "/[\s]|&nbsp;/", $text, -1, PREG_SPLIT_NO_EMPTY );
			preg_match( "/[\s]|&nbsp;/", $text, $lastchar, 0, $length );
			if ( empty( $lastchar ) )
				array_pop( $words );

			$text = implode( ' ', $words ) . $append;
		}

		return $text;
	}

	public static function get_uri_length( $url, $default = '' )
	{
		if ( $headers = wp_get_http_headers( $url ) )
			return (int) $headers['content-length'];

		return $default;
	}

	// debug on production env
	public static function is_debug()
	{
		if ( WP_DEBUG && WP_DEBUG_DISPLAY && ! self::is_dev() )
			return true;

		return false;
	}

	// DEPRECATED: use gThemeUtilities::isDev();
	public static function is_dev()
	{
		return self::isDev();
	}

	public static function isDev()
	{
		if ( defined( 'GTHEME_DEV_ENVIRONMENT' ) && constant( 'GTHEME_DEV_ENVIRONMENT' ) )
			return TRUE;

		if ( defined( 'WP_STAGE' ) && 'development' == constant( 'WP_STAGE' ) )
			return TRUE;

		// TODO : check stage production and debug constant then true

		return FALSE;
	}

	public static function is_print()
	{
		return ( isset( $_GET['print'] ) && $_GET['print'] == 'print' ) ? true : false;
	}

	// DEPRECATED
	public static function is_rtl()
	{
		return gThemeOptions::info( 'rtl', is_rtl() );
	}

	public static function isRTL( $true = TRUE, $false = FALSE )
	{
		return gThemeOptions::info( 'rtl', is_rtl() ) ? $true : $false;
	}

	public static function home()
	{
		return gThemeOptions::info( 'home_url_override', esc_url( home_url( '/' ) ) );
	}


	public static function sanitize_sep( $sep = 'def', $context = 'default_sep', $def = ' ' )
	{
		if ( 'def' == $sep )
			return gThemeOptions::info( $context, $def );

		if ( false === $sep )
			return ' ';

		return $sep;
	}

	// https://gist.github.com/boonebgorges/5510970
	public static function parse_args_r( &$a, $b )
	{
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;

		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = self::parse_args_r( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}

		return $r;
	}

	public static function update_count_callback( $terms, $taxonomy )
	{
		global $wpdb;
		foreach ( (array) $terms as $term ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}

	private static function _tag_open( $tag, $atts, $content = true )
	{
		$html = '<'.$tag;
		foreach( $atts as $key => $att ) {

			if ( is_array( $att ) && count( $att ) )
				$att = implode( ' ', array_unique( array_filter( $att ) ) );

			if ( 'selected' == $key )
				$att = ( $att ? 'selected' : false );

			if ( 'checked' == $key )
				$att = ( $att ? 'checked' : false );

			if ( 'readonly' == $key )
				$att = ( $att ? 'readonly' : false );

			if ( 'disabled' == $key )
				$att = ( $att ? 'disabled' : false );

			if ( false === $att )
				continue;

			if ( 'class' == $key )
				//$att = sanitize_html_class( $att, false );
				$att = $att;
			else if ( 'href' == $key || 'src' == $key )
				$att = esc_url( $att );
			//else if ( 'input' == $tag && 'value' == $key )
				//$att = $att;
			else
				$att = esc_attr( $att );

			$html .= ' '.$key.'="'.trim( $att ).'"';
		}

		if ( false === $content )
			return $html.' />';

		return $html.'>';
	}

	public static function html( $tag, $atts = array(), $content = false, $sep = '' )
	{
		$html = self::_tag_open( $tag, $atts, $content );

		if ( false === $content )
			return $html.$sep;

		if ( is_null( $content ) )
			return $html.'</'.$tag.'>'.$sep;

		return $html.$content.'</'.$tag.'>'.$sep;
	}

	// DEPRECATED: use gThemeUtilities::linkStyleSheet()
	public static function link_stylesheet( $url, $attr = 'media="all"' )
	{
		echo "\t".'<link rel="stylesheet" href="'.esc_url( $url ).'" type="text/css" '.$attr.' />'."\n";
	}

	public static function linkStyleSheet( $url, $version = GTHEME_VERSION, $media = false )
	{
		echo "\t".self::html( 'link', array(
			'rel' => 'stylesheet',
			'href' => add_query_arg( 'ver', $version, $url ),
			'type' => 'text/css',
			'media' => $media,
		) )."\n";
	}

	// http://stackoverflow.com/a/9241873
	public static function json_merge( $first, $second )
	{
		return json_encode(
			array_merge_recursive(
				json_decode( $first, true ),
				json_decode( $second, true )
			)
		);
	}

	public static function notice( $notice, $class = 'success updated fade', $echo = true )
	{
		if ( is_admin() )
			$template = '<div id="message" class="%1$s"><p>%2$s</p></div>';
		else
			$template = '<div class="alert alert-%1$s alert-dismissible" role="alert">'
					   .'<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">'
					   ._x( 'Close', 'Alert button (screen reader only)', GTHEME_TEXTDOMAIN )
					   .'</span></button>%2$s</div>'; // bootstrap dismissible alert

		$html = sprintf( $template, $class, $notice );
		if ( ! $echo )
			return $html;
		echo $html;
	}

	public static function headerNav( $settings_uri = '', $active = '', $sub_pages = array(), $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( ! count( $sub_pages ) )
			return;

		$html = '';

		foreach ( $sub_pages as $page_slug => $sub_page )
			$html .= self::html( 'a', array(
				'class' => 'nav-tab '.$class_prefix.$page_slug.( $page_slug == $active ? ' nav-tab-active' : '' ),
				'href' => add_query_arg( 'sub', $page_slug, $settings_uri ),
			), esc_html( $sub_page ) );

		echo self::html( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	// NOT PROPERLY WORKING ON ADMIN
	// http://kovshenin.com/2012/current-url-in-wordpress/
	// http://www.stephenharris.info/2012/how-to-get-the-current-url-in-wordpress/
	public static function getCurrentURL( $trailingslashit = false )
	{
		global $wp;

		if ( is_admin() )
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		else
			$current_url = home_url( add_query_arg( array(), ( empty( $wp->request ) ? false : $wp->request ) ) );

		if ( $trailingslashit )
			return trailingslashit( $current_url );

		return $current_url;
	}

	public static function getPostTypes()
	{
		$list = array();
		$post_types = get_post_types( array(
			'public' => true,
			'_builtin' => true,
		), 'objects' );

		foreach ( $post_types as $post_type => $post_type_obj )
			$list[$post_type] = $post_type_obj->labels->name;

		return $list;
	}
}
