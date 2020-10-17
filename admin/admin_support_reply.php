<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_reply.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/url.php");
	include_once($root_folder_path . "includes/support_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/support_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support");

	$type_absent_error = va_message("STATUS_TYPE_ABSENT_ERROR");

	$tl = new VA_URL("admin_support.php");
	$tl->add_parameter("s_tn", REQUEST, "s_tn");
	$tl->add_parameter("s_ne", REQUEST, "s_ne");
	$tl->add_parameter("s_sm", REQUEST, "s_sm");
	$tl->add_parameter("s_kw", REQUEST, "s_kw");
	$tl->add_parameter("s_sd", REQUEST, "s_sd");
	$tl->add_parameter("s_ed", REQUEST, "s_ed");
	$tl->add_parameter("s_at", REQUEST, "s_at");
	$tl->add_parameter("s_dp", REQUEST, "s_dp");
	$tl->add_parameter("s_tp", REQUEST, "s_tp");
	$tl->add_parameter("s_st", REQUEST, "s_st");
	$tl->add_parameter("s_in", REQUEST, "s_in");
	$tl->add_parameter("s_sti",REQUEST, "s_sti");
	$tl->add_parameter("page", REQUEST, "page");
	$tl->add_parameter("sort_ord", REQUEST, "sort_ord");
	$tl->add_parameter("sort_dir", REQUEST, "sort_dir");
	// link to the tickets page
	$admin_support_url = $tl->get_url();
	// link to the current page
	$tl->add_parameter("support_id", REQUEST, "support_id");
	$admin_support_reply_url = $tl->get_url("admin_support_reply.php");
	// link to edit parent ticket request
	$tl->add_parameter("rp", CONSTANT, $admin_support_reply_url);
	$admin_support_request_url = $tl->get_url("admin_support_request.php");
	$tl->remove_parameter("rp");
	// link to delete ticket request
	$tl->add_parameter("operation", CONSTANT, "delete");
	$admin_request_delete_url = $tl->get_url("admin_support_request.php");
	// close link
	$tl->add_parameter("operation", CONSTANT, "close");
	$close_ticket_url = $tl->get_url("admin_support_reply.php");

	$va_trail = array(
		"admin_menu.php?code=dashboard" => va_message("DASHBOARD_MSG"),
		$admin_support_url => va_message("SUPPORT_TICKETS_MSG"),
		$admin_support_reply_url => va_message("TICKET_DETAILS_MSG"),
	);
	
	// connection for support attachemnts 
	$dba = new VA_SQL();
	$dba->DBType       = $db->DBType;
	$dba->DBDatabase   = $db->DBDatabase;
	$dba->DBUser       = $db->DBUser;
	$dba->DBPassword   = $db->DBPassword;
	$dba->DBHost       = $db->DBHost;
	$dba->DBPort       = $db->DBPort;
	$dba->DBPersistent = $db->DBPersistent;

	$dbd = new VA_SQL();
	$dbd->DBType       = $db->DBType;
	$dbd->DBDatabase   = $db->DBDatabase;
	$dbd->DBUser       = $db->DBUser;
	$dbd->DBPassword   = $db->DBPassword;
	$dbd->DBHost       = $db->DBHost;
	$dbd->DBPort       = $db->DBPort;
	$dbd->DBPersistent = $db->DBPersistent;
            
	$eol = get_eol();
	$admin_id = get_session("session_admin_id");

	// get permissions
	$permissions = get_permissions();
	$allow_edit  = get_setting_value($permissions, "support_ticket_edit", 0);
	$allow_close = get_setting_value($permissions, "support_ticket_close", 0); 
	$allow_reply = get_setting_value($permissions, "support_ticket_reply", 0); 
	$ticket_errors = "";

	//$close_id   = get_param("close_id");
	$support_id = get_param("support_id");
	$tab = get_param("tab");
	$operation  = get_param("operation");
	if ($operation) { $tab = $operation; }
	$rnd        = get_param("rnd");

	$close_status_id = ""; 
	$reply_status_id = ""; $assign_status_id = ""; $return_status_id = ""; $other_status_id = ""; $forward_status_id = "";
	$reply_default_id = ""; $assign_default_id = ""; $return_default_id = ""; $other_default_id = ""; $forward_default_id = "";
	$reply_statuses_no = 0; $assign_statuses_no = 0; $return_statuses_no = 0; $other_statuses_no = 0; $forward_statuses_no = 0;
	$support_statuses = array();
	$reply_statuses = array(array("", va_message("SUPPORT_SELECT_STATUS_MSG")));
	$assign_statuses = array(array("", va_message("SUPPORT_SELECT_STATUS_MSG")));
	$return_statuses = array(array("", va_message("SUPPORT_SELECT_STATUS_MSG")));
	$other_statuses = array(array("", va_message("SUPPORT_SELECT_STATUS_MSG")));
	$forward_statuses = array(array("", va_message("SUPPORT_SELECT_STATUS_MSG")));

	$sql  = " SELECT status_id, status_name, status_type, is_default, is_internal, is_update_status, is_keep_assigned ";
	$sql .= " FROM " . $table_prefix . "support_statuses ";
	$sql .= " ORDER BY status_name ASC";
	$db->query($sql);
	while ($db->next_record()) {
		$status_id = $db->f("status_id");
		$is_default = $db->f("is_default");
		$status_type = strtoupper($db->f("status_type"));
		$status_name = get_translation($db->f("status_name"));
		$is_internal = $db->f("is_internal");
		$is_update_status = $db->f("is_update_status");
		$is_keep_assigned = $db->f("is_keep_assigned");

		$status_desc = $status_name;
		if ($is_internal) {
			$status_desc .= " (".va_message("INTERNAL_MESSAGE_MSG").")";
		}
		$support_statuses[$status_id] = $db->Record;
		$support_statuses[$status_id]["status_desc"] = $status_desc;

		if ($status_type == "USER_REPLY") {
		} else if ($status_type == "ADMIN_REPLY") {
			$reply_statuses_no++; 
			$reply_statuses[] = array($status_id, $status_desc);
			if ($is_default) {
				$reply_default_id = $status_id;
			}
		} else if ($status_type == "ADMIN_ASSIGNMENT") {
			$assign_statuses_no++; 
			$return_statuses_no++;
			$assign_statuses[] = array($status_id, $status_desc);
			$return_statuses[] = array($status_id, $status_desc);
			if ($is_default) {
				$assign_default_id = $status_id;
				$return_default_id = $status_id;
			}
		} else if ($status_type == "CLOSE") {
			$close_status_id = $status_id;
		} else if ($status_type == "OTHER_ACTION") {
			$other_statuses_no++; 
			$other_statuses[] = array($status_id, $status_desc);
			if ($is_default) {
				$other_default_id = $status_id;
			}
		} else if ($status_type == "FORWARD") {
			$forward_statuses_no++;
			$forward_statuses[] = array($status_id, $status_desc);
			if ($is_default) {
				$forward_default_id = $status_id;
			}
		}		
	}

	if ($reply_statuses_no == 1) {
		$reply_status_id = $reply_statuses[1][0]; 
	}
	if ($assign_statuses_no == 1) {
		$assign_status_id = $assign_statuses[1][0]; 
	}
	if ($return_statuses_no == 1) {
		$return_status_id = $return_statuses[1][0]; 
	}
	if ($other_statuses_no == 1) {
		$other_status_id = $other_statuses[1][0]; 
	}
	if ($forward_statuses_no == 1) {
		$forward_status_id = $forward_statuses[1][0]; 
	}

	if ($operation == "close") {
		if ($allow_close) {
			if ($close_status_id) {
				$sql  = " UPDATE " . $table_prefix . "support SET support_status_id=" . $db->tosql($close_status_id, INTEGER);
				$sql .= " , date_modified=" . $db->tosql(va_time(), DATETIME);
				$sql .= " , admin_id_assign_by = 0 ";
				$sql .= " , admin_id_assign_to = 0 ";
				$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
				$db->query($sql);
			}
			header("Location: " . $admin_support_url);
			exit;
		} else {
			$ticket_errors = CLOSE_TICKET_NOT_ALLOWED_MSG;
		}
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support_reply.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ADMIN_TICKET_MSG, CONFIRM_DELETE_MSG));
	$t->set_var("admin_support_url", htmlspecialchars($admin_support_url));
	$t->set_var("admin_support_reply_url", htmlspecialchars($admin_support_reply_url));
	$t->set_var("admin_support_request_url", htmlspecialchars($admin_support_request_url));
	$t->set_var("admin_request_delete_url", htmlspecialchars($admin_request_delete_url));
	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_reply_href", "admin_support_reply.php");
	$t->set_var("admin_support_message_href", "admin_support_message.php");
	$t->set_var("admin_support_request_href", "admin_support_request.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("site_url", $settings["site_url"]);

	$t->set_var("rnd", va_timestamp());

	// get admin privileges
	$admin_privileges = array();
	$sql  = " SELECT privilege_id, privilege_name FROM ".$table_prefix."admin_privileges ";
	$db->query($sql);
	while ($db->next_record()) {
		$privilege_id = $db->f("privilege_id");
		$privilege_name = $db->f("privilege_name");
		$admin_privileges[$privilege_id] = $privilege_name;
	}

	// get user types 
	$user_types = array();
	$sql  = " SELECT type_id, type_name FROM ".$table_prefix."user_types ";
	$db->query($sql);
	while ($db->next_record()) {
		$type_id = $db->f("type_id");
		$type_name = $db->f("type_name");
		$user_types[$type_id] = $type_name;
	}


	// signature
	$session_admin_id = get_session("session_admin_id");
	$admin_signature = get_db_value(" SELECT signature FROM ". $table_prefix . "admins WHERE admin_id=" .$db->tosql($session_admin_id,INTEGER));
	$t->set_var("admin_signature", htmlspecialchars($admin_signature));

	// update request viewed information
	$sql  = " UPDATE " . $table_prefix . "support SET date_viewed=" . $db->tosql(va_time(), DATETIME);
	$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
	$sql .= " AND date_viewed IS NULL ";
	$db->query($sql);

	// get information about ticket
	$ticket_date = ""; $ticket_dep_id = ""; $ticket_type_id = ""; $ticket_product_id = "";
	$ticket_mail_cc = ""; $ticket_mail_bcc = "";
	$sql  = " SELECT s.support_id, s.dep_id, s.support_type_id, s.support_product_id, ";
	$sql .= " s.user_id, u.user_type_id, s.user_name, s.user_email, s.mail_cc, s.mail_bcc, ";
	$sql .= " s.remote_address, s.identifier, s.mail_headers, s.mail_body_html, s.mail_body_text, ";
	$sql .= " s.environment, p.product_name, st.type_name, s.summary, s.description, ";
	$sql .= " ss.status_name, ss.status_id, sp.priority_name, s.date_added, s.date_modified, ";
	$sql .= " aa.admin_name as assign_to, s.date_viewed, sti.site_name  ";
	$sql .= " FROM (((((((" . $table_prefix . "support s ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_products p ON p.product_id=s.support_product_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id=s.support_status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON st.type_id=s.support_type_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_priorities sp ON sp.priority_id=s.support_priority_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins aa ON aa.admin_id=s.admin_id_assign_to) ";
	$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id=s.site_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "users u ON s.user_id=u.user_id) ";
	$sql .= " WHERE s.support_id=" . $db->tosql($support_id, INTEGER);
	$db->query($sql);
	if ($db->next_record())
	{
		$dep_id = $db->f("dep_id");
		$user_id = $db->f("user_id");
		$user_type_id = $db->f("user_type_id");
		$ticket_dep_id = $db->f("dep_id");
		$ticket_type_id = $db->f("support_type_id");
		$ticket_product_id = $db->f("support_product_id");
		$t->set_var("user_id", $user_id);
		$user_name = $db->f("user_name");
		$t->set_var("user_name", htmlspecialchars($user_name));
		$user_email = $db->f("user_email");
		$ticket_user_email = $db->f("user_email");
		$ticket_mail_cc = $db->f("mail_cc");
		$ticket_mail_bcc = $db->f("mail_bcc");
		$initial_mail_headers = $db->f("mail_headers");
		$initial_mail_body_html = $db->f("mail_body_html");
		$initial_mail_body_text = $db->f("mail_body_text");
		$identifier = $db->f("identifier");
		$environment = $db->f("environment");
		$remote_address = $db->f("remote_address");
		$site_name = $db->f("site_name");
		if ($sitelist) {
			$t->set_var("site_name", $site_name);
			$t->parse("site_name_block");
		}
			
		// information about verification code
		$ticket_date = $db->f("date_added", DATETIME);
		$date_added = $db->f("date_added", DATETIME);
		$vc = md5($support_id.$date_added[3].$date_added[4].$date_added[5]);
		$user_support_url = $settings["site_url"] . "support_messages.php?support_id=" . $support_id . "&vc=" . $vc;
		$t->set_var("vc_parameter", $vc);
		$t->set_var("user_support_url", $user_support_url);

		//---------------------------------------------------------------
		$support_properties = array();
		$sql  = " SELECT op.property_id, ocp.property_name, op.property_value, ";
		$sql .= " ocp.control_type ";
		$sql .= " FROM (" . $table_prefix . "support_properties op ";
		$sql .= " INNER JOIN " . $table_prefix . "support_custom_properties ocp ON op.property_id=ocp.property_id)";
		$sql .= " WHERE op.support_id=" . $db->tosql($support_id, INTEGER);
		$sql .= " ORDER BY ocp.property_order, op.property_id ";
		$dba->query($sql);
		while ($dba->next_record()) {
			$property_id   = $dba->f("property_id");
			$property_name = $dba->f("property_name");
			$property_value = $dba->f("property_value");
			$control_type = $dba->f("control_type");
			// check value description
			if(($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") && is_numeric($property_value)) {
				$sql  = " SELECT property_value FROM " . $table_prefix . "support_custom_values ";
				$sql .= " WHERE property_value_id=" . $db->tosql($property_value, INTEGER);
				$dbd->query($sql);
				if ($dbd->next_record()) {
					$property_value = $dbd->f("property_value");
				}
			}
			if (isset($support_properties[$property_id])) {
				$support_properties[$property_id]["value"] .= "; " . $property_value;
			} else {
				$support_properties[$property_id] = array(
					"name" => $property_name, "value" => $property_value,
				);
			}
		}
		foreach ($support_properties as $property_id => $property_values) {
			$property_name = $property_values["name"];
			$property_value = $property_values["value"];
			$t->set_var("property_name", $property_name);
			$t->set_var("property_value", $property_value);
			$t->sparse("custom_properties", true);
		}
		
		//---------------------------------------------------------------
		$t->set_var("user_email", $user_email);
		$t->set_var("mail_cc", $ticket_mail_cc);

		$t->set_var("environment", htmlspecialchars($environment));
		$remote_address_desc = $remote_address;
		if ($remote_address_desc && function_exists("geoip_country_code_by_name")) {
			$geoip_country_code = @geoip_country_code_by_name($remote_address);
			if ($geoip_country_code) { $remote_address_desc .= " (".$geoip_country_code.")"; }
		}
		$t->set_var("remote_address_desc", htmlspecialchars($remote_address_desc));

		$product_name = $db->f("product_name");
		$t->set_var("product_name", $product_name);
		$t->set_var("product", $product_name);

		$t->set_var("assign_to", $db->f("assign_to"));
		$t->set_var("type", get_translation($db->f("type_name")));
		$current_status = strlen($db->f("status_name")) ? $db->f("status_name") : "";
		$current_status = get_translation($current_status);
		$t->set_var("current_status", $current_status);
		
		$current_status_id = $db->f("status_id");
		$summary = $db->f("summary");
		$t->set_var("summary", htmlspecialchars($summary));
		
		$priority = get_translation($db->f("priority_name"));
		$t->set_var("priority", $priority);
		$date_modified = $db->f("date_modified", DATETIME);
		$date_modified_string = va_date($datetime_show_format, $date_modified);
		$t->set_var("date_modified", $date_modified_string);

		$date_added = $db->f("date_added", DATETIME);
		$request_added_string = va_date($datetime_show_format, $date_added);
		$date_added_string = $request_added_string;

		$request_viewed = $db->f("date_viewed", DATETIME);
		$request_viewed_string = va_date($datetime_show_format, $request_viewed);

		$t->set_var("request_added", $date_added_string);
		$description = $db->f("description");
		$t->set_var("request_description", nl2br(htmlspecialchars($description)));
		$last_message = $description;

		$identifier_html = htmlspecialchars($identifier);
		if ($identifier) {
			$sql  = " SELECT order_id FROM " . $table_prefix . "orders ";
			$sql .= " WHERE order_id=" . $db->tosql($identifier, INTEGER);
			$sql .= " OR transaction_id=" . $db->tosql($identifier, TEXT);
			$sql .= " OR invoice_number=" . $db->tosql($identifier, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$order_id = $db->f("order_id");
				$identifier_html = "<a href=\"" . $order_details_site_url . "admin_order.php?order_id=" . $order_id ."\">" . htmlspecialchars($identifier) . "</a>";
			} else {
				$identifier_html = htmlspecialchars($identifier) . APPROPRIATE_CODE_ERROR_MSG;
			}
		}
		$t->set_var("identifier", htmlspecialchars($identifier));
		$t->set_var("identifier_html", $identifier_html);

		// update request viewed information
		$sql  = " UPDATE " . $table_prefix . "support_messages SET date_viewed=" . $db->tosql(va_time(), DATETIME);
		$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
		$sql .= " AND date_viewed IS NULL ";
		$sql .= " AND (admin_id_assign_by IS NULL OR admin_id_assign_by=0 ";
		$sql .= " OR (internal=1 AND (admin_id_assign_to IS NULL OR admin_id_assign_to=0 OR admin_id_assign_to=" . $db->tosql(get_session("session_admin_id"), INTEGER) . "))) ";
		$db->query($sql);
		$vc = md5($support_id . $date_added[3].$date_added[4].$date_added[5]);

		if (strlen($remote_address)) {
			$admin_order_black_url = new VA_URL($order_details_site_url . "admin_black_ip.php", true, array("ip", "operation", "currency_code"));
			$admin_order_black_url->add_parameter("popup", CONSTANT, 1);
			$admin_order_black_url->add_parameter("module", CONSTANT, "support");
			$admin_order_black_url->add_parameter("ip_address", CONSTANT, $remote_address);
			$t->set_var("admin_order_black_url", $admin_order_black_url->get_url());
  
			$ip_data = blacklist_check("support", array($remote_address));
			if (isset($ip_data[$remote_address]) && $ip_data[$remote_address]["range"] == $remote_address) {
				$module_rule = $ip_data[$remote_address]["rule"];
				if ($module_rule == "blocked" || $module_rule == "block") {
					$t->set_var("ip_class", "blocked-ip");
				} else if ($module_rule == "warn" || $module_rule == "warning") {
					$t->set_var("ip_class", "warning-ip");
				} else {
					$t->set_var("ip_class", "blacklist-ip");
				}
				$t->set_var("ip_edit_text", va_message("EDIT_BUTTON")." / ".va_message("REMOVE_BUTTON"));
			} else {
				$t->set_var("ip_edit_text", va_message("ADD_TO_BLACK_LIST_MSG"));
			}
		}

		if ($allow_edit) {
			$t->parse("edit_ticket", false);
		}

		if ($allow_close && $db->f("status_id") != $close_status_id) {
			$t->set_var("close_ticket_url", $close_ticket_url);
			$t->parse("close_ticket", false);
		} 
	} else {
		header("Location: ".$admin_support_url);
		exit;
	}
	// check if outgoing_email could be found 
	$outgoing_email = get_outgoing_email($ticket_dep_id, $ticket_type_id, $ticket_product_id);

	$t->set_var("admin_support_attachments_url", "admin_support_attachments.php?support_id=" . urlencode($support_id) . "&dep_id=" . urlencode($dep_id));

	// get department information
	$dep_name = ""; $dep_name = ""; $dep_admins_all = 0;
	$dep_manager_reply_admin_mail = ""; $dep_manager_reply_manager_mail = ""; $dep_manager_reply_user_mail = "";
	$dep_assign_admin_mail = ""; $dep_assign_manager_mail = ""; $dep_assign_to_mail = ""; $dep_assign_user_mail = "";
	$sql = "SELECT * FROM " . $table_prefix . "support_departments WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$short_name = get_translation($db->f("short_name"));
		$dep_name = get_translation($db->f("dep_name"));
		$dep_name = get_translation($db->f("dep_name"));
		$dep_admins_all = $db->f("admins_all");
		$dep_signature = $db->f("signature");

		$dep_manager_reply_admin_mail = json_decode($db->f("manager_reply_admin_mail"), true);
		$dep_manager_reply_manager_mail = json_decode($db->f("manager_reply_manager_mail"), true);
		$dep_manager_reply_user_mail = json_decode($db->f("manager_reply_user_mail"), true);

		$dep_assign_admin_mail = json_decode($db->f("assign_admin_mail"), true);
		$dep_assign_manager_mail = json_decode($db->f("assign_manager_mail"), true);
		$dep_assign_to_mail = json_decode($db->f("assign_to_mail"), true);
		$dep_assign_user_mail = json_decode($db->f("assign_user_mail"), true);
	}
	$t->set_var("department_title", $dep_name);

	// get global helpdesk settings
	$support_settings = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='support'";
	if ($multisites_version) {
		$sql .= "AND ( site_id=1 OR  site_id=" . $db->tosql($site_id,INTEGER). ") ";
		$sql .= "ORDER BY site_id ASC ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$support_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$r = new VA_Record($table_prefix . "support_messages");
	$r->add_where("message_id", INTEGER);
	$r->add_hidden("s_tn", TEXT);
	$r->add_hidden("s_ne", TEXT);
	$r->add_hidden("s_sm", TEXT);
	$r->add_hidden("s_kw", TEXT);
	$r->add_hidden("s_sd", TEXT);
	$r->add_hidden("s_ed", TEXT);
	$r->add_hidden("s_at", TEXT);
	$r->add_hidden("s_dp", TEXT);
	$r->add_hidden("s_tp", TEXT);
	$r->add_hidden("s_st", TEXT);
	$r->add_hidden("s_in", TEXT);
	$r->add_hidden("s_sti", TEXT);
	$r->add_hidden("page", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);

	$r->errors = $ticket_errors;
	if ($dep_admins_all) {
		$sql  = " SELECT a.admin_id,a.admin_name ";
		$sql .= " FROM (".$table_prefix."admins a ";
		$sql .= " INNER JOIN " . $table_prefix . "admin_privileges_settings aps ON a.privilege_id=aps.privilege_id) ";
		$sql .= " WHERE aps.block_name='support' AND aps.permission=1 ";
		$sql .= " ORDER BY a.admin_name ";
	} else {
		$sql  = " SELECT a.admin_id,a.admin_name FROM (" . $table_prefix . "admins a ";
		$sql .= " INNER JOIN " . $table_prefix . "support_users_departments sud ON a.admin_id=sud.admin_id) ";
		$sql .= " WHERE sud.dep_id=" . $db->tosql($dep_id, INTEGER);
		$sql .= " ORDER BY a.admin_name ";
	}
	$admins = get_db_values($sql, array(array("", "")));

	$r->add_textbox("support_id", INTEGER);
	$r->add_textbox("dep_id", INTEGER);
	$r->add_textbox("internal", INTEGER);
	$r->add_select("admin_id_assign_to", INTEGER, $admins, ASSIGN_TO_MSG);
	$r->change_property("admin_id_assign_to", USE_SQL_NULL, false);	
	if ($operation == "assign" || $operation == "return") {
		$r->change_property("admin_id_assign_to", REQUIRED, true);
	}
	$r->add_hidden("admin_id_return_to", INTEGER);
	$r->add_textbox("support_status_id", INTEGER, va_message("STATUS_MSG"));
	$r->change_property("support_status_id", REQUIRED, true);

	$r->add_select("reply_status_id", INTEGER, $reply_statuses);
	$r->change_property("reply_status_id", DEFAULT_VALUE, $reply_default_id);
	$r->change_property("reply_status_id", USE_IN_INSERT, false);
	if ($reply_statuses_no <= 1) {
		$r->change_property("reply_status_id", SHOW, false);
		if ($reply_statuses_no == 1) {
			$reply_status = $support_statuses[$reply_status_id]["status_desc"];
		} else {
			$reply_status = str_replace("{status_type}", va_message("REPLY_TO_CUSTOMER_MSG"), $type_absent_error);
		}
		$t->set_var("reply_status", htmlspecialchars($reply_status));
		$t->sparse("reply_status_block", false);
	}
	$r->add_select("assign_status_id", INTEGER, $assign_statuses);
	$r->change_property("assign_status_id", DEFAULT_VALUE, $assign_default_id);
	$r->change_property("assign_status_id", USE_IN_INSERT, false);
	if ($assign_statuses_no <= 1) {
		$r->change_property("assign_status_id", SHOW, false);
		if ($assign_statuses_no == 1) {
			$assign_status = $support_statuses[$assign_status_id]["status_desc"];
		} else {
			$assign_status = str_replace("{status_type}", va_message("ASSIGN_TICKET_MSG"), $type_absent_error);
		}
		$t->set_var("assign_status", htmlspecialchars($assign_status));
		$t->sparse("assign_status_block", false);
	}
	
	$r->add_select("return_status_id", INTEGER, $return_statuses);
	$r->change_property("return_status_id", DEFAULT_VALUE, $return_default_id);
	$r->change_property("return_status_id", USE_IN_INSERT, false);
	if ($return_statuses_no <= 1) {
		$r->change_property("return_status_id", SHOW, false);
		if ($return_statuses_no == 1) {
			$return_status = $support_statuses[$return_status_id]["status_desc"];
		} else {
			$return_status = str_replace("{status_type}", va_message("ASSIGN_TICKET_MSG"), $type_absent_error);
		}
		$t->set_var("return_status", htmlspecialchars($return_status));
		$t->sparse("return_status_block", false);
	}

	$r->add_select("other_status_id", INTEGER, $other_statuses);
	$r->change_property("other_status_id", DEFAULT_VALUE, $other_default_id);
	$r->change_property("other_status_id", USE_IN_INSERT, false);
	if ($other_statuses_no <= 1) {
		$r->change_property("other_status_id", SHOW, false);
		if ($other_statuses_no == 1) {
			$other_status = $support_statuses[$other_status_id]["status_desc"];
		} else {
			$other_status = str_replace("{status_type}", va_message("OTHER_ACTION_MSG"), $type_absent_error);
		}
		$t->set_var("other_status", htmlspecialchars($other_status));
		$t->sparse("other_status_block", false);
	}

	// forward fields
	$r->add_textbox("forward_mail", TEXT);

	$r->add_select("forward_status_id", INTEGER, $forward_statuses);
	$r->change_property("forward_status_id", DEFAULT_VALUE, $forward_default_id);
	$r->change_property("forward_status_id", USE_IN_INSERT, false);
	if ($forward_statuses_no <= 1) {
		$r->change_property("forward_status_id", SHOW, false);
		if ($forward_statuses_no == 1) {
			$forward_status = $support_statuses[$forward_status_id]["status_desc"];
		} else {
			$forward_status = str_replace("{status_type}", va_message("FORWARD_TICKET_MSG"), $type_absent_error);
		}
		$t->set_var("forward_status", htmlspecialchars($forward_status));
		$t->sparse("forward_status_block", false);
	}
	$r->add_textbox("forward_to", TEXT, va_message("EMAIL_TO_MSG"));
	$r->change_property("forward_to", DEFAULT_VALUE, get_setting_value($support_settings, "forward_mail_to"));
	$r->change_property("forward_to", USE_IN_INSERT, false);

	$forward_default_from = get_setting_value($support_settings, "forward_mail_from");
	if (!$forward_default_from) {
		$sql = " SELECT email FROM ".$table_prefix."admins WHERE admin_id=".$db->tosql($admin_id, INTEGER);
		$admin_email = get_db_value($sql);
		if ($admin_email) {
			$forward_default_from = $admin_email;	
		} else {
			$forward_default_from = ($outgoing_email) ? $outgoing_email : $settings["admin_email"];	
		}
	}
	$t->set_var("forward_default_from", htmlspecialchars($forward_default_from));
	$r->add_textbox("forward_from", TEXT, va_message("EMAIL_FROM_MSG"));
	$r->change_property("forward_from", DEFAULT_VALUE, $forward_default_from);
	$r->change_property("forward_from", USE_IN_INSERT, false);

	$r->add_textbox("forward_cc", TEXT, va_message("EMAIL_CC_MSG"));
	$r->change_property("forward_cc", DEFAULT_VALUE, get_setting_value($support_settings, "forward_mail_cc"));
	$r->change_property("forward_cc", USE_IN_INSERT, false);
	$r->add_textbox("forward_bcc", TEXT, va_message("EMAIL_BCC_MSG"));
	$r->change_property("forward_bcc", DEFAULT_VALUE, get_setting_value($support_settings, "forward_mail_bcc"));
	$r->change_property("forward_bcc", USE_IN_INSERT, false);
	$r->add_textbox("forward_reply_to", TEXT, va_message("EMAIL_REPLY_TO_MSG"));
	$r->change_property("forward_reply_to", DEFAULT_VALUE, get_setting_value($support_settings, "forward_mail_reply_to"));
	$r->change_property("forward_reply_to", USE_IN_INSERT, false);
	$r->add_textbox("forward_return_path", TEXT, va_message("EMAIL_RETURN_PATH_MSG"));
	$r->change_property("forward_return_path", DEFAULT_VALUE, get_setting_value($support_settings, "forward_mail_return_path"));
	$r->change_property("forward_return_path", USE_IN_INSERT, false);

	$forward_default_subject = get_setting_value($support_settings, "forward_mail_subject", $summary);
	parse_value($forward_default_subject);
	$r->add_textbox("forward_subject", TEXT, va_message("EMAIL_SUBJECT_MSG"));
	$r->change_property("forward_subject", DEFAULT_VALUE, $forward_default_subject);
	$r->change_property("forward_subject", USE_IN_INSERT, false);

	$r->add_textbox("admin_id_assign_by", INTEGER);
	$r->change_property("admin_id_assign_by", USE_SQL_NULL, false);
	$r->add_textbox("message_text", TEXT, MESSAGE_MSG);
	$r->change_property("message_text", PARSE_NAME, "response_message");
	$r->change_property("message_text", TRIM, true);
	$r->change_property("message_text", REQUIRED, true);

	$r->add_textbox("date_added", DATETIME);
	$r->get_form_values();

	$errors = "";

	$session_rnd = get_session("session_rnd");
	$operation = get_param("operation");
	$rnd = get_param("rnd");

	if ($operation && $rnd != $session_rnd) {

		// set status
		if ($operation == "assign") {
			if ($assign_statuses_no == 1) {
				$r->set_value("support_status_id", $assign_status_id);
			} else if ($assign_statuses_no > 1) { 
				$r->set_value("support_status_id", $r->get_value("assign_status_id"));
			}
		} else if ($operation == "return") {
			$r->set_value("admin_id_assign_to", $r->get_value("admin_id_return_to"));
			if ($return_statuses_no == 1) {
				$r->set_value("support_status_id", $return_status_id);
			} else if ($return_statuses_no > 1) { 
				$r->set_value("support_status_id", $r->get_value("return_status_id"));
			}
		} else if ($operation == "other") {
			if ($other_statuses_no == 1) {
				$r->set_value("support_status_id", $other_status_id);
			} else if ($other_statuses_no > 1) { 
				$r->set_value("support_status_id", $r->get_value("other_status_id"));
			}
		} else if ($operation == "forward") {
			if ($forward_statuses_no == 1) {
				$r->set_value("support_status_id", $forward_status_id);
			} else if ($forward_statuses_no > 1) { 
				$r->set_value("support_status_id", $r->get_value("forward_status_id"));
			}
			$r->change_property("forward_to", REQUIRED, true);

		} else {
			// use for reply operation
			if ($reply_statuses_no == 1) {
				$r->set_value("support_status_id", $reply_status_id);
			} else if ($reply_statuses_no > 1) { 
				$r->set_value("support_status_id", $r->get_value("reply_status_id"));
			}
		}

		if ($allow_reply) {
			$is_valid = $r->validate();
		} else {
			$is_valid = false;
			$r->errors = REPLY_TICKET_NOT_ALLOWED_MSG;
		}

		if ($is_valid) {
			$new_status_id = $r->get_value("support_status_id");
			$internal_message = isset($support_statuses[$new_status_id]) ? $support_statuses[$new_status_id]["is_internal"] : 0;
			$is_update_status = isset($support_statuses[$new_status_id]) ? $support_statuses[$new_status_id]["is_update_status"] : 0;
			$is_keep_assigned = isset($support_statuses[$new_status_id]) ? $support_statuses[$new_status_id]["is_keep_assigned"] : 0;

			$date_added = va_time();
			$r->set_value("dep_id", $dep_id);
			$r->set_value("internal", intval($internal_message));
			$r->set_value("date_added", $date_added);
			$r->set_value("admin_id_assign_by", get_session("session_admin_id"));

			if ($operation == "forward") {
				$forward_headers = array();
				$forward_headers["to"] = $r->get_value("forward_to");
				$forward_headers["from"] = $r->get_value("forward_from");
				$forward_headers["cc"] = $r->get_value("forward_cc");
				$forward_headers["bcc"] = $r->get_value("forward_bcc");
				$forward_headers["reply_to"] = $r->get_value("forward_reply_to");
				$forward_headers["return_path"] = $r->get_value("forward_return_path");
				$forward_headers["mail_type"] = get_setting_value($support_settings, "forward_message_type");
				$forward_headers["subject"] = $r->get_value("forward_subject");
				$forward_headers["message"] = get_setting_value($support_settings, "forward_mail_message", $message_text);
				$r->set_value("forward_mail", json_encode($forward_headers));
			}
				
			if ($r->insert_record()) {
				// get added message_id
				$new_message_id = $db->last_insert_id();
				$r->set_value("message_id", $new_message_id);

				$date_added_string = va_date($datetime_show_format, $date_added);
				$support_url = $settings["site_url"] . "support_messages.php?support_id=" . $support_id . "&vc=" . $vc;
    
				// get admin data who assigned the ticket 
				$admin_id = get_session("session_admin_id");
				$admin_name = ""; $admin_email = ""; $from_admin_email = "";
				$sql = " SELECT admin_name,email FROM " . $table_prefix . "admins ";
				$sql.= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
				$db->query($sql); 
				if ($db->next_record()) {
					$admin_name = $db->f("admin_name");
					$admin_email = $db->f("email");
					$from_admin_email = $admin_email;
				}

				// get data for admin which was assigned to ticket
				$admin_id_assigned_to = $r->get_value("admin_id_assign_to");
				$admin_name_assigned_to = ""; $admin_email_assigned_to = "";
				if (intval($admin_id_assigned_to) > 0) {
					$sql = " SELECT admin_name,email FROM " . $table_prefix . "admins ";
					$sql.= " WHERE admin_id=" . $db->tosql($admin_id_assigned_to, INTEGER);
					$db->query($sql); 
					if ($db->next_record()) {
						$admin_name_assigned_to = $db->f("admin_name");
						$admin_email_assigned_to = $db->f("email");
					}
				}
    
				$current_status = get_array_value($r->get_value("support_status_id"), $support_statuses);
    
				$t->set_var("status", $current_status);
				$t->set_var("current_status", $current_status);
				$t->set_var("vc", $vc);
				$t->set_var("support_url", $support_url);
				$t->set_var("message_added", $date_added_string);
				$t->set_var("date_modified", $date_added_string);

				// check attachments
				$attachments = array();
				$sql  = " SELECT attachment_id, file_name, file_path FROM " . $table_prefix . "support_attachments ";
				$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
				$sql .= " AND admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
				$sql .= " AND message_id=0 ";
				$sql .= " AND attachment_status=0 ";
				$db->query($sql);
				while ($db->next_record()) {
					$file_name = $db->f("file_name");
					$file_path = $db->f("file_path");
					if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
						$file_path = "../".$file_path;
					}
					$attachments[] = array($file_name, $file_path);
				}
    
				$sql  = " UPDATE " . $table_prefix . "support_attachments ";
				$sql .= " SET message_id=" . $db->tosql($new_message_id, INTEGER);
				$sql .= " , attachment_status=1 ";
				$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
				$sql .= " AND admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
				$sql .= " AND message_id=0 ";
				$sql .= " AND attachment_status=0 ";
				$db->query($sql);

				// prepare tags for email
				$mail_tags = array();
				$ticket_date_string = va_date($datetime_show_format, $ticket_date);
				$message_date_string = va_date($datetime_show_format, va_time());
				$site_url = get_setting_value($settings, "site_url", "");
				$admin_site_url = get_setting_value($settings, "admin_site_url", $site_url."admin/");
				$vc = md5($support_id . $ticket_date[3].$ticket_date[4].$ticket_date[5]);
				$ticket_url = $site_url . "support_messages.php?support_id=" . $support_id. "&vc=" . $vc;
				$admin_ticket_url = $admin_site_url."admin_support_reply.php?support_id=".$support_id;
				$message_text = $r->get_value("message_text");

				$mail_tags = array(
					"ticket_id" => $support_id,
					"support_id" => $support_id,
					"message_id" => $new_message_id,
					"vc" => $vc,
	    
					"ticket_added" => $ticket_date_string,
					"request_added" => $ticket_date_string,
					"message_added" => $message_date_string,
					"date_added" => $ticket_date_string,
					"date_modified" => $message_date_string,
	    
					"user_id" => $user_id,
					"user_name" => $user_name,
					"user_email" => $user_email,
					"from_user" => $user_name,
					"from_email" => $user_email,

					"admin_id" => $admin_id,
					"admin_name" => $admin_name,
					"admin_id_assign_by" => $admin_id,
					"admin_id_assigned_by" => $admin_id,
					"admin_email_assign_by" => $admin_email,
					"admin_email_assigned_by" => $admin_email,
					"admin_name_assign_by" => $admin_name,
					"admin_name_assigned_by" => $admin_name,
					"admin_assign_by" => $admin_name,
					"admin_assigned_by" => $admin_name,
					"assign_by" => $admin_name,
					"assigned_by" => $admin_name,

					"admin_id_assign_to" => $admin_id_assigned_to,
					"admin_id_assigned_to" => $admin_id_assigned_to,
					"admin_email_assign_to" => $admin_email_assigned_to,
					"admin_email_assigned_to" => $admin_email_assigned_to,
					"admin_name_assign_to" => $admin_name_assigned_to,
					"admin_name_assigned_to" => $admin_name_assigned_to,
					"admin_assign_to" => $admin_name_assigned_to,
					"admin_assigned_to" => $admin_name_assigned_to,
					"assign_to" => $admin_name_assigned_to,
					"assigned_to" => $admin_name_assigned_to,
	    
					"summary" => $summary,
					"subject" => $summary,
	    
					"description" => $description,
					"message_text" => $message_text,
					"identifier" => $identifier,
					"environment" => $environment,
	    
					"dep_name" => $dep_name,
					"site_name" => $site_name,
	    
					"site_url" => $site_url,
					"support_url" => $ticket_url,
					"ticket_url" => $ticket_url,
					"user_support_url" => $ticket_url,
					"user_ticket_url" => $ticket_url,
					"admin_support_url" => $admin_ticket_url,
					"admin_ticket_url" => $admin_ticket_url,
				);
				// add custom properties to mail tags list
				foreach ($support_properties as $property_id => $property_values) {
					$property_name = $property_values["name"];
					$property_value = $property_values["value"];
					$mail_tags["field_name_".$property_id] = $property_name;
					$mail_tags["field_value_".$property_id] = $property_value;
					$mail_tags["field_".$property_id] = $property_value;
				}

				// send email notification to admin
				if ($operation == "assign" || $operation == "return") {
				  // assignment notification block

					// check global assignment notifications
					$assign_admin_notification = get_setting_value($support_settings, "assign_admin_notification");
					$assign_manager_notification = get_setting_value($support_settings, "assign_manager_notification");
					$assign_to_notification = get_setting_value($support_settings, "assign_to_notification");
					$assign_user_notification = get_setting_value($support_settings, "assign_user_notification");

					// check department notification settings
					$dep_assign_admin_notification = get_setting_value($dep_assign_admin_mail, "assign_admin_notification", 0);
					$assign_admin_hp_disable = get_setting_value($dep_assign_admin_mail, "assign_admin_hp_disable", 0);
					if ($assign_admin_hp_disable) { $assign_admin_notification = 0; }
		    
					$dep_assign_manager_notification = get_setting_value($dep_assign_manager_mail, "assign_manager_notification", 0);
					$assign_manager_hp_disable = get_setting_value($dep_assign_manager_mail, "assign_manager_hp_disable", 0);
					if ($assign_manager_hp_disable) { $assign_manager_notification = 0; }
		    
					$dep_assign_to_notification = get_setting_value($dep_assign_to_mail, "assign_to_notification", 0);
					$assign_to_hp_disable = get_setting_value($dep_assign_to_mail, "assign_to_hp_disable", 0);
					if ($assign_to_hp_disable) { $assign_to_notification = 0; }

					$dep_assign_user_notification= get_setting_value($dep_assign_user_mail, "assign_user_notification", 0);
					$assign_user_hp_disable = get_setting_value($dep_assign_user_mail, "assign_user_hp_disable", 0);
					if ($assign_user_hp_disable) { $assign_user_notification = 0; }

					// global assignment admin notification
					if ($assign_admin_notification) {
						$mail_to = get_setting_value($support_settings, "assign_admin_to", $settings["admin_email"]);
						$admin_subject = get_setting_value($support_settings, "assign_admin_subject", $summary);
						$admin_message = get_setting_value($support_settings, "assign_admin_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($support_settings, "assign_admin_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($support_settings, "assign_admin_cc");
						$email_headers["bcc"] = get_setting_value($support_settings, "assign_admin_bcc");
						$email_headers["reply_to"] = get_setting_value($support_settings, "assign_admin_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "assign_admin_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "assign_admin_message_type");
		    
						va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
					} // end global assignment admin notification
		    
					// department assignment admin notification
					if ($dep_assign_admin_notification) {
						$mail_to = get_setting_value($dep_assign_admin_mail, "assign_admin_to", $settings["admin_email"]);
						$admin_subject = get_setting_value($dep_assign_admin_mail, "assign_admin_subject", $summary);
						$admin_message = get_setting_value($dep_assign_admin_mail, "assign_admin_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($dep_assign_admin_mail, "assign_admin_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($dep_assign_admin_mail, "assign_admin_cc");
						$email_headers["bcc"] = get_setting_value($dep_assign_admin_mail, "assign_admin_bcc");
						$email_headers["reply_to"] = get_setting_value($dep_assign_admin_mail, "assign_admin_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_assign_admin_mail, "assign_admin_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_assign_admin_mail, "assign_admin_message_type");
		    
						va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
					} // end department assignment admin notification

					// global assignment by manager notification
					if ($assign_manager_notification && $admin_email) {
						$manager_subject = get_setting_value($support_settings, "assign_manager_subject", $summary);
						$manager_message = get_setting_value($support_settings, "assign_manager_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($support_settings, "assign_manager_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($support_settings, "assign_manager_cc");
						$email_headers["bcc"] = get_setting_value($support_settings, "assign_manager_bcc");
						$email_headers["reply_to"] = get_setting_value($support_settings, "assign_manager_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "assign_manager_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "assign_manager_message_type");
		    
						va_mail($admin_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end global assignment by manager notification
		    
					// department assignment by manager notification
					if ($dep_assign_manager_notification && $admin_email) {
						$manager_subject = get_setting_value($dep_assign_manager_mail, "assign_manager_subject", $summary);
						$manager_message = get_setting_value($dep_assign_manager_mail, "assign_manager_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($dep_assign_manager_mail, "assign_manager_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($dep_assign_manager_mail, "assign_manager_cc");
						$email_headers["bcc"] = get_setting_value($dep_assign_manager_mail, "assign_manager_bcc");
						$email_headers["reply_to"] = get_setting_value($dep_assign_manager_mail, "assign_manager_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_assign_manager_mail, "assign_manager_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_assign_manager_mail, "assign_manager_message_type");
		    
						va_mail($admin_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end department assignment by manager notification
					
					// global assignment to manager notification
					if ($assign_to_notification && $admin_email_assigned_to) {
						$manager_subject = get_setting_value($support_settings, "assign_to_subject", $summary);
						$manager_message = get_setting_value($support_settings, "assign_to_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($support_settings, "assign_to_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($support_settings, "assign_to_cc");
						$email_headers["bcc"] = get_setting_value($support_settings, "assign_to_bcc");
						$email_headers["reply_to"] = get_setting_value($support_settings, "assign_to_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "assign_to_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "assign_to_message_type");
		    
						va_mail($admin_email_assigned_to, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end global assignment to manager notification
		    
					// department assignment to manager notification
					if ($dep_assign_to_notification && $admin_email_assigned_to) {
						$manager_subject = get_setting_value($dep_assign_to_mail, "assign_to_subject", $summary);
						$manager_message = get_setting_value($dep_assign_to_mail, "assign_to_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($dep_assign_to_mail, "assign_to_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($dep_assign_to_mail, "assign_to_cc");
						$email_headers["bcc"] = get_setting_value($dep_assign_to_mail, "assign_to_bcc");
						$email_headers["reply_to"] = get_setting_value($dep_assign_to_mail, "assign_to_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_assign_to_mail, "assign_to_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_assign_to_mail, "assign_to_message_type");
		    
						va_mail($admin_email_assigned_to, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end department assignment to manager notification


					// global assignment user notification
					if ($assign_user_notification && $user_email) {
						$manager_subject = get_setting_value($support_settings, "assign_user_subject", $summary);
						$manager_message = get_setting_value($support_settings, "assign_user_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($support_settings, "assign_user_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($support_settings, "assign_user_cc");
						$email_headers["bcc"] = get_setting_value($support_settings, "assign_user_bcc");
						$email_headers["reply_to"] = get_setting_value($support_settings, "assign_user_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "assign_user_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "assign_user_message_type");
		    
						va_mail($user_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end global assignment user notification
		    
					// department assignment user notification
					if ($dep_assign_user_notification && $user_email) {
						$manager_subject = get_setting_value($dep_assign_user_mail, "assign_user_subject", $summary);
						$manager_message = get_setting_value($dep_assign_user_mail, "assign_user_message", $message_text);
		    
						$email_headers = array();
						$email_headers["from"] = ($outgoing_email) ? $outgoing_email : get_setting_value($dep_assign_user_mail, "assign_user_from", $settings["admin_email"]);	
						$email_headers["cc"] = get_setting_value($dep_assign_user_mail, "assign_user_cc");
						$email_headers["bcc"] = get_setting_value($dep_assign_user_mail, "assign_user_bcc");
						$email_headers["reply_to"] = get_setting_value($dep_assign_user_mail, "assign_user_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_assign_user_mail, "assign_user_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_assign_user_mail, "assign_user_message_type");
		    
						va_mail($user_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end department assignment user notification

					// end assignment notification block 
				} else if ($operation == "forward") {
					$forward_to = $r->get_value("forward_to");
					if ($forward_to) {
						unset($forward_headers["to"]);
						unset($forward_headers["subject"]);
						unset($forward_headers["message"]);

						$forward_subject = $r->get_value("forward_subject");
						$forward_message = get_setting_value($support_settings, "forward_mail_message", $message_text);
		    
						va_mail($forward_to, $forward_subject, $forward_message, $forward_headers, $attachments, $mail_tags);
					} // end department assignment user notification

				} else if ($operation == "other") {
					// here could be some code for other action
				} else if ($operation == "reply") {
					// manager reply notification block

					// check global admin and user notification 
					$manager_reply_user_notification = get_setting_value($support_settings, "manager_reply_user_notification");
					$manager_reply_manager_notification= get_setting_value($support_settings, "manager_reply_manager_notification");
					$manager_reply_admin_notification = get_setting_value($support_settings, "manager_reply_admin_notification");
		    
					// check department notification settings
					$dep_manager_reply_user_notification = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_notification", 0);
					$manager_reply_user_hp_disable = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_hp_disable", 0);
					if ($manager_reply_user_hp_disable) { $manager_reply_user_notification = 0; }
		    
					$dep_manager_reply_manager_notification = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_notification", 0);
					$manager_reply_manager_hp_disable = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_hp_disable", 0);
					if ($manager_reply_manager_hp_disable) { $manager_reply_manager_notification = 0; }
		    
					$dep_manager_reply_admin_notification = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_notification", 0);
					$manager_reply_admin_hp_disable = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_hp_disable", 0);
					if ($manager_reply_admin_hp_disable) { $manager_reply_admin_notification = 0; }

					// global customer notification
					if ($manager_reply_user_notification) {
						$user_subject = get_setting_value($support_settings, "manager_reply_user_subject", $summary);
						$user_message = get_setting_value($support_settings, "manager_reply_user_message", $message_text);
        
						$mail_cc = get_setting_value($support_settings, "manager_reply_user_cc");
						if ($ticket_mail_cc) {
							if ($mail_cc) { $mail_cc .= ", "; }
							$mail_cc .= $ticket_mail_cc;
						}
						$mail_bcc = get_setting_value($support_settings, "manager_reply_user_bcc");
						if ($ticket_mail_bcc) {
							if ($mail_bcc) { $mail_bcc .= ", "; }
							$mail_bcc .= $ticket_mail_bcc;
						}
		      
						$email_headers = array();
						if ($outgoing_email) {
							$email_headers["from"] = $outgoing_email;	
						} else {
							$email_headers["from"] = get_setting_value($support_settings, "manager_reply_user_from", $settings["admin_email"]);	
						}
						$email_headers["cc"] = $mail_cc;
						$email_headers["bcc"] = $mail_bcc;
						$email_headers["reply_to"] = get_setting_value($support_settings, "manager_reply_user_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "manager_reply_user_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "manager_reply_user_message_type");
		    
						va_mail($ticket_user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
					}
					// end global customer notification
		    
					// department customer notification
					if ($dep_manager_reply_user_notification) {
						$user_subject = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_subject", $summary);
						$user_message = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_message", $message_text);
        
						$mail_cc = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_cc");
						if ($ticket_mail_cc) {
							if ($mail_cc) { $mail_cc .= ", "; }
							$mail_cc .= $ticket_mail_cc;
						}
						$mail_bcc = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_bcc");
						if ($ticket_mail_bcc) {
							if ($mail_bcc) { $mail_bcc .= ", "; }
							$mail_bcc .= $ticket_mail_bcc;
						}
		      
						$email_headers = array();
						if ($outgoing_email) {
							$email_headers["from"] = $outgoing_email;	
						} else {
							$email_headers["from"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_from", $settings["admin_email"]);	
						}
						$email_headers["cc"] = $mail_cc;
						$email_headers["bcc"] = $mail_bcc;
						$email_headers["reply_to"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_message_type");
		    
						va_mail($ticket_user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
					}
					// end department customer notification
		    
					// global manager notification
					if ($manager_reply_manager_notification && $from_admin_email) {
						$manager_subject = get_setting_value($support_settings, "manager_reply_manager_subject", $summary);
						$manager_message = get_setting_value($support_settings, "manager_reply_manager_message", $message_text);
		    
						$email_headers = array();
						if ($outgoing_email) {
							$email_headers["from"] = $outgoing_email;	
						} else {
							$email_headers["from"] = get_setting_value($support_settings, "manager_reply_manager_from", $settings["admin_email"]);	
						}
						$email_headers["cc"] = get_setting_value($support_settings, "manager_reply_manager_cc");
						$email_headers["bcc"] = get_setting_value($support_settings, "manager_reply_manager_bcc");
						$email_headers["reply_to"] = get_setting_value($support_settings, "manager_reply_manager_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "manager_reply_manager_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "manager_reply_manager_message_type");
		    
						va_mail($from_admin_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end global manager notification
		    
					// department manager notification
					if ($dep_manager_reply_manager_notification && $from_admin_email) {
						$manager_subject = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_subject", $summary);
						$manager_message = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_message", $message_text);
		    
						$email_headers = array();
						if ($outgoing_email) {
							$email_headers["from"] = $outgoing_email;	
						} else {
							$email_headers["from"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_from", $settings["admin_email"]);	
						}
						$email_headers["cc"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_cc");
						$email_headers["bcc"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_bcc");
						$email_headers["reply_to"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_message_type");
		    
						va_mail($from_admin_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
					} // end department manager notification
		    
					// global admin notification
					if ($manager_reply_admin_notification) {
						$mail_to = get_setting_value($support_settings, "manager_reply_admin_to", $settings["admin_email"]);
		    
						$admin_subject = get_setting_value($support_settings, "manager_reply_admin_subject", $summary);
						$admin_message = get_setting_value($support_settings, "manager_reply_admin_message", $message_text);
		      
						$email_headers = array();
						if ($outgoing_email) {
							$email_headers["from"] = $outgoing_email;	
						} else {
							$email_headers["from"] = get_setting_value($support_settings, "manager_reply_admin_from", $settings["admin_email"]);	
						}
						$email_headers["cc"] = get_setting_value($support_settings, "manager_reply_admin_cc");
						$email_headers["bcc"] = get_setting_value($support_settings, "manager_reply_admin_bcc");
						$email_headers["reply_to"] = get_setting_value($support_settings, "manager_reply_admin_reply_to");
						$email_headers["return_path"] = get_setting_value($support_settings, "manager_reply_admin_return_path");
						$email_headers["mail_type"] = get_setting_value($support_settings, "manager_reply_admin_message_type");
		    
						va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
					} // end global admin notification
		    
					// department admin notification
					if ($dep_manager_reply_admin_notification) {
						$mail_to = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_to", $settings["admin_email"]);
		    
						$admin_subject = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_subject", $summary);
						$admin_message = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_message", $message_text);
		      
						$email_headers = array();
						if ($outgoing_email) {
							$email_headers["from"] = $outgoing_email;	
						} else {
							$email_headers["from"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_from", $settings["admin_email"]);	
						}
						$email_headers["cc"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_cc");
						$email_headers["bcc"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_bcc");
						$email_headers["reply_to"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_reply_to");
						$email_headers["return_path"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_return_path");
						$email_headers["mail_type"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_message_type");
		    
						va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
					} // end department admin notification

				} // end manager reply notification


				// update main ticket data
				$sql  = " UPDATE " . $table_prefix . "support SET ";
				$sql .= " date_modified=" . $db->tosql(va_time(), DATETIME);
				$sql .= " , admin_id_modified_by=" . intval($admin_id);
				if ($is_update_status) {
					$sql .= " , support_status_id=" . intval($new_status_id); 
				}
				if ($operation == "assign" || $operation == "return") {
					$sql .= " , admin_id_assign_by=" . $db->tosql($r->get_value("admin_id_assign_by"), INTEGER, true, false);
					$sql .= " , admin_id_assign_to=" . $db->tosql($r->get_value("admin_id_assign_to"), INTEGER, true, false);
				} else if (!$is_keep_assigned) {
					$sql .= " , admin_id_assign_by=0 ";
					$sql .= " , admin_id_assign_to=0 ";
				}
				$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
				$db->query($sql);
			}
        
			header("Location: " . $admin_support_url);
			exit;
		}
		else
		{
			//$errors .= "Please provide information in the sections with red, italicized headings, then click 'Submit'.<br>";
			set_session("session_rnd", "");
		}
	} else {
		// new message (set default values)
		$r->set_default_values();
	}

	// set ticket information
	$t->set_var("summary", htmlspecialchars($summary));
	$t->set_var("description", nl2br(htmlspecialchars($description)));
	$t->set_var("user_name", htmlspecialchars($user_name));
	$t->set_var("identifier", htmlspecialchars($identifier));
	$t->set_var("environment", htmlspecialchars($environment));

	// show ticket statistics
	$currency = get_currency();
	$orders_stats = false; $orders_name_stats = false;
	$sql  = " SELECT COUNT(*) AS orders_number, SUM(order_total) AS orders_total, o.order_status, os.status_name ";
	$sql .= " FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id)";
	if ($user_id > 0) {
		$where = " WHERE (user_id=" . $db->tosql($user_id, INTEGER) . " OR email=" . $db->tosql($user_email, TEXT) . ") ";
	} else {
		$where = " WHERE email=" . $db->tosql($user_email, TEXT);
	}
	$group_by = " GROUP BY o.order_status, os.status_name ";
	$db->query($sql.$where.$group_by);
	if ($db->next_record()) {
		$orders_stats = true;
	} else {
		$where = " WHERE name=" . $db->tosql($user_name, TEXT);
		$name_parts = explode(" ", $user_name, 2);
		if (sizeof($name_parts) == 1) {
			$where .= " OR first_name=" . $db->tosql($name_parts[0], TEXT);
		} else {
			$where .= " OR (first_name=" . $db->tosql($name_parts[0], TEXT);
			$where .= " AND last_name=" . $db->tosql($name_parts[1], TEXT) . ") ";
		}
		$db->query($sql.$where.$group_by);
		if ($db->next_record()) {
			$orders_name_stats = true;
		}
	}

	if ($orders_stats || $orders_name_stats) {
		$orders_number_sum = 0; $orders_total_sum = 0;
		$admin_orders_url = new VA_URL("admin_orders.php", false);
		if ($user_id > 0) {
			$admin_orders_url->add_parameter("s_uid", CONSTANT, $user_id);
		}
		if ($orders_name_stats) {
			$admin_orders_url->add_parameter("s_ne", CONSTANT, $user_name);
		} else {
			$admin_orders_url->add_parameter("s_ne", CONSTANT, $user_email);
		}

		do {
			$order_status = get_translation($db->f("status_name"));
			$order_status_id = $db->f("order_status");
			if (!$order_status) { $order_status = $order_status_id; }
			$orders_number = $db->f("orders_number");
			$orders_total = $db->f("orders_total");
			$orders_number_sum += $orders_number; 
			$orders_total_sum += $orders_total;
			$admin_orders_url->add_parameter("s_os", CONSTANT, $order_status_id);

			$t->set_var("order_status", $order_status);
			$t->set_var("orders_number", $orders_number);
			$t->set_var("admin_orders_url", $admin_orders_url->get_url());
			$t->set_var("orders_total", $currency["left"] . number_format($orders_total * $currency["rate"], 2) . $currency["right"]);
			$t->sparse("orders_statuses", true);
		} while ($db->next_record());

		$admin_orders_url->add_parameter("s_os", CONSTANT, "");
		$t->set_var("admin_orders_url", $admin_orders_url->get_url());
		$t->set_var("orders_number_sum", $orders_number_sum);
		$t->set_var("orders_total_sum", $currency["left"] . number_format($orders_total_sum * $currency["rate"], 2) . $currency["right"]);
		if ($orders_name_stats) {
			$t->sparse("orders_name_stats", false);
		} else {
			$t->sparse("orders_stats", false);
		}
	}

	$sql  = " SELECT COUNT(*) AS tickets_number, s.support_status_id, ss.status_name ";
	$sql .= " FROM (" . $table_prefix . "support s ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON s.support_status_id=ss.status_id)";
	if ($user_id > 0) {
		$sql .= " WHERE (user_id=" . $db->tosql($user_id, INTEGER) . " OR user_email=" . $db->tosql($user_email, TEXT) . ") ";
	} else {
		$sql .= " WHERE user_email=" . $db->tosql($user_email, TEXT);
	}
	$sql .= " GROUP BY s.support_status_id, ss.status_name ";
	$db->query($sql);
	if ($db->next_record()) {
		$tickets_number_sum = 0;
		$admin_tickets_url= new VA_URL("admin_support.php", false);
		if ($user_id > 0) {
			$admin_tickets_url->add_parameter("user_id", CONSTANT, $user_id);
		}
		$admin_tickets_url->add_parameter("s_ne", CONSTANT, $user_email);

		do {
			$ticket_status = get_translation($db->f("status_name"));
			$ticket_status_id = $db->f("support_status_id");
			if (!$ticket_status) { $ticket_status = $ticket_status_id; }
			$tickets_number = $db->f("tickets_number");
			$tickets_number_sum += $tickets_number; 
			$admin_tickets_url->add_parameter("status_id", CONSTANT, $ticket_status_id);

			$t->set_var("ticket_status", $ticket_status);
			$t->set_var("tickets_number", $tickets_number);
			$t->set_var("admin_tickets_url", $admin_tickets_url->get_url());
			$t->sparse("tickets_statuses", true);
		} while ($db->next_record());

		$admin_tickets_url->add_parameter("status_id", CONSTANT, "");
		$admin_tickets_url->add_parameter("s_in", CONSTANT, 2);
		$t->set_var("admin_tickets_url", $admin_tickets_url->get_url());
		$t->set_var("tickets_number_sum", $tickets_number_sum);
		$t->sparse("tickets_stats", false);
	}


	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support_reply.php");

	$admin_header_template = "admin_header_wide.html";
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "support_messages WHERE support_id=" . $db->tosql($support_id, INTEGER));
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "mes_page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT sm.admin_id_assign_by,sm.admin_id_assign_to,sm.message_id,ss.status_name, ";
	$sql .= " a.admin_name, a.privilege_id, aa.admin_name AS assign_to, aa.privilege_id AS addigned_to_privilege_id, ";
	$sql .= " sm.message_text, sm.date_added, sm.date_viewed, sm.internal, sm.forward_mail, ";
	$sql .= " sm.mail_headers, sm.mail_body_html, sm.mail_body_text, sm.reply_from, sm.reply_to ";
	$sql .= " FROM (((" . $table_prefix . "support_messages sm ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id=sm.support_status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id=sm.admin_id_assign_by) ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins aa ON aa.admin_id=sm.admin_id_assign_to) ";
	$sql .= " WHERE sm.support_id=" . $db->tosql($support_id, INTEGER);
	$sql .= " ORDER BY sm.date_added DESC ";
	$db->query($sql);
	if ($db->next_record())
	{
		$tl->remove_parameter("rp");
		$tl->remove_parameter("operation");
		$tl->add_parameter("message_id", DB, "message_id");
		$last_message = $db->f("message_text");

		$t->set_var("support_id", $support_id);

		do
		{
			$message_id = $db->f("message_id");
			$mail_headers = $db->f("mail_headers");
			$mail_body_html = $db->f("mail_body_html");
			$mail_body_text = $db->f("mail_body_text");
			$reply_from = $db->f("reply_from");

			$admin_privilege_id = $db->f("privilege_id");
			$admin_id_assign_to = $db->f("admin_id_assign_to");
			$admin_id_assign_by = $db->f("admin_id_assign_by");
			$addigned_to_privilege_id = $db->f("addigned_to_privilege_id");
			$forward_mail = $db->f("forward_mail");

			$internal_message = $db->f("internal");

			$t->set_var("admin_support_message_url", $tl->get_url("admin_support_message.php"));
			$t->parse("edit_link", false);

			$status = get_translation($db->f("status_name"));

			$internal_message = $db->f("internal");
			$assign_to = $db->f("assign_to");

			$t->set_var("status", $status);
			$t->set_var("message_id", $message_id);
			if ($admin_id_assign_by) {
				$posted_user_class = "site-admin";
				$posted_user_name = $db->f("admin_name");
				$posted_user_type = ($admin_privilege_id && isset($admin_privileges[$admin_privilege_id])) ? $admin_privileges[$admin_privilege_id] : va_message("NOT_AVAILABLE_MSG");
				if (!$posted_user_name) {
					$posted_user_name = "ID: " . $db->f("admin_id_assign_by");
				}
			} else {
				if ($user_id) {
					$posted_user_class = "site-user";
					$posted_user_type = ($user_type_id && isset($user_types[$user_type_id])) ? $user_types[$user_type_id] : va_message("NOT_AVAILABLE_MSG");
				} else {
					$posted_user_class = "site-guest";
					$posted_user_type = va_message("GUEST_MSG");
				}
				if ($reply_from && $reply_from != $user_email) {
					$posted_user_name = $reply_from;
				} else {
					$posted_user_name = strlen($user_name) ? $user_name . " <" . $user_email . ">" : $user_email;
				}
			}
			$date_added = $db->f("date_added", DATETIME);
			$date_added_string = va_date($datetime_show_format, $date_added);
			$t->set_var("date_added", $date_added_string);
			$t->set_var("posted_user_name", htmlspecialchars($posted_user_name));
			$t->set_var("posted_user_class", htmlspecialchars($posted_user_class));
			$t->set_var("posted_user_type", htmlspecialchars($posted_user_type));

			if ($db->f("internal") == 1) {
				$t->parse("internal_block", false);
				$t->set_var("style_am","internal_message");
			} else {
				$t->set_var("internal_block", "");
				$t->set_var("style_am","usual_message");
			}
			if (strlen($db->f("assign_to"))) {
				$t->set_var("message_assign_to", $db->f("assign_to"));
				$t->parse("assign_to_block", false);
			} else {
				$t->set_var("assign_to_block", "");
			}

			// show viewed by block if eligible 
			$viewed_by = "";
			$t->set_var("viewed_by_block", "");
			$t->set_var("date_viewed_block", "");
			$t->set_var("not_viewed_block", "");
			$date_viewed = $db->f("date_viewed", DATETIME);
			if (is_array($date_viewed)) {
				$date_viewed_string = va_date($datetime_show_format, $date_viewed);
				$t->set_var("date_viewed", $date_viewed_string);
				$t->sparse("date_viewed_block", false);
			} else {
				$t->set_var("date_viewed", SUPPORT_NOT_VIEWED_MSG);
				$t->sparse("not_viewed_block", false);
			}
			$viewed_by = "";
			if ($admin_id_assign_to) {
				$viewed_by = strlen($assign_to) ? $assign_to : "ID: ".$admin_id_assign_by;
				$viewed_user_class = "site-admin";
			} else if (!$internal_message) {
				if ($admin_id_assign_by) {
					$viewed_by = va_message("USER_MSG");
					if ($user_id) {
						$viewed_user_class = "site-user";
					} else {
						$viewed_user_class = "site-guest";
					}
				} else {
					$viewed_by = va_message("ADMIN_MSG");
					$viewed_user_class = "site-admin";
				}
			}
			if (strlen($viewed_by)) {
				$t->set_var("viewed_by", htmlspecialchars($viewed_by));
				$t->set_var("viewed_user_name", htmlspecialchars($viewed_by));
				$t->set_var("viewed_user_class", htmlspecialchars($viewed_user_class));
				$t->sparse("viewed_by_block", false);
			}

			// check and parse forward fields
			$t->set_var("forward_to_block", "");
			$t->set_var("forward_cc_block", "");
			$t->set_var("forward_bcc_block", "");
			$t->set_var("forward_subject_block", "");
			if ($forward_mail) {
				$forward_mail = json_decode($forward_mail, true);
				if (isset($forward_mail["to"]) && $forward_mail["to"]) {
					$t->set_var("forward_to", htmlspecialchars($forward_mail["to"]));
					$t->sparse("forward_to_block", false);
				}
				if (isset($forward_mail["cc"]) && $forward_mail["cc"]) {
					$t->set_var("forward_cc", htmlspecialchars($forward_mail["cc"]));
					$t->sparse("forward_cc_block", false);
				}
				if (isset($forward_mail["bcc"]) && $forward_mail["bcc"]) {
					$t->set_var("forward_bcc", htmlspecialchars($forward_mail["bcc"]));
					$t->sparse("forward_bcc_block", false);
				}
				if (isset($forward_mail["subject"]) && $forward_mail["subject"]) {
					$t->set_var("forward_subject", htmlspecialchars($forward_mail["subject"]));
					$t->sparse("forward_subject_block", false);
				}
			}


			// check for mail data
			if ($mail_headers || $mail_body_html || $mail_body_text) {
				if ($mail_headers) {
					$t->set_var("admin_support_mail_data_url", "admin_support_mail_data.php?type=header&message_id=". $message_id);
					$t->parse("mail_headers", false);
				} else {
					$t->set_var("mail_headers", "");
				}
				if ($mail_body_html) {
					$t->set_var("admin_support_mail_data_url", "admin_support_mail_data.php?type=html&message_id=". $message_id);
					$t->parse("mail_body_html", false);
				} else {
					$t->set_var("mail_body_html", "");
				}
				if ($mail_body_text) {
					$t->set_var("admin_support_mail_data_url", "admin_support_mail_data.php?type=text&message_id=". $message_id);
					$t->parse("mail_body_text", false);
				} else {
					$t->set_var("mail_body_text", "");
				}
				$t->parse("mail_data", false);
			} else {
				$t->set_var("mail_data", "");
			}

			// check for attachments
			$sql  = " SELECT * FROM " . $table_prefix . "support_attachments ";
			$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
			$sql .= " AND message_id=" . $db->tosql($message_id, INTEGER);
			$sql .= " AND attachment_status=1 ";
			$dba->query($sql);
			if ($dba->next_record()) {
				$attach_no = 1;
				$attachments_files = ""; 
				do {
					$attachment_id = $dba->Record["attachment_id"];
					$file_name     = $dba->Record["file_name"];
					$file_path     = $dba->Record["file_path"];
					// use one level up if path is not absolute
					if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
						$file_path = "../".$file_path;
					}
					$size	         = get_nice_bytes(filesize($file_path));
					$attachments_files  .= $attach_no . ". <a target=\"_blank\" href=\"admin_support_attachment.php?atid=" . $attachment_id . "\">" . $file_name . "</a> (" . $size . ")&nbsp;&nbsp;";
					$attach_no++;
				} while ($dba->next_record());
				$t->set_var("attachments_files", $attachments_files);
				$t->parse("attachments_block",false);
			} else { 
				$t->set_var("attachments_block","");
			}

			$message_text = $db->f("message_text");
			$message_text = process_message($message_text);
			$t->set_var("message_text", $message_text);

			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
	}

	// parse initial request on the last page
	if ($page_number == ceil($total_records / $records_per_page)) {
		$t->set_var("edit_link", "");
		$t->set_var("internal_block", "");
		$t->set_var("assign_to_block", "");
		$t->set_var("style_am","usual_message");
		$t->set_var("mail_data", "");
		$t->set_var("mail_headers", "");
		$t->set_var("mail_body_html", "");
		$t->set_var("mail_body_text", "");
		// hide forward fields
		$t->set_var("forward_to_block", "");
		$t->set_var("forward_cc_block", "");
		$t->set_var("forward_bcc_block", "");
		$t->set_var("forward_subject_block", "");
  
		$posted_user_name = strlen($user_name) ? $user_name . " <" . $user_email . ">" : $user_email;
		if ($user_id) {
			$posted_user_class = "site-user";
			$posted_user_type = ($user_type_id && isset($user_types[$user_type_id])) ? $user_types[$user_type_id] : va_message("NOT_AVAILABLE_MSG");
		} else {
			$posted_user_class = "site-guest";
			$posted_user_type = va_message("GUEST_MSG");
		}

		$t->set_var("status", NEW_MSG);
		$t->set_var("posted_user_name", htmlspecialchars($posted_user_name));
		$t->set_var("posted_user_class", htmlspecialchars($posted_user_class));
		$t->set_var("posted_user_type", htmlspecialchars($posted_user_type));
		$t->set_var("date_added", $request_added_string);

		if (is_array($request_viewed)) {
			$t->set_var("date_viewed", $request_viewed_string);
		} else {
			$t->set_var("date_viewed", SUPPORT_NOT_VIEWED_MSG);
		}
		$t->set_var("viewed_by", va_message("ADMIN_MSG"));
		$t->set_var("viewed_user_name", va_message("ADMIN_MSG"));
		$t->set_var("viewed_user_class", htmlspecialchars("site-admin"));
		$t->sparse("viewed_by_block", false);


		if ($initial_mail_headers || $initial_mail_body_html || $initial_mail_body_text) {
			if ($initial_mail_headers) {
				$t->set_var("admin_support_mail_data_url", "admin_support_mail_data.php?type=header&support_id=". $support_id);
				$t->parse("mail_headers", false);
			}
			if ($initial_mail_body_html) {
				$t->set_var("admin_support_mail_data_url", "admin_support_mail_data.php?type=html&support_id=". $support_id);
				$t->parse("mail_body_html", false);
			}
			if ($initial_mail_body_text) {
				$t->set_var("admin_support_mail_data_url", "admin_support_mail_data.php?type=text&support_id=". $support_id);
				$t->parse("mail_body_text", false);
			}
			$t->parse("mail_data", false);
		}

		// check for attachments
		$sql  = " SELECT * FROM " . $table_prefix . "support_attachments ";
		$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
		$sql .= " AND message_id=0 AND attachment_status=1 ";
		$db->query($sql);
		if ($db->next_record()) {
			$attach_no = 1;
			$attachments_files = ""; 
			do {
				$attachment_id = $db->Record["attachment_id"];
				$file_name     = $db->Record["file_name"];
				$file_path     = $db->Record["file_path"];
				if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
					$file_path = "../".$file_path;
				}
				$size	         = get_nice_bytes(filesize($file_path));
				$attachments_files  .= $attach_no . ". <a target=\"_blank\" href=\"admin_support_attachment.php?atid=" . $attachment_id . "\">" . $file_name . "</a> (" . $size . ")&nbsp;&nbsp;";
				$attach_no++;
			} while ($db->next_record());
			$t->set_var("attachments_files", $attachments_files);
			$t->parse("attachments_block",false);
		} else { 
			$t->set_var("attachments_block","");
		}
  
		$description = process_message($description);

		$t->set_var("message_text", $description);
  
		$t->parse("initial_block", false);
		$t->parse("records", true);
	}


	if (!strlen($operation)) // (set default message text for reply)
	{
		$last_message = ">" . str_replace("\n", "\n>", $last_message);
		// add department signature
		if ($dep_signature) {
			$last_message .= $eol . $eol . $dep_signature;
		}
		$r->set_value("message_text", $last_message);
	}

	// check attachments
	$attachments_files = "";
	$sql  = " SELECT attachment_id, file_name, file_path FROM " . $table_prefix . "support_attachments ";
	$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
	$sql .= " AND admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
	$sql .= " AND message_id=0 ";
	$sql .= " AND attachment_status=0 ";
	$db->query($sql);
	while ($db->next_record()) {
		$attachment_id = $db->f("attachment_id");
		$filename = $db->f("file_name");
		$file_path = $db->f("file_path");
		if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
			$file_path = "../".$file_path;
		}
		$filesize = filesize($file_path);
		if ($attachments_files) { $attachments_files .= "; "; }
		$attachments_files .= "<a href=\"admin_support_attachment.php?atid=" .$attachment_id. "\" target=\"_blank\">" . $filename . "</a> (" . get_nice_bytes($filesize) . ")";
	}
	if ($attachments_files) {
		$t->set_var("attached_files", $attachments_files);
		$t->set_var("attachments_class", "display: block;");
	} else {
		$t->set_var("attachments_class", "display: none;");
	}
	$r->set_parameters();

	$tabs = array(
		"reply" => array("title" => va_message("REPLY_TO_CUSTOMER_MSG")), 
		"assign" => array("title" => va_message("ASSIGN_TICKET_MSG")), 
		"return" => array("title" => va_message("REPLY_TO_NAME_MSG"), "show" => false), 
		"other" => array("title" => va_message("OTHER_ACTION_MSG"), "show" => $other_statuses_no), 
		"forward" => array("title" => va_message("FORWARD_TICKET_MSG")), 
	);
	if (!$tab || !isset($tabs[$tab])) { $tab = "reply"; }
	if (!$operation) { $operation = $tab; }

	// check last message
	$sql  = " SELECT sm.admin_id_assign_by,sm.admin_id_assign_to,a.admin_name ";
	$sql .= " FROM (" . $table_prefix . "support_messages sm ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id=sm.admin_id_assign_by) ";
	$sql .= " WHERE sm.support_id=" . $db->tosql($support_id, INTEGER);
	$sql .= " ORDER BY sm.date_added DESC ";
	$db->RecordsPerPage = 1;
	$db->PageNumber = 1;
	$db->query($sql);
	if ($db->next_record()) {
		$admin_id_assign_by = $db->f("admin_id_assign_by");
		$admin_id_assign_to = $db->f("admin_id_assign_to");
		$return_to_admin = $db->f("admin_name");
		if ($admin_id_assign_by != $session_admin_id && $admin_id_assign_to == $session_admin_id) {
			$reply_to_name = str_replace("{name}", $admin_assign_by, va_message("REPLY_TO_NAME_MSG"));
			$tabs["return"]["title"] = $reply_to_name;
			$tabs["return"]["show"] = true;
			$t->set_var("return_to_admin", $return_to_admin);
			$t->set_var("admin_id_return_to", $admin_id_assign_by);
		}
	}

	$tab = parse_tabs($tabs, $tab);

	$t->set_var("operation", $operation);
	$t->set_var("button_name", $tabs[$operation]["title"]);
	$t->set_var("page", $page_number);
	$t->set_var("rp", urlencode($admin_support_reply_url));
	if ($allow_reply) {
		$t->parse("reply_button", false);
		$t->parse("assign_button", false);
		$t->parse("return_button", false);
		$t->parse("other_button", false);
		$t->parse("forward_button", false);
	}
	
	$t->pparse("main");

