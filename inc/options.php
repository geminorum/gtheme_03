<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeOptions extends gThemeModuleCore 
{

	public static function defaults( $option = false, $default = false )
	{
		$defaults = array(
			'name' => 'gtheme',
			'title' => _x( 'gTheme', 'Theme Title', GTHEME_TEXTDOMAIN ),
			'sub_title' => false, //'gTheme Child',
			
			// SETTINGS PAGE
			'menu_title' => _x( 'Theme Settings', 'Admin Menu Title', GTHEME_TEXTDOMAIN ),
			'settings_title' => _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ),
			'settings_page' => 'gtheme-theme',
			
			// ACCESSES
			'settings_access' => 'edit_theme_options',
			'editor_access' => 'edit_others_posts',
			
			// INTEGRATION WITH OTHER PLUGINS
			'supports' => array( // 3th party plugin supports
				'gmeta' => false,
				'geditorial-meta' => false,
				'gshop' => false,
				'gpeople' => false,
				'gpersiandate' => true,
				'gbook' => false,
				'query-multiple-taxonomies' => false,
				
				'zoom' => true,
			),
			
			'module_args' => array(
				
			),
			
			// NAVIGATION & MENUS
			'register_nav_menus' => array(
				'primary' => __( 'Primary Navigation', GTHEME_TEXTDOMAIN ),
				'secondary' => __( 'Secondary Navigation', GTHEME_TEXTDOMAIN ),
				'tertiary' => __( 'Tertiary Navigation', GTHEME_TEXTDOMAIN ),
			),
			'nav_menu_allowedtags' => array( 
				'p'
			),
			
			// SIDEBARS
			'sidebars' => array(
				'side-index' => _x( 'Index: Side', 'Sidebar Titles', GTHEME_TEXTDOMAIN ),
				'side-singular' => _x( 'Singular: Side', 'Sidebar Titles', GTHEME_TEXTDOMAIN ),
			),
		
			// MEDIA TAGS
			'images' => array(	// n-name, w-width, h-height, c-crop, d-description, p-for posts, t-media tag, i-insert
				'raw' => gThemeOptions::register_image( 
					_x( 'Raw', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					9999, 9999, 0,
					true, true
				),
				'big' => gThemeOptions::register_image( 
					_x( 'Big', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					1280, 720, 0,
					true, true
				),
				'single' => gThemeOptions::register_image( 
					_x( 'Single', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					1000, 1000, 0,
					true, true
				),
			),
			
			// COUNTS API
			'counts' => array(
				'dashboard' => array(
					'title' => __( 'Dashboard', GTHEME_TEXTDOMAIN ),
					'desc' => __( 'Dashboard Count', GTHEME_TEXTDOMAIN ),
					'def' => 5,
				),
				'latest' => array(
					'title' => __( 'Latest Posts', GTHEME_TEXTDOMAIN ),
					'desc' => __( 'Latest Posts Count', GTHEME_TEXTDOMAIN ),
					'def' => 5,
				),
			),
			
			'default_sep' => ' ',
			'title_sep' => is_rtl()? ' &laquo; ' : ' &raquo; ',
			'nav_sep' => is_rtl() ? ' &raquo; ' : ' &laquo; ',
			'byline_sep' => ' | ',
			'term_sep' => ', ',
			'comment_action_sep' => ' | ',
			
			'text_size_increase' => '[ A+ ]',
			'text_size_decrease' => '[ A- ]',
			'text_size_sep' => ' / ',
			
			'text_justify' => '[ Ju ]',
			'text_unjustify' => '[ uJ ]',
			'text_justify_sep' => ' / ',
			
			'excerpt_length' => 40,
			'excerpt_more' => ' &hellip;',
			'trim_excerpt_characters' => false, // set this to desired characters count. like : 300
			
			// comment to use default
			//'read_more_text' => '&hellip;',
			//'read_more_title' => '<a %1$s href="%2$s" title="Continue reading &ldquo;%3$s&rdquo; &hellip;" class="%4$s" >%5$s</a>%6$s',
			
			'rtl' => is_rtl(),
			'locale' => get_locale(),
			
			// FEEDS
			'feed_str_replace' => array( // TODO: make ltr compatible
				'<p>' => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
				'<p style="text-align: right;">' => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
				'<blockquote>' => '<blockquote style="direction:rtl;float:left;width:45%;maegin:20px 20px 20px 0;font-family:tahoma;line-height:22px;font-weight:bold;font-size:14px !important;">',
				'class="alignleft"' => 'style="float:left;margin-right:15px;"',
				'class="alignright"' => 'style="float:right;margin-left:15px;"',
				'class="aligncenter"' => 'style="margin-left:auto;margin-right:auto;text-align:center;"',
				'<h3>' => '<h3 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h4>' => '<h4 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h5>' => '<h5 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h6>' => '<h6 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<div class="lead">' => '<div style="color:#ccc;">',
				'<div class="label">' => '<div style="float:left;color:#333;">',
			),
			'enclosure_image_size' => 'single',
			
			// SEO
			'meta_image_size' => 'single',
			'rel_publisher' => false,
			'twitter_site' => false,
			'googlecse_cx' => false,
			
			'blog_title' => gtheme_get_option( 'blog_title', get_bloginfo( 'name' ) ), // used on page title other than frontpage
			'frontpage_title' => gtheme_get_option( 'frontpage_title', get_bloginfo( 'name' ) ), // set false to disable
			'frontpage_desc' => gtheme_get_option( 'frontpage_desc', get_bloginfo( 'description' ) ), // set false to disable
			
			'default_image_src' => GTHEME_URL.'/images/document.png',
			
			'copyright' => gtheme_get_option( 'copyright', __( '&copy; All right reserved.', GTHEME_TEXTDOMAIN ) ),
			
			// COMMENTS
			'comments_disable_types' => array( 'attachment' ),
			'comment_callback' => array( 'gThemeComments', 'comment_callback' ), // null to use wp core
			'comment_form' => array( 'gThemeComments', 'comment_form' ), // comment_form to use wp core
			//'comment_form_strings' => array(),
			//'comment_nav_strings' => array(),
			//'comment_action_strings' => array(),
			'comments_closed' => __( 'Comments are closed.', GTHEME_TEXTDOMAIN ), // set false to hide the text
			'comment_awaiting' => __( 'Your comment is awaiting moderation.', GTHEME_TEXTDOMAIN ), // set false to hide the text
			
			// AVATARS
			'comment_avatar_size' => 75, // wp core is 32
			'default_avatar_src' => GTHEME_URL.'/images/avatar.png',
			
			// SYSTEM TAGS
			'system_tags_cpt' => array( 'post' ),
			'system_tags_defaults' => array( 
				'dashboard' => _x( 'Dashboard', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'latest' => _x( 'Latest', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'no-front' => _x( 'No FrontPage', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
			),
			
			// EDITOR
			'default_content' => _x( '[content not available yet]', 'Editor Default Content', GTHEME_TEXTDOMAIN ),
			'mce_buttons' => array( 'sup', 'sub', 'hr' ),
			'mce_buttons_2' => array( 'styleselect' ),
			'mce_style_formats' => array(
				array(
					'title' => _x( 'Source', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'block' => 'p',
					'classes' => 'entry-source',
				),
				array(
					'title' => _x( 'Blockquote', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'block' => 'p',
					'classes' => 'entry-quote',
				),
				array(
					'title' => _x( 'Deleted', 'Editor Custom Class', GTHEME_TEXTDOMAIN ),
					'inline' => 'del',
				),
			),
			
			
			'settings_legend' => false, // html content to appear after settings
			'search_page' => gtheme_get_option( 'search_page', 0 ),
			
			// 'home_url_override' => '', // full escaped url to overrided home page / comment to disable
			// 'empty_search_query' => '', // string to use on search input form / comment to use default
			// 'the_date_format' => 'j M Y', // used on post by line
	
			'post_actions_icons' => false,
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
			
			//'js_tooltipsy' => false, // enables tooltipsy
			'before_tag_list' => '', // string before tag list
			
			'wpautop_with_br' => false, // set true to disable extra br removing
			'adjacent_empty' => '[&hellip;]', // next/prev link, if empty post title
			'head_viewport' => 'width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no', // html head viewport meta, for mobile support. set false to disable
			
			'strings_index_navline' => array( // string for index navline based on conditional tags
				//'category' => 'Category Archives for <strong>%s</strong>',
			),
			
			'author_link_template' => '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			'default_editor' => 'html', // set default editor of post edit screen to html for each user // needs module arg // Either 'tinymce', or 'html', or 'test'
			
			'child_group_class' => false, // body class for goruping the child theme on a network!
			'css_font_stack' => array( // list of font-faces to check after page load via FontDetect
				'Arial',
				'Tahoma',
			),
			
			// BANNERS
			'banners_legend' => false, // html before admin banners page
			'banner_groups' => array(
				'first' => _x( 'First', 'Banner Groups', GTHEME_TEXTDOMAIN ),
				'second' => _x( 'Second', 'Banner Groups', GTHEME_TEXTDOMAIN ),
			),
			
		);
		
		if ( false === $option )
			return $defaults;
			
		if( isset( $defaults[$option] ) )
			return $defaults[$option];
			
		return $default;	
	}	
	
	public static function register_image( $n, $w, $h = 9999, $c = 0, $t = true, $i = false, $p = array( 'post' ), $d = '' )
	{
		return array( 
			'n' => $n, // name
			'w' => $w, // width
			'h' => $h, // height
			'c' => $c, // crop
			't' => $t, // media tag
			'i' => $i, // insert in post
			'p' => $p, // post_type
			'd' => $d, // description
		);
	}

	public static function get_options()
	{
		return get_option( constant( 'GTHEME' ) );
	}
	
	public static function update_options( $options )
	{
		return update_option( constant( 'GTHEME' ), $options );
	}

	public static function get_option( $name, $default = false ) 
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::get_options();
			
		if ( $gtheme_options === false ) 
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
			$gtheme_options = self::get_options();

		if ( $gtheme_options === false ) 
			$gtheme_options = array();
		
		$gtheme_options[$name] = $value;
		
		return self::update_options( $gtheme_options );
	}

	public static function delete_option( $name ) 
	{
		global $gtheme_options;
		if ( empty(	$gtheme_options ) )
			$gtheme_options = self::get_options();

		if ( $gtheme_options === false ) 
			$gtheme_options = array();
		
		unset( $gtheme_options[$name] );
		
		return self::update_options( $gtheme_options );
	}	

	public static function info( $info = false, $default = false )
	{
		global $gtheme_info;
		
		if ( empty( $gtheme_info ) )
			$gtheme_info = apply_filters( 'gtheme_get_info', self::defaults() );
		
		if ( false === $info )
			return $gtheme_info;
			
		if( isset( $gtheme_info[$info] ) )
			return $gtheme_info[$info];
			
		return $default;	
	}
	
	public static function count( $name, $def = 0 )
	{
		$option_counts = self::get_option( 'counts', array() );
		if ( count( $option_counts ) && isset( $option_counts[$name] ) )
			return $option_counts[$name];
		
		$info_counts = self::info( 'counts', array() );
		if ( count( $info_counts ) && isset( $info_counts[$name] )  )
			return $info_counts[$name]['def'];
			
		return $def;
	}

	public static function supports( $plugins, $if_not_set = false ) 
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
		return current_user_can( self::info( $role.'_access', 'edit_others_posts' ) );
	}
	
	public static function editor_can( $then = true, $not = false )
	{
		return self::user_can( 'editor' ) ? $then : $not;
	}
}

// DEPRECATED / BACK COMP
function gtheme_get_info( $info = false, $default = false ) { return gThemeOptions::info( $info, $default ); }
function gtheme_get_option( $name, $default = false ) { return gThemeOptions::get_option( $name, $default ); }
function gtheme_update_option( $name, $value ) { return gThemeOptions::update_option( $name, $value ); }
function gtheme_delete_option( $name ) { return gThemeOptions::delete_option( $name ); }
function gtheme_get_count( $name, $def = 0 ){ return gThemeOptions::count( $name, $def ); }
function gtheme_supports( $plugins, $if_not_set = false ) { return gThemeOptions::supports( $plugins, $if_not_set ); }
