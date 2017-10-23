<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTemplate extends gThemeModuleCore
{

	public static function logo( $context = 'header', $template = NULL, $echo = TRUE )
	{
		if ( is_null( $template ) ) {
			// $template = '<h1><a class="logo-class main-logo no-outline" href="'.gtheme_get_home().'" rel="home">'.get_bloginfo( 'name' ).'</a></h1>';
			// $template = '<a class="head-logo no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>';
			// $template = '<a class="navbar-brand no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>';
			$template = '<a href="%1$s" title="%3$s" rel="home"><img src="'.GTHEME_CHILD_URL.'/images/logo.png" alt="%2$s" /></a>';

			$template = gThemeOptions::info( 'template_logo', $template );
		}

		$logo = vsprintf( $template, array(
			gThemeUtilities::home(),
			gThemeOptions::info( 'blog_name', '' ),
			esc_attr( gThemeOptions::info( 'logo_title', '' ) ),
		) );

		if ( ! $echo )
			return $logo;

		echo $logo;
	}

	// only for wp users
	// @SEE: `gThemeContent::byline()`
	// @REF: `get_the_author_posts_link()`
	public static function author( $post = NULL, $echo = TRUE )
	{
		if ( ! $post = get_post( $post ) )
			return '';

		if ( $post->post_author == gThemeOptions::getOption( 'default_user', 0 ) )
			return '';

		if ( ! $user = get_userdata( $post->post_author ) )
			return '';

		$template     = gThemeOptions::getOption( 'template_author_link', '<a href="%1$s" title="%2$s" rel="author">%3$s</a>' );
		$display_name = get_the_author_meta( 'display_name', $user->ID ); // applying gMember filter

		return vsprintf( $template, array(
			esc_url( get_author_posts_url( $user->ID, $user->user_nicename ) ),
			esc_attr( sprintf( _x( 'Posts by %s', 'Modules: Template: Author', GTHEME_TEXTDOMAIN ), $display_name ) ),
			$display_name,
		) );
	}

	// ANCESTOR : gtheme_get_term_link_tag()
	public static function term_link( $term, $taxonomy, $title = NULL )
	{
		if ( ! is_object( $term ) )
			$term = get_term( $term, $taxonomy );

		if ( ! is_null( $title ) ) {

			$title = $term->name;

			if ( $term->description )
				$title.= ' :: '.( wp_trim_words( $term->description,
					apply_filters( 'excerpt_length', 55 ),
					apply_filters( 'excerpt_more', ' &hellip;' )
				) );
		}

		// $template = '<a href="%1$s" title="%3$s" class="%4$s">%2$s</a>';
		$template = '<a href="%1$s" title="%3$s" class="%4$s" data-html="true" data-toggle="tooltip" data-placement="top">%2$s</a>';

		return vsprintf( gThemeOptions::info( 'template_term_link', $template ), array(
			esc_url( get_term_link( $term, $taxonomy ) ),
			esc_html( apply_filters( 'single_term_title', $term->name ) ),
			esc_attr( $title ),
			'term-link term-'.$taxonomy.'-link',
		) );
	}

	// ANCESTOR : get_category_parents()
	public static function term_parents( $id, $sep = 'def', $taxonomy = 'category', $visited = array() )
	{
		$html = '';

		$parent = get_term( $id, $taxonomy );
		if ( is_wp_error( $parent ) )
			return $parent;

		if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
			$visited[] = $parent->parent;
			$html.= self::term_parents( $parent->parent, $sep, $taxonomy, $visited );
		}

		// $html.= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '">'.$parent->name.'</a>' . $sep;
		$html.= self::term_link( $parent, $taxonomy ).$sep;

		return $html;
	}

	// ANCESTOR : gtheme_get_the_categories()
	public static function the_terms( $sep = 'def', $taxonomy = 'category', $mode = 'both' )
	{
		$terms = get_the_terms( get_the_ID(), $taxonomy );

		if ( FALSE === $terms || is_wp_error( $terms ) )
			return '';

		// TODO: if $mode == child

		foreach ( $terms as $term )
			return self::term_parents( $term, gThemeUtilities::sanitize_sep( $sep, 'nav_sep', ' &raquo; ' ), $taxonomy );
	}

	public static function avatar( $id_or_email, $size = NULL )
	{
		if ( 0 === $size || FALSE === $size )
			return;

		if ( is_null( $size ) )
			$size = (int) gThemeOptions::info( 'comment_avatar_size', 64 );

		$default = gThemeOptions::info( 'default_avatar_src', FALSE );

		if ( gThemeWordPress::isDev() && $default )
			echo gThemeHTML::tag( 'img', array(
				'src'   => $default,
				'alt'   => 'avatar',
				'class' => 'avatar avatar-'.$size.' photo avatar-default',
				'style' => 'max-width:'.$size.'px;max-height:'.$size.'px;',
			) );
		else
			echo get_avatar( $id_or_email, $size, $default );
	}

	// ANCESTOR : gtheme_copyright()
	public static function copyright( $b = '<p class="copyright text-muted credit">', $a = '</p>', $p = FALSE )
	{
		$copyright = gThemeOptions::info( 'copyright', FALSE );

		if ( FALSE === $copyright )
			return;

		if ( $p )
			$copyright = wpautop( gThemeUtilities::wordWrap( $copyright ), FALSE );

		echo $b.$copyright.$a;
	}
}
