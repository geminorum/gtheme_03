<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( is_single() ) { // any posttype except attachment/page

	gThemeContent::wrapOpen( 'singular' );

		if ( gThemeTerms::has( 'poster' ) )
			get_template_part( 'partials/poster', get_post_type() );
		else
			get_template_part( 'partials/entry', get_post_type() );

		gThemeSideBar::sidebar( 'after-singular' );

		gThemeComments::template( '<div class="entry-comments">', '</div>' );

	gThemeContent::wrapClose( 'singular' );

} else if ( is_page() ) {

	gThemeContent::wrapOpen( 'page' );

		get_template_part( 'partials/page', gtheme_template_base() );

	gThemeContent::wrapClose( 'page' );

} else {

	gThemeContent::wrapOpen( 'index' );

		gThemeImage::image( [
			'tag'   => 'single',
			'link'  => 'parent',
			'empty' => FALSE,
		] );

		get_template_part( 'partials/summary', gtheme_template_base() );

	gThemeContent::wrapClose( 'index' );
}
