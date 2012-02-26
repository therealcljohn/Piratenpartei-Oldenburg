<?php
function jd_draw_template($array,$template,$type='list') {
	//1st argument: array of details
	//2nd argument: template to print details into
	foreach ($array as $key=>$value) {	
	    $search = "{".$key."}";
		if ($type != 'list') {
			if ( $key == 'link' && $value == '') { $value = ( get_option('mc_uri') != '' )?get_option('mc_uri'):get_bloginfo('url'); }
			if ( $key != 'guid') { $value = htmlentities($value); }
		}
		$template = stripcslashes(str_replace($search,$value,$template));
		$rss_search = "{rss_$key}";
		$value = utf8_encode(htmlentities( $value,ENT_COMPAT,get_bloginfo('charset') ) );
		$template = stripcslashes(str_replace($rss_search,$value,$template));
	}
	return $template;
}

function mc_maplink( $event, $request='map', $source='event' ) {
	if ( $source == 'event' ) {
		$map_string = $event->event_street.' '.$event->event_street2.' '.$event->event_city.' '.$event->event_state.' '.$event->event_postcode.' '.$event->event_country;	
		$zoom = ($event->event_zoom != 0)?$event->event_zoom:'15';	
		$map_string = str_replace(" ","+",$map_string);
		if ($event->event_longitude != '0.000000' && $event->event_latitude != '0.000000') {
			$map_string = "$event->event_latitude,$event->event_longitude";
		}
	} else {
		$map_string = $event->location_street.' '.$event->location_street2.' '.$event->location_city.' '.$event->location_state.' '.$event->location_postcode.' '.$event->location_country;	
		$zoom = ($event->location_zoom != 0)?$event->location_zoom:'15';	
		$map_string = str_replace(" ","+",$map_string);
		if ($event->location_longitude != '0.000000' && $event->location_latitude != '0.000000') {
			$map_string = "$event->location_latitude,$event->location_longitude";
		}	
	}
	if ( strlen( trim( $map_string ) ) > 5 ) {
		$map_url = "http://maps.google.com/maps?f=q&amp;z=$zoom&amp;q=to+$map_string";
		if ( $request == 'url' || $source == 'location' ) { return $map_url; }
		$map_label = stripslashes( ($event->event_label != "")?$event->event_label:$event->event_title);
		$map = "<a href=\"$map_url\" class='map-link external'>".sprintf(__('Map<span> to %s</span>','my-calendar'),$map_label )."</a>";
	} else {
		$map = "";
	}
	return $map;
}

function mc_hcard( $event, $address='true', $map='true', $source='event' ) {
	$the_map = mc_maplink( $event, 'map', $source );
	$event_url = ($source=='event')?$event->event_url:$event->location_url;
	$event_label = stripslashes( ($source=='event')?$event->event_label:$event->location_label );
	$event_street = stripslashes( ($source=='event')?$event->event_street:$event->location_street );
	$event_street2 = stripslashes( ($source=='event')?$event->event_street2:$event->location_street2 );
	$event_city = stripslashes( ($source=='event')?$event->event_city:$event->location_city );
	$event_state = stripslashes( ($source=='event')?$event->event_state:$event->location_state );
	$event_postcode = stripslashes( ($source=='event')?$event->event_postcode:$event->location_postcode );
	$event_country = stripslashes( ($source=='event')?$event->event_country:$event->location_country );
	
	if ( !$event_url && !$event_label && !$event_street && !$event_street2 && !$event_city && !$event_state && !$event_postcode && !$event_country ) return;
	
	$sitelink_html = "<div class='url link'><a href='$event_url' class='location-link external'>".sprintf(__('Visit web site<span>: %s</span>','my-calendar'),$event_label)."</a></div>";
	$hcard = "<div class=\"address vcard\">";
	if ( $address == 'true' ) {
		$hcard .= "<div class=\"adr\">";
		if ($event_label != "") {$hcard .= "<strong class=\"org\">".$event_label."</strong><br />";	}					
		if ($event_street != "") {$hcard .= "<div class=\"street-address\">".$event_street."</div>";}
		if ($event_street2 != "") {	$hcard .= "<div class=\"street-address\">".$event_street2."</div>";	}
		if ($event_city != "") {$hcard .= "<span class=\"locality\">".$event_city."</span>, ";}						
		if ($event_state != "") {$hcard .= "<span class=\"region\">".$event_state."</span> ";}
		if ($event_postcode != "") {$hcard .= " <span class=\"postal-code\">".$event_postcode."</span>";}	
		if ($event_country != "") {	$hcard .= "<div class=\"country-name\">".$event_country."</div>";}
		$hcard .= "</div>";
	}
	if ( $map == 'true' ) {
		$the_map = ($source == 'location' )?"<a href='$the_map'>$event_label</a>":$the_map;
		$hcard .= ($the_map!='')?"<div class='url map'>$the_map</div>":'';
	}
	$hcard .= ($event_url!='')?$sitelink_html:'';
	$hcard .= "</div>";	
	return $hcard;
}

