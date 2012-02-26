<?php
// Display the admin configuration page
function edit_mc_templates() {
	global $wpdb;
	// We can't use this page unless My Calendar is installed/upgraded
	check_my_calendar();
	$templates = get_option( 'mc_templates' );

	if ( isset($_POST['mc_grid_template'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");

		$mc_grid_template = $_POST['mc_grid_template'];
		$templates['grid'] = $mc_grid_template;
		update_option( 'mc_templates', $templates );
		update_option( 'mc_use_grid_template',( empty($_POST['mc_use_grid_template'])?0:1 ) );

		echo "<div class=\"updated\"><p><strong>".__('Grid Output Template saved','my-calendar').".</strong></p></div>";
	}
	
	if ( isset($_POST['mc_list_template'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");

		$mc_list_template = $_POST['mc_list_template'];
		$templates['list'] = $mc_list_template;
		update_option( 'mc_templates', $templates );
		update_option( 'mc_use_list_template',( empty($_POST['mc_use_list_template'])?0:1 ) );

		echo "<div class=\"updated\"><p><strong>".__('List Output Template saved','my-calendar').".</strong></p></div>";
	}

	if ( isset($_POST['mc_mini_template'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");

		$mc_mini_template = $_POST['mc_mini_template'];
		$templates['mini'] = $mc_mini_template;
		update_option( 'mc_templates', $templates );
		update_option( 'mc_use_mini_template',( empty($_POST['mc_use_mini_template'])?0:1 ) );
		echo "<div class=\"updated\"><p><strong>".__('Mini Output Template saved','my-calendar').".</strong></p></div>";
	}

	if ( isset($_POST['mc_details_template'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");

		$mc_details_template = $_POST['mc_details_template'];
		$templates['details'] = $mc_details_template;
		update_option( 'mc_templates', $templates );
		update_option( 'mc_use_details_template',( empty($_POST['mc_use_details_template'])?0:1 ) );
		echo "<div class=\"updated\"><p><strong>".__('Event Details Template saved','my-calendar').".</strong></p></div>";
	}	
	global $grid_template, $list_template, $mini_template, $single_template;
	$mc_grid_template = stripslashes( ($templates['grid']!='')?$templates['grid']:$grid_template );
	$mc_use_grid_template = get_option('mc_use_grid_template');
	$mc_list_template = stripslashes( ($templates['list']!='')?$templates['list']:$list_template );
	$mc_use_list_template = get_option('mc_use_list_template');
	$mc_mini_template = stripslashes( ($templates['mini']!='')?$templates['mini']:$mini_template );
	$mc_use_mini_template = get_option('mc_use_mini_template');
	$mc_details_template = stripslashes( ($templates['details']!='')?$templates['details']:$single_template );
	$mc_use_details_template = get_option('mc_use_details_template');	
?>
    <div class="wrap templating" id="poststuff">
	
<?php my_calendar_check_db(); ?>
    <h2><?php _e('My Calendar Information Templates','my-calendar'); ?></h2>
    <?php jd_show_support_box(); ?>
	<div class='mc_template_tags'>
	<h3><?php _e('Event Template Tags','my-calendar'); ?></h3>
<dl>
<dt><code>{title}</code></dt>
<dd><?php _e('Title of the event.','my-calendar'); ?></dd>

<dt><code>{link_title}</code></dt>
<dd><?php _e('Title of the event as a link if a URL is present, or the title alone if not.','my-calendar'); ?></dd>

<dt><code>{time}</code></dt>
<dd><?php _e('Start time for the event.','my-calendar'); ?></dd>

<dt><code>{usertime}</code>/<code>{endusertime}</code></dt>
<dd><?php _e('Event times adjusted to the current user\'s time zone if set.','my-calendar'); ?></dd>

<dt><code>{date}</code></dt>
<dd><?php _e('Date on which the event begins.','my-calendar'); ?></dd>

<dt><code>{enddate}</code></dt>
<dd><?php _e('Date on which the event ends.','my-calendar'); ?></dd>

<dt><code>{endtime}</code></dt>
<dd><?php _e('Time at which the event ends.','my-calendar'); ?></dd>

<dt><code>{author}</code></dt>
<dd><?php _e('Author who posted the event.','my-calendar'); ?></dd>

<dt><code>{host}</code></dt>
<dd><?php _e('Name of the assigned host for the event.','my-calendar'); ?></dd>

<dt><code>{host_email}</code></dt>
<dd><?php _e('Email for the person assigned as host.','my-calendar'); ?></dd>

<dt><code>{shortdesc}</code></dt>
<dd><?php _e('Short event description.','my-calendar'); ?></dd>

<dt><code>{description}</code></dt>
<dd><?php _e('Description of the event.','my-calendar'); ?></dd>

<dt><code>{image}</code></dt>
<dd><?php _e('Image associated with the event.','my-calendar'); ?></dd>

<dt><code>{link}</code></dt>
<dd><?php _e('URL provided for the event.','my-calendar'); ?></dd>

<dt><code>{details}</code></dt>
<dd><?php _e('Link to an auto-generated page containing information about the event.','my-calendar'); ?>

<dt><code>{event_open}</code></dt>
<dd><?php _e('Whether event is currently open for registration.','my-calendar'); ?></dd>

<dt><code>{event_status}</code></dt>
<dd><?php _e('Current status of event: either "Published" or "Reserved."','my-calendar'); ?></dd>
</dl>
<h3><?php _e('Location Template Tags','my-calendar'); ?></h3>

<dl>
<dt><code>{location}</code></dt>
<dd><?php _e('Name of the location of the event.','my-calendar'); ?></dd>

<dt><code>{street}</code></dt>
<dd><?php _e('First line of the site address.','my-calendar'); ?></dd>

<dt><code>{street2}</code></dt>
<dd><?php _e('Second line of the site address.','my-calendar'); ?></dd>

<dt><code>{city}</code></dt>
<dd><?php _e('City.','my-calendar'); ?></dd>

<dt><code>{state}</code></dt>
<dd><?php _e('State.','my-calendar'); ?></dd>

<dt><code>{postcode}</code></dt>
<dd><?php _e('Postal code/zip code.','my-calendar'); ?></dd>

<dt><code>{region}</code></dt>
<dd><?php _e('Custom region.','my-calendar'); ?></dd>

<dt><code>{country}</code></dt>
<dd><?php _e('Country for the event location.','my-calendar'); ?></dd>

<dt><code>{sitelink}</code></dt>
<dd><?php _e('Output the URL for the location.','my-calendar'); ?></dd>

<dt><code>{hcard}</code></dt>
<dd><?php _e('Event address in <a href="http://microformats.org/wiki/hcard">hcard</a> format.','my-calendar'); ?></dd>

<dt><code>{link_map}</code></dt>
<dd><?php _e('Link to Google Map to the event, if address information is available.','my-calendar'); ?></dd>
</dl>
<h4><?php _e('Category Template Tags','my-calendar'); ?></h4>

<dl>
<dt><code>{category}</code></dt>
<dd><?php _e('Name of the category of the event.','my-calendar'); ?></dd>

<dt><code>{icon}</code></dt>
<dd><?php _e('URL for the event\'s category icon.','my-calendar'); ?></dd>

<dt><code>{color}</code></dt>
<dd><?php _e('Hex code for the event\'s category color.','my-calendar'); ?></dd>

<dt><code>{category_id}</code></dt>
<dd><?php _e('ID of the category of the event.','my-calendar'); ?></dd>
</dl>
	</div>
	<p><?php _e('Advanced users may wish to customize the HTML elements and order of items presented for each event. This page provides the ability to create a customized view of your events in each different context. All available template tags are documented on the Help page. The default templates provided are based on the default views assuming all output is enabled. <strong>Custom templates will override any other output rules you\'ve configured in settings.</strong>','my-calendar'); ?> <a href="<?php echo admin_url("admin.php?page=my-calendar-help#templates"); ?>"><?php _e("Templates Help",'my-calendar'); ?></a> &raquo;</p>
<div class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('My Calendar: Grid Event Template','my-calendar'); ?></h3>
	<div class="inside">	
    <form method="post" action="<?php echo admin_url("admin.php?page=my-calendar-templates"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<p>
	<input type="checkbox" id="mc_use_grid_template" name="mc_use_grid_template" value="1"  <?php jd_cal_checkCheckbox('mc_use_grid_template',1); ?>/> <label for="mc_use_grid_template"><?php _e('Use this grid event template','my-calendar'); ?></label>
	</p>
	<p>
	<label for="mc_grid_template"><?php _e('Your custom template for events in the calendar grid output.','my-calendar'); ?></label><br /><textarea id="mc_grid_template" name="mc_grid_template" rows="12" cols="64"><?php echo $mc_grid_template; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Grid Template','my-calendar'); ?> &raquo;" />
	</p>
	</form>
	</div>
</div>
</div>

<div class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('My Calendar: List Event Template','my-calendar'); ?></h3>
	<div class="inside">	
    <form method="post" action="<?php echo admin_url("admin.php?page=my-calendar-templates"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<p>
	<input type="checkbox" id="mc_use_list_template" name="mc_use_list_template" value="1"  <?php jd_cal_checkCheckbox('mc_use_list_template',1); ?>/> <label for="mc_use_list_template"><?php _e('Use this list event template','my-calendar'); ?></label>
	</p>
	<p>
	<label for="mc_list_template"><?php _e('Your custom template for events in calendar list output.','my-calendar'); ?></label><br /><textarea id="mc_list_template" name="mc_list_template" rows="12" cols="64"><?php echo $mc_list_template; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save List Template','my-calendar'); ?> &raquo;" />
	</p>
	</form>
	</div>
</div>
</div>

<div class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('My Calendar: Mini Calendar Template','my-calendar'); ?></h3>
	<div class="inside">	
    <form method="post" action="<?php echo admin_url("admin.php?page=my-calendar-templates"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<p>
	<input type="checkbox" id="mc_use_mini_template" name="mc_use_mini_template" value="1"  <?php jd_cal_checkCheckbox('mc_use_mini_template',1); ?>/> <label for="mc_use_mini_template"><?php _e('Use this mini event template','my-calendar'); ?></label>
	</p>
	<p>
	<label for="mc_mini_template"><?php _e('Your custom template for events in sidebar/mini calendar output.','my-calendar'); ?></label><br /><textarea id="mc_mini_template" name="mc_mini_template" rows="12" cols="64"><?php echo $mc_mini_template; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Mini Template','my-calendar'); ?> &raquo;" />
	</p>
	</form>
	</div>
</div>
</div>

<div class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('My Calendar: Event Details Page Template','my-calendar'); ?></h3>
	<div class="inside">	
    <form method="post" action="<?php echo admin_url("admin.php?page=my-calendar-templates"); ?>">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<p>
	<input type="checkbox" id="mc_use_details_template" name="mc_use_details_template" value="1"  <?php jd_cal_checkCheckbox('mc_use_details_template',1); ?>/> <label for="mc_use_details_template"><?php _e('Use this details template','my-calendar'); ?></label>
	</p>
	<p>
	<label for="mc_details_template"><?php _e('Your custom template for events on the event details page.','my-calendar'); ?></label><br /><textarea id="mc_details_template" name="mc_details_template" rows="12" cols="64"><?php echo $mc_details_template; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Details Template','my-calendar'); ?> &raquo;" />
	</p>
	</form>
	</div>
</div>
</div>
</div>
<?php
}
?>