<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapOpen( 'item' );

	gThemeImage::image( [ 'context' => 'item', 'empty' => FALSE ] );

	if ( ! gThemeTerms::has( 'poster' ) ) {

		gThemeContent::header( [ 'context' => 'item', 'byline' => TRUE ] );
		gThemeContent::excerpt();
	}

	gThemeContent::footer( [ 'context' => 'item' ] );

gThemeContent::wrapClose( 'item' );
