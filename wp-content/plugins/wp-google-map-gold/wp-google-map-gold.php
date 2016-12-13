<?php
/**
 * Google_Maps_Pro class file.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 * @version 3.4.1
 */

/*
Plugin Name: WP Google Map Pro
Plugin URI: http://www.flippercode.com/
Description: (Gold Version) World's most advanced google maps plugin. Location, Category, Layers, Controls, Shapes, Routes, Directions, Marker clusters, Listing, Places and many more...
Author: flippercode
Author URI: http://www.flippercode.com/
Version: 3.4.1
Text Domain: wpgmp_google_map
Domain Path: /lang/
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( 'Google_Maps_Pro' ) ) {

	/**
	 * Main plugin class
	 * @author Flipper Code <hello@flippercode.com>
	 * @package Maps
	 */
	class Google_Maps_Pro
	{
		/**
		 * List of Modules.
		 * @var array
		 */
		private $modules = array();
		/**
		 * Intialize variables, files and call actions.
		 * @var array
		 */
		public function __construct() {
			error_reporting( E_ERROR | E_PARSE );
			$this->_define_constants();
			$this->_load_files();
			register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );
			if( is_multisite() ){
			  add_action( 'wpmu_new_blog', array( $this, 'wpgmp_on_blog_new_generate'), 10, 6 );
              add_filter( 'wpmu_drop_tables', array( $this, 'wpgmp_on_blog_delete') );
			}
			add_action( 'plugins_loaded', array( $this, 'load_plugin_languages' ) );
			add_action( 'init', array( $this, '_init' ) );
			add_action( 'widgets_init', array( $this, 'wpgmp_google_map_widget' ) );

		}
		/**
		 * Call WordPress hooks.
		 */
		function _init() {

			global $wpdb;

			// Actions.
			add_action( 'admin_menu', array( $this, 'create_menu' ) );
			add_action( 'media_upload_ell_insert_gmap_tab', array( $this, 'wpgmp_google_map_media_upload_tab' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wpgmp_frontend_scripts' ) );
			add_action( 'admin_print_scripts', array( $this, 'wpgmp_backend_styles' ) );
			add_action( 'admin_init', array( $this, 'wpgmp_export_data' ) );
			add_action( 'add_meta_boxes', array( $this, 'wpgmp_call_meta_box' ) );
			add_action( 'save_post', array( $this, 'wpgmp_save_meta_box_data' ) );

			// Filters.
			add_filter( 'media_upload_tabs', array( $this, 'wpgmp_google_map_tabs_filter' ) );

			// Shortodes.
			add_shortcode( 'put_wpgm', array( $this, 'wpgmp_show_location_in_map' ) );
			add_shortcode( 'display_map', array( $this, 'wpgmp_display_map' ) );

		}
		/**
		 * Export data into csv,xml,json or excel file
		 */
		function wpgmp_export_data() {

			if ( isset( $_POST['action'] ) and false != strstr( $_POST['action'],'export_' ) ) {
				$export_action = explode( '_',$_POST['action'] );
				if ( 3 == count( $export_action ) and 'export' == $export_action[0] ) {
					$model_class = 'WPGMP_Model_'.ucwords( $export_action[1] );
					$entity = new $model_class;
					$entity->export( $export_action[2] );
				}
			}
		}
		/**
		 * Register WP Google Map Widget
		 */
		function wpgmp_google_map_widget() {

			register_widget( 'WPGMP_Google_Map_Widget_Class' );
		}
		/**
		 * Display WP Google Map meta box on pages/posts and custom post type(s).
		 */
		function wpgmp_call_meta_box() {

			$screens = array( 'post', 'page' );

			$args = array(
			'public'  => true,
			'_builtin'  => false,
			);

			$custom_post_types = get_post_types( $args, 'names' );

			$screens = array_merge( $screens, $custom_post_types );

			$screens = apply_filters('wpgmp_meta_boxes',$screens);

			$selected_values = unserialize(get_option('wpgmp_allow_meta'));

			foreach ( $screens as $screen ) {

				if(is_array($selected_values)) {

					if(in_array($screen, $selected_values)) {
						continue;
					}

				}

				add_meta_box(
					'wpgmp_google_map_metabox',
					__( 'WP Google Map Pro', WPGMP_TEXT_DOMAIN ),
					array( $this, 'wpgmp_add_meta_box' ),
					$screen
				);
			}
		}
		/**
		 * Callback to display  wp google map pro meta box.
		 * @param  string $post Post Type.
		 */
		function wpgmp_add_meta_box($post) {

			global $wpdb;
			$modelFactory = new WPGMP_Model();
			$category_obj = $modelFactory->create_object( 'group_map' );
			$categories = $category_obj->fetch();
			$map_obj = $modelFactory->create_object( 'map' );
			$all_maps = $map_obj->fetch();
			$wpgmp_location_address = get_post_meta( $post->ID, '_wpgmp_location_address', true );
			$wpgmp_map_ids = get_post_meta( $post->ID, '_wpgmp_map_id', true );
			$wpgmp_map_id = unserialize( $wpgmp_map_ids );
			if ( ! is_array( $wpgmp_map_id ) ) {
				$wpgmp_map_id = array( $wpgmp_map_ids );
			}
			$wpgmp_metabox_marker_id = get_post_meta( $post->ID, '_wpgmp_metabox_marker_id', true );
			$wpgmp_metabox_latitude = get_post_meta( $post->ID, '_wpgmp_metabox_latitude', true );
			$wpgmp_metabox_longitude = get_post_meta( $post->ID, '_wpgmp_metabox_longitude', true );
			$wpgmp_metabox_location_redirect = get_post_meta( $post->ID, '_wpgmp_metabox_location_redirect', true );
			$wpgmp_metabox_custom_link = get_post_meta( $post->ID, '_wpgmp_metabox_custom_link', true );
			if ( isset( $_SERVER['HTTPS'] ) && ( 'on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS'] ) || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
				$wpgmp_apilocation = 'https';
			} else {
				$wpgmp_apilocation = 'http';
			}
			
			$language = get_option( 'wpgmp_language' );

			if ( $language == '' ) {
				$language = 'en';
			}

			if ( get_option( 'wpgmp_api_key' ) != '' ) {
				$wpgmp_apilocation .= '://maps.google.com/maps/api/js?key='.get_option( 'wpgmp_api_key' ).'&libraries=geometry,places,weather,panoramio,drawing&language='.$language;
			} else {
				$wpgmp_apilocation .= '://maps.google.com/maps/api/js?libraries=geometry,places,weather,panoramio,drawing&language='.$language;
			}

		?>
		<script src="<?php echo $wpgmp_apilocation; ?>"></script>
		<script>
		jQuery(document).ready(function($) {
  			var wpgmp_input = $("#wpgmp_metabox_location").val();
			var wpgmp_metabox_autocomplete = new google.maps.places.Autocomplete(wpgmp_metabox_location);
			google.maps.event.addListener(wpgmp_metabox_autocomplete, 'place_changed', function() {
			var metabox_place = wpgmp_metabox_autocomplete.getPlace();
			$(".wpgmp_metabox_latitude").val(metabox_place.geometry.location.lat());
			$(".wpgmp_metabox_longitude").val(metabox_place.geometry.location.lng());
			});

			$("select[name='wpgmp_metabox_location_redirect']").change(function() {
			var rval = $(this).val();
			if(rval=="custom_link")
			{
			$("#wpgmp_toggle_custom_link").show("slow");
			}
			else
			{
			$("#wpgmp_toggle_custom_link").hide("slow");
			}
			});
	});
		</script>

		<div class="wpgmp_metabox_container">
		<div class="row_metabox">
		<div class="wpgmp_metabox_left">
		<label for="wpgmp_metabox_location"><?php _e( 'Enter Location :', WPGMP_TEXT_DOMAIN ); ?></label>
	</div>
	<div class="wpgmp_metabox_right">
	<input type="text" id="wpgmp_metabox_location" class="wpgmp_metabox_location wpgmp_auto_suggest" name="wpgmp_metabox_location" value="<?php echo $wpgmp_location_address; ?>" size="25" />
	</div>
	</div>
	<div class="row_metabox">
	<div class="wpgmp_metabox_left">
	<label for="wpgmp_enter_location"><?php _e( 'Latitude', WPGMP_TEXT_DOMAIN ); ?>&nbsp;/&nbsp;<?php _e( 'Longitude', WPGMP_TEXT_DOMAIN ); ?>&nbsp;:</label>
	</div>
	<div class="wpgmp_metabox_right">
	<input type="text" class="wpgmp_metabox_latitude" id="wpgmp_metabox_latitude" name="wpgmp_metabox_latitude" value="<?php echo $wpgmp_metabox_latitude; ?>" placeholder="Latitude" />
	<input type="text" class="wpgmp_metabox_longitude" id="wpgmp_metabox_longitude" name="wpgmp_metabox_longitude" value="<?php echo $wpgmp_metabox_longitude; ?>" placeholder="Longitude" />
	</div>
	</div>
	<div class="row_metabox">
	<div class="wpgmp_metabox_left">
	<label><?php _e( 'Choose Marker Categories :', WPGMP_TEXT_DOMAIN ) ?></label>
	</div>
	<div class="wpgmp_metabox_right">
	<?php
	$selected_categories = unserialize( $wpgmp_metabox_marker_id );

	if ( ! is_array( $selected_categories ) ) {
		$selected_categories = array( $wpgmp_metabox_marker_id );
	}

	if ( $categories ) {
		foreach ( $categories as $category ) {
			if ( in_array( $category->group_map_id, $selected_categories ) ) {
				$s = "checked='checked'";
			} else {
				$s = '';
			}
		?>
		<p class="description">
		<input type="checkbox" id="wpgmp_location_group_map<?php echo $category->group_map_id; ?>" <?php echo $s; ?> name="wpgmp_metabox_marker_id[]" value="<?php echo $category->group_map_id; ?>">
	<?php echo $category->group_map_title; ?>
	</p>
	<?php
		}
	} else {
		echo '<p class="description">';
		_e( "Do you want to assign a category? Please create category <a href='".admin_url( 'admin.php?page=wpgmp_add_group_map' )."'>here</a>.", WPGMP_TEXT_DOMAIN );
		echo '</p>';
	}
		?>
		</div>
		</div>
		<div class="row_metabox">
		</div>
		<div class="row_metabox">
		<div class="wpgmp_metabox_left">
		<label for="wpgmp_enter_location"><?php _e( 'Location Redirect :', WPGMP_TEXT_DOMAIN ); ?></label>
	</div>
	<div class="wpgmp_metabox_right">
	<select name="wpgmp_metabox_location_redirect" id="wpgmp_metabox_location_redirect">
	<option value="marker"<?php selected( $wpgmp_metabox_location_redirect, 'marker' ); ?>>Marker</option>
	<option value="post"<?php selected( $wpgmp_metabox_location_redirect, 'post' ); ?>>Post</option>
	<option value="custom_link"<?php selected( $wpgmp_metabox_location_redirect, 'custom_link' ); ?>>Custom Link</option>
	</select>
	</div>
	</div>

	<?php
	if ( ! empty( $wpgmp_metabox_custom_link ) && 'custom_link' == $wpgmp_metabox_location_redirect ) {
		$display_custom_link = 'display:block';
	} else {
		$display_custom_link = 'display:none';
	}
		?>

		<div class="row_metabox" style="<?php echo $display_custom_link; ?>" id="wpgmp_toggle_custom_link">
	<div class="wpgmp_metabox_left">
	<label for="wpgmp_metabox_custom_link">&nbsp;</label>
	</div>
	<div class="wpgmp_metabox_right">
	<input type="textbox" value="<?php echo $wpgmp_metabox_custom_link; ?>" name="wpgmp_metabox_custom_link" class="wpgmp_metabox_location" />
	<p class="description"><?php _e( 'Please enter link.', WPGMP_TEXT_DOMAIN )?></p>
	</div>
	</div>
	<?php do_action( 'wpgmp_meta_box_fields' );?>
	<div class="row_metabox">
	<div class="wpgmp_metabox_left">
	<label><?php _e( 'Select Map :', WPGMP_TEXT_DOMAIN ) ?></label>
	</div>
	<div class="wpgmp_metabox_right">
	<p>
   <table>
	<?php

	if ( count( $all_maps ) > 0 ) {
		foreach ( $all_maps as $map ) :

			if ( is_array( $wpgmp_map_id ) and in_array( $map->map_id,$wpgmp_map_id ) ) {
				$c = 'checked=checked';
			} else { 	$c = ''; }

		?>
	   <tr>
		 <td><input <?php echo $c; ?> type="checkbox" name="wpgmp_metabox_mapid[]" value="<?php echo $map->map_id ?>">&nbsp; <?php echo $map->map_title; ?></td>
    </tr>
    <?php endforeach;
	} else {
		_e( 'Please <a href="'.admin_url( 'admin.php?page=wpgmp_create_map' ).'">create a map</a> first.', 'wpgmp_google_map' );

	}
	?>
   </table>
   <input type="hidden" name="wpgmp_hidden_flag" value="true" />
</p>

	</div>
	</div>

	</div>
	<?php
		}
		/**
		 * Save meta box data
		 * @param  int $post_id Post ID.
		 */
		function wpgmp_save_meta_box_data($post_id) {
			if ( isset( $_POST['wpgmp_hidden_flag'] ) ) {
				$wpgmp_enter_location = sanitize_text_field( wp_unslash( $_POST['wpgmp_metabox_location'] ) );
				$wpgmp_metabox_latitude = sanitize_text_field( wp_unslash( $_POST['wpgmp_metabox_latitude'] ) );
				$wpgmp_metabox_longitude = sanitize_text_field( wp_unslash( $_POST['wpgmp_metabox_longitude'] ) );
				$wpgmp_map_id = serialize( wp_unslash( $_POST['wpgmp_metabox_mapid'] ) );
				$wpgmp_metabox_marker_id = serialize( wp_unslash( $_POST['wpgmp_metabox_marker_id'] ) );
				$wpgmp_metabox_location_redirect = sanitize_text_field( wp_unslash( $_POST['wpgmp_metabox_location_redirect'] ) );
				$wpgmp_metabox_custom_link = sanitize_text_field( wp_unslash( $_POST['wpgmp_metabox_custom_link'] ) );
				$wpgmp_metabox_taxomomies_terms = serialize( wp_unslash( $_POST['wpgmp_metabox_taxomomies_terms'] ) );
				$wpgmp_extensions_fields = serialize( wp_unslash( $_POST['wpgmp_extensions_fields'] ) );

				// Update the meta field in the database.
				update_post_meta( $post_id, '_wpgmp_location_address', $wpgmp_enter_location );
				update_post_meta( $post_id, '_wpgmp_metabox_latitude', $wpgmp_metabox_latitude );
				update_post_meta( $post_id, '_wpgmp_metabox_longitude', $wpgmp_metabox_longitude );
				update_post_meta( $post_id, '_wpgmp_metabox_location_redirect', $wpgmp_metabox_location_redirect );
				update_post_meta( $post_id, '_wpgmp_metabox_custom_link', $wpgmp_metabox_custom_link );
				update_post_meta( $post_id, '_wpgmp_map_id', $wpgmp_map_id );
				update_post_meta( $post_id, '_wpgmp_metabox_marker_id', $wpgmp_metabox_marker_id );
				update_post_meta( $post_id, '_wpgmp_metabox_taxomomies_terms', $wpgmp_metabox_taxomomies_terms );
				update_post_meta( $post_id, '_wpgmp_extensions_fields', $wpgmp_extensions_fields );
			}
		}
		/**
		 * Eneque scripts at frontend.
		 */
		function wpgmp_frontend_scripts() {

			$scripts = array();
			wp_enqueue_script( 'jquery' );
			if ( isset( $_SERVER['HTTPS'] ) && ( 'on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS'] ) || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
				$wpgmp_apilocation = 'https';
			} else {
				$wpgmp_apilocation = 'http';
			}
			$language = get_option( 'wpgmp_language' );

			if ( $language == '' ) {
				$language = 'en';
			}

			if ( get_option( 'wpgmp_api_key' ) != '' ) {
				$wpgmp_apilocation .= '://maps.google.com/maps/api/js?key='.get_option( 'wpgmp_api_key' ).'&libraries=geometry,places,weather,panoramio,drawing&language='.$language;
			} else {
				$wpgmp_apilocation .= '://maps.google.com/maps/api/js?libraries=geometry,places,weather,panoramio,drawing&language='.$language;
			}

			$scripts[] = array(
			'handle'  => 'wpgmp-google-api',
			'src'   => $wpgmp_apilocation,
			'deps'    => array(),
			);

			$scripts[] = array(
			'handle'  => 'wpgmp-frontend',
			'src'   => WPGMP_JS.'frontend.js',
			'deps'    => array(),
			);

			$where = get_option( 'wpgmp_scripts_place' );
			if ( $where == 'header' ) {
				$where = false;
			} else {
				$where = true;
			}

			if ( $scripts ) {
				foreach ( $scripts as $script ) {
					wp_register_script( $script['handle'], $script['src'], $script['deps'], '', $where );
				}
			}

			$scripts = array();

			$scripts[] = array(
			'handle'  => 'wpgmp-google-map-main',
			'src'   => WPGMP_JS.'maps.js',
			'deps'    => array( 'wpgmp-google-api' ),
			);

			if ( $scripts ) {
				foreach ( $scripts as $script ) {
					wp_enqueue_script( $script['handle'], $script['src'], $script['deps'], '', $where );
				}
			}

			$frontend_styles = array(
			'wpgmp-frontend'  => WPGMP_CSS.'frontend.css',
			);

			if ( $frontend_styles ) {
				foreach ( $frontend_styles as $frontend_style_key => $frontend_style_value ) {
					wp_register_style( $frontend_style_key, $frontend_style_value );
				}
			}

		}
		/**
		 * Display map at the frontend using put_wpgmp shortcode.
		 * @param  array  $atts   Map Options.
		 * @param  string $content Content.
		 */
		function wpgmp_show_location_in_map($atts, $content = null) {
			error_reporting( E_ERROR | E_PARSE );

			try {
				$factoryObject = new WPGMP_Controller();
				$viewObject = $factoryObject->create_object( 'shortcode' );
				$output = $viewObject->display( 'put-wpgmp',$atts );
				 return $output;

			} catch (Exception $e) {
				echo WPGMP_Template::show_message( array( 'error' => $e->getMessage() ) );

			}

		}
		/**
		 * Display map at the frontend using display_map shortcode.
		 * @param  array $atts    Map Options.
		 */
		function wpgmp_display_map($atts) {

			try {
				$factoryObject = new WPGMP_Controller();
				$viewObject = $factoryObject->create_object( 'shortcode' );
				 $output = $viewObject->display( 'display-map',$atts );
				 return $output;

			} catch (Exception $e) {
				echo WPGMP_Template::show_message( array( 'error' => $e->getMessage() ) );

			}

		}
		/**
		 * Process slug and display view in the backend.
		 */
		function processor() {
			error_reporting( E_ERROR | E_PARSE );

			$return = '';
			if ( isset( $_GET['page'] ) ) {
				$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			} else {
				$page = 'wpgmp_view_overview';
			}

			$pageData = explode( '_', $page );
			$obj_type = $pageData[2];
			$obj_operation = $pageData[1];

			if ( count( $pageData ) < 3 ) {
				die( 'Cheating!' );
			}

			try {
				if ( count( $pageData ) > 3 ) {
					$obj_type = $pageData[2].'_'.$pageData[3];
				}

				$factoryObject = new WPGMP_Controller();
				$viewObject = $factoryObject->create_object( $obj_type );
				$viewObject->display( $obj_operation );

			} catch (Exception $e) {
				echo WPGMP_Template::show_message( array( 'error' => $e->getMessage() ) );

			}

		}
		/**
		 * Create backend navigation.
		 */
		function create_menu() {

			global $navigations;

			$pagehook1 = add_menu_page(
				__( 'WP Google Map Pro', WPGMP_TEXT_DOMAIN ),
				__( 'WP Google Map Pro', WPGMP_TEXT_DOMAIN ),
				'wpgmp_admin_overview',
				WPGMP_SLUG,
				array( $this,'processor' ),
				WPGMP_IMAGES.'/default_marker.png'
			);

			if ( current_user_can( 'manage_options' )  ) {
								$role = get_role( 'administrator' );
								$role->add_cap( 'wpgmp_admin_overview' );
			}

			$this->load_modules_menu();

			add_action( 'load-'.$pagehook1, array( $this, 'wpgmp_backend_scripts' ) );

		}
		/**
		 * Read models and create backend navigation.
		 */
		function load_modules_menu() {

			$modules = $this->modules;
			$pagehooks = array();
			if ( is_array( $modules ) ) {
				foreach ( $modules as $module ) {

						$object = new $module;

					if ( method_exists( $object,'navigation' ) ) {

						if ( ! is_array( $object->navigation() ) ) {
							continue;
						}

						foreach ( $object->navigation() as $nav => $title ) {

							if ( current_user_can( 'manage_options' ) && is_admin() ) {
								$role = get_role( 'administrator' );
								$role->add_cap( $nav );

							}

							$pagehooks[] = add_submenu_page(
								WPGMP_SLUG,
								$title,
								$title,
								$nav,
								$nav,
								array( $this,'processor' )
							);

						}
					}
				}
			}

			if ( is_array( $pagehooks ) ) {

				foreach ( $pagehooks as $key => $pagehook ) {
					add_action( 'load-'.$pagehooks[ $key ], array( $this, 'wpgmp_backend_scripts' ) );
				}
			}

		}
		/**
		 * Eneque scripts in the backend.
		 */
		function wpgmp_backend_scripts() {

			global $pagehook3, $pagehook6, $pagehook9, $pagehook11;

			if ( isset( $_SERVER['HTTPS'] ) && ( 'on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS'] ) || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
					$wpgmp_apilocation = 'https';
			} else {
				$wpgmp_apilocation = 'http';
			}

			if ( get_option( 'wpgmp_api_key' ) != '' ) {
				$wpgmp_apilocation .= '://maps.google.com/maps/api/js?key='.get_option( 'wpgmp_api_key' ).'&libraries=geometry,places,weather,panoramio,drawing&language=en';
			} else {
				$wpgmp_apilocation .= '://maps.google.com/maps/api/js?libraries=geometry,places,weather,panoramio,drawing&language=en';
			}

			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'wp-color-picker' );
			$wp_scripts = array( 'jQuery','thickbox', 'wp-color-picker' );

			if ( $wp_scripts ) {
				foreach ( $wp_scripts as $wp_script ) {
					wp_enqueue_script( $wp_script );
				}
			}

			$scripts = array();

			$scripts[] = array(
			'handle'  => 'wpgmp-backend-google-maps',
			'src'   => WPGMP_JS.'backend.js',
			'deps'    => array(),
			);

			$scripts[] = array(
			'handle'  => 'wpgmp-backend-google-api',
			'src'   => $wpgmp_apilocation,
			'deps'    => array(),
			);

			$scripts[] = array(
			'handle'  => 'wpgmp-map',
			'src'   => WPGMP_JS.'maps.js',
			'deps'    => array(),
			);

			if ( $scripts ) {
				foreach ( $scripts as $script ) {
					wp_enqueue_script( $script['handle'], $script['src'], $script['deps'] );
				}
			}

			$wpgmp_local = array();
			$wpgmp_local['language'] = 'en';
			$wpgmp_local['urlforajax'] = admin_url( 'admin-ajax.php' );
			$wpgmp_local['hide'] = __( 'Hide',WPGMP_TEXT_DOMAIN );
			wp_localize_script( 'wpgmp-map', 'wpgmp_local', $wpgmp_local );

			$wpgmp_js_lang = array();
			$wpgmp_js_lang['confirm'] = __( 'Are you sure to delete item?',WPGMP_TEXT_DOMAIN );
			wp_localize_script( 'wpgmp-backend-google-maps', 'wpgmp_js_lang', $wpgmp_js_lang );

			$admin_styles = array(
			'wpgmp-map-bootstrap' => WPGMP_CSS.'bootstrap.min.flat.css',
			'wpgmp-backend-google-map' => WPGMP_CSS.'backend.css',
			);

			if ( $admin_styles ) {
				foreach ( $admin_styles as $admin_style_key => $admin_style_value ) {
					wp_enqueue_style( $admin_style_key, $admin_style_value );
				}
			}
		}
		/**
		 * Metabox stylesheet.
		 */
		function wpgmp_backend_styles() {

			wp_enqueue_style( 'wpgmp-backend-metabox', WPGMP_CSS.'wpgmp-metabox-css.css' );
		}
		/**
		 * Load plugin language file.
		 */
		function load_plugin_languages() {

			load_plugin_textdomain( WPGMP_TEXT_DOMAIN, false, WPGMP_FOLDER.'/lang/' );
		}
		/**
		 * Call hook on plugin activation for both multi-site and single-site.
		 */
		function plugin_activation( $network_wide ) {

			if ( is_multisite() && $network_wide ) {
				global $wpdb;
				$currentblog = $wpdb->blogid;
				$activated = array();
				$sql = "SELECT blog_id FROM {$wpdb->blogs}";
				$blog_ids = $wpdb->get_col( $wpdb->prepare( $sql, null ) );

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->wpgmp_activation();
					$activated[] = $blog_id;
				}

				switch_to_blog( $currentblog );
				update_site_option( 'op_activated', $activated );

			} else {
				$this->wpgmp_activation();
			}
		}
		/**
		 * Call hook on plugin deactivation for both multi-site and single-site.
		 */
		function plugin_deactivation() {

			if ( is_multisite() && $network_wide ) {
				global $wpdb;
				$currentblog = $wpdb->blogid;
				$activated = array();
				$sql = "SELECT blog_id FROM {$wpdb->blogs}";
				$blog_ids = $wpdb->get_col( $wpdb->prepare( $sql, null ) );

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->wpgmp_deactivation();
					$activated[] = $blog_id;
				}

				switch_to_blog( $currentblog );
				update_site_option( 'op_activated', $activated );

			} else {
				$this->wpgmp_deactivation();
			}
		}

		/**
		 * Perform tasks on new blog create and table install.
		 */
		 
		 function wpgmp_on_blog_new_generate(  $blog_id, $user_id, $domain, $path, $site_id, $meta ){
		    
			if ( is_plugin_active_for_network( plugin_basename(__FILE__) ) ) {
               switch_to_blog( $blog_id );
               $this->wpgmp_activation();
               restore_current_blog();
             }	 
		 
		 }

		/**
		 * Perform tasks on when blog deleted and remove plugin tables.
		 */
		 
		 function wpgmp_on_blog_delete( $tables ){
			global $wpdb;
            $tables[] = str_replace( $wpdb->base_prefix, $wpdb->prefix, TBL_LOCATION );
			$tables[] = str_replace( $wpdb->base_prefix, $wpdb->prefix, TBL_GROUPMAP );
			$tables[] = str_replace( $wpdb->base_prefix, $wpdb->prefix, TBL_MAP );
			$tables[] = str_replace( $wpdb->base_prefix, $wpdb->prefix, TBL_ROUTES );
			$tables[] = str_replace( $wpdb->base_prefix, $wpdb->prefix, TBL_BACKUPS );
            return $tables; 
		 }
		/**
		 * Create choose icon tab in media manager.
		 * @param  array $tabs Current Tabs.
		 * @return array       New Tabs.
		 */
		function wpgmp_google_map_tabs_filter($tabs) {

			$newtab = array( 'ell_insert_gmap_tab' => __( 'Choose Icons', WPGMP_TEXT_DOMAIN ) );
			return array_merge( $tabs, $newtab );
		}
		/**
		 * Intialize wp_iframe for icons tab
		 * @return [type] [description]
		 */
		function wpgmp_google_map_media_upload_tab() {

			return wp_iframe( array( $this, 'wpgmp_google_map_icon' ), $errors );
		}
		/**
		 * Read images/icons folder.
		 */
		function wpgmp_google_map_icon() {

			wp_enqueue_style( 'media' );
			media_upload_header();
			$form_action_url = site_url( "wp-admin/media-upload.php?type={$GLOBALS['type']}&tab=ell_insert_gmap_tab", 'admin' );
		?>

		<style type="text/css">
		#select_icons .read_icons {
		width: 32px;
		height: 32px;ß
		}
		#select_icons .active img {
		border: 3px solid #000;
		width: 26px;
		}
		</style>

		<script type="text/javascript">

		jQuery(document).ready(function($) {

		$(".read_icons").click(function () {

		$(".read_icons").removeClass('active');
		$(this).addClass('active');
		});

		$('input[name="wpgmp_search_icon"]').keyup(function() {
		if($(this).val() == '')
		$('.read_icons').show();
		else {
		$('.read_icons').hide();
        $('img[title^="' + $(this).val() + '"]').parent().show();
		}

    	});

		});

		function add_icon_to_images(target) {

		if(jQuery('.read_icons').hasClass('active'))
		{
		imgsrc = jQuery('.active').find('img').attr('src');
		var win = window.dialogArguments || opener || parent || top;
		win.send_icon_to_map(imgsrc,target);
		}
		else
		{
		alert('<?php _e( 'Choose marker icon',WPGMP_TEXT_DOMAIN ); ?>');
		}
		}
		</script>

		<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr( $form_action_url ); ?>" class="media-upload-form" id="library-form">
	<h3 class="media-title" style="color: #5A5A5A; font-family: Georgia, 'Times New Roman', Times, serif; font-weight: normal; font-size: 1.6em; margin-left: 10px;"><?php _e( 'Choose icon', WPGMP_TEXT_DOMAIN ) ?> 	<input name="wpgmp_search_icon" id="wpgmp_search_icon" type='text' value="" placeholder="<?php _e( 'Search icons',WPGMP_TEXT_DOMAIN ); ?>" />
