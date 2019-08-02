<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetPostTerms extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'post_terms',
			'class' => 'post-terms',
			'title' => _x( 'Theme: Post Terms', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the current post\'s assigned terms on selected taxonomy.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
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

		$html = '';

		foreach ( $taxonomies as $taxonomy ) {

			$terms = get_the_terms( $post, $taxonomy );

			if ( ! $terms || is_wp_error( $terms ) )
				continue;

			foreach ( $terms as $term ) {
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
		echo '<div class="-list-wrap search-terms"><ul>';
			echo $html;
		echo '</ul></div>';
		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = sanitize_text_field( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['taxonomy'] = strip_tags( $new['taxonomy'] );

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_class( $instance );

		$this->form_taxonomy( $instance );

		$this->after_form( $instance );
	}
}
