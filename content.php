<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-wrap content-index clearfix' ); ?>><?php

	gThemeImage::image( array( 'tag' => 'single' ) );
	gThemeContent::header( array( 'context' => 'index', ) );

	if ( is_singular() ) {
		gThemeContent::content();
	} else {
		gThemeContent::excerpt();
	}

?></article>
