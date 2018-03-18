<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeShortCodes extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'defaults'         => TRUE,
			'caption_override' => TRUE,
			'gallery_override' => TRUE,
		), $args ) );

		if ( $defaults )
			add_action( 'init', array( $this, 'init' ), 14 );

		if ( $caption_override )
			add_filter( 'img_caption_shortcode', array( $this, 'img_caption_shortcode' ), 10, 3 );

		if ( $gallery_override )
			add_filter( 'post_gallery', array( $this, 'post_gallery' ), 10, 3 );
	}

	public function init()
	{
		$this->shortcodes( array(
			'theme-image' => 'shortcode_theme_image',
			'panels'      => 'shortcode_panels',
			'panel'       => 'shortcode_panel',
			'tabs'        => 'shortcode_tabs',
			'tab'         => 'shortcode_tab',
			'children'    => 'shortcode_children',
			'siblings'    => 'shortcode_siblings',
			// 'slider'      => 'shortcode_gallery_slider',
		) );
	}

	public function img_caption_shortcode( $empty, $attr, $content )
	{
		$args = shortcode_atts( array(
			'id'      => FALSE,
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => FALSE,
			'class'   => '',
		), $attr, 'caption' );

		$args['width'] = (int) $args['width'];
		if ( $args['width'] < 1 || empty( $args['caption'] ) )
			return $content;

		$caption = gThemeAttachment::normalizeCaption( $args['caption'], '<figcaption>', '</figcaption>' );

		return gThemeHTML::tag( 'figure', array(
			'id'    => $args['id'],
			'class' => trim( $args['align'].' '.$args['class'] ),
		), do_shortcode( $content ).$caption );
	}

	public function post_gallery( $empty, $attr, $instance )
	{
		if ( is_feed() )
			return $empty;

		$type = isset( $attr['type'] ) ? $attr['type'] : gThemeOptions::info( 'gallery_default_type', 'default' );

		switch ( $type ) {
			case 'columns' : return $this->shortcode_gallery_column( $attr );
			case 'slider'  : return $this->shortcode_gallery_slider( $attr );
			default        : return $empty; // TODO : write better default than WP's
		}

		return $empty;
	}

	public function shortcode_gallery_column( $atts, $content = NULL, $tag = 'gallery' )
	{
		$args = shortcode_atts( array(
			'order'     => 'ASC',
			'orderby'   => 'menu_order ID',
			'id'        => get_the_ID(),
			'columns'   => 3,
			'size'      => 'thumbnail',
			'include'   => '',
			'exclude'   => '',
			'link'      => 'file', // 'file', 'none', empty
			'file_size' => gThemeOptions::info( 'gallery_file_size', 'big' ),
			'nocaption' => '<svg class="-icon -icon-magnifier"><use xlink:href="#icon-magnifier"></use></svg>',
		), $atts, $tag );

		$id = intval( $args['id'] );

		$posts_args = array(
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => $args['order'],
			'orderby'        => $args['orderby'],
		);

		if ( ! empty( $args['include'] ) ) {
			$attachments = array();
			$posts_args['include'] = $args['include'];
			foreach ( get_posts( $posts_args ) as $key => $val )
				$attachments[$val->ID] = $val;
		} else if ( ! empty( $args['exclude'] ) ) {
			$posts_args['post_parent'] = $id;
			$posts_args['exclude']     = $args['exclude'];
			$attachments = get_children( $posts_args );
		} else {
			$posts_args['post_parent'] = $id;
			$attachments = get_children( $posts_args );
		}

		if ( empty( $attachments ) )
			return '';

		if ( 'none' == $args['link'] ) {

			$default = '<figure class="-gallery-img">%1$s<figcaption><div class="-description"><p>%3$s</p></div></figcaption></figure>';

		} else {

			$default = '<div class="-wrap"><figure class="-gallery-img">%1$s<figcaption><a href="%2$s" title="%4$s" id="%5$s"><div class="-description"><p>%3$s</p></div></a></figcaption></figure></div>';

			if ( gThemeOptions::supports( 'zoom', TRUE ) ) {
				$args['link'] = 'file';

				// CAUTION: css must added manually
				wp_enqueue_script( 'gtheme-zoom', GTHEME_URL.'/libs/zoom.min.js', array( 'jquery' ), '20141123', TRUE );
			}
		}

		// CAUTION: css must added manually
		// wp_register_script( 'gtheme-imagesloaded', GTHEME_URL.'/js/jquery.imagesloaded.min.js', array( 'jquery' ), '3.0.4', TRUE );
		// wp_enqueue_script( 'gtheme-gallery', GTHEME_URL.'/js/script.gallery.min.js', array( 'jquery', 'gtheme-imagesloaded' ), GTHEME_VERSION, TRUE );
		wp_enqueue_script( 'gtheme-gallery', GTHEME_URL.'/js/script.gallery.min.js', array( 'jquery', 'imagesloaded' ), GTHEME_VERSION, TRUE );

		$html     = '';
		$template = gThemeOptions::info( 'gallery_template', $default );
		$selector = $this->selector( 'gallery-column-' );

		foreach ( $attachments as $id => $attachment ) {

			if ( 'file' == $args['link'] ) {
				// $url = wp_get_attachment_url( $id );
				// geting the 'big' file, not 'raw' or full url
				list( $url, $width, $height ) = wp_get_attachment_image_src( $id, $args['file_size'] );
			} else if ( 'none' == $args['link'] ) {
				$url = '';
			} else {
				$url = get_attachment_link( $id );
			}

			if ( trim( $attachment->post_excerpt ) ) {
				$attr    = array( 'aria-describedby' => "$selector-$id" );
				$title   = esc_attr( $attachment->post_excerpt );
				$caption = wptexturize( $attachment->post_excerpt );
			} else {
				$attr    = $title = '';
				$caption = $args['nocaption'];
			}

			$html.= sprintf( $template,
				gThemeImage::getImageHTML( $id, $args['size'], $attr ),
				$url,
				$caption,
				$title,
				$selector.'-'.$id
			);
		}

		$icons = '<svg style="position: absolute; width: 0; height: 0; overflow: hidden" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><symbol id="icon-magnifier" viewBox="0 0 32 32"><title>magnifier</title><path d="M29.156 29.961l-0.709 0.709c-0.785 0.784-2.055 0.784-2.838 0l-5.676-5.674c-0.656-0.658-0.729-1.644-0.281-2.412l-3.104-3.102c-1.669 1.238-3.728 1.979-5.965 1.979-5.54 0-10.031-4.491-10.031-10.031s4.491-10.032 10.031-10.032c5.541 0 10.031 4.491 10.031 10.032 0 2.579-0.98 4.923-2.58 6.7l3.035 3.035c0.768-0.447 1.754-0.375 2.41 0.283l5.676 5.674c0.784 0.785 0.784 2.056 0.001 2.839zM18.088 11.389c0-4.155-3.369-7.523-7.524-7.523s-7.524 3.367-7.524 7.523 3.368 7.523 7.523 7.523 7.525-3.368 7.525-7.523z"></path></symbol></defs></svg>';

		return '<div class="theme-gallery-wrap -columns"><div class="-gallery-spinner"></div>'.gThemeHTML::tag( 'div', array(
			'id'    => $selector,
			'class' => array(
				'-gallery',
				'-columns-'.$args['columns'],
				'-size-'.sanitize_html_class( $args['size'] ),
			),
		), $html ).$icons.'</div>';
	}

	public function shortcode_gallery_slider( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( array(
			'order'   => 'ASC',
			'orderby' => 'menu_order ID',
			'id'      => get_the_ID(),
			'size'    => 'big',
			'include' => '',
			'exclude' => '',
			'link'    => '', // 'file', 'none', empty
		), $atts, $tag );

		$id = intval( $args['id'] );

		$posts_args = array(
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => $args['order'],
			'orderby'        => $args['orderby'],
		);

		if ( ! empty( $args['include'] ) ) {

			$attachments = array();
			$posts_args['include'] = $args['include'];

			foreach ( get_posts( $posts_args ) as $key => $val )
				$attachments[$val->ID] = $val;

		} else if ( ! empty( $args['exclude'] ) ) {

			$posts_args['post_parent'] = $id;
			$posts_args['exclude']     = $args['exclude'];
			$attachments = get_children( $posts_args );

		} else {

			$posts_args['post_parent'] = $id;
			$attachments = get_children( $posts_args );
		}

		if ( empty( $attachments ) )
			return '';

		$selector = $this->selector( 'slider-gallery-' );

		$attr = '';
		$html = '<div class="theme-gallery-wrap -flex"><div class="flexslider" id="'.$selector.'"><ul class="slides">';

		foreach ( $attachments as $id => $attachment ) {

			$html.= '<li>'.gThemeImage::getImageHTML( $id, $args['size'], $attr );

			$html.= gThemeAttachment::caption( array(
				'before' => '<div class="flex-caption">',
				'after'  => '</div>',
				'id'     => $id,
				'echo'   => FALSE,
			) ).'</li>';
		}

		$html.= '</ul></div></div>';

		$html.= '<script type="text/javascript">
/* <![CDATA[ */
			jQuery(document).ready(function($){
				$("#'.$selector.'").flexslider({
					animation: "slide",
					rtl: true
				});
			});
/* ]]> */
		</script>';

		wp_enqueue_script( 'gtheme-flexslider', GTHEME_URL.'/libs/flexslider-rtl/jquery.flexslider-min.js', array( 'jquery' ), '2.2.0', TRUE );

		return $html;
	}

	/*** SYNTAX:

	[panels id="" class="" role=""]
		[panel parent="" id="" title="" title_tag="" context="" expanded=""]...[/panel]
		[panel parent="" id="" title="" title_tag="" context="" expanded=""]...[/panel]
		[panel parent="" id="" title="" title_tag="" context="" expanded=""]...[/panel]
	[/panels]

	**/

	protected $panel_group_count = 0;
	protected $panel_count       = 0;
	protected $panel_parent      = FALSE;

	public function shortcode_panels( $atts, $content = NULL, $tag = '' )
	{
		if ( is_null( $content ) )
			return $content;

		$args = shortcode_atts( array(
			'class' => '',
			'id'    => 'panel-group-'.$this->panel_group_count,
			'role'  => 'tablist',
		), $atts, $tag );

		$this->panel_parent = $args['id'];

		$html = '<div class="panel-group '.$args['class'].'" id="'.$args['id'].'" role="'.$args['role'].'" aria-multiselectable="true">';
		$html.= do_shortcode( $content );
		$html.= '</div>';

		$this->panel_parent = FALSE;
		$this->panel_group_count++;

		return $html;
	}

	public function shortcode_panel( $atts, $content = NULL, $tag = '' )
	{
		if ( is_null( $content ) )
			return $content;

		$args = shortcode_atts( array(
			'parent'    => ( $this->panel_parent ? $this->panel_parent : 'panel-group-'.$this->panel_group_count ),
			'id'        => 'panel-'.$this->panel_count,
			'title'     => _x( 'Untitled', 'Panel Shortcode Title', GTHEME_TEXTDOMAIN ),
			'title_tag' => 'h4',
			'context'   => 'default',
			'expanded'  => FALSE,
		), $atts, $tag );

		$html = '<div class="panel panel-'.$args['context'].'">';
		$html.= '<div class="panel-heading" role="tab" id="'.$args['id'].'-wrap">';
		$html.= '<'.$args['title_tag'].' class="panel-title"><a data-toggle="collapse" data-parent="#'.$args['parent'].'" href="#'.$args['id'].'" aria-expanded="'.( $args['expanded'] ? 'true' : 'false').'" aria-controls="'.$args['id'].'">';
		$html.= $args['title'].'</a></'.$args['title_tag'].'></div>';
		$html.= '<div id="'.$args['id'].'" class="panel-collapse collapse'.( $args['expanded'] ? ' in' : '' ).'" role="tabpanel" aria-labelledby="'.$args['id'].'-wrap">';
		$html.= '<div class="panel-body">'.$content.'</div></div></div>';

		$this->panel_count++;
		return $html;
	}

	/*** SYNTAX:

	[tabs id="" class="" role=""]
		[tab id="" title="" active=""]...[/tab]
		[tab id="" title=""]...[/tab]
		[tab id="" title=""]...[/tab]
	[/tabs]

	**/

	protected $tabs_active = '';
	protected $tabs_nav    = array();
	protected $tabs_count  = 0;

	public function shortcode_tabs( $atts, $content = NULL, $tag = '' )
	{
		if ( is_null( $content ) )
			return $content;

		$args = shortcode_atts( array(
			'class' => '',
			'id'    => 'tabs-'.$this->tabs_count,
			'role'  => 'tabpanel',
		), $atts, $tag );

		$tabs = do_shortcode( trim( $content, '<br />'."\n" ) );

		if ( empty( $this->tabs_nav ) )
			return $content;

		$html = '<div class="'.$args['class'].'" id="'.$args['id'];
		$html.= '" role="'.$args['role'].'">';
		$html.= '<ul class="nav nav-tabs" role="tablist">';

		foreach ( $this->tabs_nav as $id => $title ) {
			$html.= '<li role="presentation"';
			$html.= ( $id == $this->tabs_active ? ' class="active"' : '' ).'>';
			$html.= '<a href="#'.$id.'" aria-controls="'.$id;
			$html.= '" role="tab" data-toggle="tab">'.$title.'</a></li>';
		}

		$html.= '</ul><div class="tab-content">';
		$html.= $tabs;
		$html.= '</div></div>';

		$this->tabs_nav = array();
		$this->tabs_count++;

		return $html;
	}

	public function shortcode_tab( $atts, $content = NULL, $tag = '' )
	{
		if ( is_null( $content ) )
			return $content;

		$args = shortcode_atts( array(
			'id'      => 'tab-'.$this->tabs_count.'-'.count( $this->tabs_nav ),
			'title'   => _x( 'Untitled', 'Tab Shortcode Title', GTHEME_TEXTDOMAIN ),
			'context' => 'default',
			'active'  => FALSE,
		), $atts, $tag );

		if ( $args['active'] )
			$this->tabs_active = $args['id'];

		$this->tabs_nav[$args['id']] = $args['title'];

		$html = '<div role="tabpanel" class="tab-pane tab-'.$args['context'];
		$html.= ( $args['active'] ? ' active' : '' ).'" id="'.$args['id'].'">';
		$html.= $content.'</div>';

		return $html;
	}

	public function shortcode_theme_image( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( array(
			'src'    => FALSE,
			'alt'    => FALSE,
			'title'  => FALSE,
			'width'  => FALSE,
			'height' => FALSE,
			'url'    => FALSE,
			'dir'    => 'images',
		), $atts, $tag );

		if ( ! $args['src'] )
			return $content;

		$html = gThemeHTML::tag( 'img', array(
			'src'    => GTHEME_CHILD_URL.'/'.$args['dir'].'/'.$args['src'],
			'alt'    => $args['alt'],
			'title'  => $args['url'] ? FALSE : $args['title'],
			'width'  => $args['width'],
			'height' => $args['height'],
		) );

		if ( $args['url'] )
			return gThemeHTML::tag( 'a', array(
				'href'  => $args['url'],
				'title' => $args['title'],
			), $html );

		return $html;
	}

	public function shortcode_children( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( array(
			'id'      => get_queried_object_id(),
			'type'    => 'page',
			'excerpt' => TRUE,
		), $atts, $tag );

		if ( ! $args['id'] )
			return $content;

		if ( ! is_singular( $args['type'] ) )
			return $content;

		$children = wp_list_pages( array(
			'child_of'     => $args['id'],
			'post_type'    => $args['type'],
			'excerpt'      => $args['excerpt'],
			'echo'         => FALSE,
			'depth'        => 1,
			'title_li'     => '',
			'item_spacing' => 'discard',
			'sort_column'  => 'menu_order, post_title',
			'walker'       => new gTheme_Walker_Page(),
		) );

		if ( ! $children )
			return $content;

		return '<div class="list-group children">'.$children.'</div>';
	}

	public function shortcode_siblings( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( array(
			'parent'  => NULL,
			'type'    => 'page',
			'excerpt' => TRUE,
		), $atts, $tag );

		if ( ! is_singular( $args['type'] ) )
			return $content;

		if ( is_null( $args['parent'] ) ) {

			$object = get_queried_object();

			if ( $object && isset( $object->post_parent ) )
				$args['parent'] = $object->post_parent;
		}

		if ( ! $args['parent'] )
			return $content;

		$siblings = wp_list_pages( array(
			'child_of'     => $args['parent'],
			'post_type'    => $args['type'],
			'excerpt'      => $args['excerpt'],
			'echo'         => FALSE,
			'depth'        => 1,
			'title_li'     => '',
			'item_spacing' => 'discard',
			'sort_column'  => 'menu_order, post_title',
			'walker'       => new gTheme_Walker_Page(),
		) );

		if ( ! $siblings )
			return $content;

		return '<div class="list-group siblings">'.$siblings.'</div>';
	}
}

