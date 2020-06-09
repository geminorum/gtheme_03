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

		echo '<'.$tag.( $post_id ? ' id="post-'.$post_id.'"' : '' ).' class="'.join( ' ', self::getPostClass( $classes, $post_id ) ).'">';

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

	public static function post( $context = NULL, $part = 'content' )
	{
		if ( is_null( $context ) )
			$context = gtheme_template_base();

		do_action( 'gtheme_post_before', $context, $part );

		get_template_part( $part, $context );

		do_action( 'gtheme_post_after', $context, $part );
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

		do_action( 'gtheme_content_before' );

		echo $before;

		if ( gThemeOptions::info( 'restricted_content', FALSE ) )
			self::restricted();
		else
			the_content( self::continueReading( ( $edit ? get_edit_post_link() : '' ) ) );

		if ( gThemeOptions::info( 'copy_disabled', FALSE ) )
			echo '<div class="copy-disabled"></div>'; // http://stackoverflow.com/a/23337329

		echo $after;

		do_action( 'gtheme_content_after' );
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
			'title' => self::title_attr( FALSE, $title ),
			'class' => '-link -permalink',
		], gThemeText::wordWrap( $title ) );

		echo $after;
	}

	public static function byline( $post = NULL, $before = '', $after = '', $echo = TRUE, $fallback = NULL )
	{
		if ( ! $post = get_post( $post ) )
			return '';

		// dummy post
		if ( ! $post->ID )
			return '';

		if ( 'page' == $post->post_type )
			return '';

		$args = [ 'id' => $post->ID, 'echo' => FALSE, 'context' => 'single' ];

		if ( gThemeOptions::supports( 'gpeople', TRUE )
			&& is_callable( [ 'gPeopleRemoteTemplate', 'post_byline' ] ) ) {

			if ( $html = \gPeopleRemoteTemplate::post_byline( $post, $args ) ) {

				if ( $echo )
					echo $before.$html.$after;

				return $before.$html.$after;
			}
		}

		// FIXME: check posttype: video: featured people

		if ( gThemeOptions::supports( 'geditorial-meta', TRUE ) ) {

			if ( $html = gThemeEditorial::author( $args ) ) {

				if ( $echo )
					echo $before.$html.$after;

				return $before.$html.$after;
			}
		}

		if ( is_null( $fallback ) )
			$fallback = gThemeOptions::info( 'byline_fallback', TRUE );

		if ( ! $fallback )
			return '';

		if ( $html = gThemeTemplate::author( $post, FALSE ) ) {

			if ( $echo )
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

	// core duplicate for performance concerns
	// @REF: `get_post_class()`
	public static function getPostClass( $class = '', $post_id = NULL )
	{
		$post = get_post( $post_id );

		$classes = $class
			? array_map( [ 'gThemeHTML', 'sanitizeClass' ], gThemeHTML::attrClass( $class ) )
			: [];

		if ( ! $post )
			return $classes;

		if ( ! empty( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query']->current_post > -1 )
			$classes[] = sprintf( 'loop-index-%d', $GLOBALS['wp_query']->current_post );

		$classes[] = gThemeHTML::sanitizeClass( 'type-'.$post->post_type );

		if ( post_password_required( $post->ID ) )
			$classes[] = 'post-password-required';

		else if ( ! empty( $post->post_password ) )
			$classes[] = 'post-password-protected';

		$classes[] = 'hentry';

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
		/* translators: %s: post title */
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

	// OLD: gtheme_the_title_attribute()
	public static function title_attr( $echo = TRUE, $title = NULL, $template = NULL, $empty = '' )
	{
		if ( FALSE === $title )
			return '';

		if ( is_null( $title ) )
			$title = trim( strip_tags( get_the_title() ) );

		if ( 0 === strlen( $title ) )
			return $empty;

		if ( is_null( $template ) )
			/* translators: %s: post title */
			$attr = _x( 'Permanent link to &ndash;%s&ndash;', 'Content: Title Attr','gtheme' );

		else if ( FALSE === $template )
			/* translators: %s: post title */
			$attr = _x( 'Short link for &ndash;%s&ndash;', 'Content: Title Attr', 'gtheme' );

		else
			$attr = $template;

		$result = esc_attr( sprintf( $attr, $title ) );

		if ( ! $echo )
			return $result;

		echo $result;
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
	// based on WP core : get_the_content()
	public static function teaser_OLD( $fallback = TRUE, $echo = TRUE )
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

		if ( ! $echo )
			return $output;
		echo $output;
	}

	// ANCESTOR: gtheme_the_excerpt()
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

		echo $b.$excerpt.$a;
	}

	// FIXME: must accept `$post`
	public static function postActions( $before = '<li class="-action entry-action %s">', $after = '</li>', $list = TRUE, $icon = NULL )
	{
		if ( ! $post = get_post() )
			return;

		// dummy post
		if ( ! $post->ID )
			return;

		if ( TRUE === $list )
			$actions = gThemeOptions::info( 'post_actions', [
				'printlink',
				'addtoany',
				'shortlink',
				'comments_link',
				'edit_post_link',
				'editorial_estimated',
			] );

		else if ( is_array( $list ) )
			$actions = $list;

		else
			$actions = [];

		if ( is_null( $icon ) )
			$icon = gThemeOptions::info( 'post_actions_icons', FALSE );

		do_action( 'gtheme_action_links_before', $before, $after, $actions, $icon );

		foreach ( $actions as $action ) {

			if ( is_array( $action ) ) {

				if ( is_callable( $action ) )
					call_user_func_array( $action, [ $before, $after, $icon ] );

			} else {

				self::doAction( $action, $before, $after, $icon );
			}
		}

		do_action( 'gtheme_action_links', $before, $after, $actions, $icon );
	}

	public static function doAction( $action, $before, $after, $icon = FALSE )
	{
		switch ( $action ) {

			case 'byline':

				self::byline( NULL, sprintf( $before, '-action -byline' ), $after );

			break;
			case 'textsize_buttons':
			case 'textsize_buttons_nosep':

				self::text_size_buttons(
					sprintf( $before, 'textsize-buttons hidden-print' ), $after,
					( 'textsize_buttons_nosep' == $action ? FALSE : 'def' ),
					( $icon ? self::getGenericon( 'zoom' ) : 'def' ),
					( $icon ? self::getGenericon( 'unzoom' ) : 'def' )
				);

			break;
			case 'textjustify_buttons':
			case 'textjustify_buttons_nosep':

				self::justify_buttons(
					sprintf( $before, 'textjustify-buttons hidden-print' ), $after,
					( 'textjustify_buttons_nosep' == $action ? FALSE : 'def' ),
					( $icon ? self::getGenericon( 'minimize' ) : 'def' ),
					( $icon ? self::getGenericon( 'previous' ) : 'def' )
				);

			break;
			case 'a2a_dd':
			case 'addtoany':

				self::addtoany(
					sprintf( $before, 'addtoany post-share-link' ), $after,
					( $icon ? self::getGenericon( 'share' ) : _x( 'Share This', 'Modules: Content: Action', 'gtheme' ) )
				);

			break;
			case 'addthis':

				self::addthis(
					sprintf( $before, 'addthis post-share-link' ), $after,
					( $icon ? self::getGenericon( 'share' ) : _x( 'Share This', 'Modules: Content: Action', 'gtheme' ) )
				);

			break;
			case 'pocket_button':

				self::pocket(
					sprintf( $before, 'pocket post-share-button' ), $after,
					_x( 'Pocket', 'Modules: Content: Action', 'gtheme' )
				);

			break;
			case 'printlink':

				self::printLink(
					( $icon ? self::getGenericon( 'print' ) : _x( 'Print Version', 'Modules: Content: Action', 'gtheme' ) ),
					NULL,
					sprintf( $before, '-action -printlink' ),
					$after,
					FALSE // self::title_attr( FALSE, '', FALSE )
				);

			break;
			case 'shortlink':

				self::shortlink(
					( $icon ? self::getGenericon( 'link' ) : _x( 'Short Link', 'Modules: Content: Action', 'gtheme' ) ),
					NULL,
					sprintf( $before, '-action -shortlink' ),
					$after,
					self::title_attr( FALSE, NULL, FALSE )
				);

			break;
			case 'comments_link':
			case 'comments_link_feed':

				if ( comments_open() ) {

					printf( $before, 'comments-link' );

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

						$link     = get_permalink();
						$respond  = $link.'#respond';
						$comments = $link.'#comments';
						$class    = ''; // hastip
					}

					if ( $icon )
						printf( '<a href="%2$s" class="%1$s">%3$s</a>', $class, ( get_comments_number() ? $comments : $respond ), self::getGenericon( 'comment' ) );

					else
						comments_number(
							/* translators: %1$s: class name, %2$s: comments number, %3$s: comment url */
							sprintf( _x( '<a href="%3$s" class="%1$s">Your Comment</a>', 'Modules: Content: Action', 'gtheme' ), $class, '', $respond ),
							/* translators: %1$s: class name, %2$s: comments number, %3$s: comment url */
							sprintf( _x( '<a href="%3$s" class="%1$s">One Comment</a>', 'Modules: Content: Action', 'gtheme' ), $class, '', $comments ),
							/* translators: %1$s: class name, %2$s: comments number, %3$s: comment url */
							sprintf( _x( '<a href="%3$s" class="%1$s">%2$s Comments</a>', 'Modules: Content: Action', 'gtheme' ), $class, '%', $comments )
						);

					if ( 'comments_link_feed' == $action ) {

						if ( $icon )
							printf( '<a href="%2$s" class="%1$s">%3$s</a>', 'comments-link-rss', get_post_comments_feed_link(), self::getGenericon( 'feed' ) );

						else
							/* translators: %1$s: comments rss link, %2$s: title attr, %3$s: class name */
							printf( _x( ' <small><small>(<a href="%1$s" title="%2$s" class="%3$s"><abbr title="Really Simple Syndication">RSS</abbr></a>)</small></small>', 'Modules: Content: Action', 'gtheme' ),
								get_post_comments_feed_link(),
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
					sprintf( $before, 'post-edit-link post-edit-link-li' ),
					$after
				);

			break;
			case 'tag_list': // DEPRECATED

				if ( is_object_in_taxonomy( get_post_type(), 'post_tag' ) )
					the_tags(
						sprintf( $before, 'tag-links' ).
						gThemeOptions::info( 'before_tag_list', '' ),
						gThemeOptions::info( 'term_sep', _x( ', ', 'Options: Separator: Term', 'gtheme' ) ),
						$after
					);

			break;
			case 'tags':

				if ( is_object_in_taxonomy( get_post_type(), 'post_tag' ) )
					gThemeTerms::theList( 'post_tag', sprintf( $before, 'tag-term' ), $after );

			break;
			case 'cat_list': // DEPRECATED

				if ( is_object_in_taxonomy( get_post_type(), 'category' ) )
					echo sprintf( $before, 'cat-links' )
						.gThemeOptions::info( 'before_cat_list', '' )
						.get_the_category_list( gThemeOptions::info( 'term_sep', _x( ', ', 'Options: Separator: Term', 'gtheme' ) ) )
						.$after;

			break;
			case 'categories':

				if ( is_object_in_taxonomy( get_post_type(), 'category' ) )
					gThemeTerms::theList( 'category', sprintf( $before, 'category-term' ), $after );

			break;
			case 'primary_term':

				gThemeTerms::linkPrimary( sprintf( $before, 'primary-term' ), $after );

			break;
			case 'the_date':
			case 'date':

				gThemeDate::date( [
					'before'   => sprintf( $before, 'the-date' ),
					'after'    => $after,
					'text'     => $icon ? self::getGenericon( 'edit' ) : NULL,
					'template' => '<a href="%1$s"%2$s><time class="%5$s-time" datetime="%3$s">%4$s</time></a>',
					'timeago'  => FALSE, // FIXME: add another action for time ago
				] );

			break;
			case 'editorial_label':

				gThemeEditorial::label( [
					'before' => sprintf( $before, 'entry-label' ),
					'after'  => $after,
				] );

			break;
			case 'editorial_estimated';

				gThemeEditorial::estimated( [
					'before' => sprintf( $before, 'entry-estimated' ),
					'after'  => $after,
					'prefix' => '',
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
		if ( ! $post = get_post( $post ) )
			return;

		if ( ! in_array( $post->post_type, (array) gThemeOptions::info( 'print_posttypes', [ 'post' ] ) ) )
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
			'data'  => [
				'toggle' => 'tooltip',
				'id'     => $post->ID,
			],
		], $text ).$after;
	}

	public static function shortlink( $text, $post = NULL, $before = '', $after = '', $title = NULL )
	{
		if ( ! $post = get_post() )
			return;

		if ( ! $shortlink = wp_get_shortlink( $post->ID ) )
			return;

		echo $before.gThemeHTML::tag( 'a', [
			'href'  => $shortlink,
			'title' => $title ?: FALSE,
			'rel'   => 'shortlink',
			'data'  => [
				'toggle' => 'tooltip',
				'id'     => $post->ID,
			],
		], $text ).$after;
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
		?><script type="text/javascript">jQuery(function ($) {
$('#text-justify, #text-unjustify').removeAttr('href').css('cursor', 'pointer');

$('#text-justify').click(function (e) {
	e.preventDefault();
	$('.entry-content p').each(function () {
		$(this).css('text-align', 'justify');
	});
	$('#text-unjustify').fadeIn();
	$('#text-justify').hide();
});

$('#text-unjustify').click(function (e) {
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
	public static function addtoany( $before = '', $after = '', $text = NULL, $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) )
			add_action( 'wp_footer', [ __CLASS__, 'addtoany_footer' ] );

		$premalink = get_permalink();
		$linkname  = self::title_attr( FALSE, NULL, '%s' );

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

		?><script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.linkname = '<?php echo esc_js( self::title_attr( FALSE, NULL, '%s' ) ); ?>';
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
(function(){var a=document.createElement('script');a.type='text/javascript';a.async=true;a.src='//static.addtoany.com/menu/page.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(a,s);})();
</script><?php
	}

	// FIXME: DRAFT / NOT TESTED
	// @SEE: http://www.addthis.com/academy/the-addthis_share-variable/
	// @SEE: http://www.addthis.com/academy/setting-the-url-title-to-share/
	// @SEE: http://www.addthis.com/academy/specifying-the-image-posted-to-pinterest/
	public static function addthis( $b = '', $a = '', $text = NULL, $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) )
			add_action( 'wp_footer', [ __CLASS__, 'addthis_footer' ], 5 );

		echo $b;
		echo '<div class="addthis_sharing_toolbox" data-url="'.get_permalink().'" data-title="';
			the_title_attribute();
		echo '" data-image=""></div>';
		echo $a;
	}

	// FIXME: DRAFT / NOT TESTED
	// @SEE: http://www.addthis.com/academy/the-addthis_config-variable/
	// @SEE: http://www.addthis.com/academy/integrating-with-google-analytics/
	public static function addthis_footer()
	{
		?><script type="text/javascript">
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
<script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script><?php
	}

	// @REF: https://getpocket.com/publisher/button_docs
	public static function pocket( $before = '', $after = '', $text = NULL, $footer = TRUE )
	{
		if ( $footer && ( is_singular() || is_single() ) )
			add_action( 'wp_footer', [ __CLASS__, 'pocket_footer' ], 5 );

		echo $before.gThemeHTML::tag( 'a', [
			'href'  => 'https://getpocket.com/save',
			'class' => 'pocket-btn',
			'data'  => [
				'save-url'     => get_permalink(),
				'lang'         => gThemeOptions::info( 'lang', 'en' ),
				'pocket-label' => $text ?: 'pocket',
				'pocket-count' => 'none', // horizontal/vertical
				// 'pocket-align' => 'left', // only useful when using with count
			],
		], NULL ).$after;
	}

	public static function pocket_footer()
	{
		?><script type="text/javascript">!function(d,i){if(!d.getElementById(i)){var j=d.createElement("script");j.id=i;j.src="https://widgets.getpocket.com/v1/j/btn.js?v=1";var w=d.getElementById(i);d.body.appendChild(j);}}(document,"pocket-btn-js");</script><?php
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

			gThemeEditorial::meta( 'over-title', [ 'after' => $sep ] );

			if ( $title )
				echo $title;

			gThemeEditorial::meta( 'sub-title', [ 'before' => $sep ] );

			if ( $byline )
				echo strip_tags( self::byline( NULL, ' — ', '', FALSE ) );

		return trim( str_ireplace( '&nbsp;', ' ', ob_get_clean() ) );
	}

	// ANCESTOR : gtheme_post_header()
	public static function header( $atts = [] )
	{
		$args = self::atts( [
			'post'        => NULL,
			'context'     => 'single',
			'prefix'      => 'entry',
			'byline'      => FALSE,
			'actions'     => FALSE, // or NULL to check for posttype
			'action_icon' => NULL,
			'shortlink'   => gThemeOptions::info( 'content_header_shortlink', FALSE ),
			'wrap_tag'    => 'header',
			'wrap_close'  => TRUE,
			'itemprop'    => TRUE,
			'link_rel'    => 'bookmark',
			'title_tag'   => is_singular() ? 'h2' : 'h3',
			'meta_tag'    => 'h5',
			'title'       => NULL,
			'title_attr'  => NULL, // or FALSE to disable
			'title_sep'   => ' / ', // used on meta as title attr
			'amp'         => is_singular(),
			'meta'        => gThemeOptions::supports( 'geditorial-meta', TRUE ),
			'link'        => TRUE, // default/custom/disable
			'anchor'      => FALSE, // permalink anchor for the post
		], $atts );

		if ( ! $post = get_post( $args['post'] ) )
			return;

		if ( is_null( $args['title'] ) )
			$args['title'] = get_the_title( $post );

		if ( 0 == strlen( $args['title'] ) )
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

		echo '<div class="-titles titles-class '.$args['prefix'].'-titles">';

		if ( $args['meta'] )
			gThemeEditorial::meta( 'over-title', [
				'post_id' => $post->ID,
				'before'  => '<'.$args['meta_tag'].' class="-overtitle overtitle '.$args['prefix'].'-overtitle"'.( $args['itemprop'] ? ' itemprop="alternativeHeadline"' : '' ).'>',
				'after'   => '</'.$args['meta_tag'].'>',
			] );

		echo '<'.$args['title_tag'].' class="-title title '.$args['prefix'].'-title'.( $args['amp'] ? ' amp-wp-title' : '' ).'"';

		if ( $args['itemprop'] )
			echo ' itemprop="headline"';

		echo '>';

		if ( $args['link'] ) {

			echo '<a href="'.esc_url( $args['link'] ).'"';

			if ( $args['itemprop'] )
				echo ' itemprop="url"';

			if ( $args['link_rel'] )
				echo ' rel="'.$args['link_rel'].'"';

			$title_template = TRUE === $args['shortlink'] ? FALSE : NULL;

			if ( is_null( $args['title_attr'] ) ) {

				$args['title_attr'] = trim( strip_tags( $args['title'] ) );

			} else if ( 'meta' == $args['title_attr'] ) {

				$overtitle = gThemeEditorial::getMeta( 'over-title', [ 'post_id' => $post->ID ] );
				$subtitle  = gThemeEditorial::getMeta( 'sub-title', [ 'post_id' => $post->ID ] );

				$args['title_attr'] = $overtitle;

				if ( $overtitle && $subtitle )
					$args['title_attr'].= $args['title_sep'];

				$args['title_attr'].= $subtitle;

				$title_template = '%s';
			}

			if ( $args['title_attr'] )
				echo ' title="'.self::title_attr( FALSE, $args['title_attr'], $title_template ).'"';

			echo '>'.gThemeText::wordWrap( $args['title'], 2 ).'</a>';

		} else {

			echo gThemeText::wordWrap( $args['title'], 2 );
		}

		if ( $args['anchor'] )
			echo '<a id="post-'.$post->ID.'"></a>'; // @REF: `permalink_anchor();`

		echo '</'.$args['title_tag'].'>';

		if ( $args['meta'] )
			gThemeEditorial::meta( 'sub-title', [
				'post_id' => $post->ID,
				'before'  => '<'.$args['meta_tag'].' class="-subtitle subtitle '.$args['prefix'].'-subtitle"'.( $args['itemprop'] ? ' itemprop="alternativeHeadline"' : '' ).'>',
				'after'   => '</'.$args['meta_tag'].'>',
			] );

		echo '</div>';

		if ( $args['byline'] ) {
			self::byline( $post, '<div class="-byline '.$args['prefix'].'-byline byline-'.$args['context'].'">', '</div>' );
		}

		if ( $args['actions'] || ( is_null( $args['actions'] ) && ! is_page() ) ) {
			echo '<ul class="-actions -actions-header '.$args['prefix'].'-actions actions-'.$args['context'].' -inline">';
				self::postActions( '<li class="-action '.$args['prefix'].'-action %s">', '</li>', $args['actions'], $args['action_icon'] );
			echo '</ul>';
		}

		if ( $args['wrap_close'] && $args['wrap_tag'] )
			echo '</'.$args['wrap_tag'].'>'."\n";
	}

	public static function footer( $atts = [] )
	{
		$args = self::atts( [
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

		if ( $args['actions'] ) {
			echo '<footer class="footer-class footer-'.$args['context'].' '.$args['prefix'].'-footer">';
				echo '<ul class="-actions -actions-footer '.$args['prefix'].'-actions actions-'.$args['context'].' -inline">';
					self::postActions( '<li class="-action '.$args['prefix'].'-action %s">', '</li>', $args['actions'], $args['action_icon'] );
				echo '</ul>';
			echo '</footer>';
		}
	}

	public static function navigation( $atts = [] )
	{
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
	}
}
