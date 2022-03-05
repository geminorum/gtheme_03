<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeUtilities extends gThemeBaseCore
{

	public static function enqueueSlick()
	{
		static $enqueued = FALSE;

		if ( $enqueued )
			return TRUE;

		// `jQuery(function(r){r(".wrap-slick-carousel .-carousel").slick({rtl:"rtl"===r("html").attr("dir")})});`
		$script = <<<'JS'
jQuery(function(r){r(".wrap-slick-carousel .-carousel").slick()});
JS;

		wp_enqueue_script( 'gtheme-slick', GTHEME_URL.'/js/vendor/slick-carousel.min.js', [], '1.8.1', TRUE );
		wp_add_inline_script( 'gtheme-slick', $script );

		// NOTE: for reference
		// wp_enqueue_script( 'slick-carousel', GTHEME_URL.'/js/slick.carousel'.( SCRIPT_DEBUG ? '' : '.min' ).'.js', [ 'jquery', 'gtheme-slick' ], GTHEME_VERSION, TRUE );

		return $enqueued = TRUE;
	}

	public static function enqueueAutosize( $ver = '4.0.2' )
	{
		wp_enqueue_script( 'gtheme-autosize', '//cdn.jsdelivr.net/npm/autosize@'.$ver.'/dist/autosize.min.js', [], NULL, TRUE );
		wp_add_inline_script( 'gtheme-autosize', "autosize(document.querySelectorAll('textarea'));" );
	}

	public static function enqueueTimeAgo()
	{
		$callback = [ 'gPersianDateTimeAgo', 'enqueue' ];

		if ( ! is_callable( $callback ) )
			return FALSE;

		return call_user_func( $callback );
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

	public static function prepContact( $value, $title = NULL )
	{
		if ( is_email( $value ) )
			$prepared = gThemeHTML::mailto( $value, $title );

		else if ( gThemeURL::isValid( $value ) )
			$prepared = gThemeHTML::link( $title, gThemeURL::untrail( $value ) );

		else if ( is_numeric( str_ireplace( [ '+', '-', '.' ], '', $value ) ) )
			$prepared = gThemeHTML::tel( $value, FALSE, $title );

		else
			$prepared = gThemeHTML::escape( $value );

		return apply_filters( 'gtheme_prep_contact', $prepared, $value, $title );
	}

	public static function getSeparated( $string, $delimiters = NULL, $limit = NULL, $delimiter = '|' )
	{
		if ( is_array( $string ) )
			return $string;

		if ( is_null( $delimiters ) )
			$delimiters = [
				// '/',
				'،',
				'؛',
				';',
				',',
				// '-',
				// '_',
				'|',
			];

		$string = str_ireplace( $delimiters, $delimiter, $string );

		$seperated = is_null( $limit )
			? explode( $delimiter, $string )
			: explode( $delimiter, $string, $limit );

		return gThemeArraay::prepString( $seperated );
	}

	// FIXME: DEPRECATED
	public static function wordWrap( $text, $min = 2 )
	{
		self::_dep( 'gThemeText::wordWrap()' );

		return gThemeText::wordWrap( $text, $min );
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

	public static function isSystemPage()
	{
		if ( defined( 'GTHEME_IS_WP_SIGNUP' ) && GTHEME_IS_WP_SIGNUP )
			return TRUE;

		if ( defined( 'GTHEME_IS_WP_ACTIVATE' ) && GTHEME_IS_WP_ACTIVATE )
			return TRUE;

		if ( defined( 'GTHEME_IS_SYSTEM_PAGE' ) && GTHEME_IS_SYSTEM_PAGE )
			return TRUE;

		return FALSE;
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
		$home = gThemeOptions::info( 'home_url_override', gThemeOptions::getHomeURL() );
		return $display ? gThemeURL::prepTitle( $home ) : $home;
	}

	// FIXME: DEPRECATED
	public static function sanitize_sep( $sep = 'def', $context = 'default_sep', $def = ' ' )
	{
		self::_dep();

		if ( 'def' == $sep )
			return gThemeOptions::info( $context, $def );

		if ( FALSE === $sep )
			return ' ';

		return $sep;
	}

	// FIXME: DEPRECATED: use self::recursiveParseArgs()
	public static function parse_args_r( &$a, $b )
	{
		// self::_dep( 'self::recursiveParseArgs()' );

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

	// FIXME: DEPRECATED: USE: `gThemeHTML::notice()`
	public static function notice( $notice, $class = 'success updated fade', $echo = TRUE )
	{
		self::_dep( 'gThemeHTML::notice()' );

		if ( is_admin() )
			$template = '<div id="message" class="%1$s"><p>%2$s</p></div>';
		else
			$template = '<div class="alert alert-%1$s alert-dismissible" role="alert">'
						.'<button type="button" class="close" data-dismiss="alert">'
						.'<span aria-hidden="true">&times;</span><span class="screen-reader-text sr-only visually-hidden">'
						._x( 'Close', 'Alert button (screen reader only)', 'gtheme' )
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

	public static function joinItems( $items )
	{
		return
			_x( '&rdquo;', 'Utilities: Join Items Helper', 'gtheme' )
			.implode( _x( '&ldquo; and &rdquo;', 'Utilities: Join Items Helper', 'gtheme' ),
				array_filter( array_merge( [
					implode( _x( '&ldquo;, &rdquo;', 'Utilities: Join Items Helper', 'gtheme' ),
					array_slice( $items, 0, -1 ) ) ],
					array_slice( $items, -1 ) ) ) )
			._x( '&ldquo;', 'Utilities: Join Items Helper', 'gtheme' ).'.';
	}

	// @REF: https://en.wikipedia.org/wiki/ISO_639
	// @REF: http://stackoverflow.com/a/16838443
	// @REF: `bp_core_register_common_scripts()`
	public static function getISO639( $locale = NULL )
	{
		if ( is_null( $locale ) )
			$locale = get_locale();

		if ( ! $locale )
			return 'en';

		$ISO639 = str_replace( '_', '-', strtolower( $locale ) );
		return substr( $ISO639, 0, strpos( $ISO639, '-' ) );
	}
}
