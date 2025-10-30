<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSearch extends gThemeModuleCore
{

	public static function getAction()
	{
		if ( defined( 'GNETWORK_SEARCH_REDIRECT' ) && GNETWORK_SEARCH_REDIRECT )
			return GNETWORK_SEARCH_URL;

		return GTHEME_HOME;
	}

	public static function getKey()
	{
		if ( defined( 'GNETWORK_SEARCH_REDIRECT' ) && GNETWORK_SEARCH_REDIRECT )
			return GNETWORK_SEARCH_QUERYID;

		return 's';
	}

	public static function getPlaceholderText()
	{
		return gThemeOptions::info( 'search_placeholder_text', _x( 'Search &hellip;', 'Modules: Search: Placeholder', 'gtheme' ) );
	}

	public static function getSubmitText()
	{
		return gThemeOptions::info( 'search_submit_text', _x( 'Search', 'Modules: Search: Submit Text', 'gtheme' ) );
	}

	public static function getLabelText()
	{
		return gThemeOptions::info( 'search_label_text', _x( 'Search for:', 'Modules: Search: Label', 'gtheme' ) );
	}

	// ANCESTOR: get_search_query()
	public static function query( $query_var = null )
	{
		if ( is_null( $query_var ) )
			$query_var = self::getKey();

		return apply_filters( 'get_search_query', get_query_var( $query_var ) );
	}

	public static function getLink( $query = FALSE, $url = FALSE, $query_id = 's' )
	{
		if ( $url )
			return $query ? add_query_arg( $query_id, urlencode( $query ), $url ) : $url;

		if ( defined( 'GNETWORK_SEARCH_REDIRECT' ) && GNETWORK_SEARCH_REDIRECT )
			return $query ? add_query_arg( GNETWORK_SEARCH_QUERYID, urlencode( $query ), GNETWORK_SEARCH_URL ) : GNETWORK_SEARCH_URL;

		return get_search_link( $query );
	}

	// EXAMPLE: for `search_form_actions`: `[ 'news' => [ 'action' => 'https://news.example.com/', 'title' => _x( 'News', 'Search Form Action', 'gtheme' ) ] ]`
	public static function getActionSelector( $class = '', $name = 'search-action' )
	{
		if ( ! $actions = gThemeOptions::info( 'search_form_actions', [] ) )
			return '';

		$html = '';

		foreach ( $actions as $id => $args ) {
			$html.= '<div class="form-check form-check-inline">';
				$html.= '<input type="radio" id="action-'.$id.'" name="'.$name.'" class="form-check-input" data-action="'.$args['action'].'" />';
				$html.= '<label class="form-check-label" for="action-'.$id.'">'.$args['title'].'</label>';
			$html.= '</div>';
		}

		return gThemeHTML::wrap( $html, [ '-actions', $class ] );
	}

	public static function enqueueActionSelector( $name = 'search-action' )
	{
		// wp_enqueue_script( 'gtheme-search-actions', GTHEME_CHILD_URL.'/js/search.actions.js', [ 'jquery' ], GTHEME_CHILD_VERSION, TRUE ); return; // <---- NOTE THIS

		$script = 'jQuery(function(t){t("input[type=radio][name='.$name.']").on("change",function(){var a=t(this).data("action"),n=t(this).closest("form");n.attr("action",a)})});';

		// @REF: https://core.trac.wordpress.org/ticket/44551
		// @REF: https://wordpress.stackexchange.com/a/311279
		wp_register_script( 'gtheme-search-actions', '', [ 'jquery' ], '', TRUE );
		wp_enqueue_script( 'gtheme-search-actions' ); // must register then enqueue
		wp_add_inline_script( 'gtheme-search-actions', $script );
	}

	public static function formActions( $context = 'index', $class = '', $layout = NULL )
	{
		if ( is_null( $layout ) )
			$layout = gThemeOptions::info( 'search_form_actions_layout', 'default' );

		if ( FALSE === $layout )
			return;

		$actions = self::getActionSelector( $class );

		switch ( $layout ) {

			case 'simple':
				self::formSimple( $context, $actions );

			break;
			default:
			case 'default':
				self::form( $context, $actions );
		}

		if ( $actions )
			self::enqueueActionSelector();
	}

	public static function formSimple( $context = 'index', $extra = '', $field_class = '', $form_class = '' )
	{
		$label = self::getLabelText();
		$query = ( '404' == $context || 'notfound' == $context ) ? '' : esc_attr( self::query() );

		$html = '<form role="search" method="get" class="form search-form -simple search-form-'.$context.' '.$form_class.' -print-hide" action="'.esc_url( self::getAction() ).'">';

			$html.= '<span class="screen-reader-text sr-only visually-hidden"><label>'.$label.'</label></span>';

			$html.= '<input type="search" class="form-control search-field '.$field_class.'" placeholder="'.esc_attr( self::getPlaceholderText() );
			$html.= '" value="'.$query.'" name="'.self::getKey().'" title="'.esc_attr( $label ).'" />';

			// TODO: fire action here
			$html.= $extra;

		$html.= '</form>';

		echo $html;
	}

	public static function formSecondary( $context = 'index', $extra = '', $field_class = '', $form_class = '' )
	{
		$html = $extra;

		// TODO: search exact checkbox
		// TODO: select to title-only/content-only/isbn/byline
		// - filter the options
		$html = '';

		$html = apply_filters( 'gtheme_search_form_secondary_extra', $html, $context, $extra );

		self::formSimple( $context, $html, $field_class, $form_class );
	}

	// FIXME: DRAFT: NOT USED
	// @REF: http://bootsnipp.com/snippets/featured/expanding-search-button-in-css
	// @SEE: http://jsbin.com/futeyo/1/edit?html,css,js,output
	public static function formExpanding( $placeholder = NULL, $class = '' )
	{
		if ( is_null( $placeholder ) )
			$placeholder = self::getPlaceholderText();

		echo '<form class="form search-form -expanding '.$class.'" role="search" method="get" action="'.esc_url( self::getAction() ).'">';
		echo '<div class="form-group">';
			echo '<label for="search" class="screen-reader-text sr-only visually-hidden">'.self::getLabelText().'</label>';
			echo '<input id="search" type="text" class="form-control" name="'.self::getKey().'" value="'.esc_attr( self::query() ).'"';
			if ( $placeholder )
				echo ' placeholder="'.esc_attr( $placeholder ).'" ';
		echo '/>';
		echo '<span class="glyphicon glyphicon-search form-control-feedback"></span>';
		echo '</div></form>';
	}

	public static function form( $context = 'index', $extra = '' )
	{
		$label = self::getLabelText();
		$query = ( '404' == $context || 'notfound' == $context ) ? '' : esc_attr( self::query() );

		$html = '<form role="search" method="get" class="form search-form search-form-';
		$html.= $context.' -print-hide" action="'.esc_url( self::getAction() ).'">';

			$html.= '<span class="screen-reader-text sr-only visually-hidden"><label>'.$label.'</label></span>';

			$html.= '<div class="input-group">';

				$html.= '<input type="search" class="form-control search-field" placeholder="'.esc_attr( self::getPlaceholderText() );
				$html.= '" value="'.$query.'" name="'.self::getKey().'" title="'.esc_attr( $label ).'" />';

				// $html.= '<span class="input-group-btn input-group-append">';
					$html.= '<button type="submit" class="btn btn-outline-secondary search-submit">'.self::getSubmitText().'</button>';
				// $html.= '</span>';

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

			$html.= '<label for="search-terms" class="accessibly-hidden screen-reader-text sr-only visually-hidden">'.self::getLabelText().'</label>';

			$html.= '<input type="text" id="search-terms" class="form-control" name="search-terms" value="';

			// $html.= esc_attr( self::query( 'search-terms' ) );
			$html.= esc_attr( get_search_query() );

			$html.= '" placeholder="'.esc_attr( self::getPlaceholderText() ).'" />';

			$html.= bp_search_form_type_select();

			$html.= '<span class="input-group-btn input-group-append">';
				$html.= '<button type="submit" class="btn btn-default btn-outline-secondary" name="search-submit" id="search-submit">'.self::getSubmitText().'</button>';
			$html.= '</span></div>';

			$html.= wp_nonce_field( 'bp_search_form', '_wpnonce', TRUE, FALSE );
		$html.= '</form>';

		echo $html;

		do_action( 'bp_search_login_bar' );
	}
}
