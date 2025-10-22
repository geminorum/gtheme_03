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
	// @REF: https://stackoverflow.com/a/5240825
	// @SEE: https://stackoverflow.com/a/7409591
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

	/**
	 * Removes paragraph from around images.
	 * @source https://css-tricks.com/?p=15293
	 *
	 * @param string $text
	 * @param string $tag
	 * @param string $class
	 * @return string
	 */
	public static function replaceImageP( $text, $tag = 'figure', $class = '' )
	{
		return $tag && trim( $tag )
			? preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', sprintf( '<%s%s>\1\2\3</%s>', $tag, ( $class ? ( ' class="'.$class.'"' ) : '' ), $tag ), $text )
			: preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $text );
	}

	// DEPRECATED: use `Text::replaceImageP( $string, FALSE )`
	public static function noImageP( $string )
	{
		return self::replaceImageP( $string, FALSE );
	}

	// like wp but without check for func_overload
	// @SOURCE: `seems_utf8()`
	public static function seemsUTF8( $string )
	{
		$length = strlen( $string );

		for ( $i = 0; $i < $length; $i++ ) {

			$c = ord( $string[$i] );

			if ( $c < 0x80 )
				$n = 0; // 0bbbbbbb

			else if ( ( $c & 0xE0 ) == 0xC0 )
				$n = 1; // 110bbbbb

			else if ( ( $c & 0xF0 ) == 0xE0 )
				$n = 2; // 1110bbbb

			else if ( ( $c & 0xF8 ) == 0xF0 )
				$n = 3; // 11110bbb

			else if ( ( $c & 0xFC ) == 0xF8 )
				$n = 4; // 111110bb

			else if ( ( $c & 0xFE ) == 0xFC )
				$n = 5; // 1111110b

			else
				return FALSE; // does not match any model

			for ( $j = 0; $j < $n; $j++ ) // n bytes matching 10bbbbbb follow ?
				if ( ( ++$i == $length )
					|| ( ( ord( $string[$i] ) & 0xC0 ) != 0x80 ) )
						return FALSE;
		}

		return TRUE;
	}

	// @SOURCE: `normalize_whitespace()`
	public static function normalizeWhitespace( $string )
	{
		// return preg_replace( '!\s+!', ' ', $string );
		// return preg_replace( '/\s\s+/', ' ', $string );

		return preg_replace(
			array( '/\n+/', '/[ \t]+/' ),
			array( "\n", ' ' ),
			str_replace( "\r", "\n", trim( $string ) )
		);
	}

	// @REF: http://stackoverflow.com/a/3226746
	public static function normalizeWhitespaceUTF8( $string, $check = FALSE )
	{
		if ( $check && ! self::seemsUTF8( $string ) )
			return self::normalizeWhitespace( $string );

		return preg_replace( '/[\p{Z}\s]{2,}/u', ' ', $string );
	}

	// @REF: _cleanup_image_add_caption()
	// remove any line breaks from inside the tags
	public static function noLineBreak( $string )
	{
		return preg_replace( '/[\r\n\t]+/', ' ', $string );
	}

	/**
	 * Copyright (c) 2008, David R. Nadeau, NadeauSoftware.com.
	 * All rights reserved.
	 * License: http://www.opensource.org/licenses/bsd-license.php
	 *
	 * Strip punctuation characters from UTF-8 text.
	 *
	 * Characters stripped from the text include characters in the following
	 * Unicode categories:
	 *
	 * 	Separators
	 * 	Control characters
	 *	Formatting characters
	 *	Surrogates
	 *	Open and close quotes
	 *	Open and close brackets
	 *	Dashes
	 *	Connectors
	 *	Numer separators
	 *	Spaces
	 *	Other punctuation
	 *
	 * Exceptions are made for punctuation characters that occur withn URLs
	 * (such as [ ] : ; @ & ? and others), within numbers (such as . , % # '),
	 * and within words (such as - and ').
	 *
	 * Parameters:
	 * 	text		the UTF-8 text to strip
	 *
	 * Return values:
	 * 	the stripped UTF-8 text.
	 *
	 * See also:
	 * 	http://nadeausoftware.com/articles/2007/9/php_tip_how_strip_punctuation_characters_web_page
	 */
	public static function stripPunctuation( $text )
	{
		$urlbrackets    = '\[\]\(\)';
		$urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
		$urlspaceafter  = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
		$urlall         = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

		$specialquotes = '\'"\*<>';

		$fullstop      = '\x{002E}\x{FE52}\x{FF0E}';
		$comma         = '\x{002C}\x{FE50}\x{FF0C}';
		$arabsep       = '\x{066B}\x{066C}';
		$numseparators = $fullstop . $comma . $arabsep;

		$numbersign    = '\x{0023}\x{FE5F}\x{FF03}';
		$percent       = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
		$prime         = '\x{2032}\x{2033}\x{2034}\x{2057}';
		$nummodifiers  = $numbersign . $percent . $prime;

		return preg_replace(
			array(
			// Remove separator, control, formatting, surrogate,
			// open/close quotes.
				'/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
			// Remove other punctuation except special cases
				'/\p{Po}(?<![' . $specialquotes .
					$numseparators . $urlall . $nummodifiers . '])/u',
			// Remove non-URL open/close brackets, except URL brackets.
				'/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
			// Remove special quotes, dashes, connectors, number
			// separators, and URL characters followed by a space
				'/[' . $specialquotes . $numseparators . $urlspaceafter .
					'\p{Pd}\p{Pc}]+((?= )|$)/u',
			// Remove special quotes, connectors, and URL characters
			// preceded by a space
				'/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
			// Remove dashes preceded by a space, but not followed by a number
				'/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
			// Remove consecutive spaces
				'/ +/',
			),
			' ',
			$text );
	}

	public static function wordCountUTF8( $html, $normalize = TRUE )
	{
		if ( ! $html )
			return 0;

		if ( $normalize ) {

			$html = preg_replace( array(
				'@<script[^>]*?>.*?</script>@si',
				'@<style[^>]*?>.*?</style>@siU',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<![\s\S]*?--[ \t\n\r]*>@',
				'/<blockquote.*?>(.*)?<\/blockquote>/im',
				'/<figure.*?>(.*)?<\/figure>/im',
			), '', $html );

			$html = strip_tags( $html );

			// FIXME: convert back html entities

			$html = str_replace( array(
				"&nbsp;",
				"&mdash;",
				"&ndash;",
			), ' ', $html );

			$html = str_replace( array(
				"&zwnj;",
				"\xE2\x80\x8C", // Zero Width Non-Joiner U+200C
				"\xE2\x80\x8F", // Right-To-Left Mark U+200F
				"\xE2\x80\x8E", // Right-To-Left Mark U+200E
				"\xEF\xBB\xBF", // UTF8 Bom
			), '', $html );

			$html = strip_shortcodes( $html );

			$html = self::noLineBreak( $html );
			$html = self::stripPunctuation( $html );
			$html = self::normalizeWhitespaceUTF8( $html, TRUE );

			$html = trim( $html );
		}

		if ( ! $html )
			return 0;

		// http://php.net/manual/en/function.str-word-count.php#85579
		// return preg_match_all( "/\\p{L}[\\p{L}\\p{Mn}\\p{Pd}'\\x{2019}]*/u", $html, $matches );

		/**
		* This simple utf-8 word count function (it only counts)
		* is a bit faster then the one with preg_match_all
		* about 10x slower then the built-in str_word_count
		*
		* If you need the hyphen or other code points as word-characters
		* just put them into the [brackets] like [^\p{L}\p{N}\'\-]
		* If the pattern contains utf-8, utf8_encode() the pattern,
		* as it is expected to be valid utf-8 (using the u modifier).
		*
		* @link http://php.net/manual/en/function.str-word-count.php#107363
		**/
		return count( preg_split( '~[^\p{L}\p{N}\']+~u', $html ) );
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

	public static function has( $haystack, $needles, $operator = 'OR' )
	{
		if ( ! $haystack )
			return FALSE;

		if ( ! is_array( $needles ) )
			return FALSE !== stripos( $haystack, $needles );

		if ( 'OR' == $operator ) {
			foreach ( $needles as $needle )
				if ( FALSE !== stripos( $haystack, $needle ) )
					return TRUE;

			return FALSE;
		}

		$has = FALSE;

		foreach ( $needles as $needle )
			if ( FALSE !== stripos( $haystack, $needle ) )
				$has = TRUE;

		return $has;
	}

	public static function start( $haystack, $needles, $operator = 'OR' )
	{
		if ( ! $haystack )
			return FALSE;

		if ( ! is_array( $needles ) )
			return 0 === stripos( $haystack, $needles );

		if ( 'OR' == $operator ) {
			foreach ( $needles as $needle )
				if ( 0 === stripos( $haystack, $needle ) )
					return TRUE;

			return FALSE;
		}

		$start = FALSE;

		foreach ( $needles as $needle )
			if ( 0 === stripos( $haystack, $needle ) )
				$start = TRUE;

		return $start;
	}

	// @SOURCE: http://web.archive.org/web/20110215015142/http://www.phpwact.org/php/i18n/charsets#htmlspecialchars
	// @SOURCE: `_wp_specialchars()`
	// converts a number of special characters into their HTML entities
	// specifically deals with: &, <, >, ", and '
	public static function utf8SpecialChars( $string, $flags = ENT_COMPAT )
	{
		$string = (string) $string;

		if ( 0 === strlen( $string ) )
			return '';

		if ( preg_match( '/[&<>"\']/', $string ) )
			$string = @htmlspecialchars( $string, $flags, 'UTF-8' );

		return $string;
	}

	// @SOURCE: `bp_core_replace_tokens_in_text()`
	public static function replaceTokens( $string, $tokens )
	{
		$unescaped = $escaped = [];

		foreach ( $tokens as $token => $value ) {

			if ( ! is_string( $value ) && is_callable( $value ) )
				$value = call_user_func( $value );

			// tokens can not be objects or arrays
			if ( ! is_scalar( $value ) )
				continue;

			$unescaped['{{{'.$token.'}}}'] = $value;
			$escaped['{{'.$token.'}}']     = self::utf8SpecialChars( $value, ENT_QUOTES );
		}

		$string = strtr( $string, $unescaped );  // do first
		$string = strtr( $string, $escaped );

		return $string;
	}

	// NOTE: the order is important!
	public static function convertFormatToToken( $template, $keys )
	{
		foreach ( $keys as $offset => $key )
			$template = str_ireplace( '%'.( $offset + 1 ).'$s', '{{'.$key.'}}', $template );

		return $template;
	}
}
