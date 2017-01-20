<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeAttachment extends gThemeModuleCore
{

	// used in caption shortcode
	public static function normalizeCaption( $caption, $before = '', $after = '', $default = '' )
	{
		if ( $caption = trim( str_ireplace( '&nbsp;', ' ', $caption ) ) ) {

			$caption = gThemeL10N::str( $caption );
			$caption = apply_filters( 'gnetwork_typography', $caption );

			if ( trim( $caption ) )
				return $before.gThemeUtilities::wordWrap( $caption, 2 ).$after;
		}

		return $default;
	}

	// FIXME: DRAFT
	// SEE: https://core.trac.wordpress.org/changeset/37915
	public static function caption( $atts = array() )
	{
		$args = self::atts( array(
			'before' => '<div class="entry-summary entry-caption">',
			'after'  => '</div>',
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

	// FIXME: DRAFT / WORKING
	// OLD: gtheme_attachment()
	// SEE: prepend_attachment()
	public static function image( $atts = array() )
	{
		$args = self::atts( array(
			'before' => '<div class="entry-attachment entry-attachment-image">',
			'after'  => '</div>',
			'id'     => get_the_ID(),
			'tag'    => 'big',
			'echo'   => TRUE,
		), $atts );

		if ( ! wp_attachment_is_image( $args['id'] ) )
			return FALSE;

		$html = wp_get_attachment_image( $args['id'], $args['tag'] );

		if ( ! $args['echo'] )
			return $html ? $args['before'].$html.$args['after'] : FALSE;

		if ( $html )
			echo $args['before'].$html.$args['after'];
	}

	public static function backlink( $atts = array() )
	{
		$args = self::atts( array(
			'before'   => '<div class="entry-backlink">',
			'after'    => '</div>',
			'id'       => get_the_ID(),
			'template' => _x( '&larr; Back to &ldquo;%s&rdquo;', 'Module: Attachment: Backlink Template', GTHEME_TEXTDOMAIN ),
			'echo'     => TRUE,
		), $atts );

		$post = get_post( $args['id'] );

		if ( ! $post )
			return FALSE;

		if ( empty( $post->post_parent ) )
			return FALSE;

		$html = '<a href="'.get_permalink( $post->post_parent ).'" class="-backlink">'
			.sprintf( $args['template'], get_the_title( $post->post_parent ) ).'</a>';

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}
}
