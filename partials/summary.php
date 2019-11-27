<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeImage::image( [
	'tag'   => 'single',
	'link'  => 'parent',
	'empty' => FALSE,
] );

gThemeContent::header( [ 'context' => 'index', 'byline' => TRUE ] );
gThemeContent::excerpt();
gThemeContent::footer( [ 'context' => 'index' ] );
