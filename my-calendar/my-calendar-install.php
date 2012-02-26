<?php
// define global variables;
global $initial_listjs, $initial_caljs, $initial_minijs, $initial_ajaxjs, $initial_db, $initial_loc_db, $initial_cat_db, $default_template,$default_user_settings, $wpdb,$grid_template,$list_template,$mini_template,$single_template, $defaults;

$defaults = array(
	'upcoming'=>array(	
		'type'=>'event',
		'before'=>3,
		'after'=>3,
		'template'=>$default_template,
		'category'=>'',
		'text'=>'',
		'title'=>'Upcoming Events'
	),
	'today'=>array(
		'template'=>$default_template,
		'category'=>'',
		'title'=>'Today\'s Events',
		'text'=>''
	)
);

$grid_template = addslashes('<span class="event-time dtstart" title="{dtstart}">{time}<span class="time-separator"> - </span><span class="end-time dtend" title="{dtend}">{endtime}</span></span>

<div class="sub-details"><span class="event-author">Posted by: <span class="author-name">{author}</span></span><br />
{hcard}
<p class="mc_details">{details}</p>
<p>{ical_html}</p>
<div class="shortdesc">{image}
{shortdesc}
</div>
<p><a href="{link}" class="event-link external">{title}</a></p></div>');

$list_template = addslashes('<span class="event-time dtstart" title="{dtstart}">{time}<span class="time-separator"> - </span><span class="end-time dtend" title="{dtend}">{endtime}</span></span>

<div class="sub-details">
<h3 class="event-title summary">{icon_html}{title}</h3>
<span class="event-author">Posted by: <span class="author-name">{author}</span></span><br />
{hcard}
<p class="mc_details">{details}</p>
<p>{ical_html}</p>
<div class="shortdesc">{image}{shortdesc}</div>
<p><a href="{link}" class="event-link external">{title}</a></p></div>');

$mini_template = addslashes('<span class="event-time dtstart" title="{dtstart}">{time}<span class="time-separator"> - </span><span class="end-time dtend" title="{dtend}">{endtime}</span></span>

<div class="sub-details"><span class="event-author">Posted by: <span class="author-name">{author}</span></span><br />
{hcard}
<p class="mc_details">{details}</p>
<p>{ical_html}</p>
<p><a href="{link}" class="event-link external">{title}</a></p></div>');

$single_template = addslashes('<span class="event-time dtstart" title="{dtstart}">{time}<span class="time-separator"> - </span><span class="end-time dtend" title="{dtend}">{endtime}</span></span>

<div class="sub-details"><span class="event-author">Posted by: <span class="author-name">{author}</span></span><br />
{hcard}
<p class="mc_details">{details}</p>
<p>{ical_html}</p>
<div class="shortdesc">{image}{description}</div>
<p><a href="{link}" class="event-link external">{title}</a></p></div>');

$initial_ajaxjs = "jQuery(document).ready(function($){
	$('.calendar .my-calendar-nav a').live('click', function(e){
		e.preventDefault();
		var link = $(this).attr('href');
		$('#jd-calendar.calendar').html('Loading...');
		$('#jd-calendar.calendar').load(link+' #jd-calendar.calendar > *', function() {
			$('.calendar-event').children().not('h3').hide();
		});
	});	
	$('.mini .my-calendar-nav a').live('click', function(e){
		e.preventDefault();
		var link = $(this).attr('href');
		$('#jd-calendar.mini').html('Loading...');
		$('#jd-calendar.mini').load(link+' #jd-calendar.mini > *', function() {
			$('.mini .has-events').children().not('.trigger').hide();
		});
	});	
	$('.list .my-calendar-nav a').live('click', function(e){
		e.preventDefault();
		var link = $(this).attr('href');
		$('#jd-calendar.list').html('Loading...');
		$('#jd-calendar.list').load(link+' #jd-calendar.list > *', function() {
			$('#calendar-list li.mc-events').children().not('.event-date').hide();
			$('#calendar-list li.current-day').children().show();
		});
	});	
});";
// defaults will go into the options table on a new install
$initial_caljs = 'jQuery(document).ready(function($) {
  $(".calendar-event").children().not(".event-title").hide();
  $(".calendar-event .event-title").live("click",
     function(e) {
         e.preventDefault(); // remove line if you are using a link in the event title
	 $(this).parent().children().not(".event-title").toggle();
	 });
  $(".calendar-event .close").live("click",
     function(e) {
         e.preventDefault();
	 $(this).parent().toggle();
	 });
});';  

