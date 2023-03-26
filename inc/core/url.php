<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeURL extends gThemeBaseCore
{
	// @SOURCE: http://stackoverflow.com/a/8891890
	public static function current( $trailingslashit = FALSE, $forwarded_host = FALSE )
	{
		$ssl = ( ! empty( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] );

		$protocol = strtolower( $_SERVER['SERVER_PROTOCOL'] );
		$protocol = substr( $protocol, 0, strpos( $protocol, '/' ) ).( ( $ssl ) ? 's' : '' );

		$port = $_SERVER['SERVER_PORT'];
		$port = ( ( ! $ssl && '80' == $port ) || ( $ssl && '443' == $port ) ) ? '' : ':'.$port;

		$host = ( $forwarded_host && isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : NULL );
		$host = isset( $host ) ? $host : $_SERVER['SERVER_NAME'].$port;

		$current = $protocol.'://'.$host.$_SERVER['REQUEST_URI'];
		return $trailingslashit ? self::trail( $current ) : $current;
	}

	// like twitter links
	public static function prepTitle( $url, $slash = FALSE )
	{
		$title = preg_replace( '|^http(s)?://(www\.)?|i', '', $url );
		$title = self::untrail( $title );
		return $slash ? str_ireplace( array( '/', '\/' ), '-', $title ) : $title;
	}

	public static function prepTitleQuery( $string )
	{
		return str_ireplace( array( '_', '-' ), ' ', urldecode( $string ) );
	}

	// will remove trailing forward and backslashes if it exists already before adding
	// a trailing forward slash. This prevents double slashing a string or path.
	// @SOURCE: `trailingslashit()`
	public static function trail( $path )
	{
		return self::untrail( $path ).'/';
	}

	// removes trailing forward slashes and backslashes if they exist.
	// @SOURCE: `untrailingslashit()`
	public static function untrail( $path )
	{
		return rtrim( $path, '/\\' );
	}

	// @ALSO: `esc_url_raw( $url ) === $url`, `wp_http_validate_url()`
	// @REF: https://halfelf.org/2015/url-validation/
	// @REF: https://d-mueller.de/blog/why-url-validation-with-filter_var-might-not-be-a-good-idea/
	public static function isValid( $url )
	{
		$url = trim( $url );

		if ( empty( $url ) )
			return FALSE;

		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) )
			return FALSE;

		// $url = filter_var( $url, FILTER_SANITIZE_STRING );
		$url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );

		if ( FALSE !== filter_var( $url, FILTER_VALIDATE_URL ) )
			return TRUE;

		return FALSE;
	}
}
