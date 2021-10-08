<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeBanners extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'admin' => FALSE,
		], $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', [ $this, 'subs' ], 5 );
			add_action( 'gtheme_settings_load', [ $this, 'load' ] );
		}
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'dashboard' => _x( 'Dashboard', 'Banner Groups', 'gtheme' ),
			'sponsors'  => _x( 'Sponsors', 'Banner Groups', 'gtheme' ),
		], $extra );
	}

	public static function getGroups( $defaults = NULL )
	{
		if ( is_null( $defaults ) )
			$defaults = self::defaults();

		return gThemeOptions::info( 'banner_groups', $defaults );
	}

	// used on the widget
	public static function getRenderers( $defaults = NULL )
	{
		if ( is_null( $defaults ) )
			$defaults = [
				'slick_carousel'     => _x( 'Slick Carousel', 'Modules: Banners: Renderer', 'gtheme' ),
				'bootstrap_carousel' => _x( 'Bootstrap Carousel', 'Modules: Banners: Renderer', 'gtheme' ),
			];

		return gThemeOptions::info( 'banner_renderers', $defaults );
	}

	public static function defaultRenderer()
	{
		return gThemeOptions::info( 'banner_default_renderer', 'bootstrap_carousel' );
	}

	public static function banner( $group, $order = 0, $atts = [] )
	{
		$banner = self::get( $group, $order );

		if ( FALSE === $banner )
			return;

		self::html( $banner, $atts );
	}

	// NOTE: resets the returned array
	public static function getGroup( $group )
	{
		return array_values( wp_list_filter( gThemeOptions::getOption( 'banners', [] ), [ 'group' => $group ] ) );
	}

	public static function group( $group, $atts = [] )
	{
		if ( ! $banners = self::getGroup( $group ) )
			return FALSE;

		$args = self::atts( [
			'before'    => '',
			'after'     => '',
			'tag'       => 'li',
			'tag_class' => '',
			'tag_start' => '',
			'tag_end'   => '',
		], $atts );

		echo $args['before'];

		foreach ( $banners as $banner ) {

			if ( $args['tag'] )
				echo '<'.$args['tag'].( $args['tag_class'] ? ' class="'.$args['tag_class'].'"': '' ).'>';

			echo $args['tag_start'];

			self::html( $banner, $atts );

			echo $args['tag_end'];

			if ( $args['tag'] )
				echo '</'.$args['tag'].'>';
		}

		echo $args['after'];
	}

	// ANCESTOR: gtheme_get_banner()
	public static function get( $group, $order = 0 )
	{
		if ( ! $banners = self::getGroup( $group ) )
			return FALSE;

		$ordered = gThemeArraay::reKey( $banners, 'order' );

		return array_key_exists( $order, $ordered )
			? $ordered[$order]
			: FALSE;
	}

	// ANCESTOR: gtheme_banner()
	public static function html( $banner, $atts = [] )
	{
		$args = self::atts( [
			'w'           => 'auto',
			'h'           => 'auto',
			'c'           => '#fff',
			'img_class'   => gThemeImage::cssClass(),
			'img_style'   => FALSE,
			'img_loading' => 'lazy', // FALSE to disable
			'a_class'     => 'gtheme-banner',
			'a_style'     => FALSE,
			'placeholder' => TRUE,
			'echo'        => TRUE,
		], $atts );

		$html  = '';
		$title = empty( $banner['title'] ) ? '' : $banner['title'];

		if ( ! empty( $banner['image'] ) && 'http://' != $banner['image'] )
			$html.= gThemeHTML::tag( 'img', [
				'src'     => $banner['image'],
				'class'   => $args['img_class'],
				'style'   => $args['img_style'],
				'loading' => $args['img_loading'],
				'alt'     => $title,
			] );

		else if ( $args['placeholder'] )
			$html.= '<div style="display:block;width:'.$args['w'].';height:'.$args['h'].';background-color:'.$args['c'].';" ></div>';

		if ( ! empty( $banner['url'] ) && 'http://' != $banner['url'] )
			$html = gThemeHTML::tag( 'a', [
				'href'  => $banner['url'],
				'class' => $args['a_class'],
				'style' => $args['a_style'],
				'title' => $title,
			], $html );

		if ( ! $args['echo'] )
			return $html;

		echo $html;
	}

	// @REF: https://github.com/jsor/jcarousel
	// NOTE: needs additional styles, see blocks for `jcarousel`
	public static function paginatedCarousel( $group, $atts = [], $before = '', $after = '' )
	{
		$args = array_merge( [
			'before' => $before.'<div class="wrap-jcarousel-paginated -group-'.$group.'"><div class="-carousel"><ul>',
			'after'  => '</ul></div><div class="-pagination"></div></div>'.$after,
		], $atts );

		if ( FALSE === self::group( $group, $args ) )
			return FALSE;

		wp_register_script( 'jquery-jcarousel', GTHEME_URL.'/js/vendor/jquery.jcarousel.min.js', [ 'jquery' ], '0.3.9', TRUE );
		wp_enqueue_script( 'jcarousel-paginated', GTHEME_URL.'/js/jcarousel.paginated'.( SCRIPT_DEBUG ? '' : '.min' ).'.js', [ 'jquery', 'jquery-jcarousel' ], GTHEME_VERSION, TRUE );

		return TRUE;
	}

	// NOTE: needs no additional styles or scripts
	// @SUPPORT BS4/BS5
	public static function bootstrapCarousel( $group, $atts = [], $before = '', $after = '', $custom_id = NULL )
	{
		if ( ! $banners = self::getGroup( $group ) )
			return FALSE;

		$html = $indi = '';
		$id   = is_null( $custom_id ) ? ( $group.'CarouselBanners' ) : $custom_id;

		$controls = '<a class="carousel-control-prev" href="#'.$id.'" role="button" data-slide="prev" data-bs-slide="prev">';
		$controls.= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
		$controls.= '<span class="sr-only visually-hidden">'._x( 'Previous', 'Carousel Control', 'gtheme' ).'</span></a>';
		$controls.= '<a class="carousel-control-next" href="#'.$id.'" role="button" data-slide="next" data-bs-slide="next">';
		$controls.= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
		$controls.= '<span class="sr-only visually-hidden">'._x( 'Next', 'Carousel Control', 'gtheme' ).'</span></a>';

		$args = array_merge( [
			'a_class'   => 'd-block w-100',
			'img_class' => 'd-block w-100',
			'echo'      => FALSE,
		], $atts );

		foreach ( $banners as $offset => $banner ) {

			if ( ! $item = self::html( $banner, $args ) )
				continue;

			$html.= '<div class="carousel-item'.( $offset ? '' : ' active' ).'">'.$item.'</div>';
			$indi.= '<li data-target="#'.$id.'" data-slide-to="'.$offset.'" data-bs-target="#'.$id.'" data-bs-slide-to="'.$offset.'"'.( $offset ? '' : ' class="active"' ).'></li>';
		}

		echo $before.'<div class="wrap-bootstrap-carousel -group-'.$group.'">';
		echo '<div id="'.$id.'" class="-carousel carousel slide w-100" data-ride="carousel" data-bs-ride="carousel">';
		echo '<ol class="carousel-indicators">'.$indi.'</ol>';
		echo '<div class="carousel-inner">'.$html.'</div>';
		echo $controls.'</div></div>'.$after;

		return TRUE;
	}

	// @REF: https://github.com/kenwheeler/slick/
	// @REF: https://kenwheeler.github.io/slick/
	// NOTE: needs additional styles, see blocks for `slick-slider`
	public static function slickCarousel( $group, $atts = [], $before = '', $after = '', $count = NULL )
	{
		static $enqueued = FALSE;

		$config = [
			'slidesToShow'   => is_null( $count ) ? gThemeCounts::get( 'carousel_'.$group, 5 ) : $count,
			'slidesToScroll' => 1,
			'autoplaySpeed'  => 3000,
			'autoplay'       => TRUE,
			'arrows'         => FALSE,
			'dots'           => FALSE,
			// 'pauseOnHover'   => FALSE,
			'responsive'     => [
				[
					'breakpoint' => 768,
					'settings'   => [ 'slidesToShow' => 3 ],
				],
				[
					'breakpoint' => 576, // 520,
					'settings'   => [ 'slidesToShow'=> 2 ],
				],
			],
		];

		$args = array_merge( [
			'before' => $before.'<div class="wrap-slick-carousel -group-'.$group.'"><div class="-carousel slick-slider" data-slick=\''.wp_json_encode( $config ).'\'>',
			'after'  => '</div></div>'.$after,
			'tag'    => 'div',
		], $atts );

		if ( FALSE === self::group( $group, $args ) )
			return FALSE;

		if ( $enqueued )
			return TRUE;

		$script = <<<'JS'
jQuery(function(r){r(".wrap-slick-carousel .-carousel").slick({rtl:"rtl"===r("html").attr("dir")})});
JS;

		wp_enqueue_script( 'gtheme-slick', GTHEME_URL.'/js/vendor/slick-carousel.min.js', [], '1.8.1', TRUE );
		wp_add_inline_script( 'gtheme-slick', $script );

		// NOTE: for reference
		// wp_enqueue_script( 'slick-carousel', GTHEME_URL.'/js/slick.carousel'.( SCRIPT_DEBUG ? '' : '.min' ).'.js', [ 'jquery', 'gtheme-slick' ], GTHEME_VERSION, TRUE );

		$enqueued = TRUE;

		return TRUE;
	}

	public function subs( $subs )
	{
		return array_merge( $subs, [ 'banners' => _x( 'Banners', 'Modules: Menu Name', 'gtheme' ) ] );
	}

	public function load( $sub )
	{
		if ( 'banners' == $sub ) {

			if ( ! empty( $_POST ) && wp_verify_nonce( $_POST['_gtheme_banners'], 'gtheme-banners' ) ) {

				$banners = self::getGroups();
				$old     = gThemeOptions::getOption( 'banners', [] );
				$new     = [];

				$titles = $_POST['gtheme-banners-title'];
				$groups = $_POST['gtheme-banners-group'];
				$urls   = $_POST['gtheme-banners-url'];
				$images = $_POST['gtheme-banners-image'];
				$orders = $_POST['gtheme-banners-order'];

				$count = count( $images );

				for ( $i = 0; $i < $count; $i++ ) {
					//if ( $images[$i] != '' && $images[$i] != 'http://' ) {
					if ( $orders[$i] != '' ) {

						if ( strlen( $titles[$i] ) > 0 )
							$new[$i]['title'] = trim( stripslashes( strip_tags( $titles[$i] ) ) );
						else
							$new[$i]['title'] = '';

						if ( array_key_exists( $groups[$i], $banners ) )
							$new[$i]['group'] = $groups[$i];
						else
							$new[$i]['group'] = '';

						if ( $urls[$i] == 'http://' )
							$new[$i]['url'] = '';
						else
							$new[$i]['url'] = esc_url( $urls[$i] );

						if ( $images[$i] == 'http://' )
							$new[$i]['image'] = '';
						else
							$new[$i]['image'] = esc_url( $images[$i] );

						if ( strlen( $orders[$i] ) > 0 )
							$new[$i]['order'] = (int) $orders[$i];
						else
							$new[$i]['order'] = $i;
					}
				}

				// http://stackoverflow.com/a/4582659
				if ( count( $new ) ) {
					foreach ( $new as $key => $row ) {
						$group_row[$key] = $row['group'];
						$order_row[$key] = $row['order'];
					}
					array_multisort( $group_row, SORT_ASC, $order_row, SORT_ASC, $new );
				}

				if ( ! empty( $new ) && $new != $old )
					$result = gThemeOptions::update_option( 'banners', $new );

				else if ( empty( $new ) && $old )
					$result = gThemeOptions::delete_option( 'banners' );

				else
					$result = FALSE;

				gThemeWordPress::redirectReferer( $result ? 'updated' : 'error' );
			}

			add_action( 'gtheme_settings_sub_banners', [ $this, 'settings_sub_html' ], 10, 2 );
		}
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$legend  = gThemeOptions::info( 'banners_legend' );
		$banners = gThemeOptions::getOption( 'banners', [] );
		$groups  = self::getGroups();

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'Custom Banners', 'Modules: Banners', 'gtheme' ).'</h3>';

			if ( $legend ) {
				echo '<table class="form-table"><tbody><tr valign="top">';
				echo '<th scope="row"><label>'._x( 'Legend', 'Modules: Banners', 'gtheme' );
				echo '</label></th><td>'.$legend;
					gThemeHTML::desc( _x( 'Your theme extra information', 'Modules: Banners', 'gtheme' ) );
				echo '</td></tr></tbody></table>';
			}

			echo '<table id="repeatable-fieldset-one" width="100%"><thead><tr>';
				echo '<th width="10%">'._x( 'Group', 'Modules: Banners', 'gtheme' ).'</th>';
				echo '<th width="5%">'._x( 'Ord.', 'Modules: Banners', 'gtheme' ).'</th>';
				echo '<th>'._x( 'Title', 'Modules: Banners', 'gtheme' ).'</th>';
				echo '<th width="20%">'._x( 'URL', 'Modules: Banners', 'gtheme' ).'</th>';
				echo '<th width="20%">'._x( 'Image', 'Modules: Banners', 'gtheme' ).'</th>';
				echo '<th style="width:30px;"></th>';
			echo '</tr></thead><tbody>';

			foreach ( $banners as $banner ) {

				echo '<tr>';

				echo '<td>'.gThemeHTML::dropdown( $groups, [
						'name'     => 'gtheme-banners-group[]',
						'class'    => 'widefat',
						'selected' => $banner['group'],
					] ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', [
					'name'  => 'gtheme-banners-order[]',
					'type'  => 'number',
					'class' => 'widefat',
					'value' => empty( $banner['order'] ) ? '' : $banner['order'],
				] ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', [
					'name'  => 'gtheme-banners-title[]',
					'type'  => 'text',
					'class' => 'widefat',
					'value' => empty( $banner['title'] ) ? '' : $banner['title'],
				] ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', [
					'name'  => 'gtheme-banners-url[]',
					'type'  => 'url',
					'class' => 'widefat',
					'value' => empty( $banner['url'] ) ? '' : $banner['url'],
					'dir'   => 'ltr',
				] ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', [
					'name'  => 'gtheme-banners-image[]',
					'type'  => 'url',
					'class' => 'widefat',
					'value' => empty( $banner['image'] ) ? '' : $banner['image'],
					'dir'   => 'ltr',
				] ).'</td>';

				echo '<td><a class="button remove-row" href="#" style="padding:2px 2px 0 2px"><span class="dashicons dashicons-trash"></span></a></td></tr>';
			}

			echo '<tr class="empty-row screen-reader-text">';

			echo '<td>'.gThemeHTML::dropdown( $groups, [
					'name'     => 'gtheme-banners-group[]',
					'class'    => 'widefat',
					'selected' => 'none',
				] ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', [
				'name'  => 'gtheme-banners-order[]',
				'type'  => 'number',
				'class' => 'widefat',
				'value' => '',
			] ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', [
				'name'  => 'gtheme-banners-title[]',
				'type'  => 'text',
				'class' => 'widefat',
				'value' => '',
			] ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', [
				'name'  => 'gtheme-banners-url[]',
				'type'  => 'url',
				'class' => 'widefat',
				'value' => '',
				'dir'   => 'ltr',
			] ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', [
				'name'  => 'gtheme-banners-image[]',
				'type'  => 'url',
				'class' => 'widefat',
				'value' => '',
				'dir'   => 'ltr',
			] ).'</td>';

			echo '<td><a class="button remove-row" href="#" style="padding:2px 2px 0 2px"><span class="dashicons dashicons-trash"></span></a></td></tr>';

		echo '</tbody></table>';

		echo '<p class="submit"><a id="add-row" class="button" href="#">'._x( 'Add Another', 'Modules: Banners', 'gtheme' ).'</a>&nbsp;&nbsp;';
		echo '<input type="submit" class="button-primary" name="submitform" value="&nbsp;&nbsp;'._x( 'Save', 'Modules: Banners', 'gtheme' ).'&nbsp;&nbsp;" /></p>';

		wp_nonce_field( 'gtheme-banners', '_gtheme_banners' );

	echo '</form>';

	?><script type="text/javascript">
jQuery(function ($) {
	$('#add-row').on('click', function(){
		var row = $('.empty-row.screen-reader-text').clone(true);
		row.removeClass( 'empty-row screen-reader-text' );
		row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
		return false;
	});

	$('.remove-row').on('click', function(){
		$(this).parents('tr').remove();
		return false;
	});
});
</script><?php
	}
}
