<?php
global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version, $sr_changelog, $sr_last_checked, $sr_text_domain, $sr_slug;

$sr_sku = 'sr';
$sr_text_domain = ( defined('SR_TEXT_DOMAIN') ) ? SR_TEXT_DOMAIN : 'smart-reporter-for-wp-e-commerce';
$sr_slug = 'smart-reporter-for-wp-e-commerce';


if (! function_exists( 'get_plugin_data' )) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$sr_base_name = SR_PLUGIN_FILE;
$sr_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . SR_PLUGIN_FILE );


add_site_option( 'sr_license_key', '' );
add_site_option( 'sr_download_url', '' );
add_site_option( 'sr_installed_version', '' );
add_site_option( 'sr_live_version', '' );
add_site_option( 'sr_due_date', '' );
add_site_option( 'sr_login_link', '' );
add_site_option( 'sr_changelog', '' );
add_site_option( 'sr_last_checked', '' );

$sr_check_update_timeout = (24 * 60 * 60); // timeout for making request to StoreApps

if ( get_site_option( 'sr_installed_version' ) != $sr_plugin_data ['Version'] ) {
    update_site_option( 'sr_installed_version', $sr_plugin_data ['Version'] );   
}

if ( ( get_site_option( 'sr_live_version' ) == '' ) || ( get_site_option( 'sr_live_version' ) < get_site_option( 'sr_installed_version' ) ) ) {
    update_site_option( 'sr_live_version', $sr_plugin_data['Version'] );
}

$sr_live_version = get_site_option( 'sr_live_version' );
$sr_installed_version = get_site_option( 'sr_installed_version' );

if ( empty( $sr_license_key ) ) {
    $sr_stored_license_key = sr_get_license_key();
    $sr_license_key = ( !empty( $sr_stored_license_key ) ) ? $sr_stored_license_key : get_site_option( 'sr_license_key' );
}

// Actions for License Validation & Upgrade process
add_action( 'admin_footer', 'sr_add_plugin_style_script' );
add_action( 'admin_footer', 'sr_support_ticket_content' );
// add_action( "after_plugin_row_".$sr_base_name, 'sr_update_row', 10, 2 );
add_action( "after_plugin_row_".$sr_base_name, 'sr_update_row', 99, 2 );
add_action( 'wp_ajax_sr_validate_license_key', 'sr_validate_license_key' );
add_action( 'wp_ajax_sr_force_check_for_updates', 'sr_force_check_for_updates' );
add_action( 'wp_ajax_sr_reset_license_details', 'sr_reset_license_details' );

// Filters for pushing Smart Reporter plugin details in Plugins API of WP
add_filter( 'site_transient_update_plugins', 'sr_overwrite_site_transient', 10, 3 );

add_filter( 'plugin_row_meta', 'sr_add_support_link' , 10, 4 );
add_filter( 'plugin_action_links_' . $sr_base_name, 'sr_plugin_action_links' );

function sr_force_check_for_updates() {

    global $sr_check_update_timeout;

    $current_transient = get_site_transient( 'update_plugins' );
    $new_transient = apply_filters( 'site_transient_update_plugins', $current_transient, 'update_plugins', true );
    set_site_transient( 'update_plugins', $new_transient, $sr_check_update_timeout );
    echo json_encode( 'checked' );
    exit();
}

function sr_reset_license_details() {

    check_ajax_referer( 'storeapps-reset-license', 'security' );

    global $wpdb;

    update_site_option( 'sr_license_key', '' );
    update_site_option( 'sr_installed_version', '' );
    update_site_option( 'sr_live_version', '' );
    update_site_option( 'sr_login_link', '' );
    update_site_option( 'sr_due_date', '' );
    update_site_option( 'sr_changelog', '' );
    update_site_option( 'sr_last_checked', '' );
    update_site_option( 'sr_download_url', '' );

    sr_check_for_updates();

    die();

}


function sr_check_for_updates() {

    global $sr_sku, $sr_installed_version, $sr_live_version, $sr_changelog;

    $sr_license_key = get_site_option( 'sr_license_key');

    $license_query = ( !empty( $sr_license_key ) ) ? '&serial=' . $sr_license_key : '';

    $check_for_update_url = 'http://www.storeapps.org/wp-admin/admin-ajax.php?action=get_products_latest_version&sku=' . $sr_sku . $license_query . '&uuid=' . urlencode( admin_url( '/' ) );
    $check_for_update_link = ( ! empty( $check_for_update_url ) ) ? add_query_arg( array( 'utm_source' => $sr_sku . '-v' . $sr_installed_version, 'utm_medium' => 'upgrade', 'utm_campaign' => 'active_install' ), $check_for_update_url ) : '';

    $result = wp_remote_post( $check_for_update_link );
    
    if (is_wp_error($result)) {
        return;
    }
    
    $response = json_decode( $result ['body'] );
    
    $live_version = $response->version;

    if ( isset( $response->link ) ) {
        update_site_option( 'sr_login_link', $response->link );
    }
     
    if ( isset( $response->due_date ) ) {
        update_site_option( 'sr_due_date', $response->due_date );
    }    
    
    if ( $sr_live_version == $live_version || $response == 'false' ) {
        return;
    }
    
    $sr_changelog = $response->changelog;
    $sr_live_version = $live_version;

    update_site_option( 'sr_live_version', $live_version );
    update_site_option( 'sr_changelog', $response->changelog );

}

