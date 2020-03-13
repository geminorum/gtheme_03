<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeFilters extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'content_extra'      => FALSE,
			'auto_paginate'      => FALSE,
			'redirect_canonical' => FALSE,
			'default_editor'     => FALSE,
			'disable_autoembed'  => FALSE, // gNetwork does it
			'overwrite_author'   => TRUE,
		], $args ) );

		if ( ! is_admin() ) {

			add_action( 'wp_head', [ $this, 'wp_head' ], 5 );
			add_filter( 'body_class', [ $this, 'body_class' ], 10, 2 );
			add_filter( 'post_class', [ $this, 'post_class' ], 10, 3 );

			add_filter( 'document_title_separator', [ $this, 'document_title_separator' ] );
			add_filter( 'document_title_parts', [ $this, 'document_title_parts' ], 8 );

			add_filter( 'the_excerpt', function( $text ){
				return $text ? $text.gThemeContent::continueReading() : $text;
			}, 5 );

			// FALSE by default, we don't use this filter anyway
			if ( $length = gThemeOptions::info( 'excerpt_length', FALSE ) )
				add_filter( 'excerpt_length', function( $first ) use( $length ) {
					return $length;
				} );

			add_filter( 'excerpt_more', [ $this, 'excerpt_more' ] );

			if ( gThemeOptions::info( 'trim_excerpt_characters', FALSE ) ) {
				remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
				add_filter( 'get_the_excerpt', [ $this, 'get_the_excerpt' ] );
			}

			add_filter( 'the_content', [ $this, 'the_content' ], 15 );
			add_filter( 'the_content_more_link', [ $this, 'the_content_more_link' ] );

			if ( $content_extra )
				add_filter( 'the_content', [ $this, 'the_content_extra' ], 16 );

			if ( $auto_paginate )
				add_action( 'loop_start', [ $this, 'loop_start' ] );

			if ( $redirect_canonical )
				add_filter( 'redirect_canonical', [ $this, 'redirect_canonical' ], 10, 2 );

			if ( $default_editor )
				add_filter( 'wp_default_editor', [ $this, 'wp_default_editor' ] );

			// https://gist.github.com/ocean90/3796628
			// disables the auto-embeds function in WordPress 3.5
			if ( $disable_autoembed )
				remove_filter( 'the_content', [ $GLOBALS['wp_embed'], 'autoembed' ], 8 );

			// to remove wp recent comments widget styles
			add_filter( 'show_recent_comments_widget_style', '__return_false' );

			if ( $overwrite_author ) {
				add_filter( 'the_author', [ $this, 'the_author' ], 15 );
				add_filter( 'the_author_posts_link', [ $this, 'the_author_posts_link' ], 15 );
			}
		}
	}

	public function wp_head()
	{
		$singular = is_singular();
		$print    = gThemeUtilities::isPrint();

		if ( $viewport = gThemeOptions::info( 'head_viewport', 'width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no' ) )
			echo '<meta name="viewport" content="'.$viewport.'" />'."\n";

		if ( ! $print ) {

			gThemeThird::htmlThemeColor( gThemeOptions::info( 'theme_color' ) );
			gThemeSocial::doHead();
		}

		if ( $singular && $print ) {

			echo self::getStyleLink( TRUE, TRUE );

		} else if ( gThemeOptions::info( 'deferred_styles', FALSE )
			&& ! gThemeWordPress::isDebug() ) {

			self::preloadStyles();

			$this->action( 'gtheme_wrap_body_open' );
			$this->action( 'gtheme_wrap_body_close' );

		} else {

			foreach ( (array) gThemeOptions::info( 'stylesheets', [] ) as $stylesheet )
				gThemeUtilities::linkStyleSheet( $stylesheet, FALSE );

			echo self::getStyleLink( $singular );
		}

		if ( gThemeWordPress::isDev() && ! gThemeUtilities::isPrint() )
			gThemeUtilities::linkStyleSheet( GTHEME_URL.'/css/main.dev'.( gThemeUtilities::isRTL() ? '-rtl' : '' ).'.css', GTHEME_VERSION, 'all' );

		if ( $singular )
			echo '<link rel="pingback" href="'.get_bloginfo( 'pingback_url', 'display' ).'" />'."\n";
	}

	public function gtheme_wrap_body_open()
	{
		echo '<div id="preloadspinner" class="preload -spinner '
			.gThemeOptions::info( 'preload_spinner_class', 'light' )
			.'"><div><div></div></div></div>'."\n";
	}

	public function gtheme_wrap_body_close()
	{
		$html = '';

		foreach ( (array) gThemeOptions::info( 'stylesheets', [] ) as $stylesheet )
			$html.= gThemeUtilities::linkStyleSheet( $stylesheet, FALSE, FALSE, FALSE );

		$html.= self::getStyleLink( is_singular() );

		if ( $html )
			self::deferredStyles( $html );
	}

	public static function preloadStyles( $group = NULL )
	{
		if ( is_null( $group ) )
			$group = gThemeOptions::getGroup();

		$file = $group.'.preload'
			.( gThemeUtilities::isRTL() ? '-rtl' : '' )
			// .( SCRIPT_DEBUG ? '' : '.min' )
			.'.css';

		$path = file_exists( GTHEME_CHILD_DIR.'/css/'.$file )
			? GTHEME_CHILD_DIR.'/css/'.$file
			: GTHEME_DIR.'/css/'.$file;

		echo '<style type="text/css">';
			readfile( $path );
		echo '</style>'."\n";

		echo '<noscript><style type="text/css">#preloadspinner{display:none;z-index:-999999;}</style></noscript>'."\n";
	}

	public static function getStyleLink( $singular = FALSE, $print = FALSE, $group = NULL )
	{
		$rtl  = gThemeUtilities::isRTL();
		$args = [ 'ver' => GTHEME_CHILD_VERSION ];

		if ( is_null( $group ) )
			$group = gThemeOptions::getGroup();

		if ( gThemeWordPress::isDev() )
			$args['debug'] = '';

		if ( $singular && $print ) {

			$file = $group.'.print'
				.( $rtl ? '-rtl' : '' )
				// .( SCRIPT_DEBUG ? '' : '.min' )
				.'.css';

			$url = file_exists( GTHEME_CHILD_DIR.'/css/'.$file )
				? GTHEME_CHILD_URL.'/css/'.$file
				: GTHEME_URL.'/css/'.$file;

			$media = 'all'; // also for custom endpoint view

		} else if ( file_exists( GTHEME_CHILD_DIR.'/css/css.php' ) ) {

			$args['dir']   = $rtl ? 'rtl' : 'ltr';
			$args['group'] = gThemeOptions::getGroup();

			$url = GTHEME_CHILD_URL.'/css/';

			$media = FALSE;

		} else {

			$file = $group.'.screen'
				.( $rtl ? '-rtl' : '' )
				// .( SCRIPT_DEBUG ? '' : '.min' )
				.'.css';

			$url = file_exists( GTHEME_CHILD_DIR.'/css/'.$file )
				? GTHEME_CHILD_URL.'/css/'.$file
				: GTHEME_URL.'/css/'.$file;

			$media = 'all';
		}

		return gThemeUtilities::linkStyleSheet( $url, $args, $media, FALSE );
	}

	// @REF: https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery
	public static function deferredStyles( $tags )
	{
		?><noscript id="deferred-styles"><?php echo "\n".$tags; ?></noscript>
<script type="text/javascript">
(function(){
	var loadDeferredStyles = function(){
		var addStylesNode = document.getElementById("deferred-styles");
		var replacement = document.createElement("div");
		replacement.innerHTML = addStylesNode.textContent;
		document.body.appendChild(replacement)
		addStylesNode.parentElement.removeChild(addStylesNode);
		<?php if ( SCRIPT_DEBUG ) echo 'console.log("styles loaded");'; ?>
		window.setTimeout(disableSpinner, 600);
	};
	var disableSpinner = function(){
		var spinner = document.getElementById("preloadspinner");
		if (spinner.classList) spinner.classList.add("fade-out");
		else spinner.className += " fade-out";
		<?php if ( SCRIPT_DEBUG ) echo 'console.log("spinner disabled");'; ?>
	};
	var raf = window.requestAnimationFrame
		|| window.mozRequestAnimationFrame
		|| window.webkitRequestAnimationFrame
		|| window.msRequestAnimationFrame;
	if (raf) raf(function(){window.setTimeout(loadDeferredStyles, 0);});
	else window.addEventListener('load', loadDeferredStyles);
}());
</script><?php
	}

	public function body_class( $classes, $class )
	{
		global $post, $pagenow;

		$gtheme_info = gThemeOptions::info();

		if ( ! is_array( $classes ) )
			$classes = (array) $classes;

		$classes[] = 'gtheme';

		if ( ! empty( $gtheme_info['name'] ) && $gtheme_info['name'] != 'gtheme' )
			$classes[] = $gtheme_info['name'];

		if ( ! empty( $gtheme_info['additional_body_class'] ) )
			$classes[] = $gtheme_info['additional_body_class'];

		$classes[] = 'theme-group-'.gThemeOptions::getGroup();

		if ( $extra = gThemeOptions::getOption( 'body_class_extra', FALSE ) )
			$classes = array_merge( $classes, array_map( 'sanitize_html_class', explode( ' ', $extra ) ) );

		if ( gThemeOptions::info( 'bootstrap_navbar_fixed', FALSE ) )
			$classes[] = 'navbar-fixed';

		if ( ! empty( $pagenow ) && 'index.php' !== $pagenow )
			$classes[] = sanitize_html_class( 'pagenow-'.str_ireplace( '.php', '', $pagenow ) );

		if ( gThemeWordPress::isDev() )
			$classes[] = 'stage-development';

		if ( ! gThemeUtilities::isRTL() )
			$classes[] = 'ltr';

		if ( is_page() )
			$classes[] = sanitize_html_class( 'slug-'.get_post()->post_name );

		// $uri = explode( '/', $_SERVER['REQUEST_URI'] );

		// if ( isset( $uri[1] ) ) {
		// 	$uri_string = htmlentities( trim( strip_tags( $uri[1] ) ) );
		// 	if ( ! empty( $uri_string ) )
		// 		$classes[] = 'uri-'.$uri_string;
		// }

		// foreach ( $gtheme_info['sidebars'] as $sidebar_name => $sidebar_title )
		// 	if ( is_active_sidebar( $sidebar_name ) )
		// 		$classes[] = 'sidebar-'.$sidebar_name;

		return $classes;
	}

	protected $current_post_class = 'odd';

	public function post_class( $classes, $class, $post_ID )
	{
		if ( is_embed() )
			return $classes;

		global $wp_query, $post;

		if ( ( is_archive() || is_home() || is_front_page() ) ) {

			if ( FALSE !== strpos( $post->post_content, '<!--more-->' ) )
				$classes[] = 'more';

			if ( 0 == $wp_query->current_post )
				$classes[] = 'first';

			$classes[] = $this->current_post_class;

			$this->current_post_class = ( 'odd' == $this->current_post_class ) ? 'even' : 'odd';
		}

		return $classes;
	}

	// helper to reset post class
	public function reset_post_class( $class = 'odd' )
	{
		$this->current_post_class = $class;
	}

	public function document_title_separator( $sep )
	{
		return gThemeOptions::info( 'title_sep', $sep );
	}

	public function document_title_parts( $title )
	{
		if ( is_feed() ) {


		} else if ( is_front_page() ) {

			if ( $frontpage = gThemeOptions::info( 'frontpage_title', FALSE ) ) {
				$title['title'] = trim( $frontpage );
				unset( $title['tagline'] ); // remove default
			}

		} else {

			if ( $blog = gThemeOptions::info( 'blog_title', FALSE ) )
				$title['site'] = $blog;
		}

		return $title;
	}

	public function excerpt_more( $more )
	{
		$custom = gThemeOptions::info( 'excerpt_more', '&nbsp;&hellip;' );

		if ( TRUE === $custom )
			return gThemeContent::continueReading();

		if ( FALSE === $custom )
			return '';

		return $custom;
	}

	// @SOURCE: http://wordpress.org/plugins/character-count-excerpt/
	public function get_the_excerpt( $text, $length = FALSE )
	{
		if ( FALSE === $length )
			$length = gThemeOptions::info( 'trim_excerpt_characters', 300 );

		if ( FALSE === $length )
			return wp_trim_excerpt( $text );

		$raw  = $text;
		$text = trim( $text );

		if ( '' == $text ) {

			$text = get_the_content( '' );
			$text = strip_shortcodes( $text );
			$text = apply_filters( 'the_content', $text );
			$text = str_replace( ']]>', ']]&gt;', $text );
			$text = strip_tags( $text );

			if ( strlen( $text ) > $length ) {

				$regex = "/[\n\r\t ]+/";
				$text  = substr( $text, 0, $length + 1 );
				$words = preg_split( $regex, $text, -1, PREG_SPLIT_NO_EMPTY );

				// if the last character is not a white space,
				// must remove the cut off last word
				preg_match( $regex, $text, $last, 0, $length );

				if ( empty( $last ) )
					array_pop( $words );

				$more = apply_filters( 'excerpt_more', ' '.'[&hellip;]' );
				$text = implode( ' ', $words ).$more;
			}
		}

		return apply_filters( 'wp_trim_excerpt', $text, $raw );
	}

	public function the_content( $content )
	{
		if ( gThemeOptions::info( 'restricted_content', FALSE ) ) {

			// FIXME: now only for rest-api
			if ( gThemeWordPress::isREST() && gThemeContent::isRestricted() ) {
				$GLOBALS['more'] = 0;
				$content = get_the_content( FALSE );
			}
		}

		// removes empty paragraph tags
		if ( gThemeOptions::info( 'content_remove_empty_p', TRUE ) )
			$content = gThemeText::noEmptyP( $content );

		// removes paragraph around images
		if ( gThemeOptions::info( 'content_remove_image_p', TRUE ) )
			$content = gThemeText::noImageP( $content );

		return $content;
	}

	public function the_content_more_link( $link )
	{
		return preg_replace( '|#more-[0-9]+|', '', $link );
	}

	public function the_content_extra( $content )
	{
		// http://stackoverflow.com/a/3226746
		// http://plugins.svn.wordpress.org/remove-double-space/tags/0.3/remove-double-space.php
		if ( seems_utf8( $content ) )
			return preg_replace( '/[\p{Z}\s]{2,}/u', ' ', $content );
		else
			return preg_replace( '/\s\s+/', ' ', $content );
	}

	// FIXME: Not working well with UTF / problem in `str_word_count()`
	// https://gist.github.com/danielbachhuber/6691084
	// auto-paginate after 500 words, but respect paragraphs and don't leave page stubs.
	public function loop_start( $query )
	{
		if ( ! $query->is_main_query() )
			return;

		if ( ! is_singular( gThemeOptions::info( 'post_auto_paginate_type', [ 'post' ] ) ) )
			return;

		$min  = gThemeOptions::info( 'post_auto_paginate_min', 900 );
		$each = gThemeOptions::info( 'post_auto_paginate_each', 500 );

		$content = $query->posts[0]->post_content;
		$count   = gThemeText::wordCountUTF8( $content );

		if ( $min > $count )
			return;

		$content_array   = str_split( $content );
		$word_array      = str_word_count( $content, 2 );
		$word_count      = 0;
		$next_page_count = 0;

		while ( count( $word_array ) > $min ) {

			$word_array = array_slice( $word_array, $each + $word_count, null, TRUE );
			$word_count = 0;

			foreach ( $word_array as $i => $word ) {

				if ( 'p' != $word ) {
					$word_count++;
					continue;
				}

				// found a '<p>'
				if ( '<' == $content_array[$i-1] )
					$k = $i-2;
				// found a '</p>'
				else
					$k = $i+3;

				$k = $k + ( $next_page_count * 15 );
				$next_page_count++;

				$content = substr( $content, 0, $k ).'<!--nextpage-->'.substr( $content, $k );
				break;
			}
		}

		$query->posts[0]->post_content = $content;
	}

	// TODO: add the link to wp_list_page ( or whatever! )
	// https://gist.github.com/danielbachhuber/1636361
	// filter canonical redirects so we can support full page URLs
	public function redirect_canonical( $redirect_url, $requested_url )
	{
		$url_endings = [
			'full',
			'pall',
		];

		if ( ( is_singular() || is_single() ) && in_array( trim( strtolower( substr( $requested_url, -5 ) ), '/' ), $url_endings ) )
			return trailingslashit( $requested_url );
		else
			return $redirect_url;
	}

	public function wp_default_editor( $r )
	{
		return gThemeOptions::info( 'default_editor', 'html' );
	}

	// not in admin
	// applying gMember filter
	public function the_author( $display_name )
	{
		$default = gThemeOptions::getOption( 'default_user', 0 );

		if ( is_feed() )
			return $default ? get_the_author_meta( 'display_name', $default ) : NULL;

		if ( $meta = gThemeEditorial::author( [ 'echo' => FALSE ] ) )
			return $meta;

		if ( ! $fallback = gThemeOptions::info( 'byline_fallback', TRUE ) )
			return NULL;

		return $default ? get_the_author_meta( 'display_name', $default ) : $display_name;
	}

	public function the_author_posts_link( $link )
	{
		return gThemeContent::byline( NULL, '', '', FALSE );
	}
}

// helper to reset post class
function gtheme_reset_post_class( $class = 'odd' ) { gTheme()->filters->reset_post_class( $class ); }
