<?php
// Display the admin configuration page
function my_calendar_import() {
	if ( get_option('ko_calendar_imported') != 'true' ) {
	global $wpdb;
		define('KO_CALENDAR_TABLE', $wpdb->prefix . 'calendar');
		define('KO_CALENDAR_CATS', $wpdb->prefix . 'calendar_categories');
		$events = $wpdb->get_results("SELECT * FROM " . KO_CALENDAR_TABLE, 'ARRAY_A');
		$sql = "";
		foreach ($events as $key) {
			$title = mysql_real_escape_string($key['event_title']);
			$desc = mysql_real_escape_string($key['event_desc']);
			$begin = mysql_real_escape_string($key['event_begin']);
			$end = mysql_real_escape_string($key['event_end']);
			$time = mysql_real_escape_string($key['event_time']);
			$recur = mysql_real_escape_string($key['event_recur']);
			$repeats = mysql_real_escape_string($key['event_repeats']);
			$author = mysql_real_escape_string($key['event_author']);
			$category = mysql_real_escape_string($key['event_category']);
			$linky = mysql_real_escape_string($key['event_link']);
		    $sql = "INSERT INTO " . my_calendar_table() . " SET 
			event_title='" . ($title) . "', 
			event_desc='" . ($desc) . "', 
			event_begin='" . ($begin) . "', 
			event_end='" . ($end) . "', 
			event_time='" . ($time) . "', 
			event_recur='" . ($recur) . "', 
			event_repeats='" . ($repeats) . "', 
			event_author=".($author).", 
			event_category=".($category).", 
			event_link='".($linky)."';
			";
			$events_results = $wpdb->query($sql);		
		}	
		$cats = $wpdb->get_results("SELECT * FROM " . KO_CALENDAR_CATS, 'ARRAY_A');	
		$catsql = "";
		foreach ($cats as $key) {
			$name = mysql_real_escape_string($key['category_name']);
			$color = mysql_real_escape_string($key['category_colour']);
			$id = mysql_real_escape_string($key['category_id']);
			$catsql = "INSERT INTO " . my_calendar_categories_table() . " SET 
				category_id='".$id."',
				category_name='".$name."', 
				category_color='".$color."' 
				ON DUPLICATE KEY UPDATE 
				category_name='".$name."', 
				category_color='".$color."';
				";	
			$cats_results = $wpdb->query($catsql);
			//$wpdb->print_error(); 			
		}			
		$message = ( $cats_results !== false )?__('Categories imported successfully.','my-calendar'):__('Categories not imported.','my-calendar');
		$e_message = ( $events_results !== false )?__('Events imported successfully.','my-calendar'):__('Events not imported.','my-calendar');
		$return = "<div id='message' class='updated fade'><ul><li>$message</li><li>$e_message</li></ul></div>";
		echo $return;
		if ( $cats_results !== false && $events_results !== false ) {
			update_option( 'ko_calendar_imported','true' );
		}
	} 
}

