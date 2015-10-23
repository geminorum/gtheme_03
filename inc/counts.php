<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeCounts extends gThemeModuleCore
{

	var $_option_key = 'counts';

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

	public static function defaults( $extra = array() )
	{
		return array_merge( array(
			'dashboard' => array(
				'title' => _x( 'Dashboard', 'Counts Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Dashboard Count', 'Counts Module', GTHEME_TEXTDOMAIN ),
				'def'   => 5,
			),
			'latest' => array(
				'title' => _x( 'Latest Posts', 'Counts Module', GTHEME_TEXTDOMAIN ),
				'desc'  => _x( 'Latest Posts Count', 'Counts Module', GTHEME_TEXTDOMAIN ),
				'def'   => 5,
			),
		), $extra );
	}

	public static function get( $name, $def = 0 )
	{
		$option_counts = gThemeOptions::getOption( 'counts', array() );
		if ( count( $option_counts ) && isset( $option_counts[$name] ) )
			return $option_counts[$name];

		$info_counts = gThemeOptions::info( 'counts', array() );
		if ( count( $info_counts ) && isset( $info_counts[$name] )  )
			return $info_counts[$name]['def'];

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
		$subs['counts'] = _x( 'Counts', 'Counts Module: Tab Title', GTHEME_TEXTDOMAIN );
		return $subs;
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$defaults = gThemeOptions::info( 'counts', array() );
		$options  = gThemeOptions::getOption( 'counts', array() );

		echo '<form method="post" action="">';
			// echo '<h3>'.__( 'General Settings', GTHEME_TEXTDOMAIN ).'</h3>';
			echo '<table class="form-table">';

				foreach ( $defaults as $count => $default ) {
					$this->do_settings_field( array(
						'title'   => $default['title'],
						'values'   => isset( $default['type'] ) ? $default['type'] : 'page',
						'type'    => 'number',
						'field'   => $count,
						'default' => ( isset( $options[$count] ) ? $options[$count] : $defaults[$count]['def'] ),
						'desc'    => isset( $default['desc'] ) ? $default['desc'] : '',
					), TRUE );
				}

			echo '</table>';

			submit_button();

			wp_nonce_field( 'gtheme-counts', '_gtheme_counts' );
		echo '</form>';
	}

	public function load( $sub )
	{
		if ( 'counts' == $sub ) {

			if ( ! empty( $_POST )
				&& wp_verify_nonce( $_POST['_gtheme_counts'], 'gtheme-counts' ) ) {

					$options = gThemeOptions::getOption( 'counts', array() );

					foreach ( gThemeOptions::info( 'counts', array() ) as $option => $default )

						if ( isset( $_POST['gtheme_counts'][$option] )
							&& trim( $_POST['gtheme_counts'][$option] ) )
								$options[$option] = $_POST['gtheme_counts'][$option];

						else
							unset( $options[$option] );

					$result = gThemeOptions::update_option( 'counts', $options );

					wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
					exit();
			}

			add_action( 'gtheme_settings_sub_counts', array( &$this, 'settings_sub_html' ), 10, 2 );
		}
	}
}