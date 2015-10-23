<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeComments extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'strip_trackbacks' => TRUE,
			'reverse_comments' => FALSE,
			'disable_types'    => FALSE,
			'closing_time'     => FALSE,
		), $args ) );

		add_filter( 'comment_class', array( &$this, 'comment_class' ), 10 ,4 );
		add_action( 'comment_form_before', array( &$this, 'comment_form_before' ) );

		if ( $strip_trackbacks ) {
			add_filter( 'the_posts', array( &$this, 'the_posts' ) );
			add_filter( 'comments_array', array( &$this, 'comments_array' ) );
			add_filter( 'get_comments_number', array( &$this, 'get_comments_number' ) );
		}

		if ( $reverse_comments )
			add_filter( 'comments_array', array( &$this, 'comments_array_reverse' ), 12 );

		if ( $disable_types )
			add_filter( 'comments_open', array( &$this, 'comments_open' ), 10 , 2 );

		if ( $closing_time )
			add_action( 'comment_form_top', array( &$this, 'comment_form_top' ) );

	}

	public function comment_class( $classes, $class, $comment_id, $post_id )
	{
		$comment = get_comment( $comment_id );

		if ( $comment->comment_approved == '0' )
			$classes[] = 'comment-awaiting';

		if ( $comment->user_id > 0 && user_can( $comment->user_id, 'edit_others_posts' ) )
			$classes[] = 'comment-by-editor';

		return $classes;
	}

	// http://make.wordpress.org/themes/2012/05/08/proposed-wordpress-3-4-guidelines-revisions/
	// http://wpengineer.com/2358/enqueue-comment-reply-js-the-right-way/
	public function comment_form_before()
	{
		if ( comments_open() && get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );
	}

	// http://www.honeytechblog.com/how-to-remove-tracbacks-and-pings-from-wordpress-posts/
	// http://plugins.svn.wordpress.org/hide-trackbacks/trunk/hide-trackbacks.php
	public function the_posts( $posts )
	{
		foreach ( $posts as $key => $p ) {
			if ( $p->comment_count <= 0 )
				return $posts;
			$posts[$key]->comment_count = self::count( (int) $p->ID );
		}
		return $posts;
	}

	// updates the count for comments and trackbacks
	public function comments_array( $array )
	{
		global $comments;
		$comments = self::strip_trackbacks( $array );

		return $comments;
	}

	// corrects comment count within the loop
	public function get_comments_number( $commentcount )
	{
		return self::count( get_the_ID() );
	}

	// helper: counting comments per post
	public static function count( $id )
	{
		$comments = self::strip_trackbacks( get_approved_comments( $id ) );
		return sizeof( $comments );
	}

	// helper: filtering out the trackbacks / pingbacks leaving comments only from list of comments
	public static function strip_trackbacks( $comments )
	{
		if ( ! is_array( $comments ) )
			return $comments;

		return array_filter( $comments, array( __CLASS__, 'strip_trackback' ) );
	}

	// helper: strips out trackbacks/pingbacks
	public static function strip_trackback( $comment )
	{
		if ( $comment->comment_type == 'trackback'
			|| $comment->comment_type == 'pingback' )
				return FALSE;

		return TRUE;
	}

	// http://www.wprecipes.com/how-to-reverse-wordpress-comments-order
	public function comments_array_reverse( $comments )
	{
		return array_reverse( $comments, TRUE );
	}

	// http://www.wpbeginner.com/wp-tutorials/how-to-disable-comments-on-wordpress-media-attachments/
	public function comments_open( $open, $post_id )
	{
		$post_types = gThemeOptions::info( 'comments_disable_types', array( 'attachment' ) );
		if ( $post_types && is_array( $post_types ) ) {
			$post = get_post( $post_id );
			if ( in_array( $post->post_type, $post_types ) )
				return FALSE;
		}

		return $open;
	}

	// inform user about automatic comment closing time
	// http://wpengineer.com/2692/inform-user-about-automatic-comment-closing-time/
	// TODO: bootstrap styling / notice
	public function comment_form_top()
	{
		global $post;

		if ( 'open' == $post->comment_status ) {

			$expires = strtotime( "{$post->post_date_gmt} GMT" )
					 + get_option( 'close_comments_days_old' )
					 * DAY_IN_SECONDS;

			printf( __( '(This topic will automatically close in %s. )', GTHEME_TEXTDOMAIN ),  human_time_diff( $expires ) );
		}
	}

	public static function passwordRequired( $print = TRUE )
	{
		if ( post_password_required() ) {

			if ( $print ) {
				echo '<p class="no-password">';
					_e( 'This post is password protected. Enter the password to view any comments.', GTHEME_TEXTDOMAIN );
				echo '</p></div>';
			}

			return TRUE;;
		}

		return FALSE;
	}

	public static function navigation( $class = 'comment-nav-above' )
	{
		$strings = gThemeOptions::info( 'comment_nav_strings', array(
			'title'    => _x( 'Comment navigation', 'Comments Module', GTHEME_TEXTDOMAIN ),
			'previous' => _x( '&rarr; Older Comments', 'Comments Module', GTHEME_TEXTDOMAIN ),
			'next'     => _x( 'Newer Comments &larr;', 'Comments Module', GTHEME_TEXTDOMAIN ),
		) );

		echo '<nav class="navigation comment-navigation '.$class.'" role="navigation">';
		if ( $strings['title'] )
			echo '<h4 class="assistive-text sr-only">'.$strings['title'].'</h4>';
		echo '<div class="nav-previous">';
			previous_comments_link( $strings['previous'] );
		echo '</div><div class="nav-next">';
			next_comments_link( $strings['next'] );
		echo '</div></nav>';
	}

	public static function title( $class = 'comments-title', $tag = 'h3' )
	{
		$commnets_number = get_comments_number();
		echo gThemeUtilities::html( $tag, array(
			'class' => $class,
		), sprintf( _nx(
			'One thought on &ldquo;%2$s&rdquo;',
			'%1$s thoughts on &ldquo;%2$s&rdquo;',
			$commnets_number, 'Comments Title', GTHEME_TEXTDOMAIN ),
				number_format_i18n( $commnets_number ),
				'<span>'.get_the_title().'</span>'
		) );
	}

	public static function feed( $class = 'comments-feed' )
	{
		if ( gThemeUtilities::isRTL() )
			$icon = '<span class="genericon genericon-feed genericon-flip-horizontal"></span>';
		else
			$icon = '<span class="genericon genericon-feed"></span>';

		$html = gThemeUtilities::html( 'a', array(
			'href'  => get_post_comments_feed_link(),
			'title' => __( 'Grab the feed for comments of this post', GTHEME_TEXTDOMAIN ),
		), $icon );

		echo gThemeUtilities::html( 'div', array(
			'class' => $class,
		), $html );
	}

	// When this is enabled, new comments on a post will not refresh the cached static files.
	public static function lockDownNotice( $class = '' )
	{
		if ( defined( 'WPLOCKDOWN' ) && constant( 'WPLOCKDOWN' ) ) {
			echo '<div class="lockdown-notice '.$class.'">';
				_ex( 'Sorry, The site is locked down. Updates will appear shortly', 'Comments Module', GTHEME_TEXTDOMAIN );
			echo '</div>';
		}
	}

	// UNFINISHED but working
	public static function comment_callback( $comment, $args, $depth )
	{
		switch ( $comment->comment_type ) {
			case 'pingback' :
			case 'trackback' :
				break;

			case 'comment' :
			default :

				echo '<li ';
					comment_class( 'media' );
				echo ' id="li-comment-'.$comment->comment_ID.'">';

					echo '<a class="comment-avatar '.( gThemeUtilities::isRTL() ? 'pull-right media-right' : 'pull-left media-left' ).'" href="'.get_comment_author_url().'" rel="external nofollow">';
						gThemeTemplate::avatar( $comment, gThemeOptions::info( 'comment_avatar_size', 75 ) );
					echo '</a><div class="media-body comment-body" id="comment-body-'.$comment->comment_ID.'"><h6 class="media-heading comment-meta">';
						echo '<span class="comment-author">'.get_comment_author_link().'</span>';
						echo ' <small class="comment-time">';
						self::time( $comment->comment_ID );
					echo '</small></h6><div class="comment-content">';
						comment_text();
					echo '</div>';

						if ( '0' == $comment->comment_approved )
							echo '<p class="text-danger comment-awaiting-moderation comment-moderation">'
							.gThemeOptions::info( 'comment_awaiting',
								__( 'Your comment is awaiting moderation.', GTHEME_TEXTDOMAIN ) )
							.'</p>';

						self::actions( $comment, $args, $depth );
					echo '</div>';
			break;
		}
	}

	public static function time( $id )
	{
		echo '<a href="'.esc_url( get_comment_link( $id ) ).'">';
			echo '<time datetime="';
				comment_time( 'c' );
			echo '">'.sprintf(
				_x( '%1$s at %2$s', '1: date, 2: time', GTHEME_TEXTDOMAIN ),
				get_comment_date(),
				get_comment_time()
			);
		echo '</time></a>';
	}

	public static function actions( $comment, $args, $depth, $class = 'media-actions comment-actions' )
	{
		$actions = array();
		$strings = gThemeOptions::info( 'comment_action_strings', array(
			'reply_text'    => __( 'Reply' ),
			'reply_to_text' => __( 'Reply to %s' ),
			'login_text'    => __( 'Log in to Reply' ),
			'edit'          => __( 'Edit This' ),
		) );

		$reply = get_comment_reply_link( array(
			'depth'         => $depth,
			'max_depth'     => $args['max_depth'],
			'add_below'     => 'comment-body',
			// 'before'        => '<span class="reply">',
			// 'after'         => '</span>',
			'reply_text'    => $strings['reply_text'],
			'reply_to_text' => $strings['reply_to_text'],
			'login_text'    => $strings['login_text'],
		), $comment );

		if ( $reply )
			$actions['reply'] = $reply;

		$edit = get_edit_comment_link( $comment->comment_ID );
		if ( $edit )
			$actions['edit-link'] = gThemeUtilities::html( 'a', array(
				'href' => $edit,
				'class' => 'comment-edit-link',
			), $strings['edit'] );

		$actions = apply_filters( 'gtheme_comment_actions', $actions, $comment, $args, $depth );

		if ( ! count( $actions ) )
			return;

		echo '<ul class="list-inline '.$class.'">';
			foreach ( $actions as $class => $action )
				echo '<li class="'.$class.'">'.$action.'</li>';
		echo '</ul>';
	}

	public static function comment_form( $args = array(), $post_id = null )
	{
		if ( comments_open() ) {

			if ( is_null( $post_id ) )
				$post_id = get_the_ID();

			$user = wp_get_current_user();
			$user_identity = ! empty( $user->ID ) ? $user->display_name : '';
			$commenter = wp_get_current_commenter();

			$required = get_option( 'require_name_email' );
			$html5 = current_theme_supports( 'html5', 'comment-form' ) ? true : false;
			$strings = gThemeOptions::info( 'comment_form_strings', array(
				'required'          => _x( '(Required)', 'Comment Form Strings', GTHEME_TEXTDOMAIN ),
				'name'              => _x( 'Name', 'Comment Form Strings', GTHEME_TEXTDOMAIN ),
				'email'             => _x( 'Email', 'Comment Form Strings', GTHEME_TEXTDOMAIN ),
				'url'               => _x( 'Website', 'Comment Form Strings', GTHEME_TEXTDOMAIN ),
				'comment'           => _x( 'Comment', 'Comment Form Strings', GTHEME_TEXTDOMAIN ),
				'must_log_in'       => __( 'You must be <a href="%s">logged in</a> to post a comment.' ),
				'logged_in_as'      => __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ),
				'title_reply'       => __( 'Leave a Reply' ),
				'title_reply_to'    => __( 'Leave a Reply to %s' ),
				'cancel_reply_link' => __( 'Cancel reply' ),
				'label_submit'      => __( 'Post Comment' ),
			) );

			$fields = array();

			$fields['author'] = '<div class="form-group comment-form-author"><label for="author">'
				.$strings['name']
				.( $required ? ' <span class="required">'.$strings['required'].'</span>' : '' )
				.'</label>'
				.gThemeUtilities::html( 'input', array(
					'type'               => 'text',
					'x-autocompletetype' => 'name-full',
					'aria-required'      => ( $required ? 'true' : false ),
					'class'              => 'form-control',
					'size'               => '30',
					'id'                 => 'author',
					'name'               => 'author',
					'value'              => $commenter['comment_author'],
				) ).'</div>';

			$fields['email'] = '<div class="form-group comment-form-email"><label for="email">'
				.$strings['email']
				.( $required ? ' <span class="required">'.$strings['required'].'</span>' : '' )
				.'</label>'
				.gThemeUtilities::html( 'input', array(
					'type'               => ( $html5 ? 'email' : 'text' ),
					'x-autocompletetype' => 'email',
					'aria-required'      => ( $required ? 'true' : false ),
					'class'              => 'form-control comment-field-ltr',
					'size'               => '30',
					'id'                 => 'email',
					'name'               => 'email',
					'value'              => $commenter['comment_author_email'],
					// 'placeholder'        => $strings['email'], // NOTE: problem with rtl
				) ).'</div>';

			$fields['url'] = '<div class="form-group comment-form-url"><label for="url">'
				.$strings['url']
				.'</label>'
				.gThemeUtilities::html( 'input', array(
					'type'  => ( $html5 ? 'url' : 'text' ),
					'class' => 'form-control comment-field-ltr',
					'size'  => '30',
					'id'    => 'url',
					'name'  => 'url',
					'value' => $commenter['comment_author_url'],
					// 'placeholder'   => $strings['url'], // NOTE: problem with rtl
				) ).'</div>';

			$defaults = array(
				'fields' => apply_filters( 'comment_form_default_fields', $fields ),

				'comment_field' => '<div class="form-group comment-form-comment"><label for="comment" class="sr-only">'
					.$strings['comment'].'</label>'
					.gThemeUtilities::html( 'textarea', array(
						'aria-required' => 'true',
						'class'         => 'form-control',
						'cols'          => '45',
						'rows'          => '4',
						'id'            => 'comment',
						'name'          => 'comment',
						'placeholder'   => $strings['comment'],
					), NULL ).'</div>',

				'must_log_in' => '<p class="must-log-in">'.sprintf( $strings['must_log_in'],
					wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ).'</p>',

				'logged_in_as' => '<p class="logged-in-as">'.sprintf( $strings['logged_in_as'],
					get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ).'</p>',

				'comment_notes_before' => '', //'<p class="comment-notes"><span id="email-notes">' . __( 'Your email address will not be published.' ) . '</span>'. ( $req ? $required_text : '' ) . '</p>',
				'comment_notes_after'  => '', //'<p class="form-allowed-tags" id="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
				'id_form'              => 'commentform',
				'id_submit'            => 'submit',
				'class_submit'         => 'submit',
				'name_submit'          => 'submit',
				'title_reply'          => $strings['title_reply'],
				'title_reply_to'       => $strings['title_reply_to'],
				'cancel_reply_link'    => $strings['cancel_reply_link'],
				'label_submit'         => $strings['label_submit'],
			);

			$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );
			self::form( $post_id, $args, $commenter, $user_identity );

		} else {

			do_action( 'comment_form_comments_closed' );
		}
	}

	private static function form( $post_id, $args, $commenter, $user_identity = '' )
	{
		do_action( 'comment_form_before' );

		echo '<div id="respond" class="comment-form"><h3 id="reply-title" class="comment-reply-title">';
			comment_form_title( $args['title_reply'], $args['title_reply_to'] );
			echo ' <small>';
				cancel_comment_reply_link( $args['cancel_reply_link'] );
			echo '</small></h3>';

			if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {

				echo $args['must_log_in'];
				do_action( 'comment_form_must_log_in_after' );

			} else {

				echo '<form action="'.site_url( '/wp-comments-post.php' )
					.'" method="post" id="'.esc_attr( $args['id_form'] ).'" role="form">';

					do_action( 'comment_form_top' );

					echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );

					if ( is_user_logged_in() ) {

						echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );

						do_action( 'comment_form_logged_in_after', $commenter, $user_identity );

					} else {

						echo $args['comment_notes_before'];

						do_action( 'comment_form_before_fields' );

						foreach ( (array) $args['fields'] as $name => $field )
							echo apply_filters( "comment_form_field_{$name}", $field )."\n";

						do_action( 'comment_form_after_fields' );
					}

					echo $args['comment_notes_after'];

					echo '<button class="btn btn-default" type="submit" id="'.esc_attr( $args['id_submit'] ).'">'.$args['label_submit'].'</button>';
					comment_id_fields( $post_id );

					do_action( 'comment_form', $post_id );

				echo '</form>';
			}

		echo '</div>';

		do_action( 'comment_form_after' );
	}
}