function sr_overwrite_site_transient($plugin_info, $transient = 'update_plugins', $force_check_updates = false) {
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version, $sr_slug;
    if ( !isset( $plugin_info->response ) || empty( $plugin_info->response ) || empty( $plugin_info->response[$sr_base_name] ) || count( $plugin_info->response ) <= 0 ) return $plugin_info;

    $sr_live_version = get_site_option( 'sr_live_version' );
    $download_url       = get_site_option( 'sr_download_url' );
    $download_link      = ( ! empty( $download_url ) ) ? add_query_arg( array( 'utm_source' => $sr_sku . '-v' . $sr_live_version, 'utm_medium' => 'upgrade', 'utm_campaign' => 'update' ), $download_url ) : '';

    if ( empty( $plugin_info->response [$sr_base_name]->package ) || strpos( $plugin_info->response [$sr_base_name]->package, 'downloads.wordpress.org' ) > 0 ) {
        $plugin_info->response [$sr_base_name]->package = $download_link;
    }

    if (empty( $plugin_info->checked ))
        return $plugin_info;

    $time_not_changed = isset( $plugin_info->last_checked ) && $sr_check_update_timeout > ( time() - $plugin_info->last_checked );

    if ( $force_check_updates || !$time_not_changed ) {
        sr_check_for_updates();
    }

    return $plugin_info;
}


function sr_validate_license_key() {
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version;
    $sr_license_key = (isset($_REQUEST ['license_key']) && !empty($_REQUEST ['license_key'])) ? $_REQUEST ['license_key'] : '';
    $storeapps_validation_url = 'http://www.storeapps.org/wp-admin/admin-ajax.php?action=woocommerce_validate_serial_key&serial=' . urlencode($sr_license_key) . '&is_download=true&sku=' . $sr_sku;
    $resp_type = array('headers' => array('content-type' => 'application/text'));
    $response_info = wp_remote_post($storeapps_validation_url, $resp_type); //return WP_Error on response failure

    if (is_array($response_info)) {
        $response_code = wp_remote_retrieve_response_code($response_info);
        $response_msg = wp_remote_retrieve_response_message($response_info);

        // if ($response_code == 200 && $response_msg == 'OK') {
        if ($response_code == 200) {
            $storeapps_response = wp_remote_retrieve_body($response_info);
            $decoded_response = json_decode($storeapps_response);
            if ($decoded_response->is_valid == 1) {
                update_site_option('sr_license_key', $sr_license_key);
                update_site_option('sr_download_url', $decoded_response->download_url);
                sr_check_for_updates();
            } else {
                sr_remove_license_download_url();
            }
            echo $storeapps_response;
            exit();
        }
        sr_remove_license_download_url();
        echo json_encode(array('is_valid' => 0));
        exit();
    }
    sr_remove_license_download_url();
    echo json_encode(array('is_valid' => 0));
    exit();
}


function sr_remove_license_download_url() {
    update_site_option('sr_license_key', '');
    update_site_option('sr_download_url', '');
}


function sr_add_plugin_style() {
    echo '<style type="text/css">';
    ?>
        div#TB_window {
            background: lightgrey;
        }
        <?php if ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
        tr.sr_license_key .key-icon-column:before {
            content: "\f112";
            display: inline-block;
            -webkit-font-smoothing: antialiased;
            font: normal 1.5em/1 'dashicons';
        }
        tr.sr_due_date .renew-icon-column:before {
            content: "\f463";
            display: inline-block;
            -webkit-font-smoothing: antialiased;
            font: normal 1.5em/1 'dashicons';
        }
        <?php } ?>
        a#sr_reset_license {
            cursor: pointer;
        }
    <?php
    echo '</style>';
}


