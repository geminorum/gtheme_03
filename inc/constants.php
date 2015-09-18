<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

$gtheme_constants = array(

	'GTHEME'               => 'gtheme',
	'GTHEME_VERSION'       => gThemeCore::version( 'gtheme_03' ),
	'GTHEME_CHILD_VERSION' => gThemeCore::version(),
	'GTHEME_TEXTDOMAIN'    => 'gtheme',
	'GTHEME_LOCALE'        => get_locale(),
	'GTHEME_DIR'           => get_template_directory(),
	'GTHEME_URL'           => get_template_directory_uri(),
	'GTHEME_CHILD_DIR'     => get_stylesheet_directory(),
	'GTHEME_CHILD_URL'     => get_stylesheet_directory_uri(),

	'GTHEME_SYSTEMTAGS'        => 'system_tags',
	'GTHEME_FRAGMENTCACHE'     => 'gtheme',
	'GTHEME_CACHETTL'          => 3600,
	'GTHEME_IMAGES_META'       => '_gtheme_images',
	'GTHEME_IMAGES_TERMS_META' => '_gtheme_images_terms',

	'GTHEME_FLUSH' => isset( $_GET['flush'] ), // FIXME: DEPRECATED
);

foreach ( $gtheme_constants as $key => $val )
	defined( $key ) or define( $key, $val );
