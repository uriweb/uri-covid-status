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
			'Students in isolation / quarantine',
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
 * Shortcode callback
 */
function uri_covid_shortcode($attributes, $content, $shortcode) {
   
	uri_covid_scripts();
	
	$days = uri_covid_get_days();
	$totals = uri_covid_total_days( $days );
	$last_day = end( $days );
	
	

	$output = '<div class="uri-covid-status">';
	
	$date = date( 'F j, Y', strtotime( $last_day['date'] ) );
	$output .= '<h2>Coronavirus data for ' . $date . '</h2>';

	if ( shortcode_exists( 'cl-metric' ) ) {

		$output .= '<div class="cl-tiles fifths">';

		$v = ( empty( $last_day['tests'] ) ) ? 'O' : _uri_covid_number_format( $last_day['tests'] );
		$caption = ( 1 == $v ) ? 'Test Administered': 'Tests Administered';
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '"]', FALSE );

		$v = ( empty( $last_day['positives'] ) ) ? 'O' : _uri_covid_number_format( $last_day['positives'] );
		$caption = ( 1 == $v ) ? 'Positive Case': 'Positive Cases';
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '"]', FALSE );

		$v = _uri_covid_percentage( $last_day['positives'], $last_day['tests'] );
		$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Percentage of positive tests"]', FALSE );

		$v = ( empty( $last_day['occupied_quarantine_beds'] ) ) ? 'O' : _uri_covid_number_format( $last_day['occupied_quarantine_beds'] );
		$s = ( 1 == $v ) ? 'Student' : 'Students';
		$caption = $s . ' in isolation / quarantine';
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '"]', FALSE );

		$v = _uri_covid_percentage( $last_day['occupied_quarantine_beds'], $last_day['total_quarantine_beds'] );
		$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Isolation / quarantine beds occupied"]', FALSE );

		$output .= '</div>';

	} else {
		$output .= '
	Date = ' . $last_day['date'] . '<br>
	Total number of tests = ' . $last_day['tests'] . '<br>
	Total positive cases = ' . $last_day['positives'] . '<br>
	Percent Positive = ' .  number_format( $last_day['positives'] / $last_day['tests'] * 100, 2 ) . '<br>
	Number of occupied isolation / quarantine beds = ' . $last_day['occupied_quarantine_beds'] . '<br>
	Percent of occupied isolation / quarantine beds = ' . number_format( $last_day['occupied_quarantine_beds'] / $last_day['total_quarantine_beds'] * 100, 2 ) . '
	';
	}
	
	$output .= '<div class="cl-tiles halves"><div id="covid-daily-tests"></div><div id="covid-iso-quar"></div></div>';
	
	$output .= '<br><br>';

	$since = date( 'F j, Y', strtotime( $days[0]['date'] ) );
	$output .= '<h2>Cumulative testing data since ' . $since . '.</h2>';

	if ( shortcode_exists( 'cl-metric' ) ) {

		$output .= '<div class="cl-tiles halves">';

		$v = ( empty( $totals['tests'] ) ) ? 'O' : _uri_covid_number_format( $totals['tests'] );
		$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="Total Tests" style="clear"]', FALSE );

		$v = _uri_covid_percentage( $totals['positives'], $totals['tests'] );
		$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Percentage of positive cases" style="clear"]', FALSE );

		$output .= '</div>';

	} else {
		$output .= '
	Date = ' . $last_day['date'] . '<br>
	Total number of tests = ' . $last_day['tests'] . '<br>
	Total positive cases = ' . $last_day['positives'] . '<br>
	';
	}

	$output .= '<div id="covid-cumulative-chart"></div>';



	$output .= '</div>';

	return $output;

}
add_shortcode( 'uri-covid-status', 'uri_covid_shortcode' );



/**
 * Load the data from the database
 */
function uri_covid_get_days() {
	if ( FALSE === ( $days = get_transient( 'uri_covid_days' ) ) ) {
		$days = uri_covid_query_spreadsheet();
		set_transient( 'uri_covid_days', $days, HOUR_IN_SECONDS );
	}

// 	unset( $days[0] );

	return $days;
}

/**
 * Load the data from the source spreadsheet
 */
  
function uri_covid_query_spreadsheet() {
	// set up the sheet id and which sheet to use
	// productoin data
	$sheet_id = '1o3Lr_FLnngmVMx3oPGwh4XHK4B3jmiuXLGpKOsJN6mE/1';
	// test data
 	// $sheet_id = '1JXX3HNWo2ei1teygjTj1VZgPPU84PSDDgvn0wbgCC0E/1';
	
	// assemble the URL
	$data_url = 'https://spreadsheets.google.com/feeds/list/' . $sheet_id . '/public/values?alt=json';
	$request = wp_remote_get( $data_url );
	// there aren't really any great options if we hit an error, but this is how it'd work
	// 	if( is_wp_error( $request ) ) {
	// 	}
	$data = json_decode( $request['body'] );

	$days = array();

	foreach($data->{'feed'}->{'entry'} as $row) {
		$days[] = array(
			'date' => $row->{'gsx$date'}->{'$t'},
			'tests' => $row->{'gsx$tests'}->{'$t'},
			'positives' => $row->{'gsx$positives'}->{'$t'},
			'total_quarantine_beds' => $row->{'gsx$isoquarbeds'}->{'$t'},
			'occupied_quarantine_beds' => $row->{'gsx$occupiedisoquarbeds'}->{'$t'}
		);
	}
	return $days;
}

/**
 * total the rows in the dataset
 */
function uri_covid_total_days( $days ) {

	$totals = array();

	foreach ( $days as $key => $day ) {
		foreach ( $day as $k => $v ) {
			if ( is_numeric( $v ) ) {
				if( isset( $totals[$k] ) ) {
					$totals[$k] += $v;
				} else {
					$totals[$k] = $v;
				}
			}
		}
	}
	return $totals;
}


/**
 * Helper function to calculate and format percentages
 */
function _uri_covid_percentage( $x, $y, $default=0 ) {
	if( 0 == $y ) {
		return $default;
	}
	$percentage = $x / $y * 100;
	if( $percentage < 1 && $percentage > 0 ) {
		return '&lt;1';
	} else {
		return _uri_covid_number_format( $percentage );
	}
}

/**
 * Helper function to format numbers
 */
function _uri_covid_number_format( $x ) {
	return number_format( $x, 0, '.', ',' );
}