function sr_update_row($file, $sr_plugin_data) {
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version, $sr_text_domain;
    $sr_license_key = get_site_option('sr_license_key');
    $sr_due_date = get_site_option('sr_due_date');
    $sr_login_link = get_site_option('sr_login_link');
    $valid_color = '#AAFFAA';
    $invalid_color = '#FFAAAA';
    $color = ($sr_license_key != '') ? $valid_color : $invalid_color;
    ?>
    <?php if ( empty( $sr_license_key ) ) { ?>
        <tr class="sr_license_key" style="background: <?php echo $color; ?>">
            <td class="key-icon-column" style="vertical-align: middle;"></td>
            <td style="vertical-align: middle;"><label for="sr_license_key"><strong><?php _e( 'License Key', $sr_text_domain ); ?></strong></label></td>
            <td>
                <input type="text" id="sr_license_key" name="sr_license_key" value="<?php echo $sr_license_key; ?>" size="50" style="text-align: center;" />
                <input type="button" class="button" id="sr_validate_license_button" name="sr_validate_license_button" value="<?php _e( 'Validate', $sr_text_domain ); ?>" />
                <input type="button" class="button" id="sr_check_for_updates" name="sr_check_for_updates" value="Check for updates" />
                <img id="sr_license_validity_image" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" style="display: none; vertical-align: middle;" />
            </td>
        </tr>
        <?php } ?>
        <?php
            if ( !empty( $sr_due_date ) ) {
                $start = strtotime( $sr_due_date . ' -30 days' );
                $due_date = strtotime( $sr_due_date );
                $now = time();
                if ( $now >= $start ) {
                    $remaining_days = round( abs( $due_date - $now )/60/60/24 );
                    $target_link = 'http://www.storeapps.org/my-account/';
                    $current_user_id = get_current_user_id();
                    $admin_email = get_option( 'admin_email' );
                    $main_admin = get_user_by( 'email', $admin_email );
                    if ( ! empty( $main_admin->ID ) && $main_admin->ID == $current_user_id ) {
                        $target_link = $sr_login_link;
                    }
                    $login_link = add_query_arg( array( 'utm_source' => $sr_sku, 'utm_medium' => 'upgrade', 'utm_campaign' => 'renewal' ), $target_link );
                    ?>
                       <!-- 96down.com <tr class="sr_due_date" style="background: #FFAAAA;">
                            <td class="renew-icon-column" style="vertical-align: middle;"></td>
                            <td style="vertical-align: middle;" colspan="2">
                                <?php
                                    if ( $now > $due_date ) {
                                        echo sprintf(__( 'Your license for %s %s. Please %s to continue receiving updates & support', $sr_text_domain ), 'Smart Reporter', '<strong>' . __( 'has expired', $sr_text_domain ) . '</strong>', '<a href="' . $login_link . '" target="_blank">' . __( 'renew your license now', $sr_text_domain ) . '</a>');
                                    } else {
                                        echo sprintf(__( 'Your license for %s %swill expire in %d %s%s. Please %s to get %sdiscount upto 50%%%s', $sr_text_domain ), 'Smart Reporter', '<strong>', $remaining_days, _n( 'day', 'days', $remaining_days, $sr_text_domain ), '</strong>', '<a href="' . $login_link . '" target="_blank">' . __( 'renew your license now', $sr_text_domain ) . '</a>', '<strong>', '</strong>');
                                    }
                                ?>
                            </td>
                        </tr>-->
                    <?php
                }
            }
    }

    function sr_add_plugin_style_script() {

        global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version, $sr_text_domain, $sr_slug;

        $license_key = get_site_option( 'sr_license_key' );
        $valid_color = '#AAFFAA';
        $invalid_color = '#FFAAAA';
        $color = ($license_key != '') ? $valid_color : $invalid_color;
        sr_add_plugin_style();
        ?>

    <script type="text/javascript">
        
        jQuery(function(){
            jQuery('input#sr_validate_license_button').on( 'click', function(){
                jQuery('img#sr_license_validity_image').show();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'sr_validate_license_key',
                        'license_key': jQuery('input#sr_license_key').val()
                    },
                    success: function( response ) {
                        if ( response.is_valid == 1 ) {
                            jQuery('tr.sr_license_key').css('background', '<?php echo $valid_color; ?>');
                            jQuery('#sr_license_key').hide();
                        } else {
                            jQuery('tr.sr_license_key').css('background', '<?php echo $invalid_color; ?>');
                            jQuery('input#sr_license_key').val('');
                        }
                        location.reload();
                    }
                });
            });

            jQuery('input#sr_check_for_updates').on( 'click', function(){
                jQuery('img#sr_license_validity_image').show();
                jQuery.ajax({
                    url: '<?php echo admin_url("admin-ajax.php") ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'sr_force_check_for_updates'
                    },
                    success: function( response ) {
                        if ( response == 'checked' ) {
                            location.reload();
                        } else {
                            jQuery('img#sr_license_validity_image').hide();
                        }
                    }
                });
            });

            jQuery('a#sr_reset_license').on( 'click', function(){
                var status_element = jQuery(this).closest('tr');
                status_element.css('opacity', '0.4');
                jQuery.ajax({
                    url: '<?php echo admin_url("admin-ajax.php") ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'sr_reset_license_details',
                        security: '<?php echo wp_create_nonce( "storeapps-reset-license" ); ?>'
                    },
                    success: function( response ) {
                        location.reload();
                    }
                });
            });

            jQuery(document).ready(function(){
                <?php if ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
                    jQuery('tr.sr_license_key .key-icon-column').css( 'border-left', jQuery('tr.sr_license_key').prev().prev().prev().find('th.check-column').css( 'border-left' ) );
                    jQuery('tr.sr_due_date .renew-icon-column').css( 'border-left', jQuery('tr.sr_license_key').prev().prev().prev().find('th.check-column').css( 'border-left' ) );
                <?php } ?>

                // thickbox code
                var width = jQuery(window).width();
                var H = jQuery(window).height();
                var W = ( 720 < width ) ? 720 : width;

                var adminbar_height = 0;

                if ( jQuery('body.admin-bar').length )
                    adminbar_height = 28;

                jQuery("#TB_window").css({"max-height": 390 +'px'});

                ajaxContentW = W - 110;
                ajaxContentH = H - 130 - adminbar_height;
                jQuery("#TB_ajaxContent").css({"width": ajaxContentW +'px', "height": ajaxContentH +'px'});

                jQuery('tr[data-slug="<?php echo $sr_slug; ?>"]').find( 'div.plugin-version-author-uri' ).addClass( 'sr_social_links' );

                jQuery('tr.sr_license_key').css( 'background', jQuery('tr.sr_due_date').css( 'background' ) );

                <?php if ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
                    jQuery('tr.sr_license_key .key-icon-column').css( 'border-left', jQuery('tr#<?php echo $sr_slug; ?>').find('th.check-column').css( 'border-left' ) );
                    jQuery('tr.sr_due_date .renew-icon-column').css( 'border-left', jQuery('tr#<?php echo $sr_slug; ?>').find('th.check-column').css( 'border-left' ) );
                <?php } ?>  

            });
        });
    </script>
    
    <?php
}

    function sr_add_support_link( $plugin_meta, $plugin_file, $plugin_data, $status ) {

        global $sr_base_name, $sr_text_domain;

        if ( $sr_base_name == $plugin_file ) {
            $plugin_meta[] = '<a id="sr_reset_license" title="' . __( 'Reset License Details', $sr_text_domain ) . '">' . __( 'Reset License', $sr_text_domain ) . '</a>';
            $plugin_meta[] = '<br>' . sr_add_social_links();
        }
        
        return $plugin_meta;
        
    }

    function sr_plugin_action_links( $links ) {

        global $sr_text_domain;

        $query_char = ( strpos( $_SERVER['REQUEST_URI'], '?' ) !== false ) ? '&' : '?';
        $action_links['need_help'] = '<a href="#TB_inline'.$query_char.'max-height=420px&inlineId=sr_post_query_form" class="thickbox sr_support_link" title="' . __( 'Submit your query', $sr_text_domain ) . '">' . __( 'Need Help?', $sr_text_domain ) . '</a>';

        return array_merge( $action_links, $links );
    }

    if (! function_exists('sr_add_social_links')) {
        function sr_add_social_links() {

            $social_link = '<style type="text/css">
                                div.sr_social_links > iframe {
                                    max-height: 1.5em;
                                    vertical-align: middle;
                                    padding: 5px 2px 0px 0px;
                                }
                                iframe[id^="twitter-widget"] {
                                    max-width: 10.3em;
                                }
                                iframe#fb_like_sr {
                                    max-width: 6em;
                                }
                                span > iframe {
                                    vertical-align: middle;
                                }
                            </style>';
            $social_link .= '<a href="https://twitter.com/storeapps" class="twitter-follow-button" data-show-count="true" data-dnt="true" data-show-screen-name="false">Follow</a>';
            $social_link .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
            $social_link .= '<iframe id="fb_like_sr" src="http://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FStore-Apps%2F614674921896173&width=100&layout=button_count&action=like&show_faces=false&share=false&height=21"></iframe>';
            $social_link .= '<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script><script type="IN/FollowCompany" data-id="3758881" data-counter="right"></script>';

            return $social_link;

        }
    }

