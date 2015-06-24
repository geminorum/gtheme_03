<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeAdmin extends gThemeModuleCore
{

	var $_default_user = 0;

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'set_def_user'          => TRUE,
			'set_def_user_comments' => TRUE,
			'default_publish'       => FALSE,
		), $args ) );

		$this->_default_user = gThemeOptions::get_option( 'default_user', 0 );

		add_filter( 'default_avatar_select', array( & $this, 'default_avatar_select' ) );

		if ( $this->_default_user > 0 ) {

			if ( $set_def_user )
				add_filter( 'wp_insert_post_data', array( & $this, 'wp_insert_post_data' ), 9, 2 );

			if ( $set_def_user_comments )
				add_filter( 'preprocess_comment', array( & $this, 'preprocess_comment' ) );
		}

		if ( $default_publish )
			add_action ( 'admin_menu', array( & $this, 'admin_menu' ) );
	}

	public function wp_insert_post_data( $data, $postarr )
	{
		global $user_ID;

		if ( $this->_default_user < 1 )
			return $data;

		$post_type_object = get_post_type_object( $postarr['post_type'] );

		if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			if ( 'auto-draft' == $postarr['post_status'] && $user_ID == $postarr['post_author'] )
				$data['post_author'] = (int) $this->_default_user;
		}

		return $data;
	}

	// force default user on admin comment replies by super admins
	public function preprocess_comment( $commentdata )
	{
		if ( defined( 'DOING_AJAX' ) && constant( 'DOING_AJAX' ) ) {
			if ( is_admin() && is_super_admin() ) {
				if ( $this->_default_user > 0 ) {
					$user = get_user_by( 'id', (int) $this->_default_user );

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
		return '<p>'.__( '<strong>The default avatar is overrided by the active theme.</strong>', GTHEME_TEXTDOMAIN ).'</p>';
	}
}
