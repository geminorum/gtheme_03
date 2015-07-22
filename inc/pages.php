<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemePages extends gThemeModuleCore
{

	var $_option_key = 'pages';

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'admin' => FALSE,
		), $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', array( &$this, 'subs' ), 5 );
			add_action( 'gtheme_settings_load', array( &$this, 'load' ) );
		}
	}

	public static function get( $name, $def = 0 )
	{
		$option_pages = gThemeOptions::get_option( 'pages', array() );
		if ( count( $option_pages ) && isset( $option_pages[$name] ) )
			return $option_pages[$name];

		$info_pages = gThemeOptions::info( 'pages', array() );
		if ( count( $info_pages ) && isset( $info_pages[$name] )  )
			return $info_pages[$name]['def'];

		return $def;
	}
	
	public static function link( $name, $def = '#' )
	{
		// FIXME
		return $def;
	}

	public function subs( $subs )
	{
		$subs['pages'] = __( 'Pages', GTHEME_TEXTDOMAIN );
		return $subs;
	}

	public function settings_sub_html( $settings_uri, $sub = 'general' )
	{
		$defaults = gThemeOptions::info( 'pages', array() );
		$options  = gThemeOptions::get_option( 'pages', array() );

		echo '<form method="post" action="">';
			// echo '<h3>'.__( 'General Settings', GTHEME_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';

				foreach ( $defaults as $page => $default ) {
					$this->do_settings_field( array(
						'title'   => $default['title'],
						'values'   => isset( $default['type'] ) ? $default['type'] : 'page',
						'type'    => 'page',
						'field'   => $page,
						'default' => ( isset( $options[$page] ) ? $options[$page] : $defaults[$page]['def'] ),
						'desc'    => $default['desc'],
					), TRUE );
				}

			echo '</table>';

			submit_button();

			wp_nonce_field( 'gtheme-pages', '_gtheme_pages' );
		echo '</form>';

		gnetwork_dump($options);
	}

	public function load( $sub )
	{
		if ( 'pages' == $sub ) {

			if ( ! empty( $_POST )
				&& wp_verify_nonce( $_POST['_gtheme_pages'], 'gtheme-pages' ) ) {

					$options = gThemeOptions::get_option( 'pages', array() );
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

			add_action( 'gtheme_settings_sub_pages', array( &$this, 'settings_sub_html' ), 10, 2 );
		}
	}
}
