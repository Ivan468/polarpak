<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_custom_form_sent.php                               ***
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
	
	$sent_form_id = get_param("sent_form_id");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_custom_form_sent.html");
	
	$t->set_var("admin_custom_forms_sent_url", "admin_custom_forms_sent.php");
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$sql  = " SELECT cf.form_title FROM " . $table_prefix . "custom_forms cf";
	$sql .= " LEFT JOIN " . $table_prefix . "custom_forms_sent cfs ON cf.form_id=cfs.form_id";
	$sql .= " WHERE cfs.sent_form_id=" . $db->tosql($sent_form_id,INTEGER);
	$form_title = get_db_value($sql);
	
	$sql = "SELECT ff.field_name,fsf.field_value FROM " . $table_prefix . "custom_forms_sent_fields fsf ";
	$sql .= " LEFT JOIN " . $table_prefix . "custom_forms_fields ff ON fsf.field_id=ff.field_id";
	$sql .= " WHERE fsf.sent_form_id=" . $db->tosql($sent_form_id,INTEGER);
	$sql .= " ORDER BY ff.field_order";
	$db->query($sql);
	if($db->next_record()) {
		
		$t->set_var("no_records", "");
		$t->set_var("form_title", $form_title);
		$t->parse("form_name_block");
		
		do {
							
				$t->set_var("field_name", $db->f("field_name"));
				$t->set_var("field_value", $db->f("field_value"));
				
				$t->parse("records", true);
							
			} while ($db->next_record());
	} else {
		
		$t->parse("no_records", false);
		$t->set_var("records", "");
		$t->set_var("form_name_block", "");
		
	}

	$t->pparse("main");
	

?>