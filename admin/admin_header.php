<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_header.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	// set javascript first
	set_script_tag("../js/ajax.js", true, "admin_header");
	set_script_tag("../js/admin.js", true, "admin_header");
	set_script_tag("../js/chat.js", true, "admin_header");
	set_link_tag("styles/admin.css", "stylesheet", "text/css");

	$language_code = get_language("messages.php");
	include_once("../messages/".$language_code."/cart_messages.php");
	include_once("../messages/".$language_code."/support_messages.php");


	if (!isset($va_type) || !$va_type) {
		$va_type = defined("VA_TYPE") ? strtolower(VA_TYPE) : "standard";
	}
	if (!isset($va_name) || !$va_name) {
		$va_name = defined("VA_PRODUCT") ? strtolower(VA_PRODUCT) : "shop";
	}
	$va_version_code = va_version_code();
	$permissions = get_permissions();

	// check permission
	$orders_permission = get_setting_value($permissions, "sales_orders", 0);
	$tickets_permission = get_setting_value($permissions, "support", 0);
	$site_users_permission = get_setting_value($permissions, "site_users", 0);

	// Admin Site URL settings
	$admin_folder     = get_admin_dir();
	$site_url         = get_setting_value($settings, "site_url", "");
	$secure_url       = get_setting_value($settings, "secure_url", "");
	$admin_site_url   = $site_url . $admin_folder;
	$admin_secure_url = $secure_url . $admin_folder;
	if ($is_ssl) {
		$absolute_url = $secure_url;
	} else {
		$absolute_url = $site_url;
	}

	// SSL settings
	$ssl_admin_tickets  = get_setting_value($settings, "ssl_admin_tickets", 0);
	$ssl_admin_ticket   = get_setting_value($settings, "ssl_admin_ticket", 0);
	$ssl_admin_helpdesk = get_setting_value($settings, "ssl_admin_helpdesk", 0);
	if ($ssl_admin_tickets && strlen($secure_url)) {
		$tickets_site_url = $admin_secure_url;
	} else {
		$tickets_site_url = $admin_site_url;
	}
	if ($ssl_admin_ticket && strlen($secure_url)) {
		$ticket_site_url = $admin_secure_url;
	} else {
		$ticket_site_url = $admin_site_url;
	}
	if ($ssl_admin_helpdesk && strlen($secure_url)) {
		$helpdesk_site_url = $admin_secure_url;
	} else {
		$helpdesk_site_url = $admin_site_url;
	}

	// orders SSL settings
	$ssl_admin_orders_list = get_setting_value($settings, "ssl_admin_orders_list", 0);
	$ssl_admin_order_details = get_setting_value($settings, "ssl_admin_order_details", 0);
	$ssl_admin_orders_pages = get_setting_value($settings, "ssl_admin_orders_pages", 0);
	$secure_admin_order_create = get_setting_value($settings, "secure_admin_order_create", 0);
	if ($ssl_admin_orders_list && strlen($secure_url)) {
		$orders_list_site_url = $admin_secure_url;
	} else {
		$orders_list_site_url = $admin_site_url;
	}
	if ($ssl_admin_order_details && strlen($secure_url)) {
		$order_details_site_url = $admin_secure_url;
	} else {
		$order_details_site_url = $admin_site_url;
	}
	if ($ssl_admin_orders_pages && strlen($secure_url)) {
		$orders_pages_site_url = $admin_secure_url;
	} else {
		$orders_pages_site_url = $admin_site_url;
	}

	$t->set_file("block_header", "admin_header.html");

	$t->set_var("CHARSET", va_message("CHARSET"));
	$t->set_var("site_url", htmlspecialchars($site_url));
	$t->set_var("secure_url", htmlspecialchars($secure_url));
	$t->set_var("absolute_url", htmlspecialchars($absolute_url));

	$t->set_var("index_href", $site_url . "index.php");	
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_global_search_href", "admin_global_search.php");
	
	// New block
	$current_date_header = va_time();
	$cyear_header = $current_date_header[YEAR]; 
	$cmonth_header = $current_date_header[MONTH]; 
	$cday_header = $current_date_header[DAY];
	$today_header = mktime (0, 0, 0, $cmonth_header, $cday_header, $cyear_header);
	$tomorrow_header = mktime (0, 0, 0, $cmonth_header, $cday_header + 1, $cyear_header);
	
	$t->set_var("today_header",va_date($date_edit_format, $today_header));
	
	$new_date_header = date("Y-m-d");
	if ($orders_permission && ($va_version_code & 1)) {
		$sql = " SELECT count(o.order_id) ";
		$sql.= " FROM ".$table_prefix."orders o ";
		//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
		$sql.= " WHERE o.order_placed_date > ".$db->tosql($today_header,DATE);
		$sql.= " AND o.order_placed_date < ".$db->tosql($tomorrow_header,DATE);
		$t->set_var("new_orders",get_db_value($sql));
		$t->sparse("new_orders_block", false);
	}

	if ($site_users_permission) {
		$sql = " SELECT count(user_id) ";
		$sql.= " FROM ".$table_prefix."users ";
		$sql.= " WHERE registration_date > ".$db->tosql($today_header,DATE);
		$sql.= " AND registration_date < ".$db->tosql($tomorrow_header,DATE);
		$t->set_var("new_users",get_db_value($sql));
		$t->sparse("new_users_block", false);
	}
	

	if ($tickets_permission && ($va_version_code & 4)) {
		if (comp_vers(va_version(), "4.1.11") == 1) {
			// check chats waiting number
			$current_ts = va_timestamp();
			$active_chat_ts = $current_ts - 120; // check only chats where users online
			$sql = " SELECT count(*) ";
			$sql.= " FROM ".$table_prefix."chats ";
			$sql.= " WHERE chat_status=1 ";
			$sql.= " AND user_online >= ".$db->tosql($active_chat_ts, DATETIME);
			$chats_number = get_db_value($sql);
			if (preg_match("/\{number\}/", va_message("CHATS_WAITING_MSG"))) {
				$chats_message = str_replace("{number}", intval($chats_number), va_message("CHATS_WAITING_MSG"));
			} else {
				$chats_message = "<b>" .intval($chats_number) . "</b> " . va_message("CHATS_WAITING_MSG");
			}
			$t->set_var("chats_message", $chats_message);
		}

		// check tickets
		$sql = " SELECT count(support_id) ";
		$sql.= " FROM ".$table_prefix."support ";
		$sql.= " WHERE date_added > ".$db->tosql($today_header,DATE);
		$sql.= " AND date_added < ".$db->tosql($tomorrow_header,DATE);
		$t->set_var("new_tickets",get_db_value($sql));
		$t->sparse("new_tickets_block", false);
	}

	if(($orders_permission && ($va_version_code & 1)) || ($tickets_permission && ($va_version_code & 4)) || $site_users_permission) {
		$t->sparse("today_block", false);
	}


	
	$version_number = va_version();
	$version_name   = ucfirst($va_name);
	$version_type   = ucfirst($va_type);
	$t->set_var("version_number", $version_number);
	$t->set_var("version_name",   $version_name);
	$t->set_var("version_build",  VA_BUILD);
	if ($version_type != $version_name) {
		$t->set_var("version_type", $version_type);
	}

	// show languages
	$request_uri = get_request_uri();
	$language_uri = $request_uri;
	$language_uri = preg_replace("/\&?lang=[^\&]{2,}/i", "", $language_uri);
	$language_uri .= (strpos($language_uri, "?")) ? "&" : "?";
	$language_uri .= "lang=";
	$sql  = " SELECT * FROM " . $table_prefix . "languages ";
	$sql .= " WHERE show_for_user=1 ";
	$sql .= " ORDER BY language_order, language_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$row_language_code = $db->f("language_code");
		$language_name = $db->f("language_name");
		$language_image = $db->f("language_image");
		$language_image_active = $db->f("language_image_active");
		$language_url = $language_uri.urlencode($row_language_code);
		if (strtolower($row_language_code) == strtolower($language_code)) {
			$t->set_var("active_language", htmlspecialchars($language_name));
			if (!$language_image_active) { $language_image_active = $language_image; }
			if ($language_image_active) {
				if (!preg_match("/^http/", $language_image_active)) { $language_image_active = "../" . $language_image_active; }
				$t->set_var("src", htmlspecialchars($language_image_active));
				$t->parse("active_image", false);
			}
		} else {
			$t->set_var("language_name", htmlspecialchars($language_name));
			$t->set_var("language_url", htmlspecialchars($language_url));

			if ($language_image) {
				if (!preg_match("/^http/", $language_image)) { $language_image = "../" . $language_image; }
				$t->set_var("src", htmlspecialchars($language_image));
				$t->parse("language_image", false);
			} else {
				$t->set_var("language_image", "");
			}

			$t->parse("header_languages", true);
		}
	}

	include_once ("./admin_block_menu.php");
	include_once ("./admin_block_breadcrumb.php");

	// additional blocks	
	include_once ("./blocks/admin_leftside_menu.php");
	admin_leftside_breadcrumbs_block('block_leftside_breadcrumbs');
	
	include_once ("./blocks/admin_bookmarks.php");
	admin_bookmarks_block('block_bookmarks');

	$t->parse_to("block_header", "admin_header", true);

