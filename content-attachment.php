<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapOpen( 'attachment' );

	if ( gThemeAttachment::media() )
		gThemeAttachment::download();

	gThemeAttachment::caption();
	gThemeAttachment::backlink();

	gThemeAttachment::content();

gThemeContent::wrapClose( 'attachment' );
