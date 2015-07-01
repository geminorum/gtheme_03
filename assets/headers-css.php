<?php

$debug = isset( $_GET['debug'] );
$ltr   = isset( $_GET['ltr'] );

header( 'Content-type: text/css;  charset: UTF-8');

if ( $debug ) {

	header( 'Cache-Control: no-cache, no-store, must-revalidate' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

} else {

	ini_set( 'zlib.output_compression', 4096 );

	$offset = 60 * 60 * 24 * 365; // for a day * 365
	header( 'Cache-Control: max-age='.$offset.', must-revalidate' );
	header( 'Expires: '.gmdate( 'D, d M Y H:i:s', time() + $offset ).' GMT' );
}
