<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeAttachment extends gThemeModuleCore
{

	// TODO: filter `wp_get_attachment_link` to append: `attachment_download_prefix` @REF: https://core.trac.wordpress.org/ticket/41574

	// used in caption short-code
	public static function normalizeCaption( $caption, $before = '', $after = '', $default = '' )
	{
		if ( ! $caption )
			return $default;

		if ( $caption = trim( str_ireplace( '&nbsp;', ' ', $caption ) ) ) {

			$caption = gThemeL10N::str( $caption );
			$caption = apply_filters( 'gnetwork_typography', $caption );

			if ( trim( $caption ) )
				return $before.gThemeText::wordWrap( $caption, 2 ).$after;
		}

		return $default;
	}

	public static function caption( $atts = [] )
	{
		$args = self::atts( [
			'before' => '<div class="entry-summary entry-caption">',
			'after'  => '</div>',
			'id'     => NULL,
			'prep'   => TRUE,
			'echo'   => TRUE,
		], $atts );

		if ( ! $html = wp_get_attachment_caption( $args['id'] ) )
			return FALSE;

		if ( $args['prep'] )
			$html = gThemeUtilities::prepDescription( $html );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	// FIXME: DEPRECATED: USE: `gThemeAttachment::media()`
	public static function image( $atts = [] )
	{
		self::_dep( 'gThemeAttachment::media()' );
		return self::media( $atts );
	}

	// @REF: `prepend_attachment()`
	public static function media( $atts = [] )
	{
		$args = self::atts( [
			'before' => '<div class="entry-attachment entry-attachment-media">',
			'after'  => '</div>',
			'id'     => NULL,
			'tag'    => gThemeOptions::info( 'attachment_file_size', 'big' ),
			'cover'  => TRUE, // audio cover
			'echo'   => TRUE,
			'extra'  => [],
		], $atts );

		if ( ! $post = get_post( $args['id'] ) )
			return FALSE;

		$html = '';

		if ( wp_attachment_is( 'image', $post ) ) {

			$html = gThemeImage::getImageHTML( $post->ID, $args['tag'] );

		} else if ( wp_attachment_is( 'video', $post ) ) {

			$meta = wp_get_attachment_metadata( $post->ID );

			$shortcode = [
				'src'      => wp_get_attachment_url( $post->ID ),
				'download' => TRUE,
			];

			if ( ! empty( $meta['width'] )
				&& ! empty( $meta['height'] ) ) {
				$shortcode['width'] = (int) $meta['width'];
				$shortcode['height'] = (int) $meta['height'];
			}

			if ( $thumbnail_id = get_post_thumbnail_id( $post ) )
				$shortcode['poster'] = wp_get_attachment_url( $thumbnail_id );

			// NOTE: avoid using `wp_video_shortcode()` directly!
			$html = do_shortcode(
				gThemeUtilities::shortcodeBuild( 'video', array_merge( $shortcode, $args['extra'] ) )
			);

		} else if ( wp_attachment_is( 'audio', $post ) ) {

			$html = gThemeImage::getImage( [
				'link'    => 'attachment',
				'post_id' => $post->ID,
				'tag'     => $args['tag'],
			] );

			if ( $html )
				$html = '<div class="attachment-cover">'.$html.'</div>';

			// NOTE: avoid using `wp_audio_shortcode()` directly!
			$html.= do_shortcode(
				gThemeUtilities::shortcodeBuild( 'audio', array_merge( [
					'src'      => wp_get_attachment_url( $post->ID ),
					'download' => TRUE,
				], $args['extra'] )	)
			);

		} else if ( 'text/csv' == $post->post_mime_type ) {

			$html = do_shortcode(
				gThemeUtilities::shortcodeBuild( 'csv', [
					'id' => $post->ID,
				] )
			);

		} else if ( 'application/epub+zip' == $post->post_mime_type ) {

			$html = do_shortcode(
				gThemeUtilities::shortcodeBuild( 'epub', [
					'id' => $post->ID,
				] )
			);

		} else if ( 'application/pdf' == $post->post_mime_type ) {

			$html = do_shortcode(
				gThemeUtilities::shortcodeBuild( 'pdf', [
					'id'  => $post->ID,
					'url' => wp_get_attachment_url( $post->ID ), // in case of customized short-codes
				] )
			);

		} else {

			// FALLBACK
			self::download( [
				'id'     => $args['id'],
				'before' => '<div class="entry-download -fallback">',
			] );

			return FALSE;
		}

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];

		return TRUE;
	}

	public static function backlink( $atts = [] )
	{
		$args = self::atts( [
			'before'   => '<div class="entry-backlink">',
			'after'    => '</div>',
			'id'       => NULL,
			/* translators: %s: post title */
			'template' => _x( '&larr; Back to &ldquo;%s&rdquo;', 'Module: Attachment: Backlink Template', 'gtheme' ),
			'empty'    => _x( '&larr; Back', 'Module: Attachment: Backlink Empty Title', 'gtheme' ),
			'echo'     => TRUE,
		], $atts );

		if ( ! $post = get_post( $args['id'] ) )
			return FALSE;

		if ( empty( $post->post_parent ) )
			return FALSE;

		if ( $title = get_the_title( $post->post_parent ) )
			$html = sprintf( $args['template'], $title );

		else
			$html = $args['empty'];

		$html = gThemeHTML::tag( 'a', [
			'href'  => get_permalink( $post->post_parent ),
			'class' => '-backlink',
		], $html );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	// @REF: https://caniuse.com/download
	public static function download( $atts = [] )
	{
		$args = self::atts( [
			'before' => '<div class="entry-download">',
			'after'  => '</div>',
			'id'     => NULL,
			'title'  => NULL,
			'class'  => NULL,
			'link'   => _x( 'Download Attachment', 'Module: Attachment: Link', 'gtheme' ),
			'echo'   => TRUE,
		], $atts );

		if ( ! $post = get_post( $args['id'] ) )
			return FALSE;

		$html = gThemeHTML::tag( 'a', [
			'href'     => wp_get_attachment_url( $post->ID ),
			'download' => gThemeOptions::info( 'attachment_download_prefix', '' ).basename( get_attached_file( $post->ID ) ),
			'title'    => $args['title'] ?? get_the_title( $post ),
			'class'    => gThemeHTML::attrClass( '-download', $args['class'] ?? 'btn btn-default btn-outline-secondary' ),
			'rel'      => 'attachment',
		], $args['link'] );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	public static function content()
	{
		if ( ! $post = get_post() )
			return;

		if ( empty( $post->post_content ) )
			return;

		gThemeContent::content();
	}
}
