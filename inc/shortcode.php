<?php
/**
 * @author: John Pennypacker <jpennypacker@uri.edu>
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');



/**
 * Shortcode callback.
 * 
 * Display options:
 * 
 * total-tests         total tests administered (start date, end date)
 * total-positive      total positive cases (start date, end date)
 * total-isolation     total students in iso (start date, end date)
 * percent-isolation   percentage of iso beds in use (start date, end date)
 * percent-positive    percentage of positive cases (start date, end date)
 * 
 * chart-isolation:    iso (start date, end date)
 * chart-tests:        tests administered (start date, end date)
 * chart-positives:    positive tests (start date, end date)
 * 
 * headline date range / since / for
 *  
 */
function uri_covid_shortcode($attributes, $content, $shortcode) {

	// normalize attribute keys, lowercase
	$attributes = array_change_key_case( (array)$attributes, CASE_LOWER );

	// default attributes
	$attributes = shortcode_atts( array(
		'display' => 'total-tests',
		'start' => 'Jan 1, 2020',
		'end' => 'today',
		'display_date_format' => 'F j',
		'before' => '',
		'after' => '',
	), $attributes, $shortcode );
	
	$start = strtotime( $attributes['start'] );
	$end = strtotime( $attributes['end'] );
	
	uri_covid_styles();

	
	$days = uri_covid_get_days( $start, $end );
	$totals = uri_covid_total_days( $days );

	$output = $attributes['before'];
	
	switch ( $attributes['display'] ) {
		case 'headline':
			$output .= '<h2>Coronavirus data for ' . 
			date( $attributes['display_date_format'], $start ) . ' – ' . 
			date( $attributes['display_date_format'], $end ) . '</h2>';	
		break;
		case 'total-tests':
			$v = ( empty( $totals['tests'] ) ) ? 'O' : _uri_covid_number_format( $totals['tests'] );
			$caption = ( 1 == $v ) ? 'Test Administered': 'Tests Administered';
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '" class="fitted uri-covid-status"]', FALSE );	
			} else {
				$output .= '<p class="uri-covid-status">' . $v . ' ' . $caption . '</p>';
			}
		break;
		case 'total-positive':
			$v = ( empty( $totals['positives'] ) ) ? 'O' : _uri_covid_number_format( $totals['positives'] );
			$caption = ( 1 == $v ) ? 'Positive Case': 'Positive Cases';
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '" class="fitted uri-covid-status"]', FALSE );	
			} else {
				$output .= '<p class="uri-covid-status">' . $v . ' ' . $caption . '</p>';
			}
		break;
		case 'percent-positive':
			$v = _uri_covid_percentage( $totals['positives'], $totals['tests'] );
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Percentage of positive tests" class="fitted uri-covid-status"]', FALSE );
			} else {
				$output .= '<p class="uri-covid-status">' . $v . '% positive tests</p>';
			}
		break;
		case 'total-isolation':
			$v = ( empty( $totals['occupied_quarantine_beds'] ) ) ? 'O' : _uri_covid_number_format( $totals['occupied_quarantine_beds'] );
			$s = ( 1 == $v ) ? 'Student' : 'Students';
			$caption = $s . ' in isolation / quarantine';
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '" class="fitted uri-covid-status"]', FALSE );
			} else {
				$output .= '<p class="uri-covid-status">' . $v . ' ' . $caption . '</p>';
			}
		break;
		case 'percent-isolation':
			$v = _uri_covid_percentage( $totals['occupied_quarantine_beds'], $totals['total_quarantine_beds'] );
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="Isolation / quarantine beds occupied" class="fitted uri-covid-status"]', FALSE );
			} else {
				$output .= '<p class="uri-covid-status">' . $v . '% of isolation / quarantine beds occupied</p>';
			}
		break;
		case 'chart-isolation':
			uri_covid_scripts();
			$output .= '<div id="covid-iso-quar" class="chart uri-covid-status"></div>';
		break;
		case 'chart-tests':
			uri_covid_scripts();
			$output .= '<div id="covid-daily-tests" class="chart uri-covid-status"></div>';
		break;
		case 'chart-positivies':
			uri_covid_scripts();
			$output .= '<div id="covid-cumulative-chart" class="chart uri-covid-status"></div>';
		break;
	}
	
	$output .= $attributes['after'];
	return $output;

}
add_shortcode( 'uri-covid-status', 'uri_covid_shortcode' );



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


