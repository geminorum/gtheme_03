<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( is_single() ) { // any posttype except attachment/page

	gThemeContent::wrapOpen( 'singular' );

		if ( gThemeTerms::has( 'poster' ) )
			get_template_part( 'partials/poster', get_post_type() );
		else
			get_template_part( 'partials/entry', get_post_type() );

		gThemeSideBar::sidebar( 'after-singular', '<div class="wrap-side sidebar-after-singular">', '</div>' );

		gThemeComments::template( '<div class="entry-comments">', '</div>' );

	gThemeContent::wrapClose( 'singular' );

} else if ( gThemeUtilities::isSystemPage() || is_page() || is_404() ) {

	gThemeContent::wrapOpen( 'page' );

		get_template_part( 'partials/page', gtheme_template_base() );

	gThemeContent::wrapClose( 'page' );

} else {

	gThemeContent::wrapOpen( 'index' );

		get_template_part( 'partials/summary', get_post_type() );

	gThemeContent::wrapClose( 'index' );
}
