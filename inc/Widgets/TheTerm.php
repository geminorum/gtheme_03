<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetTheTerm extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'the_term',
			'class' => 'the-term',
			'title' => _x( 'Theme: The Term', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the current term info based on the query.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		];
	}

	public function widget( $args, $instance )
	{
		if ( defined( 'GTHEME_WIDGET_THETERM_DISABLED' )
			&& GTHEME_WIDGET_THETERM_DISABLED )
				return;

		if ( ! ( is_tax() || is_tag() || is_category() ) )
			return;

		if ( ! $term = get_queried_object() )
			return;

		$desc  = get_term_field( 'description', $term->term_id, $term->taxonomy );
		$image = ! empty( $instance['meta_image'] ) ? gThemeImage::termImage( [ 'term_id' => $term->term_id ] ) : FALSE;

		if ( ! $desc && ! $image && ! empty( $instance['hide_no_desc'] ) )
			return;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance, sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ) );

		if ( $image )
			echo $image;

		if ( $desc )
			echo gThemeUtilities::prepDescription( $desc );

		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = sanitize_text_field( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['meta_image']   = isset( $new['meta_image'] );
		$instance['hide_no_desc'] = isset( $new['hide_no_desc'] );

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_class( $instance );

		$this->form_checkbox( $instance, TRUE, 'meta_image', _x( 'Display Meta Image', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );
		$this->form_checkbox( $instance, TRUE, 'hide_no_desc', _x( 'Hide if no Description', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );

		$this->after_form( $instance );
	}
}
