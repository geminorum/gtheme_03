<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidgetCustomHTML extends gThemeWidget
{

	public static function setup()
	{
		return [
			'name'  => 'custom_html',
			'class' => 'custom-html',
			'title' => _x( 'Theme: Custom HTML', 'Widget: Title', 'gtheme' ),
			'desc'  => _x( 'Displays arbitrary HTML code with support for shortcodes and embeds.', 'Widget: Description', 'gtheme' ),
		];
	}

	public function widget_html( $args, $instance )
	{
		global $wp_embed;

		if ( ! $content = trim( $instance['content'] ) )
			return FALSE;

		if ( ! empty( $instance['embeds'] ) ) {
			$content = $wp_embed->run_shortcode( $content );
			$content = $wp_embed->autoembed( $content );
		}

		if ( ! empty( $instance['shortcodes'] ) )
			$content = do_shortcode( $content );

		if ( ! empty( $instance['legacy'] ) )
			$content = apply_filters( 'widget_text', $content, $instance, $this );

		if ( ! empty( $instance['filters'] ) )
			$content = apply_filters( 'widget_custom_html_content', $content, $instance, $this );

		if ( ! $content )
			return FALSE;

		if ( ! empty( $instance['autop'] ) )
			$content = wpautop( $content );

		$this->before_widget( $args, $instance );
		$this->widget_title( $args, $instance );
		echo '<div class="textwidget custom-html-widget">';
			echo $content;
		echo '</div>';
		$this->after_widget( $args, $instance );

		return TRUE;
	}

	public function update( $new, $old )
	{
		$instance = $old;

		$instance['title']      = sanitize_text_field( $new['title'] );
		$instance['title_link'] = strip_tags( $new['title_link'] );
		$instance['class']      = strip_tags( $new['class'] );

		if ( current_user_can( 'unfiltered_html' ) )
			$instance['content'] = $new['content'];
		else
			$instance['content'] = wp_kses_post( $new['content'] );

		$instance['embeds']     = isset( $new['embeds'] );
		$instance['shortcodes'] = isset( $new['shortcodes'] );
		$instance['filters']    = isset( $new['filters'] );
		$instance['legacy']     = isset( $new['legacy'] );
		$instance['autop']      = isset( $new['autop'] );

		$this->flush_widget_cache();

		return $instance;
	}

	public function form( $instance )
	{
		$this->before_form( $instance );

		$this->form_title( $instance );
		$this->form_title_link( $instance );
		$this->form_class( $instance );

		$this->form_content( $instance );

		echo '<div class="-group">';

		$this->form_checkbox( $instance, FALSE, 'embeds', _x( 'Process Embeds', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, FALSE, 'shortcodes', _x( 'Process Shortcodes', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, FALSE, 'filters', _x( 'Process Filters', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, FALSE, 'legacy', _x( 'Process Filters (Legacy)', 'Widget: Setting', 'gtheme' ) );
		$this->form_checkbox( $instance, FALSE, 'autop', _x( 'Automatic Paragraphs', 'Widget: Setting', 'gtheme' ) );

		echo '</div>';

		$this->after_form( $instance );
	}
}