</h3>
	<div style="margin-bottom:20px; float:left; width:100%;">
	<ul style="float:left; width:100%;" id="select_icons">
	<?php
	$dir = WPGMP_ICONS_DIR;
	$file_display = array( 'jpg', 'jpeg', 'png', 'gif' );

	if ( file_exists( $dir ) == false ) {
		echo 'Directory \'', $dir, '\' not found!';

	} else {
		$dir_contents = scandir( $dir );
		foreach ( $dir_contents as $file ) {
			$image_data = explode( '.', $file );
			$file_type = strtolower( end( $image_data ) );
			if ( '.' !== $file && '..' !== $file && true == in_array( $file_type, $file_display ) ) {
			?>
			<li class="read_icons" style="float:left;">
			<img alt="<?php echo $image_data[0]; ?>" title="<?php echo $image_data[0]; ?>" src="<?php echo WPGMP_ICONS.$file; ?>" style="cursor:pointer;" />
		</li>
		<?php
			}
		}
	}
		$target = esc_js($_GET['target']);
		?>
		</ul>
		<button type="button" class="button" style="margin-left:10px;" value="1" onclick="add_icon_to_images('<?php echo $target; ?>');" name="send[<?php echo $picid ?>]"><?php _e( 'Insert into Post', WPGMP_TEXT_DOMAIN ) ?></button>
	</div>
	</form>
	<?php
		}
		/**
		 * Perform tasks on plugin deactivation.
		 */
		function wpgmp_deactivation() {

		}

		/**
		 * Perform tasks on plugin deactivation.
		 */
		function wpgmp_activation() {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$modules = $this->modules;
			$pagehooks = array();
			if ( is_array( $modules ) ) {
				foreach ( $modules as $module ) {
					$object = new $module;
					if ( method_exists( $object,'install' ) ) {
								$tables[] = $object->install();
					}
				}
			}

			if ( is_array( $tables ) ) {
				foreach ( $tables as $i => $sql ) {
					dbDelta( $sql );
				}
			}

		}
		/**
		 * Define all constants.
		 */
		private function _define_constants() {

			global $wpdb;

			if ( ! defined( 'WPGMP_SLUG' ) ) {
				define( 'WPGMP_SLUG', 'wpgmp_view_overview' );
			}

			if ( ! defined( 'WPGMP_VERSION' ) ) {
				define( 'WPGMP_VERSION', '3.4.1' );
			}

			if ( ! defined( 'WPGMP_TEXT_DOMAIN' ) ) {
				define( 'WPGMP_TEXT_DOMAIN', 'wpgmp_google_map' );
			}

			if ( ! defined( 'WPGMP_FOLDER' ) ) {
				define( 'WPGMP_FOLDER', basename( dirname( __FILE__ ) ) );
			}

			if ( ! defined( 'WPGMP_DIR' ) ) {
				define( 'WPGMP_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'WPGMP_ICONS_DIR' ) ) {
				define( 'WPGMP_ICONS_DIR', WPGMP_DIR.'/assets/images/icons/' );
			}

			if ( ! defined( 'WPGMP_CORE_CLASSES' ) ) {
				define( 'WPGMP_CORE_CLASSES', WPGMP_DIR.'core/' );
			}
			
			if ( ! defined( 'WPGMP_PLUGIN_CLASSES' ) ) {
				define( 'WPGMP_PLUGIN_CLASSES', WPGMP_DIR . 'classes/' );
			}
			//
			if ( ! defined( 'WPGMP_MODEL' ) ) {
				define( 'WPGMP_MODEL', WPGMP_DIR . 'modules/' );
			}

			if ( ! defined( 'WPGMP_CONTROLLER' ) ) {
				define( 'WPGMP_CONTROLLER', WPGMP_CORE_CLASSES );
			}

			if ( ! defined( 'WPGMP_CORE_CONTROLLER_CLASS' ) ) {
				define( 'WPGMP_CORE_CONTROLLER_CLASS', WPGMP_CORE_CLASSES.'class.controller.php' );
			}

			if ( ! defined( 'WPGMP_MODEL' ) ) {
				define( 'WPGMP_MODEL', WPGMP_DIR.'modules/' );
			}

			if ( ! defined( 'WPGMP_URL' ) ) {
				define( 'WPGMP_URL', plugin_dir_url( WPGMP_FOLDER ).WPGMP_FOLDER.'/' );
			}

			if ( ! defined( 'WPGMP_INC_URL' ) ) {
				define( 'WPGMP_INC_URL', WPGMP_URL.'includes/' );
			}

			if ( ! defined( 'WPGMP_CSS' ) ) {
				define( 'WPGMP_CSS', WPGMP_URL.'assets/css/' );
			}

			if ( ! defined( 'WPGMP_JS' ) ) {
				define( 'WPGMP_JS', WPGMP_URL.'assets/js/' );
			}

			if ( ! defined( 'WPGMP_IMAGES' ) ) {
				define( 'WPGMP_IMAGES', WPGMP_URL.'assets/images/' );
			}

			if ( ! defined( 'WPGMP_FONTS' ) ) {
				define( 'WPGMP_FONTS', WPGMP_URL.'fonts/' );
			}

			if ( ! defined( 'WPGMP_ICONS' ) ) {
				define( 'WPGMP_ICONS', WPGMP_URL.'assets/images/icons/' );
			}
			$upload_dir = wp_upload_dir();
			if ( ! defined( 'WPGMP_BACKUP' ) ) {

				if ( ! is_dir( $upload_dir['basedir'].'/maps-backup' ) ) {
					mkdir( $upload_dir['basedir'].'/maps-backup' );
				}
				define( 'WPGMP_BACKUP',$upload_dir['basedir'].'/maps-backup/' );
				define( 'WPGMP_BACKUP_URL',$upload_dir['baseurl'].'/maps-backup/' );

			}

			if ( ! defined( 'TBL_LOCATION' ) ) {
				define( 'TBL_LOCATION', $wpdb->prefix.'map_locations' );
			}

			if ( ! defined( 'TBL_GROUPMAP' ) ) {
				define( 'TBL_GROUPMAP', $wpdb->prefix.'group_map' );
			}

			if ( ! defined( 'TBL_MAP' ) ) {
				define( 'TBL_MAP', $wpdb->prefix.'create_map' );
			}

			if ( ! defined( 'TBL_ROUTES' ) ) {
				define( 'TBL_ROUTES', $wpdb->prefix.'map_routes' );
			}

			if ( ! defined( 'TBL_BACKUPS' ) ) {
				define( 'TBL_BACKUPS', $wpdb->prefix.'wpgmp_backups' );
			}

		}
		/**
		 * Load all required core classes.
		 */
		private function _load_files() {

			
			$coreInitialisationFile = plugin_dir_path( __FILE__ ).'core/class.initiate-core.php';
			if ( file_exists( $coreInitialisationFile ) ) {
			   require_once( $coreInitialisationFile );
			}
			
			//Load Plugin Files	
			$plugin_files_to_include = array('wpgmp-controller.php',
											 'wpgmp-model.php',
											 'class.map-widget.php');
			foreach ( $plugin_files_to_include as $file ) {

				if(file_exists(WPGMP_PLUGIN_CLASSES . $file))
				require_once( WPGMP_PLUGIN_CLASSES . $file ); 
			}
			// Load all modules.
			$core_modules = array( 'overview','location','map','group_map','drawing','route','backup','permissions','settings' );
			if ( is_array( $core_modules ) ) {
				foreach ( $core_modules as $module ) {

					$file = WPGMP_MODEL.$module.'/model.'.$module.'.php';
					
					if ( file_exists( $file ) ) {
						include_once( $file );
						$class_name = 'WPGMP_Model_'.ucwords( $module );
						array_push( $this->modules, $class_name );
					}
				}
			}

			//exit;
			// load Extensions
			$extension_modules = apply_filters( 'wpgmp_extensions',array() );
			if ( is_array( $extension_modules ) ) {
				foreach ( $extension_modules as $extension ) {
					array_push( $this->modules, $extension );
				}
			}
		}
	}
}

new Google_Maps_Pro();
