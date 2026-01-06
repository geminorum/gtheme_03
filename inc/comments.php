<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeComments extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'reverse_comments' => FALSE,
			'disable_types'    => FALSE,
			'closing_time'     => FALSE,
		], $args ) );

		add_filter( 'comment_class', [ $this, 'comment_class' ], 10 ,4 );
		add_action( 'comment_form_before', [ $this, 'comment_form_before' ] );

		if ( $reverse_comments )
			add_filter( 'comments_array', [ $this, 'comments_array_reverse' ], 12 );

		if ( $disable_types )
			add_filter( 'comments_open', [ $this, 'comments_open' ], 10 , 2 );

		if ( $closing_time )
			add_action( 'comment_form_top', [ $this, 'comment_form_top' ] );
	}

	public static function template( $before = '', $after = '', $post = NULL )
	{
		if ( ! gThemeOptions::info( 'comments_support', TRUE ) )
			return;

		if ( ! $post = get_post( $post ) )
			return;

		// dummy post
		if ( ! $post->ID )
			return;

		if ( 'page' == $post->post_type )
			return;

		do_action( 'gtheme_comments_before', $post );

		echo $before;
			comments_template( '', FALSE );
		echo $after;

		do_action( 'gtheme_comments_after', $post );
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

	// @REF: https://wp.me/p29gdg-eL
	// @REF: http://wpengineer.com/?p=2358
	public function comment_form_before()
	{
		if ( comments_open() && get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );
	}

	// http://www.wprecipes.com/how-to-reverse-wordpress-comments-order
	public function comments_array_reverse( $comments )
	{
		return array_reverse( $comments, TRUE );
	}

	// http://www.wpbeginner.com/wp-tutorials/how-to-disable-comments-on-wordpress-media-attachments/
	public function comments_open( $open, $post_id )
	{
		if ( ! $disabled = gThemeOptions::info( 'comments_disable_types', [ 'attachment' ] ) )
			return $open;

		if ( in_array( get_post_type( $post_id ), (array) $disabled ) )
			return FALSE;

		return $open;
	}

	// Inform user about automatic comment closing time
	// http://wpengineer.com/2692/inform-user-about-automatic-comment-closing-time/
	// TODO: bootstrap styling / notice
	public function comment_form_top()
	{
		global $post;

		if ( 'open' == $post->comment_status ) {

			$expires = strtotime( "{$post->post_date_gmt} GMT" )
					 + get_option( 'close_comments_days_old' )
					 * DAY_IN_SECONDS;

			printf(
				/* translators: `%s`: human time diff */
				_x( '(This topic will automatically close in %s.)', 'Modules: Comments', 'gtheme' ),
				human_time_diff( $expires, current_time( 'timestamp' ) )
			);
		}
	}

	public static function passwordRequired( $print = TRUE )
	{
		if ( ! post_password_required() )
			return FALSE;

		if ( $print )
			echo '<p class="no-password">'
				._x( 'This post is password protected. Enter the password to view any comments.', 'Modules: Comments', 'gtheme' )
			.'</p>';

		echo '</div>';

		return TRUE;
	}

	public static function navigation( $class = 'comment-nav-above' )
	{
		$strings = gThemeOptions::info( 'comment_nav_strings', [
			'title'    => _x( 'Comment navigation', 'Modules: Comments', 'gtheme' ),
			'previous' => _x( '&rarr; Older Comments', 'Modules: Comments', 'gtheme' ),
			'next'     => _x( 'Newer Comments &larr;', 'Modules: Comments', 'gtheme' ),
		] );

		echo '<nav class="navigation comment-navigation '.$class.'">';

		if ( $strings['title'] )
			echo '<h4 class="screen-reader-text sr-only visually-hidden">'.$strings['title'].'</h4>';

		echo '<div class="nav-previous">';
			previous_comments_link( $strings['previous'] );
		echo '</div><div class="nav-next">';
			next_comments_link( $strings['next'] );
		echo '</div></nav>';
	}

	public static function renderTitle( $class = 'comments-title', $tag = 'h3' )
	{
		$callback = gThemeOptions::info( 'comments_title_callback', [ 'gThemeComments', 'title_callback' ] );

		if ( is_callable( $callback ) )
			call_user_func_array( $callback, [ 'comments-title', 'h3' ] );
	}

	public static function title_callback_simple( $class = 'comments-title', $tag = 'h3' )
	{
		gThemeHTML::h3( _x( 'Comments', 'Modules: Comments Title', 'gtheme' ), $class );
	}

	public static function title_callback( $class = 'comments-title', $tag = 'h3' )
	{
		$comments = get_comments_number();
		/* translators: %1$s: comments number, %2$s: post title */
		$template = _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $comments, 'Modules: Comments Title', 'gtheme' );
		$title    = sprintf( $template, number_format_i18n( $comments ), '<span>'.get_the_title().'</span>' );

		echo gThemeHTML::tag( $tag, [ 'class' => $class ], $title );
	}

	public static function feed( $class = 'comments-feed' )
	{
		$icon = '<svg style="display:none" viewBox="0 0 32 32"><path d="M16-0.034c-8.842 0-16.034 7.192-16.034 16.034s7.192 16.034 16.034 16.034 16.034-7.192 16.034-16.034-7.192-16.034-16.034-16.034zM16 30.966c-8.252 0-14.966-6.714-14.966-14.966s6.714-14.966 14.966-14.966 14.966 6.714 14.966 14.966-6.714 14.966-14.966 14.966zM10.502 13.951c-0.295 0-0.534 0.239-0.534 0.534s0.239 0.534 0.534 0.534c2.253 0 4.14 0.697 5.454 2.016 1.315 1.318 2.010 3.208 2.010 5.465 0 0.295 0.239 0.534 0.534 0.534s0.534-0.239 0.534-0.534c0.001-2.546-0.802-4.696-2.32-6.22-1.521-1.523-3.668-2.329-6.212-2.329zM10.505 9.027c-0.295 0-0.534 0.239-0.534 0.534s0.239 0.534 0.534 0.534c7.14 0 12.523 5.333 12.523 12.404 0 0.295 0.239 0.534 0.534 0.534s0.534-0.239 0.534-0.534c0.001-7.68-5.842-13.472-13.591-13.472zM11.999 18.882c-1.121 0-2.033 0.913-2.033 2.035 0 1.121 0.912 2.033 2.033 2.033 1.122 0 2.035-0.912 2.035-2.034s-0.914-2.034-2.035-2.034zM11.999 21.882c-0.532 0-0.965-0.433-0.965-0.965 0-0.533 0.433-0.967 0.965-0.967s0.966 0.435 0.967 0.967c0 0.532-0.434 0.965-0.967 0.965z"></path></svg>';

		$html = gThemeHTML::tag( 'a', [
			'href'  => get_post_comments_feed_link(),
			'title' => _x( 'Grab the feed for comments of this post', 'Modules: Comments', 'gtheme' ),
		], $icon );

		echo gThemeHTML::wrap( $html, $class );
	}

	// When this is enabled, new comments on a post will not refresh the cached static files.
	public static function lockDownNotice( $class = '' )
	{
		if ( defined( 'WPLOCKDOWN' ) && constant( 'WPLOCKDOWN' ) ) {
			echo '<div class="lockdown-notice '.$class.'">';
				_ex( 'Sorry, The site is locked down. Updates will appear shortly.', 'Modules: Comments', 'gtheme' );
			echo '</div>';
		}
	}

	public static function renderList()
	{
		echo '<ol class="commentlist comment-list media-list">';

		// http://codex.wordpress.org/Function_Reference/wp_list_comments
		wp_list_comments( [
			'callback' => gThemeOptions::info( 'comments_item_callback', [ 'gThemeComments', 'comment_callback' ] ),
			'style'    => 'ol',
			'type'     => 'comment', // no ping & trackback / default is 'all'
		] );

		echo '</ol>';
	}

	public static function comment_callback( $comment, $args, $depth )
	{
		switch ( $comment->comment_type ) {

			case 'pingback':
			case 'trackback':
			break;

			case 'review':
			case 'comment':
			case '':
			// default:

				$avatar     = get_option( 'show_avatars' );
				$comment_id = get_comment_ID(); // for the filter

				echo '<li ';
					comment_class( 'media'.( $avatar ? ' -with-avatar' : ' -no-avatar' ) );
				echo ' id="comment-'.$comment_id.'">';

				if ( $avatar ) {
					if ( $author_url = get_comment_author_url() ) {

						echo '<a class="comment-avatar" href="'.esc_url( $author_url ).'" rel="external nofollow">';
							gThemeTemplate::avatar( $comment );
						echo '</a>';

					} else {

						echo '<span class="comment-avatar">';
							gThemeTemplate::avatar( $comment );
						echo '</span>';
					}
				}

				echo '<div class="comment-body" id="comment-body-'.$comment_id.'">';

					echo '<h6 class="comment-meta">';
						echo '<span class="comment-author">'.get_comment_author_link().'</span>';
						echo ' ';
						self::time( $comment, '<small class="comment-time">', '</small>' );
					echo '</h6>';

					echo '<div class="comment-content">';
						comment_text( $comment );
					echo '</div>';

					self::awaiting( $comment );
					self::commentActions( $comment, $args, $depth );

				echo '</div>';
		}
	}

	public static function awaiting( $comment, $before = '<p class="text-danger comment-awaiting-moderation comment-moderation">', $after = '</p>' )
	{
		if ( '0' != $comment->comment_approved )
			return;

		$awaiting = gThemeOptions::info( 'comment_awaiting',
			_x( 'Your comment is awaiting moderation.', 'Modules: Comments', 'gtheme' ) );

		if ( $awaiting )
			echo $before.$awaiting.$after;
	}

	public static function time( $comment, $before = '', $after = '' )
	{
		echo $before;
		echo '<a href="'.esc_url( get_comment_link( $comment ) ).'">';
			echo '<time datetime="'.get_comment_time( 'c', TRUE, FALSE ).'">';
			/* translators: %1$s: comment date, %2$s: comment time */
			printf( _x( '%1$s at %2$s', 'Modules: Comments: Comment Time', 'gtheme' ),
				get_comment_date(),
				get_comment_time()
			);
		echo '</time></a>';
		echo $after;
	}

	public static function commentActions( $comment, $args, $depth, $class = '' )
	{
		$actions = [];

		$strings = gThemeOptions::info( 'comment_action_strings', [
			'reply_text'    => _x( 'Reply', 'Modules: Comments: Action String', 'gtheme' ),
			/* translators: %s: reply to user */
			'reply_to_text' => _x( 'Reply to %s', 'Modules: Comments: Action String', 'gtheme' ),
			'login_text'    => _x( 'Log in to Reply', 'Modules: Comments: Action String', 'gtheme' ),
			'edit'          => _x( 'Edit This', 'Modules: Comments: Action String', 'gtheme' ),
		] );

		$reply = get_comment_reply_link( [
			'depth'         => $depth,
			'max_depth'     => $args['max_depth'],
			'add_below'     => 'comment-body',
			// 'before'        => '<span class="reply">',
			// 'after'         => '</span>',
			'reply_text'    => $strings['reply_text'],
			'reply_to_text' => $strings['reply_to_text'],
			'login_text'    => $strings['login_text'],
		], $comment );

		if ( $reply )
			$actions['reply'] = $reply;

		if ( $edit = get_edit_comment_link( $comment->comment_ID ) )
			$actions['edit-link'] = gThemeHTML::tag( 'a', [
				'href'  => $edit,
				'class' => 'comment-edit-link',
			], $strings['edit'] );

		$actions = apply_filters( 'gtheme_comment_actions', $actions, $comment, $args, $depth );

		if ( empty( $actions ) )
			return;

		echo '<ul class="comment-actions list-inline '.$class.'">';
			foreach ( $actions as $action_class => $action )
				echo '<li class="list-inline-item '.$action_class.'">'.$action.'</li>';
		echo '</ul>';
	}

	public static function renderForm( $post = NULL )
	{
		if ( gThemeUtilities::isPrint() )
			return;

		$callback = gThemeOptions::info( 'comments_form_callback', [ 'gThemeComments', 'form_callback' ] );

		if ( is_callable( $callback ) )
			call_user_func_array( $callback, [ [], $post ] );

		else if ( is_null( $callback ) )
			comment_form();
	}

	// @REF: `comment_form()`
	public static function form_callback( $args = [], $post = NULL )
	{
		if ( ! $post = get_post( $post ) )
			return;

		if ( ! post_type_supports( $post->post_type, 'comments' ) )
			return;

		if ( comments_open( $post ) ) {

			$user     = wp_get_current_user();
			$identity = empty( $user->ID ) ? '' : $user->display_name;

			$commenter = wp_get_current_commenter();
			$permalink = apply_filters( 'the_permalink', get_permalink( $post ), $post );
			$required  = get_option( 'require_name_email' );

			$strings = self::atts( [
				'required' => _x( '(Required)', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'name'     => _x( 'Name', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'email'    => _x( 'Email', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'url'      => _x( 'Website', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'comment'  => _x( 'Comment', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'cookies'  => _x( 'Save my name, email, and site URL in my browser for next time I post a comment.', 'Modules: Comments: Comment Form String', 'gtheme' ),

				/* translators: `%s`: login URL */
				'must_log_in'        => _x( 'You must be <a href="%s">logged in</a> to post a comment.', 'Modules: Comments: Comment Form String', 'gtheme' ),
				/* translators: `%1$s`: profile URL, `%2$s`: logged in as title, `%3$s`: display name, `%4$s`: log-out URL */
				'logged_in_as'       => _x( '<a href="%1$s" aria-label="%2$s">Logged in as %3$s</a>. <a href="%4$s">Log out?</a>', 'Modules: Comments: Comment Form String', 'gtheme' ),
				/* translators: `%s`: display name */
				'logged_in_as_title' => _x( 'Logged in as %s. Edit your profile.', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'title_reply'        => _x( 'Leave a Reply', 'Modules: Comments: Comment Form String', 'gtheme' ),
				/* translators: `%s`: reply to title */
				'title_reply_to'     => _x( 'Leave a Reply to %s', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'cancel_reply_link'  => _x( 'Cancel reply', 'Modules: Comments: Comment Form String', 'gtheme' ),
				'label_submit'       => _x( 'Post Comment', 'Modules: Comments: Comment Form String', 'gtheme' ),
			], gThemeOptions::info( 'comment_form_strings', [] ) );

			$fields = [];

			$fields['author'] = '<div class="-form-group form-group comment-form-author"><label for="author" class="form-label">'
				.$strings['name']
				.( $required ? ' <span class="required">'.$strings['required'].'</span>' : '' )
				.'</label>'
				.gThemeHTML::tag( 'input', [
					'type'         => 'text',
					'autocomplete' => 'name',
					'required'     => $required,
					'class'        => 'form-control',
					'size'         => '30',
					'id'           => 'author',
					'name'         => 'author',
					'value'        => $commenter['comment_author'],
				] ).'</div>';

			$fields['email'] = '<div class="-form-group form-group comment-form-email"><label for="email" class="form-label">'
				.$strings['email']
				.( $required ? ' <span class="required">'.$strings['required'].'</span>' : '' )
				.'</label>'
				.gThemeHTML::tag( 'input', [
					'type'         => 'email',
					'autocomplete' => 'email',
					'required'     => $required,
					'class'        => 'form-control comment-field-ltr',
					'size'         => '30',
					'id'           => 'email',
					'name'         => 'email',
					'value'        => $commenter['comment_author_email'],
					// 'placeholder'        => $strings['email'], // NOTE: problem with rtl
				] ).'</div>';

			$fields['url'] = '<div class="-form-group form-group comment-form-url"><label for="url" class="form-label">'
				.$strings['url']
				.'</label>'
				.gThemeHTML::tag( 'input', [
					'type'         => 'url',
					'autocomplete' => 'url',
					'class'        => 'form-control comment-field-ltr',
					'size'         => '30',
					'id'           => 'url',
					'name'         => 'url',
					'value'        => $commenter['comment_author_url'],
					// 'placeholder'   => $strings['url'], // NOTE: problem with rtl
				] ).'</div>';

			if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) )
				$fields['cookies'] = '<div class="-form-group form-check checkbox comment-form-cookies-consent">'
					.'<label for="wp-comment-cookies-consent" class="form-check-label">'
					.gThemeHTML::tag( 'input', [
						'type'    => 'checkbox',
						'id'      => 'wp-comment-cookies-consent',
						'name'    => 'wp-comment-cookies-consent',
						'class'   => 'form-check-input', // BS5
						'value'   => 'yes',
						'checked' => ! empty( $commenter['comment_author_email'] ),
					] ).' '.$strings['cookies']
					.'</label></div>';

			$defaults = [
				'fields' => apply_filters( 'comment_form_default_fields', $fields ),

				'comment_field' => '<div class="-form-group form-group comment-form-comment"><label for="comment" class="screen-reader-text sr-only visually-hidden">'
					.$strings['comment'].'</label>'
					.gThemeHTML::tag( 'textarea', [
						'required'    => TRUE,
						'class'       => 'form-control',
						'cols'        => '45',
						'rows'        => '4',
						'id'          => 'comment',
						'name'        => 'comment',
						'placeholder' => $strings['comment'],
					], NULL ).'</div>',

				'must_log_in' => '<p class="must-log-in">'
						.sprintf( $strings['must_log_in'], wp_login_url( $permalink ) )
					.'</p>',

				'logged_in_as' => '<p class="logged-in-as">'
					.vsprintf( $strings['logged_in_as'], [
						get_edit_user_link(),
						esc_attr( sprintf( $strings['logged_in_as_title'], $identity ) ),
						$identity,
						wp_logout_url( $permalink ),
					] ).'</p>',

				'comment_notes_before' => '',
				'comment_notes_after'  => '',

				'id_form'      => 'commentform',
				'id_submit'    => 'submit',
				'class_form'   => 'form-comment-form',
				'class_submit' => 'submit btn btn-default btn-outline-secondary',
				'name_submit'  => 'submit',

				'title_reply'        => $strings['title_reply'],
				'title_reply_to'     => $strings['title_reply_to'],
				'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
				'title_reply_after'  => '</h3>',

				'cancel_reply_link'   => $strings['cancel_reply_link'],
				'cancel_reply_before' => ' <small>',
				'cancel_reply_after'  => '</small>',

				'label_submit'  => $strings['label_submit'],
				'submit_button' => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
				'submit_field'  => '<p class="form-submit">%1$s %2$s</p>',

				'action' => site_url( '/wp-comments-post.php' ),
			];

			$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

			self::form( $post, $args, $commenter, $identity );

			gThemeUtilities::enqueueAutosize();

		} else {

			do_action( 'comment_form_comments_closed' );
		}
	}

	private static function form( $post, $args, $commenter, $identity = '' )
	{
		do_action( 'comment_form_before' );

		echo '<div id="respond" class="comment-form">';

			echo $args['title_reply_before'];
				comment_form_title( $args['title_reply'], $args['title_reply_to'] );
				echo $args['cancel_reply_before'];
					cancel_comment_reply_link( $args['cancel_reply_link'] );
				echo $args['cancel_reply_after'];
			echo $args['title_reply_after'];

			if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {

				echo $args['must_log_in'];
				do_action( 'comment_form_must_log_in_after' );

			} else {

				echo '<form action="'.$args['action']
					.'" method="post" id="'.esc_attr( $args['id_form'] )
					.'" class="'.esc_attr( $args['class_form'] )
					.'" role="form">';

					do_action( 'comment_form_top' );

					echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );

					if ( is_user_logged_in() ) {

						echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $identity );

						do_action( 'comment_form_logged_in_after', $commenter, $identity );

					} else {

						echo $args['comment_notes_before'];

						do_action( 'comment_form_before_fields' );

						foreach ( (array) $args['fields'] as $name => $field )
							echo apply_filters( "comment_form_field_{$name}", $field )."\n";

						do_action( 'comment_form_after_fields' );
					}

					echo $args['comment_notes_after'];

					$submit_button = sprintf(
						$args['submit_button'],
						esc_attr( $args['name_submit'] ),
						esc_attr( $args['id_submit'] ),
						esc_attr( $args['class_submit'] ),
						esc_attr( $args['label_submit'] )
					);

					$submit_field = sprintf(
						$args['submit_field'],
						apply_filters( 'comment_form_submit_button', $submit_button, $args ),
						get_comment_id_fields( $post->ID )
					);

					echo apply_filters( 'comment_form_submit_field', $submit_field, $args );

					do_action( 'comment_form', $post->ID );

				echo '</form>';
			}

		echo '</div>';

		do_action( 'comment_form_after' );
	}
}
