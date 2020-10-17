<?php

	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$default_title = "{MY_PROFILES_MSG}";

	check_user_security("profiles");

	// get profiles settings
	$profiles_settings = get_settings("profiles");
	$funds_item_id = get_setting_value($profiles_settings, "funds_item_id", "");

	// get type settings
	$type_settings = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "user_types_settings WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
	$db->query($sql);
	while($db->next_record()) {
		$type_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$profiles_limit = get_setting_value($type_settings, "profiles_limit", 1);


	$errors = "";
	$user_id = get_session("session_user_id");
	$profile_id = get_param("profile_id");
	$operation = get_param("operation");
	$ad_rnd = get_param("ad_rnd");
	$session_rnd = get_session("session_profiles_rnd");

	$html_template = get_setting_value($block, "html_template", "block_profiles_user_list.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("profiles_user_list_href",  get_custom_friendly_url("profiles_user_list.php"));
	$t->set_var("profiles_user_edit_href",   get_custom_friendly_url("profiles_user_edit.php"));
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
	if ($errors) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	} 

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", get_custom_friendly_url("profiles_user_list.php"));
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "p.profile_id");
	$s->set_sorter(NAME_MSG, "sorter_name", "2", "p.name");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("profiles_user_list.php"));

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM ";
	$sql .= $table_prefix . "profiles p";
	$sql .= " WHERE p.user_id=" . $db->tosql($user_id, INTEGER);
	$total_records = get_db_value($sql);

	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT p.profile_id, p.is_shown, p.is_approved, p.profile_name, c.country_code, s.state_code, p.city ";
	$sql .= " FROM ((" . $table_prefix . "profiles p ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON p.country_id=c.country_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON p.state_id=s.state_id) ";
	$sql .= " WHERE p.user_id=" . $db->tosql($user_id, INTEGER);
	$db->query($sql . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");

		do {
			$profile_id = $db->f("profile_id");
			$is_shown = $db->f("is_shown");
			$is_approved = $db->f("is_approved");
			$profile_name = $db->f("profile_name");
			$country_code = $db->f("country_code");
			$state_code = $db->f("state_code");
			$city = $db->f("city");
			$address = $country_code;
			if (strlen($state_code)) { $address .= ", ".$state_code; }
			if (strlen($city)) { $address .= ", ".$city; }

			$t->set_var("profile_id", $profile_id);
			$t->set_var("profile_name", $profile_name);
			$t->set_var("address", $address);

			if ($is_approved != 1) {
				$status = "<font color=\"red\"><b>".NOT_APPROVED_MSG."</b></font>";
			} else if ($is_shown != 1) {
				$status = "<font color=\"grey\"><b>".INACTIVE_MSG."</b></font>";
			}	else {
				$status = "<font color=\"blue\"><b>".ACTIVE_MSG."</b></font>";
			}
			$t->set_var("status", $status);

			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	if ($total_records < $profiles_limit) {
		$t->parse("new_profile_link", false);
	}

	$block_parsed = true;

?>