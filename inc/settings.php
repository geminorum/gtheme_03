<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSettings extends gThemeModuleCore 
{
	var $_option_key = 'settings';
	
	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'activation_redirect' => true, // redirect after theme activation
		), $args ) );
		
		if ( $activation_redirect ) {
			global $pagenow;
			if ( is_admin() && isset( $_GET['activated'] ) && 'themes.php' == $pagenow ) {
				wp_redirect( admin_url( 'themes.php?page='.gtheme_get_info( 'settings_page', 'gtheme-theme' ) ) );
				exit;
			}
		}
		
		
		add_action( 'admin_menu', array( & $this, 'admin_menu' ) );
		
		// maybe better : move to admin module
		// disable avatar select on admin settings
		add_filter( 'default_avatar_select', array( & $this, 'default_avatar_select' ) );
		
		add_action( 'gtheme_settings_sub_general', array( & $this, 'sub_general' ), 10, 2 );
		add_action( 'gtheme_settings_load', array( & $this, 'load' ) );
		//add_filter( 'gtheme_settings_sub_
	}
	
	public function default_avatar_select( $avatar_list ) 
	{
		return '<p>'.__( '<strong>The default avatar is overrided by the active theme.</strong>', GTHEME_TEXTDOMAIN ).'</p>';
	}
	
	public function admin_menu()
	{
		$info = gtheme_get_info();
		$page = current_user_can( 'edit_theme_options' ) ? 'themes.php' : 'index.php';
		$hook = add_submenu_page( $page,
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
		$info = gtheme_get_info();
		$page = current_user_can( 'edit_theme_options' ) ? 'themes.php' : 'index.php';
		$settings_uri = $page.'?page='.$info['settings_page'];
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
}
