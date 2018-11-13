<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSettings extends gThemeModuleCore
{

	protected $key = 'settings';

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'activation_redirect' => FALSE, // redirects to settings after activation
		], $args ) );

		$this->set_page();

		if ( is_admin() ) {

			if ( $activation_redirect
				&& isset( $_GET['activated'] )
				&& 'themes.php' == $GLOBALS['pagenow'] )
					gThemeWordPress::redirect( admin_url( $this->_settings_uri ) );

			add_action( 'admin_menu', [ $this, 'admin_menu' ] );

			add_action( 'gtheme_settings_sub_general', [ $this, 'settings_sub_html' ], 10, 2 );
			add_action( 'gtheme_settings_load', [ $this, 'load' ] );

		} else {

			add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 32 );
		}
	}

	private function set_page()
	{
		$this->_settings_parent = current_user_can( 'edit_theme_options' ) ? 'themes.php' : 'index.php';
		$this->_settings_uri = $this->_settings_parent.'?page='.gThemeOptions::info( 'settings_page', 'gtheme-theme' );
	}

	public function admin_menu()
	{
		$hook = add_submenu_page(
			$this->_settings_parent,
			gThemeOptions::info( 'settings_title', _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ) ),
			gThemeOptions::info( 'menu_title', _x( 'Theme Settings', 'Admin Menu Title', GTHEME_TEXTDOMAIN ) ),
			gThemeOptions::info( 'settings_access', 'edit_theme_options' ),
			gThemeOptions::info( 'settings_page', 'gtheme-theme' ),
			array( $this, 'admin_settings' )
		);

		add_action( 'load-'.$hook, [ $this, 'admin_settings_load' ] );
	}

	public function admin_settings()
	{
		$uri  = $this->_settings_uri; // back comp

		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';
		$subs = apply_filters( 'gtheme_settings_subs', [
			'overview' => _x( 'Overview', 'Modules: Menu Name', GTHEME_TEXTDOMAIN ),
			'general'  => _x( 'General', 'Modules: Menu Name', GTHEME_TEXTDOMAIN ),
		] );

		$messages = apply_filters( 'gtheme_settings_messages', [
			'error'   => self::error( _x( 'Settings not updated.', 'Settings Module', GTHEME_TEXTDOMAIN ) ),
			'updated' => self::updated( _x( 'Settings updated.', 'Settings Module', GTHEME_TEXTDOMAIN ) ),
		] );

		echo '<div class="wrap"><h1 class="wp-heading-inline settings-title">'.
			gThemeOptions::info( 'settings_title', _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ) )
			.'</h1> <a href="https://geminorum.ir/wordpress/gtheme_03" class="page-title-action settings-title-action" target="_blank">'
			.GTHEME_VERSION.'</a><hr class="wp-header-end">';

			gThemeHTML::headerNav( $uri, $sub, $subs );

			if ( isset( $_GET['message'] ) ) {

				if ( isset( $messages[$_REQUEST['message']] ) )
					echo $messages[$_REQUEST['message']];

				else
					gThemeHTML::notice( $_REQUEST['message'], 'error fade' );

				$_SERVER['REQUEST_URI'] = remove_query_arg( [ 'message' ], $_SERVER['REQUEST_URI'] );
			}

			if ( file_exists( GTHEME_DIR.'/admin/'.$sub.'.php' ) )
				require_once( GTHEME_DIR.'/admin/'.$sub.'.php' );

			else if ( file_exists( GTHEME_CHILD_DIR.'/admin/'.$sub.'.php' ) )
				require_once( GTHEME_CHILD_DIR.'/admin/'.$sub.'.php' );

			else
				do_action( 'gtheme_settings_sub_'.$sub, $uri, $sub );

		echo '<div class="clear"></div></div>';
	}

	public function admin_settings_load()
	{
		do_action( 'gtheme_settings_load', self::req( 'sub', 'general' ) );
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$defaults = self::defaults();
		$options  = gThemeOptions::getOptions();

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'General Settings', 'Settings Module', GTHEME_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';

			if ( $theme_groups = gThemeOptions::info( 'theme_groups', FALSE ) )
				$this->do_settings_field( [
					'title'   => _x( 'Theme Group', 'Settings Module', GTHEME_TEXTDOMAIN ),
					'type'    => 'select',
					'field'   => 'theme_group',
					'values'  => $theme_groups,
					'default' => ( isset( $options['theme_group'] ) ? $options['theme_group'] : $defaults['theme_group'] ),
					'desc'    => _x( 'Current site\'s theme group.', 'Settings Module', GTHEME_TEXTDOMAIN ),
				], TRUE );

			$this->do_settings_field( [
				'title'   => _x( 'Site User', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'select',
				'field'   => 'default_user',
				'values'  => self::getUsers(),
				'default' => ( isset( $options['default_user'] ) ? $options['default_user'] : $defaults['default_user'] ),
				'desc'    => _x( 'Site default user. For hiding the editoris!', 'Settings Module', GTHEME_TEXTDOMAIN ),
			], TRUE );

			$this->do_settings_field( [
				'title'   => _x( 'FrontPage Title', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'text',
				'field'   => 'frontpage_title',
				'default' => ( isset( $options['frontpage_title'] ) ? $options['frontpage_title'] : $defaults['frontpage_title'] ),
				'desc'    => _x( 'The title used on frontpage. Blank to use the build-in text.', 'Settings Module', GTHEME_TEXTDOMAIN ),
			], TRUE );

			$this->do_settings_field( [
				'title'   => _x( 'FrontPage Description', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'text',
				'field'   => 'frontpage_desc',
				'default' => ( isset( $options['frontpage_desc'] ) ? $options['frontpage_desc'] : $defaults['frontpage_desc'] ),
				'desc'    => _x( 'The description meta tag used on frontpage. Blank to use the build-in text.', 'Settings Module', GTHEME_TEXTDOMAIN ),
			], TRUE );

			$this->do_settings_field( [
				'title'   => _x( 'Extra Body Class', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'type'    => 'text',
				'field'   => 'body_class_extra',
				'default' => ( isset( $options['body_class_extra'] ) ? $options['body_class_extra'] : $defaults['body_class_extra'] ),
				'desc'    => _x( 'Additional classes to append to the html body tag. Seperate with space.', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'dir'     => 'ltr',
			], TRUE );

			if ( $legend = gThemeOptions::info( 'settings_legend', FALSE ) )
				$this->do_settings_field( [
					'title'  => _x( 'Legend', 'Settings Module', GTHEME_TEXTDOMAIN ),
					'type'   => 'custom',
					'field'  => 'custom',
					'values' => $legend,
				], TRUE );

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

				gThemeWordPress::redirectReferer( $result ? 'updated' : 'error' );
			}
		}
	}

	public static function defaults()
	{
		return [
			'theme_group'      => 'main',
			'default_user'     => 0,
			'frontpage_title'  => '',
			'frontpage_desc'   => '',
			'body_class_extra' => '',
		];
	}

	public function admin_bar_menu( $wp_admin_bar )
	{
		if ( ! is_admin_bar_showing() || ! is_user_logged_in() )
			return;

		if ( current_user_can( 'customize' ) ) {
			$wp_admin_bar->remove_node( 'customize' );
			remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
		}

		if ( current_user_can( 'publish_posts' ) )
			$wp_admin_bar->add_node( [
				'id'    => 'gtheme-flush',
				'title' => '<span class="ab-icon dashicons dashicons-backup" style="margin:2px 0 0 0;"></span>',
				'href'  => add_query_arg( 'flush', '', gThemeURL::current() ),
				'meta'  => [ 'title' => _x( 'Flush', 'Settings Module', GTHEME_TEXTDOMAIN ) ],
			] );

		if ( current_user_can( 'edit_posts' ) )
			$wp_admin_bar->add_node( [
				'parent' => 'site-name',
				'id'     => 'all-posts',
				'title'  => _x( 'All Posts', 'Settings Module', GTHEME_TEXTDOMAIN ),
				'href'   => admin_url( 'edit.php' ),
			] );

		if ( ! current_user_can( gThemeOptions::info( 'settings_access', 'edit_theme_options' ) ) )
			return;

		$wp_admin_bar->remove_node( 'themes' );

		$wp_admin_bar->add_node( [
			'parent' => 'appearance',
			'id'     => 'gtheme',
			'title'  => gThemeOptions::info( 'menu_title', _x( 'Theme Settings', 'Admin Menu Title', GTHEME_TEXTDOMAIN ) ),
			'href'   => admin_url( $this->_settings_uri ),
			'meta'   => [ 'title' => gThemeOptions::info( 'settings_title', _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ) ) ],
		] );

		if ( ! gThemeWordPress::isDev() )
			return;

		$wp_admin_bar->add_node( [
			'id'     => 'gtheme-template-base',
			'title'  => gtheme_template_base() ? esc_html( gtheme_template_base() ) : '[EMPTY]',
			'parent' => 'top-secondary',
			'href'   => FALSE,
			'meta'   => [ 'title' => _x( 'Theme Template Base', 'Modules: Settings', GTHEME_TEXTDOMAIN ) ],
		] );
	}
}
