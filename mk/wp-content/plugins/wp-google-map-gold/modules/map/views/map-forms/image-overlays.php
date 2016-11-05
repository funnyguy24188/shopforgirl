<?php
/**
 * Image overlays over google maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_image_overlays', array(
	'value' => __( 'Image Overalys', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_image_overlays[image_overlays]', array(
	'lable' => __( 'Apply Image Overlays', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'image_overlays',
	'current' => $data['map_image_overlays']['image_overlays'],
	'desc' => __( 'Please check to apply image overlays.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '.map_image_overlays' ),
));

$form->set_col(8);

$form->add_element( 'text', 'map_image_overlays[image_overlays_url]', array(
	'lable' => __( 'Image Tiles URL', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_image_overlays']['image_overlays_url'],
	'desc' => 'Enter base path of the image tiles.',
	'class' => 'map_image_overlays form-control',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
	'show' => 'false',
));

$form->add_element( 'text', 'map_image_overlays[image_overlays_zoom]', array(
	'value' => $data['map_image_overlays']['image_overlays_zoom'],
	'desc' => 'Enter grid here. Default is 15.',
	'class' => 'map_image_overlays form-control',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
	'show' => 'false',
));

$form->set_col(1);
