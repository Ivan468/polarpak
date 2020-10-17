<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_privileges_edit.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("admins_groups");

	// initialize all permissions here
	$perms = array(
		SYSTEM_MSG => array(
			"site_settings" => SITE_SETTINGS_MSG,
			"forum" => ADMIN_FORUM_TITLE,
			"newsletter" => NEWSLETTER_MSG,
			"ads" => CLASSIFIED_ADS_MSG,
			"black_ips" => BLACK_IPS_MSG,
			"banned_contents" => BANNED_CONTENT,
			"manual" => MANUAL_MSG,
			"visits_report" => TRACKING_VISITS_REPORT_MSG,
			"import_export" => IMPORT_EXPORT_MSG,
			"db_management" => DATABASE_MANAGEMENT_MSG,
			"system_upgrade" => SYSTEM_UPGRADE_MSG,
		),
		CMS_PERMISSIONS_MSG => array(
			"cms_settings" => CMS_SETTINGS_MSG,
			"site_navigation" => SITE_NAVIGATION_MSG,
			"web_pages" => WEB_PAGES_MSG,
			"custom_blocks" => CUSTOM_BLOCKS_MSG,
			"static_messages" => SYSTEM_STATIC_MESSAGES_MSG,
			"filemanager" => FILE_MANAGER_MSG,
			"banners" => BANNERS_MANAGEMENT_MSG,
			"polls" => OPINION_POLLS_MSG,
			"filters" => FILTERS_MSG,
			"custom_friendly_urls" => CUSTOM_FRIENDLY_URLS_MSG,
			"sliders"=> SLIDERS_MSG,
		),
		ARTICLES_PERMISSIONS_MSG => array(
			"articles" => ARTICLES_TITLE,
			"articles_statuses" => ARTICLES_STATUSES_MSG,
			"articles_reviews" => ARTICLES_REVIEWS_MSG,
			"articles_reviews_settings" => REVIEWS_SETTINGS_MSG,
		),
	);

	$va_version_code = va_version_code();

	$permissions = get_permissions();
	$privilege_id = get_param("privilege_id");
	$admins_hidden_permission = get_setting_value($permissions, "admins_hidden", 0);

	if ($privilege_id && !$admins_hidden_permission) {
		$sql  = " SELECT is_hidden FROM " . $table_prefix . "admin_privileges ";
		$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$is_hidden = $db->f("is_hidden");
			if ($is_hidden) {
				header("Location: admin_privileges.php");
				exit;
			}
		} else {
			header("Location: admin_privileges.php");
			exit;
		}
	}

	// check available permissions
	$orders_permissions = false;
	$helpdesk_permissions = false;
	if ($va_version_code & 1) {
		$orders_permissions = true;
	}
	if ($va_version_code & 4) {
		$helpdesk_permissions = true;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_privileges_edit.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_privileges_href", "admin_privileges.php");
	$t->set_var("admin_privileges_edit_href", "admin_privileges_edit.php");
	$t->set_var("admin_user_types_select_href", "admin_user_types_select.php");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", TYPE_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "admin_privileges");

	$r->add_where("privilege_id", INTEGER);
	$r->change_property("privilege_id", USE_IN_INSERT, true);
	if ($admins_hidden_permission) {
		$r->add_checkbox("is_hidden", INTEGER);
	}
	$r->add_textbox("privilege_name", TEXT, PRIVILEGE_TYPE_MSG);
	$r->change_property("privilege_name", REQUIRED, true);
	if ($helpdesk_permissions) {
		$r->add_checkbox("support_privilege", INTEGER);
	}

	// user fields
	$r->add_checkbox("non_logged_users", INTEGER);
	$r->change_property("non_logged_users", DEFAULT_VALUE, 1);
	$r->add_checkbox("user_types_all", INTEGER);
	$r->change_property("user_types_all", DEFAULT_VALUE, 1);
	$r->add_textbox("user_types_ids", TEXT);

	$rps = new VA_Record($table_prefix . "admin_privileges_settings", "aps");
	// system settings
	$rps->add_checkbox("site_settings", INTEGER);
	$rps->add_checkbox("forum", INTEGER);
	$rps->add_checkbox("manual", INTEGER);
	$rps->add_checkbox("newsletter", INTEGER);
	$rps->add_checkbox("black_ips", INTEGER);
	$rps->add_checkbox("banned_contents", INTEGER);
	$rps->add_checkbox("import_export", INTEGER);
	$rps->add_checkbox("ads", INTEGER);
	$rps->add_checkbox("visits_report", INTEGER);
	$rps->add_checkbox("db_management", INTEGER);
	$rps->add_checkbox("system_upgrade", INTEGER);

	// cms settings
	$rps->add_checkbox("cms_settings", INTEGER);
	$rps->add_checkbox("site_navigation", INTEGER);
	$rps->add_checkbox("footer_links", INTEGER);
	$rps->add_checkbox("static_messages", INTEGER);
	$rps->add_checkbox("filemanager", INTEGER);
	$rps->add_checkbox("web_pages", INTEGER);
	$rps->add_checkbox("custom_blocks", INTEGER);
	$rps->add_checkbox("custom_friendly_urls", INTEGER);
	$rps->add_checkbox("filters", INTEGER);
	$rps->add_checkbox("banners", INTEGER);
	$rps->add_checkbox("polls", INTEGER);
	$rps->add_checkbox("sliders", INTEGER);
	
	// products permissions
	$rps->add_checkbox("products_categories", INTEGER);
	$rps->add_checkbox("products_settings", INTEGER);
	$rps->add_checkbox("product_types", INTEGER);
	$rps->add_checkbox("manufacturers", INTEGER);
	$rps->add_checkbox("suppliers", INTEGER);
	$rps->add_checkbox("shipping_methods", INTEGER);
	$rps->add_checkbox("shipping_times", INTEGER);
	$rps->add_checkbox("shipping_rules", INTEGER);
	$rps->add_checkbox("downloadable_products", INTEGER);
	$rps->add_checkbox("coupons", INTEGER);
	$rps->add_checkbox("saved_types", INTEGER);
	$rps->add_checkbox("advanced_search", INTEGER);
	$rps->add_checkbox("products_report", INTEGER);
	$rps->add_checkbox("product_prices", INTEGER);
	$rps->add_checkbox("product_images", INTEGER);
	$rps->add_checkbox("product_properties", INTEGER);
	$rps->add_checkbox("product_features", INTEGER);
	$rps->add_checkbox("product_related", INTEGER);
	$rps->add_checkbox("product_categories", INTEGER);
	$rps->add_checkbox("product_accessories", INTEGER);
	$rps->add_checkbox("product_releases", INTEGER);
	$rps->add_checkbox("products_order", INTEGER);
	$rps->add_checkbox("products_export", INTEGER);
	$rps->add_checkbox("products_import", INTEGER);
	$rps->add_checkbox("products_export_google_base", INTEGER);
	$rps->add_checkbox("features_groups", INTEGER);
	$rps->add_checkbox("tell_friend", INTEGER);
	$rps->add_checkbox("categories_export", INTEGER);
	$rps->add_checkbox("categories_import", INTEGER);
	$rps->add_checkbox("categories_order", INTEGER);
	$rps->add_checkbox("view_categories", INTEGER);
	$rps->add_checkbox("view_products", INTEGER);
	$rps->add_checkbox("add_categories", INTEGER);
	$rps->add_checkbox("update_categories", INTEGER);
	$rps->add_checkbox("remove_categories", INTEGER);
	$rps->add_checkbox("add_products", INTEGER);
	$rps->add_checkbox("update_products", INTEGER);
	$rps->add_checkbox("remove_products", INTEGER);
	$rps->add_checkbox("duplicate_products", INTEGER);
	$rps->add_checkbox("approve_products", INTEGER);
	$rps->add_checkbox("products_reviews", INTEGER);
	$rps->add_checkbox("products_reviews_settings", INTEGER);

	// articles
	$rps->add_checkbox("articles", INTEGER);
	$rps->add_checkbox("articles_statuses", INTEGER);
	$rps->add_checkbox("articles_reviews", INTEGER);
	$rps->add_checkbox("articles_reviews_settings", INTEGER);
	$rps->add_checkbox("articles_lost", INTEGER);

	// sales orders settings
	$rps->add_checkbox("sales_orders", INTEGER);
	$rps->add_checkbox("order_payment", INTEGER);
	$rps->add_checkbox("remove_orders", INTEGER);
	$rps->add_checkbox("update_orders", INTEGER);
	$rps->add_checkbox("create_orders", INTEGER);
	$rps->add_checkbox("add_order_products", INTEGER);
	$rps->add_checkbox("update_order_products", INTEGER);
	$rps->add_checkbox("remove_order_products", INTEGER);
	$rps->add_checkbox("orders_stats", INTEGER);
	$rps->add_checkbox("order_statuses", INTEGER);
	$rps->add_checkbox("order_notes", INTEGER);
	$rps->add_checkbox("order_links", INTEGER);
	$rps->add_checkbox("order_serials", INTEGER);
	$rps->add_checkbox("order_vouchers", INTEGER);
	$rps->add_checkbox("order_profile", INTEGER);
	$rps->add_checkbox("order_confirmation", INTEGER);
	$rps->add_checkbox("orders_import", INTEGER);
	$rps->add_checkbox("payment_systems", INTEGER);
	$rps->add_checkbox("tax_rates", INTEGER);
	$rps->add_checkbox("orders_recover", INTEGER);

	// helpdesk settings
	$rps->add_checkbox("support", INTEGER);
	$rps->add_checkbox("support_settings", INTEGER);
	$rps->add_checkbox("support_ticket_new", INTEGER);
	$rps->add_checkbox("support_ticket_edit", INTEGER);
	$rps->add_checkbox("support_ticket_close", INTEGER);
	$rps->add_checkbox("support_ticket_reply", INTEGER);
	$rps->add_checkbox("support_users", INTEGER);
	$rps->add_checkbox("support_departments", INTEGER);
	$rps->add_checkbox("support_predefined_reply", INTEGER);
	$rps->add_checkbox("support_static_data", INTEGER);
	$rps->add_checkbox("support_users_stats", INTEGER);
	$rps->add_checkbox("support_users_priorities", INTEGER);

	// customers permissions
	$rps->add_checkbox("site_users", INTEGER);
	$rps->add_checkbox("users_groups", INTEGER);
	$rps->add_checkbox("users_login", INTEGER);
	$rps->add_checkbox("add_users", INTEGER);
	$rps->add_checkbox("update_users", INTEGER);
	$rps->add_checkbox("remove_users", INTEGER);
	$rps->add_checkbox("import_users", INTEGER);
	$rps->add_checkbox("export_users", INTEGER);
	$rps->add_checkbox("users_forgot", INTEGER);
	$rps->add_checkbox("users_payments", INTEGER);
	$rps->add_checkbox("add_payments", INTEGER);
	$rps->add_checkbox("update_payments", INTEGER);
	$rps->add_checkbox("remove_payments", INTEGER);
	$rps->add_checkbox("export_payments", INTEGER);
	$rps->add_checkbox("subscriptions", INTEGER);
	$rps->add_checkbox("subscriptions_groups", INTEGER);

	// administrators permissions
	$rps->add_checkbox("admin_users", INTEGER);
	$rps->add_checkbox("admins_groups", INTEGER);
	$rps->add_checkbox("admins_login", INTEGER);
	$rps->add_checkbox("add_admins", INTEGER);
	$rps->add_checkbox("update_admins", INTEGER);
	$rps->add_checkbox("remove_admins", INTEGER);
	if ($admins_hidden_permission) {
		$rps->add_checkbox("admins_hidden", INTEGER);
	}
	
	// static tables
	$rps->add_checkbox("static_tables", INTEGER);	
	$rps->add_checkbox("static_google_base_types", INTEGER);
	$rps->add_checkbox("static_google_base_attributes", INTEGER);
	$rps->add_checkbox("static_prices", INTEGER);
	
	// multisites permissions
	$rps->add_checkbox("admin_sites", INTEGER);
	$rps->add_checkbox("add_sites", INTEGER);
	$rps->add_checkbox("update_sites", INTEGER);
	$rps->add_checkbox("remove_sites", INTEGER);

	// xml permissions
	$rps->add_checkbox("get_orders_statuses", INTEGER);
	$rps->add_checkbox("get_orders_ids", INTEGER);
	$rps->add_checkbox("update_order_status", INTEGER);
	$rps->add_checkbox("add_duplicate_coupons", INTEGER);
	
	// product registration permissions
	$rps->add_checkbox("admin_registration", INTEGER);
	$rps->add_checkbox("edit_reg_list", INTEGER);
	$rps->add_checkbox("edit_reg_categories", INTEGER);
	$rps->add_checkbox("edit_reg_products", INTEGER);

	
	
	
	$r->get_form_values();
	$rps->get_form_values();

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	$privilege_id = get_param("privilege_id");
	$return_page = get_param("rp");
	if (!strlen($return_page)) { $return_page = "admin_privileges.php"; }

	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $privilege_id)
		{
			$r->delete_record();
			$db->query("DELETE FROM " . $table_prefix . "admin_privileges_settings WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER));
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();

		if ($is_valid)
		{
			if (strlen($privilege_id)) {
				$r->update_record();
				$sql = "DELETE FROM " . $table_prefix . "admin_privileges_settings WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER);
				if (!$admins_hidden_permission) {
					$sql .= " AND block_name<>'admins_hidden'";
				}
				$db->query($sql);
			} else {
				$sql = " SELECT MAX(privilege_id) FROM " . $table_prefix . "admin_privileges ";
				$privilege_id = get_db_value($sql) + 1;
				$r->set_value("privilege_id", $privilege_id);
				$r->insert_record();
			}

			foreach ($rps->parameters as $key => $value) {
				$sql  = " INSERT INTO " . $table_prefix . "admin_privileges_settings (privilege_id, block_name, permission) VALUES (";
				$sql .= $db->tosql($privilege_id, INTEGER) . ", '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], INTEGER) . ")";
				$db->query($sql);
			}

			header("Location: " . $return_page);
			exit;
		}

	}
	elseif (strlen($privilege_id))
	{
		$r->get_db_values();

		$sql  = " SELECT block_name,permission FROM " . $table_prefix . "admin_privileges_settings ";
		$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$block_name = $db->f("block_name");
			if (isset($rps->parameters[$block_name])) {
				$rps->set_value($block_name, $db->f("permission"));
			}
		}
	} else {
		$r->set_value("user_types_all", 1);
		$r->set_value("non_logged_users", 1);
	}

	$r->set_form_parameters();
	$rps->set_form_parameters();
	if ($orders_permissions) {
		$t->parse("orders_settings", false);
	}
	if ($helpdesk_permissions) {
		$t->parse("helpdesk_settings", false);
	}

	if (strlen($privilege_id)) {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);
	} else {
		$t->set_var("save_button", ADD_BUTTON);
		$t->set_var("delete", "");
	}


	$user_types_ids = $r->get_value("user_types_ids");
	if ($user_types_ids) {
		$sql  = " SELECT ut.type_id, ut.type_name ";
		$sql .= " FROM " . $table_prefix . "user_types ut ";
		$sql .= " WHERE ut.type_id IN (" . $db->tosql($user_types_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY ut.type_name ";
		$db->query($sql);
		while($db->next_record())
		{
			$row_type_id = $db->f("type_id");
			$type_name = get_translation($db->f("type_name"));
	
			$t->set_var("user_type_id", $row_type_id);
			$t->set_var("user_type_name", $type_name);
			$t->set_var("user_type_name_js", str_replace("\"", "&quot;", $type_name));
	
			$t->parse("selected_user_types", true);
			$t->parse("selected_user_types_js", true);
		}
	}

	$tabs = array(
		"general" => array("title" => EDIT_PERMISSIONS_MSG), 
		"users" => array("title" => USERS_TYPES_MSG), 
	);
	parse_admin_tabs($tabs, $tab, 7);


	$t->set_var("rp", htmlspecialchars($return_page));

	$t->pparse("main");

?>