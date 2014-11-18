<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSettings extends gThemeModuleCore 
{
	
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
		
		$messages = apply_filters( 'gtheme_settings_messages', array() );
		
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
	
	
}
