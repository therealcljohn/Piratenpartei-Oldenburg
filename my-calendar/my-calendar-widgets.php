<?php
class my_calendar_today_widget extends WP_Widget {

function my_calendar_today_widget() {
	parent::WP_Widget( false,$name=__('My Calendar: Today\'s Events','my-calendar') );
}

function widget($args, $instance) {
	extract($args);
	$the_title = apply_filters('widget_title',$instance['my_calendar_today_title']);
	$the_template = $instance['my_calendar_today_template'];
	$the_substitute = $instance['my_calendar_no_events_text'];
	$the_category = ($instance['my_calendar_today_category']=='')?'default':esc_attr($instance['my_calendar_today_category']);
	$widget_link = (!empty($instance['my_calendar_today_linked']) && $instance['my_calendar_today_linked']=='yes')?get_option('mc_uri'):'';
	$widget_title = empty($the_title) ? '' : $the_title;
	$widget_title = ($widget_link=='') ? $widget_title : "<a href='$widget_link'>$widget_title</a>";	
	$widget_title = ($widget_title!='') ? $before_title . $widget_title . $after_title : '';
	$the_events = my_calendar_todays_events($the_category,$the_template,$the_substitute);
		if ($the_events != '') {
		  echo $before_widget;
		  echo $widget_title;
		  echo $the_events;
		  echo $after_widget;
		}
}

function form($instance) {
	global $default_template;
	$widget_title = esc_attr($instance['my_calendar_today_title']);
	$widget_template = esc_attr($instance['my_calendar_today_template']);
	if (!$widget_template) { $widget_template = $default_template; }
	$widget_text = esc_attr($instance['my_calendar_no_events_text']);
	$widget_category = esc_attr($instance['my_calendar_today_category']);
	$widget_linked = esc_attr($instance['my_calendar_today_linked']);
	
?>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_today_title'); ?>"><?php _e('Title','my-calendar'); ?>:</label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_today_title'); ?>" name="<?php echo $this->get_field_name('my_calendar_today_title'); ?>" value="<?php echo $widget_title; ?>"/>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_today_template'); ?>"><?php _e('Template','my-calendar'); ?></label><br />
	<textarea class="widefat" rows="8" cols="20" id="<?php echo $this->get_field_id('my_calendar_today_template'); ?>" name="<?php echo $this->get_field_name('my_calendar_today_template'); ?>"><?php echo $widget_template; ?></textarea>
	</p>
	<?php if ( get_option('mc_uri') == '' ) { $disabled = " disabled='disabled'"; $warning = _e('Add calendar URL to use this option.','my-calendar');  } else { ""; } ?>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_today_linked'); ?>"><?php _e('Link widget title to calendar:','my-calendar'); ?></label> <select<?php echo $disabled; ?> id="<?php echo $this->get_field_id('my_calendar_today_linked'); ?>" name="<?php echo $this->get_field_name('my_calendar_today_linked'); ?>">
	<option value="no" <?php echo ($widget_linked == 'no')?'selected="selected"':''; ?>><?php _e('Not Linked','my-calendar') ?></option>
	<option value="yes" <?php echo ($widget_linked == 'yes')?'selected="selected"':''; ?>><?php _e('Linked','my-calendar') ?></option>
	</select>
	</p>	
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_no_events_text'); ?>"><?php _e('Show this text if there are no events today:','my-calendar'); ?></label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_no_events_text'); ?>" name="<?php echo $this->get_field_name('my_calendar_no_events_text'); ?>" value="<?php echo $widget_text; ?>" /></textarea>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_today_category'); ?>"><?php _e('Category or categories to display:','my-calendar'); ?></label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_today_category'); ?>" name="<?php echo $this->get_field_name('my_calendar_today_category'); ?>" value="<?php echo $widget_category; ?>" /></textarea>
	</p>	
	<?php
}  

	function update($new_instance,$old_instance) {
		$instance = $old_instance;
		$instance['my_calendar_today_title'] = strip_tags($new_instance['my_calendar_today_title']);
		$instance['my_calendar_today_template'] = $new_instance['my_calendar_today_template'];
		$instance['my_calendar_no_events_text'] = strip_tags($new_instance['my_calendar_no_events_text']);
		$instance['my_calendar_today_category'] = strip_tags($new_instance['my_calendar_today_category']);
		$instance['my_calendar_today_linked'] = strip_tags($new_instance['my_calendar_today_linked']);		
		return $instance;		
	}

}

