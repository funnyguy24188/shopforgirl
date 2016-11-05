<?php
/**
 * Class: WPGMP_Model_Location
 * @author Flipper Code <hello@flippercode.com>
 * @package Maps
 * @version 3.0.0
 */

if ( ! class_exists( 'WPGMP_Model_Location' ) ) {

	/**
	 * Location model for CRUD operation.
	 * @package Maps
	 * @author Flipper Code <hello@flippercode.com>
	 */
	class WPGMP_Model_Location extends FlipperCode_Model_Base
	{
		/**
		 * Validations on location properies.
		 * @var array
		 */
		public $validations = array(
		'location_title' => array( 'req' => 'Please enter location title.' ),
		'location_latitude' => array( 'req' => 'Please enter location latitude.','latlng' => 'Invalid latitude.' ),
		'location_longitude' => array( 'req' => 'Please enter location longitude.', 'latlng' => 'Invalid longitude.' ),
			);
		/**
		 * Intialize location object.
		 */
		public function __construct() {
			$this->table = TBL_LOCATION;
			$this->unique = 'location_id';
		}
		/**
		 * Admin menu for CRUD Operation
		 * @return array Admin meny navigation(s).
		 */
		public function navigation() {

			return array(
			'wpgmp_form_location' => __( 'Add Location', WPGMP_TEXT_DOMAIN ),
			'wpgmp_manage_location' => __( 'Manage Locations', WPGMP_TEXT_DOMAIN ),
			'wpgmp_import_location' => __( 'Import Locations', WPGMP_TEXT_DOMAIN ),
			);
		}
		/**
		 * Install table associated with Location entity.
		 * @return string SQL query to install map_locations table.
		 */
		public function install() {

			global $wpdb;
			$map_location = 'CREATE TABLE '.$wpdb->prefix.'map_locations (
location_id int(11) NOT NULL AUTO_INCREMENT,
location_title varchar(255) DEFAULT NULL,
location_address varchar(255) DEFAULT NULL,
location_draggable varchar(255) DEFAULT NULL,
location_infowindow_default_open varchar(255) DEFAULT NULL,
location_animation varchar(255) DEFAULT NULL,
location_latitude varchar(255) DEFAULT NULL,
location_longitude varchar(255) DEFAULT NULL,
location_city varchar(255) DEFAULT NULL,
location_state varchar(255) DEFAULT NULL,
location_country varchar(255) DEFAULT NULL,
location_postal_code varchar(255) DEFAULT NULL,
location_zoom int(11) DEFAULT NULL,
location_author int(11) DEFAULT NULL,
location_messages text DEFAULT NULL,
location_settings text DEFAULT NULL,
location_group_map text DEFAULT NULL,
location_extrafields text DEFAULT NULL,
PRIMARY KEY  (location_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;';

			return $map_location;
		}
		/**
		 * Get Location(s)
		 * @param  array $where  Conditional statement.
		 * @return array         Array of Location object(s).
		 */
		public function fetch($where = array()) {

			$objects = $this->get( $this->table, $where );

			if ( isset( $objects ) ) {
				foreach ( $objects as $object ) {
					$object->location_settings = unserialize( $object->location_settings );
					$object->location_extrafields = unserialize( $object->location_extrafields );
					// Data convertion for version < 3.0.
					$is_category = unserialize( $object->location_group_map );
					if ( ! is_array( $is_category ) ) {
						$object->location_group_map = array( $object->location_group_map );
					} else {
						$object->location_group_map = $is_category;
					}
					// Data convertion for version < 3.0.
					$is_message = unserialize( base64_decode( $object->location_messages ) );
					if ( is_array( $is_message ) ) {
						$object->location_messages = $is_message['googlemap_infowindow_message_one'];
					}
				}
				return $objects;
			}
		}

		/**
		 * Add or Edit Operation.
		 */
		public function save() {
			global $_POST;
			$entityID = '';
			if ( isset( $_REQUEST['_wpnonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ); }

			if ( isset( $nonce ) and ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

				die( 'Cheating...' );

			}

			$this->verify( $_POST );

			if ( is_array( $this->errors ) and ! empty( $this->errors ) ) {
				$this->throw_errors();
			}

			if ( isset( $_POST['entityID'] ) ) {
				$entityID = intval( wp_unslash( $_POST['entityID'] ) );
			}

			if ( isset( $_POST['location_messages'] ) ) {
				$data['location_messages'] = wp_unslash( $_POST['location_messages'] );
			}
			if ( isset( $_POST['extensions_fields'] ) ) {
				$_POST['location_settings']['extensions_fields'] = $_POST['extensions_fields'];
			}

			$data['location_settings'] = serialize( wp_unslash( $_POST['location_settings'] ) );
			$data['location_group_map'] = serialize( wp_unslash( $_POST['location_group_map'] ) );
			$extra_fields = wp_unslash( $_POST['location_extrafields'] );
			$data['location_extrafields'] = serialize( wp_unslash( $extra_fields ) );
			$data['location_title'] 		= sanitize_text_field( wp_unslash( $_POST['location_title'] ) );
			$data['location_address'] 		= sanitize_text_field( wp_unslash( $_POST['location_address'] ) );
			$data['location_latitude'] 		= sanitize_text_field( wp_unslash( $_POST['location_latitude'] ) );
			$data['location_longitude'] 	= sanitize_text_field( wp_unslash( $_POST['location_longitude'] ) );
			$data['location_city'] 			= sanitize_text_field( wp_unslash( $_POST['location_city'] ) );
			$data['location_state'] 		= sanitize_text_field( wp_unslash( $_POST['location_state'] ) );
			$data['location_country'] 		= sanitize_text_field( wp_unslash( $_POST['location_country'] ) );
			$data['location_postal_code'] 	= sanitize_text_field( wp_unslash( $_POST['location_postal_code'] ) );
			$data['location_zoom']  		= intval( wp_unslash( $_POST['location_zoom'] ) );
			$data['location_draggable']  	= sanitize_text_field( wp_unslash( $_POST['location_draggable'] ) );
			$data['location_infowindow_default_open']  = sanitize_text_field( wp_unslash( $_POST['location_infowindow_default_open'] ) );
			$data['location_animation']  	= sanitize_text_field( wp_unslash( $_POST['location_animation'] ) );
			$data['location_author']  		= get_current_user_id();
			if ( $entityID > 0 ) {
				$where[ $this->unique ] = $entityID;
			} else {
				$where = '';
			}

			$result = FlipperCode_Database::insert_or_update( $this->table, $data, $where );

			if ( false === $result ) {
				$response['error'] = __( 'Something went wrong. Please try again.',WPGMP_TEXT_DOMAIN );
			} elseif ( $entityID > 0 ) {
				$response['success'] = __( 'Location updated successfully',WPGMP_TEXT_DOMAIN );
			} else {
				$response['success'] = __( 'Location added successfully.',WPGMP_TEXT_DOMAIN );
			}
			return $response;
		}

		/**
		 * Delete location object by id.
		 */
		public function delete() {
			if ( isset( $_GET['location_id'] ) ) {
				$id = intval( wp_unslash( $_GET['location_id'] ) );
				$connection = FlipperCode_Database::connect();
				$this->query = $connection->prepare( "DELETE FROM $this->table WHERE $this->unique='%d'", $id );
				return FlipperCode_Database::non_query( $this->query, $connection );
			}
		}
		/**
		 * Export data into csv
		 * @param  string $type File Type.
		 */
		function export($type = 'csv') {
			$selected_locations = array();
			if(isset($_POST['id']) and is_array($_POST['id'])) {
				$selected_locations = $_POST['id'];
			}

			$all_locations = $this->fetch();
			$file_name = sanitize_file_name( 'location_'.$type.'_'.time() );
			$modelFactory = new WPGMP_Model();
			$category = $modelFactory->create_object( 'group_map' );
			$categories = $category->fetch();
			if ( ! empty( $categories ) ) {
				$categories_data = array();
				foreach ( $categories as $cat ) {
					$categories_data[ $cat->group_map_id ] = $cat->group_map_title;
				}
			}
			// get extra columns added.
			$extra_fields = unserialize(get_option( 'wpgmp_location_extrafields' ));
			if(!is_array($extra_fields)) {
				$extra_fields = array();
			}
			foreach ( $all_locations as $location ) {
				if( !empty($selected_locations) and !in_array($location->location_id, $selected_locations) ) {
					continue;
				}
				$location_extrafields = $location->location_extrafields;
				$assigned_categories = array();
				if ( isset( $location->location_group_map ) and is_array( $location->location_group_map ) ) {
					foreach ( $location->location_group_map as $c => $cat ) {
						$assigned_categories[] = $categories_data[ $cat ];
					}
				}
				$assigned_categories = implode( ',',$assigned_categories );
				$loc_data = array(
				'location_title' => $location->location_title,
				'location_address' => $location->location_address,
				'location_latitude' => $location->location_latitude,
				'location_longitude' => $location->location_longitude,
				'location_city' => $location->location_city,
				'location_state' => $location->location_state,
				'location_country' => $location->location_country,
				'location_postal_code' => $location->location_postal_code,
				'location_messages' => $location->location_messages,
				'location_group_map' => $assigned_categories,
				);
				if(is_array($location_extrafields) and !empty($location_extrafields)) {
					foreach($location_extrafields as $extra_name => $extra_value) {
						$loc_data[$extra_name]=$extra_value;
					}
				}
				$data_locations[] = $loc_data;
			}
			$head_columns = array(
				'Title',
				'Address',
				'Latitude',
				'Longitude',
				'City',
				'State',
				'Country',
				'Postal Code',
				'Message',
				'Categories',
			);
			$head_columns = array_merge($head_columns,$extra_fields);
			$exporter = new FlipperCode_Export_Import($head_columns,$data_locations);
			$exporter->export( $type,$file_name );
			die();
		}
		/**
		 * Import Location via CSV,JSON,XML and Excel.
		 * @return array Success or Failure error message.
		 */
		public function import_location() {
			$result = false;
			if ( isset( $_REQUEST['_wpnonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ); }

			if ( ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

				die( 'Cheating...' );

			}
			if ( isset( $_POST['import_loc'] ) ) {
				if ( isset( $_FILES['import_file']['tmp_name'] ) and '' == sanitize_file_name( wp_unslash( $_FILES['import_file']['tmp_name'] ) ) ) {
					$response['error'] = __( 'Please select file to be imported.', WPGMP_TEXT_DOMAIN );
				} elseif ( isset( $_FILES['import_file']['name'] ) and ! $this->validate_extension( sanitize_file_name( wp_unslash( $_FILES['import_file']['name'] ) ) ) ) {
					$response['error'] = __( 'Please upload a valid csv file.', WPGMP_TEXT_DOMAIN );
				} else {
					$file_extension = explode( '.', sanitize_file_name( wp_unslash( $_FILES['import_file']['name'] ) ) );
					$importer = new FlipperCode_Export_Import();
					$file_data = $importer->import( $file_extension[1],'import_file',sanitize_text_field($_POST['wpgmp_csv_delimiter']) );
					$datas = array();
					if ( ! empty( $file_data ) ) {
						$modelFactory = new WPGMP_Model();
						$category = $modelFactory->create_object( 'group_map' );
						$categories = $category->fetch();
						if ( ! empty( $categories ) ) {
							$categories_data = array();
							foreach ( $categories as $cat ) {
								$categories_data[ $cat->group_map_id ] = strtolower( sanitize_text_field( $cat->group_map_title ) );
							}
						}

						foreach ( $file_data as $data ) {
							$category_ids = array();

							if ( isset( $data['title'] ) ) {
								$datas['location_title'] = $data['title'];
							} else if ( isset( $data['location_title'] ) ) {
								$datas['location_title'] = $data['location_title'];
							}

							if ( isset( $data['address'] ) ) {
								$datas['location_address'] = $data['address'];
							} else if ( isset( $data['location_title'] ) ) {
								$datas['location_address'] = $data['location_address'];
							}

							if ( isset( $data['latitude'] ) ) {
								$datas['location_latitude'] = $data['latitude'];
							} else if ( isset( $data['location_latitude'] ) ) {
								$datas['location_latitude'] = $data['location_latitude'];
							} else {
								continue;
							}

							if ( isset( $data['longitude'] ) ) {
								$datas['location_longitude'] = $data['longitude'];
							} else if ( isset( $data['location_longitude'] ) ) {
								$datas['location_longitude'] = $data['location_longitude'];
							} else {
								continue;
							}

							if( !is_numeric($datas['location_longitude']) )	{
								continue;
							}

							if( !is_numeric($datas['location_latitude']) )	{
								continue;
							}

							if ( isset( $data['city'] ) ) {
								$datas['location_city'] = $data['city'];
							} else if ( isset( $data['location_city'] ) ) {
								$datas['location_city'] = $data['location_city'];
							}

							if ( isset( $data['state'] ) ) {
								$datas['location_state'] = $data['state'];
							} else if ( isset( $data['location_state'] ) ) {
								$datas['location_city'] = $data['location_state'];
							}

							if ( isset( $data['country'] ) ) {
								$datas['location_country'] = $data['country'];
							} else if ( isset( $data['location_city'] ) ) {
								$datas['location_country'] = $data['location_country'];
							}

							if ( isset( $data['postal-code'] ) ) {
								$datas['location_postal_code'] = $data['postal-code'];
							} else if ( isset( $data['location_city'] ) ) {
								$datas['location_postal_code'] = $data['location_postal_code'];
							}

							if ( isset( $data['message'] ) ) {
								$datas['location_messages'] = $data['message'];
							} else if ( isset( $data['location_messages'] ) ) {
								$datas['location_messages'] = $data['location_messages'];
							}

							if ( isset( $data['location_group_map'] ) ) {
								$data['categories'] = $data['location_group_map'];
							}

							$datas['location_messages'] = $data['message'];
							// Find out categories id or insert new category.
							if ( isset( $data['categories'] ) and ! empty( $data['categories'] ) ) {
								$all_cat = explode( ',', strtolower( $data['categories'] ) );
								if ( is_array( $all_cat ) ) {
									foreach ( $all_cat as $cat ) {
										$cat_id = array_search( sanitize_text_field( $cat ), (array) $categories_data );
										if ( false == $cat_id ) {
											// Create a new category.
											$new_cat_id = FlipperCode_Database::insert_or_update( TBL_GROUPMAP, array(
												'group_map_title' => sanitize_text_field( $cat ),
												'group_marker' => WPGMP_IMAGES.'default_marker.png',
											) );
											$category_ids[] = $new_cat_id;
											$categories_data[ $new_cat_id ] = sanitize_text_field( $cat );

										} else {
											$category_ids[] = $cat_id;
										}
									}
								}
							}
							if ( is_array( $category_ids ) and ! empty( $category_ids ) ) {
								$datas['location_group_map'] = serialize( (array) $category_ids );
							}
							// Rest Columns are extra fields.
							$extra_fields = array();
							$core_fields = array(
							'title',
							'address',
							'latitude',
							'longitude',
							'city',
							'state',
							'country',
							'postal-code',
							'message',
							'categories',
							'draggable',
							'disable infowindow',
							'infowindow default open',
							'animation'
							);
							if ( is_array( $data ) ) {
								$extra_count = 0;
								$current_extra_fields = unserialize(get_option('wpgmp_location_extrafields'));
								if(!is_array($current_extra_fields)) {
									$current_extra_fields = array();
								}
								foreach ( $data as $key => $value ) {
									if ( ! in_array( strtolower($key), $core_fields ) and $key != '' ) {
										$current_extra_fields[] = $key;
									}
								}
								$current_extra_fields = array_unique($current_extra_fields);
								update_option( 'wpgmp_location_extrafields', serialize(  $current_extra_fields  ) );
								//Data entry.
								foreach ( $data as $key => $value ) {
									if ( ! in_array( $key, $core_fields ) and $key != '' ) {
										$extra_fields[sanitize_title($key)] =  $value;
										$extra_count++;
									}
								}
								$datas['location_extrafields'] = serialize( $extra_fields );
							}

							$result = FlipperCode_Database::insert_or_update( $this->table, $datas );
						}
					}

					if ( false === $result ) {
						$response['error'] = __( 'Something went wrong. Please try again.',WPGMP_TEXT_DOMAIN );
					} elseif ( $entityID > 0 ) {
						$response['success'] = __( 'Location updated successfully',WPGMP_TEXT_DOMAIN );
					} else {
						$response['success'] = __( 'Location added successfully.',WPGMP_TEXT_DOMAIN );
					}
					return $response;

				}
				return $response;
			}
		}
	}
}
