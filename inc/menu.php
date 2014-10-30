<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeMenu extends gThemeModuleCore {

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'register_nav' => true,
			'allowedtags' => false,
		), $args ) );

		if ( $register_nav )
			add_action( 'init', array( $this, 'init' ) );
		
		if ( $allowedtags )
			add_filter( 'wp_nav_menu_container_allowedtags', array( $this, 'wp_nav_menu_container_allowedtags' ) );
	}


	public function init() 
	{
		$menus = gtheme_get_info( 'register_nav_menus', array() );
		if ( count( $menus ) )
			register_nav_menus( $menus );
	}
	
	public static function nav( $args = array() ) 
	{
		return wp_nav_menu( self::args( $args ) );
	}
	
	public static function args( $atts = array() )
	{
		$args = array( 
			'fallback_cb' => '__return_null',
			'echo' => isset( $atts['echo'] ) ? $atts['echo'] : true,
			'depth' => isset( $atts['depth'] ) ? $atts['depth'] : 1,
			'container' => isset( $atts['container'] ) ? $atts['container'] : 'nav',
			'theme_location' => isset( $atts['location'] ) ? $atts['location'] : 'primary',
		);
		
		$args['menu'] = $args['theme_location'];
		$args['menu_class'] = $args['theme_location'].' '.( isset( $atts['class'] ) ? $atts['class'] : 'clearfix' );
		
		//$args = array( // 'items_wrap' => '%3$s', // Remove LI Elements From Output of wp_nav_menu // http://css-tricks.com/snippets/wordpress/remove-li-elements-from-output-of-wp_nav_menu/
		
		if ( isset( $atts['walker'] ) )
			$args['walker'] = new $atts['walker'];
	
		return $args;
	}	
	

	public function wp_nav_menu_container_allowedtags( $tags ) 
	{
		$new_tags = (array) gtheme_get_info( 'nav_menu_allowedtags', array( 'p' ) );
		if ( count( $new_tags ) )
			$tags = array_merge( $tags, $new_tags );
		return $tags;
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
			
			foreach( $sorted_menu_items as $menu ){
				if ( true == $menu->current )
					$current = $menu->ID;
				
				if ( true == $menu->current_item_ancestor )
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
		$output = $class_names = $value = '';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, array() ) );
		$class_names = ' class="'.esc_attr( $class_names ).'"';
		$output .= '<li id="menu-item-'.$item->ID.'"'.$value.$class_names.'>';
		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		return $output.'<a'. $attributes .'>'.$item->title.'</a></li>'."\n";
	}
	
}