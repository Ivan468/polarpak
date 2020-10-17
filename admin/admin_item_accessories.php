<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_item_accessories.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
	

	@set_time_limit (900);
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	require_once($root_folder_path . "includes/ajax_list_tree.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("products_categories");
	check_admin_security("product_accessories");
	
	$permissions = get_permissions();

	set_session("related_url", "admin_item_accessories.php");

	$product_related = get_setting_value($permissions, "product_related", 0);
	$product_accessories = get_setting_value($permissions, "product_accessories", 0);
	$related_forums   = get_setting_value($permissions, "forum", 0);
	$related_articles = get_setting_value($permissions, "articles", 0);

	$item_id = get_param("item_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "accessories"; }
	
	$sql  = " SELECT item_name FROM " . $table_prefix . "items ";
	$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$item_name = get_translation($db->f("item_name"));
	} else {
		die(str_replace("{item_id}", $item_id, PRODUCT_ID_NO_LONGER_EXISTS_MSG));
	}
		
	// init ajax tree list and set it as ajax requests listener
	$list = new VA_Ajax_List_Tree($settings["admin_templates_dir"], "ajax_list_tree.html");
	$list->set_branches('categories', 'category_id', 'category_name', 'parent_category_id');
	$list->set_leaves('items', 'item_id', 'item_name', 'items_categories', " (is_draft IS NULL OR is_draft=0) ");
	$list->set_actions('selected_related_ids', 'ul', 'leaftostock');
	$list->ajax_listen('products_ajax_tree', 'admin_item_accessories.php?item_id='.$item_id, $item_id);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_item_accessories.html");

	$t->set_var("admin_item_accessories_href", "admin_item_accessories.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_product_href", "admin_product.php");
	$t->set_var("related_items", "");
	$t->set_var("available_items", "");
	$t->set_var("item_name", $item_name);

	$category_id = get_param("category_id");
	if (!strlen($category_id)) { $category_id = "0"; }

	$operation = get_param("operation");
	$return_page = "admin_items_list.php?category_id=" . $category_id;
	$errors = "";
	
	if ($operation == "cancel") {
		header("Location: " . $return_page);
		exit;
	} elseif ($operation == "save" || $operation == "apply") {
		$related_ids = get_param("related_ids");
		
		if (!strlen($errors))
		{
			$related_ids = explode(",", $related_ids);
			$db->query("DELETE FROM " . $table_prefix . "items_accessories WHERE item_id=" . $item_id);
			for ($i = 0; $i < sizeof($related_ids); $i++) {
				if (strlen($related_ids[$i])) {
					$accessory_order = $i + 1;
					$sql  = " INSERT INTO " . $table_prefix . "items_accessories (item_id, accessory_id, accessory_order) VALUES (";
					$sql .= $item_id . "," . $db->tosql($related_ids[$i], INTEGER) . "," . $accessory_order . ")";
					$db->query($sql);
				}
			}
			if ($operation == "save") {
				header("Location: " . $return_page);
				exit;
			}
		}
	}
		
	
	$sql  = " SELECT ia.accessory_id, i.item_id, i.item_name ";
	$sql .= " FROM " . $table_prefix . "items i ";
	$sql .= " LEFT JOIN " . $table_prefix . "items_accessories ia ON ia.accessory_id=i.item_id ";
	$sql .= " WHERE ia.item_id=" . $db->tosql($item_id, INTEGER);
	$sql .= " ORDER BY ia.accessory_order, i.item_name ";
	$db->query($sql);
	while ($db->next_record())
	{
		$row_item_id   = $db->f("item_id");
		$related_id    = $db->f("accessory_id");
		$related_name  = get_translation($db->f("item_name"));
		$t->set_var("related_id", $row_item_id);
		$t->set_var("related_name", str_replace("\"", "&quot;", $related_name));
		$t->parse("related_items", true);
	}
		
	if ($tab=="general" || $tab == "accessories") {
		$list->parse_root_tree('products_ajax_tree', 'admin_item_accessories.php?item_id='.$item_id, 0, $item_id);
	} elseif ($tab == "full" || $tab == "accessories_full") {
		$list->parse_plain('products_ajax_tree', $item_id);
	}
	
	// set tabs
	$tab_url = new VA_URL("admin_item_related.php", false);
	$tab_url->add_parameter("item_id", REQUEST, "item_id");
	$tab_url->add_parameter("category_id", REQUEST, "category_id");
	$tabs   = array();
	if ($product_related) {
		$tabs["items"] = array(
			"title" => ADMIN_RELATED_PRODUCTS_TITLE,
			"url" => $tab_url->get_url("admin_item_related.php")
		);
	}
	if ($product_accessories) {
		$tabs["accessories"] = array(
			"title" => PROD_ACCESSORIES_MSG,
			"url" => $tab_url->get_url("admin_item_accessories.php")
		);
	}
	if ($related_articles) {
		$tabs["articles"] = array(
			"title" => RELATED_ARTICLES_MSG,
			"url" => $tab_url->get_url("admin_item_articles_related.php")
		);
	}	
	if ($related_forums) {
		$tabs["forums"] = array(
			"title" => RELATED_FORUMS_MSG,
			"url" => $tab_url->get_url("admin_item_forums_related.php")
		);
	}
	foreach ($tabs as $tab_name => $tab_data) {
		$t->set_var("tab_id", "tab_" . $tab_name);
		$t->set_var("tab_title", $tab_data["title"]);
		$t->set_var("tab_url", $tab_data["url"]);
		if ($tab_name == $tab) {
			$t->set_var("tab_class", "adminTabActive");
			$t->set_var($tab_name . "_style", "display: block;");
		} else {
			$t->set_var("tab_class", "adminTab");
			$t->set_var($tab_name . "_style", "display: none;");
		}
		$t->parse("tabs");
	}
	
	if (strlen($errors))	{
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}	else {
		$t->set_var("errors", "");
	}

	$t->set_var("item_id", $item_id);
	$t->set_var("category_id", $category_id);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>