<?php

/*
Plugin Name: V8CH Card Widget
Plugin URI: http://www.v8ch.com
Description: Widget for creating layout cards with icon image and optionally post excerpts.
Version: 0.1.1
Author: Robert Pratt
License: GPL3
License URI: https://opensource.org/licenses/GPL-3.0
Text Domain:
Domain Path:
*/

// Security check
defined( 'ABSPATH' ) or die( 'Fail on direct access' );

// Load widget
function v8ch_load_card_widget() {
	register_widget('V8CH_Card_Widget');
}
add_action('widgets_init', 'v8ch_load_card_widget');

// Setup automatic updates
require 'vendor/plugin-updates/plugin-update-checker.php';
$v8ch_card_widget_updates = PucFactory::buildUpdateChecker(
	'http://www.v8ch.com/update/v8ch-card-widget.json',
	__FILE__,
	'v8ch-card-widget'
);

class V8CH_Card_Widget extends WP_Widget {

	const VERSION = '0.1.1';

	const CUSTOM_IMAGE_SIZE_SLUG = 'v8ch_card_widget_custom';

	/**
	 * V8CH Image Card Widget constructor
	 *
	 * @author V8CH
	 */
	public function __construct() {
		// load_plugin_textdomain( 'v8ch_card_widget', false, trailingslashit( basename( dirname( __FILE__ ) ) ) . 'lang/' );
		$widget_ops  = array(
			'classname'   => 'widget_v8ch_card',
			'description' => __( 'A custom modification of the Modern Tribe Image Widget used to show a single image with a title, URL, description and posts from a selectable taxonomy', 'v8ch_card_widget' )
		);
		$control_ops = array( 'id_base' => 'widget_v8ch_card' );
		parent::__construct( 'widget_v8ch_card', __( 'V8CH Card Widget', 'v8ch_card_widget' ), $widget_ops, $control_ops );

		add_action( 'sidebar_admin_setup', array( $this, 'admin_setup' ) );
		add_action( 'admin_head-widgets.php', array( $this, 'admin_head' ) );
	}

	/**
	 * Enqueue all the javascript.
	 */
	public function admin_setup() {
		wp_enqueue_media();
		wp_enqueue_script( 'v8ch-card-widget', plugins_url('assets/js/v8ch-card-widget.js', __FILE__), array(
			'jquery',
			'media-upload',
			'media-views'
		), self::VERSION );

		wp_localize_script( 'v8ch-card-widget', 'V8CHCardWidget', array(
			'frame_title'  => __( 'Select an Image', 'v8ch_card_widget' ),
			'button_title' => __( 'Insert Into Widget', 'v8ch_card_widget' ),
		) );
	}

	private function tabs( $count ) {
		$i = 0;
		$tabs = "";
		while ( $i < $count ) {
			$tabs .= "\t";
			$i++;
		}
		return $tabs;
	}