function edit_my_calendar_config() {
	global $wpdb,$default_user_settings;
	// We can't use this page unless My Calendar is installed/upgraded
	check_my_calendar();
	if (!empty($_POST)) {
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");  
	}
   if (isset($_POST['permissions'])) {
		// management
		$new_perms = $_POST['permissions'];
		$mc_event_approve = ( !empty($_POST['mc_event_approve']) && $_POST['mc_event_approve']=='on')?'true':'false';
		$mc_event_approve_perms = $_POST['mc_event_approve_perms'];
		$mc_event_edit_perms = $_POST['mc_event_edit_perms'];
		update_option('mc_event_approve_perms',$mc_event_approve_perms);
		update_option('mc_event_approve',$mc_event_approve);
		update_option('mc_can_manage_events',$new_perms);	  	
		update_option('mc_event_edit_perms',$mc_event_edit_perms);
		
		if ( get_site_option('mc_multisite') == 2 ) {
			$mc_current_table = (int) $_POST['mc_current_table'];
			update_option('mc_current_table',$mc_current_table);
		}
		
		echo "<div class='updated'><p><strong>".__('Permissions Settings saved','my-calendar').".</strong></p></div>";
	}
 // output
	if (isset($_POST['mc_show_months']) ) {
		$mc_title_template = $_POST['mc_title_template'];
		$mc_details_label = $_POST['mc_details_label'];
		$mc_link_label = $_POST['mc_link_label'];
		$templates = get_option('mc_templates');
		$templates['title'] = $mc_title_template;
		$templates['label'] = $mc_details_label;
		$templates['link'] = $mc_link_label;
		update_option('mc_uri',$_POST['mc_uri'] );
		update_option('mc_skip_holidays_category',(int) $_POST['mc_skip_holidays_category']);
		update_option('mc_skip_holidays',( !empty($_POST['mc_skip_holidays']) && $_POST['mc_skip_holidays']=='on')?'true':'false');
		update_option('mc_templates',$templates);
		update_option('mc_display_author',( !empty($_POST['mc_display_author']) && $_POST['mc_display_author']=='on')?'true':'false');
		update_option('mc_show_event_vcal',( !empty($_POST['mc_show_event_vcal']) && $_POST['mc_show_event_vcal']=='on')?'true':'false');		
		update_option('mc_display_jump',( !empty($_POST['mc_display_jump']) && $_POST['mc_display_jump']=='on')?'true':'false');
		update_option('mc_show_list_info',( !empty($_POST['mc_show_list_info']) && $_POST['mc_show_list_info']=='on')?'true':'false');		
		update_option('mc_show_months',(int) $_POST['mc_show_months']);
		update_option('mc_date_format',$_POST['mc_date_format']);
		update_option('mc_week_format',$_POST['my_calendar_week_format']);
		update_option('mc_time_format',$_POST['mc_time_format']);
		update_option('mc_show_map',( !empty($_POST['mc_show_map']) && $_POST['mc_show_map']=='on')?'true':'false');
		update_option('mc_show_address',( !empty($_POST['mc_show_address']) && $_POST['mc_show_address']=='on')?'true':'false'); 
		update_option('mc_hide_icons',( !empty($_POST['mc_hide_icons']) && $_POST['mc_hide_icons']=='on')?'true':'false');
		update_option('mc_event_link_expires',( !empty($_POST['mc_event_link_expires']) && $_POST['mc_event_link_expires']=='on')?'true':'false');
		update_option('mc_apply_color',$_POST['mc_apply_color']);
		update_option('mc_event_registration',( !empty($_POST['mc_event_registration']) && $_POST['mc_event_registration']=='on')?'true':'false');
		update_option('mc_short',( !empty($_POST['mc_short']) && $_POST['mc_short']=='on')?'true':'false');
		update_option('mc_desc',( !empty($_POST['mc_desc']) && $_POST['mc_desc']=='on')?'true':'false');
		update_option('mc_details',( !empty($_POST['mc_details']) && $_POST['mc_details']=='on')?'true':'false');
		update_option('mc_show_weekends',( !empty($_POST['mc_show_weekends']) && $_POST['mc_show_weekends']=='on')?'true':'false');
		update_option('mc_no_fifth_week',( !empty($_POST['mc_no_fifth_week']) && $_POST['mc_no_fifth_week']=='on')?'true':'false');
		update_option('mc_show_rss',( !empty($_POST['mc_show_rss']) && $_POST['mc_show_rss']=='on')?'true':'false');
		update_option('mc_show_ical',( !empty($_POST['mc_show_ical']) && $_POST['mc_show_ical']=='on')?'true':'false');
		update_option('mc_default_sort',$_POST['mc_default_sort']);
		// styles (output)
		echo "<div class=\"updated\"><p><strong>".__('Output Settings saved','my-calendar').".</strong></p></div>";
	}
	// input
	if (isset($_POST['mc_input'])) {
		$mc_input_options_administrators = ( !empty($_POST['mc_input_options_administrators']) && $_POST['mc_input_options_administrators']=='on')?'true':'false'; 
		$mc_input_options = array(
			'event_short'=>( !empty($_POST['mci_event_short']) && $_POST['mci_event_short'])?'on':'',
			'event_desc'=>( !empty($_POST['mci_event_desc']) && $_POST['mci_event_desc'])?'on':'',
			'event_category'=>( !empty($_POST['mci_event_category']) && $_POST['mci_event_category'])?'on':'',
			'event_image'=>( !empty($_POST['mci_event_image']) && $_POST['mci_event_image'])?'on':'',
			'event_link'=>( !empty($_POST['mci_event_link']) && $_POST['mci_event_link'])?'on':'',
			'event_recurs'=>( !empty($_POST['mci_event_recurs']) && $_POST['mci_event_recurs'])?'on':'',
			'event_open'=>( !empty($_POST['mci_event_open']) && $_POST['mci_event_open'])?'on':'',
			'event_location'=>( !empty($_POST['mci_event_location']) && $_POST['mci_event_location'])?'on':'',
			'event_location_dropdown'=>( !empty($_POST['mci_event_location_dropdown']) && $_POST['mci_event_location_dropdown'])?'on':'',
			'event_use_editor'=>( !empty($_POST['mci_event_use_editor']) && $_POST['mci_event_use_editor'])?'on':''
			);
		update_option('mc_input_options',$mc_input_options);
		update_option('mc_input_options_administrators',$mc_input_options_administrators);	
		echo "<div class=\"updated\"><p><strong>".__('Input Settings saved','my-calendar').".</strong></p></div>";
	}
	if ( current_user_can('manage_network') ) {
		if ( isset($_POST['mc_network']) ) {
			$mc_multisite = (int) $_POST['mc_multisite'];
			update_site_option('mc_multisite',$mc_multisite );
			echo "<div class=\"updated\"><p><strong>".__('Multsite settings saved','my-calendar').".</strong></p></div>";
		}
	}
	// custom text
	if (isset( $_POST['mc_previous_events'] ) ) {
		$mc_notime_text = $_POST['mc_notime_text'];
		$mc_previous_events = $_POST['mc_previous_events'];
		$mc_next_events = $_POST['mc_next_events'];
		$mc_event_open = $_POST['mc_event_open'];
		$mc_event_closed = $_POST['mc_event_closed'];
		$my_calendar_caption = $_POST['my_calendar_caption'];
		update_option('mc_notime_text',$mc_notime_text);
		update_option('mc_next_events',$mc_next_events);
		update_option('mc_previous_events',$mc_previous_events);	
		update_option('mc_caption',$my_calendar_caption);
		update_option('mc_event_open',$mc_event_open);
		update_option('mc_event_closed',$mc_event_closed);
		echo "<div class=\"updated\"><p><strong>".__('Custom text settings saved','my-calendar').".</strong></p></div>";	 
	}
	// Mail function by Roland
	if (isset($_POST['mc_email']) ) {
		$mc_event_mail = ( !empty($_POST['mc_event_mail']) && $_POST['mc_event_mail']=='on')?'true':'false';
		$mc_event_mail_to = $_POST['mc_event_mail_to'];
		$mc_event_mail_subject = $_POST['mc_event_mail_subject'];
		$mc_event_mail_message = $_POST['mc_event_mail_message'];
		update_option('mc_event_mail_to',$mc_event_mail_to);
		update_option('mc_event_mail_subject',$mc_event_mail_subject);
		update_option('mc_event_mail_message',$mc_event_mail_message);
		update_option('mc_event_mail',$mc_event_mail);
		echo "<div class=\"updated\"><p><strong>".__('Email notice settings saved','my-calendar').".</strong></p></div>";
	}
	// Custom User Settings
	if (isset($_POST['mc_user'])) {
		$mc_user_settings_enabled = ( !empty($_POST['mc_user_settings_enabled']) && $_POST['mc_user_settings_enabled']=='on')?'true':'false';
		$mc_location_type = $_POST['mc_location_type'];
		$mc_user_settings = $_POST['mc_user_settings'];
		$mc_user_settings['my_calendar_tz_default']['values'] = csv_to_array($mc_user_settings['my_calendar_tz_default']['values']);
		$mc_user_settings['my_calendar_location_default']['values'] = csv_to_array($mc_user_settings['my_calendar_location_default']['values']);
		$mc_location_control = ( isset( $_POST['mc_location_control'] ) && $_POST['mc_location_control'] == 'on' )?'on':'';
		update_option( 'mc_location_control',$mc_location_control );
		update_option( 'mc_location_type',$mc_location_type );
		update_option( 'mc_user_settings_enabled',$mc_user_settings_enabled );
		update_option( 'mc_user_settings',$mc_user_settings );  
		echo "<div class=\"updated\"><p><strong>".__('User custom settings saved','my-calendar').".</strong></p></div>";
	}
	// Pull known values out of the options table
	$allowed_group = get_option('mc_can_manage_events');
	$mc_show_months = get_option('mc_show_months');
	$mc_show_map = get_option('mc_show_map');
	$mc_show_address = get_option('mc_show_address');
	$disp_author = get_option('mc_display_author');
	$mc_event_link_expires = get_option('mc_event_link_expires');
	$mc_event_mail = get_option('mc_event_mail');
	$mc_event_mail_to = get_option('mc_event_mail_to');
	$mc_event_mail_subject = get_option('mc_event_mail_subject');
	$mc_event_mail_message = get_option('mc_event_mail_message');
	$mc_event_approve = get_option('mc_event_approve');
	$mc_event_approve_perms = get_option('mc_event_approve_perms');
	$disp_jump = get_option('mc_display_jump');
	$mc_no_fifth_week = get_option('mc_no_fifth_week');
	$templates = get_option('mc_templates');
	$mc_title_template = $templates['title'];
	$mc_details_label = $templates['label'];
	$mc_link_label = $templates['link'];
	$mc_uri = get_option('mc_uri');
?>
    <div class="wrap">
<?php 
my_calendar_check_db();
check_akismet();
?>
    <div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('My Calendar Options','my-calendar'); ?></h2>
    <?php jd_show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('Calendar Management Settings','my-calendar'); ?></h3>
	<div class="inside">	
    <form id="my-calendar-manage" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>    
	<fieldset>
    <legend><?php _e('Calendar Options: Management','my-calendar'); ?></legend>
    <ul>
    <li><label for="permissions"><?php _e('Lowest user group that may create events','my-calendar'); ?></label> <select id="permissions" name="permissions">
		<option value="read"<?php echo jd_option_selected( get_option('mc_can_manage_events'),'read','option'); ?>><?php _e('Subscriber','my-calendar')?></option>
		<option value="edit_posts"<?php echo jd_option_selected(get_option('mc_can_manage_events'),'edit_posts','option'); ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="publish_posts"<?php echo jd_option_selected(get_option('mc_can_manage_events'),'publish_posts','option'); ?>><?php _e('Author','my-calendar')?></option>
		<option value="moderate_comments"<?php echo jd_option_selected(get_option('mc_can_manage_events'),'moderate_comments','option'); ?>><?php _e('Editor','my-calendar')?></option>
		<option value="manage_options"<?php echo jd_option_selected(get_option('mc_can_manage_events'),'manage_options','option'); ?>><?php _e('Administrator','my-calendar')?></option>
	</select>
	</li>
    <li>
    <label for="mc_event_approve_perms"><?php _e('Lowest user group that may approve events','my-calendar'); ?></label> <select id="mc_event_approve_perms" name="mc_event_approve_perms">
		<option value="read"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'read','option'); ?>><?php _e('Subscriber','my-calendar')?></option>
		<option value="edit_posts"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'edit_posts','option'); ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="publish_posts"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'publish_posts','option'); ?>><?php _e('Author','my-calendar')?></option>
		<option value="moderate_comments"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'moderate_comments','option'); ?>><?php _e('Editor','my-calendar')?></option>
		<option value="manage_options"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'manage_options','option'); ?>><?php _e('Administrator','my-calendar')?></option>
	</select> <input type="checkbox" id="mc_event_approve" name="mc_event_approve" <?php jd_cal_checkCheckbox('mc_event_approve','true'); ?> /> <label for="mc_event_approve"><?php _e('Enable approval options.','my-calendar'); ?></label>
	</li>
    <li>
    <label for="mc_event_edit_perms"><?php _e('Lowest user group that may edit or delete all events','my-calendar'); ?></label> <select id="mc_event_edit_perms" name="mc_event_edit_perms">
		<option value="edit_posts"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'edit_posts','option'); ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="publish_posts"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'publish_posts','option'); ?>><?php _e('Author','my-calendar')?></option>
		<option value="moderate_comments"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'moderate_comments','option'); ?>><?php _e('Editor','my-calendar')?></option>
		<option value="manage_options"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'manage_options','option'); ?>><?php _e('Administrator','my-calendar')?></option>
	</select><br />
	<em><?php _e('By default, only administrators may edit or delete any event. Other users may only edit or delete events which they authored.','my-calendar'); ?></em>
	</li>
	<?php if ( get_site_option('mc_multisite') == 2 && MY_CALENDAR_TABLE != MY_CALENDAR_GLOBAL_TABLE ) { ?>
	<li>
	<input type="radio" name="mc_current_table" id="mc0" value="0"<?php echo jd_option_selected(get_option('mc_current_table'),0); ?> /> <label for="mc0"><?php _e('Currently editing my local calendar','my-calendar'); ?></label>
	</li>
	<li>
	<input type="radio" name="mc_current_table" id="mc1" value="1"<?php echo jd_option_selected(get_option('mc_current_table'),1); ?> /> <label for="mc1"><?php _e('Currently editing the network calendar','my-calendar'); ?></label>
	</li>
	<?php } else { ?>
	<li><?php _e('You are currently working in the primary site for this network; your local calendar is also the global table.','my-calendar'); ?></li>
	<?php } ?>
	</ul>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Approval Settings','my-calendar'); ?> &raquo;" />
		</p>
	</form>
	</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Text Settings','my-calendar'); ?></h3>
	<div class="inside">
	    <form id="my-calendar-text" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>		
