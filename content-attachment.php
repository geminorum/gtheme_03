<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapOpen( 'attachment' );

	if ( gThemeAttachment::media() )
		gThemeAttachment::download();

	gThemeAttachment::caption();
	gThemeAttachment::backlink();

	gThemeAttachment::content();

	gThemeComments::template( '<div class="entry-comments">', '</div>' );

gThemeContent::wrapClose( 'attachment' );