function sr_support_ticket_content() {

    global $current_user, $wpdb, $woocommerce, $pagenow;
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version, $sr_text_domain;
        
    if ( !( $current_user instanceof WP_User ) ) return;

    ?>
    <div id="sr_post_query_form" style="display: none;">
        <style>
            table#sr_post_query_table {
                padding: 5px;
            }
            table#sr_post_query_table tr td {
                padding: 5px;
            }
            input.sr_text_field {
                padding: 5px;
            }
            label {
                font-weight: bold;
            }
            #TB_ajaxContent {
                width: 100% !important;
                height: 100% !important;
            }
        </style>
        <?php
            if ( !wp_script_is('jquery') ) {
                wp_enqueue_script('jquery');
                wp_enqueue_style('jquery');
            }

            $first_name = get_user_meta($current_user->ID, 'first_name', true);
            $last_name = get_user_meta($current_user->ID, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            $customer_name = ( !empty( $name ) ) ? $name : $current_user->data->display_name;
            $customer_email = $current_user->data->user_email;
            $ecom_plugin_version = '';

            if ( isset( $_GET['post_type'] ) && !empty( $_GET['post_type'] ) ) {
                switch ( $_GET['post_type'] ) {
                    case 'wpsc-product':
                        $ecom_plugin_version = 'WPeC ' . ( defined( 'WPSC_VERSION' ) ? WPSC_VERSION : '' );
                        break;
                    case 'product':
                        $ecom_plugin_version = 'WooCommerce ' . ( ( defined( 'WOOCOMMERCE_VERSION' ) ) ? WOOCOMMERCE_VERSION : $woocommerce->version );
                        break;
                    default:
                        $ecom_plugin_version = '';
                        break;
                }
            }
            
            $wp_version = ( is_multisite() ) ? 'WPMU ' . get_bloginfo('version') : 'WP ' . get_bloginfo('version');
            $admin_url = admin_url();
            $php_version = ( function_exists( 'phpversion' ) ) ? phpversion() : '';
            // $wp_max_upload_size = wp_convert_bytes_to_hr( wp_max_upload_size() );
            $wp_max_upload_size = size_format( wp_max_upload_size() );
            $server_max_upload_size = ini_get('upload_max_filesize');
            $server_post_max_size = ini_get('post_max_size');
            $wp_memory_limit = WP_MEMORY_LIMIT;
            $wp_debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? 'On' : 'Off';
            $this_plugins_version = $sr_plugin_data['Name'] . ' ' . $sr_plugin_data['Version'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $additional_information = "===== Additional Information =====
                                       [E-Commerce Plugin: $ecom_plugin_version] =====
                                       [WP Version: $wp_version] =====
                                       [Admin URL: $admin_url] =====
                                       [PHP Version: $php_version] =====
                                       [WP Max Upload Size: $wp_max_upload_size] =====
                                       [Server Max Upload Size: $server_max_upload_size] =====
                                       [Server Post Max Size: $server_post_max_size] =====
                                       [WP Memory Limit: $wp_memory_limit] =====
                                       [WP Debug: $wp_debug] =====
                                       [" . $sr_plugin_data['Name'] . " Version: $this_plugins_version] =====
                                       [License Key: $sr_license_key]=====
                                       [IP Address: $ip_address] =====
                                      ";



            if( isset( $_POST['submit_query'] ) && $_POST['submit_query'] == "Send" ){


                // wp_mail( 'support@storeapps.org', 'subject', 'message' );
               $additional_info = ( isset( $_POST['additional_information'] ) && !empty( $_POST['additional_information'] ) ) ? ( ( function_exists( 'woocommerce_clean' ) ) ? woocommerce_clean( $_POST['additional_information'] ) : $_POST['additional_information'] ) : '';
               $additional_info = str_replace( '=====', '<br />', $additional_info );
               $additional_info = str_replace( array( '[', ']' ), '', $additional_info );

               $headers = 'From: ';
               $headers .= ( isset( $_POST['client_name'] ) && !empty( $_POST['client_name'] ) ) ? ( ( function_exists( 'woocommerce_clean' ) ) ? woocommerce_clean( $_POST['client_name'] ) : $_POST['client_name'] ) : '';
               $headers .= ' <' . ( ( function_exists( 'woocommerce_clean' ) ) ? woocommerce_clean( $_POST['client_email'] ) : $_POST['client_email'] ) . '>' . "\r\n";
               $headers .= 'MIME-Version: 1.0' . "\r\n";
               $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

               ob_start();
               if ( isset( $_POST['include_data'] ) && $_POST['include_data'] == 'yes' ) {
                    echo $additional_info . '<br /><br />';
                }
               // echo woocommerce_clean( nl2br($_POST['message']) );
               echo nl2br($_POST['message']) ;
               $message = ob_get_clean();
               if ( empty( $_POST['name'] ) ) {
                    wp_mail( 'support@storeapps.org', $_POST['subject'], $message, $headers );
                    header('Location: ' . $_SERVER['HTTP_REFERER'] );
               }
            } 

        ?>
        <!-- <form id="sr_form_post_query" method="POST" action="http://www.storeapps.org/api/supportticket.php" enctype="multipart/form-data"> -->
        
        <form id="sr_form_post_query" method="POST" action="" enctype="multipart/form-data">
            <script type="text/javascript">
                jQuery(function(){

                    //Code for handling the sizing of the thickbox w.r.to. Window size

                    jQuery(document).ready(function(){

                        var width = jQuery(window).width();
                        var H = jQuery(window).height();
                        var W = ( 720 < width ) ? 720 : width;

                        var adminbar_height = 0;

                        if ( jQuery('body.admin-bar').length )
                            adminbar_height = 28;

                        jQuery("#TB_window").css({"max-height": 390 +'px'});

                        ajaxContentW = W - 110;
                        ajaxContentH = H - 130 - adminbar_height;
                        jQuery("#TB_ajaxContent").css({"width": ajaxContentW +'px', "height": ajaxContentH +'px'});

                    });
                
                    jQuery(window).resize(function(){

                        var width = jQuery(window).width();
                        var H = jQuery(window).height();
                        var W = ( 720 < width ) ? 720 : width;

                        var adminbar_height = 0;

                        if ( jQuery('body.admin-bar').length )
                            adminbar_height = 28;

                        jQuery('#TB_window').css('margin-top', '');
                        jQuery("#TB_window").css({"max-height": 520 +'px',"top":48 +'px'});


                        ajaxContentW = W - 110;
                        ajaxContentH = H - 130 - adminbar_height;
                        jQuery("#TB_ajaxContent").css({"width": ajaxContentW +'px', "height": ajaxContentH +'px'});

                    });
           
                    jQuery('input#sr_submit_query').click(function(e){
                        var error = false;

                        var client_name = jQuery('input#client_name').val();
                        if ( client_name == '' ) {
                            jQuery('input#client_name').css('border-color', 'red');
                            error = true;
                        } else {
                            jQuery('input#client_name').css('border-color', '');
                        }

                        var client_email = jQuery('input#client_email').val();
                        if ( client_email == '' ) {
                            jQuery('input#client_email').css('border-color', 'red');
                            error = true;
                        } else {
                            jQuery('input#client_email').css('border-color', '');
                        }

                        var message = jQuery('table#sr_post_query_table textarea#message').val();

                        if ( message == '' ) {
                            jQuery('textarea#message').css('border-color', 'red');
                            error = true;
                        } else {
                            jQuery('textarea#message').css('border-color', '');
                        }

                        var subject = jQuery('table#sr_post_query_table input#subject').val();
                        if ( subject == '' ) {
                            var msg_len = message.length;
                            
                            if (msg_len <= 50) {
                                subject = message;
                            }
                            else
                            {
                                subject = message.substr(0,50) + '...';
                            }
                            
                            jQuery('input#subject').val(subject);
                            
                        } else {
                           jQuery('input#subject').css('border-color', '');
                        }

                        if ( error == true ) {
                            jQuery('label#error_message').text('* All fields are compulsory.');
                            e.preventDefault();
                        } else {
                            jQuery('label#error_message').text('');
                        }

                    });

                    jQuery('input,textarea').keyup(function(){
                        var value = jQuery(this).val();
                        if ( value.length > 0 ) {
                            jQuery(this).css('border-color', '');
                            jQuery('label#error_message').text('');
                        }
                    });

                });
            </script>
            <table id="sr_post_query_table">
                <tr>
                    <td><label for="client_name"><?php _e('Name', $sr_text_domain); ?>*</label></td>
                    <td><input type="text" class="regular-text sr_text_field" id="client_name" name="client_name" value="<?php echo $customer_name; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;" /></td>
                </tr>
                <tr>
                    <td><label for="client_email"><?php _e('E-mail', $sr_text_domain); ?>*</label></td>
                    <td><input type="email" class="regular-text sr_text_field" id="client_email" name="client_email" value="<?php echo $customer_email; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;" /></td>
                </tr>
                <tr>
                    <td><label for="current_plugin"><?php _e('Product', $sr_text_domain); ?></label></td>
                    <td><input type="text" class="regular-text sr_text_field" id="current_plugin" name="current_plugin" value="<?php echo $this_plugins_version; ?>" readonly autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"/><input type="text" name="name" value="" style="display: none;" /></td>
                </tr>
                <tr>
                    <td><label for="subject"><?php _e('Subject', $sr_text_domain); ?></label></td>
                    <td><input type="text" class="regular-text sr_text_field" id="subject" name="subject" value="<?php echo ( !empty( $subject ) ) ? $subject : ''; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding-top: 12px;"><label for="message"><?php _e('Message', $sr_text_domain); ?>*</label></td>
                    <td><textarea id="message" name="message" rows="10" cols="60" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"><?php echo ( !empty( $message ) ) ? $message : ''; ?></textarea></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding-top: 12px;"></td>
                    <td><input id="include_data" type="checkbox" name="include_data" value="yes" /> <label for="include_data"><?php echo __( 'Include plugins / environment details to help solve issue faster', $sr_text_domain ); ?></label></td>
                </tr>
                <tr>
                    <td></td>
                    <td><label id="error_message" style="color: red;"></label></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" class="button" id="sr_submit_query" name="submit_query" value="Send" /></td>
                </tr>
            </table>
            <?php wp_nonce_field( 'storeapps-submit-query_sr' ); ?>
            <input type="hidden" name="license_key" value="<?php echo $sr_license_key; ?>" />
            <input type="hidden" name="sku" value="<?php echo $sr_sku; ?>" />
            <input type="hidden" class="hidden_field" name="ecom_plugin_version" value="<?php echo $ecom_plugin_version; ?>" />
            <input type="hidden" class="hidden_field" name="wp_version" value="<?php echo $wp_version; ?>" />
            <input type="hidden" class="hidden_field" name="admin_url" value="<?php echo $admin_url; ?>" />
            <input type="hidden" class="hidden_field" name="php_version" value="<?php echo $php_version; ?>" />
            <input type="hidden" class="hidden_field" name="wp_max_upload_size" value="<?php echo $wp_max_upload_size; ?>" />
            <input type="hidden" class="hidden_field" name="server_max_upload_size" value="<?php echo $server_max_upload_size; ?>" />
            <input type="hidden" class="hidden_field" name="server_post_max_size" value="<?php echo $server_post_max_size; ?>" />
            <input type="hidden" class="hidden_field" name="wp_memory_limit" value="<?php echo $wp_memory_limit; ?>" />
            <input type="hidden" class="hidden_field" name="wp_debug" value="<?php echo $wp_debug; ?>" />
            <input type="hidden" class="hidden_field" name="current_plugin" value="<?php echo $this_plugins_version; ?>" />
            <input type="hidden" class="hidden_field" name="ip_address" value="<?php echo $ip_address; ?>" />
            <input type="hidden" class="hidden_field" name="additional_information" value='<?php echo $additional_information; ?>' />
        </form>
    </div>
    <?php
}



function sr_settings_page($plugin_name) {
    global $sr_download_url, $wpdb, $sr_text_domain;
    
    $is_pro_updated = is_pro_updated ();
    $license_key = sr_get_license_key();
    if (isset ( $_POST ['submit'] )) {
        $latest_version = get_latest_version ($plugin_name);
        $license_key = $wpdb->_real_escape( $_POST ['license_key'] );
        $sr_dir = dirname($plugin_name);
        $sr_post_url = STORE_APPS_URL . 'wp-admin/admin-ajax.php?action=woocommerce_validate_serial_key&serial=' . urlencode($license_key) . '&sku=sr';                
        $sr_response_result = smart_get_sr_response ( $sr_post_url );   
        if ($license_key != '') {
            if ($sr_response_result->is_valid == 1) {
                if ( is_multisite() ) {
                    $delete_query = "DELETE FROM $wpdb->sitemeta WHERE meta_key = 'sr_license_key'";
                    $wpdb->query ( $delete_query );
                    $query  = "REPLACE INTO $wpdb->sitemeta (`meta_key`,`meta_value`) VALUES('sr_license_key','$license_key')";
                } else {
                    $query  = "REPLACE INTO `{$wpdb->prefix}options`(`option_name`,`option_value`) VALUES('sr_license_key','$license_key')";
                }
                $result = $wpdb->query ( $query );
                $msg    = 'Your key is valid. Automatic Upgrades and support are now activated.';
                sr_display_notice ( $msg );
            } else {
                sr_display_err( $sr_response_result->msg );
            }
        } else {
            $msg = 'Please enter license key';
            sr_display_err ( $msg );
        }
    }
    ?>
</br>
<form method="post" action="">
<div class="wrap">
<div id="icon-smart-reporter" class="sr_icon32"><br/></div>
<h2>Smart Reporter Pro Settings</h2>
<!-- Your Smart Reporter Pro license key is used to verify your support
package, enable automatic updates and receive support. --></div>
<br />
<div id="sr_auto_refresh_setting">
    <script type="text/javascript">
        jQuery(function($){

            jQuery('#sr_send_test_mail').click(function(){

                var error = false,
                    sr_email = jQuery('input[name=sr_send_summary_mails_email]').val().trim();

                if ( sr_email == '' ) {
                    jQuery('input[name=sr_send_summary_mails_email]').css('border-color', 'red');
                    error = true;
                } else {
                    jQuery('input[name=sr_send_summary_mails_email]').css('border-color', '');
                    error = false;
                }
                
                if ( !error ) {

                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl + "?action=sr_send_test_mail",
                        success: function( response ) {
                            $("#sr_send_test_mail_success").show();
                        }
                    });
                }
            });

            jQuery('#sr_set_auto_refresh').click(function(e){

                e.preventDefault();                

                var regex_duration = /^\d*\.?\d+$/;
                var sr_refresh_duration = Number(jQuery('input[name=sr_refresh_duration]').val());
                var error = false;
                if ( ! regex_duration.test( sr_refresh_duration ) || sr_refresh_duration < 1 ) {
                    jQuery('input[name=sr_refresh_duration]').css('border-color', 'red');
                    jQuery('#sr_save_refresh_setting_result').css('color', 'red');
                    jQuery('#sr_save_refresh_setting_result').text("<?php _e('Invalid value', $sr_text_domain); ?>");
                    error = true;
                } else {
                    jQuery('input[name=sr_refresh_duration]').css('border-color', '');
                    jQuery('#sr_save_refresh_setting_result').css('color', '');
                    jQuery('#sr_save_refresh_setting_result').text('');
                    error = false;
                }
                
                if ( !error ) {



                    // jQuery('img#sr_update_progress').show();
                    jQuery.ajax({
                        type: 'POST',
                        // url: "<?php echo WP_PLUGIN_URL.'/smart-reporter-for-wp-e-commerce/pro/upgrade.php'; ?>",
                        url: ajaxurl + "?action=sr_save_settings",
                        data: {
                            sr_is_auto_refresh:             jQuery('input#sr_is_auto_refresh').is(':checked'),
                            sr_what_to_refresh:             jQuery('select#sr_what_to_refresh').val(),
                            sr_refresh_duration:            jQuery('input#sr_refresh_duration').val(),
                            sr_send_summary_mails:          jQuery('input#sr_send_summary_mails').is(':checked'),
                            sr_summary_mail_interval:       jQuery('select#sr_summary_mail_interval').val(),
                            sr_summary_week_start_day:      jQuery('select#sr_summary_week_start_day').val(),
                            sr_summary_month_start_day:     jQuery('select#sr_summary_month_start_day').val(),
                            sr_send_summary_mails_email:    jQuery('input#sr_send_summary_mails_email').val()
                        },
                        success: function( response ) {
                            $("#sr_settings_updated_message").show();
                        }
                    });
                }
            });
            
            jQuery('#sr_is_auto_refresh').click(function(){
                jQuery('#sr_what_to_refresh').attr('disabled', !jQuery('#sr_what_to_refresh').attr('disabled'));
                jQuery('#sr_refresh_duration').attr('disabled', !jQuery('#sr_refresh_duration').attr('disabled'));
            });

            jQuery('#sr_send_summary_mails').click(function(){
                jQuery('input[name=sr_send_summary_mails_email]').css('border-color', '');
                jQuery('#sr_send_test_mail').removeClass('sr_send_test_mail');
                
                jQuery('#sr_summary_mail_interval').attr('disabled', !jQuery('#sr_send_summary_mails_email').attr('disabled'));
                jQuery('#sr_send_summary_mails_email').attr('disabled', !jQuery('#sr_send_summary_mails_email').attr('disabled'));
                jQuery('#sr_send_test_mail').attr('disabled', !jQuery('#sr_send_test_mail').attr('disabled'));

            });

            jQuery('#sr_summary_mail_interval').on('change',function(){
                if (jQuery('#sr_summary_mail_interval').val() == 'weekly') {
                    jQuery('#sr_summary_month_start_day_span').hide();
                    jQuery('#sr_summary_week_start_day_span').show();
                } else if (jQuery('#sr_summary_mail_interval').val() == 'monthly') {
                    jQuery('#sr_summary_week_start_day_span').hide();
                    jQuery('#sr_summary_month_start_day_span').show();
                } else {
                    jQuery('#sr_summary_week_start_day_span').hide();
                    jQuery('#sr_summary_month_start_day_span').hide();
                }
            });

        });
    </script>
    <label for="sr_is_auto_refresh"><input type="checkbox" id="sr_is_auto_refresh" name="sr_is_auto_refresh" value="yes" <?php checked( get_site_option( 'sr_is_auto_refresh' ), 'yes' ); ?> /> <?php _e('Enable Auto Refresh', $sr_text_domain); ?></label> &rArr;
    <select id="sr_what_to_refresh" name="sr_what_to_refresh" <?php echo ( get_site_option( 'sr_is_auto_refresh' ) != 'yes' ) ? "disabled" : ""; ?>>
        <option value="select" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'select' ); ?>><?php _e('Select', $sr_text_domain); ?></option>
        <option value="kpi" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'kpi' ); ?>><?php _e('KPI', $sr_text_domain); ?></option>
        <option value="dashboard" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'dashboard' ); ?>><?php _e('Dashboard', $sr_text_domain); ?></option>
        <option value="all" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'all' ); ?>><?php _e('All', $sr_text_domain); ?></option>
    </select> &rArr;
    <?php _e('Auto Refresh Every', $sr_text_domain); ?> <input type="text" id="sr_refresh_duration" name="sr_refresh_duration" size="4" value="<?php echo get_site_option( 'sr_refresh_duration' ); ?>" <?php echo ( get_site_option( 'sr_is_auto_refresh' ) != 'yes' ) ? "disabled" : ""; ?> style="text-align: center;" /> <?php _e('minutes', $sr_text_domain) ?>
        <span style="font-size:0.8em;font-style:italic;color:#E34F4C;"><?php _e('*Only for Smart Reporter old view*', $sr_text_domain) ?> </span>
