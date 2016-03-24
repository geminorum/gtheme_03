<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemePages extends gThemeModuleCore
{

	protected $option_key = 'pages';

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'admin' => FALSE,
		), $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', array( $this, 'subs' ), 5 );
			add_action( 'gtheme_settings_load', array( $this, 'load' ) );
		}
	}

	public static function defaults( $extra = array() )
	{
		return array_merge( array(
			'about' => array(
				'title' => _x( 'About Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s main information', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'def'   => 0,
			),
			'contact' => array(
				'title' => _x( 'Contact Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s contact details', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'def'   => 0,
			),
			'search' => array(
				'title' => _x( 'Search Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s advanced search tools', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'def'   => 0,
			),
			'archives' => array(
				'title' => _x( 'Archives Page', 'Pages Module', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s main archives', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'def'   => 0,
			),
			'latest' => array(
				'title' => _x( 'Latest Posts Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s latest posts list', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'def'   => 0,
			),
			'social' => array(
				'title' => _x( 'Social Profiles Page', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Select a page for this site\'s social profiles', 'Pages Module', GTHEME_TEXTDOMAIN ),
				'def'   => 0,
			),
		), $extra );
	}

	public static function get( $name, $def = 0 )
	{
		$option_pages = gThemeOptions::getOption( 'pages', array() );
		if ( count( $option_pages ) && isset( $option_pages[$name] ) )
			return $option_pages[$name];

		$info_pages = gThemeOptions::info( 'pages', array() );
		if ( count( $info_pages ) && isset( $info_pages[$name] )  )
			return $info_pages[$name]['def'];

		return $def;
	}

	public static function link( $name, $atts = array() )
	{
		$args = self::atts( array(
			'title'   => NULL,
			'attr'    => FALSE,
			'def'     => '#',
			'class'   => FALSE,
			'before'  => '',
			'after'   => '',
			'echo'    => TRUE,
			'context' => NULL,
			'rel'     => FALSE,
		), $atts );

		if ( $page = self::get( $name, 0 ) ) {
			$args['def'] = get_permalink( $page );

			if ( ! $args['title'] )
				$args['title'] = get_the_title( $page );
		}

		if ( $args['title'] ) {
			$html = $args['before'].gThemeUtilities::html( 'a', array(
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
		$subs['pages'] = _x( 'Pages', 'Pages Module: Tab Title', GTHEME_TEXTDOMAIN );
		return $subs;
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$defaults = gThemeOptions::info( 'pages', array() );
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
						'default' => isset( $options[$page] ) ? $options[$page] : $defaults[$page]['def'],
						'desc'    => isset( $default['desc'] ) ? $default['desc'] : '',
					), TRUE );
				}

			echo '</table>';

			submit_button();

			wp_nonce_field( 'gtheme-pages', '_gtheme_pages' );
		echo '</form>';
	}

	public function load( $sub )
	{
		if ( 'pages' == $sub ) {

			if ( ! empty( $_POST )
				&& wp_verify_nonce( $_POST['_gtheme_pages'], 'gtheme-pages' ) ) {

					$options = gThemeOptions::getOption( 'pages', array() );

					foreach ( gThemeOptions::info( 'pages', array() ) as $option => $default )

						if ( isset( $_POST['gtheme_pages'][$option] )
							&& trim( $_POST['gtheme_pages'][$option] ) )
								$options[$option] = $_POST['gtheme_pages'][$option];

						else
							unset( $options[$option] );

					$result = gThemeOptions::update_option( 'pages', $options );

					wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
					exit();

			}

			add_action( 'gtheme_settings_sub_pages', array( $this, 'settings_sub_html' ), 10, 2 );
		}
	}
}
