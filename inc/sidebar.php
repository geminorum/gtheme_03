<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSideBar extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'disable_sidebars'     => FALSE,
			'primary_cats_sidebar' => FALSE,
		), $args ) );

		if ( ! $disable_sidebars )
			add_action( 'widgets_init', array( $this, 'widgets_init' ), 18 );

		if ( $primary_cats_sidebar )
			add_action( 'widgets_init', array( $this, 'widgets_init_categories' ) );
	}

	public static function defaults( $extra = array() )
	{
		return array_merge( array(
			'side-index'    => _x( 'Index: Side', 'Modules: Sidebar: Defaults', GTHEME_TEXTDOMAIN ),
			'side-singular' => _x( 'Singular: Side', 'Modules: Sidebar: Defaults', GTHEME_TEXTDOMAIN ),
		), $extra );
	}

	public static function sidebar( $name, $before = '', $after = '', $else = FALSE )
	{
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
		return apply_filters( 'gtheme_widgets', array(
			'gThemeWidgetCustomHTML',
			'gThemeWidgetSearch',
			'gThemeWidgetTermPosts',
			'gThemeWidgetRelatedPosts',
			'gThemeWidgetRecentPosts',
			'gThemeWidgetRecentComments',
			'gThemeWidgetTemplatePart',
			'gThemeWidgetChildren',
			'gThemeWidgetSiblings',
			'gThemeWidgetTheTerm',
		) );
	}

	public function widgets_init()
	{
		global $wp_widget_factory;

		foreach ( self::widgets() as $widget ) {

			if ( 'gThemeWidgetSearch' == $widget )
				$wp_widget_factory->widgets['WP_Widget_Search'] = new gThemeWidgetSearch();

			else if ( 'gThemeWidgetRecentPosts' == $widget )
				$wp_widget_factory->widgets['WP_Widget_Recent_Posts'] = new gThemeWidgetRecentPosts();

			else if ( 'gThemeWidgetRecentComments' == $widget )
				$wp_widget_factory->widgets['WP_Widget_Recent_Comments'] = new gThemeWidgetRecentComments();

			else
				$wp_widget_factory->widgets[$widget] = new $widget();
		}

		$sidebars = apply_filters( 'gtheme_sidebars', gThemeOptions::info( 'sidebars', self::defaults() ) );

		if ( ! count( $sidebars ) )
			return;

		$sidebar_args_func = gThemeOptions::info( 'sidebar_args_func', array( __CLASS__, 'args' ) );

		foreach ( $sidebars as $sidebar_id => $sidebar_title ) {
			$args = array( 'id' => $sidebar_id );
			if ( is_array( $sidebar_title ) )
				$args = array_merge( $args, $sidebar_title );
			else
				$args = call_user_func_array( $sidebar_args_func, array( $sidebar_id, $sidebar_title ) );
			register_sidebar( $args );
		}
	}

	public static function args( $sidebar_id, $sidebar_title )
	{
		return array(
			'id'            => $sidebar_id,
			'name'          => $sidebar_title,
			'before_widget' => '<section id="%1$s" class="widget gtheme-widget widget-'.$sidebar_id.' %2$s">',
			'after_widget'  => "</section>",
			'before_title'  => '<h3 class="widget-title widget-'.$sidebar_id.'-title">',
			'after_title'   => '</h3>',
		);
	}

	// DRAFT
	// create widgetized sidebars for each category
	// http://bavotasan.com/2012/create-widgetized-sidebars-for-each-category-in-wordpress/
	public function widgets_init_categories()
	{
		// must use primary cats
		$categories = get_categories( array( 'hide_empty' => 0 ) );

		foreach ( $categories as $category ) {
			if ( 0 == $category->parent )
				register_sidebar( array(
					'name'          => $category->cat_name,
					'id'            => $category->category_nicename . '-sidebar',
					'description'   => 'This is the ' . $category->cat_name . ' widgetized area',
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h3 class="widget-title">',
					'after_title'   => '</h3>',
				) );
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
	 *	gThemeSideBar::set_widget( array(
	 *     'id_base' => 'text',
	 *     'sidebar_id' => 'sidebar-1',
	 *     'settings' => array(
	 *       'title' => 'This is a cool text widget',
	 *       'text' => 'Lorem ipsum',
	 *       'filter' => false,
	 *     ),
	 *   );
	 */
	public static function set_widget( $args )
	{
		$r = wp_parse_args( $args, array(
			'id_base'    => '',
			'sidebar_id' => '',
			'settings'   => array(),
		) );

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
			//$all_settings = array( $multi_number => $settings );
		} else {
			$multi_number = 1;
			$all_settings = array( $multi_number => $settings );
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

		$sidebars[$sidebar_id] = array();
		wp_set_sidebars_widgets( $sidebars );
	}

	// https://gist.github.com/boonebgorges/3909373
	// check to see whether a widget has been registered
	public static function widget_exists( $id_base )
	{
		global $wp_widget_factory;

		foreach ( $wp_widget_factory->widgets as $w ) {
			if ( $id_base == $w->id_base ) {
				return true;
			}
		}

		return false;
	}
}

class gThemeWidget extends WP_Widget
{

	const BASE   = 'gtheme';
	const MODULE = FALSE;

	public function __construct()
	{
		$args = gThemeBaseCore::atts( array(
			'name'    => FALSE,
			'class'   => '',
			'title'   => '',
			'desc'    => '',
			'control' => array(),
			'flush'   => array(),
		), $this->setup() );

		if ( ! $args['name'] )
			return FALSE;

		parent::__construct( static::BASE.'_'.$args['name'], $args['title'], array(
			'description' => $args['desc'],
			'classname'   => '{GTHEME_WIDGET_CLASSNAME}'.'widget-'.static::BASE.'-'.$args['class'],
		), $args['control'] );

		$this->alt_option_name = 'widget_'.static::BASE.'_'.$args['name'];

		foreach ( $args['flush'] as $action )
			add_action( $action, array( $this, 'flush_widget_cache' ) );
	}

	public static function setup()
	{
		return array(
			'name'  => '',
			'class' => '',
			'title' => '',
			'desc'  => '',
			'flush' => array(
				'save_post',
				'deleted_post',
				'switch_theme',
			),
		);
	}

	// override this to bypass caching
	public function widget( $args, $instance )
	{
		$this->widget_cache( $args, $instance );
	}

	public function widget_cache( $args, $instance, $prefix = '' )
	{
		if ( $this->is_preview() )
			return $this->widget_html( $args, $instance );

		if ( gThemeWordPress::isFlush() )
			delete_transient( $this->alt_option_name );

		if ( FALSE === ( $cache = get_transient( $this->alt_option_name ) ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[$args['widget_id'].$prefix] ) )
			return print $cache[$args['widget_id'].$prefix];

		ob_start();

		if ( $this->widget_html( $args, $instance ) )
			$cache[$args['widget_id'].$prefix] = ob_get_flush();

		else
			return ob_end_flush();

		set_transient( $this->alt_option_name, $cache, GTHEME_CACHETTL );
	}

	// FIXME: DROP THIS
	public function widget_cache_OLD( $args, $instance, $prefix = '' )
	{
		$cache = $this->is_preview() ? array() : wp_cache_get( $this->alt_option_name, 'widget' );

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( is_array( $cache ) && isset( $cache[$args['widget_id'].$prefix] ) ) {
			echo $cache[$args['widget_id'].$prefix];
			return;
		}

		ob_start();

		if ( $this->widget_html( $args, $instance ) ) {
			if ( ! $this->is_preview() ) {
				$cache[$args['widget_id'].$prefix] = ob_get_flush();
				wp_cache_set( $this->alt_option_name, $cache, 'widget' );
			} else {
				ob_end_flush();
			}
		} else {
			ob_end_flush();
		}
	}

	public function widget_html( $args, $instance )
	{
		return FALSE;
	}

	public function before_widget( $args, $instance, $echo = TRUE )
	{
		$classes = isset( $instance['context'] ) && $instance['context'] ? 'context-'.sanitize_html_class( $instance['context'], 'general' ).' ' : '';
		$classes.= isset( $instance['class'] ) && $instance['class'] ? $instance['class'].' ' : '';

		$html = preg_replace( '%{GTHEME_WIDGET_CLASSNAME}%', $classes, $args['before_widget'] );

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function after_widget( $args, $instance, $echo = TRUE )
	{
		if ( ! $echo )
			return $args['after_widget'];

		echo $args['after_widget'];
	}

	public function widget_title( $args, $instance, $default = '', $echo = TRUE )
	{
		$title = apply_filters( 'widget_title',
			empty( $instance['title'] ) ? $default : $instance['title'],
			$instance,
			$this->id_base
		);

		if ( $title && isset( $instance['title_link'] ) && $instance['title_link'] )
			$title = gThemeHTML::tag( 'a', array(
				'href' => $instance['title_link'],
			), $title );

		if ( ! $title )
			return '';

		$html = $args['before_title'].$title.$args['after_title'];

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function flush_widget_cache()
	{
		// wp_cache_delete( $this->alt_option_name, 'widget' );
		delete_transient( $this->alt_option_name );
	}

	public function before_form( $instance, $echo = TRUE )
	{
		$classes = [ static::BASE.'-admin-wrap-widgetform' ];

		if ( self::MODULE )
			$classes[] = '-'.self::MODULE;

		$html = '<div class="'.join( ' ', $classes ).'">';

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function after_form( $instance, $echo = TRUE )
	{
		$html = '</div>';

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function form_content( $instance, $default = '', $field = 'content', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Custom HTML:', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label );

		echo gThemeHTML::tag( 'textarea', array(
			'rows'  => '3',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'class' => 'widefat code textarea-autosize',
		), isset( $instance[$field] ) ? $instance[$field] : $default );

		echo '</p>';
	}

	public function form_number( $instance, $default = '10', $field = 'number', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Number of posts to show:', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'number',
			'size'  => 3,
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label.' '.$html ).'</p>';
	}

	public function form_context( $instance, $default = '', $field = 'context' )
	{
		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'Context:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_class( $instance, $default = '', $field = 'class' )
	{
		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'CSS Class:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_post_type( $instance, $default = 'post', $field = 'post_type' )
	{
		$html = '';
		$type = isset( $instance[$field] ) ? $instance[$field] : $default;

		foreach ( gThemeWordPress::getPostTypes() as $name => $title )
			$html.= gThemeHTML::tag( 'option', array(
				'value'    => $name,
				'selected' => $type == $name,
			), $title );

		$html = gThemeHTML::tag( 'select', array(
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		), $html );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'PostType:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_taxonomy( $instance, $default = 'post_tag', $field = 'taxonomy', $post_type_field = 'post_type', $post_type_default = 'post' )
	{
		$html      = '';
		$post_type = isset( $instance[$post_type_field] ) ? $instance[$post_type_field] : $post_type_default;
		$taxonomy  = isset( $instance[$field] ) ? $instance[$field] : $default;

		foreach ( gThemeWordPress::getTaxonomies( 0, array(), $post_type ) as $name => $title )
			$html.= gThemeHTML::tag( 'option', array(
				'value'    => $name,
				'selected' => $taxonomy == $name,
			), $title );

		$html = gThemeHTML::tag( 'select', array(
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		), $html );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'Taxonomy:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_title( $instance, $default = '', $field = 'title' )
	{
		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'Title:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_title_link( $instance, $default = '', $field = 'title_link' )
	{
		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'Title Link:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_custom_link( $instance, $default = '', $field = 'custom_link', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Custom Link:', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'url',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label.$html ).'</p>';
	}

	public function form_custom_code( $instance, $default = '', $field = 'custom_code', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Custom Code:', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'class' => array( 'widefat', 'code' ),
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label.$html ).'</p>';
	}

	public function form_custom_empty( $instance, $default = '', $field = 'empty', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Empty Message:', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'class' => array( 'widefat', 'code' ),
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label.$html ).'</p>';
	}

	public function form_avatar_size( $instance, $default = '32', $field = 'avatar_size' )
	{
		$html = gThemeHTML::tag( 'input', array(
			'type'  => 'text',
			'size'  => 3,
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		) );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'Avatar Size:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}

	public function form_image_size( $instance, $default = 'thumbnail', $field = 'image_size', $post_type = 'post' )
	{
		$sizes = array();

		foreach ( gThemeOptions::info( 'images', array() ) as $name => $size )
			if ( isset( $size['p'] ) && in_array( $post_type, $size['p'] ) )
				$sizes[$name] = $size['n'].' ('.number_format_i18n( $size['w'] ).'&nbsp;&times;&nbsp;'.number_format_i18n( $size['h'] ).')';

		if ( count( $sizes ) ) {

			$selected = isset( $instance[$field] ) ? $instance[$field] : $default;
			$html     = '';

			foreach ( $sizes as $size => $title )
				$html.= gThemeHTML::tag( 'option', array(
					'value'    => $size,
					'selected' => $selected == $size,
				), $title );

			$html = gThemeHTML::tag( 'select', array(
				'class' => 'widefat',
				'name'  => $this->get_field_name( $field ),
				'id'    => $this->get_field_id( $field ),
			), $html );

			echo '<p>'.gThemeHTML::tag( 'label', array(
				'for' => $this->get_field_id( $field ),
			), _x( 'Image Size:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';

		} else {
			echo '<p>'._x( 'No Image Size Available!', 'Widget: Setting', GTHEME_TEXTDOMAIN ).'</p>';
		}
	}

	public function form_checkbox( $instance, $default = FALSE, $field = 'checked', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Checked', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		$html = gThemeHTML::tag( 'input', array(
			'type'    => 'checkbox',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'checked' => isset( $instance[$field] ) ? $instance[$field] : $default,
		) );

		echo '<p>'.$html.'&nbsp;'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label ).'</p>';
	}

	// only works on hierarchical
	public function form_page_id( $instance, $default = '0', $field = 'page_id', $post_type_field = 'posttype', $post_type_default = 'page', $label = NULL )
	{
		$post_type = isset( $instance[$post_type_field] ) ? $instance[$post_type_field] : $post_type_default;
		$page_id  = isset( $instance[$field] ) ? $instance[$field] : $default;

		if ( is_null( $label ) )
			$label = _x( 'Page:', 'Widget: Setting', GTHEME_TEXTDOMAIN );

		$html = wp_dropdown_pages( array(
			'post_type'        => $post_type,
			'selected'         => $page_id,
			'name'             => $this->get_field_name( $field ),
			'id'               => $this->get_field_id( $field ),
			'class'            => 'widefat',
			'show_option_none' => __( '&mdash; Select &mdash;', GTHEME_TEXTDOMAIN ),
			'sort_column'      => 'menu_order, post_title',
			'echo'             => FALSE,
		) );

		if ( ! $html )
			$html = '<br /><code>N/A</code>';

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), $label.$html ).'</p>';
	}

	public function form_term_id( $instance, $default = '0', $field = 'term_id', $taxonomy_field = 'taxonomy', $taxonomy_default = 'post_tag' )
	{
		$taxonomy = isset( $instance[$taxonomy_field] ) ? $instance[$taxonomy_field] : $taxonomy_default;
		$term_id  = isset( $instance[$field] ) ? $instance[$field] : $default;

		$html = gThemeHTML::tag( 'option', array(
			'value'    => '0',
			'selected' => $term_id == '0',
		), __( '&mdash; Select &mdash;', GTHEME_TEXTDOMAIN ) );

		foreach ( get_terms( $taxonomy, array( 'hide_empty' => FALSE ) ) as $term )
			$html.= gThemeHTML::tag( 'option', array(
				'value'    => $term->term_id,
				'selected' => $term_id == $term->term_id,
			), $term->name );

		$html = gThemeHTML::tag( 'select', array(
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		), $html );

		echo '<p>'.gThemeHTML::tag( 'label', array(
			'for' => $this->get_field_id( $field ),
		), _x( 'Term:', 'Widget: Setting', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}
}

class gThemeWidgetTermPosts extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'term_posts',
			'class' => 'term-posts',
			'title' => _x( 'Theme: Term Posts', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the latest posts from a single term.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
			'flush' => array(
				'save_post',
				'deleted_post',
				'switch_theme',
			),
		);
	}

	public function widget_html( $args, $instance )
	{
		$context   = isset( $instance['context'] ) ? $instance['context'] : 'recent';
		$term_id   = isset( $instance['term_id'] ) ? $instance['term_id'] : FALSE;
		$taxonomy  = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'post_tag';
		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( ! $term_id )
			return FALSE;

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$query_args = array(
			'tax_query' => array( array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => array( $term_id ),
				'operator' => 'IN',
			) ),
			'posts_per_page' => $number,
			'post_type'      => $post_type,
			'post_status'    => 'publish',

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		);

		if ( is_singular() )
			$query_args['post__not_in'] = array( get_queried_object_id() );

		$row_query = new \WP_Query( $query_args );

		if ( $row_query->have_posts() ) {

			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="theme-list-wrap term-posts"><ul>';

			while ( $row_query->have_posts() ) {
				$row_query->the_post();
				if ( trim( get_the_title() ) ) {
					echo '<li>'; get_template_part( 'row', $context ); echo '</li>';
				}
			}

			wp_reset_postdata();

			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = strip_tags( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['term_id']    = strip_tags( $new['term_id'] );
		$instance['taxonomy']   = strip_tags( $new['taxonomy'] );
		$instance['post_type']  = strip_tags( $new['post_type'] );
		$instance['context']    = strip_tags( $new['context'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['number'] = (int) $new['number'];

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance );
		$this->form_taxonomy( $instance );
		$this->form_term_id( $instance );
		$this->form_context( $instance, 'recent' );
		$this->form_class( $instance );
		$this->form_number( $instance, '5' );

		$this->after_form( $instance );
	}
}

class gThemeWidgetRelatedPosts extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'related_posts',
			'class' => 'related-posts',
			'title' => _x( 'Theme: Related Posts', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the related posts based on terms in a taxonomy.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
			'flush' => array(
				'save_post',
				'deleted_post',
				'switch_theme',
			),
		);
	}

	public function widget( $args, $instance )
	{
		global $post;

		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( ! is_singular( $post_type ) )
			return;

		$this->widget_cache( $args, $instance, '_'.$post->ID );
	}

	public function widget_html( $args, $instance )
	{
		global $post;

		$context   = isset( $instance['context'] ) ? $instance['context'] : 'related';
		$taxonomy  = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'post_tag';
		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$terms = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $terms ) || ! count( $terms ) )
			return;

		$row_query = new \WP_Query( array(
			'tax_query' => array( array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $terms,
				'operator' => 'IN',
			) ),
			'post_type'      => $post_type,
			'post__not_in'   => array( $post->ID ),
			'posts_per_page' => $number,
			'post_status'    => 'publish',

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		) );

		if ( $row_query->have_posts() ) {
			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="theme-list-wrap related-posts"><ul>';
			while ( $row_query->have_posts() ) {
				$row_query->the_post();
				if ( trim( get_the_title() ) ) {
					echo '<li>'; get_template_part( 'row', $context ); echo '</li>';
				}
			}
			wp_reset_postdata();
			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = strip_tags( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['context']    = strip_tags( $new['context'] );
		$instance['class']      = strip_tags( $new['class'] );
		$instance['post_type']  = strip_tags( $new['post_type'] );
		$instance['taxonomy']   = strip_tags( $new['taxonomy'] );

		$instance['number'] = (int) $new['number'];

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance );
		$this->form_taxonomy( $instance );
		$this->form_context( $instance, 'related' );
		$this->form_class( $instance );
		$this->form_number( $instance, '5' );

		$this->after_form( $instance );
	}
}

class gThemeWidgetRecentPosts extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'recent_posts',
			'class' => 'recent-posts',
			'title' => _x( 'Theme: Recent Posts', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the most recent posts.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
			'flush' => array(
				'save_post',
				'deleted_post',
				'switch_theme',
			),
		);
	}

	public function widget_html( $args, $instance )
	{
		$post_type = empty( $instance['post_type'] ) ? 'post' : $instance['post_type'];
		$context   = isset( $instance['context'] ) ? $instance['context'] : 'recent';

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$query_args = array(
			'posts_per_page' => $number,
			'post_type'      => $post_type,
			'post_status'    => 'publish',

			'ignore_sticky_posts'    => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		);

		if ( is_singular() )
			$query_args['post__not_in'] = array( get_queried_object_id() );

		$row_query = new \WP_Query( $query_args );

		if ( $row_query->have_posts() ) {
			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="theme-list-wrap recent-posts"><ul>';
			while ( $row_query->have_posts() ) {
				$row_query->the_post();
				if ( trim( get_the_title() ) ) {
					echo '<li>'; get_template_part( 'row', $context ); echo '</li>';
				}
			}
			wp_reset_postdata();
			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = strip_tags( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['post_type']  = strip_tags( $new['post_type'] );
		$instance['context']    = strip_tags( $new['context'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['number'] = (int) $new['number'];

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance );
		$this->form_context( $instance, 'recent' );
		$this->form_class( $instance );
		$this->form_number( $instance, '5' );

		$this->after_form( $instance );
	}
}

class gThemeWidgetRecentComments extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'recent_comments',
			'class' => 'recent-comments',
			'title' => _x( 'Theme: Recent Comments', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the most recent comments.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
			'flush' => array(
				'comment_post',
				'edit_comment',
				'transition_comment_status',
			),
		);
	}

	public function widget_html( $args, $instance )
	{
		if ( empty( $instance['number'] )
			|| ! $number = absint( $instance['number'] ) )
				$number = 10;

		$comments = get_comments( apply_filters( 'widget_comments_args', array(
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish'
		) ) );

		if ( $comments ) {

			$callback = gThemeOptions::info( 'recent_comment_callback', array( $this, 'comment_callback' ) );
			$avatar_size = empty( $instance['avatar_size'] ) ? 32 : absint( $instance['avatar_size'] );

			// prime cache for associated posts
			// prime post term cache if we need it for permalinks
			$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
			echo '<div class="theme-list-wrap recent-comments"><ul>';

			foreach ( (array) $comments as $comment ) {
				echo '<li>';
					echo call_user_func_array( $callback, array( $comment, $avatar_size ) );
				echo '</li>';
			}

			echo '</ul></div>';
			$this->after_widget( $args, $instance );

			return TRUE;
		}

		return FALSE;
	}

	public function comment_callback( $comment, $avatar_size )
	{
		$content = gThemeL10N::str( wp_strip_all_tags( $comment->comment_content, TRUE ) );

		return sprintf( '<span class="comment-author-link">%1$s</span>: <a class="comment-post-link" href="%2$s" data-toggle="tooltip" data-placement="bottom" title="%3$s: %4$s">%5$s</a>',
			// get_comment_author_link(),
			gThemeL10N::str( get_comment_author( $comment->comment_ID ) ),
			esc_url( get_comment_link( $comment->comment_ID ) ),
			esc_attr( $content ),
			esc_attr( get_the_title( $comment->comment_post_ID ) ),
			wp_trim_words( $content, 10, '&nbsp;&hellip;' )
		);
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = strip_tags( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['number']      = (int) $new['number'];
		$instance['avatar_size'] = (int) $new['avatar_size'];

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_avatar_size( $instance );
		$this->form_number( $instance, '5' );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}

class gThemeWidgetSearch extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'search',
			'class' => 'search',
			'title' => _x( 'Theme: Search', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays search form.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		);
	}

	public function widget( $args, $instance )
	{
		$context = empty( $instance['context'] ) ? '' : $instance['context'];

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
			get_template_part( 'searchform', $context );
		$this->after_widget( $args, $instance );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_context( $instance );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}

class gThemeWidgetTemplatePart extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'template_part',
			'class' => 'template-part',
			'title' => _x( 'Theme: Template Part', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Include selected template part into sidebars.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		);
	}

	public function widget( $args, $instance )
	{
		$context = empty( $instance['context'] ) ? '' : $instance['context'];

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
			get_template_part( 'widget', $context );
		$this->after_widget( $args, $instance );
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_context( $instance );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}

class gThemeWidgetChildren extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'children',
			'class' => 'children',
			'title' => _x( 'Theme: Children', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the list of current post\'s children.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		);
	}

	public function widget( $args, $instance )
	{
		$post_type = empty( $instance['post_type'] ) ? 'page' : $instance['post_type'];

		$html = gTheme()->shortcodes->shortcode_children( array( 'type' => $post_type ) );

		if ( $html ) {
			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
				echo $html;
			$this->after_widget( $args, $instance );
		}
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance, 'page' );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}

class gThemeWidgetSiblings extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'siblings',
			'class' => 'siblings',
			'title' => _x( 'Theme: Siblings', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the list of current post\'s siblings.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		);
	}

	public function widget( $args, $instance )
	{
		$post_type = empty( $instance['post_type'] ) ? 'page' : $instance['post_type'];

		$html = gTheme()->shortcodes->shortcode_siblings( array( 'type' => $post_type ) );

		if ( $html ) {
			$this->before_widget( $args, $instance );
			$this->widget_title( $args, $instance );
				echo $html;
			$this->after_widget( $args, $instance );
		}
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_post_type( $instance, 'page' );
		$this->form_class( $instance );

		$this->after_form( $instance );
	}
}

