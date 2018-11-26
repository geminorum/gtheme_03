<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'index', 'byline' => TRUE ] );
gThemeContent::excerpt();

gThemeContent::footer( [
	'context' => 'index',
	'actions' => [
		'categories',
		'date',
	],
] );
