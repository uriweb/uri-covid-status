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

define( 'URI_COVID_PATH', plugin_dir_path( __FILE__ ) );
define( 'URI_COVID_URL', str_replace('/inc', '/', plugins_url( 'inc', __FILE__ ) ) );

// require the code to handle where to find template files
require_once URI_COVID_PATH . 'inc/shortcode.php';

// activate the admin settings screen
require_once URI_COVID_PATH . 'inc/settings.php';


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
 * Loads the CSS
 */
function uri_covid_styles() {
	wp_register_style( 'uri-covid-status', plugins_url( '/css/covid.css', __FILE__ ) );
	wp_enqueue_style( 'uri-covid-status' );
}


/**
 * Load the data from the database
 * @param start obj a date object
 * @param end obj a date object
 * @return arr
 */
function uri_covid_get_days( $start=FALSE, $end=FALSE ) {

	if ( FALSE === ( $days = get_transient( 'uri_covid_days' ) ) ) {
		$days = uri_covid_query_spreadsheet();
		set_transient( 'uri_covid_days', $days, HOUR_IN_SECONDS );
	}
	
	if ( $start || $end ) {
		// validate date range, segment the days data
		if ( $end < $start ) {
			// problem: end date is before the start date
			// for now, resolve it by removing the end date
			// @todo: display an error
			$end = FALSE;
		}
		if ( FALSE === $end ) {
			$end = strtotime('today');
		}
		if ( FALSE === $start ) {
			$start = strtotime('yesterday');
		}
		return uri_covid_slice_days( $days, $start, $end );
	}
	
	// no dates selected, return the whole array
	return $days;
}

/**
 * Get just the desired date range from the days array.
 * @param days arr the dates array
 * @param start obj a date object
 * @param end obj a date object
 * @return arr
 */
function uri_covid_slice_days( $days, $start, $end ) {
	$s = date( 'n/j/Y', $start );
	$e = date( 'n/j/Y', $end );	
//	$range = round( ( $end - $start ) / ( 60 * 60 * 24 ) );
	$total = count( $days );
	$first = $days[0];
	$last = $days[$total-1];
//	$mid = array_slice($days, floor($total/2), floor($total/2) );
	
	if ( $s < $first['date'] ) {
		// @todo: notify user if the requested date range is outside of the available data
		$s = $first['date'];
	}
	if ( $e > $last['date'] ) {
		// @todo: notify user if the requested date range is outside of the available data
		$e = $last['date'];
	}

	$start_key = array_search( $s, array_column( $days, 'date' ) );
	$end_key = array_search( $e, array_column( $days, 'date' ) );

	$slice = array_slice($days, $start_key, $end_key+1 );
		
	return $slice;
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


