<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_sms_status.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/sorter.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/order_items.php");
	include_once ($root_folder_path . "includes/order_links.php");
	include_once ("../messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$operation = get_param("operation");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_sms_status.html");
	$t->set_var("site_url", $settings["site_url"]);

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_order_sms_href", "admin_order_sms.php");

	$r = new VA_Record("");

	$r->add_textbox("sms_message_id", TEXT, ID_MSG);
	$r->change_property("sms_message_id", REQUIRED, true);

	$site_url = $settings["site_url"];

	$sms_status = "";

	$r->get_form_values();
	if(strlen($operation)) {
		$is_valid = $r->validate();

		if($is_valid) {
			$sms_status = sms_get_status($r->get_value("sms_message_id"));
		}
	}
	$r->set_parameters();

	if(strlen($sms_status)) {
		$t->set_var("sms_status", htmlspecialchars($sms_status));
		$t->parse("message", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>