<?php
// Used to draw multiple events
function my_calendar_draw_events($events, $type, $process_date) {
  if ( $type == 'mini' && ( get_option('mc_open_day_uri') == 'true' || get_option('mc_open_day_uri') == 'listanchor' || get_option('mc_open_day_uri') == 'calendaranchor' ) ) return;
  // We need to sort arrays of objects by time
  if ( is_array($events) ) {
 usort($events, "my_calendar_time_cmp");


 $temp_array = array();
 $output_array = array();
 $output = '';
	if ($type == "mini" && count($events) > 0) {

	$output .= "<div id='date-$process_date' class='calendar-events'>";
	if ( get_option('mc_draggable') == '1' ) { 
	$output .= "
<script type='text/javascript'>
jQuery(document).ready(function($) {
	$('#date-$process_date').easydrag();
});
</script>";
	}
	}
	foreach(array_keys($events) as $key) { 
		$event =& $events[$key];
		$temp_array[] = $event;
	}
	
	// By default, skip no events.
	$skipping = false;
	foreach(array_keys($temp_array) as $key) {
		$event =& $temp_array[$key];
		// if any event this date is in the holiday category, we are skipping
		if ( $event->event_category == get_option('mc_skip_holidays_category') ) {
			$skipping = true;
			break;
		}
	}
	// check each event, if we're skipping, only include the holiday events.
	foreach(array_keys($temp_array) as $key) {
		$event =& $temp_array[$key];	
		if ($skipping == true) {
			if ($event->event_category == get_option('mc_skip_holidays_category') ) {
				$output_array[] = my_calendar_draw_event($event, $type, $process_date);
			} else {
				if ( $event->event_holiday == '0' ) { // '1' means "is canceled"
					$output_array[] = my_calendar_draw_event($event, $type, $process_date);
				}
			}
		} else {
			$output_array[] = my_calendar_draw_event($event, $type, $process_date);
		}
	}
	if ( is_array($output_array) ) {
		foreach (array_keys($output_array) as $key) {
			$value =& $output_array[$key];	
			$output .= $value;
		}
	}

	if ($type == "mini" && count($events) > 0) { $output .= "</div>"; }	

	return $output;
	}
}
// Used to draw an event to the screen
function my_calendar_draw_event($event, $type="calendar", $process_date) {
	global $wpdb,$wp_plugin_url;
	// My Calendar must be updated to run this function
	check_my_calendar();	
	
	$templates = get_option('mc_templates');
	$header_details = '';
	$body_details = '';
	$address = '';
	$output = '';
	$date_format = ( get_option('mc_date_format') != '' )?get_option('mc_date_format'):get_option('date_format');

	$data = event_as_array($event);	
	$details = false;
	switch ($type) {
		case 'mini':
			$template = $templates['mini'];
			if ( get_option('mc_use_mini_template')==1 ) {
				$details = jd_draw_template( $data, $template );
			}		
		break;
		case 'list':
			$template = $templates['list'];
			if ( get_option('mc_use_list_template')==1 ) {
				$details = jd_draw_template( $data, $template );
			}		
		break;
		case 'single':
			$template = $templates['details'];
			if ( get_option('mc_use_details_template')==1 ) {
				$details = jd_draw_template( $data, $template );
			}		
		break;
		case 'calendar':
		default:
			$template = $templates['grid'];
			if ( get_option('mc_use_grid_template')==1 ) {
				$details = jd_draw_template( $data, $template );
			}
		break;
		
	}
						 
	$mc_display_author 	= get_option('mc_display_author');
	$display_map 		= get_option('mc_show_map');
	$display_address 	= get_option('mc_show_address');
	$display_details 	= get_option('mc_details');
	$id_start 			= date('Y-m-d',strtotime($event->event_begin));
	$id_end 			= date('Y-m-d',strtotime($event->event_end));
	$uid 				= 'mc_'.$id_start.'_'.$event->event_id;	
	$this_category 		= $event->event_category; 
    // get user-specific data
	$tz = mc_user_timezone();
	$category = "mc_".sanitize_title( $event->category_name );
	if ( get_option('mc_hide_icons')=='true' ) {
		$image = "";
	} else {
	    if ($event->category_icon != "") {
			$path = (is_custom_icon())?$wp_plugin_url.'/my-calendar-custom/':plugins_url('icons',__FILE__).'/';
			$hex = (strpos($event->category_color,'#') !== 0)?'#':'';
			$image = '<img src="'.$path.$event->category_icon.'" alt="" class="category-icon" style="background:'.$hex.$event->category_color.';" />';
		} else {
			$image = "";
		}
	}
	if ( $type == 'calendar' ) {
		if ( get_option('mc_draggable') == '1' ) { 
	$header_details .= "
<script type='text/javascript'>
jQuery(document).ready(function($) {
	$('#$uid-$type-details').easydrag();
});
</script>";
		}
	}
// move this div start for custom.
    $header_details .=  "<div id='$uid-$type' class='$type-event $category vevent'>\n";
	$templates = get_option('mc_templates');
	$title_template = ($templates['title'] == '' )?'{title}':$templates['title'];
	$mytitle = jd_draw_template($data,$title_template);
	$toggle = ($type == 'calendar')?"&nbsp;<a href='#' class='mc-toggle'><img src='".MY_CALENDAR_DIRECTORY."/images/event-details.png' alt='".__('Event Details','my-calendar')."' /></a>":'';
	$toggle =  (get_option('mc_open_uri')=='true')?'':$toggle;
	$current_date = date_i18n($date_format,strtotime($process_date));
	$event_date = ($type == 'single')?$current_date.', ':'';
	if ( $event->event_span == 1 ) { $group_class = ' multidate group'.$event->event_group_id; } else { $group_class = ''; }
	$header_details .= ($type != 'list' && $type != 'single')?"<h3 class='event-title summary$group_class'>$image".$mytitle."$toggle</h3>\n":'';
	$title = apply_filters( 'mc_before_event_title','',$event );
	$title .= ($type == 'single' )?"<h2 class='event-title summary'>$image $mytitle</h2>\n":'';
	$title .= apply_filters( 'mc_after_event_title','',$event );
	$header_details .= apply_filters( 'mc_event_title',$title,$event );
	
	// need to figure out what this is. I don't remember it, and it isn't being used...
	//$closure = ( $details === false )?'</div>':"";
	$dateid = date('Y-m-d',$event->event_start_ts);		
	
	if ( $details === false ) {
		// put together address information as vcard
		if (($display_address == 'true' || $display_map == 'true') ) {
			$address .= mc_hcard( $event, $display_address, $display_map );
		}
		// end vcard
		$body_details .= "	<div id='$uid-$type-details' class='details'>\n"; 
		$body_details .= apply_filters('mc_before_event','',$event);
		$body_details .= ($type == 'calendar' || $type == 'mini' )?"<span class='close'><a href='#' class='mc-toggle mc-close'><img src='".MY_CALENDAR_DIRECTORY."/images/event-close.png' alt='".__('Close','my-calendar')."' /></a></span>":'';
		$body_details .= "<div class='time-block'>";
			if ( $event->event_time != "00:00:00" && $event->event_time != '' ) {
				$body_details .= "\n	<span class='event-time dtstart' title='".$id_start.'T'.$event->event_time."'>$event_date".date_i18n(get_option('mc_time_format'), strtotime($event->event_time));
				if ($event->event_endtime != "00:00:00" && $event->event_endtime != '' ) {
					$body_details .= "<span class='time-separator'> &ndash; </span><span class='end-time dtend' title='".$id_end.'T'.$event->event_endtime."'>".date_i18n(get_option('mc_time_format'), strtotime($event->event_endtime))."</span>";
				}
				if ($tz != '') {
					$local_begin = date_i18n( get_option('mc_time_format'), strtotime($event->event_time ."+$tz hours") );
					$body_details .= "<hr /><small class='local-time'>". sprintf(__('(%s in your time zone)','my-calendar'),$local_begin)."</small>";
				}
				$body_details .= "</span>\n";				
			} else {
				$body_details .= "<span class='event-time'>";
					if ( get_option('mc_notime_text') == '' || get_option('mc_notime_text') == "N/A" ) { 
					$body_details .= "<abbr title='".__('Not Applicable','my-calendar')."'>".__('N/A','my-calendar')."</abbr>\n"; 
					} else {
					$body_details .= get_option('mc_notime_text');
					}
				$body_details .= "</span>";
			}
			$body_details .= "
			</div>
			<div class='sub-details'>";
			if ($type == "list") {
				$body_details .= "<h3 class='event-title summary'>$image".$mytitle."</h3>\n";
			}
			if ($mc_display_author == 'true') {
				$e = get_userdata($event->event_author);
				$body_details .= '<span class="event-author">'.__('Posted by', 'my-calendar').': <span class="author-name">'.$e->display_name."</span></span><br />\n";
			}	
		if (($display_address == 'true' || $display_map == 'true') ) {
			$body_details .= $address;
		}
		if ($display_details == 'true' && !isset($_GET['mc_id']) ) {
			$id = $event->event_id;
			$details_template = ( !empty($templates['label']) )? stripcslashes($templates['label']):__('Details about','my-calendar').' {title}';
			$tags = array( "{title}","{location}","{color}","{icon}","{date}","{time}" );
			$current_time = date_i18n(get_option('mc_time_format'), strtotime($event->event_time));			
			$replacements = array( stripslashes($event->event_title), stripslashes($event->event_label), $event->category_color, $event->category_icon, $current_date, $current_time );
			$details_label = str_replace($tags,$replacements,$details_template );	
			$details_link = mc_build_url( array('mc_id'=>$uid), array('month','dy','yr','ltype','loc','mcat'), get_option( 'mc_uri' ) );
			$body_details .= ( get_option( 'mc_uri' ) != '' )?"<p class='mc_details'><a href='$details_link'>$details_label</a></p>\n":'';
		}
	  // handle link expiration
		if ( $event->event_link_expires == 0 ) {
			$event_link = esc_url($event->event_link);
		} else {
			if ( my_calendar_date_comp( $event->event_end,date_i18n('Y-m-d',time()+$offset ) ) ) {
				$event_link = '';
			} else {
				$event_link = esc_url($event->event_link);
			}
		}

		if ( function_exists('my_calendar_generate_vcal') && get_option('mc_show_event_vcal') == 'true' ) {
			$nonce = wp_create_nonce('my-calendar-nonce');
			$vcal_link = "<p class='ical'><a rel='nofollow' href='".home_url()."?vcal=$uid"."'>".__('iCal','my-calendar')."</a></p>\n";
			$body_details .= $vcal_link;
		}

		$event_image = ($event->event_image!='')?"<img src='$event->event_image' alt='' class='mc-image' />":'';
		$short = '';
		if ( get_option('mc_short') == 'true' && $type != 'single' ) {
			$short = "<div class='shortdesc'>$event_image".wpautop(stripcslashes($event->event_short),1)."</div>";	
		}
		if ( get_option('mc_desc') == 'true' || $type == 'single' ) {
			$description = "
			<div class='longdesc'>$event_image".wpautop(stripcslashes($event->event_desc),1)."</div>";
		} else {
			$description = '';
		}
		if ( get_option('mc_event_registration') == 'true' ) {
			switch ($event->event_open) {
				case '0':$status = get_option('mc_event_closed');break;
				case '1':$status = get_option('mc_event_open');break;
				case '2':$status = '';break;
				default:$status = '';
			}
		} else {
			$status = '';
		}
		// if the event is a member of a group of events, but not the first, note that.
		if ($event->event_group == 1 ) {
			$info = array();
			$info[] = $event->event_id;
			update_option( 'mc_event_groups' , $info );
		}
		if ( is_array( get_option( 'mc_event_groups' ) ) ) {
			if ( in_array ( $event->event_id , get_option( 'mc_event_groups') ) ) {
				if ( $process_date != $event->event_original_begin ) {
					$status = __("This class is part of a series. You must register for the first event in this series to attend.",'my-calendar');
				}
			}
		}
		$status = ($status != '')?"<p>$status</p>":'';
		$return = ($type == 'single')?"<p><a href='".get_option('mc_uri')."'>".__('View full calendar','my-calendar')."</a></p>":'';
		// if we're opening in a new page, there's no reason to display any of that. Later, re-write this section to make this easier to skip.
		if ( $type == 'calendar' && get_option('mc_open_uri') == 'true' && $time != 'day' ) $body_details = $description = $short = $status = '';

		if ($event_link != '') {
			$is_external = mc_external_link( $event_link );	
			$link_template = ( isset($templates['link']))?$templates['link']:'{title}';
			$link_text = jd_draw_template($data,$link_template);
			$details = "\n". $header_details . $body_details . $description . $short . $status."<p><a href='$event_link' $is_external>".$link_text.'</a></p>'.$return;
		} else {
			$details = "\n". $header_details . $body_details . $description . $short . $status . $return;	
		}
		$details .= "
			</div><!--ends .sub-details-->\n";
	} else {
		$toggle = ($type == 'calendar' || $type == 'mini' )?"<a href='#' class='mc-toggle mc-close close'><img src='".MY_CALENDAR_DIRECTORY."/images/event-close.png' alt='".__('Close','my-calendar')."' /></a>":'';	
		$details = $header_details."\n<div id='$uid-$type-details' class='details'>\n	".$toggle.$details."\n";
	}
	// create edit links
		if ( mc_can_edit_event( $event->event_author ) ) {
			$groupedit = ( $event->event_group_id != 0 )?"<li><a href='".admin_url("admin.php?page=my-calendar-groups&amp;mode=edit&amp;event_id=$event->event_id&amp;group_id=$event->event_group_id")."' class='group'>".__('Edit Group','my-calendar')."</a></li>\n":'';	
			if ( $event->event_recur == 'S' ) {
				$edit = "
				<div class='mc_edit_links'>
				<ul>
				<li><a href='".admin_url("admin.php?page=my-calendar&amp;mode=edit&amp;event_id=$event->event_id")."' class='edit'>".__('Edit','my-calendar')."</a></li>
				<li><a href='".admin_url("admin.php?page=my-calendar&amp;mode=delete&amp;event_id=$event->event_id")."' class='delete'>".__('Delete','my-calendar')."</a></li>
				$groupedit
				</ul>
				</div>";
			} else {
				$edit = "<div class='mc_edit_links'>
				<ul>
				<li><a href='".admin_url("admin.php?page=my-calendar&amp;mode=edit&amp;event_id=$event->event_id&amp;date=$dateid")."' class='edit'>".__('Edit This Date','my-calendar')."</a></li>
				<li><a href='".admin_url("admin.php?page=my-calendar&amp;mode=edit&amp;event_id=$event->event_id")."' class='edit'>".__('Edit All','my-calendar')."</a></li>
				<li><a href='".admin_url("admin.php?page=my-calendar&amp;mode=delete&amp;event_id=$event->event_id&amp;date=$dateid")."' class='delete'>".__('Delete This Date','my-calendar')."</a></li>
				<li><a href='".admin_url("admin.php?page=my-calendar&amp;mode=delete&amp;event_id=$event->event_id")."' class='delete'>".__('Delete All','my-calendar')."</a></li>
				$groupedit
				</ul>
				</div>";	
			}
		} else {
			$edit = ''; 
		}
	if ( $type == 'calendar' && get_option('mc_open_uri') == 'true' && $time != 'day' ) { $edit = ''; }
		
	$details .= $edit;
	$details .= apply_filters('mc_after_event','',$event);
	$details .= "</div><!--ends .details--></div>";
	$details = apply_filters('mc_event_content',$details,$event);
	
	switch($type) {
		case 'calendar':$details = apply_filters('mc_event_content_calendar',$details,$event);
		break;
		case 'mini':$details = apply_filters('mc_event_content_mini',$details,$event);
		break;
		case 'grid':$details = apply_filters('mc_event_content_grid',$details,$event);
		break;
		case 'single':$details = apply_filters('mc_event_content_single',$details,$event);
		break;
	}
	
	if ( get_option( 'mc_event_approve' ) == 'true' ) {
		if ( $event->event_approved == 1 ) {	
		  return $details;
		}
	} else {
		return $details;
	}
}

