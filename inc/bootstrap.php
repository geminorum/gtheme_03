<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeBootstrap extends gThemeModuleCore
{

	public function setup_actions( $args = [], $childless = NULL )
	{
		extract( self::atts( [
			'enhance_forms' => TRUE,
		], $args ) );

		if ( $enhance_forms ) {
			add_filter( 'widget_categories_dropdown_args', [ $this, 'widget_categories_dropdown_args' ], 12, 2 );
		}
	}

	public function widget_categories_dropdown_args( $args, $instance )
	{
		if ( empty( $args['class'] ) )
			$args['class'] = 'postform form-select form-select-sm';
		else
			$args['class'].= ' form-select form-select-sm';

		return $args;
	}

	/**
	 * Retrieves bootstrap major version for the current theme.
	 *
	 * @return false|null|int $version
	 */
	public static function version()
	{
		if ( ! $version = gThemeOptions::info( 'bootstrap_version', NULL ) )
			return $version; // FALSE for explicitly not using any version

		if ( version_compare( $version, '6.0.0', '>=' ) )
			return NULL; // not supported

		if ( version_compare( $version, '5.0.0', '>=' ) )
			return 5;

		if ( version_compare( $version, '4.0.0', '>=' ) )
			return 4;

		if ( version_compare( $version, '3.0.0', '>=' ) )
			return 3;

		return NULL; // not supported
	}

	// NOTE: supports `BS4`/`BS5`
	public static function navbarOpen( $brand = NULL, $class = 'navbar-expand-md', $additional = '', $target = 'navbar' )
	{
		$fixed  = gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE );
		$scheme = gThemeOptions::getColorScheme( 'bootstrap_color_scheme', 'dark' ); // dark/light

		// echo '<nav class="navbar navbar-'.$scheme.' bg-'.$scheme.( $fixed ? ' fixed-top ' : ' ' ).$class.'">';
		echo '<nav class="navbar navbar-'.$scheme.( $fixed ? ' fixed-top ' : ' ' ).$class.'">';

			echo $additional;
			self::navbarBrand( $brand );
			self::navbarToggler( $target );
			echo '<div class="collapse navbar-collapse" id="'.$target.'">';
	}

	// NOTE: supports `BS4`
	public static function navbarClose( $additional = '' )
	{
		echo $additional;
		echo '</div></nav>';
	}

	// NOTE: supports `BS4`/`BS5`
	public static function navbarToggler( $target = 'navbar', $class = '' )
	{
		echo '<button class="navbar-toggler '.$class.' -print-hide" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#'.$target.'" data-bs-target="#'.$target.'" aria-controls="'.$target.'" aria-expanded="false" aria-label="'.__( 'Toggle navigation', 'gtheme' ).'">';
		echo '<span class="navbar-toggler-icon"></span></button>';
	}

	// NOTE: supports `BS3`
	public static function navbarClass( $additional = '', $inverse = FALSE )
	{
		$fixed = gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE );

		echo 'class="navbar navbar-default'.( $fixed ? ' navbar-fixed-top' : '' ).( $inverse ? ' navbar-inverse' : '' ).' '.$additional.'"';
	}

	// NOTE: supports `BS3`
	public static function navbarHeader( $brand = NULL, $target = 'navbar' )
	{
		echo '<div class="navbar-header">';
			echo '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#'.$target.'" aria-expanded="false">';
				echo '<span class="screen-reader-text sr-only visually-hidden">'.__( 'Toggle navigation', 'gtheme' ).'</span>';
				echo '<span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>';
			echo '</button>';

			self::navbarBrand( $brand );

		echo '</div>';
	}

	// NOTE: supports `BS3`/`BS4`/`BS5`
	public static function navbarBrand( $brand = NULL, $filename = NULL, $class = '' )
	{
		if ( FALSE === $brand )
			return;

		$default = 'logo.png'; // FIXME: default `$filename` must be: `logo-navbar.png`
		$title   = '<span class="site-title" title="{{{logo_title}}}">{{site_name}}</span>';

		if ( is_null( $filename ) )
			$template = '<img src="'.GTHEME_CHILD_URL.'/images/'.$default.'" alt="{{{site_name}}}" fetchpriority="high" />';

		else if ( ! $filename )
			$template = $title;

		else if ( gThemeText::start( $filename, '#' ) )
			$template = '<svg class="-logo"><use xlink:href="'.$filename.'"></use></svg>';

		else
			$template = '<img src="'.GTHEME_CHILD_URL.'/images/'.$filename.'" alt="{{{site_name}}}" fetchpriority="high" />';

		if ( is_null( $brand ) )
			$brand = gThemeOptions::info( 'blog_name', FALSE );

		else if ( 'logo' == $brand )
			$brand = gThemeTemplate::logo( 'navbar', $template, FALSE );

		else if ( 'logo-title' == $brand )
			$brand = gThemeTemplate::logo( 'navbar', $template, FALSE, $filename ? '' : ( ' '.$title ) );

		if ( FALSE !== $brand )
			vprintf( '<a class="navbar-brand %4$s no-outline" href="%1$s" title="%2$s">%3$s</a>', [
				esc_url( gThemeUtilities::home() ),
				esc_attr( gThemeOptions::info( 'logo_title', '' ) ),
				$brand,
				$class,
			] );
	}

	// FIXME: add cache / problem with `yamm!`
	public static function navbarNav( $location = 'primary', $wrap = 'navbar', $class = '' )
	{
		$menu = wp_nav_menu( [
			'echo'           => 0,
			'menu'           => $location,
			'theme_location' => $location,
			'depth'          => 2,
			'container'      => '',
			'item_spacing'   => 'discard',
			'menu_class'     => 'nav'.( $wrap ? ' navbar-nav' : '' ).' menu-'.$location.' '.$class,
			'fallback_cb'    => 'wp_bootstrap_navwalker::fallback',
			'walker'         => new gThemeBootstrap_Walker_NavBar(),
			'after'          => '<span class="-dummy"></span>',

			/// Extra Arguments
			'theme_bs_version' => self::version(),
		] );

		if ( ! $menu )
			return FALSE;

		echo $wrap ? '<div id="'.$wrap.'" class="collapse navbar-collapse">'.$menu.'</div>' : $menu;

		return TRUE;
	}

	public static function navbarForm( $placeholder = NULL, $class = '' )
	{
		if ( is_null( $placeholder ) )
			$placeholder = gThemeSearch::getPlaceholderText();

		echo '<form class="navbar-form '.$class.'" role="search" method="get" action="'.gThemeSearch::getAction().'"><div class="form-group">';
			echo '<label for="search" class="screen-reader-text sr-only visually-hidden">'.gThemeSearch::getLabelText().'</label>';
			echo '<input id="search" type="text" class="form-control" name="'.gThemeSearch::getKey().'" value="'.esc_attr( gThemeSearch::query() ).'"';
			if ( $placeholder )
				echo ' placeholder="'.esc_attr( $placeholder ).'" ';
		echo '/></div></form>';
	}

	// NOTE: use `.dropdown-toggle::after{content:none;}` for hiding the caret!
	public static function navbarWooCommerce( $class = '', $menuname = NULL, $fallback = '' )
	{
		if ( ! gThemeWooCommerce::available() )
			return print $fallback;

		echo '<ul class="nav navbar-nav -woocommerce-nav '.$class.'">';

			gThemeWooCommerce::accountDropdown( '-account', $menuname, '<li class="dropdown nav-item %s">', '</li>', TRUE );
			gThemeWooCommerce::cartDropdown( '-cart', '<li class="dropdown nav-item %s">', '</li>', TRUE );

		echo '</ul>';
	}

	public static function commentCallback_BS4( $comment, $args, $depth )
	{
		switch ( $comment->comment_type ) {

			case 'note':
			case 'pingback':
			case 'trackback':
				break;

			case 'review':
			case 'comment':
			case '':
			// default:

				$avatar  = get_option( 'show_avatars' );
				$classes = get_comment_class( $avatar ? '-with-avatar' : '-no-avatar' );

				echo '<li id="comment-'.get_comment_ID().'" class="'.gThemeHTML::prepClass( $classes ).'">';

					if ( $avatar ) {
						if ( $author_url = get_comment_author_url() ) {

							echo '<a class="comment-avatar" href="'.esc_url( $author_url ).'" rel="external nofollow">';
								gThemeTemplate::avatar( $comment );
							echo '</a>';

						} else {

							echo '<span class="comment-avatar">';
								gThemeTemplate::avatar( $comment );
							echo '</span>';
						}
					}

					echo '<div id="comment-body-'.get_comment_ID().'" class="comment-body">';

						echo '<h6 class="comment-meta">';
							echo '<span class="comment-author">'.get_comment_author_link().'</span>';
							echo '&nbsp;';
							gThemeComments::time( $comment, '<small class="comment-time">', '</small>' );
						echo '</h6>';

						echo '<div class="comment-content">';
							comment_text( $comment->comment_ID );
							echo '<div class="clearfix"></div>';
						echo '</div>';

						gThemeComments::awaiting( $comment );
						gThemeComments::commentActions( $comment, $args, $depth );

					echo '</div>';
			break;
		}
	}

	public static function commentCallback_BS3( $comment, $args, $depth )
	{
		switch ( $comment->comment_type ) {

			case 'note':
			case 'pingback':
			case 'trackback':
			break;

			case 'review':
			case 'comment':
			case '':
			// default:

				$avatar = get_option( 'show_avatars' );

				echo '<li ';
					comment_class( 'media'.( $avatar ? ' -with-avatar' : ' -no-avatar' ) );
				echo ' id="comment-'.get_comment_ID().'">';

					if ( $avatar ) {
						if ( $author_url = get_comment_author_url() ) {

							echo '<a class="comment-avatar '
								.( gThemeUtilities::isRTL() ? 'pull-right media-right' : 'pull-left media-left' )
								.'" href="'.esc_url( $author_url ).'" rel="external nofollow">';
								gThemeTemplate::avatar( $comment );
							echo '</a>';

						} else {

							echo '<span class="comment-avatar '
								.( gThemeUtilities::isRTL() ? 'pull-right media-right' : 'pull-left media-left' )
								.'">';
								gThemeTemplate::avatar( $comment );
							echo '</span>';
						}
					}

					echo '<div class="media-body comment-body" id="comment-body-'.get_comment_ID().'">';

						echo '<h6 class="media-heading comment-meta">';
							echo '<span class="comment-author">'.get_comment_author_link().'</span>';
							echo ' ';
							gThemeComments::time( $comment, '<small class="comment-time">', '</small>' );
						echo '</h6>';

						echo '<div class="comment-content">';
							comment_text( $comment->comment_ID );
						echo '</div>';

						gThemeComments::awaiting( $comment );
						gThemeComments::commentActions( $comment, $args, $depth, 'media-actions' );

					echo '</div><div class="clearfix"></div>';
			break;
		}
	}
}
