<?php
/**
 * Route Direction setting for google maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_route_settings', array(
	'value' => __( 'Route Direction Settings', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));

$form->add_element( 'checkbox', 'map_route_direction_setting[route_direction]', array(
	'lable' => __( 'Turn On Map Route Directions', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_route_direction',
	'current' => $data['map_route_direction_setting']['route_direction'],
	'desc' => __( 'Please check to enable map route directions.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '#map_route_direction_setting, #no_route_message' ),
));

$routeobj = $modelFactory->create_object( 'route' );
$routes_results = $routeobj->fetch();
if ( ! empty( $routes_results ) ) {
	for ( $i = 0; $i < count( $routes_results ); $i++ ) {
		$route_checkbox = $form->field_checkbox('map_route_direction_setting[specific_routes][]',array(
			'value' => $routes_results[ $i ]->route_id,
			'current' => ((in_array( $routes_results[ $i ]->route_id, (array) $data['map_route_direction_setting']['specific_routes'] )) ? $routes_results[ $i ]->route_id : ''),
			'class' => 'chkbox_class',
			'before' => '<div class="col-md-1">',
			'after' => '</div>',
			));
		$all_routes[] = array( $route_checkbox,$routes_results[ $i ]->route_title,$routes_results[ $i ]->route_travel_mode,$routes_results[ $i ]->route_unit_system );
	}
}
$form->add_element( 'table', 'map_route_direction_setting[specific_routes]', array(
	'heading' => array( __( 'Select',WPGMP_TEXT_DOMAIN ) ,__( 'Route Title',WPGMP_TEXT_DOMAIN ),__( 'Travel Mode',WPGMP_TEXT_DOMAIN ),__( 'Unit System',WPGMP_TEXT_DOMAIN ) ),
	'data' => $all_routes,
	'id' => 'map_route_direction_setting',
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
	'current' => $data['map_route_direction_setting']['specific_routes'],
	'show' => 'false',
));
if ( empty( $all_routes ) ) {
	$url = admin_url( 'admin.php?page=wpgmp_form_route' );
	$link = sprintf( wp_kses( __( 'No route found. <a target="_blank" href="%s">Click here</a> to create a route.', WPGMP_TEXT_DOMAIN ), array( 'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $url ) );
	$form->add_element( 'message', 'no_route_message', array(
		'value' => $link,
		'class' => 'alert alert-danger false',
		'id' => 'no_route_message',
		'before' => '<div class="col-md-12">',
		'after' => '</div>',
	));
}
