<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( 'index' );

	gThemeImage::image( array( 'tag' => 'single' ) );
	gThemeContent::header( array( 'context' => 'index', ) );

	if ( is_singular() ) {
		gThemeContent::content();
	} else {
		gThemeContent::excerpt();
	}

gThemeContent::wrapClose();
