<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetTermPosts extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'term_posts',
			'class' => 'term-posts',
			'title' => _x( 'Theme: Term Posts', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the latest posts from a single term.', 'Widget: Description', 'gtheme' ),
			'flush' => [
				'save_post',
				'deleted_post',
				'switch_theme',
			],
		];
	}

	public function widget_html( $args, $instance )
	{
		$context  = isset( $instance['context'] ) ? $instance['context'] : 'recent';
		$term_id  = isset( $instance['term_id'] ) ? $instance['term_id'] : FALSE;
		$taxonomy = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'post_tag';
		$posttype = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( ! $term_id )
			return FALSE;

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$singular = is_singular( $posttype ) || is_single( $posttype );

		$query_args = [
			'tax_query' => [ [
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => [ $term_id ],
				'operator' => 'IN',
			] ],
			'posts_per_page' => $number,
			'post_type'      => $posttype,
			'post_status'    => 'publish',

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		];

		if ( $singular )
			$query_args['post__not_in'] = [ get_queried_object_id() ];

		if ( ! empty( $instance['menu_order'] ) )
			$query_args['orderby'] = 'menu_order date';

		$row_query = new \WP_Query( $query_args );

		if ( $row_query->have_posts() ) {

			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="-list-wrap term-posts"><ul class="-items">';

			while ( $row_query->have_posts() ) {

				$row_query->the_post();

				if ( trim( get_the_title() ) ) {
					echo '<li>';
						get_template_part( 'row', $context );
						echo '<span class="-dummy"></span>';
					echo '</li>';
				}
			}

			wp_reset_postdata();

			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return ! $singular; // avoid caching if it's singular

		} else if ( $instance['empty'] ) {

			$this->before_widget( $args, $instance );
				$this->widget_title( $args, $instance );
				gThemeHTML::desc( $instance['empty'], TRUE, '-empty' );
			$this->after_widget( $args, $instance );

			return ! $singular; // avoid caching if it's singular
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']       = sanitize_text_field( $new['title'] );
		$instance['title_link']  = strip_tags( $new['title_link'] );
		$instance['title_image'] = strip_tags( $new['title_image'] );
		$instance['term_id']     = empty( $new['term_id'] ) ? 0 : (int) $new['term_id'];
		$instance['taxonomy']    = strip_tags( $new['taxonomy'] );
		$instance['post_type']   = strip_tags( $new['post_type'] );
		$instance['context']     = strip_tags( $new['context'] );
		$instance['class']       = strip_tags( $new['class'] );

		$instance['menu_order'] = isset( $new['menu_order'] );

		$instance['number'] = (int) $new['number'];
		$instance['empty']  = trim( current_user_can( 'unfiltered_html' ) ? $new['empty'] : wp_kses_post( $new['empty'] ) );

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
		$this->form_context( $instance, 'recent' );
		$this->form_post_type( $instance );
		$this->form_taxonomy( $instance );
		$this->form_term_id( $instance );
		$this->form_checkbox( $instance, FALSE, 'menu_order', _x( 'Use Internal Order', 'Widget: Setting', 'gtheme' ) );
		$this->form_number( $instance, '5' );

		$this->form_custom_empty( $instance );

		$this->after_form( $instance );
	}
}
