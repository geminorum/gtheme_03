<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

$gtheme_constants = [

	'GTHEME'               => 'gtheme',
	'GTHEME_VERSION'       => gThemeCore::version( 'gtheme_03' ),
	'GTHEME_CHILD_VERSION' => gThemeCore::version(),
	'GTHEME_TEXTDOMAIN'    => 'gtheme', // DEPRECATED
	'GTHEME_LOCALE'        => get_locale(),
	'GTHEME_DIR'           => get_template_directory(),
	'GTHEME_URL'           => get_template_directory_uri(),
	'GTHEME_CHILD_DIR'     => get_stylesheet_directory(),
	'GTHEME_CHILD_URL'     => get_stylesheet_directory_uri(),
	'GTHEME_HOME'          => home_url( '/' ),

	'GTHEME_PRINT_QUERY'       => 'print', // @SEE: gEditorial Paper
	'GTHEME_SYSTEMTAGS'        => 'system_tags',
	'GTHEME_PEOPLE_TAXONOMY'   => defined( 'GPEOPLE_PEOPLE_TAXONOMY' ) ? constant( 'GPEOPLE_PEOPLE_TAXONOMY' ) : 'people',
	'GTHEME_FRAGMENTCACHE'     => 'gtheme',
	'GTHEME_CACHETTL'          => 60 * 60 * 12, // 12 hours
	'GTHEME_IMAGES_META'       => '_gtheme_images',
	'GTHEME_IMAGES_TERMS_META' => '_gtheme_images_terms',

	// 'GTHEME_WIDGET_THETERM_DISABLED' => FALSE, // will define by theme to skip the term info display
	// 'GTHEME_SOCIAL_META_DISABLED'    => FALSE, // will define by theme to skip social meta tags

	// 'GTHEME_IS_SYSTEM_PAGE' => FALSE, // cannot set this early!
	// 'GTHEME_IS_WP_SIGNUP'   => FALSE, // cannot set this early!
	// 'GTHEME_IS_WP_ACTIVATE' => FALSE, // cannot set this early!
];

foreach ( $gtheme_constants as $key => $val )
	defined( $key ) or define( $key, $val );
