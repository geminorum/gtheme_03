<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSettings extends gThemeModuleCore 
{
	var $_option_key = 'settings';
	
	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'activation_redirect' => true, // redirect after theme activation
		), $args ) );
		
		$this->set_page();
		
		if ( is_admin() ) {
		
			if ( $activation_redirect ) {
				global $pagenow;
				if ( isset( $_GET['activated'] ) && 'themes.php' == $pagenow ) {
					wp_redirect( admin_url( $this->_settings_uri ) );
					exit;
				}
			}
		
			add_action( 'admin_menu', array( & $this, 'admin_menu' ) );
			
			add_action( 'gtheme_settings_sub_general', array( & $this, 'sub_general' ), 10, 2 );
			add_action( 'gtheme_settings_load', array( & $this, 'load' ) );
		
		} else {
			
			add_action( 'admin_bar_menu', array( & $this, 'admin_bar_menu' ), 32 );
			
		}
	}
	
	private function set_page()
	{
		$this->_settings_parent = current_user_can( 'edit_theme_options' ) ? 'themes.php' : 'index.php';
		$this->_settings_uri = $this->_settings_parent.'?page='.gThemeOptions::info( 'settings_page', 'gtheme-theme' );
	}
	
	public function admin_menu()
	{
		$info = gThemeOptions::info();
		$hook = add_submenu_page( $this->_settings_parent,
			$info['settings_title'],
			$info['menu_title'],
			$info['settings_access'],
			$info['settings_page'],
			array( & $this, 'admin_settings' ) 
		);
		
		add_action( 'load-'.$hook, array( & $this, 'admin_settings_load' ) ); 
	}
	
	public function admin_settings()
	{
		$info = gThemeOptions::info();
		$settings_uri = $this->_settings_uri; // back comp
		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';
		$subs = apply_filters( 'gtheme_settings_subs', array(
			'overview' => __( 'Overview', GTHEME_TEXTDOMAIN ),
			'general' => __( 'General', GTHEME_TEXTDOMAIN ),
		) );
		
		$messages = apply_filters( 'gtheme_settings_messages', array(
			'error' => gThemeUtilities::notice( __( 'Settings not updated.', GTHEME_TEXTDOMAIN ), 'error', false ),
			'updated' => gThemeUtilities::notice( __( 'Settings updated.', GTHEME_TEXTDOMAIN ), 'updated fade', false ),
		) );
		
		echo '<div class="wrap"><h2>'.$info['settings_title'].'</h2>';
		
			gThemeUtilities::headerNav( $settings_uri, $sub, $subs );
			
			if ( isset( $_GET['message'] ) ) { 
				if ( isset( $messages[$_REQUEST['message']] ) ) {
					echo $messages[$_REQUEST['message']];
				} else {
					gThemeUtilities::notice( $_REQUEST['message'], 'error fade' );
				}
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] ); 
			}  
		
			if ( file_exists( GTHEME_DIR.'admin'.DS.$sub.'.php' ) ) 
				require_once( GTHEME_DIR.'admin'.DS.$sub.'.php' );
			else if ( file_exists( GTHEME_CHILD_DIR.'admin'.DS.$sub.'.php' ) ) 
				require_once( GTHEME_CHILD_DIR.'admin'.DS.$sub.'.php' );
			else 
				do_action( 'gtheme_settings_sub_'.$sub, $settings_uri, $sub );
		
		echo '<div class="clear"></div></div>';
	}
	
	public function admin_settings_load()
	{
		$sub = isset( $_REQUEST['sub'] ) ? $_REQUEST['sub'] : null;
		do_action( 'gtheme_settings_load', $sub );
	}
	
	public function sub_general( $settings_uri, $sub )
	{
		$defaults = self::defaults();
		$options = gThemeOptions::get_options();
	
		echo '<form method="post" action="">';
			echo '<h3>'.__( 'General Settings', GTHEME_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';
			
			$this->do_settings_field( array(
				'title' => __( 'Site User', GTHEME_TEXTDOMAIN ),
				'type' => 'select',
				'field' => 'default_user',
				'values' => self::getUsers(),
				'default' => ( isset( $options['default_user'] ) ? $options['default_user'] : $defaults['default_user'] ),
				'desc' => __( 'Site default user. using to hide editors!', GTHEME_TEXTDOMAIN ),
			), true );
			
			$this->do_settings_field( array(
				'title' => __( 'FrontPage Title', GTHEME_TEXTDOMAIN ),
				'type' => 'text',
				'field' => 'frontpage_title',
				'default' => ( isset( $options['frontpage_title'] ) ? $options['frontpage_title'] : $defaults['frontpage_title'] ),
				'desc' => __( 'The title used on frontpage. Blank to use the build-in text.', GTHEME_TEXTDOMAIN ),
			), true );
			
			$this->do_settings_field( array(
				'title' => __( 'FrontPage Description', GTHEME_TEXTDOMAIN ),
				'type' => 'text',
				'field' => 'frontpage_desc',
				'default' => ( isset( $options['frontpage_desc'] ) ? $options['frontpage_desc'] : $defaults['frontpage_desc'] ),
				'desc' => __( 'The description meta tag used on frontpage. Blank to use the build-in text.', GTHEME_TEXTDOMAIN ),
			), true );
			
			echo '</table>';
			
			submit_button();
			
			wp_nonce_field( 'gtheme-settings', '_gtheme_settings' );
		echo '</form>';		
	}
	
	public function load( $sub )
	{
		if ( 'general' == $sub ) { 
			if ( ! empty( $_POST ) 
				&& wp_verify_nonce( $_POST['_gtheme_settings'], 'gtheme-settings' ) ) {
				
				$options = gThemeOptions::get_options();
				foreach ( self::defaults() as $option => $default )
					if ( isset( $_POST['gtheme_settings'][$option] )
						&& trim( $_POST['gtheme_settings'][$option] ) )
							$options[$option] = $_POST['gtheme_settings'][$option];
					else
						unset( $options[$option] );
			
				$result = gThemeOptions::update_options( $options );
				wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
				exit();
			}
		}
	}
	
	public static function defaults()
	{
		return array(
			'default_user' => 0,
			'frontpage_title' => '',
			'frontpage_desc' => '',
		);
	}
	
	public function admin_bar_menu( $wp_admin_bar )
	{
		if ( ! is_admin_bar_showing() || ! is_user_logged_in() )
			return;
		
		$info = gThemeOptions::info();
		
		if ( current_user_can( 'customize' ) ) {
			$wp_admin_bar->remove_node( 'customize' );
			remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
		}
		
		if ( current_user_can( 'publish_posts' ) )
			$wp_admin_bar->add_node( array( 
				'id' => 'gtheme-flush', 
				'title' => '<span class="ab-icon dashicons dashicons-backup" style="margin:2px 0 0 0;"></span>',
				'meta'   => array(
					'title' => __( 'Flush', GTHEME_TEXTDOMAIN ), 
				),
				'href' => add_query_arg( 'flush', 'flush', gThemeUtilities::getCurrentURL() ),
			) );
		
		if ( current_user_can( 'edit_posts' ) )
			$wp_admin_bar->add_node( array(
				'parent' => 'site-name',
				'id'     => 'all-posts',
				'title'  => __( 'All Posts' ),
				'href'   => admin_url( 'edit.php' ),
			) );
		
		if ( ! current_user_can( 'edit_theme_options' ) 
			|| ! current_user_can( $info['settings_access'] ) )
			return;		
		
		$wp_admin_bar->remove_node( 'themes' );
		
		$wp_admin_bar->add_node( array( 
			'parent' => 'appearance', 
			'id' => 'gtheme', 
			'title' => $info['menu_title'], 
			'href' => admin_url( $this->_settings_uri ),
			'meta'  => array(
				'title' => $info['settings_title'],
			),
		) );
		
		if ( gThemeUtilities::is_dev() )
			$wp_admin_bar->add_node( array(
				'id'     => 'gtheme-template-base',
				'title'  => esc_html( gtheme_template_base() ),
				'parent' => 'top-secondary',
				'href'   => false,
			) );
	}
}
