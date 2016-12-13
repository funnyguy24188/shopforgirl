<?php
/**
 * Plugin Overviews.
 * @package Maps
 * @author Flipper Code <flippercode>
 **/

?>

<div class="container wpgmp-docs">
<div class="row flippercode-main">
    <div class="col-md-12">
           <h4 class="alert alert-info"> <?php _e( 'How to Create Google Maps API Key',WPGMP_TEXT_DOMAIN ); ?> </h4>
           <div class="wpgmp-overview">
            <p>
            Since 22th June 2016, google maps doesn't work if google maps api key is not provided. Follow
            our guide to <a target="_blank" href="http://bit.ly/292gCV2">create your google maps api key </a>. Then go to <a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpgmp_manage_settings' ); ?>">settings</a> page and insert your google maps api key.
            </p>
            <p>Below are the api's which will be enabled automatically. </p>
            <ul>
                <li>Google Maps JavaScript API</li>
                <li>Google Maps Geocoding API</li>
                <li>Google Maps Directions API</li>
                <li>Google Maps Distance Matrix API</li>
                <li>Google Maps Elevation API</li>
                <li>Google Places API Web Service</li>
            </ul>
            </div>
            <h4 class="alert alert-info"> <?php _e( 'Create your first map',WPGMP_TEXT_DOMAIN ); ?> </h4>
              <div class="wpgmp-overview">
                <ol>
                    <li><?php
                    $url = admin_url( 'admin.php?page=wpgmp_form_location' );
                    $link = sprintf( wp_kses( __( 'Use our auto suggestion enabled location box to add your location <a href="%s">here</a>. You can add multiple locations. All those locations will be available to choose when you create your map.', WPGMP_TEXT_DOMAIN ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
                    echo $link;?>
                    </li>
                    <li><?php
                    $url = admin_url( 'admin.php?page=wpgmp_form_map' );
                    $link = sprintf( wp_kses( __( 'Now <a href="%s">click here</a> to create a map. You can create as many maps you want to add. Using shortcode, you can add maps on posts/pages.', WPGMP_TEXT_DOMAIN ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
                    echo $link;?>
                    </li>
                    <li><?php
                    $url = admin_url( 'admin.php?page=wpgmp_manage_map' );
                    $link = sprintf( wp_kses( __( 'When done with administrative tasks, you can display map on posts/pages using. You can create as many maps you want to add. Using shortcode, you can add maps on posts/pages. Enable map in the widgets section to display in sidebar.', WPGMP_TEXT_DOMAIN ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
                    echo $link;?>
                    </li>
                </ol>
            </div>
            <h4 class="alert alert-info"> <?php _e( 'Shortcodes',WPGMP_TEXT_DOMAIN ); ?> </h4>
            <div class="wpgmp-overview">
           
            <p>
                <?php _e( 'This plugin provides shortcodes which helpful for a non-programmer and programmer to display maps dynamically. Below are the shortcode combinations you may use though possiblity are endless to create combinations of shortcodes.',WPGMP_TEXT_DOMAIN );  ?>
            </p>
            <p>
                <h5 class="alert alert-info"><?php _e( 'Display Map using latitude & longitude',WPGMP_TEXT_DOMAIN ); ?></h5>
            </p>
            <p>
                <?php _e( 'Standard format for shortcode is as below.',WPGMP_TEXT_DOMAIN ); ?>
            </p>
            <p>
            <code>
[display_map width="500" height="500" zoom="5" language="en" map_type="ROADMAP" map_draggable="true"  marker1="39.639538 | -101.527405 | title | infowindow message | marker category name"]
</code>

                <?php _e( 'So you can display any number of markers using this shortcode.',WPGMP_TEXT_DOMAIN ); ?>
            </p>
            <p><?php _e( 'Below are few examples to understand it better.',WPGMP_TEXT_DOMAIN ); ?> </p>
            <p>
                <b><?php _e( 'Single Location',WPGMP_TEXT_DOMAIN );?> :</b>
                <br />
                <code>[display_map marker1="39.639538 | -101.527405 | hello world | This is first marker's info window message | category"]</code>
                <br>
            </p>
            <p>
                <b><?php _e( 'Multiple Locations',WPGMP_TEXT_DOMAIN ); ?> :</b>
                <br />
                <code>
[display_map marker1="39.639538 | -101.527405" marker2="39.027719|-111.546936"]
</code>
            </p>
            <p>
                <h5 class="alert alert-info"><?php _e( 'Display Map using Address',WPGMP_TEXT_DOMAIN ); ?></h5>
            </p>
            <p>
                <?php _e( 'Standard format for shortcode is as below.',WPGMP_TEXT_DOMAIN );?>
            </p>
            <p>
                <code>
[display_map width="500" height="500" zoom="5" language="en" map_type="ROADMAP" map_draggable="true" scroll_wheel="true" <br /> address1="New Delhi, india | title | infowindow message |  marker category name"]
</code>

            </p>
            <p><?php _e( 'Below are few examples to understand it better.',WPGMP_TEXT_DOMAIN ); ?> </p>
            <p>
                <b><?php _e( 'Single Location' );?> :</b>
                <br />
                <code>[display_map address1="New Delhi, india | hello world | This is first marker's info window message | category"]</code>
                <br />
            </p>
            <p>
                <b><?php _e( 'Multiple Locations',WPGMP_TEXT_DOMAIN );?> :</b>
                <br />
                <code>
[display_map address1="New Delhi, India" address2="Mumbai, India"]
</code>
            </p>
        </div>

         <h4 class="alert alert-info"><?php _e( 'Place holders', WPGMP_TEXT_DOMAIN ) ?></h4>
        <p><?php _e( 'Use following placeholder on infowindow setting or listing placeholder in map settings.', WPGMP_TEXT_DOMAIN ) ?></p>
        <ul>
                <li><b><?php _e( 'Location ID',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_id}</code></li>
                <li><b><?php _e( 'Location Title',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_title}</code></li>
                <li><b><?php _e( 'Location Address',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_address}</code></li>
                <li><b><?php _e( 'Location Message',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_message}</code></li>
                <li><b><?php _e( 'Location Categories',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_category}</code></li>
                <li><b><?php _e( 'Location Marker Icon',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_icon}</code></li>
                <li><b><?php _e( 'Location Latitude',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_latitude}</code></li>
                <li><b><?php _e( 'Location Longitude',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_longitude}</code></li>
                <li><b><?php _e( 'Location City',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_city}</code></li>
                <li><b><?php _e( 'Location State',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_state}</code></li>
                <li><b><?php _e( 'Location Country',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{marker_country}</code></li>
                <li><b><?php _e( 'Location Zoom' ); ?> :</b><code>{marker_zoom}</code></li>
                <li><b><?php _e( 'Location Postal Code' ); ?> :</b><code>{marker_postal_code}</code></li>
        </ul>
        <p><?php _e( 'Use following placeholder on infowindow setting or listing placeholder in map settings.', WPGMP_TEXT_DOMAIN ) ?></p>
        <ul>
                <li><b><?php _e( 'Post Title',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_title}</code></li>
                <li><b><?php _e( 'Post Title with Link',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_link}</code></li>
                <li><b><?php _e( 'Post Excerpt',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_excerpt}</code></li>
                <li><b><?php _e( 'Post Content',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_content}</code></li>
                <li><b><?php _e( 'Featured Image',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_featured_image}</code></li>
                <li><b><?php _e( 'Categories',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_categories}</code></li>
                <li><b><?php _e( 'Tags',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{post_tags}</code></li>
                <li><b><?php _e( 'Custom Fields',WPGMP_TEXT_DOMAIN ); ?> :</b><code>{%custom_field_slug_here%}</code> eg. {%age%}, {%salary%}</li>

        </ul>


    </div>
</div>
