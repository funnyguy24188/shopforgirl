<?php
/**
 * Contro Positioning over google maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_limit_panning_setting', array(
	'value' => __( 'Limit Panning Settings', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_all_control[panning_control]', array(
	'lable' => __( 'Limit Panning', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_panning_control',
	'current' => $data['map_all_control']['panning_control'],
	'desc' => __( 'Apply limit panning. if you enabled,below information can not be empty.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '.panning_control' ),
));

$form->set_col( 2 );
$form->add_element( 'text', 'map_all_control[from_latitude]', array(
	'lable' => __( 'South West', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['from_latitude'],
	'desc' => __( 'Enter here "South West" latitude', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control panning_control',
	'show' => 'false',
	'before' => '<div class="col-md-4">',
	'after' => '</div>',
));

$form->add_element( 'text', 'map_all_control[from_longitude]', array(
	'value' => $data['map_all_control']['from_longitude'],
	'desc' => __( 'Enter here "South West" longitude', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control panning_control',
	'show' => 'false',
	'before' => '<div class="col-md-4">',
	'after' => '</div>',
));

$form->add_element( 'text', 'map_all_control[to_latitude]', array(
	'lable' => __( 'North East', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['to_latitude'],
	'desc' => __( 'Enter here "North East" latitude', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control panning_control',
	'show' => 'false',
	'before' => '<div class="col-md-4">',
	'after' => '</div>',
));

$form->add_element( 'text', 'map_all_control[to_longitude]', array(
	'value' => $data['map_all_control']['to_longitude'],
	'desc' => __( 'Enter here "North East" longitude', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control panning_control',
	'show' => 'false',
	'before' => '<div class="col-md-4">',
	'after' => '</div>',
));
$form->set_col( 1 );
for ( $i = 1; $i<20;$i++ ) {
	$zoom_level[$i]=$i;
}
$form->add_element( 'select', 'map_all_control[zoom_level]', array(
	'lable' => __( 'Zoom Level', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_all_control']['zoom_level'],
	'desc' => __( 'Select zoom level.', WPGMP_TEXT_DOMAIN ),
	'options' => $zoom_level,
	'class' => 'form-control panning_control',
	'show' => 'false',
));


