<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTemplate extends gThemeModuleCore
{

	// NOTE: order is: `base-context` -> `context-posttype` -> `base` -> `context` -> 'posttype`
	public static function wrapOpen( $context = 'index', $extra = [], $posttype = NULL )
	{
		$base = gtheme_template_base();
		$full = gThemeOptions::info( 'template_column_fullpage', FALSE ); // all pages are full pages!

		// `array_merge` cannot handle numeric keys
		if ( '404' == $context )
			$context = 'notfound';

		$columns = array_merge( $full ? [] : [
			'notfound'      => 'col-sm-6', // `404` alias
			'index'         => 'col-sm-8',
			'latest'        => 'col-sm-6',
			'singular'      => 'col-sm-8',
			'attachment'    => 'col-sm-8',
			'systempage'    => 'col-sm-6',
			'newpost'       => 'col-sm-12',
			'fullwidthpage' => 'col-sm-12',
			'signup'        => 'col-sm-12',
			'activate'      => 'col-sm-12',
			'bbpress'       => 'col-sm-12',
			'buddypress'    => 'col-sm-12',
		], gThemeOptions::info( 'template_columns', [] ) );

		if ( is_null( $posttype ) )
			$posttype = get_post_type();

		if ( ! $posttype && is_post_type_archive() )
			$posttype = get_queried_object()->name;

		if ( $base && array_key_exists( $base.'-'.$context, $columns ) )
			$column = $columns[$base.'-'.$context];

		else if ( $posttype && array_key_exists( $context.'-'.$posttype, $columns ) )
			$column = $columns[$context.'-'.$posttype];

		else if ( $base && array_key_exists( $base, $columns ) )
			$column = $columns[$base];

		else if ( array_key_exists( $context, $columns ) )
			$column = $columns[$context];

		else if ( $posttype && array_key_exists( $posttype, $columns ) )
			$column = $columns[$posttype];

		else
			$column = gThemeOptions::info( 'template_column_fallback', $full ? 'col' : 'col-sm-8' );

		$classes = [ 'wrap-content', 'wrap-content-'.$context ];

		vprintf( gThemeOptions::info( 'template_wrap_open', '<div id="content" class="%1$s">' ), [
			gThemeHTML::prepClass( $column, $classes, $extra ),
			$context,
		] );

		do_action( 'gtheme_template_wrap_open', $context, $posttype );
	}

	public static function wrapClose( $context = 'index', $additional = '', $posttype = NULL )
	{
		if ( is_null( $posttype ) )
			$posttype = get_post_type();

		if ( ! $posttype && is_post_type_archive() )
			$posttype = get_queried_object()->name;

		do_action( 'gtheme_template_wrap_close', $context, $posttype );

		echo $additional;
		echo gThemeOptions::info( 'template_wrap_close', '</div>' );
	}

	// @REF: https://css-tricks.com/header-text-image-replacement/
	// @REF: http://luigimontanez.com/2010/stop-using-text-indent-css-trick/
	public static function logo_OLD( $context = 'header', $template = NULL, $verbose = TRUE )
	{
		if ( is_null( $template ) ) {
			// $template = '<h1><a class="logo-class main-logo no-outline" href="'.gtheme_get_home().'" rel="home">'.get_bloginfo( 'name' ).'</a></h1>';
			// $template = '<a class="head-logo no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>';
			// $template = '<a class="navbar-brand no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>';
			$template = '<a href="%1$s" title="%3$s" rel="home"><img src="'.GTHEME_CHILD_URL.'/images/logo.png" alt="%2$s" /></a>';

			$template = gThemeOptions::info( 'template_logo', $template );
		}

		$logo = vsprintf( $template, [
			gThemeUtilities::home(),
			gThemeOptions::info( 'blog_name', '' ),
			esc_attr( gThemeOptions::info( 'logo_title', '' ) ),
		] );

		if ( ! $verbose )
			return $logo;

		echo $logo;
	}

	public static function logo( $context = 'header', $template = NULL, $verbose = TRUE )
	{
		$default = gThemeOptions::info( 'template_logo',
			'<a href="{{home_url}}" title="{{{logo_title}}}" rel="home"><img src="{{logo_url_png}}" alt="{{{site_name}}}" fetchpriority="{{fetchpriority}}" /></a>' );

		if ( is_null( $template ) )
			$template = gThemeOptions::info( 'template_logo_'.$context, $default );

		// NOTE: the order is important on format to token conversion
		$tokens = [
			'home_url'       => gThemeUtilities::home(),
			'about_page_url' => gThemeContent::getPostLink( gThemePages::get( 'about' ) ) ?: '',
			'site_name'      => gThemeOptions::info( 'blog_name', '' ),
			'logo_title'     => gThemeOptions::info( 'logo_title', '' ),
			'theme_group'    => gThemeOptions::getGroup(),                 // default is `main`
			'logo_url_svg'   => GTHEME_CHILD_URL.'/images/logo.svg',
			'logo_url_png'   => GTHEME_CHILD_URL.'/images/logo.png',
			'site_locale'    => get_locale(),
			'fetchpriority'  => 'high',                                    // @REF: https://web.dev/priority-hints/#the-fetchpriority-attribute
		];

		// NOTE: back-comp: DROP THIS
		if ( gThemeText::has( $template, [ '%1$s', '%2$s', '%3$s' ] ) )
			$template = gThemeText::convertFormatToToken( $template, array_keys( $tokens ) );

		$html = gThemeText::replaceTokens( $template, $tokens );

		if ( ! $verbose )
			return $html;

		echo $html;
	}

	public static function customLogo( $context = 'header', $before = '', $after = '', $fallback = NULL )
	{
		if ( $html = get_custom_logo() )
			echo $before.$html.$after;

		else if ( is_null( $fallback ) )
			echo $before.self::logo( $context, NULL, FALSE ).$after;

		else if ( $fallback )
			echo $before.$fallback.$after;
	}

	public static function about( $before = '', $after = '', $custom = NULL, $page = 'about' )
	{
		$group = gThemeOptions::getGroup();
		$title = gThemeOptions::info( 'frontpage_desc' );

		if ( 'main' == $group ) {

			gThemePages::link( $page, [
				'title'  => $custom ?: $title,
				'attr'   => 'title', // target page title
				'rel'    => 'about',
				'def'    => FALSE, // disable link if target page does not exist
				'before' => $before,
				'after'  => $after,
			] );

		} else if ( $title || $custom ) {

			echo $before.'<a href="'.esc_url( GTHEME_HOME ).'" title="'.esc_attr_x( 'Front Page', 'Modules: Template: Title Attr', 'gtheme' ).'">';
				echo $custom ?: esc_html( $title );
			echo '</a>'.$after;
		}
	}

	public static function description( $before = '<p class="site-description -description">', $after = '</p>' )
	{
		if ( $desc = gThemeOptions::info( 'frontpage_desc' ) )
			echo $before.$desc.$after;
	}

	// FIXME: DRAFT: NOT USED
	public static function header( $before = '', $after = '' )
	{
		if ( ! $src = get_header_image() )
			return;

		$image = get_custom_header();

		echo $before;

		echo '<center><a href="'.esc_url( gThemeUtilities::home() ).'">';
		echo '<img src="'.$src.'" class="header-image';
		echo '" width="'.esc_attr( $image->width );
		echo '" height="'.esc_attr( $image->height );
		echo '" alt="'.gThemeOptions::info( 'blog_name', '' );
		echo '" /></a></center>';

		echo $after;
	}

	public static function sidebar( $name = NULL, $before = '', $after = '' )
	{
		if ( ! gThemeOptions::info( 'sidebar_support', TRUE ) )
			return;

		echo $before;
			get_sidebar( $name );
		echo $after;
	}

	// NOTE: only for WP users
	// @REF: `get_the_author_posts_link()`
	public static function author( $post = NULL, $verbose = TRUE )
	{
		if ( ! $post = get_post( $post ) )
			return '';

		// dummy post
		if ( ! $post->ID )
			return '';

		if ( $post->post_author == gThemeOptions::getOption( 'default_user', 0 ) )
			return '';

		if ( ! $user = get_userdata( $post->post_author ) )
			return '';

		$display_name = get_the_author_meta( 'display_name', $user->ID ); // applying gMember filter

		if ( ! $link = get_author_posts_url( $user->ID, $user->user_nicename ) )
			return $display_name;

		$template = gThemeOptions::getOption( 'template_author_link', '<a href="%1$s" title="%2$s" rel="author">%3$s</a>' );

		return vsprintf( $template, [
			esc_url( $link ),
			/* translators: %s: display name */
			esc_attr( sprintf( _x( 'Posts by %s', 'Modules: Template: Author', 'gtheme' ), $display_name ) ),
			$display_name,
		] );
	}

	// NOTE: DEPRECATED
	// ANCESTOR: `gtheme_get_term_link_tag()`
	public static function term_link( $term, $taxonomy, $title = NULL )
	{
		self::_dep( 'gThemeTerms::getTermLink()' );

		if ( ! is_object( $term ) )
			$term = get_term( $term, $taxonomy );

		$name = sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' );

		if ( ! is_null( $title ) ) {

			$title = $name;

			if ( $term->description )
				$title.= ' :: '.( wp_trim_words( $term->description,
					apply_filters( 'excerpt_length', 55 ),
					apply_filters( 'excerpt_more', ' &hellip;' )
				) );
		}

		// $template = '<a href="%1$s" title="%3$s" class="%4$s">%2$s</a>';
		$template = '<a href="%1$s" title="%3$s" class="%4$s" data-html="true" data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top">%2$s</a>';

		return vsprintf( gThemeOptions::info( 'template_term_link', $template ), [
			esc_url( get_term_link( $term, $taxonomy ) ),
			esc_html( apply_filters( 'single_term_title', $name ) ),
			esc_attr( $title ),
			'term-link term-'.$taxonomy.'-link',
		] );
	}

	// NOTE: DEPRECATED
	// ANCESTOR: `get_category_parents()`
	public static function term_parents( $id, $sep = 'def', $taxonomy = 'category', $visited = [] )
	{
		self::_dep( 'gThemeTerms::getWithParents()' );

		$html = '';

		$parent = get_term( $id, $taxonomy );

		if ( is_wp_error( $parent ) )
			return $parent;

		if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
			$visited[] = $parent->parent;
			$html.= self::term_parents( $parent->parent, $sep, $taxonomy, $visited );
		}

		// $html.= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '">'.$parent->name.'</a>' . $sep;
		$html.= self::term_link( $parent, $taxonomy ).$sep;

		return $html;
	}

	// NOTE: DEPRECATED
	// ANCESTOR: `gtheme_get_the_categories()`
	public static function the_terms( $sep = 'def', $taxonomy = 'category', $mode = 'both' )
	{
		self::_dep();

		if ( ! $post = get_post() )
			return '';

		if ( ! is_object_in_taxonomy( $post, $taxonomy ) )
			return '';

		$terms = get_the_terms( $post, $taxonomy );

		if ( FALSE === $terms || is_wp_error( $terms ) )
			return '';

		// TODO: if $mode == child

		foreach ( $terms as $term )
			return self::term_parents( $term, gThemeOptions::info( 'nav_sep', _x( ' &laquo; ', 'Options: Separator: Nav', 'gtheme' ) ), $taxonomy );
	}

	public static function avatar( $id_or_email, $size = NULL )
	{
		if ( 0 === $size || FALSE === $size )
			return;

		if ( is_null( $size ) )
			$size = (int) gThemeOptions::info( 'comment_avatar_size', 75 );

		$default = gThemeOptions::info( 'default_avatar_src', GTHEME_URL.'/images/avatar-512.png' );

		if ( gThemeWordPress::isDev() && $default )
			echo gThemeHTML::tag( 'img', [
				'src'      => $default,
				'alt'      => '',                                                // 'avatar',
				'class'    => 'avatar avatar-'.$size.' photo avatar-default',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'style'    => 'max-width:'.$size.'px;max-height:'.$size.'px;',
			] );
		else
			echo get_avatar( $id_or_email, $size, $default );
	}

	public static function getCopyright( $context = NULL )
	{
		if ( $option = gThemeOptions::getOption( 'copyright' ) )
			return $option;

		if ( $info = gThemeOptions::info( 'copyright', NULL ) ) // FALSE to disable
			return $info;

		if ( FALSE !== $info )
			return apply_filters( 'gtheme_copyright', __( '&copy; All rights reserved.', 'gtheme' ), $context );

		return FALSE;
	}

	// ANCESTOR: `gtheme_copyright()`
	public static function copyright( $before = '<p class="copyright text-muted credit">', $after = '</p>', $p = FALSE )
	{
		$copyright = self::getCopyright( 'default' );

		if ( FALSE === $copyright )
			return;

		if ( gThemeOptions::info( 'copyright_append_site_modified', TRUE ) )
			$copyright.= gThemeEditorial::siteModified( [
				'link'   => gThemeOptions::info( 'copyright_link_site_modified', FALSE ),
				'title'  => FALSE,
				'before' => ' '._x( 'Last updated on', 'Root: End: Before Site Modified', 'gtheme' ).' ',
			], FALSE );

		if ( $p )
			$copyright = gThemeText::autoP( gThemeText::wordWrap( $copyright ), FALSE );

		echo $before.$copyright.$after;

		if ( gThemeOptions::info( 'copyright_append_home_in_print', TRUE ) )
			echo '<div class="-home-in-print visible-print-block -print-only -code">'.gThemeUtilities::home( TRUE ).'</div>';
	}

	public static function copyrightAMP()
	{
		if ( ! $copyright = self::getCopyright( 'amp' ) )
			return;

		echo gThemeText::autoP( gThemeText::wordWrap( $copyright ), FALSE );
	}

	public static function svgIcon( $id, $before = '', $after = '', $extra = [] )
	{
		return $before.'<span class="'.gThemeHTML::prepClass( '-theme-icon-wrap', $extra ).'">'.
			'<svg class="-theme-icon -theme-icon-svg"><use xlink:href="#'.$id.'"></use></svg></span>'.$after;
	}

	public static function telephone( $number, $before = '', $after = '', $atts = [] )
	{
		echo $before;

		if ( function_exists( 'gNetwork' ) )
			echo gNetwork()->shortcodes->shortcode_tel( $atts, $number );

		else
			echo '<a class="-tel" href="tel:'.$number.'">'.apply_filters( 'number_format_i18n', $number ).'‏</a>';

		echo $after;
	}
}
