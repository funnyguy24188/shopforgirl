<?php
/**
 * Class: WPGMP_Model_Permissions
 * @author Flipper Code <hello@flippercode.com>
 * @version 3.0.0
 * @package Maps
 */

if ( ! class_exists( 'WPGMP_Model_Permissions' ) ) {

	/**
	 * Permission model for Plugin Access Permission.
	 * @package Maps
	 * @author Flipper Code <hello@flippercode.com>
	 */
	class WPGMP_Model_Permissions extends FlipperCode_Model_Base {
		/**
		 * Intialize Permission object.
		 */
		function __construct() {
		}
		/**
		 * Admin menu for Permission Operation
		 * @return array Admin menu navigation(s).
		 */
		function navigation() {
			return array(
			'wpgmp_manage_permissions' => __( 'Manage Permissions', WPGMP_TEXT_DOMAIN ),
			);
		}
		/**
		 * Save Permissions
		 */
		function save() {
			global $_POST;
			if ( isset( $_REQUEST['_wpnonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ); }

			if ( isset( $nonce ) and ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

				die( 'Cheating...' );

			}
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
			$this->verify( $_POST );

			if ( is_array( $this->errors ) and ! empty( $this->errors ) ) {
				$this->throw_errors();
			}
			if ( isset( $_POST['wpgmp_save_permission'] ) ) {
				$wpgmp_map_permissions = wp_unslash( $_POST['wpgmp_map_permissions'] );
				if ( ! empty( $wpgmp_roles ) ) {
					foreach ( $wpgmp_roles as $wpgmp_role_key => $wpgmp_role_value ) {
						if ( $wpgmp_role_key == 'administrator' && is_admin() && current_user_can( 'manage_options' ) ) {
							continue; }

						$role = get_role( $wpgmp_role_key );

						if ( ! empty( $wpgmp_permissions ) ) {
							foreach ( $wpgmp_permissions as $wpgmp_mkey => $wpgmp_mvalue ) {
								if ( isset( $wpgmp_map_permissions[$wpgmp_role_key][$wpgmp_mkey] ) ) {
									$role->add_cap( $wpgmp_mkey );
								} else {
									$role->remove_cap( $wpgmp_mkey );
								}
							}
						}
					}
				}
			}
			$response['success'] = __( 'Permissions saved successfully.', WPGMP_TEXT_DOMAIN );
			return $response;
		}
		
	}
}
