<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );

gThemeImage::image( [
	'tag'    => 'half',
	'link'   => 'attachment',
	'empty'  => FALSE,
	'before' => '<div class="entry-cover alignleft">',
	'after'  => '</div>',
] );

gThemeContent::content();

gThemeNavigation::content( 'singular', TRUE, 'courses' );

gThemeEditorial::addendumAppendages( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-appendages after-rows">',
	'after'  => '</div>',
	'wrap'   => FALSE,
] );