function mc_build_date_switcher( $type='calendar', $cid='all' ) {
global $wpdb;
$current_url = mc_get_current_url();
	$date_switcher = "";
	$date_switcher .= '<div class="my-calendar-date-switcher">
            <form action="'.$current_url.'" method="get"><div>';
	$qsa = array();
	parse_str($_SERVER['QUERY_STRING'],$qsa);
	if ( !isset( $_GET['cid'] ) ) { $date_switcher .= '<input type="hidden" name="cid" value="'.$cid.'" />'; }	
	foreach ($qsa as $name => $argument) {
		$name = esc_attr(strip_tags($name));
		$argument = esc_attr(strip_tags($argument));
	    if ($name != 'month' && $name != 'yr' && $name != 'dy' ) {
			$date_switcher .= '<input type="hidden" name="'.$name.'" value="'.$argument.'" />';
	    }
	  }
	// We build the months in the switcher
	$date_switcher .= '
            <label for="mc-'.$type.'-month">'.__('Month','my-calendar').':</label> <select id="mc-'.$type.'-month" name="month">'."\n";
			for ($i=1;$i<=12;$i++) {
				$date_switcher .= "<option value='$i'".mc_month_comparison($i).'>'.date_i18n('F',mktime(0,0,0,$i,1)).'</option>'."\n";
			}
			$date_switcher .= '</select>'."\n".'
            <label for="mc-'.$type.'-year">'.__('Year','my-calendar').':</label> <select id="mc-'.$type.'-year" name="yr">'."\n";
			// query to identify oldest start date in the database
	$query = "SELECT event_begin FROM ".MY_CALENDAR_TABLE." WHERE event_approved = 1 AND event_flagged <> 1 ORDER BY event_begin ASC LIMIT 0 , 1";
	$year1 = date('Y',strtotime( $wpdb->get_var( $query ) ) );
	$diff1 = date('Y') - $year1;
	$past = $diff1;
	$future = 5;
	$fut = 1;
	$f = '';
	$p = '';
	$offset = (60*60*get_option('gmt_offset'));
		while ($past > 0) {
		    $p .= '<option value="';
		    $p .= date("Y",time()+($offset))-$past;
		    $p .= '"'.mc_year_comparison(date("Y",time()+($offset))-$past).'>';
		    $p .= date("Y",time()+($offset))-$past."</option>\n";
		    $past = $past - 1;
		}
		while ($fut < $future) {
		    $f .= '<option value="';
		    $f .= date("Y",time()+($offset))+$fut;
		    $f .= '"'.mc_year_comparison(date("Y",time()+($offset))+$fut).'>';
		    $f .= date("Y",time()+($offset))+$fut."</option>\n";
		    $fut = $fut + 1;
		} 
	$date_switcher .= $p;
	$date_switcher .= '<option value="'.date("Y",time()+($offset)).'"'.mc_year_comparison(date("Y",time()+($offset))).'>'.date("Y",time()+($offset))."</option>\n";
	$date_switcher .= $f;
    $date_switcher .= '</select> <input type="submit" class="button" value="'.__('Go','my-calendar').'" /></div>
	</form></div>';
	$date_switcher = apply_filters('mc_jumpbox',$date_switcher);
	return $date_switcher;
}