class my_calendar_upcoming_widget extends WP_Widget {

function my_calendar_upcoming_widget() {
	parent::WP_Widget( false,$name=__('My Calendar: Upcoming Events','my-calendar') );
}

function widget($args, $instance) {
	extract($args);
	$the_title = apply_filters('widget_title',$instance['my_calendar_upcoming_title']);
	$the_template = $instance['my_calendar_upcoming_template'];
	$the_substitute = $instance['my_calendar_no_events_text'];
	$before = ($instance['my_calendar_upcoming_before']!='')?esc_attr($instance['my_calendar_upcoming_before']):3;
	$after = ($instance['my_calendar_upcoming_after']!='')?esc_attr($instance['my_calendar_upcoming_after']):3;
	$skip = ($instance['my_calendar_upcoming_skip']!='')?esc_attr($instance['my_calendar_upcoming_skip']):0;
	$type = esc_attr($instance['my_calendar_upcoming_type']);
	$order = esc_attr($instance['my_calendar_upcoming_order']);
	$the_category = ($instance['my_calendar_upcoming_category']=='')?'default':esc_attr($instance['my_calendar_upcoming_category']);
	$widget_link = ($instance['my_calendar_upcoming_linked']=='yes')?get_option('mc_uri'):'';
	$widget_title = empty($the_title) ? '' : $the_title;
	$widget_title = ($widget_link=='') ? $widget_title : "<a href='$widget_link'>$widget_title</a>";
	$widget_title = ($widget_title!='') ? $before_title . $widget_title . $after_title : '';	
	$the_events = my_calendar_upcoming_events($before,$after,$type,$the_category,$the_template,$the_substitute, $order,$skip);
		if ($the_events != '') {
		  echo $before_widget;
		  echo $widget_title;
		  echo $the_events;
		  echo $after_widget;
		}
}


function form($instance) {
	global $default_template;
	$widget_title = esc_attr($instance['my_calendar_upcoming_title']);
	$widget_template = esc_attr($instance['my_calendar_upcoming_template']);
	if (!$widget_template) { $widget_template = $default_template; }
	$widget_text = esc_attr($instance['my_calendar_no_events_text']);
	$widget_category = esc_attr($instance['my_calendar_upcoming_category']);
	$widget_before = esc_attr($instance['my_calendar_upcoming_before']);
	$widget_after = esc_attr($instance['my_calendar_upcoming_after']);
	$widget_type = esc_attr($instance['my_calendar_upcoming_type']);
	$widget_order = esc_attr($instance['my_calendar_upcoming_order']);
	$widget_linked = esc_attr($instance['my_calendar_upcoming_linked']);
	$widget_skip = esc_attr($instance['my_calendar_upcoming_skip']);
	
?>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_title'); ?>"><?php _e('Title','my-calendar'); ?>:</label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_upcoming_title'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_title'); ?>" value="<?php echo $widget_title; ?>"/>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_template'); ?>"><?php _e('Template','my-calendar'); ?></label><br />
	<textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('my_calendar_upcoming_template'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_template'); ?>"><?php echo $widget_template; ?></textarea>
	</p>
	<fieldset>
	<legend><?php _e('Widget Options','my-calendar'); ?></legend>
	<?php $config_url = admin_url("admin.php?page=my-calendar-config"); ?>
	<?php if ( get_option('mc_uri') == '' ) { $disabled = " disabled='disabled'"; $warning = _e('Add <a href="'.$config_url.'#mc_uri" target="_blank" title="Opens in new window">calendar URL in settings</a> to use this option.','my-calendar');  } else { ""; } ?>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_linked'); ?>"><?php _e('Link widget title to calendar:','my-calendar'); ?></label> <select<?php echo $disabled; ?> id="<?php echo $this->get_field_id('my_calendar_upcoming_linked'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_linked'); ?>">
	<option value="no" <?php echo ($widget_linked == 'no')?'selected="selected"':''; ?>><?php _e('Not Linked','my-calendar') ?></option>
	<option value="yes" <?php echo ($widget_linked == 'yes')?'selected="selected"':''; ?>><?php _e('Linked','my-calendar') ?></option>
	</select>
	</p>
	
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_type'); ?>"><?php _e('Display upcoming events by:','my-calendar'); ?></label> <select id="<?php echo $this->get_field_id('my_calendar_upcoming_type'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_type'); ?>">
	<option value="events" <?php echo ($widget_type == 'events')?'selected="selected"':''; ?>><?php _e('Events (e.g. 2 past, 3 future)','my-calendar') ?></option>
	<option value="days" <?php echo ($widget_type == 'days')?'selected="selected"':''; ?>><?php _e('Dates (e.g. 4 days past, 5 forward)','my-calendar') ?></option>
	</select>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_skip'); ?>"><?php _e('Skip the first <em>n</em> events','my-calendar'); ?></label> <input type="text" id="<?php echo $this->get_field_id('my_calendar_upcoming_skip'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_skip'); ?>" value="<?php echo $widget_skip; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_order'); ?>"><?php _e('Events sort order:','my-calendar'); ?></label> <select id="<?php echo $this->get_field_id('my_calendar_upcoming_order'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_order'); ?>">
	<option value="asc" <?php echo ($widget_order == 'asc')?'selected="selected"':''; ?>><?php _e('Ascending (near to far)','my-calendar') ?></option>
	<option value="desc" <?php echo ($widget_order == 'desc')?'selected="selected"':''; ?>><?php _e('Descending (far to near)','my-calendar') ?></option>
	</select>
	</p>	
	<p>
	<input type="text" id="<?php echo $this->get_field_id('my_calendar_upcoming_after'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_after'); ?>" value="<?php echo $widget_after; ?>" size="1" maxlength="3" /> <label for="<?php echo $this->get_field_id('my_calendar_upcoming_after'); ?>"><?php _e("$widget_type into the future;",'my-calendar'); ?></label><br />
	<input type="text" id="<?php echo $this->get_field_id('my_calendar_upcoming_before'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_before'); ?>" value="<?php echo $widget_before; ?>" size="1" maxlength="3" /> <label for="<?php echo $this->get_field_id('my_calendar_upcoming_after'); ?>"><?php _e("$widget_type from the past",'my-calendar'); ?></label>
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_no_events_text'); ?>"><?php _e('Show this text if there are no events meeting your criteria:','my-calendar'); ?></label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_no_events_text'); ?>" name="<?php echo $this->get_field_name('my_calendar_no_events_text'); ?>" value="<?php echo $widget_text; ?>" /></textarea>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_upcoming_category'); ?>"><?php _e('Category or categories to display:','my-calendar'); ?></label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_upcoming_category'); ?>" name="<?php echo $this->get_field_name('my_calendar_upcoming_category'); ?>" value="<?php echo $widget_category; ?>" /></textarea>
	</p>	
	<?php
}  

	function update($new_instance,$old_instance) {
		$instance = $old_instance;
		$instance['my_calendar_upcoming_title'] = strip_tags($new_instance['my_calendar_upcoming_title']);
		$instance['my_calendar_upcoming_template'] = $new_instance['my_calendar_upcoming_template'];
		$instance['my_calendar_no_events_text'] = strip_tags($new_instance['my_calendar_no_events_text']);
		$instance['my_calendar_upcoming_category'] = strip_tags($new_instance['my_calendar_upcoming_category']);		
		$instance['my_calendar_upcoming_before'] = strip_tags($new_instance['my_calendar_upcoming_before']);
		$instance['my_calendar_upcoming_after'] = strip_tags($new_instance['my_calendar_upcoming_after']);
		$instance['my_calendar_upcoming_type'] = strip_tags($new_instance['my_calendar_upcoming_type']);
		$instance['my_calendar_upcoming_order'] = strip_tags($new_instance['my_calendar_upcoming_order']);
		$instance['my_calendar_upcoming_linked'] = strip_tags($new_instance['my_calendar_upcoming_linked']);
		$instance['my_calendar_upcoming_skip'] = strip_tags($new_instance['my_calendar_upcoming_skip']);
		return $instance;		
	}

}

