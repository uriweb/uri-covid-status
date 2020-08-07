<?php
/*
Plugin Name: URI COVID-19 Status
Plugin URI: https://www.uri.edu
Description: Dashboard display of COVID data
Version: 1.0
Author: URI Web Communications
Author URI: 
@author: John Pennypacker <jpennypacker@uri.edu>
@author: Brandon Fuller <bjcfuller@uri.edu>
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');



/**
 * Loads the javascript
 */
function uri_covid_scripts() {
	wp_register_script( 'uri-covid-status', plugins_url( '/js/covid.js', __FILE__ ) );
	wp_enqueue_script( 'uri-covid-status' );
	$days = uri_covid_get_days();
	$data = array(
		array(
			'Date',
			'Tests',
			'Positive cases',
			'Students in isolation',
		)
	);
	foreach( $days as $day ) {
		$data[] = array(
			$day['date'],
			(int)$day['tests'],
			(int)$day['positives'],
			(int)$day['occupied_quarantine_beds'],
		);
	}
	wp_localize_script( 'uri-covid-status', 'uriCOVIDStatus', $data );
}


/**
 * Helper function to calculate and format percentages
 */
function uri_covid_percentage( $x, $y ) {
	if( $y == 0 ) {
		return FALSE;
	}
	return number_format( $x / $y * 100, 0 );
}

/**
 * Shortcode callback
 */
function uri_covid_shortcode($attributes, $content, $shortcode) {
   
	uri_covid_scripts();
	
	$days = uri_covid_get_days();
	$last_day = end( $days );
	

	$output = '<div class="uri-covid-status">';
	

	if ( shortcode_exists( 'cl-metric' ) ) {
		$date = date( 'F j, Y', strtotime( $last_day['date'] ) );
		$output .= '<h2>Coronavirus data for ' . $date . '</h2>';

		$output .= '<div class="cl-tiles fifths">';

		$v = ( empty( $last_day['tests'] ) ) ? 'O' : $last_day['tests'];
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="Tests Administered"]', FALSE );

		$v = ( empty( $last_day['positives'] ) ) ? 'O' : $last_day['positives'];
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="Positive Tests"]', FALSE );

		$v = uri_covid_percentage( $last_day['positives'], $last_day['tests']);
		$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Percentage of positive tests"]', FALSE );

		$v = ( empty( $last_day['occupied_quarantine_beds'] ) ) ? 'O' : $last_day['occupied_quarantine_beds'];
		$s = ( $v == 1 ) ? 'Student' : 'Students';
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $s . ' in isolation"]', FALSE );

		$v = uri_covid_percentage( $last_day['occupied_quarantine_beds'], $last_day['total_quarantine_beds']);
		$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Isolation beds occupied"]', FALSE );

		$output .= '</div>';

	} else {
		$output .= '
	Date = ' . $last_day['date'] . '<br>
	Total number of tests = ' . $last_day['tests'] . '<br>
	Total Positive Cases = ' . $last_day['positives'] . '<br>
	Percent Positive = ' .  number_format( $last_day['positives'] / $last_day['tests'] * 100, 2 ) . '<br>
	Number of occupied isolation/quarantine beds = ' . $last_day['occupied_quarantine_beds'] . '<br>
	Percent of occupied isolation/quarantine beds = ' . number_format( $last_day['occupied_quarantine_beds'] / $last_day['total_quarantine_beds'] * 100, 2 ) . '
	';
	}
	
	$output .= '<div id="covid-line-chart"></div>';


	$output .= '</div>';

	return $output;

}
add_shortcode( 'uri-covid-status', 'uri_covid_shortcode' );



/**
 * Load the data from the database
 */
function uri_covid_get_days() {
	if ( FALSE === ( $days = get_transient( 'uri_covid_days' ) ) ) {
		uri_covid_query_spreadsheet();
	}
	return $days;
}

/**
 * Load the data from the source spreadsheet
 */
function uri_covid_query_spreadsheet() {
	$data_url = 'https://spreadsheets.google.com/feeds/list/1o3Lr_FLnngmVMx3oPGwh4XHK4B3jmiuXLGpKOsJN6mE/1/public/values?alt=json';
	$data = json_decode( file_get_contents( $data_url ) );

	$days = array();

	foreach($data->{'feed'}->{'entry'} as $row) {
		$days[] = array(
			'date' => $row->{'gsx$date'}->{'$t'},
			'tests' => $row->{'gsx$tests'}->{'$t'},
			'positives' => $row->{'gsx$positives'}->{'$t'},
			'total_quarantine_beds' => $row->{'gsx$quarbeds'}->{'$t'},
			'occupied_quarantine_beds' => $row->{'gsx$occupiedquarbeds'}->{'$t'}
		);
	}
	
	set_transient( 'uri_covid_days', $days, HOUR_IN_SECONDS );
	
}

