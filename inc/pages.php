<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemePages extends gThemeModuleCore
{

	protected $key = 'pages';

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'admin' => FALSE,
		], $args ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', [ $this, 'subs' ], 5 );
			add_action( 'gtheme_settings_load', [ $this, 'load' ] );

			add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
		}
	}

	public function display_post_states( $states, $post )
	{
		if ( 'page' != $post->post_type )
			return $states;

		$option = gThemeOptions::getOption( 'pages', [] );

		if ( empty( $option ) )
			return $states;

		$flipped = array_flip( $option );

		if ( ! array_key_exists( $post->ID, $flipped ) )
			return $states;

		$defaults = gThemeOptions::info( 'pages_list', self::defaults() );

		$title = isset( $defaults[$flipped[$post->ID]] )
			? $defaults[$flipped[$post->ID]]
			: $flipped[$post->ID];

		/* translators: %s: page theme */
		$states['gtheme_page'] = sprintf( _x( 'Theme: %s', 'Modules: Pages: Page State', 'gtheme' ), $title );

		return $states;
	}

	// FIXME: drop this
	public static function defaultPages( $extra = [] )
	{
		return self::defaults( $extra );
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'not-found'  => _x( '404: Not Found', 'Modules: Pages: Default Pages', 'gtheme' ),
			'about'      => _x( 'About', 'Modules: Pages: Default Pages', 'gtheme' ),
			'contact'    => _x( 'Contact', 'Modules: Pages: Default Pages', 'gtheme' ),
			'search'     => _x( 'Search', 'Modules: Pages: Default Pages', 'gtheme' ),
			'archives'   => _x( 'Archives', 'Modules: Pages: Default Pages', 'gtheme' ),
			'latest'     => _x( 'Latest Posts', 'Modules: Pages: Default Pages', 'gtheme' ),
			'social'     => _x( 'Social Profiles', 'Modules: Pages: Default Pages', 'gtheme' ),
			'contribute' => _x( 'Contribute', 'Modules: Pages: Default Pages', 'gtheme' ),
			'advertise'  => _x( 'Advertise here', 'Modules: Pages: Default Pages', 'gtheme' ),
			'copyright'  => _x( 'Copyright Policy', 'Modules: Pages: Default Pages', 'gtheme' ),
			'terms'      => _x( 'Terms of Use', 'Modules: Pages: Default Pages', 'gtheme' ),
			'privacy'    => _x( 'Privacy Policy', 'Modules: Pages: Default Pages', 'gtheme' ),
		], $extra );
	}

	public static function get( $name, $default = 0 )
	{
		$option = gThemeOptions::getOption( 'pages', [] );

		if ( ! empty( $option[$name] ) )
			return $option[$name];

		$defaults = gThemeOptions::info( 'pages_list', self::defaults() );

		if ( empty( $defaults[$name] ) )
			return $default;

		if ( $page = get_page_by_path( $name, OBJECT, 'page' ) )
			return $page->ID;

		return $default;
	}

	public static function link( $name, $atts = [] )
	{
		$args = self::atts( [
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
			'data'    => FALSE,
		], $atts );

		if ( $args['url'] )
			$args['def'] = $args['url'];

		else if ( $page = self::get( $name, 0 ) )
			$args['def'] = get_permalink( $page );

		if ( is_null( $args['title'] ) && $page )
			$args['title'] = get_the_title( $page );

		if ( $args['title'] ) {

			$html = $args['before'].gThemeHTML::tag( 'a', [
				'href'  => $args['def'],
				'class' => $args['class'],
				'title' => $args['attr'],
				'data'  => $args['data'],
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
		return array_merge( $subs, [ 'pages' => _x( 'Pages', 'Modules: Menu Name', 'gtheme' ) ] );
	}

	public function settings_sub_html( $uri, $sub = 'general' )
	{
		$defaults = gThemeOptions::info( 'pages_list', self::defaults() );
		$options  = gThemeOptions::getOption( 'pages', [] );

		echo '<form method="post" action="">';
			echo '<h3>'._x( 'Site Page Settings', 'Modules: Pages: Form Title', 'gtheme' ).'</h3>';
			echo '<table class="form-table">';

				foreach ( $defaults as $page => $title )
					$this->do_settings_field( [
						'title'   => $title,
						'type'    => 'page',
						'field'   => $page,
						'default' => isset( $options[$page] ) ? $options[$page] : 0,
					], TRUE );


			echo '</table>';
			echo '<p class="submit">';

				$this->settings_buttons( 'pages', FALSE );
				echo get_submit_button( _x( 'Create Default Pages', 'Modules: Pages', 'gtheme' ), 'secondary', 'create-default-pages', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';
				echo get_submit_button( _x( 'Create Default Menus', 'Modules: Pages', 'gtheme' ), 'secondary', 'create-default-menus', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';

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

					$defaults = gThemeOptions::info( 'pages_list', self::defaults() );
					$nav_menu = gThemeOptions::info( 'pages_nav_menu', 'primary' );
					$count    = 0;

					if ( $defaults && count( $defaults ) ) {

						if ( $object = wp_get_nav_menu_object( $nav_menu ) )
							$menu = $object->term_id;
						else
							$menu = wp_create_nav_menu( $nav_menu );

						foreach ( $defaults as $slug => $title ) {

							if ( 'not-found' == $slug )
								continue;

							if ( ! $page = get_page_by_path( $slug, OBJECT, 'page' ) )
								continue;

							$id = wp_update_nav_menu_item( $menu, 0, [
								'menu-item-title'     => $title,
								'menu-item-object'    => 'page',
								'menu-item-object-id' => $page->ID,
								'menu-item-type'      => 'post_type',
								'menu-item-status'    => 'publish',
							] );

							if ( ! is_wp_error( $id ) )
								$count++;
						}

						gThemeWordPress::redirectReferer( [
							'message' => 'created',
							'count'   => $count,
						] );
					}

				} else if ( ! empty( $_POST['create-default-pages'] ) ) {

					$defaults = gThemeOptions::info( 'pages_list', self::defaults() );
					$content  = gThemeOptions::info( 'pages_pre_text', _x( '[ This page is being completed ]', 'Options: Page Pre-Text', 'gtheme' ) );
					$user     = gThemeOptions::getOption( 'default_user', 0 );
					$count    = 0;

					foreach ( (array) $defaults as $slug => $title ) {

						// already exits
						if ( get_page_by_path( $slug, OBJECT, 'page' ) )
							continue;

						$id = wp_insert_post( [
							'post_title'   => $title,
							'post_name'    => $slug,
							'post_content' => $content,
							'post_author'  => $user,
							'post_status'  => 'publish',
							'post_type'    => 'page',
						] );

						if ( ! is_wp_error( $id ) )
							$count++;
					}

					gThemeWordPress::redirectReferer( [
						'message' => 'created',
						'count'   => $count,
					] );

				} else {

					$defaults = gThemeOptions::info( 'pages_list', self::defaults() );
					$options  = gThemeOptions::getOption( 'pages', [] );

					foreach ( $defaults as $slug => $title )

						if ( isset( $_POST['gtheme_pages'][$slug] )
							&& trim( $_POST['gtheme_pages'][$slug] ) )
								$options[$slug] = $_POST['gtheme_pages'][$slug];

						else
							unset( $options[$slug] );

					$result = gThemeOptions::update_option( 'pages', $options );

					gThemeWordPress::redirectReferer( [
						'message' => ( $result ? 'updated' : 'error' ),
					] );
				}
			}

			add_action( 'gtheme_settings_sub_pages', [ $this, 'settings_sub_html' ], 10, 2 );
		}
	}
}