function my_calendar_print() {
global $wp_plugin_url;
$category=(isset($_GET['mcat']))?$_GET['mcat']:''; // these are all sanitized elsewhere
$time=(isset($_GET['time']))?$_GET['time']:'month';
$ltype=(isset($_GET['ltype']))?$_GET['ltype']:'';
$lvalue=(isset($_GET['lvalue']))?$_GET['lvalue']:'';
header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));
echo '<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" dir="'.get_bloginfo('text_direction').'" lang="'.get_bloginfo('language').'">
<![endif]-->
<!--[if IE 7]>
<html id="ie7" dir="'.get_bloginfo('text_direction').'" lang="'.get_bloginfo('language').'">
<![endif]-->
<!--[if IE 8]>
<html id="ie8" dir="'.get_bloginfo('text_direction').'" lang="'.get_bloginfo('language').'">
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html dir="'.get_bloginfo('text_direction').'" lang="'.get_bloginfo('language').'">
<!--<![endif]-->
<head>
<meta charset="'.get_bloginfo('charset').'" />
<meta name="viewport" content="width=device-width" />
<title>'.get_bloginfo('name').' - '.__('Calendar: Print View','my-calendar').'</title>
<meta name="generator" content="My Calendar for WordPress" />
<meta name="robots" content="noindex,nofollow" />';
if ( file_exists( get_stylesheet_directory() . '/mc-print.css' ) ) {
	$stylesheet = get_stylesheet_directory_uri() . '/mc-print.css';
} else {
	$stylesheet = $wp_plugin_url."/my-calendar/mc-print.css";
}
echo "
<!-- Copy mc-print.css to your theme directory if you wish to replace the default print styles -->
<link rel='stylesheet' href='$stylesheet' type='text/css' media='screen,print' />
</head>
<body>\n";
echo my_calendar('print','calendar',$category,'no','no','no',$time,$ltype,$lvalue);
$return_url = ( get_option('mc_uri') != '' )?get_option('mc_uri'):home_url();
echo "<p class='return'><a href='$return_url'>".__('Return to site','my-calendar')."</a></p>";
echo '
</body>
</html>';
}

