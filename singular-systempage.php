<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="container -main -singular -systempage"><div class="row justify-content-center">';
gThemeTemplate::wrapOpen( 'systempage' );

	if ( have_posts() ) {

		while ( have_posts() ) {
			the_post();
			gThemeContent::post();
		}

		// gThemeNavigation::content( 'singular' );

	} else {

		get_template_part( 'content', '404' );
	}

gThemeTemplate::wrapClose( 'systempage' );
gThemeTemplate::sidebar( 'systempage' );
echo '</div></div>';