class gThemeWidgetTheTerm extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'the_term',
			'class' => 'the-term',
			'title' => _x( 'Theme: The Term', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays the current term info based on the query.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		);
	}

	public function widget( $args, $instance )
	{
		if ( defined( 'GTHEME_WIDGET_THETERM_DISABLED' )
			&& GTHEME_WIDGET_THETERM_DISABLED )
				return;

		if ( ! ( is_tax() || is_tag() || is_category() ) )
			return;

		if ( ! $term = get_queried_object() )
			return;

		$desc  = get_term_field( 'description', $term->term_id, $term->taxonomy );
		$image = ! empty( $instance['meta_image'] ) ? gThemeImage::termImage( array( 'term_id' => $term->term_id ) ) : FALSE;

		if ( ! $desc && ! $image && ! empty( $instance['hide_no_desc'] ) )
			return;

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance, $term->name );

		if ( $image )
			echo $image;

		if ( $desc )
			echo wpautop( gThemeUtilities::wordWrap( $desc ), FALSE );

		$this->after_widget( $args, $instance );
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = strip_tags( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		$instance['meta_image']   = isset( $new['meta_image'] );
		$instance['hide_no_desc'] = isset( $new['hide_no_desc'] );

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_class( $instance );

		$this->form_checkbox( $instance, TRUE, 'meta_image', _x( 'Display Meta Image', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );
		$this->form_checkbox( $instance, TRUE, 'hide_no_desc', _x( 'Hide if no Description', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );

		$this->after_form( $instance );
	}
}

class gThemeWidgetCustomHTML extends gThemeWidget
{

	public static function setup()
	{
		return array(
			'name'  => 'custom_html',
			'class' => 'custom-html',
			'title' => _x( 'Theme: Custom HTML', 'Widget: Title', GTHEME_TEXTDOMAIN ),
			'desc'  => _x( 'Displays arbitrary HTML code with support for shortcodes and embeds.', 'Widget: Description', GTHEME_TEXTDOMAIN ),
		);
	}

	public function widget_html( $args, $instance )
	{
		global $wp_embed;

		if ( ! $content = trim( $instance['content'] ) )
			return FALSE;

		if ( ! empty( $instance['embeds'] ) ) {
			$content = $wp_embed->run_shortcode( $content );
			$content = $wp_embed->autoembed( $content );
		}

		if ( ! empty( $instance['shortcodes'] ) )
			$content = do_shortcode( $content );

		if ( ! empty( $instance['legacy'] ) )
			$content = apply_filters( 'widget_text', $content, $instance, $this );

		if ( ! empty( $instance['filters'] ) )
			$content = apply_filters( 'widget_custom_html_content', $content, $instance, $this );

		if ( ! $content )
			return FALSE;

		if ( ! empty( $instance['autop'] ) )
			$content = wpautop( $content );

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
		echo '<div class="textwidget custom-html-widget">';
			echo $content;
		echo '</div>';
		$this->after_widget( $args, $instance );

		return TRUE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = strip_tags( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		if ( current_user_can( 'unfiltered_html' ) )
			$instance['content'] = $new['content'];
		else
			$instance['content'] = wp_kses_post( $new['content'] );

		$instance['embeds']     = isset( $new['embeds'] );
		$instance['shortcodes'] = isset( $new['shortcodes'] );
		$instance['filters']    = isset( $new['filters'] );
		$instance['legacy']     = isset( $new['legacy'] );
		$instance['autop']      = isset( $new['autop'] );

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_class( $instance );

		$this->form_content( $instance );

		echo '<div class="-group">';

		$this->form_checkbox( $instance, FALSE, 'embeds', _x( 'Process Embeds', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );
		$this->form_checkbox( $instance, FALSE, 'shortcodes', _x( 'Process Shortcodes', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );
		$this->form_checkbox( $instance, FALSE, 'filters', _x( 'Process Filters', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );
		$this->form_checkbox( $instance, FALSE, 'legacy', _x( 'Process Filters (Legacy)', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );
		$this->form_checkbox( $instance, FALSE, 'autop', _x( 'Automatic Paragraphs', 'Widget: Setting', GTHEME_TEXTDOMAIN ) );

		echo '</div>';

		$this->after_form( $instance );
	}
}