// Actually do the printing of the calendar
function my_calendar($name,$format,$category,$showkey,$shownav,$toggle,$time='month',$ltype='',$lvalue='') {
    global $wpdb, $wp_plugin_url;
	$my_calendar_body = '';
	$args = array('name'=>$name,'format'=>$format,'category'=>$category,'showkey'=>$showkey,'shownav'=>$shownav,'toggle'=>$toggle,'time'=>$time,'ltype'=>$ltype,'lvalue'=>$lvalue);
	$my_calendar_body .= apply_filters('mc_before_calendar','',$args);
	$main_class = ( $name !='' )?sanitize_title($name):'all';
	$cid = ( isset( $_GET['cid'] ) )?esc_attr(strip_tags($_GET['cid'])):'all';
	$format = ( mc_is_mobile() )?'list':$format;
	$date_format = ( get_option('mc_date_format') != '' )?get_option('mc_date_format'):get_option('date_format');
	
	if ( $format != 'mini' && $toggle == 'yes' ) {
		$format_toggle = "<div class='mc-format'>";
		$current_url = mc_get_current_url();
		switch ($format) {
			case 'list':
				$url = mc_build_url( array('format'=>'calendar'), array() );		
				$format_toggle .= "<a href='$url'>".__('View as Grid','my-calendar')."</a>";			
			break;
			default:
				$url = mc_build_url( array('format'=>'list'), array() );	
				$format_toggle .= "<a href='$url'>".__('View as List','my-calendar')."</a>";
			break;
		}
		$format_toggle .= "</div>";
	} else {
		$format_toggle = '';
	}
	
	if ( isset( $_GET['mc_id'] ) && $format != 'mini' ) {
		$mc_id = explode("_",$_GET['mc_id']);
		$id = (int) $mc_id[2];
		$date = $mc_id[1];
		$my_calendar_body .= my_calendar_get_event( $date, $id );
	} else {
	if ($category == "") {
		$category=null;
	}
    // First things first, make sure calendar is up to date
    check_my_calendar();
    // Deal with the week not starting on a monday
	$name_days = array(
		__('<abbr title="Sunday">Sun</abbr>','my-calendar'),
		__('<abbr title="Monday">Mon</abbr>','my-calendar'),
		__('<abbr title="Tuesday">Tues</abbr>','my-calendar'),
		__('<abbr title="Wednesday">Wed</abbr>','my-calendar'),
		__('<abbr title="Thursday">Thur</abbr>','my-calendar'),
		__('<abbr title="Friday">Fri</abbr>','my-calendar'),
		__('<abbr title="Saturday">Sat</abbr>','my-calendar')
		);
	$abbrevs = array( 'sun','mon','tues','wed','thur','fri','sat' );
	if ($format == "mini") {
		$name_days = array(
		__('<abbr title="Sunday">S</abbr>','my-calendar'),
		__('<abbr title="Monday">M</abbr>','my-calendar'),
		__('<abbr title="Tuesday">T</abbr>','my-calendar'),
		__('<abbr title="Wednesday">W</abbr>','my-calendar'),
		__('<abbr title="Thursday">T</abbr>','my-calendar'),
		__('<abbr title="Friday">F</abbr>','my-calendar'),
		__('<abbr title="Saturday">S</abbr>','my-calendar')
		);
	}
	$start_of_week = get_option('start_of_week');
	$start_of_week = ( get_option('mc_show_weekends') == 'true' )?$start_of_week:1;
	$start_of_week = ( $start_of_week==1||$start_of_week==0)?$start_of_week:0;
	if ( $start_of_week == '1' ) {
   			$first = array_shift($name_days);
			$afirst = array_shift($abbrevs);
			$name_days[] = $first;	
			$abbrevs[] = $afirst;
	}
     // Carry on with the script
	$offset = (60*60*get_option('gmt_offset'));
    // If we don't pass arguments we want a calendar that is relevant to today
	$c_m = 0;
	if ( isset($_GET['dy']) && $main_class == $cid ) {
		$c_day = (int) $_GET['dy'];
	} else {
		if ($time == 'week' ) {
			$dm = first_day_of_week();
			$c_day = $dm[0];
			$c_m = $dm[1];
		} else if ( $time == 'day' ) {
			$c_day = date("d",time()+($offset));
		} else {
			$c_day = 1;
		}
	}	
	if ( isset($_GET['month']) && $main_class == $cid  ) {
		$c_month = (int) $_GET['month'];
		if ( !isset($_GET['dy']) ) { $c_day = 1; }
	} else {
		$xnow = date('Y-m-d',time()+($offset));
		$c_month = ($c_m == 0)?date("m",time()+($offset)):date("m",strtotime( $xnow.' -1 month'));
	}

	if ( isset($_GET['yr']) && $main_class == $cid ) {
		$c_year = (int) $_GET['yr'];
	} else {
		$c_year = date("Y",time()+($offset));			
	}
    // Years get funny if we exceed 3000, so we use this check
    if ( !($c_year <= 3000 && $c_year >= 0)) {
		// No valid year causes the calendar to default to today
        $c_year = date("Y",time()+($offset));
        $c_month = date("m",time()+($offset));
        $c_day = date("d",time()+($offset));
    }
		$mc_print_url = mc_build_url( array( 'time'=>$time,'ltype'=>$ltype,'lvalue'=>$lvalue,'mcat'=>$category,'yr'=>$c_year,'month'=>$c_month,'dy'=>$c_day, 'cid'=>'print' ), array(), mc_feed_base() . 'my-calendar-print' );
		
	$anchor = (get_option('ajax_javascript') == '1' )?'#jd-calendar':'';	
	if ($shownav == 'yes') {
		$pLink = my_calendar_prev_link($c_year,$c_month,$c_day,$format,$time);
		$nLink = my_calendar_next_link($c_year,$c_month,$c_day,$format,$time);	
		$prevLink = mc_build_url( array( 'yr'=>$pLink['yr'],'month'=>$pLink['month'],'dy'=>$pLink['day'],'cid'=>$main_class ),array() );
		$nextLink = mc_build_url( array( 'yr'=>$nLink['yr'],'month'=>$nLink['month'],'dy'=>$nLink['day'],'cid'=>$main_class ),array() );
		$previous_link = apply_filters('mc_previous_link','		<li class="my-calendar-prev"><a class="prevMonth" href="' . $prevLink . $anchor .'" rel="nofollow">'.$pLink['label'].'</a></li>',$pLink);
		$next_link = apply_filters('mc_next_link','		<li class="my-calendar-next"><a class="nextMonth" href="' . $nextLink . $anchor .'" rel="nofollow">'.$nLink['label'].'</a></li>',$nLink);
		$mc_nav = '
<div class="my-calendar-nav">
	<ul>
		'.$previous_link.'
		'.$next_link.'
	</ul>
</div>';
	} else {
		$mc_nav = '';
	}
	$my_calendar_body .= "<div id=\"jd-calendar\" class=\"$format $time $main_class\">";
	if ( get_option( 'mc_show_print' ) == 'true' ) { $my_calendar_body .= "<p class='mc-print'><a href='$mc_print_url'>".__('Print View','my-calendar')."</a></p>"; }
	if ( $time == 'day' ) {
		$dayclass = strtolower(date_i18n('D',mktime (0,0,0,$c_month,$c_day,$c_year)));	
		$grabbed_events = my_calendar_grab_events($c_year,$c_month,$c_day,$category,$ltype,$lvalue);
		$events_class = '';
		if (!count($grabbed_events)) {
			$events_class = "no-events";
		} else {
			$class = '';
			foreach ( array_keys($grabbed_events) as $key ) {
				$an_event =& $grabbed_events[$key];	
				$author = ' author'.$an_event->event_author;
				if ( strpos ( $class, $author ) === false ) {
					$class .= $author;
				}
			}
			$events_class = "has-events$class";
		}
		$class = '';
		$dateclass = mc_dateclass( time()+$offset, mktime(0,0,0,$c_month,$c_day, $c_year ) );
		$my_calendar_body .= $mc_nav."\n"."<h3 class='mc-single".$class."'>".date_i18n( $date_format,strtotime("$c_year-$c_month-$c_day")).'</h3><div id="mc-day" class="'.$dayclass.' '.$dateclass.' '.$events_class.'">'."\n";
		$process_date = date_i18n("Y-m-d",strtotime("$c_year-$c_month-$c_day"));		
		if ( count($grabbed_events) > 0 ) {
			foreach ( array_keys($grabbed_events) as $key ) {
			$now =& $grabbed_events[$key];				
				$author = ' author'.$now->event_author;
				if ( strpos ( $class, $author ) === false ) {
					$class .= $author;
				}
			}
			$my_calendar_body .= my_calendar_draw_events($grabbed_events, $format, $process_date);
		} else {
			$my_calendar_body .= __( 'No events scheduled for today!','my-calendar');
		}
		$my_calendar_body .= "</div>";
	} else {
		if ( !is_numeric($c_day) || $c_day == 0 ) { $c_day = date("d",time()+($offset)); }
		$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));		
		$num_months = get_option('mc_show_months');
		if ( $time == 'month' && $c_day > $days_in_month ) {
			$c_day = $days_in_month;
		}
		$current_date = mktime(0,0,0,$c_month,$c_day,$c_year);
		$current_date_header = date_i18n('F Y',$current_date);
		$through_date = mktime(0,0,0,$c_month+($num_months-1),$c_day,$c_year);

		$current_month_header = ( date('Y',$current_date) == date('Y',$through_date) )?date_i18n('F',$current_date):date_i18n('F Y',$current_date);
		$through_month_header = date_i18n('F Y', $through_date);
		// Adjust the days of the week if week start is not Monday
			if ($time == 'week') {
				$first_weekday = $start_of_week;
			} else {
				if ( $start_of_week == 0 ) {
					$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
				} else {
					$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
					$first_weekday = ($first_weekday==0?6:$first_weekday-1);
				}
			}
			$and = __("and",'my-calendar');
			$category_label = ($category != "" && $category != "all")?str_replace("|"," $and ",$category) . ' ':'';
			// Add the calendar table and heading
			$caption_text = ' '.stripslashes( trim( get_option('mc_caption') ) );
			$mc_display_jump = get_option('mc_display_jump');
				if ($format == "calendar" || $format == "mini" ) {
					$my_calendar_body .= '
			<div class="my-calendar-header">';
					// We want to know if we should display the date switcher
					if ( $time != 'week' && $time != 'day' ) {
						$my_calendar_body .= ( $mc_display_jump == 'true' )?mc_build_date_switcher( $format, $main_class ):'';
					}
					// The header of the calendar table and the links.
					$my_calendar_body .= "$mc_nav\n$format_toggle\n</div>";
					$my_calendar_body .= "\n<table class=\"my-calendar-table\">\n";
					$week_caption = stripslashes(get_option('mc_week_caption'));
					$caption_heading = ($time != 'week')?$current_date_header.$caption_text:$week_caption.$caption_text;
					$my_calendar_body .= "<caption class=\"my-calendar-$time\">".$caption_heading."</caption>\n";
				} else {
					// determine which header text to show depending on number of months displayed;
					if ( $time != 'week' && $time != 'day' ) {
						$list_heading = ($num_months <= 1)?__('Events in','my-calendar').' '.$current_date_header.$caption_text."\n":$current_month_header.'&ndash;'.$through_month_header.$caption_text;
					} else {
						$list_heading = stripslashes(get_option('mc_week_caption'));
					}
					$my_calendar_body .= "<h3 class=\"my-calendar-$time\">$list_heading</h3>\n";		
					$my_calendar_body .= '<div class="my-calendar-header">'; 
					// We want to know if we should display the date switcher
					if ( $time != 'week' && $time != 'day' ) {
						$my_calendar_body .= ( $mc_display_jump == 'true' )?mc_build_date_switcher( $format, $main_class ):'';
					}
					$my_calendar_body .= "$mc_nav\n$format_toggle\n</div>";	
				}
		// If in a calendar format, print the headings of the days of the week
	if ( $format == "calendar" || $format == "mini" ) {

		$my_calendar_body .= "<thead>\n<tr>\n";
		for ($i=0; $i<=6; $i++) {
			if ( $start_of_week == 0) {
				$class = ($i<6&&$i>0)?'day-heading':'weekend-heading';
			} else {
				$class = ($i<5)?'day-heading':'weekend-heading';
			}
			$dayclass = strtolower(strip_tags($abbrevs[$i]));
			if ( ( $class == 'weekend-heading' && get_option('mc_show_weekends') == 'true' ) || $class != 'weekend-heading' ) {
				$my_calendar_body .= "<th scope='col' class='$class $dayclass'>".$name_days[$i]."</th>\n";
			}
		}	
		$my_calendar_body .= "\n</tr>\n</thead>\n<tbody>";

		if ($time == 'week') {
			$firstday = date('j',mktime(0,0,0,$c_month,$c_day,$c_year));
			$lastday = $firstday + 6;
		} else {
			$firstday = 1;
			$lastday = $days_in_month;
		}
		$thisday = 0;
		$useday = 1;
		$inc_month = false;
		$go = false;
		$inc = 0;
			for ($i=$firstday; $i<=$lastday;) {
			$my_calendar_body .= '<tr>';
					if ($time == 'week') {
						$ii_start = $first_weekday;$ii_end = $first_weekday + 6;
					} else {
						$ii_start = 0;$ii_end = 6;
					}
					for ($ii=$ii_start; $ii<=$ii_end; $ii++) {
					// moved $process_date down here because needs to be updated daily, not weekly.
					$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$thisday+1,$c_year));
					//echo "$i, $firstday, $ii, $first_weekday<br />";
						if ($ii==$first_weekday && $i==$firstday) {
							$go = TRUE;
						} elseif ($thisday > $days_in_month ) {
							$go = FALSE;
						}
						if ( empty( $thisday ) ) {
							$numdays = date('t',mktime(0,0,0,$c_month-1));
							$now = $numdays - ($first_weekday-($ii+1));
						}
						if ( $go ) {
						$addclass = "";
							if ($i > $days_in_month) {
								$addclass = " nextmonth";
								$thisday = $useday;
								if ($inc_month == false) {
									$c_year = ($c_month == 12)?$c_year+1:$c_year;
									$c_month = ($c_month == 12)?1:$c_month+1;
								} 
								$inc_month = true;
								$useday++;
							} else {
								$thisday = $i;
							}
							$class = '';
							$grabbed_events = my_calendar_grab_events($c_year,$c_month,$thisday,$category,$ltype,$lvalue);
							$events_class = '';
								if (!count($grabbed_events) || !is_array($grabbed_events)) {
									$events_class = "no-events$addclass";
									$element = 'span';
									$trigger = '';
									$close = 'span';
								} else {
									foreach ( $grabbed_events as $an_event ) {
										$author = ' author'.$an_event->event_author;
										if ( strpos ( $class, $author ) === false ) {
											$class .= $author;
										}
										$cat = ' mcat_'.sanitize_title($an_event->category_name);
										if ( strpos ( $class, $cat ) === false ) {
											$class .= $cat;
										}
									}			
									$events_class = "has-events$addclass$class";
									if ($format == 'mini') {
									 if ( get_option('mc_open_day_uri') == 'true' || get_option('mc_open_day_uri') == 'false' ) {
										$day_url = mc_build_url( array('yr'=>$c_year,'month'=>$c_month,'dy'=>$thisday), array('month','dy','yr','ltype','loc','mcat'), get_option( 'mc_day_uri' ) );
										$link = ( get_option('mc_day_uri') != '' )?$day_url:'#';
									} else {
										$atype = str_replace( 'anchor','',get_option('mc_open_day_uri') );
										$ad = str_pad( $thisday, 2, '0', STR_PAD_LEFT ); // need to match format in ID
										$am = str_pad( $c_month, 2, '0', STR_PAD_LEFT );
										$date_url = mc_build_url( array('yr'=>$c_year,'month'=>$c_month,'dy'=>$thisday), array('month','dy','yr','ltype','loc','mcat','cid'), get_option( 'mc_mini_uri' ) );	
										$link = ( get_option('mc_mini_uri') != '' ) ?$date_url.'#'.$atype.'-'.$c_year.'-'.$am.'-'.$ad:'#';
									}
										$element = 'a href="'.$link.'"';
										$close = 'a';
										$trigger = 'trigger';
									} else {
										$element = 'span';
										$trigger = '';
										$close = 'span';
									}
								}
								$dateclass = mc_dateclass( time()+$offset, mktime(0,0,0,$c_month,$thisday, $c_year ) );
								
							if ( $start_of_week == 0) {
								$class = ($ii<6&&$ii>0?"$trigger":" weekend $trigger");
								$is_weekend = ($ii<6&&$ii>0)?false:true;
								$i++;
							} else {
								$class = ($ii<5)?"$trigger":" weekend $trigger";
								$is_weekend = ($ii<5)?false:true;
								$i++;
							}
							$dayclass = strtolower(date_i18n('D',mktime (0,0,0,$c_month,$thisday,$c_year)));
							$week_format = (get_option('mc_week_format')=='')?'M j, \'y':get_option('mc_week_format');
							$week_date_format = date_i18n($week_format,strtotime( "$c_year-$c_month-$thisday" ) );				
							$thisday_heading = ($time == 'week')?"<small>$week_date_format</small>":$thisday;
							/* if ( $thisday == 19 || $thisday == 20 || $thisday == 21 ) {
									echo 'Today: '.date_i18n("Y-m-d h:i",time()+$offset).' -- '.date("Y-m-d h:i", mktime (0,0,0,$c_month,$thisday,$c_year)).'<br />';
} */
							if ( ( $is_weekend && get_option('mc_show_weekends') == 'true' ) || !$is_weekend ) {
					$my_calendar_body .= "\n".'<td id="'.$format.'-'.$process_date.'" class="'.$dayclass.' '.$class.' '.$dateclass.' '.$events_class.'">'."\n<$element class='mc-date ".$class."'>".$thisday_heading."</$close>". my_calendar_draw_events($grabbed_events, $format, $process_date)."</td>";
							}
					  } else {
						if ( !isset($now) ) { $now = 1; }
						if ( get_option('mc_show_weekends') != 'true' && date('N',strtotime(date('Y-m-d',mktime(0,0,0,$c_month,1,$c_year)))) < 6 ) {
							$process_date = date('Y-m-d',mktime(0,0,0,$c_month,$now,$c_year));
						} else {
							$process_date = date('Y-m-d',mktime(0,0,0,$c_month-1,$now,$c_year));						
						}
						$is_weekend = ( date('N',strtotime($process_date)) < 6 )?false:true;
						//$my_calendar_body .= date('N',$process_date);
						if ( ( $is_weekend && get_option('mc_show_weekends') == 'true' ) || !$is_weekend ) {
							if ( get_option('mc_show_weekends') == 'true' || ( get_option('mc_show_weekends') != 'true' && $inc < 5 ) ) {
							$my_calendar_body .= "<td class='day-without-date'>&nbsp;</td>\n";
							}
							$inc++;
						}
					  }
					}
				$my_calendar_body .= "</tr>\n";
			}
		$my_calendar_body .= "\n</tbody>\n</table>";
	} else if ($format == "list") {
		$my_calendar_body .= "<ul id=\"calendar-list\">";
		// show calendar as list
		$num_months = ($time == 'week')?1:get_option('mc_show_months');
		$num_events = 0;
		for ($m=0;$m<$num_months;$m++) {
			$add_month = ($m == 0)?0:1;
			$c_month = (int) $c_month + $add_month;
			if ($c_month > 12) {
				$c_month = $c_month - 12;
				$c_year = $c_year + 1;
			}
			$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
			
				if ($time == 'week') {
					$firstday = date('j',mktime(0,0,0,$c_month,$c_day,$c_year));
					$lastday = $firstday + 6;
				} else {
					$firstday = 1;
					$lastday = $days_in_month;
				}
				$useday = 1;
				$inc_month = false;	
				$class = 'even';
			for ($i=$firstday; $i<=$lastday; $i++) {
					if ($i > $days_in_month) {
						$thisday = $useday;
						if ($inc_month == false) {
							$c_month = ($c_month == 12)?1:$c_month+1;
						} 
						$inc_month = true;
						$useday++;
					} else {
						$thisday = $i;
					}		
			$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$thisday,$c_year));
				$grabbed_events = my_calendar_grab_events($c_year,$c_month,$thisday,$category,$ltype,$lvalue);
				if (count($grabbed_events)) {
					if ( get_option('list_javascript') != 1) {
						$is_anchor = "<a href='#'>";
						$is_close_anchor = "</a>";
					} else {
						$is_anchor = $is_close_anchor = "";
					}
					$classes = mc_dateclass( time()+$offset, mktime(0,0,0,$c_month,$thisday, $c_year ) );
					$classes .= ( my_calendar_date_xcomp( $process_date, date('Y-m-d',time()+$offset) ) )?' past-date':'';
							usort( $grabbed_events, 'my_calendar_time_cmp' );
							$now = $grabbed_events[0];
							$count = count( $grabbed_events ) - 1;
							if ( $count == 0 ) { $cstate = ''; } else 
							if ( $count == 1 ) { 
								$cstate = sprintf(__(" and %d other event",'my-calendar'), $count); 
							} else {
								$cstate = sprintf(__(" and %d other events",'my-calendar'), $count); 
							}
							if ( get_option( 'mc_show_list_info' ) == 'true' ) {
								$title = ' - '.$is_anchor . stripcslashes($now->event_title).$cstate . $is_close_anchor;
							} else {
								$title = '';
							}
					$my_calendar_body .= "
					<li id='$format-$process_date' class='mc-events $class $classes'>
					<strong class=\"event-date\">$is_anchor".date_i18n($date_format,mktime(0,0,0,$c_month,$thisday,$c_year))."$is_close_anchor"."$title</strong>".my_calendar_draw_events($grabbed_events, $format, $process_date)."
					</li>";
					$num_events++;
				} 	
				$class = (my_calendar_is_odd($num_events))?"odd":"even";
			}	
		}
		if ($num_events == 0) {
			$my_calendar_body .= "<li class='no-events'>".__('There are no events scheduled during this period.','my-calendar') . "</li>";
		}
		$my_calendar_body .= "</ul>";
	} else {
		$my_calendar_body .= __("Unrecognized calendar format. Please use one of 'list','calendar', or 'mini'.",'my-calendar')." '<code>$format</code>.'";
	}	
	$category_key = '';
	$cat_details = '';
		if ($showkey != 'no') {
			$cat_limit = mc_select_category($category,'all','category');
			$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " $cat_limit ORDER BY category_name ASC";
			$cat_details = $wpdb->get_results($sql);
			$category_key .= '<div class="category-key">
			<h3>'.__('Category Key','my-calendar')."</h3>\n<ul>\n";
				$subpath = (is_custom_icon())?'/my-calendar-custom/':'/my-calendar/icons/';
				$path = $wp_plugin_url . $subpath;
			foreach($cat_details as $cat_detail) {
				$hex = (strpos($cat_detail->category_color,'#') !== 0)?'#':'';
			
				$title_class = sanitize_title($cat_detail->category_name);
				if ($cat_detail->category_icon != "" && get_option('mc_hide_icons')!='true') {
					$category_key .= '<li class="cat_'.$title_class.'"><span class="category-color-sample"><img src="'.$path.$cat_detail->category_icon.'" alt="" style="background:'.$hex.$cat_detail->category_color.';" /></span>'.stripcslashes($cat_detail->category_name)."</li>\n";
				} else {
					$category_key .= '<li class="cat_'.$title_class.'"><span class="category-color-sample no-icon" style="background:'.$hex.$cat_detail->category_color.';"> &nbsp; </span>'.stripcslashes($cat_detail->category_name)."</li>\n";			
				}
			}
			$category_key .= "</ul>\n</div>";
		}
		$category_key = apply_filters('mc_category_key',$category_key,$cat_details);
		$my_calendar_body .= $category_key;
			if ($format != 'mini') {
				$ical_m = (isset($_GET['month']))?(int) $_GET['month']:date('n');
				$ical_y = (isset($_GET['yr']))?(int) $_GET['yr']:date('Y');
				$my_calendar_body .= mc_rss_links($ical_y,$ical_m);
			}
		}
		$my_calendar_body .= "\n</div>";		
	}
    // The actual printing is done by the shortcode function.
	$my_calendar_body .= apply_filters('mc_after_calendar','',$args);
    return $my_calendar_body;
}

