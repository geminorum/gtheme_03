<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeUtilities extends gThemeBaseCore
{

	public static function enqueueAutosize( $ver = '4.0.2' )
	{
		wp_enqueue_script( 'gtheme-autosize', '//cdn.jsdelivr.net/npm/autosize@'.$ver.'/dist/autosize.min.js', [], NULL, TRUE );
		wp_add_inline_script( 'gtheme-autosize', "autosize(document.querySelectorAll('textarea'));" );
	}

	public static function prepTitle( $text, $post_id = 0 )
	{
		if ( ! $text )
			return '';

		$text = apply_filters( 'the_title', $text, $post_id );
		$text = apply_filters( 'string_format_i18n', $text );
		$text = apply_filters( 'gnetwork_typography', $text );

		return trim( $text );
	}

	public static function prepDescription( $text, $shortcode = TRUE, $autop = TRUE )
	{
		if ( ! $text )
			return '';

		if ( $shortcode )
			$text = do_shortcode( $text, TRUE );

		$text = apply_filters( 'html_format_i18n', $text );
		$text = apply_filters( 'gnetwork_typography', $text );

		return $autop ? wpautop( $text ) : $text;
	}

	// @REF: http://davidwalsh.name/word-wrap-mootools-php
	// @REF: https://css-tricks.com/preventing-widows-in-post-titles/
	public static function wordWrap( $text, $min = 2 )
	{
		$return = $text;

		// FIXME: must convert back all &nbsp; to space

		if ( strlen( trim( $text ) ) ) {
			$arr = explode( ' ', trim( $text ) );

			if ( count( $arr ) >= $min ) {
				$arr[count( $arr ) - 2].= '&nbsp;'.$arr[count( $arr ) - 1];
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
		global $wp_query;

		if ( GTHEME_PRINT_QUERY && ! empty( $wp_query->query_vars )
			&& array_key_exists( GTHEME_PRINT_QUERY, $wp_query->query_vars ) )
				return TRUE;

		return isset( $_GET[(GTHEME_PRINT_QUERY ?: 'print')] );
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

	public static function home( $display = FALSE )
	{
		$home = gThemeOptions::info( 'home_url_override', esc_url( home_url( '/' ) ) );
		return $display ? gThemeURL::prepTitle( $home ) : $home;
	}

	// FIXME: must be depricated
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
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), [ 'term_taxonomy_id' => $term ] );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}

	public static function linkStyleSheet( $url, $version = GTHEME_CHILD_VERSION, $media = FALSE, $echo = TRUE )
	{
		return gThemeHTML::linkStyleSheet( $url, $version, $media, $echo );
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

	// FIXME: DROP THIS
	public static function getCurrentURL( $trailingslashit = FALSE, $forwarded_host = FALSE )
	{
		return gThemeURL::current( $trailingslashit, $forwarded_host );
	}
}
