<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

#[\AllowDynamicProperties]
class gThemeBootstrap_Walker_NavBar extends \Walker_Nav_Menu
{

	// ORIGINALLY BASED ON: `wp_bootstrap_navwalker` class v2.0.4 by Edward McIntyre
	// @link https://github.com/twittem/wp-bootstrap-navwalker
	// @link https://github.com/dupkey/bs4navwalker
	// @SEE: http://www.creativewebdesign.ro/en/blog/wordpress/create-a-responsive-wordpress-theme-with-bootstrap-3-header-and-footer/

	public function start_lvl( &$output, $depth = 0, $args = [] )
	{
		$indent = str_repeat( "\t", $depth );
		$output.= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
	}

	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 )
	{
		$args    = (object) $args;
		$version = empty( $args->theme_bs_version ) ? FALSE : (int) $args->theme_bs_version;
		$indent  = $depth ? str_repeat( "\t", $depth ) : '';

		// separator on drop-down
		if ( 0 == strcasecmp( $item->xfn, 'divider' ) ) {
			$output.= $indent.'<li role="separator" class="divider">';

		} else if ( 1 === $depth && 0 === strcasecmp( $item->xfn, 'header' ) ) {
			$output.= $indent.'<li role="presentation" class="dropdown-header">'.esc_attr( $item->title );

		} else if ( 0 === strcasecmp( $item->xfn, 'disabled' ) ) {
			$output.= $indent.'<li role="presentation" class="disabled"><a href="#">'.esc_attr( $item->title ).'</a>';

		} else if ( 0 === $depth && 0 === strcasecmp( $item->xfn, 'yamm' ) ) {

			// @REF: https://geedmo.github.io/yamm/
			// CAUTION: `#navbar` must have `.yamm`

			$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
			$classes[] = 'nav-item'; // BS4
			$classes[] = 'dropdown'; // mega-menu
			$classes[] = 'yamm-fw';  // mega-menu

			$output.= $indent.'<li class="'.gThemeHTML::prepClass( apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) ).'">';
				$output.= '<a href="'.( empty( $item->url ) ? '#' : $item->url ).'" class="nav-link dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" data-display="static" data-bs-auto-close="outside">'.esc_attr( $item->title ).'</a>';
					$output.= '<div class="dropdown-menu"><div class="yamm-content">';

					ob_start();
						get_template_part( 'partials/megamenu', esc_attr( $item->attr_title ) );
					$output.= ob_get_clean();

			$output.= '</div></div>';

		} else {

			$class = $value = $attributes = '';

			$classes = empty( $item->classes ) ? [] : (array) $item->classes;
			// $classes[] = 'menu-item-'.$item->ID;

			if ( in_array( $version, [ 4, 5 ] ) )
				$classes[] = 'nav-item';

			if ( $args->has_children )
				$classes[] = 'dropdown';

			if ( in_array( 'current-menu-item', $classes ) )
				$classes[] = 'active';

			$class = apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth );
			$class = empty( $class ) ? '' : ' class="'.gThemeHTML::prepClass( $class ).'"';

			// $id = apply_filters( 'nav_menu_item_id', 'menu-item-'.$item->ID, $item, $args, $depth );
			// $id = $id ? ' id="'.esc_attr( $id ).'"' : '';
			$id = '';

			$output.= $indent.'<li'.$id.$value.$class.'>';

			$atts = [
				'class'  => '',
				'title'  => empty( $item->attr_title ) ? ''  : $item->attr_title,
				'target' => empty( $item->target )     ? ''  : $item->target,
				'href'   => empty( $item->url )        ? '#' : $item->url,
				// 'rel'    => empty( $item->xfn )        ? ''  : $item->xfn,          // we use this for glyphicons
			];

			if ( in_array( $version, [ 4, 5 ] ) )
				$atts['class'] = $atts['class'].' '.( $depth === 0 ? 'nav-link' : 'dropdown-item' );

			if ( $args->has_children && $depth === 0 ) {
				$atts['data-toggle']    = 'dropdown';
				$atts['data-bs-toggle'] = 'dropdown';
				$atts['class']          = $atts['class'].' dropdown-toggle';
				$atts['aria-haspopup']  = 'true';
			}

			// if ( $atts['title'] ) {
			// 	$atts['data-toggle']    = 'tooltip';
			// 	$atts['data-bs-toggle'] = 'tooltip';
			// 	$atts['data-placement'] = 'auto bottom';
			// }

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes.= ' '.$attr.'="'.$value.'"';
				}
			}

			$title = gThemeUtilities::prepTitle( $item->title, $item->ID );
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = $args->before;

			/*
			 * Glyphicons
			 * ===========
			 * Since the the menu item is NOT a Divider or Header we check the see
			 * if there is a value in the xfn property. If the xfn
			 * property is NOT null we apply it as the class name for the glyphicon.
			 */
			if ( ! empty( $item->xfn ) )
				$item_output.= '<a'.$attributes.'><span class="glyphicon '.esc_attr( $item->xfn ).'"></span>&nbsp;';
			else
				$item_output.= '<a'.$attributes.'>';

			$item_output.= $args->link_before.$title.$args->link_after;
			$item_output.= ( $args->has_children && 0 === $depth ) ? ' <span class="caret"></span></a>' : '</a>';
			$item_output.= $args->after;

			$output.= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @see Walker::start_el()
	 * @since 2.5.0
	 *
	 * @param object $element Data object
	 * @param array $children_elements List of elements to continue traversing.
	 * @param int $max_depth Max depth to traverse.
	 * @param int $depth Depth of current element.
	 * @param array $args
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output )
	{
		if ( ! $element )
			return;

		$id_field = $this->db_fields['id'];

		// Display this element.
		if ( is_object( $args[0] ) )
			$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a manu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 *
	 * @param array $args passed from the `wp_nav_menu()`
	 */
	public static function fallback( $args )
	{
		if ( current_user_can( 'manage_options' ) ) {

			extract( $args );

			$fb_output = null;

			if ( $container ) {
				$fb_output = '<'.$container;

				if ( $container_id )
					$fb_output.= ' id="'.$container_id.'"';

				if ( $container_class )
					$fb_output.= ' class="'.$container_class.'"';

				$fb_output.= '>';
			}

			$fb_output.= '<ul';

			if ( $menu_id )
				$fb_output.= ' id="'.$menu_id.'"';

			if ( $menu_class )
				$fb_output.= ' class="'.$menu_class.'"';

			$fb_output.= '>';
			$fb_output.= '<li><a href="'.admin_url( 'nav-menus.php' ).'">Add a menu</a></li>';
			$fb_output.= '</ul>';

			if ( $container )
				$fb_output.= '</'.$container.'>';

			echo $fb_output;
		}
	}
}
