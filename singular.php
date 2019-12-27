<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeTemplate::wrapOpen( 'singular' );

	gThemeNavigation::breadcrumb( [ 'home' => 'home' ] );

	if ( have_posts() ) {

		while ( have_posts() ) {
			the_post();
			gThemeContent::post();
		}

		// gThemeNavigation::content( 'singular' );

	} else {

		get_template_part( 'content', '404' );
	}

gThemeTemplate::wrapClose( 'singular' );
gThemeTemplate::sidebar( 'singular' );
