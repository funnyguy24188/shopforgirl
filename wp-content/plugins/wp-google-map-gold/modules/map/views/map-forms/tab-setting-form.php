<?php
/**
 * Display Tabs over google maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_tabs_setting', array(
	'value' => __( 'Tabs Settings', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_all_control[display_marker_category]', array(
	'lable' => __( 'Display Tabs', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_display_marker_category',
	'current' => $data['map_all_control']['display_marker_category'],
	'desc' => __( 'Display various tabs on the map.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '.map_tabs_setting' ),
));

$form->add_element( 'checkbox', 'map_all_control[hide_tabs_default]', array(
	'lable' => __( 'Hide Tabs on Load', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_hide_tabs_default',
	'current' => $data['map_all_control']['hide_tabs_default'],
	'desc' => __( 'Hide tabs by default.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_category_tab_setting',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_category_tab]', array(
	'lable' => __( 'Display Categories Tab', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_category_tab',
	'current' => $data['map_all_control']['wpgmp_category_tab'],
	'desc' => __( 'Display Categories/Locations Tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class map_tabs_setting switch_onoff',
	'show' => 'false',
	'data' => array( 'target' => '.wpgmp_category_tab_setting' ),

));

$form->add_element( 'text', 'map_all_control[wpgmp_category_tab_title]', array(
	'lable' => __( 'Category Tab Title', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_category_tab_title'],
	'id' => 'wpgmp_category_tab_title',
	'desc' => __( 'Title of the category tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control wpgmp_category_tab_setting',
	'show' => 'false',
	'default_value' => __( 'Categories',WPGMP_TEXT_DOMAIN ),
));

$form->add_element( 'select', 'map_all_control[wpgmp_category_order]', array(
	'lable' => __( 'Sort By', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_all_control']['wpgmp_category_order'],
	'desc' => __( 'Select Sort By.', WPGMP_TEXT_DOMAIN ),
	'options' => array( 'title' => __( 'Title',WPGMP_TEXT_DOMAIN ),'count' => __( 'Location Count.',WPGMP_TEXT_DOMAIN ) ),
	'class' => 'form-control wpgmp_category_tab_setting',
	'show' => 'false',
	'before' => '<div class="col-md-4">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_category_tab_show_count]', array(
	'lable' => __( 'Show Location Count', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_category_tab_show_count',
	'current' => $data['map_all_control']['wpgmp_category_tab_show_count'],
	'desc' => __( 'Display location count next to category name.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_category_tab_setting',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_category_tab_hide_location]', array(
	'lable' => __( 'Hide Locations', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_category_tab_hide_location',
	'current' => $data['map_all_control']['wpgmp_category_tab_hide_location'],
	'desc' => __( 'Hide locations below category selection.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_category_tab_setting',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_category_tab_show_all]', array(
	'lable' => __( 'Select All', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_category_tab_show_all',
	'current' => $data['map_all_control']['wpgmp_category_tab_show_all'],
	'desc' => __( 'Display select all checkbox to select all categories at once.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_category_tab_setting',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_direction_tab]', array(
	'lable' => __( 'Display Directions Tab', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_direction_tab',
	'current' => $data['map_all_control']['wpgmp_direction_tab'],
	'desc' => __( 'Display Direction Tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff map_tabs_setting',
	'data' => array( 'target' => '.wpgmp_direction_tab' ),
	'show' => 'false',
));

$form->add_element( 'text', 'map_all_control[wpgmp_direction_tab_title]', array(
	'lable' => __( 'Direction Tab Title', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_direction_tab_title'],
	'id' => 'wpgmp_direction_tab_title',
	'desc' => __( 'Title of the route tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control wpgmp_direction_tab',
	'show' => 'false',
	'default_value' => __( 'Directions',WPGMP_TEXT_DOMAIN ),
));

$form->add_element( 'select', 'map_all_control[wpgmp_unit_selected]', array(
	'lable' => __( 'Select Unit', WPGMP_TEXT_DOMAIN ),
	'options' => array( 'km' => __( 'KM',WPGMP_TEXT_DOMAIN ),'miles' => __( 'miles',WPGMP_TEXT_DOMAIN ) ),
	'current' => $data['map_all_control']['wpgmp_unit_selected'],
	'class' => 'chkbox_class wpgmp_direction_tab',
	'show' => 'false',
	'default_value' => 'km',
));
$form->add_element( 'radio', 'map_all_control[wpgmp_direction_tab_start]', array(
	'lable' => __( 'Start Location', WPGMP_TEXT_DOMAIN ),
	'radio-val-label' => array( 'textbox' => __( 'Auto Search Textbox',WPGMP_TEXT_DOMAIN ),'selectbox' => __( 'Location Dropdown',WPGMP_TEXT_DOMAIN ) ),
	'current' => $data['map_all_control']['wpgmp_direction_tab_start'],
	'class' => 'chkbox_class wpgmp_direction_tab',
	'show' => 'false',
	'default_value' => 'textbox',
));

$form->add_element( 'text', 'map_all_control[wpgmp_direction_tab_start_default]', array(
	'lable' => __( 'Default Start Location', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_direction_tab_start_default'],
	'id' => 'wpgmp_direction_tab_start_default',
	'desc' => __( 'Set default start location.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control wpgmp_direction_tab wpgmp_auto_suggest',
	'show' => 'false',
));

$form->add_element( 'radio', 'map_all_control[wpgmp_direction_tab_end]', array(
	'lable' => __( 'End Location', WPGMP_TEXT_DOMAIN ),
	'radio-val-label' => array( 'textbox' => __( 'Auto Search Textbox',WPGMP_TEXT_DOMAIN ),'selectbox' => __( 'Location Dropdown',WPGMP_TEXT_DOMAIN ) ),
	'current' => $data['map_all_control']['wpgmp_direction_tab_end'],
	'class' => 'chkbox_class wpgmp_direction_tab',
	'show' => 'false',
	'default_value' => 'textbox',
));

$form->add_element( 'text', 'map_all_control[wpgmp_direction_tab_end_default]', array(
	'lable' => __( 'Default End Location', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_direction_tab_end_default'],
	'id' => 'wpgmp_direction_tab_end_default',
	'desc' => __( 'Set default end location.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control wpgmp_direction_tab wpgmp_auto_suggest',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_direction_tab_suppress_markers]', array(
	'lable' => __( 'Suppress Markers', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_direction_tab_suppress_markers',
	'current' => $data['map_all_control']['wpgmp_direction_tab_suppress_markers'],
	'desc' => __( 'Check the suppressMarkers property to hide directions markers', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_direction_tab',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_nearby_tab]', array(
	'lable' => __( 'Display Nearby Tab', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_nearby_tab',
	'current' => $data['map_all_control']['wpgmp_nearby_tab'],
	'desc' => __( 'Display nearby tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff map_tabs_setting',
	'show' => 'false',
	'data' => array( 'target' => '.nearby_tabs_setting' ),
));

$form->add_element( 'text', 'map_all_control[wpgmp_nearby_tab_title]', array(
	'lable' => __( 'Nearby Tab Title', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_nearby_tab_title'],
	'id' => 'wpgmp_nearby_tab_title',
	'desc' => __( 'Title of the nearby tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control nearby_tabs_setting',
	'show' => 'false',
	'default_value' => __( 'Nearby Places',WPGMP_TEXT_DOMAIN ),
));

$form->add_element( 'message', 'amenities_instruction', array(
	'value' => __( 'You can select amenities to display in nearby tab to be searchable in the below list.',WPGMP_TEXT_DOMAIN ),
	'class' => 'alert alert-success nearby_tabs_setting',
	'show' => 'false',
));

$amenities_options = array(
'accounting',
'airport',
'amusement_park',
'aquarium',
'art_gallery',
'atm',
'bakery',
'bank',
'bar',
'beauty_salon',
'bicycle_store',
'book_store',
'bowling_alley',
'bus_station',
'cafe',
'campground',
'car_dealer',
'car_rental',
'car_repair',
'car_wash',
'casino',
'cemetery',
'church',
'city_hall',
'clothing_store',
'convenience_store',
'courthouse',
'dentist',
'department_store',
'doctor',
'electrician',
'electronics_store',
'embassy',
'establishment',
'finance',
'fire_station',
'florist',
'food',
'funeral_home',
'furniture_store',
'gas_station',
'general_contractor',
'grocery_or_supermarket',
'gym',
'hair_care',
'hardware_store',
'health',
'hindu_temple',
'home_goods_store',
'hospital',
'insurance_agency',
'jewelry_store',
'laundry',
'lawyer',
'library',
'liquor_store',
'local_government_office',
'locksmith',
'lodging',
'meal_delivery',
'meal_takeaway',
'mosque',
'movie_rental',
'movie_theater',
'moving_company',
'museum',
'night_club',
'painter',
'park',
'parking',
'pet_store',
'pharmacy',
'physiotherapist',
'place_of_worship',
'plumber',
'police',
'post_office',
'real_estate_agency',
'restaurant',
'roofing_contractor',
'rv_park',
'school',
'shoe_store',
'shopping_mall',
'spa',
'stadium',
'storage',
'store',
'subway_station',
'synagogue',
'taxi_stand',
'train_station',
'travel_agency',
'university',
'veterinary_care',
'zoo',
	);
$amenities = array();
if ( ! empty( $amenities_options ) ) {
	$count = 0;
	$column = 1;
	foreach ( $amenities_options as $place_type => $amenity ) {

		$amenities[ $count ][] = $form->field_checkbox( 'map_all_control[wpgmp_nearby_amenities]['.$amenity.']', array(
				'desc' => $amenity,
				'value' => $amenity,
				'current' => $data['map_all_control']['wpgmp_nearby_amenities'][ $amenity ],
				'before' => '<div class="col-md-1">',
				'after' => '</div>',
				'class' => 'chkbox_class',
				) );
		if ( 0 == $column % 7 ) {
			$count++; }

		$column++;
	}
}
$form->add_element( 'table', 'wpgmp_amenities_table', array(
		'heading' => array( '','','','','','','','' ),
		'data' => $amenities,
		'before' => '<div class="col-md-11">',
		'after' => '</div>',
		'class' => ' nearby_tabs_setting',
		'show' => 'false',
	));

$form->add_element( 'checkbox', 'map_all_control[show_nearby_circle]', array(
	'lable' => __( 'Display Circle around amenities', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'show_nearby_circle',
	'current' => $data['map_all_control']['show_nearby_circle'],
	'desc' => __( 'Display a circle around the nearby locations.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff nearby_tabs_setting',
	'show' => 'false',
	'data' => array( 'target' => '.nearby_circle_settings' ),
));
$form->set_col( 5 );
$color = (empty( $data['map_all_control']['nearby_circle_fillcolor'] )) ?  '8CAEF2' : sanitize_text_field( wp_unslash( $data['map_all_control']['nearby_circle_fillcolor'] ) );
$form->add_element( 'text', 'map_all_control[nearby_circle_fillcolor]', array(
	'value' => $color,
	'class' => 'color {pickerClosable:true} form-control nearby_circle_settings',
	'id' => 'nearby_circle_fillcolor',
	'desc' => __( 'Circle fill color.', WPGMP_TEXT_DOMAIN ),
	'show' => 'false',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
));
$form->add_element( 'text', 'map_all_control[nearby_circle_fillopacity]', array(
	'value' => $data['map_all_control']['nearby_circle_fillopacity'],
	'class' => 'form-control nearby_circle_settings',
	'id' => 'nearby_circle_fillopacity',
	'desc' => __( 'Circle fill opacity.', WPGMP_TEXT_DOMAIN ),
	'show' => 'false',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
	'default_value' => '.5',
));
$color = (empty( $data['map_all_control']['nearby_circle_strokecolor'] )) ?  '8CAEF2' : sanitize_text_field( wp_unslash( $data['map_all_control']['nearby_circle_strokecolor'] ) );
$form->add_element( 'text', 'map_all_control[nearby_circle_strokecolor]', array(
	'value' => $color,
	'class' => 'color {pickerClosable:true} form-control nearby_circle_settings',
	'id' => 'nearby_circle_strokecolor',
	'desc' => __( 'Circle stroke color.', WPGMP_TEXT_DOMAIN ),
	'show' => 'false',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
));

$form->add_element( 'text', 'map_all_control[nearby_circle_strokeopacity]', array(
	'value' => $data['map_all_control']['nearby_circle_strokeopacity'],
	'class' => 'form-control nearby_circle_settings',
	'id' => 'nearby_circle_strokeopacity',
	'desc' => __( 'Circle stroke opacity.', WPGMP_TEXT_DOMAIN ),
	'show' => 'false',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
	'default_value' => '.5',
));

$form->add_element( 'text', 'map_all_control[nearby_circle_strokeweight]', array(
	'value' => $data['map_all_control']['nearby_circle_strokeweight'],
	'class' => 'form-control nearby_circle_settings',
	'id' => 'nearby_circle_strokeweight',
	'desc' => __( 'Circle stroke weight.', WPGMP_TEXT_DOMAIN ),
	'show' => 'false',
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
	'default_value' => '1',
));
$form->set_col( 1 );
$zoom_level = array();
for ( $i = 1; $i < 20; $i++ ) {
	$zoom_level[ $i ] = $i;
}
$form->add_element( 'select', 'map_all_control[nearby_circle_zoom]', array(
	'lable' => __( 'Circle Zoom Level', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_all_control']['nearby_circle_zoom'],
	'desc' => __( 'Available options 1 to 19.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control  nearby_circle_settings',
	'options' => $zoom_level,
	'before' => '<div class="col-md-2">',
	'after' => '</div>',
	'default_value' => '8',
	'show' => 'false',
));


$form->add_element( 'checkbox', 'map_all_control[wpgmp_route_tab]', array(
	'lable' => __( 'Display Route Tab', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_route_tab',
	'current' => $data['map_all_control']['wpgmp_route_tab'],
	'desc' => __( 'Display route tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class map_tabs_setting switch_onoff',
	'data' => array( 'target' => '.wpgmp_route_tab_setting' ),
	'show' => 'false',
));

$form->add_element( 'text', 'map_all_control[wpgmp_route_tab_title]', array(
	'lable' => __( 'Route Tab Title', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_route_tab_title'],
	'id' => 'wpgmp_route_tab_title',
	'desc' => __( 'Title of the route tab.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control wpgmp_route_tab_setting',
	'show' => 'false',
	'default_value' => __( 'Routes',WPGMP_TEXT_DOMAIN ),
));

$form->set_col( 1 );
