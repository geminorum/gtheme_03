<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );

gThemeImage::image( [
	'tag'   => 'half',
	'link'  => 'attachment',
	'empty' => FALSE,
	'before' => '<div class="entry-cover alignleft">',
	'after'  => '</div>',
] );

gThemeContent::content();

gThemeEditorial::course( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-course after-rows">',
	'after'  => '</div>',
	'title'  => FALSE,
] );

gThemeNavigation::content( 'singular' );
