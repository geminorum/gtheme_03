<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( array( 'context' => 'index' ) );
gThemeContent::byline( NULL, '<div class="entry-byline">', '</div>' );
gThemeContent::excerpt();

gThemeContent::footer( array(
	'context' => 'index',
	'actions' => array(
		'categories',
		'date',
	),
) );
