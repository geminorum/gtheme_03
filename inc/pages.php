<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemePages extends gThemeModuleCore
{

	protected $key = 'pages';

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'admin' => FALSE,
		), $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', array( $this, 'subs' ), 5 );
			add_action( 'gtheme_settings_load', array( $this, 'load' ) );
		}
	}

	public static function defaultPages( $extra = array() )
	{
		return array_merge( array(
			'about'    => _x( 'About', 'Pages Module: Default Pages', GTHEME_TEXTDOMAIN ),
			'contact'  => _x( 'Contact', 'Pages Module: Default Pages', GTHEME_TEXTDOMAIN ),
			'search'   => _x( 'Search', 'Pages Module: Default Pages', GTHEME_TEXTDOMAIN ),
			'archives' => _x( 'Archives', 'Pages Module: Default Pages', GTHEME_TEXTDOMAIN ),
			'latest'   => _x( 'Latest Posts', 'Pages Module: Default Pages', GTHEME_TEXTDOMAIN ),
			'social'   => _x( 'Social Profiles', 'Pages Module: Default Pages', GTHEME_TEXTDOMAIN ),
		), $extra );
	}

	public static function defaults( $extra = array() )
	{
		return array_merge( array(
			'about' => array(
				'title' => _x( 'About Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s main information', 'Pages Module', GTHEME_TEXTDOMAIN ),
			),
			'contact' => array(
				'title' => _x( 'Contact Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s contact details', 'Pages Module', GTHEME_TEXTDOMAIN ),
			),
			'search' => array(
				'title' => _x( 'Search Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s advanced search tools', 'Pages Module', GTHEME_TEXTDOMAIN ),
			),
			'archives' => array(
				'title' => _x( 'Archives Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s main archives', 'Pages Module', GTHEME_TEXTDOMAIN ),
			),
			'latest' => array(
				'title' => _x( 'Latest Posts Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s latest posts list', 'Pages Module', GTHEME_TEXTDOMAIN ),
			),
			'social' => array(
				'title' => _x( 'Social Profiles Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s social profiles', 'Pages Module', GTHEME_TEXTDOMAIN ),
			),
		), $extra );
	}

	public static function get( $name, $def = 0 )
	{
		$option_pages = gThemeOptions::getOption( 'pages', array() );
		if ( count( $option_pages ) && isset( $option_pages[$name] ) )
			return $option_pages[$name];

		$info_pages = gThemeOptions::info( 'pages_list', self::defaults() );
		if ( count( $info_pages ) && isset( $info_pages[$name] ) )
			if ( $page = get_page_by_path( $name, OBJECT, 'page' ) )
				return $page->ID;

		return $def;
	}

	public static function link( $name, $atts = array() )
	{
		$args = self::atts( array(
			'title'   => NULL,
			'attr'    => FALSE,
			'url'     => FALSE,
			'def'     => '#',
			'class'   => FALSE,
			'before'  => '',
			'after'   => '',
			'echo'    => TRUE,
			'context' => NULL,
			'rel'     => FALSE,
		), $atts );

		if ( $args['url'] )
			$args['def'] = $args['url'];

		else if ( $page = self::get( $name, 0 ) )
			$args['def'] = get_permalink( $page );

		if ( is_null( $args['title'] ) && $page )
			$args['title'] = get_the_title( $page );

		if ( $args['title'] ) {

			$html = $args['before'].gThemeHTML::tag( 'a', array(
				'href'  => $args['def'],
				'class' => $args['class'],
				'title' => $args['attr'],
			), $args['title'] ).$args['after'];

			if ( ! $args['echo'] )
				return $html;

			echo $html;

		} else if ( ! $args['echo'] ) {
			return FALSE;
		}
	}

	public function subs( $subs )
	{
		return array_merge( $subs, array( 'pages' => _x( 'Pages', 'Modules: Menu Name', GTHEME_TEXTDOMAIN ) ) );
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$defaults = gThemeOptions::info( 'pages_list', self::defaults() );
		$options  = gThemeOptions::getOption( 'pages', array() );

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'Site Page Settings', 'Pages Module: Form Title', GTHEME_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';

				foreach ( $defaults as $page => $default ) {
					$this->do_settings_field( array(
						'title'   => $default['title'],
						'values'  => isset( $default['type'] ) ? $default['type'] : 'page',
						'type'    => 'page',
						'field'   => $page,
						'default' => isset( $options[$page] ) ? $options[$page] : 0,
						'desc'    => isset( $default['desc'] ) ? $default['desc'] : '',
					), TRUE );
				}

			echo '</table>';
			echo '<p class="submit">';

				$this->settings_buttons( 'pages', FALSE );
				echo get_submit_button( _x( 'Create Default Pages', 'Pages Module', GTHEME_TEXTDOMAIN ), 'secondary', 'create-default-pages', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';
				echo get_submit_button( _x( 'Create Default Menus', 'Pages Module', GTHEME_TEXTDOMAIN ), 'secondary', 'create-default-menus', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';

			echo '</p>';
			wp_nonce_field( 'gtheme-pages', '_gtheme_pages' );
		echo '</form>';
	}

	public function load( $sub )
	{
		if ( 'pages' == $sub ) {

			if ( ! empty( $_POST )
				&& wp_verify_nonce( $_POST['_gtheme_pages'], 'gtheme-pages' ) ) {


				if ( ! empty( $_POST['create-default-menus'] ) ) {

					$map   = gThemeOptions::info( 'pages_pre_map', array() );
					$nav   = gThemeOptions::info( 'pages_nav_menu', 'primary' );
					$count = 0;

					if ( $map && count( $map ) ) {

						if ( $object = wp_get_nav_menu_object( $nav ) )
							$menu = $object->term_id;
						else
							$menu = wp_create_nav_menu( $nav );

						foreach ( $map as $slug => $title ) {
							if ( $page = get_page_by_path( $slug, OBJECT, 'page' ) ) {
								$id = wp_update_nav_menu_item( $menu, 0, array(
									'menu-item-title'     => $title,
									'menu-item-object'    => 'page',
									'menu-item-object-id' => $page->ID,
									'menu-item-type'      => 'post_type',
									'menu-item-status'    => 'publish'
								) );

								if ( ! is_wp_error( $id ) )
									$count++;
							}
						}

						wp_redirect( add_query_arg( array(
							'message' => 'created',
							'count'   => $count,
						), wp_get_referer() ) );
						exit();
					}

				} else if ( ! empty( $_POST['create-default-pages'] ) ) {

					$map   = gThemeOptions::info( 'pages_pre_map', array() );
					$text  = gThemeOptions::info( 'pages_pre_text', _x( '[ This page is being completed ]', 'Options: Page Pre-Text', GTHEME_TEXTDOMAIN ) );
					$user  = gThemeOptions::getOption( 'default_user', 0 );
					$count = 0;

					if ( $map && count( $map ) ) {
						foreach ( $map as $slug => $title ) {
							if ( ! $page = get_page_by_path( $slug, OBJECT, 'page' ) ) {
								$id = wp_insert_post( array(
									'post_title'   => $title,
									'post_name'    => $slug,
									'post_content' => $text,
									'post_author'  => $user,
									'post_status'  => 'publish',
									'post_type'    => 'page',
								) );

								if ( ! is_wp_error( $id ) )
									$count++;
							}
						}

						wp_redirect( add_query_arg( array(
							'message' => 'created',
							'count'   => $count,
						), wp_get_referer() ) );
						exit();
					}

				} else {

					$options = gThemeOptions::getOption( 'pages', array() );

					foreach ( gThemeOptions::info( 'pages_list', array() ) as $option => $default )

						if ( isset( $_POST['gtheme_pages'][$option] )
							&& trim( $_POST['gtheme_pages'][$option] ) )
								$options[$option] = $_POST['gtheme_pages'][$option];

						else
							unset( $options[$option] );

					$result = gThemeOptions::update_option( 'pages', $options );

					wp_redirect( add_query_arg( array(
						'message' => ( $result ? 'updated' : 'error' ),
					), wp_get_referer() ) );
					exit();
				}
			}

			add_action( 'gtheme_settings_sub_pages', array( $this, 'settings_sub_html' ), 10, 2 );
		}
	}
}
