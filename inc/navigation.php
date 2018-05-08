<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeNavigation extends gThemeModuleCore
{

	// @SEE: https://codex.wordpress.org/Pagination
	// @SEE: [New Functions Available In WordPress 4.1](https://paulund.co.uk/new-functions-available-wordpress-4-1)
	public static function content( $context = 'index', $taxonomy = 'category', $max_num_pages = NULL )
	{
		global $wp_query;

		$classes = array( 'navigation' );

		if ( $context )
			$classes[] = 'navigation-'.$context;

		if ( is_null( $max_num_pages ) )
			$max_num_pages = $wp_query->max_num_pages;

		$prev_text = _x( '<span aria-hidden="true">&larr;</span> Older', 'Modules: Navigation: Post Navigation: Previous', GTHEME_TEXTDOMAIN );
		$next_text = _x( 'Newer <span aria-hidden="true">&rarr;</span>', 'Modules: Navigation: Post Navigation: Next', GTHEME_TEXTDOMAIN );

		if ( is_singular() ) {

			$classes[] = 'post-navigation';
			$title = _x( 'Post Navigation', 'Modules: Navigation: Screen Reader Title', GTHEME_TEXTDOMAIN );

			$prev = get_adjacent_post_link( '%link', $prev_text, FALSE, '', TRUE,  $taxonomy );
			$next = get_adjacent_post_link( '%link', $next_text, FALSE, '', FALSE, $taxonomy );

		} else if ( $max_num_pages > 1 && ( is_home() || is_front_page() || is_archive() || is_search() ) ) {

			$classes[] = 'paging-navigation';
			$title = _x( 'Posts Navigation', 'Modules: Navigation: Screen Reader Title', GTHEME_TEXTDOMAIN );

			// intentionally reversed!
			$prev = get_next_posts_link( $prev_text );
			$next = get_previous_posts_link( $next_text );

		} else {
			return;
		}

		if ( ! $prev && ! $next )
			return;

		$html = sprintf( '<h2 class="sr-only screen-reader-text">%s</h2>', $title );
		$html.= '<ul class="pager nav-links">';

		if ( $prev )
			$html.= sprintf( '<li class="previous nav-previous">%1$s</li>', $prev );

		if ( $next )
			$html.= sprintf( '<li class="next nav-next">%1$s</li>', $next );

		$html.= '</ul>';

		echo gThemeHTML::tag( 'nav', array(
			'role'  => 'navigation',
			'class' => $classes,
		), $html );
	}

	public static function paginate( $atts = [], $query = NULL )
	{
		if ( is_null( $query ) )
			$query = $GLOBALS['wp_query'];

		if ( $query->max_num_pages < 2 )
			return;

		$big = 999999999;

		$args = array_merge( self::atts( [
			'prev_text' => _x( '<span aria-hidden="true">&larr;</span>', 'Modules: Navigation: Pagination: Previous', GTHEME_TEXTDOMAIN ),
			'next_text' => _x( '<span aria-hidden="true">&rarr;</span>', 'Modules: Navigation: Pagination: Next', GTHEME_TEXTDOMAIN ),
			'end_size'  => 1, // how many numbers on either the start and the end list edges / default 1
			'mid_size'  => 4, // how many numbers to either side of the current pages / default 2
		], $atts ), [
			'type'    => 'array',
			'format'  => '?paged=%#%',
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => isset( $query->max_num_pages ) ? $query->max_num_pages : 1,
		] );

		if ( ! $links = paginate_links( $args ) )
			return;

		echo '<nav>';

			printf( '<h2 class="sr-only screen-reader-text">%s</h2>',
				_x( 'Navigation', 'Modules: Navigation: Screen Reader Title', GTHEME_TEXTDOMAIN ) );

			echo '<ul class="pagination">';

			foreach ( $links as $link )
				printf( '<li>%s</li>', $link );

		echo '</ul></nav>';
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
		if ( is_singular( gThemeOptions::info( 'breadcrumb_posttypes', array( 'post' ) ) ) )
			self::breadcrumbSingle( $atts );

		else if ( is_archive() || is_search() )
			self::breadcrumbArchive( $atts );
	}

	// Home > Cat > Label
	// bootstrap 3 compatible markup
	public static function breadcrumbSingle( $atts = array() )
	{
		global $page, $numpages;

		$args = self::atts( array(
			'home'       => FALSE, // 'home' // 'network' // 'custom string'
			'home_title' => NULL,
			'term'       => 'both',
			'tax'        => 'category',
			'label'      => TRUE,
			'page_is'    => TRUE,
			'post_title' => FALSE,
			'class'      => 'gtheme-breadcrumb',
			'before'     => '<div class="nav-content nav-content-single">',
			'after'      => '</div>',
			'context'    => NULL,
		), $atts );

		$crumbs = self::crumbHome( $args );

		if ( FALSE !== $args['term'] )
			$crumbs[] = gThemeTemplate::the_terms( FALSE, $args['tax'], $args['term'] );

		if ( FALSE !== $args['label'] && function_exists( 'gmeta_label' ) )
			$crumbs[] = gmeta_label( '', '', FALSE, array( 'echo' => FALSE ) );

		if ( is_singular() ) {

			$single_html = '';

			if ( is_preview() )
				$single_html.= _x( '(Preview)', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN );

			if ( $args['page_is'] && in_the_loop() ) { // CAUTION : must be in the loop after the_post()

				if ( ! empty( $page ) && 1 != $numpages ) //&& $page > 1 )
					$single_html.= sprintf( _x( 'Page <strong>%s</strong> of %s', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN ),
						number_format_i18n( $page ),
						number_format_i18n( $numpages ) );
			}

			if ( ! empty( $single_html ) )
				$crumbs[] = $single_html;
		}

		if ( $args['post_title'] && get_the_title() )
			$crumbs[] = '<a href="'.esc_url( apply_filters( 'the_permalink', get_permalink() ) )
					.'" title="'.gThemeContent::title_attr( FALSE ).'" rel="bookmark">'
					.get_the_title().'</a>';

		$crumbs = array_filter( $crumbs );
		$count  = count( $crumbs );

		if ( ! $count )
			return;

		echo $args['before'].'<ol class="breadcrumb '.$args['class'].'">';

		foreach ( $crumbs as $offset => $crumb )
			echo '<li'.( ( $count - 1 ) == $offset ? ' class="active"' : '' ).'>'.$crumb.'</li>';

		echo '</ol>'.$args['after'];
	}

	public static function breadcrumb_single( $atts = array() )
	{
		self::__dep( 'gThemeNavigation::breadcrumbSingle()' );
		self::breadcrumbSingle( $atts );
	}

	// home > archives > paged
	// bootstrap 3 compatible markup
	// @SEE: [get_the_archive_title()](https://developer.wordpress.org/reference/functions/get_the_archive_title/)
	public static function breadcrumbArchive( $atts = array() )
	{
		$args = self::atts( array(
			'home'       => FALSE, // 'home' // 'network' // 'custom string'
			'home_title' => NULL,
			'strings'    => gThemeOptions::info( 'strings_breadcrumb_archive', array() ),
			'class'      => 'gtheme-breadcrumb',
			'before'     => '<div class="nav-content nav-content-archive">',
			'after'      => '</div>',
			'context'    => NULL,
		), $atts );

		$crumbs = self::crumbHome( $args );

		if ( $crumb = self::crumbArchive( $args ) )
			$crumbs[] = $crumb;

		if ( is_paged() ) {
			$template = empty( $args['strings']['paged'] ) ? _x( 'Page <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['paged'];
			$crumbs[] = sprintf( $template, number_format_i18n( get_query_var( 'paged' ) ) );
		}

		$crumbs = array_filter( $crumbs );
		$count  = count( $crumbs );

		if ( ! $count )
			return;

		echo $args['before'].'<ol class="breadcrumb '.$args['class'].'">';

		foreach ( $crumbs as $offset => $crumb )
			echo '<li'.( ( $count - 1 ) == $offset ? ' class="active"' : '' ).'>'.$crumb.'</li>';

		echo '</ol>'.$args['after'];
	}

	public static function breadcrumb_archive( $atts = array() )
	{
		self::__dep( 'gThemeNavigation::breadcrumbArchive()' );
		self::breadcrumbArchive( $atts );
	}

	public static function crumbHome( $args )
	{
		$crumbs = array();

		if ( empty( $args['home'] ) )
			return $crumbs;

		if ( is_null( $args['home_title'] ) )
			$args['home_title'] = gThemeOptions::info( 'logo_title', '' );

		if ( 'home' == $args['home'] ) {
			$crumbs[] = '<a href="'.esc_url( home_url( '/' ) ).'" rel="home" title="'.esc_attr( $args['home_title'] ).'">'.gThemeOptions::info( 'blog_title' ).'</a>';

		} else if ( 'network' == $args['home'] ) {

			if ( is_main_site() ) {
				$crumbs[] = '<a href="'.esc_url( home_url( '/' ) ).'" rel="home" title="'.esc_attr( $args['home_title'] ).'">'.gThemeOptions::info( 'blog_title' ).'</a>';
			} else {
				$crumbs[] = '<a href="'.esc_url( gThemeUtilities::home() ).'" title="'.esc_attr( $args['home_title'] ).'">'.gThemeOptions::info( 'blog_name' ).'</a>';
				$crumbs[] = '<a href="'.esc_url( home_url( '/' ) ).'" rel="home" title="'.esc_attr( gThemeOptions::getOption( 'frontpage_desc', '' ) ).'">'.gThemeOptions::info( 'blog_title' ).'</a>';
			}

		} else {
			$crumbs[] = '<a href="'.esc_url( gThemeUtilities::home() ).'" rel="home" title="'.esc_attr( $args['home_title'] ).'">'.$args['home'].'</a>';
		}

		return apply_filters( 'gtheme_breadcrumb_after_home', $crumbs, $args );
	}

	// @REF: `get_the_archive_title()`
	public static function crumbArchive( $args )
	{
		if ( is_front_page() || is_home() )
			return FALSE;

		if ( is_404() )
			return empty( $args['strings']['404'] )
				? _x( 'Not Found', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['404'];

		if ( is_category() )
			return sprintf( ( empty( $args['strings']['category'] )
				? _x( 'Category Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['category'] ),
			single_term_title( '', FALSE ) );

		if ( is_tag() )
			return sprintf( ( empty( $args['strings']['tag'] )
				? _x( 'Tag Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['tag'] ),
			single_term_title( '', FALSE ) );

		if ( is_tax( 'people' ) )
			return sprintf( ( empty( $args['strings']['people'] )
				? _x( 'People Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['people'] ),
			single_term_title( '', FALSE ) );

		if ( is_post_type_archive() )
			return sprintf( ( empty( $args['strings']['posttype'] )
				? _x( 'Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['posttype'] ),
			post_type_archive_title( '', FALSE ) );

		if ( is_tax() ) {

			$tax = get_taxonomy( get_queried_object()->taxonomy );

			return sprintf( ( empty( $args['strings']['tax'] )
				? _x( '%s Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['tax'] ),
			$tax->labels->singular_name, single_term_title( '', FALSE ) );
		}

		if ( is_author() ) {

			$default = gThemeOptions::getOption( 'default_user', 0 );
			$author  = intval( get_query_var( 'author' ) );

			if ( $default == $author )
				return FALSE;

			return sprintf( ( empty( $args['strings']['author'] )
				? _x( 'Author Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['author'] ),
			get_the_author_meta( 'display_name', $author ) );
		}

		if ( is_search() )
			return sprintf( ( empty( $args['strings']['search'] )
				? _x( 'Search Results for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['search'] ),
			''.get_search_query().'' );

		if ( is_day() )
			return sprintf( ( empty( $args['strings']['day'] )
				? _x( 'Daily Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['day'] ),
			get_the_date( gThemeOptions::getOption( 'date_format_day', 'j M Y' ) ) );

		if ( is_month() )
			return sprintf( ( empty( $args['strings']['month'] )
				? _x( 'Monthly Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['month'] ),
			get_the_date( 'F Y' ) );

		if ( is_year() )
			return sprintf( ( empty( $args['strings']['year'] )
				? _x( 'Yearly Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
				: $args['strings']['year'] ),
			get_the_date( 'Y' ) );

		return empty( $args['strings']['archive'] )
			? _x( 'Archives', 'Modules: Navigation: Breadcrumbs', GTHEME_TEXTDOMAIN )
			: $args['strings']['archive'];
	}

	public static function paginateLinks()
	{
		self::__dep( 'gThemeNavigation::paginate()' );
		self::paginate();
	}
}
