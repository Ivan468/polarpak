<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_products_notify.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$setting_type = "product_notify";
	$message_types = 
		array( 
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_products_notify.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_products_notify_href", "admin_products_notify.php");
	$t->set_var("admin_products_notify_help_href", "admin_products_notify_help.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");

	$r = new VA_Record($table_prefix . "global_settings");

	// set up email parameters
	$r->add_checkbox("admin_notify_user_update", INTEGER);
	$r->add_checkbox("admin_notify_admin_update", INTEGER);
	$r->add_textbox("admin_email", TEXT);
	$r->add_textbox("admin_mail_from", TEXT);
	$r->add_textbox("cc_emails", TEXT);
	$r->add_textbox("admin_mail_bcc", TEXT);
	$r->add_textbox("admin_mail_reply_to", TEXT);
	$r->add_textbox("admin_mail_return_path", TEXT);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_message_type", TEXT, $message_types);
	$r->add_textbox("admin_message", TEXT);

	$r->add_checkbox("user_notify", INTEGER);
	$r->add_textbox("user_mail_from", TEXT);
	$r->add_textbox("user_mail_cc", TEXT);
	$r->add_textbox("user_mail_bcc", TEXT);
	$r->add_textbox("user_mail_reply_to", TEXT);
	$r->add_textbox("user_mail_return_path", TEXT);
	$r->add_textbox("user_subject", TEXT);
	$r->add_radio("user_message_type", TEXT, $message_types);
	$r->add_textbox("user_message", TEXT);

	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if(!strlen($return_page)) $return_page = "admin_items_list.php";
	$t->set_var("rp", htmlspecialchars($return_page));

	if(strlen($operation))
	{
		if($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		if(!strlen($r->errors))
		{
			$sql  = " DELETE FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			$sql .= " AND site_id=" . $db->tosql($param_site_id, INTEGER);
			$db->query($sql);
			foreach($r->parameters as $key => $value)
			{
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= $db->tosql($setting_type, TEXT) . ", '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
				$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
				$db->query($sql);
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	else // get product notify settings
	{
		foreach($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT) . " AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist", false);
	}	

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");

?>