<fieldset>
	<legend><?php _e('Calendar Options: Customizable Text Fields','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="mc_notime_text"><?php _e('Label for events without a set time','my-calendar'); ?></label> <input type="text" id="mc_notime_text" name="mc_notime_text" value="<?php if ( get_option('mc_notime_text') == "") { _e('N/A','my-calendar'); } else { echo esc_attr( stripslashes( get_option('mc_notime_text') ) ); } ?>" />
	</li>
	<li>
	<label for="mc_previous_events"><?php _e('Previous events link','my-calendar'); ?></label> <input type="text" id="mc_previous_events" name="mc_previous_events" value="<?php if ( get_option('mc_previous_events') == "") { _e('Previous Events','my-calendar'); } else { echo esc_attr( stripslashes( get_option('mc_previous_events') ) ); } ?>" />
	</li>
	<li>
	<label for="mc_next_events"><?php _e('Next events link','my-calendar'); ?></label> <input type="text" id="mc_next_events" name="mc_next_events" value="<?php if ( get_option('mc_next_events') == "") { _e('Next Events','my-calendar'); } else { echo esc_attr(  stripslashes( get_option('mc_next_events') ) ); } ?>" />
	</li>
	<li>
	<label for="mc_event_open"><?php _e('If events are open','my-calendar'); ?></label> <input type="text" id="mc_event_open" name="mc_event_open" value="<?php if ( get_option('mc_event_open') == "") { _e('Registration is open','my-calendar'); } else { echo esc_attr( stripslashes( get_option('mc_event_open') ) ); } ?>" />
	</li>
	<li>
	<label for="mc_event_closed"><?php _e('If events are closed','my-calendar'); ?></label> <input type="text" id="mc_event_closed" name="mc_event_closed" value="<?php if ( get_option('mc_event_closed') == "") { _e('Registration is closed','my-calendar'); } else { echo esc_attr( stripslashes( get_option('mc_event_closed') ) ); } ?>" />
	</li>	
	<li>
	<label for="my_calendar_caption"><?php _e('Additional caption:','my-calendar'); ?></label> <input type="text" id="my_calendar_caption" name="my_calendar_caption" value="<?php echo esc_attr( stripslashes( get_option('mc_caption') ) ); ?>" /><br /><small><?php _e('The calendar caption is the text containing the displayed month and year in either list or calendar format. This text will be displayed following that existing text.','my-calendar'); ?></small>
	</li>
	</ul>
	</fieldset>	
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Custom Text Settings','my-calendar'); ?> &raquo;" />
	</p>
	</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Output Settings','my-calendar'); ?></h3>
	<div class="inside">
 <form id="my-calendar-output" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<fieldset>
	<legend><?php _e('Calendar Options: Customize the Output of your Calendar','my-calendar'); ?></legend>
	<fieldset>
	<legend><?php _e('General Calendar Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="mc_uri"><?php _e('<abbr title="Uniform resource locator">URL</abbr> to use for event details display.','my-calendar'); ?></label> 
	<input type="text" name="mc_uri" id="mc_uri" size="40" value="<?php echo esc_url($mc_uri); ?>" /><br /><small><?php _e('Can be any Page or Post which includes the <code>[my_calendar]</code> shortcode.','my-calendar'); ?> <?php mc_guess_calendar(); ?></small>
	</li>
	<li>
	<label for="mc_time_format"><?php _e('Time format','my-calendar'); ?></label> <input type="text" id="mc_time_format" name="mc_time_format" value="<?php if ( get_option('mc_time_format')  == "") { echo ''; } else { echo esc_attr( stripslashes( get_option( 'mc_time_format') ) ); } ?>" /> <?php _e('Current:','my-calendar'); ?> <?php if ( get_option('mc_time_format') == '') { echo date_i18n( get_option('time_format') ); } else { echo date_i18n(get_option('mc_time_format')); } ?>
	</li>	
	<li>
	<label for="mc_week_format"><?php _e('Date format in grid mode, week view','my-calendar'); ?></label> <input type="text" id="mc_week_format" name="my_calendar_week_format" value="<?php if ( get_option('mc_week_format')  == "") { echo ''; } else { echo esc_attr( stripslashes( get_option( 'mc_week_format') ) ); } ?>" /> <?php _e('Current:','my-calendar'); ?> <?php if ( get_option('mc_week_format') == '') { echo date_i18n('M j, \'y'); } else { echo date_i18n(get_option('mc_week_format')); } ?>
	</li>	
	<li>
	<label for="mc_date_format"><?php _e('Date Format in all other views','my-calendar'); ?></label> <input type="text" id="mc_date_format" name="mc_date_format" value="<?php if ( get_option('mc_date_format')  == "") { echo esc_attr( stripslashes( get_option('date_format') ) ); } else { echo esc_attr( stripslashes( get_option( 'mc_date_format') ) ); } ?>" /> <?php _e('Current:','my-calendar'); ?> <?php if ( get_option('mc_date_format') == '') { echo date_i18n(get_option('date_format')); } else { echo date_i18n(get_option('mc_date_format')); } ?><br />
	<small><?php _e('Date format uses the same syntax as the <a href="http://php.net/date">PHP <code>date()</code> function</a>. Save options to update sample output.','my-calendar'); ?></small>
	</li>
	<li>
	<input type="checkbox" id="mc_show_rss" name="mc_show_rss" <?php jd_cal_checkCheckbox('mc_show_rss','true'); ?> /> <label for="mc_show_rss"><?php _e('Show link to My Calendar RSS feed.','my-calendar'); ?></label> <small><?php _e('RSS feed shows recently added events.','my-calendar'); ?></small>
	</li>
	<li>
	<input type="checkbox" id="mc_show_ical" name="mc_show_ical" <?php jd_cal_checkCheckbox('mc_show_ical','true'); ?> /> <label for="mc_show_ical"><?php _e('Show link to iCal format download.','my-calendar'); ?></label> <small><?php _e('iCal outputs events occurring in the current calendar month.','my-calendar'); ?></small>
	</li>
	<li>
	<input type="checkbox" id="mc_display_jump" name="mc_display_jump" <?php jd_cal_checkCheckbox('mc_display_jump','true'); ?> /> <label for="mc_display_jump"><?php _e('Display a jumpbox for changing month and year quickly?','my-calendar'); ?></label>
	</li>		
	</ul>	
	<?php // End General Options // ?>
	</fieldset>
	
	<fieldset>
	<legend><?php _e('Grid Layout Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<input type="checkbox" id="mc_show_weekends" name="mc_show_weekends" <?php jd_cal_checkCheckbox('mc_show_weekends','true'); ?> /> <label for="mc_show_weekends"><?php _e('Show Weekends on Calendar','my-calendar'); ?></label>
	</li>		
	</ul>	
	<?php // End Grid Options // ?>
	</fieldset>	
	
	<fieldset>
	<legend><?php _e('List Layout Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="mc_show_months"><?php _e('How many months of events to show at a time:','my-calendar'); ?></label> <input type="text" size="3" id="mc_show_months" name="mc_show_months" value="<?php echo $mc_show_months; ?>" />
	</li>
	<li>
	<input type="checkbox" id="mc_show_list_info" name="mc_show_list_info" <?php jd_cal_checkCheckbox( 'mc_show_list_info','true' ); ?> /> <label for="mc_show_list_info"><?php _e('Show the first event\'s title and the number of events that day with the date:','my-calendar'); ?></label>
	</li>	
	</ul>	
	<?php // End List Options // ?>
	</fieldset>	

	<fieldset>
	<legend><?php _e('Event Details Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="mc_title_template"><?php _e('Event title template','my-calendar'); ?></label> 
	<input type="text" name="mc_title_template" id="mc_title_template" size="30" value="<?php echo stripslashes(esc_attr($mc_title_template)); ?>" /> <small><a href="<?php echo admin_url("admin.php?page=my-calendar-help#templates"); ?>"><?php _e("Templating Help",'my-calendar'); ?></a> <?php _e('All template tags are available.','my-calendar'); ?></small>
	</li>
	<li>
	<label for="mc_details_label"><?php _e('Event details link text','my-calendar'); ?></label>
	<input type="text" name="mc_details_label" id="mc_details_label" size="30" value="<?php echo stripslashes(esc_attr($mc_details_label)); ?>" />
	<small><?php _e('Available template tags: <code>{title}</code>, <code>{location}</code>, <code>{color}</code>, <code>{icon}</code>, <code>{date}</code>, <code>{time}</code>.','my-calendar'); ?></small>
	</li>
	<li>
	<label for="mc_link_label"><?php _e('Event URL link text','my-calendar'); ?></label>
	<input type="text" name="mc_link_label" id="mc_link_label" size="30" value="<?php echo stripslashes(esc_attr($mc_link_label)); ?>" />
	<small><a href="<?php echo admin_url("admin.php?page=my-calendar-help#templates"); ?>"><?php _e("Templating Help",'my-calendar'); ?></a><?php _e('All template tags are available.','my-calendar'); ?></small>
	</li>	
	<li>
	<input type="checkbox" id="mc_display_author" name="mc_display_author" <?php jd_cal_checkCheckbox('mc_display_author','true'); ?> /> <label for="mc_display_jump"><?php _e('Display author\'s name','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_show_event_vcal" name="mc_show_event_vcal" <?php jd_cal_checkCheckbox('mc_show_event_vcal','true'); ?> /> <label for="mc_show_ical"><?php _e('Display link to single event iCal download.','my-calendar'); ?></label> 
	</li>		
	<li>
	<input type="checkbox" id="mc_hide_icons" name="mc_hide_icons" <?php jd_cal_checkCheckbox('mc_hide_icons','true'); ?> /> <label for="mc_hide_icons"><?php _e('Hide category icons','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_show_map" name="mc_show_map" <?php jd_cal_checkCheckbox('mc_show_map','true'); ?> /> <label for="mc_show_map"><?php _e('Show Link to Google Map','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_show_address" name="mc_show_address" <?php jd_cal_checkCheckbox('mc_show_address','true'); ?> /> <label for="mc_show_address"><?php _e('Show Event Address','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_short" name="mc_short" <?php jd_cal_checkCheckbox('mc_short','true'); ?> /> <label for="mc_short"><?php _e('Show short description field on calendar.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_desc" name="mc_desc" <?php jd_cal_checkCheckbox('mc_desc','true'); ?> /> <label for="mc_desc"><?php _e('Show full description field on calendar.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_details" name="mc_details" <?php jd_cal_checkCheckbox('mc_details','true'); ?> /> <label for="mc_details"><?php _e('Show link to single-event details. (requires <a href=\'#mc_uri\'>URL, above</a>)','my-calendar'); ?></label>
	</li>		
	<li>
	<input type="checkbox" id="mc_event_link_expires" name="mc_event_link_expires" <?php jd_cal_checkCheckbox('mc_event_link_expires','true'); ?> /> <label for="mc_event_link_expires"><?php _e('Event links expire after the event has passed.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_event_registration" name="mc_event_registration" <?php jd_cal_checkCheckbox('mc_event_registration','true'); ?> /> <label for="mc_event_registration"><?php _e('Show current availability status','my-calendar'); ?></label>
	</li>
	<li>
    <input type="radio" id="mc_apply_color_default" name="mc_apply_color" value="default" <?php if ( get_option('mc_apply_color' ) == '' ) { echo 'checked="checked"'; } else { jd_cal_checkCheckbox('mc_apply_color','default'); } ?> /> <label for="mc_apply_color_default"><?php _e('Default usage of category colors.','my-calendar'); ?></label><br />
    <input type="radio" id="mc_apply_color_to_titles" name="mc_apply_color" value="font" <?php jd_cal_checkCheckbox('mc_apply_color','font'); ?> /> <label for="mc_apply_color_to_titles"><?php _e('Apply category colors to event titles as a font color.','my-calendar'); ?></label><br />
	<input type="radio" id="mc_apply_bgcolor_to_titles" name="mc_apply_color" value="background" <?php jd_cal_checkCheckbox('mc_apply_color','background'); ?> /> <label for="mc_apply_bgcolor_to_titles"><?php _e('Apply category colors to event titles as a background color.','my-calendar'); ?></label>	
	</li>	
	</ul>	
	<?php // End Event Options // ?>
	</fieldset>	
	<fieldset>
	<legend><?php _e('Event Scheduling Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<input type="checkbox" id="mc_no_fifth_week" name="mc_no_fifth_week" value="on" <?php jd_cal_checkCheckbox('mc_no_fifth_week','true'); ?> /> <label for="mc_no_fifth_week"><?php _e('Default setting for event input: If a recurring event is scheduled for a date which doesn\'t exist (such as the 5th Wednesday in February), move it back one week.','my-calendar'); ?></label>	
	</li>
	<li>
	<label for="mc_skip_holidays_category"><?php _e('Holiday Category','my-calendar'); ?></label>
	<select id="mc_skip_holidays_category" name="mc_skip_holidays_category">
			<option value=''> -- <?php _e('None','my-calendar'); ?> -- </option>
			<?php
			// Grab all the categories and list them
			$sql = "SELECT * FROM " . my_calendar_categories_table();
			$cats = $wpdb->get_results($sql);
				foreach($cats as $cat) {
					echo '<option value="'.$cat->category_id.'"';
						if ( get_option('mc_skip_holidays_category') == $cat->category_id ){
						 echo ' selected="selected"';
						}
					echo '>'.stripslashes($cat->category_name)."</option>\n";
				}
			?>
			</select>
    </li>
	<li>
	<input type="checkbox" id="mc_skip_holidays" name="mc_skip_holidays" <?php jd_cal_checkCheckbox('mc_skip_holidays','true'); ?> /> <label for="mc_skip_holidays"><?php _e('Default setting for event input: If an event coincides with an event in the designated "Holiday" category, do not show the event.','my-calendar'); ?></label>
	</li>
	<li>	
	<label for="mc_default_sort"><?php _e('Default Sort order for Admin Events List','my-calendar'); ?></label>
	<select id="mc_default_sort" name="mc_default_sort">
		<option value='1' <?php jd_cal_checkSelect( 'mc_default_sort','1'); ?>><?php _e('Event ID','my-calendar'); ?></option>
		<option value='2' <?php jd_cal_checkSelect( 'mc_default_sort','2'); ?>><?php _e('Title','my-calendar'); ?></option>
		<option value='3' <?php jd_cal_checkSelect( 'mc_default_sort','3'); ?>><?php _e('Description','my-calendar'); ?></option>
		<option value='4' <?php jd_cal_checkSelect( 'mc_default_sort','4'); ?>><?php _e('Start Date','my-calendar'); ?></option>
		<option value='5' <?php jd_cal_checkSelect( 'mc_default_sort','5'); ?>><?php _e('Author','my-calendar'); ?></option>
		<option value='6' <?php jd_cal_checkSelect( 'mc_default_sort','6'); ?>><?php _e('Category','my-calendar'); ?></option>
		<option value='7' <?php jd_cal_checkSelect( 'mc_default_sort','7'); ?>><?php _e('Location Name','my-calendar'); ?></option>
	</select>	
	</li>	
	</ul>	
	<?php // End Scheduling Options // ?>
	</fieldset>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Output Settings','my-calendar'); ?> &raquo;" />
	</p>
</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Input Settings','my-calendar'); ?></h3>
	<div class="inside">
<form id="my-calendar-input" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<fieldset>
	<legend><?php _e('Select which input fields will be available when adding or editing events.','my-calendar'); ?></legend>
	<div><input type='hidden' name='mc_input' value='true' /></div>
	<ul>
	<?php 
		$input_options = get_option('mc_input_options');
		$input_labels = array('event_location_dropdown'=>__('Show Event Location Dropdown Menu','my-calendar'),'event_short'=>__('Show Event Short Description field','my-calendar'),'event_desc'=>__('Show Event Description Field','my-calendar'),'event_category'=>__('Show Event Category field','my-calendar'),'event_image'=>__('Show Event image field','my-calendar'),'event_link'=>__('Show Event Link field','my-calendar'),'event_recurs'=>__('Show Event Recurrence Options','my-calendar'),'event_open'=>__('Show Event registration options','my-calendar'),'event_location'=>__('Show Event location fields','my-calendar'),'event_use_editor'=>__('Use HTML Editor in Event Description Field') );
		$output = '';
		// if input options isn't an array, we'll assume that this plugin wasn't upgraded properly, and reset them to the default.
		if ( !is_array($input_options) ) {
			update_option( 'mc_input_options',array('event_short'=>'on','event_desc'=>'on','event_category'=>'on','event_image'=>'on','event_link'=>'on','event_recurs'=>'on','event_open'=>'on','event_location'=>'on','event_location_dropdown'=>'on','event_use_editor'=>'on' ) );	
		}
	foreach ($input_options as $key=>$value) {
			$checked = ($value == 'on')?"checked='checked'":'';
			$output .= "<li><input type=\"checkbox\" id=\"mci_$key\" name=\"mci_$key\" $checked /> <label for=\"mci_$key\">$input_labels[$key]</label></li>";
		}
		echo $output;
	?>
	<li>
	<input type="checkbox" id="mc_input_options_administrators" name="mc_input_options_administrators" <?php jd_cal_checkCheckbox('mc_input_options_administrators','true'); ?> /> <label for="mc_input_options_administrators"><strong><?php _e('Administrators see all input options','my-calendar'); ?></strong></label>
	</li>
	</ul>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Input Settings','my-calendar'); ?> &raquo;" />
	</p>
</form>
</div>
</div>
<?php if ( current_user_can('manage_network') ) { ?>
<div class="postbox">
	<h3><?php _e('Multisite Settings (Network Administrators only)','my-calendar'); ?></h3>
	<div class="inside">
	<p><strong><?php _e('Multisite support is a beta feature - use with caution.','my-calendar'); ?></strong></p>
	<form id="my-calendar-multisite" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>	
	<div><input type='hidden' name='mc_network' value='true' /></div>	
	<fieldset>
	<legend><?php _e('Settings for WP MultiSite configurations','my-calendar'); ?></legend>
	<p><?php _e('The central calendar is the calendar associated with the primary site in your WordPress Multisite network.','my-calendar'); ?></p>	
	<ul>
	<li><input type="radio" value="0" id="ms0" name="mc_multisite"<?php echo jd_option_selected(get_site_option('mc_multisite'),'0'); ?> /> <label for="ms0"><?php _e('Site owners may only post to their local calendar','my-calendar'); ?></label></li>
	<li><input type="radio" value="1" id="ms1" name="mc_multisite"<?php echo jd_option_selected(get_site_option('mc_multisite'),'1'); ?> /> <label for="ms1"><?php _e('Site owners may only post to the central calendar','my-calendar'); ?></label></li>
	<li><input type="radio" value="2" id="ms2" name="mc_multisite"<?php echo jd_option_selected(get_site_option('mc_multisite'),2); ?> /> <label for="ms2"><?php _e('Site owners may manage either calendar','my-calendar'); ?></label></li>
	</ul>
	<p class="notice"><strong>*</strong> <?php _e('Changes only effect input permissions. Public-facing calendars will be unchanged.','my-calendar'); ?></p>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Multisite Settings','my-calendar'); ?> &raquo;" />
		</p>
</form>	
	</div>
</div>
<?php } ?>
<div class="postbox">
	<h3><?php _e('Calendar Email Settings','my-calendar'); ?></h3>
	<div class="inside">
<form id="my-calendar-email" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<fieldset>
	<legend><?php _e('Calendar Options: Email Notifications','my-calendar'); ?></legend>
<div><input type='hidden' name='mc_email' value='true' /></div>
	<ul>
	<li>
	<input type="checkbox" id="mc_event_mail" name="mc_event_mail" <?php jd_cal_checkCheckbox('mc_event_mail','true'); ?> /> <label for="mc_event_mail"><strong><?php _e('Send Email Notifications when new events are scheduled or reserved.','my-calendar'); ?></strong></label>
	</li>
	<li>
	<label for="mc_event_mail_to"><?php _e('Notification messages are sent to: ','my-calendar'); ?></label> <input type="text" id="mc_event_mail_to" name="mc_event_mail_to" size="40"  value="<?php if ( get_option('mc_event_mail_to') == "") { bloginfo('admin_email'); } else { echo stripslashes(esc_attr( get_option('mc_event_mail_to')) ); } ?>" />
	</li>	
	<li>
	<label for="mc_event_mail_subject"><?php _e('Email subject','my-calendar'); ?></label> <input type="text" id="mc_event_mail_subject" name="mc_event_mail_subject" size="60" value="<?php if ( get_option('mc_event_mail_subject') == "") { bloginfo('name'); echo ': '; _e('New event Added','my-calendar'); } else { echo stripslashes(esc_attr( get_option('mc_event_mail_subject') ) ); } ?>" />
	</li>
	<li>
	<label for="mc_event_mail_message"><?php _e('Message Body','my-calendar'); ?></label><br /> <textarea rows="6" cols="80"  id="mc_event_mail_message" name="mc_event_mail_message"><?php if ( get_option('mc_event_mail_message') == "") { _e('New Event:','my-calendar'); echo "\n{title}: {date}, {time} - {event_status}"; } else { echo stripslashes( esc_attr( get_option('mc_event_mail_message') ) ); } ?></textarea><br />
	<a href="<?php echo admin_url("admin.php?page=my-calendar-help#templates"); ?>"><?php _e("Shortcode Help",'my-calendar'); ?></a> <?php _e('All template shortcodes are available.','my-calendar'); ?>
	</li>
	</ul>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Email Settings','my-calendar'); ?> &raquo;" />
		</p>
</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar User Settings','my-calendar'); ?></h3>
	<div class="inside">
<form id="my-calendar-user" method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
<div><input type='hidden' name='mc_user' value='true' /></div>

	<fieldset>
	<legend><?php _e('Settings which can be configured in registered user\'s accounts','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="mc_user_settings_enabled" name="mc_user_settings_enabled" value="on" <?php jd_cal_checkCheckbox('mc_user_settings_enabled','true'); ?> /> <label for="mc_user_settings_enabled"><strong><?php _e('Allow registered users to provide timezone or location presets in their user profiles.','my-calendar'); ?></strong></label>
	</p>

<?php

$mc_user_settings = get_option('mc_user_settings'); 
if (!is_array($mc_user_settings)) {
	update_option( 'mc_user_settings', $default_user_settings );
	$mc_user_settings = get_option('mc_user_settings');
}
?>
<fieldset>
<legend><?php _e('Timezone Settings','my-calendar'); ?></legend>
<p><?php _e('These settings provide registered users with the ability to select a time zone in their user profile. When they view your calendar, the times for events will display the time the event happens in their time zone as well as the entered value.','my-calendar'); ?></p>
	<p>
	<input type="checkbox" id="tz_enabled" name="mc_user_settings[my_calendar_tz_default][enabled]" <?php jd_cal_checkCheckbox('mc_user_settings','on','my_calendar_tz_default'); ?> /> <label for="tz_enabled"><?php _e('Enable Timezone','my-calendar'); ?></label>
	</p>
	<p>
	<label for="tz_label"><?php _e('Select Timezone Label','my-calendar'); ?></label> <input type="text" name="mc_user_settings[my_calendar_tz_default][label]" id="tz_label" value="<?php echo stripslashes(esc_attr($mc_user_settings['my_calendar_tz_default']['label'])); ?>" size="40" />
	</p>
	<p>
	<label for="tz_values"><?php _e('Timezone Options','my-calendar'); ?> (<?php _e('Value, Label; one per line','my-calendar'); ?>)</label><br />
 	<?php 
	$timezones = '';
foreach ( $mc_user_settings['my_calendar_tz_default']['values'] as $key=>$value ) {
$timezones .= stripslashes("$key,$value")."\n";
}
	?>	
	<textarea name="mc_user_settings[my_calendar_tz_default][values]" id="tz_values" cols="60" rows="8"><?php echo trim($timezones); ?></textarea>
	</p>
</fieldset>

<fieldset>
<legend><?php _e('Location Settings','my-calendar'); ?></legend>
<p><?php _e('These settings provide registered users with the ability to select a location in their user profile. When they view your calendar, their initial view will be limited to locations which include that location parameter. These values can also be used to generate custom location filtering options using the <code>my_calendar_locations</code> shortcode. It is not necessary to enable these settings for users to use the custom filtering options.','my-calendar'); ?></p>
	<p>
	<input type="checkbox" id="loc_enabled" name="mc_user_settings[my_calendar_location_default][enabled]" <?php jd_cal_checkCheckbox('mc_user_settings','on','my_calendar_location_default'); ?> /> <label for="loc_enabled"><?php _e('Enable Location','my-calendar'); ?></label>
	</p>
	<p>
	<input type="checkbox" id="loc_control" name="mc_location_control" <?php jd_cal_checkCheckbox('mc_location_control','on' ); ?> /> <label for="mc_location_control"><?php _e('Use this location list as input control','my-calendar'); ?></label> <small><?php _e('The normal text entry for this location type will be replaced by a drop down containing these choices.','my-calendar'); ?></small>
	</p>
	<p>
	<label for="loc_label"><?php _e('Select Location Label','my-calendar'); ?></label> <input type="text" name="mc_user_settings[my_calendar_location_default][label]" id="loc_label" value="<?php echo stripslashes( esc_attr( $mc_user_settings['my_calendar_location_default']['label'] ) ); ?>" size="40" />
	</p>
	<p>
	<label for="loc_values"><?php _e('Location Options','my-calendar'); ?> (<?php _e('Value, Label; one per line','my-calendar'); ?>)</label><br />
	<?php 
	$locations = '';
foreach ( $mc_user_settings['my_calendar_location_default']['values'] as $key=>$value ) {
$locations .= stripslashes("$key,$value")."\n";
}
?>
	<textarea name="mc_user_settings[my_calendar_location_default][values]" id="loc_values" cols="60" rows="8"><?php echo trim($locations); ?></textarea>
	</p>
	<p>
	<label for="loc_type"><?php _e('Location Type','my-calendar'); ?></label><br />
	<select id="loc_type" name="mc_location_type">
	<option value="event_label" <?php jd_cal_checkSelect( 'mc_location_type','event_label' ); ?>><?php _e('Location Name','my-calendar'); ?></option>
	<option value="event_city" <?php jd_cal_checkSelect( 'mc_location_type','event_city' ); ?>><?php _e('City','my-calendar'); ?></option>
	<option value="event_state" <?php jd_cal_checkSelect( 'mc_location_type','event_state'); ?>><?php _e('State/Province','my-calendar'); ?></option>
	<option value="event_country" <?php jd_cal_checkSelect( 'mc_location_type','event_country'); ?>><?php _e('Country','my-calendar'); ?></option>
	<option value="event_postcode" <?php jd_cal_checkSelect( 'mc_location_type','event_postcode'); ?>><?php _e('Postal Code','my-calendar'); ?></option>
	<option value="event_region" <?php jd_cal_checkSelect( 'mc_location_type','event_region'); ?>><?php _e('Region','my-calendar'); ?></option>	
	</select>
	</p>
</fieldset>
	</fieldset>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save User Settings','my-calendar'); ?> &raquo;" />
	</p>
  </form>
  <?php
//update_option( 'ko_calendar_imported','false' );
if (isset($_POST['import']) && $_POST['import'] == 'true') {
	$nonce=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
	my_calendar_import();
}
if ( get_option( 'ko_calendar_imported' ) != 'true' ) {
  	if (function_exists('check_calendar')) {
	echo "<div class='import'>";
	echo "<p>";
	_e('My Calendar has identified that you have the Calendar plugin by Kieran O\'Shea installed. You can import those events and categories into the My Calendar database. Would you like to import these events?','my-calendar');
	echo "</p>";
?>
		<form method="post" action="<?php echo admin_url("admin.php?page=my-calendar-config"); ?>">
		<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>		
		<div>
		<input type="hidden" name="import" value="true" />
		<input type="submit" value="<?php _e('Import from Calendar','my-calendar'); ?>" name="import-calendar" class="button-primary" />
		</div>
		</form>
<?php
	echo "</div>";
	}
}
?>
	</div>
</div>
</div>
</div>
<?php
}
?>