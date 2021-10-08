<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetBannerGroup extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'banner_group',
			'class' => 'banner-group',
			'title' => _x( 'Theme: Banner Group', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays banners from selected group into sidebars.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget_html( $args, $instance )
	{
		if ( empty( $instance['group'] ) )
			return FALSE;

		$renderer = isset( $instance['renderer'] )
			? $instance['renderer']
			: gThemeOptions::info( 'banner_default_renderer', 'bootstrap_carousel' );

		$before = $this->before_widget( $args, $instance, FALSE, $renderer );
		$title  = $this->widget_title( $args, $instance, FALSE );
		$after  = $this->after_widget( $args, $instance, FALSE );

		switch ( $renderer ) {

			case 'slick_carousel':

				$callback = [ 'gThemeBanners', 'slickCarousel' ];

				if ( ! is_callable( $callback ) )
					return FALSE;

				$array = [
					$instance['group'],
					[], // atts
					$before.$title,
					$after,
					// 0, // count
				];

				if ( call_user_func_array( $callback, $array ) )
					return TRUE;

				break;

			case 'bootstrap_carousel':

				$callback = [ 'gThemeBanners', 'bootstrapCarousel' ];

				if ( ! is_callable( $callback ) )
					return FALSE;

				$array = [
					$instance['group'],
					[], // atts
					$before.$title,
					$after,
					str_replace( [ '-', '_' ], '', $args['widget_id'] ), // avoiding weird behaviours on anchors
				];

				if ( call_user_func_array( $callback, $array ) )
					return TRUE;

				break;
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$this->flush_widget_cache();
		return $this->handle_update( $new, $old, [], [
			'group'    => 'text',
			'renderer' => 'text',
		] );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_dropdown( $instance, gThemeBanners::getGroups(), 0, 'group', _x( 'Group:', 'Widget: BannerGroup', 'gtheme' ) );
		$this->form_dropdown( $instance, gThemeBanners::getRenderers(), gThemeBanners::defaultRenderer(), 'renderer', _x( 'Renderer:', 'Widget: BannerGroup', 'gtheme' ) );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_title_image( $instance );
		$this->form_class( $instance );

		$this->form_open_widget( $instance );
		$this->form_after_title( $instance );
		$this->form_close_widget( $instance );

		$this->after_form( $instance );
	}
}
