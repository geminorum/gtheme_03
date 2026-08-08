<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeFeatures extends gThemeModuleCore
{
	public function setup_actions( $settings = [], $childless = NULL )
	{
		$args = self::atts( [
			'check_actions'      => $childless,
			'insert_intros'      => TRUE,
			'insert_toc'         => $childless,
			'insert_embed'       => $childless,
			'insert_media'       => $childless,
			'insert_people'      => TRUE,
			'insert_action'      => TRUE,
			'insert_attachments' => FALSE,
			'insert_source'      => TRUE,
			'insert_likes'       => TRUE,
			'insert_reflist'     => TRUE,
			'insert_navigation'  => TRUE,
			'insert_mainpost'    => TRUE,
			'insert_supported'   => TRUE,
		], $settings );

		if ( $args['check_actions'] )
			add_filter( 'gtheme_content_actions', [ $this, 'check_content_actions' ], 99, 3 );

		add_action( 'template_redirect',
			function () use ( $args ) {

				if ( ! is_singular() )
					return;

				if ( $args['insert_intros'] )
					add_action( 'gtheme_content_wrap_before',
						[ $this, 'render_editorial_intros' ], 14 );

				if ( $args['insert_toc'] )
					add_action( 'gtheme_content_before',
						[ $this, 'render_editorial_toc' ], 20 );

				if ( $args['insert_embed'] )
					// add_action( 'gtheme_content_before',
					add_action( 'gtheme_content_image_wrap_after',
						[ $this, 'render_editorial_embed' ], 50 );

				if ( $args['insert_media'] ) {
					add_action( 'gtheme_content_before',
						[ $this, 'render_editorial_media_before' ], 80 );
					add_action( 'gtheme_content_after',
						[ $this, 'render_editorial_media_after' ], 8 );
				}

				if ( $args['insert_people'] )
					add_action( 'gtheme_content_before',
						[ $this, 'render_people_image' ], 99 );

				if ( $args['insert_action'] )
					add_action( 'gtheme_sidebar_wrap_before',
						[ $this, 'render_editorial_action' ], 14 );

				if ( $args['insert_attachments'] )
					add_action( 'gtheme_content_wrap_after',
						[ $this, 'render_editorial_attachments' ], 16 );

				if ( $args['insert_source'] )
					add_action( 'gtheme_content_wrap_after',
						[ $this, 'render_editorial_source' ], 18 );

				if ( $args['insert_likes'] )
					add_action( 'gtheme_content_wrap_after',
						[ $this, 'render_editorial_likes' ], 22 );

				if ( $args['insert_reflist'] )
					add_action( 'gtheme_content_wrap_after',
						[ $this, 'render_editorial_reflist' ], 88 );

				if ( $args['insert_navigation'] )
					add_action( 'gtheme_content_wrap_after',
						[ $this, 'render_content_navigation' ], 99 );

				if ( $args['insert_mainpost'] )
					// add_action( 'gtheme_content_wrap_after',
					add_action( 'gtheme_sidebar_wrap_before',
						[ $this, 'render_editorial_for_mainpost' ], 55 );

				if ( $args['insert_supported'] )
					add_action( 'gtheme_content_wrap_after',
						[ $this, 'render_editorial_for_supported' ], 99 );
			} );
	}

	public function check_content_actions( null|false|array $actions, object $post, bool $icon ): null|false|array
	{
		switch ( $post->post_type ) {

			case 'publication':
				return FALSE;
		}

		return $actions;
	}

	public function render_editorial_toc()
	{
		if ( ! gThemeUtilities::isPrint()
			&& is_singular( gThemeOptions::info( 'headings_posttypes', [ 'entry', 'lesson' ] ) ) )
				gThemeEditorial::headingsTOC();
	}

	public function render_editorial_embed()
	{
		if ( $embed = gThemeEditorial::getMeta( 'content_embed_url', [ 'fallback' => 'video_embed_url' ] ) )
			echo gThemeHTML::wrap( $embed, '-embed' );
	}

	public function render_editorial_media_before()
	{
		if ( $video = gThemeEditorial::getMeta( 'video_source_url' ) )
			echo gThemeHTML::wrap( $video, '-video -video-source' );

		if ( $audio = gThemeEditorial::getMeta( 'audio_source_url' ) )
			echo gThemeHTML::wrap( $audio, '-audio -audio-source' );
	}

	public function render_editorial_media_after()
	{
		if ( $text = gThemeEditorial::getMeta( 'text_source_url' ) )
			echo gThemeHTML::wrap( $text, '-text -text-source' );
	}

	public function render_editorial_action( $sidebar = '' )
	{
		if ( ! in_array( $sidebar, [
			'entry-side',
		], TRUE ) )
			return;

		gThemeEditorial::theAction( [
			'before'     => '<div class="entry-after after-single after-action d-grid gap-2">',
			'after'      => '</div>',
			'link_class' => 'btn btn-outline-primary btn-lg btn-block', // `btn-block` is for BS-4
			'span_class' => 'btn btn-outline-primary btn-lg btn-block disabled',
		] );
	}

	public function render_editorial_source()
	{
		gThemeEditorial::theSource( [
			'before' => '<div class="entry-after after-single after-source text-end">'.
				gThemeOptions::info( 'source_before', '' ),
			'after'  => '</div>',
		] );
	}

	public function render_editorial_likes()
	{
		gThemeEditorial::postLikeButton( [
			'before' => '<div class="entry-after after-single after-like my-2">',
			'after'  => '</div>',
		] );
	}

	public function render_editorial_reflist()
	{
		gThemeEditorial::refList( [
			'before' => '<div class="entry-after after-single after-reflist  my-2">',
			'after'  => '</div>',
			'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
			'wrap'   => FALSE,
		] );
	}

	public function render_content_navigation()
	{
		// `gThemeContent::navigation();`
		gThemeContent::navigationFancy();
	}

	public function render_editorial_attachments()
	{
		gThemeEditorial::listAttachments( [
			'title'     => gThemeOptions::info( 'attachments_title', FALSE ),
			'mime_type' => gThemeOptions::info( 'attachments_mimetypes', FALSE ),
			'before'    => '<div class="clearfix"></div><div class="entry-after after-attachments after-rows">',
			'after'     => '</div>',
			'wrap'      => FALSE,
		] );
	}

	public function render_people_image()
	{
		if ( gThemeTerms::has( 'insert-people' ) )
			gThemeEditorial::personPicture( [
				'before' => '<div class="entry-person">',
				'after'  => '</div>',
			] );
	}

	public function render_editorial_intros()
	{
		switch ( get_post_type() ) {

			case 'publication':

				gThemeEditorial::metaHTML( 'highlight', [
					'before'   => '<div class="entry-highlight text-bg-light mb-3 pt-3 px-4 pb-1">',
					'after'    => '</div>',
					'fallback' => 'cover_blurb',
				] );

				break;

			default:

				gThemeEditorial::metaHTML( 'lead', [
					'before'   => '<div class="entry-lead text-bg-light mb-3 pt-3 px-4 pb-1">',
					'after'    => '</div>',
					'fallback' => 'abstract',
				] );
		}
	}

	public function render_editorial_for_mainpost( $sidebar = '' )
	{
		if ( ! in_array( $sidebar, [
			'entry-side',
		], TRUE ) )
			return;

		switch ( get_post_type() ) {

			default:

				gThemeEditorial::publication( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-publication after-rows">',
					'after'  => '</div>',
					'title'  => FALSE,
				] );

				gThemeEditorial::addendumAppendages( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-appendages after-rows my-2 -print-hide">',
					'after'  => '</div>',
					'wrap'   => FALSE,
				] );

				gThemeEditorial::venuePlace( [
					'title'  => gThemeOptions::info( 'venue_title', FALSE ),
					'before' => '<div class="clearfix"></div><div class="entry-after after-venue-place after-rows my-2">',
					'after'  => '</div>',
					'wrap'   => FALSE,
				] );
		}
	}

	public function render_editorial_for_supported()
	{
		switch ( get_post_type() ) {

			case 'place':

				gThemeEditorial::venueMetaSummary( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-meta-summary venue-meta-summary after-rows">',
					'after'  => '</div>',
				], FALSE );

				break;

			case 'publication':

				gThemeEditorial::bookMetaSummary( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-meta-summary book-meta-summary after-rows">',
					'after'  => '</div>',
				], FALSE );

				break;

			case 'event':

				gThemeEditorial::happeningMetaSummary( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-meta-summary happening-meta-summary after-rows">',
					'after'  => '</div>',
				], FALSE );

				break;

			case 'issue':

				gThemeEditorial::magazineSupported( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-issue after-rows">',
					'after'  => '</div>',
					'wrap'   => FALSE,
					'title'  => FALSE,
					'future' => FALSE,
				] );

				break;

			case 'dossier':

				gThemeEditorial::dossierSupported( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-dossier after-rows">',
					'after'  => '</div>',
					'wrap'   => FALSE,
					'title'  => FALSE,
					'future' => FALSE,
				] );

				break;

			case 'course':

				gThemeEditorial::courseLessons( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-course-lessons after-rows">',
					'after'  => '</div>',
					'order'  => 'DESC',
					'wrap'   => FALSE,
					'title'  => FALSE,
					'future' => FALSE,
				] );

				break;
		}
	}
}
