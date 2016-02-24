<?php
/**
 * Template override for the Modern Tribe Image Widget plugin
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

echo $before_widget;

// Wrapper around h3, img and description
echo '<div class="' . $this->widget_options['classname'] . '-img-wrap">';

// h3
if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

// img
echo '<div class="' . $this->widget_options['classname'] . '-img">';
echo $this->get_image_html( $instance, true );
echo '</div>'; // img

// description
echo '<div class="' . $this->widget_options['classname'] . '-description" >';
if ( !empty( $description ) ) {
	echo wpautop( $description );
}
echo "</div>";

echo "</div>"; // wrapper

// List of related clippings
echo '<div class="clippings-list-wrap">';
if ( !empty( $type_slug ) ) {
	$atts = array( 'type_slug' => $type_slug );
	echo almanac_clippings_by_type( $atts );
}
echo '</div>';

echo $after_widget;
?>