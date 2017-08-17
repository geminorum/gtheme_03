<?php

function include_css( $array = array(), $debug = FALSE ){
	foreach ( $array as $css )
		if ( file_exists( $css ) )
			include( $css );
		else if ( $debug )
			echo '/** cannot find css at '.$css.' **/';
}

/*
SEE :
	http://www.catswhocode.com/blog/3-ways-to-compress-css-files-using-php
	https://github.com/GaryJones/Simple-PHP-CSS-Minification/
*/

function minify_css( $buffer ) {

	$buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer ); // comments
	$buffer = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $buffer ); // remove tabs, spaces, newlines, etc.
	$buffer = preg_replace( '/\s+/', ' ', $buffer ); // normalize whitespace
	$buffer = preg_replace( '/;(?=\s*})/', '', $buffer ); // remove ; before }
	$buffer = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $buffer ); // remove space after , : ; { } */ >
	$buffer = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $buffer ); // remove space before , ; { } ( ) >
	$buffer = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $buffer ); // strips leading 0 on decimal values (converts 0.5px into .5px)
	$buffer = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $buffer ); // strips units if value is 0 (converts 0px to 0)
	$buffer = preg_replace( '/0 0 0 0/', '0', $buffer ); // converts all zeros value into short-hand
	$buffer = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $buffer ); // shortern 6-character hex color codes to 3-character where possible

	return trim( $buffer );
}
