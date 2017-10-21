<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeBanners extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'admin' => FALSE,
		), $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', array( $this, 'subs' ), 5 );
			add_action( 'gtheme_settings_load', array( $this, 'load' ) );
		}
	}

	public static function defaults( $extra = array() )
	{
		return array_merge( array(
			'first'  => _x( 'First', 'Banner Groups', GTHEME_TEXTDOMAIN ),
			'second' => _x( 'Second', 'Banner Groups', GTHEME_TEXTDOMAIN ),
		), $extra );
	}

	public static function banner( $group, $order = 0, $atts = array() )
	{
		$banner = self::get( $group, $order );

		if ( FALSE === $banner )
			return;

		self::html( $banner, $atts );
	}

	public static function group( $group, $atts = array() )
	{
		$banners = gThemeOptions::getOption( 'banners', array() );
		$saved   = array();

		foreach ( $banners as $banner )
			if ( isset( $banner['group'] )
				&& $group == $banner['group'] )
					$saved[] = $banner;

		if ( count( $saved ) ) {

			$args = self::atts( array(
				'before'    => '',
				'after'     => '',
				'tag'       => 'li',
				'tag_start' => '',
				'tag_end'   => '',
			), $atts );

			echo $args['before'];

			foreach ( $saved as $banner ) {

				if ( $args['tag'] )
					echo '<'.$args['tag'].'>';

				echo $args['tag_start'];

				self::html( $banner, $atts );

				echo $args['tag_end'];

				if ( $args['tag'] )
					echo '</'.$args['tag'].'>';
			}

			echo $args['after'];
		}
	}

	// ANCESTOR: gtheme_get_banner()
	public static function get( $group, $order = 0 )
	{
		$banners = gThemeOptions::getOption( 'banners', array() );

		foreach ( $banners as $banner ) {
			if ( isset( $banner['group'] ) && $group == $banner['group'] ) {
				if ( isset( $banner['order'] ) && $order == $banner['order'] ) {
					return $banner;
				}
			}
		}

		return FALSE;
	}

	// ANCESTOR: gtheme_banner()
	public static function html( $banner, $atts = array() )
	{
		$args = self::atts( array(
			'w'           => 'auto',
			'h'           => 'auto',
			'c'           => '#fff',
			'img_class'   => 'img-responsive',
			'a_class'     => 'gtheme-banner',
			'img_style'   => '',
			'a_style'     => '',
			'placeholder' => TRUE,
		), $atts );

		$html  = '';
		$title = empty( $banner['title'] ) ? '' : $banner['title'];

		if ( ! empty( $banner['image'] ) && 'http://' != $banner['image'] )
			$html.= '<img src="'.$banner['image'].'" alt="'.$title.'" class="'.$args['img_class'].'" style="'.$args['img_style'].'" />';

		else if ( $args['placeholder'] )
			$html.= '<div style="display:block;width:'.$args['w'].';height:'.$args['h'].';background-color:'.$args['c'].';" ></div>';

		if ( ! empty( $banner['url'] ) && 'http://' != $banner['url'] )
			$html = '<a href="'.$banner['url'].'" title="'.$title.'" class="'.$args['a_class'].'" style="'.$args['a_style'].'">'.$html.'</a>';

		if ( ! empty( $html ) )
			echo $html;
	}

	public function subs( $subs )
	{
		return array_merge( $subs, array( 'banners' => _x( 'Banners', 'Modules: Menu Name', GTHEME_TEXTDOMAIN ) ) );
	}

	public function load( $sub )
	{
		if ( 'banners' == $sub ) {

			if ( ! empty( $_POST ) && wp_verify_nonce( $_POST['_gtheme_banners'], 'gtheme-banners' ) ) {

				$banners = gThemeOptions::info( 'banner_groups', self::defaults() );
				$old     = gThemeOptions::getOption( 'banners', array() );
				$new     = array();

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
							$new[$i]['order'] = intval( $orders[$i] );
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

				wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
				exit();
			}

			add_action( 'gtheme_settings_sub_banners', array( $this, 'settings_sub_html' ), 10, 2 );
		}
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$legend  = gThemeOptions::info( 'banners_legend' );
		$groups  = gThemeOptions::info( 'banner_groups', gThemeBanners::defaults() );
		$banners = gThemeOptions::getOption( 'banners', array() );

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'Custom Banners', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</h3>';

			if ( $legend ) {
				echo '<table class="form-table"><tbody><tr valign="top">';
				echo '<th scope="row"><label>'._x( 'Legend', 'Modules: Banners', GTHEME_TEXTDOMAIN );
				echo '</label></th><td>'.$legend;
					gThemeHTML::desc( _x( 'Your theme extra information', 'Modules: Banners', GTHEME_TEXTDOMAIN ) );
				echo '</td></tr></tbody></table>';
			}

			echo '<table id="repeatable-fieldset-one" width="100%"><thead><tr>';
				echo '<th width="10%">'._x( 'Group', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</th>';
				echo '<th width="5%">'._x( 'Ord.', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</th>';
				echo '<th>'._x( 'Title', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</th>';
				echo '<th width="20%">'._x( 'URL', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</th>';
				echo '<th width="20%">'._x( 'Image', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</th>';
				echo '<th style="width:30px;"></th>';
			echo '</tr></thead><tbody>';

			foreach ( $banners as $banner ) {

				echo '<tr>';

				echo '<td>'.gThemeHTML::dropdown( $groups, array(
						'name'     => 'gtheme-banners-group[]',
						'class'    => 'widefat',
						'selected' => $banner['group'],
					) ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', array(
					'name'  => 'gtheme-banners-order[]',
					'type'  => 'number',
					'class' => 'widefat',
					'value' => empty( $banner['order'] ) ? '' : $banner['order'],
				) ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', array(
					'name'  => 'gtheme-banners-title[]',
					'type'  => 'text',
					'class' => 'widefat',
					'value' => empty( $banner['title'] ) ? '' : $banner['title'],
				) ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', array(
					'name'  => 'gtheme-banners-url[]',
					'type'  => 'url',
					'class' => 'widefat',
					'value' => empty( $banner['url'] ) ? '' : $banner['url'],
					'dir'   => 'ltr',
				) ).'</td>';

				echo '<td>'.gThemeHTML::tag( 'input', array(
					'name'  => 'gtheme-banners-image[]',
					'type'  => 'url',
					'class' => 'widefat',
					'value' => empty( $banner['image'] ) ? '' : $banner['image'],
					'dir'   => 'ltr',
				) ).'</td>';

				echo '<td><a class="button remove-row" href="#" style="padding:2px 2px 0 2px"><span class="dashicons dashicons-trash"></span></a></td></tr>';
			}

			echo '<tr class="empty-row screen-reader-text">';

			echo '<td>'.gThemeHTML::dropdown( $groups, array(
					'name'     => 'gtheme-banners-group[]',
					'class'    => 'widefat',
					'selected' => 'none',
				) ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', array(
				'name'  => 'gtheme-banners-order[]',
				'type'  => 'number',
				'class' => 'widefat',
				'value' => '',
			) ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', array(
				'name'  => 'gtheme-banners-title[]',
				'type'  => 'text',
				'class' => 'widefat',
				'value' => '',
			) ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', array(
				'name'  => 'gtheme-banners-url[]',
				'type'  => 'url',
				'class' => 'widefat',
				'value' => '',
				'dir'   => 'ltr',
			) ).'</td>';

			echo '<td>'.gThemeHTML::tag( 'input', array(
				'name'  => 'gtheme-banners-image[]',
				'type'  => 'url',
				'class' => 'widefat',
				'value' => '',
				'dir'   => 'ltr',
			) ).'</td>';

			echo '<td><a class="button remove-row" href="#" style="padding:2px 2px 0 2px"><span class="dashicons dashicons-trash"></span></a></td></tr>';

		echo '</tbody></table>';

		echo '<p class="submit"><a id="add-row" class="button" href="#">'._x( 'Add Another', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'</a>&nbsp;&nbsp;';
		echo '<input type="submit" class="button-primary" name="submitform" value="&nbsp;&nbsp;'._x( 'Save', 'Modules: Banners', GTHEME_TEXTDOMAIN ).'&nbsp;&nbsp;" /></p>';

		wp_nonce_field( 'gtheme-banners', '_gtheme_banners' );

	echo '</form>';

	?><script type="text/javascript">
jQuery(document).ready(function($){
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
