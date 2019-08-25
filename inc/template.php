<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTemplate extends gThemeModuleCore
{

	public static function wrapOpen( $context = 'index', $extra = [] )
	{
		$columns = self::atts( [
			'index'         => 'col-sm-8',
			'singular'      => 'col-sm-8',
			'systempage'    => 'col-sm-6',
			'fullwidthpage' => 'col-sm-12',
			'signup'        => 'col-sm-12',
			'activate'      => 'col-sm-12',
		], gThemeOptions::info( 'template_columns', [] ) );

		if ( array_key_exists( $context, $columns ) )
			$column = $columns[$context];
		else
			$column = gThemeOptions::info( 'template_column_fallback', 'col-sm-8' );

		$classes = [ 'wrap-content', 'wrap-content-'.$context ];

		echo '<div class="'.join( ' ', gThemeHTML::attrClass( $column, $classes, $extra ) ).'" id="content">';

		do_action( 'gtheme_template_wrap_open', $context );
	}

	public static function wrapClose( $context = 'index' )
	{
		do_action( 'gtheme_template_wrap_close', $context );

		echo '</div>';
	}

	// @REF: https://css-tricks.com/header-text-image-replacement/
	// @REF: http://luigimontanez.com/2010/stop-using-text-indent-css-trick/
	public static function logo( $context = 'header', $template = NULL, $echo = TRUE )
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

		if ( ! $echo )
			return $logo;

		echo $logo;
	}

	public static function description( $before = '<p class="site-description -description">', $after = '</p>' )
	{
		$desc = gThemeOptions::info( 'frontpage_desc' );

		if ( $desc )
			echo $before.$desc.$after;
	}

	// FIXME: DRAFT: NOT USED
	public static function header( $before = '', $after = '' )
	{
		if ( ! $src = get_header_image() )
			return;

		$image = get_custom_header();

		echo $before;

		echo '<center><a href="'.gThemeUtilities::home().'">';
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

	// FIXME: DEPRECATED
	// only for wp users
	// @REF: `get_the_author_posts_link()`
	public static function author( $post = NULL, $echo = TRUE )
	{
		// self::_dep( 'gThemeContent::byline()' );

		if ( ! $post = get_post( $post ) )
			return '';

		// dummy post
		if ( ! $post->ID )
			return '';

		if ( $post->post_author == gThemeOptions::getOption( 'default_user', 0 ) )
			return '';

		if ( ! $user = get_userdata( $post->post_author ) )
			return '';

		$template     = gThemeOptions::getOption( 'template_author_link', '<a href="%1$s" title="%2$s" rel="author">%3$s</a>' );
		$display_name = get_the_author_meta( 'display_name', $user->ID ); // applying gMember filter

		return vsprintf( $template, [
			esc_url( get_author_posts_url( $user->ID, $user->user_nicename ) ),
			esc_attr( sprintf( _x( 'Posts by %s', 'Modules: Template: Author', GTHEME_TEXTDOMAIN ), $display_name ) ),
			$display_name,
		] );
	}

	// FIXME: DEPRECATED
	// ANCESTOR : gtheme_get_term_link_tag()
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
		$template = '<a href="%1$s" title="%3$s" class="%4$s" data-html="true" data-toggle="tooltip" data-placement="top">%2$s</a>';

		return vsprintf( gThemeOptions::info( 'template_term_link', $template ), [
			esc_url( get_term_link( $term, $taxonomy ) ),
			esc_html( apply_filters( 'single_term_title', $name ) ),
			esc_attr( $title ),
			'term-link term-'.$taxonomy.'-link',
		] );
	}

	// FIXME: DEPRECATED
	// ANCESTOR : get_category_parents()
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

	// FIXME: DEPRECATED
	// ANCESTOR : gtheme_get_the_categories()
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
			return self::term_parents( $term, gThemeOptions::info( 'nav_sep', _x( ' &laquo; ', 'Options: Separator: Nav', GTHEME_TEXTDOMAIN ) ), $taxonomy );
	}

	public static function avatar( $id_or_email, $size = NULL )
	{
		if ( 0 === $size || FALSE === $size )
			return;

		if ( is_null( $size ) )
			$size = (int) gThemeOptions::info( 'comment_avatar_size', 75 );

		$default = gThemeOptions::info( 'default_avatar_src', GTHEME_URL.'/images/avatar.png' );

		if ( gThemeWordPress::isDev() && $default )
			echo gThemeHTML::tag( 'img', [
				'src'   => $default,
				'alt'   => 'avatar',
				'class' => 'avatar avatar-'.$size.' photo avatar-default',
				'style' => 'max-width:'.$size.'px;max-height:'.$size.'px;',
			] );
		else
			echo get_avatar( $id_or_email, $size, $default );
	}

	// ANCESTOR : gtheme_copyright()
	public static function copyright( $before = '<p class="copyright text-muted credit">', $after = '</p>', $p = FALSE )
	{
		$copyright = gThemeOptions::info( 'copyright', FALSE );

		if ( FALSE === $copyright )
			return;

		if ( gThemeOptions::info( 'copyright_append_site_modified', TRUE ) )
			$copyright.= gThemeEditorial::siteModified( [
				'link'   => gThemeOptions::info( 'copyright_link_site_modified', FALSE ),
				'title'  => FALSE,
				'before' => ' '._x( 'Last updated on', 'Root: End: Before Site Modified', GTHEME_TEXTDOMAIN ).' ',
			], FALSE );

		if ( $p )
			$copyright = gThemeText::autoP( gThemeText::wordWrap( $copyright ), FALSE );

		echo $before.$copyright.$after;

		if ( gThemeOptions::info( 'copyright_append_home_in_print', TRUE ) )
			echo '<div class="visible-print-block -print-only -code">'.gThemeUtilities::home( TRUE ).'</div>';
	}

	public static function copyrightAMP()
	{
		if ( ! $copyright = gThemeOptions::info( 'copyright', FALSE ) )
			return;

		echo gThemeText::autoP( gThemeText::wordWrap( $copyright ), FALSE );
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
