<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetPostRow extends gThemeWidget
{

	// TODO: filter `widget_title`, for dynamic `{post_title}`

	const WIDGET = 'post_row';

	public static function setup()
	{
		return [
			'name'  => 'post_row',
			'class' => 'post-row',
			'title' => _x( 'Theme: Post Row', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays selected post as row.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget_html( $args, $instance )
	{
		if ( empty( $instance['post_id'] ) )
			return FALSE;

		if ( ! $post = get_post( $instance['post_id'] ) )
			return FALSE;

		// TODO: check if the post is private

		$context = empty( $instance['context'] ) ? '' : $instance['context'];

		// @REF: https://developer.wordpress.org/?p=2837#comment-874
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
			get_template_part( 'row', $context );
		$this->after_widget( $args, $instance );

		wp_reset_postdata(); // since we setup post data

		return TRUE;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_number( $instance, '', 'post_id', _x( 'Post ID:', 'Widget: PostRow', 'gtheme' ) );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );
		$this->form_context( $instance, 'widget' );

		$this->form_open_widget( $instance );
		$this->form_after_title( $instance );
		$this->form_close_widget( $instance );

		$this->after_form( $instance );
	}
}
