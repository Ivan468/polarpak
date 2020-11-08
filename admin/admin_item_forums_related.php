<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_item_forums_related.php                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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
	check_admin_security("product_related");
	check_admin_security("forum");

	set_session("related_url", "admin_item_forums_related.php");

	$permissions = get_permissions();
	$product_related = get_setting_value($permissions, "product_related", 0);
	$product_accessories = get_setting_value($permissions, "product_accessories", 0);
	$related_forums   = get_setting_value($permissions, "forum", 0);
	$related_articles = get_setting_value($permissions, "articles", 0);
	
	$item_id = get_param("item_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "forums"; }
	
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
	$list->set_branches('forum_list', 'forum_id', 'forum_name');	
	$list->set_topbranches('forum_categories', 'category_id', 'category_name');	
	$list->set_leaves('forum', 'thread_id', 'topic');
	$list->set_actions('selected_related_ids', 'ul', 'leaftostock');
	$list->ajax_listen('products_ajax_tree', 'admin_item_forums_related.php?item_id=' . $item_id, null);
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_item_forums_related.html");

	$t->set_var("admin_item_related_href", "admin_item_related.php");
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
			$db->query("DELETE FROM " . $table_prefix . "items_forum_topics WHERE item_id=" . $item_id);
			for ($i = 0; $i < sizeof($related_ids); $i++) {
				if (strlen($related_ids[$i])) {
					$related_order = $i + 1;
					$sql  = " INSERT INTO " . $table_prefix . "items_forum_topics (item_id, thread_id, thread_order) VALUES (";
					$sql .= $item_id . "," . $db->tosql($related_ids[$i], INTEGER) . "," . $related_order . ")";
					$db->query($sql);
				}
			}
			if ($operation == "save") {
				header("Location: " . $return_page);
				exit;
			}
		}
	}
		
	
	$sql  = " SELECT f.thread_id, f.topic ";
	$sql .= " FROM " . $table_prefix . "forum f ";
	$sql .= " LEFT JOIN " . $table_prefix . "items_forum_topics fr ON fr.thread_id=f.thread_id ";
	$sql .= " WHERE fr.item_id=" . $db->tosql($item_id, INTEGER);
	$sql .= " ORDER BY fr.thread_order, f.topic ";
	$db->query($sql);
	while ($db->next_record())
	{
		$thread_id   = $db->f("thread_id");
		$related_name  = get_translation($db->f("topic"));
		$t->set_var("related_id", $thread_id);
		$t->set_var("related_name", str_replace("\"", "&quot;", $related_name));
		$t->parse("related_items", true);
	}
		
	if ($tab=="general" || $tab == "forums") {
		$list->parse_root_tree('products_ajax_tree', 'admin_item_forums_related.php?item_id='.$item_id, 0, $item_id);
	} elseif ($tab == "full" || $tab == "forums_full") {
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