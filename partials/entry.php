<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

// Flow: Cover -> Header -> Summary/Content

echo '<div class="entry-double-top"><div class="-wrap">';

gThemeSideBar::sidebar( 'entry-double-before', '<div class="wrap-side sidebar-entry-double-before">', '</div>' );

echo '</div></div><div class="-wrap splitrow"><div class="-side entry-double-head"><div class="-wrap">';

gThemeImage::image( [
	'tag'   => 'single',
	'link'  => 'attachment',
	'empty' => FALSE,
] );

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );

echo '</div></div><div class="-side entry-double-main"><div class="-wrap">';

gThemeEditorial::metaHTML( 'lead', [ 'before' => '<div class="entry-lead">', 'after' => '</div>' ] );

if ( gThemeTerms::has( 'insert-people' ) )
	gThemeEditorial::personPicture( [ 'before' => '<div class="entry-person">', 'after' => '</div>' ] );

gThemeContent::content();
gThemeContent::navigation();

gThemeEditorial::theSource( [
	'before' => '<div class="entry-after after-single after-source">'.gThemeOptions::info( 'source_before', '' ),
	'after'  => '</div>',
] );

gThemeEditorial::postLikeButton( [ 'before' => '<div class="entry-after after-single after-like">', 'after' => '</div>' ] );

gThemeEditorial::refList( [
	'before' => '<div class="entry-after after-single after-reflist">',
	'after'  => '</div>',
	'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
	'wrap'   => FALSE,
] );

gThemeSideBar::sidebar( 'entry-double-content', '<div class="wrap-side sidebar-entry-double-content">', '</div>' );

echo '</div></div><div class="-side entry-double-foot"><div class="-wrap">';

gThemeSideBar::sidebar( 'entry-double-side', '<div class="wrap-side sidebar-entry-double-side">', '</div>' );

echo '</div></div></div><div class="entry-double-bottom"><div class="-wrap">';

gThemeSideBar::sidebar( 'entry-double-after', '<div class="wrap-side sidebar-entry-double-after">', '</div>' );

gThemeNavigation::content( 'singular' );

echo '</div></div>';
