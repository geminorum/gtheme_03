<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSettings extends gThemeModuleCore
{

	protected $option_key = 'settings';

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'activation_redirect' => TRUE, // redirect after theme activation
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

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			add_action( 'gtheme_settings_sub_general', array( $this, 'settings_sub_html' ), 10, 2 );
			add_action( 'gtheme_settings_load', array( $this, 'load' ) );

		} else {

			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 32 );

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
			array( $this, 'admin_settings' )
		);

		add_action( 'load-'.$hook, array( $this, 'admin_settings_load' ) );
	}

	public function admin_settings()
	{
		$info = gThemeOptions::info();
		$settings_uri = $this->_settings_uri; // back comp

		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';
		$subs = apply_filters( 'gtheme_settings_subs', array(
			'overview' => _x( 'Overview', 'Settings Module: Sub Title', GTHEME_TEXTDOMAIN ),
			'general'  => _x( 'General', 'Settings Module: Sub Title', GTHEME_TEXTDOMAIN ),
		) );

		$messages = apply_filters( 'gtheme_settings_messages', array(
			'error'   => self::error( _x( 'Settings not updated.', 'Settings Module', GTHEME_TEXTDOMAIN ) ),
			'updated' => self::updated( _x( 'Settings updated.', 'Settings Module', GTHEME_TEXTDOMAIN ) ),
		) );

		echo '<div class="wrap"><h1>'.$info['settings_title']
			.' <a href="http://geminorum.ir/wordpress/gtheme_03" class="page-title-action" target="_blank">'
			.GTHEME_VERSION.'</a></h1>';

			gThemeUtilities::headerNav( $settings_uri, $sub, $subs );

			if ( isset( $_GET['message'] ) ) {
				if ( isset( $messages[$_REQUEST['message']] ) ) {
					echo $messages[$_REQUEST['message']];
				} else {
					gThemeUtilities::notice( $_REQUEST['message'], 'error fade' );
				}
				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
			}

			if ( file_exists( GTHEME_DIR.'/admin/'.$sub.'.php' ) )
				require_once( GTHEME_DIR.'/admin/'.$sub.'.php' );
			else if ( file_exists( GTHEME_CHILD_DIR.'/admin/'.$sub.'.php' ) )
				require_once( GTHEME_CHILD_DIR.'/admin/'.$sub.'.php' );
			else
				do_action( 'gtheme_settings_sub_'.$sub, $settings_uri, $sub );

		echo '<div class="clear"></div></div>';
	}

	public function admin_settings_load()
	{
		$sub = isset( $_REQUEST['sub'] ) ? $_REQUEST['sub'] : 'general';
		do_action( 'gtheme_settings_load', $sub );
	}

	public function settings_sub_html( $settings_uri, $sub = 'general' )
	{
		$defaults = self::defaults();
		$options  = gThemeOptions::getOptions();

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'General Settings', 'Settings Module', GTHEME_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';

			$this->do_settings_field( array(
				'title'   => _x( 'Site User', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'select',
				'field'   => 'default_user',
				'values'  => self::getUsers(),
				'default' => ( isset( $options['default_user'] ) ? $options['default_user'] : $defaults['default_user'] ),
				'desc'    => _x( 'Site default user. For hiding the editoris!', 'Settings Module', GTHEME_TEXTDOMAIN ),
			), TRUE );

			$this->do_settings_field( array(
				'title'   => _x( 'FrontPage Title', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'text',
				'field'   => 'frontpage_title',
				'default' => ( isset( $options['frontpage_title'] ) ? $options['frontpage_title'] : $defaults['frontpage_title'] ),
				'desc'    => _x( 'The title used on frontpage. Blank to use the build-in text.', 'Settings Module', GTHEME_TEXTDOMAIN ),
			), TRUE );

			$this->do_settings_field( array(
				'title'   => _x( 'FrontPage Description', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'text',
				'field'   => 'frontpage_desc',
				'default' => ( isset( $options['frontpage_desc'] ) ? $options['frontpage_desc'] : $defaults['frontpage_desc'] ),
				'desc'    => _x( 'The description meta tag used on frontpage. Blank to use the build-in text.', 'Settings Module', GTHEME_TEXTDOMAIN ),
			), TRUE );

			$this->do_settings_field( array(
				'title'   => _x( 'Extra Body Class', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'text',
				'field'   => 'body_class_extra',
				'default' => ( isset( $options['body_class_extra'] ) ? $options['body_class_extra'] : $defaults['body_class_extra'] ),
				'desc'    => _x( 'Additional class to append to the body classess. Seperate with single space', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'dir'     => 'ltr',
			), TRUE );

			if ( $legend = gThemeOptions::info( 'settings_legend', FALSE ) )
				$this->do_settings_field( array(
					'title'  => _x( 'Legend', 'Settings Module', GTHEME_TEXTDOMAIN ),
					'type'   => 'custom',
					'field'  => 'custom',
					'values' => $legend,
				), TRUE );

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

				$options = gThemeOptions::getOptions();
				foreach ( self::defaults() as $option => $default )
					if ( isset( $_POST['gtheme_settings'][$option] )
						&& trim( $_POST['gtheme_settings'][$option] ) )
							$options[$option] = $_POST['gtheme_settings'][$option];
					else
						unset( $options[$option] );

				$result = gThemeOptions::updateOptions( $options );
				wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
				exit();
			}
		}
	}

	public static function defaults()
	{
		return array(
			'default_user'     => 0,
			'frontpage_title'  => '',
			'frontpage_desc'   => '',
			'body_class_extra' => '',
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
				'id'    => 'gtheme-flush',
				'title' => '<span class="ab-icon dashicons dashicons-backup" style="margin:2px 0 0 0;"></span>',
				'href'  => add_query_arg( 'flush', '', gThemeUtilities::getCurrentURL() ),
				'meta'  => array(
					'title' => _x( 'Flush', 'Settings Module', GTHEME_TEXTDOMAIN ),
				),
			) );

		if ( current_user_can( 'edit_posts' ) )
			$wp_admin_bar->add_node( array(
				'parent' => 'site-name',
				'id'     => 'all-posts',
				'title'  => _x( 'All Posts', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'href'   => admin_url( 'edit.php' ),
			) );

		if ( ! current_user_can( 'edit_theme_options' )
			|| ! current_user_can( $info['settings_access'] ) )
			return;

		$wp_admin_bar->remove_node( 'themes' );

		$wp_admin_bar->add_node( array(
			'parent' => 'appearance',
			'id'     => 'gtheme',
			'title'  => $info['menu_title'],
			'href'   => admin_url( $this->_settings_uri ),
			'meta'   => array(
				'title' => $info['settings_title'],
			),
		) );

		if ( gThemeUtilities::isDev() )
			$wp_admin_bar->add_node( array(
				'id'     => 'gtheme-template-base',
				'title'  => esc_html( gtheme_template_base() ),
				'parent' => 'top-secondary',
				'href'   => FALSE,
			) );
	}
}
