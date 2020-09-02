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
 *                     - displays data from last date in range
 * percent-isolation   percentage of iso beds in use (start date, end date)
 *                     - displays data from last date in range
 * percent-positive    percentage of positive cases (start date, end date)
 * 
 * chart-isolation:    iso (start date, end date)
 * chart-tests:        tests administered (start date, end date)
 * chart-positives:    positive tests (start date, end date)
 * 
 * date-range:				 displays the date range either as a single date or start – end.
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
		'style' => '',
		'before' => '',
		'after' => '',
		'caption' => ''
	), $attributes, $shortcode );
	
	$start = strtotime( $attributes['start'] );
	$end = strtotime( $attributes['end'] );
	$style = $attributes['style'];
	

	$days = uri_covid_get_days( $start, $end );
	$totals = uri_covid_total_days( $days );
	$range_in_days = round( ( $end - $start ) / ( 60 * 60 * 24 ) );

	$output = $attributes['before'];
	
	switch ( $attributes['display'] ) {
		case 'date-range':
			$s = uri_covid_start_date( $days, $start );
			$e = uri_covid_end_date( $days, $end );
			if ( $start != $end ) {
				$output .= date( $attributes['display_date_format'], $s ) . ' – ' . date( $attributes['display_date_format'], $e );	
			} else {
				$output .= date( $attributes['display_date_format'], $e );	
			}
		break;
		case 'total-tests':
			$v = ( empty( $totals['tests'] ) ) ? '&#8203;0&#8203;' : _uri_covid_number_format( $totals['tests'] );
			if ( '' !== $attributes['caption'] ) {
				$caption = $attributes['caption'];
			} else {
				$caption = ( 1 == $v ) ? 'Test administered': 'Tests administered';
			}
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '" class="fitted uri-covid-status" style="' . $style . '"]', FALSE );	
			} else {
				$output .= '<p class="uri-covid-status">' . $v . ' ' . $caption . '</p>';
			}
		break;
		case 'total-positive':
			$v = ( empty( $totals['positives'] ) ) ? '&#8203;0&#8203;' : _uri_covid_number_format( $totals['positives'] );
			if ( '' !== $attributes['caption'] ) {
				$caption = $attributes['caption'];
			} else {
				$caption = ( 1 == $v ) ? 'Positive case': 'Positive cases';
			}
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '" class="fitted uri-covid-status" style="' . $style . '"]', FALSE );	
			} else {
				$output .= '<p class="uri-covid-status">' . $v . ' ' . $caption . '</p>';
			}
		break;
		case 'percent-positive':
			$v = _uri_covid_percentage( $totals['positives'], $totals['tests'] );
			if ( '' !== $attributes['caption'] ) {
				$caption = $attributes['caption'];
			} else {
				$caption = 'Positive test rate';
			}
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="' . $caption . '" class="fitted uri-covid-status" style="' . $style . '"]', FALSE );
			} else {
				$output .= '<p class="uri-covid-status">' . $v . '% positive tests</p>';
			}
		break;
		case 'total-isolation':
			// we can't add these up; we can't average them, so only show the last day
			// @todo: provide a message to the user
			$last_day = $days[count($days)-1];
			$v = ( empty( $last_day['occupied_quarantine_beds'] ) ) ? '&#8203;0&#8203;' : _uri_covid_number_format( $last_day['occupied_quarantine_beds'] );
			if ( '' !== $attributes['caption'] ) {
				$caption = $attributes['caption'];
			} else {
				$s = ( 1 == $v ) ? 'Student' : 'Students';
				$caption = $s . ' in isolation / quarantine';
			}
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '" caption="' . $caption . '" class="fitted uri-covid-status" style="' . $style . '"]', FALSE );
			} else {
				$output .= '<p class="uri-covid-status">' . $v . ' ' . $caption . '</p>';
			}
		break;
		case 'percent-isolation':
			// we can't add these up; we can't average them, so only show the last day
			// @todo: provide a message to the user
			$last_day = $days[count($days)-1];
			$v = _uri_covid_percentage( $last_day['occupied_quarantine_beds'], $last_day['total_quarantine_beds'] );
			if ( '' !== $attributes['caption'] ) {
				$caption = $attributes['caption'];
			} else {
				$caption = 'Isolation / quarantine beds occupied';
			}
			if ( shortcode_exists( 'cl-metric' ) ) {
				$output .= do_shortcode( '[cl-metric metric="' . $v . '%" caption="' . $caption . '" class="fitted uri-covid-status" style="' . $style . '"]', FALSE );
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
	
	if ( ! empty ( $output ) ) {
		uri_covid_styles();
	}
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


