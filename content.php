<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

	if ( is_singular() ) {

		gThemeContent::wrapOpen( 'singular' );
		gThemeImage::image( [ 'tag' => 'single' ] );

		if ( gThemeTerms::has( 'poster' ) ) {

			// NO HEADER
			// NO CONTENT

		} else {
			get_template_part( 'partials/entry', 'singular' );
		}

		gThemeComments::template( '<div class="wrap-comments">', '</div>' );

	} else {

		gThemeContent::wrapOpen( 'index' );
		gThemeImage::image( [ 'tag' => 'single' ] );

		get_template_part( 'partials/entry', 'index' );
	}

gThemeContent::wrapClose();
