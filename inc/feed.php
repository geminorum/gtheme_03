<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeFeed extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'prepare'    => TRUE,
			'restricted' => FALSE,
			'enclosures' => TRUE, // adding post image as rss enclosure
			'paged'      => FALSE,
		), $args ) );

		if ( $prepare )
			add_filter( 'the_content_feed', array( &$this, 'the_content_feed' ), 12, 2 );

		if ( $restricted )
			add_filter( 'the_content_feed', array( &$this, 'the_content_feed_restricted' ), 11, 2 );

		if ( $enclosures )
			add_action( 'rss2_item', array( &$this, 'rss2_item' ) );

		if ( $paged )
			add_filter( 'posts_where', array( &$this, 'posts_where' ), 10, 2 );
	}

	public function the_content_feed( $content, $feed_type )
	{
		global $id;
		$header = apply_filters( 'gtheme_feed_content_header_before', '', $content, $id, $feed_type );
		$footer = apply_filters( 'gtheme_feed_content_footer_before', '', $content, $id, $feed_type );

		$meta_label = gmeta_label( '<div class="label">', '</div>', FALSE, array( 'echo' => FALSE ) );
		if ( $meta_label )
			$header .= $meta_label;

		$meta_over_title = gmeta( 'over-title', '<h4>', '</h4>', FALSE, array( 'echo' => FALSE ) );
		if ( $meta_over_title )
			$header .= $meta_over_title;

		$meta_title = get_the_title();
		if ( ! empty( $meta_title ) )
			$header .= '<h2>'.$meta_title.'</h2>';

		$meta_sub_title = gmeta( 'sub-title', '<h4>', '</h4>', FALSE, array( 'echo' => FALSE ) );
		if ( $meta_sub_title )
			$header .= $meta_sub_title;

		$meta_author = gmeta_author( '<h4>', '</h4>', FALSE, array( 'echo' => FALSE ) );
		if ( $meta_author )
			$header .= $meta_author;

		$header = apply_filters( 'gtheme_feed_content_header_after', $header, $content, $id, $feed_type );
		$footer = apply_filters( 'gtheme_feed_content_footer_after', $footer, $content, $id, $feed_type );

		$meta_lead = gmeta_lead( '<div class="lead">', '</div>', 'wpautop', array( 'echo' => FALSE ) );
		if ( $meta_lead )
			$header .= $meta_lead;

		$replaces = gtheme_get_info( 'feed_str_replace', array() );
		if ( count( $replaces ) ) {
			if ( ! empty( $header ) )
				$header = str_replace( array_keys( $replaces ), $replaces, $header );

			if ( ! empty( $content ) )
				$content = str_replace( array_keys( $replaces ), $replaces, $content );

			if ( ! empty( $footer ) )
				$footer = str_replace( array_keys( $replaces ), $replaces, $footer );
		}

			/*
			$header .= gcharghad_nounder_thumb( $the_cat, '<p style="float:right;margin-left:15px;margin-bottom:10px;">', '</p>', false );


			if ( $external ) {
				$header .= '<p style="margin-left:auto;margin-right:auto;text-align:center;">'.do_shortcode( '[ms_place name="raw" max_width="650" link="'.$external.'" title="'.the_title_attribute( 'echo=0' ).'"]' ).'</p>';
				$footer .= '<hr /><p style="margin-left:auto;margin-right:auto;text-align:center;direction:rtl;font:bold 1.3em arial;"><a style="text-decoration:none;color:#ccc;" href="'.$external.'">������ ���� ������ �� �� ������ �����</a></p>';
			} else {
				// TODO : adjust this. what if there's no raw media?
				//$header .= '<p style="margin-left:auto;margin-right:auto;text-align:center;">'.do_shortcode( '[ms_place name="raw" max_width="650" link="'.get_permalink().'" title="'.the_title_attribute( 'echo=0' ).'"]' ).'</p>';

				// TODO : add related posts by p2p & tags
				$footer .= '<hr /><p style="direction:rtl;font:bold 1.3em arial;">'
						.'<a style="text-decoration:none;color:#ccc;" href="'.get_permalink( $id ).'/#respond">��ϐ�� ���</a> / '
						.'<a style="text-decoration:none;color:#ccc;" href="'.get_option( 'home' ).'/?p='.$id.'">����� �����</a></p>';
			}
			*/

		if ( gtheme_is_rtl() )
			return '<div style="direction:rtl !important;text-align:right !important;">'.$header.$content.$footer.'</div>';

		return $header.$content.$footer;
	}

	public function the_content_feed_restricted( $content, $feed_type )
	{
		return gThemeContent::teaser( TRUE, FALSE );
	}

	public function rss2_item()
	{
		$size = gtheme_get_info( 'enclosure_image_size', 'single' );
		if ( ! $size )
			return;

		$id = gThemeImage::id( $size );
		if ( ! $id )
			return;

		$image = wp_get_attachment_image_src( $id, $size );
		if ( ! $image )
			return;

		$post = get_post( $id );
		echo "\t".'<enclosure url="'.trim( htmlspecialchars( $image[0] ) ).'" length="'.trim( gThemeUtilities::get_uri_length( $image[0] ) ).'" type="'.$post->post_mime_type.'" />'."\n";
	}

	// https://gist.github.com/danielbachhuber/6557916
	// paginated rss feeds / 'page' sets the page number
	public function posts_where( $where, $query )
	{
		if ( ! is_feed() )
			return;

		$page = ( empty( $_GET['page'] ) ? 1 : (int) $_GET['page'] );

		$query->set( 'nopaging', FALSE );
		$query->set( 'paged', $page );

		return $where;
	}
}

// https://gist.github.com/danielbachhuber/2418510
// http://digwp.com/2012/10/customizing-wordpress-feeds/

// http://wp.tutsplus.com/tutorials/creative-coding/tips-to-customize-and-optimize-your-blogs-feed/
// http://wp.tutsplus.com/tutorials/creative-coding/extending-the-default-wordpress-rss-feed/

// http://www.wpbeginner.com/wp-tutorials/how-to-add-content-and-completely-manipulate-your-wordpress-rss-feeds/
// http://www.wpbeginner.com/wp-tutorials/how-to-create-custom-rss-feeds-in-wordpress/
// http://www.wpbeginner.com/plugins/control-your-rss-feeds-footer-in-wordpress/

// http://feedly.uservoice.com/knowledgebase/topics/33323-publishers-optimize-your-feeds-for-feedly
