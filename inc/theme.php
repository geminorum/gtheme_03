<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTheme extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'cleanup'           => FALSE,   // @SEE: `gNetwork` Optimize
			'html_title'        => TRUE,    // @REF: https://make.wordpress.org/core/?p=11311
			'adminbar'          => TRUE,
			'wpcf7'             => TRUE,
			'page_excerpt'      => TRUE,
			'content_width'     => TRUE,    // @SEE: https://core.trac.wordpress.org/ticket/21256
			'feed_links'        => TRUE,    // Adds default posts and comments RSS feed links to HTML head.
			'post_formats'      => FALSE,
			'custom_background' => FALSE,
			'custom_header'     => FALSE,
			'custom_logo'       => TRUE,
			'custom_fontsizes'  => FALSE,
			'html5'             => TRUE,
			'js'                => FALSE,
			'hooks'             => FALSE,   // NO NEED
			'wc_support'        => gThemeWooCommerce::available(),
			'bp_support'        => TRUE,
			'bp_no_styles'      => TRUE,
			'print_support'     => TRUE,
			'alignwide_support' => FALSE,
			'childless_parent'  => gThemeUtilities::isChildless(),
		], $args ) );

		if ( $cleanup )
			$this->cleanup();

		if ( $html_title )
			add_theme_support( 'title-tag' );

		if ( $adminbar )
			// NOTE: To remove the default padding styles from WordPress for the Toolbar.
			add_theme_support( 'admin-bar', [ 'callback' => '__return_false' ] );

		if ( $wpcf7 && function_exists( 'wpcf7_enqueue_scripts' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts_wpcf7' ], 5 );
			add_filter( 'shortcode_atts_wpcf7', [ $this, 'shortcode_atts_wpcf7' ], 8 );
		}

		if ( $page_excerpt )
			add_post_type_support( 'page', 'excerpt' );

		if ( $content_width ) {

			$this->set_content_width( gThemeOptions::info( 'default_content_width', FALSE ) );

			if ( gThemeOptions::info( 'full_content_width', FALSE ) )
				add_action( 'template_redirect', static function() {
					if ( is_page_template( 'fullwidthpage.php' ) )
						$GLOBALS['content_width'] = gThemeOptions::info( 'full_content_width' );
				} );
		}

		if ( $feed_links )
			add_theme_support( 'automatic-feed-links' );

		if ( $wc_support )
			add_theme_support( 'woocommerce', gThemeOptions::info( 'woocommerce_support', gThemeWooCommerce::defaults() ) );

		if ( $bp_support )
			add_theme_support( 'buddypress' );

		add_filter( 'bp_use_theme_compat_with_current_theme', ( $bp_support ? '__return_true' : '__return_false' ) );

		if ( $bp_no_styles ) {

			add_action( 'wp_enqueue_scripts', [ $this, 'remove_bp_styles' ], 20 );  // `bp-legacy`
			add_filter( 'bp_nouveau_enqueue_styles', '__return_false', 20 );        // `bp-nouveau`

			self::define( 'GNETWORK_DISABLE_BUDDYPRESS_STYLES', TRUE );  // `gNetwork`
		}

		if ( $post_formats )
			add_theme_support( 'post-formats',
				gThemeOptions::info( 'support_post_formats', [
					'aside',   // Typically styled without a title. Similar to a Facebook note update.
					'gallery', // A gallery of images. Post will likely contain a gallery short-code and will have image attachments.
					'link',    // A link to another site. Themes may wish to use the first <a href=””> tag in the post content as the external link for that post. An alternative approach could be if the post consists only of a URL, then that will be the URL and the title (post_title) will be the name attached to the anchor for it.
					'image',   // A single image. The first <img /> tag in the post could be considered the image. Alternatively, if the post consists only of a URL, that will be the image URL and the title of the post (post_title) will be the title attribute for the image.
					'quote',   // A quotation. Probably will contain a `blockquote` holding the quote content. Alternatively, the quote may be just the content, with the source/author being the title.
					'status',  // A short status update, similar to a Twitter status update.
					'video',   // A single video. The first <video /> tag or object/embed in the post content could be considered the video. Alternatively, if the post consists only of a URL, that will be the video URL. May also contain the video as an attachment to the post, if video support is enabled on the blog (like via a plugin).
					'audio',   // An audio file. Could be used for Podcasting.
					// 'chat',    // A chat transcript.
			] ) );

		else
			add_action( 'init', static function() {
				remove_post_type_support( 'post', 'post-formats' );
			} );

		if ( $custom_background )
			add_theme_support( 'custom-background',
				gThemeOptions::info( 'support_custom_background', [
					'default-color'          => 'ffffff',
					'default-image'          => '',
					'wp-head-callback'       => '_custom_background_cb',
					'admin-head-callback'    => '',
					'admin-preview-callback' => '',
			] ) );

		if ( $custom_header )
			$this->_support_custom_headers();

		if ( $custom_logo )
			$this->_support_custom_logo();

		if ( $custom_fontsizes ) {

			add_theme_support( 'disable-custom-font-sizes' );

			add_theme_support( 'editor-font-sizes',
				gThemeOptions::info( 'editor_custom_fontsizes', [
					[
						'name'      => _x( 'Small', 'Editor Custom Font Sizes', 'gtheme' ),
						'shortName' => _x( 'S', 'Editor Custom Font Sizes', 'gtheme' ),
						'slug'      => 'small',
						'size'      => 12,
					],
					[
						'name'      => _x( 'Regular', 'Editor Custom Font Sizes', 'gtheme' ),
						'shortName' => _x( 'M', 'Editor Custom Font Sizes', 'gtheme' ),
						'slug'      => 'regular',
						'size'      => 16,
					],
					[
						'name'      => _x( 'Large', 'Editor Custom Font Sizes', 'gtheme' ),
						'shortName' => _x( 'L', 'Editor Custom Font Sizes', 'gtheme' ),
						'slug'      => 'large',
						'size'      => 36,
					],
					[
						'name'      => _x( 'Larger', 'Editor Custom Font Sizes', 'gtheme' ),
						'shortName' => _x( 'XL', 'Editor Custom Font Sizes', 'gtheme' ),
						'slug'      => 'larger',
						'size'      => 50,
					],
			] ) );
		}

		if ( $html5 )
			add_theme_support( 'html5',
				gThemeOptions::info( 'support_html5', [
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
			] ) );

		if ( $js )
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );

		if ( $hooks ) {

			// @REF: http://justintadlock.com/archives/2011/09/01/a-better-way-for-plugins-to-hook-into-theme-templates
			add_theme_support( 'template-hooks',
				gThemeOptions::info( 'support_template_hooks', [
					'gtheme_post_before',
					'gtheme_post_after',
					'gtheme_content_wrap_open',
					'gtheme_content_wrap_close',
					'gtheme_wrap_body_open',
					'gtheme_wrap_body_close',
					'gtheme_do_header',
					'gtheme_do_after_header',
					'gtheme_do_before_footer',
					'gtheme_do_footer',
			] ) );
		}

		if ( GTHEME_PRINT_QUERY && $print_support ) {
			add_action( 'init', static function() {
				add_rewrite_endpoint( GTHEME_PRINT_QUERY, EP_PERMALINK | EP_PAGES ); // FIXME: apply `print_posttypes` from info
			} );
		}

		if ( $alignwide_support ) {
			add_theme_support( 'align-wide' );
		}

		if ( ! $childless_parent )
			return;
	}

	// @REF: https://developer.wordpress.org/themes/functionality/custom-headers/
	private function _support_custom_headers()
	{
		add_theme_support( 'custom-header' ); // FIXME: add args!
	}

	// @REF: https://developer.wordpress.org/themes/functionality/custom-logo/
	// @REF: https://make.wordpress.org/core/2016/03/10/custom-logo/
	// @REF: https://make.wordpress.org/core/2020/07/28/themes-changes-related-to-get_custom_logo-in-wordpress-5-5/
	private function _support_custom_logo()
	{
		if ( ! $atts = gThemeOptions::info( 'custom_logo_support' ) )
			return FALSE;

		$args = self::atts( [
			'width'       => NULL,
			'height'      => NULL,
			'flex-width'  => TRUE,
			'flex-height' => TRUE,
			// 'header-text' => [
			// 	gThemeOptions::info( 'blog_name', '' ), // site-title
			// 	gThemeOptions::info( 'frontpage_desc', '' ), // site-description
			// ],

			// 'unlink-homepage-logo' => TRUE,
		], is_array( $atts ) ? $atts : [] );

		return add_theme_support( 'custom-logo', $args );
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

		// Completely remove the version number from pages and feeds
		add_filter( 'the_generator', '__return_null', 99 );

		remove_filter( 'comment_text', 'make_clickable', 9 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );
		foreach ( [ 'the_content', 'the_title', 'wp_title', 'document_title' ] as $filter )
			remove_filter( $filter, 'capital_P_dangit', 11 );
	}

	public function set_content_width( $width )
	{
		global $content_width;

		if ( ! $width )
			return FALSE;

		if ( ! empty( $content_width ) )
			return FALSE;

		return $content_width = (int) $width;
	}

	public function wp_enqueue_scripts_wpcf7()
	{
		add_filter( 'wpcf7_load_css', '__return_false', 12 ); // NOTE: styles will be added by the theme

		if ( 'contact' == gtheme_template_base() )
			add_filter( 'wpcf7_load_js', '__return_true', 12 );
		else
			add_filter( 'wpcf7_load_js', '__return_false', 12 );
	}

	public function shortcode_atts_wpcf7( $out )
	{
		wpcf7_enqueue_scripts();

		return $out;
	}

	public function wp_enqueue_scripts()
	{
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'gtheme-all', GTHEME_URL."/js/script.all$suffix.js", [ 'jquery' ], GTHEME_VERSION, TRUE );

		// NO NEED: we enqueue auto-size on comment form, and justify by its caller
		// if ( is_singular() || is_single() )
		// 	wp_enqueue_script( 'gtheme-singular', GTHEME_URL."/js/script.singular$suffix.js", [ 'jquery' ], GTHEME_VERSION, TRUE );
	}

	public function remove_bp_styles()
	{
		$handles = [
			'bp-legacy-css',
			'bp-legacy-css-rtl',
			'bp-parent-css',
			'bp-parent-css-rtl',
			'bp-child-css',
			'bp-child-css-rtl',
		];

		foreach ( $handles as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
}
