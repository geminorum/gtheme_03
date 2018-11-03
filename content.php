<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

	if ( is_singular() || is_single() ) {

		gThemeContent::wrapOpen( 'singular' );

			gThemeImage::image( [
				'tag'   => 'single',
				'link'  => 'attachment',
				'empty' => FALSE,
			] );

			if ( gThemeTerms::has( 'poster' ) ) {

				// NO HEADER
				// NO CONTENT

			} else {
				get_template_part( 'partials/entry', 'singular' );
			}

			gThemeSideBar::sidebar( 'after-singular' );

			gThemeComments::template( '<div class="wrap-comments">', '</div>' );

		gThemeContent::wrapClose( 'singular' );

	} else {

		gThemeContent::wrapOpen( 'index' );

			gThemeImage::image( [
				'tag'   => 'single',
				'link'  => 'parent',
				'empty' => FALSE,
			] );

			get_template_part( 'partials/entry', 'index' );

		gThemeContent::wrapClose( 'index' );
	}
