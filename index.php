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

} else {

	gThemeTemplate::wrapOpen( '404' );
		gThemeNavigation::breadcrumb( [ 'home' => 'home', 'context' => '404' ] );

		get_template_part( 'content', '404' );

	gThemeTemplate::wrapClose( '404' );
}

gThemeTemplate::sidebar( gtheme_template_base() );
