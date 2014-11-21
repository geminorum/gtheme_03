<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeShortCodes extends gThemeModuleCore 
{

	function setup_actions( $args = array() )
	{
		add_action( 'init', array( & $this, 'init' ), 12 );
	}
	
	public function init()
	{
		$shortcodes = array(
			'theme-image' => 'shortcode_theme_image',
			'panel-group' => 'shortcode_panel_group',
			'panel' => 'shortcode_panel',
		);
	
		foreach ( $shortcodes as $shortcode => $method ) {
			remove_shortcode( $shortcode ); 
			add_shortcode( $shortcode, array( & $this, $method) ); 
		}
	}
	
	/** SYNTAX:
	
	[panel-group id="" class="" role=""]
		[panel parent="" id="" title="" title_tag="" context="" expanded=""]...[/panel]
		[panel parent="" id="" title="" title_tag="" context="" expanded=""]...[/panel]
		[panel parent="" id="" title="" title_tag="" context="" expanded=""]...[/panel]
	[/panel-group]
	
	**/
	
	var $_panel_group_count = 0;
	var $_panel_count = 0;
	var $_panel_parent = false;
	
	function shortcode_panel_group( $atts, $content = null, $tag = '' ) 
	{
		if ( is_null( $content ) )
			return $content;
	
		$args = shortcode_atts( array(
			'class' => '',
			'id' => 'panel-group-'.$this->_panel_group_count,
			'role' => 'tablist',
		), $atts, $tag );
		
		$this->_panel_parent = $args['id'];
		
		$html  = '<div class="panel-group '.$args['class'].'" id="'.$args['id'].'" role="'.$args['role'].'" aria-multiselectable="true">';
		$html .= do_shortcode( $content );
		$html .= '</div>';
	
		$this->_panel_parent = false;
		$this->_panel_group_count++;
		
		return $html;
	}
	
	function shortcode_panel( $atts, $content = null, $tag = '' ) 
	{
		if ( is_null( $content ) )
			return $content;
	
		$args = shortcode_atts( array(
			'parent' => ( $this->_panel_parent ? $this->_panel_parent : 'panel-group-'.$this->_panel_group_count ),
			'id' => 'panel-'.$this->_panel_count,
			'title' => _x( 'Untitled', 'Panel Shortcode', GTHEME_TEXTDOMAIN ),
			'title_tag' => 'h4',
			'context' => 'default',
			'expanded' => false,
		), $atts, $tag );

		$html  = '<div class="panel panel-'.$args['context'].'">';
		$html .= '<div class="panel-heading" role="tab" id="'.$args['id'].'-wrap">';
		$html .= '<'.$args['title_tag'].' class="panel-title"><a data-toggle="collapse" data-parent="#'.$args['parent'].'" href="#'.$args['id'].'" aria-expanded="'.( $args['expanded'] ? 'true' : 'false').'" aria-controls="'.$args['id'].'">';
		$html .= $args['title'].'</a></'.$args['title_tag'].'></div>';
		$html .= '<div id="'.$args['id'].'" class="panel-collapse collapse'.( $args['expanded'] ? ' in' : '' ).'" role="tabpanel" aria-labelledby="'.$args['id'].'-wrap">';
		$html .= '<div class="panel-body">'.$content.'</div></div></div>';
	
		$this->_panel_count++;
		return $html;
	}
	
	function shortcode_theme_image( $atts, $content = null, $tag = '' ) 
	{
		$args = shortcode_atts( array(
			'src' => false,
			'alt' => false,
			'title' => false,
			'width' => false,
			'height' => false,
			'url' => false,
			'dir' => 'images',
		), $atts, $tag );
	
		if ( ! $args['src'] )
			return $content;
	
		$html = gThemeUtilities::html( 'img', array( 
			'src' => GTHEME_CHILD_URL.'/'.$args['dir'].'/'.$args['src'],
			'alt' => $args['alt'],
			'title' => ( $args['url'] ? false : $args['title'] ),
			'width' => $args['width'],
			'height' => $args['height'],
		) );
		
		if ( $args['url'] )	
			return gThemeUtilities::html( 'a', array( 
				'href' => $args['url'],
				'title' => $args['title'],
			), $html );
		
		return $html;
	}
}