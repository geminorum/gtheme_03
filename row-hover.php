<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeImage::image( [
	'tag'   => 'single',
	'link'  => 'parent',
	'empty' => FALSE,
] );

echo '<div class="-hover">';

	gThemeContent::header( [
		'wrap_tag'  => 'div',
		'context'   => 'list',
		'prefix'    => 'row',
		'title_tag' => 'h3',
		'meta_tag'  => 'h5',
		'byline'    => TRUE,
	] );

	gThemeEditorial::metaHTML( 'dashboard' );

	// MAYBE: a continue reading button

echo '</div>';
