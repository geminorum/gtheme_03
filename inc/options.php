<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeOptions extends gThemeModuleCore {

	function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'cleanup' => true,
		), $args ) );
		
		if ( $cleanup ) {
		}
	}
		
	public static function defaults( $option = false, $default = false ){
		$defaults = array(
			'name' => 'gtheme',
			'title' => 'gTheme',
			'sub_title' => false, //'gTheme Child',
			'menu_title' => __( 'Theme Settings', GTHEME_TEXTDOMAIN ),
			'settings_title' => __( 'gTheme Settings', GTHEME_TEXTDOMAIN ),
			'settings_page' => 'gtheme-theme',
			'settings_access' => 'edit_theme_options',
			//'admin_bar_support' => constant( 'GTHEME_ADMINBAR' ), // better: dep
			
			'supports' => array( // 3th party plugin supports
				'gmeta' => false,
				'geditorial-meta' => false,
				'gshop' => false,
				'gpeople' => false,
				'gpersiandate' => true,
				'gbook' => false,
				'query-multiple-taxonomies' => false,
			),
			
			'module_args' => array(
				
			),
			
			'register_nav_menus' => array(
				'primary' => __( 'Primary Navigation', GTHEME_TEXTDOMAIN ),
				'secondary' => __( 'Secondary Navigation', GTHEME_TEXTDOMAIN ),
				'tertiary' => __( 'Tertiary Navigation', GTHEME_TEXTDOMAIN ),
			),
			'nav_menu_allowedtags' => array( 
				'p'
			),
			
			'sidebar_args_func' => 'gtheme_sidebar_args',
			'sidebars' => apply_filters( 'gtheme_sidebars', array(
				'side-index' => 'Index - Side',
				//'side-front' => 'Front - Side',
				//'side-single' => 'Single - Side',
				//'side-page' => 'Page - Side',
				//'foot-index-right' => 'Right - Index - Foot',
				//'foot-index-left' => 'Left - Index - Foot',
				//'content-index-before' => 'Before - Index - Content',
				//'content-index-after' => 'After - Index - Content',
				//'content-front-before' => 'Before - Front - Content',
				//'content-front-after' => 'After - Front - Content',
				//'content-single-before' => 'Before - Index - Single',
				//'content-single-after' => 'After - Index - Single',
			) ),
		
			'def_primary_cats' => array(),
			
			'images' => array(	// n-name, w-width, h-height, c-crop, d-description, p-for posts, t-media tag, i-insert
				'raw' => array( 'n' => __( 'Raw', GTHEME_TEXTDOMAIN ), 'w' => 9999, 'h' => 9999, 'c' => 0, 'd' => '', 'p' => array( 'post' ), 't' => true, 'i' => true, ),
				'dashboard' => array( 'n' => __( 'Dashboard', GTHEME_TEXTDOMAIN ), 'w' => 292, 'h' => 472, 'c' => 0, 'd' => '', 'p' => array( 'post' ), 't' => true, 'i' => true, ),
				'single' => array( 'n' => __( 'Single', GTHEME_TEXTDOMAIN ), 'w' => 604, 'h' => 977, 'c' => 0, 'd' => '', 'p' => array( 'post' ), 't' => true, 'i' => true, ),
				'late' => array( 'n' => __( 'Late', GTHEME_TEXTDOMAIN ), 'w' => 214, 'h' => 346, 'c' => 0, 'd' => '', 'p' => array( 'post' ), 't' => true, 'i' => true, ),
				'entry' => array( 'n' => __( 'Entry', GTHEME_TEXTDOMAIN ), 'w' => 58, 'h' => 94, 'c' => 1, 'd' => '', 'p' => array( 'post' ), 't' => true, 'i' => true, ),
				'content' => array( 'n' => __( 'Content', GTHEME_TEXTDOMAIN ), 'w' => 448, 'h' => 725, 'c' => 0, 'd' => '', 'p' => array( 'post' ), 't' => false, 'i' => true, ),
				'content-half' => array( 'n' => __( 'Content Half', GTHEME_TEXTDOMAIN ), 'w' => 214, 'h' => 346, 'c' => 0, 'd' => '', 'p' => array( 'post' ), 't' => false, 'i' => true, ),
				
				'issue-top' => array( 'n' => __( 'Issue Horizontal', GTHEME_TEXTDOMAIN ), 'w' => 448, 'h' => 725, 'c' => 0, 'd' => '', 'p' => array( 'issue' ), 't' => true, 'i' => true, ),
				'issue-side' => array( 'n' => __( 'Issue Cover', GTHEME_TEXTDOMAIN ), 'w' => 448, 'h' => 725, 'c' => 0, 'd' => '', 'p' => array( 'issue' ), 't' => true, 'i' => true, ),
				'issue-full' => array( 'n' => __( 'Issue Full', GTHEME_TEXTDOMAIN ), 'w' => 448, 'h' => 725, 'c' => 0, 'd' => '', 'p' => array( 'issue' ), 't' => true, 'i' => true, ),
				
				// add_image_size( 'sidebar-thumb', 120, 120, true ); // Hard Crop Mode
				// add_image_size( 'homepage-thumb', 220, 180 ); // Soft Crop Mode
				// add_image_size( 'singlepost-thumb', 590, 9999 ); // Unlimited Height Mode
				
				
			),
			'enqueue_scripts' => array(
				'all' => array(
					'gtheme-all',
					//'gtheme-tooltipster', // http://calebjacob.com/tooltipster/
					//'gtheme-dim-background', // https://github.com/andywer/jquery-dim-background
				),
				'home' => array(),
				'singular' => array(
					'gtheme-singular',
				
				),
			),
			'enqueue_styles' => array(
				'all' => array(
					//'elusive-webfont',
					//'genericons',
				
				),
				'home' => array(),
				'singular' => array(),
			),
			
			
			'counts' => array(
				'dashboard' => array(
					'title' => __( 'Dashboard', GTHEME_TEXTDOMAIN ),
					'desc' => __( 'Dashboard Count', GTHEME_TEXTDOMAIN ),
					'def' => 5,
				),
				'latest' => array(
					'title' => __( 'Latest Posts', GTHEME_TEXTDOMAIN ),
					'desc' => __( 'Latest Posts Count', GTHEME_TEXTDOMAIN ),
					'def' => 7,
				),
			),
			
			'default_sep' => ' ',
			'title_sep' => ' &laquo; ',
			'nav_sep' => ' &raquo; ',
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
			'meta_image_size' => 'single',
			'rel_publisher' => false,
			'twitter_site' => false,
			'googlecse_cx' => false,
			
			'blog_title' => gtheme_get_option( 'blog_title', get_bloginfo( 'name' ) ), // used on page title other than frontpage
			'frontpage_title' => gtheme_get_option( 'frontpage_title', get_bloginfo( 'name' ) ), // set false to disable
			'frontpage_desc' => gtheme_get_option( 'frontpage_desc', get_bloginfo( 'description' ) ), // set false to disable
			'default_image_src' => GTHEME_URL.'/images/default_doc.png', // set false to disable
			'copyright' => gtheme_get_option( 'copyright', '&copy; All right reserved.' ),
			
			'comment_callback' => array( 'gThemeComments', 'comment_callback' ), // null to use wp core
			'comment_form' => array( 'gThemeComments', 'comment_form' ), // comment_form to use wp core
			'comment_form_defaults' => array(
				'title_reply' => __( 'Leave a Reply' ),
				'title_reply_to' => __( 'Leave a Reply to %s' ),
				'cancel_reply_link' => __( 'Cancel reply' ),
				'label_submit' => __( 'Post Comment' ),
			),
			//'comment_form_reply' => __( 'Reply', GTHEME_TEXTDOMAIN ),
			//'comments_closed' => __( 'Comments are closed.' , GTHEME_TEXTDOMAIN ), // set empty to hide the text
			'comment_avatar_size' => 50, // wp core is 32
			'default_avatar_src' => GTHEME_URL.'/images/default_avatar.png',
			
			
			//'support_system_tags' => true, // dep
			'system_tags_cpt' => array( 'post' ),
			//'support_p2p_for_posts' => true, // dep
			
			'teeny_mce_buttons' => array(),
			'mce_buttons' => array( 'sup', 'sub', 'hr' ),
			'mce_buttons_2' => array( 'styleselect' ),
			'mce_advanced_styles' => array( 
				__( 'Warning', GTHEME_TEXTDOMAIN ) => 'warning',
				__( 'Notice', GTHEME_TEXTDOMAIN ) => 'notice',
				__( 'Download', GTHEME_TEXTDOMAIN ) => 'download',
				__( 'Testimonial', GTHEME_TEXTDOMAIN ) => 'testimonial box',
			),
			//'mce_style_formats' => array();
			
			'settings_legend' => false, // html content ro appear after settings
			
			
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
			/**
			EXAMPE : it's working, just uncomment.
			'adjacent_links' => array( // define how the next prev link should appear on wp_head link rel. false to disable
				'entry' => array(
					'same_term' => true,
					'ex_terms' => '',
					'taxonomy' => 'section',
					'orderby' => 'menu_order',
				),
			),
			**/
			
			'wpautop_with_br' => false, // set true to disable extra br removing
			'adjacent_empty' => '[&hellip;]', // next/prev link, if empty post title
			'head_viewport' => 'width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no', // html head viewport meta, for mobile support. set false to disable
			
			'strings_index_navline' => array( // string for index navline based on conditional tags
				//'category' => 'Category Archives for <strong>%s</strong>',
			),
			
			'author_link_template' => '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			'default_editor' => 'html', // set default editor of post edit screen to html for each user // needs module arg // Either 'tinymce', or 'html', or 'test'
			
			'child_group_class' => false, // body class for goruping the child theme on a network!
			
			
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
	
	
}

function gtheme_get_info( $info = false, $default = false ){
	global $gtheme_info;
	if ( empty( $gtheme_info ) )
		$gtheme_info = apply_filters( 'gtheme_get_info', gThemeOptions::defaults() );
	
	if ( false === $info )
		return $gtheme_info;
	if( isset( $gtheme_info[$info] ) )
		return $gtheme_info[$info];
	return $default;	
}


function gtheme_get_option( $name, $default = false ) {
	global $gtheme_options;
	if ( empty(	$gtheme_options ) )
		$gtheme_options = get_option( constant( 'GTHEME' ) );
		
	if ( $gtheme_options === false ) 
		$gtheme_options = array();
		
	if ( !isset( $gtheme_options[$name] ) ) 
		//$gtheme_options[$name] = $default;
		return $default;
	
	return $gtheme_options[$name];
}

function gtheme_update_option( $name, $value ) {
	global $gtheme_options;
	if ( empty(	$gtheme_options ) )
		$gtheme_options = get_option( constant( 'GTHEME' ) );

	if ( $gtheme_options === false ) 
		$gtheme_options = array();
	
	//unset ( $gtheme_options[$name] );
	$gtheme_options[$name] = $value;
	
	return update_option( constant( 'GTHEME' ), $gtheme_options );
}

function gtheme_delete_option( $name ) {
	global $gtheme_options;
	if ( empty(	$gtheme_options ) )
		$gtheme_options = get_option( constant( 'GTHEME' ) );

	if ( $gtheme_options === false ) 
		$gtheme_options = array();
	
	unset( $gtheme_options[$name] );
	
	return update_option( constant( 'GTHEME' ), $gtheme_options );
}

function gtheme_get_count( $name, $def = 0 ){
	$option_counts = gtheme_get_option( 'counts', array() );
	if ( count( $option_counts ) && isset( $option_counts[$name] ) )
		return $option_counts[$name];
	
	$info_counts = gtheme_get_info( 'counts', array() );
	if ( count( $info_counts ) && isset( $info_counts[$name] )  )
		return $info_counts[$name]['def'];
		
	return $def;
}

function gtheme_supports( $plugins, $if_not_set = false ) {
	$supports = gtheme_get_info( 'supports', array() );
	
	if ( is_array( $plugins ) )
		foreach ( $plugins as $plugin )
			if ( isset( $supports[$plugin] ) )
				return $supports[$plugin];
	
	if ( isset( $supports[$plugins] ) )
		return $supports[$plugins];
		
	return $if_not_set;
}

function gtheme_get_banner( $group, $order = 0 ) {
	$banners = gtheme_get_option( 'banners', array() );
	foreach ( $banners as $banner ) {
		if ( isset( $banner['group'] ) && $group == $banner['group'] ) {
			if ( isset( $banner['order'] ) && $order == $banner['order'] ) {
				return $banner;
			}
		}
	}
	return false;
}

function gtheme_banner( $banner, $atts = array() ){
	//if ( false === $banner ) return;

	$args = shortcode_atts( array(
		'w' => 'auto',
		'h' => 'auto',
		'c' => '#fff',
		'img_class' => 'img-responsive',
		'a_class' => 'gtheme-banner',
		'img_style' => '',
		'a_style' => '',
		'placeholder' => true,
	), $atts );
		
	$html = '';
	$title = isset( $banner['title'] ) && $banner['title'] ? $banner['title'] : '' ;
	
	if ( isset( $banner['image'] ) && $banner['image'] && 'http://' != $banner['image'] )
		$html .= '<img src="'.$banner['image'].'" alt="'.$title.'" class="'.$args['img_class'].'" style="'.$args['img_style'].'" />';
	else if ( $args['placeholder'] )
		$html .= '<div style="display:block;width:'.$args['w'].';height:'.$args['h'].';background-color:'.$args['c'].';" ></div>';
		
	if ( isset( $banner['url'] ) && $banner['url'] && 'http://' != $banner['url'] )
		$html = '<a href="'.$banner['url'].'" title="'.$title.'" class="'.$args['a_class'].'" style="'.$args['a_style'].'">'.$html.'</a>';

	if ( ! empty ( $html ) )
		echo $html;
}
