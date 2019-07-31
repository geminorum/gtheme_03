<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeFrontPage extends gThemeModuleCore
{

	// ANCESTOR : gtheme_get_displayed()
	public static function getDisplayed()
	{
		global $gtheme_front_page_displayed;

		if ( empty( $gtheme_front_page_displayed ) )
			$gtheme_front_page_displayed = [];

		if ( is_singular() || is_single() )
			$gtheme_front_page_displayed[] = get_the_ID();

		return array_unique( $gtheme_front_page_displayed, SORT_NUMERIC );
	}

	// ANCESTOR : gtheme_add_displayed()
	public static function addDisplayed( $post_id = NULL )
	{
		global $gtheme_front_page_displayed;

		if ( empty( $gtheme_front_page_displayed ) )
			$gtheme_front_page_displayed = [];

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		$gtheme_front_page_displayed[] = $post_id;

		return $post_id;
	}

	public static function getLatestPostID( $posttype )
	{
		$args = [
			'post_type'           => $posttype,
			'numberposts'         => 1,
			'offset'              => 0,
			'orderby'             => 'post_date',
			'order'               => 'DESC',
			'post_status'         => 'publish',
			'suppress_filters'    => TRUE,
			'ignore_sticky_posts' => TRUE,
			'no_found_rows'       => TRUE,
			'fields'              => 'ids',
		];

		$query = new \WP_Query;
		$posts = $query->query( $args );

		return $posts[0];
	}
}
