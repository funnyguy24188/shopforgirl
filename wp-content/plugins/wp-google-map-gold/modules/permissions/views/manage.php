<?php
/**
 * This class used to manage permissions in backend.
 * @author Flipper Code <hello@flippercode.com>
 * @version 1.0.0
 * @package Maps
 */

global $wp_roles;
$wpgmp_roles = $wp_roles->get_names();
unset($wpgmp_roles['administrator']);
$wpgmp_permissions = array(
'wpgmp_admin_overview'          => 'Map Overview',
'wpgmp_form_location'            => 'Add Locations',
'wpgmp_manage_location'         => 'Manage Locations',
'wpgmp_import_location'         => 'Import Locations',
'wpgmp_form_map'              => 'Create Map',
'wpgmp_manage_map'              => 'Manage Map',
'wpgmp_manage_drawing'          => 'Drawing',
'wpgmp_form_group_map'     => 'Add Marker Category',
'wpgmp_manage_group_map'  => 'Manage Marker Category',
'wpgmp_form_route'              => 'Add Routes',
'wpgmp_manage_route'           => 'Manage Routes',
'wpgmp_settings'         		=> 'Settings',
);

$form = new FlipperCode_HTML_Markup();
$form->set_header( __( 'Manage Permission(s)', WPGMP_TEXT_DOMAIN ), $response );
if ( ! empty( $wpgmp_permissions ) ) {
	$count = 0;
	foreach ( $wpgmp_permissions as $wpgmp_mkey => $wpgmp_mvalue ) {
		$permission_row[ $count ][0] = $wpgmp_mvalue;
		foreach ( $wpgmp_roles as $wpgmp_role_key => $wpgmp_role_value ) {
			$urole = get_role( $wpgmp_role_key );
			$permission_row[ $count ][] = $form->field_checkbox( 'wpgmp_map_permissions['.$wpgmp_role_key.']['.$wpgmp_mkey.']', array(
				'value' => 'true',
				'current' => ((@array_key_exists( $wpgmp_mkey,$urole->capabilities ) == true) ? 'true' : 'false' ),
				'before' => '<div class="col-md-1">',
				'after' => '</div>',
				'class' => 'chkbox_class',
				) );
		}
		$count++;
	}
}
$form->add_element( 'table', 'wpgmp_save_permission_table', array(
		'heading' => array_merge( array( 'Page' ),$wpgmp_roles ),
		'data' => $permission_row,
		'before' => '<div class="col-md-11">',
		'after' => '</div>',
	));

$form->add_element('submit','wpgmp_save_permission',array(
	'value' => __( 'Save Permissions',WPGMP_TEXT_DOMAIN ),
));

$form->add_element( 'hidden', 'operation', array(
	'value' => 'save',
));

$form->render();

