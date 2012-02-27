<?php

//Load wptwitbox plugin
$wptwitbox_plugin_path = WP_CONTENT_DIR."/plugins/wptwitbox/wptwitbox.php";
if(file_exists($wptwitbox_plugin_path)) {
	require_once($wptwitbox_plugin_path);
}

function my_calendar_cronjobs() {
	if (isset($_GET['my_calendar_do_cronjobs'])) {
		twitterRememberNotification();
		die;
	}
}

function twitterRememberNotification() {
	global $wpdb, $default_template;
	$output = '';
	$offset = (60*60*get_option('gmt_offset'));

	// This function cannot be called unless calendar is up to date
	check_my_calendar();
	$defaults = get_option('mc_widget_defaults');
	$template = ($template == 'default')?$defaults['today']['template']:$template;
	if ($template == '' ) { $template = "$default_template"; };	
	$category = ($category == 'default')?$defaults['today']['category']:$category;
	$no_event_text = ($substitute == '')?$defaults['today']['text']:$substitute;

	$events = my_calendar_grab_events(date("Y",time()+$offset),date("m",time()+$offset),date("d",time()+$offset),$category);
	foreach($events as $event) {
		$event_as_array = event_as_array( $event );

		//if event is in the next 120 minutes
		//if last notification is more that 5 hours in the past
		if(strtotime($event_as_array['dtstart'])<=time()+60*120 AND strtotime($event_as_array['event_last_notification'])<=time()-60*300) {
			//Call wptwitbox if activated
			if(!empty($GLOBALS['wpTwitBox'])) {
				//Build own details link, because bitly wont build a short url from guid in $event_as_array. Parse guid through urlencode to see why!
				$id_start 			= date('Y-m-d',strtotime($event_as_array['dtstart']));
				$mcid 				= 'mc_'.$id_start.'_'.$event_as_array['id'];
				$details_url = get_option( 'mc_uri' )."?mc_id=$mcid";

				$short_link = $GLOBALS['wpTwitBox']->get_bitly_link("$details_url");

				$status = "Nicht vergessen: gleich um $event_as_array[time] Uhr findet $event_as_array[title] statt! $short_link";
				$GLOBALS['wpTwitBox']->exe_twitter_call(
					'statuses/update',
					'post',
					array(
						'status' => "$status"
					)
				);

				//set last notifaction time
				$sql = "UPDATE " . my_calendar_table() . " SET event_last_notification=NOW() WHERE event_id=$event_as_array[id]";
				$result = $wpdb->get_results( $sql, ARRAY_A );
			}


		}
	}
}


?>