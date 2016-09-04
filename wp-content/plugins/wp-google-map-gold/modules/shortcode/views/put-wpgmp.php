<?php
/**
 * Parse Shortcode and display maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

if ( isset( $options['id'] ) ) {
	 $map_id = $options['id'];
} else { return ''; }

if ( isset( $options['show'] ) ) {
	 $show_option = $options['show'];
} else {
	$show_option = 'default' ;
}
$shortcode_filters = array();
if ( isset( $options['category'] ) ) {
	$shortcode_filters['category'] = $options['category'];
}
// Fetch map information.
$modelFactory = new WPGMP_Model();
$map_obj = $modelFactory->create_object( 'map' );
$map_record = $map_obj->fetch( array( array( 'map_id', '=', $map_id ) ) );
$map = $map_record[0];

$category_obj = $modelFactory->create_object( 'group_map' );
$categories = $category_obj->fetch();
$all_categories = array();
$all_categories_name = array();
$route_obj = $modelFactory->create_object( 'route' );
$all_routes = $route_obj->fetch();
		$location_obj = $modelFactory->create_object( 'location' );

if ( ! empty( $categories ) ) {
	foreach ( $categories as $category ) {
		$all_categories[ $category->group_map_id ] = $category;
		$all_categories_name[ sanitize_title( $category->group_map_title ) ] = $category;
	}
}
if ( ! empty( $map->map_locations ) ) {
		$map_locations = $location_obj->fetch( array( array( 'location_id', 'IN', implode( ',',$map->map_locations ) ) ) );
}

$show_all_locations = apply_filters('wpgmp_show_all_locations_on_map',false);

if( true == $show_all_locations ) {
	$map_locations = $location_obj->fetch();
}

// Routes data.
if ( ! empty( $all_routes ) ) {
	$routes_data = array();
	foreach ( $all_routes as $route ) {
		$routes_data[ $route->route_id ] = $route;
	}
}
$map_data = array();
// Set map options.
$map_data['places'] = array();
if ( $map->map_all_control['infowindow_openoption'] == 'mouseclick' ) {
	$map->map_all_control['infowindow_openoption'] = 'click';
} else if ( $map->map_all_control['infowindow_openoption'] == 'mousehover' ) {
	$map->map_all_control['infowindow_openoption'] = 'mouseover';
} else if ( $map->map_all_control['infowindow_openoption'] == 'mouseover' ) {
	$map->map_all_control['infowindow_openoption'] = 'mouseover';
} else {
	$map->map_all_control['infowindow_openoption'] = 'click';
}
$map_data['map_options'] = array(
'center_lat' => sanitize_text_field( $map->map_all_control['map_center_latitude'] ),
'center_lng' => sanitize_text_field( $map->map_all_control['map_center_longitude'] ),
'zoom' => (isset( $options['zoom'] )) ? intval( $options['zoom'] ): intval( $map->map_zoom_level ),
'map_type_id' => sanitize_text_field( $map->map_type ),
'center_by_nearest' => ('true' == sanitize_text_field( $map->map_all_control['nearest_location'] ) ),
'center_circle_fillcolor' => sanitize_text_field( $map->map_all_control['center_circle_fillcolor'] ),
'center_circle_fillopacity' => sanitize_text_field( $map->map_all_control['center_circle_fillopacity'] ),
'center_circle_strokecolor' => sanitize_text_field( $map->map_all_control['center_circle_strokecolor'] ),
'center_circle_strokeopacity' => sanitize_text_field( $map->map_all_control['center_circle_strokeopacity'] ),
'show_center_circle' => (sanitize_text_field( $map->map_all_control['show_center_circle'] ) == 'true'),
'show_center_marker' => (sanitize_text_field( $map->map_all_control['show_center_marker'] ) == 'true'),
'center_marker_icon' => esc_url( $map->map_all_control['marker_center_icon'] ),
'center_marker_infowindow' => wpautop( wp_unslash( $map->map_all_control['show_center_marker_infowindow'] ) ),
'center_circle_strokeweight' => sanitize_text_field( $map->map_all_control['center_circle_strokeweight'] ),
'draggable' => (sanitize_text_field( $map->map_all_control['map_draggable'] ) != 'false'),
'scroll_wheel' => sanitize_text_field( $map->map_scrolling_wheel ),
'display_45_imagery' => sanitize_text_field( $map->map_45imagery ),
'marker_default_icon' => esc_url( $map->map_all_control['marker_default_icon'] ),
'infowindow_setting' => wpautop( wp_unslash( $map->map_all_control['infowindow_setting'] ) ),
'infowindow_bounce_animation' => $map->map_all_control['infowindow_bounce_animation'],
'infowindow_drop_animation' => ('true' == $map->map_all_control['infowindow_drop_animation'] ),
'close_infowindow_on_map_click' => ('true' == $map->map_all_control['infowindow_close'] ),
'default_infowindow_open' => ('true' == $map->map_all_control['infowindow_open'] ),
'infowindow_open_event' => ($map->map_all_control['infowindow_openoption']) ? $map->map_all_control['infowindow_openoption'] : 'click',
'infowindow_filter_only' => ($map->map_all_control['infowindow_filter_only'] == 'true'),
'infowindow_click_change_zoom' => (int) $map->map_all_control['infowindow_zoomlevel'],
'infowindow_click_change_center' => ('true' == $map->map_all_control['infowindow_iscenter'] ),
'pan_control' => ($map->map_all_control['pan_control'] != 'false'),
'zoom_control' => ($map->map_all_control['zoom_control'] != 'false'),
'map_type_control' => ($map->map_all_control['map_type_control'] != 'false'),
'scale_control' => ( $map->map_all_control['scale_control'] != 'false'),
'street_view_control' => ($map->map_all_control['street_view_control'] != 'false'),
'overview_map_control' => ($map->map_all_control['overview_map_control'] != 'false'),
'pan_control_position' => $map->map_all_control['pan_control_position'],
'zoom_control_position' => $map->map_all_control['zoom_control_position'],
'zoom_control_style' => $map->map_all_control['zoom_control_style'],
'map_type_control_position' => $map->map_all_control['map_type_control_position'],
'map_type_control_style' => $map->map_all_control['map_type_control_style'],
'street_view_control_position' => $map->map_all_control['street_view_control_position'],
);

$map_data['map_options']['width'] = sanitize_text_field( $map->map_width );

$map_data['map_options']['height'] = sanitize_text_field( $map->map_height );

$map_data['map_options'] = apply_filters( 'wpgmp_map_options',$map_data['map_options'] );

$map_data['map_options'] = apply_filters( 'wpgmp_maps_options',$map_data['map_options'],$map );

if ( isset( $map_data['map_options']['width'] ) ) {
	$width = $map_data['map_options']['width'];
} else { 	$width = '100%'; }

if ( isset( $map_data['map_options']['height'] ) ) {
	$height = $map_data['map_options']['height'];
} else { 	$height = '300px'; }

if ( '' != $width and strstr( $width, '%' ) === false ) {
	$width = str_replace( 'px', '', $width ).'px';
}

if ( '' == $width ) {
	$width = '100%';
}
if ( strstr( $height, '%' ) === false ) {
	$height = str_replace( 'px', '', $height ).'px';
}

$wpgmp_local = array();
if ( $map->map_all_control['wpgmp_language'] ) {
	$wpgmp_local['language'] = $map->map_all_control['wpgmp_language'];
} else { $wpgmp_local['language'] = 'en'; }
$wpgmp_local['select_radius'] = __( 'Select Radius',WPGMP_TEXT_DOMAIN );
$wpgmp_local['search_placeholder'] = __( 'Enter address or latitude or longitude or title or city or state or country or postal code here...', WPGMP_TEXT_DOMAIN );
$wpgmp_local['select'] = __( 'Select',WPGMP_TEXT_DOMAIN );
$wpgmp_local['select_all'] = __( 'Select All',WPGMP_TEXT_DOMAIN );
$wpgmp_local['select_category'] = __( 'Select Category',WPGMP_TEXT_DOMAIN );
$wpgmp_local['all_location'] = __( 'All', WPGMP_TEXT_DOMAIN );
$wpgmp_local['show_locations'] = __( 'Show Locations', WPGMP_TEXT_DOMAIN );
$wpgmp_local['sort_by'] = __( 'Sort by', WPGMP_TEXT_DOMAIN );
$wpgmp_local['wpgmp_not_working'] = __( 'not working...', WPGMP_TEXT_DOMAIN );
$wpgmp_local['place_icon_url'] = WPGMP_ICONS;
$wpgmp_local['wpgmp_location_no_results'] = __( 'No results found.', WPGMP_TEXT_DOMAIN );
$wpgmp_local['wpgmp_route_not_avilable'] = __( 'Route is not available for your requested route.', WPGMP_TEXT_DOMAIN );
$wpgmp_local['img_grid'] = "<span class='span_grid'><a class='wpgmp_grid'><img src='".WPGMP_IMAGES."grid.png'></a></span>";
$wpgmp_local['img_list'] = "<span class='span_list'><a class='wpgmp_list'><img src='".WPGMP_IMAGES."list.png'></a></span>";
$wpgmp_local['img_print'] = "<span class='span_print'><a class='wpgmp_print' onclick=jQuery('.wpgmp_print_listing').print()><img src='".WPGMP_IMAGES."print.png'></a></span>";
$wpgmp_local['hide'] = __( 'Hide',WPGMP_TEXT_DOMAIN );
$wpgmp_local['show'] = __( 'Show',WPGMP_TEXT_DOMAIN );
$wpgmp_local['start_location'] = __( 'Start Location',WPGMP_TEXT_DOMAIN );
$wpgmp_local['start_point'] = __( 'Start Point',WPGMP_TEXT_DOMAIN );
$wpgmp_local['radius'] = __( 'Radius',WPGMP_TEXT_DOMAIN );
$wpgmp_local['end_location'] = __( 'End Location',WPGMP_TEXT_DOMAIN );
$wpgmp_local['take_current_location'] = __( 'Take Current Location',WPGMP_TEXT_DOMAIN );
$wpgmp_local['center_location_message'] = __( 'Your Location',WPGMP_TEXT_DOMAIN );
$wpgmp_local['center_location_message'] = __( 'Your Location',WPGMP_TEXT_DOMAIN );
$wpgmp_local['driving'] = __( 'Driving',WPGMP_TEXT_DOMAIN );
$wpgmp_local['bicycling'] = __( 'Bicycling',WPGMP_TEXT_DOMAIN );
$wpgmp_local['walking'] = __( 'Walking',WPGMP_TEXT_DOMAIN );
$wpgmp_local['transit'] = __( 'Transit',WPGMP_TEXT_DOMAIN );
$wpgmp_local['metric'] = __( 'metric',WPGMP_TEXT_DOMAIN );
$wpgmp_local['imperial'] = __( 'imperial',WPGMP_TEXT_DOMAIN );
$wpgmp_local['find_direction'] = __( 'Find Direction',WPGMP_TEXT_DOMAIN );
$wpgmp_local['miles'] = __( 'Miles',WPGMP_TEXT_DOMAIN );
$wpgmp_local['km'] = __( 'KM',WPGMP_TEXT_DOMAIN );
$wpgmp_local['show_amenities'] = __( 'Show Amenities',WPGMP_TEXT_DOMAIN );
$wpgmp_local['find_location'] = __( 'Find Locations',WPGMP_TEXT_DOMAIN );
$wpgmp_local['prev'] = __( 'Prev',WPGMP_TEXT_DOMAIN );
$wpgmp_local['next'] = __( 'Next',WPGMP_TEXT_DOMAIN );
$wpgmp_local = apply_filters('wpgmp_text_settings',$wpgmp_local,$map->id);
wp_enqueue_script( 'wpgmp-google-api' );
wp_enqueue_script( 'wpgmp-google-map-main' );
wp_enqueue_script( 'wpgmp-frontend' );
wp_enqueue_style( 'wpgmp-frontend' );
wp_localize_script( 'wpgmp-google-map-main', 'wpgmp_local',$wpgmp_local );

if ( is_array( $map_locations ) ) {
	$loc_count = 0;
	foreach ( $map_locations as $location ) {
		$location_categories = array();
		if ( empty( $location->location_group_map ) ) {
			$location_categories[] = array(
			  'id'      => '',
			  'name'    => 'Uncategories',
			  'type'    => 'category',
			  'icon'    => WPGMP_ICONS.'marker_default_icon.png',
			);
		} else {

			foreach ( $location->location_group_map as $key => $loc_category_id ) {
				$loc_category = $all_categories[ $loc_category_id ];
				if ( ! empty( $loc_category ) ) {
					$location_categories[] = array(
					'id'      => $loc_category->group_map_id,
					'name'    => $loc_category->group_map_title,
					'type'    => 'category',
					'icon'    => $loc_category->group_marker,
					);
				}
			}
		}
		// Extra Fields in location.
		$extra_fields = array();
		$added_extra_fields = unserialize( get_option( 'wpgmp_location_extrafields' ) );

		if ( isset( $added_extra_fields ) ) {
			foreach ( $added_extra_fields as $i => $label ) {
				$extra_fields[ sanitize_title( $label ) ] = $location->location_extrafields[ sanitize_title( $label ) ];
			}
		}
		$onclick = isset( $location->location_settings['onclick'] ) ? $location->location_settings['onclick'] : 'marker';
		$map_data['places'][ $loc_count ] = array(
			'id'          => $location->location_id,
			'title'       => $location->location_title,
			'address'     => $location->location_address,
			'source'	  => 'manual',
			'content'     => ('' != $location->location_messages) ? do_shortcode( stripcslashes( $location->location_messages ) ) : $location->location_title,
			'location' => array(
			'icon'      => ($location_categories[0]['icon']) ? $location_categories[0]['icon'] : $map_data['map_options']['marker_default_icon'],
			'lat'       => $location->location_latitude,
			'lng'       => $location->location_longitude,
			'city'      => $location->location_city,
			'state'     => $location->location_state,
			'country'   => $location->location_country,
			'onclick_action' => $onclick,
			'redirect_custom_link' => $location->location_settings['redirect_link'],
			'open_new_tab' => $location->location_settings['redirect_link_window'],
			'postal_code' => $location->location_postal_code,
			'draggable' => ( 'true' == $location->location_draggable ),
			'infowindow_default_open' => ('true' == $location->location_infowindow_default_open),
			'animation' => $location->location_animation,
			'infowindow_disable' => ($location->location_settings['hide_infowindow'] !== 'false'),
			'zoom'      => 5,
			'extra_fields' => $extra_fields,
			'extension_fields' => apply_filters( 'wpgmp_extensions_fields_output',$location->location_settings['wpgmp_extensions_fields'] ),
			),
			'categories' => $location_categories,
		  );

		$loc_count++;
	}
}
// KML Layer.
if ( ! empty( $map->map_layer_setting['choose_layer']['kml_layer'] ) && $map->map_layer_setting['choose_layer']['kml_layer'] == 'KmlLayer' ) {
	if ( strpos( $map->map_layer_setting['map_links'], ',' ) !== false ) {
		$kml_layers_links = explode( ',', $map->map_layer_setting['map_links'] );
	} else {
		$kml_layers_links = array( $map->map_layer_setting['map_links'] );
	}

		$map_data['kml_layer']  = array(
		  'kml_layers_links' => $kml_layers_links,
		);
}
// Fusion Layer.
if ( ! empty( $map->map_layer_setting['choose_layer']['fusion_layer'] ) && $map->map_layer_setting['choose_layer']['fusion_layer'] == 'FusionTablesLayer' ) {
		$map_data['fusion_layer']  = array(
		  'fusion_table_select'   => $map->map_layer_setting['fusion_select'],
		  'fusion_table_from'     => $map->map_layer_setting['fusion_from'],
		  'fusion_icon_name'     => $map->map_layer_setting['fusion_icon_name'],
		  'fusion_heat_map'       => ($map->map_layer_setting['heat_map'] === 'true'?true:false),
		);
}

if ( ! empty( $map->map_layer_setting['choose_layer']['bicycling_layer'] ) && $map->map_layer_setting['choose_layer']['bicycling_layer'] == 'BicyclingLayer' ) {
	$map_data['bicyle_layer'] = array(
	'display_layer' => true,
	);
}

if ( ! empty( $map->map_layer_setting['choose_layer']['traffic_layer'] ) && $map->map_layer_setting['choose_layer']['traffic_layer'] == 'TrafficLayer' ) {
	$map_data['traffic_layer']  = array(
	'display_layer' => true,
	);
}

if ( ! empty( $map->map_layer_setting['choose_layer']['transit_layer'] ) && $map->map_layer_setting['choose_layer']['transit_layer'] == 'TransitLayer' ) {
	$map_data['transit_layer']  = array(
	'display_layer' => true,
	);
}
// Geo tags for google maps pro.
if ( ! empty( $map->map_all_control['geo_tags'] ) && $map->map_all_control['geo_tags'] == 'true' ) {
	$geo_filters = array_filter( $map->map_geotags );
	if ( is_array( $geo_filters ) ) {
		foreach ( $geo_filters as $filter_post_type => $filter ) {
			$filter_array[] = array( $filter_post_type => $filter );
		}
	}
}
$screens = array( 'post' );

$args = array(
			'public'  => true,
			'_builtin'  => false,
			);

$output = 'names';
$operator = 'and';
$post_types = get_post_types( $args, $output, $operator );
$custom_post_types = array( 'post', 'page' );
$all_post_types = array_merge( $post_types, $custom_post_types );

if ( is_array( $all_post_types ) ) {
	$selected_values = unserialize(get_option('wpgmp_allow_meta'));

	foreach ( $all_post_types as $post_type ) {

		if(is_array($selected_values)) {

					if(in_array($post_type, $selected_values)) {
						continue;
					}

		}

		$filter_array[] = array(
		$post_type => array(
		'address' => '_wpgmp_location_address',
		'latitude' => '_wpgmp_metabox_latitude',
		'longitude' => '_wpgmp_metabox_longitude',
		'category' => '_wpgmp_metabox_marker_id',
		),
		);
	}
}

if ( ! empty( $filter_array ) ) {
	foreach ( $filter_array as $filter ) {
		foreach ( $filter as $key => $value ) {
			if ( 'geo_tags' != $key ) {

				$custom_meta_keys = array();

				if ( ! empty( $value['latitude'] ) ) {
					$custom_meta_keys[] = array( 'key' => $value['latitude'],'value' => '','compare' => '!=' );
				}

				if ( ! empty( $value['longitude'] ) ) {
					$custom_meta_keys[] = array( 'key' => $value['longitude'],'value' => '','compare' => '!=' );
				}


				$args = array(
				'post_type' => $key,
				'posts_per_page' => -1,
				'meta_query' => array( $custom_meta_keys ),
				'post_status' => array( 'publish' ),
				);
				$args = apply_filters( 'wpgmp_geo_tags_args',$args,$map );
				$wpgmp_the_query = new WP_Query( $args );

				if ( $wpgmp_the_query->have_posts() ) {
					while ( $wpgmp_the_query->have_posts() ) {
						$wpgmp_the_query->the_post();
						global $post;
						$places = array();
						$content = $map->map_all_control['infowindow_geotags_setting'];
						$category_names = '';
						if ( empty( $value['latitude'] ) or empty( $value['longitude'] ) ) {
							continue; }
						// Check if meta post is assigned to $map->map_id.
						if ( '_wpgmp_location_address' == $value['address'] ) {

							$wpgmp_map_ids = get_post_meta( $post->ID, '_wpgmp_map_id', true );
							$wpgmp_map_id = unserialize( $wpgmp_map_ids );

							if ( ! is_array( $wpgmp_map_id ) ) {
								$wpgmp_map_id = array( $wpgmp_map_ids );
							}
							if ( ! in_array( $map->map_id, $wpgmp_map_id ) ) {
								continue;
							}
						}
						$places['location']['extension_fields'] = apply_filters( 'wpgmp_extensions_fields_output',unserialize( get_post_meta( $post->ID, '_wpgmp_extensions_fields', true ) ) );

						$replace_data['post_title'] = get_the_title();
						$replace_data['post_excerpt'] = get_the_excerpt();
						$replace_data['post_content'] = get_the_content();
						$replace_data['post_link'] = get_permalink( $post->ID );
						$categories = get_the_category();
						$category_names = '';
						if ( ! empty( $categories ) ) {
							foreach ( $categories as $category ) {
								$category_names .= $category->name.',';
							}
						}
						$replace_data['post_categories'] = trim( $category_names, ',' );
						$posttags = get_the_tags();
						$tag_names = '';
						if ( $posttags ) {
							foreach ( $posttags as $tag ) {
								$tag_names .= $tag->name.',';
							}
						}
						$post_featured_image = '';
						$replace_data['post_tags'] = trim( $tag_names, ',' );
						$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
						if ( isset( $featured_image[0] ) ) {
							$post_featured_image = '<img width="'.$featured_image[1].'" height="'.$featured_image[2].'" src="'.$featured_image[0].'" class="wp-post-image alignleft wpgmp_featured_image" >';
						}
						$replace_data['post_featured_image'] = apply_filters( 'wpgmp_geo_featured_image',$post_featured_image,$post->ID,$map->map_id );
						// Display custom fields here.
						$matches = array();
						$custom_fields = array();
						preg_match_all( '/{%(.*?)%}/',  $content, $matches );
						if ( isset( $matches[0] ) ) {
							foreach ( $matches[0] as $k => $m ) {
								$post_meta_key = $matches[1][ $k ];
								$meta_value = get_post_meta( $post->ID, $post_meta_key, true )? get_post_meta( $post->ID, $post_meta_key, true ) : '';
								$replace_data[ '%'.$post_meta_key.'%' ] = $meta_value;
								$custom_fields[ '%'.$post_meta_key.'%' ] = $meta_value;
							}
						}
						if ( empty( $custom_fields ) ) {
							$listing_content  = stripslashes( trim( $map->map_all_control['wpgmp_categorydisplayformat'] ) );

							preg_match_all( '/{%(.*?)%}/',  $listing_content, $matches );
							if ( isset( $matches[0] ) ) {
								foreach ( $matches[0] as $k => $m ) {
									$post_meta_key = $matches[1][ $k ];
									$meta_value = get_post_meta( $post->ID, $post_meta_key, true )? get_post_meta( $post->ID, $post_meta_key, true ) : '';
									$replace_data[ '%'.$post_meta_key.'%' ] = $meta_value;
									$custom_fields[ '%'.$post_meta_key.'%' ] = $meta_value;
								}
							}
						}

						preg_match_all( '/{\s*taxonomy\s*=\s*(.*?)}/',  $content, $matches );

						if ( isset( $matches[0] ) ) {

							foreach ( $matches[0] as $k => $m ) {
								$post_meta_key = $matches[1][ $k ];
								$terms = wp_get_post_terms( $post->ID,$post_meta_key,array( 'fields' => 'all' ) );
								$meta_value = '';
								if ( $terms ) {
									$tags_links = array();
									foreach ( $terms as $tag ) {
										$tags_links[] = $tag->name;
									}
									if ( ! empty( $tags_links ) ) {
										$meta_value = implode( ', ', $tags_links );
									}
								}
								$replace_data[ 'taxonomy='.$post_meta_key ] = $meta_value;
								$custom_fields[ 'taxonomy='.$post_meta_key ] = $meta_value;

							}
						}

						if ( empty( $custom_fields ) ) {
							$listing_content  = stripslashes( trim( $map->map_all_control['wpgmp_categorydisplayformat'] ) );
							preg_match_all( '/{\s*taxonomy\s*=\s*(.*?)}/',  $listing_content, $matches );
							if ( isset( $matches[0] ) ) {

								foreach ( $matches[0] as $k => $m ) {
									$post_meta_key = $matches[1][ $k ];
									$terms = wp_get_post_terms( $post->ID,$post_meta_key,array( 'fields' => 'all' ) );
									$meta_value = '';
									if ( $terms ) {
										$tags_links = array();
										foreach ( $terms as $tag ) {
											$tags_links[] = $tag->name;
										}
										if ( ! empty( $tags_links ) ) {
											$meta_value = implode( ', ', $tags_links );
										}
									}
									$replace_data[ 'taxonomy='.$post_meta_key ] = $meta_value;
									$custom_fields[ 'taxonomy='.$post_meta_key ] = $meta_value;

								}
							}
						}

						if ( is_array( $places['location']['extension_fields'] ) ) {
							foreach ( $places['location']['extension_fields'] as $extension_shortcode => $extension_output ) {
								$replace_data[ $extension_shortcode ] = $extension_output;
							}
						}

						$replace_data = apply_filters( 'wpgmp_geotags_placeholder',$replace_data,$post->ID,$map->map_id );
						// Here parse infowindow setting and create infowindow message.
						$places['source'] = 'post';
						$places['title'] = $replace_data['post_title'];
						foreach ( $replace_data as $placeholder => $holder_value ) {
							$content = str_replace( '{'.$placeholder.'}', $holder_value, $content );
						}
						$places['infowindow_content'] = apply_filters( 'wpgmp_geotags_content',$content,$post->ID,$map->map_id );
						$places['content'] = get_the_excerpt();
						if ( ! empty( $value['address'] ) ) {
							$places['address'] = get_post_meta( $post->ID, $value['address'], true );
						} else {
							$places['address'] = '';
						}

						if ( empty( $value['latitude'] ) ) {
							$places['location']['lat'] = '';
						} else {
							$places['location']['lat'] = get_post_meta( $post->ID, $value['latitude'], true );
						}

						if ( empty( $value['longitude'] ) ) {
							$places['location']['lng'] = '';
						} else {
							$places['location']['lng'] = get_post_meta( $post->ID, $value['longitude'], true ); }

						if ( ! empty( $value['category'] ) ) {
							$category_name = get_post_meta( $post->ID, $value['category'], true ); }

						$assigned_category = unserialize( $category_name );

						if ( ! is_array( $assigned_category ) and '' != $category_name ) {
							$assigned_category[] = $category_name;
						}
						$places['id'] = $post->ID;
						$onclick = get_post_meta( $post->ID, '_wpgmp_metabox_location_redirect', true );
						$onclick = ($onclick) ? $onclick : 'marker';
						$wpgmp_metabox_custom_link = get_post_meta( $post->ID, '_wpgmp_metabox_custom_link', true );
						$places['location']['redirect_custom_link'] = $wpgmp_metabox_custom_link;
						$places['location']['onclick_action'] = $onclick;
						$places['location']['redirect_permalink'] = get_permalink( $post->ID );
						$places['location']['zoom'] = intval( $map->map_zoom_level );
						$custom_fields['post_link'] = $replace_data['post_link'];
						$custom_fields['post_featured_image'] = $replace_data['post_featured_image'];
						$custom_fields['post_categories'] = $replace_data['post_categories'];
						$custom_fields['post_tags'] = $replace_data['post_tags'];
						$places['location']['extra_fields'] = $custom_fields;

						$places['infowindow_disable'] = false;
						if ( is_array( $assigned_category ) ) {
							$category_count = 0;
							foreach ( $assigned_category as $category_name ) {
								if ( ! empty( $category_name ) ) {

									$loc_category = $all_categories_name[ sanitize_title( $category_name ) ];
									if ( empty( $loc_category ) ) {
										$loc_category = $all_categories[ sanitize_title( $category_name ) ];
									}
									$places['location']['icon'] = $loc_category->group_marker;
									$places['categories'][ $category_count ]['icon'] = $loc_category->group_marker;
									$places['categories'][ $category_count ]['name'] = $loc_category->group_map_title;
									$places['categories'][ $category_count ]['id']   = $loc_category->group_map_id;
									$places['categories'][ $category_count ]['type'] = 'category';
								}
								$category_count++;
							}
						}
						$map_data['places'][] = $places;
					}
				}
				wp_reset_postdata();
			}
		}
	}
}


// Add  new places from external data source.
$custom_markers = array();
$map_id = $map->map_id;
$all_custom_markers = apply_filters( 'wpgmp_marker_source',$custom_markers,$map_id );
if ( is_array( $all_custom_markers ) ) {
	foreach ( $all_custom_markers as $marker ) {
		$places = array();
		$new_catagory = $all_categories_name[ sanitize_title( $marker['category'] ) ];
		$places['id'] = ($marker['id']) ? $marker['id'] : rand( 4000,9999 );
		$places['title'] = $marker['title'];
		$places['source'] = 'external';
		$places['address'] = $marker['address'];
		$places['content'] = $marker['message'];
		$places['location']['onclick_action'] = 'marker';
		$places['location']['lat'] = $marker['latitude'];
		$places['location']['lng'] = $marker['longitude'];
		$places['infowindow_disable'] = false;
		$places['location']['zoom'] = intval( $map->map_zoom_level );
		if ( $new_catagory ) {
			$places['categories'][0]['icon'] = $new_catagory->group_marker;
			$places['categories'][0]['name'] = $new_catagory->group_map_title;
			$places['categories'][0]['id']   = $new_catagory->group_map_id;
			$places['categories'][0]['type'] = 'category';
			$places['location']['icon'] = ($marker['icon']) ? $marker['icon'] : $new_catagory->group_marker;
		}
		$map_data['places'][] = $places;
	}
}

// Here loop through all places and apply filter. Shortcode Awesome.
$filterd_places = array();
if ( is_array( $map_data['places'] ) ) {

	foreach ( $map_data['places'] as $place ) {
		$use_me = true;
		if ( 'post' == $place['source'] ) {
			$place['listing_hook'] = apply_filters( 'wpgmp_listing_html','',$place,$place['id'], $map->map_id );
		} else {
			$place['listing_hook'] = '';
		}
		// Category filter here.
		if ( isset( $shortcode_filters['category'] ) ) {
			$found_category = false;
			$show_categories_only = explode( ',', $shortcode_filters['category'] );
			foreach ( $place['categories'] as $cat ) {
				if ( in_array( strtolower( $cat['name'] ),$show_categories_only ) or in_array( strtolower( $cat['id'] ),$show_categories_only ) ) {
					$found_category = true;
				}
			}
			if ( false == $found_category ) {
				$use_me = false;
			}
		}
		$place['content'] = do_shortcode($place['content']);
		$use_me = apply_filters( 'wpgmp_show_place',$use_me,$place,$map );
		if ( true == $use_me ) {
			$filterd_places[] = apply_filters( 'wpgmp_marker_property',$place,$map );
		}
	}
	unset( $map_data['places'] );
}
$map_data['places'] = apply_filters( 'wpgmp_markers',$filterd_places, $map->map_id );

if ( '' == $map_data['map_options']['center_lat'] ) {
	$map_data['map_options']['center_lat'] = $map_data['places'][0]['location']['lat'];
}

if ( '' == $map_data['map_options']['center_lng'] ) {
	$map_data['map_options']['center_lng'] = $map_data['places'][0]['location']['lng'];
}

// Styles.
		$map_stylers = array();
if ( isset( $map->style_google_map['mapfeaturetype'] ) ) {
	unset( $map_stylers );
	$total_rows = count( $map->style_google_map['mapfeaturetype'] );
	for ( $i = 0;$i < $total_rows;$i++ ) {
		if ( empty( $map->style_google_map['mapfeaturetype'][ $i ] ) or empty( $map->style_google_map['mapelementtype'][ $i ] ) ) {
			continue;
		}
		if ( __( 'Select Featured Type',WPGMP_TEXT_DOMAIN ) == $map->style_google_map['mapfeaturetype'][ $i ] ) {
			continue;
		}
		if( $map->style_google_map['visibility'][ $i ] == 'off' ) {
			$map_stylers[]  = array(
			featureType   => $map->style_google_map['mapfeaturetype'][ $i ],
			elementType   => $map->style_google_map['mapelementtype'][ $i ],
			stylers       => array( array(
				visibility  => $map->style_google_map['visibility'][ $i ],
			),
			),
		);
		} else {
			$map_stylers[]  = array(
			featureType   => $map->style_google_map['mapfeaturetype'][ $i ],
			elementType   => $map->style_google_map['mapelementtype'][ $i ],
			stylers       => array( array(
				color       => '#'.str_replace( '#','',$map->style_google_map['color'][ $i ] ),
				visibility  => $map->style_google_map['visibility'][ $i ],
			),
			),
		);
		}
		
	}
}

if ( isset( $map_stylers ) ) {
	if ( is_array( $map_stylers ) ) {
		$map_data['styles'] = $map_stylers;
	}
} elseif ( $map->map_all_control['custom_style'] != '' ) {
	$map_data['styles'] = stripslashes( $map->map_all_control['custom_style'] );
}
// Street view.
if ( $map->map_street_view_setting['street_control'] == 'true' ) {
	$map_data['street_view'] = array(
	'street_control'            => @$map->map_street_view_setting['street_control'],
	'street_view_close_button'  => (@$map->map_street_view_setting['street_view_close_button'] === 'true'?true:false),
	'links_control'             => (@$map->map_street_view_setting['links_control'] === 'true'?true:false),
	'street_view_pan_control'   => (@$map->map_street_view_setting['street_view_pan_control'] === 'true'?true:false),
	'pov_heading'				=> $map->map_street_view_setting['pov_heading'],
	'pov_pitch'					=> $map->map_street_view_setting['pov_pitch'],
	);
}

	  // Routes.
if ( ! empty( $map->map_route_direction_setting['route_direction'] ) && $map->map_route_direction_setting['route_direction'] == 'true' ) {
	$wpgmp_routes = $map->map_route_direction_setting['specific_routes'];
	if ( ! empty( $wpgmp_routes ) ) {

		$all_routes = array();
		foreach ( $wpgmp_routes as $route_key => $wpgmp_route ) {
			$wpgmp_route_data[ $route_key ] = $routes_data[ $wpgmp_route ];
			$wpgmp_route_way_points = $wpgmp_route_data[ $route_key ]->route_way_points;

			$location_data[ $route_key ]['route_id']                  = $wpgmp_route_data[ $route_key ]->route_id;
			$location_data[ $route_key ]['route_title']                  = $wpgmp_route_data[ $route_key ]->route_title;
			$location_data[ $route_key ]['route_stroke_color']        = '#'.str_replace( '#','',$wpgmp_route_data[ $route_key ]->route_stroke_color );
			$location_data[ $route_key ]['route_stroke_opacity']      = $wpgmp_route_data[ $route_key ]->route_stroke_opacity;
			$location_data[ $route_key ]['route_stroke_weight']       = $wpgmp_route_data[ $route_key ]->route_stroke_weight;
			$location_data[ $route_key ]['route_travel_mode']         = $wpgmp_route_data[ $route_key ]->route_travel_mode;
			$location_data[ $route_key ]['route_unit_system']         = $wpgmp_route_data[ $route_key ]->route_unit_system;
			$location_data[ $route_key ]['route_marker_draggable']    = ($wpgmp_route_data[ $route_key ]->route_marker_draggable === 'true');
			$location_data[ $route_key ]['route_custom_marker']       = ($wpgmp_route_data[ $route_key ]->route_custom_marker === 'true');
			$location_data[ $route_key ]['route_optimize_waypoints']  = ($wpgmp_route_data[ $route_key ]->route_optimize_waypoints === 'true');
			$location_data[ $route_key ]['route_direction_panel']     = ($wpgmp_route_data[ $route_key ]->route_direction_panel === 'true');
			if ( is_array( $wpgmp_route_way_points ) and ! empty( $wpgmp_route_way_points ) ) {

				$wpgmp_route_way_point_data = $location_obj->fetch( array( array( 'location_id', 'IN', implode( ',',$wpgmp_route_way_points ) ) ) );

				if ( $wpgmp_route_way_point_data ) {
					foreach ( $wpgmp_route_way_point_data as $wpgmp_route_way_point_key => $row ) {
						$location_data[ $route_key ]['way_points'][] = $row->location_latitude.','.$row->location_longitude;
					}
				}
			}

			if ( $wpgmp_route_data[ $route_key ]->route_start_location && ! empty( $wpgmp_route_data[ $route_key ]->route_start_location ) ) {
				 $route_start_obj = $location_obj->fetch( array( array( 'location_id', 'IN', $wpgmp_route_data[ $route_key ]->route_start_location ) ) );
				$location_data[ $route_key ]['start_location_data'] = $route_start_obj[0]->location_latitude.','.$route_start_obj[0]->location_longitude;
			}

			if ( $wpgmp_route_data[ $route_key ]->route_end_location && ! empty( $wpgmp_route_data[ $route_key ]->route_end_location ) ) {
				 $route_end_obj = $location_obj->fetch( array( array( 'location_id', 'IN', $wpgmp_route_data[ $route_key ]->route_end_location ) ) );

				$location_data[ $route_key ]['end_location_data'] = $route_end_obj[0]->location_latitude.','.$route_end_obj[0]->location_longitude;
			}
		}
	}
	$map_data['routes'] = $location_data;
}

	// Marker cluster.
if ( ! empty( $map->map_cluster_setting['marker_cluster'] ) && $map->map_cluster_setting['marker_cluster'] == 'true' ) {
	$map_data['marker_cluster'] = array(
	'grid'      => $map->map_cluster_setting['grid'],
	'max_zoom'  => $map->map_cluster_setting['max_zoom'],
	'image_path' => WPGMP_IMAGES.'m',
	'icon'  => WPGMP_IMAGES.'cluster/'.$map->map_cluster_setting['icon'],
	'hover_icon'  => WPGMP_IMAGES.'cluster/'.$map->map_cluster_setting['hover_icon'],
	'apply_style'  => ($map->map_cluster_setting['marker_cluster_style'] == 'true'),
	'marker_zoom_level' => (isset( $map->map_cluster_setting['location_zoom'] ) ? $map->map_cluster_setting['location_zoom'] : 10),
	);
	$map_data['marker_cluster'] = apply_filters( 'wpgmp_marker_cluster',$map_data['marker_cluster'],$map->map_id );
}

// Overlays.
if ( ! empty( $map->map_overlay_setting['overlay'] ) && $map->map_overlay_setting['overlay'] == 'true' ) {
		$map_data['overlay_setting'] = array(
		  'border_color'  => '#'.str_replace( '#','',$map->map_overlay_setting['overlay_border_color'] ),
		  'width'         => $map->map_overlay_setting['overlay_width'],
		  'height'        => $map->map_overlay_setting['overlay_height'],
		  'font_size'     => $map->map_overlay_setting['overlay_fontsize'],
		  'border_width'  => $map->map_overlay_setting['overlay_border_width'],
		  'border_style'  => $map->map_overlay_setting['overlay_border_style'],
		);
}

// Limit panning and zoom control.
if ( ! empty( $map->map_all_control['panning_control'] ) && $map->map_all_control['panning_control'] == 'true' ) {
	$map_data['panning_control'] = array(
	'from_latitude'      => $map->map_all_control['from_latitude'],
	'from_longitude'      => $map->map_all_control['from_longitude'],
	'to_latitude'         => $map->map_all_control['to_latitude'],
	'to_longitude'        => $map->map_all_control['to_longitude'],
	'zoom_level'          => $map->map_zoom_level,
	);
}
// Display tabs on maps.
if ( ! empty( $map->map_all_control['display_marker_category'] ) && $map->map_all_control['display_marker_category'] == true ) {



	if ( ! empty( $map->map_route_direction_setting['route_direction'] ) && $map->map_route_direction_setting['route_direction'] == 'true' ) {
		$display_route_tab_data = $map->map_route_direction_setting['route_direction'];
	}
	$map_data['map_tabs']   = array(
	'hide_tabs_default' => ('true' == $map->map_all_control['hide_tabs_default']),
	'category_tab'          => array(
	  'cat_tab'       => ('true' == $map->map_all_control['wpgmp_category_tab']),
	  'cat_tab_title'       => ($map->map_all_control['wpgmp_category_tab_title']) ? $map->map_all_control['wpgmp_category_tab_title'] : __( 'Categories',WPGMP_TEXT_DOMAIN ),
	  'cat_order_by'       => $map->map_all_control['wpgmp_category_order'],
	  'show_count'	=> ('true' == $map->map_all_control['wpgmp_category_tab_show_count']),
	  'hide_location'	=> ($map->map_all_control['wpgmp_category_tab_hide_location'] == 'true'),
	  'select_all'	=> ($map->map_all_control['wpgmp_category_tab_show_all'] == 'true'),

	),
	'direction_tab'         => array(
		'dir_tab' => ('true' == $map->map_all_control['wpgmp_direction_tab']),
		'direction_tab_title' => ($map->map_all_control['wpgmp_direction_tab_title']) ? $map->map_all_control['wpgmp_direction_tab_title'] : __( 'Directions',WPGMP_TEXT_DOMAIN ),
		'default_start_location' => $map->map_all_control['wpgmp_direction_tab_start_default'],
		'default_end_location' => $map->map_all_control['wpgmp_direction_tab_end_default'],
		'suppress_markers' => ('true' == $map->map_all_control['wpgmp_direction_tab_suppress_markers']),
	),
	'nearby_tab'            => array(
		'near_tab' => ($map->map_all_control['wpgmp_nearby_tab'] == 'true'),
		'nearby_tab_title' => ($map->map_all_control['wpgmp_nearby_tab_title']) ? $map->map_all_control['wpgmp_nearby_tab_title'] : __( 'Nearby',WPGMP_TEXT_DOMAIN ),
		'nearby_amenities' => $map->map_all_control['wpgmp_nearby_amenities'],
		'nearby_circle_fillcolor' => sanitize_text_field( $map->map_all_control['nearby_circle_fillcolor'] ),
		'nearby_circle_fillopacity' => sanitize_text_field( $map->map_all_control['nearby_circle_fillopacity'] ),
		'nearby_circle_strokecolor' => sanitize_text_field( $map->map_all_control['nearby_circle_strokecolor'] ),
		'nearby_circle_strokeopacity' => sanitize_text_field( $map->map_all_control['nearby_circle_strokeopacity'] ),
		'show_nearby_circle' => (sanitize_text_field( $map->map_all_control['show_nearby_circle'] ) == 'true'),
		'nearby_circle_strokeweight' => sanitize_text_field( $map->map_all_control['nearby_circle_strokeweight'] ),
		'nearby_circle_zoom' => ($map->map_all_control['nearby_circle_zoom']) ? sanitize_text_field( $map->map_all_control['nearby_circle_zoom'] ) : 9,
		),
	'route_tab'             => array(
	  'display_route_tab'       => ($map->map_all_control['wpgmp_route_tab'] == 'true'),
	  'route_tab_title' => ($map->map_all_control['wpgmp_route_tab_title']) ? $map->map_all_control['wpgmp_route_tab_title'] : __( 'Routes',WPGMP_TEXT_DOMAIN ),
	  'display_route_tab_data'  => ( 'true' == $display_route_tab_data ),
	  'route_tab_data'          => $map_data['routes'],
	  'route_tab_title'       => $map->map_all_control['wpgmp_route_tab_title'],

	),
	'route_start_location'  => ($map->map_all_control['wpgmp_direction_tab_start']) ? $map->map_all_control['wpgmp_direction_tab_start'] : 'textbox',
	'route_end_location'    => ($map->map_all_control['wpgmp_direction_tab_end']) ? $map->map_all_control['wpgmp_direction_tab_end'] : 'textbox',
	);
	$map_data['map_tabs']['extension_tabs'] = apply_filters( 'wpgmp_map_tabs','' );

}

	  // Display nearby tabs.
if ( ! is_admin() && ! empty( $map->map_all_control['wpgmp_nearby_tab'] ) && $map->map_all_control['wpgmp_nearby_tab'] == true ) {
	$map_data['nearby_tab'] = array();
}

if ( ! empty( $map->map_all_control['display_listing'] ) && $map->map_all_control['display_listing'] == true ) {
		$filcate = array( 'place_category' );
		$soring_array = array(
			'category_asc'    => 'A-Z Category',
			'category_desc'   => 'Z-A Category',
			'title_asc'       => 'A-Z Title',
			'title_desc'      => 'Z-A Title',
			'address_asc'     => 'A-Z Address',
			'address_desc'    => 'Z-A Address',
		);

		if ( empty( $map->map_all_control['wpgmp_listing_number'] ) ) {
			$map->map_all_control['wpgmp_listing_number'] = 10; }

		$map_data['listing'] = array(
		  'listing_header' => $map->map_all_control['wpgmp_before_listing'],
		  'display_search_form'  => ( 'true' == $map->map_all_control['wpgmp_search_display'] ),
		  'display_category_filter'  => ($map->map_all_control['wpgmp_display_category_filter'] == 'true'),
		  'display_sorting_filter'  => ('true' == $map->map_all_control['wpgmp_display_sorting_filter']),
		  'display_radius_filter'  => ('true' == $map->map_all_control['wpgmp_display_radius_filter']),
		  'radius_dimension'  => $map->map_all_control['wpgmp_radius_dimension'],
		  'radius_options'  => $map->map_all_control['wpgmp_radius_options'],
		  'display_location_per_page_filter'  => ('true' == $map->map_all_control['wpgmp_display_location_per_page_filter']),
		  'display_print_option'  => ($map->map_all_control['wpgmp_display_print_option'] == 'true'),
		  'display_grid_option'  => ($map->map_all_control['wpgmp_display_grid_option'] == 'true'),
		  'filters' => array( 'place_category' ),
		  'sorting_options' => $soring_array,
		  'default_sorting'     => array( 'orderby' => $map->map_all_control['wpgmp_categorydisplaysort'],'inorder' => 'asc' ),
		  'listing_container'   => '.location_listing'.$map->map_id,
		  'tabs_container'      => '.location_listing'.$map->map_id,
		  'pagination'          => array( 'listing_per_page' => $map->map_all_control['wpgmp_listing_number'] ),
		  'list_grid'           => ($map->map_all_control['wpgmp_list_grid']) ? $map->map_all_control['wpgmp_list_grid'] : 'wpgmp_listing_list',
		  'listing_placeholder' => do_shortcode(stripslashes( trim( $map->map_all_control['wpgmp_categorydisplayformat'] ) )),
		);
}
	  $map_data['map_property'] = array( 'map_id' => $map->map_id );


if ( '' != sanitize_text_field( $map->map_all_control['geojson_url'] ) ) {
	$map_data['geojson'] = sanitize_text_field( $map->map_all_control['geojson_url'] );
}

	  // Drawing.
	  $drawing_editable_true = false;
if ( is_admin() && current_user_can( 'manage_options' ) ) {
	$drawing_editable_true = true;
	$objects = array( 'circle','polygon','polyline','rectangle' );
	for ( $i = 0; $i < count( $objects ); $i++ ) {
		$object_name = $objects[ $i ];
		$drawingModes[] = 'google.maps.drawing.OverlayType.'.strtoupper( $object_name );

		$drawing_options[ $object_name ][] = "fillColor: '#ff0000'";
		$drawing_options[ $object_name ][] = "strokeColor: '#ff0000'";
		$drawing_options[ $object_name ][] = 'strokeWeight: 1';
		$drawing_options[ $object_name ][] = 'strokeOpacity: 1';
		$drawing_options[ $object_name ][] = 'zindex: 1';
		$drawing_options[ $object_name ][] = 'fillOpacity: 1';
		$drawing_options[ $object_name ][] = 'editable: true';
		$drawing_options[ $object_name ][] = 'draggable: true';
		$drawing_options[ $object_name ][] = 'clickable: false';
	}

	if ( is_array( $drawingModes ) ) {
		$display_modes = implode( ',',$drawingModes ); }

	if ( is_array( $drawing_options['circle'] ) ) {
		$display_circle_options = implode( ',',$drawing_options['circle'] ); }

	if ( is_array( $drawing_options['polygon'] ) ) {
		$display_polygon_options = implode( ',',$drawing_options['polygon'] ); }

	if ( is_array( $drawing_options['polyline'] ) ) {
		$display_polyline_options = implode( ',',$drawing_options['polyline'] ); }

	if ( is_array( $drawing_options['rectangle'] ) ) {
		$display_rectangle_options = implode( ',',$drawing_options['rectangle'] ); }
}
if ( $map->map_polyline_setting['polylines'] != '' ) {
	$map_shapes = array();
	$all_saved_shape  = $map->map_polyline_setting['polylines'];
	$all_shapes = explode( '|',$all_saved_shape[0] );
	if ( is_array( $all_shapes ) ) {
		foreach ( $all_shapes as $key => $shapes ) {
			$find_shape = explode( '=',$shapes, 2 );

			if ( 'polylines' == $find_shape[0] ) {
				$polylines_shape[0] = $find_shape[1]; } else if ( 'polygons' == $find_shape[0] ) {
				$polygons_shape[0]  = $find_shape[1]; } else if ( 'circles' == $find_shape[0] ) {
					$circles_shape[0] = $find_shape[1]; } else if ( 'rectangles' == $find_shape[0] ) {
					$rectangles_shape[0]  = $find_shape[1]; }
		}
	}

	if ( $polygons_shape[0] && ! empty( $polygons_shape[0] ) ) {
		$all_polylines  = explode( '::',$polygons_shape[0] );

		for ( $p = 0;$p < count( $all_polylines );$p++ ) {
			unset( $settings );
			$all_settings = explode( '...',$all_polylines[ $p ] );
			$cordinates = explode( '----',$all_settings[0] );
			$all_events = $all_settings[2];
			$all_events = explode( '***',$all_events );
			$all_settings_val = explode( ',',$all_settings[1] );

			if ( empty( $all_settings_val[3] ) ) {
				$all_settings_val[3] = '#ff0000'; }

			if ( empty( $all_settings_val[4] ) ) {
				$all_settings_val[4] = 1; }

			if ( empty( $all_settings_val[2] ) ) {
				$all_settings_val[2] = '#ff0000'; }

			if ( empty( $all_settings_val[1] ) ) {
				$all_settings_val[1] = 1; }

			if ( empty( $all_settings_val[0] ) ) {
				$all_settings_val[0] = 5; }

			$settings['stroke_color']   = '#'.str_replace( '#','',$all_settings_val[2] );
			$settings['stroke_opacity'] = $all_settings_val[1];
			$settings['stroke_weight']  = $all_settings_val[0];
			$settings['fill_color']     = '#'.str_replace( '#','',$all_settings_val[3] );
			$settings['fill_opacity']   = $all_settings_val[4];
			$events = array();
			$events['url'] = $all_events[0];
				$events['message'] = nl2br(stripcslashes( $all_events[1] ));
			$map_shapes['polygons'][]   = array( 'cordinates' => $cordinates, 'settings' => $settings, 'events' => $events );
		}
	}

	if ( $polylines_shape[0] && ! empty( $polylines_shape[0] ) ) {
		$all_polylines = explode( '::',$polylines_shape[0] );
		for ( $p = 0;$p < count( $all_polylines );$p++ ) {
			$all_settings = explode( '...',$all_polylines[ $p ] );
			$cordinates = explode( '----',$all_settings[0] );
			$all_events = $all_settings[2];
			$all_events = explode( '***',$all_events );
			$all_settings_val = explode( ',',$all_settings[1] );

			if ( empty( $all_settings_val[2] ) ) {
				$all_settings_val[2] = '#ff0000'; }

			if ( empty( $all_settings_val[1] ) ) {
				$all_settings_val[1] = 1; }

			if ( empty( $all_settings_val[0] ) ) {
				$all_settings_val[0] = 5; }

			$settings['stroke_color']   = '#'.str_replace( '#','',$all_settings_val[2] );
			$settings['stroke_opacity'] = $all_settings_val[1];
			$settings['stroke_weight']  = $all_settings_val[0];
			$events = array();
			$events['url'] = $all_events[0];
				$events['message'] = nl2br(stripcslashes( $all_events[1] ));
			$map_shapes['polylines'][]  = array( 'cordinates' => $cordinates, 'settings' => $settings, 'events' => $events );
		}
	}
	if ( $circles_shape && ! empty( $circles_shape[0] ) ) {
		$all_circles = explode( '::',$circles_shape[0] );
		for ( $p = 0;$p < count( $all_circles );$p++ ) {
			$all_settings = explode( '...',$all_circles[ $p ] );
			$cordinates = explode( '----',$all_settings[0] );
			$all_events = $all_settings[2];
			$all_events = explode( '***',$all_events );
			$all_settings_val = explode( ',',$all_settings[1] );

			if ( empty( $all_settings_val[5] ) ) {
				$all_settings_val[5] = 1; }

			if ( empty( $all_settings_val[3] ) ) {
				$all_settings_val[3] = '#ff0000'; }

			if ( empty( $all_settings_val[4] ) ) {
				$all_settings_val[4] = 1; }

			if ( empty( $all_settings_val[2] ) ) {
				$all_settings_val[2] = '#ff0000'; }

			if ( empty( $all_settings_val[1] ) ) {
				$all_settings_val[1] = 1; }

			if ( empty( $all_settings_val[0] ) ) {
				$all_settings_val[0] = 5; }

			$settings['stroke_color']   = '#'.str_replace( '#','',$all_settings_val[2] );
			$settings['stroke_opacity'] = $all_settings_val[1];
			$settings['stroke_weight']  = $all_settings_val[0];
			$settings['fill_color']     = '#'.str_replace( '#','',$all_settings_val[3] );
			$settings['fill_opacity']   = $all_settings_val[4];
			$settings['radius']         = $all_settings_val[5];
			$events = array();
			$events['url'] = $all_events[0];
			$events['message'] = nl2br(stripcslashes( $all_events[1] ));
			$map_shapes['circles'][]    = array( 'cordinates' => $cordinates,'settings' => $settings, 'events' => $events );
		}
	}

	if ( $rectangles_shape[0] && ! empty( $rectangles_shape[0] ) ) {
		$all_polylines = explode( '::',$rectangles_shape[0] );
		for ( $p = 0;$p < count( $all_polylines );$p++ ) {
			$all_settings = explode( '...',$all_polylines[ $p ] );
			$cordinates = explode( '----',$all_settings[0] );
			$all_settings_val = explode( ',',$all_settings[1] );
			$all_events = $all_settings[2];
			$all_events = explode( '***',$all_events );
			if ( empty( $all_settings_val[3] ) ) {
				$all_settings_val[3] = 'ff0000'; }

			if ( empty( $all_settings_val[4] ) ) {
				$all_settings_val[4] = 1; }

			if ( empty( $all_settings_val[2] ) ) {
				$all_settings_val[2] = 'ff0000'; }

			if ( empty( $all_settings_val[1] ) ) {
				$all_settings_val[1] = 1; }

			if ( empty( $all_settings_val[0] ) ) {
				$all_settings_val[0] = 5; }

			$settings['stroke_color']   = '#'.str_replace( '#','',$all_settings_val[2] );
			$settings['stroke_opacity'] = $all_settings_val[1];
			$settings['stroke_weight']  = $all_settings_val[0];
			$settings['fill_color']     = '#'.str_replace( '#','',$all_settings_val[3] );
			$settings['fill_opacity']   = $all_settings_val[4];
			$events = array();
			$events['url'] = $all_events[0];
			$events['message'] = nl2br(stripcslashes( $all_events[1] ));
			$map_shapes['rectangles'][] = array( 'cordinates' => $cordinates, 'settings' => $settings, 'events' => $events );
		}
	}
}


	  $map_data['shapes'] = array(
		'drawing_editable'  => $drawing_editable_true,
	  );

	  $map_shapes = apply_filters( 'wpgmp_shapes',@$map_shapes,$map_data,$map->map_id );

if ( ! empty( $map_shapes ) && is_array( $map_shapes ) ) {
	$map_data['shapes']['shape'] = $map_shapes; }

$map_output = '';

	  	$map_output .= '<div class="wpgmp_map_container" rel="map'.$map->map_id.'">';

		$map_div = '<div class="wpgmp_map" style="width:'.$width.'; height:'.$height.';" id="map'.$map->map_id.'" ></div>';
		$listing_div = '';
if ( ! empty( $map->map_all_control['display_listing'] ) && $map->map_all_control['display_listing'] == true ) {

			$listing_div = '<div class="location_listing'.$map->map_id.'" style="float:left; width:100%;"></div><div class="location_pagination'.$map->map_id.' wpgmp_pagination" style="float:left; width:100%;"></div>';
}
		$output = $map_div.$listing_div;
		$map_output .= apply_filters( 'wpgmp_map_output', $output,$map_div,$listing_div,$map->map_id );
		$map_output .= '</div>';
		$map_data_obj = json_encode( $map_data );

$map_output .= '<script>jQuery(document).ready(function($) {var map'.$map_id.' = $("#map'.$map_id.'").maps('.$map_data_obj.').data("wpgmp_maps");});</script>';
	return $map_output;
