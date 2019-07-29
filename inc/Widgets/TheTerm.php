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

		$before = $after = $image = '';

		$name = sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' );
		$desc = sanitize_term_field( 'description', $term->description, $term->term_id, $term->taxonomy, 'display' );

		if ( ! empty( $instance['meta_image'] ) )
			$image = gThemeImage::termImage( [ 'term_id' => $term->term_id, 'alt' => $name ] );

		if ( ! empty( $instance['content_actions'] ) && has_action( 'gtheme_widget_the_term_before' ) ) {
			ob_start();
				do_action( 'gtheme_widget_the_term_before', $term, $instance, $name, $desc, $image );
			$before = trim( ob_get_clean() );
		}

		if ( ! empty( $instance['content_actions'] ) && has_action( 'gtheme_widget_the_term_after' ) ) {
			ob_start();
				do_action( 'gtheme_widget_the_term_after', $term, $instance, $name, $desc, $image );
			$after = trim( ob_get_clean() );
		}

		if ( ! $before && ! $after && ! $desc && ! $image && ! empty( $instance['hide_no_desc'] ) )
			return;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance, $name );

		echo gThemeHTML::wrap( $image, 'gtheme-widget-image' );
		echo gThemeHTML::wrap( $before, 'gtheme-widget-before' );
		echo gThemeHTML::wrap( gThemeUtilities::prepDescription( $desc ), 'gtheme-widget-description' );
		echo gThemeHTML::wrap( $after, 'gtheme-widget-after' );

		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = sanitize_text_field( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['meta_image']      = isset( $new['meta_image'] );
		$instance['hide_no_desc']    = isset( $new['hide_no_desc'] );
		$instance['content_actions'] = isset( $new['content_actions'] );

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
		$this->form_checkbox( $instance, TRUE, 'content_actions', _x( 'Fire Before & After Actions', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );

		$this->after_form( $instance );
	}
}
