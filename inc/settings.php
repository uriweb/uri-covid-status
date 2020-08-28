<?php
/**
 * Description: Create admin settings menu for the COVID Tracker.
 * Author: John Pennypacker <jpennypacker@uri.edu>
 */

function uri_covid_status_delete_transients() {
	if( 'uri_covid_status' === $_POST['option_page'] ) {
		delete_transient('uri_covid_days');
	}
}
add_action('update_option', 'uri_covid_status_delete_transients', 10, 3);


/**
 * Add the settings page to the settings menu
 * @see https://developer.wordpress.org/reference/functions/add_options_page/
 */
function uri_covid_status_create_menu() {
	add_options_page(
		__( 'COVID Tracker', 'uri' ),
		__( 'COVID Tracker', 'uri' ),
		'manage_options',
		'uri-covid-status-settings',
		'uri_covid_status_settings_page'
	);
}
add_action( 'admin_menu', 'uri_covid_status_create_menu' );



function uri_covid_status_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		echo '<div id="setting-message-denied" class="updated settings-error notice is-dismissible"> 
<p><strong>You do not have permission to use this form.</strong></p>
<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		return;
	}

	if( ! empty ( $_POST ) ) {
		uri_covid_status_delete_transients();
		echo '<div id="setting-message" class="updated settings-message notice is-dismissible"> 
<p><strong>Data refreshed.</strong></p>
<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}


?>
<div class="wrap">
<h1>URI COVID Status</h1>

<form method="post" action="">
    <?php
    	settings_fields( 'uri_covid_status' );
    ?>

    <p>Click the button below to refresh the COVID Tracker data from the spreadsheet.</p>
    
    <?php submit_button( 'Refresh Data' ); ?>

</form>
</div>
<?php } ?>