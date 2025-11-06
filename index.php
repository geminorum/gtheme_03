<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( have_posts() ) {

	gThemeTemplate::wrapOpen( 'index' );
		gThemeNavigation::breadcrumb( [ 'home' => 'home', 'context' => 'index' ] );

		while ( have_posts() ) {
			the_post();
			gThemeContent::post( 'index' );
		}

		gThemeNavigation::content( 'index' );
	gThemeTemplate::wrapClose( 'index' );

} else if ( is_search() ) {

	gThemeTemplate::wrapOpen( 'emptysearch' );
		gThemeNavigation::breadcrumb( [ 'home' => 'home', 'context' => 'emptysearch' ] );

		gThemeContent::notFound( 'emptysearch' );

	gThemeTemplate::wrapClose( 'emptysearch' );

} else {

	gThemeTemplate::wrapOpen( 'notfound' );
		gThemeNavigation::breadcrumb( [ 'home' => 'home', 'context' => 'notfound' ] );

		gThemeContent::notFound( 'index' );

	gThemeTemplate::wrapClose( 'notfound' );
}

gThemeTemplate::sidebar( gtheme_template_base() );
