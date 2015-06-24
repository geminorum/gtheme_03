<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeFrontPage extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'cleanup' => true,
		), $args ) );

		if ( $cleanup ) {
		}
	}

	public static function getDisplayed()
	{
		global $gtheme_front_page_displayed;

		if ( empty( $gtheme_front_page_displayed ) )
			return array();

		return $gtheme_front_page_displayed;
	}

	public static function addDisplayed( $post_id = null )
	{
		global $gtheme_front_page_displayed;

		if ( empty( $gtheme_front_page_displayed ) )
			$gtheme_front_page_displayed = array();

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		$gtheme_front_page_displayed[] = $post_id;
	}

}
