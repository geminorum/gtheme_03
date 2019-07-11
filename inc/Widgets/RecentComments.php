<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetRecentComments extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'recent_comments',
			'class' => 'recent-comments',
			'title' => _x( 'Theme: Recent Comments', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the most recent comments.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
			'flush' => [
				'comment_post',
				'edit_comment',
				'transition_comment_status',
			],
		];
	}

	public function widget_html( $args, $instance )
	{
		if ( empty( $instance['number'] )
			|| ! $number = absint( $instance['number'] ) )
				$number = 10;

		$comments = get_comments( apply_filters( 'widget_comments_args', [
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish'
		] ) );

		if ( $comments ) {

			$callback = gThemeOptions::info( 'recent_comment_callback', [ $this, 'comment_callback' ] );
			$avatar_size = empty( $instance['avatar_size'] ) ? 32 : absint( $instance['avatar_size'] );

			// prime cache for associated posts
			// prime post term cache if we need it for permalinks
			$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="-list-wrap recent-comments"><ul>';

			foreach ( (array) $comments as $comment ) {
				echo '<li>';
					echo call_user_func_array( $callback, [ $comment, $avatar_size ] );
				echo '</li>';
			}

			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function comment_callback( $comment, $avatar_size )
	{
		$content = gThemeL10N::str( wp_strip_all_tags( $comment->comment_content, TRUE ) );

		return sprintf( '<span class="comment-author-link">%1$s</span>: <a class="comment-post-link" href="%2$s" data-toggle="tooltip" data-placement="bottom" title="%3$s: %4$s">%5$s</a>',
			// get_comment_author_link(),
			gThemeL10N::str( get_comment_author( $comment->comment_ID ) ),
			esc_url( get_comment_link( $comment->comment_ID ) ),
			esc_attr( $content ),
			esc_attr( get_the_title( $comment->comment_post_ID ) ),
			wp_trim_words( $content, 10, '&nbsp;&hellip;' )
		);
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = sanitize_text_field( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['number']      = (int) $new['number'];
		$instance['avatar_size'] = (int) $new['avatar_size'];

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_avatar_size( $instance );
		$this->form_number( $instance, '5' );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}
