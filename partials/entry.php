<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'index' ] );
gThemeContent::byline( NULL, '<div class="entry-byline">', '</div>' );
gThemeContent::excerpt();

gThemeContent::footer( [
	'context' => 'index',
	'actions' => [
		'categories',
		'date',
	],
] );
