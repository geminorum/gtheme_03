<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeMenu extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'register_nav' => TRUE,
			'allowedtags'  => FALSE,
		], $args ) );

		if ( $register_nav )
			add_action( 'init', [ $this, 'init' ] );

		if ( $allowedtags )
			add_filter( 'wp_nav_menu_container_allowedtags', [ $this, 'wp_nav_menu_container_allowedtags' ] );

		if ( ! is_admin() ) {
			add_filter( 'nav_menu_css_class', [ $this, 'nav_menu_css_class' ], 10, 4 );
			add_filter( 'nav_menu_link_attributes', [ $this, 'nav_menu_link_attributes' ], 10, 4 );
			add_filter( 'nav_menu_item_id', '__return_empty_string', 12 );
		}
	}

	public function init()
	{
		$menus = gThemeOptions::info( 'register_nav_menus', [] );

		if ( ! empty( $menus ) )
			register_nav_menus( $menus );
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'primary'   => _x( 'Primary Navigation', 'Modules: Menu: Defaults', 'gtheme' ),
			'secondary' => _x( 'Secondary Navigation', 'Modules: Menu: Defaults', 'gtheme' ),
			'tertiary'  => _x( 'Tertiary Navigation', 'Modules: Menu: Defaults', 'gtheme' ),
		], $extra );
	}

	public static function navNetwork( $before = '', $after = '' )
	{
		if ( function_exists( 'gnetwork_navigation' ) )
			gnetwork_navigation( $before, $after );
	}

	public static function nav( $location = 'primary', $atts = [], $before = '', $after = '' )
	{
		$args = array_merge( [
			'location' => $location,
			'after'    => '<span class="-dummy"></span>',
		], $atts );

		$key = GTHEME_FRAGMENTCACHE.'_'.md5( maybe_serialize( $args ) );

		if ( gThemeWordPress::isFlush() )
			delete_transient( $key );

		if ( FALSE === ( $menu = get_transient( $key ) ) ) {

			$args['echo'] = FALSE;

			if ( ! $menu = wp_nav_menu( self::parseArgs( $args ) ) )
				return '';

			set_transient( $key, $menu, GTHEME_CACHETTL );
		}

		if ( isset( $atts['echo'] ) && ! $atts['echo'] )
			return $before.$menu.$after;

		echo $before.$menu.$after;
	}

	public static function parseArgs( $atts = [] )
	{
		$args = [
			'fallback_cb'    => '__return_null',
			'echo'           => isset( $atts['echo'] )       ? $atts['echo']       : TRUE,
			'depth'          => isset( $atts['depth'] )      ? $atts['depth']      : 1,
			'container'      => isset( $atts['container'] )  ? $atts['container']  : '', // 'nav',
			'theme_location' => isset( $atts['location'] )   ? $atts['location']   : 'primary',
			'items_wrap'     => isset( $atts['items_wrap'] ) ? $atts['items_wrap'] : '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'item_spacing'   => 'discard',

			/// Extra Args:
			'theme_bs_version' => gThemeBootstrap::version(),
		];

		foreach ( [ 'before', 'after', 'link_before', 'link_after' ] as $key )
			if ( array_key_exists( $key, $atts ) )
				$args[$key] = $atts[$key];

		$args['menu']       = $args['theme_location'];
		$args['menu_class'] = 'menu-'.$args['theme_location'].' '.( isset( $atts['class'] ) ? $atts['class'] : 'clearfix' );

		if ( isset( $atts['walker'] ) )
			$args['walker'] = new $atts['walker'];

		return $args;
	}

	public function wp_nav_menu_container_allowedtags( $tags )
	{
		$new_tags = (array) gThemeOptions::info( 'nav_menu_allowedtags', [ 'p' ] );

		if ( count( $new_tags ) )
			$tags = array_merge( $tags, $new_tags );

		return $tags;
	}

	public function nav_menu_link_attributes( $atts, $menu_item, $args, $depth = 0 )
	{
		// if ( FALSE !== strpos( $args->menu_class, 'nav' ) )
		// 	$atts['class'] = 'nav-link';

		if ( FALSE !== strpos( $args->menu_class, 'dropdown-menu' ) )
			$atts['class'] = 'dropdown-item';

		return $atts;
	}

	public function nav_menu_css_class( $classes, $item, $args, $depth = 0 )
	{
		// we cache menus, so no active item!
		$classes = array_diff( $classes, [
			'menu-item',
			'menu-item-'.$item->ID,
			'menu-item-type-'.$item->type,
			'menu-item-object-'.$item->object,
			'page_item',
			'page-item-'.$item->object_id,
			'current-menu-item',
			'current_page_item',
			'active',
		] );

		if ( ! isset( $args->menu_class ) || empty( $args->menu_class ) )
			return $classes;

		// http://getbootstrap.com/components/#list-group
		if ( FALSE !== strpos( $args->menu_class, 'list-group' ) )
			$classes[] = 'list-group-item';

		// if ( FALSE !== strpos( $args->menu_class, 'nav' ) )
		// 	$classes[] = 'nav-item';

		return $classes;
	}

	public static function separated( $location, $sep = '' )
	{
		$cache = new gThemeFragmentCache( 'gtheme_separated_'.$location );

		if ( ! $cache->output() ) {

			$menu = wp_get_nav_menu_object( $location );

			if ( $menu && ! is_wp_error( $menu ) )
				$menu_items = wp_get_nav_menu_items( $menu->term_id );

			_wp_menu_item_classes_by_context( $menu_items );

			$sorted_menu_items = [];

			foreach ( (array) $menu_items as $key => $menu_item )
				$sorted_menu_items[$menu_item->menu_order] = $menu_item;

			$primary = $secondary = '';
			$parent  = $current = '-1';

			foreach ( $sorted_menu_items as $menu ) {

				if ( TRUE == $menu->current )
					$current = $menu->ID;

				if ( TRUE == $menu->current_item_ancestor )
					$parent = $menu->ID;

				if ( '0' == $menu->menu_item_parent )
					$primary.= self::menu_el( $menu );

				if ( $current == $menu->menu_item_parent )
					$secondary.= self::menu_el( $menu );

				else if ( $parent == $menu->menu_item_parent )
					$secondary.= self::menu_el( $menu );
			}

			if ( $primary ) {

				echo '<ul class="list-unstyled separated-menu separated-menu-parents">'.$primary.'</ul>';

				if ( $secondary )
					echo $sep.'<ul class="list-unstyled separated-menu separated-menu-children">'.$secondary.'</ul>';
			}

			$cache->store();
		}
	}

	public static function menu_el( $item )
	{
		$html = gThemeHTML::tag( 'a', [
			'title'  => empty( $item->attr_title ) ? FALSE : $item->attr_title,
			'target' => empty( $item->target ) ? FALSE : $item->target,
			'rel'    => empty( $item->xfn ) ? FALSE : $item->xfn,
			'href'   => empty( $item->url ) ? FALSE : $item->url,
		], $item->title );

		$classes = empty( $item->classes ) ? [] : (array) $item->classes;

		return gThemeHTML::tag( 'li', [
			'id'    => apply_filters( 'nav_menu_item_id', 'menu-item-'.$item->ID, $item, [], 0 ),
			'class' => apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, [], 0 ),
		], $html );
	}
}
