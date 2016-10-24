<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeContent extends gThemeModuleCore
{

	public static function wrapOpen( $context = 'index', $extra = array(), $tag = 'article' )
	{
		$classes = array_merge( array(
			'entry-wrap',
			'content-'.$context,
			'clearfix',
		), $extra );

		$post_id = get_the_ID();

		echo '<'.$tag.( $post_id ? ' id="post-'.$post_id.'"' : '' ).' class="'.join( ' ', get_post_class( $classes, $post_id ) ).'">';
	}

	public static function wrapClose( $tag = 'article' )
	{
		echo '</'.$tag.'>';
	}

	public static function notFoundMessage( $before = '<p class="not-found">', $after = '</p>' )
	{
		$default = _x( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'Content: Not Found Message', GTHEME_TEXTDOMAIN );

		if ( $message = gThemeOptions::info( 'message_notfound', $default ) )
			echo $before.gThemeUtilities::wordWrap( $message ).$after;
	}

	public static function post( $context = NULL, $part = 'content' )
	{
		if ( is_null( $context ) )
			$context = gtheme_template_base();

		do_action( 'before_post', $context, $part );
		get_template_part( $part, $context );
		do_action( 'after_post', $context, $part );
	}

	// http://www.billerickson.net/code/wp_query-arguments/
	public static function query( $args = array(), $expiration = GTHEME_CACHETTL )
	{
		if ( gThemeUtilities::isDev() )
			return new WP_Query( $args );

		$key = 'gtq_'.md5( serialize( $args ) );

		if ( gThemeUtilities::isFlush() )
			delete_transient( $key );

		if ( FALSE === ( $query = get_transient( $key ) ) ) {
			 $query = new WP_Query( $args );
			 set_transient( $key, $query, $expiration );
		}

		return $query;
	}

	public static function content( $before = '<div class="entry-content">', $after = '</div>', $edit = NULL )
	{
		if ( is_null( $edit ) )
			$edit = gThemeOptions::info( 'read_more_edit', FALSE );

		echo $before;

		if ( gThemeOptions::info( 'restricted_content', FALSE ) )
			self::restricted();
		else
			the_content( self::continueReading( ( $edit ? get_edit_post_link() : '' ) ) );

		if ( gThemeOptions::info( 'copy_disabled', FALSE ) )
			echo '<div class="copy-disabled"></div>'; // http://stackoverflow.com/a/23337329/4864081

		echo $after;
	}

	public static function row( $before = '', $after = '', $empty = FALSE )
	{
		if ( ! get_the_title() && ! $empty )
			return;

		echo $before;

			echo '<a class="permalink" title="';
				self::title_attr();
			echo '" href="';
				the_permalink();
			echo '">';
				echo gThemeUtilities::wordWrap( get_the_title(), 2 );
			echo '</a>';

		echo $after;
	}

	// FIXME: WORKING DRAFT
	// SEE: gThemeDate::date()
	public static function date( $before = '<div class="entry-date">', $after = '</div>' )
	{
		echo $before;

		the_date( 'Y/j/m' );

		echo $after;
	}

	// FIXME: DEPRECATED
	public static function continue_reading( $edit = '', $scope = '', $permalink = FALSE, $title_att = FALSE )
	{
		self::__dep( 'gThemeContent::continueReading()' );
		return self::continueReading( $edit, $scope, $permalink, $title_att );
	}

	public static function continueReading( $edit = '', $scope = '', $link = FALSE, $title = FALSE )
	{
		if ( FALSE === $title )
			$title = esc_attr( strip_tags( get_the_title() ) );

		if ( FALSE === $link )
			$link = esc_url( get_permalink() );

		if ( ! empty( $edit ) )
			$edit = vsprintf( ' <a href="%1$s" title="%3$s" class="%4$s">%2$s</a>', array(
				$edit,
				_x( 'Edit', 'Content: Read More Edit', GTHEME_TEXTDOMAIN ),
				_x( 'Jump to edit page', 'Content: Read More Edit Title', GTHEME_TEXTDOMAIN ),
				'post-edit-link',
			) );

		$text  = gThemeOptions::info( 'read_more_text', _x( 'Read more&nbsp;<span class="excerpt-link-hellip">&hellip;</span>', 'Content: Read More Text', GTHEME_TEXTDOMAIN ) );
		$title = sprintf( gThemeOptions::info( 'read_more_title', _x( 'Continue reading &ldquo;%s&rdquo; &hellip;', 'Content: Read More Title', GTHEME_TEXTDOMAIN ) ), $title );

		return vsprintf( ' <a %6$s href="%1$s" aria-label="%3$s" class="%4$s">%2$s</a>%5$s', array( $link, $text, $title, 'excerpt-link', $edit, $scope ) );
	}

	// OLD: gtheme_the_title_attribute()
	public static function title_attr( $echo = TRUE, $title = NULL, $template = NULL, $empty = '' )
	{
		if ( is_null( $title ) )
			$title = trim( strip_tags( get_the_title() ) );

		if ( 0 === strlen( $title ) )
			return $empty;

		if ( is_null( $template ) )
			$attr = _x( 'Permanent link to &ndash;%s&ndash;', 'Content: Title Attr',GTHEME_TEXTDOMAIN );

		else if ( FALSE === $template )
			$attr = _x( 'Short link for &ndash;%s&ndash;', 'Content: Title Attr', GTHEME_TEXTDOMAIN );

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

			global $more;
			$more = 0;

			the_content( FALSE );

			if ( is_null( $message ) )
				$message = gThemeOptions::info( 'restricted_message', '' );

			if ( $message )
				echo $before.$message.$after;

		} else {

			// not caching the full article!
			self::doNotCache();

			if ( is_null( $stripteser ) )
				$stripteser = ! gThemeOptions::info( 'restricted_teaser', FALSE );

			the_content( self::continueReading(), $stripteser );
		}
	}

	// @REF: https://developer.wordpress.org/reference/functions/the_content/#comment-338
	public static function teaser( $before = '<div class="entry-teaser">', $after = '</div>', $link = NULL, $edit = NULL )
	{
		global $more;
		$more = 0;

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
		if ( post_password_required() )
			return;

		$post = get_post();

		if ( ! $post )
			return;

		if ( $only && empty( $post->post_excerpt ) )
			return;

		if ( $excerpt_length )
			// MIGHT be a problem since we bypass other filters too
			$excerpt = apply_filters( 'the_excerpt', gTheme()->filters->get_the_excerpt( gThemeL10N::html( $post->post_excerpt ), $excerpt_length ) );
		else
			$excerpt = apply_filters( 'the_excerpt', get_the_excerpt() );

		if ( ! empty( $atts ) )
			$excerpt = preg_replace( '/(<p\b[^><]*)>/i', '$1 '.$atts.'>', $excerpt ); // http://stackoverflow.com/a/3983870/642752

		echo $b.$excerpt.$a;
	}

	public static function slug()
	{
		global $post;
		return get_post( $post )->post_name;
	}

	public static function actions( $before = '<span class="post-action %s">', $after = '</span>', $action_list = TRUE, $icons = 'def' )
	{
		if ( TRUE === $action_list )
			$actions = gThemeOptions::info( 'post_actions', array() );
		else if ( is_array( $action_list ) )
			$actions = $action_list;
		else
			$actions = array();

		if ( 'def' === $icons )
			$icons = gThemeOptions::info( 'post_actions_icons', FALSE );

		do_action( 'gtheme_action_links_before', $before, $after, $actions, $icons );

		foreach ( $actions as $action )
			self::do_action( $action, $before, $after, $icons );

		do_action( 'gtheme_action_links', $before, $after, $actions, $icons );
	}

	public static function do_action( $action, $before, $after, $icons = FALSE )
	{
		switch ( $action ) {

			case 'textsize_buttons' :
			case 'textsize_buttons_nosep' :
				self::text_size_buttons(
					sprintf( $before, 'textsize-buttons hidden-print' ), $after,
					( 'textsize_buttons_nosep' == $action ? FALSE : 'def' ),
					( $icons ? '<div class="genericon genericon-zoom"></div>' : 'def' ),
					( $icons ? '<div class="genericon genericon-unzoom"></div>' : 'def' )
					);
			break;

			case 'textjustify_buttons' :
			case 'textjustify_buttons_nosep' :
				self::justify_buttons(
					sprintf( $before, 'textjustify-buttons hidden-print' ), $after,
					( 'textjustify_buttons_nosep' == $action ? FALSE : 'def' ),
					( $icons ? '<div class="genericon genericon-minimize"></div>' : 'def' ),
					( $icons ? '<div class="genericon genericon-previous"></div>' : 'def' )
					);
			break;

			case 'printfriendly' :
				self::printfriendly(
					sprintf( $before, 'printfriendly post-print-link hidden-print' ), $after,
					( $icons ? '<div class="genericon genericon-print"></div>' : __( 'Print Version', GTHEME_TEXTDOMAIN ) )
				);
			break;

			case 'a2a_dd' :
			case 'addtoany' :
				self::addtoany(
					sprintf( $before, 'addtoany post-share-link' ), $after,
					( $icons ? '<div class="genericon genericon-share"></div>' : __( 'Share This', GTHEME_TEXTDOMAIN ) )
				);
			break;

			case 'addthis' :
				self::addthis(
					sprintf( $before, 'addthis post-share-link' ), $after,
					( $icons ? '<div class="genericon genericon-share"></div>' : __( 'Share This', GTHEME_TEXTDOMAIN ) )
				);
			break;

			case 'shortlink' :
				the_shortlink(
					( $icons ? '<div class="genericon genericon-link"></div>' : __( 'Short Link', GTHEME_TEXTDOMAIN ) ),
					self::title_attr( FALSE, NULL, '%s' ),
					sprintf( $before, 'post-short-link' ),
					$after
				);
			break;

			case 'comments_link' :
			case 'comments_link_feed' :
				if ( comments_open() ) {
					printf( $before, 'comments-link' );

					// if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) )
					/*
						comments_popup_link(
							__( 'Leave a comment', 'mytheme' ),
							__( '1 Comment', 'mytheme' ),
							__( '% Comments', 'mytheme' )
						);
					*/

					if ( is_singular() ) {
						$link = '#respond';
						$class = 'scroll1';
					} else {
						$link = get_permalink().'#respond';
						$class = 'hastip1';
					}

					if ( $icons )
						printf( '<a href="%2$s" class="%1$s"><div class="genericon genericon-comment"></div></a>', $class, $link  );
					else
						comments_number(
							sprintf( __( '<a href="%3$s" class="%1$s">Your Comment</a>', GTHEME_TEXTDOMAIN ), $class, '', $link ),
							sprintf( __( '<a href="%3$s" class="%1$s">One Comment</a>', GTHEME_TEXTDOMAIN ), $class, '', $link ),
							sprintf( __( '<a href="%3$s" class="%1$s">%2$s Comments</a>', GTHEME_TEXTDOMAIN ), $class, '%', $link )
						);

					if ( 'comments_link_feed' == $action ) {
						if ( $icons )
							printf( '<a href="%2$s" class="%1$s"><div class="genericon genericon-feed"></div></a>', 'comments-link-rss', get_post_comments_feed_link() );
						else
							printf( __( ' <small><small>(<a href="%1$s" title="%2$s" class="%3$s"><abbr title="Really Simple Syndication">RSS</abbr></a>)</small></small>', GTHEME_TEXTDOMAIN ),
								get_post_comments_feed_link(),
								__( 'Feed for this post\'s comments', GTHEME_TEXTDOMAIN ),
								'comments-link-rss'
							);
					}
					echo $after;
				}
			break;

			case 'edit_post_link' :
				edit_post_link(
					( $icons ? '<div class="genericon genericon-edit"></div>' : __( 'Edit', GTHEME_TEXTDOMAIN ) ),
					sprintf( $before, 'post-edit-link post-edit-link-li' ),
					$after
				);
			break;

			case 'tag_list' :
				if ( is_object_in_taxonomy( get_post_type(), 'post_tag' ) ) {
					echo get_the_tag_list( sprintf( $before, 'tag-links' ).gtheme_get_info( 'before_tag_list', '' ), gThemeUtilities::sanitize_sep( 'def', 'term_sep' ), $after );
					/*
					$tag_list = get_the_tag_list( '', gThemeUtilities::sanitize_sep( 'def', 'term_sep' ) );
					if ( $tag_list )
						echo sprintf( $before, 'tags-links' ).$tag_list.$after;
					*/
				}
			break;
			case 'cat_list' :
				if ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
					echo sprintf( $before, 'cat-links' ).gtheme_get_info( 'before_cat_list', '' ).get_the_category_list( gThemeUtilities::sanitize_sep( 'def', 'term_sep' ) ).$after;
				}

			break;
			case 'the_date' :

				gThemeDate::date( array(
					'before' => sprintf( $before, 'the-date' ),
					'after'  => $after,
					'text'   => ( $icons ? '<div class="genericon genericon-edit"></div>' : NULL ),
				) );

			break;
		}
	}

	// ALSO SEE : http://wp.tutsplus.com/tutorials/theme-development/creating-a-wordpress-post-text-size-changer-using-jquery/
	public static function text_size_buttons( $b = '', $a = '', $sep = 'def', $increase = 'def', $decrease = 'def' )
	{
		echo $b;

		echo '<a id="gtheme-fontsize-plus" class="fontsize-button increase-font" href="#" title="'.__( 'Increase font size', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $increase ? gtheme_get_info( 'text_size_increase', '[ A+ ]' ) : $increase );
		echo '</a>';

		if ( FALSE !== $sep ) {
			echo '<a id="gtheme-fontsize-default" class="fontsize-button" href="#">';
			echo gThemeUtilities::sanitize_sep( $sep, 'text_size_sep' );
			echo '</a>';
		}

		echo '<a id="gtheme-fontsize-minus" class="fontsize-button decrease-font" href="#" title="'.__( 'Decrease font size', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $decrease ? gtheme_get_info( 'text_size_decrease', '[ A- ]' ) : $decrease );
		echo '</a>';

		echo $a;
	}

	public static function justify_buttons( $b = '', $a = '', $sep = 'def', $justify = 'def', $unjustify = 'def' )
	{
		echo $b;

		echo '<a id="text-justify" class="text-justify-button hidden" href="#" title="'.__( 'Justify paragraphs', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $justify ? gtheme_get_info( 'text_justify', 'Ju' ) : $justify );
		echo '</a>';

		if ( FALSE !== $sep )
			echo gThemeUtilities::sanitize_sep( $sep, 'text_justify_sep' );

		echo '<a id="text-unjustify" class="text-justify-button" href="#" title="'.__( 'Un-justify paragraphs', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $unjustify ? gtheme_get_info( 'text_unjustify', 'uJ' ) : $unjustify );
		echo '</a>';

		echo $a;
	}

	// http://www.printfriendly.com/button
	public static function printfriendly( $b = '', $a = '', $text = NULL, $footer = TRUE, $analytics = TRUE )
	{
		if ( $footer && is_singular() )
			add_action( 'wp_footer', array( __CLASS__, 'printfriendly_footer' ) );

		$query_args = array(
			'url' => urlencode( get_permalink() ),
			// 'headerImageUrl' => '',
			// 'headerTagline' => '',
			'pfCustomCSS'       => urlencode( GTHEME_URL.'/css/printfriendly_01.css' ),
			'imageDisplayStyle' => gThemeUtilities::isRTL( 'left', 'right' ), //'block',
			// 'disableClickToDel' => '',
			// 'disablePDF' => '',
			// 'disablePrint' => '',
			// 'disableEmail' => '',
			// 'hideImages' => '',
		);


		$onclick = 'onclick="window.print(); return false;"';
		$title_var = "NULL";
		$analytics_code = "if(typeof(_gaq) != 'undefined') { _gaq.push(['_trackEvent','PRINTFRIENDLY', 'print', '".$title_var."']);}";
		if ( $analytics )
			$onclick = 'onclick="window.print();'.$analytics_code.' return false;"';

		echo $b;
		printf( '<a href="%1$s" rel="nofollow" %3$s>%2$s</a>',
			add_query_arg( $query_args, 'http://www.printfriendly.com/print' ),
			( $text ? $text : __( 'Print Version', GTHEME_TEXTDOMAIN ) ),
			$onclick
		);
		echo $a;
	}

	// prints the PrintFriendly JavaScript, in the footer, and loads it asynchronously.
	public static function printfriendly_footer()
	{
		?><script type="text/javascript">
		  (function() {
			var e = document.createElement('script');
				e.type="text/javascript";
			if('https:' == document.location.protocol) {
			  js='https://pf-cdn.printfriendly.com/ssl/main.js';
			}else{
			  js='http://cdn.printfriendly.com/printfriendly.js';
			}
			e.src = js;
			document.getElementsByTagName('head')[0].appendChild(e);
			})();
	  </script><?php
	}

	// http://www.addtoany.com/buttons/for/website
	public static function addtoany( $b = '', $a = '', $text = NULL, $footer = TRUE )
	{
		if ( $footer && is_singular() )
			add_action( 'wp_footer', array( __CLASS__, 'addtoany_footer' ), 5 );

		$query_args = array(
			'linkurl'  => urlencode( get_permalink() ),
			'linkname' => self::title_attr( FALSE, NULL, '%s' ),
		);

		echo $b;
		printf( '<a href="%1$s" rel="nofollow" title="%3$s">%2$s</a>',
			add_query_arg( $query_args, 'http://www.addtoany.com/share_save' ),
			( $text ? $text : __( 'Share This', GTHEME_TEXTDOMAIN ) ),
			__( 'Share This with your friends.', GTHEME_TEXTDOMAIN )
		);
		echo $a;
	}

	public static function addtoany_footer()
	{
		?><script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.linkname = '<?php echo esc_js( esc_url_raw( get_permalink() ) ); ?>';
a2a_config.linkurl = '<?php echo esc_js( self::title_attr( FALSE, NULL, '%s' ) ); ?>';
a2a_config.onclick = 1;
a2a_config.locale = "fa";
</script>
<script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script><?php
	}

	// FIXME: DRAFT / NOT TESTED
	// @SEE: http://www.addthis.com/academy/the-addthis_share-variable/
	// @SEE: http://www.addthis.com/academy/setting-the-url-title-to-share/
	// @SEE: http://www.addthis.com/academy/specifying-the-image-posted-to-pinterest/
	public static function addthis( $b = '', $a = '', $text = NULL, $footer = TRUE )
	{
		if ( $footer && is_singular() )
			add_action( 'wp_footer', array( __CLASS__, 'addthis_footer' ), 5 );

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
addthis_config.ui_language = 'fa';
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

	// ANCESTOR : gtheme_post_header()
	public static function header( $atts = array() )
	{
		$args = self::atts( array(
			'context'     => 'single',
			'prefix'      => 'entry',
			'actions'     => FALSE,
			'action_icon' => 'def',
			'shortlink'   => FALSE,
			'wrap_tag'   => 'header',
			'title_tag'   => 'h2',
			'meta_tag'    => 'h4',
			'title'       => NULL,
			'meta'        => TRUE,
			'link'        => TRUE, // disable linking compeletly
			'anchor'      => FALSE, // permalink anchor for the post
		), $atts );

		if ( is_null( $args['title'] ) )
			$args['title'] = gThemeUtilities::wordWrap( get_the_title(), 2 );

		if ( 0 == strlen( $args['title'] ) )
			return;

		if ( $args['link'] ) {
			if ( FALSE === $args['shortlink'] )
				$link = get_permalink();
			else if ( TRUE === $args['shortlink'] )
				$link = wp_get_shortlink( 0, 'post' );
			else
				$link = $args['shortlink'];
		}

		if ( $args['meta'] )
			$args['meta'] = gThemeOptions::supports( 'geditorial-meta', TRUE );

		echo '<'.$args['wrap_tag'].' class="header-class header-'.$args['context'].' '.$args['prefix'].'-header">';
		echo '<div class="titles-class '.$args['prefix'].'-titles">';

		if ( $args['meta'] )
			gThemeEditorial::meta( 'over-title', array(
				'before' => '<'.$args['meta_tag'].' itemprop="alternativeHeadline" class="overtitle '.$args['prefix'].'-overtitle">',
				'after'  => '</'.$args['meta_tag'].'>',
			) );

		echo '<'.$args['title_tag'].' itemprop="headline" class="title '.$args['prefix'].'-title">';

		if ( $args['link'] ) {
			echo '<a itemprop="url" rel="bookmark" href="'.$link.'" title="';
				self::title_attr( TRUE, $args['title'], ( TRUE === $args['shortlink'] ? FALSE : NULL ) );
			echo '">'.$args['title'].'</a>';
		} else {
			echo $args['title'];
		}

		if ( $args['anchor'] )
			permalink_anchor( 'id' );

		echo '</'.$args['title_tag'].'>';

		if ( $args['meta'] )
			gThemeEditorial::meta( 'sub-title', array(
				'before' => '<'.$args['meta_tag'].' itemprop="alternativeHeadline" class="subtitle '.$args['prefix'].'-subtitle">',
				'after'  => '</'.$args['meta_tag'].'>',
			) );

		echo '</div>';

		if ( $args['actions'] ) {
			echo '<ul class="list-inline actions-class actions-'.$args['context'].' '.$args['prefix'].'-actions">';
				self::actions( '<li class="post-action %s">', '</li>', $args['actions'], $args['action_icon'] );
			echo '</ul>';
		}

		echo '</'.$args['wrap_tag'].'>';
	}

	public static function footer( $atts = array() )
	{
		$args = self::atts( array(
			'context'     => 'single',
			'prefix'      => 'entry',
			'actions'     => FALSE,
			'action_icon' => 'def',
			'shortlink'   => FALSE,
			'title_tag'   => 'h2',
			'meta_tag'    => 'h4',
			'title'       => NULL,
			'meta'        => TRUE,
			'link'        => TRUE, // disable linking compeletly
		), $atts );

		if ( $args['actions'] ) {
			echo '<footer class="footer-class footer-'.$args['context'].' '.$args['prefix'].'-footer">';
				echo '<ul class="list-inline actions-class actions-'.$args['context'].' '.$args['prefix'].'-actions">';
					self::actions( '<li class="post-action %s">', '</li>', $args['actions'], $args['action_icon'] );
				echo '</ul>';
			echo '</footer>';
		}
	}
}
