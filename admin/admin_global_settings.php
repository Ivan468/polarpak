<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_global_settings.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once("./admin_common.php");

	check_admin_security("site_settings");

	$va_trail = array(
		"admin_menu.php?code=settings" => va_constant("SETTINGS_MSG"),
		"admin_menu.php?code=system-settings" => va_constant("SYSTEM_MSG"),
		"admin_global_settings.php" => va_constant("GLOBAL_SETTINGS_MSG"),
	);

	$setting_type = "global";
	$va_version_code = va_version_code();
	$current_site_url = get_setting_value($settings, "site_url", "");
	$parsed_url = parse_url($current_site_url);
	$friendly_path = isset($parsed_url["path"]) ? $parsed_url["path"] : "/";
	$domain_start_regexp = "/^http(s)?:\\/\\//i";

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
	$t->set_file("main", "admin_global_settings.html");
	include_once("./admin_header.php");

	$t->set_var("admin_global_settings_href", "admin_global_settings.php");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_design_scheme_href", "admin_design_scheme.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("footer_url", "admin_menu_list.php");
	$t->set_var("friendly_path", $friendly_path);
	$t->set_var("current_dir", htmlspecialchars(getcwd()));
	
	$r = new VA_Record($table_prefix . "global_settings");

	// load data to listbox
	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ", array(array("", "")));
	$states = get_db_values("SELECT state_id, state_name FROM " . $table_prefix . "states ORDER BY state_name ", array(array("", "")));
	
	$records_per_page = 
		array( 
			array(5, 5), array(10, 10), array(15, 15),
			array(20, 20), array(25, 25), array(50, 50),
			array(75, 75), array(100, 100)
			);

	$validation_types = 
		array( 
			array(2, va_message("FOR_ALL_USERS_MSG")), array(1, va_message("UNREGISTERED_USER_ONLY_MSG")), array(0, va_message("NOT_USED_MSG"))
		);

	$yes_no = 
		array( 
			array(1, va_message("YES_MSG")), array(0, va_message("NO_MSG"))
			);

	$password_encrypt = 
		array( 
			array(0, va_message("NONE_MSG")), 
			array(1, va_message("USE_MD5_ENCRYPTION_MSG"))
			);


	$html_editors = 
		array( 
			array(1, va_message("WYSIWYG_HTML_EDITOR_MSG")),
			array(0, va_message("TEXTAREA_EDITOR_MSG")),
			array(2, va_message("EXTERNAL_CKEDITOR_MSG"))
			);

	$friendly_auto_options =
		array( 
			array(0, va_message("DONT_GENERATE_FRIENDLY_URL_MSG")),
			array(1, va_message("ALWAYS_GENERATE_FRIENDLY_URL_MSG")),
			array(2, va_message("GENERATE_FRIENDLY_URL_MANUALLY_MSG"))
			);

	$friendly_transform_options =
		array( 
			array(0, va_message("DONT_TRANSFORM_MSG")),
			array(1, va_message("LOWERCASE_TRANSFORM_MSG")),
			array(2, va_message("UPPERCASE_TRANSFORM_MSG")),
			array(3, va_message("UCWORDS_TRANSFORM_MSG")),
			array(4, va_message("UCFIRST_TRANSFORM_MSG")),
			);

	$friendly_extensions =
		array( 
			array("", "[without extension] "),
			array(".html", ".html "),
			array(".htm", ".htm "),
			array(".php", ".php "),
			);

	//SMS notifications for unregistered users:
	$sms_allowed_options =
		array( 
			array(0, va_message("SMS_NOTE_NOT_ALLOWED_MSG")),
			array(1, va_message("SMS_NOTIFY_ALLOWED_MSG")),
			array(2, va_message("SMS_NOTE_ALLOWED_LIST_MSG")),
			);

	$welcome_popup_options =
		array( 
			array("never", va_message("DONT_SHOW_MSG")),
			array("once", va_message("SHOW_ONLY_ONCE_MSG")),
			array("every", va_message("SHOW_EVERY_TIME_MSG")),
			);

	$welcome_layouts =
		array( 
			array("default", va_message("DEFAULT_MSG")),
			array("none", va_message("NONE_MSG")),
			);

	$length_units =
		array( 
			array("mm", va_message("LENGTH_MILIMETRE_MSG")),
			array("cm", va_message("LENGTH_CENTIMETRE_MSG")),
			array("m", va_message("LENGTH_METRE_MSG")),
			array("in", va_message("LENGTH_INCH_MSG")),
			array("ft", va_message("LENGTH_FOOT_MSG")),
			array("yd", va_message("LENGTH_YARD_MSG")),
		);

	$int_length_units =
		array( 
			array("mm", va_message("LENGTH_MILIMETRE_MSG")),
			array("cm", va_message("LENGTH_CENTIMETRE_MSG")),
			array("m", va_message("LENGTH_METRE_MSG")),
		);

	$imp_length_units =
		array( 
			array("in", va_message("LENGTH_INCH_MSG")),
			array("ft", va_message("LENGTH_FOOT_MSG")),
			array("yd", va_message("LENGTH_YARD_MSG")),
		);

	$length_systems =
		array( 
			array("int", va_message("INTERNATIONAL_SYSTEM_MSG")),
			array("imp", va_message("IMPERIAL_SYSTEM_MSG")),
		);

	$precision_types =
		array( 
			array("integer", va_message("NEAREST_INTEGER_MSG")),
			array("decimal", va_message("DECIMAL_MSG")),
			array("fractional", va_message("FRACTIONAL_MSG")),
		);

	$weight_units =
		array( 
			array("g", va_message("WEIGHT_GRAM_MSG")),
			array("kg", va_message("WEIGHT_KILOGRAM_MSG")),
			array("oz", va_message("WEIGHT_OUNCE_MSG")),
			array("lb", va_message("WEIGHT_POUND_MSG")),
		);

	$weight_systems =
		array( 
			array("int", va_message("INTERNATIONAL_SYSTEM_MSG")),
			array("imp", va_message("IMPERIAL_SYSTEM_MSG")),
		);

	$int_weight_units =
		array( 
			array("g", va_message("WEIGHT_GRAM_MSG")),
			array("kg", va_message("WEIGHT_KILOGRAM_MSG")),
		);

	$imp_weight_units =
		array( 
			array("oz", va_message("WEIGHT_OUNCE_MSG")),
			array("lb", va_message("WEIGHT_POUND_MSG")),
		);


	$param_site_id = get_session("session_site_id");
	$sql  = " SELECT lt.layout_id, lt.layout_name FROM " . $table_prefix . "layouts AS lt"; 
	$sql .= " LEFT JOIN " . $table_prefix . "layouts_sites AS st ON st.layout_id = lt.layout_id ";		
	$sql .= " WHERE (lt.sites_all=1 OR st.site_id=".$db->tosql($param_site_id, INTEGER).") ";	
	$sql .= " GROUP BY lt.layout_id, lt.layout_name ";	
	$admin_templates_dir_values = get_db_values($sql, "");
			
	// set up parameters
	$r->add_textbox("site_name", TEXT, va_message("SITE_NAME_MSG"));
	$r->change_property("site_name", REQUIRED, true);
	$r->change_property("site_name", USE_IN_INSERT, false);
	$r->add_textbox("site_url", TEXT, va_message("SITE_URL_MSG")." (".va_message("FRONT_END_MSG").")");
	$r->change_property("site_url", REQUIRED, true);
	$r->change_property("site_url", REGEXP_MASK, $domain_start_regexp);
	$r->change_property("site_url", USE_IN_INSERT, false);
	$r->add_textbox("admin_url", TEXT, va_message("SITE_URL_MSG")." (".va_message("BACK_END_MSG").")");
	$r->change_property("admin_url", REGEXP_MASK, $domain_start_regexp);
	$r->change_property("admin_url", USE_IN_INSERT, false);
	$r->add_radio("full_image_url", INTEGER, $yes_no);
	$r->add_textbox("admin_email", TEXT);
	$r->change_property("admin_email", REQUIRED, true);
	$r->add_select("country_id", INTEGER, $countries);
	$r->add_select("state_id", INTEGER, $states);
	$r->add_checkbox("use_default_language", INTEGER);
	$r->add_checkbox("phone_code_select", INTEGER);
	$r->add_select("layout_id", INTEGER, $admin_templates_dir_values);
	$r->add_radio("password_encrypt", INTEGER, $password_encrypt);
	$r->change_property("password_encrypt", BEFORE_SHOW_VALUE, "disable_password_encrypt");
	$r->add_radio("admin_password_encrypt", INTEGER, $password_encrypt);
	$r->change_property("admin_password_encrypt", BEFORE_SHOW_VALUE, "disable_admin_password_encrypt");
	if ($param_site_id > 1) {
		$r->change_property("password_encrypt", SHOW, false);
		$r->change_property("admin_password_encrypt", SHOW, false);
		$r->change_property("password_encrypt", USE_IN_INSERT, false);
		$r->change_property("admin_password_encrypt", USE_IN_INSERT, false);
	}
	$r->add_textbox("weight_measure", TEXT);
	
	//editors tab
	$r->add_radio("html_editor", INTEGER, $html_editors);
	$r->add_radio("html_editor_products", INTEGER, $html_editors);
	$r->add_radio("html_editor_ads", INTEGER, $html_editors);
	$r->add_radio("html_editor_articles", INTEGER, $html_editors);
	$r->add_radio("html_editor_manuals", INTEGER, $html_editors);
	$r->add_radio("html_editor_email", INTEGER, $html_editors);
	$r->add_radio("html_editor_custom_blocks", INTEGER, $html_editors);
	$r->add_radio("html_editor_custom_pages", INTEGER, $html_editors);

	// run php code
	$r->add_checkbox("php_in_footer_body", INTEGER);
	$r->add_checkbox("php_in_custom_blocks", INTEGER);
	$r->add_checkbox("php_in_custom_pages", INTEGER);

	$r->add_textbox("tmp_dir", TEXT, va_message("TEMP_FOLDER_MSG"));
	$r->change_property("tmp_dir", BEFORE_VALIDATE, "check_tmp_dir");

	// logo settings
	$r->add_textbox("favicon", TEXT);
	$r->add_textbox("logo_image", TEXT);
	$r->add_textbox("logo_image_alt", TEXT);
	$r->add_textbox("logo_image_width", INTEGER, va_message("WIDTH_MSG"));
	$r->add_textbox("logo_image_height", INTEGER, va_message("HEIGHT_MSG"));

	// text editor settings
	$r->add_checkbox("user_image_upload", INTEGER);
	$r->add_textbox("user_image_size", INTEGER);
	$r->add_textbox("user_image_width", INTEGER);
	$r->add_textbox("user_image_height", INTEGER);
	$r->add_checkbox("show_preview_image_admin", INTEGER);
	$r->add_checkbox("show_preview_image_client", INTEGER);

	$r->add_radio("is_sms_allowed", INTEGER, $sms_allowed_options);

	$r->add_textbox("secure_url", TEXT, va_message("SECURE_SITE_URL_MSG"));
	$r->change_property("secure_url", REGEXP_MASK, $domain_start_regexp);
	$r->change_property("secure_url", BEFORE_VALIDATE, "check_secure_url");
	$r->add_checkbox("secure_user_login", INTEGER);
	$r->add_checkbox("secure_user_profile", INTEGER);

	if ($va_version_code & 1) {
		$r->add_checkbox("secure_order_profile", INTEGER);
		$r->add_checkbox("secure_payments", INTEGER);
		$r->add_checkbox("secure_merchant_order", INTEGER);
		$r->add_checkbox("ssl_admin_order_details", INTEGER);
		$r->add_checkbox("secure_admin_order_create", INTEGER);
		$r->add_checkbox("ssl_admin_orders_list", INTEGER);
		$r->add_checkbox("ssl_admin_orders_pages", INTEGER);
	}
	if ($va_version_code & 4) {
		$r->add_checkbox("secure_user_tickets", INTEGER);
		$r->add_checkbox("secure_user_ticket", INTEGER);
		$r->add_checkbox("ssl_admin_tickets", INTEGER);
		$r->add_checkbox("ssl_admin_ticket", INTEGER);
		$r->add_checkbox("ssl_admin_helpdesk", INTEGER);
	}

	$r->add_checkbox("secure_admin_login", INTEGER);
	$r->add_checkbox("secure_redirect", INTEGER);

	// length units fields
	$r->add_radio("length_unit", TEXT, $length_units);
	$r->add_radio("length_system", TEXT, $length_systems);

	$r->add_checkboxlist("int_length_units", TEXT, $int_length_units);
	$r->change_property("int_length_units", USE_IN_INSERT, true);
	$r->add_radio("int_length_precision", TEXT, $precision_types);
	$r->add_textbox("int_length_decimals", INTEGER, va_message("INTERNATIONAL_LENGTH_MSG")." (".va_message("NUMBER_OF_DECIMALS_MSG").")");
	$r->change_property("int_length_decimals", MIN_VALUE, 0);
	$r->change_property("int_length_decimals", MAX_VALUE, 10);
	$r->add_textbox("int_length_denominator", INTEGER, va_message("INTERNATIONAL_LENGTH_MSG")." (".va_message("FRACTIONAL_DENOMINATOR_MSG").")");
	$r->change_property("int_length_denominator", MIN_VALUE, 0);

	$r->add_checkboxlist("imp_length_units", TEXT, $imp_length_units);
	$r->change_property("imp_length_units", USE_IN_INSERT, true);
	$r->add_radio("imp_length_precision", TEXT, $precision_types);
	$r->add_textbox("imp_length_decimals", INTEGER, va_message("IMPERIAL_LENGTH_MSG")." (".va_message("NUMBER_OF_DECIMALS_MSG").")");
	$r->change_property("imp_length_decimals", MIN_VALUE, 0);
	$r->change_property("imp_length_decimals", MAX_VALUE, 10);
	$r->add_textbox("imp_length_denominator", INTEGER, va_message("IMPERIAL_LENGTH_MSG")." (".va_message("FRACTIONAL_DENOMINATOR_MSG").")");
	$r->change_property("imp_length_denominator", MIN_VALUE, 0);

	// weight units fields
	$r->add_radio("weight_unit", TEXT, $weight_units);
	$r->add_radio("weight_system", TEXT, $weight_systems);

	$r->add_checkboxlist("int_weight_units", TEXT, $int_weight_units);
	$r->change_property("int_weight_units", USE_IN_INSERT, true);
	$r->add_radio("int_weight_precision", TEXT, $precision_types);
	$r->add_textbox("int_weight_decimals", INTEGER, va_message("INTERNATIONAL_WEIGHT_MSG")." (".va_message("NUMBER_OF_DECIMALS_MSG").")");
	$r->change_property("int_weight_decimals", MIN_VALUE, 0);
	$r->change_property("int_weight_decimals", MAX_VALUE, 10);
	$r->add_textbox("int_weight_denominator", INTEGER, va_message("INTERNATIONAL_WEIGHT_MSG")." (".va_message("FRACTIONAL_DENOMINATOR_MSG").")");
	$r->change_property("int_weight_denominator", MIN_VALUE, 0);

	$r->add_checkboxlist("imp_weight_units", TEXT, $imp_weight_units);
	$r->change_property("imp_weight_units", USE_IN_INSERT, true);
	$r->add_radio("imp_weight_precision", TEXT, $precision_types);
	$r->add_textbox("imp_weight_decimals", INTEGER, va_message("IMPERIAL_WEIGHT_MSG")." (".va_message("NUMBER_OF_DECIMALS_MSG").")");
	$r->change_property("imp_weight_decimals", MIN_VALUE, 0);
	$r->change_property("imp_weight_decimals", MAX_VALUE, 10);
	$r->add_textbox("imp_weight_denominator", INTEGER, va_message("IMPERIAL_WEIGHT_MSG")." (".va_message("FRACTIONAL_DENOMINATOR_MSG").")");
	$r->change_property("imp_weight_denominator", MIN_VALUE, 0);


	$r->add_checkbox("friendly_urls", INTEGER, va_message("ACTIVATE_FRIENDLY_URLS_MSG"));
	$r->change_property("friendly_urls", BEFORE_VALIDATE, "check_friendly_htaccess");
	$r->add_checkbox("friendly_url_redirect", INTEGER);
	$r->add_radio("friendly_auto", INTEGER, $friendly_auto_options);
	$r->add_radio("friendly_extension", TEXT, $friendly_extensions);
	$r->add_select("friendly_transform", INTEGER, $friendly_transform_options);
	
	// Google related fields
	$r->add_checkbox("google_analytics", INTEGER);
	$r->add_checkbox("google_universal", INTEGER);
	$r->add_textbox("google_tracking_code", TEXT);
	$r->change_property("google_tracking_code", TRIM, TRUE);

	// tracking fields
	$r->add_textbox("online_time", INTEGER);
	$r->add_checkbox("tracking_visits", INTEGER);
	$r->add_checkbox("tracking_pages", INTEGER);

	$r->add_textbox("min_rating", FLOAT);
	$r->add_textbox("min_votes", INTEGER);

	$r->add_textbox("footer_head", TEXT);
	$r->add_textbox("html_below_footer", TEXT);

	$r->add_textbox("head_html", TEXT);
	$r->change_property("head_html", PARSE_NAME, "head_html_edit");

	// SMTP settings
	$r->add_checkbox("smtp_mail", INTEGER);
	$r->add_textbox("smtp_host", TEXT);
	$r->add_textbox("smtp_port", INTEGER);
	$r->add_textbox("smtp_timeout", INTEGER);
	$r->add_textbox("smtp_username", TEXT);
	$r->add_textbox("smtp_password", TEXT);
	
	// Email function settings
	$r->add_textbox("email_additional_headers", TEXT);
	$r->add_textbox("email_additional_parameters", TEXT);
	
	// PGP settings
	$r->add_textbox("pgp_binary", TEXT);
	$r->add_textbox("pgp_home", TEXT);
	$r->add_textbox("pgp_tmp", TEXT);
	$r->add_textbox("pgp_keyserver", TEXT);
	$r->add_textbox("pgp_proxy", TEXT);
	$r->add_checkbox("pgp_ascii", INTEGER);
	$r->change_property("pgp_keyserver", SHOW, false); 

	// cookie bar settings
	$r->add_checkbox("cookie_bar", TEXT);
	$r->add_textbox("cookie_consent_time", INTEGER);
	$r->add_textbox("cookie_bar_message", TEXT);

	$r->add_checkbox("cookie_necessary_show", TEXT);
	$r->add_textbox("cookie_necessary_name", TEXT);
	$r->add_textbox("cookie_necessary_message", TEXT);

	$r->add_checkbox("cookie_analytics_show", TEXT);
	$r->add_checkbox("cookie_analytics_disable", TEXT);
	$r->add_textbox("cookie_analytics_name", TEXT);
	$r->add_textbox("cookie_analytics_message", TEXT);

	$r->add_checkbox("cookie_personal_show", TEXT);
	$r->add_checkbox("cookie_personal_disable", TEXT);
	$r->add_textbox("cookie_personal_name", TEXT);
	$r->add_textbox("cookie_personal_message", TEXT);

	$r->add_checkbox("cookie_target_show", TEXT);
	$r->add_checkbox("cookie_target_disable", TEXT);
	$r->add_textbox("cookie_target_name", TEXT);
	$r->add_textbox("cookie_target_message", TEXT);

	$r->add_checkbox("cookie_other_show", TEXT);
	$r->add_checkbox("cookie_other_disable", TEXT);
	$r->add_textbox("cookie_other_name", TEXT);
	$r->add_textbox("cookie_other_message", TEXT);

	// welcome settings
	$r->add_radio("welcome_popup", TEXT, $welcome_popup_options);
	$r->add_radio("welcome_layout", TEXT, $welcome_layouts);
	$r->add_textbox("welcome_delay", INTEGER);
	$r->add_textbox("welcome_code", TEXT);
	$r->add_textbox("welcome_block", TEXT);


	// Offline settings
	if ($param_site_id == 1) {
		$r->add_radio("site_offline", INTEGER, $yes_no);
		$r->add_textbox("offline_message", TEXT);
	}

	// site map settings	
	$sm = new VA_Record($table_prefix . "global_settings");
	$sm->add_checkbox("site_map_custom_pages", INTEGER);
	$sm->add_checkbox("remove_prod_dup", INTEGER);
	$sm->add_checkbox("remove_art_dup", INTEGER);

	$sm->add_textbox("site_map_folder", TEXT);
	$sm->add_textbox("site_map_filename", TEXT);

	if ($va_version_code & 1) {
		$sm->add_checkbox("site_map_manufacturers", INTEGER);
		$sm->add_checkbox("site_map_categories", INTEGER);
		$sm->add_checkbox("site_map_items", INTEGER);
		$sm->change_property("site_map_items", SHOW, true);
	} else {
		$sm->change_property("site_map_items", SHOW, false);
	}
	if ($va_version_code & 8) {
		$sm->add_checkbox("site_map_forums", INTEGER);
		$sm->add_checkbox("site_map_forum_categories", INTEGER);
		$sm->change_property("site_map_forum_categories", SHOW, true);
	} else {
		$sm->change_property("site_map_forum_categories", SHOW, false);
	}
	if ($va_version_code & 16) {
		$sm->add_checkbox("site_map_ad_categories", INTEGER);
		$sm->add_checkbox("site_map_ads", INTEGER);
		$sm->change_property("site_map_ads", SHOW, true);
	} else {
		$sm->change_property("site_map_ads", SHOW, false);
	}
	if ($va_version_code & 32) {
		$sm->add_checkbox("site_map_manuals", INTEGER);
		$sm->add_checkbox("site_map_manual_articles", INTEGER);
		$sm->add_checkbox("site_map_manual_categories", INTEGER);
		$sm->change_property("site_map_manual_categories", SHOW, true);
	} else {
		$sm->change_property("site_map_manual_categories", SHOW, false);
	}
	// sitemap articles
	$articles_categories = array();
	if ($va_version_code & 2) {
		$sql  = " SELECT ac.category_id, ac.category_name ";
		$sql .= " FROM " . $table_prefix . "articles_categories ac ";
		$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites AS st ON st.category_id = ac.category_id ";
		$sql .= " WHERE ac.parent_category_id=0 ";
		$sql .= " AND (ac.sites_all=1 OR st.site_id=".$db->tosql($param_site_id, INTEGER).") ";
		$sql .= " GROUP BY ac.category_id, ac.category_name";
		$db->query($sql);
		while ($db->next_record()) {
			$row_cat_id = $db->f("category_id");
			$row_cat_name = get_translation($db->f("category_name"), $language_code);
			$sm->add_checkbox("site_map_articles_categories_" . $row_cat_id, INTEGER);
			$sm->add_checkbox("site_map_articles_" . $row_cat_id, INTEGER);
			$articles_categories[$row_cat_id] = $row_cat_name;
		}
	}


	$r->get_form_values();
	$sm->get_form_values();

	$site_url = $r->get_value("site_url");
	if (strlen($site_url) && substr($site_url, strlen($site_url) - 1) != "/") {
		$site_url .= "/";
		$r->set_value("site_url", $site_url);
	}
	$admin_url = $r->get_value("admin_url");
	if (strlen($admin_url) && substr($admin_url, strlen($admin_url) - 1) != "/") {
		$admin_url .= "/";
		$r->set_value("admin_url", $admin_url);
	}
	$secure_url = $r->get_value("secure_url");
	if (strlen($secure_url) && substr($secure_url, strlen($secure_url) - 1) != "/") {
		$secure_url .= "/";
		$r->set_value("secure_url", $secure_url);
	}
	$google_tracking_code = $r->get_value("google_tracking_code");
	if (preg_match("/^[\"'](.*)[\"']$/", $google_tracking_code, $match)) {
		$r->set_value("google_tracking_code", $match[1]);
	}

	$operation = get_param("operation");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";

	$message_build_xml = ""; // save here message about new file build
	if ($operation == "build_xml"){
		// validate and update site map settings
		$sm_form_valid = $sm->validate();
		if ($sm_form_valid) {
			$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='site_map' AND site_id=". $db->tosql($param_site_id, INTEGER);
			$db->query($sql);
			foreach ($sm->parameters as $key => $value) {				
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'site_map', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . "," . $db->tosql($param_site_id, INTEGER) .  ")";
				$db->query($sql);
			}
			include("./admin_site_map_xml_build.php");
			$operation = "";
		}
	}

	if (strlen($operation))	{
		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		}

		$form_valid = $r->validate();
		$sm_form_valid = $sm->validate();
		if (!$sm_form_valid) {
			$r->errors .= $sm->errors;
		}

		if (!strlen($r->errors))
		{			
			// update site name 
			$sql  = " UPDATE " . $table_prefix . "sites ";
			$sql .= " SET site_name=" . $db->tosql($r->get_value("site_name"), TEXT);
			$sql .= " , site_url=" . $db->tosql($r->get_value("site_url"), TEXT);
			$sql .= " , admin_url=" . $db->tosql($r->get_value("admin_url"), TEXT);
			$sql .= " WHERE site_id=" . $db->tosql($param_site_id, INTEGER);
			$db->query($sql);

			// check password ecnryption for users
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='global' AND setting_name='password_encrypt' ";
			$old_password_encrypt = get_db_value($sql);
			$new_password_encrypt = $r->get_value("password_encrypt");

			// check password ecnryption for admins
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='global' AND setting_name='admin_password_encrypt' ";
			$old_admin_password_encrypt = get_db_value($sql);
			$new_admin_password_encrypt = $r->get_value("admin_password_encrypt");
			
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='global' AND setting_name='friendly_auto' AND site_id=". $db->tosql($param_site_id, INTEGER);
			$old_friendly_auto = get_db_value($sql);

			// delete password_encrypt and admin_password_encrypt settings for all sites except master site
			$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='global' AND (setting_name='password_encrypt' OR setting_name='admin_password_encrypt') AND site_id>1 ";
			$db->query($sql);

			// update global settings
			$new_settings = array();
			foreach ($r->parameters as $key => $value) {
				if ($r->get_property_value($key, USE_IN_INSERT)) {
					if ($r->get_property_value($key, CONTROL_TYPE) == CHECKBOXLIST) {
						$new_settings[$key] = json_encode($value[CONTROL_VALUE]);
					} else {
						$new_settings[$key] = $value[CONTROL_VALUE];
					}
				}
			}
			update_settings($setting_type, $param_site_id, $new_settings);
			set_session("session_settings", "");
			set_session("session_show_site", ""); // admin option for offline site

			// update site map settings
			$site_map_settings = array();
			foreach ($sm->parameters as $key => $value) {
				$site_map_settings[$key] = $value[CONTROL_VALUE];
			}
			update_settings("site_map", $param_site_id, $site_map_settings);

			// check if user password encrypt option was changed to md5
			if ($new_password_encrypt == 1 && $new_password_encrypt != $old_password_encrypt) {
				$sql  = " SELECT user_id, password FROM " . $table_prefix . "users ";
				$db->query($sql);
				while ($db->next_record()) {
					$user_id = $db->f("user_id");
					$password = $db->f("password");
					if (!preg_match("/[0-9a-f]{32}/i", $password)) {
						$sql  = " UPDATE " . $table_prefix . "users SET ";
						$sql .= " password=" . $db->tosql(md5($password), TEXT);
						$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
						$dbs->query($sql);
					}
				}
			}

			// check if admin password encrypt optin was changed to md5
			if ($new_admin_password_encrypt == 1 && $new_admin_password_encrypt != $old_admin_password_encrypt) {
				$sql  = " SELECT admin_id, password FROM " . $table_prefix . "admins ";
				$db->query($sql);
				while ($db->next_record()) {
					$admin_id = $db->f("admin_id");
					$password = $db->f("password");
					if (!preg_match("/[0-9a-f]{32}/i", $password)) {
						$sql  = " UPDATE " . $table_prefix . "admins SET ";
						$sql .= " password=" . $db->tosql(md5($password), TEXT);
						$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
						$dbs->query($sql);
					}
				}
			}

			$friendly_urls = $r->get_value("friendly_urls");
			$friendly_auto = $r->get_value("friendly_auto");
			// check if friendly url functionality was turn on with automatic links generation
			if ($friendly_urls && ($friendly_auto == 1 || $friendly_auto == 2) && $friendly_auto != $old_friendly_auto) {

				foreach ($friendly_tables as $table_name => $table_info) {
					$key_field = $table_info[0];
					$title_field = $table_info[1];
					$sql  = " SELECT " . $key_field . ", " . $title_field . " FROM " . $table_name;
					$sql .= " WHERE friendly_url IS NULL OR friendly_url='' ";
					$dbs->query($sql);
					while ($dbs->next_record()) {
						$key_id = $dbs->f($key_field);
						$title_value = get_translation($dbs->f($title_field));
						$friendly_url = generate_friendly_url($title_value);
						$sql  = " UPDATE " . $table_name . " SET ";
						$sql .= " friendly_url=" . $db->tosql($friendly_url, TEXT);
						$sql .= " WHERE " . $key_field . "=" . $db->tosql($key_id, INTEGER);
						$db->query($sql);
					}
				}
			}
			// show success message
			$t->parse("success_block", false);			
		}
		else // parse errors
		{
			$t->set_var("errors_list", $r->errors);
			$t->parse("errors", false);			
		}
	} else {			
		// get global settings
		$r->empty_values();
		$sm->empty_values();
		$r->parameters["int_length_units"][CONTROL_VALUE] = array();
		$r->parameters["imp_length_units"][CONTROL_VALUE] = array();
		$r->parameters["int_weight_units"][CONTROL_VALUE] = array();
		$r->parameters["imp_weight_units"][CONTROL_VALUE] = array();

		$sql  = "SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= "WHERE setting_type='global' ";
		$sql .= "AND (site_id=" . $db->tosql($param_site_id, INTEGER)." OR site_id=1 ) ";
		$sql .= "ORDER BY site_id ASC";
		$db->query($sql);
		while ($db->next_record()) {
			$setting_name = $db->f("setting_name");
			$setting_value = $db->f("setting_value");
			if ($r->parameter_exists($setting_name)) {
				if ($r->get_property_value($setting_name, CONTROL_TYPE) == CHECKBOXLIST) {
					$setting_values = json_decode($setting_value, true);
					foreach ($setting_values as $setting_value) {
						$r->set_value($setting_name, $setting_value);
					}
				} else {
					$r->set_value($setting_name, $setting_value);
				}
			}
		}

		$sql  = " SELECT site_name, site_url, admin_url FROM " . $table_prefix . "sites ";
		$sql .= " WHERE site_id=" . $db->tosql($param_site_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$site_name = $db->f("site_name");
			$site_url = $db->f("site_url");
			$admin_url = $db->f("admin_url");

			$r->set_value("site_name", $site_name);
			$r->set_value("site_url", $site_url);
			$r->set_value("admin_url", $admin_url);
		}
		
		// get site map settings
		$sql  = "SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= "WHERE setting_type='site_map' "; 
		$sql .= "AND (site_id=" . $db->tosql($param_site_id, INTEGER)." OR site_id=1 ) ";
		$sql .= "ORDER BY site_id ASC";
		$db->query($sql);
		while ($db->next_record()) {
			$setting_name = $db->f("setting_name");
			$setting_value = $db->f("setting_value");
			if ($sm->parameter_exists($setting_name)) {
				$sm->set_value($setting_name, $setting_value);
			}
		}
	}

	// check if we need to hide some controls
	$int_length_precision = $r->get_value("int_length_precision");
	if ($int_length_precision != "decimal") {
		$r->change_property("int_length_decimals", CONTROL_HIDE, true);
	}
	if ($int_length_precision != "fractional") {
		$r->change_property("int_length_denominator", CONTROL_HIDE, true);
	}
	$imp_length_precision = $r->get_value("imp_length_precision");
	if ($imp_length_precision != "decimal") {
		$r->change_property("imp_length_decimals", CONTROL_HIDE, true);
	}
	if ($imp_length_precision != "fractional") {
		$r->change_property("imp_length_denominator", CONTROL_HIDE, true);
	}
	$int_weight_precision = $r->get_value("int_weight_precision");
	if ($int_weight_precision != "decimal") {
		$r->change_property("int_weight_decimals", CONTROL_HIDE, true);
	}
	if ($int_weight_precision != "fractional") {
		$r->change_property("int_weight_denominator", CONTROL_HIDE, true);
	}
	$imp_weight_precision = $r->get_value("imp_weight_precision");
	if ($imp_weight_precision != "decimal") {
		$r->change_property("imp_weight_decimals", CONTROL_HIDE, true);
	}
	if ($imp_weight_precision != "fractional") {
		$r->change_property("imp_weight_denominator", CONTROL_HIDE, true);
	}
	
	foreach ($articles_categories as $row_cat_id => $row_cat_name) {
		$t->set_var("row_cat_id", $row_cat_id);
			
		if (defined("SM_SHOW_ARTICLES_CAT_MSG")) {
			$sm_show_articles_cat = str_replace("{row_cat_name}", $row_cat_name, va_message("SM_SHOW_ARTICLES_CAT_MSG"));
			$sm_show_articles = str_replace("{row_cat_name}", $row_cat_name, va_message("SM_SHOW_ARTICLES_MSG"));
		} else {
			$sm_show_articles_cat = $row_cat_name . " " . va_message("CATEGORIES_TITLE");
			$sm_show_articles = $row_cat_name  . " " . va_message("ARTICLES_TITLE");
		}
		$t->set_var("SM_SHOW_ARTICLES_CAT_MSG", $sm_show_articles_cat);
		$t->set_var("SM_SHOW_ARTICLES_MSG", $sm_show_articles);
		if ($sm->get_value("site_map_articles_categories_" . $row_cat_id)) {
			$t->set_var("site_map_articles_categories", "checked");
		} else {
			$t->set_var("site_map_articles_categories", "");
		}
		if ($sm->get_value("site_map_articles_" . $row_cat_id)) {
			$t->set_var("site_map_articles", "checked");
		} else {
			$t->set_var("site_map_articles", "");
		}
		$t->parse("map_articles_settings", true);
	}

	if (!$message_build_xml) {
		// check if current sitemap index exists and show date creation
		$site_map_folder = $sm->get_value("site_map_folder");
		$site_map_filename = $sm->get_value("site_map_filename");
		if (!$site_map_folder) {
			$site_map_folder  = "../";
			$sm->set_value("site_map_folder", $site_map_folder);
		}
		if (!strlen($site_map_filename)) { $site_map_filename = "sitemap"; }
		$site_map_fileindex = $site_map_folder.$site_map_filename.".xml";
		
		if (file_exists($site_map_fileindex)){
			$size = filesize($site_map_fileindex);
			if ($size > 0) {
				$fp = @fopen($site_map_fileindex, "r");
				$contents = fread($fp, $size);
				@fclose($fp);
				if (preg_match_all("/<lastmod>(.*)\<\/lastmod>/Uis", $contents, $matches, PREG_SET_ORDER)){
					$datetime_loc_format = array("YYYY", "-", "MM", "-", "DD", "T", "HH", ":", "mm", ":", "ss", "+00:00");
					$date_modified_value = parse_date($matches[0][1], $datetime_loc_format, $date_errors);
					$date_modified = va_date($datetime_show_format, $date_modified_value);
					$message_build_xml = str_replace("{creation_date}", $date_modified, SM_LATEST_BUILD);
					$message_build_xml = str_replace("{filename}", basename($site_map_fileindex), $message_build_xml);
				}
			}
		}
	}

	if (strlen($message_build_xml)) {
		$t->set_var("message_build_xml", $message_build_xml);
		$t->sparse("success_build_xml", false);
	} 

	$r->set_parameters();
	$sm->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	$layout_scheme = "";
	$active_layout_id = $settings["layout_id"];
	$sql  = " SELECT lt.style_name, lt.layout_name FROM " . $table_prefix . "layouts AS lt"; 
	$sql .= " LEFT JOIN " . $table_prefix . "layouts_sites AS st ON st.layout_id = lt.layout_id ";		
	$sql .= " WHERE lt.layout_id=" . $db->tosql($active_layout_id, INTEGER);
	$sql .= " AND (lt.sites_all=1 OR st.site_id=".$db->tosql($param_site_id, INTEGER).") ";
	$db->query($sql);
	if ($db->next_record()) {
		$style_name = $db->f("style_name");
		$layout_lc = strtolower($db->f("layout_name"));

		$filepath = "";
		if (file_exists("../styles/".$style_name)) {
			$filepath = "../styles/".$style_name;
		} else if (file_exists("../styles/".$style_name.".css")) {
			$filepath = "../styles/".$style_name.".css";
		} else if (file_exists("../styles/".$layout_lc.".css")) {
			$filepath = "../styles/".$layout_lc.".css";
		}
		if ($filepath) {
			$filecontent = implode("", file($filepath));
			if (preg_match("/schemes: (\{[^\}]+\})/Uis", $filecontent, $match)) {
				$schemes_json = $match[1];
				$schemes = json_decode($schemes_json, true);
				if (count($schemes)) {
					$layout_scheme = "<a href=\"admin_design.php?layout_id=".$active_layout_id."\">" .va_message("CHANGE_ACTIVE_SCHEME_MSG")."</a>";
				}
			}
		}
	}
	$t->set_var("layout_scheme", $layout_scheme);

	// multi-site settings
	multi_site_settings();

	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"images" => array("title" => va_message("IMAGES_MSG")), 
		"units" => array("title" => va_message("UNITS_MSG")), 
		"friendly" => array("title" => va_message("FRIENDLY_URLS_MSG")), 
		"head" => array("title" => "Head HTML"), 
		"footer" => array("title" => va_message("FOOTER_MSG")), 
		"site_map" => array("title" => va_message("SITE_MAP_TITLE")), 
		"smtp" => array("title" => "SMTP"), 
		"pgp" => array("title" => "PGP"),
		"offline" => array("title" => va_message("OFFLINE_MSG"), "show" => ($param_site_id == 1)),
		"texteditor" => array("title" => va_message("TEXT_EDITOR_MSG")),
		"tracking" => array("title" => va_message("TRACKING_SETTINGS_MSG")),
		"cookies" => array("title" => va_message("COOKIE_BAR_MSG")),
		"welcome" => array("title" => va_message("WELCOME_POPUP_MSG")),
	);

	parse_tabs($tabs, $tab);

	include_once("./admin_footer.php");
	if ($va_version_code & 2) {
		$t->parse("articles_settings_block", false);
	}
	
	$t->pparse("main");

	function disable_password_encrypt($parameters)
	{
		global $r, $t;
		$current_value = $parameters["current_value"];
		if ($r->get_value("password_encrypt") == 1) {
			if ($current_value == "1") {
				$t->set_var("password_encrypt_disabled", "");
			} else {
				$t->set_var("password_encrypt_disabled", "disabled");
			}
		} else {
			$t->set_var("password_encrypt_disabled", "");
		}
	}

	function disable_admin_password_encrypt($parameters)
	{
		global $r, $t;
		$current_value = $parameters["current_value"];
		if ($r->get_value("admin_password_encrypt") == 1) {
			if ($current_value == "1") {
				$t->set_var("admin_password_encrypt_disabled", "");
			} else {
				$t->set_var("admin_password_encrypt_disabled", "disabled");
			}
		} else {
			$t->set_var("admin_password_encrypt_disabled", "");
		}
	}

	function check_tmp_dir() 
	{
		global $r;

		$auto_tmp = false;
		$tmp_dir = $r->get_value("tmp_dir");
		if ($tmp_dir) {
			if (preg_match("/\//", $tmp_dir)) {
				if (!preg_match("/\/$/", $tmp_dir)) { $tmp_dir .= "/"; }
			} else if (preg_match("/\\\\/", $tmp_dir)) {
				if (!preg_match("/\\\\$/", $tmp_dir)) { $tmp_dir .= "\\"; }
			}
			$r->set_value("tmp_dir", $tmp_dir);
			if (!is_dir($tmp_dir)) {
				$r->errors .= FOLDER_DOESNT_EXIST_MSG . htmlspecialchars($tmp_dir);
			} else {
				$tmp_file = $tmp_dir . "tmp_" . md5(uniqid(rand(), true)) . ".txt";
				$fp = @fopen($tmp_file, "w");
				if ($fp === false) {
					$r->errors .= str_replace("{folder_name}", htmlspecialchars($tmp_dir), FOLDER_PERMISSION_MESSAGE);
				} else {
					fclose($fp);
					unlink($tmp_file);
				}
			}
		} else if ($auto_tmp) {
			// auto-update temporary folder
			if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
				$auto_tmp_dir = get_var("TEMP");
				if (!$auto_tmp_dir) { $auto_tmp_dir = get_var("TMP"); }
			} else {
				$auto_tmp_dir = "/tmp/";
			}
			if (preg_match("/\//", $auto_tmp_dir)) {
				if (!preg_match("/\/$/", $auto_tmp_dir)) { $auto_tmp_dir .= "/"; }
			} else if (preg_match("/\\\\/", $auto_tmp_dir)) {
				if (!preg_match("/\\\\$/", $auto_tmp_dir)) { $auto_tmp_dir .= "\\"; }
			}
			if (is_dir($auto_tmp_dir)) {
				$tmp_file = $auto_tmp_dir . "tmp_" . md5(uniqid(rand(), true)) . ".txt";
				$fp = @fopen($tmp_file, "w");
				if ($fp !== false) {
					fclose($fp);
					unlink($tmp_file);
					$r->set_value("tmp_dir", $auto_tmp_dir);
				}
			}
		}
	}

	function check_secure_url()
	{
		global $r, $domain_start_regexp, $va_version_code;

		$ssl_options_check = array(
			"secure_admin_login", 
		);
		if ($va_version_code & 1) {
			$ssl_options_check[] = "secure_order_profile";
			$ssl_options_check[] = "secure_merchant_order";
			$ssl_options_check[] = "ssl_admin_order_details";
			$ssl_options_check[] = "secure_admin_order_create";
			$ssl_options_check[] = "ssl_admin_orders_list";
			$ssl_options_check[] = "ssl_admin_orders_pages";
		}
		if ($va_version_code & 4) {
			$ssl_options_check[] = "secure_user_tickets";
			$ssl_options_check[] = "secure_user_ticket";
			$ssl_options_check[] = "ssl_admin_tickets";
			$ssl_options_check[] = "ssl_admin_ticket";
			$ssl_options_check[] = "ssl_admin_helpdesk";
		}

		$check_domain = false;
		for ($s = 0; $s < sizeof($ssl_options_check); $s++) {
			$ssl_option_name = $ssl_options_check[$s];
			$ssl_option = $r->get_value($ssl_option_name);
			if ($ssl_option) { $check_domain = true; break; }
		}

		$site_url = $r->get_value("site_url");
		$secure_url = $r->get_value("secure_url");
		if ($check_domain && preg_match($domain_start_regexp, $site_url) && preg_match($domain_start_regexp, $secure_url)) {
			$site_url_parsed = parse_url($site_url);
			$secure_url_parsed = parse_url($secure_url);
			$site_url_host = $site_url_parsed["host"];
			$secure_url_host = $secure_url_parsed["host"];
			if (!preg_match("/".preg_quote($site_url_host, "/")."$/i", $secure_url_host)) {
				$r->errors .= HOSTNAMES_SHOULDBE_SAME_MSG;
			}
		}
	}

	function check_friendly_htaccess()
	{
		global $r;
		$allow_friendly_urls = true;
		$friendly_urls = $r->get_value("friendly_urls");
		$server_software = get_var("SERVER_SOFTWARE");
		if ($friendly_urls && preg_match("/apache/i", $server_software)) {
			$htaccess_file = "../.htaccess";
			if (!file_exists($htaccess_file)) {
				$allow_friendly_urls = false;
			}
		}
		if (!$allow_friendly_urls) {
			$r->parameters["friendly_urls"][IS_VALID] = false;
			$r->parameters["friendly_urls"][ERROR_DESC] = "<b>".$r->parameters["friendly_urls"][CONTROL_DESC] . "</b>: " . FILE_DOESNT_EXIST_MSG . " <b>".$htaccess_file."</b>";
		}
	}

?>