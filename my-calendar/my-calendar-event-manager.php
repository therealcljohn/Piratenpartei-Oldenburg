<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'my-calendar-event-manager.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
	die ('Please do not load this page directly. Thanks!');
}

/* 
param = event id, date to split around
return = message confirming successful edits
*/
function mc_split_event( $event_id, $instance ) {
global $wpdb, $users_entries;
	$event = $wpdb->get_results("SELECT * FROM " . my_calendar_table() . " WHERE event_id = $event_id");
	$event = $event[0];
	if ( $event->event_recur == 'S' ) {
		return;
	} else {
		$instance = strtotime( $instance );
		$repeats = $event->event_repeats;
		$repeat_data = mc_increment_event( $event, $instance, false );
		
		$repeats_past = $repeat_data[0];
		$next_date = $repeat_data[1];

		$repeats_future = ($event->event_repeats == 0)?0:($repeats - $repeats_past - 2);
		$recur_type_future = ($repeats_future == 0)?'S':$event->event_recur;
		$recur_type_past = ($repeats_past == 0)?'S':$event->event_recur;
				
		$past_event = $future_event = clone($event); // all objects are identical at this point
		
		$past_event->event_repeats = $repeats_past;
		$past_event->event_recur = $recur_type_past;
		$past_event_array = array( true, $users_entries, mc_convert( $past_event ), '' );
		$message = my_calendar_save( 'add', $past_event_array );

		if ( $next_date ) { // if there is an additional occurrence of this event
			$future_event->event_repeats = $repeats_future;
			$future_event->event_begin = $next_date;
			$future_event->event_recur = $recur_type_future;
				$day_diff = jd_date_diff($event->event_begin, $event->event_end);			
				$next_end = my_calendar_add_date($next_date,abs($day_diff),0,0);
			$future_event->event_end = $next_end; // add diff between begin and end to new begin
			$future_event_array = array( true, $users_entries, mc_convert( $future_event ), '' );
			$message .= my_calendar_save( 'add', $future_event_array );
		}
		return $message;
	}
}

function mc_convert( $object, $edit=false ) {
	$submit = array(
		// strings
			'event_begin'=>$object->event_begin,
			'event_end'=>$object->event_end, 
			'event_title'=>$object->event_title, 
			'event_desc'=>$object->event_desc, 	
			'event_image'=>$object->event_image,
			'event_time'=>$object->event_time, 
			'event_recur'=>$object->event_recur, 
			'event_link'=>$object->event_link,
			'event_label'=>$object->event_label, 
			'event_street'=>$object->event_street, 
			'event_street2'=>$object->event_street2, 
			'event_city'=>$object->event_city, 
			'event_state'=>$object->event_state, 
			'event_postcode'=>$object->event_postcode,
			'event_region'=>$object->event_region,
			'event_country'=>$object->event_country,
			'event_url'=>$object->event_url,				
			'event_endtime'=>$object->event_endtime, 								
			'event_short'=>$object->event_short,
		// integers
			'event_repeats'=>$object->event_repeats, 
			'event_author'=>$object->event_author,
			'event_category'=>$object->event_category, 
			'event_link_expires'=>$object->event_link_expires, 				
			'event_zoom'=>$object->event_zoom,
			'event_open'=>$object->event_open,
			'event_group'=>$object->event_group,
			'event_approved'=>$object->event_approved,
			'event_host'=>$object->event_host,
			'event_flagged'=>$object->event_flagged,
			'event_fifth_week'=>$object->event_fifth_week,
			'event_holiday'=>$object->event_holiday,
			'event_group_id'=>$object->event_group_id,
		// floats
			'event_longitude'=>$object->event_longitude,
			'event_latitude'=>$object->event_latitude,			
		);	
		if ( $edit ) { unset( $submit['event_author'] ); }
	return $submit;
}

