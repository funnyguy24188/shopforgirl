<?php
/**
 * This class used to backup all tables for this plugins.
 * @author Flipper Code <hello@flippercode.com>
 * @version 3.0.0
 * @package Maps
 */

if ( class_exists( 'WP_List_Table_Helper' ) and ! class_exists( 'Wpgmp_Backup_Table' ) ) {

	/**
	 * Display backup manager.
	 */
	class Wpgmp_Backup_Table extends WP_List_Table_Helper {
		/**
		 * Intialize manage backup table.
		 * @param array $tableinfo Table's properties.
		 */
		public function __construct($tableinfo) {
			parent::__construct( $tableinfo ); }
		/**
		 * Show backup file name.
		 * @param  array $item Backup row.
		 * @return string      File Path.
		 */
		function column_backup_file_name($item) {

			$file_path = WPGMP_BACKUP_URL.$item->backup_file_name;
			if ( isset( $_REQUEST['page'] ) ) {
				$actions = array(
				'delete' => sprintf( '<a href="?page=%s&doaction=%s&'.$this->primary_col.'=%s">Delete</a>',sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ),'delete',$item->backup_id ),
				);
			}

			return sprintf( '%1$s %2$s', '<a href='.$file_path.">$item->backup_file_name</a>", $this->row_actions( $actions ) );
		}
		/**
		 * Show backup Import button.
		 * @param  array $item Backup row.
		 * @return string     Import button.
		 */
		function column_backup_import($item) {

			return sprintf(
				'<input type="button" data-backup="'.$item->backup_id.'" name="wpgmp_check_backup" class="btn btn-success btn-xs wpgmp_check_backup" value="Import" />'
			);
		}
		/**
		 * Delete Backup File.
		 * @param  int $id Backup record ID.
		 */
		public function process_delete($id) {
			// Function for deleting file physically.
			global $wpdb;
			$select_record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.TBL_BACKUPS.' WHERE backup_id=%d',$id ) );
			$file_path = WPGMP_BACKUP.$select_record->backup_file_name;
			if ( file_exists( $file_path ) ) {
				@unlink( $file_path ); }
		}

	}

	if ( 'upload_backup' == $_POST['operation'] ) {
		$respone_upload_backup = $response;
	} else {
		$respone_upload_backup = array();
	}

	if ( 'take_backup' == $_POST['operation'] ) {
		$respone_take_backup = $response;
	} else {
		$respone_take_backup = array();
	}

	$form  = new FlipperCode_HTML_Markup();
	$form->set_header( __( 'Take Backup', WPGMP_TEXT_DOMAIN ), $respone_take_backup );

	$form->add_element('hidden', 'operation', array(
		'value' => 'take_backup',
	));

	$form->add_element('message','backup_message',array(
		'value' => __( 'Click below to create a backup of all locations, maps, categories, terms and routes.',WPGMP_TEXT_DOMAIN ),
		'class' => 'alert alert-info',
	));

	$form->add_element('submit', 'wpgmp_save_backup', array(
		'value' => __( 'Plugin Backup',WPGMP_TEXT_DOMAIN ),
	));

	$form->render();

	$import_form = new FlipperCode_HTML_Markup();
	$import_form->set_header( __( 'Upload Backup', WPGMP_TEXT_DOMAIN ), $respone_upload_backup );
	$import_form->add_element('hidden', 'operation', array(
		'value' => 'upload_backup',
	));

	$import_form->add_element('file','uploaded_file',array(
		'label' => __( 'Choose File',WPGMP_TEXT_DOMAIN ),
		'desc' => __( 'Please upload backup.',WPGMP_TEXT_DOMAIN ),
		'class' => 'file_input',
	));
	$import_form->add_element('submit', 'wpgmp_backup_submit', array(
		'value' => __( 'Upload Backup',WPGMP_TEXT_DOMAIN ),
	));
	$import_form->render();

	global $wpdb;
	$columns = array(
	'backup_file_name'  => 'File Name',
	'backup_date' => 'Date',
	'backup_time' => 'Time',
	'backup_import' => 'Import',
	);
	$sortable  = array( 'backup_file_name','backup_date','backup_time','backup_import' );
	$tableinfo = array(
	'table' => $wpdb->prefix.'wpgmp_backups',
	'textdomain' => WPGMP_TEXT_DOMAIN,
	'singular_label' => 'backup',
	'plural_label' => 'backup',
	'admin_listing_page_name' => 'wpgmp_manage_backup',
	'admin_add_page_name' => 'wpgmp_manage_backup',
	'primary_col' => 'backup_id',
	'columns' => $columns,
	'sortable' => $sortable,
	'per_page' => 20,
	'actions' => array( 'delete' ),
	'col_showing_links' => 'backup_file_name',
	'show_add_button' => false,
	);
	return new Wpgmp_Backup_Table( $tableinfo );


}
