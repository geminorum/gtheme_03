<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetSearchTerms extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'search_terms',
			'class' => 'search-terms',
			'title' => _x( 'Theme: Search Terms', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the results of current search criteria on selected taxonomies.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget( $args, $instance )
	{
		if ( ! is_search() )
			return;

		// avoid filtering
		if ( ! $criteria = trim( get_query_var( 's' ) ) )
			return;

		// FIXME: optional skip if paged

		if ( empty( $instance['taxonomy'] ) || 'all' == $instance['taxonomy'] )
			$taxonomies = get_taxonomies( [ 'public' => TRUE, 'show_ui' => TRUE ] );
		else
			$taxonomies = [ $instance['taxonomy'] ];

		if ( ! empty( $instance['strip_hashtags'] ) )
			$criteria = preg_replace_callback( "/^#(.*)$/mu", function ( $matches ) {
				return str_replace( '_', ' ', $matches[1] );
			}, $criteria );

		$query = new \WP_Term_Query( [
			'search'     => $criteria, // 'name__like'
			'taxonomy'   => $taxonomies,
			'orderby'    => 'name',
			'hide_empty' => FALSE,
		] );

		if ( empty( $query->terms ) )
			return;

		$title = count( $taxonomies ) > 1;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
		echo '<div class="-list-wrap search-terms"><ul class="-items">';

		foreach ( $query->terms as $term ) {
			echo '<li>';

			if ( ! empty( $instance['prefix_with_name'] ) )
				printf( '%s:&nbsp;', get_taxonomy( $term->taxonomy )->labels->singular_name );

			echo gThemeHTML::tag( 'a', [
				'href'  => get_term_link( $term->term_id, $term->taxonomy ),
				'title' => $title && empty( $instance['prefix_with_name'] ) ? get_taxonomy( $term->taxonomy )->labels->singular_name : FALSE,
				'class' => [ '-term', '-taxonomy-'.$term->taxonomy ],
			], sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ) );

			echo '</li>';
		}

		echo '</ul></div>';
		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$this->flush_widget_cache();

		return $this->handle_update( $new, $old, [
			'prefix_with_name',
			'strip_hashtags',
		] );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );

		$this->form_taxonomy( $instance );

		$this->form_checkbox( $instance, FALSE, 'prefix_with_name', _x( 'Prefix Terms with Taxonomy Name', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, FALSE, 'strip_hashtags', _x( 'Strip Hash-tags', 'Widget: Setting', 'gtheme' ) );

		$this->after_form( $instance );
	}
}
