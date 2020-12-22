<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetTheTerm extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'the_term',
			'class' => 'the-term',
			'title' => _x( 'Theme: The Term', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the current term info based on the query.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget( $args, $instance )
	{
		if ( defined( 'GTHEME_WIDGET_THETERM_DISABLED' )
			&& TRUE === GTHEME_WIDGET_THETERM_DISABLED )
				return;

		$term = FALSE;

		if ( empty( $instance['taxonomy'] ) || 'all' == $instance['taxonomy'] ) {

			if ( defined( 'GTHEME_WIDGET_THETERM_DISABLED' )
				&& 'all' === GTHEME_WIDGET_THETERM_DISABLED )
					return;

			if ( ! ( is_tax() || is_tag() || is_category() ) )
				return;

			$term = get_queried_object();

		} else {

			if ( defined( 'GTHEME_WIDGET_THETERM_DISABLED' )
				&& $instance['taxonomy'] === GTHEME_WIDGET_THETERM_DISABLED )
					return;

			if ( is_singular() || is_single() ) {

				$terms = get_the_terms( NULL, $instance['taxonomy'] );

				if ( ! $terms || is_wp_error( $terms ) )
					return;

				// grab the first one!
				$term = array_shift( $terms );

			} else if ( 'category' == $instance['taxonomy'] && is_category() ) {

				$term = get_queried_object();

			} else if ( 'post_tag' == $instance['taxonomy'] && is_tag() ) {

				$term = get_queried_object();

			} else if ( is_tax( $instance['taxonomy'] ) ) {

				$term = get_queried_object();
			}
		}

		if ( ! $term )
			return;

		$before = $after = $image = '';

		$name = sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' );
		$desc = sanitize_term_field( 'description', $term->description, $term->term_id, $term->taxonomy, 'display' );
		$link = get_term_link( $term, $term->taxonomy );

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

		$this->before_widget( $args, $instance, TRUE, ( $image ? '-has-image' : '' ) );
		$this->widget_title( $args, $instance, $name );

		// link image only on singular
		if ( is_singular() || is_single() )
			$image = gThemeHTML::link( $image, $link );

		echo gThemeHTML::wrap( $image, 'gtheme-widget-image' );

		// fallback in case of a custom title
		if ( ! empty( $instance['title'] ) )
			echo gThemeHTML::wrap( gThemeHTML::link( $name, $link ), 'gtheme-widget-name' );

		echo gThemeHTML::wrap( $before, 'gtheme-widget-before' );
		echo gThemeHTML::wrap( gThemeUtilities::prepDescription( $desc ), 'gtheme-widget-description' );
		echo gThemeHTML::wrap( $after, 'gtheme-widget-after' );

		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']       = sanitize_text_field( $new['title'] );
		$instance['title_link']  = strip_tags( $new['title_link'] );
		$instance['title_image'] = strip_tags( $new['title_image'] );
		$instance['class']       = strip_tags( $new['class'] );
		$instance['taxonomy']    = strip_tags( $new['taxonomy'] );

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
		$this->form_title_image( $instance );
		$this->form_class( $instance );

		$this->form_taxonomy( $instance );
		gThemeHTML::desc( _x( '&ldquo;All taxonomies option&rdquo; only works on archive pages.', 'Widget: Setting', 'gtheme' ) );

		$this->form_checkbox( $instance, TRUE, 'meta_image', _x( 'Display Meta Image', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, TRUE, 'hide_no_desc', _x( 'Hide if no Description', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, TRUE, 'content_actions', _x( 'Fire Before & After Actions', 'Widget: Setting', 'gtheme' ) );

		$this->after_form( $instance );
	}
}
