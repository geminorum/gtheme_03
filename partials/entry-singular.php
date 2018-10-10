<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular' ] );
gThemeContent::byline( NULL, '<div class="entry-byline">', '</div>' );

gThemeEditorial::estimated( [
	'before' => '<div class="entry-before before-single before-estimated">',
	'after'  => '</div>',
] );

gThemeEditorial::lead( [ 'before' => '<div class="entry-lead">', 'after' => '</div>' ] );

if ( gThemeTerms::has( 'insert-people' ) )
	gThemeEditorial::personPicture( [ 'before' => '<div class="entry-person">', 'after' => '</div>' ] );

gThemeContent::content();

gThemeEditorial::source( [
	'before' => '<div class="entry-after after-single after-source">'.gThemeOptions::info( 'source_before', '' ),
	'after'  => '</div>',
] );

gThemeEditorial::refList( [
	'before' => '<div class="entry-after after-single after-reflist">',
	'after'  => '</div>',
	'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
] );
