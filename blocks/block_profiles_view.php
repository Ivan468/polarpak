<?php

	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$default_title = "{profile_name}, {age}";

	$user_id = get_session("session_user_id");
	$profile_id = get_param("pid");
	$current_date = va_time();
	$site_url = get_setting_value($settings, "site_url");

	$profile_settings = get_settings("profile_settings");
	$default_photo = get_setting_value($profile_settings, "photo_small_default", "images/no_photo.gif");

	if (strlen($profile_id)) {
		// check if selected profile exists 
		$sql  = " SELECT profile_id FROM " . $table_prefix . "profiles ";
		$sql .= " WHERE profile_id=" . $db->tosql($profile_id, INTEGER);
		$db->query($sql);
		if (!$db->next_record()) {
			header("Location: index.php");
			exit;
		}
	}

	$eol = get_eol();
	set_script_tag("js/images.js");

	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$html_template = get_setting_value($block, "html_template", "block_profiles_view.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("site_url",        $settings["site_url"]);
	$t->set_var("user_home_href",  get_custom_friendly_url("user_home.php"));
	$t->set_var("profiles_user_list_href",   get_custom_friendly_url("profiles_user_list.php"));
	$t->set_var("profiles_user_edit_href",    get_custom_friendly_url("profiles_user_edit.php"));
	$t->set_var("user_upload_href",get_custom_friendly_url("user_upload.php"));
	$t->set_var("user_select_href",get_custom_friendly_url("user_select.php"));
	$t->set_var("profiles_terms_href", get_custom_friendly_url("profiles_terms.php"));
	$t->set_var("photo_upload_href", get_custom_friendly_url("photo_upload.php"));

	$t->set_var("date_edit_format",join("", $date_edit_format));
	$t->set_var("date_format_msg", $date_format_msg);

	$states     = get_db_values("SELECT state_id,state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ", array(array("", SELECT_STATE_MSG)));
	$countries  = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", SELECT_COUNTRY_MSG)));
	$ethnicities = get_db_values("SELECT ethnicity_id,ethnicity_name FROM " . $table_prefix . "ethnicities ORDER BY sort_order ", array(array("", "")));

	$height_values = array(
		"150" => "< 5' 0\" (< 152 cm)",
		"152" => "5' 0\" (152 cm)",
		"155" => "5' 1\" (155 cm)",
		"157" => "5' 2\" (157 cm)",
		"160" => "5' 3\" (160 cm)",
		"163" => "5' 4\" (163 cm)",
		"165" => "5' 5\" (165 cm)",
		"168" => "5' 6\" (168 cm)",
		"170" => "5' 7\" (170 cm)",
		"173" => "5' 8\" (173 cm)",
		"175" => "5' 9\" (175 cm)",
		"178" => "5' 10\" (178 cm)",
		"180" => "5' 11\" (180 cm)",
		"183" => "6' 0\" (183 cm)",
		"185" => "6' 1\" (185 cm)",
		"188" => "6' 2\" (188 cm)",
		"191" => "6' 3\" (191 cm)",
		"193" => "6' 4\" (193 cm)",
		"196" => "6' 5\" (196 cm)",
		"198" => "6' 6\" (198 cm)",
		"201" => "6' 7\" (201 cm)",
		"203" => "6' 8\" (203 cm)",
		"206" => "6' 9\" (206 cm)",
		"208" => "6' 10\" (208 cm)",
		"211" => "6' 11\" (211 cm)",
		"213" => "7' 0\" (213 cm)",
		"215" => "> 7' (> 213 cm)",
	);

	$sex_values = array(
		array("", ""),
		array("1", MALE_MSG),
		array("2", FEMALE_MSG),
	);


	// get profile data
	$sql  = " SELECT u.nickname, d.profile_id, d.profile_name, d.birth_date, d.city, ";
	$sql .= " d.profile_info, d.looking_info, d.height, ";
	$sql .= " d.photo_id, up.tiny_photo, up.small_photo, up.large_photo, up.super_photo, ";
	$sql .= " c.country_name,s.state_name,e.ethnicity_name ";
	$sql .= " FROM " . $table_prefix . "profiles d ";
	$sql .= " INNER JOIN " . $table_prefix . "users u ON u.user_id=d.user_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "users_photos up ON up.photo_id=d.photo_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=d.country_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=d.state_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "ethnicities e ON e.ethnicity_id=d.ethnicity_id ";
	$sql .= " WHERE profile_id=" . $db->tosql($profile_id, INTEGER);
	$sql .= " AND d.is_shown=1 ";
	$db->query($sql);
	if ($db->next_record()) {
		// check nickname to send message
		$nickname = $db->f("nickname");

		$profile_id = $db->f("profile_id");
		$birth_date = $db->f("birth_date", DATETIME);
		$birth_date_ts = va_timestamp($birth_date);
		$age = $current_date[YEAR] - $birth_date[YEAR];
		if ($birth_date[MONTH] < $current_date[MONTH] || ($birth_date[MONTH] == $current_date[MONTH] && $birth_date[DAY] < $current_date[DAY])) {
			$age--;
		}

		$user_id = $db->f("user_id");
		$name = $db->f("profile_name");
		$profile_name = $db->f("profile_name");
		$country_name = get_translation($db->f("country_name"));
		$state_name = get_translation($db->f("state_name"));
		$ethnicity_name = get_translation($db->f("ethnicity_name"));
		$city = get_translation($db->f("city"));
		$personal_info = $db->f("profile_info");
		$profile_info = $db->f("profile_info");
		$looking_info = $db->f("looking_info");
		$height = $db->f("height");
	
		$t->set_var("profile_id", $profile_id);
		$t->set_var("profile_name", $profile_name);
		$t->set_var("name", $name);
		$t->set_var("age", $age);

		$t->set_var("country_name", htmlspecialchars($country_name));

		$t->set_var("state_block", "");
		if ($state_name) {
			$t->set_var("state_name", htmlspecialchars($state_name));
			$t->sparse("state_block", false);
		}
		$t->set_var("city_block", "");
		if ($city) {
			$t->set_var("city", htmlspecialchars($city));
			$t->sparse("city_block", false);
		}

		$t->set_var("personal_info_block", "");
		if ($personal_info) {
			$personal_info = strip_tags($personal_info);
			$t->set_var("personal_info", htmlspecialchars($personal_info));
			$t->sparse("personal_info_block", false);
		}

		$t->set_var("looking_info_block", "");
		if ($looking_info) {
			$looking_info = strip_tags($looking_info);
			$t->set_var("looking_info", htmlspecialchars($looking_info));
			$t->sparse("looking_info_block", false);
		}

		$t->set_var("ethnicity_block", "");
		if ($city) {
			$t->set_var("ethnicity_name", htmlspecialchars($ethnicity_name));
			$t->sparse("ethnicity_block", false);
		}

		$t->set_var("height_block", "");
		if ($height && $height_values[$height]) {
			$t->set_var("height", htmlspecialchars($height_values[$height]));
			$t->sparse("height_block", false);
		}

		// show profile photo 	
		$photo_id = $db->f("photo_id");
		$tiny_photo = $db->f("tiny_photo");
		$small_photo = $db->f("small_photo");
		$large_photo = $db->f("large_photo");
		$super_photo = $db->f("super_photo");

		if (!strlen($large_photo) || !image_exists($large_photo)) {
			$image_exists = false;
			$large_photo = $default_photo;
		} else {
			$image_exists = true;
		}
		if ($large_photo) {
			$image_size = @GetImageSize($large_photo);
			if ($image_exists) {
				$photo_vc = md5($large_photo);
				$large_photo = "photo.php?id=".urlencode($photo_id)."&type=large&vc=".urlencode($photo_vc);
			}
			if ($super_photo) {
				$photo_vc = md5($super_photo);
				$super_photo_src = "photo.php?id=".urlencode($photo_id)."&type=super&vc=".urlencode($photo_vc);
			} else {
				$super_photo_src = $large_photo;
			}

    	$t->set_var("alt", htmlspecialchars($name));
    	$t->set_var("photo_src", htmlspecialchars($large_photo));
    	$t->set_var("super_photo_src", htmlspecialchars($super_photo_src));
			if(is_array($image_size)) {
				$t->set_var("photo_size", $image_size[2] );
			} else {
				$t->set_var("photo_size", "");
			}
			$t->parse("profile_photo", false);
		} else {
			$t->set_var("profile_photo", "");
		}

		// check additional photos to show
		$t->set_var("profile_photos", "");
		$sql  = " SELECT * FROM " . $table_prefix . "users_photos ";
		$sql .= " WHERE photo_type=1 AND key_id=" . $db->tosql($profile_id, INTEGER);
		$sql .= " AND photo_id<>" . $db->tosql($photo_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$photo_id = $db->f("photo_id");
			$photo_name = $db->f("photo_name");
			$tiny_photo = $db->f("tiny_photo");
			$small_photo = $db->f("small_photo");
			$large_photo = $db->f("large_photo");
			$super_photo = $db->f("super_photo");
			$image_size = "";
			if ($small_photo) {
				$image_size = @GetImageSize($small_photo);
				if ($image_exists) {
					$photo_vc = md5($small_photo);
					$small_photo_src = "photo.php?id=".urlencode($photo_id)."&type=small&vc=".urlencode($photo_vc);
				}
				if ($super_photo) {
					$photo_vc = md5($super_photo);
					$super_photo_src = "photo.php?id=".urlencode($photo_id)."&type=super&vc=".urlencode($photo_vc);
				} else if ($large_photo) {
					$photo_vc = md5($large_photo);
					$super_photo_src = "photo.php?id=".urlencode($photo_id)."&type=large&vc=".urlencode($photo_vc);
				}
			}

    	$t->set_var("photo_alt", htmlspecialchars($photo_name));
    	$t->set_var("small_photo_src", htmlspecialchars($small_photo_src));
    	$t->set_var("super_photo_src", htmlspecialchars($super_photo_src));
			if(is_array($image_size)) {
				$t->set_var("photo_size", $image_size[2] );
			} else {
				$t->set_var("photo_size", "");
			}
			$t->parse("profile_photos", true);

/*
  [is_shown] BYTE,
  [is_approved] BYTE,
  [photo_name] VARCHAR(255),
  [photo_desc] LONGTEXT,
  [tiny_photo] VARCHAR(255),
  [small_photo] VARCHAR(255),
  [large_photo] VARCHAR(255),
  [super_photo] VARCHAR(255),///*/

		}


		// send message url
		$send_message_url = new VA_URL("user_messages.php");
		$send_message_url->add_parameter("mtid", CONSTANT, 15);
		$send_message_url->add_parameter("mkid", CONSTANT, $profile_id);
		$send_message_url->add_parameter("message_to", CONSTANT, $nickname);
		$send_message_url->add_parameter("operation", CONSTANT, "new");
		$t->set_var("send_message_url", $send_message_url->get_url());
	} else {
		header("Location: ".$site_url);
		exit;
	}

/*	
	$r = new VA_Record($table_prefix . "dating");

	// set up html form parameters
	$r->add_where("profile_id", INTEGER);
	$r->change_property("profile_id", USE_IN_INSERT, true);

	$r->add_where("user_id", INTEGER);
	$r->change_property("user_id", USE_IN_INSERT, true);
	$r->add_checkbox("is_shown", INTEGER);
	$r->add_textbox("is_approved", INTEGER);
	$r->add_textbox("photo_id", INTEGER);
	$r->change_property("photo_id", AFTER_REQUEST, "check_photo");

	$r->add_select("dating_sex_id", INTEGER, $sex_values, DATING_SEX_FIELD);
	$r->change_property("dating_sex_id", REQUIRED, true);
	$r->add_select("looking_sex_id", INTEGER, $sex_values, LOOKING_SEX_FIELD);
	$r->change_property("looking_sex_id", REQUIRED, true);

	$r->add_textbox("name", TEXT, NAME_MSG);
	$r->change_property("name", REQUIRED, true);
	$r->add_select("ethnicity_id", INTEGER, $ethnicities, ETHNICITY_MSG);
	$r->add_select("height", INTEGER, $height_values, HEIGHT_MSG);


	// internal fields
	$r->add_textbox("date_added",   DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_updated", DATETIME);
	$r->add_textbox("date_last_visit", DATETIME);
*/

	$profile_id = get_param("profile_id");
	$operation = get_param("operation");
	$current_tab = get_param("current_tab");
	if (!$current_tab) { $current_tab = "general"; }
	$return_page = get_custom_friendly_url("profiles_user_list.php");
	$properties = array();
	$features = array();
	$images = array();
	$is_valid = true;

	$photo_id = "";
	if (strlen($photo_id)) {
		$photo_src = "photo.php?id=".urlencode($photo_id)."&type=large";
		$t->set_var("photo_src", $photo_src);
		$t->parse("profile_photo", false);
	} else {
		$t->sparse("no_profile_photo", false);
	}


	// set styles for tabs
	$tabs = array(
		"general" => array("title" => GENERAL_TAB, "show" => true), 
		"desc" => array("title" => DESCRIPTION_TAB, "show" => false), 
		"location" => array("title" => LOCATION_TAB, "show" => false), 
		"images" => array("title" => IMAGES_MSG, "show" => false), 
	);
	//parse_tabs($tabs, $current_tab);

	$block_parsed = true;

	function birth_date_check()
	{
		global $r;
		$birth_month = $r->get_value("birth_month");
		$birth_day = $r->get_value("birth_day");
		$birth_year = $r->get_value("birth_year");
		if ($birth_month && $birth_day && $birth_year) {
			if (checkdate($birth_month, $birth_day, $birth_year)) {
				$birth_date_ts = mktime(0,0,0, $birth_month, $birth_day, $birth_year);
				$r->set_value("birth_date", $birth_date_ts);
			} else {
				$error_desc = str_replace("{field_name}", BIRTHDAY_MSG, INCORRECT_DATE_MESSAGE);
				$r->change_property("birth_date", IS_VALID, false);
				$r->change_property("birth_date", ERROR_DESC, $error_desc);
			}
		} 
	}

function check_photo()
{
	global $r, $db, $table_prefix;
	$photo_id = $r->get_value("photo_id");
	$user_id = get_session("session_user_id");
	$sql  = " SELECT user_id FROM " . $table_prefix . "users_photos ";
	$sql .= " WHERE photo_id=" . $db->tosql($photo_id, INTEGER);
	$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
	$db->query($sql);
	if (!$db->next_record()) {
		// clear photo value
		$r->set_value("photo_id", "");
	}
}



?>