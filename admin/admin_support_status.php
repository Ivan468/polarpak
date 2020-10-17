<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_status.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");                              
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");
	check_admin_security("support_static_data");                                     

	$status_types = array(
		array("", ""), 
		array("NEW", va_message("TICKET_NEW_MSG")), 
		array("USER_REPLY", va_message("TICKET_USER_REPLY_MSG")), 
		array("ADMIN_REPLY", va_message("TICKET_MANAGER_REPLY_MSG")), 
		array("ADMIN_ASSIGNMENT", va_message("TICKET_ASSIGN_MANAGER_MSG")), 
		array("FORWARD", va_message("TICKET_FORWARD_MSG")), 
		array("CLOSE", va_message("CLOSE_TICKET_MSG")), 
		array("OTHER_ACTION", va_message("OTHER_ACTION_MSG")), 
	);

	$status_types_info = array(
		"NEW" => va_message("USER_NEW_NOTE"),
		"USER_REPLY" => va_message("USER_REPLY_NOTE"),
		"ADMIN_REPLY" => va_message("ADMIN_REPLY_NOTE"),
		"ADMIN_ASSIGNMENT" => va_message("TICKET_ASSIGNED_NOTE"),
		"FORWARD" => va_message("TICKET_FORWARDED_NOTE"),
		"CLOSE" => va_message("TICKET_CLOSED_NOTE"),
		"OTHER_ACTION" => va_message("OTHER_ACTION_NOTE"),
	);


	$t = new VA_Template($settings["admin_templates_dir"]);                          
	$t->set_file("main","admin_support_status.html");                                                                                   
	$t->set_var("admin_href", "admin.php");                                          
	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_status_href", "admin_support_status.php");
	$t->set_var("admin_support_statuses_href", "admin_support_statuses.php");
	$t->set_var("status_types_info", json_encode($status_types_info, JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT));
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", STATUS_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "support_statuses");
	$r->return_page = "admin_support_statuses.php";        
                                                         
	$r->add_where("status_id", INTEGER);                   
	$r->add_textbox("status_order", INTEGER, va_message("STATUS_ORDER_MSG"));   
	$r->change_property("status_order", REQUIRED, true);
	$r->change_property("status_order", DEFAULT_VALUE, 1);
	$r->add_select("status_type", TEXT, $status_types, va_message("STATUS_TYPE_MSG"));
	$r->change_property("status_type", REQUIRED, true);
	$r->add_textbox("status_name", TEXT, va_message("STATUS_NAME_MSG"));   
	$r->change_property("status_name", REQUIRED, true);
	$r->add_checkbox("is_internal", INTEGER);                 
	$r->add_checkbox("is_list", INTEGER);                     	
	$r->add_checkbox("is_update_status", INTEGER);
	$r->add_checkbox("is_keep_assigned", INTEGER);                     	
	$r->add_checkbox("show_for_user", INTEGER);               
	$r->add_textbox("html_start", TEXT);                      
	$r->add_textbox("html_end", TEXT);                        
	$r->add_textbox("status_icon", TEXT);                     

	$r->set_event(BEFORE_VALIDATE, "check_status_closed");    
	$r->process();
                                                            
	include_once("./admin_header.php");                            
	include_once("./admin_footer.php");                            

	$t->pparse("main");                                       

	function check_status_closed()                            
	{                                                         
		global $db, $r, $table_prefix;                          

		if ($r->get_value("is_closed")) {                       
			$sql = "SELECT status_id, status_name FROM " . $table_prefix . "support_statuses WHERE is_closed = 1";
			$db->query($sql);                                                                                     
			if ($db->next_record()) {                                                                             
				if ($r->get_value("status_id") != $db->f("status_id")) {                                            
					$r->errors = "<b>Set this status when manager close the ticket</b> is already set in status <b>'" . $db->f("status_name") . "'</b>.<br>\n";
				}
			}  
		}    
	}      