// Widget upcoming events
function my_calendar_upcoming_events($before='default',$after='default',$type='default',$category='default',$template='default',$substitute='',$order='asc',$skip=0 ) {
  global $wpdb,$default_template,$defaults;
  $output = '';
  $date_format = ( get_option('mc_date_format') != '' )?get_option('mc_date_format'):get_option('date_format');
  
  // This function cannot be called unless calendar is up to date
	check_my_calendar();
	$offset = (60*60*get_option('gmt_offset'));	
    $widget_defaults = get_option('mc_widget_defaults');
	if ( !is_array($widget_defaults) ) { $widget_defaults = array(); } // get globals; check on mc_widget_des
	$display_upcoming_type = ($type == 'default')?$widget_defaults['upcoming']['type']:$type;
	if ($display_upcoming_type == '') { $display_upcoming_type = 'event'; }
    // Get number of units we should go into the future
	$after = ($after == 'default')?$widget_defaults['upcoming']['after']:$after;
	if ($after == '') { $after = 10; }
	// Get number of units we should go into the past
	$before = ($before == 'default')?$widget_defaults['upcoming']['before']:$before;
	if ($before == '') { $before = 0; }
	$category = ($category == 'default')?'':$category;
	$template = ($template == 'default')?$widget_defaults['upcoming']['template']:$template;
	if ($template == '' ) { $template = "$default_template"; };
	$no_event_text = ($substitute == '')?$widget_defaults['upcoming']['text']:$substitute;
    $day_count = -($before);
	$header = "<ul id='upcoming-events'>";
	$footer = "</ul>";	
	$output ='';
	if ($display_upcoming_type == "days") {
		$temp_array = array();
		while ($day_count < $after+1) {
			list($y,$m,$d) = split("-",date("Y-m-d",mktime($day_count*24,0,0,date("m",time()+$offset),date("d",time()+$offset),date("Y",time()+$offset))));
			$events = my_calendar_grab_events( $y,$m,$d,$category );
			$current_date = "$y-$m-$d";
			@usort($events, "my_calendar_time_cmp");
			if (count($events) != 0) {
				foreach($events as $event) {
					$event_details = event_as_array($event);
					$date_diff = jd_date_diff( strtotime($event_details['date']),strtotime($event_details['enddate']));
					$date = date_i18n( $date_format,strtotime($current_date));
					$date_end = date_i18n( $date_format,strtotime(my_calendar_add_date($current_date,$date_diff)));
					$event_details['date'] = $date;
					$event_details['enddate'] = $date_end;
					if ( get_option( 'mc_event_approve' ) == 'true' ) {
						if ( $event->event_approved != 0 ) { $temp_array[] = $event_details; }
					} else {
						$temp_array[] = $event_details;
					}
				}			  
			} 
            $day_count = $day_count+1;
		}
		// By default, skip no events.
		$skipping = false;
		foreach ( $temp_array as $details ) {
			// if any event this date is in the holiday category, we are skipping
			if ( $details['cat_id'] == get_option('mc_skip_holidays_category') ) {
				$skipping = true;
				break;
			}
		}
		// check each event, if we're skipping, only include the holiday events.
		$i = 0;
		foreach ( reverse_array($temp_array, true, $order) as $details ) {
			if ( $i < $skip && $skip != 0 ) {
				$i++;
			} else {
				if ($skipping == true) {
					if ($details['cat_id'] == get_option('mc_skip_holidays_category') ) {
						$output .= "<li>".jd_draw_template($details,$template)."</li>";		  
					} else {
						if ( $details['skip_holiday'] == 'false' ) { // 'true' means "is canceled"
							$output .= "<li>".jd_draw_template($details,$template)."</li>";
						}						
					}
				} else {
					$output .= "<li>".jd_draw_template($details,$template)."</li>";		  
				}
			}
		}
	} else {
        $events = mc_get_all_events($category);		 // grab all events within reasonable proximity
		$output .= mc_produce_upcoming_events( $events,$template,$before,$after,'list',$order,$skip );
	}
	if ($output != '') {
		$output = $header.$output.$footer;
		return $output;
	} else {
		return stripcslashes( $no_event_text );
	}	
}
// make this function time-sensitive, not date-sensitive.
function mc_produce_upcoming_events($events,$template,$before=0,$after=10,$type='list',$order='asc',$skip=0) {
		$output = '';
		$near_events = array();
		$temp_array = array();
		$past = 1;
		$future = 1;
		$offset = (60*60*get_option('gmt_offset'));
		$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));		
         @usort( $events, "my_calendar_timediff_cmp" );// sort all events by proximity to current date
	     $count = count($events);
			for ( $i=0;$i<$count;$i++ ) {
				if ( is_object( $events[$i] ) ) {
					$beginning = $events[$i]->event_begin . ' ' . $events[$i]->event_time;
					$current = date('Y-m-d H:i',time()+$offset);
					if ($events[$i]) {
						if ( ( $past<=$before && $future<=$after ) ) {
							$near_events[] = $events[$i]; // if neither limit is reached, split off freely
						} else if ( $past <= $before && ( my_calendar_date_comp( $beginning,$current ) ) ) {
							$near_events[] = $events[$i]; // split off another past event
						} else if ( $future <= $after && ( !my_calendar_date_comp( $beginning,$current ) ) ) {
							$near_events[] = $events[$i]; // split off another future event
						}				
						if ( my_calendar_date_comp( $beginning,$current ) ) {
							$past++;
						} elseif  ( my_calendar_date_equal( $beginning,$current ) ) {
							$present = 1;
						} else {
							$future++;
						}
						if ($past > $before && $future > $after) {
							break;
						}
					}
				}
			}
		  $events = $near_events;
		  @usort( $events, "my_calendar_datetime_cmp" ); // sort split events by date
		if ( is_array( $events ) ) {
			foreach( $events as $event ) {
				$event_details = event_as_array( $event );
					if ( get_option( 'mc_event_approve' ) == 'true' ) {
						if ( $event->event_approved != 0 ) { $temp_array[] = $event_details; }
					} else {
						$temp_array[] = $event_details;
					}		
			}
		
			// By default, skip no events.
			$skipping = false;
			foreach ( $temp_array as $details ) {
				if ( $details['cat_id'] == get_option('mc_skip_holidays_category') ) {
					$skipping = true;
					break;
				}			
			}
			// check each event, if we're skipping, only include the holiday events.
			$i = 0;
			foreach( reverse_array($temp_array, true, $order) as $details ) {
				$date = date('Y-m-d',strtotime($details['date']));
				if (my_calendar_date_comp( $date,$today )===true) {
					$class = "past-event";
				} else {
					$class = "future-event";
				}
				if ( my_calendar_date_equal( $date,$today ) ) {
					$class = "today";
				}
				if ($type == 'list') {
					$prepend = "<li class=\"$class\">";
					$append = "</li>\n";
				} else {
					$prepend = $append = '';
				}				
				// if any event this date is in the holiday category, we are skipping
		
				if ( $i < $skip && $skip != 0 ) {
					$i++;
				} else {	
					if ($skipping == true) {
						if ($details['cat_id'] == get_option('mc_skip_holidays_category') ) {
							$output .= apply_filters('mc_event_upcoming',"$prepend".jd_draw_template($details,$template,$type)."$append",$event);
						} else {
							if ( $details['skip_holiday'] == 'false' ) { // 'true' means "is canceled"
								$output .= apply_filters('mc_event_upcoming',"$prepend".jd_draw_template($details,$template,$type)."$append",$event); 
							}	
						}
					} else {
						$output .= apply_filters('mc_event_upcoming',"$prepend".jd_draw_template($details,$template,$type)."$append",$event); 	  
					}
				}
			}
		// This may have once been relevant, but I don't see how it is now.
        //$day_count = $day_count+1;
		} else {
			$output .= '';
		}
	return $output;
}

