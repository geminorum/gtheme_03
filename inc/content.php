<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeContent extends gThemeModuleCore 
{
	// http://www.billerickson.net/code/wp_query-arguments/
	public static function query( $args = array(), $expiration = GTHEME_CACHETTL ) 
	{
		if ( gThemeUtilities::is_dev() )
			return new WP_Query( $args );
	
		$key = 'gtq_'.md5( serialize( $args ) );

		if ( constant( 'GTHEME_FLUSH' ) )
			delete_transient( $key );
		
		if ( false === ( $query = get_transient( $key ) ) ) {
			 $query = new WP_Query( $args );
			 set_transient( $key, $query, $expiration );
		}
		
		return $query;
	}

	public static function content( $b = '<div class="entry-content">', $a = '</div>' )
	{
		echo $b;
		
		the_content( self::continue_reading( get_edit_post_link() ) );
		
		/**
		wp_link_pages( array( 
			'before' => '<div class="page-link"><span>'
				.__( 'Pages:', GTHEME_TEXTDOMAIN ).'</span>',
			'after' => '</div>',
		) );
		**/
		echo $a;
	}
	
	public static function date( $b = '<div class="entry-date">', $a = '</div>' )
	{
		echo $b;
		
		the_date( 'Y/j/m' );
		
		echo $a;
	}
	
	public static function continue_reading( $edit = '', $scope = '', $permalink = false, $title_att = false ) 
	{ 
		if ( ! empty( $edit ) ) 
			$edit = sprintf( __( ' <span class="sep edit-sep">|</span> <a href="%1$s" title="%2$s" class="%3$s">%4$s</a>', GTHEME_TEXTDOMAIN ),
				$edit,
				__( 'Jump to edit page', GTHEME_TEXTDOMAIN ),
				'post-edit-link',
				_x( 'Edit', 'continue reading link', GTHEME_TEXTDOMAIN )
			);
		
		if ( false === $permalink )
			$permalink = get_permalink();
			
		if ( false === $title_att )
			$title_att = get_the_title();
			
		return ' '.sprintf( 
			gtheme_get_info( 'read_more_title', __( '<a %1$s href="%2$s" title="Continue reading &ldquo;%3$s&rdquo; &hellip;" class="%4$s" >%5$s</a>%6$s', GTHEME_TEXTDOMAIN ) ),
			$scope,
			$permalink,
			$title_att,
			'excerpt-link',
			gtheme_get_info( 'read_more_text', __( 'Read more&nbsp;<span class="excerpt-link-arr">&rarr;</span>', GTHEME_TEXTDOMAIN ) ),
			$edit
		);
	}
	
	// ANCESTOR: gtheme_the_title_attribute()
	public static function title_attr( $echo = true, $title = null, $template = null, $empty = '' )
	{
		if ( is_null( $title ) )
			$title = get_the_title();
			
		if ( strlen( $title ) == 0 )
			return $empty;

		if ( is_null( $template ) )
			$title_attr = __( 'Permanent link to &mdash;%s&mdash;', GTHEME_TEXTDOMAIN );
		else if ( false === $template )
			$title_attr = __( 'Short link to &mdash;%s&mdash;', GTHEME_TEXTDOMAIN );
		else
			$title_attr = $template;
		
		$title_attr = esc_attr( sprintf( $title_attr, strip_tags( $title ) ) );
		
		if ( ! $echo ) 
			return $title_attr;
			
		echo $title_attr; 
	}
	
	// ANCESTOR: gtheme_the_content_restricted()
	public static function restricted( $stripteser = false, $restricted_message = '', $b = '<div class="restricted-content">', $a = '</div>' ) 
	{
		global $more;
		
		if ( apply_filters( 'gtheme_content_restricted', ! is_user_logged_in() ) ) {
			gThemeContent::teaser();
			if ( ! empty( $restricted_message ) )
				echo $b.$restricted_message.$a;
		} else {
			defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true ); // not caching the full article!
			the_content( null, $stripteser );
		}
	}
	
	// based on WP core : get_the_content()
	public static function teaser( $fallback = true, $echo = true )
	{
		global $more, $page, $pages;
		
		if ( post_password_required() )
			return get_the_password_form();
		
		if ( $page > count( $pages ) )
			$page = count( $pages );

		$content = $pages[$page-1];
		if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
			$content = explode( $matches[0], $content, 2 );
			$content = $content[0];
			
			if ( ! $more )
				$content = force_balance_tags( $content );
		
		} else if ( $fallback ) {
			$content = get_the_content();
		} else {
			return null;
		}
		
		$output = apply_filters( 'the_content', $content );
		$output = str_replace(']]>', ']]&gt;', $output );
		
		if( ! $echo )
			return $output;
		echo $output;
	}
	
	// ANCESTOR: gtheme_the_excerpt()
	public static function excerpt( $atts = 'itemprop="description" ', $b = '<div class="entry-summary">', $a = '</div>', $only = false, $excerpt_length = false ) 
	{
		if ( post_password_required() ) 
			return;
			
		$post = get_post();	
		
		if ( ! $post )
			return;
		
		if ( $only && empty( $post->post_excerpt ) )
			return;

		$excerpt = $post->post_excerpt;
			
		if ( $excerpt_length )
			// MIGHT be a problem since we bypass other filters too
			//$excerpt = apply_filters( 'the_excerpt', gThemeFilters::get_the_excerpt( gtheme_l10n( $post->post_excerpt ), $excerpt_length ) );
			$excerpt = apply_filters( 'the_excerpt', gTheme()->filters->get_the_excerpt( gtheme_l10n( $post->post_excerpt ), $excerpt_length ) );
		else
			$excerpt = apply_filters( 'the_excerpt', get_the_excerpt() );

		if ( ! empty( $atts ) )
			$excerpt = preg_replace( '/(<p\b[^><]*)>/i', '$1 '.$atts.'>', $excerpt ); // http://stackoverflow.com/a/3983870/642752
		
		echo $b.$excerpt.$a;
	} 
	
	public static function slug() 
	{ 
		global $post; 
		return get_post( $post )->post_name; 
	}
	
	public static function actions( $before = '<span class="post-action %s">', $after = '</span>', $action_list = true, $icons = 'def' ) 
	{
		if ( true === $action_list )
			$actions = gtheme_get_info( 'post_actions', array() );
		else if ( is_array( $action_list ) )
			$actions = $action_list;
		else
			$actions = array();
			
		if ( 'def' === $icons )
			$icons = gtheme_get_info( 'post_actions_icons', false );
		
		do_action( 'gtheme_action_links_before', $before, $after, $actions, $icons );
		
		foreach ( $actions as $action )
			self::do_actions( $action, $before, $after, $icons );
			
		do_action( 'gtheme_action_links', $before, $after, $actions, $icons );
	}

	public static function do_actions( $action, $before, $after, $icons = false )
	{
		switch ( $action ) {
			
			case 'textsize_buttons' :
			case 'textsize_buttons_nosep' :
				self::text_size_buttons( 
					sprintf( $before, 'textsize-buttons' ), $after, 
					( 'textsize_buttons_nosep' == $action ? false : 'def' ),
					( $icons ? '<div class="genericon genericon-zoom"></div>' : 'def' ),
					( $icons ? '<div class="genericon genericon-unzoom"></div>' : 'def' )
					);
			break;
			
			case 'textjustify_buttons' :
			case 'textjustify_buttons_nosep' :
				self::justify_buttons( 
					sprintf( $before, 'textjustify-buttons' ), $after, 
					( 'textjustify_buttons_nosep' == $action ? false : 'def' ),
					( $icons ? '<div class="genericon genericon-minimize"></div>' : 'def' ),
					( $icons ? '<div class="genericon genericon-previous"></div>' : 'def' )
					);
			break;
			
			case 'printfriendly' :
				self::printfriendly(
					sprintf( $before, 'printfriendly post-print-link' ), $after,
					( $icons ? '<div class="genericon genericon-print"></div>' : __( 'Print Version', GTHEME_TEXTDOMAIN ) )
				);
			break;
			
			case 'a2a_dd' :
				self::addtoany(
					sprintf( $before, 'addtoany post-share-link' ), $after,
					( $icons ? '<div class="genericon genericon-share"></div>' : __( 'Share This', GTHEME_TEXTDOMAIN ) )					
				);
			break;	

			case 'shortlink' : 
				the_shortlink( 
					( $icons ? '<div class="genericon genericon-link"></div>' : __( 'Short Link', GTHEME_TEXTDOMAIN ) ), 
					self::title_attr( false, null, '%s' ), 
					sprintf( $before, 'post-short-link' ),
					$after 
				);
			break;		   
			
			case 'comments_link' : 
			case 'comments_link_feed' : 
				if ( comments_open() ) {
					printf( $before, 'comments-link' );
					
					// if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) )
					/**
						comments_popup_link( 
							__( 'Leave a comment', 'mytheme' ),
							__( '1 Comment', 'mytheme' ),
							__( '% Comments', 'mytheme' )
						); 
					*/
					
					if ( is_singular() ) {
						$link = '#respond';
						$class = 'scroll1';
					} else {
						$link = get_permalink().'#respond';
						$class = 'hastip1';
					}
					
					if ( $icons )
						printf( '<a href="%2$s" class="%1$s"><div class="genericon genericon-comment"></div></a>', $class, $link  );
					else
						comments_number( 
							sprintf( __( '<a href="%3$s" class="%1$s">Your Comment</a>', GTHEME_TEXTDOMAIN ), $class, '', $link ),
							sprintf( __( '<a href="%3$s" class="%1$s">One Comment</a>', GTHEME_TEXTDOMAIN ), $class, '', $link ),
							sprintf( __( '<a href="%3$s" class="%1$s">%2$s Comments</a>', GTHEME_TEXTDOMAIN ), $class, '%', $link )
						); 

					if ( 'comments_link_feed' == $action ) {
						if ( $icons )
							printf( '<a href="%2$s" class="%1$s"><div class="genericon genericon-feed"></div></a>', 'comments-link-rss', get_post_comments_feed_link() );
						else
							printf( __( ' <small><small>(<a href="%1$s" title="%2$s" class="%3$s"><abbr title="Really Simple Syndication">RSS</abbr></a>)</small></small>', GTHEME_TEXTDOMAIN ), 
								get_post_comments_feed_link(),
								__( 'Feed for this post\'s comments', GTHEME_TEXTDOMAIN ),
								'comments-link-rss'
							);
					}
					echo $after;
				}
			break;
			
			case 'edit_post_link' :
				edit_post_link( 
					( $icons ? '<div class="genericon genericon-edit"></div>' : __( 'Edit', GTHEME_TEXTDOMAIN ) ),
					sprintf( $before, 'post-edit-link post-edit-link-li' ),
					$after
				);
			break;
			
			case 'tag_list' :
				if ( is_object_in_taxonomy( get_post_type(), 'post_tag' ) ) {
					echo get_the_tag_list( sprintf( $before, 'tag-links' ).gtheme_get_info( 'before_tag_list', '' ), gThemeUtilities::sanitize_sep( 'def', 'term_sep' ), $after );
					/**
					$tag_list = get_the_tag_list( '', gThemeUtilities::sanitize_sep( 'def', 'term_sep' ) );
					if ( $tag_list )
						echo sprintf( $before, 'tags-links' ).$tag_list.$after;
					**/
				}
			break;
			case 'cat_list' :
				if ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
					echo sprintf( $before, 'cat-links' ).gtheme_get_info( 'before_cat_list', '' ).get_the_category_list( gThemeUtilities::sanitize_sep( 'def', 'term_sep' ) ).$after;
				}
			break;
		}
	}	

	// ALSO SEE : http://wp.tutsplus.com/tutorials/theme-development/creating-a-wordpress-post-text-size-changer-using-jquery/
	public static function text_size_buttons( $b = '', $a = '', $sep = 'def', $increase = 'def', $decrease = 'def' ) 
	{
		echo $b;
		
		echo '<a id="jfontsize-plus" class="fontsize-button increase-font" href="#" title="'.__( 'Increase font size', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $increase ? gtheme_get_info( 'text_size_increase', '[ A+ ]' ) : $increase ); 
		echo '</a>';
		
		if ( false !== $sep ) {
			echo '<a id="jfontsize-default" class="fontsize-button" href="#">';
			echo gThemeUtilities::sanitize_sep( $sep, 'text_size_sep' );
			echo '</a>';
		}
		
		echo '<a id="jfontsize-minus" class="fontsize-button decrease-font" href="#" title="'.__( 'Decrease font size', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $decrease ? gtheme_get_info( 'text_size_decrease', '[ A- ]' ) : $decrease ); 
		echo '</a>';
		
		echo $a;
	}
	
	public static function justify_buttons( $b = '', $a = '', $sep = 'def', $justify = 'def', $unjustify = 'def' ) 
	{
		echo $b;
		
		echo '<a id="text-justify" class="text-justify-button hidden" href="#" title="'.__( 'Justify paragraphs', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $justify ? gtheme_get_info( 'text_justify', 'Ju' ) : $justify ); 
		echo '</a>';
		
		if ( false !== $sep )
			echo gThemeUtilities::sanitize_sep( $sep, 'text_justify_sep' );
		
		echo '<a id="text-unjustify" class="text-justify-button" href="#" title="'.__( 'Un-justify paragraphs', GTHEME_TEXTDOMAIN ).'">';
			echo ( 'def' == $unjustify ? gtheme_get_info( 'text_unjustify', 'uJ' ) : $unjustify ); 
		echo '</a>';
		
		echo $a;
	}
	
	// http://www.printfriendly.com/button
	public static function printfriendly( $b = '', $a = '', $text = null, $footer = true, $analytics = true ) 
	{
		if ( $footer && is_singular() )
			add_action( 'wp_footer', array( __CLASS__, 'printfriendly_footer' ) );
	
		$query_args = array(
			'url' => urlencode( get_permalink() ),
			//'headerImageUrl' => '',
			//'headerTagline' => '',
			//'pfCustomCSS' => '',
			'imageDisplayStyle' => 'block',
			//'disableClickToDel' => '',
			//'disablePDF' => '',
			//'disablePrint' => '',
			//'disableEmail' => '',
			//'hideImages' => '',
		);
			
		$onclick = 'onclick="window.print(); return false;"';
		$title_var = "NULL";
		$analytics_code = "if(typeof(_gaq) != 'undefined') { _gaq.push(['_trackEvent','PRINTFRIENDLY', 'print', '".$title_var."']);}";
		if( $analytics )
			$onclick = 'onclick="window.print();'.$analytics_code.' return false;"';
			
		echo $b;
		printf( '<a href="%1$s" rel="nofollow" %3$s>%2$s</a>',
			add_query_arg( $query_args, 'http://www.printfriendly.com/print' ),
			( $text ? $text : __( 'Print Version', GTHEME_TEXTDOMAIN ) ),
			$onclick
		);
		echo $a;
	}
	
	// prints the PrintFriendly JavaScript, in the footer, and loads it asynchronously.
	public static function printfriendly_footer() 
	{
		?><script type="text/javascript">
		  (function() {
			var e = document.createElement('script'); e.type="text/javascript";
			if('https:' == document.location.protocol) {
			  js='https://pf-cdn.printfriendly.com/ssl/main.js';
			}
			else{
			  js='http://cdn.printfriendly.com/printfriendly.js';
			}
			e.src = js;
			document.getElementsByTagName('head')[0].appendChild(e);
	  	  })();
	  </script><?php
	}	
	
	// http://www.addtoany.com/buttons/for/website
	public static function addtoany( $b = '', $a = '', $text = null, $footer = true )
	{
		if ( $footer && is_singular() )
			add_action( 'wp_footer', array( __CLASS__, 'addtoany_footer' ) );
	
		$query_args = array(
			'linkurl' => urlencode( get_permalink() ),
			'linkname' => self::title_attr( false, null, '%s' ),
		);	
	
		echo $b;
		printf( '<a href="%1$s" rel="nofollow" title="%3$s">%2$s</a>',
			add_query_arg( $query_args, 'http://www.addtoany.com/share_save' ),
			( $text ? $text : __( 'Share This', GTHEME_TEXTDOMAIN ) ),
			__( 'Share This with your friends.', GTHEME_TEXTDOMAIN )
		);
		echo $a;
	}
	
	public static function addtoany_footer() 
	{
		?><script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.linkname = '<?php echo esc_js( esc_url_raw( get_permalink() ) ); ?>';
a2a_config.linkurl = '<?php echo esc_js( self::title_attr( false, null, '%s' ) ); ?>';
a2a_config.onclick = 1;
a2a_config.locale = "fa";
</script>
<script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script><?php
	}	
	
	// ANCESTOR : gtheme_post_header()
	public static function header( $atts = array() )
	{
		$args = self::atts( array(
			'context' => 'single',
			'prefix' => 'entry',
			'actions' => false,
			'shortlink' => false,
			'title_tag' => 'h2',
			'meta_tag' => 'h4',
			
			'title' => null,
			'meta' => true,
			'link' => true, // disable linking compeletly
		), $atts );
	
		if ( is_null( $args['title'] ) )
			$args['title'] = get_the_title();
		
		if ( strlen( $args['title'] ) == 0 ) 
			return;
		
		if ( $args['link'] ) {
			if ( false === $args['shortlink'] ) 
				$link = get_permalink();
			else if ( true === $args['shortlink'] )
				$link = wp_get_shortlink( 0, 'query' );
			else
				$link = $args['shortlink'];
		}
		
		if ( $args['meta'] )
			$args['meta'] = gThemeOptions::supports( 'geditorial-meta', true );
		
		echo '<header class="header-class header-'.$args['context'].' '.$args['prefix'].'-header">';
		echo '<div class="titles-class '.$args['prefix'].'-titles">';
		
		if ( $args['meta'] )
			gmeta( 'over-title', '<'.$args['meta_tag'].' itemprop="alternativeHeadline" class="overtitle '.$args['prefix'].'-overtitle">', '</'.$args['meta_tag'].'>' ); 
		
		echo '<'.$args['title_tag'].' itemprop="headline" class="title '.$args['prefix'].'-title">';
		
		if ( $args['link'] ) {
			echo '<a itemprop="url" rel="bookmark" href="'.$link.'" title="'; 
				self::title_attr( true, $args['title'] );
			echo '">'.$args['title'].'</a>';
		} else {
			echo $args['title'];
		}
		
		echo '</'.$args['title_tag'].'>';
		
		if ( $args['meta'] )
			gmeta( 'sub-title', '<'.$args['meta_tag'].' itemprop="alternativeHeadline" class="subtitle '.$args['prefix'].'-subtitle">', '</'.$args['meta_tag'].'>' ); 
			
		echo '</div>';
		
		if ( $args['actions'] ) {
			echo '<ul class="list-inline actions-class actions-'.$args['context'].' '.$args['prefix'].'-actions">';
				self::actions( '<li class="post-action %s">', '</li>', $args['actions'] );
			echo '</ul>';
		}
		
		echo '</header>';
	}
	
	//////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	/////READ TIME////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	// https://gist.github.com/norcross/d10e26839699f61c00b7
	//////////////////////////////////////////////////////////////////
	// !! working but need adjustment with post actions
	
	// handle the calculation
	public static function calc_read_time( $seconds = 0 ) 
	{
		$minutes = floor( $seconds / 60 );
	 
		if ( $minutes < 1 )
			return __( 'less than 1 minute', GTHEME_TEXTDOMAIN );
		
		return sprintf( _n( '%d minute', '%d minutes', $minutes, GTHEME_TEXTDOMAIN ), $minutes );
	}
 
	// display the estimated time to read the content
	public static function display_read_time( $content ) 
	{
		global $post;

		$seconds = get_post_meta( $post->ID, '_seconds_read_time', true );

		if ( empty( $seconds ) )
			return $content;

		$readtime = self::calc_read_time( $seconds );

		// create a prefix
		$readprfx = __( 'Estimated read time:', GTHEME_TEXTDOMAIN );

		// make a fancy box
		$readbox = '<p class="estimated-read-time"><strong>'.esc_attr( $readprfx ).'</strong> '.esc_attr( $readtime ).'</p>';

		// send it back before the content
		return $readbox.$content;

	}
	// add_filter( 'the_content', 'rkv_display_read_time', 10 );

	// store the estimated time to read the content
	public static function store_read_time( $post_id = 0 ) 
	{
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( get_post_type( $post_id ) != 'post' )
			return $post_id;

		$content = get_post_field( 'post_content', $post_id, 'raw' );
		$wordnum = str_word_count( strip_tags( $content ) );
		$avgtime = apply_filters( 'gtheme_estimated_reading_time', 120 );
		$seconds = floor( (int) $wordnum / (int) $avgtime ) * 60;
		update_post_meta( $post_id, '_seconds_read_time', $seconds );
		
		return $post_id;
	}
	// add_action( 'save_post', 'rkv_store_read_time' );
}
