<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetTemplatePart extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'template_part',
			'class' => 'template-part',
			'title' => _x( 'Theme: Template Part', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Include selected template part into sidebars.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget( $args, $instance )
	{
		$context = empty( $instance['context'] ) ? '' : $instance['context'];

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
			get_template_part( 'widget', $context );
		$this->after_widget( $args, $instance );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );
		$this->form_context( $instance );

		$this->form_open_widget( $instance );
		$this->form_after_title( $instance );
		$this->form_close_widget( $instance );

		$this->after_form( $instance );
	}
}
