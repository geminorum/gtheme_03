<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeAttachment extends gThemeModuleCore
{

	// FIXME: DRAFT
	// SEE: https://core.trac.wordpress.org/changeset/37915
	public static function caption( $atts = array() )
	{
		$args = self::atts( array(
			'before' => '<div class="entry-summary entry-caption">',
			'after'  => '<div>',
			'id'     => get_the_ID(),
			'echo'   => TRUE,
			'length' => NULL, // trim chars
		), $atts );

		$post = get_post( $args['id'] );

		if ( ! $post )
			return;

		if ( empty( $post->post_excerpt ) )
			return;

		// $html = $args['before'].apply_filters( 'the_excerpt', $post->post_excerpt ). $args['after'];
		$html = $args['before'].$post->post_excerpt.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
	}

	// FIXME: DRAFT
	public static function link( $attachment_id, $atts = array() )
	{
		echo '<div class="entry-link"><a href="';
			echo wp_get_attachment_url( $attachment_id );
			echo '" title="'.wp_specialchars( get_the_title( $attachment_id ), 1 ).'" rel="attachment">';
			_e( 'Download Attachment', GTHEME_TEXTDOMAIN );
		echo '</a></div>';
	}

	// FIXME: DRAFT / UNFINISHED
	// OLD: gtheme_attachment()
	public static function image( $atts = array() )
	{
		return wp_get_attachment_image( $id, $args['size'], FALSE, $attr );
	}
}
