<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeThird extends gThemeBaseCore
{

	// @REF: https://gist.github.com/boonebgorges/5537311
	public static function getTwitter( $string, $url = FALSE )
	{
		$parts = parse_url( $string );

		if ( empty( $parts['host'] ) )
			$handle = 0 === strpos( $string, '@' ) ? substr( $string, 1 ) : $string;
		else
			$handle = trim( $parts['path'], '/\\' );

		return $url ? trailingslashit( 'https://twitter.com/'.$handle ) : '@'.$handle;
	}
}