$initial_listjs = 'jQuery(document).ready(function($) {
  $("#calendar-list li.mc-events").children().not(".event-date").hide();
  $("#calendar-list li.current-day").children().show();
  $(".event-date").live("click",
     function(e) {
	 e.preventDefault();
	 $(this).parent().children().not(".event-date").toggle();
     });
});';  

$initial_minijs = 'jQuery(document).ready(function($) {
  $(".mini .has-events").children().not(".trigger").hide();
  $(".mini .has-events .trigger").live("click",
     function(e) {
	 e.preventDefault();	 
	 $(this).parent().children().not(".trigger").toggle(); 
	 });
  $(".mini-event .close").live("click",
     function(e) {
         e.preventDefault();
	 $(this).parent().parent().parent().toggle();
	 });
});';

$default_template = "<strong>{date}</strong> &#8211; {link_title}<br /><span>{time}, {category}</span>";

if ( ! empty($wpdb->charset) ) {
	$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
}
if ( ! empty($wpdb->collate) ) {
	$charset_collate .= " COLLATE $wpdb->collate";
}

$event_holiday = (get_option('mc_skip_holidays') == 'true' )?1:0;
$event_fifth_week = (get_option('mc_no_fifth_week') == 'true' )?1:0;

$initial_db = "CREATE TABLE " . my_calendar_table() . " ( 
 event_id INT(11) NOT NULL AUTO_INCREMENT,
 event_begin DATE NOT NULL,
 event_end DATE NOT NULL,
 event_title VARCHAR(255) NOT NULL,
 event_desc TEXT NOT NULL,
 event_short TEXT NOT NULL,
 event_open INT(3) DEFAULT '2',
 event_time TIME,
 event_endtime TIME,
 event_recur CHAR(1),
 event_repeats INT(3),
 event_status INT(1) NOT NULL DEFAULT '1',  
 event_author BIGINT(20) UNSIGNED,
 event_host BIGINT(20) UNSIGNED, 
 event_category BIGINT(20) UNSIGNED NOT NULL DEFAULT '1',
 event_link TEXT,
 event_link_expires TINYINT(1) NOT NULL,
 event_label VARCHAR(60) NOT NULL,
 event_street VARCHAR(60) NOT NULL,
 event_street2 VARCHAR(60) NOT NULL,
 event_city VARCHAR(60) NOT NULL,
 event_state VARCHAR(60) NOT NULL,
 event_postcode VARCHAR(10) NOT NULL,
 event_region VARCHAR(255) NOT NULL,
 event_country VARCHAR(60) NOT NULL,
 event_url TEXT,
 event_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_zoom INT(2) NOT NULL DEFAULT '14',
 event_group INT(1) NOT NULL DEFAULT '0',
 event_group_id INT(11) NOT NULL DEFAULT '0',
 event_approved INT(1) NOT NULL DEFAULT '1',
 event_flagged INT(1) NOT NULL DEFAULT '0',
 event_holiday INT(1) NOT NULL DEFAULT '$event_holiday',
 event_fifth_week INT(1) NOT NULL DEFAULT '$event_fifth_week',
 event_image TEXT,
 event_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY  (event_id),
 KEY event_recur (event_recur)
 ) $charset_collate;";
 
$initial_cat_db = "CREATE TABLE " . my_calendar_categories_table() . " ( 
 category_id INT(11) NOT NULL AUTO_INCREMENT, 
 category_name VARCHAR(255) NOT NULL, 
 category_color VARCHAR(7) NOT NULL, 
 category_icon VARCHAR(128) NOT NULL,
 PRIMARY KEY  (category_id) 
 ) $charset_collate;";
 
$initial_loc_db = "CREATE TABLE " . my_calendar_locations_table() . " ( 
 location_id INT(11) NOT NULL AUTO_INCREMENT, 
 location_label VARCHAR(60) NOT NULL,
 location_street VARCHAR(60) NOT NULL,
 location_street2 VARCHAR(60) NOT NULL,
 location_city VARCHAR(60) NOT NULL,
 location_state VARCHAR(60) NOT NULL,
 location_postcode VARCHAR(10) NOT NULL,
 location_region VARCHAR(255) NOT NULL,
 location_url TEXT,
 location_country VARCHAR(60) NOT NULL,
 location_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_zoom INT(2) NOT NULL DEFAULT '14',
 PRIMARY KEY  (location_id) 
 ) $charset_collate;";

