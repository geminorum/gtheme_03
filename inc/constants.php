<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

defined( 'GTHEME' ) or define( 'GTHEME', 'gtheme' );
defined( 'GTHEME_VERSION' ) or define( 'GTHEME_VERSION', gThemeCore::version() );
defined( 'GTHEME_TEXTDOMAIN' ) or define( 'GTHEME_TEXTDOMAIN', 'gtheme' );
defined( 'GTHEME_DIR' ) or define( 'GTHEME_DIR', get_template_directory() );
defined( 'GTHEME_URL' ) or define( 'GTHEME_URL', get_template_directory_uri() );
defined( 'GTHEME_CHILD_DIR' ) or define( 'GTHEME_CHILD_DIR',  get_stylesheet_directory() );
defined( 'GTHEME_CHILD_URL' ) or define( 'GTHEME_CHILD_URL', get_stylesheet_directory_uri() );

defined( 'GTHEME_SYSTEMTAGS' ) or define( 'GTHEME_SYSTEMTAGS', 'system_tags' );
defined( 'GTHEME_FRAGMENTCACHE' ) or define( 'GTHEME_FRAGMENTCACHE', 'gtheme' );
defined( 'GTHEME_CACHETTL' ) or define( 'GTHEME_CACHETTL', 3600 );
defined( 'GTHEME_IMAGES_META' ) or define( 'GTHEME_IMAGES_META', '_gtheme_images' );
defined( 'GTHEME_IMAGES_TERMS_META' ) or define( 'GTHEME_IMAGES_TERMS_META', '_gtheme_images_terms' );

if ( ! defined( 'GTHEME_FLUSH' ) && ( isset( $_GET['flush'] ) && 'flush' == $_GET['flush'] ) ) 
	define( 'GTHEME_FLUSH', true ); 
else 
	define( 'GTHEME_FLUSH', false );
