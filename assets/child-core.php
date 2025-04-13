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
	// `load_child_theme_textdomain( 'gtheme', GTHEME_CHILD_DIR.'/languages' );`
	// NOTE: `load_child_theme_textdomain` with same text-domain as parent theme no longer working as WP 6.7
	// @REF: https://core.trac.wordpress.org/ticket/52438
	protected function _load_textdomain( $textdomain = NULL )
	{
		add_action( 'after_setup_theme',
			static function () use ( $textdomain ) {
				// load_child_theme_textdomain( 'gtheme', GTHEME_CHILD_DIR.'/languages' );
				// load_child_theme_textdomain( 'gtheme', GTHEME_CHILD_DIR.'/languages/'.determine_locale() . '.mo' );
				// load_textdomain( 'gtheme', wp_normalize_path( GTHEME_CHILD_DIR.'/languages/'.determine_locale().'.mo' ) );
				load_theme_textdomain( $textdomain ?? 'gtheme', GTHEME_CHILD_DIR.'/languages' );
			}, 12
		);
	}
} }
