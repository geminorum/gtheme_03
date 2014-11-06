<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeOptions extends gThemeModuleCore {

	public static function defaults( $option = false, $default = false ){
		$defaults = array(
			'name' => 'gtheme',
			'title' => _x( 'gTheme', 'Theme Title', GTHEME_TEXTDOMAIN ),
			'sub_title' => false, //'gTheme Child',
			'menu_title' => _x( 'Theme Settings', 'Admin Menu Title', GTHEME_TEXTDOMAIN ),
			'settings_title' => _x( 'gTheme Settings', 'Admin Settings Page Title', GTHEME_TEXTDOMAIN ),
			'settings_page' => 'gtheme-theme',
			'settings_access' => 'edit_theme_options',
			
			// INTEGRATION WITH OTHER PLUGINS
			'supports' => array( // 3th party plugin supports
				'gmeta' => false,
				'geditorial-meta' => false,
				'gshop' => false,
				'gpeople' => false,
				'gpersiandate' => true,
				'gbook' => false,
				'query-multiple-taxonomies' => false,
			),
			
			'module_args' => array(
				
			),
			
			// NAVIGATION & MENUS
			'register_nav_menus' => array(
				'primary' => __( 'Primary Navigation', GTHEME_TEXTDOMAIN ),
				'secondary' => __( 'Secondary Navigation', GTHEME_TEXTDOMAIN ),
				'tertiary' => __( 'Tertiary Navigation', GTHEME_TEXTDOMAIN ),
			),
			'nav_menu_allowedtags' => array( 
				'p'
			),
			
			// SIDEBARS
			'sidebars' => array(
				'side-index' => _x( 'Index: Side', 'Sidebar Titles', GTHEME_TEXTDOMAIN ),
				'side-singular' => _x( 'Singular: Side', 'Sidebar Titles', GTHEME_TEXTDOMAIN ),
			),
		
			// MEDIA TAGS
			'images' => array(	// n-name, w-width, h-height, c-crop, d-description, p-for posts, t-media tag, i-insert
				'raw' => gThemeOptions::register_image( 
					_x( 'Raw', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					9999, 9999, 0,
					true, true
				),
				'single' => gThemeOptions::register_image( 
					_x( 'Single', 'Media Tag Titles', GTHEME_TEXTDOMAIN ),
					1000, 1000, 0,
					true, true
				),
			),
			
			// COUNTS API
			'counts' => array(
				'dashboard' => array(
					'title' => __( 'Dashboard', GTHEME_TEXTDOMAIN ),
					'desc' => __( 'Dashboard Count', GTHEME_TEXTDOMAIN ),
					'def' => 5,
				),
				'latest' => array(
					'title' => __( 'Latest Posts', GTHEME_TEXTDOMAIN ),
					'desc' => __( 'Latest Posts Count', GTHEME_TEXTDOMAIN ),
					'def' => 5,
				),
			),
			
			'default_sep' => ' ',
			'title_sep' => is_rtl()? ' &laquo; ' : ' &raquo; ',
			'nav_sep' => is_rtl() ? ' &raquo; ' : ' &laquo; ',
			'byline_sep' => ' | ',
			'term_sep' => ', ',
			'comment_action_sep' => ' | ',
			
			'text_size_increase' => '[ A+ ]',
			'text_size_decrease' => '[ A- ]',
			'text_size_sep' => ' / ',
			
			'text_justify' => '[ Ju ]',
			'text_unjustify' => '[ uJ ]',
			'text_justify_sep' => ' / ',
			
			'excerpt_length' => 40,
			'excerpt_more' => ' &hellip;',
			'trim_excerpt_characters' => false, // set this to desired characters count. like : 300
			
			// comment to use default
			//'read_more_text' => '&hellip;',
			//'read_more_title' => '<a %1$s href="%2$s" title="Continue reading &ldquo;%3$s&rdquo; &hellip;" class="%4$s" >%5$s</a>%6$s',
			
			'rtl' => is_rtl(),
			'locale' => get_locale(),
			
			// FEEDS
			'feed_str_replace' => array( // TODO: make ltr compatible
				'<p>' => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
				'<p style="text-align: right;">' => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
				'<blockquote>' => '<blockquote style="direction:rtl;float:left;width:45%;maegin:20px 20px 20px 0;font-family:tahoma;line-height:22px;font-weight:bold;font-size:14px !important;">',
				'class="alignleft"' => 'style="float:left;margin-right:15px;"',
				'class="alignright"' => 'style="float:right;margin-left:15px;"',
				'class="aligncenter"' => 'style="margin-left:auto;margin-right:auto;text-align:center;"',
				'<h3>' => '<h3 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h4>' => '<h4 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h5>' => '<h5 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<h6>' => '<h6 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
				'<div class="lead">' => '<div style="color:#ccc;">',
				'<div class="label">' => '<div style="float:left;color:#333;">',
			),
			'enclosure_image_size' => 'single',
			
			// SEO
			'meta_image_size' => 'single',
			'rel_publisher' => false,
			'twitter_site' => false,
			'googlecse_cx' => false,
			
			'blog_title' => gtheme_get_option( 'blog_title', get_bloginfo( 'name' ) ), // used on page title other than frontpage
			'frontpage_title' => gtheme_get_option( 'frontpage_title', get_bloginfo( 'name' ) ), // set false to disable
			'frontpage_desc' => gtheme_get_option( 'frontpage_desc', get_bloginfo( 'description' ) ), // set false to disable
			
			'default_image_src' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAgAAAAKXCAMAAADDxQIzAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAAuUAAALlAF37bb0AAAAGXRFWHRTb2Z0d2FyZQB3d3cuaW5rc2NhcGUub3Jnm+48GgAAAvdQTFRF////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAVynFdwAAAPx0Uk5TAAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmJygpKissLS4vMDEyMzQ1Njc4OTo7PD0+P0BBQkNERUZHSElKS0xNTk9QUVJTVFVXWFlaW1xdXl9gYWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXp7fH1+f4CBgoOEhYaHiImKi4yNjo+QkZOUlZaXmJmam5ydnp+goaKjpKWmp6ipqqusra+wsbKztLW2t7i5uru8vb6/wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2Nna29zd3t/g4eLj5OXm5+jp6uvs7e7v8PHy8/T19vf4+fr7/P3+n9+fcQAAES1JREFUeNrt3XmcjfUewPHnHMYkTOMalBoyWUKYsVTWRqgmISVLom430pQW5Mptma6UVEpFrptWQpYrS6GiYlS02G5C04zINvYx+/njvnp1731+z/Mbcc55lt8zz+fz/znPb37ft+Msz3OOpllStTa3Pvneiswt2YeLQ2RjJUdytq5fMXf8bW3jNEUKth699Bcm43x7lo9pW8Ht6SfeuyCXUbjXkUUj6rk3/aqDV5YwA7cr/fROd/436PzWCXZfjfJmd3d8/GlfsO8q9XWfgIPTD9y8gS1Xrc0DHXtG2Pl7tlvFfrjOkfHXfIOtVrX5ifa/6h/Gyz6FOzEmxubX/Z+faQlF+WRjhWfa/01N7Jz/DYdOd9ycFVPSu7aonxCrka1VqnFx8y53v7g8q/Q0kzg5xLZjxzxX9kGLVrn5dpRfu2DY0vyyCbxRxZ4j1sks62jH5w2qzjRceiu27ztlPiPb2siOozXKko90eFoaj/muVvHql36V57K/jfVHarNfOkz+JP7tK1CVx47Lj8vdrD5Kd+kgJW/VZfPVqNYr0ouDgn7WHuLGAvMRlrdk49Wp4Tzp3+dfrLz/q8zPNzd0ZdPV6orVphEV32TdnScfNd53YXqAHVeuASdNT9FSrbrnpL3Gez6Yym6rWEq2cU5Hk62534Qdps8dk9hrNau91jipXy15fy6wzHivi6ux06oW+6ZxVuut+GhorPE+JwbZZ4UbbTxLc3L099jRcKJ//iD2WO16GJ+w9476CcBuw8c+3dhh1WtleDGQG+3TgPkGT+nsr/r1NXxmuzq6O0szzP81dtcLZRiGNjiauzrH8ApwdQyb64UChoftffFR3NXj4j39lMDeeqMq34lzeznyO0o6JZ5v2IKd9Ur1xM/uS1pFfD9zxUvQ+rCv3qmT+Pnwx5Hey6XimwqT2FUvNVL8T6BdhHcivq94II5N9VKVdgrDWxLZfVxcJNzHCPbUW/UXHwJSIrqLqcI97OAVoNdeC34ljG9eJPeQIJ4FdAs76rW6iC8EIvkE/17hDjLZT++1VBjg4xHcXnwI6cR2eq/mwou4neHfvIkw/0XsphcTr+PvGPatnxZu3ZTN9GKJwqkc08N+Epmj3/hb9tKbfSJ8n1y4l/C1FB4AMthKb3a/MMQuYd72wajfRiDXqycM8e9h3naxftNsdtKrfaNP8fPwbllBOLVwChvp1YTzOQrD+9aIK4QHD04E9WziM7nwvkPuYeFLIPgYwLvt0uc4IdL3EGaxjd5tsj7HhWHdcJ1+w/5so3cTPhHaFtYNhe8easw2erd44VlgxTBuVyvC25FqCWeHhvPVYZ0ifeQgxRK+2bVnGDcbot9sAZvo5V7XJ3l/GDe7j7OBy0nj9Ek+FsbNhO8EGMcmerl0fZLPhXGzpzgduJw0OLJLe1/Ub3YHm+jl+kT2jt4M/WZ92UQv1034cqcwbjZLv1kam+jl2kV2ieB7kX6IRIp1pT7JTwEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAAIAAYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOW+dvokPwaAD+utT3IOAHxYuj7JyQDwYU/rkxwNAB/2tj7JgQDwX8EcfZKpAPBf1+iDDDUEgP+apQ9yf0UA+K4L8/RBTtUA4LuWhyJ7CgCA8tHdwvz3BAHgt1JPCgBe0gDgs64TngCESlMA4K8qjSoQ5h+aoQHAV/XcLo4/lJsAAD/V7KOQseEaANQvNuF8K2r9xEbT+EMbggBQtgoNbhg5/dNNWYcKQ3a19xINAEpWrcfzGwtCdne4hQYABWs9fl1RyIHyOmoAUK6L/rol5Ewn0jQAqPbf/oCVJQ6NP7S1qQYAxd6iuWtnyLFmV9UAoFSVR+Q4N/4DQyNcJQDsauAe58afNyFOA4BSNV7l3PiPvnJh5AsFgC2P/uMLHBv/miHnRrNUANhQ8vY/nllRbnaWFe1aO/nm2lGuFQDWNzz/NIM/uW7mmN6X1jxHpcUCwOri5pY9/X0zep6j4HIBYHHNdpQ1/e2TOgTVXC8ArK19rjz9f49tou6CAWBpPfKk8e8ZWkHlFQPAyoZIH/ode7SK2ksGgIXdU2oaf+GUmqqvGQDW1c/8wd+cBuovGgCW1d307t/Bq72wagBYVZvjxvlvTtIA4KOS9hvnv7iaBgAfVekr4/wnBjUA+KnJhvHn3+aZhQPAknoZz86/UgOAr0o8JM7/UAMNAP5qpeHT/q4aAPxVf8N/AOkaAPxVtV/E+U/VAOCznhfn/0lFAPis5uJHgDtraADwWUvET3+baQDwWSnifwCDNAD4rfeF+X8dAIDfaiKeBNJVA4DfEr6mP7RMA4DfqlOsb2BJCwD4rtHCA8CbGgB81/f6/p2qCwDflSw8AEzSAOC7hHeB86oDwHcF9+rbt1ADgO9qLfwPcAcAfP0aoKQmAPzXMn33PtMA4LtiTui7NwoA/quj8BSgAQD811h987Y4e+Q6/Yb2rAQAtxM+CJrg5HETXvvta6hy7wSAy32pb157Bw/bYfd/D/oIANztqL55VR07aGD0/89BLE4CgJtdoO9djmMHrf4v4ZnnFAC4WRd971Y6dczLs8RTED8EgJvdoe/dyw4dcoTxW0jWAMDNHtD37kFHDhj3vulLiJ4BgJs96vAnQSnmLyEtbQEAN3tW37u+DhxumPQd1KN5GehqU/W9u9b2g1V91zz+0ic0ALjaOw6+D3TZNvP8D6ZpAHC3BfreJdt8qNtPmue/NlEDgMstdApA5delr6B+LkYDgF8ANN4k/UZwb8vuHADKAxhw3Dz/r+prAPALgNip0sP/lEoaAPwC4JKN0q8E3mLpAQCgNICbjpjn/21DDQB+ARDzovTwP93qXx4DgLoA6mWax3/C+q+gAYCyAG44ZJ7/Zht+fQwAigKoONH8A0ShN8/VAOAXABd+Jv1E/J9t+SMAoCSAa/ab5/9Dcw0AfgEQfNL8+2Oh2XaddgwA9QDUXmUef/5w2/4IACgHIHWvef47W2kA8AuAwLhi8/znn6cBwC8AEpaZx1/4gK1/BACUAtA+xzz/n6/QAOAbAKOkXx9f/CcNAH4BUH2RefxFD9v+9eMAUAZA25/M89/d0f4/AgCRN1/fOwvepruvwDz/D5344jEARN5T+nfERf0xTdw88/hLHnXk54cBEHm9rPuGoOQfzfP/9Wpn/ggARF5w7f+2rleU9zT0lHn+n5yvAUD5Gv5syVXaVd6RLvsbX0EDgAc6b1p26NT666O7k2ZbzfM/cK1zfwIAoiw+2n+rQ6TL/j6/SAOAX6r8T+nh/1lHf3x2ln7kNMbheI2+N88/t6ezK/iHs99yQYb6S5f9ra/n8BJe8PwvHni32Fel6z5equT0IjL0g49gJI6WtEG67O9m51cxSj/8OGbiZH2ky/42XuLCMoZ6/WfPPFrMZOnhf1qsGwvppy9gAWNxrLrSZX/HB7izksv1JWxjLk7VQ7rsb1Njl5YSL5x9WJHJOFLFZ6TL/l6v7NpqhGuQGjMbJ6qzxjz+k7e7uJwv9HX0ZzgO1F267G/bZW6uZ6a+kFlMx/aCGdJlf+9WdXVFDwvfPhfDgGyu9krz+E8Nc3lJVwqL6caE7O2qPeb5/5js+lPSY5b+BA2dvsAj0mV/8+LcX9YH+nKyGZKN1VhqHn/BfSqsa6SwohTGZFvtss3z/6mtEgtLEZaUwZxs+3cmXfa3qLoiL0x+Eb6GkkHZU/xC6bK/UQFVFif89E2oKbOyoza7zPPPaa/O6i4TH5YYlg3dK132tyxBpfWJZ6Z0YlxWV22uefzF4wJKrfB+YW2ZDMziWm43z39vqmJLrCU+Qt3CyCztLumyv1W1lVvkdGF5O/hAwMKqvC1d9Z0RVG+Z9cXXqJwcbF1Npcv+9ndXcqFviFcmxjE4ixosXfa3po6aK20kfkjN2cHWVHmGdNnfM8qedTdLXGYfhmfFP6rvzPM/1EPd1V6cJ/4wSQvGF3X9jpnnn1lX5fX+zfAxVQIDjK7YV6TrPiar/fIq1vB2xWpeC0b3supr8/iPKP//6jWG9b7GEKPoxsPm+W9IUn/Vxnes0xljpMW8ID38vxrrgXXXMJyxUsQJohGWuM48/mP9vLHyDoZzVvIHMctIuv6gef7fNfLK2scYFz4xyDjDrcLT0mV/Myp7ZvUB02mri6sx0fCqs1q67G+wl9afYPrG2s1JzDScuu0zz3+rx86xSzL9YNXBVKZ61gWfkC77e7uK1/6IlqYvrilMDzDZs6vWCumyv7s8+GdcZT6BZUNXZns2dZYu+9ve0pN/SG/pDNblLRnvGZ8/j5Uu+5vj1afQ3aVvryx5qy4j/sNqLJEu+/PwW6ltD0hvZeZPqs6UT5982d+uNl7+explSQJCh6elxTLpsnuo0LxbC+I9/n7G+lAZHZ83iMcBufgF0o+9PuT5P6qMD7R+/4ho1Yh6jNxQa+myv+x25eHv6pUbOk05K6akd21RP4H/EX4rXXrRtKRG+fjL6q4NnaGifMqXLvsbW27eOQsOzw1RmO3pXJ4e3mrOLGWkYbWiVjn7H67DNwz17Ct5vPydQRHo/SWDPcv2ldOz6LqvZrZn0+oLyu1LnQ4zjzPfM1Q6oYJWjjv31g9LGPIfdLD8/+hinXveP8SgT9PaRF+86xVIGfnBbqYtP/w/76cr6aq2Gpgx+6PMLdmHixn9b9PPmpPsjcn9B9oKe30MDUO4AAAAAElFTkSuQmCCdf5680aaefbd59e0479ffe13f7626904',
			
			'copyright' => gtheme_get_option( 'copyright', __( '&copy; All right reserved.', GTHEME_TEXTDOMAIN ) ),
			
			// COMMENTS
			'comment_callback' => array( 'gThemeComments', 'comment_callback' ), // null to use wp core
			'comment_form' => array( 'gThemeComments', 'comment_form' ), // comment_form to use wp core
			'comment_form_defaults' => array(
				'title_reply' => __( 'Leave a Reply' ),
				'title_reply_to' => __( 'Leave a Reply to %s' ),
				'cancel_reply_link' => __( 'Cancel reply' ),
				'label_submit' => __( 'Post Comment' ),
			),
			//'comment_form_reply' => __( 'Reply', GTHEME_TEXTDOMAIN ),
			//'comments_closed' => __( 'Comments are closed.' , GTHEME_TEXTDOMAIN ), // set empty to hide the text
			'comment_avatar_size' => 50, // wp core is 32
			'default_avatar_src' => GTHEME_URL.'/images/default_avatar.png',
			
			// SYSTEM TAGS
			'system_tags_cpt' => array( 'post' ),
			'system_tags_defaults' => array( 
				'dashboard' => _x( 'Dashboard', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'latest' => _x( 'Latest', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
				'no-front' => _x( 'No FrontPage', 'System Tags Defaults', GTHEME_TEXTDOMAIN ),
			),
			
			// EDITOR
			'teeny_mce_buttons' => array(),
			'mce_buttons' => array( 'sup', 'sub', 'hr' ),
			'mce_buttons_2' => array( 'styleselect' ),
			'mce_advanced_styles' => array( 
				__( 'Warning', GTHEME_TEXTDOMAIN ) => 'warning',
				__( 'Notice', GTHEME_TEXTDOMAIN ) => 'notice',
				__( 'Download', GTHEME_TEXTDOMAIN ) => 'download',
				__( 'Testimonial', GTHEME_TEXTDOMAIN ) => 'testimonial box',
			),
			//'mce_style_formats' => array();
			
			'settings_legend' => false, // html content on appear after settings
			
			
			'search_page' => gtheme_get_option( 'search_page', 0 ),
			
			// 'home_url_override' => '', // full escaped url to overrided home page / comment to disable
			// 'empty_search_query' => '', // string to use on search input form / comment to use default
			// 'the_date_format' => 'j M Y', // used on post by line
	
			'post_actions_icons' => false,
			'post_actions' => array( // the order is important!
				'textsize_buttons', // or 'textsize_buttons_nosep',
				'textjustify_buttons_nosep', // 'textjustify_buttons', // or ,
				'printfriendly',
				'a2a_dd',
				'shortlink',
				'comments_link_feed', // or 'comments_link',
				'edit_post_link',
				//'tag_list',
			),
			
			//'js_tooltipsy' => false, // enables tooltipsy
			'before_tag_list' => '', // string before tag list
			/**
			EXAMPE : it's working, just uncomment.
			'adjacent_links' => array( // define how the next prev link should appear on wp_head link rel. false to disable
				'entry' => array(
					'same_term' => true,
					'ex_terms' => '',
					'taxonomy' => 'section',
					'orderby' => 'menu_order',
				),
			),
			**/
			
			'wpautop_with_br' => false, // set true to disable extra br removing
			'adjacent_empty' => '[&hellip;]', // next/prev link, if empty post title
			'head_viewport' => 'width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no', // html head viewport meta, for mobile support. set false to disable
			
			'strings_index_navline' => array( // string for index navline based on conditional tags
				//'category' => 'Category Archives for <strong>%s</strong>',
			),
			
			'author_link_template' => '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			'default_editor' => 'html', // set default editor of post edit screen to html for each user // needs module arg // Either 'tinymce', or 'html', or 'test'
			
			'child_group_class' => false, // body class for goruping the child theme on a network!
			
			
			'banner_groups' => array(
				'first' => _x( 'First', 'Banner Groups', GTHEME_TEXTDOMAIN ),
				'second' => _x( 'Second', 'Banner Groups', GTHEME_TEXTDOMAIN ),
			),
			
			
		);
		if ( false === $option )
			return $defaults;
		if( isset( $defaults[$option] ) )
			return $defaults[$option];
		return $default;	
	}	
	
	public static function register_image( $n, $w, $h = 9999, $c = 0, $t = true, $i = false, $p = array( 'post' ), $d = '' )
	{
		return array( 
			'n' => $n, // name
			'w' => $w, // width
			'h' => $h, // height
			'c' => $c, // crop
			't' => $t, // media tag
			'i' => $i, // insert in post
			'p' => $p, // post_type
			'd' => $d, // description
		);
	}
	
	
}

function gtheme_get_info( $info = false, $default = false ){
	global $gtheme_info;
	if ( empty( $gtheme_info ) )
		$gtheme_info = apply_filters( 'gtheme_get_info', gThemeOptions::defaults() );
	
	if ( false === $info )
		return $gtheme_info;
	if( isset( $gtheme_info[$info] ) )
		return $gtheme_info[$info];
	return $default;	
}


function gtheme_get_option( $name, $default = false ) {
	global $gtheme_options;
	if ( empty(	$gtheme_options ) )
		$gtheme_options = get_option( constant( 'GTHEME' ) );
		
	if ( $gtheme_options === false ) 
		$gtheme_options = array();
		
	if ( !isset( $gtheme_options[$name] ) ) 
		//$gtheme_options[$name] = $default;
		return $default;
	
	return $gtheme_options[$name];
}

function gtheme_update_option( $name, $value ) {
	global $gtheme_options;
	if ( empty(	$gtheme_options ) )
		$gtheme_options = get_option( constant( 'GTHEME' ) );

	if ( $gtheme_options === false ) 
		$gtheme_options = array();
	
	//unset ( $gtheme_options[$name] );
	$gtheme_options[$name] = $value;
	
	return update_option( constant( 'GTHEME' ), $gtheme_options );
}

function gtheme_delete_option( $name ) {
	global $gtheme_options;
	if ( empty(	$gtheme_options ) )
		$gtheme_options = get_option( constant( 'GTHEME' ) );

	if ( $gtheme_options === false ) 
		$gtheme_options = array();
	
	unset( $gtheme_options[$name] );
	
	return update_option( constant( 'GTHEME' ), $gtheme_options );
}

function gtheme_get_count( $name, $def = 0 ){
	$option_counts = gtheme_get_option( 'counts', array() );
	if ( count( $option_counts ) && isset( $option_counts[$name] ) )
		return $option_counts[$name];
	
	$info_counts = gtheme_get_info( 'counts', array() );
	if ( count( $info_counts ) && isset( $info_counts[$name] )  )
		return $info_counts[$name]['def'];
		
	return $def;
}

function gtheme_supports( $plugins, $if_not_set = false ) {
	$supports = gtheme_get_info( 'supports', array() );
	
	if ( is_array( $plugins ) )
		foreach ( $plugins as $plugin )
			if ( isset( $supports[$plugin] ) )
				return $supports[$plugin];
	
	if ( isset( $supports[$plugins] ) )
		return $supports[$plugins];
		
	return $if_not_set;
}

function gtheme_get_banner( $group, $order = 0 ) {
	$banners = gtheme_get_option( 'banners', array() );
	foreach ( $banners as $banner ) {
		if ( isset( $banner['group'] ) && $group == $banner['group'] ) {
			if ( isset( $banner['order'] ) && $order == $banner['order'] ) {
				return $banner;
			}
		}
	}
	return false;
}

function gtheme_banner( $banner, $atts = array() ){
	//if ( false === $banner ) return;

	$args = shortcode_atts( array(
		'w' => 'auto',
		'h' => 'auto',
		'c' => '#fff',
		'img_class' => 'img-responsive',
		'a_class' => 'gtheme-banner',
		'img_style' => '',
		'a_style' => '',
		'placeholder' => true,
	), $atts );
		
	$html = '';
	$title = isset( $banner['title'] ) && $banner['title'] ? $banner['title'] : '' ;
	
	if ( isset( $banner['image'] ) && $banner['image'] && 'http://' != $banner['image'] )
		$html .= '<img src="'.$banner['image'].'" alt="'.$title.'" class="'.$args['img_class'].'" style="'.$args['img_style'].'" />';
	else if ( $args['placeholder'] )
		$html .= '<div style="display:block;width:'.$args['w'].';height:'.$args['h'].';background-color:'.$args['c'].';" ></div>';
		
	if ( isset( $banner['url'] ) && $banner['url'] && 'http://' != $banner['url'] )
		$html = '<a href="'.$banner['url'].'" title="'.$title.'" class="'.$args['a_class'].'" style="'.$args['a_style'].'">'.$html.'</a>';

	if ( ! empty ( $html ) )
		echo $html;
}
