<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeImage::image( [
	'tag'   => 'single',
	'link'  => 'parent',
	'empty' => FALSE,
] );

gThemeContent::header( [ 'context' => 'index' ] );

gThemeEditorial::metaHTML( 'highlight', [
	'before'   => '<div class="entry-highlight">',
	'after'    => '</div>',
	'context'  => 'index',
	'fallback' => 'cover_blurb',
] );

gThemeContent::footer( [ 'context' => 'index' ] );
