<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeNavigation extends gThemeModuleCore
{

	// @SEE: https://codex.wordpress.org/Pagination
	// @SEE: [New Functions Available In WordPress 4.1](https://paulund.co.uk/new-functions-available-wordpress-4-1)
	public static function content( $context = 'index', $in_same_term = FALSE, $taxonomy = 'category', $max_num_pages = NULL )
	{
		global $wp_query;

		$classes = [ 'navigation', 'hidden-print' ];

		if ( $context )
			$classes[] = 'navigation-'.$context;

		if ( is_null( $max_num_pages ) )
			$max_num_pages = $wp_query->max_num_pages;

		if ( is_page() ) {

			return; // skip on pages

		} else if ( is_singular() || is_single() ) {

			$classes[] = 'post-navigation';

			$strings = apply_filters( 'gtheme_navigation_content_singular_strings', [
				'title' => _x( 'Post Navigation', 'Modules: Navigation: Screen Reader Title', 'gtheme' ),
				'prev'  => _x( '<span aria-hidden="true">&larr;</span>&nbsp;%title', 'Modules: Navigation: Post Navigation: Previous', 'gtheme' ),
				'next'  => _x( '%title&nbsp;<span aria-hidden="true">&rarr;</span>', 'Modules: Navigation: Post Navigation: Next', 'gtheme' ),
			], $context, get_post_type(), $taxonomy );

			// TODO: support row templates
			$prev = get_adjacent_post_link( '%link', $strings['prev'], $in_same_term, '', TRUE,  $taxonomy );
			$next = get_adjacent_post_link( '%link', $strings['next'], $in_same_term, '', FALSE, $taxonomy );

		} else if ( $max_num_pages > 1 && ( is_home() || is_front_page() || is_archive() || is_search() ) ) {

			$classes[] = 'paging-navigation';

			$strings = apply_filters( 'gtheme_navigation_content_archive_strings', [
				'title' => _x( 'Posts Navigation', 'Modules: Navigation: Screen Reader Title', 'gtheme' ),
				'prev'  => _x( '<span aria-hidden="true">&larr;</span>&nbsp;Older', 'Modules: Navigation: Posts Navigation: Previous', 'gtheme' ),
				'next'  => _x( 'Newer&nbsp;<span aria-hidden="true">&rarr;</span>', 'Modules: Navigation: Posts Navigation: Next', 'gtheme' ),
			], $context );

			// intentionally reversed!
			$prev = get_next_posts_link( $strings['prev'] );
			$next = get_previous_posts_link( $strings['next'] );

		} else {

			return;
		}

		if ( ! $prev && ! $next )
			return;

		$html = sprintf( '<h2 class="screen-reader-text sr-only visually-hidden">%s</h2>', $strings['title'] );
		$html.= '<ul class="nav-links pager gtheme-pager">';

		if ( $prev )
			$html.= sprintf( '<li class="previous nav-previous">%1$s</li>', $prev );

		if ( $next )
			$html.= sprintf( '<li class="next nav-next">%1$s</li>', $next );

		echo apply_filters( 'gtheme_navigation_content', gThemeHTML::tag( 'nav', [ 'class' => $classes ], $html.'</ul>' ), $context );
	}

	public static function paginate( $atts = [], $query = NULL, $extra = [], $ul_class = [] )
	{
		if ( is_null( $query ) )
			$query = $GLOBALS['wp_query'];

		if ( $query->max_num_pages < 2 )
			return;

		$big = 999999999;

		$args = array_merge( self::atts( [
			'prev_text' => _x( '<span aria-hidden="true">&larr;</span>', 'Modules: Navigation: Pagination: Previous', 'gtheme' ),
			'next_text' => _x( '<span aria-hidden="true">&rarr;</span>', 'Modules: Navigation: Pagination: Next', 'gtheme' ),
			'end_size'  => 1, // how many numbers on either the start and the end list edges / default 1
			'mid_size'  => 4, // how many numbers to either side of the current pages / default 2
		], $atts ), [
			'type'    => 'array',
			'format'  => '?paged=%#%',
			// 'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'base'    => esc_url_raw( str_replace( $big, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( $big ) ) ) ),
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => isset( $query->max_num_pages ) ? $query->max_num_pages : 1,
		] );

		// @SEE: new filter: `paginate_links_output`
		if ( ! $links = paginate_links( $args ) )
			return;

		echo '<nav class="'.gThemeHTML::prepClass( '-pagination', $extra ).'" aria-label="'._x( 'Page Navigation', 'Modules: Navigation: Pagination: aria-label', 'gtheme' ).'">';

			printf( '<h2 class="screen-reader-text sr-only visually-hidden">%s</h2>',
				_x( 'Navigation', 'Modules: Navigation: Screen Reader Title', 'gtheme' ) );

			echo '<ul class="'.gThemeHTML::prepClass( 'pagination', $ul_class ).'">';

			foreach ( $links as $link )
				printf( '<li class="page-item">%s</li>', $link );

		echo '</ul></nav>';
	}

	// ANCESTOR: `gtheme_content_nav()`
	public static function part( $context = NULL, $max_num_pages = NULL )
	{
		global $wp_query;

		if ( is_null( $max_num_pages ) )
			$max_num_pages = $wp_query->max_num_pages;

		if ( $max_num_pages > 1 )
			get_template_part( 'nav', $context );
	}

	// wrapper with conditional tags
	public static function breadcrumb( $atts = [] )
	{
		global $wp_query;

		if ( ! gThemeOptions::info( 'breadcrumb_support', TRUE ) )
			return;

		$posttypes = gThemeOptions::info( 'breadcrumb_posttypes', [ 'post', 'entry', 'product' ] );

		if ( FALSE !== $posttypes && is_singular( $posttypes ) )
			self::breadcrumbSingle( $atts );

		else if ( function_exists( 'is_shop' ) && is_shop() )
			self::breadcrumbArchive( $atts );

		else if ( FALSE !== $posttypes && is_post_type_archive( $posttypes ) )
			self::breadcrumbArchive( $atts );

		else if ( ! is_post_type_archive() && ( is_archive() || is_search() ) )
			self::breadcrumbArchive( $atts );

		// 404 has it's own search form
		if ( is_search() && ! empty( $wp_query->found_posts ) && gThemeOptions::info( 'breadcrumb_search_form', TRUE ) )
			gThemeSearch::formSecondary();

		do_action( 'gtheme_navigation_breadcrumb_after' );
	}

	// Home > Cat > Label
	// bootstrap 3 compatible markup
	public static function breadcrumbSingle( $atts = [] )
	{
		global $page, $numpages;

		if ( gThemeOptions::info( 'breadcrumb_single_disabled', FALSE ) )
			return;

		if ( ! $post = get_post() )
			return;

		$args = self::atts( [
			'home'       => FALSE, // 'home' // 'network' // 'custom string'
			'home_title' => NULL,
			'taxonomy'   => isset( $atts['tax'] ) ? $atts['tax'] : NULL,
			'term'       => count( gThemeOptions::getOption( 'terms', [] ) ) ? 'primary' : 'parents', // FALSE,
			'label'      => TRUE,
			'page_is'    => TRUE,
			'post_title' => FALSE,
			'class'      => 'gtheme-breadcrumb',
			'before'     => '<nav class="nav-content nav-content-single nav-content-singular" aria-label="breadcrumb">',
			'after'      => '</nav>',
			'context'    => NULL,
		], $atts );

		if ( 'primary' == $args['term'] )
			$args['taxonomy'] = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );

		else if ( is_null( $args['taxonomy'] ) )
			$args['taxonomy'] = gThemeTerms::getMainTaxonomy( $post->post_type, 'category' );

		$crumbs = self::crumbHome( $args );

		if ( $args['taxonomy'] && 'primary' == $args['term'] )
			$crumbs[] = gThemeTerms::linkPrimary( '', '', $post, '', FALSE );

		else if ( $args['taxonomy'] && 'parents' == $args['term'] )
			$crumbs = array_merge( $crumbs, gThemeTerms::getWithParents( $args['taxonomy'], $post, TRUE ) );

		if ( FALSE !== $args['label'] )
			$crumbs[] = gThemeEditorial::label( [ 'id' => $post, 'echo' => FALSE ] );

		if ( is_singular() || is_single() ) {

			$single_html = '';

			if ( is_preview() )
				$single_html.= _x( '(Preview)', 'Modules: Navigation: Breadcrumbs', 'gtheme' );

			if ( $args['page_is'] && in_the_loop() ) { // CAUTION : must be in the loop after the_post()

				if ( ! empty( $page ) && 1 != $numpages ) //&& $page > 1 )
					/* translators: %1$s: page number, %2$s: page total */
					$single_html.= sprintf( _x( 'Page <strong>%1$s</strong> of %2$s', 'Modules: Navigation: Breadcrumbs', 'gtheme' ),
						number_format_i18n( $page ),
						number_format_i18n( $numpages ) );
			}

			if ( ! empty( $single_html ) )
				$crumbs[] = $single_html;
		}

		if ( $args['post_title'] && ( $post_title = get_the_title( $post ) ) )
			$crumbs[] = '<a href="'.esc_url( apply_filters( 'the_permalink', get_permalink( $post ), $post ) )
				.'" title="'.gThemeContent::getTitleAttr( NULL, $post_title, $post ).'">'.$post_title.'</a>';

		$crumbs = array_filter( $crumbs );
		$count  = count( $crumbs );

		if ( $count < 2 )
			return;

		echo $args['before'].'<ol class="breadcrumb '.$args['class'].'">';

		foreach ( $crumbs as $offset => $crumb )
			echo '<li class="breadcrumb-item '.( ( $count - 1 ) == $offset ? ' active' : '' ).'">'.$crumb.'</li>';

		echo '</ol>'.$args['after'];
	}

	public static function breadcrumb_single( $atts = [] )
	{
		self::_dep( 'gThemeNavigation::breadcrumbSingle()' );
		self::breadcrumbSingle( $atts );
	}

	// home > archives > paged
	// bootstrap 3 compatible markup
	// @SEE: [get_the_archive_title()](https://developer.wordpress.org/reference/functions/get_the_archive_title/)
	public static function breadcrumbArchive( $atts = [] )
	{
		$args = self::atts( [
			'home'       => FALSE, // `home`/`network`/`Custom String`
			'home_title' => NULL,
			'no_prefix'  => NULL,
			'siblings'   => NULL, // TODO: siblings as bootstrap dropdown
			'item_class' => '-crumb', // TODO
			'strings'    => gThemeOptions::info( 'strings_breadcrumb_archive', [] ),
			'class'      => 'gtheme-breadcrumb',
			'before'     => '<nav class="nav-content nav-content-archive" aria-label="breadcrumb">',
			'after'      => '</nav>',
			'context'    => NULL,
		], $atts );

		// bailing!
		if ( FALSE === ( $archive = self::crumbArchive( $args ) ) )
			return;

		$crumbs = self::crumbHome( $args );

		if ( $archive )
			$crumbs = array_merge( $crumbs, (array) $archive );

		if ( is_paged() ) {

			// NOTE: we do not apply `no_prefix` on paged crumbs
			$template = empty( $args['strings']['paged'] )
				/* translators: `%s`: page number */
				? _x( 'Page <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['paged'];

			$crumbs[] = sprintf( $template, number_format_i18n( get_query_var( 'paged' ) ) );
		}

		$crumbs = array_filter( $crumbs );
		$count  = count( $crumbs );

		if ( ! $count )
			return;

		echo $args['before'].'<ol class="breadcrumb '.$args['class'].'">';

		foreach ( $crumbs as $offset => $crumb )
			echo '<li class="breadcrumb-item'.( ( $count - 1 ) == $offset ? ' active' : '' ).'">'.$crumb.'</li>';

		echo '</ol>'.$args['after'];
	}

	public static function breadcrumb_archive( $atts = [] )
	{
		self::_dep( 'gThemeNavigation::breadcrumbArchive()' );
		self::breadcrumbArchive( $atts );
	}

	public static function crumbHome( $args )
	{
		$crumbs = [];

		if ( empty( $args['home'] ) )
			return $crumbs;

		if ( is_null( $args['home_title'] ) )
			$args['home_title'] = gThemeOptions::info( 'logo_title', '' );

		if ( 'home' == $args['home'] ) {
			$crumbs[] = '<a href="'.esc_url( GTHEME_HOME ).'" rel="home" title="'.esc_attr( $args['home_title'] ).'">'.gThemeOptions::info( 'site_crumb' ).'</a>';

		} else if ( 'network' == $args['home'] ) {

			if ( is_main_site() ) {
				$crumbs[] = '<a href="'.esc_url( GTHEME_HOME ).'" rel="home" title="'.esc_attr( $args['home_title'] ).'">'.gThemeOptions::info( 'site_crumb' ).'</a>';
			} else {
				$crumbs[] = '<a href="'.esc_url( gThemeUtilities::home() ).'" title="'.esc_attr( $args['home_title'] ).'">'.gThemeOptions::info( 'blog_name' ).'</a>';
				$crumbs[] = '<a href="'.esc_url( GTHEME_HOME ).'" rel="home" title="'.esc_attr( gThemeOptions::getOption( 'frontpage_desc', '' ) ).'">'.gThemeOptions::info( 'site_crumb' ).'</a>';
			}

		} else {
			$crumbs[] = '<a href="'.esc_url( gThemeUtilities::home() ).'" rel="home" title="'.esc_attr( $args['home_title'] ).'">'.$args['home'].'</a>';
		}

		return apply_filters( 'gtheme_navigation_crumb_home', $crumbs, $args ); // OLD FILTER: `gtheme_breadcrumb_after_home`
	}

	// @REF: `get_the_archive_title()`
	public static function crumbArchive( $args )
	{
		$crumb     = [];
		$dropdown  = FALSE;
		$no_prefix = ! empty( $args['no_prefix'] );

		if ( is_front_page() || is_home() ) {

			$crumb = FALSE;

		} else if ( is_404() ) {

			$crumb = empty( $args['strings']['notfound'] )
				? _x( 'Not Found', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['notfound'];

		} else if ( is_category() ) {

			$title = _x( 'All Categories', 'Modules: Navigation: Breadcrumbs', 'gtheme' );
			$link  = self::getTaxonomyArchiveLink( 'category', '<a href="%s" title="'.esc_attr( $title ).'">' );

			$crumb = sprintf( $no_prefix ? '%2$s%1$s%3$s' : ( empty( $args['strings']['category'] )
				/* translators: `%1$s`: category title, `%2$s`: link markup start, `%3$s`: link markup end */
				? _x( '%2$sCategory%3$s Archives for <strong>%1$s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['category'] ),
			single_term_title( '', FALSE ), $link ?: '', $link ? '</a>': '' );

		} else if ( is_tag() ) {

			$title = _x( 'All Tags', 'Modules: Navigation: Breadcrumbs', 'gtheme' );
			$link  = self::getTaxonomyArchiveLink( 'post_tag', '<a href="%s" title="'.esc_attr( $title ).'">' );

			$crumb = sprintf( $no_prefix ? '%2$s%1$s%3$s' : ( empty( $args['strings']['tag'] )
				/* translators: `%1$s`: tag title, `%2$s`: link markup start, `%3$s`: link markup end */
				? _x( '%2$sTag%3$s Archives for <strong>%1$s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['tag'] ),
			single_term_title( '', FALSE ), $link ?: '', $link ? '</a>': '' );

		// } else if ( is_tax( GTHEME_PEOPLE_TAXONOMY ) ) {

		// 	$title = _x( 'All People', 'Modules: Navigation: Breadcrumbs', 'gtheme' );
		// 	$link  = self::getTaxonomyArchiveLink( GTHEME_PEOPLE_TAXONOMY, '<a href="%s" title="'.esc_attr( $title ).'">' );

		// 	$crumb = sprintf( $no_prefix ? '%2$s%1$s%3$s' : ( empty( $args['strings'][GTHEME_PEOPLE_TAXONOMY] )
		// 		/* translators: `%1$s`: person title, `%2$s`: link markup start, `%3$s`: link markup end */
		// 		? _x( '%2$sPeople%3$s Archives for <strong>%1$s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
		// 		: $args['strings'][GTHEME_PEOPLE_TAXONOMY] ),
		// 	single_term_title( '', FALSE ), $link ?: '', $link ? '</a>': '' );

		} else if ( is_tax() ) {

			if ( ! $taxonomy = get_query_var( 'taxonomy' ) ) {

				$queried = get_queried_object();

				// NOTE: in case overridden
				if ( is_a( $queried, 'WP_Term' ) )
					$taxonomy = $queried->taxonomy;
			}

			if ( $taxonomy && ( $object = get_taxonomy( $taxonomy ) ) ) {

				$queried  = get_queried_object();
				$main_tax = in_array( $object->name, array_values( gThemeTerms::getMainTaxonomies() ), TRUE );

				if ( ! empty( $args['siblings'] ) && $object->hierarchical && $main_tax )
					$dropdown = self::getTaxonomySiblingsDropdown( $queried );

				$link = self::getTaxonomyArchiveLink( $object->name,
					'<a href="%s" title="'.esc_attr( $object->labels->all_items ).'">' );

				if ( $no_prefix && ! $main_tax )
					$crumb[] = $link ? ( $link.$object->labels->name.'</a>' ) : $object->labels->name;

				if ( $object->hierarchical )
					$crumb = array_merge( $crumb, self::getTaxonomyParentCrumbs( $queried, $main_tax && ! empty( $args['siblings'] ) ) );

				if ( $no_prefix && ( $link || $dropdown ) )
					$template = '%4$s%3$s%5$s';

				else if ( $no_prefix )
					$template = '%4$s<span title="%1$s">%3$s</span>%5$s';

				else if ( ! empty( $args['strings'][$taxonomy] ) )
					$template = $args['strings'][$taxonomy];

				else if ( ! empty( $args['strings']['tax'] ) )
					$template = $args['strings']['tax'];

				else
					/* translators: `%1$s`: tax singular name, `%2$s`: tax plural name, `%3$s`: term title, `%4$s`: link markup start, `%5$s`: link markup end */
					$template = _x( '%4$s%1$s%5$s Archives for <strong>%3$s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' );

				$title = single_term_title( '', FALSE );
				$html  = vsprintf( $template, [
					$object->labels->singular_name,
					$object->labels->name,
					$dropdown ? ( '<a href="#" class="xx-dropdown-toggle" role="button" data-bs-toggle="dropdown">'.$title.'</a>' ) : $title,
					$no_prefix ? '' : ( $link ?: '' ),
					$no_prefix ? '' : ( $link ? '</a>': '' ),
				] );

				if ( $dropdown )
					$html = '<div class="dropdown d-inline">'.$html.$dropdown.'</div>';

				$crumb[] = $html;
			}

		} else if ( is_post_type_archive() ) {

			$crumb = sprintf( $no_prefix ? '%s' : ( empty( $args['strings']['posttype'] )
				/* translators: `%s`: post-type title */
				? _x( 'Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['posttype'] ),
			post_type_archive_title( '', FALSE ) );

		} else if ( is_author() ) {

			$default = gThemeOptions::getOption( 'default_user', 0 );
			$author  = (int) get_query_var( 'author' );

			if ( $default == $author )
				return FALSE;

			$crumb = sprintf( $no_prefix ? '%s' : ( empty( $args['strings']['author'] )
				/* translators: `%s`: author display name */
				? _x( 'Author Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['author'] ),
			get_the_author_meta( 'display_name', $author ) );

		} else if ( is_search() ) {

			$crumb = sprintf( $no_prefix ? '%s' : ( empty( $args['strings']['search'] )
				/* translators: `%s`: search query */
				? _x( 'Search Results for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['search'] ),
			''.get_search_query().'' );

		} else if ( is_day() ) {

			$crumb = sprintf( $no_prefix ? '%s' : ( empty( $args['strings']['day'] )
				/* translators: `%s`: Day */
				? _x( 'Daily Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['day'] ),
			get_the_date( gThemeOptions::info( 'date_format_day', _x( 'j M Y', 'Options: Defaults: Date Format: Day', 'gtheme' ) ) ) );

		} else if ( is_month() ) {

			$crumb = sprintf( $no_prefix ? '%s' : ( empty( $args['strings']['month'] )
				/* translators: `%s`: Month */
				? _x( 'Monthly Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['month'] ),
			get_the_date( 'F Y' ) );

		} else if ( is_year() ) {

			$crumb = sprintf( ( empty( $args['strings']['year'] )
				/* translators: `%s`: Year */
				? _x( 'Yearly Archives for <strong>%s</strong>', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['year'] ),
			get_the_date( 'Y' ) );

		} else {

			$crumb = empty( $args['strings']['archive'] )
				? _x( 'Archives', 'Modules: Navigation: Breadcrumbs', 'gtheme' )
				: $args['strings']['archive'];
		}

		return apply_filters( 'gtheme_navigation_crumb_archive', $crumb, $args );
	}

	public static function getTaxonomySiblingsDropdown( $queried )
	{
		if ( ! $queried )
			return FALSE;

		$default = gThemeTaxonomy::getDefaultTermID( $queried->taxonomy );
		$extra   = [
			'parent'     => $queried->parent,
			'hide_empty' => TRUE,
			'exclude'    => $default ? [ $default ] : '',
		];

		if ( ! $terms = gThemeTaxonomy::listTerms( $queried->taxonomy, 'all', $extra ) )
			return FALSE;

		$list = [];

		foreach ( $terms as $term )
			$list[] = sprintf( '<li><a class="dropdown-item%s" href="%s">%s</a></li>',
				$term->term_id == $queried->term_id ? ' active' : '',
				esc_url( get_term_link( $term ) ),
				sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ),
			);

		return '<ul class="dropdown-menu dropdown-menu-lg-end">'.join( "\n", $list ).'</ul>';
	}

	public static function getTaxonomyParentCrumbs( $queried, $siblings = FALSE )
	{
		if ( ! $queried || empty( $queried->parent ) )
			return [];

		$is_child = TRUE;
		$parents  = [];
		$current  = $queried->term_id;

		while ( $is_child ) {

			$term = get_term_by( 'id', (int) $current, $queried->taxonomy );

			if ( $term
				&& $parent = get_term_by( 'id', (int) $term->parent, $queried->taxonomy ) ) {

				$dropdown = self::getTaxonomySiblingsDropdown( $parent );

				$link = sprintf( '<a class="%s"%s href="%s">%s</a>',
					$dropdown ? 'xx-dropdown-toggle' : 'has-no-dropdown',
					$dropdown ? ' data-bs-toggle="dropdown"' : '',
					esc_url( get_term_link( $parent ) ),
					sanitize_term_field( 'name', $parent->name, $parent->term_id, $parent->taxonomy, 'display' ),
				);

				if ( $dropdown )
					$link = '<div class="dropdown d-inline">'.$link.$dropdown.'</div>';

				$parents[] = $link;

			} else {

				$is_child = FALSE;
			}

			if ( $term )
				$current = $term->parent;
		}

		return empty( $parents )
			? []
			: array_reverse( $parents );
	}

	public static function getTaxonomyArchiveLink( $taxonomy, $template = '%s' )
	{
		$link = apply_filters( 'gtheme_navigation_taxonomy_archive_link', FALSE, $taxonomy );

		return $link ? sprintf( $template, esc_url( $link ) ) : FALSE;
	}

	public static function paginateLinks()
	{
		self::_dep( 'gThemeNavigation::paginate()' );
		self::paginate();
	}
}
