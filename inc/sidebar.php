<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSideBar extends gThemeModuleCore 
{
	var $_ajax = true;

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'disable_sidebars' => false,
			'primary_cats_sidebar' => false,
		), $args ) );
		
		if ( ! $disable_sidebars )
			add_action( 'widgets_init', array( & $this, 'widgets_init' ) );
		
		if ( $primary_cats_sidebar )
			add_action( 'widgets_init', array( & $this, 'widgets_init_categories' ) );
	}
	
	public static function sidebar( $name, $b = '', $a = '', $else = false ) 
	{
		if ( is_active_sidebar( $name )  ) {
			echo $b;
			dynamic_sidebar( $name );
			echo $a;
		} else if ( $else ) { // TODO : add dev env, empty space remainder!
			echo $b.$else.$a;		
		}
	}
	
	public static function widgets()
	{
		return apply_filters( 'gtheme_widgets', array(
			'gThemeWidgetSearch',
			'gThemeWidgetRecentPosts',
		) );
	}
		
	public function widgets_init()
	{
		foreach ( self::widgets() as $widget ) {
			register_widget( $widget );	
			if ( ! gThemeUtilities::is_dev() ) {
				if ( 'gThemeWidgetSearch' == $widget )
					unregister_widget( 'WP_Widget_Search' );
				if ( 'gThemeWidgetRecentPosts' == $widget )
					unregister_widget( 'WP_Widget_Recent_Posts' );
				if ( 'gtheme_widgets_recent_comments' == $widget )
					unregister_widget( 'WP_Widget_Recent_Comments' );
			}
		}
		
		//if ( gThemeUtilities::is_dev() )
			//register_widget( 'gtheme_widgets_tempname' );	
		
		$sidebars = apply_filters( 'gtheme_sidebars', gtheme_get_info( 'sidebars', array() ) );
		if ( ! count( $sidebars ) )
			return;
			
		$sidebar_args_func = gtheme_get_info( 'sidebar_args_func', array( __CLASS__, 'args' ) );
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
			'id' => $sidebar_id,
			'name' => $sidebar_title,
			'before_widget' => '<section id="%1$s" class="widget gtheme-widget widget-'.$sidebar_id.' %2$s">',
			'after_widget' => "</section>",
			'before_title' => '<h3 class="widget-title gtheme-widget-title widget-'.$sidebar_id.'-title">',
			'after_title' => '</h3>',	
		);
	}
	
	// DRAFT
	// create widgetized sidebars for each category
	// http://bavotasan.com/2012/create-widgetized-sidebars-for-each-category-in-wordpress/
	public function widgets_init_categories()
	{
		// must use primary cats
		$categories = get_categories( array( 'hide_empty'=> 0 ) );

		foreach ( $categories as $category ) {
			if ( 0 == $category->parent )
				register_sidebar( array(
					'name' => $category->cat_name,
					'id' => $category->category_nicename . '-sidebar',
					'description' => 'This is the ' . $category->cat_name . ' widgetized area',
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget' => '</aside>',
					'before_title' => '<h3 class="widget-title">',
					'after_title' => '</h3>',
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
			'id_base' => '',
			'sidebar_id' => '',
			'settings' => array(),
		) );

		$id_base    = $r['id_base'];
		$sidebar_id = $r['sidebar_id'];
		$settings   = (array) $r['settings'];

		// Don't try to set a widget if it hasn't been registered
		if ( ! self::widget_exists( $id_base ) ) {
			return new WP_Error( 'widget_does_not_exist', 'Widget does not exist' );
		}

		$sidebars = wp_get_sidebars_widgets();
		if ( ! isset( $sidebars[ $sidebar_id ] ) ) {
			return new WP_Error( 'sidebar_does_not_exist', 'Sidebar does not exist' );
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


class gThemeWidgetRecentPosts extends WP_Widget 
{

	public function __construct() 
	{
		parent::__construct( 'gtheme_recent_posts', __( 'gTheme: Recent Posts', GTHEME_TEXTDOMAIN ), array( 
			'description' => __( 'Customized most recent posts.', GTHEME_TEXTDOMAIN ),
			'classname' => 'widget-gtheme-recent-posts',
		) );
		
		$this->alt_option_name = 'widget_gtheme_recent_posts';

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	public function widget( $args, $instance ) 
	{
		$cache = wp_cache_get( $this->alt_option_name, 'widget' );

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		
		$context = isset( $instance['context'] ) ? $instance['context'] : 'recent';
		$title = apply_filters( 'widget_title', 
			empty( $instance['title'] ) ? '' : $instance['title'],
			$instance,
			$this->id_base
		);
		
		if ( empty( $instance['number'] ) 
			|| ! $number = absint( $instance['number'] ) )
 			$number = 10;

		$row_query = new WP_Query( array( 
			/**'tax_query' => array( array(
				'taxonomy' => $term->taxonomy,
				'field' => 'term_id',
				'terms' => $term->term_id,
			) ), **/
			'posts_per_page' => $number,
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			// http://www.billerickson.net/code/improve-performance-of-wp_query/
			'no_found_rows' => true, // counts posts, remove if pagination required
			'update_post_term_cache' => false, // grabs terms, remove if terms required (category, tag...)
			'update_post_meta_cache' => false, // grabs post meta, remove if post meta required	
		) );	
		
		if ( $row_query->have_posts() ) { 
			echo $args['before_widget'];
			if ( $title )
				echo $args['before_title'].$title.$args['after_title'];
			echo '<ul class="list-unstyled row-ul list-rows">';
			while ( $row_query->have_posts() ) { 
				$row_query->the_post();
				if ( trim( get_the_title() ) ) {
					echo '<li class="list-row">'; get_template_part( 'row', $context ); echo '</li>'; 
				}
			}
			wp_reset_postdata();
			echo '</ul>'.$args['after_widget'];
		} 
		
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set( $this->alt_option_name, $cache, 'widget' );
	}

	public function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['context'] = strip_tags( $new_instance['context'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions[$this->alt_option_name] ) )
			delete_option( $this->alt_option_name );

		return $instance;
	}

	public function flush_widget_cache() 
	{
		wp_cache_delete( $this->alt_option_name, 'widget' );
	}

	public function form( $instance ) 
	{
		$html = gThemeUtilities::html( 'input', array( 
			'type' => 'text',
			'class' => 'widefat',
			'name' => $this->get_field_name( 'title' ),
			'id' => $this->get_field_id( 'title' ),
			'value' => isset( $instance['title'] ) ? $instance['title'] : '',
		) );
		
		echo '<p>'. gThemeUtilities::html( 'label', array( 
			'for' => $this->get_field_id( 'title' ),
		), __( 'Title:', GTHEME_TEXTDOMAIN ).$html ).'</p>';
		
		$html = gThemeUtilities::html( 'input', array( 
			'type' => 'text',
			'class' => 'widefat',
			'name' => $this->get_field_name( 'context' ),
			'id' => $this->get_field_id( 'context' ),
			'value' => isset( $instance['context'] ) ? $instance['context'] : 'recent',
		) );
		
		echo '<p>'. gThemeUtilities::html( 'label', array( 
			'for' => $this->get_field_id( 'context' ),
		), __( 'Context:', GTHEME_TEXTDOMAIN ).$html ).'</p>';
		
		$html = gThemeUtilities::html( 'input', array( 
			'type' => 'text',
			'size' => 3,
			'name' => $this->get_field_name( 'number' ),
			'id' => $this->get_field_id( 'number' ),
			'value' => isset( $instance['number'] ) ? $instance['number'] : 5,
		) );
		
		echo '<p>'. gThemeUtilities::html( 'label', array( 
			'for' => $this->get_field_id( 'number' ),
		), __( 'Number of posts to show:', GTHEME_TEXTDOMAIN ).' '.$html ).'</p>';
	}
}


class gThemeWidgetSearch extends WP_Widget 
{

	public function __construct() 
	{
		parent::__construct( 'gtheme_search', __( 'gTheme: Search', GTHEME_TEXTDOMAIN ), array( 
			'description' => __( 'Selectable search form', GTHEME_TEXTDOMAIN ),
			'classname' => 'widget-gtheme-search',
			) );
	}

	public function widget( $args, $instance ) 
	{
		$context = empty( $instance['context'] ) ? '' : $instance['context'];
		$title = apply_filters( 'widget_title', 
			empty( $instance['title'] ) ? '' : $instance['title'],
			$instance,
			$this->id_base
		);	

		echo $args['before_widget'];
		if ( $title ) 
			echo $args['before_title'].$title.$args['after_title'];
			get_template_part( 'searchform', $context );
		echo $args['after_widget'];
	}

	public function form( $instance ) 
	{
		$html = gThemeUtilities::html( 'input', array( 
			'type' => 'text',
			'class' => 'widefat',
			'name' => $this->get_field_name( 'title' ),
			'id' => $this->get_field_id( 'title' ),
			'value' => isset( $instance['title'] ) ? $instance['title'] : '',
		) );
		
		echo '<p>'. gThemeUtilities::html( 'label', array( 
			'for' => $this->get_field_id( 'title' ),
		), __( 'Title:', GTHEME_TEXTDOMAIN ).$html ).'</p>';
		
		$html = gThemeUtilities::html( 'input', array( 
			'type' => 'text',
			'class' => 'widefat',
			'name' => $this->get_field_name( 'context' ),
			'id' => $this->get_field_id( 'context' ),
			'value' => isset( $instance['context'] ) ? $instance['context'] : '',
		) );
		
		echo '<p>'. gThemeUtilities::html( 'label', array( 
			'for' => $this->get_field_id( 'context' ),
		), __( 'Context:', GTHEME_TEXTDOMAIN ).$html ).'</p>';
	}
}
