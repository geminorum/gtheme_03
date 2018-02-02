<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( array( 'context' => 'singular' ) );
gThemeContent::byline( NULL, '<div class="entry-byline">', '</div>' );

gThemeEditorial::lead( array( 'before' => '<div class="entry-lead">', 'after' => '</div>' ) );
gThemeContent::content();

gThemeEditorial::refList( array(
	'before' => '<div class="entry-after after-single after-reflist">',
	'after'  => '</div>',
	'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
) );
