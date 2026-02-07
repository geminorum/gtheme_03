<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeContent extends gThemeModuleCore
{

	public static function wrapOpen( $context = 'index', $extra = [], $tag = NULL )
	{
		$classes = array_merge( [
			'entry-wrap',
			'content-'.$context,
			'clearfix',
		], $extra );

		$post_id = get_the_ID();

		if ( is_null( $tag ) )
			$tag = gThemeOptions::info( 'content_wrap_tag', 'article' );

		echo '<'.$tag.( $post_id ? ' id="post-'.$post_id.'"' : '' ).' class="'.gThemeHTML::prepClass( self::getPostClass( $classes, $post_id ) ).'">';

		do_action( 'gtheme_content_wrap_open', $context );
	}

	public static function wrapClose( $context = 'index', $tag = NULL )
	{
		do_action( 'gtheme_content_wrap_close', $context );

		if ( is_null( $tag ) )
			$tag = gThemeOptions::info( 'content_wrap_tag', 'article' );

		echo '</'.$tag.'>';
	}

	public static function notFoundMessage( $before = '<p class="not-found">', $after = '</p>' )
	{
		$default = _x( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'Content: Not Found Message', 'gtheme' );

		if ( $message = gThemeOptions::info( 'message_notfound', $default ) )
			echo $before.gThemeText::wordWrap( $message ).$after;
	}

	public static function notFound( $context = NULL, $part = 'notfound', $location = NULL )
	{
		$base = gtheme_template_base();

		if ( is_null( $context ) )
			$context = $base; // apply_filters( 'gtheme_notfound_context', 'notfound', $part, $base );

		do_action( 'gtheme_notfound_before', $context, $part, $base );

		get_template_part( sprintf( $location ?? 'partials/%s', $part ), $context );

		do_action( 'gtheme_notfound_after', $context, $part, $base );
	}

	public static function post( $context = NULL, $part = 'content', $location = NULL )
	{
		$base = gtheme_template_base();

		if ( is_null( $context ) )
			$context = $base;

		do_action( 'gtheme_post_before', $context, $part, $base );

		get_template_part( sprintf( $location ?? '%s', $part ), $context );

		do_action( 'gtheme_post_after', $context, $part, $base );
	}

	public static function partial( $context = NULL, $part = 'item', $location = NULL )
	{
		$base = gtheme_template_base();

		if ( is_null( $context ) )
			$context = $base;

		do_action( 'gtheme_partial_before', $context, $part, $base );

		get_template_part( sprintf( $location ?? 'partials/%s', $part ), $context );

		do_action( 'gtheme_partial_after', $context, $part, $base );
	}

	// http://www.billerickson.net/code/wp_query-arguments/
	public static function query( $args = [], $expiration = GTHEME_CACHETTL )
	{
		if ( gThemeWordPress::isDev() )
			return new \WP_Query( $args );

		$key = md5( 'gtq_'.serialize( $args ) );

		if ( gThemeWordPress::isFlush() )
			delete_transient( $key );

		if ( FALSE === ( $query = get_transient( $key ) ) ) {
			$query = new \WP_Query( $args );
			set_transient( $key, $query, $expiration );
		}

		return $query;
	}

	public static function content( $before = '<div class="entry-content">', $after = '</div>', $edit = NULL )
	{
		if ( is_null( $edit ) )
			$edit = gThemeOptions::info( 'read_more_edit', FALSE );

		do_action( 'gtheme_content_wrap_before' );

		echo $before;

		if ( gThemeOptions::info( 'restricted_content', FALSE ) )
			self::restricted();
		else
			the_content( self::continueReading( ( $edit ? get_edit_post_link() : '' ) ) );

		if ( gThemeOptions::info( 'copy_disabled', FALSE ) )
			echo '<div class="copy-disabled"></div>'; // http://stackoverflow.com/a/23337329

		echo $after;

		do_action( 'gtheme_content_wrap_after' );
	}

	public static function row( $before = '', $after = '', $empty = FALSE )
	{
		if ( ! $post = get_post() )
			return;

		$title = get_the_title( $post );

		if ( ! $title && ! $empty )
			return;

		echo $before;

		echo gThemeHTML::tag( 'a', [
			'href'  => apply_filters( 'the_permalink', get_permalink( $post ), $post ),
			'title' => self::getTitleAttr( NULL, $title, $post ),
			'class' => '-link -permalink',
		], gThemeText::wordWrap( $title ) );

		echo $after;
	}

	public static function rows( $context, $before = '', $after = '', $extra = [], $count = NULL )
	{
		echo $before;

		$cache = new gThemeFragmentCache( sprintf( 'rows_%s_paged_%d', $context, max( 1, get_query_var( 'paged' ) ) ) );

		if ( ! $cache->output() ) {

			$query = new \WP_Query( array_merge( [
				'post_type'      => apply_filters( 'gtheme_content_rows_posttypes', gThemeOptions::info( 'rows_'.$context.'_posttypes', [ 'post' ] ), $context ),
				'posts_per_page' => $count ?? gThemeCounts::get( $context, get_option( 'posts_per_page', 10 ) ),
			], $extra ) );

			if( $query->have_posts() ) {

				gtheme_reset_post_class();
				echo '<div class="-list-wrap -rows -rows-'.$context.'"><ul class="-items">';

				while ( $query->have_posts() ) {

					$query->the_post();

					echo '<li>';
						get_template_part( 'row', $context );
						echo '<span class="-dummy"></span>';
					echo '</li>';
				}

				echo '</ul></div>';
				wp_reset_postdata();
			}

			$cache->store( FALSE );

		} else {

			$cache->discard();
		}

		echo $after;
	}

	public static function masonry( $context, $before = '', $after = '', $extra = [], $count = NULL )
	{
		if ( empty( $context ) )
			return FALSE;

		$brick_class = gThemeOptions::info( 'masonry_brick_class', gThemeTemplate::defaultItemClass( $context ) );
		$grid_class  = gThemeOptions::info( 'masonry_grid_class', gThemeTemplate::defaultWrapClass( $context ) );
		$selector    = gThemeOptions::info( 'masonry_css_selector', 'gtheme-masonry' ); // FALSE to disable `enqueueMasonry()`

		echo $before;
		printf( '<div class="%s %s -masonry-grid -%s">', $grid_class, $selector ?: 'gtheme-masonry', $context );
		echo '<!-- OPEN: MASONRY: `'.$context.'` -->'."\n";

		$cache = new gThemeFragmentCache( sprintf( 'masonry_%s_paged_%d', $context, max( 1, get_query_var( 'paged' ) ) ) );
		$extra = $extra ?? gThemeTerms::getQueryNoFrontExtra();

		if ( ! $cache->output() ) {

			$query = new \WP_Query( array_merge( [
				'posts_per_page' => $count ?? gThemeCounts::get( $context, get_option( 'posts_per_page', 10 ) ),
				'post__not_in'   => gThemeFrontPage::getDisplayed(),
			], $extra ) );

			if ( $query->have_posts() ) {

				printf( '<div class="%s -masonry-sizer" style="display:none;"></div>', $brick_class );
				gtheme_reset_post_class();

				while ( $query->have_posts() ) {
					$query->the_post();
					printf( '<div class="%s -masonry-brick">', $brick_class );

						self::partial( $context );

					echo '</div>';
					gThemeFrontPage::addDisplayed();
				}

				wp_reset_postdata();

				$cache->store( FALSE );

			} else {

				$cache->discard();
			}
		}

		echo "\n".'<!-- CLOSE: MASONRY: `'.$context.'` -->'."\n";
		echo '</div>';
		echo $after;

		if ( $selector )
			gThemeUtilities::enqueueMasonry( $selector );

		return TRUE;
	}

	public static function recent( $context, $before = '', $after = '', $extra = [], $count = NULL )
	{
		if ( empty( $context ) )
			return FALSE;

		$queried    = get_queried_object_id() ?: 0;
		$item_class = gThemeOptions::info( 'recent_item_class', gThemeTemplate::defaultItemClass( $context ) );
		$wrap_class = gThemeOptions::info( 'recent_wrap_class', gThemeTemplate::defaultWrapClass( $context ) );
		$orderby    = gThemeOptions::info( 'recent_orderby', 'menu_order date' );

		echo $before;

		$cache = new gThemeFragmentCache( sprintf( 'recent_%s_item_%d', $context, $queried ) );

		if ( ! $cache->output() ) {

			$query_args = [
				'post_status'    => 'publish',
				'post_type'      => apply_filters( 'gtheme_content_recent_posttypes', gThemeOptions::info( sprintf( 'recent_%s_posttypes', $context ), [ 'post' ] ), $context ),
				'posts_per_page' => $count ?? gThemeCounts::get( sprintf( 'recent_%s', $context ), get_option( 'posts_per_page', 10 ) / 2 ),
				'post__not_in'   => gThemeFrontPage::getDisplayed( $queried ?: [] ),

				'ignore_sticky_posts'    => TRUE,
				'no_found_rows'          => TRUE,
				'update_post_term_cache' => FALSE,
				'update_post_meta_cache' => FALSE,
			];

			if ( $orderby )
				$query_args['orderby'] = $orderby;

			$query = new \WP_Query( array_merge( $query_args, $extra ) );

			if ( $query->have_posts() ) {

				printf( '<div class="%s -recent-wrap -%s" data-queried="%d">', $wrap_class, $context, $queried );

				gtheme_reset_post_class();

				while ( $query->have_posts() ) {
					$query->the_post();
					printf( '<div class="%s -recent-item">', $item_class );

						self::partial( $context );

					echo '</div>';
					gThemeFrontPage::addDisplayed();
				}

				wp_reset_postdata();

				echo '</div>';

				$cache->store( FALSE );

			} else {

				return $cache->discard( $after );
			}
		}

		echo $after;
		return TRUE;
	}

	public static function related( $context, $before = '', $after = '', $extra = [], $count = NULL )
	{
		if ( empty( $context ) )
			return FALSE;

		if ( ! is_singular() && ! is_single() )
			return FALSE;

		if ( ! $queried = get_queried_object_id() )
			return FALSE;

		$item_class = gThemeOptions::info( 'related_item_class', gThemeTemplate::defaultItemClass( $context ) );
		$wrap_class = gThemeOptions::info( 'related_wrap_class', gThemeTemplate::defaultWrapClass( $context ) );
		$taxonomy   = gThemeOptions::info( 'related_taxonomy', 'post_tag' );

		echo $before;

		$cache = new gThemeFragmentCache( sprintf( 'related_%s_item_%d', $context, $queried ) );

		if ( ! $cache->output() ) {

			// NOTE: hits cached terms for the post
			$terms = get_the_terms( $queried, $taxonomy );

			if ( is_wp_error( $terms ) || empty( $terms ) )
				return $cache->discard( $after );

			$query_args = [
				'tax_query' => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'id',
						'terms'    => wp_list_pluck( $terms, 'term_id' ),
						'operator' => 'IN',
					],
				],

				'post_status'    => 'publish',
				'post_type'      => apply_filters( 'gtheme_content_related_posttypes', gThemeOptions::info( sprintf( 'related_%s_posttypes', $context ), [ 'post' ] ), $context ),
				'posts_per_page' => $count ?? gThemeCounts::get( sprintf( 'related_%s', $context ), get_option( 'posts_per_page', 10 ) / 2 ),
				'post__not_in'   => gThemeFrontPage::getDisplayed( $queried ),

				'ignore_sticky_posts'    => TRUE,
				'no_found_rows'          => TRUE,
				'update_post_term_cache' => FALSE,
				'update_post_meta_cache' => FALSE,
			];

			if ( GTHEME_SYSTEMTAGS && taxonomy_exists( GTHEME_SYSTEMTAGS ) ) {
				$query_args['tax_query']['relation'] = 'AND';
				$query_args['tax_query'][] = [
					'taxonomy' => GTHEME_SYSTEMTAGS,
					'field'    => 'slug',
					'terms'    => 'no-related',
					'operator' => 'NOT IN',
				];
			}

			$query = new \WP_Query( array_merge( $query_args, $extra ) );

			if ( $query->have_posts() ) {

				printf( '<div class="%s -related-wrap -%s" data-queried="%d">', $wrap_class, $context, $queried );

				gtheme_reset_post_class();

				while ( $query->have_posts() ) {
					$query->the_post();
					printf( '<div class="-related-item %s">', $item_class );

						self::partial( $context );

					echo '</div>';
					gThemeFrontPage::addDisplayed();
				}

				wp_reset_postdata();

				echo '</div>';

				$cache->store( FALSE );

			} else {

				return $cache->discard( $after );
			}
		}

		echo $after;
		return TRUE;
	}

	public static function byline( $post = NULL, $before = '', $after = '', $verbose = TRUE, $fallback = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return '';

		// dummy post
		if ( ! $post->ID )
			return '';

		if ( 'page' == $post->post_type )
			return '';

		if ( $html = gThemeEditorial::byline( [ 'echo' => FALSE ], $post ) ) {

			if ( $verbose )
				echo $before.$html.$after;

			return $before.$html.$after;
		}

		if ( $html = gThemeEditorial::metaByline( $post, [ 'echo' => FALSE ] ) ) {

			if ( $verbose )
				echo $before.'<span class="-byline">'.$html.'</span>'.$after;

			return $before.'<span class="-byline">'.$html.'</span>'.$after;
		}

		$args = [ 'id' => $post->ID, 'echo' => FALSE, 'context' => 'single' ];

		if ( gThemeOptions::supports( 'gpeople', TRUE )
			&& is_callable( [ 'gPeopleRemoteTemplate', 'post_byline' ] ) ) {

			if ( $html = \gPeopleRemoteTemplate::post_byline( $post, $args ) ) {

				if ( $verbose )
					echo $before.$html.$after;

				return $before.$html.$after;
			}
		}

		// NOTE: DEPRECATED
		if ( gThemeOptions::supports( 'geditorial-meta', TRUE ) ) {

			if ( $html = gThemeEditorial::author( $args ) ) {

				if ( $verbose )
					echo $before.$html.$after;

				return $before.$html.$after;
			}
		}

		if ( $html = apply_filters( 'gtheme_content_byline_empty', '', $post ) ) {

			if ( $verbose )
				echo $before.$html.$after;

			return $before.$html.$after;
		}

		if ( is_null( $fallback ) )
			$fallback = gThemeOptions::info( 'byline_fallback', TRUE );

		if ( ! $fallback )
			return '';

		if ( $html = gThemeTemplate::author( $post, FALSE ) ) {

			if ( $verbose )
				echo $before.$html.$after;

			return $before.$html.$after;
		}

		return '';
	}

	public static function date( $before = '<div class="entry-date">', $after = '</div>' )
	{
		gThemeDate::once( [
			'before' => $before,
			'after'  => $after,
			'format' => gThemeOptions::info( 'date_format_content', _x( 'Y/j/m', 'Options: Defaults: Date Format: Content', 'gtheme' ) ),
			'echo'   => TRUE,
		] );
	}

	// simplified `get_post()`
	public static function getPost( $post = NULL, $output = OBJECT, $filter = 'raw' )
	{
		if ( $post instanceof \WP_Post )
			return $post;

		// handling dummy posts!
		if ( '-9999' == $post )
			$post = NULL;

		return get_post( $post, $output, $filter );
	}

	public static function getPostLink( $post, $fallback = NULL, $statuses = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return FALSE;

		$status = get_post_status( $post );

		if ( is_null( $statuses ) )
			$statuses = [ 'publish', 'inherit' ]; // MAYBE: `apply_filters()`

		if ( ! in_array( $status, (array) $statuses, TRUE ) )
			return $fallback;

		return apply_filters( 'the_permalink', get_permalink( $post ), $post );
	}

	public static function getPostTitle( $post, $fallback = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return Plugin::na( FALSE );

		$title = apply_filters( 'the_title', $post->post_title, $post->ID );

		if ( ! empty( $title ) )
			return $title;

		if ( FALSE === $fallback )
			return '';

		if ( is_null( $fallback ) )
			return _x( '(untitled)', 'Modules: Content: Post Title', 'gtheme' );

		return $fallback;
	}

	// core duplicate for performance concerns
	// @REF: `get_post_class()`
	// TODO: add slugs form `gThemeTerms::getMainTaxonomy()`
	public static function getPostClass( $class = '', $post_id = NULL )
	{
		$post = self::getPost( $post_id );

		$classes = $class
			? array_map( [ 'gThemeHTML', 'sanitizeClass' ], gThemeHTML::attrClass( $class ) )
			: [];

		if ( ! $post )
			return $classes;

		if ( ! empty( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query']->current_post > -1 )
			$classes[] = sprintf( 'loop-index-%d', $GLOBALS['wp_query']->current_post );

		$classes[] = gThemeHTML::sanitizeClass( 'type-'.$post->post_type );

		if ( has_post_thumbnail( $post ) )
			$classes[] = 'post-has-thumbnail';

		else
			$classes[] = 'post-has-no-thumbnail';

		if ( post_password_required( $post->ID ) )
			$classes[] = 'post-password-required';

		else if ( ! empty( $post->post_password ) )
			$classes[] = 'post-password-protected';

		if ( $format = get_post_format( $post ) )
			$classes[] = gThemeHTML::sanitizeClass( 'format-'.$format );

		else
			$classes[] = 'format-standard';

		$classes[] = 'hentry';

		if ( $woocommerce = gThemeWooCommerce::isPage() )
			$classes[] = sprintf( 'post-is-woocommerce post-%s', $woocommerce );

		// core default filter
		return array_unique( apply_filters( 'post_class', $classes, $class, $post->ID ) );
	}

	// FIXME: DEPRECATED
	public static function continue_reading( $edit = '', $scope = '', $permalink = FALSE, $title_att = FALSE )
	{
		self::_dep( 'gThemeContent::continueReading()' );
		return self::continueReading( $edit, $scope, $permalink, $title_att );
	}

	public static function continueReading( $edit = '', $scope = '', $link = FALSE, $post_title = FALSE )
	{
		if ( FALSE === $post_title )
			$post_title = strip_tags( get_the_title() );

		if ( FALSE === $link )
			$link = get_permalink();

		if ( ! empty( $edit ) )
			$edit = vsprintf( ' <a href="%1$s" title="%3$s" class="%4$s">%2$s</a>', [
				$edit,
				_x( 'Edit', 'Content: Read More Edit', 'gtheme' ),
				_x( 'Jump to edit page', 'Content: Read More Edit Title', 'gtheme' ),
				'post-edit-link',
			] );

		$template = gThemeOptions::info( 'template_read_more', ' <a %6$s href="%1$s" aria-label="%3$s" class="%4$s">%2$s</a>%5$s' );
		$text     = gThemeOptions::info( 'read_more_text', _x( 'Read more&nbsp;<span class="excerpt-link-hellip">&hellip;</span>', 'Content: Read More Text', 'gtheme' ) );
		/* translators: `%s`: post title */
		$title    = gThemeOptions::info( 'read_more_title', _x( 'Continue reading &ldquo;%s&rdquo; &hellip;', 'Content: Read More Title', 'gtheme' ) );

		return vsprintf( $template, [
			esc_url( $link ),
			$text,
			esc_attr( sprintf( $title, $post_title ) ),
			'excerpt-link',
			$edit,
			$scope,
		] );
	}

	// OLD: `gtheme_the_title_attribute()`
	// OLD: `gThemeContent::title_attr()`
	// `$template`: `FALSE` for short-link, `NULL` for permanent
	public static function getTitleAttr( $template = NULL, $title = NULL, $post = NULL, $empty = '' )
	{
		if ( FALSE === $title )
			return '';

		$post = self::getPost( $post );

		if ( ! $post && ! $title )
			return $empty;

		if ( is_null( $title ) )
			$title = trim( strip_tags( get_the_title( $post ) ) );

		if ( is_null( $template ) )
			/* translators: %s: post title */
			$attr = _x( 'Permanent link to &ndash;%s&ndash;', 'Content: Title Attr','gtheme' );

		else if ( FALSE === $template )
			/* translators: %s: post title */
			$attr = _x( 'Short link for &ndash;%s&ndash;', 'Content: Title Attr', 'gtheme' );

		else
			$attr = $template;

		return sprintf( $attr, $title );
	}

	// FIXME: DEPRECATED, BACK-COMP ONLY
	// CAUTION: used in child themes
	public static function title_attr( $verbose = TRUE, $title = NULL, $template = NULL, $empty = '' )
	{
		$attr = self::getTitleAttr( $template, $title, NULL, $empty );

		if ( ! $verbose )
			return esc_attr( $attr );

		echo esc_attr( $attr );
	}

	public static function isRestricted()
	{
		return apply_filters( 'gtheme_content_restricted', ! is_user_logged_in() );
	}

	public static function restricted( $stripteser = NULL, $message = NULL, $before = '<div class="restricted-content">', $after = '</div>' )
	{
		if ( self::isRestricted() ) {

			$GLOBALS['more'] = 0;

			the_content( FALSE );

			if ( is_null( $message ) )
				$message = gThemeOptions::info( 'restricted_message', '' );

			if ( $message )
				echo $before.$message.$after;

		} else {

			// not caching the full article!
			gThemeWordPress::doNotCache();

			if ( is_null( $stripteser ) )
				$stripteser = ! gThemeOptions::info( 'restricted_teaser', FALSE );

			the_content( self::continueReading(), $stripteser );
		}
	}

	// @REF: https://developer.wordpress.org/?p=1394#comment-338
	public static function teaser( $before = '<div class="entry-teaser">', $after = '</div>', $link = NULL, $edit = NULL )
	{
		$GLOBALS['more'] = 0;

		if ( is_null( $edit ) )
			$edit = gThemeOptions::info( 'read_more_edit', FALSE );

		if ( is_null( $link ) )
			$link = self::continueReading( ( $edit ? get_edit_post_link() : '' ) );

		echo $before;
			the_content( $link );
		echo $after;
	}

	// FIXME: DEPRECATED: DROP THIS
	// based on WP core : `get_the_content()`
	public static function teaser_OLD( $fallback = TRUE, $verbose = TRUE )
	{
		global $more, $page, $pages;

		if ( post_password_required() )
			return get_the_password_form();

		if ( $page > count( $pages ) )
			$page = count( $pages );

		$content = $pages[$page-1];
		if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
			$content = explode( $matches[0], $content, 2 );
			$content = $content[0];

			if ( ! $more )
				$content = force_balance_tags( $content );

		} else if ( $fallback ) {
			$content = get_the_content();
		} else {
			return NULL;
		}

		$output = apply_filters( 'the_content', $content );
		$output = str_replace(']]>', ']]&gt;', $output );

		if ( ! $verbose )
			return $output;
		echo $output;
	}

	// TODO: support for Editorial `Dashboard` Meta field
	// ANCESTOR: `gtheme_the_excerpt()`
	public static function excerpt( $atts = 'itemprop="description" ', $b = '<div class="entry-summary">', $a = '</div>', $only = FALSE, $excerpt_length = FALSE )
	{
		if ( ! $post = get_post() )
			return;

		if ( post_password_required() )
			return;

		if ( $only && empty( $post->post_excerpt ) )
			return;

		if ( $excerpt_length )
			// MIGHT be a problem since we bypass other filters too
			$excerpt = apply_filters( 'the_excerpt', gTheme()->filters->get_the_excerpt( gThemeL10N::html( trim( $post->post_excerpt ) ), $excerpt_length ) );
		else
			$excerpt = apply_filters( 'the_excerpt', get_the_excerpt() );

		if ( ! empty( $atts ) )
			$excerpt = preg_replace( '/(<p\b[^><]*)>/i', '$1 '.$atts.'>', $excerpt ); // http://stackoverflow.com/a/3983870/642752

		echo $b.apply_shortcodes( $excerpt ).$a;
	}

	// FIXME: DEPRECATED
	public static function postActions( $before = '<li class="-action entry-action %s">', $after = '</li>', $list = TRUE, $icon = NULL )
	{
		self::_dev_dep( 'gThemeContent::renderActions()' );
		return self::renderActions( NULL, $before, $after, $list, $icon );
	}

	public static function renderActions( $post = NULL, $before = '<li class="-action entry-action %s">', $after = '</li>', $list = TRUE, $icon = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return;

		// dummy post
		if ( ! $post->ID )
			return;

		$actions = [];

		if ( is_array( $list ) ) {

			$actions = $list;

		} else if ( TRUE === $list || NULL === $list ) {

			$posttype = gThemeOptions::info( sprintf( 'post_actions_for_%s', $post->post_type ), NULL );

			if ( FALSE === $posttype )
				return; // bailing!

			if ( is_null( $posttype ) )
				$posttype = gThemeOptions::info( 'post_actions', NULL );

			if ( FALSE === $posttype )
				return; // bailing!

			$actions = $posttype ?: [
				'printlink',
				'shortlink',
				'editorial_ical',
				'bootstrap_qrcode',
				'addtoany',
				'comments_link',
				'edit_post_link',
				'editorial_estimated',
				// 'editorial_meta_source',
			];
		}

		if ( is_null( $icon ) )
			$icon = gThemeOptions::info( 'post_actions_icons', FALSE );

		$actions = apply_filters( 'gtheme_content_actions', $actions, $post, $icon );

		if ( FALSE === $actions )
			return; // bailing!

		do_action( 'gtheme_action_links_before', $before, $after, $actions, $icon );

		foreach ( $actions as $action ) {

			if ( is_array( $action ) ) {

				if ( is_callable( $action ) )
					call_user_func_array( $action, [ $before, $after, $icon, $post ] );

			} else if ( $action ) {

				self::renderSingleAction( $action, $before, $after, $icon, $post );
			}
		}

		do_action( 'gtheme_action_links', $before, $after, $actions, $icon );
	}

	// FIXME: DEPRECATED
	public static function doAction( $action, $before, $after, $icon = FALSE )
	{
		self::_dep( 'gThemeContent::renderSingleAction()' );
		return self::renderSingleAction( $action, $before, $after, $icon );
	}

	public static function renderSingleAction( $action, $before, $after, $icon = FALSE, $post = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return;

		switch ( $action ) {

			case 'byline':

				self::byline( $post, sprintf( $before, '-action -byline' ), $after );
				break;

			case 'textsize_buttons':
			case 'textsize_buttons_nosep':

				self::text_size_buttons(
					sprintf( $before, 'textsize-buttons hide-if-no-js hidden-print' ), $after,
					( 'textsize_buttons_nosep' == $action ? FALSE : 'def' ),
					( $icon ? self::getGenericon( 'zoom' ) : 'def' ),
					( $icon ? self::getGenericon( 'unzoom' ) : 'def' )
				);

				break;

			case 'textjustify_buttons':
			case 'textjustify_buttons_nosep':

				self::justify_buttons(
					sprintf( $before, 'textjustify-buttons hide-if-no-js hidden-print' ), $after,
					( 'textjustify_buttons_nosep' == $action ? FALSE : 'def' ),
					( $icon ? self::getGenericon( 'minimize' ) : 'def' ),
					( $icon ? self::getGenericon( 'previous' ) : 'def' )
				);

				break;

			case 'a2a_dd':
			case 'addtoany':

				self::addtoany( ( $icon ? self::getGenericon( 'share' ) : _x( 'Share This', 'Modules: Content: Action', 'gtheme' ) ),
					$post, sprintf( $before, 'addtoany post-share-link hide-if-no-js hidden-print' ), $after
				);

				break;

			case 'addthis':

				self::addthis( ( $icon ? self::getGenericon( 'share' ) : _x( 'Share This', 'Modules: Content: Action', 'gtheme' ) ),
					$post, sprintf( $before, 'addthis post-share-link hide-if-no-js hidden-print' ), $after
				);

				break;

			case 'pocket_button':

				self::pocket( _x( 'Pocket', 'Modules: Content: Action', 'gtheme' ),
					$post, sprintf( $before, 'pocket post-share-button hide-if-no-js hidden-print' ), $after
				);

				break;

			case 'printlink':

				// bail if disabled
				if ( ! GTHEME_PRINT_QUERY )
					break;

				self::printLink( ( $icon ? self::getGenericon( 'print' ) : _x( 'Print Version', 'Modules: Content: Action', 'gtheme' ) ),
					$post, sprintf( $before, '-action -printlink hidden-print' ), $after,
					FALSE // self::getTitleAttr( FALSE )
				);

				break;

			case 'shortlink':

				self::shortlink( ( $icon ? self::getGenericon( 'link' ) : _x( 'Short Link', 'Modules: Content: Action', 'gtheme' ) ),
					$post, sprintf( $before, '-action -shortlink hidden-print' ), $after,
					self::getTitleAttr( FALSE )
				);

				break;

			case 'bootstrap_qrcode':

				self::bootstrapQRCode( ( $icon ? self::getGenericon( 'fullscreen' ) : _x( 'QR-Code', 'Modules: Content: Action', 'gtheme' ) ),
					$post, sprintf( $before, '-action -qrcode -bootstrap-qrcode hide-if-no-js hidden-print dropdown' ), $after,
					self::getTitleAttr(
						/* translators: `%s`: post title */
						_x( 'QR-Code for &ndash;%s&ndash;', 'Content: Title Attr', 'gtheme' )
					)
				);

				break;

			case 'comments_link':
			case 'comments_link_feed':

				// FIXME: separate the logic

				if ( comments_open() ) {

					printf( $before, 'comments-link -print-hide' );

					// if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) )
					// 	comments_popup_link(
					// 		_x( 'Leave a comment', 'Modules: Content: Action', 'gtheme' ),
					// 		_x( '1 Comment', 'Modules: Content: Action', 'gtheme' ),
					// 		_x( '% Comments', 'Modules: Content: Action', 'gtheme' )
					// 	);

					if ( is_singular() || is_single() ) {

						$respond  = '#respond';
						$comments = '#comments';
						$class    = 'scroll';

					} else {

						$link     = get_permalink( $post );
						$respond  = $link.'#respond';
						$comments = $link.'#comments';
						$class    = ''; // hastip
					}

					if ( $icon )
						printf( '<a href="%2$s" class="%1$s">%3$s</a>', $class, ( get_comments_number( $post ) ? $comments : $respond ), self::getGenericon( 'comment' ) );

					else
						echo get_comments_number_text(
							/* translators: %1$s: class name, %2$s: comments number, %3$s: comment url */
							sprintf( _x( '<a href="%3$s" class="%1$s">Your Comment</a>', 'Modules: Content: Action', 'gtheme' ), $class, '', $respond ),
							/* translators: %1$s: class name, %2$s: comments number, %3$s: comment url */
							sprintf( _x( '<a href="%3$s" class="%1$s">One Comment</a>', 'Modules: Content: Action', 'gtheme' ), $class, '', $comments ),
							/* translators: %1$s: class name, %2$s: comments number, %3$s: comment url */
							sprintf( _x( '<a href="%3$s" class="%1$s">%2$s Comments</a>', 'Modules: Content: Action', 'gtheme' ), $class, '%', $comments ),
							$post
						);

					if ( 'comments_link_feed' == $action ) {

						if ( $icon )
							printf( '<a href="%2$s" class="%1$s">%3$s</a>', 'comments-link-rss', get_post_comments_feed_link( $post->ID ), self::getGenericon( 'feed' ) );

						else
							/* translators: %1$s: comments rss link, %2$s: title attr, %3$s: class name */
							printf( _x( ' <small><small>(<a href="%1$s" title="%2$s" class="%3$s"><abbr title="Really Simple Syndication">RSS</abbr></a>)</small></small>', 'Modules: Content: Action', 'gtheme' ),
								get_post_comments_feed_link( $post->ID ),
								_x( 'Feed for this post\'s comments', 'Modules: Content: Action', 'gtheme' ),
								'comments-link-rss'
							);
					}

					echo $after;
				}

				break;

			case 'edit_post_link':
			case 'edit':

				edit_post_link(
					( $icon ? self::getGenericon( 'edit' ) : _x( 'Edit', 'Modules: Content: Action', 'gtheme' ) ),
					sprintf( $before, 'post-edit-link post-edit-link-li hidden-print' ), $after, $post
				);

				break;

			case 'tag_list': // DEPRECATED

				if ( is_object_in_taxonomy( $post->post_type, 'post_tag' ) ) {

					$html = get_the_tag_list(
						sprintf( $before, 'tag-links' ).gThemeOptions::info( 'before_tag_list', '' ),
						gThemeOptions::info( 'term_sep', _x( ', ', 'Options: Separator: Term', 'gtheme' ) ),
						$after,
						$post->ID
					);

					if ( ! is_wp_error( $html ) )
						echo $html;
				}

				break;

			case 'tags':

				if ( is_object_in_taxonomy( $post->post_type, 'post_tag' ) )
					gThemeTerms::theList( 'post_tag', sprintf( $before, 'tag-term' ), $after, $post );

				break;

			case 'cat_list': // DEPRECATED

				if ( is_object_in_taxonomy( $post->post_type, 'category' ) ) {

					$html = get_the_category_list( gThemeOptions::info( 'term_sep', _x( ', ', 'Options: Separator: Term', 'gtheme' ) ), '', $post->ID );

					if ( $html )
						echo sprintf( $before, 'cat-links' ).gThemeOptions::info( 'before_cat_list', '' ).$html.$after;
				}

				break;

			case 'categories':

				if ( $taxonomy = gThemeTerms::getMainTaxonomy( $post->post_type, FALSE ) )
					gThemeTerms::theList( $taxonomy, sprintf( $before, $taxonomy.'-term' ), $after, $post, TRUE );

				else if ( is_object_in_taxonomy( $post->post_type, 'category' ) )
					gThemeTerms::theList( 'category', sprintf( $before, 'category-term' ), $after, $post, TRUE );

				break;

			case 'primary_term':

				gThemeTerms::linkPrimary( sprintf( $before, 'primary-term' ), $after, $post );

				break;

			case 'the_date':
			case 'date':

				gThemeDate::date( [
					'post'    => $post,
					'before'  => sprintf( $before, 'the-date' ),
					'after'   => $after,
					'text'    => $icon ? self::getGenericon( 'edit' ) : NULL,
					'timeago' => FALSE, // TODO: add another action for time-ago
				] );

				break;

			case 'editorial_published':

				// TODO: make link to search with meta

				gThemeEditorial::metaPublished( $post, [
					'before' => sprintf( $before, 'entry-published' ),
					'after'  => $after,
					'filter' => [ 'gThemeL10N', 'str' ],
				] );

				break;

			case 'editorial_label':

				gThemeEditorial::label( [
					'id'     => $post,
					'before' => sprintf( $before, 'entry-label' ),
					'after'  => $after,
				] );

				break;

			case 'editorial_estimated':

				gThemeEditorial::estimated( [
					'post'   => $post,
					'before' => sprintf( $before, 'entry-estimated' ),
					'after'  => $after,
					'prefix' => '',
				] );

				break;

			case 'editorial_ical':

				gThemeEditorial::calendarLink( [
					'post'   => $post,
					'before' => sprintf( $before, '-calendar-link hidden-print' ),
					'after'  => $after,
					'text'   => $icon ? self::getGenericon( 'calendar' ) : NULL,
				] );

				break;

			case 'editorial_meta_source':

				gThemeEditorial::theSource( [
					'id'            => $post,
					'before'        => sprintf( $before, 'entry-meta-source -print-hide' ),
					'after'         => $after,
					'title_swap'    => TRUE,
					'title_default' => gThemeOptions::info( 'meta_source_title', _x( 'Source', 'Content: Meta Source', 'gtheme' ) ),
				] );
		}
	}

	// DEPRECATED: use SVG icons
	public static function getGenericon( $icon = 'edit', $tag = 'div' )
	{
		return '<'.$tag.' class="genericon genericon-'.$icon.'"></'.$tag.'>';
	}

	public static function printLink( $text, $post = NULL, $before = '', $after = '', $title = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return;

		if ( ! in_array( $post->post_type, (array) gThemeOptions::info( 'print_posttypes', [ 'post', 'entry' ] ) ) )
			return;

		if ( ! $permalink = get_permalink( $post ) )
			return;

		$endpoint = GTHEME_PRINT_QUERY ?: 'print';

		if ( $GLOBALS['wp_rewrite']->using_permalinks()
			&& ! in_array( $post->post_status, [ 'draft', 'pending', 'auto-draft', 'future' ] ) ) {

			$printlink = gThemeURL::trail( $permalink ).$endpoint;

		} else {

			$printlink = add_query_arg( [ $endpoint => '' ], $permalink );
		}

		echo $before.gThemeHTML::tag( 'a', [
			'href'  => $printlink,
			'title' => $title ?: FALSE,
			'rel'   => 'print',
			'data'  => [
				'toggle'    => 'tooltip',
				'bs-toggle' => 'tooltip',
				'id'        => $post->ID,
			],
		], $text ).$after;
	}

	public static function shortlink( $text, $post = NULL, $before = '', $after = '', $title = NULL )
	{
		if ( ! $post = self::getPost( $post ) )
			return;

		if ( ! $shortlink = wp_get_shortlink( $post->ID ) )
			return;

		echo $before.gThemeHTML::tag( 'a', [
			'href'  => $shortlink,
			'title' => $title ?: FALSE,
			'rel'   => 'shortlink',
			'data'  => [
				'toggle'    => 'tooltip',
				'bs-toggle' => 'tooltip',
				'id'        => $post->ID,
			],
		], $text ).$after;
	}

	// @REF: http://nicholaelaw.github.io/demo-qr-code-in-tooltip/
	// @REF: https://qr-creator.com/plugin.php
	// @REF: https://developers.google.com/chart/infographics/docs/qr_codes
	// NOTE: requires bootstrap for dropdown
	public static function bootstrapQRCode( $text, $post = NULL, $before = '', $after = '', $title = NULL )
	{
		static $enqueued = FALSE;

		if ( ! gThemeOptions::info( 'bootstrap_version' ) )
			return;

		if ( ! $post = self::getPost( $post ) )
			return;

		if ( $shortlink = wp_get_shortlink( $post->ID ) )
			$url = $shortlink;

		else if ( $premalink = get_permalink( $post ) )
			$url = $premalink;

		$rtl     = gThemeOptions::info( 'rtl', FALSE );
		$float   = gThemeOptions::info( 'bootstrap_qrcode_float', $rtl ? 'left' : 'right' );
		$size    = gThemeOptions::info( 'bootstrap_qrcode_size', 148 );
		$loading = gThemeOptions::info( 'bootstrap_qrcode_loading', sprintf( '<small>%s</small>', _x( 'Loading&hellip;', 'Qr-Code', 'gtheme' ) ) );

		$dropdown = '<div class="dropdown-menu dropdown-menu-'.$float.' ';
		$dropdown.= 'p1-0 text-center bg-white rounded-0 -qrcode-wrap" style="width:'.( $size + 10 ).'px;height:'.( $size + 10 ).'px;min-width:unset;text-align:center;padding-top:5px"';
		if ( $title ) $dropdown.= ' data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="bottom" data-bs-placement="bottom" title="'.esc_attr( $title ).'"';
		$dropdown.= '>'.$loading.'</div>';

		echo $before.gThemeHTML::tag( 'a', [
			'href'  => $url, // MAYBE: direct link to qr-code image
			'title' => self::getTitleAttr( ( $shortlink ? FALSE : NULL ), NULL, $post, FALSE ),
			'class' => 'bootstrap-qrcode-toggle',
			'rel'   => 'qrcode',
			'data'  => [
				'toggle'      => 'dropdown',
				'bs-toggle'   => 'dropdown',
				'display'     => 'static',
				'id'          => $post->ID,
				'qrcode-url'  => $url,
				'qrcode-size' => $size,
			],
		], $text ).$dropdown.$after;

		if ( $enqueued )
			return TRUE;

		// wp_enqueue_script( 'gtheme-bootstrap-qrcode', GTHEME_URL.'/js/script.bootstrap-qrcode'.( SCRIPT_DEBUG ? '' : '.min' ).'.js', [ 'jquery', ], GTHEME_VERSION, TRUE );

		$script = <<<'JS'
jQuery(function($){$('.-action.-bootstrap-qrcode').on('show.bs.dropdown',function(event){if($(this).data('qrcode'))return;const $link=$(this).find('a.bootstrap-qrcode-toggle');const size=$link.data('qrcode-size');$(this).find('.-qrcode-wrap').html($('<img />',{src:'https://api.qrserver.com/v1/create-qr-code/?size='+size+'x'+size+'&ecc=M&data='+encodeURIComponent($link.data('qrcode-url')),alt:'qrcode'}));$(this).data('qrcode',true);});});
JS;
		// @REF: https://core.trac.wordpress.org/ticket/44551
		// @REF: https://wordpress.stackexchange.com/a/311279
		wp_register_script( 'gtheme-bootstrap-qrcode', '', [ 'jquery' ], '', TRUE );
		wp_enqueue_script( 'gtheme-bootstrap-qrcode' ); // must register then enqueue
		wp_add_inline_script( 'gtheme-bootstrap-qrcode', $script );

		$enqueued = TRUE;
	}

	// @SEE : https://code.tutsplus.com/tutorials/creating-a-wordpress-post-text-size-changer-using-jquery--wp-28403
	public static function text_size_buttons( $b = '', $a = '', $sep = 'def', $increase = 'def', $decrease = 'def' )
	{

		if ( 'def' == $increase )
			$increase = gThemeOptions::info( 'text_size_increase', _x( '[ A+ ]', 'Options: Text Size Increase', 'gtheme' ) );

		if ( 'def' == $decrease )
			$decrease = gThemeOptions::info( 'text_size_decrease', _x( '[ A- ]', 'Options: Text Size Decrease', 'gtheme' ) );

		if ( 'def' == $sep )
			$sep = gThemeOptions::info( 'text_size_sep', _x( ' / ', 'Options: Text Size Sep', 'gtheme' ) );

		echo $b;

		echo '<a id="gtheme-fontsize-plus" class="fontsize-button increase-font" href="#" title="';
			_e( 'Increase font size', 'gtheme' );
		echo '">'.$increase.'</a>';

		if ( $sep )
			printf( '<a id="gtheme-fontsize-default" class="fontsize-button" href="#">%s</a>', $sep );

		echo '<a id="gtheme-fontsize-minus" class="fontsize-button decrease-font" href="#" title="';
			_e( 'Decrease font size', 'gtheme' );
		echo '">'.$decrease.'</a>';

		echo $a;
	}

	public static function justify_buttons( $b = '', $a = '', $sep = 'def', $justify = 'def', $unjustify = 'def', $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) ) {
			wp_enqueue_script( 'jquery' );
			add_action( 'wp_footer', [ __CLASS__, 'justify_buttons_footer' ], 99 );
		}

		if ( 'def' == $justify )
			$justify = gThemeOptions::info( 'text_justify', _x( '[ Ju ]', 'Options: Text Justify', 'gtheme' ) );

		if ( 'def' == $unjustify )
			$unjustify = gThemeOptions::info( 'text_unjustify', _x( '[ uJ ]', 'Options: Text Unjustify', 'gtheme' ) );

		if ( 'def' == $sep )
			$sep = gThemeOptions::info( 'text_justify_sep', _x( ' / ', 'Options: Text Justify Sep', 'gtheme' ) );

		echo $b;

		echo '<a id="text-justify" class="text-justify-button hidden" href="#" title="';
			_e( 'Justify paragraphs', 'gtheme' );
		echo '">'.$justify.'</a>';

		if ( $sep )
			printf( '%s', $sep );

		echo '<a id="text-unjustify" class="text-justify-button" href="#" title="';
			_e( 'Un-justify paragraphs', 'gtheme' );
		echo '">'.$unjustify.'</a>';

		echo $a;
	}

	// FIXME: test this!
	public static function justify_buttons_footer()
	{
		?><script>jQuery(function ($) {
$('#text-justify, #text-unjustify').removeAttr('href').css('cursor', 'pointer');

$('#text-justify').on('click', function (e) {
	e.preventDefault();
	$('.entry-content p').each(function () {
		$(this).css('text-align', 'justify');
	});
	$('#text-unjustify').fadeIn();
	$('#text-justify').hide();
});

$('#text-unjustify').on('click', function (e) {
	e.preventDefault();
	$('body.rtl .entry-content p').each(function () {
		$(this).css('text-align', 'right');
	});
	$('#text-justify').fadeIn();
	$('#text-unjustify').hide();
	});
});</script><?php
	}

	// @REF: https://www.addtoany.com/buttons/customize/
	public static function addtoany( $text = NULL, $post = NULL, $before = '', $after = '', $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) )
			add_action( 'wp_footer', [ __CLASS__, 'addtoany_footer' ] );

		$premalink = get_permalink( $post );
		$linkname  = self::getTitleAttr( '%s', NULL, $post );

		$url = add_query_arg( [
			'linkurl'  => urlencode( $premalink ),
			'linkname' => urlencode( $linkname ),
		], 'http://www.addtoany.com/share_save' );

		echo $before;
		printf( '<a class="a2a_dd" href="%1$s" rel="nofollow" data-a2a-url="%3$s" data-a2a-title="%4$s">%2$s</a>',
			esc_url( $url ),
			( $text ?: _x( 'Share This', 'Modules: Content: Addtoany', 'gtheme' ) ),
			$premalink,
			esc_attr( $linkname )
		);
		echo $after;
	}

	public static function addtoany_footer()
	{
		if ( $twitter = gThemeOptions::info( 'twitter_site', FALSE ) )
			$twitter_template = '${title} ${link} '.gThemeThird::getTwitter( $twitter );
		else
			$twitter_template = '${title} ${link}';

		/* translators: %s: post title */
		$check = sprintf( _x( 'Check this out %s', 'Modules: Content: Addtoany', 'gtheme' ), '${title}' );
		/* translators: %s: post link */
		$click = sprintf( _x( "Click the link:\n%s", 'Modules: Content: Addtoany', 'gtheme' ), '${link}' );

		?><script>
var a2a_config = a2a_config || {};
a2a_config.linkname = '<?php echo esc_js( self::getTitleAttr( '%s' ) ); ?>';
a2a_config.linkurl = '<?php echo esc_js( esc_url_raw( get_permalink() ) ); ?>';
a2a_config.onclick = true;
a2a_config.locale = "fa";
a2a_config.prioritize = ["email", "twitter", "facebook", "evernote", "tumblr", "wordpress", "blogger_post", "read_it_later", "linkedin"];
a2a_config.templates = {
	twitter: "<?php echo $twitter_template; ?>",
	email: {
		subject: "<?php echo esc_js( $check ); ?>",
		body: "<?php echo esc_js( $click ); ?>"
	}
};
if(typeof(ga)!='undefined'){a2a_config.track_links = 'ga';}
(function(){var a=document.createElement('script');a.async=true;a.src='//static.addtoany.com/menu/page.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(a,s);})();
</script><?php
	}

	// FIXME: DRAFT / NOT TESTED
	// @SEE: http://www.addthis.com/academy/the-addthis_share-variable/
	// @SEE: http://www.addthis.com/academy/setting-the-url-title-to-share/
	// @SEE: http://www.addthis.com/academy/specifying-the-image-posted-to-pinterest/
	public static function addthis( $text = '', $post = NULL, $b = '', $a = '', $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) )
			add_action( 'wp_footer', [ __CLASS__, 'addthis_footer' ], 5 );

		echo $b;
		echo '<div class="addthis_sharing_toolbox" data-url="'.get_permalink( $post ).'" data-title="'
				.self::getTitleAttr( '%s', NULL, $post ).'" data-image="">'.$text.'</div>';
		echo $a;
	}

	// FIXME: DRAFT / NOT TESTED
	// @SEE: http://www.addthis.com/academy/the-addthis_config-variable/
	// @SEE: http://www.addthis.com/academy/integrating-with-google-analytics/
	public static function addthis_footer()
	{
		?><script>
var addthis_config = addthis_config || {};
addthis_config.username = '';
addthis_config.ui_language = '<?php echo esc_js( gThemeOptions::info( 'lang', 'en' ) ); ?>';
addthis_config.data_track_clickback = false;
addthis_config.services_custom = [
	{
		name: "My Service",
		url: "http://share.example.com?url={{URL}}&title={{TITLE}}",
		icon: "http://example.com/icon.jpg"
	}
];
</script>
<script src="//static.addtoany.com/menu/page.js"></script><?php
	}

	// @REF: https://getpocket.com/publisher/button_docs
	public static function pocket( $text = NULL, $post = NULL, $before = '', $after = '', $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) )
			add_action( 'wp_footer', [ __CLASS__, 'pocket_footer' ], 5 );

		echo $before.gThemeHTML::tag( 'a', [
			'href'  => 'https://getpocket.com/save',
			'class' => 'pocket-btn',
			'data'  => [
				'save-url'     => get_permalink( $post ),
				'lang'         => gThemeOptions::info( 'lang', 'en' ),
				'pocket-label' => $text ?: 'pocket',
				'pocket-count' => 'none', // horizontal/vertical
				// 'pocket-align' => 'left', // only useful when using with count
			],
		], NULL ).$after;
	}

	public static function pocket_footer()
	{
		?><script>!function(d,i){if(!d.getElementById(i)){var j=d.createElement("script");j.id=i;j.src="https://widgets.getpocket.com/v1/j/btn.js?v=1";var w=d.getElementById(i);d.body.appendChild(j);}}(document,"pocket-btn-js");</script><?php
	}

	// for embed/twitter-feed
	public static function getHeader( $title, $sep, $byline = TRUE )
	{
		ob_start();

			gThemeEditorial::label( [
				'after'       => ': ',
				'image'       => FALSE,
				'link'        => FALSE,
				'description' => FALSE,
			] );

			gThemeEditorial::metaOverTitle( NULL, [ 'after' => $sep ] );

			if ( $title )
				echo $title;

			gThemeEditorial::metaSubTitle( NULL, [ 'before' => $sep ] );

			if ( $byline )
				echo strip_tags( self::byline( NULL, ' — ', '', FALSE ) );

		return trim( str_ireplace( '&nbsp;', ' ', ob_get_clean() ) );
	}

	// NOTE: ANCESTOR : `gtheme_post_header()`
	public static function header( $atts = [] )
	{
		$singular = is_singular();

		if ( $singular && gThemeWooCommerce::isPage() )
			return;

		$args = self::atts( [
			'post'        => NULL,
			'context'     => 'single',
			'prefix'      => 'entry',
			'byline'      => FALSE,
			'actions'     => FALSE,                                                      // or NULL to check for post-type
			'action_icon' => NULL,
			'shortlink'   => gThemeOptions::info( 'content_header_shortlink', FALSE ),
			'wrap_tag'    => 'header',
			'wrap_close'  => TRUE,
			'trim_title'  => FALSE,                                                      // or number of chars
			'trim_meta'   => FALSE,                                                      // or number of chars
			'word_wrap'   => TRUE,                                                       // avoid widow words
			'itemprop'    => TRUE,
			'link_rel'    => NULL,                                                       // NULL for check short-link argument, `FALSE` to disable
			'title_tag'   => $singular ? 'h2' : 'h3',
			'meta_tag'    => $singular ? 'h3' : 'h4',
			'title'       => NULL,
			'title_attr'  => NULL,                                                       // or FALSE to disable
			'title_sep'   => ' / ',                                                      // used on meta as title attribute
			'amp'         => $singular,
			'meta'        => gThemeOptions::supports( 'geditorial-meta', TRUE ),
			'link'        => TRUE,                                                       // default/custom/disable
			'anchor'      => FALSE,                                                      // perma-link anchor for the post
		], $atts );

		if ( ! $post = self::getPost( $args['post'] ) )
			return;

		if ( is_null( $args['title'] ) )
			$args['title'] = get_the_title( $post );

		if ( ! is_string( $args['title'] ) )
			return;

		if ( 0 === strlen( $args['title'] ) )
			return;

		if ( TRUE === $args['link'] ) {

			if ( FALSE === $args['shortlink'] )
				$args['link'] = get_permalink( $post );

			else if ( TRUE === $args['shortlink'] )
				$args['link'] = wp_get_shortlink( $post->ID, 'post' );

			else
				$args['link'] = $args['shortlink'];
		}

		if ( $args['wrap_tag'] )
			echo '<'.$args['wrap_tag'].' class="-header header-class header-'.$args['context'].' '.$args['prefix'].'-header'.( $args['amp'] ? ' amp-wp-article-header' : '' ).'">';

		do_action( 'gtheme_content_header_open', $post, $args );

		echo '<div class="-titles titles-class '.$args['prefix'].'-titles">';

		do_action( 'gtheme_content_header_before', $post, $args );

		if ( $args['meta'] )
			gThemeEditorial::metaOverTitle( $post, [
				'before'    => '<'.$args['meta_tag'].' class="-overtitle overtitle '.$args['prefix'].'-overtitle"'.( $args['itemprop'] ? ' itemprop="alternativeHeadline"' : '' ).'>',
				'after'     => '</'.$args['meta_tag'].'>',
				'trim'      => $args['trim_meta'],
				'word_wrap' => $args['word_wrap'],
			] );

		echo '<'.$args['title_tag'].' class="-title title '.$args['prefix'].'-title'.( $args['amp'] ? ' amp-wp-title' : '' ).'"';

		if ( $args['itemprop'] )
			echo ' itemprop="headline"';

		echo '>';

		$title = $args['trim_title']
			? gThemeUtilities::trimChars( $args['title'], $args['trim_title'] )
			: $args['title'];

		if ( $args['link'] ) {

			echo '<a href="'.esc_url( $args['link'] ).'"';

			if ( $args['itemprop'] )
				echo ' itemprop="url"';

			if ( is_null( $args['link_rel'] ) )
				echo ' rel="'.( $args['shortlink'] ? 'shortlink' : 'bookmark' ).'"'; // @SEE: https://www.seroundtable.com/google-ignores-rel-shortlink-24561.html

			else if ( $args['link_rel'] )
				echo ' rel="'.$args['link_rel'].'"';

			$title_template = TRUE === $args['shortlink'] ? FALSE : NULL;

			if ( is_null( $args['title_attr'] ) ) {

				$args['title_attr'] = trim( strip_tags( $args['title'] ) );

			} else if ( 'meta' == $args['title_attr'] ) {

				$overtitle = gThemeEditorial::metaOverTitle( $post, [ 'echo' => FALSE, 'word_wrap' => FALSE ] );
				$subtitle  = gThemeEditorial::metaSubTitle( $post,  [ 'echo' => FALSE, 'word_wrap' => FALSE ] );

				$args['title_attr'] = $overtitle;

				if ( $overtitle && $subtitle )
					$args['title_attr'].= $args['title_sep'];

				$args['title_attr'].= $subtitle;

				$title_template = '%s';
			}

			if ( $args['title_attr'] )
				echo ' title="'.self::getTitleAttr( $title_template, $args['title_attr'], $post ).'"';

			echo '>'.( $args['word_wrap'] ? gThemeText::wordWrap( $title, 2 ) : $title ).'</a>';

		} else {

			echo ( $args['word_wrap'] ? gThemeText::wordWrap( $title, 2 ) : $title );
		}

		if ( $args['anchor'] )
			echo '<a id="post-'.$post->ID.'"></a>'; // @REF: `permalink_anchor();`

		echo '</'.$args['title_tag'].'>';

		if ( $args['meta'] )
			gThemeEditorial::metaSubTitle( $post, [
				'before'    => '<'.$args['meta_tag'].' class="-subtitle subtitle '.$args['prefix'].'-subtitle"'.( $args['itemprop'] ? ' itemprop="alternativeHeadline"' : '' ).'>',
				'after'     => '</'.$args['meta_tag'].'>',
				'trim'      => $args['trim_meta'],
				'word_wrap' => $args['word_wrap'],
			] );

		do_action( 'gtheme_content_header_close', $post, $args );

		echo '</div>';

		if ( $args['byline'] ) {
			self::byline( $post, '<div class="-byline '.$args['prefix'].'-byline byline-'.$args['context'].'">', '</div>' );
		}

		if ( $args['actions'] || ( is_null( $args['actions'] ) && ! is_page() ) ) {
			echo '<ul class="-actions -actions-header '.$args['prefix'].'-actions actions-'.$args['context'].' -inline">';
				self::renderActions( $post, '<li class="-action '.$args['prefix'].'-action %s">', '</li>', $args['actions'], $args['action_icon'] );
			echo '</ul>';
		}

		do_action( 'gtheme_content_header_end', $post, $args );

		if ( $args['wrap_close'] && $args['wrap_tag'] )
			echo '</'.$args['wrap_tag'].'>'."\n";
	}

	public static function footer( $atts = [] )
	{
		$args = self::atts( [
			'post'        => NULL,
			'context'     => 'single',
			'prefix'      => 'entry',
			'actions'     => gThemeOptions::info( 'post_actions_footer', [ 'byline', 'categories', 'date' ] ),
			'action_icon' => NULL,
			'shortlink'   => FALSE,
			'title_tag'   => 'h2',
			'meta_tag'    => 'h4',
			'title'       => NULL,
			'meta'        => TRUE,
			'link'        => TRUE, // disable linking compeletly
		], $atts );

		if ( ! $post = self::getPost( $args['post'] ) )
			return;

		if ( $args['actions'] ) {
			echo '<footer class="footer-class footer-'.$args['context'].' '.$args['prefix'].'-footer">';
				echo '<ul class="-actions -actions-footer '.$args['prefix'].'-actions actions-'.$args['context'].' -inline">';
					self::renderActions( $post, '<li class="-action '.$args['prefix'].'-action %s">', '</li>', $args['actions'], $args['action_icon'] );
				echo '</ul>';
			echo '</footer>';
		}
	}

	public static function navigation( $atts = [] )
	{
		if ( ! gThemeUtilities::contentHasPages() )
			return FALSE;

		$args = self::atts( [
			'link_before'      => '',
			'link_after'       => '',
			'next_or_number'   => 'number',
			'separator'        => '',
			'nextpagelink'     => _x( 'Next page', 'Modules: Content: Link Pages', 'gtheme' ),
			'previouspagelink' => _x( 'Previous page', 'Modules: Content: Link Pages', 'gtheme' ),
			'pagelink'         => '%',
		], $atts );

		$args['before'] = $args['after'] = $args['echo'] = '';

		if ( ! $html = wp_link_pages( $args ) )
			return FALSE;

		$args = self::atts( [
			'before' => '<div class="entry-pages">',
			'after'  => '</div>',
			'title'  => _x( 'Pages:', 'Modules: Content: Link Pages', 'gtheme' ),
			'echo'   => TRUE,
		], $atts );

		if ( $args['title'] )
			$html = '<span class="-title">'.$args['title'].'</span> '.$html;

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
		return TRUE;
	}

	// @REF: https://developer.wordpress.org/reference/functions/wp_link_pages/#comment-6282
	// @EXAMPLE: `« Previous 1 2 3 Next »`
	public static function navigationFancy( $atts = [] )
	{
		if ( ! gThemeUtilities::contentHasPages() )
			return FALSE;

		$args = self::atts( [
			'link_before'      => '',
			'link_after'       => '',
			'nextpagelink'     => _x( 'Next &raquo;', 'Modules: Content: Link Pages', 'gtheme' ),
			'previouspagelink' => _x( '&laquo; Previous', 'Modules: Content: Link Pages', 'gtheme' ),
			'pagelink'         => '%',
		], $atts );

		$args['before'] = $args['after'] = $args['echo'] = '';

		$separator = wp_link_pages( array_merge( $args, [
			'next_or_number'   => 'number',

		] ) );

		$html = wp_link_pages( array_merge( $args, [
			'next_or_number' => 'next',
			'separator'      => $separator,
		] ) );

		if ( gThemeUtilities::contentFirstPage() )
			$html = $separator.$html;

		else if ( gThemeUtilities::contentLastPage() )
			$html.= $separator;

		if ( ! $html )
			return FALSE;

		$args = self::atts( [
			'before' => '<div class="entry-pages">',
			'after'  => '</div>',
			'title'  => '', // It's better not to have title on `Fancy`
			'echo'   => TRUE,
		], $atts );

		if ( $args['title'] )
			$html = '<span class="-title">'.$args['title'].'</span> '.$html;

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
		return TRUE;
	}
}
