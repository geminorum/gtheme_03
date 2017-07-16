<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeOptions extends gThemeModuleCore
{

	public static function defaults( $option = FALSE, $default = FALSE )
	{
		$blog_name = get_bloginfo( 'name', 'display' );

		$defaults = array(
			'name'      => 'gtheme',
			'title'     => _x( 'gTheme', 'Theme Title', GTHEME_TEXTDOMAIN ),
			'sub_title' => FALSE, //'gTheme Child',

			'blog_name'  => $blog_name,
			'logo_title' => _x( 'Home', 'Logo Title', GTHEME_TEXTDOMAIN ),

			// SETTINGS PAGE
			'menu_title'     => _x( 'Theme Settings', 'Admin Menu Title', GTHEME_TEXTDOMAIN ),
			'settings_title' => _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ),
			'settings_page'  => 'gtheme-theme',

			// ACCESSES
			'settings_access' => 'edit_theme_options',
			'editor_access'   => 'edit_others_posts',

			// INTEGRATION WITH OTHER PLUGINS
			'supports' => array( // 3th party plugin supports
				'gmeta'                     => FALSE,
				'geditorial-meta'           => FALSE,
				'gshop'                     => FALSE,
				'gpeople'                   => FALSE,
				'gpersiandate'              => TRUE,
				'gbook'                     => FALSE,
				'query-multiple-taxonomies' => FALSE,
				'zoom'                      => TRUE,
			),

			'module_args' => array(),

			// NAVIGATION & MENUS
			'register_nav_menus' => array(
				'primary'   => __( 'Primary Navigation', GTHEME_TEXTDOMAIN ),
				'secondary' => __( 'Secondary Navigation', GTHEME_TEXTDOMAIN ),
				'tertiary'  => __( 'Tertiary Navigation', GTHEME_TEXTDOMAIN ),
			),
			'nav_menu_allowedtags' => array( 'p' ),

			// SIDEBARS
			'sidebars' => array(
				'side-index'    => _x( 'Index: Side', 'Sidebar Titles', GTHEME_TEXTDOMAIN ),
				'side-singular' => _x( 'Singular: Side', 'Sidebar Titles', GTHEME_TEXTDOMAIN ),
			),

			// MEDIA TAGS
			'images' => array(	// n-name, w-width, h-height, c-crop, d-description, p-for posts, t-media tag, i-insert
				'raw' => gThemeOptions::register_image(
					_x( 'Raw', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					9999, 9999, 0,
					TRUE, TRUE
				),
				'big' => gThemeOptions::register_image(
					_x( 'Big', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					1280, 720, 0,
					TRUE, TRUE
				),
				'single' => gThemeOptions::register_image(
					_x( 'Single', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					1000, 1000, 0,
					TRUE, TRUE
				),
			),
			'thumbnail_image_size' => 'single',
			'enclosure_image_size' => 'single',

			// 'jpeg_quality'          => 82, // quality of JPEG images uploaded to WP
			// 'wp_editor_set_quality' => 82, // quality of JPEG images edited within WP

			// COUNTS API
			'counts' => gThemeCounts::defaults(),  // NOTE: use gThemeCounts::defaults( array( -extra / override -) )

			// PAGES API
			'pages_list'     => gThemePages::defaults(), // NOTE: use gThemePages::defaults( array( -extra / override -) )
			'pages_pre_map'  => gThemePages::defaultPages(), // NOTE: use gThemePages::defaultPages( array( -extra / override -) )
			'pages_pre_text' => _x( '[ This page is being completed ]', 'Options: Page Pre-Text', GTHEME_TEXTDOMAIN ),

			// PRIMARY TERMS API
			'primary_terms_legend'   => FALSE,
			'primary_terms_taxonomy' => 'category',
			'primary_terms_defaults' => array(),

			'default_sep'        => _x( ' ', 'Options: Separator: Default', GTHEME_TEXTDOMAIN ),
			'title_sep'          => _x( ' &raquo; ', 'Options: Separator: Title', GTHEME_TEXTDOMAIN ),
			'nav_sep'            => _x( ' &laquo; ', 'Options: Separator: Nav', GTHEME_TEXTDOMAIN ),
			'byline_sep'         => _x( ' | ', 'Options: Separator: Byline', GTHEME_TEXTDOMAIN ),
			'term_sep'           => _x( ', ', 'Options: Separator: Term', GTHEME_TEXTDOMAIN ),
			'feed_sep'           => _x( '; ', 'Options: Separator: Feed', GTHEME_TEXTDOMAIN ),
			'comment_action_sep' => _x( ' | ', 'Options: Separator: Comment Action', GTHEME_TEXTDOMAIN ),

			'text_size_increase' => _x( '[ A+ ]', 'Options: Text Size Increase', GTHEME_TEXTDOMAIN ),
			'text_size_decrease' => _x( '[ A- ]', 'Options: Text Size Decrease', GTHEME_TEXTDOMAIN ),
			'text_size_sep'      => _x( ' / ', 'Options: Text Size Sep', GTHEME_TEXTDOMAIN ),

			'text_justify'     => _x( '[ Ju ]', 'Options: Text Justify', GTHEME_TEXTDOMAIN ),
			'text_unjustify'   => _x( '[ uJ ]', 'Options: Text Unjustify', GTHEME_TEXTDOMAIN ),
			'text_justify_sep' => _x( ' / ', 'Options: Text Justify Sep', GTHEME_TEXTDOMAIN ),

			'excerpt_length'          => 40,
			'excerpt_more'            => ' &hellip;',
			'trim_excerpt_characters' => FALSE, // set this to desired characters count. like : 300

			// commented to use default

			// 'restricted_content' => FALSE, // set TURE to show teaser only
			// 'restricted_teaser'  => FALSE, // set FALSE to show teaser alongside
			// 'restricted_message' => '', // restricted notice
			// 'copy_disabled'      => FALSE, // set TRUE to make hard copying the contents!

			// 'read_more_text'  => 'Read more&nbsp;<span class="excerpt-link-hellip">&hellip;</span>',
			// 'read_more_title' => 'Continue reading &ldquo;%s&rdquo; &hellip;',
			// 'read_more_edit'  => FALSE,

			'rtl'    => is_rtl(),
			'locale' => get_locale(),

			// FIXME: make ltr compatible
			// FEEDS
			'feed_str_replace' => array(
				'<p>'                            => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
				'<p style="text-align: right;">' => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
				'<blockquote>'                   => '<blockquote style="direction:rtl;float:left;width:45%;maegin:20px 20px 20px 0;font-family:tahoma;line-height:22px;font-weight:bold;font-size:14px !important;">',
				'class="alignleft"'              => 'style="float:left;margin-right:15px;"',
				'class="alignright"'             => 'style="float:right;margin-left:15px;"',
				'class="aligncenter"'            => 'style="margin-left:auto;margin-right:auto;text-align:center;"',
				'<h3>'                           => '<h3 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h4>'                           => '<h4 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h5>'                           => '<h5 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h6>'                           => '<h6 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<div class="lead">'             => '<div style="color:#ccc;">',
				'<div class="label">'            => '<div style="float:left;color:#333;">',
			),

			// SEO
			'meta_image_size' => 'single',
			'rel_publisher'   => FALSE,
			'twitter_site'    => FALSE,
			'googlecse_cx'    => FALSE,

			'blog_title'      => self::getOption( 'blog_title', $blog_name ), // used on page title other than frontpage
			'frontpage_title' => self::getOption( 'frontpage_title', FALSE ), // FALSE to default
			'frontpage_desc'  => self::getOption( 'frontpage_desc', get_bloginfo( 'description', 'display' ) ), // FALSE to disable

			'default_image_src' => GTHEME_URL.'/images/document-large.png', // FIXME: MUST DEP
			'copyright'         => self::getOption( 'copyright', __( '&copy; All right reserved.', GTHEME_TEXTDOMAIN ) ),

			// 'default_content_width' => 455, // setting global content_width // FALSE to default
			// 'full_content_width' => 455, // setting global content_width on fullwidthpage.php // FALSE to default

			// COMMENTS
			'comments_disable_types' => array( 'attachment' ),
			'comment_callback'       => array( 'gThemeComments', 'comment_callback' ), // null to use wp core
			'comment_form'           => array( 'gThemeComments', 'comment_form' ), // comment_form to use wp core
			// 'comment_form_strings'   => array(),
			// 'comment_nav_strings'    => array(),
			// 'comment_action_strings' => array(),
			'comments_closed'        => __( 'Comments are closed.', GTHEME_TEXTDOMAIN ), // set FALSE to hide the text
			'comment_awaiting'       => __( 'Your comment is awaiting moderation.', GTHEME_TEXTDOMAIN ), // set FALSE to hide the text

			// AVATARS
			'comment_avatar_size' => 75, // wp core is 32
			'default_avatar_src'  => GTHEME_URL.'/images/avatar.png',

			// SYSTEM TAGS
			'system_tags_cpt'      => array( 'post' ),
			'system_tags_excludes' => array( 'no-front', 'no-feed' ),
			'system_tags_defaults' => array(
				'dashboard'    => _x( 'Dashboard', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'featured'     => _x( 'Featured', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'latest'       => _x( 'Latest', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'tile'         => _x( 'Tile', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'full-article' => _x( 'Full Article', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'poster'       => _x( 'Poster Entry', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'no-front'     => _x( 'Not on FrontPage', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'no-feed'      => _x( 'Not on Feed', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
			),

			// EDITOR
			'default_content'   => _x( '[content not available yet]', 'Editor Default Content', GTHEME_TEXTDOMAIN ),
			'mce_buttons'       => array(), // 'superscript', 'subscript'
			'mce_buttons_2'     => array( 'styleselect' ),
			'mce_style_formats' => array(
				array(
					'title'   => _x( 'Blockquote', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'block'   => 'blockquote',
					'classes' => 'entry-quote',
				),
				array(
					'title'    => _x( 'Unordered List', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'selector' => 'ul', // http://wordpress.stackexchange.com/a/85071
					'classes'  => 'entry-list',
				),
				array(
					'title'    => _x( 'Ordered List', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'selector' => 'ol',
					'classes'  => 'entry-list',
				),
				array(
					'title'   => _x( 'Note', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'block'   => 'p',
					'classes' => 'entry-note',
				),
				array(
					'title'   => _x( 'Source', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'block'   => 'p',
					'classes' => 'entry-source',
				),
			),

			'settings_legend' => FALSE, // html content to appear after settings
			// FIXME: DEPRECATED: use PAGES API
			// 'search_page' => self::getOption( 'search_page', 0 ),

			// 'home_url_override' => '', // full escaped url to overrided home page / comment to disable
			// 'empty_search_query' => '', // string to use on search input form / comment to use default

			'post_actions_icons' => FALSE,
			'post_actions' => array( // the order is important!
				'textsize_buttons', // or 'textsize_buttons_nosep',
				'textjustify_buttons_nosep', // 'textjustify_buttons', // or ,
				'printfriendly',
				'a2a_dd',
				'shortlink',
				'comments_link_feed', // or 'comments_link',
				'edit_post_link',
				//'tag_list',
			),

			// 'js_tooltipsy'    => FALSE, // enables tooltipsy

			'wpautop_with_br' => FALSE, // set TRUE to disable extra br removing
			'adjacent_empty'  => '[&hellip;]', // next/prev link, if empty post title
			'head_viewport'   => 'width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no', // html head viewport meta, for mobile support. set FALSE to disable

			'strings_index_navline' => array( // string for index navline based on conditional tags
				// 'category' => 'Category Archives for <strong>%s</strong>',
			),

			'default_editor'       => 'html', // set default editor of post edit screen to html for each user // needs module arg // Either 'tinymce', or 'html', or 'test'

			'additional_body_class' => FALSE, // body class just in case!
			'child_group_class'     => FALSE, // body class for goruping the child theme on a network!
			'css_font_stack'        => array( // list of font-faces to check after page load via FontDetect
				'Arial',
				'Tahoma',
			),

			// BANNERS API
			'banners_legend' => FALSE, // html before admin banners page
			'banner_groups'  => array(
				'first'  => _x( 'First', 'Banner Groups', GTHEME_TEXTDOMAIN ),
				'second' => _x( 'Second', 'Banner Groups', GTHEME_TEXTDOMAIN ),
			),

			// TEMPLATES
			// 'template_logo'        => '<a class="navbar-brand no-outline" href="%1$s" title="%3$s" rel="home"><h1 class="text-hide main-logo">%2$s</h1></a>',
			// 'template_term_link'   => '<a href="%1$s" title="%3$s" class="%4$s" data-html="true" data-toggle="tooltip" data-placement="top">%2$s</a>',
			// 'template_author_link' => '<a href="%1$s" title="%2$s" rel="author">%3$s</a>', // FIXME: no gThemeAuthors yet! / RENAMED
			// 'template_the_date'    => '<span class="date"><a href="%1$s" title="%2$s" rel="shortlink"><time class="%5$s-date" datetime="%3$s">%4$s</time></a></span>',

			// DATE FORMATS
			'date_format_byline' => _x( 'j M Y', 'Options: Defaults: Date Format: Byline', GTHEME_TEXTDOMAIN ), // used on post by line
			'date_format_day'    => _x( 'j M Y', 'Options: Defaults: Date Format: Day', GTHEME_TEXTDOMAIN ), // day navigation

			// ATTACHMENT
			'attachment_download_prefix' => '', // EXAMPLE: 'example.com-'
		);

		if ( FALSE === $option )
			return $defaults;

		if ( isset( $defaults[$option] ) )
			return $defaults[$option];

		return $default;
	}

	public static function registerImage( $atts = array() )
	{
		$args = self::atts( array(
			'name'        => __( 'Untitled' ),
			'description' => '',
			'width'       => 0,
			'height'      => 9999,
			'crop'        => FALSE,
			'post_type'   => array( 'post' ),
			'taxonomy'    => FALSE, // support for terms
			'tag'         => TRUE, // media tag
			'insert'      => FALSE, // insert in post
		), $atts );

		return array(
			'n' => $args['name'],
			'd' => $args['description'],
			'w' => $args['width'],
			'h' => $args['height'],
			'c' => $args['crop'],
			'p' => $args['post_type'],
			't' => $args['taxonomy'],
			's' => $args['tag'],
			'i' => $args['insert'],
		);
	}

	// FIXME: DEPRICATED
	public static function register_image( $n, $w, $h = 9999, $c = 0, $t = TRUE, $i = FALSE, $p = array( 'post' ), $d = '' )
	{
		self::__dep( 'gThemeOptions::registerImage()' );
		return array(
			'n' => $n, // name (title)
			'd' => $d, // description
			'w' => $w, // width
			'h' => $h, // height
			'c' => $c, // crop
			's' => $t, // media tag
			'i' => $i, // insert in post
			'p' => $p, // post_type
			't' => FALSE, // taxonomy
		);
	}

	public static function getOptions()
	{
		return get_option( constant( 'GTHEME' ) );
	}

	// FIXME: DEPRECATED: use gThemeOptions::getOptions();
	public static function get_options()
	{
		self::__dep( 'gThemeOptions::getOptions()' );
		return self::getOptions();
	}

	public static function updateOptions( $options )
	{
		return update_option( constant( 'GTHEME' ), $options );
	}

	// FIXME: DEPRECATED: use gThemeOptions::updateOptions();
	public static function update_options( $options )
	{
		self::__dep( 'gThemeOptions::updateOptions()' );
		return self::updateOptions( $options );
	}

	// FIXME: DEPRECATED: use gThemeOptions::getOption();
	public static function get_option( $name, $default = FALSE )
	{
		self::__dep( 'gThemeOptions::getOption()' );
		return self::getOption( $name, $default );
	}

	public static function getOption( $name, $default = FALSE )
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::getOptions();

		if ( $gtheme_options === FALSE )
			$gtheme_options = array();

		if ( !isset( $gtheme_options[$name] ) )
			//$gtheme_options[$name] = $default;
			return $default;

		return $gtheme_options[$name];
	}

	public static function update_option( $name, $value )
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::getOptions();

		if ( $gtheme_options === FALSE )
			$gtheme_options = array();

		$gtheme_options[$name] = $value;

		return self::updateOptions( $gtheme_options );
	}

	public static function delete_option( $name )
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::getOptions();

		if ( $gtheme_options === FALSE )
			$gtheme_options = array();

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

	// FIXME: DEPRECATED: use gThemeCounts::get()
	public static function count( $name, $def = 0 )
	{
		self::__dep( 'gThemeCounts::get()' );
		return gThemeCounts::get( $name, $def );
	}

	public static function supports( $plugins, $if_not_set = FALSE )
	{
		$supports = self::info( 'supports', array() );

		if ( is_array( $plugins ) )
			foreach ( $plugins as $plugin )
				if ( isset( $supports[$plugin] ) )
					return $supports[$plugin];

		if ( isset( $supports[$plugins] ) )
			return $supports[$plugins];

		return $if_not_set;
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
