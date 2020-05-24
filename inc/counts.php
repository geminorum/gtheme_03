<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeCounts extends gThemeModuleCore
{

	protected $key = 'counts';

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'admin' => FALSE,
		], $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', [ $this, 'subs' ], 5 );
			add_action( 'gtheme_settings_load', [ $this, 'load' ] );
		}
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'dashboard' => [
				'title' => _x( 'Dashboard', 'Modules: Counts', 'gtheme' ),
				'desc'  => _x( 'Dashboard Count', 'Modules: Counts', 'gtheme' ),
				'def'   => 5,
			],
			'latest' => [
				'title' => _x( 'Latest Posts', 'Modules: Counts', 'gtheme' ),
				'desc'  => _x( 'Latest Posts Count', 'Modules: Counts', 'gtheme' ),
				'def'   => 5,
			],
		], $extra );
	}

	public static function get( $name, $default = NULL )
	{
		$option_counts = gThemeOptions::getOption( 'counts', [] );

		if ( count( $option_counts ) && isset( $option_counts[$name] ) )
			return $option_counts[$name];

		if ( ! is_null( $default ) )
			return $default;

		$info_counts = gThemeOptions::info( 'counts', self::defaults() );

		if ( count( $info_counts ) && isset( $info_counts[$name] )  )
			return $info_counts[$name]['def'];

		return 0;
	}

	public static function link( $name, $atts = [] )
	{
		$args = self::atts( [
			'title'   => NULL,
			'attr'    => FALSE,
			'def'     => '#',
			'class'   => FALSE,
			'before'  => '',
			'after'   => '',
			'echo'    => TRUE,
			'context' => NULL,
			'rel'     => FALSE,
		], $atts );

		if ( $page = self::get( $name, 0 ) ) {
			$args['def'] = get_permalink( $page );

			if ( ! $args['title'] )
				$args['title'] = get_the_title( $page );
		}

		if ( $args['title'] ) {
			$html = $args['before'].gThemeHTML::tag( 'a', [
				'href'  => $args['def'],
				'class' => $args['class'],
				'title' => $args['attr'],
			], $args['title'] ).$args['after'];

			if ( ! $args['echo'] )
				return $html;

			echo $html;

		} else if ( ! $args['echo'] ) {
			return FALSE;
		}
	}

	public function subs( $subs )
	{
		return array_merge( $subs, [ 'counts' => _x( 'Counts', 'Modules: Menu Name', 'gtheme' ) ] );
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$defaults = gThemeOptions::info( 'counts', self::defaults() );
		$options  = gThemeOptions::getOption( 'counts', [] );

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'Item Counts', 'Modules: Counts', 'gtheme' ).'</h3>';
			echo '<table class="form-table">';

				foreach ( $defaults as $count => $default ) {
					$this->do_settings_field( [
						'title'   => $default['title'],
						'values'  => isset( $default['type'] ) ? $default['type'] : 'page',
						'type'    => 'number',
						'field'   => $count,
						'default' => ( isset( $options[$count] ) ? $options[$count] : $defaults[$count]['def'] ),
						'desc'    => isset( $default['desc'] ) ? $default['desc'] : '',
					], TRUE );
				}

			echo '</table>';

			submit_button();

			wp_nonce_field( 'gtheme-counts', '_gtheme_counts' );
		echo '</form>';
	}

	public function load( $sub )
	{
		if ( 'counts' == $sub ) {

			if ( ! empty( $_POST ) && wp_verify_nonce( $_POST['_gtheme_counts'], 'gtheme-counts' ) ) {

				$options = gThemeOptions::getOption( 'counts', [] );

				foreach ( gThemeOptions::info( 'counts', self::defaults() ) as $option => $default )

					if ( isset( $_POST['gtheme_counts'][$option] ) )
						$options[$option] = trim( $_POST['gtheme_counts'][$option] );

					else
						unset( $options[$option] );

				$result = gThemeOptions::update_option( 'counts', $options );

				gThemeWordPress::redirectReferer( $result ? 'updated' : 'error' );
			}

			add_action( 'gtheme_settings_sub_counts', [ $this, 'settings_sub_html' ], 10, 2 );
		}
	}
}
