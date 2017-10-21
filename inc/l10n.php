<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeL10N extends gThemeModuleCore
{

	public static function str( $string )
	{
		if ( 'fa_IR' == GTHEME_LOCALE )
			return self::localize_fa_IR( $string );

		return apply_filters( 'string_format_i18n', $string );
	}

	public static function html( $html )
	{
		return apply_filters( 'html_format_i18n', $html );
	}

	public static function localize_fa_IR( $string )
	{
		if ( is_null( $string ) )
			return NULL;

		$pairs = array(
			'0'                 => chr(0xDB).chr(0xB0),
			'1'                 => chr(0xDB).chr(0xB1),
			'2'                 => chr(0xDB).chr(0xB2),
			'3'                 => chr(0xDB).chr(0xB3),
			'4'                 => chr(0xDB).chr(0xB4),
			'5'                 => chr(0xDB).chr(0xB5),
			'6'                 => chr(0xDB).chr(0xB6),
			'7'                 => chr(0xDB).chr(0xB7),
			'8'                 => chr(0xDB).chr(0xB8),
			'9'                 => chr(0xDB).chr(0xB9),

			chr(0xD9).chr(0xA0) => chr(0xDB).chr(0xB0),
			chr(0xD9).chr(0xA1) => chr(0xDB).chr(0xB1),
			chr(0xD9).chr(0xA2) => chr(0xDB).chr(0xB2),
			chr(0xD9).chr(0xA3) => chr(0xDB).chr(0xB3),
			chr(0xD9).chr(0xA4) => chr(0xDB).chr(0xB4),
			chr(0xD9).chr(0xA5) => chr(0xDB).chr(0xB5),
			chr(0xD9).chr(0xA6) => chr(0xDB).chr(0xB6),
			chr(0xD9).chr(0xA7) => chr(0xDB).chr(0xB7),
			chr(0xD9).chr(0xA8) => chr(0xDB).chr(0xB8),
			chr(0xD9).chr(0xA9) => chr(0xDB).chr(0xB9),

			chr(0xD9).chr(0x83) => chr(0xDA).chr(0xA9),
			chr(0xD9).chr(0x89) => chr(0xDB).chr(0x8C),
			chr(0xD9).chr(0x8A) => chr(0xDB).chr(0x8C),
			chr(0xDB).chr(0x80) => chr(0xD9).chr(0x87).chr(0xD9).chr(0x94),
		);

		return strtr( $string, $pairs );
	}
}
