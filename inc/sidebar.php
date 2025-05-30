<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSideBar extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'register_defaults'    => TRUE,
			'disable_sidebars'     => FALSE,
			'primary_cats_sidebar' => FALSE,
		], $args ) );

		if ( $register_defaults )
			add_filter( 'register_sidebar_defaults', [ $this, 'register_sidebar_defaults' ], 12 );

		if ( ! $disable_sidebars )
			add_action( 'widgets_init', [ $this, 'widgets_init' ], 18 );

		if ( $primary_cats_sidebar )
			add_action( 'widgets_init', [ $this, 'widgets_init_primaries' ] );
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'side-index'      => _x( 'Index: Side', 'Sidebar Name', 'gtheme' ),
			'side-singular'   => _x( 'Singular: Side', 'Sidebar Name', 'gtheme' ),
			'side-systempage' => _x( 'System Page: Side', 'Sidebar Name', 'gtheme' ),
			'after-singular'  => _x( 'Singular: After Entry', 'Sidebar Name', 'gtheme' ),
			'entry-before'    => _x( 'Entry: Before', 'Sidebar Name', 'gtheme' ),
			'entry-after'     => _x( 'Entry: After', 'Sidebar Name', 'gtheme' ),
			'entry-content'   => _x( 'Entry: Content', 'Sidebar Name', 'gtheme' ),
			'entry-side'      => _x( 'Entry: Side', 'Sidebar Name', 'gtheme' ),

			// 'navigation-after' => _x( 'Navigation: After', 'Sidebar Name', 'gtheme' ),

			// 'side-shop'    => _x( 'Woocommerce Shop: Side', 'Sidebar Name', 'gtheme' ),
			// 'side-product' => _x( 'Woocommerce Product: Side', 'Sidebar Name', 'gtheme' ),
		], $extra );
	}

	public static function sidebar( $name, $before = '', $after = '', $else = FALSE )
	{
		if ( ! gThemeOptions::info( 'sidebar_support', TRUE ) )
			return;

		$class = gThemeOptions::info( 'sidebar_wrap_'.$name, '' );

		if ( is_active_sidebar( $name )  ) {

			printf( $before, $class );
				dynamic_sidebar( $name );
			echo $after;

		} else if ( FALSE !== $else ) {
			// TODO : add dev env, empty space remainder!
			echo sprintf( $before, $class ).$else.$after;
		}
	}

	public static function widgets()
	{
		return apply_filters( 'gtheme_widgets', [
			'gThemeWidgetCustomHTML',
			'gThemeWidgetSearch',
			'gThemeWidgetSearchTerms',
			'gThemeWidgetPostFeatured',
			'gThemeWidgetPostRow',
			'gThemeWidgetPostTerms',
			'gThemeWidgetTermPosts',
			'gThemeWidgetRelatedPosts',
			'gThemeWidgetRecentPosts',
			'gThemeWidgetRecentComments',
			'gThemeWidgetTemplatePart',
			'gThemeWidgetBannerGroup',
			'gThemeWidgetChildren',
			'gThemeWidgetSiblings',
			'gThemeWidgetTheTerm',
			'gThemeWidgetPackGrid',
		] );
	}

	public function widgets_init()
	{
		$this->_register_widgets();
		$this->_register_sidebars();
		$this->_register_widget_shelfs();
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

		$callback = gThemeOptions::info( 'sidebar_args_callback', FALSE ); // `[ __CLASS__, 'parseArgs' ]`

		foreach ( $sidebars as $id => $name ) {

			if ( is_array( $name ) )
				$args = array_merge( [ 'id' => $id ], $name );

			else if ( $callback )
				$args = call_user_func_array( $callback, [ $id, $name ] );

			else
				$args = [ 'id' => $id, 'name' => $name ];

			register_sidebar( $args );
		}
	}

	public function register_sidebar_defaults( $defaults )
	{
		$tag   = gThemeOptions::info( 'sidebar_args_html_tag', 'section' );
		$title = gThemeOptions::info( 'sidebar_args_html_title', 'h3' );

		return array_merge( $defaults, [
			'before_widget' => '<'.$tag.' id="%1$s" class="widget gtheme-widget widget-'.$defaults['id'].' %2$s"><div class="-wrap">',
			'after_widget'  => '</div></'.$tag.'>',
			'before_title'  => '<div class="-wrap-title"><'.$title.' class="-title widget-title widget-'.$defaults['id'].'-title">',
			'after_title'   => '</'.$title.'></div>',
		] );
	}

	// NOTE: we are no longer use this by default.
	// @see `register_sidebar_defaults` filter
	public static function parseArgs( $sidebar, $name = FALSE, $description = '', $extra = '' )
	{
		$tag   = gThemeOptions::info( 'sidebar_args_html_tag', 'section' );
		$title = gThemeOptions::info( 'sidebar_args_html_title', 'h3' );

		return [
			'id'             => $sidebar,
			'name'           => $name ?: $sidebar,
			'before_widget'  => '<'.$tag.' id="%1$s" class="widget gtheme-widget widget-'.$sidebar.( $extra ? ( ' '.$extra ) : '' ).' %2$s"><div class="-wrap">',
			'after_widget'   => '</div></'.$tag.'>',
			'before_title'   => '<div class="-wrap-title"><'.$title.' class="-title widget-title widget-'.$sidebar.'-title">',
			'after_title'    => '</'.$title.'></div>',
			'description'    => $description,
			'before_sidebar' => '', // `<div id="%1$s" class="%2$s">`
			'after_sidebar'  => '',
		];
	}

	private function _register_widget_shelfs()
	{
		$shelfs = apply_filters( 'gtheme_widget_shelfs', gThemeOptions::info( 'widget_shelfs', [] ) );

		if ( empty( $shelfs ) )
			return;

		$alphabet = range( 'A', 'Z' );

		foreach ( $shelfs as $name => $atts ) {

			$args = self::parseShelfsArgs( $name, $atts );

			for ( $i = 1; $i <= $args['rows']; $i++ )
				register_sidebar( [
					'id'   => sprintf( '%s-shelf-%s', $name, strtolower( $alphabet[$i - 1] ) ),
					'name' => sprintf( '%1$s: %2$s', $args['title'], $alphabet[$i - 1] ),
				] );
		}
	}

	public static function parseShelfsArgs( $name, $atts = [] )
	{
		return self::atts( [
			'title'        => $name,
			'context'      => $name, // same as name
			'gutters'      => NULL,
			'rows'         => 5,
			'row_template' => 'row-cols-1 row-cols-lg-%d',
		], $atts );
	}

	public static function renderWidgetShelfs( $name )
	{
		if ( empty( $name ) )
			return FALSE;

		$shelfs = apply_filters( 'gtheme_widget_shelfs', gThemeOptions::info( 'widget_shelfs', [] ) );

		if ( empty( $shelfs[$name] ) )
			return FALSE;

		$alphabet = range( 'A', 'Z' );
		$args     = self::parseShelfsArgs( $name, $shelfs[$name] );

		if ( empty( $args['rows'] ) )
			return FALSE;

		echo '<div class="wrap-widget-shelfs wrap-widget-shelfs-'.$args['context'].' '.( $args['gutters'] ? '-with-gutters' : '-no-gutters' ).'">';

		for ( $i = 1; $i <= $args['rows']; $i++ ) {

			$location = sprintf( '%s-shelf-%s', $name, strtolower( $alphabet[$i - 1] ) );
			$columns  = self::getCount( $location, 1 );

			self::sidebar( $location,
				'<div class="wrap-widget-shelf -col-count-'.$columns.' row '.( $args['gutters'] ? '' : 'no-gutters g-0 ' ).sprintf( $args['row_template'], $columns ).'">',
				'</div>'
			);
		}

		echo '</div>';
	}

	// Creates widgetized sidebars for each category
	// @REF: https://bavotasan.com/2012/create-widgetized-sidebars-for-each-category-in-wordpress/
	public function widgets_init_primaries()
	{
		$primaries = gThemeOptions::getOption( 'terms', [] );

		if ( empty( $primaries ) )
			return;

		$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );
		$terms    = gThemeTaxonomy::listTerms( $taxonomy, 'all', [ 'include' => $primaries ] );

		foreach ( $terms as $term )
			register_sidebar( [
				'id'   => sprintf( '%s-primaries', $term->slug ),
				'name' => sprintf(
					/* translators: `%s`: primary term title */
					_x( 'Theme: %s Widget', 'Modules: Sidebar', 'gtheme' ),
					$term->name
				),
				'description' => sprintf(
					/* translators: `%s`: primary term title */
					_x( 'This is the %s widgetized area', 'Modules: Sidebar', 'gtheme' ),
					$term->name
				),
			] );
	}

	public static function renderPrimary( $slug = NULL )
	{
		$primaries = gThemeOptions::getOption( 'terms', [] );

		if ( empty( $primaries ) )
			return FALSE;

		$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );

		if ( 'category' == $taxonomy && is_category() )
			$term = get_queried_object();

		else if ( 'post_tag' == $taxonomy && is_tag() )
			$term = get_queried_object();

		else if ( is_tax( $taxonomy ) )
			$term = get_queried_object();

		else
			return FALSE;

		if ( ! in_array( $term->term_id, $primaries ) )
			return FALSE;

		dynamic_sidebar( $term->slug.'-primaries' );

		return TRUE;
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
		global $wp_widget_factory;

		foreach ( $wp_widget_factory->widgets as $widget )
			if ( $id_base == $widget->id_base )
				return TRUE;

		return FALSE;
	}

	// @REF: https://stackoverflow.com/a/4480386
	public static function getCount( $sidebar, $fallback = 0 )
	{
		$sidebars = wp_get_sidebars_widgets();

		if ( ! array_key_exists( $sidebar, $sidebars ) )
			return $fallback;

		return count( $sidebars[$sidebar] );
	}
}
