<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

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
		$modules = [
			'core/base'      => '',
			'core/html'      => '',
			'core/number'    => '',
			'core/third'     => '',
			'core/text'      => '',
			'core/url'       => '',
			'core/wordpress' => '',

			'constants'  => '',
			'utilities'  => '',
			'modulecore' => '',
			'cache'      => '',

			'Misc/Bootstrap_Walker_NavBar' => '',
			'Misc/gTheme_Walker_Page'      => '',

			'WordPress/Taxonomy' => '',
			'WordPress/Widget'   => '',

			'Widgets/PostTerms'      => '',
			'Widgets/TermPosts'      => '',
			'Widgets/RelatedPosts'   => '',
			'Widgets/RecentPosts'    => '',
			'Widgets/RecentComments' => '',
			'Widgets/Search'         => '',
			'Widgets/SearchTerms'    => '',
			'Widgets/TemplatePart'   => '',
			'Widgets/Children'       => '',
			'Widgets/Siblings'       => '',
			'Widgets/TheTerm'        => '',
			'Widgets/CustomHTML'     => '',

			'template'   => 'gThemeTemplate',
			'options'    => 'gThemeOptions',
			'theme'      => 'gThemeTheme',
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
			'colors'     => 'gThemeColors',
		];

		if ( is_admin() ) {
			$modules['admin'] = 'gThemeAdmin';
		} else {
			$modules['wrap'] = 'gThemeWrap';
		}

		$this->modules = apply_filters( 'gtheme_modules', $modules );
	}

	private function setup_actions()
	{
		$this->load_modules( $this->modules );

		add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ] );
		add_action( 'init', [ $this, 'init_late' ], 99 );

		do_action_ref_array( 'gtheme_after_setup_actions', [ &$this ] );
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

	private function init_modules( $modules, $options = [] )
	{
		foreach ( $modules as $module_slug => $module_class ) {

			if ( $module_class && class_exists( $module_class ) ) {

				$slug = str_ireplace( [ 'core/', 'modules/', 'misc/' ], '', $module_slug );
				$args = empty( $options[$module_slug] ) ? [] : $options[$module_slug];

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

		$this->init_modules( $this->modules, gThemeOptions::info( 'module_args', [] ) );
	}

	public function init_late()
	{
		$this->load_modules( [ 'fallbacks' => NULL ] );
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
