<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( 'index' );

	gThemeImage::image( array( 'tag' => 'single' ) );

	if ( gThemeTerms::has( 'poster' ) ) {

		// NO HEADER
		// NO CONTENT

	} else if ( is_singular() ) {

		gThemeContent::header( array( 'context' => 'index' ) );
		gThemeContent::content();

	} else {

		gThemeContent::header( array( 'context' => 'index' ) );
		gThemeContent::excerpt();
		gThemeContent::footer( array(
			'context' => 'index',
			'actions' => array(
				'categories',
				'date',
			),
		) );
	}

gThemeContent::wrapClose();
