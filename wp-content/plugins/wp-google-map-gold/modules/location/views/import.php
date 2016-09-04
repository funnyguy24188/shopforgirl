<?php
/**
 * Import Location(s) Tool.
 * @package Maps
 * @author Flipper Code <hello@flippercode.com>
 */

$form = new FlipperCode_HTML_Markup();
$form->set_header( __( 'Import Locations', WPGMP_TEXT_DOMAIN ),$response );
$form->add_element('message','import_message',array(
	'value' => __( 'You must have latitude and longitude in the csv file.' ),
	'class' => 'alert alert-success',
	'before' => '<div class="col-md-11">',
	'after' => '</div>',
));
$supported_delimiters = array(
	',' => __( 'Comma (,)',WPGMP_TEXT_DOMAIN ),
	';' => __( 'Semicolon (;)',WPGMP_TEXT_DOMAIN ),
	':' => __( 'Colon (:)',WPGMP_TEXT_DOMAIN ),
	'|' => __( 'Bar (|)',WPGMP_TEXT_DOMAIN ),
	'tab' => __( 'Tab (\t)',WPGMP_TEXT_DOMAIN ),
	'space' => __( 'Space ( )',WPGMP_TEXT_DOMAIN ),
	);
$form->add_element( 'radio', 'wpgmp_csv_delimiter', array(
	'lable' => __( 'Choose Delimiter', WPGMP_TEXT_DOMAIN ),
	'radio-val-label' => $supported_delimiters,
	'current' => '',
	'class' => 'chkbox_class inline',
	'default_value' => ',',
));
$form->add_element('file','import_file',array(
	'label' => __( 'Choose File',WPGMP_TEXT_DOMAIN ),
	'class' => 'file_input',
	'desc' => __( 'Please upload a valid CSV file.',WPGMP_TEXT_DOMAIN ),
));
$form->add_element('submit','import_loc',array(
	'value' => __( 'Import Locations',WPGMP_TEXT_DOMAIN ),
));
$html = 'Below are the detailed instruction to import your data successfully. You should have following columns in your csv to import';
$html .= '
 <table class="table dataTable no-footer">
 <thead><tr><th>#</th><th>Column Name</th><th>Required?</th><th>Details</th></tr></thead>
 <tbody>
 <tr>
 <th scope="row">1</th> <td>Title</td><td>NO</td><td>Title of the Location.</td> </tr>
 <th scope="row">2</th> <td>Address</td><td>NO</td><td>Address of the Location.</td></tr>
 <th scope="row">3</th> <td>Latitude</td><td>YES</td><td>Latitude of the Location.</td></tr>
 <th scope="row">4</th> <td>Longitude</td><td>YES</td><td>Longitude of the Location.</td></tr>
 <th scope="row">5</th> <td>Message</td><td>NO</td><td>Message you want to show in the infowindow.</td></tr>
 <th scope="row">6</th> <td>Categories</td><td>NO</td><td>Assign category to the location. Multiple categories should be separated by comma.</td></tr>
 <th scope="row">7</th> <td>City</td><td>NO</td><td>City of the Location.</td></tr>
 <th scope="row">8</th> <td>State</td><td>NO</td><td>State of the Location.</td></tr>
 <th scope="row">9</th> <td>Country</td><td>NO</td><td>Country of the Location.</td></tr>
 <th scope="row">10</th> <td>Postal Code</td><td>NO</td><td>Postal Code of the Location.</td></tr>
 <th colspan="4" class="alert-info">if you want to add custom fields/extra fields in location details, you can do that easily. Just add more columns in the csv and they\'ll be treated as extra fields. e.g let\'s add fax, website and email details.</th></tr>
 <th scope="row">11</th> <td>Fax</td><td>NO</td><td>Fax will be added as extra field in location details.</td></tr>
 <th scope="row">12</th> <td>Website</td><td>NO</td><td>Website will be added as extra field in location details.</td></tr>
 <th scope="row">13</th> <td>Email</td><td>NO</td><td>Email will be added as extra field in location details.</td>
 </tr>
 </tr></tbody>
</table>
<a class="btn btn-primary" href="'.WPGMP_URL.'assets/import_sample_file.zip'.'"> Download Sample File</a>
';


$form->add_element('html','instruction_html', array(
	'html' => $html,
	'before' => '<div class="col-md-11">',
	'after' => '</div>',
	));
$form->add_element('hidden','operation',array(
	'value' => 'import_location',
));
$form->add_element('hidden','import',array(
	'value' => 'location_import',
));
$form->render();
