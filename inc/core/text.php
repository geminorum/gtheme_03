<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeText extends gThemeBaseCore
{

	public static function formatName( $string, $separator = ', ' )
	{
		// already formatted
		if ( FALSE !== stripos( $string, trim( $separator ) ) )
			return $string;

		// remove NULL, FALSE and empty strings (""), but leave values of 0
		$parts = array_filter( explode( ' ', $string, 2 ), 'strlen' );

		if ( 1 == count( $parts ) )
			return $string;

		return $parts[1].$separator.$parts[0];
	}

	public static function reFormatName( $string, $separator = ', ' )
	{
		return preg_replace( '/(.*), (.*)/', '$2 $1', $string );
		// return preg_replace( '/(.*)([,،;؛]) (.*)/u', '$3'.$separator.'$1', $string ); // Wrong!
	}

	// simpler version of `wpautop()`
	// @REF: https://stackoverflow.com/a/5240825/4864081
	// @SEE: https://stackoverflow.com/a/7409591/4864081
	public static function autoP( $string )
	{
		$string = (string) $string;

		if ( 0 === strlen( $string ) )
			return '';

		// standardize newline characters to "\n"
		$string = str_replace( array( "\r\n", "\r" ), "\n", $string );

		// remove more than two contiguous line breaks
		$string = preg_replace( "/\n\n+/", "\n\n", $string );

		$paraphs = preg_split( "/[\n]{2,}/", $string );

		foreach ( $paraphs as $key => $p )
			$paraphs[$key] = '<p>'.str_replace( "\n", '<br />'."\n", $paraphs[$key] ).'</p>'."\n";

		$string = implode( '', $paraphs );

		// remove a P of entirely whitespace
		$string = preg_replace( '|<p>\s*</p>|', '', $string );

		return trim( $string );
	}

	// @REF: https://github.com/michelf/php-markdown/issues/230#issuecomment-303023862
	public static function removeP( $string )
	{
		return str_replace( array(
			"</p>\n\n<p>",
			'<p>',
			'</p>',
		), array(
			"\n\n",
			"",
		), $string );
	}

	// removes empty paragraph tags, and remove broken paragraph tags from around block level elements
	// @SOURCE: https://github.com/ninnypants/remove-empty-p
	public static function noEmptyP( $string )
	{
		$string = preg_replace( array(
			'#<p>\s*<(div|aside|section|article|header|footer)#',
			'#</(div|aside|section|article|header|footer)>\s*</p>#',
			'#</(div|aside|section|article|header|footer)>\s*<br ?/?>#',
			'#<(div|aside|section|article|header|footer)(.*?)>\s*</p>#',
			'#<p>\s*</(div|aside|section|article|header|footer)#',
		), array(
			'<$1',
			'</$1>',
			'</$1>',
			'<$1$2>',
			'</$1',
		), $string );

		return preg_replace( '#<p>(\s|&nbsp;)*+(<br\s*/*>)*(\s|&nbsp;)*</p>#i', '', $string );
	}

	// removes paragraph from around images
	// @SOURCE: https://css-tricks.com/?p=15293
	public static function noImageP( $string )
	{
		return preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $string );
	}
}