function mc_rss_links($y,$m) {
global $wp_rewrite;
	$feed = mc_feed_base().'my-calendar-rss';
	$ics_extend = ( $wp_rewrite->using_permalinks() )?"my-calendar-ics/?yr=$y&amp;month=$m":"my-calendar-ics&amp;yr=$y&amp;month=$m";
	$ics = mc_feed_base(). $ics_extend;

	$rss = (get_option('mc_show_rss')=='true')?"	<li class='rss'><a href='".$feed."'>".__('Subscribe by <abbr title="Really Simple Syndication">RSS</abbr>','my-calendar')."</a></li>":'';
	$ical = (get_option('mc_show_ical')=='true')?"	<li class='ics'><a href='".$ics."'>".__('Download as <abbr title="iCal Events Export">iCal</abbr>','my-calendar')."</a></li>":'';
	$output = "\n
<ul id='mc-export'>$rss
$ical
</ul>\n";	
	if ( get_option('mc_show_rss')=='true' || get_option('mc_show_ical')=='true' ) {
	return $output;
	}
}

function mc_feed_base() {
	global $wp_rewrite;
	$base = home_url();
		if ( $wp_rewrite->using_index_permalinks() ) {
			$append = "index.php/";
		} else {
			$append = '';
		}	
	$base .= ( $wp_rewrite->using_permalinks() )?'/'.$append.'feed/':'?feed=';
	return $base;
}

