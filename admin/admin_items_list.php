<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_items_list.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "includes/products_functions.php");
	include_once("./admin_common.php");

	check_admin_security("products_categories");

	list($host_valid, $license_expired, $va_code) = va_license_check();

	$no_image = get_setting_value($settings, "product_no_image_tiny", "");

	$permissions = get_permissions();
	$products_settings = get_setting_value($permissions, "products_settings", 0);
	$product_types = get_setting_value($permissions, "product_types", 0);
	$manufacturers = get_setting_value($permissions, "manufacturers", 0);
	$products_reviews = get_setting_value($permissions, "products_reviews", 0);
	$shipping_methods = get_setting_value($permissions, "shipping_methods", 0);
	$shipping_times = get_setting_value($permissions, "shipping_times", 0);
	$shipping_rules = get_setting_value($permissions, "shipping_rules", 0);
	$downloadable_products = get_setting_value($permissions, "downloadable_products", 0);
	$coupons = get_setting_value($permissions, "coupons", 0);
	$advanced_search = get_setting_value($permissions, "advanced_search", 0);
	$products_report = get_setting_value($permissions, "products_report", 0);
	$product_prices = get_setting_value($permissions, "product_prices", 0);
	$product_images = get_setting_value($permissions, "product_images", 0);
	$product_properties = get_setting_value($permissions, "product_properties", 0);
	$product_features = get_setting_value($permissions, "product_features", 0);
	$product_related = get_setting_value($permissions, "product_related", 0);
	$product_categories = get_setting_value($permissions, "product_categories", 0);
	$product_accessories = get_setting_value($permissions, "product_accessories", 0);
	$product_releases = get_setting_value($permissions, "product_releases", 0);
	$products_order = get_setting_value($permissions, "products_order", 0);
	$products_export = get_setting_value($permissions, "products_export", 0);
	$products_import = get_setting_value($permissions, "products_import", 0);
	$products_export_google_base = get_setting_value($permissions, "products_export_google_base", 0);
	$features_groups = get_setting_value($permissions, "features_groups", 0);
	$tell_friend = get_setting_value($permissions, "tell_friend", 0);
	$categories_export = get_setting_value($permissions, "categories_export", 0);
	$categories_import = get_setting_value($permissions, "categories_import", 0);
	$categories_order = get_setting_value($permissions, "categories_order", 0);
	$view_categories = get_setting_value($permissions, "view_categories", 0);
	$view_products = get_setting_value($permissions, "view_products", 0);
	$add_categories = get_setting_value($permissions, "add_categories", 0);
	$update_categories = get_setting_value($permissions, "update_categories", 0);
	$remove_categories = get_setting_value($permissions, "remove_categories", 0);
	$add_products = get_setting_value($permissions, "add_products", 0);
	$update_products = get_setting_value($permissions, "update_products", 0);
	$remove_products = get_setting_value($permissions, "remove_products", 0);
	$approve_products = get_setting_value($permissions, "approve_products", 0);
	$view_only_products = !$update_products && $view_products;
	$read_only_products = !$update_products && !$view_products;
	$view_only_categories = !$update_categories && !$remove_categories && $view_categories;
	$read_only_categories = !$update_categories && !$remove_categories && !$view_categories;
	$remove_checkbox_column = !$update_products && !$remove_products && !$approve_products;
	$empty_select_block = !$add_products && !$update_products && !$products_order;
	$empty_export_block = !$products_export && !$products_import && !$products_export_google_base;
	$empty_export_approve_block = $empty_export_block && !$approve_products;
	$empty_first_category_block = !$add_categories && !$categories_order;
	$empty_second_category_block = !$categories_export && !$categories_import;

	$list_url = new VA_URL("admin_items_list.php", false);
	$list_url->add_parameter("category_id", REQUEST, "category_id");
	$list_url->add_parameter("sc", GET, "sc");
	$list_url->add_parameter("sit", GET, "sit");
	$list_url->add_parameter("sl", GET, "sl");
	$list_url->add_parameter("sa", GET, "sa");
	$list_url->add_parameter("ss", GET, "ss");
	$list_url->add_parameter("ap", GET, "ap");
	$list_url->add_parameter("s", GET, "s");
	$list_url->add_parameter("page", GET, "page");
	if ($sitelist) {
		$list_url->add_parameter("s_sti", GET, "s_sti");		
	}	
	// url object to edit products	
	$edit_url = new VA_URL("admin_product.php", false);
	$edit_url->parameters = $list_url->parameters;
	// url object to update products	
	$update_url = new VA_URL("admin_items_list.php", false);
	$update_url->parameters = $list_url->parameters;
	
	$operation = get_param("operation");
	$items_ids = get_param("items_ids");
	$category_id = get_param("category_id");
	$categories_ids = get_param("categories_ids");
	$approved_status = get_param("approved_status");
	if ($operation == "delete_items") {
		if ($remove_products && strlen($items_ids)) {
			delete_products($items_ids);
		}
	} else if ($operation == "delete_categories") {
		if ($remove_categories && strlen($categories_ids)) {
			// check categories if we need to show warning before delete them
			$stat = VA_Categories::categories_stat($categories_ids);
			if ($stat["subcategories_number"] > 0 || $stat["unique_products_number"] > 0) {
				$delete_url = "admin_delete_confirm.php?operation=delete_categories&page=admin_items_list&delete_ids=".urlencode($categories_ids)."&category_id=".intval($category_id);
				header("Location: ".$delete_url);
				exit;
			} else {
				delete_categories($categories_ids);
			}
		}
	} else if ($operation == "update_status") {
		if ($update_products && strlen($items_ids)) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_approved=" . $db->tosql($approved_status, INTEGER); 
			$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, TEXT, false) . ")";
			$db->query($sql);
		}
	} else if ($operation == "activate") {
		if ($update_products && strlen($items_ids)) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_showing=1 "; 
			$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, TEXT, false) . ")";
			$db->query($sql);
		}
	} else if ($operation == "disable") {
		if ($update_products && strlen($items_ids)) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_showing=0 "; 
			$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, TEXT, false) . ")";
			$db->query($sql);
		}
	} else if ($operation == "approve") {
		if ($update_products && strlen($items_ids)) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_approved=1 "; 
			$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, TEXT, false) . ")";
			$db->query($sql);
		}
	} else if ($operation == "disapprove") {
		if ($update_products && strlen($items_ids)) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_approved=0 "; 
			$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, TEXT, false) . ")";
			$db->query($sql);
		}
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_items_list.html");

	//BEGIN product privileges changes
	$set_delimiter = false;
	if ($product_prices) {
		$set_delimiter = true;
	}
	if ($product_images && $set_delimiter) {
		$t->set_var("product_images_delimiter", " | ");
	} elseif ($product_images) {
		$set_delimiter = true;
	}
	if ($product_properties && $set_delimiter) {
		$t->set_var("product_properties_delimiter", " | ");
	} elseif ($product_properties) {
		$set_delimiter = true;
	}
	if ($product_features && $set_delimiter) {
		$t->set_var("product_features_delimiter", " | ");
	} elseif ($product_features) {
		$set_delimiter = true;
	}
	if ($product_related && $set_delimiter) {
		$t->set_var("product_related_delimiter", " | ");
	} elseif ($product_related) {
		$set_delimiter = true;
	}
	if ($product_categories && $set_delimiter) {
		$t->set_var("product_categories_delimiter", " | ");
	} elseif ($product_categories) {
		$set_delimiter = true;
	}
	if ($product_accessories && $set_delimiter) {
		$t->set_var("product_accessories_delimiter", " | ");
	} elseif ($product_accessories) {
		$set_delimiter = true;
	}
	if ($product_releases && $set_delimiter) {
		$t->set_var("product_releases_delimiter", " | ");
	}
	//END product privileges changes

	// set files names
	$t->set_var("admin_items_list_href",       "admin_items_list.php");
	$t->set_var("admin_products_widgets_href", "admin_products_widgets.php");
	$t->set_var("admin_cms_page_layout_href",  "admin_cms_page_layout.php");
	$t->set_var("admin_reviews_href",          "admin_reviews.php");
	$t->set_var("admin_category_edit_href",    "admin_category_edit.php");
	$t->set_var("admin_product_href",          "admin_product.php");
	$t->set_var("admin_properties_href",       "admin_properties.php");
	$t->set_var("admin_releases_href",         "admin_releases.php");
	$admin_item_related = get_session("related_url");
	if (!$admin_item_related) { $admin_item_related = "admin_item_related.php"; }
	$t->set_var("admin_item_related_href",     $admin_item_related);
	$t->set_var("admin_item_categories_href",  "admin_item_categories.php");
	$t->set_var("admin_category_items_href",  "admin_category_items.php");
	$t->set_var("admin_categories_order_href", "admin_categories_order.php");
	$t->set_var("admin_products_order_href",   "admin_products_order.php");
	$t->set_var("admin_item_types_href",       "admin_item_types.php");
	$t->set_var("admin_features_groups_href",  "admin_features_groups.php");
	$t->set_var("admin_item_prices_href",      "admin_item_prices.php");
	$t->set_var("admin_item_features_href",    "admin_item_features.php");
	$t->set_var("admin_item_images_href",      "admin_item_images.php");
	$t->set_var("admin_item_accessories_href", "admin_item_accessories.php");
	$t->set_var("admin_export_google_base_href", "admin_export_google_base.php");
	$t->set_var("admin_search_href",           "admin_search.php");
	$t->set_var("admin_tell_friend_href",      "admin_tell_friend.php");
	$t->set_var("admin_products_edit_href",  "admin_products_edit.php");
	$t->set_var("rp_url", urlencode($list_url->get_url()));

	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_export_href", "admin_export.php");

	$t->set_var("approved_status", htmlspecialchars($approved_status));

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$category_id = get_param("category_id");
	if (!strlen($category_id))  { $category_id = "0"; }
	// get search parameters
	$s = trim(get_param("s"));
	$sit = get_param("sit");
	$sc = get_param("sc");
	$sl = get_param("sl");
	$ss = get_param("ss");
	$ap = get_param("ap");
	$s_sti = get_param("s_sti");
	$search = (strlen($s) || strlen($sit) || strlen($sl) || strlen($ss) || strlen($ap) || strlen($s_sti)) ? true : false;
	if (strlen($sc)) { 
		$category_id = $sc; 
	} else {
		$sc = $category_id; 
	}
	$sa = "";

	$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "categories", "tree");
	$tree->show($category_id);

	if ($category_id) {
		$sql  = " SELECT category_name, full_description ";
		$sql .= " FROM " . $table_prefix . "categories ";
		$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$t->set_var("full_description", $db->f("full_description"));
			$t->set_var("current_category_name", get_translation($db->f("category_name")));
		} else {
			$category_id = 0;
		}
	}

	if (!$category_id) {
		if (strlen($s)) {
			$t->set_var("current_category_name", va_message("SEARCH_IN_ALL_MSG"));
		} else {
			$t->set_var("current_category_name", va_message("TOP_CATEGORY_MSG"));
		}
	}

	$t->set_var("parent_category_id", $category_id);
	// BEGIN product privileges changes
	$set_delimiter = false;
	if ($add_categories) {
		$t->parse("add_categories_priv", false);
		$set_delimiter = true;
	}
	//END product_privileges changes

	$sql  = " SELECT category_id,category_name,admin_list_class,is_showing ";
	$sql .= " FROM " . $table_prefix . "categories WHERE parent_category_id = " . $db->tosql($category_id, INTEGER);
	$sql .= " ORDER BY category_order ";
	$db->query($sql);
	if ($db->next_record())
	{
		// BEGIN product privileges changes
		if ($categories_order) {
			if ($set_delimiter) {
				$t->set_var("categories_order_delimiter", "|");
			}
			$t->parse("categories_order_link", false);
		}
		if (!$empty_first_category_block) {
			$t->parse("categories_first_block", false);
		}
		//END product_privileges changes

		$t->set_var("no_categories", "");
		$category_index = 0;
		do {
			$category_index++;
			$row_category_id = $db->f("category_id");
			$row_category_name = $db->f("category_name");
			$row_category_class = $db->f("admin_list_class");
			$is_showing = $db->f("is_showing");
			$row_category_name = get_translation($row_category_name);

			if (!$is_showing) {
				$row_category_class = trim($row_category_class." disabled");
			}
			$row_class = ($category_index % 2 == 0) ? "row1" : "row2";
			$row_category_class = trim($row_category_class." ".$row_class);

			$t->set_var("category_index", $category_index);
			$t->set_var("category_id", $row_category_id);
			$t->set_var("category_name", $row_category_name);
			$t->set_var("category_class", $row_category_class);

			if (!$read_only_categories) {
				if ($view_only_categories) {
					$t->set_var("category_edit_msg", va_message("VIEW_MSG"));
				} else {
					$t->set_var("category_edit_msg", va_message("EDIT_MSG"));
				}
				$t->parse("categories_edit_link", false);
			}
			
			if ($product_categories) {
				$t->parse("category_products_priv", false);
			} else {
				$t->set_var("category_products_priv", "");
			}

			if ($remove_categories) {
				$t->parse("category_checkbox", false);
			} else {
				$t->set_var("category_checkbox", "");
			}

			$t->parse("categories");
		} while ($db->next_record());
		if ($remove_categories) {
			$t->parse("categories_all_checkbox", false);
			if ($add_categories || $update_categories) {
				$t->set_var("delete_categories_delimiter", "|");	
			}
			$t->parse("delete_categories_link", false);
			$t->set_var("categories_colspan", "2");
		} else {
			$t->set_var("categories_colspan", "1");
		}

		$t->set_var("categories_number", $category_index);
		$t->parse("categories_header", false);
	}
	else
	{
		$t->set_var("categories", "");
		$t->set_var("categories_order_link", "");
		$t->parse("no_categories");
	}

	// BEGIN product privileges changes
	if (!$empty_first_category_block) {
		$t->parse("categories_first_block", false);
	}
	//END product_privileges changes
	
	$group_by = "";
	
	$sorter = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_items_list.php");
	$sorter->set_parameters(false, true, true, false);
	$sorter->set_default_sorting(10, "asc");
	$sorter->set_sorter(va_message("PROD_TITLE_COLUMN"), "sorter_item_name", 1, "i.item_name");
	$sorter->set_sorter(va_message("PROD_PRICE_COLUMN"), "sorter_price", 2, "i.price");
	$sorter->set_sorter(va_message("PROD_QTY_COLUMN"), "sorter_qty", 3, "i.stock_level");
	if ($search || $sit != "") {
		$sorter->set_sorter(va_message("ADMIN_ORDER_MSG"), "sorter_order", 10, "i.item_order, i.item_id", "i.item_order, i.item_id", "i.item_order DESC, i.item_id");
		$group_by .= ", i.item_order";
	} else {
		$sorter->set_sorter(va_message("ADMIN_ORDER_MSG"), "sorter_order", 10, "ic.item_order", "ic.item_order, i.item_order, i.item_id", "ic.item_order DESC, i.item_order, i.item_id");
		$group_by .= ", ic.item_order, i.item_order";
	}

	$where = "";
	$join  = "";
	$brackets = "";
	if ($search && $category_id != 0) {
		$brackets .= "((";
		$join  .= " LEFT JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
		$join  .= " LEFT JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id) ";
		
		$where .= " AND (ic.category_id = " . $db->tosql($category_id, INTEGER);
		$where .= " OR c.category_path LIKE '" . $db->tosql($tree->get_path($category_id), TEXT, false) . "%')";
	} elseif (!$search) {
		$brackets .= "(";
		$join  .= " LEFT JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
		$where .= " AND ic.category_id = " . $db->tosql($category_id, INTEGER);
	}
	if ($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$sa[$si] = str_replace("%","\%",$sa[$si]);
			$where .= " AND (i.item_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR i.item_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			if (sizeof($sa) == 1 && preg_match("/^\d+$/", $sa[0])) {
				$where .= " OR i.item_id =" . $db->tosql($sa[0], INTEGER);
			}
			$where .= " OR i.manufacturer_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}
	if ($sit == 2) {
		$where.= " AND (tiny_image = '' OR tiny_image IS NULL)";
		$where.= " AND (small_image = '' OR small_image IS NULL)";
		$where.= " AND (big_image = '' OR big_image IS NULL)";
		$where.= " AND (super_image = '' OR super_image IS NULL)";
	}
	if (strlen($sl)) {
		if ($sl == 1) {
			$where .= " AND (i.stock_level>0 OR i.stock_level IS NULL) ";
		} else {
			$where .= " AND i.stock_level<1 ";
		}
	}
	if (strlen($ss)) {
		if ($ss == 1) {
			$where .= " AND i.is_showing=1 ";
		} else {
			$where .= " AND i.is_showing=0 ";
		}
		$group_by .= ", i.is_showing";
	}
	if (strlen($ap)) {
		if ($ap == 1) {
			$where .= " AND i.is_approved=1 ";
		} else {
			$where .= " AND i.is_approved=0 ";
		}
		$group_by .= ", i.is_approved";
	}
	if (strlen($s_sti)) {
		if ($s_sti == "all") {
			$where .= " AND i.sites_all=1 ";
		} else {
			$brackets .= "(";
			$join  .= " LEFT JOIN " . $table_prefix . "items_sites s ON (s.item_id = i.item_id AND i.sites_all = 0 )) ";
			$where .= " AND (s.site_id=" . $db->tosql($s_sti, INTEGER) . " OR i.sites_all=1) ";
		}
		$group_by .= ", i.sites_all";
	}

	
	$total_records = 0;
	if (strtolower($db_type) == "mysql" || !strlen($join)) {
		$sql  = " SELECT COUNT(DISTINCT i.item_id) ";
	} else {
		$sql  = " SELECT COUNT(*) ";
	}
	$sql .= " FROM " . $brackets . $table_prefix . "items i " . $join;
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$total_records = 0;
	if (strtolower($db_type) == "mysql" || !strlen($join)) {
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
	} else {
		$sql .= " GROUP BY i.item_id";
		$db->query($sql);
		while ($db->next_record()) {
			$total_records++;
		}
	}

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_items_list.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;              

	$item_index = 0;

	// 'Add new product' link
	$set_delimiter = false;
	if ($add_products) {
		$t->set_var("add_delimiter", " | ");
		$t->set_var("admin_product_new_url", $edit_url->get_url());
		$t->parse("add_products_priv", false);
	}

	if ($total_records > 0) {
		$sql  = " SELECT i.item_id, i.is_showing, i.is_approved, i.is_draft, i.item_code, i.manufacturer_code, i.item_name, ";
		$sql .= " i.price, i.sales_price, i.is_sales, i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock, ";
		$sql .= " i.tiny_image, i.big_image, i.super_image, ";
		$sql .= " i.access_level, i.guest_access_level, i.admin_access_level ";
		$sql .= " FROM " . $brackets . $table_prefix . "items i " . $join;
		$sql .= " WHERE 1=1 ";
		$sql .= $where;
		$sql .= " GROUP BY i.item_id, i.is_showing, i.is_approved, i.is_draft, i.item_code, i.manufacturer_code, i.item_name, ";
		$sql .= " i.price, i.sales_price, i.is_sales, i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock, ";
		$sql .= " i.tiny_image, i.big_image, i.super_image, ";
		$sql .= " i.access_level, i.guest_access_level, i.admin_access_level ";
		$sql .= $group_by;
	
		$sql .= $sorter->order_by;
		$db->query($sql);
		if ($db->next_record())
		{
			//BEGIN product privileges changes
			if ($update_products) {
				$t->parse("edit_items_link", false);
			}
			if ($remove_products) {
				$t->parse("delete_items_link", false);
			}
			if ($products_order) {
				$t->parse("products_order_link", false);
			}
			//END product privileges changes
			$t->set_var("category_id", $category_id);
			do {
				$item_index++;
				$is_showing = $db->f("is_showing");
				$is_approved = $db->f("is_approved");
				$is_draft = $db->f("is_draft");

				$access_level = $db->f("access_level");
				$guest_access_level = $db->f("guest_access_level");
				$admin_access_level = $db->f("admin_access_level");

				$item_id = $db->f("item_id");
				$product_category_id = $db->f("category_id");
				$item_code = $db->f("item_code");
				$manufacturer_code = $db->f("manufacturer_code");
				$item_name = get_translation($db->f("item_name"));
				if ($is_draft && !strlen($item_name)) {
					$item_name = "[".va_message("FOLDER_DRAFT_MSG")."]";
				}
				$price = $db->f("price");
				$is_sales = $db->f("is_sales");
				$sales_price = $db->f("sales_price");
				$stock_level = $db->f("stock_level");
				$use_stock_level = $db->f("use_stock_level");
				$hide_out_of_stock = $db->f("hide_out_of_stock");
				$disable_out_of_stock = $db->f("disable_out_of_stock");

				// check item status
				if ($is_draft) {
					$item_status = va_message("FOLDER_DRAFT_MSG");
				} else if (!$is_showing) {
					$item_status = va_message("INACTIVE_MSG");
				} else if (!$is_approved) {
					$item_status = va_message("NOT_APPROVED_MSG");
				} else if ($use_stock_level && ($hide_out_of_stock || $disable_out_of_stock) && $stock_level < 1) { 
					$item_status = va_message("OUT_OF_STOCK_MSG");
				} else {
					$item_status = va_message("ACTIVE_MSG");
				}

				$tiny_image = $db->f("tiny_image");
				if (!$tiny_image) { $tiny_image = $no_image; }
				$super_image = $db->f("super_image");
				if (!$super_image) { $super_image = $db->f("big_image"); }

				$item_codes = "";
				if ($item_code && $manufacturer_code) {
					$item_codes = "(" . $item_code . ", " . $manufacturer_code . ")";
				} elseif ($item_code) {
					$item_codes = "(" . $item_code . ")";
				} elseif ($manufacturer_code) {
					$item_codes = "(" . $manufacturer_code . ")";
				}

				$price = calculate_price($price, $is_sales, $sales_price);

				// clear status tags
				$t->set_var("status_draft", "");
				$t->set_var("status_active", "");
				$t->set_var("status_inactive", "");
				$t->set_var("status_approved", "");
				$t->set_var("status_disapproved", "");
				$t->set_var("status_full_access", "");
				$t->set_var("status_limited_access", "");
				$t->set_var("status_no_access", "");
				$t->set_var("status_in_stock", "");
				$t->set_var("status_out_stock", "");

				$t->set_var("item_id", $item_id);
				$t->set_var("item_index", $item_index);
				$t->set_var("product_category_id", $product_category_id);
				$t->set_var("item_code", htmlspecialchars($item_code));
				$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
				$t->set_var("item_codes", htmlspecialchars($item_codes));
				$t->set_var("item_status", htmlspecialchars($item_status));
				if ($tiny_image) {
					if (!preg_match("/^(http|\/)/", $tiny_image)) {
						$tiny_image = "../".$tiny_image;
					}
					$t->set_var("src", htmlspecialchars($tiny_image));
					if ($super_image && !preg_match("/^(http|\/)/", $super_image)) {
						$super_image = "../".$super_image;
					}
					$t->set_var("super_src", $super_image);

					$t->parse("item_image", false);
				} else {
					$t->set_var("item_image", "");
				}

				$item_name = htmlspecialchars($item_name);
				if (is_array($sa)) {
					for ($si = 0; $si < sizeof($sa); $si++) {
						$regexp = "";
						for ($si = 0; $si < sizeof($sa); $si++) {
							if (strlen($regexp)) $regexp .= "|";
							$regexp .= preg_quote($sa[$si], "/");
						}
						if (strlen($regexp)) {
							$item_name = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $item_name);
						}
					}
				}
				$t->set_var("item_name", $item_name);
				$t->set_var("price", currency_format($price));
				if ($stock_level < 0) {
					$stock_level = "<font color=red>" . $stock_level . "</font>";
				}
				$t->set_var("stock_level", $stock_level);

				$edit_url->remove_parameter("tab");
				$edit_url->add_parameter("item_id", CONSTANT, $item_id);
				$t->set_var("admin_product_url", $edit_url->get_url());

				// BEGIN product privileges changes
				if ($product_prices) {
					$t->parse("product_prices_priv", false);
				} else {
					$t->set_var("product_prices_priv", "");
				}
				if ($product_images) {
					$t->parse("product_images_priv", false);
				} else {
					$t->set_var("product_images_priv", "");
				}
				if ($product_properties) {
					$t->parse("product_properties_priv", false);
				} else {
					$t->set_var("product_properties_priv", "");
				}
				if ($product_features) {
					$t->parse("product_features_priv", false);
				} else {
					$t->set_var("product_features_priv", "");
				}
				if ($product_related) {
					$t->parse("product_related_priv", false);
				} else {
					$t->set_var("product_related_priv", "");
				}
				if ($product_categories) {
					$t->parse("product_categories_priv", false);
				} else {
					$t->set_var("product_categories_priv", "");
				}
				if ($product_accessories) {
					$t->parse("product_accessories_priv", false);
				} else {
					$t->set_var("product_accessories_priv", "");
				}
				if ($product_releases) {
					$t->parse("product_releases_priv", false);
				} else {
					$t->set_var("product_releases_priv", "");
				}
				if ($read_only_products) {
					$t->parse("read_only_products_priv", false);
					$t->set_var("update_products_priv", "");
				} elseif ($view_only_products) {
					$t->set_var("product_edit_msg", va_message("VIEW_MSG"));
					$t->parse("update_products_priv", false);
					$t->set_var("read_only_products_priv", "");
				} else {
					$t->set_var("product_edit_msg", va_message("EDIT_MSG"));
					$t->parse("update_products_priv", false);
					$t->set_var("read_only_products_priv", "");
				}
				if (!$remove_checkbox_column) {
					$t->parse("checkbox_list_priv", false);
				}

				$update_url->add_parameter("items_ids", CONSTANT, $item_id);

				$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
				if ($is_showing) { 
					$update_url->add_parameter("operation", CONSTANT, "disable");
					$t->set_var("active_url", $update_url->get_url());
					$t->parse("status_active", false);
				} else {
					$update_url->add_parameter("operation", CONSTANT, "activate");
					$t->set_var("inactive_url", $update_url->get_url());
					$t->parse("status_inactive", false);
					$row_style .= " disabled";
				}
				if ($is_approved) { 
					$update_url->add_parameter("operation", CONSTANT, "disapprove");
					$t->set_var("approved_url", $update_url->get_url());
					$t->parse("status_approved", false);
				} else {
					$update_url->add_parameter("operation", CONSTANT, "approve");
					$t->set_var("disapproved_url", $update_url->get_url());
					$t->parse("status_disapproved", false);
					$row_style .= " not-approved"; 
				}
				if ($is_draft) { 
					$t->parse("status_draft", false);
					$row_style .= " draft"; 
				}
				// check if item is out of stock
				if (($hide_out_of_stock || $disable_out_of_stock) && $stock_level < 1) { 
					$edit_url->add_parameter("tab", CONSTANT, "stock");
					$t->set_var("stock_url", $edit_url->get_url());
					$t->parse("status_out_stock", false);
					$row_style .= " out-stock"; 
				}
				//$t->parse("status_in_stock", false);

				$edit_url->add_parameter("tab", CONSTANT, "subscriptions");
				$t->set_var("access_url", $edit_url->get_url());
				if ($access_level >= 6 && $guest_access_level >= 6 && $admin_access_level >= 6) {
					$t->parse("status_full_access", false);
				} else if ($access_level == 0 && $guest_access_level == 0 && $admin_access_level == 0) {
					$t->parse("status_no_access", false);
					$row_style .= " no-access";
				} else {
					$t->parse("status_limited_access", false);
				}

				$t->set_var("row_style", $row_style);
				// END product privileges changes
				$t->parse("items_list");
			} while ($db->next_record());
			if (!$remove_checkbox_column) {
				$t->parse("checkbox_header_priv", false);
			}
			$t->parse("items_header", false);
		}
	}

	if ($item_index < 1) {
		$t->set_var("delete_items_link", "");
		$t->set_var("products_order_link", "");
		$t->set_var("items_list", "");
	}

	if ($total_records > 0) {
		$admin_google_base_filtered_url = new VA_URL("admin_export_google_base.php", false);
		if ($search) {
			$admin_google_base_filtered_url->add_parameter("sc", GET, "sc");
		} else {
			$admin_google_base_filtered_url->add_parameter("sc", CONSTANT, $category_id);
		}
		$admin_google_base_filtered_url->add_parameter("sl", GET, "sl");
		$admin_google_base_filtered_url->add_parameter("sa", GET, "sa");
		$admin_google_base_filtered_url->add_parameter("ss", GET, "ss");
		$admin_google_base_filtered_url->add_parameter("ap", GET, "ap");
		$admin_google_base_filtered_url->add_parameter("s", GET, "s");
		$admin_google_base_filtered_url->add_parameter("s_sti", GET, "s_sti");		

		$t->set_var("admin_google_base_filtered_url", $admin_google_base_filtered_url->get_url());
		$t->set_var("total_filtered", $total_records);
		$t->parse("google_base_filtered", false);
		
		$admin_export_filtered_url = new VA_URL("admin_export.php", true);
		$admin_export_filtered_url->add_parameter("table", CONSTANT, "items");
		if (!strlen(get_param("category_id")))
			$admin_export_filtered_url->add_parameter("category_id", CONSTANT, $category_id);

		$t->set_var("admin_export_filtered_url", $admin_export_filtered_url->get_url());
		$t->set_var("total_filtered", $total_records);
		$t->parse("export_filtered", false);	
  
		if ($approve_products) {
			if (!$empty_export_block) {
				$t->set_var("update_status_br", "<br><br>");
			}
			$approved_options = array(array("", ""), array("1", va_message("IS_APPROVED_MSG")), array("0", va_message("NOT_APPROVED_MSG")));
			for ($i = 0; $i < sizeof($approved_options); $i++) {
				if ($approved_options[$i][0] == $approved_status) {
					$t->set_var("status_id_selected", "selected");
				} else {
					$t->set_var("status_id_selected", "");
				}
				$t->set_var("status_id_value", $approved_options[$i][0]);
				$t->set_var("status_id_description", $approved_options[$i][1]);
				$t->parse("status_id", true);
			}
			$t->parse("update_status", false);
		}
	}

	// BEGIN product privileges changes
	$set_delimiter = false;
	if ($products_export) {
		$t->parse("products_export_priv", false);
		$set_delimiter = true;
	}
	if ($products_import) {
		if ($set_delimiter) {
			$t->set_var("products_import_delimiter", " | ");
		}
		$t->parse("products_import_priv", false);
	}
	if ($products_export_google_base) {
		if ($set_delimiter) {
			$t->set_var("products_export_google_base_delimiter", " | ");
		}
		$t->parse("products_export_google_base_priv", false);
	}
	// END product privileges changes


	// set up search form parameters
	$stock_levels =
		array(
			array("", ""), array(0, va_message("OUTOFSTOCK_PRODUCTS_MSG")), array(1, va_message("INSTOCK_PRODUCTS_MSG"))
		);
	$sales =
		array(
			array("", ""), array(0, va_message("NOT_FOR_SALES_MSG")), array(1, va_message("FOR_SALES_MSG"))
		);
	$aproved_values =
		array(
			array("", ""), array(0, va_message("NO_MSG")), array(1, va_message("YES_MSG"))
		);

	set_options($stock_levels, $sl, "sl");
	set_options($sales, $ss, "ss");
	set_options($aproved_values, $ap, "ap");
	$values_before[] = array("0", va_message("SEARCH_IN_ALL_MSG"));
	if ($category_id != 0) {
		$values_before[] = array($category_id, va_message("SEARCH_IN_CURRENT_MSG"));
	}
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", 
			array(array("", ""), array("all",  va_message("SITES_ALL_MSG")) ));
		set_options($sites, $s_sti, "s_sti");
		$t->parse("sitelist");
	}

	$sql  = " SELECT category_id,category_name ";
	$sql .= " FROM " . $table_prefix . "categories WHERE parent_category_id = " . $db->tosql($category_id, INTEGER);
	$sql .= " ORDER BY category_order ";
	$sc_values = get_db_values($sql, $values_before);
	set_options($sc_values, $sc, "sc");
	$t->set_var("s", htmlspecialchars($s));

	// show search results message
	if (strlen($s)) {
		$found_ads_message = va_message("FOUND_ADS_MSG");
		$found_ads_message = str_replace("{found_records}", $total_records, $found_ads_message);
		$found_ads_message = str_replace("{search_string}", htmlspecialchars($s), $found_ads_message);
		$t->set_var("found_items_message", $found_ads_message);
		$t->parse("found_items", false);
	} else if ($item_index < 1) {
		if (strlen($search)) {
			$t->set_var("found_items_message", va_message("NO_PRODUCTS_MATCH_MSG"));
		} else {
			$t->set_var("found_items_message", va_message("CATEGORY_DOESNT_HAVE_PRODUCTS_MSG"));
		}
		$t->parse("found_items", false);
	}

	if ($category_id || !strlen($s)) {
		// link to change page layout
		$t->parse("custom_layout_link", false);
	} 

	
	$show_items[] = array("","");
	$show_items[] = array(2, va_message("WITHOUT_IMAGES_MSG"));
	set_options($show_items, $sit, "sit");

	$hidden_params["s"] = get_param("s");
	$hidden_params["sl"] = get_param("sl");
	$hidden_params["sc"] = get_param("sc");
	$hidden_params["sort_ord"] = get_param("sort_ord");
	$hidden_params["sort_dir"] = get_param("sort_dir");
	get_query_string($hidden_params, "", "", true);

	if (!$empty_select_block) {
		$t->parse("products_select_block_priv", false);
	}
	if (!$empty_export_approve_block) {
		$t->parse("products_export_block_priv", false);
	}

	$set_delimiter = false;
	if ($categories_export) {
		$t->parse("categories_export_priv", false);
		$set_delimiter = true;
	}
	if ($categories_import) {
		if ($set_delimiter) {
			$t->set_var("categories_import_delimiter", " | ");
		}
		$t->parse("categories_import_priv", false);
	}
	if (!$empty_second_category_block) {
		$t->parse("categories_second_block", false);
	}

	$t->set_var("items_number", $item_index);
	$t->parse("items_block", false);

	$t->pparse("main");

?>