	/**
	 * Widget frontend output
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @author Modern Tribe, Inc.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( (array) $instance, self::get_defaults() );
		if ( ! empty( $instance['imageurl'] ) || ! empty( $instance['attachment_id'] ) ) {

			$instance['title']         = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
			$instance['description']   = apply_filters( 'widget_text', $instance['description'], $args, $instance );
			$instance['post_type']     = apply_filters( 'v8ch_card_widget_post_type', $instance['post_type'], $args, $instance );
			$instance['tax_name']      = apply_filters( 'v8ch_card_widget_tax_name', $instance['tax_name'], $args, $instance );
			$instance['tax_slug']      = apply_filters( 'v8ch_card_widget_tax_slug', $instance['tax_slug'], $args, $instance );
			$instance['link']          = apply_filters( 'v8ch_card_widget_image_link', esc_url( $instance['link'] ), $args, $instance );
			$instance['linkid']        = apply_filters( 'v8ch_card_widget_image_link_id', esc_attr( $instance['linkid'] ), $args, $instance );
			$instance['linktarget']    = apply_filters( 'v8ch_card_widget_image_link_target', esc_attr( $instance['linktarget'] ), $args, $instance );
			$instance['width']         = apply_filters( 'v8ch_card_widget_image_width', abs( $instance['width'] ), $args, $instance );
			$instance['height']        = apply_filters( 'v8ch_card_widget_image_height', abs( $instance['height'] ), $args, $instance );
			$instance['maxwidth']      = apply_filters( 'v8ch_card_widget_image_maxwidth', esc_attr( $instance['maxwidth'] ), $args, $instance );
			$instance['maxheight']     = apply_filters( 'v8ch_card_widget_image_maxheight', esc_attr( $instance['maxheight'] ), $args, $instance );
			$instance['align']         = apply_filters( 'v8ch_card_widget_image_align', esc_attr( $instance['align'] ), $args, $instance );
			$instance['alt']           = apply_filters( 'v8ch_card_widget_image_alt', esc_attr( $instance['alt'] ), $args, $instance );
			$instance['rel']           = apply_filters( 'v8ch_card_widget_image_rel', esc_attr( $instance['rel'] ), $args, $instance );
			$instance['attachment_id'] = ( $instance['attachment_id'] > 0 ) ? $instance['attachment_id'] : $instance['image'];
			$instance['attachment_id'] = apply_filters( 'v8ch_card_widget_image_attachment_id', abs( $instance['attachment_id'] ), $args, $instance );
			$instance['size']          = apply_filters( 'v8ch_card_widget_image_size', esc_attr( $instance['size'] ), $args, $instance );
			$instance['imageurl']      = apply_filters( 'v8ch_card_widget_image_url', esc_url( $instance['imageurl'] ), $args, $instance );

			// No longer using extracted vars. This is here for backwards compatibility.
			extract( $instance );

			include( $this->getTemplateHierarchy( 'widget-v8ch-card' ) );
		}
	}

	/**
	 * Loads theme files in appropriate hierarchy: 1) child theme,
	 * 2) parent template. will look in the root theme
	 * directory in a theme and the views/ directory in the parent
	 *
	 * @param string $template_slug template file to search for
	 * @return template path
	 * @author V8CH modified from Modern Tribe, Inc. (Matt Wiebe)
	 **/

	public function getTemplateHierarchy($template_slug) {

		$template = $template_slug . '.php';

		if ( $theme_file = locate_template(array($template)) ) {
			$file = $theme_file;
		} else {
			$file = 'views/' . $template;
		}
		return apply_filters( 'v8ch_card_widget_template_file', $file);
	}

