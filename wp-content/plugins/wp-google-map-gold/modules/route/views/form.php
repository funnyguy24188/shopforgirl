<?php
/**
 * Template for Add & Edit Route
 * @author  Flipper Code <hello@flippercode.com>
 * @package Maps
 */

if ( isset( $_REQUEST['_wpnonce'] ) ) {

	$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

		die( 'Cheating...' );

	} else {
		$data = $_POST;
	}
}
global $wpdb;
$form  = new FlipperCode_HTML_Markup();
$modelFactory = new WPGMP_Model();
$category = $modelFactory->create_object( 'group_map' );
$location = $modelFactory->create_object( 'location' );
$locations = $location->fetch();
$categories = $category->fetch();
if ( ! empty( $categories ) ) {
	$categories_data = array();
	foreach ( $categories as $cat ) {
		$categories_data[ $cat->group_map_id ] = $cat->group_map_title;
	}
}
$route = $modelFactory->create_object( 'route' );
if ( isset( $_GET['doaction'] ) and  'edit' == $_GET['doaction'] and isset( $_GET['route_id'] ) ) {
	$route_obj   = $route->fetch( array( array( 'route_id', '=', intval( wp_unslash( $_GET['route_id'] ) ) ) ) );
	$data = (array) $route_obj[0];
} elseif ( ! isset( $_GET['doaction'] ) and isset( $response['success'] ) ) {
	// Reset $_POST object for antoher entry.
	unset( $data );
}
if ( ! empty( $locations ) ) {
	$all_locations = array();
	foreach ( $locations as $loc ) {
		$assigned_categories = array();
		if ( isset( $loc->location_group_map ) and is_array( $loc->location_group_map ) ) {
			foreach ( $loc->location_group_map as $c => $cat ) {
				$assigned_categories[] = $categories_data[ $cat ];
			}
		}
		$assigned_categories = implode( ',',$assigned_categories );
		$loc_checkbox = $form->field_checkbox('select_route_way_points[]',array(
			'value' => $loc->location_id,
			'current' => ((in_array( $loc->location_id, (array) $data['route_way_points'] )) ? $loc->location_id : ''),
			'class' => 'chkbox_class',
			'before' => '<div class="col-md-1">',
			'after' => '</div>',
			));
		$all_locations[] = array( $loc_checkbox, $loc->location_title, $loc->location_address, $assigned_categories );
	}
}


$form->set_header( __( 'Route Information', WPGMP_TEXT_DOMAIN ), $response, __( 'Manage Routes', WPGMP_TEXT_DOMAIN ), 'wpgmp_manage_route' );

$form->add_element( 'text', 'route_title', array(
	'lable' => __( 'Route Title', WPGMP_TEXT_DOMAIN ),
	'value' => (isset( $data['route_title'] ) and ! empty( $data['route_title'] )) ? sanitize_text_field( wp_unslash( $data['route_title'] ) ) : '',
	'id' => 'route_title',
	'desc' => __( 'Please enter route title.', WPGMP_TEXT_DOMAIN ),
	'placeholder' => __( 'Route Title', WPGMP_TEXT_DOMAIN ),
	'required' => true,
));

$color = (empty( $data['route_stroke_color'] )) ?  '8CAEF2' : sanitize_text_field( wp_unslash( $data['route_stroke_color'] ) );
$form->add_element( 'text', 'route_stroke_color', array(
	'lable' => __( 'Stroke Color', WPGMP_TEXT_DOMAIN ),
	'value' => $color,
	'class' => 'color {pickerClosable:true} form-control',
	'id' => 'route_stroke_color',
	'desc' => __( 'Choose route direction stroke color.(Default is Blue)', WPGMP_TEXT_DOMAIN ),
	'placeholder' => __( 'Route Stroke Color', WPGMP_TEXT_DOMAIN ),
));

$stroke_opacity = array( '1' => '1','0.9' => '0.9','0.8' => '0.8','0.7' => '0.7','0.6' => '0.6','0.5' => '0.5','0.4' => '0.4','0.3' => '0.3','0.2' => '0.2','0.1' => '0.1' );
$form->add_element( 'select', 'route_stroke_opacity', array(
	'lable' => __( 'Stroke Opacity', WPGMP_TEXT_DOMAIN ),
	'current' => (isset( $data['route_stroke_opacity'] ) and ! empty( $data['route_stroke_opacity'] )) ? sanitize_text_field( wp_unslash( $data['route_stroke_opacity'] ) ) : '',
	'desc' => __( 'Please select route direction stroke opacity.', WPGMP_TEXT_DOMAIN ),
	'options' => $stroke_opacity,
	'class' => 'form-control-select',
));

$stroke_weight = array();
for ( $sw = 10; $sw >= 1; $sw-- ) {
	$stroke_weight[ $sw ] = $sw;
}
$form->add_element( 'select', 'route_stroke_weight', array(
	'lable' => __( 'Stroke Weight', WPGMP_TEXT_DOMAIN ),
	'current' => (isset( $data['route_stroke_weight'] ) and ! empty( $data['route_stroke_weight'] )) ? sanitize_text_field( wp_unslash( $data['route_stroke_weight'] ) ) : '',
	'desc' => __( 'Please select route stroke weight.', WPGMP_TEXT_DOMAIN ),
	'options' => $stroke_weight,
	'class' => 'form-control-select',
));