// Widget todays events
function my_calendar_todays_events($category='default',$template='default',$substitute='') {
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
	$header = "<ul id='todays-events'>";
	$footer = "</ul>";		
	$holiday_exists = false;
    @usort($events, "my_calendar_time_cmp");
	// quick loop through all events today to check for holidays
	foreach( $events as $event ) {
		if ( $event->event_category == get_option('mc_skip_holidays_category') ) {	$holiday_exists = true;	}
	}
	
        foreach($events as $event) {
		    $event_details = event_as_array($event);
			$date = date_i18n(get_option('mc_date_format'),time()+$offset);
	
			// correct displayed time to today
			$event_details['date'] = $date;
			$this_event = '';
			if ( $event->event_holiday == 0 ) {
				if ( get_option( 'mc_event_approve' ) == 'true' ) {
					if ( $event->event_approved != 0 ) {$this_event = "<li>".jd_draw_template($event_details,$template)."</li>";}
				} else {
					$this_event = "<li>".jd_draw_template($event_details,$template)."</li>";
				}
			} else {
				// if we found a holiday earlier, then we know there is one today.
				if ( !$holiday_exists || ( $holiday_exists && $event->event_category == get_option('mc_skip_holidays_category') ) ) {
					if ( get_option( 'mc_event_approve' ) == 'true' ) {
						if ( $event->event_approved != 0 ) {$this_event = "<li>".jd_draw_template($event_details,$template)."</li>";}
					} else {
						$this_event = "<li>".jd_draw_template($event_details,$template)."</li>";
					}
				}
			}
			$output .= apply_filters( 'mc_event_today',$this_event,$event );
			
        }
    if (count($events) != 0) {
        return $header.$output.$footer;
    } else {
		return stripcslashes( $no_event_text );
	}
}

