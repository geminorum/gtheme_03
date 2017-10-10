<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeBootstrap extends gThemeModuleCore
{

	public static function navbarClass( $additional = '', $inverse = FALSE )
	{
		$fixed = gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE );

		echo 'class="navbar navbar-default'.( $fixed ? ' navbar-fixed-top' : '' ).( $inverse ? ' navbar-inverse' : '' ).' '.$additional.'"';
	}

	public static function navbarHeader( $brand = NULL, $target = 'navbar' )
	{
		echo '<div class="navbar-header">';
			echo '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#'.$target.'" aria-expanded="false">';
				echo '<span class="sr-only">'.__( 'Toggle navigation', GTHEME_TEXTDOMAIN ).'</span>';
				echo '<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>';
			echo '</button>';

			if ( is_null( $brand ) )
				$brand = gThemeOptions::info( 'blog_name', FALSE );

			else if ( 'logo' == $brand )
				$brand = gThemeTemplate::logo( 'navbar', '<img src="'.GTHEME_CHILD_URL.'/images/logo.png" alt="%2$s" />', FALSE );

			if ( FALSE !== $brand )
				vprintf( '<a class="navbar-brand" href="%1$s" title="%3$s">%2$s</a>', array(
					gThemeUtilities::home(),
					$brand,
					esc_attr( gThemeOptions::info( 'logo_title', '' ) ),
				) );

		echo '</div>';
	}

	// FIXME: add cache / problem with yamm
	public static function navbarNav( $location = 'primary', $wrap = 'navbar', $class = '' )
	{
		$menu = wp_nav_menu( array(
			'echo'           => 0,
			'menu'           => $location,
			'theme_location' => $location,
			'depth'          => 2,
			'container'      => '',
			'item_spacing'   => 'discard',
			'menu_class'     => 'nav navbar-nav menu-'.$location.' '.$class,
			'fallback_cb'    => 'wp_bootstrap_navwalker::fallback',
			'walker'         => new gThemeBootstrap_Walker_NavBar(),
		) );

		if ( $menu )
			echo $wrap ? '<div id="'.$wrap.'" class="collapse navbar-collapse">'.$menu.'</div>' : $menu;
	}

	// TODO: another smaller search form
	// SEE: http://jsbin.com/futeyo/1/edit?html,css,js,output
	// SEE: http://bootsnipp.com/snippets/featured/expanding-search-button-in-css
	public static function navbarForm( $placeholder = NULL, $class = '' )
	{
		if ( is_null( $placeholder ) )
			$placeholder = __( 'Search &hellip;', GTHEME_TEXTDOMAIN );

		echo '<form class="navbar-form '.$class.'" role="search" method="get" action="'.gThemeSearch::getAction().'"><div class="form-group">';
			echo '<input type="text" class="form-control" name="'.gThemeSearch::getKey().'" value="'.gThemeSearch::query().'"';
			if ( $placeholder )
				echo ' placeholder="'.$placeholder.'" ';
		echo '/></div></form>';
	}
}

// ALSO SEE: http://www.creativewebdesign.ro/en/blog/wordpress/create-a-responsive-wordpress-theme-with-bootstrap-3-header-and-footer/
// ORIGINALLY BASED ON: wp_bootstrap_navwalker class v2.0.4 by Edward McIntyre
// https://github.com/twittem/wp-bootstrap-navwalker
class gThemeBootstrap_Walker_NavBar extends Walker_Nav_Menu
{

	public function start_lvl( &$output, $depth = 0, $args = array() )
	{
		$indent = str_repeat( "\t", $depth );
		$output.= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
	}

	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
	{
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		// sep on dropdown
		if ( 0 == strcasecmp( $item->xfn, 'divider' ) ) {
			$output.= $indent.'<li role="separator" class="divider">';

		} else if ( 1 === $depth && 0 === strcasecmp( $item->xfn, 'header' ) ) {
			$output.= $indent.'<li role="presentation" class="dropdown-header">'.esc_attr( $item->title );

		} else if ( 0 === strcasecmp( $item->xfn, 'disabled' ) ) {
			$output.= $indent.'<li role="presentation" class="disabled"><a href="#">'.esc_attr( $item->title ).'</a>';

		} else if ( 0 === $depth && 0 === strcasecmp( $item->xfn, 'yamm' ) ) {

			// https://github.com/geedmo/yamm3
			// CAUTION: #navbar must have .yamm
			$output.= $indent.'<li class="dropdown yamm-fw '
					.( empty( $item->classes ) ? '' : esc_attr( join( ' ', (array) $item->classes ) ) ).'">'
					.'<a href="'.esc_url( $item->url ).'" class="dropdown-toggle" data-toggle="dropdown">'.esc_attr( $item->title ).'</a>'
					.'<ul class="dropdown-menu"><li>'
					.'<div class="yamm-content">';

			ob_start();
				get_template_part( 'menu', esc_attr( $item->attr_title ) );
			$output.= ob_get_clean();

			$output.= '</div></li></ul>';

		} else {

			$class = $value = $attributes = '';

			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			// $classes[] = 'menu-item-'.$item->ID;

			$class = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

			if ( $args->has_children )
				$class.= ' dropdown';

			if ( in_array( 'current-menu-item', $classes ) )
				$class.= ' active';

			$class = $class ? ' class="'.esc_attr( $class ).'"' : '';

			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'.$item->ID, $item, $args, $depth );
			$id = $id ? ' id="'.esc_attr( $id ).'"' : '';

			$output.= $indent.'<li'.$id.$value.$class.'>';

			$atts = array();
			$atts['title']  = ! empty( $item->title )  ? $item->title  : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn )    ? $item->xfn	   : '';
			$atts['href']   = ! empty( $item->url )    ? $item->url    : '#';

			if ( $args->has_children && $depth === 0 ) {
				$atts['data-toggle']   = 'dropdown';
				$atts['class']         = 'dropdown-toggle';
				$atts['aria-haspopup'] = 'true';
			}

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes.= ' '.$attr.'="'.$value.'"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = $args->before;

			/*
			 * Glyphicons
			 * ===========
			 * Since the the menu item is NOT a Divider or Header we check the see
			 * if there is a value in the attr_title property. If the attr_title
			 * property is NOT null we apply it as the class name for the glyphicon.
			 */
			if ( ! empty( $item->attr_title ) )
				$item_output.= '<a'.$attributes.'><span class="glyphicon '.esc_attr( $item->attr_title ).'"></span>&nbsp;';
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
	 * @param array $args passed from the wp_nav_menu function.
	 *
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
