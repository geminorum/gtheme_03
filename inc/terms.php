<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeTerms extends gThemeModuleCore
{

	var $_ajax = TRUE;

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'system_tags' => FALSE,
			'p2p'         => FALSE,
		), $args ) );

		if ( $system_tags ) {
			add_action( 'init', array( &$this, 'register_taxonomies' ) );
			add_filter( 'post_class', array( &$this, 'post_class' ), 10, 3 );
		}

		if ( $p2p )
			add_action( 'p2p_init', array( &$this, 'p2p_init' ) );
	}

	public function register_taxonomies()
	{
		$cpt = gThemeOptions::info( 'system_tags_cpt', array( 'post' ) );
		$cap = gThemeOptions::info( 'settings_access', 'edit_theme_options' );

		register_taxonomy( GTHEME_SYSTEMTAGS, $cpt, array(
			'labels' => array(
				'name'                       => _x( 'System Tags', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'singular_name'              => _x( 'System Tag', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'search_items'               => _x( 'Search System Tags', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'popular_items'              => NULL,
				'all_items'                  => _x( 'All System Tags', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'parent_item'                => _x( 'Parent System Tag', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'parent_item_colon'          => _x( 'Parent System Tag:', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'edit_item'                  => _x( 'Edit System Tag', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'update_item'                => _x( 'Update System Tag', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'add_new_item'               => _x( 'Add New System Tag', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'new_item_name'              => _x( 'New System Tag', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'separate_items_with_commas' => _x( 'Separate system tags with commas', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'add_or_remove_items'        => _x( 'Add or remove System Tags', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'choose_from_most_used'      => _x( 'Choose from most used System Tags', 'system tags labels', GTHEME_TEXTDOMAIN ),
				'menu_name'                  => _x( 'System Tags', 'system tags labels', GTHEME_TEXTDOMAIN ),
			),
			'public'                => FALSE,
			'show_in_nav_menus'     => FALSE,
			'show_ui'               => TRUE,
			'show_tagcloud'         => FALSE,
			'hierarchical'          => TRUE,
			'update_count_callback' => array( 'gThemeUtilities', 'update_count_callback' ),
			'rewrite'               => FALSE,
			'query_var'             => TRUE,
			'capabilities'          => array(
				'manage_terms' => $cap,
				'edit_terms'   => $cap,
				'delete_terms' => $cap,
				'assign_terms' => $cap,
			)
		) );
		
		if ( is_admin() ) {
			// FIXME: hook this to menu
			$this->system_tags_table_action( 'gtheme_action' );
			add_action( 'after-'.GTHEME_SYSTEMTAGS.'-table', array( &$this, 'after_system_tags_table' ) );
		}
	}

	// system tags to post_classess
	public function post_class( $classes, $class, $post_ID )
	{
		$system_tags = get_the_terms( $post_ID, GTHEME_SYSTEMTAGS );

		if ( $system_tags && ! is_wp_error( $system_tags ) )
			foreach ( $system_tags as $system_tag )
				$classes[] = 'systemtag-'.$system_tag->slug;

		return $classes;
	}
	
	private function system_tags_table_action( $action_name )
	{
		if ( ! isset( $_REQUEST[$action_name] ) )
			return FALSE;

		if ( 'install_systemtags' == $_REQUEST[$action_name] ) {
			
			$defaults = gThemeOptions::info( 'system_tags_defaults', array() );
			
			if ( count( $defaults ) )
				$added = self::insertDefaults( GTHEME_SYSTEMTAGS, $defaults );
			else
				$added = FALSE;

			if ( $added )
				$action = 'added_systemtags';
			else
				$action = 'error_systemtags';

			wp_redirect( add_query_arg( $action_name, $action ) );
			exit;
		}
	}
	
	public function after_system_tags_table( $taxonomy )
	{
		$action_name = 'gtheme_action';
		$title       = __( 'Install Default System Tags', GTHEME_TEXTDOMAIN );
		$action      = add_query_arg( $action_name, 'install_systemtags' );

		if ( isset( $_GET[$action_name] ) ) {
			if ( 'error_systemtags' == $_GET[$action_name] ) {
				$title = __( 'Error while adding default system tags.', GTHEME_TEXTDOMAIN );
			} else if ( 'added_systemtags' == $_GET[$action_name] ) {
				$title = __( 'Default system tags added.', GTHEME_TEXTDOMAIN );
			}
		}

		echo '<div class="form-wrap"><p>';
			echo '<a href="'.esc_url( $action ).'" class="button">'.$title.'</a>';
		echo '</p></div>';
	}

	public function p2p_init()
	{
		p2p_register_connection_type( array(
			'name'       => 'posts_to_posts',
			'from'       => 'post',
			'to'         => 'post',
			'reciprocal' => TRUE,
			'title'      => __( 'Connected Posts', GTHEME_TEXTDOMAIN ),
		) );
	}

	// helper for settings page
	public static function insertDefaults( $taxonomy, $defaults )
	{
		if ( ! taxonomy_exists( $taxonomy ) )
			return FALSE;

		foreach ( $defaults as $term_slug => $term_name )
			if ( ! term_exists( $term_slug, $taxonomy ) )
				wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );

		return TRUE;
	}
}
