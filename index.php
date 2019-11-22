<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="container -main -index"><div class="row justify-content-center">';
gThemeTemplate::wrapOpen( 'index' );

	gThemeNavigation::breadcrumb( [ 'home' => 'home', 'context' => 'index' ] );

	if ( have_posts() ) {

		while ( have_posts() ) {
			the_post();
			gThemeContent::post( 'index' );
		}

		gThemeNavigation::content( 'index' );

	} else {

		get_template_part( 'content', '404' );
	}

gThemeTemplate::wrapClose( 'index' );
gThemeTemplate::sidebar( gtheme_template_base() );
echo '</div></div>';
