<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( array(
	'wrap_tag'  => 'div',
	'context'   => 'list',
	'prefix'    => 'row',
	'title_tag' => 'h3',
	'meta_tag'  => 'h5',
) );

gThemeContent::byline( NULL, '<div class="entry-byline">', '</div>' );