class gTheme_Walker_Page extends Walker_Page
{

	public function start_el( &$output, $page, $depth = 0, $args = array(), $current_page = 0 )
	{
		$css_class = array( 'list-group-item', 'page-item-'.$page->ID );

		if ( isset( $args['pages_with_children'][$page->ID] ) )
			$css_class[] = 'page_item_has_children';

		if ( ! empty( $current_page ) ) {

			$_current_page = get_post( $current_page );

			if ( $_current_page && in_array( $page->ID, $_current_page->ancestors ) )
				$css_class[] = 'current_page_ancestor';

			if ( $page->ID == $current_page ) {
				$css_class[] = 'active';
				$css_class[] = 'current_page_item';

			} else if ( $_current_page && $page->ID == $_current_page->post_parent ) {
				$css_class[] = 'current_page_parent';
			}

		} else if ( $page->ID == get_option('page_for_posts') ) {
			$css_class[] = 'current_page_parent';
		}

		$css_classes = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

		if ( '' === $page->post_title )
			$page->post_title = sprintf( __( '#%d (no title)' ), $page->ID );

		if ( isset( $args['excerpt'] ) && $args['excerpt'] && ! empty( $page->post_excerpt ) ) {
			$output.= sprintf(
				'<a class="%s" href="%s"><h4 class="list-group-item-heading">%s</h4><p class="list-group-item-text">%s</p></a>',
				$css_classes,
				get_permalink( $page->ID ),
				apply_filters( 'the_title', $page->post_title, $page->ID ), // FIXME: use `prepTitle`
				$page->post_excerpt // FIXME: use `prepDescription`
			);

		} else {
			$output.= sprintf(
				'<a class="%s" href="%s">%s</a>',
				$css_classes,
				get_permalink( $page->ID ),
				apply_filters( 'the_title', $page->post_title, $page->ID ) // FIXME: use `prepTitle`
			);
		}

		/*
		if ( ! empty( $args['show_date'] ) ) {
			if ( 'modified' == $args['show_date'] ) {
				$time = $page->post_modified;
			} else {
				$time = $page->post_date;
			}

			$date_format = empty( $args['date_format'] ) ? '' : $args['date_format'];
			$output.= " ".mysql2date( $date_format, $time );
		}
		*/
	}

	public function end_el( &$output, $page, $depth = 0, $args = array() )
	{
		$output.= "\n";
	}
}