class my_calendar_mini_widget extends WP_Widget {

function my_calendar_mini_widget() {
	parent::WP_Widget( false,$name=__('My Calendar: Mini Calendar','my-calendar') );
}

function widget($args, $instance) {
	extract($args);
	if ( !empty($instance) ) {
		$the_title = apply_filters('widget_title',$instance['my_calendar_mini_title']);
		$name = $format = 'mini';
		$category = ($instance['my_calendar_mini_category']=='')?'all':esc_attr($instance['my_calendar_mini_category']);
		$showkey = ($instance['my_calendar_mini_showkey']=='')?'no':esc_attr($instance['my_calendar_mini_showkey']);
		$shownav = ($instance['my_calendar_mini_shownav']=='')?'no':esc_attr($instance['my_calendar_mini_shownav']);
		$time = ($instance['my_calendar_mini_time']=='')?'month':esc_attr($instance['my_calendar_mini_time']);
	} else {
		$the_title = '';
		$name = 'mini';
		$category = '';
		$showkey = '';
		$shownav = '';
		$time = '';
	}

	$widget_title = empty($the_title) ? __('Calendar','my-calendar') : $the_title;
	$widget_title = ($widget_title!='') ? $before_title . $widget_title . $after_title : '';
	
	$the_events = my_calendar( $name,$format,$category,$showkey,$shownav,'no',$time );
		if ($the_events != '') {
		  echo $before_widget;
		  echo $widget_title;
		  echo $the_events;
		  echo $after_widget;
		}
}

function form($instance) {
	$widget_title = esc_attr($instance['my_calendar_mini_title']);
	$widget_key = esc_attr($instance['my_calendar_mini_showkey']);
	$widget_nav = esc_attr($instance['my_calendar_mini_shownav']);
	$widget_time = esc_attr($instance['my_calendar_mini_time']);
	$widget_category = esc_attr($instance['my_calendar_mini_category']);
?>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_mini_title'); ?>"><?php _e('Title','my-calendar'); ?>:</label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_mini_title'); ?>" name="<?php echo $this->get_field_name('my_calendar_mini_title'); ?>" value="<?php echo $widget_title; ?>"/>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_mini_category'); ?>"><?php _e('Category or categories to display:','my-calendar'); ?></label><br />
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('my_calendar_mini_category'); ?>" name="<?php echo $this->get_field_name('my_calendar_mini_category'); ?>" value="<?php echo $widget_category; ?>" /></textarea>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_mini_shownav'); ?>"><?php _e('Show Next/Previous Navigation:','my-calendar'); ?></label> <select id="<?php echo $this->get_field_id('my_calendar_mini_shownav'); ?>" name="<?php echo $this->get_field_name('my_calendar_mini_shownav'); ?>">
	<option value="yes" <?php echo ($widget_nav == 'yes')?'selected="selected"':''; ?>><?php _e('Yes','my-calendar') ?></option>
	<option value="no" <?php echo ($widget_nav == 'no')?'selected="selected"':''; ?>><?php _e('No','my-calendar') ?></option>
	</select>
	</p>	
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_mini_showkey'); ?>"><?php _e('Show Category Key:','my-calendar'); ?></label> <select id="<?php echo $this->get_field_id('my_calendar_mini_showkey'); ?>" name="<?php echo $this->get_field_name('my_calendar_mini_showkey'); ?>">
	<option value="yes" <?php echo ($widget_key == 'yes')?'selected="selected"':''; ?>><?php _e('Yes','my-calendar') ?></option>
	<option value="no" <?php echo ($widget_key == 'no')?'selected="selected"':''; ?>><?php _e('No','my-calendar') ?></option>
	</select>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('my_calendar_mini_time'); ?>"><?php _e('Mini-Calendar Timespan:','my-calendar'); ?></label> <select id="<?php echo $this->get_field_id('my_calendar_mini_time'); ?>" name="<?php echo $this->get_field_name('my_calendar_mini_time'); ?>">
	<option value="month" <?php echo ($widget_time == 'month')?'selected="selected"':''; ?>><?php _e('Month','my-calendar') ?></option>
	<option value="week" <?php echo ($widget_time == 'week')?'selected="selected"':''; ?>><?php _e('Week','my-calendar') ?></option>
	</select>
	</p>	
	<?php
}  

	function update($new_instance,$old_instance) {
		$instance = $old_instance;
		$instance['my_calendar_mini_title'] = strip_tags($new_instance['my_calendar_mini_title']);
		$instance['my_calendar_mini_showkey'] = $new_instance['my_calendar_mini_showkey'];
		$instance['my_calendar_mini_shownav'] = strip_tags($new_instance['my_calendar_mini_shownav']);
		$instance['my_calendar_mini_time'] = strip_tags($new_instance['my_calendar_mini_time']);		
		$instance['my_calendar_mini_category'] = strip_tags($new_instance['my_calendar_mini_category']);		
		return $instance;		
	}

}

?>