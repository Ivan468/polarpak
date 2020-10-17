<?php                           

	$erase_tags = false;

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_ads_breadcrumb.html"); 
  $t->set_file("block_body", $html_template);

	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	}
	$user = get_param("user");

	$item_id = get_param("item_id");
	if (!strlen($category_id) && strlen($item_id)) {
		$category_id = VA_Ads::get_category_id($item_id, VIEW_ITEMS_PERM);
	}

	$t->set_var("index_href", get_custom_friendly_url("index.php"));

	$breadcrumbs_tree_array = array();
	
	if ($category_id) {
		$current_id = $category_id;
		$sql  = " SELECT c.category_id, c.category_name, c.friendly_url, c.parent_category_id";
		if (isset($site_id))  {
			$sql .= " FROM (" . $table_prefix . "ads_categories c";
			$sql .= " LEFT JOIN " . $table_prefix . "ads_categories_sites cs ON cs.category_id=c.category_id)";
			$sql .= " WHERE (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ")";
		} else {
			$sql .= " FROM " . $table_prefix . "ads_categories c";
			$sql .= " WHERE c.sites_all=1";					
		}	
		$sql .= " AND c.category_id=";
		while ($current_id)
		{
			$db->query($sql . $db->tosql($current_id, INTEGER));
			if ($db->next_record()) {
				$category_name = $db->f("category_name");
				$category_name = get_translation($category_name);
				$friendly_url = $db->f("friendly_url");
				if ($friendly_urls && $friendly_url) {
					$tree_url = $friendly_url . $friendly_extension;
				} else {
					$tree_url = "ads.php?category_id=". $current_id;
				}
				$tree_title = $category_name;
				if ($erase_tags) { $tree_title = strip_tags($tree_title); }
				array_unshift($breadcrumbs_tree_array, array($tree_url, $tree_title));
				$current_id= $db->f("parent_category_id");
			} else {
				$current_id = "0";
			}
		}
	}

	$tree_title = va_message("ADS_TITLE");
	if ($erase_tags) { $tree_title = strip_tags($tree_title); }
	array_unshift($breadcrumbs_tree_array, array(get_custom_friendly_url("ads.php"), $tree_title));

	if (strlen($user)) {
		$sql = "SELECT login, name, first_name, last_name FROM " . $table_prefix . "users WHERE user_id=" . $db->tosql($user, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$name = $db->f("name");
			$login = $db->f("login");
			$first_name = $db->f("first_name");
			$last_name = $db->f("last_name");
			if (strlen($name)) {
				$user_name = $name;
			} elseif (strlen($first_name) || strlen($last_name)) {
				$user_name = $first_name . " " . $last_name;
			} else {
				$user_name = $login;
			}

			$tree_url = "ads.php?category_id=" . urlencode($category_id) . "&user=" . urlencode($user);
			$tree_title = $user_name;
			if ($erase_tags) { $tree_title = strip_tags($tree_title); }
			$breadcrumbs_tree_array[] = array($tree_url, htmlspecialchars($tree_title));
		}
	}

	if (strlen($item_id)) {
		$sql = "SELECT item_title, friendly_url FROM " . $table_prefix . "ads_items WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$item_title = get_translation($db->f("item_title"));
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$tree_url = $friendly_url . $friendly_extension;
				if ($category_id) {
					$tree_url .= "?category_id=" . urlencode($category_id);
				}
			} elseif ($category_id) {
				$tree_url = "ads_details.php?category_id=" . urlencode($category_id) . "&item_id=" . urlencode($item_id);
			} else {
				$tree_url = "ads_details.php?item_id=" . urlencode($item_id);
			}
			$tree_title = $item_title;
			if ($erase_tags) { $tree_title = strip_tags($tree_title); }
			$breadcrumbs_tree_array[] = array($tree_url, htmlspecialchars($tree_title));
		}
	}
	
	$ic = count($breadcrumbs_tree_array) - 1;
	for ($i=0; $i<$ic; $i++) {
		$t->set_var("tree_url", htmlspecialchars($breadcrumbs_tree_array[$i][0]));
		$t->set_var("tree_title", htmlspecialchars($breadcrumbs_tree_array[$i][1]));
		$t->set_var("tree_class", "");
		$t->parse("tree", true);
	}
	if ($ic>=0) {
		$t->set_var("tree_url", htmlspecialchars($breadcrumbs_tree_array[$ic][0]));
		$t->set_var("tree_title", htmlspecialchars($breadcrumbs_tree_array[$ic][1]));
		$t->set_var("tree_class", "treeItemLast");
		$t->parse("tree", true);
	}

	if(!$layout_type) { $layout_type = "bb"; }
	$block_parsed = true;

