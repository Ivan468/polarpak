<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_global_search.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
      
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	
	include_once($root_folder_path . "messages/" . $language_code . "/forum_messages.php");
	
	check_admin_security();	
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_global_search.html");

	$search_types = array(
		"products" => PRODUCTS_MSG,
		"articles" => ARTICLES_AND_CATEGORIES_MSG,
		"manuals"  => ADMIN_MANUAL_MSG,
		"ads"      => ADS_TITLE,
		"forums"   => FORUMS_MSG,
		"orders"   => ORDERS_MSG,
		"users"    => CUSTOMERS_MSG,
	);
	
	$aa = isset($_GET['aa']) ? $_GET['aa'] : false;
	$s  = trim(rtrim(strip_tags(get_param("s"))));

	$t->set_var("s", $s);
	$t->set_var("aa", "");
	
	$global_total_records = 0;	
	foreach ($search_types AS $search_type_id => $search_type_name) {
		if (!$aa || in_array($search_type_id, $aa)) {
			$t->set_var("aa_checked", "checked");
			if ($s) {
				$function_name = "process_search_" . $search_type_id;
				if (function_exists($function_name)) {
					$function_name($s);
				}
			}
		} else {
			$t->set_var("aa_checked", "");
		}
		$t->set_var("aa_value", $search_type_id);
		$t->set_var("aa_description", $search_type_name);
		$t->parse("aa");
	}
	
	$admin_latest_searches = get_session("admin_latest_searches");
	if ($global_total_records) {
		if (!$admin_latest_searches) $admin_latest_searches = array();
		if (isset($admin_latest_searches[$s])) {
			unset($admin_latest_searches[$s]);
		}
		$admin_latest_searches[$s] = $global_total_records;
		set_session("admin_latest_searches", $admin_latest_searches);
	}
	$t->set_var("latest_search_block", "");
	$t->set_var("latest_search", "");
	if ($admin_latest_searches) {
		foreach ($admin_latest_searches AS $keyword => $count) {
			$t->set_var("keyword", $keyword);
			$t->set_var("keyword_url", urlencode($keyword));
			$t->set_var("count", $count);
			$t->parse("latest_search");
		}
		$t->parse("latest_search_block");
	}
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$t->pparse("main");
	
	function process_search_products($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_products.php";
		$global_total_records += admin_products_block('search_result', array("s" => $s));
	}
	
	function process_search_articles($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_articles.php";
		$global_total_records += admin_articles_block('search_result', array("s" => $s));
	}
	
	function process_search_manuals($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_manuals.php";
		$global_total_records += admin_manuals_block('search_result', array("s" => $s));
	}
	
	function process_search_ads($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_ads.php";
		$global_total_records += admin_ads_block('search_result', array("s" => $s));
	}
	
	function process_search_orders($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_orders.php";
		$global_total_records += admin_orders_block('search_result', array("s" => $s));
	}
	
	function process_search_users($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_users.php";
		$global_total_records += admin_users_block('search_result', array("s" => $s));
	}
	
	function process_search_forums($s) {
		global $db, $table_prefix, $global_total_records;
		include_once "blocks/admin_forums.php";
		$global_total_records += admin_forums_block('search_result', array("s" => $s));
	}
?>