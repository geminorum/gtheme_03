<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeAdmin extends gThemeModuleCore
{

	protected $default_user = 0;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'set_def_user'          => FALSE, // no longer using this!
			'set_def_user_comments' => TRUE,
			'default_publish'       => FALSE,
			'template_title'        => TRUE,
		], $args ) );

		$this->default_user = gThemeOptions::getOption( 'default_user', 0 );

		add_filter( 'default_avatar_select', [ $this, 'default_avatar_select' ] );

		if ( $this->default_user > 0 ) {

			if ( $set_def_user )
				add_filter( 'wp_insert_post_data', [ $this, 'wp_insert_post_data' ], 9, 2 );

			if ( $set_def_user_comments )
				add_filter( 'preprocess_comment', [ $this, 'preprocess_comment' ] );
		}

		if ( $default_publish )
			add_action ( 'admin_menu', [ $this, 'admin_menu' ] );

		if ( $template_title )
			add_filter( 'default_page_template_title', [ $this, 'default_page_template_title' ], 2, 12 );
	}

	public function wp_insert_post_data( $data, $postarr )
	{
		global $user_ID;

		if ( $this->default_user < 1 )
			return $data;

		$post_type_object = get_post_type_object( $postarr['post_type'] );

		if ( current_user_can( $post_type_object->cap->edit_others_posts ) ) {

			if ( 'auto-draft' == $postarr['post_status']
				&& $user_ID == $postarr['post_author'] )
					$data['post_author'] = (int) $this->default_user;
		}

		return $data;
	}

	// force default user on admin comment replies by super admins
	public function preprocess_comment( $commentdata )
	{
		if ( gThemeWordPress::isAJAX() ) {

			if ( is_admin() && current_user_can( 'manage_network' ) ) {

				if ( $this->default_user > 0 ) {

					$user = get_user_by( 'id', (int) $this->default_user );

					$commentdata['user_ID']              = $commentdata['user_id'] = $user->ID;
					$commentdata['comment_author']       = wp_slash( $user->display_name );
					$commentdata['comment_author_email'] = wp_slash( $user->user_email );
					$commentdata['comment_author_url']   = wp_slash( $user->user_url );
				}
			}
		}
		return $commentdata;
	}

	public function admin_menu()
	{
		global $submenu;

		// edit main link for posts
		$submenu['edit.php'][5][2] = 'edit.php?post_status=publish';

		// edit main link for pages
		$submenu['edit.php?post_type=page'][5][2] = 'edit.php?post_type=page&post_status=publish';
	}

	// disable avatar select on admin settings
	public function default_avatar_select( $avatar_list )
	{
		return '<br />'.gThemeHTML::notice( _x( '<strong>The default avatar is overrided by the active theme.</strong>', 'Modules: Admin', 'gtheme' ), 'notice-info inline', FALSE );
	}

	public static function default_page_template_title( $label, $context )
	{
		return gThemeOptions::info( 'default_template_title',
			_x( 'Default Template', 'Modules: Admin', 'gtheme' ) );
	}
}
