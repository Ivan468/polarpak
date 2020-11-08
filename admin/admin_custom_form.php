<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_custom_form.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("custom_blocks");
	
	$tab = get_param("tab");
	$form_id = get_param("form_id");
	if (!$tab) { $tab = "general"; }

	$t = new VA_Template($settings["admin_templates_dir"]);
	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css");
	$t->set_var("html_editor", get_setting_value($settings, "html_editor", 1));
	$t->set_file("main","admin_custom_form.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", "Custom Form", CONFIRM_DELETE_MSG));
	$t->set_var("form_id", $form_id);

	$admin_custom_forms_url = new VA_URL("admin_custom_forms.php", false);
	$admin_custom_forms_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_custom_forms_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_custom_forms_url->add_parameter("page", REQUEST, "page");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_custom_forms_href", "admin_custom_forms.php");
	$t->set_var("admin_custom_form_href", "admin_custom_form.php");
	$t->set_var("admin_custom_form_field_href", "admin_custom_form_field.php");
	$t->set_var("admin_custom_forms_url", $admin_custom_forms_url->get_url());

	$r = new VA_Record($table_prefix . "custom_forms");
	$r->return_page = "admin_custom_forms.php";

	$r->add_where("form_id", INTEGER);
	$r->add_textbox("form_name", TEXT, "Form Name");
	$r->change_property("form_name", REQUIRED, true);
	$r->add_textbox("form_title", TEXT, "Form Title");
	$r->add_textbox("form_template", TEXT, "Form Template");
	$r->add_textbox("form_notes", TEXT, "Form Notes");
	$r->add_checkbox("sent_email", INTEGER);
	$r->add_textbox("form_emails", TEXT, "Form Emails");
	$r->add_textbox("success_message", TEXT, "Success Message");
	$r->add_textbox("email_from", TEXT, "Email From");
	$r->add_textbox("email_subject", TEXT, "Email Subject");
	$r->add_textbox("submit_name", TEXT, "Submit Button Name");
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->set_event(AFTER_DELETE, "remove_layouts_forms");
	
	
	$sql  = " SELECT field_id, field_name, field_order, control_type ";
	$sql .= " FROM " . $table_prefix . "custom_forms_fields ";
	$sql .= " WHERE form_id=" . $db->tosql($form_id, INTEGER);
	$sql .= " ORDER BY field_order ";
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("name_fields", false);
		$controls = array(
			"CHECKBOXLIST" => CHECKBOXLIST_MSG, "LABEL" => LABEL_MSG, "LISTBOX" => LISTBOX_MSG,
			"RADIOBUTTON" => RADIOBUTTON_MSG, "TEXTAREA" => TEXTAREA_MSG, "TEXTBOX" => TEXTBOX_MSG);

		do {
			$field_id = $db->f("field_id");
			$field_name = $db->f("field_name");
			$field_order = $db->f("field_order");
			$control_type = $db->f("control_type");

			$t->set_var("field_id",   $field_id);
			$t->set_var("field_name", $field_name);
			$t->set_var("field_order", $field_order);
			$t->set_var("control_type", $controls[$control_type]);

			$t->parse("fields", true);
		} while ($db->next_record());
	} else {
		$t->parse("no_fields", false);
	}

	$r->process();
	
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"mail_fields" => array("title" => "Email Setting"), 
		"form_fields" => array("title" => "Form Fields")
	);	
	parse_admin_tabs($tabs, $tab, 6);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function remove_layouts_forms()
	{
		global $r, $db, $table_prefix;
		//$form_name = "custom_form_" . $r->get_value("form_id");
		//$sql = " DELETE FROM " . $table_prefix . "page_settings WHERE setting_name=" . $db->tosql($form_name, TEXT);
		//$db->query($sql);
	}

?>