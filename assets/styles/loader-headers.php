<?php

if ( $debug ) {

	header( 'Content-Type: text/css; charset=UTF-8' );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );
	// header( 'Pragma: no-cache' ); // https://core.trac.wordpress.org/ticket/37250
	header( 'Expires: 0' );

} else {
	
	ini_set( 'zlib.output_compression', 4096 );

	if ( $version ) {

		// @REF: https://core.trac.wordpress.org/ticket/28722
		
		if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) == $version ) {
			$protocol = $_SERVER['SERVER_PROTOCOL'];
			if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ) ) ) {
				$protocol = 'HTTP/1.0';
			}
			header( $protocol.' 304 Not Modified' );
			exit();
		}
		
		header( 'Etag: '.$version );
	}
	
	header( 'Content-Type: text/css; charset=UTF-8' );

	// $offset = 60 * 60 * 24 * 365; // for a day * 365
	$offset = 31536000; // 1 year

	header( 'Expires: '.gmdate( 'D, d M Y H:i:s', time() + $offset ).' GMT' );
	header( 'Cache-Control: max-age='.$offset.', must-revalidate' );
}
