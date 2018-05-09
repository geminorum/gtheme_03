<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular' ] );
gThemeContent::byline( NULL, '<div class="entry-byline">', '</div>' );

gThemeEditorial::lead( [ 'before' => '<div class="entry-lead">', 'after' => '</div>' ] );
gThemeContent::content();

gThemeEditorial::refList( [
	'before' => '<div class="entry-after after-single after-reflist">',
	'after'  => '</div>',
	'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
] );
