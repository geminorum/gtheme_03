<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( have_posts() ) {

	gThemeTemplate::wrapOpen( 'singular' );
		gThemeNavigation::breadcrumb( [ 'home' => 'home' ] );


	while ( have_posts() ) {
		the_post();
		gThemeContent::post();
	}

		// gThemeNavigation::content( 'singular' );
	gThemeTemplate::wrapClose( 'singular' );

} else {

	gThemeTemplate::wrapOpen( 'notfound' );

		gThemeContent::notFound();

	gThemeTemplate::wrapClose( 'notfound' );
}

gThemeTemplate::sidebar( 'singular' );
