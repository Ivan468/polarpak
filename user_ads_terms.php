<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_ads_terms.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_session();

	$currency = get_currency();


	// get ads settings
	$setting_type = "ads";
	$ads_settings = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	if (isset($site_id)) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$ads_settings[$db->f("setting_name")] = $db->f("setting_value");
	}
	$terms_text = get_setting_value($ads_settings, "terms_text", "");

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "user_ads_terms.html");

	$terms_text = get_currency_message($terms_text, $currency);
	$t->set_var("terms_text", $terms_text);			

	// set styles for popup window
	$css_file = "";
	if (isset($settings["style_name"]) && $settings["style_name"]) {
		$css_file = "styles/" . $settings["style_name"];
		if (isset($settings["scheme_name"]) && $settings["scheme_name"]) {
			$css_file .= "_" . $settings["scheme_name"];
		}
		$css_file .= ".css";
	}
	$t->set_var("css_file", $css_file);

	$t->pparse("main");

?>