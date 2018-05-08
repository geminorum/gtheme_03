<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="container -main -singular -systempage"><div class="row">';
echo '<div class="col-sm-6 wrap-content" id="content">';

	if ( have_posts() ) {

		while ( have_posts() ) {
			the_post();
			gThemeContent::post();
		}

		// gThemeNavigation::content();

	} else {

		get_template_part( 'content', '404' );
	}

echo '</div>';

	get_sidebar( 'systempage' );

echo '</div></div>';
