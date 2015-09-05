<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeFilters extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'content_extra'      => FALSE,
			'auto_paginate'      => FALSE,
			'redirect_canonical' => FALSE,
			'default_editor'     => FALSE,
			'disable_autoembed'  => TRUE,
		), $args ) );

		add_action( 'wp_head', array( &$this, 'wp_head' ), 5 );
		add_filter( 'body_class', array( &$this, 'body_class' ), 10, 2 );
		add_filter( 'post_class', array( &$this, 'post_class' ), 10, 3 );
		add_filter( 'wp_title', array( &$this, 'wp_title' ), 5, 3 );
		add_filter( 'get_wp_title_rss', array( &$this, 'get_wp_title_rss' ) );

		add_filter( 'the_excerpt', array( &$this, 'the_excerpt' ), 5 );
		add_filter( 'excerpt_length', array( &$this, 'excerpt_length' ) );
		add_filter( 'excerpt_more', array( &$this, 'excerpt_more' ) );

		if ( gThemeOptions::info( 'trim_excerpt_characters', FALSE ) ) {
			remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
			add_filter( 'get_the_excerpt', array( &$this, 'get_the_excerpt' ) );
		}

		add_filter( 'the_content', array( &$this, 'the_content' ), 15 );
		add_filter( 'the_content_more_link', array( &$this, 'the_content_more_link' ) );

		if ( $content_extra )
			add_filter( 'the_content', array( &$this, 'the_content_extra' ), 16 );

		if ( $auto_paginate )
			add_action( 'loop_start', array( &$this, 'loop_start' ) );

		if ( $redirect_canonical )
			add_filter( 'redirect_canonical', array( &$this, 'redirect_canonical' ), 10, 2 );

		if ( $default_editor )
			add_filter( 'wp_default_editor', array( &$this, 'wp_default_editor' ) );

		// gNetwork Cite Shortcode: [reflist]
		add_filter( 'shortcode_atts_reflist', array( &$this, 'shortcode_atts_reflist' ), 10, 3 );

		// https://gist.github.com/ocean90/3796628
		// disables the auto-embeds function in WordPress 3.5
		if ( $disable_autoembed )
			remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );

		// to remove wp recent comments widget styles
		add_filter( 'show_recent_comments_widget_style', '__return_false' );
	}

	public function wp_head()
	{
		// override mobile media query support
		$viewport = gThemeOptions::info( 'head_viewport', 'width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no' );
		if ( $viewport )
			echo "\t".'<meta name="viewport" content="'.$viewport.'" />'."\n";

		// prevent search bots from indexing search results
		if ( is_search() )
			echo "\t".'<meta name="robots" content="noindex, nofollow" />'."\n";


		$args = array(
			'ver' => GTHEME_CHILD_VERSION,
		);

		if ( gThemeUtilities::isDev() )
			$args['debug'] = '';

		if ( ! gThemeUtilities::isRTL() )
			$args['ltr'] = '';

		if ( is_singular() && gThemeUtilities::isPrint() ) {
			$css = file_exists( GTHEME_CHILD_DIR.'/print.css' ) ? GTHEME_CHILD_URL.'/print.css' : GTHEME_URL.'/print.css';
		} else {
			$css = file_exists( GTHEME_CHILD_DIR.'/css/css.php' ) ? GTHEME_CHILD_URL.'/css/' : GTHEME_CHILD_URL.'/style.css';
		}

		gThemeUtilities::linkStyleSheet( $css, $args, 'all' );

		if ( gThemeUtilities::isDev() && ! gThemeUtilities::isPrint() )
			gThemeUtilities::linkStyleSheet( GTHEME_URL.'/css/dev.css', GTHEME_VERSION, 'all' );

		if ( is_singular() )
			echo "\t".'<link rel="pingback" href="'.get_bloginfo( 'pingback_url' ).'" />'."\n";
	}

	public function body_class( $classes, $class )
	{
		global $post, $pagenow;
		$gtheme_info = gThemeOptions::info();

		if ( ! is_array( $classes ) )
			$classes = (array) $classes;

		$classes[] = 'gtheme';

		if ( $gtheme_info['name'] != 'gtheme' )
			$classes[] = $gtheme_info['name'];

		if ( $gtheme_info['additional_body_class'] )
			$classes[] = $gtheme_info['additional_body_class'];

		if ( $gtheme_info['child_group_class'] )
			$classes[] = $gtheme_info['child_group_class'];

		$extra = gThemeOptions::get_option( 'body_class_extra', FALSE );
		if ( $extra )
			$classes[] = sanitize_html_class( $extra );

		if ( ! empty( $pagenow ) )
			$classes[] = sanitize_html_class( 'page-'.str_ireplace( '.php', '', $pagenow ) );

		if ( is_page() )
			$classes[] = sanitize_html_class( 'slug-'.gThemeContent::slug() );

		if ( gThemeUtilities::isDev() )
			$classes[] = 'stage-development';

		if ( constant( 'WP_DEBUG' ) )
			$classes[] = 'wp-debug';

		if ( ! gThemeUtilities::isRTL() )
			$classes[] = 'ltr';

		if ( is_single() )
			foreach ( get_the_category() as $category )
			   $classes[] = 'cat-'.$category->slug;

		if ( is_singular() )
			$classes[] = $post->post_type.'-'.$post->post_name;

		$uri = explode( '/', $_SERVER['REQUEST_URI'] );
		if ( isset( $uri[1] ) )
			$classes[] = htmlentities( trim( strip_tags( $uri[1] ) ) );

		/*
		// http://www.wprecipes.com/how-to-automatically-add-a-class-to-body_class-if-theres-a-sidebar
		// todo : get sidebar list from gThemeOptions::info()
		if ( is_active_sidebar( 'sidebar' ) )
			$classes[] = 'has_sidebar';
		*/

		return $classes;
	}

	var $_current_post_class = 'odd';

	public function post_class( $classes, $class, $post_ID )
	{
		if ( is_admin() )
			return $classes;

		global $wp_query, $post;

		$classes[] = 'pf-content'; // print friendly / for : gThemeContent::printfriendly()

		if ( ( is_archive() || is_home() ) ) {

			if ( FALSE !== strpos( $post->post_content, '<!--more-->' ) )
				$classes[] = 'more';

			if ( 0 == $wp_query->current_post )
				$classes[] = 'first';

			$classes[] = $this->_current_post_class;
			$this->_current_post_class = ( 'odd' == $this->_current_post_class ) ? 'even' : 'odd';
		}

		foreach ( get_the_category() as $category )
		   $classes[] = 'cat-'.$category->slug;

		return $classes;
	}

	// helper to reset post class
	public function reset_post_class( $class = 'odd' )
	{
		$this->_current_post_class = $class;
	}

	public function wp_title( $title, $sep, $seplocation )
	{
		if ( is_feed() )
			return $title;

		if ( empty( $title ) ) {
			global $page, $paged;
			$sep = gThemeOptions::info( 'title_sep', ' &raquo; ' );

			$frontpage_title = gThemeOptions::info( 'frontpage_title', get_bloginfo( 'name' ) );
			if ( FALSE === $frontpage_title )
				$frontpage_title = '';

			if ( 2 <= $paged || 2 <= $page )
				$frontpage_title .= $sep.sprintf( __( 'Page %s', GTHEME_TEXTDOMAIN ), number_format_i18n( max( $paged, $page ) ) );

			return $frontpage_title;
		}

		// sep already added
		return $title.' '.gThemeOptions::info( 'blog_title', get_bloginfo( 'name' ) );
	}

	public function get_wp_title_rss( $title )
	{
		if ( empty( $title ) )
			return $title;

		return $title.trim( gThemeOptions::info( 'title_sep', '&#187;' ) );
	}

	public function the_excerpt( $text )
	{
		// return $text.' '.gThemeContent::continue_reading( get_edit_post_link() );
		return $text.' '.gThemeContent::continue_reading();
	}

	public function excerpt_length( $length )
	{
		return gThemeOptions::info( 'excerpt_length', 40 );
	}

	public function excerpt_more( $more )
	{
		return gThemeOptions::info( 'excerpt_more', ' &hellip;' );
	}

	// Originally from : http://wordpress.org/extend/plugins/character-count-excerpt/
	public function get_the_excerpt( $text, $excerpt_length = FALSE )
	{
		if ( FALSE === $excerpt_length )
			$excerpt_length = gThemeOptions::info( 'trim_excerpt_characters', 300 );

		if ( FALSE === $excerpt_length )
			return wp_trim_excerpt( $text );

		$raw_excerpt = $text;

		if ( '' == $text ) {
			$text = get_the_content('');
			$text = strip_shortcodes( $text );
			$text = apply_filters( 'the_content', $text );
			$text = str_replace( ']]>', ']]&gt;', $text );
			$text = strip_tags( $text );

			if ( strlen( $text ) > $excerpt_length ) {
				$excerpt_more = apply_filters( 'excerpt_more', ' '.'[...]' );
				$text = substr( $text, 0, $excerpt_length + 1 );

				$words = preg_split( "/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY );

				// if the last character is not a white space, we remove the cut off last word
				preg_match( "/[\n\r\t ]+/", $text, $lastchar, 0, $excerpt_length );
				if ( empty( $lastchar ) ) array_pop( $words );

				$text = implode(' ', $words);
				$text = $text . $excerpt_more;
			}
		}

		return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
	}

	public function the_content( $content )
	{
		// remove empty paragraph tags, and remove broken paragraph tags from around block level elements.
		// based on : https://github.com/ninnypants/remove-empty-p
		// by : ninnypants
		if ( gThemeOptions::info( 'content_remove_empty_p', TRUE ) ) {

			// clean up p tags around block elements
			$content = preg_replace( array(
				'#<p>\s*<(div|aside|section|article|header|footer)#',
				'#</(div|aside|section|article|header|footer)>\s*</p>#',
				'#</(div|aside|section|article|header|footer)>\s*<br ?/?>#',
				'#<(div|aside|section|article|header|footer)(.*?)>\s*</p>#',
				'#<p>\s*</(div|aside|section|article|header|footer)#',
			), array(
				'<$1',
				'</$1>',
				'</$1>',
				'<$1$2>',
				'</$1',
			), $content );

			$content = preg_replace('#<p>(\s|&nbsp;)*+(<br\s*/*>)*(\s|&nbsp;)*</p>#i', '', $content );
		}

		// http://css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/
		if ( gThemeOptions::info( 'content_remove_image_p', TRUE ) )
			$content = preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content );

	   // http://bavotasan.com/2009/removing-images-from-a-wordpress-post/
	   // preg_replace( '/<img[^>]+./','', $content );

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

	// https://gist.github.com/danielbachhuber/6691084
	// auto-paginate after 500 words, but respect paragraphs and don't leave page stubs.
	public function loop_start( $query )
	{
		if ( ! is_single()
		  || ! $query->is_main_query()
		  || 'post' != get_post_type() )
			return;

		$min  = gThemeOptions::info( 'post_auto_paginate_min', 900 );
		$each = gThemeOptions::info( 'post_auto_paginate_each', 500 );

		$content = $query->posts[0]->post_content;

		if ( $min > str_word_count( $content ) )
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

				// Found a '<p>'
				if ( '<' == $content_array[$i-1] )
					$k = $i-2;
				// Found a '</p>'
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

	// TODO : add the link to wp_list_page ( or whatever! )
	// https://gist.github.com/danielbachhuber/1636361
	// filter canonical redirects so we can support full page URLs
	public function redirect_canonical( $redirect_url, $requested_url )
	{
		$url_endings = array(
			'full',
			'pall',
		);

		if ( is_singular() && in_array( trim( strtolower( substr( $requested_url, -5 ) ), '/' ), $url_endings ) )
			return trailingslashit( $requested_url );
		else
			return $redirect_url;
	}

	public function wp_default_editor( $r )
	{
		return gThemeOptions::info( 'default_editor', 'html' );
	}

	// gNetwork
	public function shortcode_atts_reflist( $out, $pairs, $atts )
	{
		$out['number'] = TRUE;
		return $out;
	}
}

// helper to reset post class
function gtheme_reset_post_class( $class = 'odd' ) { gTheme()->filters->reset_post_class( $class ); }
