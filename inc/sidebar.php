<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSideBar extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'disable_sidebars'     => FALSE,
			'primary_cats_sidebar' => FALSE,
		], $args ) );

		if ( ! $disable_sidebars )
			add_action( 'widgets_init', [ $this, 'widgets_init' ], 18 );

		if ( $primary_cats_sidebar )
			add_action( 'widgets_init', [ $this, 'widgets_init_primaries' ] );
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'side-index'      => _x( 'Index: Side', 'Modules: Sidebar: Defaults', 'gtheme' ),
			'side-singular'   => _x( 'Singular: Side', 'Modules: Sidebar: Defaults', 'gtheme' ),
			'side-systempage' => _x( 'System Page: Side', 'Modules: Sidebar: Defaults', 'gtheme' ),
			'after-singular'  => _x( 'Singular: After Entry', 'Modules: Sidebar: Defaults', 'gtheme' ),
		], $extra );
	}

	public static function sidebar( $name, $before = '', $after = '', $else = FALSE )
	{
		if ( ! gThemeOptions::info( 'sidebar_support', TRUE ) )
			return;

		if ( is_active_sidebar( $name )  ) {

			echo $before;
				dynamic_sidebar( $name );
			echo $after;

		} else if ( FALSE !== $else ) {
			// TODO : add dev env, empty space remainder!
			echo $before.$else.$after;
		}
	}

	public static function widgets()
	{
		return apply_filters( 'gtheme_widgets', [
			'gThemeWidgetCustomHTML',
			'gThemeWidgetSearch',
			'gThemeWidgetSearchTerms',
			'gThemeWidgetPostTerms',
			'gThemeWidgetTermPosts',
			'gThemeWidgetRelatedPosts',
			'gThemeWidgetRecentPosts',
			'gThemeWidgetRecentComments',
			'gThemeWidgetTemplatePart',
			'gThemeWidgetChildren',
			'gThemeWidgetSiblings',
			'gThemeWidgetTheTerm',
		] );
	}

	public function widgets_init()
	{
		$this->_register_widgets();
		$this->_register_sidebars();
	}

	private function _get_widget_extend_map()
	{
		return [
			'gThemeWidgetSearch'         => 'WP_Widget_Search',
			'gThemeWidgetRecentPosts'    => 'WP_Widget_Recent_Posts',
			'gThemeWidgetRecentComments' => 'WP_Widget_Recent_Comments',
		];
	}

	private function _register_widgets()
	{
		global $wp_widget_factory;

		$map = $this->_get_widget_extend_map();

		foreach ( self::widgets() as $widget ) {

			if ( array_key_exists( $widget, $map ) )
				$wp_widget_factory->widgets[$map[$widget]] = new $widget();
			else
				$wp_widget_factory->widgets[$widget] = new $widget();
		}
	}

	private function _register_sidebars()
	{
		$sidebars = apply_filters( 'gtheme_sidebars', gThemeOptions::info( 'sidebars', self::defaults() ) );

		if ( empty( $sidebars ) )
			return;

		$callback = gThemeOptions::info( 'sidebar_args_callback', [ __CLASS__, 'parseArgs' ] );

		foreach ( $sidebars as $id => $name ) {

			if ( is_array( $name ) )
				$args = array_merge( self::parseArgs( $id ), $name );

			else
				$args = call_user_func_array( $callback, [ $id, $name ] );

			register_sidebar( $args );
		}
	}

	public static function parseArgs( $sidebar, $name = FALSE, $description = '', $extra = '' )
	{
		$tag   = gThemeOptions::info( 'sidebar_args_html_tag', 'section' );
		$title = gThemeOptions::info( 'sidebar_args_html_title', 'h3' );

		return [
			'id'            => $sidebar,
			'name'          => $name ?: $sidebar,
			'before_widget' => '<'.$tag.' id="%1$s" class="widget gtheme-widget widget-'.$sidebar.' '.$extra.' %2$s"><div class="-wrap">',
			'after_widget'  => '</div></'.$tag.'>',
			'before_title'  => '<'.$title.' class="-title widget-title widget-'.$sidebar.'-title">',
			'after_title'   => '</'.$title.'>',
			'description'   => $description,
		];
	}

	// TODO: add display helper
	// creates widgetized sidebars for each category
	// @REF: https://bavotasan.com/2012/create-widgetized-sidebars-for-each-category-in-wordpress/
	public function widgets_init_primaries()
	{
		$primaries = gThemeOptions::getOption( 'terms', [] );

		if ( empty( $primaries ) )
			return;

		$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );
		$terms    = gThemeTaxonomy::listTerms( $taxonomy, 'all', [ 'include' => $primaries ] );

		foreach ( $terms as $term ) {

			/* translators: %s: primary term title */
			$name = sprintf( _x( 'Theme: %s Widget', 'Modules: Sidebar', 'gtheme' ), $term->name );
			/* translators: %s: primary term title */
			$desc = sprintf( _x( 'This is the %s widgetized area', 'Modules: Sidebar', 'gtheme' ), $term->name );

			register_sidebar( self::parseArgs( $term->slug.'-sidebar', $name, $desc ) );
		}
	}

	public static function category( $name = null )
	{
		if ( ! is_category() )
			return;

		if ( is_null( $name ) )
			$name = get_cat_name( get_query_var( 'cat' ) );

		dynamic_sidebar( sanitize_title( $name ).'-sidebar' );
	}

	// https://gist.github.com/boonebgorges/3909373
	// adding widgets to sidebars
	/**
	 * gThemeSideBar::set_widget( [
	 *     'id_base' => 'text',
	 *     'sidebar_id' => 'sidebar-1',
	 *     'settings' => [
	 *       'title' => 'This is a cool text widget',
	 *       'text' => 'Lorem ipsum',
	 *       'filter' => false,
	 *     ],
	 *   ] );
	 */
	public static function set_widget( $args )
	{
		$r = wp_parse_args( $args, [
			'id_base'    => '',
			'sidebar_id' => '',
			'settings'   => [],
		] );

		$id_base    = $r['id_base'];
		$sidebar_id = $r['sidebar_id'];
		$settings   = (array) $r['settings'];

		// Don't try to set a widget if it hasn't been registered
		if ( ! self::widget_exists( $id_base ) ) {
			return new \WP_Error( 'widget_does_not_exist', 'Widget does not exist' );
		}

		$sidebars = wp_get_sidebars_widgets();
		if ( ! isset( $sidebars[$sidebar_id] ) ) {
			return new \WP_Error( 'sidebar_does_not_exist', 'Sidebar does not exist' );
		}

		$sidebar = (array) $sidebars[ $sidebar_id ];

		// Multi-widgets can only be detected by looking at their settings
		$option_name  = 'widget_' . $id_base;

		// Don't let it get pulled from the cache
		wp_cache_delete( $option_name, 'options' );
		$all_settings = get_option( $option_name );

		if ( is_array( $all_settings ) ) {
			$skeys = array_keys( $all_settings );

			// Find the highest numeric key
			rsort( $skeys );

			foreach ( $skeys as $k ) {
				if ( is_numeric( $k ) ) {
					$multi_number = $k + 1;
					break;
				}
			}

			if ( ! isset( $multi_number ) ) {
				$multi_number = 1;
			}

			$all_settings[ $multi_number ] = $settings;
			// $all_settings = [ $multi_number => $settings ];
		} else {
			$multi_number = 1;
			$all_settings = [ $multi_number => $settings ];
		}

		$widget_id = $id_base . '-' . $multi_number;
		$sidebar[] = $widget_id;

		// Because of the way WP_Widget::update_callback() works, gotta fake the $_POST
		$_POST['widget-' . $id_base] = $all_settings;

		global $wp_registered_widget_updates, $wp_registered_widget_controls;
		foreach ( (array) $wp_registered_widget_updates as $name => $control ) {

			if ( $name == $id_base ) {
				if ( !is_callable( $control['callback'] ) )
					continue;

				ob_start();
					call_user_func_array( $control['callback'], $control['params'] );
				ob_end_clean();
				break;
			}
		}

		$sidebars[ $sidebar_id ] = $sidebar;
		wp_set_sidebars_widgets( $sidebars );

		update_option( $option_name, $all_settings );
	}

	// https://gist.github.com/boonebgorges/3909373
	// moves all active widgets from a given sidebar into the inactive array
	// gThemeSideBar::clear( 'sidebar-1' );
	public static function clear( $sidebar_id, $delete_to = 'inactive' )
	{
		$sidebars = wp_get_sidebars_widgets();

		if ( ! isset( $sidebars[ $sidebar_id ] ) )
			return new WP_Error( 'sidebar_does_not_exist', 'Sidebar does not exist' );

		if ( 'inactive' == $delete_to )
			$sidebars['wp_inactive_widgets'] = array_unique( array_merge( $sidebars['wp_inactive_widgets'], $sidebars[$sidebar_id] ) );

		$sidebars[$sidebar_id] = [];
		wp_set_sidebars_widgets( $sidebars );
	}

	// https://gist.github.com/boonebgorges/3909373
	// check to see whether a widget has been registered
	public static function widget_exists( $id_base )
	{
		foreach ( $GLOABLS['wp_widget_factory']->widgets as $widget )
			if ( $id_base == $widget->id_base )
				return TRUE;

		return FALSE;
	}
}
