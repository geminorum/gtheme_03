<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

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

		if ( is_single() ) {
			$previous = get_adjacent_post_link( '%link', _x( '<span aria-hidden="true">&larr;</span> Older', 'Post Navigation', GTHEME_TEXTDOMAIN ), FALSE, '', TRUE,  $taxonomy );
			$next     = get_adjacent_post_link( '%link', _x( 'Newer <span aria-hidden="true">&rarr;</span>', 'Post Navigation', GTHEME_TEXTDOMAIN ), FALSE, '', FALSE, $taxonomy );
			$classes[] = 'post-navigation';
		} else if ( $max_num_pages > 1 && ( is_home() || is_front_page() || is_archive() || is_search() ) ) {
			// NOTE: we use reverse!
			$previous = get_next_posts_link( _x( '<span aria-hidden="true">&larr;</span> Older', 'Index Navigation', GTHEME_TEXTDOMAIN ) );
			$next     = get_previous_posts_link( _x( 'Newer <span aria-hidden="true">&rarr;</span>', 'Index Navigation', GTHEME_TEXTDOMAIN ) );
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

		echo gThemeHTML::tag( 'nav', array(
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
			self::breadcrumbSingle( $atts );
		else
			self::breadcrumbArchive( $atts );
	}

	// Home > Cat > Label
	// bootstrap 3 compatible markup
	public static function breadcrumbSingle( $atts = array() )
	{
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

		$crumbs = self::getHomeCrumbs( $args );

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
				$single_html .= _x( '(Preview)', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN );

			if ( $args['page_is'] && in_the_loop() ) { // CAUTION : must be in the loop after the_post()
				global $page, $numpages;
				if ( ! empty( $page ) && 1 != $numpages ) //&& $page > 1 )
					$single_html .= sprintf( _x( 'Page <strong>%s</strong> of %s', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ),
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

		$count = count( $crumbs );

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

		$crumbs = self::getHomeCrumbs( $args );

		$template = empty( $args['strings']['archive'] ) ? _x( 'Site Archives', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['archive'];

		if ( is_front_page() ) {

		} else if ( is_home() ) {

		} else if ( is_404() ) {
			$crumbs[] = empty( $args['strings']['404'] ) ? _x( 'Not Found', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['404'];

		} else if ( is_category() ) {

			$template = empty( $args['strings']['category'] ) ? _x( 'Category Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['category'];
			$crumbs[] = sprintf( $template, single_term_title( '', FALSE ) );

		} else if ( is_tag() ) {

			$template = empty( $args['strings']['tag'] ) ? _x( 'Tag Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['tag'];
			$crumbs[] = sprintf( $template, single_term_title( '', FALSE ) );

		} else if ( is_tax( 'people' ) ) {

			$template = empty( $args['strings']['people'] ) ? _x( 'People Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['people'];
			$crumbs[] = sprintf( $template, single_term_title( '', FALSE ) );

		} else if ( is_tax() ) {

			$template = empty( $args['strings']['tax'] ) ? _x( 'Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['tax'];
			$crumbs[] = sprintf( $template, single_term_title( '', FALSE ) );

		} else if ( is_author() ) {

			$default_user = gThemeOptions::getOption( 'default_user', 0 );
			$author_id = intval( get_query_var( 'author' ) );

			if ( $default_user != $author_id ) {
				$template = empty( $args['strings']['author'] ) ? _x( 'Author Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['author'];
				$crumbs[] = sprintf( $template, get_the_author_meta( 'display_name', $author_id ) );
			}

		} else if ( is_search() ) {

			$template = empty( $args['strings']['search'] ) ? _x( 'Search Results for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['search'];
			$crumbs[] = sprintf( $template, ''.get_search_query().'' );

		} else if ( is_day() ) {

			$template = empty( $args['strings']['day'] ) ? _x( 'Daily Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['day'];
			$crumbs[] = sprintf( $template, get_the_date( gThemeOptions::getOption( 'date_format_day', 'j M Y' ) ) );

		} else if ( is_month() ) {

			$template = empty( $args['strings']['month'] ) ? _x( 'Monthly Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['month'];
			$crumbs[] = sprintf( $template, get_the_date( 'F Y' ) );

		} else if ( is_year() ) {

			$template = empty( $args['strings']['year'] ) ? _x( 'Yearly Archives for <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['year'];
			$crumbs[] = sprintf( $template, get_the_date( 'Y' ) );

		} else if ( is_archive() ) {
			$crumbs[] = $template;

		} else {
			$crumbs[] = $template;
		}

		if ( is_paged() ) {
			$template = empty( $args['strings']['paged'] ) ? _x( 'Page <strong>%s</strong>', 'Navigation Module: Breadcrumbs', GTHEME_TEXTDOMAIN ) : $args['strings']['paged'];
			$crumbs[] = sprintf( $template, number_format_i18n( get_query_var( 'paged' ) ) );
		}

		$count = count( $crumbs );

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

	public static function getHomeCrumbs( $args )
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

	// FIXME: DRAFT
	// @SOURCE: [Create Your Own Pagination Links](https://paulund.co.uk/create-pagination)
	// to add pagination to your search results and archives
	public static function paginateLinks()
	{
		global $wp_query;

		$big = 999999999;

		$pagination_links = paginate_links( array(
			'base'               => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'             => '?paged=%#%',
			'current'            => max( 1, get_query_var( 'paged' ) ),
			'mid_size'           => 8,
			'total'              => $wp_query->max_num_pages,
			'before_page_number' => '<span class="screen-reader-text">'._x( 'Page', 'Navigation Module', GTHEME_TEXTDOMAIN ).' </span>',
		) );

		echo $pagination_links;
	}

	// FIXME: DRAFT
	// @SOURCE: [matzko/filosofo-pagination](https://github.com/matzko/filosofo-pagination)
	public static function paginateLinksAlt( $args = array() )
	{
		global $paged, $posts_per_page, $request, $wp_query, $wpdb;

		if ( is_single() || is_page() )
			return TRUE;

		$query_obj = get_queried_object();

		if ( is_string( $args ) )
			parse_str( $args, $args );

		$defaults = array(
			'adjacents'  => 1,
			'newer_link' => _x( 'Newer Posts', 'Navigation Module', GTHEME_TEXTDOMAIN ),
			'older_link' => _x( 'Older Posts', 'Navigation Module', GTHEME_TEXTDOMAIN ),
			'type'       => 'list',
		);

		$args = array_merge( $defaults, $args );

		if( is_tax() ) {
			$total_items = isset( $query_obj->count ) ? (int) $query_obj->count : 0;
		} else {
			$total_items = $wp_query->found_posts;
		}

		$limit = $posts_per_page;
		$page = empty( $paged ) ? 1 : (int) $paged;

		$page_links = paginate_links( array(
			'base'      => str_replace( '367965', '%#%', get_pagenum_link( 367965 ) ), // hacky way to generate link base
			'format'    => '',
			'prev_text' => $args['newer_link'],
			'next_text' => $args['older_link'],
			'total'     => $wp_query->max_num_pages,
			'current'   => $page,
			'type'      => $args['type'],
		) );

		echo $page_links;
	}
}