$route_travel_mode = array( 'DRIVING' => 'DRIVING','WALKING' => 'WALKING','BICYCLING' => 'BICYCLING','TRANSIT' => 'TRANSIT' );
$form->add_element( 'select', 'route_travel_mode', array(
	'lable' => __( 'Travel Modes', WPGMP_TEXT_DOMAIN ),
	'current' => (isset( $data['route_travel_mode'] ) and ! empty( $data['route_travel_mode'] )) ? sanitize_text_field( wp_unslash( $data['route_travel_mode'] ) ) : '',
	'desc' => __( 'Please select travel mode.', WPGMP_TEXT_DOMAIN ),
	'options' => $route_travel_mode,
	'class' => 'form-control-select',
));

$form->add_element( 'select', 'route_unit_system', array(
	'lable' => __( 'Unit Systems', WPGMP_TEXT_DOMAIN ),
	'current' => (isset( $data['route_unit_system'] ) and ! empty( $data['route_unit_system'] )) ? sanitize_text_field( wp_unslash( $data['route_unit_system'] ) ) : '',
	'desc' => __( 'Please select unit system.', WPGMP_TEXT_DOMAIN ),
	'options' => array( 'METRIC' => 'METRIC', 'IMPERIAL' => 'IMPERIAL' ),
	'class' => 'form-control-select',
));

$current = (empty( $data['route_marker_draggable'] )) ? '' : sanitize_text_field( wp_unslash( $data['route_marker_draggable'] ) );
$form->add_element( 'checkbox', 'route_marker_draggable', array(
	'lable' => __( 'Draggable', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'current' => $current,
	'id' => 'route_marker_draggable',
	'desc' => __( 'Please check to enable route draggable.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class',
));

$current = (empty( $data['route_optimize_waypoints'] )) ? '' : sanitize_text_field( wp_unslash( $data['route_optimize_waypoints'] ) );
$form->add_element( 'checkbox', 'route_optimize_waypoints', array(
	'lable' => __( 'Optimize Waypoints', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'current' => $current,
	'id' => 'route_optimize_waypoints',
	'desc' => __( 'Please check to enable optimize waypoints.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class',
));


if ( ! empty( $locations ) ) {
	$res = array();
	for ( $i = 0; $i < count( $locations ); $i++ ) {
		$res[ $locations[ $i ]->location_id ] = $locations[ $i ]->location_title;
	}
}

$form->add_element( 'select', 'route_start_location', array(
		'lable' => __( 'Start Location', WPGMP_TEXT_DOMAIN ),
		'current' => (isset( $data['route_start_location'] ) and ! empty( $data['route_start_location'] )) ? sanitize_text_field( wp_unslash( $data['route_start_location'] ) ) : '',
		'desc' => __( 'Please select start location.', WPGMP_TEXT_DOMAIN ),
		'options' => $res,
));

$form->add_element( 'select', 'route_end_location', array(
	'lable' => __( 'End Location', WPGMP_TEXT_DOMAIN ),
	'current' => (isset( $data['route_end_location'] ) and ! empty( $data['route_end_location'] )) ? sanitize_text_field( wp_unslash( $data['route_end_location'] ) ) : '',
	'desc' => __( 'Please select end location.', WPGMP_TEXT_DOMAIN ),
	'options' => $res,
));


$form->add_element( 'group', 'location_extra_fields', array(
	'value' => __( 'Way Point(s)', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-11">',
	'after' => '</div>',
));


$form->add_element( 'message', 'route_notes', array(
	'value' => __( 'You can select maximum 8 way point(s).', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-11">',
	'class' => 'alert alert-success',
	'after' => '</div>',
));

$form->add_element( 'table', 'route_selected_way_points', array(
		'heading' => array( 'Select', 'Title', 'Address', 'Categories' ),
		'data' => $all_locations,
		'id' => 'wpgmp_google_map_data_table',
		'before' => '<div class="col-md-11">',
		'after' => '</div>',
	));

$form->add_element('extensions','wpgmp_route_form',array(
	'value' => $data['extensions_fields'],
	'before' => '<div class="col-md-11">',
	'after' => '</div>',
	));

$form->add_element( 'submit', 'save_route_data', array(
	'value' => 'Save Route',
));

$form->add_element( 'hidden', 'route_way_points', array(
	'value' => '',
));

$form->add_element( 'hidden', 'operation', array(
	'value' => 'save',
));

if ( isset( $_GET['doaction'] ) and 'edit' == 'edit' and isset( $_GET['route_id'] ) ) {

	$form->add_element( 'hidden', 'entityID', array(
		'value' => intval( wp_unslash( $_GET['route_id'] ) ),
	));

}

$form->render();
