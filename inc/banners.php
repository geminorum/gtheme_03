<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

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
		$saved = array();

		foreach ( $banners as $banner ) {
			if ( isset( $banner['group'] ) && $group == $banner['group'] ) {
				$saved[] = $banner;
			}
		}

		if ( count( $saved ) ) {

			$args = self::atts( array(
				'before' => '',
				'after'  => '',
				'tag'    => 'li',
			), $atts );

			echo $args['before'];

			foreach ( $saved as $banner ) {
				if ( $args['tag'] )
					echo '<'.$args['tag'].'>';
				self::html( $banner, $atts );
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

		$html = '';
		$title = isset( $banner['title'] ) && $banner['title'] ? $banner['title'] : '' ;

		if ( isset( $banner['image'] ) && $banner['image'] && 'http://' != $banner['image'] )
			$html .= '<img src="'.$banner['image'].'" alt="'.$title.'" class="'.$args['img_class'].'" style="'.$args['img_style'].'" />';
		else if ( $args['placeholder'] )
			$html .= '<div style="display:block;width:'.$args['w'].';height:'.$args['h'].';background-color:'.$args['c'].';" ></div>';

		if ( isset( $banner['url'] ) && $banner['url'] && 'http://' != $banner['url'] )
			$html = '<a href="'.$banner['url'].'" title="'.$title.'" class="'.$args['a_class'].'" style="'.$args['a_style'].'">'.$html.'</a>';

		if ( ! empty ( $html ) )
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
		}
	}
}