// Configure the "Next" link in the calendar
function my_calendar_next_link($cur_year,$cur_month,$cur_day,$format,$time='month') {
  $next_year = $cur_year + 1;
  $next_events = ( get_option( 'mc_next_events') == '' )?__("Next events &raquo;",'my-calendar'):stripcslashes( get_option( 'mc_next_events') );
  $num_months = get_option('mc_show_months');
  $nYr = $cur_year;
  if ($num_months <= 1 || $format!="list" ) {
	  if ($cur_month == 12) {
			$nMonth = 1;$nYr = $next_year;
	    } else {
			$next_month = $cur_month + 1;$nMonth = $next_month; $nYr = $cur_year;
	    }
	} else {
		$next_month = (($cur_month + $num_months) > 12)?(($cur_month + $num_months) - 12):($cur_month + $num_months);
		if ($cur_month >= (13-$num_months)) {	 
			$nMonth = $next_month;$nYr = $next_year;		
		} else {
			$nMonth = $next_month;$nYr = $cur_year;
		}	
	}
	$nDay = '';
	if ( $nYr != $cur_year ) { $format = 'F, Y'; } else { $format = 'F'; }
	$date = date_i18n($format,mktime( 0,0,0,$nMonth,1,$nYr ) );	
	if ($time == 'week') {
		$nextdate = strtotime( "$cur_year-$cur_month-$cur_day"."+ 7 days" );
		$nDay = date('d',$nextdate);
		$nYr = date('Y',$nextdate);
		$nMonth = date('m',$nextdate);
		if ( $nYr != $cur_year ) { $format = 'F j, Y'; } else { $format = 'F j'; }		
		$date = __('Week of ','my-calendar').date_i18n($format,mktime( 0,0,0,$nMonth,$nDay,$nYr ) );		
	}
	if ( $time == 'day' ) {
		$nextdate = strtotime( "$cur_year-$cur_month-$cur_day"."+ 1 days" );
		$nDay = date('d',$nextdate);
		$nYr = date('Y',$nextdate);
		$nMonth = date('m',$nextdate);
		if ( $nYr != $cur_year ) { $format = 'F j, Y'; } else { $format = 'F j'; }
		$date = date_i18n($format,mktime( 0,0,0,$nMonth,$nDay,$nYr ) );
	}	
	$next_events = str_replace( '{date}', $date, $next_events ); 		
	$output = array('month'=>$nMonth,'yr'=>$nYr,'day'=>$nDay,'label'=>$next_events);
	return $output;
}

