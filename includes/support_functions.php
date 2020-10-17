<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  support_functions.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
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
	global $db, $table_prefix;

	$pipe_id = ""; $outgoing_email = "";
	$sql  = " SELECT pipe_id, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$sql .= " AND support_type_id=" . $db->tosql($support_type_id, INTEGER);
	$sql .= " AND support_product_id=" . $db->tosql($support_product_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$outgoing_email = $db->f("outgoing_email");
		return $outgoing_email;
	} 

	$sql  = " SELECT pipe_id, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$sql .= " AND support_type_id=" . $db->tosql($support_type_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$outgoing_email = $db->f("outgoing_email");
		return $outgoing_email;
	} 

	$sql  = " SELECT pipe_id, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$sql .= " AND support_type_id=" . $db->tosql($support_type_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$outgoing_email = $db->f("outgoing_email");
		return $outgoing_email;
	} 

	$sql  = " SELECT pipe_id, outgoing_email FROM ".$table_prefix."support_pipes ";
	$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$pipe_id = $db->f("pipe_id");
		$outgoing_email = $db->f("outgoing_email");
		return $outgoing_email;
	} 

}
