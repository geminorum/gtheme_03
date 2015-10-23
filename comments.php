<div id="comments" class="comments-area"><?php 

	if ( gThemeComments::passwordRequired() )
		return;

	if ( have_comments() ) {

		gThemeComments::feed();
		gThemeComments::title();

		gThemeComments::lockDownNotice();

		$gtheme_comment_navigation = ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) );

		if ( $gtheme_comment_navigation )
			gThemeComments::navigation( 'comment-nav-above' );

		echo '<ol class="commentlist comment-list media-list">';

			// http://codex.wordpress.org/Function_Reference/wp_list_comments
			wp_list_comments( array(
				'callback' => gThemeOptions::info( 'comment_callback', array( 'gThemeComments', 'comment_callback' ) ),
				'style'    => 'ol',
				'type'     => 'comment', // no ping & trackback / default is 'all'
			) );

		echo '</ol>';

		if ( $gtheme_comment_navigation )
			gThemeComments::navigation( 'comment-nav-below' );

	} elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) {
		$closed = gThemeOptions::info( 'comments_closed', __( 'Comments are closed.' , GTHEME_TEXTDOMAIN ) );
		if ( $closed )
			echo '<p class="no-comments">'.$closed.'</p>';
	}

	call_user_func( gThemeOptions::info( 'comment_form', array( 'gThemeComments', 'comment_form' ) ) );

?></div>
