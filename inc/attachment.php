<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeAttachment extends gThemeModuleCore
{

	// used in caption shortcode
	public static function normalizeCaption( $caption, $before = '', $after = '', $default = '' )
	{
		if ( ! $caption )
			return $default;

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
			'empty'    => _x( '&larr; Back', 'Module: Attachment: Backlink Empty Title', GTHEME_TEXTDOMAIN ),
			'echo'     => TRUE,
		), $atts );

		$post = get_post( $args['id'] );

		if ( ! $post )
			return FALSE;

		if ( empty( $post->post_parent ) )
			return FALSE;

		if ( $title = get_the_title( $post->post_parent ) )
			$html = sprintf( $args['template'], $title );

		else
			$html = $args['empty'];

		$html = gThemeHTML::tag( 'a', array(
			'href'  => get_permalink( $post->post_parent ),
			'class' => '-backlink',
		), $html );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	public static function download( $atts = array() )
	{
		$args = self::atts( array(
			'before' => '<div class="entry-download">',
			'after'  => '</div>',
			'id'     => NULL,
			'title'  => NULL,
			'class'  => NULL,
			'link'   => _x( 'Download Attachment', 'Module: Attachment: Link', GTHEME_TEXTDOMAIN ),
			'echo'   => TRUE,
		), $atts );

		$post = get_post( $args['id'] );

		if ( ! $post )
			return FALSE;

		$html = gThemeHTML::tag( 'a', array(
			'href'     => wp_get_attachment_url( $post->ID ),
			'download' => basename( get_attached_file( $post->ID ) ),
			'title'    => is_null( $args['title'] ) ? get_the_title( $post ) : $args['title'],
			'class'    => '-download '.( is_null( $args['class'] ) ? 'btn btn-default' : $args['class'] ),
			'rel'      => 'attachment',
		), $args['link'] );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}
}
