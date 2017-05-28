<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( 'video' );

	gThemeAttachment::media();
	gThemeAttachment::download();
	gThemeAttachment::caption();
	gThemeAttachment::backlink();

gThemeContent::wrapClose();
