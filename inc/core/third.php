<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeThird extends gThemeBaseCore
{

	/**
	 * Take the input of a "Twitter" field, decide whether it's a handle
	 * or a URL, and generate a URL
	 *
	 * @source: https://gist.github.com/boonebgorges/5537311
	 *
	 * @param  string  $string provided twitter token
	 * @param  boolean $url    convert token to profile link
	 * @param  string  $base   prefix if the url
	 * @return string          handle or the url
	 */
	public static function getTwitter( $string, $url = FALSE, $base = 'https://twitter.com/' )
	{
		$parts = wp_parse_url( $string );

		if ( empty( $parts['host'] ) )
			$handle = 0 === strpos( $string, '@' ) ? substr( $string, 1 ) : $string;
		else
			$handle = trim( $parts['path'], '/\\' );

		return $url ? trailingslashit( $base.$handle ) : '@'.$handle;
	}

	// @REF: https://generatewp.com/easy-custom-mobile-chrome-address-bar-colors-wordpress/
	// @REF: `rest_parse_hex_color()`
	public static function htmlThemeColor( $color )
	{
		if ( ! $color )
			return;

		if ( ! preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color, $matches ) )
			return;

		echo '<meta name="theme-color" content="'.$color.'" />'."\n";
		echo '<meta name="msapplication-navbutton-color" content="'.$color.'">'."\n";
		// echo '<meta name="apple-mobile-web-app-capable" content="yes">'."\n"; // DEPRECATED
		echo '<meta name="mobile-web-app-capable" content="yes">'."\n";
		echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">'."\n";
	}
}
