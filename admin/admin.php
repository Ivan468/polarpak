<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin.php                                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/chart_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/install_messages.php");
	include_once("./admin_common.php");

	if (!strlen(get_session("session_admin_id")) || !strlen(get_session("session_admin_privilege_id"))) {
		// admin is not logged in, redirect him to login form
		header ("Location: admin_login.php");
		exit;
	}

	check_admin_security();
	$permissions = get_permissions();
	$menu_active_code = "dashboard";
	include_once($root_folder_path . "includes/db_upgrade.php");


	$products_permission = get_setting_value($permissions, "products_categories", 0);
	$articles_permission = get_setting_value($permissions, "articles", 0);
	$orders_permission = get_setting_value($permissions, "sales_orders", 0);
	$tickets_permission = get_setting_value($permissions, "support", 0);
	$site_users_permission = get_setting_value($permissions, "site_users", 0);
	$visits_report_permission = get_setting_value($permissions, "visits_report", 0);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin.html");

	$t->set_var("admin_href", $admin_site_url . "admin.php");
	$t->set_var("admin_href_encode", urlencode($admin_site_url."admin.php"));
	
	// System settings
	$t->set_var("admin_global_settings_href","admin_global_settings.php");
	$t->set_var("admin_admins_href",         $admin_site_url . "admin_admins.php");
	$t->set_var("admin_privileges_href",     $admin_site_url . "admin_privileges.php");
	$t->set_var("admin_black_ips_href",      $admin_site_url . "admin_black_ips.php");
	$t->set_var("admin_banned_contents_href",$admin_site_url . "admin_banned_contents.php");
	$t->set_var("admin_dump_href",           $admin_site_url . "admin_dump.php");
	$t->set_var("admin_upgrade_href",        "admin_upgrade.php");
	$t->set_var("admin_visits_report_href",  $admin_site_url . "admin_visits_report.php");
	$t->set_var("admin_filemanager_href",    $admin_site_url . "admin_fm.php");
	$t->set_var("admin_lookup_tables_href",  $admin_site_url . "admin_lookup_tables.php");
	$t->set_var("admin_static_messages_href",$admin_site_url . "admin_messages.php");

	// Products section
	$t->set_var("admin_items_list_href",       $admin_site_url . "admin_items_list.php");
	$t->set_var("admin_products_settings_href",$admin_site_url . "admin_products_settings.php");
	$t->set_var("admin_shipping_modules_href", $admin_site_url . "admin_shipping_modules.php");
	$t->set_var("admin_shipping_times_href",   $admin_site_url . "admin_shipping_times.php");
	$t->set_var("admin_shipping_rules_href",   $admin_site_url . "admin_shipping_rules.php");
	$t->set_var("admin_search_href",           $admin_site_url . "admin_search.php");
	$t->set_var("admin_download_info_href",    $admin_site_url . "admin_download_info.php");
	$t->set_var("admin_products_notify_href",  $admin_site_url . "admin_products_notify.php");
	$t->set_var("admin_products_report_href",  $admin_site_url . "admin_products_report.php");
	$t->set_var("admin_coupons_href",          $admin_site_url . "admin_coupons.php");
	$t->set_var("admin_products_edit_href",    $admin_site_url . "admin_products_edit.php");
	$t->set_var("admin_tell_friend_href",      $admin_site_url . "admin_tell_friend.php");
	$t->set_var("admin_item_types_href",       $admin_site_url . "admin_item_types.php");
	$t->set_var("admin_manufacturers_href",    $admin_site_url . "admin_manufacturers.php");
	$t->set_var("admin_suppliers_href",        $admin_site_url . "admin_suppliers.php");
	$t->set_var("admin_features_groups_href",  $admin_site_url . "admin_features_groups.php");
	$t->set_var("admin_saved_types_href",      $admin_site_url . "admin_saved_types.php");

	// Orders management
	$t->set_var("admin_orders_href",             $orders_list_site_url . "admin_orders.php");
	$t->set_var("admin_coupons_href",            $orders_pages_site_url . "admin_coupons.php");
	$t->set_var("admin_order_info_href",         $orders_pages_site_url . "admin_order_info.php");
	$t->set_var("admin_order_statuses_href",     $orders_pages_site_url . "admin_order_statuses.php");
	$t->set_var("admin_tax_rates_href",          $orders_pages_site_url . "admin_tax_rates.php");
	$t->set_var("admin_orders_report_href",      $orders_pages_site_url . "admin_orders_report.php");
	$t->set_var("admin_order_confirmation_href", $orders_pages_site_url . "admin_order_confirmation.php");
	$t->set_var("admin_order_final_href",        $orders_pages_site_url . "admin_order_final.php");
	$t->set_var("admin_payment_systems_href",    $orders_pages_site_url . "admin_payment_systems.php");
	$t->set_var("admin_currencies_href",         $orders_pages_site_url . "admin_currencies.php");
	$t->set_var("admin_orders_products_report_href", $orders_pages_site_url . "admin_orders_products_report.php");
	$t->set_var("admin_orders_tax_report_href",      $orders_pages_site_url . "admin_orders_tax_report.php");
	$t->set_var("admin_order_printable_href", 	     $orders_pages_site_url . "admin_order_printable.php");

	// helpdesk links
	$t->set_var("admin_support_href",               $tickets_site_url . "admin_support.php");
	$t->set_var("admin_support_settings_href",			$helpdesk_site_url . "admin_support_settings.php");
	$t->set_var("admin_support_ranks_href",					$helpdesk_site_url . "admin_support_ranks.php");
	$t->set_var("admin_support_priorities_href",		$helpdesk_site_url . "admin_support_priorities.php");
	$t->set_var("admin_support_statuses_href",			$helpdesk_site_url . "admin_support_statuses.php");
	$t->set_var("admin_support_products_href",			$helpdesk_site_url . "admin_support_products.php");
	$t->set_var("admin_support_types_href",					$helpdesk_site_url . "admin_support_types.php");
	$t->set_var("admin_support_settings_href",     	$helpdesk_site_url . "admin_support_settings.php");
	$t->set_var("admin_support_departments_href",  	$helpdesk_site_url . "admin_support_departments.php");
	$t->set_var("admin_support_prereplies_href",   	$helpdesk_site_url . "admin_support_prereplies.php");
	$t->set_var("admin_support_pretypes_href",   	  $helpdesk_site_url . "admin_support_pretypes.php");
	$t->set_var("admin_support_admins_href",       	$helpdesk_site_url . "admin_support_admins.php");
	$t->set_var("admin_support_static_tables_href",	$helpdesk_site_url . "admin_support_static_tables.php");
	$t->set_var("admin_support_users_report_href",   $helpdesk_site_url . "admin_support_users_report.php");
	$t->set_var("admin_support_dep_edit_href",       $helpdesk_site_url . "admin_support_dep_edit.php");
	$t->set_var("admin_support_admin_edit_href",     $helpdesk_site_url . "admin_support_admin_edit.php");

	// forum
	$t->set_var("admin_forum_href",           $admin_site_url . "admin_forum.php");
	$t->set_var("admin_forum_settings_href",  $admin_site_url . "admin_forum_settings.php");
	$t->set_var("admin_forum_priorities_href",$admin_site_url . "admin_forum_priorities.php");
	$t->set_var("admin_icons_href",           $admin_site_url . "admin_icons.php");

	// CMS settings
	$t->set_var("admin_cms_href",            $admin_site_url . "admin_cms.php");
	$t->set_var("admin_layouts_href",        $admin_site_url . "admin_layouts.php");
	$t->set_var("admin_layout_page_href",    $admin_site_url . "admin_layout_page.php");
	$t->set_var("admin_header_menus_href",   $admin_site_url . "admin_header_menus.php");
	$t->set_var("admin_footer_links_href",   $admin_site_url . "admin_footer_links.php");
	$t->set_var("admin_custom_menus_href",   $admin_site_url . "admin_custom_menus.php");
	$t->set_var("admin_custom_blocks_href",  $admin_site_url . "admin_custom_blocks.php");
	$t->set_var("admin_pages_href",          $admin_site_url . "admin_pages.php");
	$t->set_var("admin_friendly_urls_href",  $admin_site_url . "admin_friendly_urls.php");
	$t->set_var("admin_layout_header_href",  $admin_site_url . "admin_layout_header.php");
	$t->set_var("admin_polls_href",          $admin_site_url . "admin_polls.php");
	$t->set_var("admin_filters_href",        $admin_site_url . "admin_filters.php");
	$t->set_var("admin_banners_href",        $admin_site_url . "admin_banners.php");

	// Articles & Manuals                     
	$t->set_var("admin_reviews_href",           $admin_site_url . "admin_reviews.php");
	$t->set_var("admin_products_reviews_sets_href",$admin_site_url . "admin_products_reviews_sets.php");
	$t->set_var("admin_manual_href",            $admin_site_url . "admin_manual.php");
	$t->set_var("admin_articles_top_href",      $admin_site_url . "admin_articles_top.php");
	$t->set_var("admin_articles_statuses_href", $admin_site_url . "admin_articles_statuses.php");

	// Site Users
	$t->set_var("admin_users_href",              $admin_site_url . "admin_users.php");
	$t->set_var("admin_user_types_href",         $admin_site_url . "admin_user_types.php");
	$t->set_var("admin_user_sections_href",      $admin_site_url . "admin_user_sections.php");
	$t->set_var("admin_forgotten_password_href", $admin_site_url . "admin_forgotten_password.php");
	$t->set_var("admin_user_payments_href",      $admin_site_url . "admin_user_payments.php");
	$t->set_var("admin_user_commissions_href",   $admin_site_url . "admin_user_commissions.php");
	$t->set_var("admin_newsletters_href",        $admin_site_url . "admin_newsletters.php");
	$t->set_var("admin_newsletter_users_href",   $admin_site_url . "admin_newsletter_users.php");
	$t->set_var("admin_subscriptions_href",      $admin_site_url . "admin_subscriptions.php");
	$t->set_var("admin_subscriptions_groups_href",$admin_site_url ."admin_subscriptions_groups.php");

	// Classified Ads
	$t->set_var("admin_ads_href",                 $admin_site_url . "admin_ads.php");
	$t->set_var("admin_ads_settings_href",        $admin_site_url . "admin_ads_settings.php");
	$t->set_var("admin_ads_notify_href",          $admin_site_url . "admin_ads_notify.php");
	$t->set_var("admin_ads_settings_href",        $admin_site_url . "admin_ads_settings.php");
	$t->set_var("admin_ads_search_href",          $admin_site_url . "admin_ads_search.php");
	$t->set_var("admin_ads_request_href",         $admin_site_url . "admin_ads_request.php");
	$t->set_var("admin_ads_features_groups_href", $admin_site_url . "admin_ads_features_groups.php");
	
	// Registrations
	$t->set_var("admin_registrations_href",         $admin_site_url . "admin_registrations.php");
	$t->set_var("admin_registration_products_href", $admin_site_url . "admin_registration_products.php");
	$t->set_var("admin_registration_settings_href", $admin_site_url . "admin_registration_settings.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$admin_blocks = array();
	if ($va_version_code & 1) {
		if ($products_categories_perm) {
			$admin_blocks[] = "products_categories";
		}
		$admin_blocks[] = "sales_orders";
	}
	$admin_blocks[] = "cms_settings";
	if ($va_version_code & 4) {
		$admin_blocks[] = "support";
	}
	$admin_blocks[] = "site_users";
	if ($va_version_code & 8) {
		$admin_blocks[] = "forum";
	}
	if ($va_version_code & 32) {
		$admin_blocks[] = "manual";
	}
	if ($va_version_code & 2) {
		$admin_blocks[] = "articles";
	}
	if ($va_version_code & 16) {
		$admin_blocks[] = "ads";
	}

	$admin_blocks[] = "site_settings";


	for ($i = 0; $i < sizeof($admin_blocks); $i++) {
		$t->set_var($admin_blocks[$i], "");
	}

	// check if upgrade available
	$current_db_version = va_version();
	if (comp_vers(VA_RELEASE, $current_db_version) == 1) {
		$t->set_var("latest_version", VA_RELEASE);
		$t->parse("upgrade_available", false);
	}
	
	
	// Start time
	$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$tstart = $mtime;
	
	$current_date = va_time();
	$cyear = $current_date[YEAR]; 
	$cmonth = $current_date[MONTH]; 
	$cday = $current_date[DAY];
	$d["today"][0] = mktime (0, 0, 0, $cmonth, $cday, $cyear);
	$d["today"][1] = mktime (23, 59, 59, $cmonth, $cday, $cyear);
	$d["yesterday"][0] = mktime (0, 0, 0, $cmonth, $cday - 1, $cyear);
	$d["yesterday"][1] = mktime (23, 59, 59, $cmonth, $cday - 1, $cyear);
	$d["this_week"][0] = mktime (0, 0, 0, $cmonth, $cday - 6, $cyear);
	$d["this_week"][1] = mktime (23, 59, 59, $cmonth, $cday, $cyear);
	$d["this_month"][0] = mktime (0, 0, 0, $cmonth, 1, $cyear);
	$d["this_month"][1] = mktime (23, 59, 59, $cmonth, $cday, $cyear);
	$d["last_month"][0] = mktime (0, 0, 0, $cmonth - 1, 1, $cyear);
	$d["last_month"][1] = mktime (23, 59, 59, $cmonth, 0, $cyear);
	$d["total_year"][0] = mktime (0, 0, 0, 1, 1, $cyear);
	$d["total_year"][1] = mktime (23, 59, 59, $cmonth, $cday, $cyear);
	
	$t->set_var("year_title",$cyear);
	$lastmonth = va_date("MMMM YYYY", $d["last_month"][0]);
	$t->set_var("last_month_title",$lastmonth);
	
	foreach ($d as $key => $value) {
		$row_data_name = $key;
		$date_start = $value[0];
		$date_finish = $value[1];
	}

	$va_version_code = va_version_code();
	$mas = array(); $mas_title = array();
	if ($va_version_code & 1 && $orders_permission) {
		$mas["sales"] = va_message("SALES_MSG");
		$mas["orders"] = va_message("ORDERS_MSG");
	}
	if ($site_users_permission) {
		$mas["new_users"]  = va_message("NEW_USERS_MSG");
		$mas["active_users"]  = va_message("ACTIVE_USERS_MSG");
	}
	if ($visits_report_permission) {
		//$mas["visits"] = va_message("SITE_VISITS_MSG");
	}
	if ($va_version_code & 4 && $tickets_permission) {
		$mas["tickets"] = va_message("TICKETS_MSG");
	}

	foreach ($mas as $row_code => $row_title) {
		$t->set_var("row_name",$row_title);
		if ($row_code == "orders") {
			foreach ($d as $key => $value) {
				if ($key == "today") {
					$sql = " SELECT count(o.order_id) ";
					$sql.= " FROM (".$table_prefix."orders o ";
					$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.status_id=o.order_status) ";
					$sql.= " WHERE o.order_placed_date >= ".$db->tosql($value[0],DATETIME)." AND o.order_placed_date <= ".$db->tosql($value[1],DATETIME);
					//$sql.= " AND os.paid_status = 1 ";
					$data = get_db_value($sql);
				} else {
					$data = get_cache(24, 1, "admin.php", $row_code, $key);
					if ($data === false) {
						$sql = " SELECT count(o.order_id) ";
						$sql.= " FROM (".$table_prefix."orders o ";
						$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.status_id = o.order_status) ";
						$sql.= " WHERE o.order_placed_date >= ".$db->tosql($value[0],DATETIME)." AND o.order_placed_date <= ".$db->tosql($value[1],DATETIME);
						//$sql.= " AND os.paid_status = 1 ";
						$data2 = get_db_value($sql);
						$data = set_cache($data2,"admin.php",$row_code,$key);
					}
				}
				$data = "<a href=\"admin_orders.php?s_os_list=17&s_sd=".va_date($date_edit_format, $value[0])."&s_ed=".va_date($date_edit_format,$value[1])."\">".$data."</a>";
				$t->set_var($key,$data);
			}
			$t->parse("table_center",true);
		} else if ($row_code == "sales") {
			foreach ($d as $key => $value) {
				if ($key == "today") {
					$sql = " SELECT sum(o.order_total) ";
					$sql.= " FROM ".$table_prefix."orders o ";
					//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
					$sql.= " WHERE o.order_placed_date >= ".$db->tosql($value[0],DATETIME)." AND o.order_placed_date <= ".$db->tosql($value[1],DATETIME);
					$data = get_db_value($sql);
					if (!$data) {$data = 0;}
				} else {
					$data = get_cache(24,1,"admin.php",$row_code,$key);
					if ($data === false) {
						$sql = " SELECT sum(o.order_total) ";
						$sql.= " FROM ".$table_prefix."orders o ";
						//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
						$sql.= " WHERE o.order_placed_date >= ".$db->tosql($value[0],DATETIME)." AND o.order_placed_date <= ".$db->tosql($value[1],DATETIME);
						$sales = get_db_value($sql);
						if (!$sales) {$sales = 0;}
						$data2 = $sales;
						$data = set_cache($data2,"admin.php",$row_code,$key);
					}
				}

				$data	= currency_format($data);

				if ($key == "this_month" || $key == "last_month") {
					$data = "<a href=\"admin_orders_report.php?s_form=1&s_gr=3&s_sd=".va_date($date_edit_format, $value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&filter=Filter\">".$data."</a>";
				} else if ($key == "total_year") {
					$data = "<a href=\"admin_orders_report.php?s_form=1&s_gr=2&s_sd=".va_date($date_edit_format, $value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&filter=Filter\">".$data."</a>";
				} else {
					$data = "<a href=\"admin_orders_report.php?s_form=1&s_gr=4&s_sd=".va_date($date_edit_format, $value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&filter=Filter\">".$data."</a>";
				}
				$t->set_var($key,$data);
			}
			$t->parse("table_center",true);
		} else if ($row_code == "new_users") {
			foreach ($d as $key => $value) {
				if ($key == "today") {
					$sql = " SELECT count(user_id) FROM ".$table_prefix."users WHERE registration_date >= ".$db->tosql($value[0],DATETIME)." AND registration_date <= ".$db->tosql($value[1],DATETIME)." AND is_approved = 1";
					$data = get_db_value($sql);
				} else {
					$data = get_cache(24,1,"admin.php",$row_code,$key);
					if ($data === false) {
						$sql = " SELECT count(user_id) FROM ".$table_prefix."users WHERE registration_date >= ".$db->tosql($value[0],DATETIME)." AND registration_date <= ".$db->tosql($value[1],DATETIME)." AND is_approved = 1";
						$data2 = get_db_value($sql);
						$data = set_cache($data2,"admin.php",$row_code,$key);
					}
				}
				
				$data = "<a href=\"admin_users.php?&s_sd=".va_date($date_edit_format,$value[0])."&s_ed=".va_date($date_edit_format,$value[1])."\">".$data."</a>";
				$t->set_var($key,$data);
			}
			$t->parse("table_center",true);
		} else if ($row_code == "active_users") {
			foreach ($d as $key => $value) {
				if ($key == "today") {
					$sql = " SELECT count(user_id) FROM ".$table_prefix."users WHERE last_visit_date >= ".$db->tosql($value[0],DATETIME)." AND last_visit_date <= ".$db->tosql($value[1],DATETIME)." AND is_approved = 1";
					$data = get_db_value($sql);
				} else {
					$data = get_cache(24,1,"admin.php",$row_code,$key);
					if ($data === false) {
						$sql = " SELECT count(user_id) FROM ".$table_prefix."users WHERE last_visit_date >= ".$db->tosql($value[0],DATETIME)." AND last_visit_date <= ".$db->tosql($value[1],DATETIME)." AND is_approved = 1";
						$data2 = get_db_value($sql);
						$data = set_cache($data2,"admin.php",$row_code,$key);
					}
				}
				
				$data = "<a href=\"admin_users.php?&a_sd=".va_date($date_edit_format,$value[0])."&a_ed=".va_date($date_edit_format,$value[1])."\">".$data."</a>";
				$t->set_var($key,$data);
			}
			$t->parse("table_center",true);
		} else if ($row_code == "tickets") {
			foreach ($d as $key => $value) {
				if ($key == "today") {
					$sql = " SELECT count(support_id) FROM ".$table_prefix."support WHERE date_added >= ".$db->tosql($value[0],DATETIME)." AND date_added <= ".$db->tosql($value[1],DATETIME);
					$data = get_db_value($sql);
				} else {
					$data = get_cache(24,1,"admin.php",$row_code,$key);
					if ($data === false) {
						$sql = " SELECT count(support_id) FROM ".$table_prefix."support WHERE date_added >= ".$db->tosql($value[0],DATETIME)." AND date_added <= ".$db->tosql($value[1],DATETIME);
						$data2 = get_db_value($sql);
						$data = set_cache($data2,"admin.php",$row_code,$key);
					}
				}
				$data = "<a href=\"admin_support.php?s_sd=".va_date($date_edit_format,$value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&s_in=2\">".$data."</a>";
				$t->set_var($key,$data);
			}
			$t->parse("table_center",true);
		} else if ($row_code == "visits") {
			foreach ($d as $key => $value) {
				if ($key == "today") {
					$sql = " SELECT count(visit_id) FROM ".$table_prefix."tracking_visits WHERE date_added >= ".$db->tosql($value[0],DATETIME)." AND date_added <= ".$db->tosql($value[1],DATETIME);
					$data = get_db_value($sql);
				} else {
					$data = get_cache(24,1,"admin.php",$row_code,$key);
					if ($data === false) {
						$sql = " SELECT count(visit_id) FROM ".$table_prefix."tracking_visits WHERE date_added >= ".$db->tosql($value[0],DATETIME)." AND date_added <= ".$db->tosql($value[1],DATETIME);
						$data2 = get_db_value($sql);
						$data = set_cache($data2,"admin.php",$row_code,$key);
					}
				}
				
				if ($key == "this_month" || $key == "last_month") {
					$data = "<a href=\"admin_visits_report.php?s_form=1&s_gr=3&s_sd=".va_date($date_edit_format,$value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&filter=Filter&user_agent=1&referer_host=1&referer_engine_id=1&keywords=1&request_page=1&affiliate_code=1&robot_engine_id=1\">".$data."</a>";
				} else if ($key == "total_year") {
					$data = "<a href=\"admin_visits_report.php?s_form=1&s_gr=2&s_sd=".va_date($date_edit_format,$value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&filter=Filter&user_agent=1&referer_host=1&referer_engine_id=1&keywords=1&request_page=1&affiliate_code=1&robot_engine_id=1\">".$data."</a>";
				} else {
					$data = "<a href=\"admin_visits_report.php?s_form=1&s_gr=4&s_sd=".va_date($date_edit_format,$value[0])."&s_ed=".va_date($date_edit_format,$value[1])."&filter=Filter&user_agent=1&referer_host=1&referer_engine_id=1&keywords=1&request_page=1&affiliate_code=1&robot_engine_id=1\">".$data."</a>";
				}
				
				$t->set_var($key,$data);
			}
			$t->parse("table_center",true);
		}
	}
	
	if ($va_version_code & 1 && $orders_permission) {
		$sql = " SELECT count(order_id) FROM ".$table_prefix."orders";
		$t->set_var("orders_all",get_db_value($sql));
		
		$sql = " SELECT count(o.order_id) ";
		$sql.= " FROM (".$table_prefix."orders o ";
		$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.status_id = o.order_status) ";
		$sql.= " WHERE os.paid_status = 1 ";
		$t->set_var("orders_paid",get_db_value($sql));
		
		$sql = " SELECT count(o.order_id) ";
		$sql.= " FROM (".$table_prefix."orders o ";
		$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.status_id = o.order_status) ";
		$sql.= " WHERE o.order_status <> 10";
		$sql.= " AND os.paid_status = 0 ";
		$t->set_var("orders_not_paid",get_db_value($sql));
		
		$sql = " SELECT count(order_id) FROM ".$table_prefix."orders WHERE order_status = 10";
		$t->set_var("orders_failed",get_db_value($sql));

		$t->parse("orders_summary", false);
	}

	if ($va_version_code & 1 && $products_permission) {
		$sql = " SELECT count(item_id) FROM ".$table_prefix."items";
		$t->set_var("items_all",get_db_value($sql));
		
		$sql = " SELECT count(item_id) FROM ".$table_prefix."items WHERE is_showing = 1 AND is_approved = 1";
		$t->set_var("items_show_on_web",get_db_value($sql));
		
		$sql = " SELECT count(item_id) FROM ".$table_prefix."items WHERE stock_level <= 0 AND use_stock_level=1 ";
		$t->set_var("items_out_of_stock",get_db_value($sql));
  
		$sql = " SELECT count(item_id) FROM ".$table_prefix."items ";
		$sql.= " WHERE (tiny_image = '' OR tiny_image IS NULL)";
		$sql.= " AND (small_image = '' OR small_image IS NULL)";
		$sql.= " AND (big_image = '' OR big_image IS NULL)";
		$sql.= " AND (super_image = '' OR super_image IS NULL)";
		$t->set_var("items_items_the_reqire_images",get_db_value($sql));

		$t->parse("products_summary", false);
	}

	
	if ($va_version_code & 4 && $tickets_permission) {
		$sql = " SELECT count(support_id) FROM ".$table_prefix."support";
		$data = get_db_value($sql);
		$t->set_var("tickets_all",$data);
		
		$sql = " SELECT count(support_id) FROM ".$table_prefix."support";
		$data = get_db_value($sql);
		$sql = " SELECT count(message_id) FROM ".$table_prefix."support_messages";
		$data += get_db_value($sql);
		$t->set_var("tickets_all_messages",$data);
		
		$sql = " SELECT count(support_id) FROM ".$table_prefix."support WHERE support_status_id = 2";
		$data = get_db_value($sql);
		$t->set_var("tickets_answered",$data);
		
		$sql = " SELECT count(support_id) FROM ".$table_prefix."support WHERE support_status_id = 6";
		$data = get_db_value($sql);
		$t->set_var("tickets_close",$data);

		$t->parse("tickets_summary", false);
	}
	
	if ($va_version_code & 2 && $articles_permission) {
		$sql = " SELECT count(article_id) FROM ".$table_prefix."articles";
		$t->set_var("articles_all",get_db_value($sql));
		
		$sql = " SELECT count(article_id) FROM ".$table_prefix."articles WHERE status_id IN (1,2)";
		$t->set_var("articles_show_on_web",get_db_value($sql));
		
		$sql = " SELECT count(article_id) FROM ".$table_prefix."articles WHERE is_remote_rss = 1";
		$t->set_var("articles_remote",get_db_value($sql));
  
		$sql = " SELECT count(article_id) FROM ".$table_prefix."articles ";
		$sql.= " WHERE (image_small = '' OR image_small IS NULL)";
		$sql.= " AND (image_large = '' OR image_large IS NULL)";
		$sql.= " AND (stream_video = '' OR stream_video IS NULL)";
		$t->set_var("articles_articles_the_reqire_images",get_db_value($sql));

		$t->parse("articles_summary", false);
	}

	$tabs = array(
		"sales" => array("title" => va_message("SALES_MSG"), "show" => $orders_permission), 
		"orders" => array("title" => va_message("ORDERS_MSG"), "show" => $orders_permission), 
		"users" => array("title" => va_message("NEW_USERS_MSG"), "show" => $site_users_permission), 
	);
	parse_tabs($tabs);

	if ($site_users_permission) {
		$t->sparse("users_chart", false);
	}
	if ($va_version_code & 1 && $orders_permission) {
		$t->sparse("orders_chart", false);
		$t->sparse("sales_chart", false);
	}
	if (isset($message) && $message) {
		$t->set_var("message_desc", $message);
		$t->sparse("message", false);
	}

	// End time
	$mtime = microtime();$mtime = explode(" ",$mtime);$mtime = $mtime[1] + $mtime[0];$tend = $mtime;$totaltime = ($tend - $tstart);
	$t->set_var("time_generated",round($totaltime,6));

	$t->pparse("main");
