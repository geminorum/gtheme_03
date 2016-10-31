<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeFeed extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'prepare'    => TRUE,
			'restricted' => FALSE,
			'enclosures' => TRUE, // adding post image as rss enclosure
			'paged'      => FALSE,
		), $args ) );

		if ( $prepare )
			add_filter( 'the_content_feed', array( $this, 'the_content_feed' ), 12, 2 );

		if ( $restricted )
			add_filter( 'the_content_feed', array( $this, 'the_content_feed_restricted' ), 1, 2 );

		if ( $enclosures )
			add_action( 'rss2_item', array( $this, 'rss2_item' ) );

		if ( $paged )
			add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
	}

	public function the_content_feed( $content, $feed_type )
	{
		$header = gThemeOptions::info( 'feed_content_header_before', '' );
		$footer = gThemeOptions::info( 'feed_content_footer_before', '' );

		ob_start();
			gThemeEditorial::label( array( 'before' => '<div class="label">', 'after' => '</div>' ) );
			gThemeEditorial::meta( 'over-title', array( 'before' => '<h4>', 'after' => '</h4>' ) );
			if ( $title = get_the_title() ) echo '<h2>'.$title.'</h2>';
			gThemeEditorial::meta( 'sub-title', array( 'before' => '<h4>', 'after' => '</h4>' ) );
			gThemeEditorial::author( array( 'before' => '<h4>', 'after' => '</h4>' ) );
		$header .= ob_get_clean();

		$header .= gThemeOptions::info( 'feed_content_header_after', '' );
		$footer .= gThemeOptions::info( 'feed_content_footer_after', '' );

		$lead = gThemeEditorial::lead( array(
			'before' => '<div class="lead">',
			'after'  => '</div>',
			'echo'   => FALSE,
		) );

		if ( $lead )
			$header .= $lead;

		$replaces = gThemeOptions::info( 'feed_str_replace', array() );

		if ( count( $replaces ) ) {
			if ( ! empty( $header ) )
				$header = str_replace( array_keys( $replaces ), $replaces, $header );

			if ( ! empty( $content ) )
				$content = str_replace( array_keys( $replaces ), $replaces, $content );

			if ( ! empty( $footer ) )
				$footer = str_replace( array_keys( $replaces ), $replaces, $footer );
		}

		// TODO: add shortlink
		// TODO: add comment link

		if ( gThemeUtilities::isRTL() )
			return '<div style="direction:rtl !important;text-align:right !important;">'.$header.$content.$footer.'</div>';

		return $header.$content.$footer;
	}

	public function the_content_feed_restricted( $content, $feed_type )
	{
		global $more;
		$more = 0;

		return str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', get_the_content( FALSE ) ) );
	}

	public function rss2_item()
	{
		if ( $size = gThemeOptions::info( 'enclosure_image_size', 'single' ) ) {

			if ( $id = gThemeImage::id( $size ) ) {

				if ( $image = wp_get_attachment_image_src( $id, $size ) ) {

					$post = get_post( $id );

					echo "\t".'<enclosure url="'.trim( htmlspecialchars( $image[0] ) )
						.'" length="'.trim( gThemeUtilities::getURILength( $image[0] ) )
						.'" type="'.$post->post_mime_type.'" />'."\n";
				}
			}
		}
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
