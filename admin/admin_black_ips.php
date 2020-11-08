<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_black_ips.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "messages/" . $language_code . "/support_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/forum_messages.php");

	check_admin_security("black_ips");

	$ip_modules = array(
		"log_in" => va_message("LOGIN_TITLE"), "sign_up" => va_message("SIGN_UP_MSG"), 
		"orders" => va_message("ORDERS_MSG"), "support" => va_message("SUPPORT_TITLE"), 
		"forum" => va_message("FORUM_TITLE"), "products_reviews" => va_message("PRODUCTS_REVIEWS_MSG"), 
		"articles_reviews" => va_message("ARTICLES_REVIEWS_MSG"),
	);

	$ip_rules_desc = array(
		"blocked" => array("class" => "blocked-rule", "title" => va_message("NOT_ALLOWED_MSG")),
		"warning" => array("class" => "warning-rule", "title" => va_message("SHOW_WARNING_MSG")),
		"allowed" => array("class" => "allowed-rule", "title" => va_message("ALLOWED_MSG")),
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_black_ips.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_black_ip_href", "admin_black_ip.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_black_ips.php");
	$s->set_sorter(IP_ADDRESS_MSG, "sorter_ip_address", 1, "ip_address", "", "", true);
	$s->set_sorter(ACTION_MSG, "sorter_address_action", 2, "address_action");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_black_ips.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "black_ips");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "black_ips" . $s->order_by);
	if ($db->next_record()) {

		$t->set_var("no_records", "");
		do {
			$t->set_var("ip_address", $db->f("ip_address"));

			$t->set_var("ip_rules", "");
			$ip_rules = json_decode($db->f("ip_rules"), true);
			foreach ($ip_modules as $module_code => $module_title) {
				$rule_code = isset($ip_rules[$module_code]) ? $ip_rules[$module_code] : "blocked";
				if (isset($ip_rules_desc[$rule_code])) {
					$module_class = $ip_rules_desc[$rule_code]["class"];
					$rule_title = $ip_rules_desc[$rule_code]["title"];
				} else {
					$module_class = "no-rule";
					$rule_title = va_message("NOT_ALLOWED_MSG");
				}

				$t->set_var("ip_module_class", $module_class);
				$t->set_var("ip_module_title", $module_title);
				$t->set_var("ip_rule_title", $rule_title);
				$t->parse("ip_rules", true);
			}


			$t->parse("records", true);
		} while($db->next_record());
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");

