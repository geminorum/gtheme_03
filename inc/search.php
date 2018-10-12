<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSearch extends gThemeModuleCore
{

	public static function getAction()
	{
		if ( defined( 'GNETWORK_SEARCH_REDIRECT' ) && GNETWORK_SEARCH_REDIRECT )
			return GNETWORK_SEARCH_URL;

		return home_url( '/' );
	}

	public static function getKey()
	{
		if ( defined( 'GNETWORK_SEARCH_REDIRECT' ) && GNETWORK_SEARCH_REDIRECT )
			return GNETWORK_SEARCH_QUERYID;

		return 's';
	}

	// ANCESTOR: get_search_query()
	public static function query( $query_var = null )
	{
		if ( is_null( $query_var ) )
			$query_var = self::getKey();

		$query = apply_filters( 'get_search_query', get_query_var( $query_var ) );
		return esc_attr( $query );
	}

	public static function form( $context = 'index' )
	{
		$html = '<form role="search" method="get" class="form search-form search-form-'.$context.'" action="'.esc_url( self::getAction() ).'">';

			$html.= '<span class="screen-reader-text sr-only"><label>'._x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'</label></span>';

			$html.= '<div class="input-group">';

				$html.= '<input type="search" class="form-control search-field" placeholder="'.esc_attr_x( 'Search &hellip;', 'placeholder', GTHEME_TEXTDOMAIN );
				$html.= '" value="'.self::query().'" name="'.self::getKey().'" title="'.esc_attr_x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'" />';

				$html.= '<span class="input-group-btn">';
					$html.= '<button type="submit" class="btn btn-default search-submit" />'._x( 'Search', 'submit button', GTHEME_TEXTDOMAIN ).'</button>';
				$html.= '</span>';

			$html.= '</div>';

		$html.= '</form>';

		echo $html;
	}

	public static function form_buddypress()
	{
		$html = '<form action="'.bp_search_form_action().'" method="post" id="search-form"'
			   .' class="form search-form search-form-buddypress"><div class="input-group">'
			   .'<label for="search-terms" class="accessibly-hidden sr-only">'
			   ._x( 'Search for:', 'buddypress: label', GTHEME_TEXTDOMAIN )
			   .'</label><input type="text" id="search-terms" class="form-control" name="search-terms" value="'
			   //.self::query( 'search-terms' )
			   .get_search_query()
			   .'" placeholder="'.esc_attr_x( 'Search &hellip;', 'buddypress: placeholder', GTHEME_TEXTDOMAIN )
			   .'" />'.bp_search_form_type_select()
			   .'<span class="input-group-btn"><button type="submit" class="btn btn-default " name="search-submit" id="search-submit">'
			   ._x( 'Search', 'buddypress: submit button', GTHEME_TEXTDOMAIN )
			   .'</button></span></div>'
			   .wp_nonce_field( 'bp_search_form', '_wpnonce', true, false )
			   .'</form>';

		echo $html;
		do_action( 'bp_search_login_bar' );
	}
}
