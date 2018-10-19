<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetChildren extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'children',
			'class' => 'children',
			'title' => _x( 'Theme: Children', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the list of current post\'s children.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		];
	}

	public function widget( $args, $instance )
	{
		$post_type = empty( $instance['post_type'] ) ? 'page' : $instance['post_type'];

		$html = gTheme()->shortcodes->shortcode_children( [ 'type' => $post_type ] );

		if ( $html ) {
			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
				echo $html;
			$this->after_widget( $args, $instance );
		}
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance, 'page' );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}
