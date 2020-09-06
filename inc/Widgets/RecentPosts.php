<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetRecentPosts extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'recent_posts',
			'class' => 'recent-posts',
			'title' => _x( 'Theme: Recent Posts', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays the most recent posts.', 'Widget: Description', 'gtheme' ),
			'flush' => [
				'save_post',
				'deleted_post',
				'switch_theme',
			],
		];
	}

	public function widget_html( $args, $instance )
	{
		$posttype = empty( $instance['post_type'] ) ? 'post' : $instance['post_type'];
		$context  = isset( $instance['context'] ) ? $instance['context'] : 'recent';

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$singular = is_singular( $posttype ) || is_single( $posttype );

		$query_args = [
			'posts_per_page' => $number,
			'post_type'      => $posttype,
			'post_status'    => 'publish',

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		];

		if ( $singular ) // TODO: make optional
			$query_args['post__not_in'] = [ get_queried_object_id() ];

		if ( ! empty( $instance['menu_order'] ) )
			$query_args['orderby'] = 'menu_order date';

		$row_query = new \WP_Query( $query_args );

		if ( $row_query->have_posts() ) {

			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="-list-wrap recent-posts"><ul>';

			while ( $row_query->have_posts() ) {

				$row_query->the_post();

				if ( trim( get_the_title() ) ) {
					echo '<li>';
						get_template_part( 'row', $context );
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
		$this->form_checkbox( $instance, FALSE, 'menu_order', _x( 'Use Internal Order', 'Widget: Setting', 'gtheme' ) );
		$this->form_number( $instance, '5' );

		$this->form_custom_empty( $instance );

		$this->after_form( $instance );
	}
}
