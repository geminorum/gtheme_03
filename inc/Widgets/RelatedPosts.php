<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetRelatedPosts extends gThemeWidget
{

	const WIDGET = 'related_posts';

	public static function setup()
	{
		return [
			'name'  => 'related_posts',
			'class' => 'related-posts',
			'title' => _x( 'Theme: Related Posts', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the related posts based on terms in a taxonomy.', 'Widget: Description', 'gtheme' ),
			'flush' => [
				'save_post',
				'deleted_post',
				'switch_theme',
			],
		];
	}

	public function widget( $args, $instance )
	{
		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( ! is_singular( $post_type ) )
			return;

		$this->widget_cache( $args, $instance, '_'.get_queried_object_id() );
	}

	public function widget_html( $args, $instance )
	{
		if ( ! $post_id = get_queried_object_id() )
			return FALSE;

		$context   = isset( $instance['context'] ) ? $instance['context'] : 'related';
		$taxonomy  = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'post_tag';
		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';
		$thumbnail = isset( $instance['has_thumbnail'] ) ? $instance['has_thumbnail'] : FALSE;
		$wrapitems = isset( $instance['wrap_as_items'] ) ? $instance['wrap_as_items'] : TRUE;

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );

		if ( is_wp_error( $terms ) || empty( $terms ) )
			return TRUE;

		$query_args = [
			'tax_query'      => [ [
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $terms,
				'operator' => 'IN',
			] ],
			'post_status'    => 'publish',
			'post_type'      => $post_type,
			'posts_per_page' => $number,
			'post__not_in'   => [ $post_id ],

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		];

		if ( GTHEME_SYSTEMTAGS && taxonomy_exists( GTHEME_SYSTEMTAGS ) ) {
			$query_args['tax_query']['relation'] = 'AND';
			$query_args['tax_query'][] = [
				'taxonomy' => GTHEME_SYSTEMTAGS,
				'field'    => 'slug',
				'terms'    => 'no-related',
				'operator' => 'NOT IN',
			];
		}

		if ( $thumbnail )
			$query_args['meta_query'] = [ [
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			] ];

		$row_query = new \WP_Query( $query_args );

		if ( $row_query->have_posts() ) {

			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="-list-wrap related-posts">';

			if ( $wrapitems )
				echo '<ul class="-items">';

			while ( $row_query->have_posts() ) {

				$row_query->the_post();

				if ( trim( get_the_title() ) ) {
					echo '<'.( $wrapitems ? 'li' : 'div' ).'>';
						get_template_part( 'row', $context );
						echo '<span class="-dummy"></span>';
					echo '</'.( $wrapitems ? 'li' : 'div' ).'>';
				}
			}

			wp_reset_postdata();

			if ( $wrapitems )
				echo '</ul>';

			echo '</div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$this->flush_widget_cache();
		return $this->handle_update( $new, $old, [ 'has_thumbnail', 'wrap_as_items' ] );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );
		$this->form_context( $instance, 'related' );
		$this->form_post_type( $instance );
		$this->form_taxonomy( $instance );
		$this->form_has_thumbnail( $instance );
		$this->form_number( $instance, '5' );
		$this->form_wrap_as_items( $instance );

		$this->form_open_widget( $instance );
		$this->form_after_title( $instance );
		$this->form_close_widget( $instance );

		$this->after_form( $instance );
	}
}
