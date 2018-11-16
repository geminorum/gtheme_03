<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeBootstrap extends gThemeModuleCore
{

	// BS4
	public static function navbarOpen( $brand = NULL, $class = 'navbar-expand-md' )
	{
		$target = 'navbar';
		$fixed  = gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE );
		$scheme = gThemeOptions::info( 'bootstrap_color_scheme', 'dark' ); // dark/light

		echo '<nav class="navbar navbar-'.$scheme.' bg-'.$scheme.( $fixed ? ' fixed-top ' : ' ' ).$class.'">';

			self::navbarBrand( $brand );

			echo '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#'
				.$target.'" aria-controls="'.$target.'" aria-expanded="false" aria-label="'
				.__( 'Toggle navigation', GTHEME_TEXTDOMAIN ).'">'
				.'<span class="navbar-toggler-icon"></span></button>';

			echo '<div class="collapse navbar-collapse" id="'.$target.'">';
	}

	// BS4
	public static function navbarClose( $additional = '', $dark = FALSE )
	{
		echo '</div></nav>';
	}

	// BS3
	public static function navbarClass( $additional = '', $inverse = FALSE )
	{
		$fixed = gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE );

		echo 'class="navbar navbar-default'.( $fixed ? ' navbar-fixed-top' : '' ).( $inverse ? ' navbar-inverse' : '' ).' '.$additional.'"';
	}

	// BS3
	public static function navbarHeader( $brand = NULL, $target = 'navbar' )
	{
		echo '<div class="navbar-header">';
			echo '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#'.$target.'" aria-expanded="false">';
				echo '<span class="screen-reader-text sr-only">'.__( 'Toggle navigation', GTHEME_TEXTDOMAIN ).'</span>';
				echo '<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>';
			echo '</button>';

			self::navbarBrand( $brand );

		echo '</div>';
	}

	// BS3/BS4
	public static function navbarBrand( $brand = NULL )
	{
		if ( is_null( $brand ) )
			$brand = gThemeOptions::info( 'blog_name', FALSE );

		else if ( 'logo' == $brand )
			$brand = gThemeTemplate::logo( 'navbar', '<img src="'.GTHEME_CHILD_URL.'/images/logo.png" alt="%2$s" />', FALSE );

		if ( FALSE !== $brand )
			vprintf( '<a class="navbar-brand" href="%1$s" title="%3$s">%2$s</a>', [
				gThemeUtilities::home(),
				$brand,
				esc_attr( gThemeOptions::info( 'logo_title', '' ) ),
			] );
	}

	// FIXME: add cache / problem with yamm
	public static function navbarNav( $location = 'primary', $wrap = 'navbar', $class = '' )
	{
		$menu = wp_nav_menu( [
			'echo'           => 0,
			'menu'           => $location,
			'theme_location' => $location,
			'depth'          => 2,
			'container'      => '',
			'item_spacing'   => 'discard',
			'menu_class'     => 'nav navbar-nav menu-'.$location.' '.$class,
			'fallback_cb'    => 'wp_bootstrap_navwalker::fallback',
			'walker'         => new gThemeBootstrap_Walker_NavBar(),
		] );

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
			echo '<label for="search" class="screen-reader-text sr-only">'._x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'</label>';
			echo '<input id="search" type="text" class="form-control" name="'.gThemeSearch::getKey().'" value="'.gThemeSearch::query().'"';
			if ( $placeholder )
				echo ' placeholder="'.$placeholder.'" ';
		echo '/></div></form>';
	}
}