// Configure the "Previous" link in the calendar
function my_calendar_prev_link($cur_year,$cur_month,$cur_day,$format,$time='month') {
  $last_year = $cur_year - 1;
  $previous_events = ( get_option( 'mc_previous_events') == '' )?__("&laquo; Previous events",'my-calendar'):stripcslashes( get_option( 'mc_previous_events') );
  $num_months = get_option('mc_show_months');
  $pYr = $cur_year;
  if ($num_months <= 1 || $format!="list" ) {  
		if ($cur_month == 1) {
			$pMonth = 12;$pYr = $last_year;
		} else {
	      $next_month = $cur_month - 1;  $pMonth = $next_month; $pYr = $cur_year;
	    }
	} else {
		$next_month = ($cur_month > $num_months)?($cur_month - $num_months):(($cur_month - $num_months) + 12);
		if ($cur_month <= $num_months) {
			$pMonth = $next_month; $pYr = $last_year;
		} else {
			$pMonth = $next_month; $pYr = $cur_year;
		}	
	}
	if ( $pYr != $cur_year ) { $format = 'F, Y'; } else { $format = 'F'; }	
	$date = date_i18n($format,mktime( 0,0,0,$pMonth,1,$pYr ) );
	$pDay = '';
	if ( $time == 'week' ) {
		$prevdate = strtotime( "$cur_year-$cur_month-$cur_day"."- 7 days" );
		$pDay = date('d',$prevdate);
		$pYr = date('Y',$prevdate);
		$pMonth = date('m',$prevdate);
		if ( $pYr != $cur_year ) { $format = 'F j, Y'; } else { $format = 'F j'; }				
		$date = __('Week of ','my-calendar').date_i18n($format,mktime( 0,0,0,$pMonth,$pDay,$pYr ) );
	}
	if ( $time == 'day' ) {
		$prevdate = strtotime( "$cur_year-$cur_month-$cur_day"."- 1 days" );
		$pDay = date('d',$prevdate);
		$pYr = date('Y',$prevdate);
		$pMonth = date('m',$prevdate);
		if ( $pYr != $cur_year ) { $format = 'F j, Y'; } else { $format = 'F j'; }				
		$date = date_i18n($format,mktime( 0,0,0,$pMonth,$pDay,$pYr ) );
	}
	$previous_events = str_replace( '{date}', $date, $previous_events ); 	
	$output = array( 'month'=>$pMonth,'yr'=>$pYr,'day'=>$pDay,'label'=>$previous_events );
	return $output;
}

function my_calendar_categories_list($show='list',$context='public') {
	global $wpdb;
	if ( isset($_GET['mc_id']) ) {
		return;
	}
	$output = '';
	$current_url = mc_get_current_url();
	
	$admin_fields = ($context == 'public')?' ':' multiple="multiple" size="5" ';
	$admin_label = ($context == 'public')?'':__('(select to include)','my-calendar');
	$form = "<form action='".$current_url."' method='get'>
				<div>";
			$qsa = array();
			parse_str($_SERVER['QUERY_STRING'],$qsa);
			if ( !isset( $_GET['cid'] ) ) { $form .= '<input type="hidden" name="cid" value="all" />'; }	
			foreach ($qsa as $name => $argument) {
				$name = esc_attr(strip_tags($name));
				$argument = esc_attr(strip_tags($argument));
				if ( $name != 'mcat' ) {
					$form .= '		<input type="hidden" name="'.$name.'" value="'.$argument.'" />'."\n";
				}
			}
		$form .= ($show == 'list')?'':'
		</div><p>';
	$public_form = ($context == 'public')?$form:'';
	$name = ($context == 'public')?'mcat':'category';
		
    $categories = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");
	if ( !empty($categories) && count($categories)>=1 ) {
		$output = "<div id='mc_categories'>\n";
		$url = mc_build_url( array('mcat'=>'all'),array() );		
		$output .= ($show == 'list')?"
		<ul>
			<li><a href='$url'>".__('All Categories','my-calendar')."</a></li>":$public_form.'
		<label for="category">'.__('Categories','my-calendar').' '.$admin_label.'</label>
			<select'.$admin_fields.'name="'.$name.'" id="category">
			<option value="all" selected="selected">'.__('All Categories','my-calendar').'</option>'."\n";
		
		foreach ($categories as $category) {
			$category_name = stripcslashes($category->category_name);
					if ( empty($_GET['mcat']) ) {
						$mcat = '';
					} else {
						$mcat = (int) $_GET['mcat'];
					}			
			if ($show == 'list') {
			$this_url = mc_build_url( array('mcat'=>$category->category_id ),array() );			
			$selected = ($category->category_id == $mcat )?' class="selected"':'';
			$output .= "			<li$selected><a rel='nofollow' href='$this_url'>$category_name</a></li>";
			} else {
			$selected = ($category->category_id == $mcat )?' selected="selected"':'';			
			$output .= "			<option$selected value='$category->category_id'>$category_name</option>\n";
			}
		}
		$output .= ($show == 'list')?'</ul>':'</select>';
		$output .= ($context != 'admin' && $show != 'list')?"<input type='submit' value=".__('Submit','my-calendar')." /></p></form>":'';
		$output .= "\n</div>";
	}
	$output = apply_filters('mc_category_selector',$output,$categories);
	return $output;
}
// array $add == keys and values to add 
// array $subtract == keys to subtract

