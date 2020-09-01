<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetSiblings extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'siblings',
			'class' => 'siblings',
			'title' => _x( 'Theme: Siblings', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the list of current post\'s siblings.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget( $args, $instance )
	{
		$post_type = empty( $instance['post_type'] ) ? 'page' : $instance['post_type'];

		$html = gTheme()->shortcodes->shortcode_siblings( [ 'type' => $post_type ] );

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
		$this->form_title_image( $instance );
		$this->form_class( $instance );
		$this->form_post_type( $instance, 'page' );

		$this->after_form( $instance );
	}
}
