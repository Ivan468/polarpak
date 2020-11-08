<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  support_functions.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function get_support_fields() {
	$support_fields = array(
		"dep_id" => array("setting_name" => "dep", "class"=> "fd-dep", "name_constant" => "SUPPORT_DEPARTMENT_FIELD", "default_name" => va_message("SUPPORT_DEPARTMENT_FIELD"), "show" => 1, "required" => 1, "order" => 1), 
		"support_type_id" => array("setting_name" => "type", "class"=> "fd-type", "name_constant" => "SUPPORT_TYPE_FIELD", "default_name" => va_message("SUPPORT_TYPE_FIELD"), "show" => 1, "required" => 1, "order" => 2), 
		"user_name" => array("setting_name" => "user_name", "class"=> "fd-name", "name_constant" => "CONTACT_USER_NAME_FIELD", "default_name" => va_message("CONTACT_USER_NAME_FIELD"), "show" => 1, "required" => 1, "order" => 3), 
		"user_email" => array("setting_name" => "user_email", "class"=> "fd-email", "name_constant" => "CONTACT_USER_EMAIL_FIELD", "default_name" => va_message("CONTACT_USER_EMAIL_FIELD"), "show" => 1, "required" => 1, "order" => 4), 
		"identifier" => array("setting_name" => "identifier", "class"=> "fd-identifier", "name_constant" => "SUPPORT_IDENTIFIER_FIELD", "default_name" => va_message("SUPPORT_IDENTIFIER_FIELD"), "show" => 1, "required" => 0, "order" => 5), 
		"support_product_id" => array("setting_name" => "product", "class"=> "fd-product", "name_constant" => "SUPPORT_PRODUCT_FIELD", "default_name" => va_message("SUPPORT_PRODUCT_FIELD"), "show" => 0, "required" => 0, "order" => 6), 
		"environment" => array("setting_name" => "environment", "class"=> "fd-environment", "name_constant" => "SUPPORT_ENVIRONMENT_FIELD", "default_name" => va_message("SUPPORT_ENVIRONMENT_FIELD"), "show" => 0, "required" => 0, "order" => 7), 
		"summary" => array("setting_name" => "summary", "class"=> "fd-summary", "name_constant" => "SUPPORT_SUMMARY_FIELD", "default_name" => va_message("SUPPORT_SUMMARY_FIELD"), "show" => 1, "required" => 1, "order" => 8),
		"description" => array("setting_name" => "description", "class"=> "fd-description", "name_constant" => "SUPPORT_DESCRIPTION_FIELD", "default_name" => va_message("SUPPORT_DESCRIPTION_FIELD"), "show" => 1, "required" => 1, "order" => 9), 
		"attachments" => array("setting_name" => "attachments", "class"=> "fd-attachments", "name_constant" => "ATTACHMENTS_MSG", "default_name" => va_message("ATTACHMENTS_MSG"), "show" => 1, "required" => 0, "order" => 10),
	);

	return $support_fields;
}

function get_outgoing_email($dep_id, $support_type_id, $support_product_id)
{
	global $db, $table_prefix, $settings;

	$pipe_id = ""; $outgoing_email = "";
	$sql  = " SELECT pipe_id, incoming_email, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$sql .= " AND support_type_id=" . $db->tosql($support_type_id, INTEGER);
	$sql .= " AND support_product_id=" . $db->tosql($support_product_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$incoming_email = $db->f("incoming_email");
		$outgoing_email = $db->f("outgoing_email");
		return ($outgoing_email) ? $outgoing_email : $incoming_email;
	} 

	$sql  = " SELECT pipe_id, incoming_email, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$sql .= " AND support_type_id=" . $db->tosql($support_type_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$incoming_email = $db->f("incoming_email");
		$outgoing_email = $db->f("outgoing_email");
		return ($outgoing_email) ? $outgoing_email : $incoming_email;
	} 

	$sql  = " SELECT pipe_id, incoming_email, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$sql .= " AND support_type_id=" . $db->tosql($support_type_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$incoming_email = $db->f("incoming_email");
		$outgoing_email = $db->f("outgoing_email");
		return ($outgoing_email) ? $outgoing_email : $incoming_email;
	} 

	$sql  = " SELECT pipe_id, incoming_email, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$incoming_email = $db->f("incoming_email");
		$outgoing_email = $db->f("outgoing_email");
		return ($outgoing_email) ? $outgoing_email : $incoming_email;
	} 

	return get_setting_value($settings, "admin_email");
}

function override_email_fields(&$email_settings, $override_rules, $override_fields, $parameters)
{
	if (!is_array($email_settings) || !is_array($override_rules) || !is_array($override_fields) || !is_array($parameters)) {
		return;
	}
	// sort by rule_order first then key and remove disable rules
	$sort_orders = array(); $sort_keys = array();
	foreach ($override_rules as $rule_id => $rule_data) {
		$rule_status = $rule_data["rule_status"];
		if ($rule_status) {
			$sort_orders[$rule_id] = $rule_data["rule_order"];
			$sort_keys[$rule_id] = $rule_id;
		} else {
			unset($override_rules[$rule_id]);
		}
	}
	array_multisort($sort_orders, $sort_keys, $override_rules);
	// check if any rule could be matched
	foreach ($override_rules as $rule_id => $rule_data) {
		$rule_matched = true; // set below to false if any parameter missed
		$rule_status = $rule_data["rule_status"];
		$rule_parameters = $rule_data["parameters"];
		if (!$rule_status) {
			// disable rules should be removed in the code above but probably we may need this additional check here
			$rule_matched = false; continue; 
		}
		
		foreach ($rule_parameters as $param_data) {
			$rule_param_name = $param_data["name"];
			$rule_param_values = $param_data["values"];
			if (isset($parameters[$rule_param_name])) {
				$parameter_matched = false;
				$parameter_values = $parameters[$rule_param_name];
				if (!is_array($parameter_values)) { $parameter_values = array($parameter_values); }
				if (is_array($rule_param_values) && count($rule_param_values)) {
					foreach ($rule_param_values as $value_id => $matching_value) {
						if (preg_match("/^\/.+\/\w*$/", $matching_value)) {
							$rule_value_regexp = $matching_value;
						} else {
							$rule_value_regexp= "/^" . preg_quote($matching_value, "/") . "$/is";
						}
						foreach ($parameter_values as $parameter_value) {
							if (preg_match($rule_value_regexp, $parameter_value)) {
								$parameter_matched = true;
								break;
							}
						}
					}
				} else {
					$parameter_matched = true; // rule parameter doesn't has any values so it's matched automatically if parameter was passed
				}
				if (!$parameter_matched) {
					$rule_matched = false; break; 
				}
			} else {
				$rule_matched = false; break; 
			}
		}
		if ($rule_matched) {
			foreach ($override_fields as $override_field => $email_field) {
				$override_value = get_setting_value($rule_data, $override_field);
				if (strlen($override_value)) {
					$email_settings[$email_field] = $override_value;
				}
			}
			break;
		}
	}
}