function edit_my_calendar() {
    global $current_user, $wpdb, $users_entries;
	
	if ( get_option('ko_calendar_imported') != 'true' ) {  
		if (function_exists('check_calendar')) {
		echo "<div id='message' class='updated'>";
		echo "<p>";
		_e('My Calendar has identified that you have the Calendar plugin by Kieran O\'Shea installed. You can import those events and categories into the My Calendar database. Would you like to import these events?','my-calendar');
		echo "</p>";
		?>
			<form method="post" action="<?php echo admin_url('admin.php?page=my-calendar-config'); ?>">
			<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
			<div>			
			<input type="hidden" name="import" value="true" />
			<input type="submit" value="<?php _e('Import from Calendar','my-calendar'); ?>" name="import-calendar" class="button-primary" />
			</div>
			</form>
		<?php
		echo "<p>";
		_e('Although it is possible that this import could fail to import your events correctly, it should not have any impact on your existing Calendar database. If you encounter any problems, <a href="http://www.joedolson.com/contact.php">please contact me</a>!','my-calendar');
		echo "</p>";
		echo "</div>";
		}
	}

// First some quick cleaning up 
$edit = $create = $save = $delete = false;

$action = !empty($_POST['event_action']) ? $_POST['event_action'] : '';
$event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : '';

if ( isset( $_GET['mode'] ) ) {
	if ( $_GET['mode'] == 'edit' ) {
		$action = "edit";
		$event_id = (int) $_GET['event_id'];
	}
	if ( $_GET['mode'] == 'copy' ) {
		$action = "copy";
		$event_id = (int) $_GET['event_id'];	
	}
}

// Lets see if this is first run and create us a table if it is!
check_my_calendar();

if ( !empty($_POST['mass_delete']) ) {
	$nonce=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
	$events = $_POST['mass_delete'];
	$sql = 'DELETE FROM ' . my_calendar_table() . ' WHERE event_id IN (';	
	$i=0;
	foreach ($events as $value) {
		$value = (int) $value;
		$ea = "SELECT event_author FROM " . my_calendar_table() . " WHERE event_id = $value";
		$result = $wpdb->get_results( $ea, ARRAY_A );
		$total = count($events);
		
		if ( mc_can_edit_event( $result[0]['event_author'] ) ) {
			$sql .= mysql_real_escape_string($value).',';
			$i++;
		}
	}
	$sql = substr( $sql, 0, -1 );
	$sql .= ')';
	$result = $wpdb->query($sql);
	if ( $result !== 0 && $result !== false ) {
		$message = "<div class='updated'><p>".sprintf(__('%1$d events deleted successfully out of %2$d selected','my-calendar'), $i, $total )."</p></div>";
	} else {
		$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__('Your events have not been deleted. Please investigate.','my-calendar')."</p></div>";
	}
	echo $message;
}

if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'delete' ) {
	    $sql = "SELECT event_title, event_author FROM " . my_calendar_table() . " WHERE event_id=" . (int) $_GET['event_id'];
	   $result = $wpdb->get_results( $sql, ARRAY_A );
	if ( mc_can_edit_event( $result[0]['event_author'] ) ) {
	?>
		<div class="error">
		<p><strong><?php _e('Delete Event','my-calendar'); ?>:</strong> <?php _e('Are you sure you want to delete this event?','my-calendar'); ?></p>
		<form action="<?php echo admin_url('admin.php?page=my-calendar'); ?>" method="post">
		<div>
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" />		
		<input type="hidden" value="delete" name="event_action" />
		<?php if ( !empty( $_GET['date'] ) ) { ?>
		<input type="hidden" name="event_instance" value="<?php echo date( 'Y-m-d',strtotime( $_GET['date'] ) ); ?>" />
		<?php } ?>
		<input type="hidden" value="<?php echo (int) $_GET['event_id']; ?>" name="event_id" />
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Delete','my-calendar'); echo " &quot;".$result[0]['event_title']."&quot;"; ?>" />
		</div>
		</form>
		</div>
	<?php
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to delete that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}


// Approve and show an Event ...by Roland
if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'approve' ) {
	if ( current_user_can( get_option( 'mc_event_approve_perms' ) ) ) {
	    $sql = "UPDATE " . my_calendar_table() . " SET event_approved = 1 WHERE event_id=" . (int) $_GET['event_id'];
		$result = $wpdb->get_results( $sql, ARRAY_A );
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to approve that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}

// Reject and hide an Event ...by Roland
if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'reject' ) {
	if ( current_user_can( get_option( 'mc_event_approve_perms' ) ) ) {
	    $sql = "UPDATE " . my_calendar_table() . " SET event_approved = 2 WHERE event_id=" . (int) $_GET['event_id'];
		$result = $wpdb->get_results( $sql, ARRAY_A );
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to reject that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}

if ( isset( $_POST['event_action'] ) ) {
	$nonce=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
	$proceed = false;
	global $mc_output;
	if ( is_array( $_POST['event_begin'] ) ) {
		$count = count($_POST['event_begin']);
	} else {
		$response = my_calendar_save($action,$mc_output,(int) $_POST['event_id']);
		echo $response;
	}
	for ($i=0;$i<$count;$i++) {
	$mc_output = mc_check_data($action,$_POST, $i);
		if ($action == 'add' || $action == 'copy' ) {
			$response = my_calendar_save($action,$mc_output);
		} else {
			if ( isset($_POST['event_instance'] ) && $mc_output[0] == true ) {
				// mc_split_event creates the other newevents, my_calendar_save creates this one (only if proceed is true)
				$events = mc_split_event( (int) $_POST['event_id'], $mc_output[2]['event_begin'] );
			} 
			$response = my_calendar_save($action,$mc_output,(int) $_POST['event_id']);	
		}
		echo $response;
	}
}

?>

<div class="wrap">
<?php 
my_calendar_check_db();
check_akismet();
?>
<?php 
if ( get_site_option('mc_multisite') == 2 ) { 
	if ( get_option('mc_current_table') == 0 ) {
		$message = __('Currently editing your local calendar','my-calendar');
	} else {
		$message = __('Currently editing your central calendar','my-calendar');
	}
	echo "<div class='message updated'><p>$message</p></div>";
} ?>
	<?php
	if ( $action == 'edit' || ($action == 'edit' && $error_with_saving == 1) ) {
		?>
<div id="icon-edit" class="icon32"></div>		
		<h2><?php _e('Edit Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php
		if ( empty($event_id) ) {
			echo "<div class=\"error\"><p>".__("You must provide an event id in order to edit it",'my-calendar')."</p></div>";
		} else {
			jd_events_edit_form('edit', $event_id);
		}		
		jd_events_display_list();					
	} else if ( $action == 'copy' || ($action == 'copy' && $error_with_saving == 1)) { ?>
<div id="icon-edit" class="icon32"></div>	
		<h2><?php _e('Copy Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php
		if ( empty($event_id) ) {
			echo "<div class=\"error\"><p>".__("You must provide an event id in order to edit it",'my-calendar')."</p></div>";
		} else {
			jd_events_edit_form('copy', $event_id);
		}
		jd_events_display_list();		
	} else {
	?>	
<div id="icon-edit" class="icon32"></div>	
		<h2><?php _e('Add Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php jd_events_edit_form(); ?>
		<?php jd_events_display_list(); ?>
	<?php } ?>
</div>
<?php
} 


function my_calendar_save( $action,$output,$event_id=false ) {
global $wpdb,$event_author;
	$proceed = $output[0];
	$message = '';

	if ( ( $action == 'add' || $action == 'copy' ) && $proceed == true ) {
		$add = $output[2]; // add format here
		$formats = array( 
						'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						'%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
						'%f','%f'
						);		
		$result = $wpdb->insert( 
				my_calendar_table(), 
				$add, 
				$formats 
				);
		if ( !$result ) {
			$message = "<div class='error'><p><strong>". __('Error','my-calendar') .":</strong> ". __('I\'m sorry! I couldn\'t add that event to the database.','my-calendar') . "</p></div>";	      
		} else {
	    // Call mail function
			$sql = "SELECT * FROM ". my_calendar_table()." JOIN " . my_calendar_categories_table() . " ON (event_category=category_id) WHERE event_id = ".$wpdb->insert_id;
			$event = $wpdb->get_results($sql);
			$event_start_ts = strtotime( $event[0]->event_begin . ' ' . $event[0]->event_time );
			$event[0]->event_start_ts = $event_start_ts;
			my_calendar_send_email( $event[0] );
			$message = "<div class='updated'><p>". __('Event added. It will now show in your calendar.','my-calendar') . "</p></div>";
		}
	}
	if ( $action == 'edit' && $proceed == true ) {
		$event_author = (int) ($_POST['event_author']);
		if ( mc_can_edit_event( $event_author ) ) {	
			$update = $output[2];
			$formats = array( 
						'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
						'%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
						'%f','%f'
						);
			//$wpdb->show_errors();
			$result = $wpdb->update( 
					my_calendar_table(), 
					$update, 
					array( 'event_id'=>$event_id ),
					$formats, 
					'%d' );
			//$wpdb->print_error();
				if ( $result === false ) {
					$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__('Your event was not updated.','my-calendar')."</p></div>";
				} else if ( $result === 0 ) {
					$message = "<div class='updated'><p>".__('Nothing was changed in that update.','my-calendar')."</p></div>";
				} else {
					$message = "<div class='updated'><p>".__('Event updated successfully','my-calendar')."</p></div>";
				}
		} else {
			$message = "<div class='error'><p><strong>".__('You do not have sufficient permissions to edit that event.','my-calendar')."</strong></p></div>";
		}			
	}

	if ( $action == 'delete' ) {
// Deal with deleting an event from the database
		if ( empty($event_id) )	{
			$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__("You can't delete an event if you haven't submitted an event id",'my-calendar')."</p></div>";
		} else {
			if (isset($_POST['event_instance']) ) {
				$instance = date('Y-m-d',strtotime($_POST['event_instance']) );
				mc_split_event( $event_id, $instance );
			}
			$sql = "DELETE FROM " . my_calendar_table() . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
			$wpdb->query($sql);
			$sql = "SELECT event_id FROM " . my_calendar_table() . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
			$result = $wpdb->get_results($sql);
			if ( empty($result) || empty($result[0]->event_id) ) {
				return "<div class='updated'><p>".__('Event deleted successfully','my-calendar')."</p></div>";
			} else {
				$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__('Despite issuing a request to delete, the event still remains in the database. Please investigate.','my-calendar')."</p></div>";
			}	
		}
	}
	$message = $message ."\n". $output[3];
	return $message;
}

function jd_acquire_form_data($event_id=false) {
global $wpdb,$users_entries;
	if ( $event_id !== false ) {
		if ( intval($event_id) != $event_id ) {
			return "<div class=\"error\"><p>".__('Sorry! That\'s an invalid event key.','my-calendar')."</p></div>";
		} else {
			$data = $wpdb->get_results("SELECT * FROM " . my_calendar_table() . " WHERE event_id='" . mysql_real_escape_string($event_id) . "' LIMIT 1");
			if ( empty($data) ) {
				return "<div class=\"error\"><p>".__("Sorry! We couldn't find an event with that ID.",'my-calendar')."</p></div>";
			}
			$data = $data[0];
		}
		// Recover users entries if there was an error
		if (!empty($users_entries)) {
		    $data = $users_entries;
		}
	} else {
	  // Deal with possibility that form was submitted but not saved due to error - recover user's entries here
	  $data = $users_entries;
	}
	return $data;

}

// The event edit form for the manage events admin page
function jd_events_edit_form($mode='add', $event_id=false) {
	global $wpdb,$users_entries,$user_ID, $output;
	if ($event_id != false) {
		$data = jd_acquire_form_data($event_id);
	} else {
		$data = $users_entries;
	}
?>

	<?php 
	if ( is_object($data) && $data->event_approved != 1 && $mode == 'edit' ) {
		$message = __('This event must be approved in order for it to appear on the calendar.','my-calendar');
	} else {
		$message = "";
	}
	echo ($message != '')?"<div class='error'><p>$message</p></div>":'';
	?>
	<form id="my-calendar" method="post" action="<?php echo admin_url('admin.php?page=my-calendar'); ?>">
	<?php my_calendar_print_form_fields($data,$mode,$event_id); ?>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php _e('Save Event','my-calendar'); ?> &raquo;" />
			</p>
	</form>

<?php
}
/* returns next available group ID */
function mc_group_id() {
	global $wpdb;
	$query = "SELECT MAX(event_id) FROM ".my_calendar_table();
	$result = $wpdb->get_var($query);
	$next = $result+1;
	echo $next;
}

function my_calendar_print_form_fields( $data,$mode,$event_id ) {
	global $user_ID,$wpdb;
	get_currentuserinfo();
	$user = get_userdata($user_ID);		
	$mc_input_administrator = (get_option('mc_input_options_administrators')=='true' && current_user_can('manage_options'))?true:false;
	$mc_input = get_option('mc_input_options');
?>
<div>
<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" />
<input type="hidden" name="event_group_id" value="<?php if ( !empty( $data->event_group_id ) ) { echo $data->event_group_id; } else { mc_group_id(); } ?>" />
<input type="hidden" name="event_action" value="<?php echo $mode; ?>" />
<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
<input type="hidden" name="event_author" value="<?php echo $user_ID; ?>" />
<input type="hidden" name="event_nonce_name" value="<?php echo wp_create_nonce('event_nonce'); ?>" />
</div>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">	
	<div class="inside">
<?php
	if ( !empty( $_GET['date'] ) && $data->event_recur != 'S' ) {
		$date = date_i18n( get_option('mc_date_format'),strtotime($_GET['date']) );
		$ddate = date('Y-m-d',strtotime($_GET['date']) );
		$message = __("You are editing the <strong>$date</strong> instance of this event. Other instances of this event will not be changed.",'my-calendar');
		echo "<div><input type='hidden' name='event_instance' value='$ddate' /></div>";
		echo "<div class='message updated'><p>$message</p></div>";
	} else if ( isset( $_GET['date'] ) && empty( $_GET['date'] ) ) {
		echo "<div class='message updated'><p>".__('There was an error acquiring information about this event instance. The date for this event was not provided. <strong>You are editing this entire recurrence set.</strong>','my-calendar')."</p></div>";
	}
?>
        <fieldset>
		<legend><?php _e('Enter your Event Information','my-calendar'); ?></legend>
		<p>
		<label for="event_title"><?php _e('Event Title','my-calendar'); ?><span><?php _e('(required)','my-calendar'); ?></span></label> <input type="text" id="event_title" name="event_title" class="input" size="60" value="<?php if ( !empty($data) ) echo stripslashes(esc_attr($data->event_title)); ?>" />
<?php if ( $mode == 'edit' ) { ?>
	<?php if ( get_option( 'mc_event_approve' ) == 'true' ) { ?>
		<?php if ( current_user_can( get_option('mc_event_approve_perms') ) ) { // (Added by Roland P. ?>
				<input type="checkbox" value="1" id="event_approved" name="event_approved"<?php if ( !empty($data) && $data->event_approved == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_approved == '0' ) { echo ""; } else if ( get_option( 'mc_event_approve' ) == 'true' ) { echo "checked=\"checked\""; } ?> /> <label for="event_approved"><?php _e('Publish','my-calendar'); ?><?php if ($event->event_approved != 1) { ?> <small>[<?php _e('You must approve this event to promote it to the calendar.','my-calendar'); ?>]</small> <?php } ?></label>
		<?php } else { // case: editing, approval enabled, user cannot approve ?>
				<input type="hidden" value="0" name="event_approved" /><?php _e('An administrator must approve your new event.','my-calendar'); ?>
		<?php } ?> 
	<?php } else { // Case: editing, approval system is disabled - auto approve ?>	
				<input type="hidden" value="1" name="event_approved" />
	<?php } ?>
<?php } else { // case: adding new event (if use can, then 1, else 0) ?>
<?php if ( current_user_can( get_option('mc_event_approve_perms') ) ) { $dvalue = 1; } else { $dvalue = 0; } ?>
			<input type="hidden" value="<?php echo $dvalue; ?>" name="event_approved" />
<?php } ?>
		</p>
		<?php if (  is_object($data) && $data->event_flagged == 1 ) { ?>
		<div class="error">
		<p>
		<input type="checkbox" value="0" id="event_flagged" name="event_flagged"<?php if ( !empty($data) && $data->event_flagged == '0' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_flagged == '1' ) { echo ""; } ?> /> <label for="event_flagged"><?php _e('This event is not spam','my-calendar'); ?></label>
		</p>
		</div>
		<?php } ?>
		<?php if ($mc_input['event_desc'] == 'on' || $mc_input_administrator ) { ?>
		<p>
		<?php if ( !empty($data) ) { $description = $data->event_desc; } else { $description = ''; } ?>
		<label for="content"><?php _e('Event Description (<abbr title="hypertext markup language">HTML</abbr> allowed)','my-calendar'); ?></label><br /><?php if ( $mc_input['event_use_editor'] == 'on' ) {  the_editor( stripslashes($description) ); }  else { ?><textarea id="content" name="content" class="event_desc" rows="5" cols="80"><?php echo stripslashes(esc_attr($description)); ?></textarea><?php if ( $mc_input['event_use_editor'] == 'on' ) { ?></div><?php } } ?>
		</p>
		<?php } ?>
		<?php 
		// If the editor is enabled, shouldn't display the image uploader. 
		// It restricts use of the image uploader to a single image and forces it to be in 
		// the event image field, rather than the event description.
		if ( !isset($mc_input['event_image']) ) { $mc_input['event_image'] = 'off'; }	
		if ( ( $mc_input['event_image'] == 'on' && $mc_input['event_use_editor'] != 'on' ) || ( $mc_input_administrator && $mc_input['event_use_editor'] != 'on' ) ) { ?>
		<p>
		<?php if ( !empty($data->event_image) ) { ?>
		<div class="event_image"><?php _e("This event's image:",'my-calendar'); ?><br /><img src="<?php if ( !empty($data) ) echo esc_attr($data->event_image); ?>" alt="" /></div>
		<?php } ?>
		<label for="event_image"><?php _e("Add an image:",'my-calendar'); ?></label> <input type="text" name="event_image" id="event_image" size="60" value="<?php if ( !empty($data) ) echo esc_attr($data->event_image); ?>" /> <input id="upload_image_button" type="button" class="button" value="<?php _e('Upload Image','my-calendar'); ?>" /><br /><?php _e('Include your image URL or upload an image.','my-calendar'); ?>
		</p>
		<?php } else { ?>
		<div>
		<input type="hidden" name="event_image" value="<?php if ( !empty($data) ) echo esc_attr($data->event_image); ?>" />
		<?php if ( !empty($data->event_image) ) { ?>
		<div class="event_image"><?php _e("This event's image:",'my-calendar'); ?><br /><img src="<?php echo esc_attr($data->event_image); ?>" alt="" /></div>
		<?php } ?>
		</div>
		<?php } ?>		
		<?php if ($mc_input['event_short'] == 'on' || $mc_input_administrator ) { ?>
		<p>
		<label for="event_short"><?php _e('Event Short Description (<abbr title="hypertext markup language">HTML</abbr> allowed)','my-calendar'); ?></label><br /><textarea id="event_short" name="event_short" class="input" rows="2" cols="80"><?php if ( !empty($data) ) echo stripslashes(esc_attr($data->event_short)); ?></textarea>
		</p>
		<?php } ?>
	<p>
	<label for="event_host"><?php _e('Event Host','my-calendar'); ?></label>
	<select id="event_host" name="event_host">
		<?php 
			 // Grab all the categories and list them
			$users = my_calendar_getUsers();				 
			foreach($users as $u) {
			 echo '<option value="'.$u->ID.'"';
					if (  is_object($data) && $data->event_host == $u->ID ) {
					 echo ' selected="selected"';
					} else if(  is_object($u) && $u->ID == $user->ID && empty($data->event_host) ) {
				    echo ' selected="selected"';
					}
				echo '>'.$u->display_name."</option>\n";
			}
		?>
	</select>
	</p>			
		<?php if ($mc_input['event_category'] == 'on' || $mc_input_administrator ) { ?>
        <p>
		<label for="event_category"><?php _e('Event Category','my-calendar'); ?></label>
		<select id="event_category" name="event_category">
			<?php
			// Grab all the categories and list them
			$sql = "SELECT * FROM " . my_calendar_categories_table();
				$cats = $wpdb->get_results($sql);
				foreach($cats as $cat) {
					echo '<option value="'.$cat->category_id.'"';
					if (!empty($data)) {
						if ($data->event_category == $cat->category_id){
						 echo 'selected="selected"';
						}
					}
					echo '>'.stripslashes($cat->category_name).'</option>';
				}
			?>
			</select>
            </p>
			<?php } else { ?>
			<div>
			<input type="hidden" name="event_category" value="1" />
			</div>
			<?php } ?>
			<?php if ($mc_input['event_link'] == 'on' || $mc_input_administrator ) { ?>
			<p>
			<label for="event_link"><?php _e('Event Link (Optional)','my-calendar'); ?></label> <input type="text" id="event_link" name="event_link" class="input" size="40" value="<?php if ( !empty($data) ) { echo esc_url($data->event_link); } ?>" /> <input type="checkbox" value="1" id="event_link_expires" name="event_link_expires"<?php if ( !empty($data) && $data->event_link_expires == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_link_expires == '0' ) { echo ""; } else if ( get_option( 'mc_event_link_expires' ) == 'true' ) { echo " checked=\"checked\""; } ?> /> <label for="event_link_expires"><?php _e('This link will expire when the event passes.','my-calendar'); ?></label>
			</p>
			<?php } ?>
			</fieldset>
</div>
</div>
<div class="postbox">	
<div class="inside">
			<fieldset><legend><?php _e('Event Date and Time','my-calendar'); ?></legend>
			<p><em><?php _e('Enter the beginning and ending information for this occurrence of the event.','my-calendar'); ?></em></p>
			<div id="event1" class="clonedInput">
			<?php
			if ( !empty($data) ) {
				$event_begin = esc_attr($data->event_begin); 
				$event_end = esc_attr($data->event_end);
				if ( !empty($_GET['date'] ) ) {
					$event_begin = date( 'Y-m-d', strtotime( $_GET['date'] ) );
					$day_diff = jd_date_diff($data->event_begin, $data->event_end);	
					$event_end = my_calendar_add_date($event_begin,$day_diff,0,0);	
				} 
			} else { 
				$event_begin = date_i18n("Y-m-d");
				$event_end = '';
			}
			?>
			<p>
			<label for="event_begin"><?php _e('Start Date (YYYY-MM-DD)','my-calendar'); ?> <span><?php _e('(required)','my-calendar'); ?></span></label> <input type="text" id="event_begin" name="event_begin[]" class="calendar_input" size="12" value="<?php echo $event_begin; ?>" /> <label for="event_time"><?php _e('Time (hh:mm am/pm)','my-calendar'); ?></label> <input type="text" id="event_time" name="event_time[]" class="input" size="12"	value="<?php 
					$offset = (60*60*get_option('gmt_offset'));
					if ( !empty($data) ) {
						echo ($data->event_time == "00:00:00")?'':date("h:ia",strtotime($data->event_time));
					} else {
						echo date_i18n("h:ia",time()+$offset);
					}?>" /> 
			</p>
			<p>
			<label for="event_end"><?php _e('End Date (YYYY-MM-DD)','my-calendar'); ?></label> <input type="text" name="event_end[]" id="event_end" class="calendar_input" size="12" value="<?php echo $event_end; ?>" /> <label for="event_endtime"><?php _e('End Time (hh:mm am/pm)','my-calendar'); ?></label> <input type="text" id="event_endtime" name="event_endtime[]" class="input" size="12" value="<?php
					if ( !empty($data) ) {
						echo ($data->event_endtime == "00:00:00")?'':date("h:ia",strtotime($data->event_endtime));
					} else {
						echo '';
					}?>" /> 
			</p>
			</div>
			<?php if ( !( isset($_GET['mode']) && $_GET['mode'] == 'edit' ) ) { ?>
			<div>
				<input type="button" id="add_field" value="<?php _e('Add another occurrence','my-calendar'); ?>" class="button" />
				<input type="button" id="del_field" value="<?php _e('Remove last occurrence','my-calendar'); ?>" class="button" />
			</div>
			<?php } ?>
			<p>
			<?php _e('Current time difference from GMT is ','my-calendar'); echo get_option('gmt_offset'); _e(' hour(s)', 'my-calendar'); ?>
			</p> 
			</fieldset>
</div>
</div>
			<?php if ( ( $mc_input['event_recurs'] == 'on' || $mc_input_administrator ) && empty( $_GET['date'] ) ) { ?>
<div class="postbox">
<div class="inside">
			<fieldset>
			<legend><?php _e('Recurring Events','my-calendar'); ?></legend> 
			<?php if (  is_object($data) && $data->event_repeats != NULL ) { $repeats = $data->event_repeats; } else { $repeats = 0; } ?>
			<p>
			<label for="event_repeats"><?php _e('Repeats for','my-calendar'); ?></label> <input type="text" name="event_repeats" id="event_repeats" class="input" size="1" value="<?php echo $repeats; ?>" /> 
			<label for="event_recur"><?php _e('Units','my-calendar'); ?></label> <select name="event_recur" class="input" id="event_recur">
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'S','option'); ?> value="S"><?php _e('Does not recur','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'D','option'); ?> value="D"><?php _e('Daily','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'E','option'); ?> value="E"><?php _e('Daily, weekdays only','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'W','option'); ?> value="W"><?php _e('Weekly','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'B','option'); ?> value="B"><?php _e('Bi-weekly','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'M','option'); ?> value="M"><?php _e('Date of Month (e.g., the 24th of each month)','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'U','option'); ?> value="U"><?php _e('Day of Month (e.g., the 3rd Monday of each month)','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'Y','option'); ?> value="Y"><?php _e('Annually','my-calendar'); ?></option>
			</select><br />
					<?php _e('Enter "0" if the event should recur indefinitely. Your entry is the number of events after the first occurrence of the event: a recurrence of <em>2</em> means the event will happen three times.','my-calendar'); ?>
			</p>
			</fieldset>	
</div>
</div>				
			<?php } else { ?>
			<div>
			<input type="hidden" name="event_repeats" value="0" />
			<input type="hidden" name="event_recur" value="S" />
			</div>
		
			<?php } ?>

			<?php if ($mc_input['event_open'] == 'on' || $mc_input_administrator ) { ?>			
<div class="postbox">
<div class="inside">
			<fieldset>
			<legend><?php _e('Event Registration Status','my-calendar'); ?></legend>
			<p><em><?php _e('My Calendar does not manage event registrations. Use this for information only.','my-calendar'); ?></em></p>
			<p>
			<input type="radio" id="event_open" name="event_open" value="1" <?php if (!empty($data)) { echo jd_option_selected( $data->event_open,'1'); } ?> /> <label for="event_open"><?php _e('Open','my-calendar'); ?></label> 
			<input type="radio" id="event_closed" name="event_open" value="0" <?php if (!empty($data)) {  echo jd_option_selected( $data->event_open,'0'); } ?> /> <label for="event_closed"><?php _e('Closed','my-calendar'); ?></label>
			<input type="radio" id="event_none" name="event_open" value="2" <?php if (!empty($data)) { echo jd_option_selected( $data->event_open, '2' ); } else { echo " checked='checked'"; } ?> /> <label for="event_none"><?php _e('Does not apply','my-calendar'); ?></label>	
			</p>	
			<p>
			<input type="checkbox" name="event_group" id="event_group" <?php if (  is_object($data) ) { echo jd_option_selected( $data->event_group,'1'); } ?> /> <label for="event_group"><?php _e('If this event recurs, it can only be registered for as a complete series.','my-calendar'); ?></label>
			</p>				
			</fieldset>
</div>
</div>			
			<?php } else { ?>
			<div>
			<input type="hidden" name="event_open" value="2" />
			</div>

			<?php } ?>

			<?php if ( ($mc_input['event_location'] == 'on' || $mc_input['event_location_dropdown'] == 'on') || $mc_input_administrator ) { ?>

<div class="postbox">
<div class="inside">
			<fieldset>
			<legend><?php _e('Event Location','my-calendar'); ?></legend>
			<?php } ?>
			<?php if ($mc_input['event_location_dropdown'] == 'on' || $mc_input_administrator ) { ?>
			<?php $locations = $wpdb->get_results("SELECT location_id,location_label FROM " . my_calendar_locations_table() . " ORDER BY location_label ASC");
				if ( !empty($locations) ) {
			?>				
			<p>
			<label for="location_preset"><?php _e('Choose a preset location:','my-calendar'); ?></label> <select name="location_preset" id="location_preset">
				<option value="none"> -- </option>
				<?php
				foreach ( $locations as $location ) {
					$selected = ($data->event_label == $location->location_label)?" selected='selected'":'';
					echo "<option value=\"".$location->location_id."\"$selected>".stripslashes($location->location_label)."</option>";
				}
?>
			</select>
			</p>
<?php
				} else {
				?>
				<input type="hidden" name="location_preset" value="none" />
				<p><a href="<?php echo admin_url('admin.php?page=my-calendar-locations'); ?>"><?php _e('Add recurring locations for later use.','my-calendar'); ?></a></p>
				<?php
				}
			?>
			<?php } else { ?>
				<input type="hidden" name="location_preset" value="none" />			
			<?php } ?>
			<?php if ($mc_input['event_location'] == 'on' || $mc_input_administrator ) { ?>			
			<p>
			<?php _e('All location fields are optional: <em>insufficient information may result in an inaccurate map</em>.','my-calendar'); ?>
			</p>
			<p>
			<label for="event_label"><?php _e('Name of Location (e.g. <em>Joe\'s Bar and Grill</em>)','my-calendar'); ?></label> 
			<?php if ( mc_controlled_field( 'label' ) ) {
				if ( !empty( $data ) ) $cur_label = ( stripslashes( $data->event_label ) );			
				echo mc_location_controller( 'label', $cur_label );
			} else { ?>
			<input type="text" id="event_label" name="event_label" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_label)); ?>" />
			<?php } ?>
			</p>
			<p>
			<label for="event_street"><?php _e('Street Address','my-calendar'); ?></label> <input type="text" id="event_street" name="event_street" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_street)); ?>" />
			</p>
			<p>
			<label for="event_street2"><?php _e('Street Address (2)','my-calendar'); ?></label> <input type="text" id="event_street2" name="event_street2" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_street2)); ?>" />
			</p>
			<p>
			<label for="event_city"><?php _e('City','my-calendar'); ?></label> 
			<?php if ( mc_controlled_field( 'city' ) ) {
				if ( !empty( $data ) ) $cur_label = ( stripslashes( $data->event_city ) );			
				echo mc_location_controller( 'city', $cur_label );
			} else { ?>
			<input type="text" id="event_city" name="event_city" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_city)); ?>" /> 
			<?php } ?>
			<label for="event_state"><?php _e('State/Province','my-calendar'); ?></label> 
			<?php if ( mc_controlled_field( 'state' ) ) {
				if ( !empty( $data ) ) $cur_label = ( stripslashes( $data->event_state ) );			
				echo mc_location_controller( 'state', $cur_label );
			} else { ?>
			<input type="text" id="event_state" name="event_state" class="input" size="10" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_state)); ?>" /> 
			<?php } ?>
			<label for="event_postcode"><?php _e('Postal Code','my-calendar'); ?></label> 
			<?php if ( mc_controlled_field( 'postcode' ) ) {
			if ( !empty( $data ) ) $cur_label = ( stripslashes( $data->event_postcode ) );			
				echo mc_location_controller( 'postcode', $cur_label );
			} else { ?>
			<input type="text" id="event_postcode" name="event_postcode" class="input" size="10" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_postcode)); ?>" />
			<?php } ?>
			</p>
			<p>
			<label for="event_region"><?php _e('Region','my-calendar'); ?></label> 
			<?php if ( mc_controlled_field( 'region' ) ) {			
			if ( !empty( $data ) ) $cur_label = ( stripslashes( $data->event_region ) );			
				echo mc_location_controller( 'region', $cur_label );
			} else { ?>
			<input type="text" id="event_region" name="event_region" class="input" size="40" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_region)); ?>" />
			<?php } ?>
			</p>
			<p>		
			<label for="event_country"><?php _e('Country','my-calendar'); ?></label> 
			<?php if ( mc_controlled_field( 'country' ) ) {			
			if ( !empty( $data ) ) $cur_label = ( stripslashes( $data->event_country ) );			
				echo mc_location_controller( 'country', $cur_label );
			} else { ?>
			<input type="text" id="event_country" name="event_country" class="input" size="10" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_country)); ?>" />
			<?php } ?>
			</p>
			<p>
			<label for="event_zoom"><?php _e('Initial Zoom','my-calendar'); ?></label> 
				<select name="event_zoom" id="event_zoom">
				<option value="16"<?php if ( !empty( $data ) && ( $data->event_zoom == 16 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Neighborhood','my-calendar'); ?></option>
				<option value="14"<?php if ( !empty( $data ) && ( $data->event_zoom == 14 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Small City','my-calendar'); ?></option>
				<option value="12"<?php if ( !empty( $data ) && ( $data->event_zoom == 12 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Large City','my-calendar'); ?></option>
				<option value="10"<?php if ( !empty( $data ) && ( $data->event_zoom == 10 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Greater Metro Area','my-calendar'); ?></option>
				<option value="8"<?php if ( !empty( $data ) && ( $data->event_zoom == 8 ) ) { echo " selected=\"selected\""; } ?>><?php _e('State','my-calendar'); ?></option>
				<option value="6"<?php if ( !empty( $data ) && ( $data->event_zoom == 6 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Region','my-calendar'); ?></option>
				</select>
			</p>
			<p>
			<label for="event_url"><?php _e('Location URL','my-calendar'); ?></label> <input type="text" id="event_url" name="event_url" class="input" size="40" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_url)); ?>" />
			</p>			
			<fieldset>
			<legend><?php _e('GPS Coordinates (optional)','my-calendar'); ?></legend>
			<p>
			<small><?php _e('If you supply GPS coordinates for your location, they will be used in place of any other address information to provide your map link.','my-calendar'); ?></small>
			</p>
			<p>
			<label for="event_latitude"><?php _e('Latitude','my-calendar'); ?></label> <input type="text" id="event_latitude" name="event_latitude" class="input" size="10" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_latitude)); ?>" /> <label for="event_longitude"><?php _e('Longitude','my-calendar'); ?></label> <input type="text" id="event_longitude" name="event_longitude" class="input" size="10" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_longitude)); ?>" />
			</p>			
			</fieldset>	
			<?php } ?>
			<?php if ( ( $mc_input['event_location'] == 'on' || $mc_input['event_location_dropdown'] == 'on' ) || $mc_input_administrator ) { ?>
			</fieldset>
		</div>
		</div>
			<?php } ?>

<div class="postbox">
<div class="inside">		
			<fieldset>
			<legend><?php _e('Special Options','my-calendar'); ?></legend>
			<p>
			<label for="event_holiday"><?php _e('Cancel this event if it occurs on a date with an event in the Holidays category','my-calendar'); ?></label> <input type="checkbox" value="true" id="event_holiday" name="event_holiday"<?php if ( !empty($data) && $data->event_holiday == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_holiday == '0' ) { echo ""; } else if ( get_option( 'mc_skip_holidays' ) == 'true' ) { echo " checked=\"checked\""; } ?> />
			</p>
			<p>
			<label for="event_fifth_week"><?php _e('If this event recurs, and falls on the 5th week of the month in a month with only four weeks, move it back one week.','my-calendar'); ?></label> <input type="checkbox" value="true" id="event_fifth_week" name="event_fifth_week"<?php if ( !empty($data) && $data->event_fifth_week == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_fifth_week == '0' ) { echo ""; } else if ( get_option( 'mc_no_fifth_week' ) == 'true' ) { echo " checked=\"checked\""; } ?> />
			</p>
			</fieldset>
		</div>
		</div>
</div>
<?php }

// Used on the manage events admin page to display a list of events
function jd_events_display_list( $type='normal' ) {
	global $wpdb;
	
		$sortby = ( isset( $_GET['sort'] ) )?(int) $_GET['sort']:get_option('mc_default_sort');

		if ( isset( $_GET['order'] ) ) {
			$sortdir = ( isset($_GET['order']) && $_GET['order'] == 'ASC' )?'ASC':'default';
		} else {
			$sortdir = 'default';
		}
		if ( isset( $_GET['limit'] ) ) {
			switch ($_GET['limit']) {
				case 'reserved':$status = 'reserved';
				break;
				case 'published':$status = 'published';
				break;
				default:
				$status = 'all';
				break;
			}
		} else {
			$status = 'all';
		}
	
	if ( empty($sortby) ) {
		$sortbyvalue = 'event_begin';
	} else {
		switch ($sortby) {
		    case 1:$sortbyvalue = 'event_ID';
			break;
			case 2:$sortbyvalue = 'event_title';
			break;
			case 3:$sortbyvalue = 'event_desc';
			break;
			case 4:$sortbyvalue = 'event_begin';
			break;
			case 5:$sortbyvalue = 'event_author';
			break;
			case 6:$sortbyvalue = 'event_category';
			break;
			case 7:$sortbyvalue = 'event_label';
			break;
			default:$sortbyvalue = 'event_begin';
		}
	}
	if ($sortdir == 'default') {
		$sortbydirection = 'DESC';
	} else {
		$sortbydirection = $sortdir;
	}
	
	switch ($status) {
		case 'all':$limit = '';
		break;
		case 'reserved':$limit = 'WHERE event_approved = 0';
		break;
		case 'published':$limit = 'WHERE event_approved = 1';
		break;
		default:$limit = '';
	}
	$events = $wpdb->get_results("SELECT * FROM " . my_calendar_table() . " $limit ORDER BY $sortbyvalue $sortbydirection");
	if ($sortbydirection == 'DESC') {
		$sorting = "&amp;order=ASC";
	} else {
		$sorting = '';
	}
	?>
	<h2><?php _e('Manage Events','my-calendar'); ?></h2>
		<?php if ( get_option('mc_event_approve') == 'true' ) { ?>
		<ul class="links">
		<li><a <?php echo ($_GET['limit']=='published')?' class="active-link"':''; ?> href="<?php echo admin_url('admin.php?page=my-calendar&amp;limit=published'); ?>"><?php _e('Published','my-calendar'); ?></a></li>
		<li><a <?php echo ($_GET['limit']=='reserved')?' class="active-link"':''; ?>  href="<?php echo admin_url('admin.php?page=my-calendar&amp;limit=reserved'); ?>"><?php _e('Reserved','my-calendar'); ?></a></li> 
		<li><a <?php echo ($_GET['limit']=='all' || !isset($_GET['limit']))?' class="active-link"':''; ?>  href="<?php echo admin_url('admin.php?page=my-calendar&amp;limit=all'); ?>"><?php _e('All','my-calendar'); ?></a></li>
		</ul>
		<?php } ?>	
	<?php
	if ( !empty($events) ) {
		?>
		<form action="<?php echo admin_url('admin.php?page=my-calendar'); ?>" method="post">
		<div>
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" />
		</div>
<table class="widefat page fixed" id="my-calendar-admin-table" summary="<?php _e('Table of Calendar Events','my-calendar'); ?>">
	<thead>
	<tr>
		<th class="manage-column n4" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=1$sorting"); ?>"><?php _e('ID','my-calendar') ?></a></th>
		<th class="manage-column" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=2$sorting"); ?>"><?php _e('Title','my-calendar') ?></a></th>
		<th class="manage-column n1" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=7$sorting"); ?>"><?php _e('Location','my-calendar') ?></a></th>
		<th class="manage-column n8" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=3$sorting"); ?>"><?php _e('Description','my-calendar') ?></a></th>
		<th class="manage-column n5" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=4$sorting"); ?>"><?php _e('Start Date','my-calendar') ?></a></th>
		<th class="manage-column n6" scope="col"><?php _e('Recurs','my-calendar') ?></th>
		<th class="manage-column n3" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=5$sorting"); ?>"><?php _e('Author','my-calendar') ?></a></th>
		<th class="manage-column n2" scope="col"><a href="<?php echo admin_url("admin.php?page=my-calendar&amp;sort=6$sorting"); ?>"><?php _e('Category','my-calendar') ?></a></th>
		<th class="manage-column n7" scope="col"><?php _e('Edit / Delete','my-calendar') ?></th>
	</tr>
	</thead>
		<?php
		$class = '';
		$sql = "SELECT * FROM " . my_calendar_categories_table() ;
        $categories = $wpdb->get_results($sql);
			
		foreach ( $events as $event ) {
			$class = ($class == 'alternate') ? '' : 'alternate';
			$spam = ($event->event_flagged == 1) ? ' spam' : '';
			$spam_label = ($event->event_flagged == 1) ? '<strong>Possible spam:</strong> ' : '';
			$author = get_userdata($event->event_author);
			if ($event->event_link != '') { 
			$title = "<a href='".esc_attr($event->event_link)."'>$event->event_title</a>";
			} else {
			$title = $event->event_title;
			}
			?>
			<tr class="<?php echo $class; echo $spam; ?>">
				<th scope="row"><input type="checkbox" value="<?php echo $event->event_id; ?>" name="mass_delete[]" id="mc<?php echo $event->event_id; ?>" <?php echo ($event->event_flagged == 1)?' checked="checked"':''; ?> /> <label for="mc<?php echo $event->event_id; ?>"><?php echo $event->event_id; ?></label></th>
				<td><?php echo $spam_label; echo stripslashes($title); ?></td>
				<td><?php echo stripslashes($event->event_label); ?></td>
				<td><?php echo substr(strip_tags(stripslashes($event->event_desc)),0,60); ?>&hellip;</td>
				<?php if ($event->event_time != "00:00:00") { $eventTime = date_i18n(get_option('mc_time_format'), strtotime($event->event_time)); } else { $eventTime = get_option('mc_notime_text'); } ?>
				<td><?php echo "$event->event_begin, $eventTime"; ?></td>
				<?php /* <td><?php echo $event->event_end; ?></td> */ ?>
				<td>
				<?php 
					// Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { _e('Never','my-calendar'); } 
					else if ($event->event_recur == 'D') { _e('Daily','my-calendar'); }
					else if ($event->event_recur == 'E') { _e('Weekdays','my-calendar'); }
					else if ($event->event_recur == 'W') { _e('Weekly','my-calendar'); }
					else if ($event->event_recur == 'B') { _e('Bi-Weekly','my-calendar'); }
					else if ($event->event_recur == 'M') { _e('Monthly (by date)','my-calendar'); }
					else if ($event->event_recur == 'U') { _e('Monthly (by day)','my-calendar'); }
					else if ($event->event_recur == 'Y') { _e('Yearly','my-calendar'); }
				?>&ndash;<?php
					if ($event->event_recur == 'S') { _e('N/A','my-calendar'); }
					else if ( mc_event_repeats_forever( $event->event_recur, $event->event_repeats ) ) { _e('Forever','my-calendar'); }
					else if ( $event->event_repeats > 0 ) { printf( __('%d Times','my-calendar'),$event->event_repeats); }					
				?>				
				</td>
				<td><?php echo $author->display_name; ?></td>
                                <?php
								$this_category = $event->event_category;
								foreach ($categories as $key=>$value) {
									if ($value->category_id == $this_category) {
										$this_cat = $categories[$key];
									} 
								}
                                ?>
				<td><div class="category-color" style="background-color:<?php echo (strpos($this_cat->category_color,'#') !== 0)?'#':''; echo $this_cat->category_color;?>;"> </div> <?php echo stripslashes($this_cat->category_name); ?></td>
				<?php unset($this_cat); ?>
				<td>
				<a href="<?php echo admin_url("admin.php?page=my-calendar&amp;mode=copy&amp;event_id=$event->event_id"); ?>" class='copy'><?php _e('Copy','my-calendar'); ?></a> &middot; 
				<?php if ( mc_can_edit_event( $event->event_author ) ) { ?>
				<a href="<?php echo admin_url("admin.php?page=my-calendar&amp;mode=edit&amp;event_id=$event->event_id"); ?>" class='edit'><?php _e('Edit','my-calendar'); ?></a> <?php if ( mc_event_is_grouped( $event->event_group_id ) ) { ?>
				&middot; <a href="<?php echo admin_url("admin.php?page=my-calendar-groups&amp;mode=edit&amp;event_id=$event->event_id&amp;group_id=$event->event_group_id"); ?>" class='edit group'><?php _e('Edit Group','my-calendar'); ?></a>
				<?php } ?>
				&middot; <a href="<?php echo admin_url("admin.php?page=my-calendar&amp;mode=delete&amp;event_id=$event->event_id"); ?>" class="delete"><?php _e('Delete','my-calendar'); ?></a>
				<?php } else { _e("Not editable.",'my-calendar'); } ?>
				<?php if ( get_option( 'mc_event_approve' ) == 'true' ) { ?>
				 &middot; 
						<?php if ( current_user_can( get_option('mc_event_approve_perms') ) ) { // Added by Roland P.?>
							<?php	// by Roland 
							if ( $event->event_approved == '1' )  { ?>
								<a href="<?php echo admin_url("admin.php?page=my-calendar&amp;mode=reject&amp;event_id=$event->event_id"); ?>" class='reject'><?php _e('Reject','my-calendar'); ?></a>
							<?php } else { 	?>
								<a href="<?php echo admin_url("admin.php?page=my-calendar&amp;mode=approve&amp;event_id=$event->event_id"); ?>" class='publish'><?php _e('Approve','my-calendar'); ?></a>		
							<?php } ?>
						<?php } else { ?>
							<?php	// by Roland 
							if ( $event->event_approved == '1' )  { ?>
								<?php _e('Approved','my-calendar'); ?>
							<?php } else if ($event->event_approved == '2' ) { 	?>
								<?php _e('Rejected','my-calendar'); ?>							
							<?php } else { ?>
								<?php _e('Awaiting Approval','my-calendar'); ?>		
							<?php } ?>
						<?php } ?>	
				<?php } ?>					
				</td>	
			</tr>
<?php
		}
?>
		</table>
		<p>
		<input type="submit" class="button-primary delete" value="<?php _e('Delete checked events','my-calendar'); ?>" />
		</p>
		</form>
<?php
/* LATER
		if ( get_option('mc_admin_calendar') == 'on' ) {
			echo do_shortcode("[my_calendar]");
		}
*/
	} else {
?>
		<p><?php _e("There are no events in the database!",'my-calendar') ?></p>
<?php	
	}
}

function mc_event_is_grouped( $group_id ) {
	global $wpdb;
	if ( $group_id == 0 ) { return false; } else {
	$query = "SELECT count( event_group_id ) FROM ".my_calendar_table()." WHERE event_group_id = $group_id";
	$value = $wpdb->get_var($query);
		if ( $value > 1 ) {
			return true;
		} else {
			return false;
		}
	}
}

function mc_check_data($action,$_POST, $i) {
global $wpdb, $current_user, $users_entries;

$start_date_ok = 0;
$end_date_ok = 0;
$time_ok = 0;
$endtime_ok = 0;
$url_ok = 0;
$title_ok = 0;
$recurring_ok = 0;
$submit=array();

if ( get_magic_quotes_gpc() ) {
    $_POST = array_map( 'stripslashes_deep', $_POST );
}

if (!wp_verify_nonce($_POST['event_nonce_name'],'event_nonce')) {
	return;
}

$errors = "";
if ( $action == 'add' || $action == 'edit' || $action == 'copy' ) {
	$title = !empty($_POST['event_title']) ? trim($_POST['event_title']) : '';
	$desc = !empty($_POST['content']) ? trim($_POST['content']) : '';
	$short = !empty($_POST['event_short']) ? trim($_POST['event_short']) : '';
	$recur = !empty($_POST['event_recur']) ? trim($_POST['event_recur']) : '';
	// if this is a all weekdays event, and it's been scheduled to start on a weekend, the math gets nasty. 
	// ...AND there's no reason to allow it, since weekday events will NEVER happen on the weekend.
	if ( $recur == 'E' && ( date( 'w', strtotime( $_POST['event_begin'][$i] ) ) == 0 || date( 'w', strtotime( $_POST['event_begin'][$i] ) ) == 6 ) ) {
		if ( date( 'w', strtotime( $_POST['event_begin'][$i] ) ) == 0 ) {
			$newbegin = my_calendar_add_date( $_POST['event_begin'][$i], 1 );
			if ( !empty( $_POST['event_end'][$i] ) ) {
				$newend = my_calendar_add_date( $_POST['event_end'][$i], 1 );
			} else {
				$newend = $newbegin;
			}
		} else if ( date( 'w', strtotime( $_POST['event_begin'][$i] ) ) == 6 ) {
			$newbegin = my_calendar_add_date( $_POST['event_begin'][$i], 2 );
			if ( !empty( $_POST['event_end'][$i] ) ) {
				$newend = my_calendar_add_date( $_POST['event_end'][$i], 2 );
			} else {
				$newend = $newbegin;
			}		
		}
		$begin = $newbegin;
		$end = $newend;
	} else {
		$begin = !empty($_POST['event_begin'][$i]) ? trim($_POST['event_begin'][$i]) : '';
		$end = !empty($_POST['event_end'][$i]) ? trim($_POST['event_end'][$i]) : $begin;
	}
	$time = !empty($_POST['event_time'][$i]) ? trim($_POST['event_time'][$i]) : '';
	$endtime = !empty($_POST['event_endtime'][$i]) ? trim($_POST['event_endtime'][$i]) : '';
	$repeats = !empty($_POST['event_repeats']) ? trim($_POST['event_repeats']) : 0;
	$host = !empty($_POST['event_host']) ? $_POST['event_host'] : $current_user->ID;	
	$category = !empty($_POST['event_category']) ? $_POST['event_category'] : '';
    $linky = !empty($_POST['event_link']) ? trim($_POST['event_link']) : '';
    $expires = !empty($_POST['event_link_expires']) ? $_POST['event_link_expires'] : '0';
    $approved = !empty($_POST['event_approved']) ? $_POST['event_approved'] : '0';
	$location_preset = !empty($_POST['location_preset']) ? $_POST['location_preset'] : '';
    $event_author = !empty($_POST['event_author']) ? $_POST['event_author'] : $current_user->ID;
	$event_open = !empty($_POST['event_open']) ? $_POST['event_open'] : '2';
	$event_group = !empty($_POST['event_group']) ? 1 : 0;
	$event_flagged = ( !isset($_POST['event_flagged']) || $_POST['event_flagged']===0 )?0:1;
	$event_image = esc_url_raw( $_POST['event_image'] );
	$event_fifth_week = !empty($_POST['event_fifth_week']) ? 1 : 0;
	$event_holiday = !empty($_POST['event_holiday']) ? 1 : 0;
	// get group id: if multiple events submitted, auto group OR if event being submitted is already part of a group; otherwise zero.
		$group_id_submitted = (int) $_POST['event_group_id'];
	$event_group_id = ( ( is_array($_POST['event_begin']) && count($_POST['event_begin'])>1 ) || mc_event_is_grouped( $group_id_submitted) )?$group_id_submitted:0;
	// set location
		if ($location_preset != 'none') {
			$sql = "SELECT * FROM " . my_calendar_locations_table() . " WHERE location_id = $location_preset";
			$location = $wpdb->get_row($sql);
			$event_label = $location->location_label;
			$event_street = $location->location_street;
			$event_street2 = $location->location_street2;
			$event_city = $location->location_city;
			$event_state = $location->location_state;
			$event_postcode = $location->location_postcode;
			$event_region = $location->location_region;
			$event_country = $location->location_country;
			$event_url = $location->location_url;			
			$event_longitude = $location->location_longitude;
			$event_latitude = $location->location_latitude;
			$event_zoom = $location->location_zoom;
		} else {
			$event_label = !empty($_POST['event_label']) ? $_POST['event_label'] : '';
			$event_street = !empty($_POST['event_street']) ? $_POST['event_street'] : '';
			$event_street2 = !empty($_POST['event_street2']) ? $_POST['event_street2'] : '';
			$event_city = !empty($_POST['event_city']) ? $_POST['event_city'] : '';
			$event_state = !empty($_POST['event_state']) ? $_POST['event_state'] : '';
			$event_postcode = !empty($_POST['event_postcode']) ? $_POST['event_postcode'] : '';
			$event_region = !empty($_POST['event_region']) ? $_POST['event_region'] : '';
			$event_country = !empty($_POST['event_country']) ? $_POST['event_country'] : '';
			$event_url = !empty($_POST['event_url']) ? $_POST['event_url'] : '';			
			$event_longitude = !empty($_POST['event_longitude']) ? $_POST['event_longitude'] : '';	
			$event_latitude = !empty($_POST['event_latitude']) ? $_POST['event_latitude'] : '';	
			$event_zoom = !empty($_POST['event_zoom']) ? $_POST['event_zoom'] : '';	
	    }
	// Perform some validation on the submitted dates - this checks for valid years and months
	$date_format_one = '/^([0-9]{4})-([0][1-9])-([0-3][0-9])$/';
    $date_format_two = '/^([0-9]{4})-([1][0-2])-([0-3][0-9])$/';
	if ((preg_match($date_format_one,$begin) || preg_match($date_format_two,$begin)) && (preg_match($date_format_one,$end) || preg_match($date_format_two,$end))) {
        // We know we have a valid year and month and valid integers for days so now we do a final check on the date
        $begin_split = split('-',$begin);
	    $begin_y = $begin_split[0]; 
	    $begin_m = $begin_split[1];
	    $begin_d = $begin_split[2];
        $end_split = split('-',$end);
	    $end_y = $end_split[0];
	    $end_m = $end_split[1];
	    $end_d = $end_split[2];
        if (checkdate($begin_m,$begin_d,$begin_y) && checkdate($end_m,$end_d,$end_y)) {
		// Ok, now we know we have valid dates, we want to make sure that they are either equal or that the end date is later than the start date
			if (strtotime($end) >= strtotime($begin)) {
			$start_date_ok = 1;
			$end_date_ok = 1;
			} else {
				$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('Your event end date must be either after or the same as your event begin date','my-calendar')."</p></div>";
			}
		} else {
				$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('Your date formatting is correct but one or more of your dates is invalid. Check for number of days in month and leap year related errors.','my-calendar')."</p></div>";
		}
	} else {
		$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('Both start and end dates must be in the format YYYY-MM-DD','my-calendar')."</p></div>";
	}
        // We check for a valid time, or an empty one
		$time = ($time == '')?'00:00:00':date( 'H:i:00',strtotime($time) );
        $time_format_one = '/^([0-1][0-9]):([0-5][0-9]):([0-5][0-9])$/';
		$time_format_two = '/^([2][0-3]):([0-5][0-9]):([0-5][0-9])$/';
        if (preg_match($time_format_one,$time) || preg_match($time_format_two,$time) || $time == '') {
            $time_ok = 1;
        } else {
			$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The time field must either be blank or be entered in the format hh:mm','my-calendar')."</p></div>";
	    }
        // We check for a valid end time, or an empty one
		$endtime = ($endtime == '')?'00:00:00':date( 'H:i:00',strtotime($endtime) );
        if (preg_match($time_format_one,$endtime) || preg_match($time_format_two,$endtime) || $endtime == '') {
            $endtime_ok = 1;
        } else {
            $errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The end time field must either be blank or be entered in the format hh:mm','my-calendar')."</p></div>";
	    }		
		// We check to make sure the URL is acceptable (blank or starting with http://)                                                        
		if ($linky == '') {
			$url_ok = 1;
		} else if ( preg_match('/^(http)(s?)(:)\/\//',$linky) ) {
			$url_ok = 1;
		} else {
			$linky = "http://" . $linky;
		}
	}
	// The title must be at least one character in length and no more than 255 - only basic punctuation is allowed
	$title_length = strlen($title);
	if ( $title_length > 1 && $title_length <= 255 ) {
	    $title_ok =1;
	} else {
		$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The event title must be between 1 and 255 characters in length.','my-calendar')."</p></div>";
	}
	// We run some checks on recurrance                                                                        
	if (( $repeats == 0 && $recur == 'S' ) || (($repeats >= 0) && ($recur == 'W' || $recur == 'B' || $recur == 'M' || $recur == 'U' || $recur == 'Y' || $recur == 'D' || $recur == 'E' ))) {
	    $recurring_ok = 1;
	} else {
		$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The repetition value must be 0 unless a type of recurrence is selected.','my-calendar')."</p></div>";
	}
	if ($start_date_ok == 1 && $end_date_ok == 1 && $time_ok == 1 && $endtime_ok == 1 && $url_ok == 1 && $title_ok == 1 && $recurring_ok == 1) {
		$proceed = true;
		$submit = array(
		// strings
			'event_begin'=>$begin, 
			'event_end'=>$end, 
			'event_title'=>$title, 
			'event_desc'=>$desc, 			
			'event_short'=>$short,
			'event_time'=>$time,
			'event_endtime'=>$endtime, 				
			'event_link'=>$linky,
			'event_label'=>$event_label, 
			'event_street'=>$event_street, 
			'event_street2'=>$event_street2, 
			'event_city'=>$event_city, 
			'event_state'=>$event_state, 
			'event_postcode'=>$event_postcode,
			'event_region'=>$event_region,
			'event_country'=>$event_country,
			'event_url'=>$event_url,				
			'event_recur'=>$recur, 
			'event_image'=>$event_image,
		// integers
			'event_repeats'=>$repeats, 
			'event_author'=>$current_user->ID,
			'event_category'=>$category, 		
			'event_link_expires'=>$expires, 				
			'event_zoom'=>$event_zoom,
			'event_open'=>$event_open,
			'event_group'=>$event_group,
			'event_approved'=>$approved,
			'event_host'=>$host,
			'event_flagged'=> mc_akismet( $linky, $desc ),
			'event_fifth_week'=>$event_fifth_week,
			'event_holiday'=>$event_holiday,
			'event_group_id'=>$event_group_id,
		// floats
			'event_longitude'=>$event_longitude,
			'event_latitude'=>$event_latitude			
			);
		if ($action == 'edit') { unset( $submit['event_author'] ); }
	} else {
	    // The form is going to be rejected due to field validation issues, so we preserve the users entries here
		$users_entries->event_title = $title;
		$users_entries->event_desc = $desc;
		$users_entries->event_begin = $begin;
		$users_entries->event_end = $end;
		$users_entries->event_time = $time;
		$users_entries->event_endtime = $endtime;
		$users_entries->event_recur = $recur;
		$users_entries->event_repeats = $repeats;
		$users_entries->event_host = $host;
		$users_entries->event_category = $category;
		$users_entries->event_link = $linky;
		$users_entries->event_link_expires = $expires;
		$users_entries->event_label = $event_label;
		$users_entries->event_street = $event_street;
		$users_entries->event_street2 = $event_street2;
		$users_entries->event_city = $event_city;
		$users_entries->event_state = $event_state;
		$users_entries->event_postcode = $event_postcode;
		$users_entries->event_country = $event_country;	
		$users_entries->event_region = $event_region;
		$users_entries->event_url = $event_url;
		$users_entries->event_longitude = $event_longitude;		
		$users_entries->event_latitude = $event_latitude;		
		$users_entries->event_zoom = $event_zoom;
		$users_entries->event_author = $event_author;
		$users_entries->event_open = $event_open;
		$users_entries->event_short = $short;
		$users_entries->event_group = $event_group;
		$users_entries->event_approved = $approved;
		$users_entries->event_image = $event_image;
		$users_entries->event_fifth_week = $event_fifth_week;
		$users_entries->event_holiday = $event_holiday;
		$users_entries->event_flagged = 0;
		$users_entries->event_group_id = $event_group_id;
		$proceed = false;
	}
	$data = array($proceed, $users_entries, $submit,$errors);
	return $data;
}
?>