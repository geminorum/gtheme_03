<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeShortCodes extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'defaults'         => TRUE,
			'caption_override' => TRUE,
			'gallery_override' => TRUE,
		], $args ) );

		if ( $defaults )
			add_action( 'init', [ $this, 'init' ], 14 );

		if ( $caption_override )
			add_filter( 'img_caption_shortcode', [ $this, 'img_caption_shortcode' ], 10, 3 );

		if ( $gallery_override )
			add_filter( 'post_gallery', [ $this, 'post_gallery' ], 10, 3 );
	}

	public function init()
	{
		$this->shortcodes( [
			'theme-image'    => 'shortcode_theme_image',
			'panels'         => 'shortcode_panels',
			'panel'          => 'shortcode_panel',
			'tabs'           => 'shortcode_tabs',
			'tab'            => 'shortcode_tab',
			'children'       => 'shortcode_children',
			'siblings'       => 'shortcode_siblings',
			'people-image'   => 'shortcode_person_picture',
			'person-picture' => 'shortcode_person_picture',
			'related-posts'  => 'shortcode_related_posts',
			// 'slider'        => 'shortcode_gallery_slider',
		] );
	}

	public function img_caption_shortcode( $empty, $attr, $content )
	{
		$args = shortcode_atts( [
			'id'      => FALSE,
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => FALSE,
			'class'   => '',
		], $attr, 'caption' );

		$args['width'] = (int) $args['width'];
		if ( $args['width'] < 1 || empty( $args['caption'] ) )
			return $content;

		$caption = gThemeAttachment::normalizeCaption( $args['caption'], '<figcaption>', '</figcaption>' );

		return gThemeHTML::tag( 'figure', [
			'id'    => $args['id'] ?: FALSE,
			'class' => trim( $args['align'].' '.$args['class'] ),
		], do_shortcode( $content ).$caption );
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
		$args = shortcode_atts( [
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
		], $atts, $tag );

		$id = intval( $args['id'] );

		$posts_args = [
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => $args['order'],
			'orderby'        => $args['orderby'],
		];

		if ( ! empty( $args['include'] ) ) {
			$attachments = [];
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
				wp_enqueue_script( 'gtheme-zoom', GTHEME_URL.'/js/vendor/jquery.zoom.min.js', [ 'jquery' ], '20141123', TRUE );
			}
		}

		// CAUTION: css must added manually
		wp_enqueue_script( 'gtheme-gallery', GTHEME_URL.'/js/script.gallery.min.js', [ 'jquery', 'imagesloaded' ], GTHEME_VERSION, TRUE );

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
				$attr    = [ 'aria-describedby' => "$selector-$id" ];
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

		return '<div class="theme-gallery-wrap -columns"><div class="-gallery-spinner"></div>'.gThemeHTML::tag( 'div', [
			'id'    => $selector,
			'class' => [
				'-gallery',
				'-columns-'.$args['columns'],
				'-size-'.sanitize_html_class( $args['size'] ),
			],
		], $html ).$icons.'</div>';
	}

	public function shortcode_gallery_slider( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( [
			'order'   => 'ASC',
			'orderby' => 'menu_order ID',
			'id'      => get_the_ID(),
			'size'    => 'big',
			'include' => '',
			'exclude' => '',
			'link'    => '', // 'file', 'none', empty
		], $atts, $tag );

		$id = intval( $args['id'] );

		$posts_args = [
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => $args['order'],
			'orderby'        => $args['orderby'],
		];

		if ( ! empty( $args['include'] ) ) {

			$attachments = [];
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

			$html.= gThemeAttachment::caption( [
				'before' => '<div class="flex-caption">',
				'after'  => '</div>',
				'id'     => $id,
				'echo'   => FALSE,
			] ).'</li>';
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

		wp_enqueue_script( 'gtheme-flexslider', GTHEME_URL.'/js/vendor/jquery.flexslider-rtl.min.js', [ 'jquery' ], '2.2.0', TRUE );

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

		$args = shortcode_atts( [
			'class' => '',
			'id'    => 'panel-group-'.$this->panel_group_count,
			'role'  => 'tablist',
		], $atts, $tag );

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

		$args = shortcode_atts( [
			'parent'    => ( $this->panel_parent ? $this->panel_parent : 'panel-group-'.$this->panel_group_count ),
			'id'        => 'panel-'.$this->panel_count,
			'title'     => _x( 'Untitled', 'Panel Shortcode Title', GTHEME_TEXTDOMAIN ),
			'title_tag' => 'h4',
			'context'   => 'default',
			'expanded'  => FALSE,
		], $atts, $tag );

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
	protected $tabs_nav    = [];
	protected $tabs_count  = 0;

	public function shortcode_tabs( $atts, $content = NULL, $tag = '' )
	{
		if ( is_null( $content ) )
			return $content;

		$args = shortcode_atts( [
			'class' => '',
			'id'    => 'tabs-'.$this->tabs_count,
			'role'  => 'tabpanel',
		], $atts, $tag );

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

		$this->tabs_nav = [];
		$this->tabs_count++;

		return $html;
	}

	public function shortcode_tab( $atts, $content = NULL, $tag = '' )
	{
		if ( is_null( $content ) )
			return $content;

		$args = shortcode_atts( [
			'id'      => 'tab-'.$this->tabs_count.'-'.count( $this->tabs_nav ),
			'title'   => _x( 'Untitled', 'Tab Shortcode Title', GTHEME_TEXTDOMAIN ),
			'context' => 'default',
			'active'  => FALSE,
		], $atts, $tag );

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
		$args = shortcode_atts( [
			'src'    => FALSE,
			'alt'    => FALSE,
			'title'  => FALSE,
			'width'  => FALSE,
			'height' => FALSE,
			'url'    => FALSE,
			'dir'    => 'images',
		], $atts, $tag );

		if ( ! $args['src'] )
			return $content;

		$html = gThemeHTML::tag( 'img', [
			'src'    => GTHEME_CHILD_URL.'/'.$args['dir'].'/'.$args['src'],
			'alt'    => $args['alt'],
			'title'  => $args['url'] ? FALSE : $args['title'],
			'width'  => $args['width'],
			'height' => $args['height'],
		] );

		if ( $args['url'] )
			return gThemeHTML::tag( 'a', [
				'href'  => $args['url'],
				'title' => $args['title'],
			], $html );

		return $html;
	}

	public function shortcode_children( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( [
			'id'      => get_queried_object_id(),
			'type'    => 'page',
			'excerpt' => TRUE,
		], $atts, $tag );

		if ( ! $args['id'] )
			return $content;

		if ( ! is_singular( $args['type'] ) )
			return $content;

		$children = wp_list_pages( [
			'child_of'     => $args['id'],
			'post_type'    => $args['type'],
			'excerpt'      => $args['excerpt'],
			'echo'         => FALSE,
			'depth'        => 1,
			'title_li'     => '',
			'item_spacing' => 'discard',
			'sort_column'  => 'menu_order, post_title',
			'walker'       => new gTheme_Walker_Page(),
		] );

		if ( ! $children )
			return $content;

		return '<div class="-list-wrap list-group children">'.$children.'</div>';
	}

	public function shortcode_siblings( $atts, $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( [
			'parent'  => NULL,
			'type'    => 'page',
			'excerpt' => TRUE,
		], $atts, $tag );

		if ( ! is_singular( $args['type'] ) )
			return $content;

		if ( is_null( $args['parent'] ) ) {

			$object = get_queried_object();

			if ( $object && isset( $object->post_parent ) )
				$args['parent'] = $object->post_parent;
		}

		if ( ! $args['parent'] )
			return $content;

		$siblings = wp_list_pages( [
			'child_of'     => $args['parent'],
			'post_type'    => $args['type'],
			'excerpt'      => $args['excerpt'],
			'echo'         => FALSE,
			'depth'        => 1,
			'title_li'     => '',
			'item_spacing' => 'discard',
			'sort_column'  => 'menu_order, post_title',
			'walker'       => new gTheme_Walker_Page(),
		] );

		if ( ! $siblings )
			return $content;

		return '<div class="-list-wrap list-group siblings">'.$siblings.'</div>';
	}

	public function shortcode_person_picture( $atts = [], $content = NULL, $tag = '' )
	{
		$parsed = shortcode_atts( [
			'id'       => NULL,
			'taxonomy' => GPEOPLE_PEOPLE_TAXONOMY,
			'size'     => NULL,
			'figure'   => TRUE,
			'context'  => NULL,
			'wrap'     => TRUE,
			'before'   => '',
			'after'    => '',
		], $atts, $tag );

		if ( FALSE === $parsed['context'] )
			return NULL;

		$args = [
			'taxonomy' => $parsed['taxonomy'],
			'size'     => $parsed['size'],
			'figure'   => $parsed['figure'],
			'echo'     => FALSE,
			'wrap'     => FALSE,
		];

		if ( $parsed['id'] )
			$args['id'] = $parsed['id'];

		if ( ! $html = gThemeEditorial::personPicture( $args ) )
			return $content;

		return self::shortcodeWrap( $html, 'person-picture', $parsed );
	}

	public function shortcode_related_posts( $atts = [], $content = NULL, $tag = '' )
	{
		$args = shortcode_atts( [
			'ids'      => FALSE,
			'post'     => NULL,
			'posttype' => 'post',
			'taxonomy' => 'post_tag',
			'number'   => 10,
			'title'    => _x( 'Related Posts', 'Modules: ShortCodes: Defaults', GTHEME_TEXTDOMAIN ), // FALSE to disable
			'context'  => NULL,
			'wrap'     => TRUE,
			'before'   => '',
			'after'    => '',
		], $atts, $tag );

		if ( FALSE === $args['context'] )
			return NULL;

		$query_args = [
			'post_status'            => 'publish',
			'no_found_rows'          => TRUE,
			'ignore_sticky_posts'    => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		];

		if ( ! empty( $args['ids'] ) ) {

			$query_args = array_merge( $query_args, [
				'post__in'       => explode( ',', maybe_unserialize( $args['ids'] ) ),
				'orderby'        => 'post__in',
				'post_type'      => 'any',
				'posts_per_page' => -1,
			] );

		} else {

			if ( ! $post = get_post( $args['post'] ) )
				return $content;

			$terms = wp_get_object_terms( $post->ID, $args['taxonomy'], [ 'fields' => 'ids' ] );

			if ( is_wp_error( $terms ) || empty( $terms ) )
				return $content;

			$query_args = array_merge( $query_args, [
				'tax_query' => [
					[
						'taxonomy' => $args['taxonomy'],
						'field'    => 'id',
						'terms'    => $terms,
						'operator' => 'IN',
					],
					'relation' => 'AND',
					[
						'taxonomy' => GTHEME_SYSTEMTAGS,
						'field'    => 'slug',
						'terms'    => 'no-related',
						'operator' => 'NOT IN',
					],
				],
				'post_type'      => $args['posttype'],
				'posts_per_page' => $args['number'],
				'post__not_in'   => [ $post->ID ],
			] );
		}

		$query = new \WP_Query( $query_args );

		if ( ! $query->have_posts() )
			return $content;

		ob_start();

		gThemeHTML::h3( $args['title'], '-title' );
		echo '<ul>';

		while ( $query->have_posts() ) {

			$query->the_post();

			if ( trim( get_the_title() ) ) {
				echo '<li>';
					get_template_part( 'row', $args['context'] );
				echo '</li>';
			}
		}

		echo '</ul>';
		wp_reset_postdata();

		return self::shortcodeWrap( ob_get_clean(), 'related-posts', $args );
	}
}