	/**
	 * Update widget options
	 *
	 * @param object $new_instance Widget Instance
	 * @param object $old_instance Widget Instance
	 *
	 * @return object
	 * @author V8CH
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args( (array) $new_instance, self::get_defaults() );
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['description'] = $new_instance['description'];
		} else {
			$instance['description'] = wp_filter_post_kses( $new_instance['description'] );
		}
		$instance['post_type']     = strip_tags( $new_instance['post_type'] );
		$instance['tax_name']      = strip_tags( $new_instance['tax_name'] );
		$instance['tax_slug']      = strip_tags( $new_instance['tax_slug'] );
		$instance['link']          = $new_instance['link'];
		$instance['linkid']        = $new_instance['linkid'];
		$instance['linktarget']    = $new_instance['linktarget'];
		$instance['width']         = abs( $new_instance['width'] );
		$instance['height']        = abs( $new_instance['height'] );
		$instance['size']          = $new_instance['size'];
		$instance['align']         = $new_instance['align'];
		$instance['alt']           = $new_instance['alt'];
		$instance['rel']           = $new_instance['rel'];
		$instance['attachment_id'] = abs( $new_instance['attachment_id'] );
		$instance['aspect_ratio']  = $this->get_image_aspect_ratio( $instance );

		return $instance;
	}

	/**
	 * Form UI
	 *
	 * @param object $instance Widget Instance
	 *
	 * @author V8CH
	 */
	public function form( $instance ) {

		$id_prefix = $this->get_field_id( '' );
		?>
		<div class="uploader">
			<input type="submit" class="button" name="<?php echo $this->get_field_name( 'uploader_button' ); ?>"
			       id="<?php echo $this->get_field_id( 'uploader_button' ); ?>"
			       value="<?php _e( 'Select an Image', 'v8ch_card_widget' ); ?>"
			       onclick="imageWidget.uploader( '<?php echo $this->id; ?>', '<?php echo $id_prefix; ?>' ); return false;"/>
			<div class="tribe_preview" id="<?php echo $this->get_field_id( 'preview' ); ?>">
				<?php echo $this->get_image_html( $instance, false ); ?>
			</div>
			<input type="hidden" id="<?php echo $this->get_field_id( 'attachment_id' ); ?>"
			       name="<?php echo $this->get_field_name( 'attachment_id' ); ?>"
			       value="<?php echo abs( $instance['attachment_id'] ); ?>"/>
			<input type="hidden" id="<?php echo $this->get_field_id( 'imageurl' ); ?>"
			       name="<?php echo $this->get_field_name( 'imageurl' ); ?>"
			       value="<?php echo $instance['imageurl']; ?>"/>
		</div>
		<br clear="all"/>

		<div id="<?php echo $this->get_field_id( 'fields' ); ?>"
		     <?php if ( empty( $instance['attachment_id'] ) && empty( $instance['imageurl'] ) ) { ?>style="display:none;"<?php } ?>>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>"/></p>

			<p><label
					for="<?php echo $this->get_field_id( 'alt' ); ?>"><?php _e( 'Alternate Text', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'alt' ); ?>"
				       name="<?php echo $this->get_field_name( 'alt' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['alt'] ) ); ?>"/></p>

			<p><label for="<?php echo $this->get_field_id( 'rel' ); ?>"><?php _e( 'Related', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'rel' ); ?>"
				       name="<?php echo $this->get_field_name( 'rel' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['rel'] ) ); ?>"/><br>
				<span
					class="description"><?php _e( 'A recommended HTML5 related terms list is available <a href="http://microformats.org/wiki/existing-rel-values#HTML5_link_type_extensions" target="_blank">here</a>.', 'v8ch_card_widget' ); ?></span>
			</p>

			<p><label
					for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Caption', 'v8ch_card_widget' ); ?>
					:</label>
				<textarea rows="8" class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>"
				          name="<?php echo $this->get_field_name( 'description' ); ?>"><?php echo format_to_edit( $instance['description'] ); ?></textarea>
			</p>

			<p><label
					for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>"
				       name="<?php echo $this->get_field_name( 'post_type' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['post_type'] ) ); ?>"/></p>

			<p><label
					for="<?php echo $this->get_field_id( 'tax_name' ); ?>"><?php _e( 'Taxonomy Name', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'tax_name' ); ?>"
				       name="<?php echo $this->get_field_name( 'tax_name' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['tax_name'] ) ); ?>"/></p>

			<p><label
					for="<?php echo $this->get_field_id( 'tax_slug' ); ?>"><?php _e( 'Taxonomy Slug', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'tax_slug' ); ?>"
				       name="<?php echo $this->get_field_name( 'tax_slug' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['tax_slug'] ) ); ?>"/></p>

			<p><label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Link', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>"
				       name="<?php echo $this->get_field_name( 'link' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['link'] ) ); ?>"/><br/>
				<label
					for="<?php echo $this->get_field_id( 'linkid' ); ?>"><?php _e( 'Link ID', 'v8ch_card_widget' ); ?>
					:</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'linkid' ); ?>"
				       name="<?php echo $this->get_field_name( 'linkid' ); ?>" type="text"
				       value="<?php echo esc_attr( strip_tags( $instance['linkid'] ) ); ?>"/><br/>
				<select name="<?php echo $this->get_field_name( 'linktarget' ); ?>"
				        id="<?php echo $this->get_field_id( 'linktarget' ); ?>">
					<option
						value="_self"<?php selected( $instance['linktarget'], '_self' ); ?>><?php _e( 'Stay in Window', 'v8ch_card_widget' ); ?></option>
					<option
						value="_blank"<?php selected( $instance['linktarget'], '_blank' ); ?>><?php _e( 'Open New Window', 'v8ch_card_widget' ); ?></option>
				</select></p>


