<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeNavigation extends gThemeModuleCore
{

	// SEE: https://codex.wordpress.org/Pagination
	public static function content( $context = 'index', $taxonomy = 'category', $max_num_pages = NULL )
	{
		global $wp_query;

		$classes = array( 'navigation' );

		if ( $context )
			$classes[] = 'navigation-'.$context;

		if ( is_null( $max_num_pages ) )
			$max_num_pages = $wp_query->max_num_pages;

		if ( is_single() ) {
			$previous = get_adjacent_post_link( '%link', _x( '<span aria-hidden="true">&larr;</span> Older', 'Post Navigation', GTHEME_TEXTDOMAIN ), FALSE, '', TRUE,  $taxonomy );
			$next     = get_adjacent_post_link( '%link', _x( 'Newer <span aria-hidden="true">&rarr;</span>', 'Post Navigation', GTHEME_TEXTDOMAIN ), FALSE, '', FALSE, $taxonomy );
			$classes[] = 'post-navigation';
		} elseif ( $max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) {
			$previous = get_previous_posts_link( _x( '<span aria-hidden="true">&larr;</span> Older', 'Index Navigation', GTHEME_TEXTDOMAIN ) );
			$next     = get_next_posts_link( _x( 'Newer <span aria-hidden="true">&rarr;</span>', 'Index Navigation', GTHEME_TEXTDOMAIN ) );
			$classes[] = 'paging-navigation';
		} else {
			return;
		}

		if ( ! $previous && ! $next )
			return;

		$html = sprintf( '<h2 class="sr-only screen-reader-text">%1$s</h2>',
			_x( 'Posts Navigation', 'Navigation Title (Screen Reader Only)', GTHEME_TEXTDOMAIN ) );

		$html .= '<ul class="pager">';

		if ( $previous )
			$html .= sprintf( '<li class="previous">%1$s</li>', $previous );

		if ( $next )
			$html .= sprintf( '<li class="next">%1$s</li>', $next );

		$html .= '</ul>';

		echo gThemeUtilities::html( 'nav', array(
			'role'  => 'navigation',
			'class' => $classes,
		), $html );
	}

	// ANCESTOR: gtheme_content_nav()
	public static function part( $context = NULL, $max_num_pages = NULL )
	{
		global $wp_query;

		if ( is_null( $max_num_pages ) )
			$max_num_pages = $wp_query->max_num_pages;

		if ( $max_num_pages > 1 )
			get_template_part( 'nav', $context );
	}

	// wrapper wit conditional tags
	public static function breadcrumb( $atts = array() )
	{
		if ( is_singular() )
			self::breadcrumb_single( $atts );
		else
			self::breadcrumb_archive( $atts );

	}

	// Home > Cat > Label
	// bootstrap 3 compatible markup
	public static function breadcrumb_single( $atts = array() )
	{
		$crumbs = array();

		$args = shortcode_atts( array(
			'home'       => FALSE,
			'term'       => 'both',
			'tax'        => 'category',
			'label'      => TRUE,
			'page_is'    => TRUE,
			'post_title' => FALSE,
			'class'      => 'gtheme-breadcrumb',
			'before'     => '',
			'after'      => '',
			'context'    => NULL,
		), $atts );

		if ( FALSE !== $args['home'] )
			$crumbs[] = '<a href="'.esc_url( home_url( '/' ) ).'" rel="home" title="">'. // TODO : add title
				( 'home' == $args['home'] ? get_bloginfo( 'name' ) : $args['home'] ).'</a>'; // TODO: use theme home override

		$crumbs = apply_filters( 'gtheme_breadcrumb_after_home', $crumbs, $args );

		if ( FALSE !== $args['term'] )
			$crumbs[] = gThemeTemplate::the_terms( FALSE, $args['tax'], $args['term'] );

		if ( FALSE !== $args['label'] && function_exists( 'gmeta_label' ) ) {
			$label_html = gmeta_label( '', '', FALSE, array( 'echo' => FALSE ) );
			if ( ! empty( $label_html ) )
				$crumbs[] = $label_html;
		}

		if ( is_singular() ) {
			$single_html = '';
			if ( is_preview() )
				$single_html .= __( '(Preview)', GTHEME_TEXTDOMAIN );

			if ( $args['page_is'] && in_the_loop() ) { // CAUTION : must be in the loop after the_post()
				global $page, $numpages;
				if ( ! empty( $page ) && 1 != $numpages ) //&& $page > 1 )
					$single_html .= sprintf( __( 'Page <strong>%s</strong> of %s', GTHEME_TEXTDOMAIN ), number_format_i18n( $page ), number_format_i18n( $numpages ) );
			}
			if ( ! empty( $single_html ) )
				$crumbs[] = $single_html;
		}

		if ( $args['post_title'] && get_the_title() )
			$crumbs[] = '<a href="'.esc_url( apply_filters( 'the_permalink', get_permalink() ) )
				  .'" title="'.gtheme_the_title_attribute( FALSE ).'" rel="bookmark">'
				  .get_the_title().'</a>';

		$count = count( $crumbs );
		if ( ! $count )
			return;

		echo $args['before'].'<ol class="breadcrumb '.$args['class'].'">';
		foreach ( $crumbs as $offset => $crumb ) {
			echo '<li'.( ( $count-1 ) == $offset ? ' class="active"' : '' ).'>'.$crumb.'</li>';
		}
		echo '</ol>'.$args['after'];
	}

	// home > archives > paged
	// bootstrap 3 compatible markup
	public static function breadcrumb_archive( $atts = array() )
	{
		$crumbs = array();

		$args = shortcode_atts( array(
			'home'    => FALSE,
			'strings' => gThemeOptions::info( 'strings_breadcrumb_archive', array() ),
			'class'   => 'gtheme-breadcrumb',
			'before'  => '',
			'after'   => '',
			'context' => NULL,
		), $atts );

		if ( FALSE !== $args['home'] )
			$crumbs[] = '<a href="'.esc_url( home_url( '/' ) ).'" rel="home" title="">'. // TODO : add title
				( 'home' == $args['home'] ? get_bloginfo( 'name' ) : $args['home'] ).'</a>';

		$crumbs = apply_filters( 'gtheme_breadcrumb_after_home', $crumbs, $args );

		if ( is_front_page() ) {

		} else if ( is_home() ) {

		} else if ( is_category() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['category'] ) ? $args['strings']['category'] : __( 'Category Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), single_term_title( '', FALSE ) );
		} elseif ( is_tag() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['tag'] ) ? $args['strings']['tag'] : __( 'Tag Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), single_term_title( '', FALSE ) );
		} elseif ( is_tax() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['tax'] ) ? $args['strings']['tax'] : __( 'Taxonomy Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), single_term_title( '', FALSE ) );
		} elseif ( is_author() ) {
			$default_user = gtheme_get_option( 'default_user', 0 );
			$author_id = intval( get_query_var( 'author' ) );
			if ( $default_user != $author_id )
				$crumbs[] = sprintf( ( isset( $args['strings']['author'] ) ? $args['strings']['author'] : __( 'Author Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), get_the_author_meta( 'display_name', $author_id ) );
		} elseif ( is_search() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['search'] ) ? $args['strings']['search'] : __( 'Search Results for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), ''.get_search_query().'' );
		} elseif ( is_day() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['day'] ) ? $args['strings']['day'] : __( 'Daily Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), get_the_date() );
		} elseif ( is_month() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['month'] ) ? $args['strings']['month'] : __( 'Monthly Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), get_the_date('F Y') );
		} elseif ( is_year() ) {
			$crumbs[] = sprintf( ( isset( $args['strings']['year'] ) ? $args['strings']['year'] : __( 'Yearly Archives for <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), get_the_date('Y') );
		} elseif ( is_archive() ) {
			$crumbs[] = ( isset( $args['strings']['archive'] ) ? $args['strings']['archive'] : __( 'Site Archives', GTHEME_TEXTDOMAIN ) );
		} else {
			$crumbs[] = __( 'Site Archives', GTHEME_TEXTDOMAIN );
		}

		if ( is_paged() )
			$crumbs[] = sprintf( ( isset( $args['strings']['paged'] ) ? $args['strings']['paged'] : __( 'Page <strong>%s</strong>', GTHEME_TEXTDOMAIN ) ), number_format_i18n( get_query_var( 'paged' ) ) );

		$count = count( $crumbs );
		if ( ! $count )
			return;

		echo $args['before'].'<ol class="breadcrumb '.$args['class'].'">';
		foreach ( $crumbs as $offset => $crumb ) {
			echo '<li'.( ( $count-1 ) == $offset ? ' class="active"' : '' ).'>'.$crumb.'</li>';
		}
		echo '</ol>'.$args['after'];
	}
}
