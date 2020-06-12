<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeBootstrap extends gThemeModuleCore
{

	// BS4
	public static function navbarOpen( $brand = NULL, $class = 'navbar-expand-md', $additional = '' )
	{
		$target = 'navbar';
		$fixed  = gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE );
		$scheme = gThemeOptions::info( 'bootstrap_color_scheme', 'dark' ); // dark/light

		echo '<nav class="navbar navbar-'.$scheme.' bg-'.$scheme.( $fixed ? ' fixed-top ' : ' ' ).$class.'">';

			echo $additional;
			self::navbarBrand( $brand );

			echo '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#'
				.$target.'" aria-controls="'.$target.'" aria-expanded="false" aria-label="'
				.__( 'Toggle navigation', 'gtheme' ).'">'
				.'<span class="navbar-toggler-icon"></span></button>';

			echo '<div class="collapse navbar-collapse" id="'.$target.'">';
	}

	// BS4
	public static function navbarClose( $additional = '', $dark = FALSE )
	{
		echo $additional;
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
				echo '<span class="screen-reader-text sr-only">'.__( 'Toggle navigation', 'gtheme' ).'</span>';
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

	public static function navbarForm( $placeholder = NULL, $class = '' )
	{
		if ( is_null( $placeholder ) )
			$placeholder = __( 'Search &hellip;', 'gtheme' );

		echo '<form class="navbar-form '.$class.'" role="search" method="get" action="'.gThemeSearch::getAction().'"><div class="form-group">';
			echo '<label for="search" class="screen-reader-text sr-only">'._x( 'Search for:', 'label', 'gtheme' ).'</label>';
			echo '<input id="search" type="text" class="form-control" name="'.gThemeSearch::getKey().'" value="'.esc_attr( gThemeSearch::query() ).'"';
			if ( $placeholder )
				echo ' placeholder="'.$placeholder.'" ';
		echo '/></div></form>';
	}

	public static function commentCallback_BS4( $comment, $args, $depth )
	{
		switch ( $comment->comment_type ) {

			case 'pingback':
			case 'trackback':
			break;

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

			case 'pingback':
			case 'trackback':
			break;

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
