<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeUtilities extends gThemeBaseCore
{

	// @REF: http://davidwalsh.name/word-wrap-mootools-php
	// @REF: https://css-tricks.com/preventing-widows-in-post-titles/
	public static function wordWrap( $text, $min = 2 )
	{
		$return = $text;

		if ( strlen( trim( $text ) ) ) {
			$arr = explode( ' ', trim( $text ) );

			if ( count( $arr ) >= $min ) {
				$arr[count( $arr ) - 2] .= '&nbsp;'.$arr[count( $arr ) - 1];
				array_pop( $arr );
				$return = implode( ' ', $arr );
			}
		}

		return $return;
	}

	// @SOURCE: http://bavotasan.com/2012/trim-characters-using-php/
	public static function trimChars( $text, $length = 45, $append = '&hellip;' )
	{
		$length = (int) $length;
		$text   = trim( strip_tags( $text ) );

		if ( strlen( $text ) > $length ) {

			$text  = substr( $text, 0, $length + 1 );
			$words = preg_split( "/[\s]|&nbsp;/", $text, -1, PREG_SPLIT_NO_EMPTY );

			preg_match( "/[\s]|&nbsp;/", $text, $lastchar, 0, $length );

			if ( empty( $lastchar ) )
				array_pop( $words );

			$text = implode( ' ', $words ).$append;
		}

		return $text;
	}

	public static function getURILength( $url, $default = 0 )
	{
		if ( $headers = wp_get_http_headers( $url ) )
			if ( isset( $headers['content-length'] ) )
				return (int) $headers['content-length'];

		return $default;
	}

	public static function isPrint()
	{
		return isset( $_GET['print'] );
	}

	public static function isRTL( $true = TRUE, $false = FALSE )
	{
		return gThemeOptions::info( 'rtl', is_rtl() ) ? $true : $false;
	}

	public static function IP()
	{
		if ( getenv( 'HTTP_CLIENT_IP' ) )
			return getenv( 'HTTP_CLIENT_IP' );

		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
			return getenv( 'HTTP_X_FORWARDED_FOR' );

		if ( getenv( 'HTTP_X_FORWARDED' ) )
			return getenv( 'HTTP_X_FORWARDED' );

		if ( getenv( 'HTTP_FORWARDED_FOR' ) )
			return getenv( 'HTTP_FORWARDED_FOR' );

		if ( getenv( 'HTTP_FORWARDED' ) )
			return getenv( 'HTTP_FORWARDED' );

		return $_SERVER['REMOTE_ADDR'];
	}

	public static function home()
	{
		return gThemeOptions::info( 'home_url_override', esc_url( home_url( '/' ) ) );
	}

	public static function sanitize_sep( $sep = 'def', $context = 'default_sep', $def = ' ' )
	{
		if ( 'def' == $sep )
			return gThemeOptions::info( $context, $def );

		if ( FALSE === $sep )
			return ' ';

		return $sep;
	}

	// FIXME: DEPRECATED: use self::recursiveParseArgs()
	public static function parse_args_r( &$a, $b )
	{
		// self::__dep( 'self::recursiveParseArgs()' );

		$a = (array) $a;
		$b = (array) $b;
		$r = $b;

		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $r[$k] ) ) {
				$r[$k] = self::parse_args_r( $v, $r[$k] );
			} else {
				$r[$k] = $v;
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

	private static function _tag_open( $tag, $atts, $content = TRUE )
	{
		$html = '<'.$tag;
		foreach ( $atts as $key => $att ) {

			if ( is_array( $att ) && count( $att ) )
				$att = implode( ' ', array_unique( array_filter( $att ) ) );

			if ( 'selected' == $key )
				$att = ( $att ? 'selected' : FALSE );

			if ( 'checked' == $key )
				$att = ( $att ? 'checked' : FALSE );

			if ( 'readonly' == $key )
				$att = ( $att ? 'readonly' : FALSE );

			if ( 'disabled' == $key )
				$att = ( $att ? 'disabled' : FALSE );

			if ( FALSE === $att )
				continue;

			if ( 'class' == $key )
				// $att = self::sanitize_class( $att, FALSE );
				$att = $att;
			else if ( 'href' == $key || 'src' == $key )
				$att = esc_url( $att );
			// else if ( 'input' == $tag && 'value' == $key )
			// 	$att = $att;
			else
				$att = esc_attr( $att );

			$html .= ' '.$key.'="'.trim( $att ).'"';
		}

		if ( FALSE === $content )
			return $html.' />';

		return $html.'>';
	}

	public static function html( $tag, $atts = array(), $content = FALSE, $sep = '' )
	{
		self::__dev_dep();

		$html = self::_tag_open( $tag, $atts, $content );

		if ( FALSE === $content )
			return $html.$sep;

		if ( is_null( $content ) )
			return $html.'</'.$tag.'>'.$sep;

		return $html.$content.'</'.$tag.'>'.$sep;
	}

	// WordPress core duplicate of : sanitize_html_class()
	public static function sanitize_class( $class, $fallback = '' )
	{
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class ); // Strip out any % encoded octets
		$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized ); // Limit to A-Z,a-z,0-9,_,-

		if ( '' == $sanitized )
			$sanitized = $fallback;

		return $sanitized;
	}

	// FIXME: DEPRECATED: use gThemeUtilities::linkStyleSheet()
	public static function link_stylesheet( $url, $attr = 'media="all"' )
	{
		self::__dep( 'gThemeUtilities::linkStyleSheet()' );
		echo "\t".'<link rel="stylesheet" href="'.esc_url( $url ).'" type="text/css" '.$attr.' />'."\n";
	}

	public static function linkStyleSheet( $url, $version = GTHEME_VERSION, $media = FALSE )
	{
		if ( is_array( $version ) )
			$url = add_query_arg( $version, $url );
		else
			$url = add_query_arg( 'ver', $version, $url );

		echo "\t".gThemeHTML::tag( 'link', array(
			'rel' => 'stylesheet',
			'href' => $url,
			'type' => 'text/css',
			'media' => $media,
		) )."\n";
	}

	// http://stackoverflow.com/a/9241873
	public static function json_merge( $first, $second )
	{
		return wp_json_encode(
			array_merge_recursive(
				json_decode( $first,  TRUE ),
				json_decode( $second, TRUE )
			)
		);
	}

	public static function notice( $notice, $class = 'success updated fade', $echo = TRUE )
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

	public static function headerNav( $uri = '', $active = '', $sub_pages = array(), $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( ! count( $sub_pages ) )
			return;

		$html = '';

		foreach ( $sub_pages as $page_slug => $sub_page )
			$html .= gThemeHTML::tag( 'a', array(
				'class' => 'nav-tab '.$class_prefix.$page_slug.( $page_slug == $active ? ' nav-tab-active' : '' ),
				'href'  => add_query_arg( 'sub', $page_slug, $uri ),
			), esc_html( $sub_page ) );

		echo gThemeHTML::tag( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	// NOT PROPERLY WORKING ON ADMIN
	// http://kovshenin.com/2012/current-url-in-wordpress/
	// http://www.stephenharris.info/2012/how-to-get-the-current-url-in-wordpress/
	public static function getCurrentURL( $trailingslashit = FALSE )
	{
		global $wp;

		if ( is_admin() )
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		else
			$current_url = home_url( add_query_arg( array(), ( empty( $wp->request ) ? FALSE : $wp->request ) ) );

		if ( $trailingslashit )
			return trailingslashit( $current_url );

		return $current_url;
	}
}
