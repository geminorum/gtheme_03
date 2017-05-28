<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( 'attachment' );

	if ( gThemeAttachment::media() )
		gThemeAttachment::download();

	gThemeAttachment::caption();
	gThemeAttachment::backlink();

gThemeContent::wrapClose();
