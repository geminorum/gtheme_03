<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapOpen( 'notfound' );

	gThemeContent::header( [
		'context' => 'notfound',
		'title'   => __( 'Oops! That page can&rsquo;t be found.', 'gtheme' ),
		'link'    => FALSE,
		'meta'    => FALSE,
		'anchor'  => FALSE,
	] );

	echo '<div class="entry-content entry-notfound">';

		gThemeContent::notFoundMessage();
		gThemeSearch::form( 'notfound' );

		do_action( 'gtheme_content_notfound' );
		do_action( 'gtheme_content_404' ); // DEPRECATED

	echo '</div>';

gThemeContent::wrapClose( 'notfound' );
