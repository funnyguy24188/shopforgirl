<?php
/**
 * Overlay Settings.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_overlay_setting', array(
	'value' => __( 'Overlays Settings', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_overlay_setting[overlay]', array(
	'lable' => __( 'Apply Overlays', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_overlay',
	'current' => $data['map_overlay_setting']['overlay'],
	'desc' => __( 'Please check to apply overlays. if enabled, below information can not be empty.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '.map_overlay_setting' ),
));


$form->add_element( 'text', 'map_overlay_setting[overlay_border_color]', array(
	'lable' => __( 'Overlay Border Color', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_overlay_setting']['overlay_border_color'],
	'desc' => __( 'Default is red.', WPGMP_TEXT_DOMAIN ),
	'class' => 'color {pickerClosable:true} form-control map_overlay_setting',
	'show' => 'false',
));

$form->add_element( 'text', 'map_overlay_setting[overlay_width]', array(
	'lable' => __( 'Overlay Width', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_overlay_setting']['overlay_width'],
	'desc' => __( 'Enter here overlay width. Default is 200px.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control map_overlay_setting',
	'show' => 'false',
	'default_value' => '200',
));

$form->add_element( 'text', 'map_overlay_setting[overlay_height]', array(
	'lable' => __( 'Overlay Height', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_overlay_setting']['overlay_height'],
	'desc' => __( 'Enter here overlay height. Default is 200px.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control map_overlay_setting',
	'show' => 'false',
	'default_value' => '200',
));

$form->add_element( 'text', 'map_overlay_setting[overlay_fontsize]', array(
	'lable' => __( 'Overlay Font size', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_overlay_setting']['overlay_fontsize'],
	'desc' => __( 'Enter here Overlay Font Size. Default is 16px.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control map_overlay_setting',
	'show' => 'false',
	'default_value' => '16',
));

$form->add_element( 'text', 'map_overlay_setting[overlay_border_width]', array(
	'lable' => __( 'Overlay Border Width', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_overlay_setting']['overlay_border_width'],
	'desc' => __( 'Enter here Overlay Border Width. Default is 2px.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control map_overlay_setting',
	'show' => 'false',
	'default_value' => '2',
));
$overlay_values = array( 'dotted' => 'Dotted','solid' => 'Solid','dashed' => 'Dashed' );
$form->add_element( 'select', 'map_overlay_setting[overlay_border_style]', array(
	'lable' => __( 'Overlay Border Style', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_overlay_setting']['overlay_border_style'],
	'desc' => __( 'Select overlay border style.', WPGMP_TEXT_DOMAIN ),
	'options' => $overlay_values,
	'class' => 'map_overlay_setting form-control',
	'show' => 'false',
));
