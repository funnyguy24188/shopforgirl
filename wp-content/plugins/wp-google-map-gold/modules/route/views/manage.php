<?php
/**
 * Manage Route(s)
 * @package Maps
 */

if ( class_exists( 'WP_List_Table_Helper' ) and ! class_exists( 'Wpgmp_Route_Table' ) ) {

	/**
	 * Display route(s) manager.
	 */
	class Wpgmp_Route_Table extends WP_List_Table_Helper {

		/**
	  	 * Intialize manage category table.
	  	 * @param array $tableinfo Table's properties.
	  	 */
		public function __construct($tableinfo) {
			parent::__construct( $tableinfo );
		}
		/**
	  	 * Output for Start Location column.
	  	 * @param array $item Route Row.
	  	 */
		public function column_route_start_location($item) {
			$modelFactory = new WPGMP_Model();
			$location_obj = $modelFactory->create_object( 'location' );
			$location = $location_obj->fetch( array( array( 'location_id', '=', intval( wp_unslash( $item->route_start_location ) ) ) ) );

			echo $location[0]->location_title;	}
		/**
	  	 * Output for End Location column.
	  	 * @param array $item Route Row.
	  	 */
		public function column_route_end_location($item) {
			$modelFactory = new WPGMP_Model();
			$location_obj = $modelFactory->create_object( 'location' );
			$location = $location_obj->fetch( array( array( 'location_id', '=', intval( wp_unslash( $item->route_end_location ) ) ) ) );

			echo $location[0]->location_title;	}
	}
	global $wpdb;
	$columns = array(
	'route_title'  => 'Title',
	'route_start_location' => 'Start Location',
	'route_end_location' => 'End Location',

	);
	$sortable  = array( 'route_title','route_start_location','route_end_location' );
	$tableinfo = array(
	'table' => $wpdb->prefix.'map_routes',
	'textdomain' => WPGMP_TEXT_DOMAIN,
	'singular_label' => 'route',
	'plural_label' => 'routes',
	'admin_listing_page_name' => 'wpgmp_manage_route',
	'admin_add_page_name' => 'wpgmp_form_route',
	'primary_col' => 'route_id',
	'columns' => $columns,
	'sortable' => $sortable,
	'per_page' => 20,
	'actions' => array( 'edit','delete' ),
	'col_showing_links' => 'route_title',
	);
	$obj = new Wpgmp_Route_Table( $tableinfo );

}

