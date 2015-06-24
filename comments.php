<div id="comments" class="comments-area"><?php 

	if ( post_password_required() ) {
		echo '<p class="nopassword">';
			_e( 'This post is password protected. Enter the password to view any comments.', GTHEME_TEXTDOMAIN );
		echo '</p></div>';
		return;
	}

	if ( have_comments() ) {

		gThemeComments::feed();
		gThemeComments::title();

		$gtheme_comment_navigation = ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) );

		if ( $gtheme_comment_navigation )
			gThemeComments::navigation( 'comment-nav-above' );

		echo '<ol class="commentlist media-list">';

			// http://codex.wordpress.org/Function_Reference/wp_list_comments
			wp_list_comments( array(
				'callback' => gtheme_get_info( 'comment_callback', array( 'gThemeComments', 'comment_callback' ) ),
				'style' => 'ol',
				'type' => 'comment', // no ping & trackback / default is 'all'
			) );

		echo '</ol>';

		if ( $gtheme_comment_navigation )
			gThemeComments::navigation( 'comment-nav-below' );

	} elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) {
		$closed = gtheme_get_info( 'comments_closed', __( 'Comments are closed.' , GTHEME_TEXTDOMAIN ) );
		if ( $closed )
			echo '<p class="nocomments">'.$closed.'</p>';
	}

	call_user_func( gtheme_get_info( 'comment_form', array( 'gThemeComments', 'comment_form' ) ) );

?></div>
