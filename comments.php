<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div id="comments" class="comments-area">';

	if ( gThemeComments::passwordRequired() )
		return;

	if ( have_comments() ) {

		gThemeComments::feed();
		gThemeComments::renderTitle();

		gThemeComments::lockDownNotice();

		$gtheme_comment_navigation = ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) );

		if ( $gtheme_comment_navigation )
			gThemeComments::navigation( 'comment-nav-above' );

		gThemeComments::renderList();

		if ( $gtheme_comment_navigation )
			gThemeComments::navigation( 'comment-nav-below' );

	} else if ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) {

		if ( $closed = gThemeOptions::info( 'comments_closed', __( 'Comments are closed.', GTHEME_TEXTDOMAIN ) ) )
			echo '<p class="no-comments -print-hide">'.$closed.'</p>';
	}

	gThemeComments::renderForm();

echo '</div>';