$default_user_settings = array(
	'my_calendar_tz_default'=>array(
		'enabled'=>'off',
		'label'=>__('My Calendar Default Timezone','my-calendar'),
		'values'=>array(
			"-12" => "(GMT -12:00) Eniwetok, Kwajalein",
			"-11" => "(GMT -11:00) Midway Island, Samoa",
			"-10" => "(GMT -10:00) Hawaii",
			"-9.5" => "(GMT -9:30) Marquesas Islands",
			"-9" => "(GMT -9:00) Alaska",
			"-8" => "(GMT -8:00) Pacific Time (US &amp; Canada)",
			"-7" => "(GMT -7:00) Mountain Time (US &amp; Canada)",
			"-6" => "(GMT -6:00) Central Time (US &amp; Canada), Mexico City",
			"-5" => "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima",
			"-4.5" => "(GMT -4:30) Venezuela",
			"-4" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
			"-3.5" => "(GMT -3:30) Newfoundland",
			"-3" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
			"-2" => "(GMT -2:00) Mid-Atlantic",
			"-1" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
			"0" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
			"1" => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris",
			"2" => "(GMT +2:00) Kaliningrad, South Africa",
			"3" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
			"3.5" => "(GMT +3:30) Tehran",
			"4" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
			"4.5" => "(GMT +4:30) Afghanistan",
			"5" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
			"5.5" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
			"5.75" => "(GMT +5:45) Nepal",
			"6" => "(GMT +6:00) Almaty, Dhaka, Colombo",
			"6.5" => "(GMT +6:30) Myanmar, Cocos Islands",
			"7" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
			"8" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
			"9" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
			"9.5" => "(GMT +9:30) Adelaide, Darwin",
			"10" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
			"10.5" => "(GMT +10:30) Lord Howe Island",
			"11" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
			"11.5" => "(GMT +11:30) Norfolk Island",
			"12" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka",
			"12.75" => "(GMT +12:45) Chatham Islands",
			"13" => "(GMT +13:00) Tonga",
			"14" => "(GMT +14:00) Line Islands"
			),
		),
		'my_calendar_location_default'=>array(
		'enabled'=>'off',
		'label'=>__('My Calendar Default Location','my-calendar'),
		'values'=>array(
			'AL'=>"Alabama",
			'AK'=>"Alaska", 
			'AZ'=>"Arizona", 
			'AR'=>"Arkansas", 
			'CA'=>"California", 
			'CO'=>"Colorado", 
			'CT'=>"Connecticut", 
			'DE'=>"Delaware", 
			'DC'=>"District Of Columbia", 
			'FL'=>"Florida", 
			'GA'=>"Georgia", 
			'HI'=>"Hawaii", 
			'ID'=>"Idaho", 
			'IL'=>"Illinois", 
			'IN'=>"Indiana", 
			'IA'=>"Iowa", 
			'KS'=>"Kansas", 
			'KY'=>"Kentucky", 
			'LA'=>"Louisiana", 
			'ME'=>"Maine", 
			'MD'=>"Maryland", 
			'MA'=>"Massachusetts", 
			'MI'=>"Michigan", 
			'MN'=>"Minnesota", 
			'MS'=>"Mississippi", 
			'MO'=>"Missouri", 
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio", 
			'OK'=>"Oklahoma", 
			'OR'=>"Oregon", 
			'PA'=>"Pennsylvania", 
			'RI'=>"Rhode Island", 
			'SC'=>"South Carolina", 
			'SD'=>"South Dakota",
			'TN'=>"Tennessee", 
			'TX'=>"Texas", 
			'UT'=>"Utah", 
			'VT'=>"Vermont", 
			'VA'=>"Virginia", 
			'WA'=>"Washington", 
			'WV'=>"West Virginia", 
			'WI'=>"Wisconsin", 
			'WY'=>"Wyoming"),
		)				
	); 

