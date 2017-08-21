<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

	if ( is_singular() ) {

		gThemeContent::wrapOpen( 'singular' );
		gThemeImage::image( array( 'tag' => 'single' ) );

		if ( gThemeTerms::has( 'poster' ) ) {

			// NO HEADER
			// NO CONTENT

		} else {

			gThemeContent::header( array( 'context' => 'singular' ) );
			gThemeContent::content();
		}

	} else {

		gThemeContent::wrapOpen( 'index' );
		gThemeImage::image( array( 'tag' => 'single' ) );

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
