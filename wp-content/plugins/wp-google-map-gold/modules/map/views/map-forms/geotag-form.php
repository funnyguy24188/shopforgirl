<?php
/**
 * Contro Positioning over google maps.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form->add_element( 'group', 'map_geotags_settings', array(
	'value' => __( 'GEO Tags', WPGMP_TEXT_DOMAIN ),
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
));
$form->add_element( 'checkbox', 'map_all_control[geo_tags]', array(
	'lable' => __( 'GEO Tags', WPGMP_TEXT_DOMAIN ),
	'value' => 'true',
	'id' => 'wpgmp_geo_tags',
	'current' => $data['map_all_control']['geo_tags'],
	'desc' => __( 'Enable to display location from your own custom fields of posts or custom post types.', WPGMP_TEXT_DOMAIN ),
	'class' => 'chkbox_class switch_onoff',
	'data' => array( 'target' => '.geo_tags_setting' ),
));

$screens = array( 'post' );

$args = array(
			'public'  => true,
			'_builtin'  => false,
			);

$output = 'names';
$operator = 'and';
$post_types = get_post_types( $args, $output, $operator );
$custom_post_types = array( 'post' );
$all_post_types = array_merge( $post_types, $custom_post_types );

if ( ! empty( $all_post_types ) ) {
	$count = 0;
	foreach ( $all_post_types  as $post_type ) {

		$input_data[ $count ][0] = $post_type;
		$input_data[ $count ][1] = '<input placeholder="'.__( 'Custom Field Name',WPGMP_TEXT_DOMAIN ).'" type="text" class="form-control" name="map_geotags['.$post_type.'][address]" value="'.$data['map_geotags'][ $post_type ]['address'].'">';
		$input_data[ $count ][2] = '<input placeholder="'.__( 'Custom Field Name',WPGMP_TEXT_DOMAIN ).'" type="text" class="form-control" name="map_geotags['.$post_type.'][latitude]" value="'.$data['map_geotags'][ $post_type ]['latitude'].'">';
		$input_data[ $count ][3] = '<input placeholder="'.__( 'Custom Field Name',WPGMP_TEXT_DOMAIN ).'" type="text" class="form-control" name="map_geotags['.$post_type.'][longitude]" value="'.$data['map_geotags'][ $post_type ]['longitude'].'">';
		$input_data[ $count ][4] = '<input placeholder="'.__( 'Custom Field Name',WPGMP_TEXT_DOMAIN ).'" type="text" class="form-control" name="map_geotags['.$post_type.'][category]" value="'.$data['map_geotags'][ $post_type ]['category'].'">';
		$count++;
	}
}

$form->add_element( 'table', 'geotags_table', array(
	'heading' => array( 'Post Type','Address','Latitude','Longitude','Category' ),
	'data' => $input_data,
	'id' => 'geo_tags_table',
	'before' => '<div class="col-md-12">',
	'after' => '</div>',
	'show' => 'false',
	'class' => 'dataTable geo_tags_setting',
));

