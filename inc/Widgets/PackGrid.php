<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetPackGrid extends gThemeWidget
{

	const WIDGET = 'pack_grid';

	public static function setup()
	{
		return [
			'name'  => 'pack_grid',
			'class' => 'pack-grid',
			'title' => _x( 'Theme: Pack Grid', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays selected post thumb-nails as packed grid.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget_html( $args, $instance )
	{
		$images  = [];
		$context = empty( $instance['context'] ) ? '' : $instance['context'];
		$size    = empty( $instance['image_size'] ) ? 'thumbnail' : $instance['image_size'];
		$link    = empty( $instance['custom_link'] ) ? '#' : $instance['custom_link'];
		$title   = empty( $instance['custom_title'] ) ? '' : $instance['custom_title'];
		$content = empty( $instance['content'] ) ? '' : $instance['content'];

		if ( ! empty( $instance['post_ids'] ) ) {

			foreach ( gThemeUtilities::getSeparated( $instance['post_ids'] ) as $post_id ) {

				if ( ! $thumbnail_id = get_post_thumbnail_id( $post_id ) )
					continue;

				if ( ! $thumbnail_img = wp_get_attachment_image_src( $thumbnail_id, $size ) )
					continue;

				$images[] = $thumbnail_img[0];
			}

		} else if ( ! empty( $instance['attachemnt_ids'] ) ) {

			foreach ( gThemeUtilities::getSeparated( $instance['attachemnt_ids'] ) as $attachemnt_id ) {

				if ( ! $thumbnail_img = wp_get_attachment_image_src( $attachemnt_id, $size ) )
					continue;

				$images[] = $thumbnail_img[0];
			}
		}

		if ( empty( $images ) )
			return FALSE;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );

		echo '<div class="pack-grid">';
		echo '<a class="pack-grid__item" href="'.esc_url( $link ).'">';
		echo '<div class="pack-grid__images">';

			foreach ( $images as $image )
				echo '<img class="pack-grid__image" src="'.$image.'" alt="" decoding="async" loading="lazy" />';

		echo '</div>';
		echo '<h3 class="pack-grid__title"><span>'.$title.'</span></h3>';

		if ( $content )
			echo '<div class="pack-grid__text">'.gThemeText::autoP( $content ).'</div>';

		echo '</a></div>';

		$this->after_widget( $args, $instance );

		return TRUE;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_custom_code( $instance, '', 'post_ids', _x( 'Post IDs:', 'Widget: PackGrid', 'gtheme' ) );
		$this->form_custom_code( $instance, '', 'attachemnt_ids', _x( 'Attachment IDs:', 'Widget: PackGrid', 'gtheme' ) );
		$this->form_custom_link( $instance );
		$this->form_custom_title( $instance );
		$this->form_content( $instance );
		$this->form_image_size( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );
		$this->form_context( $instance, 'packgrid' );

		$this->form_open_widget( $instance );
		$this->form_after_title( $instance );
		$this->form_close_widget( $instance );

		$this->after_form( $instance );
	}
}
