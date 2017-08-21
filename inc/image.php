<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeImage extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'core_post_thumbnails'   => FALSE, // enables WordPress core thumbnail for posts
			'image_size_tags'        => TRUE, // registers theme's image sizes
			'image_attachment_tags'  => TRUE, // displays ui for theme's image sizes
			'image_attachment_terms' => FALSE, // image for terms on admin media editor
			'responsive_class'       => FALSE, // extracts and appends css class into content images
			'media_object_sizes'     => TRUE, // tells gnetwork to not generate default image sizes
		), $args ) );

		if ( $core_post_thumbnails )
			add_theme_support( 'post-thumbnails', gThemeOptions::info( 'core_post_thumbnails', array( 'post' ) ) );

		if ( $image_size_tags ) {
			add_action( 'init', array( $this, 'init' ) );
			add_filter( 'intermediate_image_sizes_advanced', array( $this, 'intermediate_image_sizes_advanced' ), 8, 2 );
			add_filter( 'image_size_names_choose', array( $this, 'image_size_names_choose' ) );
		}

		add_filter( 'get_image_tag_class', array( $this, 'get_image_tag_class' ), 10, 4 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'wp_get_attachment_image_attributes' ), 10, 2 );

		if ( $media_object_sizes )
			add_filter( 'gnetwork_media_object_sizes', '__return_true' );

		if ( $responsive_class )
			add_filter( 'the_content', array( $this, 'the_content_responsive_class' ), 100 );

		add_filter( 'get_image_tag', array( $this, 'get_image_tag' ), 5, 6 );
		add_filter( 'post_thumbnail_html', array( $this, 'strip_width_height' ), 10 );

		add_filter( 'pre_option_image_default_link_type', array( $this, 'pre_option_image_default_link_type' ), 10 );
		add_filter( 'pre_option_image_default_align', array( $this, 'pre_option_image_default_align' ), 10 );
		add_filter( 'pre_option_image_default_size', array( $this, 'pre_option_image_default_size' ), 10 );
		add_filter( 'jpeg_quality', array( $this, 'jpeg_quality' ), 10, 2 );
		add_filter( 'wp_editor_set_quality', array( $this, 'wp_editor_set_quality' ), 10, 2 );

		if ( $image_attachment_tags ) {
			add_filter( 'attachment_fields_to_edit', array( $this, 'tags_attachment_fields_to_edit' ), 10, 2 );
			add_filter( 'attachment_fields_to_save', array( $this, 'tags_attachment_fields_to_save' ), 10, 2 );
		}

		if ( $image_attachment_terms ) {
			add_filter( 'attachment_fields_to_edit', array( $this, 'terms_attachment_fields_to_edit' ), 9, 2 );
			add_filter( 'attachment_fields_to_save', array( $this, 'terms_attachment_fields_to_save' ), 9, 2 );
		}

		if ( is_admin() ) {

			if ( $image_attachment_tags )
				add_filter( 'geditorial_tweaks_column_thumb', array( $this, 'tweaks_column_thumb' ), 12, 3 );
		}
	}

	public function init()
	{
		foreach ( gThemeOptions::info( 'images', array() ) as $name => $size )
			self::registerImageSize( $name, $size );
	}

	public function tweaks_column_thumb( $html, $post_id, $size )
	{
		if ( ! $post = get_post( $post_id ) )
			return $html;

		if ( 'post' != $post->post_type )
			return $html;

		$size = gThemeOptions::info( 'thumbnail_image_size', $size );

		if ( ! $post_thumbnail_id = self::id( $size, $post_id ) )
			return $html;

		if ( ! $post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, $size ) )
			return $html;

		$image = gThemeHTML::tag( 'img', array( 'src' => $post_thumbnail_img[0] ) );

		return gThemeHTML::tag( 'a', array(
			'href'   => wp_get_attachment_url( $post_thumbnail_id ),
			'title'  => get_the_title( $post_thumbnail_id ),
			'class'  => 'thickbox',
			'target' => '_blank',
		), $image );
	}

	// core dup with posttype/taxonomy/title
	// @REF: `add_image_size()`
	public static function registerImageSize( $name, $atts = array() )
	{
		global $_wp_additional_image_sizes;

		$args = self::atts( array(
			'n' => __( 'Untitled' ),
			'w' => 0,
			'h' => 0,
			'c' => 0,
			'p' => array( 'post' ), // posttype: TRUE: all/array: posttypes/FALSE: none
			't' => FALSE, // taxonomy: TRUE: all/array: taxes/FALSE: none
		), $atts );

		$_wp_additional_image_sizes[$name] = array(
			'width'     => absint( $args['w'] ),
			'height'    => absint( $args['h'] ),
			'crop'      => $args['c'],
			'post_type' => $args['p'],
			'taxonomy'  => $args['t'],
			'title'     => $args['n'],
		);
	}

	public function intermediate_image_sizes_advanced( $sizes, $metadata )
	{
		// removing standard image sizes
		unset(
			$sizes['thumbnail'],
			$sizes['medium'],
			$sizes['medium_large'],
			$sizes['large']
		);

		return $sizes;
	}

	// http://stackoverflow.com/a/20499803
	// https://gist.github.com/DavidCWebs/bb2df8a868a362510dc1
	// TODO: test this
	public function the_content_responsive_class( $content )
	{
		if ( empty( $content ) )
			return $content;

		$document = new \DOMDocument();
		libxml_use_internal_errors( TRUE );

		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		$document->loadHTML( utf8_decode( $content ) );
		$images = $document->getElementsByTagName( 'img' );

		foreach ( $images as $image )
			$image->setAttribute( 'class', 'img-responsive' ); // FIXME: this will replace all classes!!

		$html = $document->saveHTML();
		return $html;
	}

	public function get_image_tag_class( $class, $id, $align, $size )
	{
		return $class.' '.gThemeOptions::info( 'image-class', 'the-img img-responsive' );
	}

	public function wp_get_attachment_image_attributes( $attr, $attachment )
	{
		unset( $attr['title'] );
		$attr['class'] = $attr['class'].' '.gThemeOptions::info( 'image-class', 'the-img img-responsive' );
		return $attr;
	}

	public function get_image_tag( $html, $id, $alt, $title, $align, $size )
	{
		list( $src, $width, $height ) = image_downsize( $id, $size );

		return gThemeHTML::tag( 'img', array(
			'src'   => $src,
			'alt'   => $alt ? $alt : FALSE,
			'class' => apply_filters( 'get_image_tag_class', 'align'.$align, $id, $align, $size ),
			'data'  => array(
				'width'  => $width, // need this for `image_add_caption()`
				// 'height' => $height,
				'id'     => $id,
				'size'   => $size,
				// 'align'  => $align,
			),
		) );
	}

	public function strip_width_height( $html )
	{
		return preg_replace( '/(width|height)="\d*"\s/', '', $html );
	}

	public function pre_option_image_default_link_type( $option )
	{
		return gThemeOptions::info( 'editor_image_default_link_type', 'file' );
	}

	public function pre_option_image_default_align( $option )
	{
		return gThemeOptions::info( 'editor_image_default_align', 'center' );
	}

	public function pre_option_image_default_size( $option )
	{
		return gThemeOptions::info( 'editor_image_default_size', 'content' );
	}

	public function jpeg_quality( $quality, $context )
	{
		return gThemeOptions::info( 'jpeg_quality', $quality );
	}

	public function wp_editor_set_quality( $default_quality, $mime_type )
	{
		return gThemeOptions::info( 'wp_editor_set_quality', $default_quality );
	}

	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	// IMAGE TAGS ON ADMIN MEDIA EDITOR

	// filters the sizes on admin insert media page
	public function image_size_names_choose( $size_names )
	{
		$sizes = gThemeOptions::info( 'images', array() );

		if ( empty( $sizes ) )
			return $size_names;

		if ( isset( $_REQUEST['post_id'] )
			&& $post_id = absint( $_REQUEST['post_id'] ) ) {

			$post_type = get_post( $post_id )->post_type;

		} else if ( isset( $_REQUEST['post'] )
			&& $post_id = absint( $_REQUEST['post'] ) ) {

			$post_type = get_post( $post_id )->post_type;

		} else if ( isset( $_REQUEST['post_type'] ) ) {

			$post_type = $_REQUEST['post_type'];

		} else if ( $current = self::getCurrentPostType() ) {

			$post_type = $current;

		} else {

			// $post_type = 'post';
			return $size_names; // bailing
		}

		$new_sizes = array();

		foreach ( $sizes as $name => $size )
			if ( $size['i'] && ( TRUE === $size['p'] || in_array( $post_type, $size['p'] ) ) )
				$new_sizes[$name] = $size['n'];

		// if ( gThemeWordPress::isDev() )
		// 	error_log( print_r( compact( 'post_type', 'new_sizes', 'size_names' ), TRUE ) );

		return array_merge( $size_names, $new_sizes );
	}

	public function tags_attachment_fields_to_edit( $fields, $post )
	{
		if ( ! $post_id = absint( @$_REQUEST['post_id'] ) )
			return $fields;

		$sizes = gThemeOptions::info( 'images', array() );

		if ( empty( $sizes ) )
			return $fields;

		$post_type = get_post_type( $post_id );
		$images    = self::getMetaImages( $post_id, TRUE );

		$html = $checked = '';

		foreach ( $sizes as $name => $size ) {

			if ( $size['s'] && ( TRUE === $size['p'] || in_array( $post_type, $size['p'] ) ) ) {

				$id      = 'attachments-'.$post->ID.'-gtheme-size-'.$name;
				$checked = ( isset( $images[$name] ) && $images[$name] == $post->ID ) ? ' checked="checked"' : '';
				$label   = sprintf( _x( '%1$s <small>(%2$s&times;%3$s)</small>', 'Image Module: Media Tag Checkbox Label', GTHEME_TEXTDOMAIN ),
					$size['n'], number_format_i18n( $size['h'] ), number_format_i18n( $size['w'] ) );

				$html .= '<li><label for="'.$id.'"><input style="width:10px;vertical-align:bottom;"'
					.' type="checkbox" value="'.$name.'" id="'.$id.'" name="gtheme_size_'.$name
					.'" '.$checked.' />'.$label.'</label></li>';
			}
		}

		if ( ! empty( $html ) ) {
			$html = '<ul style="margin:0;">'.$html.'</ul>'.
					'<input type="hidden" name="gtheme-image-sizes" value="modal" />'.
					'<input type="hidden" name="attachments['.$post->ID.']" value="dummy" />';

			$fields['gtheme_image_sizes'] = array(
				'label' => __( 'Media Tags', GTHEME_TEXTDOMAIN ),
				'input' => 'html',
				'html'  => $html,
			);
		}

		return $fields;
	}

	public function tags_attachment_fields_to_save( $post, $attachment )
	{
		if ( ! isset( $_REQUEST['gtheme-image-sizes'] )
			|| 'modal' != $_REQUEST['gtheme-image-sizes'] )
				return $post;

		if ( ! $post_id = absint( $_REQUEST['post_id'] ) )
			return $post;

		$sizes = gThemeOptions::info( 'images', array() );

		if ( empty( $sizes ) )
			return $post;

		$images = $striped = array();
		$saved  = self::getMetaImages( $post_id );

		foreach ( $sizes as $name => $size )
			if ( isset( $_REQUEST['gtheme_size_'.$name] ) && $name == $_REQUEST['gtheme_size_'.$name] )
				$images[$name] = $post['ID'];

		foreach ( $saved as $saved_size => $saved_id )
			if ( $post['ID'] != $saved_id )
				$striped[$saved_size] = $saved_id;

		self::setMetaImages( array_merge( $striped, $images ), $post_id );

		return $post;
	}

	public function terms_attachment_fields_to_edit( $form_fields, $post )
	{
		if ( empty( $_REQUEST['post_id'] ) ) {

			if ( empty( $post->post_parent ) )
				return $form_fields;

			else
				$parent_id = $post->post_parent;

		} else {
			$parent_id = absint( $_REQUEST['post_id'] );
		}

		$post_type = get_post_type( $parent_id );

		if ( ! in_array( $post_type, gThemeOptions::info( 'support_images_terms', array() ) ) )
			return $form_fields;

		$saved_terms = get_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, TRUE );

		if ( ! is_array( $saved_terms ) )
			$saved_terms = array();

		$selected = array_search( $post->ID, $saved_terms );

		$id = 'attachments-'.$post->ID.'-gtheme_images_terms';

		$dropdown = wp_dropdown_categories( array(
			'taxonomy'         => gThemeOptions::info( 'support_images_terms_taxonomy', 'category' ),
			'selected'         => ( FALSE === $selected ? 0 : $selected ),
			'show_option_none' => __( '&mdash; Select a Term &mdash;', GTHEME_TEXTDOMAIN ),
			'name'             => 'attachments['.$post->ID.'][gtheme_images_terms]',
			'id'               => $id,
			'show_count'       => 0,
			'hide_empty'       => 0,
			'hierarchical'     => 1,
			'echo'             => 0,
		) );

		$form_fields['gtheme_images_terms']['tr'] = '<tr><th class="label" valign="top" scope="row"><label for="'.$id.'"><span>'
			.__( 'Assign for', GTHEME_TEXTDOMAIN ).'</span></label></th><td class="field">'
			.$dropdown.'</td></tr>';

		return $form_fields;
	}

	public function terms_attachment_fields_to_save( $post, $attachment )
	{
		if ( ! $parent_id = absint( $_REQUEST['post_id'] ) ) {

			if ( empty( $post['post_parent'] ) )
				return $post;

			else
				$parent_id = $post['post_parent'];
		}

		$post_type = get_post_type( $parent_id );
		if ( ! in_array( $post_type, gThemeOptions::info( 'support_images_terms', array() ) ) )
			return $post;

		if ( isset( $attachment['gtheme_images_terms'] ) ) {
			$saved_terms = get_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, TRUE );
			if ( ! is_array( $saved_terms ) )
				$saved_terms = array();
			$selected = array_search( $post['ID'], $saved_terms );
			unset( $saved_terms[$selected] );
			if ( '-1' != $attachment['gtheme_images_terms'] )
				$saved_terms[$attachment['gtheme_images_terms']] = $post['ID'];
			update_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, $saved_terms );
		}

		return $post;
	}

	// wrapper for WP_Image class
	// SEE : https://github.com/markoheijnen/WP_Image
	// Last Updated : 20150615
	public static function attachment( $attachment_id )
	{
		if ( ! class_exists( 'WP_Image' ) )
			include_once( GTHEME_DIR.'/libs/wp-image/wp-image.php' );

		return new WP_Image( $attachment_id );
	}

	///////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////
	///STATIC/METHODS//////////////////////////////////////////

	public static function getMetaImages( $post_id = NULL, $check = FALSE )
	{
		global $gThemeImagesMeta;

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		if ( empty( $gThemeImagesMeta ) )
			$gThemeImagesMeta = array();

		if ( isset( $gThemeImagesMeta[$post_id] ) )
			return $gThemeImagesMeta[$post_id];

		if ( $images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE ) )
			$gThemeImagesMeta[$post_id] = $images;

		else
			$gThemeImagesMeta[$post_id] = array();

		if ( $check && $thumbnail = get_post_thumbnail_id( $post_id ) ) {

			if ( ! isset( $gThemeImagesMeta[$post_id]['raw'] ) )
				$gThemeImagesMeta[$post_id]['raw'] = $thumbnail;

			delete_post_meta( $post_id, '_thumbnail_id' );
		}

		return $gThemeImagesMeta[$post_id];
	}

	public static function setMetaImages( $images, $post_id = NULL, $check = FALSE )
	{
		global $gThemeImagesMeta;

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		if ( count( $images ) )
			update_post_meta( $post_id, GTHEME_IMAGES_META, $images );
		else
			delete_post_meta( $post_id, GTHEME_IMAGES_META );

		if ( $check )
			delete_post_meta( $post_id, '_thumbnail_id' );

		$gThemeImagesMeta[$post_id] = $images;
	}

	// ANCESTOR : gtheme_get_image_id()
	public static function id( $tag = 'raw', $post_id = NULL )
	{
		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		$images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE );

		if ( isset( $images[$tag] ) )
			return $images[$tag];

		if ( isset( $images['raw'] ) )
			return $images['raw'];

		if ( ! gThemeOptions::info( 'post_thumbnail_fallback', TRUE ) )
			return FALSE;

		// fallback
		if ( $thumbnail = get_post_thumbnail_id( $post_id ) )
			return $thumbnail;

		return FALSE;
	}

	public static function update_cache( $size = 'raw', $wp_query = NULL )
	{
		if ( ! $wp_query )
			$wp_query = $GLOBALS['wp_query'];

		if ( $wp_query->thumbnails_cached )
			return;

		$thumb_ids = array();

		foreach ( $wp_query->posts as $post )
			if ( $id = self::id( $size, $post->ID ) )
				$thumb_ids[] = $id;

		if ( ! empty( $thumb_ids ) )
			_prime_post_caches( $thumb_ids, FALSE, TRUE );

		$wp_query->thumbnails_cached = TRUE;
	}

	public static function get_image( $atts = array() )
	{
		self::__dep( 'gThemeImage::getImage()' );
		return self::getImage( $atts );
	}

	// ANCESTOR: `gtheme_get_the_image()`, `gtheme_get_image_caption()`, `get_image()`
	public static function getImage( $atts = array() )
	{
		$args = self::atts( array(
			'tag'               => 'raw',
			'post_id'           => NULL,
			'post_thumbnail_id' => FALSE,
			'attr'              => '',
			'link'              => 'parent',
			'empty'             => '',
			'url'               => FALSE,
			'caption'           => FALSE,
			'default_caption'   => '',
		), $atts );

		if ( is_null( $args['post_id'] ) )
			$args['post_id'] = get_the_ID();

		if ( ! $args['post_thumbnail_id'] )
			$args['post_thumbnail_id'] = self::id( $args['tag'], $args['post_id'] );

		$args['tag'] = apply_filters( 'post_thumbnail_size', $args['tag'] );

		if ( $args['post_thumbnail_id'] ) {

			if ( $args['url'] ) {

				$url = wp_get_attachment_url( $args['post_thumbnail_id'] );
				return FALSE === $url ? $args['empty'] : $url;

			} else {

				do_action( 'begin_fetch_post_thumbnail_html', $args['post_id'], $args['post_thumbnail_id'], $args['tag'] );

				if ( in_the_loop() )
					self::update_cache( $args['tag'] );

				$html = wp_get_attachment_image( $args['post_thumbnail_id'], $args['tag'], FALSE, $args['attr'] );

				do_action( 'end_fetch_post_thumbnail_html', $args['post_id'], $args['post_thumbnail_id'], $args['tag'] );

				if ( FALSE !== $args['link'] ) {

					// link to another size of image
					if ( is_array( $args['link'] ) ) {

						foreach ( $args['link'] as $link_size ) {
							if ( $link_size_thumbnail_id = self::id( $link_size, $args['post_id'] ) ) {
								if ( $attachment_post = get_post( $link_size_thumbnail_id ) ) {
									$args['link'] = get_attachment_link( $attachment_post );
									break;
								}
							}
						}

						// not found
						if ( is_array( $args['link'] ) ) {

							$args['link'] = FALSE;

							if ( $attachment_post = get_post( $args['post_thumbnail_id'] ) )
								$args['link'] = get_attachment_link( $attachment_post );
						}

					} else if ( 'parent' == $args['link'] ) {

						$args['link'] = get_permalink( $args['post_id'] );

					} else if ( 'attachment' == $args['link'] ) {

						$args['link'] = FALSE;

						if ( $attachment_post = get_post( $args['post_thumbnail_id'] ) )
							$args['link'] = get_attachment_link( $attachment_post );

					} else if ( 'url' == $args['link'] ) {

						$args['link'] = FALSE;

						if ( $attachment_post = get_post( $args['post_thumbnail_id'] ) )
							$args['link'] = wp_get_attachment_url( $attachment_post->ID );
					}

					if ( $args['link'] && $html ) {
						$template = gThemeOptions::info( 'template_image_link', '<a href="%2$s" class="%3$s">%1$s</a>' );
						$html     = sprintf( $template, $html, esc_url( $args['link'] ), '-thumbnail-link' );
					}
				}

				if ( $args['caption'] ) {

					$caption = FALSE;

					if ( TRUE === $args['caption'] ) {

						if ( ! $caption = wp_get_attachment_caption( $args['post_thumbnail_id'] ) )
							$caption = $args['default_caption'];

					} else {
						$caption = $args['caption'];
					}

					if ( $caption && $html ) {

						$template = gThemeOptions::info( 'template_image_caption', '<div class="%3$s">%1$s<p class="%4$s">%2$s</p></div>' );

						if ( $caption = gThemeAttachment::normalizeCaption( $caption ) )
							$html = sprintf( $template, $html, $caption, 'the-caption', 'the-caption-text' );
					}
				}
			}
		} else {
			$html = $args['empty'];
		}

		return apply_filters( 'post_thumbnail_html', $html, $args['post_id'], $args['post_thumbnail_id'], $args['tag'], $args['attr'] );
	}

	// ANCESTOR : gtheme_image(), gtheme_image_caption()
	public static function image( $atts = array() )
	{
		$args = self::atts( array(
			'tag'             => 'raw',
			'post_id'         => NULL,
			'link'            => 'parent',
			'attr'            => '',
			'empty'           => self::holder( ( isset( $atts['tag'] ) ? $atts['tag'] : 'raw' ), ( isset( $atts['class'] ) ? $atts['class'] : 'gtheme-image' ) ),
			'url'             => FALSE,
			'caption'         => FALSE,
			'default_caption' => '',
			'echo'            => TRUE,
			'class'           => 'gtheme-image',
			'before'          => '<div class="entry-image'.( isset( $atts['tag'] ) ? ' image-'.$atts['tag'] : '' ).'">',
			'after'           => '</div>',
			'context'         => NULL,
		), $atts );

		if ( $args['class'] ) {
			if ( $args['attr'] ) {
				$args['attr'] = wp_parse_args( $attr, array() ); // FIXME: MAYBE WE HAVE PROBLEM!
				$args['attr']['class'] = $args['class'];
			} else {
				$args['attr'] = array( 'class' => $args['class'] );
			}
		}

		$html = self::getImage( $args );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		if ( ! empty( $html ) )
			echo $args['before'].$html.$args['after'];

		else if ( ! empty( $args['empty'] ) )
			echo $args['before'].$args['empty'].$args['after'];
	}

	// ANCESTOR: gtheme_empty_image()
	public static function holder( $tag = 'raw', $extra_class = '', $force = FALSE )
	{
		if ( ! $force && ! current_user_can( 'edit_others_posts' ) )
			return '';

		return '<div class="gtheme-image-holder holder-image-'
			.$tag.( gThemeWordPress::isDev() ? ' isdev ' : ' ' )
			.$extra_class
			.'"></div>';
	}

	public static function holderJS( $width = 100, $height = 100, $atts = array() )
	{
		return '<img class="'.gThemeOptions::info( 'image-class', 'the-img img-responsive' ).'" data-src="holder.js/'.$width.'x'.$height.'">';
	}

	// FIXME: working draft
	public static function termImage( $atts = array() )
	{
		$args = self::atts( array(
			'tag'     => 'raw',
			'term_id' => get_queried_object_id(),
			'url'     => FALSE,
			'empty'   => '',
		), $atts );

		if ( ! $args['term_id'] )
			return $args['empty'];

		if ( ! $attach_id = get_term_meta( $args['term_id'], 'image', TRUE ) )
			return $args['empty'];

		$image = wp_get_attachment_image_src( $attach_id, $args['tag'] );

		if ( empty( $image[0] ) )
			return $args['empty'];

		if ( $args['url'] )
			return $image[0];

		return gThemeHTML::tag( 'img', array(
			'src'   => $image[0],
			'alt'   => '',
			'class' => gThemeOptions::info( 'image-class', 'the-img img-responsive' ).' -featured',
		) );
	}
}
