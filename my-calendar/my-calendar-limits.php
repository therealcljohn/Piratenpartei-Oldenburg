<?php
function mc_select_category($category, $type='event', $group='events' ) {
global $wpdb;
	$select_category = '';
	$data = ($group=='category')?'category_id':'event_category';
	if ( isset( $_GET['mcat'] ) ) { $category = (int) $_GET['mcat']; }
	if ( $category == 'all' || strpos( $category, "all" ) !== false ) {
		return '';
	} else {
 	if ( strpos( $category, "|" ) || strpos( $category, "," ) ) {
		if ( strpos($category, "|" ) ) {
			$categories = explode( "|", $category );
		} else {
			$categories = explode( ",", $category );		
		}
		$numcat = count($categories);
		$i = 1;
		foreach ($categories as $key) {
			if ( is_numeric($key) ) {
				if ($i == 1) {
					$select_category .= ($type=='all')?" WHERE (":' (';
				}				
				$select_category .= " $data = $key";
				if ($i < $numcat) {
					$select_category .= " OR ";
				} else if ($i == $numcat) {
					$select_category .= ($type=='all')?") ":' ) AND';
				}
			$i++;
			} else {
				$cat = $wpdb->get_row("SELECT category_id FROM " . my_calendar_categories_table() . " WHERE category_name = '$key'");
				$category_id = $cat->category_id;
				if ($i == 1) {
					$select_category .= ($type=='all')?" WHERE (":' (';
				}
				$select_category .= " $data = $category_id";
				if ($i < $numcat) {
					$select_category .= " OR ";
				} else if ($i == $numcat) {
					$select_category .= ($type=='all')?") ":' ) AND';
				}
				$i++;						
			}
		}
	} else {
		if ( is_numeric( $category ) ) {
			$select_category = ($type=='all')?" WHERE $data = $category":" event_category = $category AND";
		} else {
		$cat = $wpdb->get_row("SELECT category_id FROM " . my_calendar_categories_table() . " WHERE category_name = '$category'");
			if ( is_object($cat) ) {
				$category_id = $cat->category_id;
				$select_category = ($type=='all')?" WHERE $data = $category_id":" $data = $category_id AND";
			} else {
				$select_category = '';
			}
		}
	}
	return $select_category;
	}
}



function mc_limit_string($type='',$ltype='',$lvalue='') {
global $user_ID;	
	 $user_settings = get_option('mc_user_settings');
	 $limit_string = "event_flagged <> 1";
	 if ( get_option('mc_user_settings_enabled') == 'true' && $user_settings['my_calendar_location_default']['enabled'] == 'on' || isset($_GET['loc']) && isset($_GET['ltype']) || ( $ltype !='' && $lvalue != '' )  ) {
		if ( !isset($_GET['loc']) && !isset($_GET['ltype']) ) {
			if (  $ltype == '' && $lvalue == '' ) {
				if ( is_user_logged_in() ) {
					get_currentuserinfo();
					$current_settings = get_user_meta( $user_ID, 'my_calendar_user_settings' );
					$current_location = $current_settings['my_calendar_location_default'];
					$location_type = get_option('mc_location_type');
				}
			} else if ( $ltype !='' && $lvalue != '' ) {	
				$location_type = $ltype;
				$current_location = $lvalue;
			}
		} else {
			$current_location = urldecode($_GET['loc']);
			$location = urldecode($_GET['ltype']);
				switch ($location) {
					case "name":$location_type = "event_label";
					break;
					case "city":$location_type = "event_city";
					break;
					case "state":$location_type = "event_state";
					break;
					case "zip":$location_type = "event_postcode";
					break;
					case "country":$location_type = "event_country";
					break;
					case "region":$location_type = "event_region";
					break;
					default:$location_type = "event_label";
					break;
				}			
		}
		if ($current_location != 'none' && $current_location != '') {
			//if ($select_category == "") {
				$limit_string = "$location_type='$current_location'";
				$limit_string .= ($type=='all')?' AND':"";
			//} else {
			//	$limit_string = "AND $location_type='$current_location'";
			//	$limit_string .= ($type=='all')?'':'';				
			//}
		}
	 }
	 return $limit_string;
}