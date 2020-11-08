<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_profiles_settings.php                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("");
	$setting_type = "profiles";

	// additional connection
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_profiles_settings.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_profiles_settings_href", "admin_profiles_settings.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");

	$t->set_var("date_edit_format", join("", $date_edit_format));

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}

	$r = new VA_Record($table_prefix . "global_settings");

	// load data to listbox
	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries ORDER BY country_order ", array(array("", "")));
	$admin_templates_dir_values = get_db_values("SELECT layout_id,layout_name FROM " . $table_prefix . "layouts", "");

	$active_values = array(
		array(1, ACTIVE_MSG), array(0, INACTIVE_MSG), 
	);

	$show_reward_credits = array(
		array(0, FOR_ALL_USERS_MSG),
		array(1, ONLY_FOR_LOGGED_IN_USERS_MSG),
	);

	$watermark_positions = array(
		array("", ""),
		array("TL", TOP_LEFT_MSG),
		array("TC", TOP_CENTER_MSG),
		array("TR", TOP_RIGHT_MSG),
		array("ML", MIDDLE_LEFT_MSG),
		array("C",  CENTER_OF_IMAGE_MSG),
		array("MR", MIDDLE_RIGHT_MSG),
		array("BL", BOTTOM_LEFT_MSG),
		array("BC", BOTTOM_CENTER_MSG),
		array("BR", BOTTOM_RIGHT_MSG),
		array("RND", RANDOM_POSITION_MSG),
	);

	$prod_image_types =
		array(
			array(0, DONT_SHOW_IMAGE_MSG),
			array(1, IMAGE_TINY_MSG),
			array(2, IMAGE_SMALL_MSG),
			array(3, IMAGE_LARGE_MSG)
		);

	// Image settings
	$r->add_textbox("no_tiny_image", TEXT);
	$r->add_textbox("no_small_image", TEXT);
	$r->add_textbox("no_large_image", TEXT);
	$r->add_textbox("jpeg_quality", NUMBER);
	$r->change_property("jpeg_quality", MIN_VALUE, 0);
	$r->change_property("jpeg_quality", MAX_VALUE, 100);

	$r->add_textbox("photo_tiny_width", INTEGER);
	$r->add_textbox("photo_tiny_height", INTEGER);
	$r->add_checkbox("photo_tiny_generate", INTEGER);
	$r->add_textbox("photo_small_width", INTEGER);
	$r->add_textbox("photo_small_height", INTEGER);
	$r->add_checkbox("photo_small_generate", INTEGER);
	$r->add_textbox("photo_large_width", INTEGER);
	$r->add_textbox("photo_large_height", INTEGER);
	$r->add_checkbox("photo_large_generate", INTEGER);
	$r->add_textbox("photo_super_width", INTEGER);
	$r->add_textbox("photo_super_height", INTEGER);
	$r->add_checkbox("photo_super_generate", INTEGER);

	// watermark settings
	$r->add_textbox("wm_tiny_image", TEXT);
	$r->add_select("wm_tiny_image_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_tiny_image_pct", INTEGER, IMAGE_TRANSPARENCY_MSG);
	$r->change_property("wm_tiny_image_pct", MIN_VALUE, 0);
	$r->change_property("wm_tiny_image_pct", MAX_VALUE, 100);
	$r->add_checkbox("wm_tiny_transparent", INTEGER);
	$r->add_textbox("wm_tiny_text", TEXT);
	$r->add_select("wm_tiny_text_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_tiny_text_size", INTEGER);
	$r->add_textbox("wm_tiny_text_color", TEXT);
	$r->add_textbox("wm_tiny_text_angle", INTEGER);
	$r->add_textbox("wm_tiny_text_pct", INTEGER, TEXT_TRANSPARENCY_MSG);
	$r->change_property("wm_tiny_text_pct", MIN_VALUE, 0);
	$r->change_property("wm_tiny_text_pct", MAX_VALUE, 100);

	$r->add_textbox("wm_small_image", TEXT);
	$r->add_select("wm_small_image_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_small_image_pct", INTEGER, IMAGE_TRANSPARENCY_MSG);
	$r->change_property("wm_small_image_pct", MIN_VALUE, 0);
	$r->change_property("wm_small_image_pct", MAX_VALUE, 100);
	$r->add_checkbox("wm_small_transparent", INTEGER);
	$r->add_textbox("wm_small_text", TEXT);
	$r->add_select("wm_small_text_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_small_text_size", INTEGER);
	$r->add_textbox("wm_small_text_color", TEXT);
	$r->add_textbox("wm_small_text_angle", INTEGER);
	$r->add_textbox("wm_small_text_pct", INTEGER, TEXT_TRANSPARENCY_MSG);
	$r->change_property("wm_small_text_pct", MIN_VALUE, 0);
	$r->change_property("wm_small_text_pct", MAX_VALUE, 100);

	$r->add_textbox("wm_large_image", TEXT);
	$r->add_select("wm_large_image_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_large_image_pct", INTEGER, IMAGE_TRANSPARENCY_MSG);
	$r->change_property("wm_large_image_pct", MIN_VALUE, 0);
	$r->change_property("wm_large_image_pct", MAX_VALUE, 100);
	$r->add_checkbox("wm_large_transparent", INTEGER);
	$r->add_textbox("wm_large_text", TEXT);
	$r->add_select("wm_large_text_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_large_text_size", INTEGER);
	$r->add_textbox("wm_large_text_color", TEXT);
	$r->add_textbox("wm_large_text_angle", INTEGER);
	$r->add_textbox("wm_large_text_pct", INTEGER, TEXT_TRANSPARENCY_MSG);
	$r->change_property("wm_large_text_pct", MIN_VALUE, 0);
	$r->change_property("wm_large_text_pct", MAX_VALUE, 100);

	$r->add_textbox("wm_super_image", TEXT);
	$r->add_select("wm_super_image_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_super_image_pct", INTEGER, IMAGE_TRANSPARENCY_MSG);
	$r->change_property("wm_super_image_pct", MIN_VALUE, 0);
	$r->change_property("wm_super_image_pct", MAX_VALUE, 100);
	$r->add_checkbox("wm_super_transparent", INTEGER);
	$r->add_textbox("wm_super_text", TEXT);
	$r->add_select("wm_super_text_pos", TEXT, $watermark_positions);
	$r->add_textbox("wm_super_text_size", INTEGER);
	$r->add_textbox("wm_super_text_color", TEXT);
	$r->add_textbox("wm_super_text_angle", INTEGER);
	$r->add_textbox("wm_super_text_pct", INTEGER, TEXT_TRANSPARENCY_MSG);
	$r->change_property("wm_super_text_pct", MIN_VALUE, 0);
	$r->change_property("wm_super_text_pct", MAX_VALUE, 100);
	
	$r->get_form_values();


	$param_site_id = get_session("session_site_id");
	$tab = get_param("tab");

	if (!$tab) { $tab = "general"; }
	//$tab = "images"; // DELETE

	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		} elseif ($operation == "more_categories_columns") {
			$columns_number += 5;
		} else {

			$is_valid = $r->validate();
	  
			if (!$is_valid) {
				$tab = "general";
			}
	  
			if ($is_valid)
			{
				// update product settings
				$sql  = " DELETE FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql($setting_type, TEXT);
				$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
				$db->query($sql);
				foreach ($r->parameters as $key => $value) {
					$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
					$sql .= $db->tosql($setting_type, TEXT) .", '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
					$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
					$db->query($sql);
				}
	  
				// show success message
				$t->parse("success_block", false);			
			}
		}
	} else {
		// get products settings
		foreach ($r->parameters as $key => $value) {
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=".$db->tosql($setting_type, TEXT)." AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	// set parameters
	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));



	// set styles for tabs
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"watermarks" => array("title" => WATERMARK_SETTINGS_MSG), 
	);
	parse_admin_tabs($tabs, $tab, 6);

	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist", false);
	}	

	include_once("./admin_footer.php");
	
	$t->pparse("main");

?>