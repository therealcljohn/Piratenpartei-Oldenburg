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
	$offset = (60*60*get_option('gmt_offset'));
	$events = my_calendar_grab_events(date("Y",time()+$offset).'-'.date("m",time()+$offset).'-'.date("d",time()+$offset),
                                          date("Y",time()+$offset).'-'.date("m",time()+$offset).'-'.date("d",time()+$offset));

	foreach($events as $event) {
		//if event is in the next 120 minutes
		//if last notification is more that 5 hours in the past
		if(strtotime($event->occur_begin)>=time()
                   AND strtotime($event->occur_begin)<=time()+60*120
                   AND strtotime($event->event_last_notification)<=time()-60*300) {
			//Call wptwitbox if activated
			if(!empty($GLOBALS['wpTwitBox'])) {
                                //twitter
                                my_calendar_twitter_remember_event( $event );
			}


		}
	}
}

function my_calendar_twitter_remember_event( $event ) {
        global $wpdb;

        //Call wptwitbox if activated
        if(!empty($GLOBALS['wpTwitBox'])) {
                $details_url = get_option( 'mc_uri' )."?mc_id=".$event->occur_id;

                $short_link = $GLOBALS['wpTwitBox']->get_bitly_link("$details_url");
                $status = "Nicht vergessen: gleich um ".date("H:i", strtotime($event->occur_begin))." Uhr findet ".$event->event_title." statt! $short_link";

                $GLOBALS['wpTwitBox']->exe_twitter_call(
                        'statuses/update',
                        'post',
                        array(
                                'status' => "$status"
                        )
                );

                //set last notifaction time
                $sql = "UPDATE " . my_calendar_table() . " SET event_last_notification=NOW() WHERE event_id=".$event->event_id;
                $result = $wpdb->get_results( $sql, ARRAY_A );
        }
}

?>