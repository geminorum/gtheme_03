<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( ! class_exists( 'gThemeChildCore' ) ) { class gThemeChildCore
{
	public function __construct()
	{
		$this->setup_actions();
	}

	public function setup_actions()
	{
		self::_load_textdomain();
	}

	// @REF: https://developer.wordpress.org/reference/functions/load_child_theme_textdomain/#comment-1552
	protected function _load_textdomain()
	{
		add_action( 'after_setup_theme',
			static function () {
				load_child_theme_textdomain( 'gtheme', GTHEME_CHILD_DIR.'/languages' );
				// load_textdomain( 'gtheme', wp_normalize_path( GTHEME_CHILD_DIR.'/languages/'.determine_locale().'.mo' ) );
				// gnetwork_log(wp_normalize_path( GTHEME_CHILD_DIR.'/languages' ));
			}
		);
	}
} }
