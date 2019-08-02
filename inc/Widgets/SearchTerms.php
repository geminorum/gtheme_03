<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetSearchTerms extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'search_terms',
			'class' => 'search-terms',
			'title' => _x( 'Theme: Search Terms', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the results of current search criteria on selected taxonomies.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		];
	}

	public function widget( $args, $instance )
	{
		if ( ! is_search() )
			return;

		if ( ! $criteria = trim( get_search_query() ) )
			return;

		if ( empty( $instance['taxonomy'] ) || 'all' == $instance['taxonomy'] )
			$taxonomies = NULL;
		else
			$taxonomies = [ $instance['taxonomy'] ];

		$query = new \WP_Term_Query( [
			'name__like' => $criteria,
			'taxonomy'   => $taxonomies,
			'orderby'    => 'name',
			'hide_empty' => TRUE,
		] );

		if ( empty( $query->terms ) )
			return;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
		echo '<div class="-list-wrap search-terms"><ul>';

		foreach ( $query->terms as $term ) {
			echo '<li>'.gThemeHTML::link(
				sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ),
				get_term_link( $term->term_id, $term->taxonomy )
			).'</li>';
		}

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
