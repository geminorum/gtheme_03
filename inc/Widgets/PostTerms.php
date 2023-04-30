<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetPostTerms extends gThemeWidget
{

	// FIXME: DEPRECATED

	const WIDGET = 'post_terms';

	public static function setup()
	{
		return [
			'name'  => 'post_terms',
			'class' => 'post-terms',
			'title' => _x( 'Theme: Post Terms', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the assigned terms of the current post.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget( $args, $instance )
	{
		if ( ! is_singular() && ! is_single() )
			return;

		if ( ! $post = get_queried_object() )
			return;

		if ( empty( $instance['taxonomy'] ) || 'all' == $instance['taxonomy'] ) {

			$taxonomies = [];

			foreach ( get_object_taxonomies( $post->post_type, 'objects' ) as $object ) {

				if ( ! empty( $object->public ) && ! empty( $object->show_ui ) )
					$taxonomies[] = $object->name;
			}

		} else {

			$taxonomies = [ $instance['taxonomy'] ];
		}

		$html    = '';
		$default = get_option( 'default_category' );
		foreach ( $taxonomies as $taxonomy ) {

			$terms = get_the_terms( $post, $taxonomy );

			if ( ! $terms || is_wp_error( $terms ) )
				continue;

			foreach ( $terms as $term ) {

				if ( 'category' == $taxonomy && $term->term_id == $default )
					continue;

				$html.= '<li>'.gThemeHTML::link(
					sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ),
					get_term_link( $term->term_id, $term->taxonomy )
				).'</li>';
			}
		}

		if ( empty( $html ) )
			return;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
		echo '<div class="-list-wrap post-terms"><ul class="-items">';
			echo $html;
		echo '</ul></div>';
		$this->after_widget( $args, $instance );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );

		$this->form_taxonomy( $instance );

		$this->form_open_widget( $instance );
		$this->form_after_title( $instance );
		$this->form_close_widget( $instance );

		$this->after_form( $instance );
	}
}
