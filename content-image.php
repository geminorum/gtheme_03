<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( 'image' );

	gThemeAttachment::media();
	gThemeAttachment::download();
	gThemeAttachment::caption();
	gThemeAttachment::backlink();

gThemeContent::wrapClose();
