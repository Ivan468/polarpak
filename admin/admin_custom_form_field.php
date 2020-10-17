<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_custom_form_field.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("custom_blocks");
	
	$form_id = get_param("form_id");
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_custom_form_field.html");
	$t->set_var("admin_href",              "admin.php");
	$t->set_var("admin_custom_form_href",  "admin_custom_form.php?form=$form_id&tab=form_fields");
	
	$controls = 
		array(			
			array("", ""),  
			array("CHECKBOXLIST", CHECKBOXLIST_MSG),
			array("LABEL",        LABEL_MSG),
			array("LISTBOX",      LISTBOX_MSG),
			array("RADIOBUTTON",  RADIOBUTTON_MSG),
			array("TEXTAREA",     TEXTAREA_MSG),
			array("TEXTBOX",      TEXTBOX_MSG)
			);

	// set up html form parameters
	$r = new VA_Record($table_prefix . "custom_forms_fields");
	$r->add_where("field_id", INTEGER);
	$r->change_property("field_id", USE_IN_INSERT, true);
	$r->add_textbox("form_id", INTEGER);
	$r->add_textbox("field_order", INTEGER, FIELD_ORDER_MSG);
	$r->change_property("field_order", REQUIRED, true);
	$r->add_textbox("field_name", TEXT, FIELD_NAME_MSG);
	$r->change_property("field_name", REQUIRED, true);
	$r->add_textbox("field_description", TEXT, FIELD_TEXT_MSG);
	$r->add_textbox("default_value", TEXT, DEFAULT_VALUE_MSG);
	$r->add_textbox("control_style", TEXT);
	$r->add_select("control_type", TEXT, $controls, FIELD_CONTROL_MSG);
	$r->change_property("control_type", REQUIRED, true);
	$r->add_checkbox("required", INTEGER);
	
	$r->add_textbox("before_name_html", TEXT);
	$r->add_textbox("after_name_html", TEXT);
	$r->add_textbox("before_control_html", TEXT);
	$r->add_textbox("after_control_html", TEXT);
	$r->add_textbox("control_code", TEXT);
	$r->add_textbox("onchange_code", TEXT);
	$r->add_textbox("onclick_code", TEXT);

	$r->add_textbox("validation_regexp", TEXT);
	$r->add_textbox("regexp_error", TEXT);
	$r->add_textbox("options_values_sql", TEXT);

	$r->get_form_values();

	$ipv = new VA_Record($table_prefix . "custom_forms_fields_values", "fields");
	$ipv->add_where("field_value_id", INTEGER);
	$ipv->add_hidden("field_id", INTEGER);
	$ipv->change_property("field_id", USE_IN_INSERT, true);
	$ipv->add_textbox("field_value", TEXT, OPTION_VALUE_MSG);
	$ipv->change_property("field_value", REQUIRED, true);
	$ipv->add_checkbox("hide_value", INTEGER);
	$ipv->add_checkbox("is_default_value", INTEGER);
	
	$field_id = get_param("field_id");
	
	$more_fields = get_param("more_fields");
	$number_fields = get_param("number_fields");

	$eg = new VA_EditGrid($ipv, "fields");
	$eg->get_form_values($number_fields);

	$operation = get_param("operation");
	$return_page = "admin_custom_form.php?form_id=$form_id&tab=form_fields";

	if (strlen($operation) && !$more_fields)
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $field_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "custom_forms_fields WHERE field_id=" . $db->tosql($field_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "custom_forms_fields_values WHERE field_id=" . $db->tosql($field_id, INTEGER));		
			header("Location: " . $return_page);
			exit;
		}
		
		$r->set_value("form_id", $form_id);

		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid); 

		if ($is_valid)
		{
			if (strlen($field_id))
			{
				$r->update_record();
				$eg->set_values("field_id", $field_id);
				$eg->update_all($number_fields);
			}
			else
			{
				$db->query("SELECT MAX(field_id) FROM " . $table_prefix . "custom_forms_fields");
				$db->next_record();
				$field_id = $db->f(0) + 1;
				$r->set_value("field_id", $field_id);
				$r->insert_record();
				$eg->set_values("field_id", $field_id);
				$eg->insert_all($number_fields);
			}
			header("Location: " . $return_page);
			exit;
		}
	}
	elseif (strlen($field_id) && !$more_fields)
	{
		$r->get_db_values();
		$eg->set_value("field_id", $field_id);
		$eg->change_property("field_value_id", USE_IN_SELECT, true);
		$eg->change_property("field_value_id", USE_IN_WHERE, false);
		$eg->change_property("field_id", USE_IN_WHERE, true);
		$eg->change_property("field_id", USE_IN_SELECT, true);
		$number_fields = $eg->get_db_values();
		if ($number_fields == 0)
			$number_fields = 5;
	}
	elseif ($more_fields)
	{
		$number_fields += 5;
	}
	else // set default values
	{
		$sql  = " SELECT MAX(field_order) FROM " . $table_prefix . "custom_forms_fields ";
		$field_order = get_db_value($sql);
		$field_order = ($field_order) ? ($field_order + 1) : 1;
		$r->set_value("field_order", $field_order);

		$number_fields = 5;
	}
	
	$t->set_var("number_fields", $number_fields);

	$eg->set_parameters_all($number_fields);
	$r->set_parameters();

	if (strlen($field_id))	
	{
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	}
	else
	{
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");	
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>