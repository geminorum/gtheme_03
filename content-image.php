<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( 'image' );

	gThemeAttachment::image( array( 'tag' => 'big' ) );
	gThemeAttachment::caption();
	gThemeAttachment::backlink();

gThemeContent::wrapClose();
