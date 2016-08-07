<?php
/**
 * Displays an inactive message if the API License Key has not yet been activated
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'kt_api_manager' ) ) {
    class kt_api_manager {
        public $upgrade_url = 'https://www.kadencethemes.com/';
        public $kt_api_version_name = 'kt_api_version_1_0';
        public $version;
        private $my_theme;
        private $kt_software_product_id;
        public $kt_data_key;
        public $kt_api_key;
        public $kt_switch;
        public $kt_activation_email;
        public $kt_product_id_key;
        public $kt_instance_key;
        public $kt_deactivate_checkbox_key;
        public $kt_activated_key;
        public $kt_deactivate_checkbox;
        public $kt_activation_tab_key;
        public $kt_deactivation_tab_key;
        public $kt_settings_menu_title;
        public $kt_settings_title;
        public $kt_menu_tab_activation_title;
        public $kt_menu_tab_deactivation_title;
        public $kt_options;
        public $kt_product_id;
        public $kt_renew_license_url;
        public $kt_instance_id;
        public $kt_domain;
        public $kt_software_version;
        public $kt_plugin_or_theme;
        public $kt_update_version;
        public $kt_update_check = 'kt_update_check';
        public $kt_api_manager_key;
        public $kt_extra;
        /**
         * @var null
         */
        protected static $_instance = null;

        public static function instance($kt_product_id_key, $kt_instance_key, $kt_activated_key, $kt_settings_menu_title, $kt_product_id) {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self($kt_product_id_key, $kt_instance_key, $kt_activated_key, $kt_settings_menu_title, $kt_product_id);
            }

            return self::$_instance;
        }
        public function __construct($kt_product_id_key, $kt_instance_key, $kt_activated_key, $kt_settings_title, $kt_product_id) {

            if ( is_admin() ) {
                add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );
                add_action( 'admin_init', array( $this, 'activation' ) );
                
                // Repeat Check license. 
                add_filter( 'pre_set_site_transient_update_themes', array( $this, 'status_check' ) );

                $this->my_theme = wp_get_theme(); // Get theme data
                $this->version = $this->my_theme->get( 'Version' );

                /**
                 * Set all data defaults here
                 */
                $this->kt_data_key                      = 'kt_api_manager';
                $this->kt_api_key                       = 'kt_api_key';
                $this->kt_switch                        = 'kt_api_switch';
                $this->kt_activation_email              = 'activation_email';
                $this->kt_product_id_key                = $kt_product_id_key;
                $this->kt_instance_key                  = $kt_instance_key;
                $this->kt_activated_key                 = $kt_activated_key;
                $this->kt_deactivate_checkbox           = 'kt_deactivate_example_checkbox';
                $this->kt_deactivation_tab_key          = 'kt_api_manager_dashboard_deactivation';
                $this->kt_activation_tab_key            = 'kt_api_manager_dashboard';
                $this->kt_settings_menu_title           = 'Theme Activation';
                $this->kt_settings_title                = $kt_settings_title;
                $this->kt_menu_tab_activation_title     = __( 'API License Activation', 'virtue' );
                $this->kt_menu_tab_deactivation_title   = __( 'API License Deactivation', 'virtue' );
                $this->kt_options                       = get_option( $this->kt_data_key );
                $this->kt_product_id                    = $kt_product_id; // Software Title
                $this->kt_renew_license_url             = 'https://www.kadencethemes.com/my-account/'; // URL to renew a license
                $this->kt_instance_id                   = get_option( $this->kt_instance_key ); // Instance ID (unique to each blog activation)
                $this->kt_domain                        = str_ireplace( array( 'http://', 'https://' ), '', home_url() );
                $this->kt_software_version              = $this->version; // The software version
                $this->kt_plugin_or_theme               = 'theme'; // 'theme' or 'plugin'
                $this->kt_software_product_id           = $this->kt_product_id;

                //require_once( 'classes/kt-key-api.php' );
                //$this->kt_api_manager_key = new kt_api_manager_key();

                // require_once( 'classes/kt-api-manager-menu.php' );
                add_action( 'admin_menu', array( $this, 'add_menu' ) );
                add_action( 'admin_init', array( $this, 'load_settings' ) );

            }
             if ( get_option( $this->kt_activated_key ) != 'Activated' ) {
                add_action( 'admin_notices', array($this, 'kt_api_m_inactive_notice') );
            }

        }

        public function activation() {
            if ( get_option( $this->kt_data_key ) === false || get_option( $this->kt_instance_key ) === false ) {
                $global_options = array(
                    $this->kt_api_key           => '',
                    $this->kt_activation_email  => '',
                );
                update_option( $this->kt_data_key, $global_options );
                $single_options = array(
                    $this->kt_product_id_key            => $this->kt_software_product_id,
                    $this->kt_instance_key              => wp_generate_password( 12, false ),
                    $this->kt_deactivate_checkbox_key   => 'on',
                    $this->kt_activated_key             => 'Deactivated',
                    );

                foreach ( $single_options as $key => $value ) {
                    update_option( $key, $value );
                }

            }

        }
        public function status_check() {
            if ( false === ( $value = get_transient( 'kt_api_status_check' ) ) ) {
                if ( get_option( $this->kt_activated_key ) == 'Activated') {
                   $data = get_option( $this->kt_data_key);
                   if(isset($data[$this->kt_switch]) &&  $data[$this->kt_switch] == 'member') {
                       $args = array(
                                    'email'         => $data[$this->kt_activation_email],
                                    'licence_key'   => $data[$this->kt_api_key],
                                    'switch'        => $data[$this->kt_switch],
                                    );
                       $status_results = json_decode( $this->status( $args), true );

                       if($status_results == 'failed') {
                            // do nothing
                       } else if($status_results['status_check'] != 'active') {
                            $this->uninstall();
                            update_option( $this->kt_activated_key, 'Deactivated' );
                       }
                   }
                }
                set_transient( 'kt_api_status_check', 1, WEEK_IN_SECONDS );
            }
        }
        public function uninstall() {
            global $blog_id;

            $this->license_key_deactivation();

            // Remove options
            if ( is_multisite() ) {

                switch_to_blog( $blog_id );

                foreach ( array(
                        $this->kt_data_key,
                        $this->kt_product_id_key,
                        $this->kt_instance_key,
                        $this->kt_deactivate_checkbox_key,
                        $this->kt_activated_key,
                        ) as $option) {

                        delete_option( $option );

                        }

                restore_current_blog();

            } else {

                foreach ( array(
                        $this->kt_data_key,
                        $this->kt_product_id_key,
                        $this->kt_instance_key,
                        $this->kt_deactivate_checkbox_key,
                        $this->kt_activated_key
                        ) as $option) {

                        delete_option( $option );

                        }

            }

        }

        /**
         * Deactivates the license on the API server
         * @return void
         */
        public function license_key_deactivation() {

            $activation_status = get_option( $this->kt_activated_key );

            $api_email = $this->kt_options[$this->kt_activation_email];
            $api_key = $this->kt_options[$this->kt_api_key];

            $args = array(
                'email' => $api_email,
                'licence_key' => $api_key,
                );

            if ( $activation_status == 'Activated' && $api_key != '' && $api_email != '' ) {
                $this->deactivate( $args ); // reset license key activation
            }
        }

        /**
         * Displays an inactive notice when the software is inactive.
         */
        public static function kt_api_m_inactive_notice() { ?>
            <?php if ( ! current_user_can( 'manage_options' ) ) return; ?>
            <?php if ( isset( $_GET['page'] ) && 'api_manager_theme_example_dashboard' == $_GET['page'] ) return; ?>
            <div id="message" class="error">
                <p><?php printf( __( 'The theme update API License Key has not been activated! %sClick here%s to activate the license api key.', 'virtue' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=kt_api_manager_dashboard' ) ) . '">', '</a>' ); ?></p>
            </div>
            <?php
        }

        /**
         * Check for external blocking contstant
         * @return string
         */
        public function check_external_blocking() {
            // show notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
            if( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL === true ) {

                // check if our API endpoint is in the allowed hosts
                $host = parse_url( $this->upgrade_url, PHP_URL_HOST );

                if( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || stristr( WP_ACCESSIBLE_HOSTS, $host ) === false ) {
                    ?>
                    <div class="error">
                        <p><?php printf( __( '<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %s updates. Please add %s to %s.', 'virtue' ), $this->kt_product_id, '<strong>' . $host . '</strong>', '<code>WP_ACCESSIBLE_HOSTS</code>'); ?></p>
                    </div>
                    <?php
                }

            }
        }
        // Add option page menu
        public function add_menu() {
            $page = add_options_page( __( $this->kt_settings_menu_title, 'virtue' ), __( $this->kt_settings_menu_title, 'virtue' ), 'manage_options', $this->kt_activation_tab_key, array( $this, 'config_page'));
            add_action( 'admin_print_styles-' . $page, array( $this, 'css_scripts' ) );
        }
        public function config_page() {

            $settings_tabs = array( $this->kt_activation_tab_key => __( $this->kt_menu_tab_activation_title, 'virtue' ), $this->kt_deactivation_tab_key => __( $this->kt_menu_tab_deactivation_title, 'virtue' ) );
            $current_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $this->kt_activation_tab_key;
            $tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $this->kt_activation_tab_key;
            ?>
            <div class='wrap'>
                <?php screen_icon(); ?>
                <h2><?php _e( $this->kt_settings_title, 'virtue' ); ?></h2>

                <h2 class="nav-tab-wrapper">
                <?php
                    foreach ( $settings_tabs as $tab_page => $tab_name ) {
                        $active_tab = $current_tab == $tab_page ? 'nav-tab-active' : '';
                        echo '<a class="nav-tab ' . $active_tab . '" href="?page=' . $this->kt_activation_tab_key . '&tab=' . $tab_page . '">' . $tab_name . '</a>';
                    }
                ?>
                </h2>
                    <form action='options.php' method='post'>
                        <div class="main">
                    <?php
                        if( $tab == $this->kt_activation_tab_key ) {
                                settings_fields( $this->kt_data_key );
                                do_settings_sections( $this->kt_activation_tab_key );
                                submit_button( __( 'Save Changes', 'virtue' ) );
                        } else {
                                settings_fields( $this->kt_deactivate_checkbox );
                                do_settings_sections( $this->kt_deactivation_tab_key );
                                submit_button( __( 'Save Changes', 'virtue' ) );
                        }
                    ?>
                        </div>
                    </form>
                </div>
                <?php
        }

        // Register settings
        public function load_settings() {

            register_setting( $this->kt_data_key, $this->kt_data_key, array( $this, 'validate_options' ) );

            // API Key
            add_settings_section( $this->kt_api_key, __( 'Update API License Activation', 'virtue' ), array( $this, 'kt_api_key_text' ), $this->kt_activation_tab_key );
            add_settings_field( $this->kt_switch, __( 'License Type:', 'virtue' ), array( $this, 'kt_api_switch_field' ),  $this->kt_activation_tab_key, $this->kt_api_key );
            add_settings_field( $this->kt_api_key, __( 'Update API License Key', 'virtue' ), array( $this, 'kt_api_key_field' ), $this->kt_activation_tab_key, $this->kt_api_key );
            add_settings_field( $this->kt_activation_email, __( 'Update API License email', 'virtue' ), array( $this, 'kt_api_email_field' ), $this->kt_activation_tab_key, $this->kt_api_key );

            // Activation settings
            register_setting( $this->kt_deactivate_checkbox, $this->kt_deactivate_checkbox, array( $this, 'kt_license_key_deactivation' ) );
            add_settings_section( 'deactivate_button', __( 'API License Deactivation', 'virtue' ), array( $this, 'kt_deactivate_text' ), $this->kt_deactivation_tab_key );
            add_settings_field( 'deactivate_button', __( 'Deactivate API License Key', 'virtue' ), array( $this, 'kt_deactivate_textarea' ), $this->kt_deactivation_tab_key, 'deactivate_button' );

        }

        // Provides text for api key section
        public function kt_api_key_text() {
            echo __('Activating your license allows for updates to theme and bundled plugins. If you need your api key you will find it by logging in here:', 'virtue') . ' <a href="https://www.kadencethemes.com/my-account/" target="_blank">kadencethemes.com/my-account/</a>';
            echo '<input type="hidden" value="'.$this->kt_instance_id.'">';
            
        }
        // Outputs the mebership key or theme key
        public function kt_api_switch_field() {

            echo "<select id='kt_switch'  name='" . $this->kt_data_key . "[" . $this->kt_switch ."]'>";
            if ( $this->kt_options[$this->kt_switch] ) {
                echo "<option value='theme'";
                if( $this->kt_options[$this->kt_switch] == 'theme') {echo "selected";}
                echo ">Theme License</option>";
                echo "<option value='member'";
                if( $this->kt_options[$this->kt_switch] == 'member') {echo "selected";}
                echo ">Member License</option>";
            } else {
                echo "<option value='theme'>".__("Theme License", "virtue")."</option><option value='member'>".__("Member License", "virtue")."</option>";
            }
            echo "</select>";
        }

        // Outputs API License text field
        public function kt_api_key_field() {

            echo "<input id='api_key' name='" . $this->kt_data_key . "[" . $this->kt_api_key ."]' size='25' type='text' value='" . $this->kt_options[$this->kt_api_key] . "' />";
            if ( $this->kt_options[$this->kt_api_key] ) {
                echo '<span class="ktap-icon-pos"><i class="icon-checkmark" style="font-size:20px; color:green;"></i></span>';
            } else {
                echo '<span class="ktap-icon-pos"><i class="icon-warning" style="font-size:20px; color:orange;"></a></span>';
            }
        }

        // Outputs API License email text field
        public function kt_api_email_field() {

            echo "<input id='activation_email' name='" . $this->kt_data_key . "[" . $this->kt_activation_email ."]' size='25' type='text' value='" . $this->kt_options[$this->kt_activation_email] . "' />";
            if ( $this->kt_options[$this->kt_activation_email] ) {
                echo '<span class="ktap-icon-pos"><i class="icon-checkmark" style="font-size:20px; color:green;"></i></span>';
            } else {
                echo '<span class="ktap-icon-pos"><i class="icon-warning" style="font-size:20px; color:orange;"></a></span>';
            }
        }

        // Sanitizes and validates all input and output for Dashboard
        public function validate_options( $input ) {

            // Load existing options, validate, and update with changes from input before returning
            $options                                = $this->kt_options;
            $options[$this->kt_api_key]             = trim( $input[$this->kt_api_key] );
            $options[$this->kt_activation_email]    = trim( $input[$this->kt_activation_email] );
            $options[$this->kt_switch]              = trim($input[$this->kt_switch]);
            $api_email                              = trim( $input[$this->kt_activation_email] );
            $api_key                                = trim( $input[$this->kt_api_key] );
            $api_switch                             = trim( $input[$this->kt_switch] );
            $activation_status                      = get_option( $this->kt_activated_key );
            $checkbox_status                        = get_option( $this->kt_deactivate_checkbox );
            $current_api_key                        = $this->kt_options[$this->kt_api_key];
            $current_switch                         = $this->kt_options[$this->kt_switch];

            // Should match the settings_fields() value
            if ( $_REQUEST['option_page'] != $this->kt_deactivate_checkbox ) {

                if ( $activation_status == 'Deactivated' || $activation_status == '' || $api_key == '' || $api_email == '' || $checkbox_status == 'on' || $current_api_key != $api_key || $current_switch != $api_switch ) {
                    if ( $current_api_key != $api_key ) {
                        $this->replace_license_key( $current_api_key );
                    }

                    $args = array(
                        'email'         => $api_email,
                        'licence_key'   => $api_key,
                        'switch'        => $api_switch,
                        );

                    $activate_results = json_decode( $this->activate( $args ), true );

                    if ( $activate_results['activated'] === true ) {
                        add_settings_error( 'activate_text', 'activate_msg', __( 'Theme activated. ', 'virtue' ), 'updated' );
                        update_option( $this->kt_activated_key, 'Activated' );
                        update_option( $this->kt_deactivate_checkbox, 'off' );
                        update_option( 'kt_api_active_order', $activate_results['activation_extra']['order_id']);
                    }

                    if ( $activate_results == false && ! empty( $this->kt_options ) && ! empty( $this->kt_activated_key )) {
                        add_settings_error( 'api_key_check_text', 'api_key_check_error', __( 'Connection failed to the License Key API server. Make sure your host servers php version has the curl module installed and enabled.', 'virtue' ), 'error' );
                        $options[$this->kt_api_key] = '';
                        $options[$this->kt_activation_email] = '';
                        update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        update_option( 'kt_api_active_order', '');
                    }

                    if ( isset( $activate_results['code'] )  && ! empty( $this->kt_options ) && ! empty( $this->kt_activated_key ) ) {

                        switch ( $activate_results['code'] ) {
                            case '100':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'api_email_text', 'api_email_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                                $options[$this->kt_activation_email] = '';
                                $options[$this->kt_api_key] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                            case '101':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'api_key_text', 'api_key_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                                $options[$this->kt_api_key] = '';
                                $options[$this->kt_activation_email] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                            case '102':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'api_key_purchase_incomplete_text', 'api_key_purchase_incomplete_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                                $options[$this->kt_api_key] = '';
                                $options[$this->kt_activation_email] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                            case '103':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'api_key_exceeded_text', 'api_key_exceeded_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                                $options[$this->kt_api_key] = '';
                                $options[$this->kt_activation_email] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                            case '104':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'api_key_not_activated_text', 'api_key_not_activated_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                                $options[$this->kt_api_key] = '';
                                $options[$this->kt_activation_email] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                            case '105':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'api_key_invalid_text', 'api_key_invalid_error', "{$activate_results['error']}. {$$additional_info}", 'error' );
                                $options[$this->kt_api_key] = '';
                                $options[$this->kt_activation_email] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                            case '106':
                                $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                                add_settings_error( 'sub_not_active_text', 'sub_not_active_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                                $options[$this->kt_api_key] = '';
                                $options[$this->kt_activation_email] = '';
                                update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                            break;
                        }

                    }

                }

            }

            return $options;
        }

        // Deactivate the current license key before activating the new license key
        public function replace_license_key( $current_api_key ) {

            $args = array(
                'email'         => $this->kt_options[$this->kt_activation_email],
                'licence_key'   => $current_api_key,
                'switch'        => $this->kt_options[$this->kt_switch],
                );

            $reset = $this->deactivate( $args ); // reset license key activation

            if ( $reset == true ) {
                return true;
            } 

            add_settings_error( 'not_deactivated_text', 'not_deactivated_error', __( 'The license could not be deactivated. Use the License Deactivation tab to manually deactivate the license before activating a new license.', 'virtue' ), 'updated' );

            return false;
        }

        // Deactivates the license key to allow key to be used on another blog
        public function kt_license_key_deactivation( $input ) {

            $activation_status = get_option( $this->kt_activated_key );

            $args = array(
                'email'         => $this->kt_options[$this->kt_activation_email],
                'licence_key'   => $this->kt_options[$this->kt_api_key],
                'switch'        => $this->kt_options[$this->kt_switch],
                );

            // For testing activation status_extra data
            // $activate_results = json_decode( $this->status( $args ), true );
            // print_r($activate_results); exit;

            $options = ( $input == 'on' ? 'on' : 'off' );

            if ( $options == 'on' && $activation_status == 'Activated' && $this->kt_options[$this->kt_api_key] != '' && $this->kt_options[$this->kt_activation_email] != '' ) {

                // deactivates license key activation
                $activate_results = json_decode( $this->deactivate( $args ), true );
                // Used to display results for development
                // print_r($activate_results); exit();

                if ( $activate_results['deactivated'] == true ) {
                    $update = array(
                        $this->kt_api_key => '',
                        $this->kt_activation_email => ''
                        );

                    $merge_options = array_merge( $this->kt_options, $update );
                    if ( ! empty( $this->kt_activated_key ) ) {
                        update_option( $this->kt_data_key, $merge_options );
                        update_option( $this->kt_activated_key, 'Deactivated' );
                        update_option( 'kt_api_active_order', '');
                        add_settings_error( 'kt_deactivate_text', 'deactivate_msg', __( 'Theme license deactivated. ', 'virtue' ) . "{$activate_results['activations_remaining']}.", 'updated' );
                    }

                    return $options;
                }

                if ( isset( $activate_results['code'] ) && ! empty( $this->kt_options ) && ! empty( $this->kt_activated_key) ) {

                    switch ( $activate_results['code'] ) {
                        case '100':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'api_email_text', 'api_email_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $update = array(
                                $this->kt_api_key => '',
                                $this->kt_activation_email => ''
                            );
                            $merge_options = array_merge( $this->kt_options, $update );
                            update_option( $this->kt_activated_key, 'Deactivated' );
                        break;
                        case '101':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'api_key_text', 'api_key_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $options[$this->kt_api_key] = '';
                            $options[$this->kt_activation_email] = '';
                            update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        break;
                        case '102':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'api_key_purchase_incomplete_text', 'api_key_purchase_incomplete_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $options[$this->kt_api_key] = '';
                            $options[$this->kt_activation_email] = '';
                            update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        break;
                        case '103':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'api_key_exceeded_text', 'api_key_exceeded_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $options[$this->kt_api_key] = '';
                            $options[$this->kt_activation_email] = '';
                            update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        break;
                        case '104':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'api_key_not_activated_text', 'api_key_not_activated_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $options[$this->kt_api_key] = '';
                            $options[$this->kt_activation_email] = '';
                            update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        break;
                        case '105':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'api_key_invalid_text', 'api_key_invalid_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $options[$this->kt_api_key] = '';
                            $options[$this->kt_activation_email] = '';
                            update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        break;
                        case '106':
                            $additional_info = ! empty( $activate_results['additional info'] ) ? esc_attr( $activate_results['additional info'] ) : '';
                            add_settings_error( 'sub_not_active_text', 'sub_not_active_error', "{$activate_results['error']}. {$additional_info}", 'error' );
                            $options[$this->kt_api_key] = '';
                            $options[$this->kt_activation_email] = '';
                            update_option( $this->kt_options[$this->kt_activated_key], 'Deactivated' );
                        break;
                    }

                }

            } else {

                return $options;
            }

            return false;

        }

        public function kt_deactivate_text() {
        }

        public function kt_deactivate_textarea() {

            echo '<input type="checkbox" id="' . $this->kt_deactivate_checkbox . '" name="' . $this->kt_deactivate_checkbox . '" value="on"';
            echo checked( get_option( $this->kt_deactivate_checkbox ), 'on' );
            echo '/>';
            ?><span class="description"><?php _e( 'Deactivates an API License Key.', 'virtue' ); ?></span>
            <?php
        }

        // Loads admin style sheets
        public function css_scripts() {

            wp_register_style( $this->kt_data_key . '-css', get_template_directory_uri() . '/kt_framework/kt_api_manage.css', array(), $this->version, 'all');
            wp_enqueue_style( $this->kt_data_key . '-css' );
        }
        public function create_software_api_url( $args ) {

            $api_url = add_query_arg( $args, $this->upgrade_url );

            return $api_url;
        }

        public function activate( $args ) {
            if($args['switch'] == 'member') {
                $productid = 'kadence_member';
            } else {
                $productid = $this->kt_product_id;
            }
            $defaults = array(
                'wc-api'            => 'am-software-api',
                'request'           => 'activation',
                'product_id'        => $productid,
                'instance'          => $this->kt_instance_id,
                'platform'          => $this->kt_domain,
                'software_version'  => $this->kt_software_version
                );

            $args = wp_parse_args( $defaults, $args );

            $target_url = esc_url_raw( $this->create_software_api_url( $args ) );

            $request = wp_safe_remote_get( $target_url, array('sslverify'  => false) );

            if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
                return false;
            }

            $response = wp_remote_retrieve_body( $request );

            return $response;
        }

        public function deactivate( $args ) {
            if($args['switch'] == 'member') {
                $productid = 'kadence_member';
            } else {
                $productid = $this->kt_product_id;
            }
            $defaults = array(
                'wc-api'        => 'am-software-api',
                'request'       => 'deactivation',
                'product_id'    => $productid,
                'instance'      => $this->kt_instance_id,
                'platform'      => $this->kt_domain
                );

            $args = wp_parse_args( $defaults, $args );

            $target_url = esc_url_raw( $this->create_software_api_url( $args ) );

            $request = wp_safe_remote_get( $target_url, array('sslverify'  => false) );
            if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
            // Request failed
                return false;
            }

            $response = wp_remote_retrieve_body( $request );

            return $response;
        }

        /**
         * Checks if the software is activated or deactivated
         */
        public function status( $args ) {
            if(isset($args['switch']) && $args['switch'] == 'member') {
                $productid = 'kadence_member';
            } else {
                $productid = $this->kt_product_id;
            }
            $defaults = array(
                'wc-api'        => 'am-software-api',
                'request'       => 'status',
                'product_id'    => $productid,
                'instance'      => $this->kt_instance_id,
                'platform'      => $this->kt_domain,
                );

            $args = wp_parse_args( $defaults, $args );

            $target_url = esc_url_raw( $this->create_software_api_url( $args ) );

            $request = wp_safe_remote_get( $target_url, array('sslverify'  => false) );

            if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
                return 'failed';
            }

            $response = wp_remote_retrieve_body( $request );

            return $response;
        }



    }

}

kt_api_manager::instance( 'virtue_premium_api_key', 'kt_api_manager_virtue_premium_instance', 'kt_api_manager_virtue_premium_activated', 'Virtue Premium Activation', 'virtue_premium' );