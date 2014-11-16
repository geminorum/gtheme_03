<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeTemplate extends gThemeModuleCore 
{
	
	public static function logo( $context = 'header' )
	{
		printf( gtheme_get_info( 'template_logo', 
			'<a class="navbar-brand no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>' ), 
				gThemeUtilities::home(),
				get_bloginfo( 'name' ),
				esc_attr__( 'Home', GTHEME_TEXTDOMAIN )
		);
	}
	
	// ANCESTOR : gtheme_get_term_link_tag()
	public static function term_link( $term, $taxonomy, $title = null ) 
	{
		if ( ! is_object( $term ) )
			$term = get_term( $term, $taxonomy );
	
		if ( ! is_null( $title ) ) {
			$title = $term->name;
			if ( $term->description )
				//$title .= ' :: '.strip_tags( wp_trim_words( 
				$title .= ' :: '.( wp_trim_words( 
					$term->description, 
					gtheme_get_info( 'excerpt_length', 40 ), 
					gtheme_get_info( 'excerpt_more', ' …' )
				) );
		}
		
		return sprintf( gtheme_get_info( 'template_term_link',
			//'<a href="%1$s" title="%3$s" class="%4$s">%2$s</a>' ),
			'<a href="%1$s" title="%3$s" class="%4$s" data-html="true" data-toggle="tooltip" data-placement="top">%2$s</a>' ),
				esc_url( get_term_link( $term, $taxonomy ) ),
				esc_html( apply_filters( 'single_term_title', $term->name ) ),
				esc_attr( $title ),
				'term-link term-'.$taxonomy.'-link'
		);
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
			$html .= self::term_parents( $parent->parent, $sep, $taxonomy, $visited );
		}
		
		//$html .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '">'.$parent->name.'</a>' . $sep;
		$html .= self::term_link( $parent, $taxonomy ).$sep;
		
		return $html;
	}
	
	// ANCESTOR : gtheme_get_the_categories()
	public static function the_terms( $sep = 'def', $taxonomy = 'category', $mode = 'both' ) 
	{
		$terms = get_the_terms( get_the_ID(), $taxonomy );
		
		if ( false === $terms || is_wp_error( $terms ) )
			return '';
		
		// TODO : if $mode == child
		
		foreach( $terms as $term )
			return self::term_parents( $term, gThemeUtilities::sanitize_sep( $sep, 'nav_sep', ' &raquo; ' ), $taxonomy );
	}
	
	public static function avatar( $id_or_email, $size = null ) 
	{
		if ( 0 === $size || false === $size )
			return;
			
		if ( is_null( $size ) )
			$size = (int) gtheme_get_info( 'comment_avatar_size', 64 );
		
		$default = gtheme_get_info( 'default_avatar_src', false );
		
		if ( gThemeUtilities::is_dev() && $default )
			echo gThemeUtilities::html( 'img', array(
				'src' => $default,
				'alt' => 'avatar',
				'class' => 'avatar avatar-'.$size.' photo avatar-default',
				'style' => 'max-width:'.$size.'px;max-height:'.$size.'px;',
			) );
		else
			echo get_avatar( $id_or_email, $size, $default );
	}
}