function mc_default_settings( ) {
global $default_template, $initial_listjs, $initial_caljs, $initial_minijs, $initial_ajaxjs, $initial_db, $initial_loc_db, $initial_cat_db, $default_user_settings,$grid_template,$list_template,$mini_template,$single_template,$mc_version, $defaults;
// no arguments
	add_option('mc_can_manage_events','edit_posts');
	add_option('mc_display_author','false');
	add_option('mc_display_jump','false');
	add_option('mc_version',$mc_version);
	add_option('mc_use_styles','false');
	add_option('mc_show_months',1);
	add_option('mc_show_map','true');
	add_option('mc_show_address','false');
	add_option('mc_calendar_javascript',0);
	add_option('mc_list_javascript',0);
	add_option('mc_mini_javascript',0);
	add_option('mc_ajax_javascript',1);
	add_option('mc_minijs',$initial_minijs);
	add_option('mc_listjs',$initial_listjs);
	add_option('mc_caljs',$initial_caljs);
	add_option('mc_ajaxjs',$initial_ajaxjs);
	add_option('mc_notime_text','N/A');
	add_option('mc_hide_icons','false');
	add_option('mc_event_link_expires','no');
	add_option('mc_apply_color','default');
	add_option('mc_input_options',array('event_short'=>'on','event_desc'=>'on','event_category'=>'on','event_image'=>'on','event_link'=>'on','event_recurs'=>'on','event_open'=>'on','event_location'=>'on','event_location_dropdown'=>'on','event_use_editor'=>'off') );
	add_option('mc_input_options_administrators','false');
	add_site_option('mc_multisite', '0' );
	add_option('mc_event_mail','false');
	add_option('mc_desc','true');
	add_option('mc_short','false');
	add_option('mc_event_mail_subject','');
	add_option('mc_event_mail_to','');
	add_option('mc_event_mail_message','');
	add_option('mc_event_approve','false');
	add_option('mc_event_approve_perms','manage_options');
	add_option('mc_no_fifth_week','true');
	add_option( 'mc_week_format', "M j, 'y" );	
	$mc_user_settings = $default_user_settings;	
	add_option('mc_user_settings',$mc_user_settings);
	add_option('mc_location_type','event_state');
	add_option('mc_user_settings_enabled',false);
	add_option('mc_user_location_type','state');
	add_option('mc_show_js','' );
	add_option('mc_show_css','' );
	add_option( 'mc_location_control','' );
	add_option('mc_date_format',get_option('date_format') );
	add_option('mc_templates', array(
		'title'=>'{title}',
		'link'=>'{title}',
		'grid'=>$grid_template,
		'list'=>$list_template,
		'mini'=>$mini_template,
		'details'=>$single_template,
		'label'=>''
	));
	add_option('mc_skip_holidays','false');
    add_option('mc_event_edit_perms','manage_options');
	add_option('mc_css_file','refresh.css');
	add_option('mc_show_rss','false');
	add_option('mc_show_ical','false');	
	add_option('mc_time_format',get_option('time_format'));
	add_option( 'mc_widget_defaults',$defaults);
	add_option( 'mc_show_weekends','true' );
	add_option( 'mc_uri','' );	
	add_option( 'mc_show_event_vcal','false' );
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($initial_db);
	dbDelta($initial_cat_db);
	dbDelta($initial_loc_db);	
	
}

function upgrade_db() {
global $initial_db, $initial_loc_db, $initial_cat_db;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($initial_db);
	dbDelta($initial_cat_db);
	dbDelta($initial_loc_db);	
}

function my_calendar_copyr($source, $dest) {
	// Sanity check
	if ( !file_exists($source) ) {
		return false;
	}
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }
    // Simple copy for a file
    if (is_file($source)) {
        return @copy($source, $dest);
    }
    // Make destination directory
    if (!is_dir($dest)) {
        @mkdir($dest);
    }
    // Loop through the folder
	$dir = dir($source);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		// Deep copy directories
		my_calendar_copyr("$source/$entry", "$dest/$entry");
	}
	// Clean up
	$dir->close();
    return true;
}
function my_calendar_rmdirr($dirname) {
	// Sanity check
	if (!file_exists($dirname)) {
	return false;
	}
	// Simple delete for a file
	if (is_file($dirname)) {
	return unlink($dirname);
	}
	// Loop through the folder
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
	// Skip pointers
		if ($entry == '.' || $entry == '..') {
		continue;
		}
		// Recurse
		my_calendar_rmdirr("$dirname/$entry");
	}
	// Clean up
	$dir->close();
	return @rmdir($dirname);
}
function my_calendar_backup() {
    $to = dirname(__FILE__)."/../styles_backup/";
    $from = dirname(__FILE__)."/styles/";
    my_calendar_copyr($from, $to);
	
    $to = dirname(__FILE__)."/../icons_backup/";
    $from = dirname(__FILE__)."/icons/";
    my_calendar_copyr($from, $to);	
}
function my_calendar_recover() {
    $from = dirname(__FILE__)."/../styles_backup/";
    $to = dirname(__FILE__)."/styles/";
    my_calendar_copyr($from, $to);
    if (is_dir($from)) {
        my_calendar_rmdirr($from);
    }
	
    $from = dirname(__FILE__)."/../icons_backup/";
    $to = dirname(__FILE__)."/icons/";
    my_calendar_copyr($from, $to);
    if (is_dir($from)) {
        my_calendar_rmdirr($from);
    }	
}
add_filter('upgrader_pre_install', 'my_calendar_backup', 10, 2);
add_filter('upgrader_post_install', 'my_calendar_recover', 10, 2);