</div>

<?php if ( ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) && ( is_plugin_active ( 'woocommerce/woocommerce.php' ) ) ) { ?>
    <br/>

    <div id="sr_summary_mails">
        <label for="sr_send_summary_mails"><input type="checkbox" id="sr_send_summary_mails" name="sr_send_summary_mails" value="yes" <?php checked( get_site_option( 'sr_send_summary_mails' ), 'yes' ); ?> /> <?php _e('Enable Summary Mails', $sr_text_domain); ?></label> &rArr;
        <select id="sr_summary_mail_interval" name="sr_summary_mail_interval"  <?php echo ( get_site_option( 'sr_send_summary_mails' ) != 'yes' ) ? "disabled" : ""; ?>>
            <option value="daily" <?php selected( get_site_option( 'sr_summary_mail_interval' ), 'daily' ); ?>><?php _e('Daily', $sr_text_domain); ?></option>
            <option value="weekly" <?php selected( get_site_option( 'sr_summary_mail_interval' ), 'weekly' ); ?>><?php _e('Weekly', $sr_text_domain); ?></option>
            <option value="monthly" <?php selected( get_site_option( 'sr_summary_mail_interval' ), 'monthly' ); ?>><?php _e('Monthly', $sr_text_domain); ?></option>
        </select> &rArr;
        
        <span id="sr_summary_week_start_day_span" <?php echo (get_site_option( 'sr_summary_mail_interval' ) != 'weekly') ? "style='display:none;'" : ""; ?>>
            <label for="sr_summary_week_start_day"><b><?php _e( 'Week starts from ', $sr_text_domain ); ?></b></label>
            <select id="sr_summary_week_start_day" name="sr_summary_week_start_day">
                <option value="sunday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'sunday' ); ?>><?php _e('Sunday', $sr_text_domain); ?></option>
                <option value="monday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'monday' ); ?>><?php _e('Monday', $sr_text_domain); ?></option>
                <option value="tuesday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'tuesday' ); ?>><?php _e('Tueday', $sr_text_domain); ?></option>
                <option value="wednesday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'wednesday' ); ?>><?php _e('Wednesday', $sr_text_domain); ?></option>
                <option value="thursday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'thursday' ); ?>><?php _e('Thursday', $sr_text_domain); ?></option>
                <option value="friday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'friday' ); ?>><?php _e('Friday', $sr_text_domain); ?></option>
                <option value="saturday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'saturday' ); ?>><?php _e('Saturday', $sr_text_domain); ?></option>
            </select> &rArr;
        </span>

        <span id="sr_summary_month_start_day_span" <?php echo (get_site_option( 'sr_summary_mail_interval' ) != 'monthly') ? "style='display:none;'" : ""; ?>>
            <label for="sr_summary_month_start_day"><b><?php _e( 'Month starts from ', $sr_text_domain ); ?></b></label>
            <select id="sr_summary_month_start_day" name="sr_summary_month_start_day">
                <?php 
                    for ($i=1;$i<=31;$i++) {
                        echo '<option value="'.$i.'"'.selected( get_site_option( 'sr_summary_month_start_day' ), $i ).'>'. $i .'</option>';
                    }
                ?>
            </select> &rArr;
        </span>

        <label for="sr_send_summary_mails_email"><b><?php _e( 'Email:', $sr_text_domain ); ?></b></label>
        <input type="text" id="sr_send_summary_mails_email" placeholder="john@yourdomain.com, marry@yourdomain.com, ..." style="margin:1px; padding:3px;" size="38" name="sr_send_summary_mails_email" value="<?php echo get_site_option( 'sr_send_summary_mails_email' ); ?>" <?php echo ( get_site_option( 'sr_send_summary_mails' ) != 'yes' ) ? "disabled" : ""; ?> style="text-align: left;" />
        <input class="button" type="button" id="sr_send_test_mail" <?php echo ( get_site_option( 'sr_send_summary_mails' ) != 'yes' ) ? "disabled" : ""; ?> value="<?php _e('Send Test Mail', $sr_text_domain); ?>" />    
        <label id="sr_send_test_mail_success" style="display:none;color:#03a025;"><?php _e( 'Test Email Sent!', $sr_text_domain ); ?></label>
    </div>

    <br/>

<?php } ?>