			<?php
			// Backwards compatibility prior to storing attachment ids
			?>
			<div id="<?php echo $this->get_field_id( 'custom_size_selector' ); ?>"
			     <?php if ( empty( $instance['attachment_id'] ) && ! empty( $instance['imageurl'] ) ) {
			     $instance['size'] = self::CUSTOM_IMAGE_SIZE_SLUG; ?>style="display:none;"<?php } ?>>
				<p><label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Size', 'v8ch_card_widget' ); ?>
						:</label>
					<select name="<?php echo $this->get_field_name( 'size' ); ?>"
					        id="<?php echo $this->get_field_id( 'size' ); ?>"
					        onChange="imageWidget.toggleSizes( '<?php echo $this->id; ?>', '<?php echo $id_prefix; ?>' );">
						<?php
						// Note: this is dumb. We shouldn't need to have to do this. There should really be a centralized function in core code for this.
						$possible_sizes                                 = apply_filters( 'image_size_names_choose', array(
							'full'      => __( 'Full Size', 'v8ch_card_widget' ),
							'thumbnail' => __( 'Thumbnail', 'v8ch_card_widget' ),
							'medium'    => __( 'Medium', 'v8ch_card_widget' ),
							'large'     => __( 'Large', 'v8ch_card_widget' ),
						) );
						$possible_sizes[ self::CUSTOM_IMAGE_SIZE_SLUG ] = __( 'Custom', 'v8ch_card_widget' );

						foreach ( $possible_sizes as $size_key => $size_label ) { ?>
							<option
								value="<?php echo $size_key; ?>"<?php selected( $instance['size'], $size_key ); ?>><?php echo $size_label; ?></option>
						<?php } ?>
					</select>
				</p>
			</div>
			<div id="<?php echo $this->get_field_id( 'custom_size_fields' ); ?>"
			     <?php if ( empty( $instance['size'] ) || $instance['size'] != self::CUSTOM_IMAGE_SIZE_SLUG ) { ?>style="display:none;"<?php } ?>>

				<input type="hidden" id="<?php echo $this->get_field_id( 'aspect_ratio' ); ?>"
				       name="<?php echo $this->get_field_name( 'aspect_ratio' ); ?>"
				       value="<?php echo $this->get_image_aspect_ratio( $instance ); ?>"/>

				<p><label
						for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width', 'v8ch_card_widget' ); ?>
						:</label>
					<input id="<?php echo $this->get_field_id( 'width' ); ?>"
					       name="<?php echo $this->get_field_name( 'width' ); ?>" type="text"
					       value="<?php echo esc_attr( strip_tags( $instance['width'] ) ); ?>"
					       onchange="imageWidget.changeImgWidth( '<?php echo $this->id; ?>', '<?php echo $id_prefix; ?>' )"
					       size="3"/></p>

				<p><label
						for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height', 'v8ch_card_widget' ); ?>
						:</label>
					<input id="<?php echo $this->get_field_id( 'height' ); ?>"
					       name="<?php echo $this->get_field_name( 'height' ); ?>" type="text"
					       value="<?php echo esc_attr( strip_tags( $instance['height'] ) ); ?>"
					       onchange="imageWidget.changeImgHeight( '<?php echo $this->id; ?>', '<?php echo $id_prefix; ?>' )"
					       size="3"/></p>

			</div>

