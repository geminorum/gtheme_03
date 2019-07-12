<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeOptions extends gThemeModuleCore
{

	public static function defaults( $option = FALSE, $default = FALSE )
	{
		$blog_name = get_bloginfo( 'name', 'display' );

		$defaults = [
			'name'      => 'gtheme',
			'title'     => _x( 'gTheme', 'Theme Title', GTHEME_TEXTDOMAIN ),
			'sub_title' => FALSE, // 'gTheme Child',

			'blog_name'  => $blog_name,
			'logo_title' => _x( 'Home', 'Logo Title', GTHEME_TEXTDOMAIN ),

			// SETTINGS PAGE
			// 'settings_title' => _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ),
			// 'menu_title'     => _x( 'Theme Settings', 'Admin Menu Title', GTHEME_TEXTDOMAIN ),
			// 'settings_page'  => 'gtheme-theme',

			// ACCESSES
			// 'settings_access'    => 'edit_theme_options',
			// 'system_tags_access' => 'edit_others_posts',
			'editor_access'      => 'edit_others_posts', // FIXME: WTF

			// INTEGRATION WITH OTHER PLUGINS
			'supports' => [
				'gpersiandate'    => defined( 'GPERSIANDATE_VERSION' ),
				'geditorial-meta' => defined( 'GEDITORIAL_VERSION' ),
				'gpeople'         => defined( 'GPEOPLE_VERSION' ),
				'gshop'           => defined( 'GSHOP_VERSION' ),
				'zoom'            => TRUE,
			],

			// 'theme_groups' => FALSE, // [ 'main' => _x( 'Main' 'Options: Theme Group', GTHEME_TEXTDOMAIN ) ],
			'module_args'  => [],

			// MENUS
			// 'register_nav_menus'   => gThemeMenu::defaults(),
			// 'nav_menu_allowedtags' => [ 'p' ],

			// NAVIGATION
			// 'breadcrumb_support'   => TRUE, // hides the default inserts
			// 'breadcrumb_posttypes' => [ 'post' ],

			// SIDEBARS
			// 'sidebars'        => gThemeSideBar::defaults(),
			// 'sidebar_support' => TRUE, // hides the default inserts

			// MEDIA TAGS
			// 'images'                  => gThemeOptions::getDefaultImages(),
			// 'image_support'           => TRUE, // hides the default inserts
			// 'post_thumbnail_fallback' => TRUE,
			// 'thumbnail_image_size'    => 'single',
			// 'enclosure_image_size'    => 'single',
			// 'amp_image_size'          => 'single',

			// 'jpeg_quality'          => 82, // quality of JPEG images uploaded to WP
			// 'wp_editor_set_quality' => 82, // quality of JPEG images edited within WP

			// COUNTS API
			// 'counts' => gThemeCounts::defaults(),

			// PAGES API
			// 'pages_list'     => gThemePages::defaults(),
			// 'pages_pre_text' => _x( '[ This page is being completed ]', 'Options: Page Pre-Text', GTHEME_TEXTDOMAIN ),
			// 'pages_nav_menu' => 'primary',

			// PRIMARY TERMS API
			// 'primary_terms_legend'   => FALSE,
			// 'primary_terms_taxonomy' => 'category',
			// 'primary_terms_defaults' => [],

			'default_sep'        => ' ', // _x( ' ', 'Options: Separator: Default', GTHEME_TEXTDOMAIN ),
			// 'title_sep'          => _x( ' &raquo; ', 'Options: Separator: Title', GTHEME_TEXTDOMAIN ),
			// 'nav_sep'            => _x( ' &laquo; ', 'Options: Separator: Nav', GTHEME_TEXTDOMAIN ),
			// 'byline_sep'         => _x( ' | ', 'Options: Separator: Byline', GTHEME_TEXTDOMAIN ),
			// 'term_sep'           => _x( ', ', 'Options: Separator: Term', GTHEME_TEXTDOMAIN ),
			// 'embed_sep'          => _x( '; ', 'Options: Separator: Embed', GTHEME_TEXTDOMAIN ),
			// 'feed_sep'           => _x( '; ', 'Options: Separator: Feed', GTHEME_TEXTDOMAIN ),
			// 'comment_action_sep' => _x( ' | ', 'Options: Separator: Comment Action', GTHEME_TEXTDOMAIN ),

			// 'text_size_increase' => _x( '[ A+ ]', 'Options: Text Size Increase', GTHEME_TEXTDOMAIN ),
			// 'text_size_decrease' => _x( '[ A- ]', 'Options: Text Size Decrease', GTHEME_TEXTDOMAIN ),
			// 'text_size_sep'      => _x( ' / ', 'Options: Text Size Sep', GTHEME_TEXTDOMAIN ),

			// 'text_justify'     => _x( '[ Ju ]', 'Options: Text Justify', GTHEME_TEXTDOMAIN ),
			// 'text_unjustify'   => _x( '[ uJ ]', 'Options: Text Unjustify', GTHEME_TEXTDOMAIN ),
			// 'text_justify_sep' => _x( ' / ', 'Options: Text Justify Sep', GTHEME_TEXTDOMAIN ),

			'source_before' => _x( 'Source: ', 'Options: Source Before', GTHEME_TEXTDOMAIN ),
			'reflist_title' => sprintf( '<h4 class="-title" id="footnotes">%s</h4>', _x( 'Footnotes', 'Options: Reflist Title', GTHEME_TEXTDOMAIN ) ),

			'excerpt_length' => 40,
			'excerpt_more'   => '&nbsp;&hellip;', // FALSE: empty / TRUE: continueReading()

			// 'restricted_content' => FALSE, // set TURE to show teaser only
			// 'restricted_teaser'  => FALSE, // set FALSE to show teaser alongside
			// 'restricted_message' => '', // restricted notice
			// 'copy_disabled'      => FALSE, // set TRUE to make hard copying the contents!

			// 'read_more_text'  => 'Read more&nbsp;<span class="excerpt-link-hellip">&hellip;</span>',
			// 'read_more_title' => 'Continue reading &ldquo;%s&rdquo; &hellip;',
			// 'read_more_edit'  => FALSE,

			'rtl'    => is_rtl(),
			'locale' => get_locale(),

			// FEEDS
			// 'feed_str_replace' => gThemeFeed::defaultReplace(),

			// EMBED
			// 'embed_image_size' => 'single',

			// SEO
			// 'meta_image_size' => 'single',
			// 'meta_image_all'  => TRUE, // display fallback image for all pages
			// 'twitter_site'    => FALSE,
			// 'googlecse_cx'    => FALSE,

			'blog_title'      => self::getOption( 'blog_title', $blog_name ), // used on page title other than frontpage
			'frontpage_title' => self::getOption( 'frontpage_title', FALSE ), // FALSE to default
			'frontpage_desc'  => self::getOption( 'frontpage_desc', get_bloginfo( 'description', 'display' ) ), // FALSE to disable

			// 'default_image_src' => FALSE, // GTHEME_URL.'/images/document-large.png', // FIXME: MUST DEP
			'copyright'         => self::getOption( 'copyright', __( '&copy; All rights reserved.', GTHEME_TEXTDOMAIN ) ),
			// 'copyright_append_site_modified' => TRUE,
			// 'copyright_link_site_modified'   => '/archives/latest',

			// 'default_content_width' => 455, // setting global content_width // FALSE to default
			// 'full_content_width' => 455, // setting global content_width on fullwidthpage.php // FALSE to default

			// COMMENTS
			// 'comments_support'       => TRUE, // hides the default inserts
			// 'comments_disable_types' => [ 'attachment' ],
			// 'comment_callback'       => [ 'gThemeComments', 'comment_callback' ], // null to use wp core
			// 'comment_callback'       => [ 'gThemeBootstrap', 'commentCallback_BS3' ], // null to use wp core
			// 'comment_form'           => [ 'gThemeComments', 'comment_form' ], // comment_form to use wp core
			// 'comment_form_strings'   => [],
			// 'comment_nav_strings'    => [],
			// 'comment_action_strings' => [],
			// 'comments_closed'        => __( 'Comments are closed.', GTHEME_TEXTDOMAIN ), // set FALSE to hide the text
			// 'comment_awaiting'       => __( 'Your comment is awaiting moderation.', GTHEME_TEXTDOMAIN ), // set FALSE to hide the text

			// AVATARS
			// 'comment_avatar_size' => 75, // wp core is 32
			// 'default_avatar_src'  => GTHEME_URL.'/images/avatar.png',

			// SYSTEM TAGS
			// 'system_tags_cpt'      => [ 'post' ],
			// 'system_tags_excludes' => [ 'no-front', 'no-feed' ],
			// 'system_tags_defaults' => gThemeTerms::defaults(),

			// EDITOR
			// 'default_content'   => _x( '[content not available yet]', 'Editor Default Content', GTHEME_TEXTDOMAIN ),
			// 'mce_buttons'       => [], // 'superscript', 'subscript'
			// 'mce_buttons_2'     => [ 'styleselect' ],
			// 'mce_style_formats' => gThemeEditor::defaultFormats(),

			// 'settings_legend' => FALSE, // html content to appear after settings
			// FIXME: DEPRECATED: use PAGES API
			// 'search_page' => self::getOption( 'search_page', 0 ),

			// 'home_url_override' => '', // full escaped url to overrided home page / comment to disable
			// 'empty_search_query' => '', // string to use on search input form / comment to use default

			// 'post_actions_icons' => FALSE, // NEEDS: genericons css
			// 'post_actions' => [ // the order is important!
			// 	// 'textsize_buttons', // or 'textsize_buttons_nosep',
			// 	// 'textjustify_buttons_nosep', // 'textjustify_buttons', // or ,
			// 	'printlink',
			// 	'addtoany',
			// 	// 'addthis',
			// 	'shortlink',
			// 	'comments_link', // or 'comments_link_feed',
			// 	'edit_post_link',
			// 	// 'cat_list',
			// 	// 'tag_list',
			// 	// 'editorial_label',
			// 	// 'editorial_estimated',
			// 	// 'the_date',
			// 	// 'primary_term',
			// 	// 'categories',
			// 	// 'tags',
			// ],

			// 'byline_fallback' => TRUE, // if FALSE hides wp users

			// BANNERS API
			// 'banners_legend' => FALSE, // html before admin banners page
			// 'banner_groups'  => gThemeBanners::defaults(),

			// TEMPLATES
			// 'template_logo'        => '<a class="navbar-brand no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>',
			// 'template_term_link'   => '<a href="%1$s" title="%3$s" class="%4$s" data-html="true" data-toggle="tooltip" data-placement="top">%2$s</a>',
			// 'template_author_link' => '<a href="%1$s" title="%2$s" rel="author">%3$s</a>', // FIXME: no gThemeAuthors yet! / RENAMED
			// 'template_the_date'    => '<span class="date"><a href="%1$s"%2$s><time class="%5$s-time do-timeago" datetime="%3$s">%4$s</time></a></span>',
			// 'template_read_more'   => ' <a %6$s href="%1$s" aria-label="%3$s" class="%4$s">%2$s</a>%5$s',

			// DATE
			// 'date_posttypes'      => [ 'post' ],
			// 'date_format_byline'  => _x( 'j M Y', 'Options: Defaults: Date Format: Byline', GTHEME_TEXTDOMAIN ), // used on post by line
			// 'date_format_content' => _x( 'Y/j/m', 'Options: Defaults: Date Format: Content', GTHEME_TEXTDOMAIN ),  // not used yet!
			// 'date_format_day'     => _x( 'j M Y', 'Options: Defaults: Date Format: Day', GTHEME_TEXTDOMAIN ), // day navigation

			// PRINT
			// 'print_posttypes' => [ 'post' ],

			// ATTACHMENT
			// 'attachment_download_prefix' => '', // EXAMPLE: 'example.com-'
		];

		if ( FALSE === $option )
			return $defaults;

		if ( isset( $defaults[$option] ) )
			return $defaults[$option];

		return $default;
	}

	public static function getDefaultImages( $extra = [] )
	{
		return array_merge( [
			'raw' => self::registerImage( [
				'name'        => _x( 'Raw', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
				'description' => '',
				'width'       => 9999,
				'height'      => 9999,
				'crop'        => 0,
				'post_type'   => TRUE,
				'taxonomy'    => TRUE,
				'tag'         => TRUE,
				'insert'      => FALSE,
			] ),
			'big' => self::registerImage( [
				'name'        => _x( 'Big', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
				'description' => '',
				'width'       => 1280,
				'height'      => 720,
				'crop'        => 0,
				'post_type'   => TRUE,
				'taxonomy'    => TRUE,
				'tag'         => TRUE,
				'insert'      => TRUE,
			] ),
			'single' => self::registerImage( [
				'name'        => _x( 'Single', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
				'description' => '',
				'width'       => 1000,
				'height'      => 1000,
				'crop'        => 0,
				'post_type'   => TRUE,
				'taxonomy'    => TRUE,
				'tag'         => TRUE,
				'insert'      => TRUE,
			] ),
		], $extra );
	}

	public static function registerImage( $atts = [] )
	{
		$args = self::atts( [
			'name'        => __( 'Untitled' ),
			'description' => '',
			'width'       => 0,
			'height'      => 9999,
			'crop'        => FALSE,
			'post_type'   => [ 'post' ],
			'taxonomy'    => FALSE, // support for terms
			'tag'         => TRUE, // media tag
			'insert'      => FALSE, // insert in post
		], $atts );

		return [
			'n' => $args['name'],
			'd' => $args['description'],
			'w' => $args['width'],
			'h' => $args['height'],
			'c' => $args['crop'],
			'p' => $args['post_type'],
			't' => $args['taxonomy'],
			's' => $args['tag'],
			'i' => $args['insert'],
		];
	}

	// FIXME: DEPRECATED
	public static function register_image( $n, $w, $h = 9999, $c = 0, $t = TRUE, $i = FALSE, $p = [ 'post' ], $d = '' )
	{
		self::_dep( 'gThemeOptions::registerImage()' );

		return [
			'n' => $n, // name (title)
			'd' => $d, // description
			'w' => $w, // width
			'h' => $h, // height
			'c' => $c, // crop
			's' => $t, // media tag
			'i' => $i, // insert in post
			'p' => $p, // post_type
			't' => FALSE, // taxonomy
		];
	}

	public static function getOptions()
	{
		return get_option( constant( 'GTHEME' ) );
	}

	// FIXME: DEPRECATED: use gThemeOptions::getOptions();
	public static function get_options()
	{
		self::_dep( 'gThemeOptions::getOptions()' );
		return self::getOptions();
	}

	public static function updateOptions( $options )
	{
		return update_option( constant( 'GTHEME' ), $options );
	}

	// FIXME: DEPRECATED: use gThemeOptions::updateOptions();
	public static function update_options( $options )
	{
		self::_dep( 'gThemeOptions::updateOptions()' );
		return self::updateOptions( $options );
	}

	// FIXME: DEPRECATED: use gThemeOptions::getOption();
	public static function get_option( $name, $default = FALSE )
	{
		self::_dep( 'gThemeOptions::getOption()' );
		return self::getOption( $name, $default );
	}

	public static function getOption( $name, $default = FALSE )
	{
		global $gtheme_options;

		if ( empty( $gtheme_options ) )
			$gtheme_options = self::getOptions();

		if ( FALSE === $gtheme_options )
			$gtheme_options = [];

		if ( !isset( $gtheme_options[$name] ) )
			// $gtheme_options[$name] = $default;
			return $default;

		return $gtheme_options[$name];
	}

	public static function update_option( $name, $value )
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::getOptions();

		if ( $gtheme_options === FALSE )
			$gtheme_options = [];

		$gtheme_options[$name] = $value;

		return self::updateOptions( $gtheme_options );
	}

	public static function delete_option( $name )
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::getOptions();

		if ( $gtheme_options === FALSE )
			$gtheme_options = [];

		unset( $gtheme_options[$name] );

		return self::updateOptions( $gtheme_options );
	}

	public static function info( $info = FALSE, $default = FALSE )
	{
		global $gtheme_info;

		if ( empty( $gtheme_info ) )
			$gtheme_info = apply_filters( 'gtheme_get_info', self::defaults() );

		if ( FALSE === $info )
			return $gtheme_info;

		if ( isset( $gtheme_info[$info] ) )
			return $gtheme_info[$info];

		return $default;
	}

	public static function getGroup( $fallback = 'main' )
	{
		if ( $group = self::getOption( 'theme_group', FALSE ) )
			return $group;

		return $fallback;
	}

	public static function isGroup( $group )
	{
		return $group == self::getGroup();
	}

	// FIXME: DEPRECATED: use gThemeCounts::get()
	public static function count( $name, $def = 0 )
	{
		self::_dep( 'gThemeCounts::get()' );
		return gThemeCounts::get( $name, $def );
	}

	public static function supports( $plugins, $fallback = FALSE )
	{
		$supports = self::info( 'supports', [] );

		foreach ( (array) $plugins as $plugin )
			if ( isset( $supports[$plugin] ) )
				return $supports[$plugin];

		return $fallback;
	}

	public static function user_can( $role = 'editor' )
	{
		return self::cuc( self::info( $role.'_access', 'edit_others_posts' ), FALSE );
	}

	public static function editor_can( $then = TRUE, $not = FALSE )
	{
		return self::user_can( 'editor' ) ? $then : $not;
	}
}

// DEPRECATED / BACK COMP
function gtheme_get_info( $info = FALSE, $default = FALSE ) { return gThemeOptions::info( $info, $default ); }
function gtheme_get_option( $name, $default = FALSE ) { return gThemeOptions::getOption( $name, $default ); }
function gtheme_update_option( $name, $value ) { return gThemeOptions::update_option( $name, $value ); }
function gtheme_delete_option( $name ) { return gThemeOptions::delete_option( $name ); }
function gtheme_get_count( $name, $def = 0 ) { return gThemeOptions::count( $name, $def ); }
function gtheme_supports( $plugins, $if_not_set = FALSE ) { return gThemeOptions::supports( $plugins, $if_not_set ); }
