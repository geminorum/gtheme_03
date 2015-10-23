<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeImage extends gThemeModuleCore
{

	var $_ajax = TRUE;

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'responsive_class' => FALSE,
		), $args ) );

		add_action( 'init', array( &$this, 'init' ) );
		add_filter( 'intermediate_image_sizes_advanced', array( &$this, 'intermediate_image_sizes_advanced' ) );

		add_filter( 'get_image_tag_class', array( &$this, 'get_image_tag_class' ), 10, 4 );
		add_filter( 'wp_get_attachment_image_attributes', array( &$this, 'wp_get_attachment_image_attributes' ), 10, 2 );

		if ( $responsive_class )
			add_filter( 'the_content', array( &$this, 'the_content_responsive_class' ), 100 );

		add_filter( 'post_thumbnail_html', array( &$this, 'strip_width_height' ), 10 );
		add_filter( 'image_send_to_editor', array( &$this, 'strip_width_height' ), 10 );
		add_filter( 'image_send_to_editor', array( &$this, 'image_send_to_editor' ), 12, 8 );

		add_filter( 'pre_option_image_default_link_type', array( &$this, 'pre_option_image_default_link_type' ), 10 );
		add_filter( 'pre_option_image_default_align', array( &$this, 'pre_option_image_default_align' ), 10 );
		add_filter( 'pre_option_image_default_size', array( &$this, 'pre_option_image_default_size' ), 10 );
		add_filter( 'jpeg_quality', array( &$this, 'jpeg_quality' ), 10, 2 );
		add_filter( 'wp_editor_set_quality', array( &$this, 'wp_editor_set_quality' ), 10, 2 );

		add_filter( 'image_size_names_choose', array( &$this, 'image_size_names_choose' ) );
		add_filter( 'attachment_fields_to_edit', array( &$this, 'tags_attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( &$this, 'tags_attachment_fields_to_save' ), 10, 2 );

		// image for terms on admin media editor
		add_filter( 'attachment_fields_to_edit', array( &$this, 'terms_attachment_fields_to_edit' ), 9, 2 );
		add_filter( 'attachment_fields_to_save', array( &$this, 'terms_attachment_fields_to_save' ), 9, 2 );
	}

	public function init()
	{
		foreach ( gThemeOptions::info( 'images', array() ) as $name => $size )
			self::registerImageSize( $name, $size );
	}

	// FIXME: DEPRECATED
	// this must be wp core feature!
	// core duplication with post_type : add_image_size()
	public static function addImageSize( $name, $width = 0, $height = 0, $crop = FALSE, $post_type = array( 'post' ) )
	{
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes[ $name ] = array(
			'width'     => absint( $width ),
			'height'    => absint( $height ),
			'crop'      => $crop,
			'post_type' => $post_type,
		);

		self::__dep( 'gThemeImage::registerImageSize()' );
	}

	public static function registerImageSize( $name, $atts = array() )
	{
		global $_wp_additional_image_sizes;

		$args = self::atts( array(
			'n' => __( 'Undefined Image Size', GTHEME_TEXTDOMAIN ),
			'w' => 0,
			'h' => 0,
			'c' => 0,
			'p' => array( 'post' ),
		), $atts );

		$_wp_additional_image_sizes[$name] = array(
			'width'     => absint( $args['w'] ),
			'height'    => absint( $args['h'] ),
			'crop'      => $args['c'],
			'post_type' => $args['p'],
			'title'     => $args['n'],
		);
	}

	public function intermediate_image_sizes_advanced( $sizes )
	{
		// removing standard image sizes
		unset(
			$sizes['thumbnail'],
			$sizes['medium'],
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

		$document = new DOMDocument();
		libxml_use_internal_errors( TRUE );

		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		$document->loadHTML( utf8_decode( $content ) );
		$images = $document->getElementsByTagName( 'img' );

		foreach ( $images as $image )
		   $image->setAttribute( 'class', 'img-responsive' ); // FIXME: this will replace all classes!!

		$html = $document->saveHTML();
		return $html;
	}

	public function wp_get_attachment_image_attributes( $attr, $attachment )
	{
		unset( $attr['title'] );
		$attr['class'] = $attr['class'].' '.gThemeOptions::info( 'image-class', 'the-img img-responsive' );
		return $attr;
	}

	public function get_image_tag_class( $class, $id, $align, $size )
	{
		return $class.' '.gThemeOptions::info( 'image-class', 'the-img img-responsive' );
	}

	public function strip_width_height( $html )
	{
		return preg_replace( '/(width|height)="\d*"\s/', '', $html );
	}

	public function image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt )
	{
		$html = '<figure id="post-'.$id.'-media-'.$id.'" class="align'.$align.'"'.( empty( $title ) ? '' : ' title="'.$title.'"' ).'>'.$html;

		if ( $caption )
			$html .= '<figcaption>'.$caption.'</figcaption>';

		return $html.'</figure>';
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
		if ( isset( $_REQUEST['post_id'] ) && $post_id = absint( $_REQUEST['post_id'] ) ) {
			$post = get_post( $post_id );
			$post_type = $post->post_type;

		} else if ( isset( $_REQUEST['post'] ) && $post_id = absint( $_REQUEST['post'] ) ) {
			$post = get_post( $post_id );
			$post_type = $post->post_type;

		} else if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];

		} else if ( $current = self::getCurrentPostType() ) {
			$post_type = $current;

		} else {
			$post_type = 'post';
		}

		$new_size_names = array();

		foreach ( gThemeOptions::info( 'images', array() ) as $name => $size )
			if ( $size['i'] && in_array( $post_type ,$size['p'] ) )
				$new_size_names[$name] = $size['n'];

		// if ( gThemeUtilities::isDev() )
		// 	error_log( print_r( compact( 'post_type', 'new_size_names', 'size_names' ), TRUE ) );

		return $new_size_names + $size_names;
	}

	public function tags_attachment_fields_to_edit( $fields, $post )
	{
		if ( ! $post_id = @ absint( $_REQUEST['post_id'] ) )
			return $fields;

		$post_type = get_post_type( $post_id );
		$images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE );
		if ( ! is_array( $images ) )
			$images = array();

		$html = $checked = '';
		$gtheme_images = (array) gThemeOptions::info( 'images', array() );

		foreach ( $gtheme_images as $name => $size ) {
			if ( $size['t'] && in_array( $post_type ,$size['p'] ) ) {
				$checked = ( isset( $images[$name] ) && $images[$name] == $post->ID ) ? ' checked="checked"' : '';
				$label = sprintf( _x( '%1$s (%2$s&nbsp;&times;&nbsp;%3$s)', 'Media Tag Checkbox Label', GTHEME_TEXTDOMAIN ), $size['n'], number_format_i18n( $size['w'] ), number_format_i18n( $size['h'] ) );
				$id = 'attachments-'.$post->ID.'-gtheme-size-'.$name;
				$html .= '<li><label for="'.$id.'"><input style="width:10px;vertical-align:bottom;" type="checkbox" value="'.$name.'" id="gtheme_size_'.$name.'" name="gtheme_size_'.$name.'" '.$checked.' /> '.esc_html( $label ).'</label></li>';
			}
		}

		if ( ! empty( $html ) ) {
			$html = '<ul style="margin:0;">'.$html.'</ul>'.
					'<input type="hidden" name="gtheme-image-sizes" value="modal" />'.
					'<input type="hidden" name="attachments['.$post->ID.']" value="dummy" />';

			$fields['gtheme_image_sizes'] = array(
				'label' => __( 'Media Tags', GTHEME_TEXTDOMAIN ),
				'input' => 'html',
				'html' => $html,
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

		$images = $striped = array();
		$sizes = (array) gThemeOptions::info( 'images', array() );
		$saved_images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE );
		if ( ! is_array( $saved_images ) )
			$saved_images = array();

		foreach ( $sizes as $name => $size )
			if ( isset( $_REQUEST['gtheme_size_'.$name] ) && $name == $_REQUEST['gtheme_size_'.$name] )
				$images[$name] = $post['ID'];

		foreach ( $saved_images as $saved_size => $saved_id )
			if ( $post['ID'] != $saved_id )
				$striped[$saved_size] = $saved_id;

		$final = array_merge( $striped, $images );

		if ( count( $final ) )
			update_post_meta( $post_id, GTHEME_IMAGES_META, $final );
		else
			delete_post_meta( $post_id, GTHEME_IMAGES_META );

		return $post;
	}

	public function terms_attachment_fields_to_edit( $form_fields, $post )
	{
		if ( ! $parent_id = @ absint( $_REQUEST['post_id'] ) ) {
			if ( empty ( $post->post_parent ) )
				return $form_fields;
			else
				$parent_id = $post->post_parent;
		}

		$post_type = get_post_type( $parent_id );
		if ( ! in_array( $post_type, gThemeOptions::info( 'support_images_terms', array() ) ) )
			return $form_fields;

		$saved_terms = get_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, TRUE );
		if ( ! is_array( $saved_terms ) )
			$saved_terms = array();
		$selected = array_search( $post->ID, $saved_terms );
		$dropdown = wp_dropdown_categories( array(
			'taxonomy'         => gThemeOptions::info( 'support_images_terms_taxonomy', 'category' ),
			'selected'         => ( FALSE === $selected ? 0 : $selected ),
			'show_option_none' => __( '&mdash; Select a Term &mdash;', GTHEME_TEXTDOMAIN ),
			'name'             => 'attachments['.$post->ID.'][gtheme_images_terms]',
			'id'               => 'attachments-'.$post->ID.'-gtheme_images_terms',
			'show_count'       => 0,
			'hide_empty'       => 0,
			'hierarchical'     => 1,
			'echo'             => 0,
		) );

		$form_fields['gtheme_images_terms']['tr'] = '<tr><th class="label" valign="top" scope="row"><label for="attachments-'.$post->ID.'-gtheme_images_terms"><span>'
			.__( 'Assign for', GTHEME_TEXTDOMAIN ).'</span></label></th><td class="field">'
			.$dropdown.'</td></tr>';

		return $form_fields;
	}

	public function terms_attachment_fields_to_save( $post, $attachment )
	{
		if ( ! $parent_id = absint( $_REQUEST['post_id'] ) ) {
			if ( empty ( $post['post_parent'] ) )
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

		if ( count( $thumb_ids ) )
			_prime_post_caches( $thumb_ids, FALSE, TRUE );

		$wp_query->thumbnails_cached = TRUE;
	}

	// ANCESTOR : gtheme_get_the_image(), gtheme_get_image_caption()
	public static function get_image( $atts = array() )
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
				if ( FALSE === $url )
					return $args['empty'];
				return $url;

			} else {

				do_action( 'begin_fetch_post_thumbnail_html', $args['post_id'], $args['post_thumbnail_id'], $args['tag'] );

				if ( in_the_loop() )
					self::update_cache( $args['tag'] );

				$html = wp_get_attachment_image( $args['post_thumbnail_id'], $args['tag'], FALSE, $args['attr'] );
				do_action( 'end_fetch_post_thumbnail_html', $args['post_id'], $args['post_thumbnail_id'], $args['tag'] );

				if ( FALSE !== $args['link'] ) {
					if ( is_array( $args['link'] ) ) {
						foreach ( $args['link'] as $link_size ) {
							$link_size_thumbnail_id = self::id( $link_size, $args['post_id'] );
							if ( $link_size_thumbnail_id ) {
								$attachment_post = get_post( $link_size_thumbnail_id );
								if ( ! is_null( $attachment_post ) ) {
									$args['link'] = get_attachment_link( $attachment_post );
									break;
								}
							}
						}
						if ( is_array( $args['link'] ) ) {// if not found
							$attachment_post = get_post( $args['post_thumbnail_id'] );
							$args['link'] = is_null( $attachment_post ) ? FALSE : get_attachment_link( $attachment_post );
						}
					} elseif ( 'parent' == $args['link'] ) {
						$args['link'] = get_permalink( $args['post_id'] );
					} elseif ( 'attachment' == $args['link'] ) {
						$attachment_post = get_post( $args['post_thumbnail_id'] );
						$args['link'] = is_null( $attachment_post ) ? FALSE : get_attachment_link( $attachment_post );
					} elseif ( 'url' == $args['link'] ) {
						$args['link'] = is_null( get_post( $args['post_thumbnail_id'] ) ) ? FALSE : wp_get_attachment_url( $args['post_thumbnail_id'] );
					}

					// last check
					if ( $args['link'] && $html ) // TODO: add template
						$html = '<a href="'.esc_url( $args['link'] ).'">'.$html.'</a>';
				}

				// TODO: use : gThemeAttachment::caption()
				if ( $args['caption'] ) {
					$caption = FALSE;
					if ( TRUE === $args['caption'] ) {
						if ( ! isset( $attachment_post ) )
							$attachment_post = get_post( $args['post_thumbnail_id'] );

						if ( ! is_null( $attachment_post ) ) {
							$caption = trim( $attachment_post->post_excerpt );
						} else {
							$caption = $args['default_caption'];
						}
					} else {
						$caption = $args['caption'];
					}

					if ( $caption && $html )
						$html = sprintf( gThemeOptions::info( 'template_image_caption',
							'<div class="%3$s">%1$s<p class="%4$s">%2$s</p></div>' ),
								$html,
								gThemeL10N::html( $caption ),
								'the-caption',
								'the-caption-text'
							);
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

		$html = self::get_image( $args );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		if ( ! empty( $html ) ) {
			echo $args['before'].$html.$args['after'];
		} else if ( ! empty( $args['empty'] ) ) {
			echo $args['before'].$args['empty'].$args['after'];
		}
	}

	// ANCESTOR: gtheme_empty_image()
	public static function holder( $tag = 'raw', $extra_class = '', $force = FALSE )
	{
		if ( ! $force && ! current_user_can( 'edit_others_posts' ) )
			return '';

		return '<div class="gtheme-image-holder holder-image-'
			.$tag.( gThemeUtilities::isDev() ? ' isdev ' : ' ' )
			.$extra_class
			.'"></div>';
	}

	public static function holderJS( $width = 100, $height = 100, $atts = array() )
	{
		return '<img class="'.gThemeOptions::info( 'image-class', 'the-img img-responsive' ).'" data-src="holder.js/'.$width.'x'.$height.'">';
	}
}