<input class="button-primary" type="submit" id="sr_set_auto_refresh" value="<?php _e('Save Settings', $sr_text_domain); ?>" />

<div id='sr_settings_updated_message' style="display:none;" class='updated fade'><p><?php _e( 'Smart Reporter Settings <b>Updated</b>',$sr_text_domain ); ?></p></div>
<div id="notification" name="notification"></div>
<?php
}

function sr_get_license_key() {
    global $wpdb;
    $key     = '';
    
    if ( is_multisite() ) {
        $query = "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = 'sr_license_key'";
    } else {
        $query = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sr_license_key'";
    }
    $records = $wpdb->get_results ( $query, ARRAY_A );
    if (count ( $records ) == 1) {
        $key = is_multisite() ? $records [0] ['meta_value'] : $records [0] ['option_value'];
    }
    return $key;
}

// if ( isset( $_POST['action'] ) && $_POST['action'] == 'set_auto_refresh' ) {
function sr_save_settings() {



    ob_clean();
    try {
        if ( !function_exists( 'update_site_option' ) ) {
            if ( ! defined('ABSPATH') ) {
                include_once ('../../../../wp-load.php');
            }
            include_once ABSPATH . 'wp-includes/option.php';
        }
        $sr_is_auto_refresh = ( $_POST['sr_is_auto_refresh'] == 'true' ) ? 'yes' : 'no';
        update_site_option( 'sr_is_auto_refresh', $sr_is_auto_refresh );
        update_site_option( 'sr_what_to_refresh', $_POST['sr_what_to_refresh'] );
        update_site_option( 'sr_refresh_duration', $_POST['sr_refresh_duration'] );

        $sr_send_summary_mails = ( $_POST['sr_send_summary_mails'] == 'true' ) ? 'yes' : 'no';
        update_site_option( 'sr_send_summary_mails', $sr_send_summary_mails );
        update_site_option( 'sr_summary_mail_interval', $_POST['sr_summary_mail_interval'] );
        update_site_option( 'sr_summary_week_start_day', $_POST['sr_summary_week_start_day'] );
        update_site_option( 'sr_summary_month_start_day', $_POST['sr_summary_month_start_day'] );
        update_site_option( 'sr_send_summary_mails_email', $_POST['sr_send_summary_mails_email'] );

        wp_clear_scheduled_hook( 'sr_send_summary_mails' );

        echo 'success';
    } catch( Exception $e ) {
        echo 'fail';
    };
    exit();
}
