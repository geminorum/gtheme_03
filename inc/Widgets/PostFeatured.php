<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetPostFeatured extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'post_featured',
			'class' => 'post-featured',
			'title' => _x( 'Theme: Post Featured', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the featured image of the current post.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget( $args, $instance )
	{
		if ( ! is_singular() && ! is_single() )
			return;

		if ( ! $post = get_queried_object() )
			return;

		$size = empty( $instance['image_size'] ) ? 'medium' : $instance['image_size'];
		$html = get_the_post_thumbnail( $post, $size );

		if ( empty( $html ) )
			return;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
		echo '<div class="post-featured">';

			if ( empty( $instance['linked'] ) )
				echo $html;

			else
				echo '<a href="'.esc_url( get_attachment_link( get_post_thumbnail_id( $post ) ) ).'" class="-attachment">'.$html.'</a>';

		echo '</div>';
		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']       = sanitize_text_field( $new['title'] );
		$instance['title_link']  = strip_tags( $new['title_link'] );
		$instance['title_image'] = strip_tags( $new['title_image'] );
		$instance['class']       = strip_tags( $new['class'] );
		$instance['image_size']  = empty( $new['image_size'] ) ? 'medium' : strip_tags( $new['image_size'] );
		$instance['linked']      = isset( $new['linked'] );

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );
		$this->form_image_size( $instance, 'medium' );
		$this->form_checkbox( $instance, FALSE, 'linked', _x( 'Link to Attachment Page', 'Widget: Setting', 'gtheme' ) );

		$this->after_form( $instance );
	}
}
