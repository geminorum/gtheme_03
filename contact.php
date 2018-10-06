<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );
/**
 * @package WordPress
 * @subpackage gTheme 3
 */
/*
Template Name: Contact Page
*/

gThemeUtilities::enqueueAutosize();

get_template_part( 'singular', gtheme_template_base() );
