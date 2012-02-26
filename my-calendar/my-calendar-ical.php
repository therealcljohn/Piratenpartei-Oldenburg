<?php
function my_calendar_ical() {

$m = ( isset($_GET['m']) )?$_GET['m']:date('n');
$y = ( isset($_GET['y']) )?$_GET['y']:date('Y');

global $mc_version;
// establish template
	$template = "\nBEGIN:VEVENT
UID:{dateid}-{id}
LOCATION:{ical_location}
SUMMARY:{title}
DTSTAMP:{ical_start}
ORGANIZER;CN={host}:MAILTO:{host_email}
DTSTART:{ical_start}
DTEND:{ical_end}
URL;VALUE=URI:{link}
DESCRIPTION;ENCODING=QUOTED-PRINTABLE:{ical_desc}
END:VEVENT";
// add ICAL headers
$output = 'BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:-//Accessible Web Design//My Calendar//http://www.mywpcal.com//v'.$mc_version.'//EN';
	
	$d = date( 't',mktime( 0,0,0,$m,1,$y ) );
	for ( $i=1;$i<=$d;$i++ ) {
		$events = my_calendar_grab_events( $y,$m,$i );

		if ( is_array($events) && !empty($events) ) {
			foreach ($events as $event) {
				if ( is_object($event) ) {
					$array = event_as_array($event);
					$output .= jd_draw_template($array,$template,'ical');
				}
			}
		} else {
				//$array = event_as_array($events);
				//$output .= jd_draw_template($array,$template,'ical');		
		}
	}	
$output .= "\nEND:VCALENDAR";
$output = preg_replace("~(?<!\r)\n~","\r\n",$output);
	header("Content-Type: text/calendar");
	header("Pragma: no-cache");
	header("Expires: 0");		
	header("Content-Disposition: inline; filename=my-calendar.ics");
	echo $output;
}
?>