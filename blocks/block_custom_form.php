<?php

	$default_title = "{form_title}";

function custom_form($block_name, $form_number)
{
	global $t;
	global $db, $table_prefix;
	global $category_id;
	global $settings, $currency;
	
	$user_id = get_session("session_user_id");
	$operation = get_param("operation");
	$remote_address = get_ip();
	
	$errors = false;
	$eol = get_eol();
	
	//get general form values
	$sql  = " SELECT * FROM " . $table_prefix . "custom_forms ";
  	$sql .= " WHERE form_id=" .  $db->tosql($form_number,INTEGER);
	$db->query($sql);
	if($db->next_record()) {
		
		$form_title = $db->f("form_title");
		$form_template = $db->f("form_template");
		$form_notes = $db->f("form_notes");
		$sent_email = $db->f("sent_email");
		$form_emails = $db->f("form_emails");
		$success_message = $db->f("success_message");
		$submit_name = $db->f("submit_name");
		$email_subject = $db->f("email_subject");
		$email_from = $db->f("email_from");
		
	} else {
		return;
	}
	
	if(strlen($form_template)) {
		$t->set_file("block_body", $form_template);
	} else {
		$html_template = get_setting_value($block, "html_template", "block_custom_form.html"); 
	  $t->set_file("block_body", $html_template);
	}
	
	$t->set_var("form_title", $form_title);
	if (strlen($form_notes)) {
		$t->set_var("form_notes", $form_notes);
		$t->parse("block_form_notes", false);
	} else {
		$t->set_var("block_form_notes", "");
	} 
	
	if (strlen($submit_name)) {
		$t->set_var("submit_name", $submit_name);
	} else $t->set_var("submit_name", "Submit Form");
	
	// prepare form fields
	$fields = array(); $n = 0;
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "custom_forms_fields ";
	$sql .= " WHERE form_id=" . $db->tosql($form_number,INTEGER);
	$sql .= " ORDER BY field_order, field_id";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$fields[$n]["field_id"] = $db->f("field_id");
			$fields[$n]["field_order"] = $db->f("field_order");
			$fields[$n]["field_name"] = $db->f("field_name");
			$fields[$n]["field_description"] = $db->f("field_description");
			$fields[$n]["default_value"] = $db->f("default_value");
			$fields[$n]["control_type"] = $db->f("control_type");
			$fields[$n]["control_style"] = $db->f("control_style");
			$fields[$n]["control_code"] = $db->f("control_code");
			$fields[$n]["onchange_code"] = $db->f("onchange_code");
			$fields[$n]["onclick_code"] = $db->f("onclick_code");
			$fields[$n]["required"] = $db->f("required");
			$fields[$n]["before_name_html"] = $db->f("before_name_html");
			$fields[$n]["after_name_html"] = $db->f("after_name_html");
			$fields[$n]["before_control_html"] = $db->f("before_control_html");
			$fields[$n]["after_control_html"] = $db->f("after_control_html");
			$fields[$n]["validation_regexp"] = $db->f("validation_regexp");
			$fields[$n]["regexp_error"] = $db->f("regexp_error");
			$fields[$n]["options_values_sql"] = $db->f("options_values_sql");

			$n++;
		} while ($db->next_record());
	}
	
	
	$r = new VA_Record($table_prefix . "custom_forms_sent", "sent_form");

	$r->add_where("sent_form_id", INTEGER);
	$r->add_textbox("form_id", INTEGER);
	$r->change_property("form_id", USE_SQL_NULL, false);
	$r->add_textbox("user_id", INTEGER);
	$r->add_textbox("remote_address", TEXT);
	$r->add_textbox("date_sent", DATETIME);
	
	
	foreach ($fields as $id => $field) {
	
		$control_type = $field["control_type"];
		$param_name = "ff_" . $field["field_id"];
		$param_title = $field["field_name"];

		if ($control_type == "CHECKBOXLIST") {
			$r->add_checkboxlist($param_name, TEXT, "", $param_title);
		} elseif ($control_type == "RADIOBUTTON") {
			$r->add_radio($param_name, TEXT, "", $param_title);
		} elseif ($control_type == "LISTBOX") {
			$r->add_select($param_name, TEXT, "", $param_title);
		} else {
			$r->add_textbox($param_name, TEXT, $param_title);
		}
		if ($control_type == "CHECKBOXLIST" || $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
			if ($field["options_values_sql"]) {
				$sql = $field["options_values_sql"];
			} else {
				$sql  = " SELECT field_value_id, field_value FROM " . $table_prefix . "custom_forms_fields_values ";
				$sql .= " WHERE field_id=" . $db->tosql($field["field_id"], INTEGER) . " AND hide_value=0";
				$sql .= " ORDER BY field_value_id ";
			}
			$r->change_property($param_name, VALUES_LIST, get_db_values($sql, ""));
		}
		if ($field["required"] == 1) {
			$r->change_property($param_name, REQUIRED, true);
		}
		if ($field["validation_regexp"]) {
			$r->change_property($param_name, REGEXP_MASK, $field["validation_regexp"]);
			if ($field["regexp_error"]) {
				$r->change_property($param_name, REGEXP_ERROR, $field["regexp_error"]);
			}
		}
		$r->change_property($param_name, USE_IN_SELECT, false);
		$r->change_property($param_name, USE_IN_INSERT, false);
		$r->change_property($param_name, USE_IN_UPDATE, false);
	}
	
	
	
	if (strlen($operation))
	{
		$r->get_form_values();

		$r->validate();

		if (strlen($r->errors)) {
			$errors = true;
		}

		if (!$errors)
		{
			$r->set_value("user_id", $user_id);
			$r->set_value("date_sent", va_time());
			$r->set_value("remote_address", $remote_address);
			$r->set_value("form_id", $form_number);
			if ($r->insert_record())
			{	
				$sent_form_id = get_db_value(" SELECT MAX(sent_form_id) FROM va_custom_forms_sent");
				
				$r->set_value("sent_form_id", $sent_form_id);

				insert_form_fields($fields,$r,$sent_form_id);

				// send email notification to admin
				if ($sent_email)
				{
					$mail_to = str_replace(";", ",", $form_emails);
					
					if (!strlen($email_subject)) $email_subject = "Custom Form #" . $form_number;
					
					$admin_message = "";
					
					$sql = "SELECT ff.field_name,fsf.field_value FROM " . $table_prefix . "custom_forms_sent_fields fsf ";
					$sql .= " LEFT JOIN " . $table_prefix . "custom_forms_fields ff ON fsf.field_id=ff.field_id";
					$sql .= " WHERE fsf.sent_form_id=" . $db->tosql($sent_form_id,INTEGER);
					$sql .= " ORDER BY ff.field_order";
					$db->query($sql);
					if($db->next_record()) {
						do {
							
								$admin_message .= $db->f("field_name") . ": " . $db->f("field_value") . "\n";
							
						} while ($db->next_record());
					}
					
					mail($mail_to, $email_subject, $admin_message, "From:" . $email_from);
				}

				$r->empty_values();
				
			}
		}
	}

	foreach ($fields as $id => $field) {
		$param_name = "ff_" . $field["field_id"];
		if ($r->parameter_exists($param_name)) {
			$r->change_property($param_name, SHOW, false);
		}
	}

	$r->set_parameters();

	if ($errors) {
		$t->parse("sent_form_errors", false);
	}

	if (!$errors && $operation) {
		$t->set_var("form_success_message", $success_message);
		$t->parse("sent_form_thanks", false);
	}

	
	$t->set_var("form_fields", "");
	$fields_ids = "";
		// show custom options
		if (sizeof($fields) > 0)
		{
			for ($n = 0; $n < sizeof($fields); $n++) {
				$field_id = $fields[$n]["field_id"];
				$param_name = "ff_" . $field_id;
				$field_order  = $fields[$n]["field_order"];
				$field_name_initial = $fields[$n]["field_name"];
				$field_name = get_translation($field_name_initial);
				$field_description = $fields[$n]["field_description"];
				$default_value = $fields[$n]["default_value"];
				$control_type = $fields[$n]["control_type"];
				$control_style = $fields[$n]["control_style"];
				$field_required = $fields[$n]["required"];
				$before_name_html = $fields[$n]["before_name_html"];
				$after_name_html = $fields[$n]["after_name_html"];
				$before_control_html = $fields[$n]["before_control_html"];
				$after_control_html = $fields[$n]["after_control_html"];
				$onchange_code = $fields[$n]["onchange_code"];
				$onclick_code = $fields[$n]["onclick_code"];
				$control_code = $fields[$n]["control_code"];
				$validation_regexp = $fields[$n]["validation_regexp"];
				$regexp_error = $fields[$n]["regexp_error"];
				$options_values_sql = $fields[$n]["options_values_sql"];

				if (strlen($fields_ids)) { $fields_ids .= ","; }
				$fields_ids .= $field_id;

				$field_control  = "";
				$field_control .= "<input type=\"hidden\" name=\"ff_name_" . $field_id . "\"";
				$field_control .= " value=\"" . strip_tags($field_name) . "\">";
				$field_control .= "<input type=\"hidden\" name=\"ff_required_" . $field_id . "\"";
				$field_control .= " value=\"" . intval($field_required) . "\">";
				$field_control .= "<input type=\"hidden\" name=\"ff_control_" . $field_id . "\"";
				$field_control .= " value=\"" . strtoupper($control_type) . "\">";

				if ($options_values_sql) {
					$sql = $options_values_sql;
				} else {
					$sql  = " SELECT * FROM " . $table_prefix . "custom_forms_fields_values ";
					$sql .= " WHERE field_id=" . $db->tosql($field_id, INTEGER) . " AND hide_value=0";
					$sql .= " ORDER BY field_value_id ";
				}
				
				if (strtoupper($control_type) == "LISTBOX") 
				{
					$selected_value = $r->get_value($param_name);
					$properties_values = "<option value=\"\">" . SELECT_MSG . " " . $field_name . "</option>" . $eol;
					$db->query($sql);
					while ($db->next_record())
					{
						if ($options_values_sql) {
							$field_value_id = $db->f(0);
							$field_value = get_translation($db->f(1));
						} else {
							$field_value_id = $db->f("field_value_id");
							$field_value = get_translation($db->f("field_value"));
						}
						$is_default_value = $db->f("is_default_value");
						$field_selected  = "";
						if (strlen($operation) || $user_id) {
							if ($selected_value == $field_value_id) {
								$field_selected  = "selected ";
							}
						} elseif ($is_default_value) {
							$field_selected  = "selected ";
						}

						$properties_values .= "<option " . $field_selected . "value=\"" . htmlspecialchars($field_value) . "\">";
						$properties_values .= htmlspecialchars($field_value);
						$properties_values .= "</option>" . $eol;
					}
					$field_control .= $before_control_html;
					$field_control .= "<select name=\"ff_" . $field_id . "\" ";
					if ($onchange_code) { $field_control .= " onchange=\"" . $onchange_code. "\""; }
					if ($onclick_code) { $field_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($control_code) { $field_control .= " " . $control_code . " "; }
					if ($control_style) { $field_control .= " class=\"" . $control_style . "\""; }
					$field_control .= ">" . $properties_values . "</select>";
					$field_control .= $after_control_html;
				} 
				elseif (strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "CHECKBOXLIST") 
				{
					$is_radio = (strtoupper($control_type) == "RADIOBUTTON");
					$selected_value = array();
					if ($is_radio) {
						$selected_value[] = $r->get_value($param_name);
					} else {
						$selected_value = $r->get_value($param_name);
					}

					$input_type = $is_radio ? "radio" : "checkbox";
					$field_control .= "<span";
					if ($control_style) {	$field_control .= " class=\"" . $control_style . "\""; }
					$field_control .= ">";
					$value_number = 0;
					$db->query($sql);
					while ($db->next_record())
					{
						$value_number++;
						if ($options_values_sql) {
							$field_value_id = $db->f(0);
							$field_value = get_translation($db->f(1));
						} else {
							$field_value_id = $db->f("field_value_id");
							$field_value = get_translation($db->f("field_value"));
						}
						$is_default_value = $db->f("is_default_value");
						$field_checked = "";
						$field_control .= $before_control_html;
						if (strlen($operation) || $user_id) {
							if (is_array($selected_value) && in_array($field_value_id, $selected_value)) {
								$field_checked = "checked ";
							}
						} elseif ($is_default_value) {
							$field_checked = "checked ";
						}

						$control_name = ($is_radio) ? ("ff_".$field_id) : ("ff_".$field_id."_".$value_number);
						$field_control .= "<input type=\"" . $input_type . "\" name=\"" . $control_name . "\" ". $field_checked;
						$field_control .= "value=\"" . htmlspecialchars($field_value) . "\" ";
						if ($onclick_code) {
							$control_onclick_code = str_replace("{option_value}", $field_value, $onclick_code);
							$field_control .= " onclick=\"" . $control_onclick_code. "\"";
						}
						if ($onchange_code) {	$field_control .= " onchange=\"" . $onchange_code . "\""; }
						if ($control_code) {	$field_control .= " " . $control_code . " "; }
						$field_control .= ">";
						$field_control .= $field_value;
						$field_control .= $after_control_html;
					}
					$field_control .= "</span>";
					if (!$is_radio) {
						$field_control .= "<input type=\"hidden\" name=\"ff_".$field_id."\" value=\"".$value_number."\">";
					}
				} 
				elseif (strtoupper($control_type) == "TEXTBOX") 
				{
					if (strlen($operation) || $user_id) {
						$control_value = $r->get_value($param_name);
					} else {
						$control_value = $default_value;
					}
					$field_control .= $before_control_html;
					$field_control .= "<input type=\"text\" name=\"ff_" . $field_id . "\"";
					if ($control_style) { $field_control .= " class=\"" . $control_style . "\""; }
					if ($onclick_code) { $field_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) { $field_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) { $field_control .= " " . $control_code . " "; }
					$field_control .= " value=\"". htmlspecialchars($control_value) . "\">";
					$field_control .= $after_control_html;
				} 
				elseif (strtoupper($control_type) == "TEXTAREA") 
				{
					if (strlen($operation) || $user_id) {
						$control_value = $r->get_value($param_name);
					} else {
						$control_value = $default_value;
					}
					$field_control .= $before_control_html;
					$field_control .= "<textarea name=\"ff_" . $field_id . "\"";
					if ($control_style) { $field_control .= " class=\"" . $control_style . "\""; }
					if ($onclick_code) { $field_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) { $field_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) { $field_control .= " " . $control_code . " "; }
					$field_control .= ">". htmlspecialchars($control_value) ."</textarea>";
					$field_control .= $after_control_html;
				} 
				else 
				{
					$field_control .= $before_control_html;
					if ($field_required) {
						$field_control .= "<input type=\"hidden\" name=\"ff_" . $field_id . "\" value=\"" . htmlspecialchars($field_description) . "\">";
					}
					$field_control .= "<span";
					if ($control_style) { $field_control .= " class=\"" . $control_style . "\""; }
					if ($onclick_code) { $field_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) { $field_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) { $field_control .= " " . $control_code . " "; }
					$field_control .= ">" . get_translation($default_value) . "</span>";
					$field_control .= $after_control_html;
				}

				$t->set_var("field_id", $field_id);
				$t->set_var("field_name", $before_name_html . $field_name . $after_name_html);
				$t->set_var("field_control", $field_control);
				if ($field_required) {
					$t->set_var("field_required", "*");
				} else {
					$t->set_var("field_required", "");
				}

				$t->parse("form_fields", true);
			}
	}
	
	$sql  = " SELECT block_desc FROM " . $table_prefix . "custom_blocks";
	$sql .= " WHERE block_id=4";
	$t->set_var("index_form_text", get_translation(get_db_value($sql)));

	$block_parsed = true;
}

function insert_form_fields($fields,$r,$sent_form_id)
{
	global $db, $table_prefix;

	foreach ($fields as $id => $data) {
		
		$field_id =$data["field_id"];
		$param_name = "ff_" . $field_id;
		$values = array();
		if ($r->get_property_value($param_name, CONTROL_TYPE) == CHECKBOXLIST) {
			$values = $r->get_value($param_name);
		} else {
			$values[] = $r->get_value($param_name);
		}
		if (is_array($values)) {
			for ($i = 0; $i < sizeof($values); $i++) {
				$field_value = $values[$i];
				if (strlen($field_value) && $field_id && $sent_form_id) {
					$sql  = " INSERT INTO " . $table_prefix . "custom_forms_sent_fields ";
					$sql .= " (sent_field_id, sent_form_id, field_id, field_value) VALUES (NULL,";
					$sql .= $db->tosql($sent_form_id, INTEGER) . ", ";
					$sql .= $db->tosql($field_id, INTEGER) . ", ";
					$sql .= $db->tosql($field_value, TEXT) . ") ";
					$db->query($sql);
				}
			}
		}
	}
}


?>