<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

final class gThemeCore
{
	private static $instance;

	public static function instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new gThemeCore;
			self::$instance->setup_globals();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	private function __construct() { }

	private function setup_globals()
	{
		$modules = array(
			'core/base'      => '',
			'core/html'      => '',
			'core/wordpress' => '',

			'constants'  => '',
			'utilities'  => '',
			'modulecore' => '',
			'cache'      => '',

			'template'   => 'gThemeTemplate',
			'options'    => 'gThemeOptions',
			'theme'      => 'gThemeTheme',
			'wrap'       => 'gThemeWrap',
			'filters'    => 'gThemeFilters',
			'content'    => 'gThemeContent',
			'embed'      => 'gThemeEmbed',
			'feed'       => 'gThemeFeed',
			'image'      => 'gThemeImage',
			'social'     => 'gThemeSocial',
			'menu'       => 'gThemeMenu',
			'terms'      => 'gThemeTerms',
			'navigation' => 'gThemeNavigation',
			'sidebar'    => 'gThemeSideBar',
			'editor'     => 'gThemeEditor',
			'comments'   => 'gThemeComments',
			'search'     => 'gThemeSearch',
			'settings'   => 'gThemeSettings',
			'banners'    => 'gThemeBanners',
			'shortcodes' => 'gThemeShortCodes',
			'l10n'       => 'gThemeL10N',
			'frontpage'  => 'gThemeFrontPage',
			'pages'      => 'gThemePages',
			'counts'     => 'gThemeCounts',
			'bootstrap'  => 'gThemeBootstrap',
			'date'       => 'gThemeDate',
			'editorial'  => 'gThemeEditorial',
			'attachment' => 'gThemeAttachment',
		);

		if ( is_admin() ) {
			$modules['admin'] = 'gThemeAdmin';
		}

		$this->modules = apply_filters( 'gtheme_modules', $modules );
	}

	private function setup_actions()
	{
		$this->load_modules( $this->modules );

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_action( 'init', array( $this, 'init_late' ), 99 );

		do_action_ref_array( 'gtheme_after_setup_actions', array( &$this ) );
	}

	private function load_modules( $modules, $root = null )
	{
		if ( is_null( $root ) )
			$root = get_template_directory();

		$stylesheet = get_stylesheet_directory();

		foreach ( $modules as $module_slug => $module_class ) {

			if ( file_exists( $stylesheet.'/gtheme/'.$module_slug.'.php' ) )
				require_once( $stylesheet.'/gtheme/'.$module_slug.'.php' );

			else if ( file_exists( $root.'/inc/'.$module_slug.'.php' ) )
				require_once( $root.'/inc/'.$module_slug.'.php' );
		}
	}

	private function init_modules( $modules, $options = array() )
	{
		foreach ( $modules as $module_slug => $module_class ) {

			if ( $module_class && class_exists( $module_class ) ) {

				$slug = str_ireplace( array( 'core/', 'modules/', 'misc/' ), '', $module_slug );
				$args = empty( $options[$module_slug] ) ? array() : $options[$module_slug];

				try {
					$this->{$module_slug} = new $module_class( $args );
				} catch ( Exception $e ) {
					// do nothing!
				}
			}
		}
	}

	public function after_setup_theme()
	{
		load_theme_textdomain( GTHEME_TEXTDOMAIN, GTHEME_DIR.'/languages' );

		$this->init_modules( $this->modules, gThemeOptions::info( 'module_args', array() ) );
	}

	public function init_late()
	{
		$this->load_modules( array( 'fallbacks' => NULL ) );
	}

	public static function version( $theme = NULL )
	{
		$theme = wp_get_theme( $theme );
		if ( ! $theme->exists() )
			return 0;

		return $theme->get( 'Version' );
	}
}

function gTheme() { return gThemeCore::instance(); }
