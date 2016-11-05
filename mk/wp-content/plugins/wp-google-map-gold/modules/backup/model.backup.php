<?php
/**
 * Class: WPGMP_Model_Backup
 * @author Flipper Code <hello@flippercode.com>
 * @version 3.0.0
 * @package Maps
 */

if ( ! class_exists( 'WPGMP_Model_Backup' ) ) {

	/**
	 * Backup model for Backup operation.
	 * @package Maps
	 * @author Flipper Code <hello@flippercode.com>
	 */
	class WPGMP_Model_Backup extends FlipperCode_Model_Base {

		/**
		 * Intialize Backup object.
		 */
		function __construct() {

			$this->table = TBL_BACKUPS;
			$this->unique = 'backup_id';

		}
		/**
		 * Admin menu for Backup Operation
		 * @return array Admin menu navigation(s).
		 */
		function navigation() {
			return array(
			'wpgmp_manage_backup' => __( 'Manage Backups', WPGMP_TEXT_DOMAIN ),
			);
		}
		/**
		 * Install table associated with Location entity.
		 * @return string SQL query to install map_locations table.
		 */
		function install() {
			global $wpdb;
			$map_backups = 'CREATE TABLE '.$wpdb->prefix.'wpgmp_backups (
			backup_id int(11) NOT NULL AUTO_INCREMENT,
			backup_file_name varchar(255) DEFAULT NULL,
			backup_date varchar(255) DEFAULT NULL,
			backup_time varchar(255) DEFAULT NULL,
			backup_date_time varchar(255) DEFAULT NULL,
			PRIMARY KEY  (backup_id)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
			return $map_backups;
		}
		/**
		 * Upload backup from .sql file.
		 * @return string Success or Error response.
		 */
		public function upload_backup() {
			global $_POST;
			if ( isset( $_REQUEST['_wpnonce'] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );

				if ( ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

					die( 'Cheating...' );

				} else {
					$data = $_POST;
				}
			}

			if ( ( ! empty( $_FILES['uploaded_file'] )) && ( 0 == $_FILES['uploaded_file']['error'] ) ) {

				$filename = basename( sanitize_file_name( wp_unslash( $_FILES['uploaded_file']['name'] ) ) );
				$ext = substr( $filename, strrpos( $filename, '.' ) + 1 );

				if ( 'sql' == $ext ) {
					$file_delimiter = ';';
					$this->import_sql( $_FILES['uploaded_file']['tmp_name'], $file_delimiter );
					$response['success'] = __( 'Backup uploaded Successfully.', WPGMP_TEXT_DOMAIN );
				} else {
					$response['error'] = __( 'Only .sql file are accepted for upload.', WPGMP_TEXT_DOMAIN );
				}
			} else {

				$response['error'] = __( 'Please choose a sql file.', WPGMP_TEXT_DOMAIN );
			}
			return $response;

		}
		/**
		 * Take backup to .sql file.
		 * @return string Success or Error response.
		 */
		public function take_backup() {

			if ( isset( $_REQUEST['_wpnonce'] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );

				if ( ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

					die( 'Cheating...' );

				} else {
					$data = $_POST;
				}
			}
			if ( isset( $_POST['wpgmp_save_backup'] ) ) {

				$response = array();
				$backup_tables = array( TBL_LOCATION,TBL_GROUPMAP,TBL_MAP,TBL_ROUTES );
				$tables = implode( ',', $backup_tables );
				$backup_response = $this->backup_database( $tables );
				if ( $backup_response ) {
					$response['success']  = __( 'Backup has been taken successfully.',WPGMP_TEXT_DOMAIN );
				} else { $response['success']  = __( 'A Problem encountered while taking backup!',WPGMP_TEXT_DOMAIN ); }
				return $response;
			}
		}
		/**
		 * Import backup from database record.
		 */
		public function import_backup() {

			if ( isset( $_REQUEST['_wpnonce'] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );

				if ( ! wp_verify_nonce( $nonce, 'wpgmp-nonce' ) ) {

					die( 'Cheating...' );

				} else {
					$data = $_POST;
				}
			}

			global $wpdb;
			if ( ! empty( $_POST['row_id'] ) ) {

				$id = intval( $_POST['row_id'] );
				$select_record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.TBL_BACKUPS.' WHERE backup_id=%d',$id ) );

				$file_delimiter = ';';
				$file_path = WPGMP_BACKUP.$select_record->backup_file_name;

				if ( file_exists( $file_path ) ) {
					$this->import_sql( $file_path, $file_delimiter ); 
					$response['success'] = __( 'Backup Import Successfully.', WPGMP_TEXT_DOMAIN );
				} else {
					$response['error'] = __( '.sql imported is missing.', WPGMP_TEXT_DOMAIN );
				}
			}
			return $response;
		}

		/**
		 * Read .sql file and execute wpdb query.
		 * @param  string $file      .sql File path.
		 * @param  string $delimiter Sql delimiter.
		 */
		public function import_sql($file, $delimiter = ';') {

			global $wpdb;
			$handle = fopen( $file, 'r' );
			$sql = '';

			if ( $handle ) {

				while ( ($line = fgets( $handle, 4096 )) !== false ) {

					$sql .= trim( ' ' . trim( $line ) );

					if ( substr( $sql, -strlen( $delimiter ) ) == $delimiter ) {
						$wpdb->query( $sql );
						$sql = '';
					}
				}

				fclose( $handle );
			}
		}

		/**
		 * Read tables and create .sql file.
		 * @param  array $tables Tables names.
		 * @return boolean       true or false.
		 */
		protected function backup_database($tables) {

			global $wpdb;

			if ( ! empty( $tables ) ) {

				$tables = explode( ',', $tables );

				$return = 'SET FOREIGN_KEY_CHECKS=0;' . "\r\n";
				$return .= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . "\r\n";
				$return .= 'SET AUTOCOMMIT=0;' . "\r\n";
				$return .= 'START TRANSACTION;' . "\r\n";

				foreach ( $tables as $table ) {

					$backup_table_query = 'SELECT * FROM '.$table.'';
					$result = $wpdb->get_results( $backup_table_query );

					$num_fields = $wpdb->result->field_count;

					$data = 'DROP TABLE IF EXISTS '.$table.';';
					$bkp_create_table = $wpdb->get_row( $wpdb->prepare( 'SHOW Create Table '.$table.'',null ) );
					$data .= "\n\n".$bkp_create_table->{'Create Table'}.";\n\n";

					foreach ( $result as $key => $row ) {

						$valdata = 'INSERT INTO '.$table.' VALUES(';

						foreach ( $row as $row_key => $r ) {

							$r = addslashes( $r );
							$r = preg_replace( "/\n/","\\n",$r );

							if ( isset( $r ) ) {
								$valdata .= '"'.$r.'",';
							} else {
								$valdata .= '""';
							}
						}

						$valdata .= ");\n";

						$data .= str_replace( ',);', ');', $valdata );
					}

					$data .= "\n\n\n\n";
					$return .= $data;
					$return .= 'SET FOREIGN_KEY_CHECKS=1;' . "\r\n";
					$return .= 'COMMIT;';
				}
			}

			// SAVE THE BACKUP AS SQL FILE.
			$current_date_time = date( 'Y-m-d H:i:s' );
			$exp_ct = explode( ' ', $current_date_time );
			$backup_file_name = sanitize_file_name( 'wpgmp-backup-database'.time().$exp_ct[0].'.sql' );
			$map_backup_data = array(
			'backup_file_name' 	=> $backup_file_name,
			'backup_date' 		=> $exp_ct[0],
			'backup_time'		=> $exp_ct[1],
			'backup_date_time'	=> $current_date_time,
			);

			$wpdb->insert( TBL_BACKUPS,$map_backup_data );
			$handle = fopen( WPGMP_BACKUP.$backup_file_name, 'w' );
			fwrite( $handle, $return );
			fclose( $handle );

			if ( ! empty( $return ) ) {
				return true;
			} else { return false; }
		}

	}
}
