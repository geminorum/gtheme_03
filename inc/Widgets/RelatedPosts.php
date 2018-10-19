<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetRelatedPosts extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'related_posts',
			'class' => 'related-posts',
			'title' => _x( 'Theme: Related Posts', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the related posts based on terms in a taxonomy.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
			'flush' => [
				'save_post',
				'deleted_post',
				'switch_theme',
			],
		];
	}

	public function widget( $args, $instance )
	{
		global $post;

		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( ! is_singular( $post_type ) )
			return;

		$this->widget_cache( $args, $instance, '_'.$post->ID );
	}

	public function widget_html( $args, $instance )
	{
		global $post;

		$context   = isset( $instance['context'] ) ? $instance['context'] : 'related';
		$taxonomy  = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'post_tag';
		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$terms = wp_get_object_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );

		if ( is_wp_error( $terms ) || empty( $terms ) )
			return TRUE;

		$row_query = new \WP_Query( [
			'tax_query' => [ [
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $terms,
				'operator' => 'IN',
			] ],
			'post_type'      => $post_type,
			'post__not_in'   => [ $post->ID ],
			'posts_per_page' => $number,
			'post_status'    => 'publish',

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		] );

		if ( $row_query->have_posts() ) {
			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="theme-list-wrap related-posts"><ul>';
			while ( $row_query->have_posts() ) {
				$row_query->the_post();
				if ( trim( get_the_title() ) ) {
					echo '<li>'; get_template_part( 'row', $context ); echo '</li>';
				}
			}
			wp_reset_postdata();
			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = sanitize_text_field( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['context']    = strip_tags( $new['context'] );
		$instance['class']      = strip_tags( $new['class'] );
		$instance['post_type']  = strip_tags( $new['post_type'] );
		$instance['taxonomy']   = strip_tags( $new['taxonomy'] );

		$instance['number'] = (int) $new['number'];

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance );
		$this->form_taxonomy( $instance );
		$this->form_context( $instance, 'related' );
		$this->form_class( $instance );
		$this->form_number( $instance, '5' );

		$this->after_form( $instance );
	}
}