			<p><label for="<?php echo $this->get_field_id( 'align' ); ?>"><?php _e( 'Align', 'v8ch_card_widget' ); ?>
					:</label>
				<select name="<?php echo $this->get_field_name( 'align' ); ?>"
				        id="<?php echo $this->get_field_id( 'align' ); ?>">
					<option
						value="none"<?php selected( $instance['align'], 'none' ); ?>><?php _e( 'none', 'v8ch_card_widget' ); ?></option>
					<option
						value="left"<?php selected( $instance['align'], 'left' ); ?>><?php _e( 'left', 'v8ch_card_widget' ); ?></option>
					<option
						value="center"<?php selected( $instance['align'], 'center' ); ?>><?php _e( 'center', 'v8ch_card_widget' ); ?></option>
					<option
						value="right"<?php selected( $instance['align'], 'right' ); ?>><?php _e( 'right', 'v8ch_card_widget' ); ?></option>
				</select></p>
		</div>
		<?php
	}

	/**
	 * Admin header css
	 *
	 * @author V8CH
	 */
	public function admin_head() {
		?>
		<style type="text/css">
			.uploader input.button {
				width: 100%;
				height: 34px;
				line-height: 33px;
				margin-top: 15px;
			}

			.tribe_preview .aligncenter {
				display: block;
				margin-left: auto !important;
				margin-right: auto !important;
			}

			.tribe_preview {
				overflow: hidden;
				max-height: 300px;
			}

			.tribe_preview img {
				margin: 10px 0;
				height: auto;
			}
		</style>
		<?php
	}

	/**
	 * Render an array of default values.
	 *
	 * @return array default values
	 */
	private static function get_defaults() {

		$defaults = array(
			'title'       => '',
			'description' => '',
			'post_type'   => '',
			'tax_name'    => '',
			'tax_slug'    => '',
			'link'        => '',
			'linkid'      => '',
			'linktarget'  => '',
			'width'       => 0,
			'height'      => 0,
			'maxwidth'    => '100%',
			'maxheight'   => '',
			'image'       => 0, // reverse compatible - now attachement_id
			'imageurl'    => '', // reverse compatible.
			'align'       => 'none',
			'alt'         => '',
			'rel'         => '',
		);

		$defaults['size']          = self::CUSTOM_IMAGE_SIZE_SLUG;
		$defaults['attachment_id'] = 0;

		return $defaults;
	}

	/**
	 * Render the image html output.
	 *
	 * @param array $instance
	 * @param bool $include_link will only render the link if this is set to true. Otherwise link is ignored.
	 *
	 * @return string image html
	 */
	private function get_image_html( $instance, $include_link = true ) {

		// Backwards compatible image display.
		if ( $instance['attachment_id'] == 0 && $instance['image'] > 0 ) {
			$instance['attachment_id'] = $instance['image'];
		}

		$output = '';

		if ( $include_link && ! empty( $instance['link'] ) ) {
			$attr   = array(
				'href'   => $instance['link'],
				'id'     => $instance['linkid'],
				'target' => $instance['linktarget'],
				'class'  => $this->widget_options['classname'] . '-image-link',
				'title'  => ( ! empty( $instance['alt'] ) ) ? $instance['alt'] : $instance['title'],
				'rel'    => $instance['rel'],
			);
			$attr   = apply_filters( 'v8ch_card_widget_link_attributes', $attr, $instance );
			$attr   = array_map( 'esc_attr', $attr );
			$output = '<a';
			foreach ( $attr as $name => $value ) {
				$output .= sprintf( ' %s="%s"', $name, $value );
			}
			$output .= '>';
		}

		$size = $this->get_image_size( $instance );
		if ( is_array( $size ) ) {
			$instance['width']  = $size[0];
			$instance['height'] = $size[1];
		} elseif ( ! empty( $instance['attachment_id'] ) ) {
			//$instance['width'] = $instance['height'] = 0;
			$image_details = wp_get_attachment_image_src( $instance['attachment_id'], $size );
			if ( $image_details ) {
				$instance['imageurl'] = $image_details[0];
				$instance['width']    = $image_details[1];
				$instance['height']   = $image_details[2];
			}
		}
		$instance['width']  = abs( $instance['width'] );
		$instance['height'] = abs( $instance['height'] );

		$attr        = array();
		$attr['alt'] = ( ! empty( $instance['alt'] ) ) ? $instance['alt'] : $instance['title'];
		if ( is_array( $size ) ) {
			$attr['class'] = 'attachment-' . join( 'x', $size );
		} else {
			$attr['class'] = 'attachment-' . $size;
		}
		$attr['style'] = '';
		if ( ! empty( $instance['maxwidth'] ) ) {
			$attr['style'] .= "max-width: {$instance['maxwidth']};";
		}
		if ( ! empty( $instance['maxheight'] ) ) {
			$attr['style'] .= "max-height: {$instance['maxheight']};";
		}
		if ( ! empty( $instance['align'] ) && $instance['align'] != 'none' ) {
			$attr['class'] .= " align{$instance['align']}";
		}
		$attr = apply_filters( 'v8ch_card_widget_image_attributes', $attr, $instance );

		// If there is an imageurl, use it to render the image. Eventually we should kill this and simply rely on attachment_ids.
		if ( ! empty( $instance['imageurl'] ) ) {
			// If all we have is an image src url we can still render an image.
			$attr['src'] = $instance['imageurl'];
			$attr        = array_map( 'esc_attr', $attr );
			$hwstring    = image_hwstring( $instance['width'], $instance['height'] );
			$output .= rtrim( "<img $hwstring" );
			foreach ( $attr as $name => $value ) {
				$output .= sprintf( ' %s="%s"', $name, $value );
			}
			$output .= ' />';
		} elseif ( abs( $instance['attachment_id'] ) > 0 ) {
			$output .= wp_get_attachment_image( $instance['attachment_id'], $size, false, $attr );
		}

		if ( $include_link && ! empty( $instance['link'] ) ) {
			$output .= '</a>';
		}

		return $output;
	}

	/**
	 * Assesses the image size in case it has not been set or in case there is a mismatch.
	 *
	 * @param $instance
	 *
	 * @return array|string
	 */
	private function get_image_size( $instance ) {
		if ( ! empty( $instance['size'] ) && $instance['size'] != self::CUSTOM_IMAGE_SIZE_SLUG ) {
			$size = $instance['size'];
		} elseif ( isset( $instance['width'] ) && is_numeric( $instance['width'] ) && isset( $instance['height'] ) && is_numeric( $instance['height'] ) ) {
			//$size = array(abs($instance['width']),abs($instance['height']));
			$size = array( $instance['width'], $instance['height'] );
		} else {
			$size = 'full';
		}

		return $size;
	}

	/**
	 * Establish the aspect ratio of the image.
	 *
	 * @param $instance
	 *
	 * @return float|number
	 */
	private function get_image_aspect_ratio( $instance ) {
		if ( ! empty( $instance['aspect_ratio'] ) ) {
			return abs( $instance['aspect_ratio'] );
		} else {
			$attachment_id = ( ! empty( $instance['attachment_id'] ) ) ? $instance['attachment_id'] : $instance['image'];
			if ( ! empty( $attachment_id ) ) {
				$image_details = wp_get_attachment_image_src( $attachment_id, 'full' );
				if ( $image_details ) {
					return ( $image_details[1] / $image_details[2] );
				}
			}
		}
	}

	public function get_posts_by_tax( $post_type, $tax_name, $tax_slug ) {

		if ( $tax_name != '' && $tax_slug != '' ) {

			$the_query = new WP_Query( array(
				'posts_type'     => $post_type,
				'tax_query'      => array(
					array(
						'taxonomy'         => $tax_name,
						'field'            => 'slug',
						'terms'            => $tax_slug,
						'include_children' => true,
					),
				),
				'posts_per_page' => 3,
			) );

			if ( $the_query->have_posts() ) {

				$string = $this->tabs(7) . '<ul class="post-excerpts">' . PHP_EOL;
				while ( $the_query->have_posts() ) {

					$the_query->the_post();

					$string .= $this->tabs(8) . '<li>' . PHP_EOL;
					$string .= $this->tabs(9) . '<h4 class="' . get_post_type() . '-title"><a href="' . get_the_permalink() . '" rel="bookmark">' . get_the_title() . '</a></h4>' . PHP_EOL;
					$string .= $this->tabs(9) . '<div class="' . get_post_type() . '-excerpt">' . get_the_excerpt() . '</div>' . PHP_EOL;
					$string .= $this->tabs(9) . '<div class="more-link-footer type-' . get_post_type() . '">' . PHP_EOL;
					$string .= $this->tabs(10) . '<a href="' . get_the_permalink() . '" rel="bookmark" class="more-link"><span class="fa fa-chevron-right"></span ><span class="fa fa-chevron-right"></span >Read more</a>' . PHP_EOL;
					$string .= $this->tabs(9) . '</div>' . PHP_EOL;
					$string .= $this->tabs(8) . '</li>' . PHP_EOL;

				}

				$string .= $this->tabs(7) . '</ul>' . PHP_EOL;

			} else {
				$string = "";
			}

			/* Restore post data */
			wp_reset_postdata();

		}

		return $string;

	}
}
