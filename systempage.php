<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );
/**
 * @package WordPress
 * @subpackage gTheme 3
 */
/*
Template Name: System Page
Template Post Type: page
*/

defined( 'GTHEME_IS_SYSTEM_PAGE' )
	or define( 'GTHEME_IS_SYSTEM_PAGE', TRUE );

get_template_part( 'singular', gtheme_template_base() );