// Draw an event but customise the HTML for use in the widget
function event_as_array($event,$type='html') {
	global $wpdb,$wp_plugin_dir,$wp_plugin_url;
	// My Calendar must be updated to run this function
	check_my_calendar();
	$details = array();
	$date_format = ( get_option('mc_date_format') != '' )?get_option('mc_date_format'):get_option('date_format');	
	$dateid = date( 'Y-m-d',strtotime( $event->event_begin ) );
	$month_date = date('dS',strtotime( $event->event_begin ) );
	$day_name = date_i18n('l',strtotime($event->event_begin));
	$week_number = mc_ordinal( week_of_month( $event->event_begin ) );
	$id = $event->event_id;
	$offset = (60*60*get_option('gmt_offset'));  
	$category_icon = esc_attr($event->category_icon);
	$path = (is_custom_icon())?plugins_url('/my-calendar-custom/'):plugins_url('icons',__FILE__).'/';
	$category_icon = $path . $category_icon;
		$e = get_userdata($event->event_author);
		$host = get_userdata($event->event_host);
		$details['author'] = $e->display_name;
		$details['host'] = ($host->display_name == '')?$e->display_name:$host->display_name;
		$details['host_email'] = ($host->user_email == '')?$e->user_email:$host->user_email;
	$map = mc_maplink( $event );
	$map_url = mc_maplink( $event, 'url' );
	$hcard = mc_hcard( $event );	
	$sitelink_html = "<div class='url link'><a href='$event->event_url' class='location-link external'>".sprintf(__('Visit web site<span>: %s</span>','my-calendar'),$event->event_label)."</a></div>";
	$details['sitelink_html'] = $sitelink_html;
	$details['sitelink'] = $event->event_url;
	switch ( $event->event_recur ) {
		case 'S':$event_recur=__('Does not recur','my-calendar');break;
		case 'D':$event_recur=__('Daily','my-calendar');break;
		case 'E':$event_recur=__('Daily, weekdays only','my-calendar');break;
		case 'W':$event_recur=__('Weekly','my-calendar');break;
		case 'B':$event_recur=__('Bi-weekly','my-calendar');break;
		case 'M':$event_recur=sprintf(__('Date of Month (the %s of each month)','my-calendar'), $month_date );break;
		case 'U':$event_recur=sprintf(__('Day of Month (the %s %s of each month)','my-calendar'), $week_number, $day_name );break;
		case 'Y':$event_recur=__('Annually','my-calendar');break;
	}	

	$details['recurs'] = $event_recur;
	$details['repeats'] = $event->event_repeats;
	$real_end_date = $event->event_end;
	$date = date_i18n( $date_format,strtotime( $event->event_begin ) );
	$date_end = date_i18n( $date_format,strtotime($real_end_date) );
	$details['image'] = ( $event->event_image != '' )?"<img src='$event->event_image' alt='' class='mc-image' />":'';
	$details['time'] = ( $event->event_time == '00:00:00' )?get_option( 'mc_notime_text' ):date(get_option('mc_time_format'),strtotime($event->event_time));
	$details['endtime'] = ($event->event_endtime == '00:00:00' )?'':date_i18n( get_option('mc_time_format'),strtotime($event->event_endtime));
	$endtime = ($event->event_endtime == '00:00:00')?'23:59:00':$event->event_endtime;	
	$tz = mc_user_timezone();
	if ($tz != '') {
		$local_begin = date_i18n( get_option('mc_time_format'), strtotime($event->event_time ."+$tz hours") );
		$local_end = date_i18n( get_option('mc_time_format'), strtotime($event->event_endtime ."+$tz hours") );
		$details['usertime'] = "$local_begin";
		$details['endusertime'] = "$local_end";
	} else {
		$details['usertime'] = $details['endtime'];
		$details['endusertime'] = $endtime;		
	}

	$offset = get_option('gmt_offset'); // reset offset in hours
	$os = strtotime($event->event_begin .' '. $event->event_time);
	$oe = strtotime($real_end_date  .' '. $endtime );
	$dtstart = date("Ymd\THi00", (mktime(date('H',$os),date('i',$os), date('s',$os), date('m',$os),date('d',$os), date('Y',$os) ) - ($offset*60*60) ) ).'Z'; 
	$dtend = date("Ymd\THi00", (mktime(date('H',$oe),date('i',$oe), date('s',$oe), date('m',$oe),date('d',$oe), date('Y',$oe) ) - ($offset*60*60) ) ).'Z';
	$details['ical_start'] = $dtstart;
	$details['ical_end'] = $dtend;
		$ical_link = mc_build_url( array('vcal'=>"mc_".$dateid."_".$id), array('month','dy','yr','ltype','loc','mcat','format'), get_option( 'mc_uri' ) );
	$details['ical'] = $ical_link;
	$details['ical_html'] = "<a class='ical' rel='nofollow' href='$ical_link'>".__('iCal','my-calendar')."</a>";
	$details['dtstart'] = date( 'Y-m-d\TH:i:s', strtotime($event->event_begin.' '.$event->event_time) );// hcal formatted
	$details['dtend'] = date( 'Y-m-d\TH:i:s', strtotime($real_end_date.' '.$endtime) );	//hcal formatted end
	$details['rssdate'] = date( 'D, d M Y H:i:s +0000', strtotime( $event->event_added ) );	
	$details['date'] = $date;
	$details['enddate'] = $date_end;
	$details['cat_id'] = $event->event_category;
	$details['category'] = stripslashes($event->category_name);
	$details['title'] = stripcslashes($event->event_title);
	$details['skip_holiday'] = ($event->event_holiday == 0)?'false':'true';

		if ( $event->event_link_expires == 0 ) {
			$details['link'] = $event->event_link;
		} else {
			if ( my_calendar_date_comp( date('Y-m-d',strtotime($real_end_date)), date('Y-m-d',time()+$offset ) ) ) {
				$details['link'] = '';
			} else {
				$details['link'] = $event->event_link;
			}
		}
		if ( $event->event_open == '1' ) {
			$event_open = get_option( 'mc_event_open' );
		} else if ( $event->event_open == '0' ) {
			$event_open = get_option( 'mc_event_closed' ); 
		} else { 
			$event_open = '';	
		}
	
	$details['description'] = stripslashes($event->event_desc);
	$details['link_title'] = ($details['link'] != '')?"<a href='".$event->event_link."'>".stripslashes($event->event_title)."</a>":stripslashes($event->event_title);
	$details['location'] = stripslashes($event->event_label);
	$details['street'] = stripslashes($event->event_street);
	$details['street2'] = stripslashes($event->event_street2);
	$details['city'] = stripslashes($event->event_city);
	$details['state'] = stripslashes($event->event_state);
	$details['postcode'] = stripslashes($event->event_postcode);
	$details['country'] = stripslashes($event->event_country);
	$details['hcard'] = stripslashes($hcard);
	$details['link_map'] = $map;
	$details['shortdesc'] = stripslashes($event->event_short);
	$details['event_open'] = $event_open;
	$details['icon'] = $category_icon;
	$details['icon_html'] = "<img src='$category_icon' class='mc-category-icon' alt='' />";
	$details['color'] = $event->category_color;
	$details['event_status'] = ( $event->event_approved == 1 )?__('Published','my-calendar'):__('Reserved','my-calendar');
		$templates = get_option('mc_templates');
		$details_template = ( !empty($templates['label']) )? stripcslashes($templates['label']):__('Details about','my-calendar').' {title}';
		$tags = array( "{title}","{location}","{color}","{icon}" );
		$replacements = array( $event->event_title, $event->event_label, $event->category_color, $event->category_icon );
		$details_label = str_replace($tags,$replacements,$details_template );
		if ( $type == 'html' ) {
			$details_link = mc_build_url( array('mc_id'=>"mc_".$dateid."_".$id), array('month','dy','yr','ltype','loc','mcat','format','feed'), get_option( 'mc_uri' ) );
		} else {
			$details_link = '';
		}
	$details['details'] = ( get_option( 'mc_uri' ) != '' )?"<a href='$details_link'>$details_label</a>":'';
	$details['dateid'] = $dateid;
	$details['id'] = $id;
	// RSS guid
	$details['region'] = $event->event_region;
	$details['guid'] =( get_option( 'mc_uri' ) != '')?"<guid isPermaLink='true'>$details_link</guid>":"<guid isPermalink='false'>$details_link</guid>";
	/* ical format */
	$details['ical_location'] = $event->event_label .' '. $event->event_street .' '. $event->event_street2 .' '. $event->event_city .' '. $event->event_state .' '. $event->event_postcode;
	$ical_description = mc_newline_replace(strip_tags($event->event_desc));
	$details['ical_description'] = str_replace( "\r", "=0D=0A=", $event->event_desc );	
	$details['ical_desc'] = $ical_description;

	$details = apply_filters( 'mc_filter_shortcodes',$details,$event );

	return $details;
}
?>