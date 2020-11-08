<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_products.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_static_data");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_products.php" => va_message("SUPPORT_PRODUCTS_MSG"),
	);

	$operation = get_param("operation");
	$product_id = get_param("product_id");

	// update show_for_user field
	if ($operation == "show-yes") {
		$sql = " UPDATE ".$table_prefix."support_products ";
		$sql.= " SET show_for_user=1 ";
		$sql.= " WHERE product_id=" . $db->tosql($product_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "show-no") {
		$sql = " UPDATE ".$table_prefix."support_products ";
		$sql.= " SET show_for_user=0 ";
		$sql.= " WHERE product_id=" . $db->tosql($product_id, INTEGER);
		$db->query($sql);
	}

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_support_products.html");

	$asps = new VA_URL("admin_support_products.php", false);
	$asps->add_parameter("sort_ord", REQUEST, "sort_ord");
	$asps->add_parameter("sort_dir", REQUEST, "sort_dir");
	$asps->add_parameter("page", REQUEST, "page");

	$asp = new VA_URL("admin_support_product.php", false);
	$asp->add_parameter("sort_ord", REQUEST, "sort_ord");
	$asp->add_parameter("sort_dir", REQUEST, "sort_dir");
	$asp->add_parameter("page", REQUEST, "page");
	$t->set_var("admin_support_product_new_url", $asp->get_url());

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_product_href", "admin_support_product.php");
	$t->set_var("admin_support_products_href", "admin_support_products.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support_products.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting("3", "asc");
	$s->set_sorter(va_message("ID_MSG"), "sorter_product_id", "1", "product_id");
	$s->set_sorter(va_message("PROD_NAME_MSG"), "sorter_product_name", "2", "product_name");
	$s->set_sorter(va_message("SORT_ORDER_MSG"), "sorter_product_order", "3", "product_order", "product_order, product_name", "product_order DESC, product_name DESC");
	$s->set_sorter(va_message("SHOW_FOR_USER_MSG"), "sorter_show_for_user", "4", "show_for_user");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support_products.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "support_products");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = set_recs_param("admin_support_products.php");
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	// get products first
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = "SELECT * FROM " . $table_prefix . "support_products";
	$sql .= $s->order_by;
	$support_products = array(); $products_ids = array();
	$db->query($sql);
	while ($db->next_record()) {
		$product_id = $db->f("product_id");
		$products_ids[] = $product_id;
		$support_products[$product_id] = $db->Record;
		$support_products[$product_id]["deps"] = array();
		$support_products[$product_id]["sites"] = array();
	}

	// get departments for properties
	$sql  = " SELECT std.product_id, sd.short_name, sd.dep_name FROM " . $table_prefix . "support_products_departments std ";
	$sql .= " INNER JOIN " . $table_prefix . "support_departments sd ON sd.dep_id=std.dep_id ";
	$sql .= " WHERE std.product_id IN (" . $db->tosql($products_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$product_id = $db->f("product_id");
		$short_name = get_translation($db->f("short_name"));
		$dep_name = get_translation($db->f("dep_name"));
		$support_products[$product_id]["deps"][] = array("short_name" => $short_name, "dep_name" => $dep_name);
	}

	// get sites for properties
	$sql  = " SELECT sts.product_id, s.site_name FROM " . $table_prefix . "support_products_sites sts ";
	$sql .= " INNER JOIN " . $table_prefix . "sites s ON s.site_id=sts.site_id ";
	$sql .= " WHERE sts.product_id IN (" . $db->tosql($products_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$product_id = $db->f("product_id");
		$site_name = get_translation($db->f("site_name"));
		$support_products[$product_id]["sites"][] = $site_name;
	}


	if (count($support_products)) {

		$t->set_var("no_records", "");
		foreach ($support_products as $product_id => $data) {

			$show_for_user = $data["show_for_user"];
			$product_order = $data["product_order"];
			$product_name = get_translation($data["product_name"]);

			$deps_all = $data["deps_all"];
			$deps = $data["deps"];
			$sites_all = $data["sites_all"];
			$sites = $data["sites"];

			if ($show_for_user) {
				$show_for_user_desc = va_message("YES_MSG");
				$show_for_user_class= "yes-option";
				$asps->add_parameter("operation", CONSTANT, "show-no");
			} else {
				$show_for_user_desc = va_message("NO_MSG");
				$show_for_user_class= "no-option";
				$asps->add_parameter("operation", CONSTANT, "show-yes");
			}
			$asps->add_parameter("product_id", CONSTANT, $product_id);
			$asp->add_parameter("product_id", CONSTANT, $product_id);

			$t->set_var("product_id", $product_id);
			$t->set_var("product_name", htmlspecialchars($product_name));
			$t->set_var("product_order", $product_order);
			$t->set_var("show_for_user_desc", $show_for_user_desc);
			$t->set_var("show_for_user_class", $show_for_user_class);
			$t->set_var("show_for_user_url", $asps->get_url());
			$t->set_var("admin_support_product_url", $asp->get_url());

			// parse field deps
			$t->set_var("extra_deps", "");
			$t->set_var("dep_list", "");
			$t->set_var("dep_single", "");
			if ($deps_all || count($deps) <= 1) {
				if ($deps_all) {
					$t->set_var("dep_name", va_message("ALL_MSG"));
				} else if (count($deps) == 1) {
					$t->set_var("dep_name", htmlspecialchars($deps[0]["short_name"]));
				} else {
					$t->set_var("dep_name", "&ndash;");
				}
				$t->sparse("dep_single", false);
			} else {

				$t->set_var("dep_summary", count($deps)." ".va_message("DEPARTMENTS_MSG"));
				foreach ($deps as $dep_data) {
					$t->set_var("dep_name", htmlspecialchars($dep_data["dep_name"]));
					$t->sparse("extra_deps", true);
				}
				$t->sparse("dep_list", false);
			}

			// parse sites
			$t->set_var("extra_sites", "");
			$t->set_var("site_list", "");
			$t->set_var("site_single", "");
			if ($sites_all || count($sites) <= 1) {
				if ($sites_all) {
					$t->set_var("site_name", va_message("ALL_MSG"));
				} else if (count($sites) == 1) {
					$t->set_var("site_name", htmlspecialchars($sites[0]));
				} else {
					$t->set_var("site_name", "&ndash;");
				}
				$t->sparse("site_single", false);
			} else {
				$t->set_var("site_summary", count($sites)." ".va_message("SITES_MSG"));
				foreach ($sites as $site_name) {
					$t->set_var("site_name", htmlspecialchars($site_name));
					$t->sparse("extra_sites", true);
				}
				$t->sparse("site_list", false);
			}

			$t->parse("records", true);
		} 
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");
