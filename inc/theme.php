<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTheme extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'cleanup'       => TRUE,
			'html_title'    => TRUE, // @REF: https://make.wordpress.org/core/?p=11311
			'adminbar'      => TRUE,
			'wpcf7'         => TRUE,
			'page_excerpt'  => TRUE,
			'content_width' => TRUE, // @SEE: https://core.trac.wordpress.org/ticket/21256
			'feed_links'    => TRUE,
			'post_formats'  => FALSE,
			'html5'         => TRUE,
			'js'            => FALSE,
			'hooks'         => TRUE, // @REF: https://is.gd/4ORzuI
			'bp_support'    => TRUE,
			'bp_no_styles'  => FALSE,
			'print_support' => TRUE,
		], $args ) );

		if ( $cleanup )
			$this->cleanup();

		if ( $html_title )
			add_theme_support( 'title-tag' );

		if ( $adminbar )
			add_theme_support( 'admin-bar', [ 'callback' => '__return_false' ] );

		if ( $wpcf7 && function_exists( 'wpcf7_enqueue_scripts' ) )
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts_wpcf7' ], 5 );

		if ( $page_excerpt )
			add_post_type_support( 'page', 'excerpt' );

		if ( $content_width ) {

			$this->set_content_width( gThemeOptions::info( 'default_content_width', FALSE ) );

			if ( gThemeOptions::info( 'full_content_width', FALSE ) )
				add_action( 'template_redirect', function(){
					if ( is_page_template( 'fullwidthpage.php' ) )
						$GLOBALS['content_width'] = gThemeOptions::info( 'full_content_width' );
				} );
		}

		if ( $feed_links )
			add_theme_support( 'automatic-feed-links' );

		if ( $bp_support )
			add_theme_support( 'buddypress' );

		add_filter( 'bp_use_theme_compat_with_current_theme', ( $bp_support ? '__return_true' : '__return_false' ) );

		if ( $bp_no_styles ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'remove_bp_styles' ], 20 ); // bp-legacy
			add_filter( 'bp_nouveau_enqueue_styles', '__return_false', 20 ); // bp-nouveau
		}

		if ( $post_formats )
			add_theme_support( 'post-formats',
				gThemeOptions::info( 'support_post_formats', [
					'aside',
					'link',
					'gallery',
					'status',
					'quote',
					'image',
			] ) );

		else
			add_action( 'init', function(){
				remove_post_type_support( 'post', 'post-formats' );
			} );

		if ( $html5 )
			add_theme_support( 'html5',
				gThemeOptions::info( 'support_html5', [
					'comment-list',
					'search-form',
					'comment-form',
					'gallery',
					'caption',
			] ) );

		if ( $js )
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );

		if ( $hooks )
			add_theme_support( 'template-hooks',
				gThemeOptions::info( 'support_template_hooks', [
					'before_post',
					'after_post',
					'template_body_top',
					// 'template_before_loop',
					// 'template_after_loop',
					// 'template_after_sidebar',
					'gtheme_do_header',
					'gtheme_do_after_header',
					'gtheme_do_before_footer',
					'gtheme_do_footer',
			] ) );

		if ( $print_support ) {
			add_action( 'init', function() {
				add_rewrite_endpoint( GTHEME_PRINT_QUERY, EP_PERMALINK | EP_PAGES );
			} );
		}
	}

	public function cleanup()
	{
		foreach ( [
			'rss2_head',
			'commentsrss2_head',
			'rss_head',
			'rdf_header',
			'atom_head',
			'comments_atom_head',
			'opml_head',
			'app_head',
			] as $action ) remove_action( $action, 'the_generator' );

		remove_action( 'wp_head', 'locale_stylesheet' );
		remove_action( 'embed_head', 'locale_stylesheet', 30 );
		remove_action( 'wp_head', 'wp_generator' );

		// completely remove the version number from pages and feeds
		add_filter( 'the_generator', '__return_null', 99 );

		remove_filter( 'comment_text', 'make_clickable', 9 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );
		foreach ( [ 'the_content', 'the_title', 'wp_title' ] as $filter )
			remove_filter( $filter, 'capital_P_dangit', 11 );
	}

	public function set_content_width( $width )
	{
		global $content_width;

		if ( ! $width )
			return FALSE;

		if ( ! empty( $content_width ) )
			return FALSE;

		return $content_width = intval( $width );
	}

	public function wp_enqueue_scripts_wpcf7()
	{
		add_filter( 'wpcf7_load_css', '__return_false', 12 ); // styles will be added by the theme

		if ( 'contact' == gtheme_template_base() )
			add_filter( 'wpcf7_load_js', '__return_true', 12 );
		else
			add_filter( 'wpcf7_load_js', '__return_false', 12 );
	}

	public function wp_enqueue_scripts()
	{
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'gtheme-all', GTHEME_URL."/js/script.all$suffix.js", [ 'jquery' ], GTHEME_VERSION, TRUE );

		// NO NEED: we enqueue autosize on comment form, and justify by it's caller
		// if ( is_singular() || is_single() )
		// 	wp_enqueue_script( 'gtheme-singular', GTHEME_URL."/js/script.singular$suffix.js", [ 'jquery' ], GTHEME_VERSION, TRUE );
	}

	public function remove_bp_styles()
	{
		global $wp_styles;

		$handles = [
			'bp-legacy-css',
			'bp-legacy-css-rtl',
			'bp-parent-css',
			'bp-parent-css-rtl',
			'bp-child-css',
			'bp-child-css-rtl',
		];

		foreach ( $handles as $handle )
			$wp_styles->dequeue( $handle );
	}
}
