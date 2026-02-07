<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="entry-wrap-singlecolumn">';

gThemeSideBar::sidebar( 'entry-before', '<div class="wrap-side sidebar-entry-before">', '</div>' );

gThemeImage::image( [
	'tag'   => 'single',
	'link'  => 'attachment',
	'empty' => FALSE,
] );

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );
gThemeEditorial::metaHTML( 'lead', [ 'before' => '<div class="entry-lead">', 'after' => '</div>' ] );

if ( gThemeTerms::has( 'insert-people' ) )
	gThemeEditorial::personPicture( [ 'before' => '<div class="entry-person">', 'after' => '</div>' ] );

gThemeContent::content();
// gThemeContent::navigation();
gThemeContent::navigationFancy();

gThemeEditorial::theAction( [
	'before'     => '<div class="entry-after after-single after-action d-grid gap-2">',
	'after'      => '</div>',
	'link_class' => 'btn btn-lg btn-outline-primary',
] );

gThemeEditorial::theSource( [
	'before' => '<div class="entry-after after-single after-source text-end">'.gThemeOptions::info( 'source_before', '' ),
	'after'  => '</div>',
] );

gThemeEditorial::postLikeButton( [
	'before' => '<div class="entry-after after-single after-like my-2">',
	'after'  => '</div>',
] );

gThemeEditorial::refList( [
	'before' => '<div class="entry-after after-single after-reflist my-2">',
	'after'  => '</div>',
	'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
	'wrap'   => FALSE,
] );

gThemeEditorial::addendumAppendages( [
	'before' => '<div class="entry-after after-appendages after-rows my-2 -print-hide">',
	'after'  => '</div>',
	'wrap'   => FALSE,
] );

gThemeEditorial::venuePlace( [
	'before' => '<div class="entry-after after-venue-place after-rows my-2">',
	'after'  => '</div>',
	'wrap'   => FALSE,
	'title'  => sprintf( '<div class="-wrap-title"><h4 class="-title">%s</h4></div>', _x( 'Venue', 'Entry After Title', 'gtheme-ahmad' ) ),
] );

gThemeNavigation::content( 'singular', TRUE );

gThemeSideBar::sidebar( 'entry-after', '<div class="wrap-side sidebar-entry-after">', '</div>' );

echo '</div>';
