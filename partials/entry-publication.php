<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );

gThemeEditorial::bookCover( [
	'id'     => NULL, // current publication
	'link'   => 'attachment',
	'before' => '<div class="entry-cover alignleft">',
	'after'  => '</div>',
	'size'   => 'half',
	'wrap'   => FALSE,
] );

gThemeContent::content();

gThemeEditorial::bookMetaSummary( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-meta-summary after-rows">',
	'after'  => '</div>',
] );

gThemeEditorial::publication( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-publication after-rows">',
	'after'  => '</div>',
	'title'  => FALSE,
] );

gThemeNavigation::content( 'singular' );
