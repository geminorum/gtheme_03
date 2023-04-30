<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );

gThemeEditorial::theCover( [
	'link'   => 'attachment',
	'before' => '<div class="entry-cover alignleft">',
	'after'  => '</div>',
	'size'   => 'half',
	'wrap'   => FALSE,
] );

gThemeContent::content();

gThemeNavigation::content( 'singular' );
