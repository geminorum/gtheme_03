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

	public static function formSimple( $context = 'index', $extra = '' )
	{
		$html = '<form role="search" method="get" class="form search-form -simple search-form-'.$context.' -print-hide" action="'.esc_url( self::getAction() ).'">';

			$html.= '<span class="screen-reader-text sr-only"><label>'._x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'</label></span>';

			$html.= '<input type="search" class="form-control search-field" placeholder="'.esc_attr_x( 'Search &hellip;', 'placeholder', GTHEME_TEXTDOMAIN );
			$html.= '" value="'.self::query().'" name="'.self::getKey().'" title="'.esc_attr_x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'" />';

			$html.= $extra;

		$html.= '</form>';

		echo $html;
	}

	// FIXME: DRAFT: NOT USED
	// @REF: http://bootsnipp.com/snippets/featured/expanding-search-button-in-css
	// @SEE: http://jsbin.com/futeyo/1/edit?html,css,js,output
	public static function formExpanding( $placeholder = NULL, $class = '' )
	{
		if ( is_null( $placeholder ) )
			$placeholder = __( 'Search &hellip;', GTHEME_TEXTDOMAIN );

		echo '<form class="form search-form -expanding '.$class.'" role="search" method="get" action="'.esc_url( self::getAction() ).'">';
		echo '<div class="form-group">';
			echo '<label for="search" class="screen-reader-text sr-only">'._x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'</label>';
			echo '<input id="search" type="text" class="form-control" name="'.self::getKey().'" value="'.self::query().'"';
			if ( $placeholder )
				echo ' placeholder="'.$placeholder.'" ';
		echo '/>';
		echo '<span class="glyphicon glyphicon-search form-control-feedback"></span>';
		echo '</div></form>';
	}

	public static function form( $context = 'index', $extra = '' )
	{
		$html = '<form role="search" method="get" class="form search-form search-form-';
		$html.= $context.' -print-hide" action="'.esc_url( self::getAction() ).'">';

			$html.= '<span class="screen-reader-text sr-only"><label>';
			$html.= _x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'</label></span>';

			$html.= '<div class="input-group">';

				$html.= '<input type="search" class="form-control search-field" placeholder="';
				$html.= esc_attr_x( 'Search &hellip;', 'placeholder', GTHEME_TEXTDOMAIN );
				$html.= '" value="'.self::query().'" name="'.self::getKey().'" title="';
				$html.= esc_attr_x( 'Search for:', 'label', GTHEME_TEXTDOMAIN ).'" />';

				$html.= '<span class="input-group-btn input-group-append">';
					$html.= '<button type="submit" class="btn btn-default search-submit" />';
					$html.= _x( 'Search', 'submit button', GTHEME_TEXTDOMAIN ).'</button>';
				$html.= '</span>';

			$html.= '</div>';

			$html.= $extra;

		$html.= '</form>';

		echo $html;
	}

	public static function form_buddypress()
	{
		$html = '<form action="'.bp_search_form_action().'" method="post" id="search-form"';
			$html.= ' class="form search-form search-form-buddypress">';

			$html.= '<div class="input-group">';

			$html.= '<label for="search-terms" class="accessibly-hidden screen-reader-text sr-only">';
				$html.= _x( 'Search for:', 'buddypress: label', GTHEME_TEXTDOMAIN );
			$html.= '</label>';

			$html.= '<input type="text" id="search-terms" class="form-control" name="search-terms" value="';

			// $html.= self::query( 'search-terms' );
			$html.= get_search_query();

			$html.= '" placeholder="'.esc_attr_x( 'Search &hellip;', 'buddypress: placeholder', GTHEME_TEXTDOMAIN ).'" />';

			$html.= bp_search_form_type_select();

			$html.= '<span class="input-group-btn input-group-append">';
				$html.= '<button type="submit" class="btn btn-default" name="search-submit" id="search-submit">';
				$html.= _x( 'Search', 'buddypress: submit button', GTHEME_TEXTDOMAIN );
			$html.= '</button></span></div>';

			$html.= wp_nonce_field( 'bp_search_form', '_wpnonce', TRUE, FALSE );
		$html.= '</form>';

		echo $html;

		do_action( 'bp_search_login_bar' );
	}
}