function mc_build_url( $add, $subtract, $root='' ) {
global $wp_rewrite;
	if ( is_front_page() ) { 
		$home = get_bloginfo('url') . '/'; 		
	} else if ( is_home() ) {
		$page = get_option('page_for_posts');
		$home = get_permalink($page); 	
	} else if ( is_archive() ) {
		$home = ''; // an empty string seems to work best; leaving it open.
	} else {
		$home = get_permalink(); 		
	}
	if ( $root != '' ) { $home = $root; }
	$variables = $_GET;
	foreach($subtract as $value) {
		unset($variables[$value]);
	}
	foreach ($add as $key=>$value) {
		$variables[$key] = $value;
	}
	unset($variables['page_id']);
	if ( $root == '' ) {
	// root is set to empty when I want to reference the current location
		$char = ( $wp_rewrite->using_permalinks() || is_front_page() || is_archive() )?'?':'&amp;';
	} else {
		$char = ( $wp_rewrite->using_permalinks() )?'?':'&amp;'; // this doesn't work correctly -- it may *never* need to be &. Consider	
	}
return $home.$char.http_build_query($variables, '', '&amp;');
}

function my_calendar_show_locations($show='list',$datatype='name') {
	global $wpdb;
		switch ( $datatype ) {
			case "name":$data = "location_label";
			break;
			case "city":$data = "location_city";
			break;
			case "state":$data = "location_state";
			break;
			case "zip":$data = "location_postcode";
			break;
			case "country":$data = "location_country";
			break;
			case "hcard":$data = "location_label";
			break;
			default:$data = "location_label";
			break;
		}	
	$locations = $wpdb->get_results("SELECT DISTINCT * FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY $data ASC" );
	if ( $locations ) {
		$output = "<ul>";
		foreach( $locations as $key=>$value ) {
			$id = $value->location_id;
			if ( $datatype != 'hcard' ) {
				$label = stripslashes($value->{$data});
				$url = mc_maplink( $value, 'url', $source='location' );
				if ( $url ) {
					$output .= "<li><a href='$url'>$label</a></li>";
				} else {
					$output .= "<li>$label</li>";
				}
			} else {
				$label = mc_hcard( $value, true, true, 'location' );
				$output .= "<li>$label</li>";
			}			
		}
		$output .= "</ul>";
		$output = apply_filters('mc_location_list',$output,$locations);
		return $output;
	}
}

function my_calendar_locations_list($show='list',$type='saved',$datatype='name') {
global $wpdb;
	$output = '';
	if ( isset( $_GET['mc_id'] ) ) {
		return;
	}
	if ( $type == 'saved' ) {
		switch ( $datatype ) {
			case "name":$data = "location_label";
			break;
			case "city":$data = "location_city";
			break;
			case "state":$data = "location_state";
			break;
			case "zip":$data = "location_postcode";
			break;
			case "country":$data = "location_country";
			break;
			default:$data = "location_label";
			break;
		}
	} else {
		$data = $datatype;
	}
	$current_url = mc_get_current_url();
	if ($type == 'saved') {
		$locations = $wpdb->get_results("SELECT DISTINCT $data FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY $data ASC", ARRAY_A );
	} else {
		$data = get_option( 'mc_user_settings' );
		$locations = $data['my_calendar_location_default']['values'];
		$datatype = str_replace('event_','',get_option( 'mc_location_type' ));
		$datatype = ($datatype=='label')?'name':$datatype;
		$datatype = ($datatype=='postcode')?'zip':$datatype;
	}
	if ( count($locations) > 1 ) {
		if ($show == 'list') {
			$url = mc_build_url( array('loc'=>'all','ltype'=>'all'),array() );
			$output .= "<ul id='mc-locations-list'>
			<li><a href='$url'>".__('Show all','my-calendar')."</a></li>\n";
		} else {
			$ltype = (!isset($_GET['ltype']))?$datatype:$_GET['ltype'];
			$output .= "
	<div id='mc_locations'>
		<form action='".$current_url."' method='get'>
		<div>
			<input type='hidden' name='ltype' value='$ltype' />";
			$qsa = array();
			parse_str($_SERVER['QUERY_STRING'],$qsa);
		if ( !isset( $_GET['cid'] ) ) { $output .= '<input type="hidden" name="cid" value="all" />'; }	
		foreach ($qsa as $name => $argument) {
			$name = esc_attr(strip_tags($name));
			$argument = esc_attr(strip_tags($argument));
				if ($name != 'loc' && $name != 'ltype') {
					$output .= "\n		".'<input type="hidden" name="'.$name.'" value="'.$argument.'" />';
				}
			}
			$output .= "
			<label for='mc-locations-list'>".__('Show events in:','my-calendar')."</label>
			<select name='loc' id='mc-locations-list'>
			<option value='all'>".__('Show all','my-calendar')."</option>\n";
		}
		foreach ( $locations as $key=>$location ) {
			if ($type == 'saved') {
				foreach ( $location as $key=>$value ) {
					$vt = urlencode(trim($value));
					$value = stripcslashes($value);
					if ( empty($_GET['loc']) ) {
						$loc = '';
					} else {
						$loc = $_GET['loc'];
					}
					if ($show == 'list') {
						$selected = ( $vt == $loc )?" class='selected'":'';
						$this_url = mc_build_url( array('loc'=>$vt,'ltype'=>$datatype),array() );
						$output .= "		<li$selected><a rel='nofollow' href='$this_url'>$value</a></li>\n";
					} else {
						$selected = ( $vt == $loc )?" selected='selected'":'';
						$output .= "		<option value='$vt'$selected>$value</option>\n";
					}
				}
			} else {
				$vk = urlencode(trim($key));
				$location = trim($location);
				if ($show == 'list') {
					$selected = ($vk == $_GET['loc'])?" class='selected'":'';
					$this_url = mc_build_url( array('loc'=>$vk,'ltype'=>$datatype),array() );					
					$output .= "		<li$selected><a rel='nofollow' href='$this_url'>$location</a></li>\n";
				} else {
					$selected = ($vk == $_GET['loc'])?" selected='selected'":'';			
					$output .= "		<option value='$vk'$selected>$location</option>\n";	
				}			
			}
		}
		if ($show == 'list') {
			$output .= "</ul>";
		} else {
			$output .= "		</select> 
			<input type='submit' value=".__('Submit','my-calendar')." />
			</div>
		</form>
	</div>";
		}
		$output = apply_filters('mc_location_selector',$output,$locations);	
		return $output;	
	} else {
		return;
	}
}

function mc_user_timezone($type='') {
global $user_ID;
	 $user_settings = get_option('mc_user_settings');
	 if ( empty($user_settings['my_calendar_tz_default']['enabled'] ) ) {
		$enabled = 'off';
	 } else {
	    $enabled = $user_settings['my_calendar_tz_default']['enabled'];
	 }
	 if ( get_option('mc_user_settings_enabled') == 'true' && $enabled == 'on' ) {
		if ( is_user_logged_in() ) {
			get_currentuserinfo();
			$current_settings = get_user_meta( $user_ID, 'my_calendar_user_settings', true );
			$tz = $current_settings['my_calendar_tz_default'];
		} else {
			$tz = '';
		}
	 } else {
		$tz = 'none';
	 }
	 if ( $tz == get_option('gmt_offset') || $tz == 'none' || $tz == '' ) {
		$gtz = '';
	 } else if ( $tz < get_option('gmt_offset') ) {
		$gtz = -(abs( get_option('gmt_offset') - $tz ) );
	 } else {
		$gtz = (abs( get_option('gmt_offset') - $tz ) );
	 }
	 return $gtz;
}
?>