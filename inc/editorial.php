<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEditorial extends gThemeModuleCore
{

	// FIXME: add theme classes / before / after
	// old: gmeta_lead()
	public static function label( $atts = array() )
	{
		if ( class_exists( 'gEditorialMetaTemplates' ) )
			return gEditorialMetaTemplates::metaLabel( $atts );
	}
}
