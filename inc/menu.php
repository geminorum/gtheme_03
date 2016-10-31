<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeMenu extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'register_nav' => TRUE,
			'allowedtags'  => FALSE,
			'css_classes'  => FALSE,
		), $args ) );

		if ( $register_nav )
			add_action( 'init', array( $this, 'init' ) );

		if ( $allowedtags )
			add_filter( 'wp_nav_menu_container_allowedtags', array( $this, 'wp_nav_menu_container_allowedtags' ) );

		if ( $css_classes )
			add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ), 10, 4 );

		if ( ! is_admin() ) {
			// add_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ) );
		}
	}

	public function init()
	{
		$menus = gThemeOptions::info( 'register_nav_menus', array() );
		if ( $menus && count( $menus ) )
			register_nav_menus( $menus );
	}

	public function wp_nav_menu_args( $args )
	{
		if ( 'menu' == $args['menu_class'] )
			$args['menu_class'] = 'list-unstyled menu';

		return $args;
	}

	public static function nav( $location = 'primary', $args = array(), $b = '', $a = '' )
	{
		$args['location'] = $location;
		$args['echo']     = FALSE;
		$menu = wp_nav_menu( self::args( $args ) );
		if ( $menu )
			echo $b.$menu.$a;
	}

	public static function args( $atts = array() )
	{
		$args = array(
			'fallback_cb'    => '__return_null',
			'echo'           => isset( $atts['echo'] ) ? $atts['echo'] : TRUE,
			'depth'          => isset( $atts['depth'] ) ? $atts['depth'] : 1,
			'container'      => isset( $atts['container'] ) ? $atts['container'] : 'nav',
			'theme_location' => isset( $atts['location'] ) ? $atts['location'] : 'primary',
			'items_wrap'     => isset( $atts['items_wrap'] ) ? $atts['items_wrap'] : '<ul id="%1$s" class="%2$s">%3$s</ul>',
		);

		$args['menu']       = $args['theme_location'];
		$args['menu_class'] = $args['theme_location'].' '.( isset( $atts['class'] ) ? $atts['class'] : 'clearfix' );

		if ( isset( $atts['walker'] ) )
			$args['walker'] = new $atts['walker'];

		return $args;
	}

	public function wp_nav_menu_container_allowedtags( $tags )
	{
		$new_tags = (array) gThemeOptions::info( 'nav_menu_allowedtags', array( 'p' ) );
		if ( count( $new_tags ) )
			$tags = array_merge( $tags, $new_tags );
		return $tags;
	}

	public function nav_menu_css_class( $classes, $item, $args, $depth = 0 )
	{
		if ( ! isset( $args->menu_class ) || empty( $args->menu_class ) )
			return $classes;

		// http://getbootstrap.com/components/#list-group
		if ( FALSE !== strpos( $args->menu_class, 'list-group' ) )
			$classes[] = 'list-group-item';

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

			$sorted_menu_items = array();
			foreach ( (array) $menu_items as $key => $menu_item )
				$sorted_menu_items[$menu_item->menu_order] = $menu_item;

			$primary = $secondary = '';
			$parent = $current = '-1';

			foreach ( $sorted_menu_items as $menu ) {
				if ( TRUE == $menu->current )
					$current = $menu->ID;

				if ( TRUE == $menu->current_item_ancestor )
					$parent = $menu->ID;

				if ( '0' == $menu->menu_item_parent )
					$primary .= self::menu_el( $menu );

				if ( $current == $menu->menu_item_parent )
					$secondary .= self::menu_el( $menu );

				else if ( $parent == $menu->menu_item_parent )
					$secondary .= self::menu_el( $menu );

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
		$html = gThemeUtilities::html( 'a', array(
			'title'  => ( empty( $item->attr_title ) ? FALSE : $item->attr_title ),
			'target' => ( empty( $item->target ) ? FALSE : $item->target ),
			'rel'    => ( empty( $item->xfn ) ? FALSE : $item->xfn ),
			'href'   => ( empty( $item->url ) ? FALSE : $item->url ),
		), $item->title );

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		return gThemeUtilities::html( 'li', array(
			'id'    => apply_filters( 'nav_menu_item_id', 'menu-item-'.$item->ID, $item, array(), 0 ),
			'class' => apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, array(), 0 ),
		), $html );
	}
}
