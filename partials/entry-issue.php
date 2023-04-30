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

gThemeEditorial::magazineSupported( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-issue after-rows">',
	'after'  => '</div>',
	'title'  => FALSE,
] );

gThemeNavigation::content( 'singular' );
