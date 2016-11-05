<?php
/**
 * Contro Positioning over google maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_listing_setting', array(
	'value' => __( 'Listing Settings', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_all_control[display_listing]', array(
	'lable' => __( 'Display Listing', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_display_listing',
	'current' => $data['map_all_control']['display_listing'],
	'desc' => __( 'Display locations listing below the map.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '.wpgmp_display_listing' ),
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_search_display]', array(
	'lable' => __( 'Display Search Form', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_search_display',
	'current' => $data['map_all_control']['wpgmp_search_display'],
	'desc' => __( 'Check to display search form below the map.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_category_filter]', array(
	'lable' => __( 'Display Category Filter', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_display_category_filter',
	'current' => $data['map_all_control']['wpgmp_display_category_filter'],
	'desc' => __( 'Check to display category filter.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));


$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_sorting_filter]', array(
	'lable' => __( 'Display Sorting Filter', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_display_sorting_filter',
	'current' => $data['map_all_control']['wpgmp_display_sorting_filter'],
	'desc' => __( 'Check to display sorting filter.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_radius_filter]', array(
	'lable' => __( 'Display Radius Filter', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_display_radius_filter',
	'current' => $data['map_all_control']['wpgmp_display_radius_filter'],
	'desc' => __( 'Check to display radius filter. Recommended to display search results within certian radius.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing switch_onoff',
	'show' => 'false',
	'data' => array( 'target' => '.wpgmp_radius_filter' ),
));

$dimension_options = array( 'miles' => __( 'Miles',WPGMP_TEXT_DOMAIN ),'km' => __( 'KM',WPGMP_TEXT_DOMAIN ) );
$form->add_element( 'select', 'map_all_control[wpgmp_radius_dimension]', array(
	'lable' => __( 'Dimension', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_all_control']['wpgmp_radius_dimension'],
	'desc' => __( 'Choose radius dimension in miles or km.', WPGMP_TEXT_DOMAIN ),
	'options' => $dimension_options,
	'class' => 'form-control  wpgmp_radius_filter',
	'show' => 'false',
));

$form->add_element( 'text', 'map_all_control[wpgmp_radius_options]', array(
	'lable' => __( 'Radius Options', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_radius_options'],
	'desc' => __( 'Set radius options. Enter comma seperated numbers.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control  wpgmp_radius_filter',
	'show' => 'false',
	'default_value' => '5,10,15,20,25,50,100,200,500',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_location_per_page_filter]', array(
	'lable' => __( 'Display Per Page Filter', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_display_location_per_page_filter',
	'current' => $data['map_all_control']['wpgmp_display_location_per_page_filter'],
	'desc' => __( 'Check to enable locations per page filter.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_location_per_page_filter]', array(
	'lable' => __( 'Display Per Page Filter', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_wpgmp_display_location_per_page_filter',
	'current' => $data['map_all_control']['wpgmp_display_location_per_page_filter'],
	'desc' => __( 'Check to enable locations per page filter.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_print_option]', array(
	'lable' => __( 'Display Print Option', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_display_print_option',
	'current' => $data['map_all_control']['wpgmp_display_print_option'],
	'desc' => __( 'Check to display print option.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'checkbox', 'map_all_control[wpgmp_display_grid_option]', array(
	'lable' => __( 'Display Grid/List Option', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_display_grid_option',
	'current' => $data['map_all_control']['wpgmp_display_grid_option'],
	'desc' => __( 'Switch between list/grid view.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'text', 'map_all_control[wpgmp_listing_number]', array(
	'lable' => __( 'Locations Per Page', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_listing_number'],
	'desc' => __( 'Set locations to display per page. Default is 10.', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control wpgmp_display_listing',
	'show' => 'false',
	'default_value' => 10,
));


$form->add_element( 'textarea', 'map_all_control[wpgmp_before_listing]', array(
	'lable' => __( 'Before Listing Placeholder', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['wpgmp_before_listing'],
	'desc' => __( 'Display a text/html content before display listing.', WPGMP_TEXT_DOMAIN ),
	'textarea_rows' => 10,
	'textarea_name' => 'map_all_control[wpgmp_before_listing]',
	'class' => 'form-control wpgmp_display_listing',
	'show' => 'false',
	'default_value' => __( 'Map Locations',WPGMP_TEXT_DOMAIN ),
));

$list_grid = array( 'wpgmp_listing_list' => 'List','wpgmp_listing_grid' => 'Grid' );
$form->add_element( 'select', 'map_all_control[wpgmp_list_grid]', array(
	'lable' => __( 'List/Grid', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_all_control']['wpgmp_list_grid'],
	'desc' => __( 'Choose listing style for frontend display.', WPGMP_TEXT_DOMAIN ),
	'options' => $list_grid,
	'class' => 'form-control wpgmp_display_listing',
	'show' => 'false',
));

$default_place_holder = '
<div class="wpgmp_locations">
<div class="wpgmp_locations_head">
<div class="wpgmp_location_title">
<a href="" class="place_title" data-zoom="{marker_zoom}" data-marker="{marker_id}">{marker_title}</a>
</div>
<div class="wpgmp_location_meta">
<span class="wpgmp_location_category">Category : {marker_category}</span>
</div>
</div>
<div class="wpgmp_locations_content">
{marker_message}
</div>
<div class="wpgmp_locations_foot"></div>
</div>';
$listing_place_holder = stripslashes( trim( $default_place_holder ) );

$form->add_element( 'textarea', 'map_all_control[wpgmp_categorydisplayformat]', array(
	'lable' => __( 'Listing Placeholder', WPGMP_TEXT_DOMAIN ),
	'value' => (($data['map_all_control']['wpgmp_categorydisplayformat']) ? $data['map_all_control']['wpgmp_categorydisplayformat'] : $listing_place_holder),
	'desc' => __( 'Use placeholder - {marker_id}, {marker_zoom}, {marker_title}, {marker_address}, {marker_city}, {marker_state}, {marker_country}, {marker_postal_code}, {marker_message}, {marker_latitude}, {marker_longitude}, {marker_icon},{marker_category},{extra_field_slug_here},{%custom_field_slug_here%}', WPGMP_TEXT_DOMAIN ),
	'textarea_rows' => 10,
	'textarea_name' => 'map_all_control[wpgmp_categorydisplayformat]',
	'class' => 'form-control wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'select', 'map_all_control[wpgmp_categorydisplaysort]', array(
	'lable' => __( 'Sort By', WPGMP_TEXT_DOMAIN ),
	'current' => $data['map_all_control']['wpgmp_categorydisplaysort'],
	'desc' => __( 'Select Sort By.', WPGMP_TEXT_DOMAIN ),
	'options' => array( 'title' => __( 'Title',WPGMP_TEXT_DOMAIN ),'address' => __( 'Address',WPGMP_TEXT_DOMAIN ), 'category' => __( 'Category',WPGMP_TEXT_DOMAIN ) ),
	'class' => 'form-control wpgmp_display_listing',
	'show' => 'false',
));

$form->add_element( 'group', 'map_geojson_setting', array(
	'value' => __( 'GEOJSON', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'text', 'map_all_control[geojson_url]', array(
	'lable' => __( 'Paste GEO JSON URL', WPGMP_TEXT_DOMAIN ),
	'value' => $data['map_all_control']['geojson_url'],
	'desc' => __( 'Enter GEO JSON Url', WPGMP_TEXT_DOMAIN ),
	'class' => 'form-control',
));
