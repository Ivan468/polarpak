<?php

	$default_title = "{SITE_MAP_TITLE}";

	$site_map_tree = array();
	
	$sitemap_settings           = get_settings("site_map");
	$site_map_custom_pages      = get_setting_value($sitemap_settings, "site_map_custom_pages");
	$site_map_categories        = get_setting_value($sitemap_settings, "site_map_categories");
	$site_map_items             = get_setting_value($sitemap_settings, "site_map_items");
	$site_map_forum_categories  = get_setting_value($sitemap_settings, "site_map_forum_categories");
	$site_map_forums            = get_setting_value($sitemap_settings, "site_map_forums");		
	$site_map_ad_categories     = get_setting_value($sitemap_settings, "site_map_ad_categories");
	$site_map_ads               = get_setting_value($sitemap_settings, "site_map_ads");
	$site_map_manual_categories = get_setting_value($sitemap_settings, "site_map_manual_categories");
	$site_map_manual_articles   = get_setting_value($sitemap_settings, "site_map_manual_articles");
	$site_map_manuals           = get_setting_value($sitemap_settings, "site_map_manuals");
	$site_map_manufacturers     = get_setting_value($sitemap_settings, "site_map_manufacturers");

	$site_url = get_setting_value($settings, "site_url");
	$friendly_urls      = get_setting_value($settings, "friendly_urls");
	$friendly_extension = get_setting_value($settings, "friendly_extension");
	
	include_once("./messages/" . $language_code . "/manuals_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/articles_functions.php");
	include_once("./includes/forums_functions.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/manuals_functions.php");

	$current_id = get_param("id");
	$ajax = get_param("ajax");
	$type = get_param("type");
	$level = get_param("level");
	$user_id      = get_session("session_user_id");
	$user_type_id = get_session("session_user_type_id");

	$t->set_file("block_body", "block_site_map.html");
	$t->set_var("pb_id", $pb_id);


	if(!$ajax){
		// set necessary scripts
		set_script_tag("js/ajax.js");

		// start custom pages site map
		if ($site_map_custom_pages) {
			$t->set_var("data_type", "custom_pages");
			$custom_pages[0]["title"] = MAIN_PAGES_MSG;
			$custom_pages[0]["url"] = $site_url;
			$custom_pages[0]["subs_number"] = 1;
			$custom_pages["top"]["subs"][0] = 1;
			set_tree($custom_pages, "top", 0, 0);
			$t->parse("site_maps", true);
		} // end of custom pages site map

		// start products site map
		if ($site_map_categories) {
			$tree = array();
			$t->set_var("data_type", "products");
			$tree[0]["title"] = PRODUCTS_TITLE;
			$tree[0]["url"] = get_custom_friendly_url("products_list.php");
			$tree[0]["subs_number"] = 1;
			$tree["top"]["subs"][0] = 1;
			set_tree($tree, "top", 0, 0);
			$t->parse("site_maps", true);
		} // end products site map


		// check article TOP categories
		$articles_categories = array();
		$sql_data = array(
			"select" => "c.category_id, c.category_name, c.friendly_url, c.category_order ",
			"where" => " c.parent_category_id=0 ",
			"order" => " c.category_order, c.category_name",
			"access_field" => true,
		);
		$sql = VA_Articles_Categories::sql($sql_data, VIEW_CATEGORIES_PERM);
		$db->query($sql);
		while ($db->next_record()) {
			$category_id = $db->f("category_id");
			$show_categories = get_setting_value($sitemap_settings, "site_map_articles_categories_" . $category_id);
			$show_articles   = get_setting_value($sitemap_settings, "site_map_articles_" . $category_id);
			if ($show_categories || $show_articles) {
				$category_name = get_translation($db->f("category_name"));
				$friendly_url = $db->f("friendly_url");
				if ($friendly_urls && $friendly_url) {
					$category_url = $friendly_url.$friendly_extension;
				} else {
					$category_url = "articles.php?category_id=".$category_id;
				}
				$tree = array();
				$t->set_var("data_type", "articles");
				$tree[$category_id]["title"] = $category_name;
				$tree[$category_id]["url"] = $category_url;
				$tree[$category_id]["subs_number"] = 1;
				$tree[0]["subs"][$category_id] = 1;
				set_tree($tree, 0, 0, 0);
				$t->parse("site_maps", true);
			}
		}
		// end check article TOP categories

		// start forum site map
		if ($site_map_forum_categories) {
			$tree = array();
			$t->set_var("data_type", "forum");
			$tree[0]["title"] = FORUM_TITLE;
			$tree[0]["url"] = get_custom_friendly_url("forums.php");
			$tree[0]["subs_number"] = 1;
			$tree["top"]["subs"][0] = 1;
			set_tree($tree, "top", 0, 0);
			$t->parse("site_maps", true);
		} // end forum site map

	} else {
		// Ajax calls to load tree branch

		// start custom pages site map
		if ($site_map_custom_pages && $type == "custom_pages") {
			$t->set_var("data_type", "custom_pages");

			$custom_pages = array();
			$sql  = " SELECT p.page_id, p.page_order, p.page_code, p.page_title, p.page_url, p.friendly_url FROM ";
			if (isset($site_id)) {
				$sql .= "(";
			}
			if (strlen($user_id)) {
				$sql .= "(";
			}
			$sql .= $table_prefix . "pages p ";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "pages_sites s ON (s.page_id=p.page_id AND p.sites_all=0)) ";
			}
			if (strlen($user_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "pages_user_types ut ON (ut.page_id=p.page_id AND p.user_types_all=0)) ";
			}
			$sql .= " WHERE p.is_showing=1 AND p.is_site_map=1 ";
			if (isset($site_id)) {
				$sql .= " AND (p.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
			} else {
				$sql .= " AND p.sites_all=1";
			}
			if (strlen($user_id)) {
				$sql .= " AND (p.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id, INTEGER) . ") ";
			} else {
				$sql .= " AND p.user_types_all=1 ";
			}
			$sql .= " ORDER BY p.page_order, p.page_title ";			
			$db->query($sql);
			$is_custom_pages = false;
			while ($db->next_record()) {
				$is_custom_pages = true;
				$page_id = $db->f("page_id");
				$page_order = $db->f("page_order");
				$page_title = get_translation($db->f("page_title"));

				if ($db->f("friendly_url") && $friendly_urls) {
					$item_url = $db->f("friendly_url") . $friendly_extension;
				} elseif ($db->f('page_url')) {
					$item_url = $db->f("page_url");
				} else {
					$item_url = "page.php?page=" . $db->f("page_code");
				}	

				$custom_pages[$page_id]["title"] = $page_title;
				$custom_pages[$page_id]["class"] = "node-leaf";
				$custom_pages[$page_id]["url"] = $item_url;
				$custom_pages[0]["subs"][$page_id] = $page_order;
			}

			$current_id = 0; $start_level = 1;
			set_tree($custom_pages, $current_id, $start_level, "");
			$t->set_var("category_id", $current_id);
			$t->set_var("subnodes", $t->get_var("subnodes_" .$start_level));
			$t->pparse("subnodes_block");
			exit;
		} // end of custom pages site map

		// start products site map
		if ($site_map_categories && $type == "products") {
			$t->set_var("data_type", "products");

			$categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($current_id, INTEGER), VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($current_id, INTEGER), VIEW_CATEGORIES_ITEMS_PERM);

			$categories = array(); $max_category_order = 0;
			if (count($categories_ids)) {
				$sql  = " SELECT c.category_id, c.category_order, c.category_name, c.a_title, c.friendly_url, ";
				$sql .= " c.short_description, c.image, c.image_alt, c.image_large, c.image_large_alt, c.parent_category_id ";		
				$sql .= ", sc.subs_number ";
				$sql .= " FROM " . $table_prefix . "categories c ";
				$sql .= " LEFT JOIN (";
				$sql .= " SELECT parent_category_id, COUNT(*) AS subs_number ";
				$sql .= " FROM " . $table_prefix . "categories ";
				$sql .= " WHERE parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
				$sql .= " GROUP BY parent_category_id ";
				$sql .= " ) sc ON sc.parent_category_id=c.category_id ";
				$sql .= " WHERE c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY c.category_order, c.category_name ";
				$db->query($sql);
				while ($db->next_record()) {
					$cur_category_id = $db->f("category_id");
					$category_order = $db->f("category_order");
					if ($category_order > $max_category_order) { $max_category_order = $category_order; }
					$category_name = get_translation($db->f("category_name"));
					$a_title = get_translation($db->f("a_title"));
					$friendly_url = $db->f("friendly_url");
					$subs_number = $db->f("subs_number");
	    
					if ($friendly_urls && $friendly_url) {
						$category_url = $friendly_url.$friendly_extension;
					} else {
						$category_url = "products_list.php?category_id=".$cur_category_id;
					}
	    
					$parent_category_id = $db->f("parent_category_id");
					$categories[$cur_category_id]["parent_id"] = $parent_category_id;
					$categories[$cur_category_id]["title"] = $category_name;
					$categories[$cur_category_id]["url"] = $category_url;
					if ($subs_number || $site_map_items) {
						$categories[$cur_category_id]["class"] = "node-childs";
					}
					//$categories[$cur_category_id]["subs_number"] = $subs_number;
	    
					if (!$allowed_categories_ids || !in_array($cur_category_id, $allowed_categories_ids)) {
						$categories[$cur_category_id]["allowed"] = false;
					} else {
						$categories[$cur_category_id]["allowed"] = true;
					}
					$categories[$parent_category_id]["subs"][$cur_category_id] = $category_order;
				}
			}

			if($site_map_items) {
				$sql_data = array();
				$sql_data["select"] = "i.item_id, i.item_name, i.friendly_url, ic.item_order ";
				$sql_data["join"] = " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id ";		
				$sql_data["where"] = "ic.category_id = " . $db->tosql($current_id, INTEGER);
				$sql_data["access_field"] = true;
				$sql = VA_Products::sql($sql_data, VIEW_CATEGORIES_ITEMS_PERM);
				$db->query($sql);
				while ($db->next_record()) {
					$item_id = $db->f("item_id");
					$node_key = "product".$item_id;
					$item_name = get_translation($db->f("item_name"));
					$friendly_url = $db->f("friendly_url");
					$item_order = $max_category_order + $db->f("item_order");
					$user_access_level = $db->f("user_access_level");
					if ($friendly_urls && $friendly_url) {
						$item_url = $friendly_url.$friendly_extension;
					} else {
						$item_url = "product_details.php?item_id=".$item_id;
					}

					$categories[$node_key]["parent_id"] = $current_id;
					$categories[$node_key]["title"] = $item_name;
					$categories[$node_key]["class"] = "node-leaf";
					$categories[$node_key]["url"] = $item_url;
					if ($user_access_level&VIEW_ITEMS_PERM) {
						$categories[$node_key]["allowed"] = true;
					} else {
						$categories[$node_key]["allowed"] = false;
					}
					$categories[$current_id]["subs"][$node_key] = $item_order;
				}
			}

			if (!count($categories)) {
				$node_key = "no-data";
				$categories[$node_key]["parent_id"] = $current_id;
				$categories[$node_key]["title"] = NO_DATA_WERE_FOUND_MSG;
				$categories[$node_key]["class"] = "node-no-data";
				$categories[$node_key]["url"] = "javascript:void(0)";
				$categories[$current_id]["subs"][$node_key] = 1;
			}

			$start_level = 1;
			set_tree($categories, $current_id, $start_level, "");
			$t->set_var("subnodes", $t->get_var("subnodes_" .$start_level));
			$t->pparse("subnodes_block");
			exit;
		}// end products site map

		// start articles site map
		if ($type == "articles") {
			$t->set_var("data_type", "articles");

			// check top_id 
			$top_id = VA_Articles_Categories::top_id($current_id);
			$show_categories = get_setting_value($sitemap_settings, "site_map_articles_categories_" . $top_id);
			$show_articles   = get_setting_value($sitemap_settings, "site_map_articles_" . $top_id);
			if (!$show_categories && !$show_articles) {
				echo NO_DATA_WERE_FOUND_MSG;
				exit;
			}

			// check article TOP category
			$tree = array(); $categories_ids = array(); $max_category_order = 0;
			$sql_data = array(
				"select" => "c.category_id, c.category_name, c.friendly_url, c.category_order ",
				"from" => $table_prefix . "articles_categories c ",
				"where" => " c.parent_category_id=" . $db->tosql($current_id, INTEGER),
				"access_field" => true,
			);
			$sql = VA_Articles_Categories::sql($sql_data, VIEW_CATEGORIES_PERM);
			$db->query($sql);
			while ($db->next_record()) {
				$category_id = $db->f("category_id");
				$categories_ids[] = $category_id;
				$category_order = $db->f("category_order");
				if ($category_order > $max_category_order) { $max_category_order = $category_order; }
				$category_name = get_translation($db->f("category_name"));
				$friendly_url = $db->f("friendly_url");
				$user_access_level = $db->f("user_access_level");
	    
				if ($friendly_urls && $friendly_url) {
					$category_url = $friendly_url.$friendly_extension;
				} else {
					$category_url = "articles.php?category_id=".$category_id;
				}

				$tree[$category_id]["parent_id"] = $current_id;
				$tree[$category_id]["title"] = $category_name;
				$tree[$category_id]["url"] = $category_url;
				if ($user_access_level&VIEW_CATEGORIES_ITEMS_PERM) {
					$tree[$category_id]["allowed"] = true;
				} else {
					$tree[$category_id]["allowed"] = false;
				}
				if ($show_articles) {
					$tree[$category_id]["class"] = "node-childs";
				}
				$tree[$current_id]["subs"][$category_id] = $category_order;
			}

			// check number of subcategories
			if (!$show_articles && count($categories_ids)) {
				$sql_data = array(
					"select" => "c.parent_category_id, COUNT(*) AS subs_number",
					"from" => $table_prefix . "articles_categories c ",
					"where" => " c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")",
					"group" => " c.parent_category_id ",
				);
				$sql = VA_Articles_Categories::sql($sql_data, VIEW_CATEGORIES_PERM);
				$db->query($sql);
				while ($db->next_record()) {
					$category_id = $db->f("parent_category_id");
					$subs_number = $db->f("subs_number");
					if ($subs_number) {
						$tree[$category_id]["class"] = "node-childs";
					}
				}
			}

			if($show_articles) {
				$sql_data = array();
				$sql_data["select"] = "a.article_id, a.article_title, a.friendly_url, aa.article_order ";
				$sql_data["where"] = "aa.category_id = " . $db->tosql($current_id, INTEGER);
				$sql_data["access_field"] = true;
				$sql = VA_Articles::sql($sql_data, VIEW_CATEGORIES_ITEMS_PERM);
				$db->query($sql);
				while ($db->next_record()) {
					$article_id = $db->f("article_id");
					$node_key = "article".$article_id;
					$article_title = get_translation($db->f("article_title"));
					$friendly_url = $db->f("friendly_url");
					$article_order = $max_category_order + $db->f("article_order");
					$user_access_level = intval($db->f("user_access_level"));
					if ($friendly_urls && $friendly_url) {
						$article_url = $friendly_url.$friendly_extension;
					} else {
						$article_url = "article.php?article_id=".$article_id;
					}

					$tree[$node_key]["parent_id"] = $current_id;
					$tree[$node_key]["title"] = $article_title;
					$tree[$node_key]["class"] = "node-leaf";
					$tree[$node_key]["url"] = $article_url;
					if ($user_access_level&VIEW_ITEMS_PERM) {
						$tree[$node_key]["allowed"] = true;
					} else {
						$tree[$node_key]["allowed"] = false;
					}
					$tree[$current_id]["subs"][$node_key] = $article_order;
				}
			}

			if (!count($tree)) {
				$node_key = "no-data";
				$tree[$node_key]["parent_id"] = $current_id;
				$tree[$node_key]["title"] = NO_DATA_WERE_FOUND_MSG;
				$tree[$node_key]["class"] = "node-no-data";
				$tree[$node_key]["url"] = "javascript:void(0)";
				$tree[$current_id]["subs"][$node_key] = 1;
			}

			$start_level = 1;
			set_tree($tree, $current_id, $start_level, "");
			$t->set_var("subnodes", $t->get_var("subnodes_" .$start_level));
			$t->pparse("subnodes_block");
			exit;
		}// end articles site map


		// start forum site map
		if ($site_map_forum_categories && $type == "forum") {
			$t->set_var("data_type", "forum");

			$tree = array(); $max_category_order = 0;
			if ($current_id == 0) {
				$sql_data = array("select" => "c.category_id, c.category_name, c.category_order, c.friendly_url");
				$sql = VA_Forum_Categories::sql($sql_data);
				$db->query($sql);
				while ($db->next_record()) {

					$category_id = $db->f("category_id");
					$category_order = $db->f("category_order");
					if ($category_order > $max_category_order) { $max_category_order = $category_order; }
					$category_name = get_translation($db->f("category_name"));
					$friendly_url = $db->f("friendly_url");
	    
					if ($friendly_urls && $friendly_url) {
						$category_url = $friendly_url.$friendly_extension;
					} else {
						$category_url = "forums.php?category_id=".$category_id;
					}
	    
					$tree[$category_id]["parent_id"] = $current_id;
					$tree[$category_id]["title"] = $category_name;
					$tree[$category_id]["url"] = $category_url;
					if ($site_map_forums) {
						$tree[$category_id]["class"] = "node-childs";
					}
					$tree[$current_id]["subs"][$category_id] = $category_order;
				}
			}

			if ($site_map_forums && $current_id) {
				$sql_data = array(
					"select" => "fl.forum_id, fl.forum_name, fl.forum_order, fl.friendly_url",
					"where" => " fl.category_id=" . $db->tosql($current_id, INTEGER),
					"access_field" => true
				);
				$sql = VA_Forums::sql($sql_data, VIEW_FORUM_PERM);
				$db->query($sql);
				while ($db->next_record()) {
					$forum_id = $db->f("forum_id");
					$node_key = "forum".$forum_id;
					$forum_name = get_translation($db->f("forum_name"));
					$friendly_url = $db->f("friendly_url");
					$forum_order = $max_category_order + $db->f("forum_order");
					$user_access_level = $db->f("user_access_level");
					if ($friendly_urls && $friendly_url) {
						$forum_url = $friendly_url.$friendly_extension;
					} else {
						$forum_url = "forum.php?forum_id=".$forum_id;
					}

					$tree[$node_key]["parent_id"] = $current_id;
					$tree[$node_key]["title"] = $forum_name;
					$tree[$node_key]["class"] = "node-leaf";
					$tree[$node_key]["url"] = $forum_url;
					if ($user_access_level&VIEW_TOPICS_PERM) {
						$tree[$node_key]["allowed"] = true;
					} else {
						$tree[$node_key]["allowed"] = false;
					}
					$tree[$current_id]["subs"][$node_key] = $forum_order;

				}
			}

			if (!count($tree)) {
				$node_key = "no-data";
				$tree[$node_key]["parent_id"] = $current_id;
				$tree[$node_key]["title"] = NO_DATA_WERE_FOUND_MSG;
				$tree[$node_key]["class"] = "node-no-data";
				$tree[$node_key]["url"] = "javascript:void(0)";
				$tree[$current_id]["subs"][$node_key] = 1;
			}

			$start_level = 1;
			set_tree($tree, $current_id, $start_level, "");
			$t->set_var("subnodes", $t->get_var("subnodes_" .$start_level));
			$t->pparse("subnodes_block");
			exit;
		}

	}


/*

		$articles_total_records = array();
		$articles_top_categories =  VA_Articles_Categories::find_all("c.category_id", 
			array("c.category_name", "c.friendly_url"),
			array(
				"where" => " c.parent_category_id=0 ",
				"order" => " ORDER BY c.category_order, c.category_name"
			)
		);

		

		if($site_map_ad_categories){
			$is_categories = false;
			$found_categories = VA_Ads_Categories::find_all("c.category_id", 
				array("c.category_name", "c.parent_category_id", "c.friendly_url"),
				array("order" => " ORDER BY c.category_order, c.category_name",
					"where" => "c.parent_category_id = ".$db->tosql(0, INTEGER)
				)
			);
			if(sizeof($found_categories)){
				$is_categories = true;
			}
			if($site_map_ads){
				$found_ads = VA_Ads::find_all("", 
					array("i.item_id", "i.item_title", "i.friendly_url"),
					array("order" => " ORDER BY i.item_order, i.item_title",
						"where" => " ac.category_id = " . $db->tosql(0, INTEGER),
					)
				);
				if(sizeof($found_ads)){
					$is_categories = true;
				}
			}

			if($is_categories){
				$t->set_var("tree_type", "ads");
				$t->parse("tree_js");
				$t->set_var("type", "ads");
				$t->set_var("sub_class", "ExpandClosed");
				$t->set_var("item_name", ADS_TITLE);
				$t->set_var("item_url", get_custom_friendly_url("ads.php"));
				$t->set_var("categori_id", 0);
				$t->parse("item");
			}
		}

		if($site_map_manual_categories){
			$is_categories = false;
			$found_categories = VA_Manuals_Categories::find_all("c.category_id", 
				array("c.category_name", "c.friendly_url"),
				array("order" => " ORDER BY c.category_order, c.category_name")
			);
			if(sizeof($found_categories)){
				$is_categories = true;
			}
			if($is_categories){
				$t->set_var("tree_type", "manuals");
				$t->parse("tree_js");
				$t->set_var("type", "manuals");
				$t->set_var("sub_class", "ExpandClosed");
				$t->set_var("item_name", MANUALS_TITLE);
				$t->set_var("item_url", get_custom_friendly_url("manuals.php"));
				$t->set_var("categori_id", 0);
				$t->parse("item");
			}
		}
		if($site_map_manufacturers){
			$is_categories = false;
			$sql  = " SELECT manufacturer_id, manufacturer_name, friendly_url ";
			$sql .=	" FROM " . $table_prefix . "manufacturers  ";
			$db->query($sql);
			if($db->next_record()){
				$is_categories = true;
			}
			if($is_categories){
				$t->set_var("tree_type", "manufacturers");
				$t->parse("tree_js");
				$t->set_var("type", "manufacturers");
				$t->set_var("sub_class", "ExpandClosed");
				$t->set_var("item_name", MANUFACTURERS_TITLE);
				$t->set_var("item_url", get_custom_friendly_url("site_map.php"));
				$t->set_var("categori_id", 0);
				$t->parse("item");
			}
		}

		$t->set_var("navigator_block", "");
		$t->parse("top_element");
// ------------------------------- END main -------------------------------
	} else {
		$type_array = explode("_", $type);
		$type = $type_array[0];
		$type_id = (isset($type_array[1]))? $type_array[1]: "";
		$parent_array = explode("_", $parent_id);
		$parent_id = $parent_array[0];
		$sub_parent_id = (isset($parent_array[1]))? $parent_array[1]: "";

		if($type == "ads" && $site_map_ad_categories){
			$found_categories = VA_Ads_Categories::find_all("c.category_id", 
				array("c.category_name", "c.parent_category_id", "c.friendly_url"),
				array("order" => " ORDER BY c.category_order, c.category_name",
					"where" => "c.parent_category_id = ".$db->tosql($parent_id, INTEGER)
				)
			);
			$category_url_prefix = "ads.php?category_id=";
			foreach ($found_categories AS  $category_id => $category) {
				$the_expand = "ExpandLeaf";
				$found_sub_categories = VA_Ads_Categories::find_all("c.category_id", 
					array("c.category_name", "c.parent_category_id", "c.friendly_url"),
					array("order" => " ORDER BY c.category_order, c.category_name",
						"where" => "c.parent_category_id = ".$db->tosql($category_id, INTEGER)
					)
				);
				if(sizeof($found_sub_categories)){
					$the_expand = "ExpandClosed";
				}
				if($site_map_ads){
					$found_ads = VA_Ads::find_all("", 
						array("i.item_id", "i.item_title", "i.friendly_url"),
						array("order" => " ORDER BY i.item_order, i.item_title",
							"where" => " ac.category_id = " . $db->tosql($category_id, INTEGER),
						)
					);
					if(sizeof($found_ads)){
						$the_expand = "ExpandClosed";
					}
				}
				if ($category['c.friendly_url'] && $friendly_urls) {
					$item_url = $category['c.friendly_url'] . $friendly_extension;
				} else {
					$item_url = "ads.php?category_id=" . $category_id;
				}
				$t->set_var("categori_id",  $category_id);
				$t->set_var("sub_class", $the_expand);
				$t->set_var("item_url",  $settings["site_url"] . $item_url);
				$t->set_var("item_name", get_translation($category['c.category_name']));
				$t->parse("sub_element");
			}
			if($site_map_ads){
				$found_ads = VA_Ads::find_all("", 
					array("i.item_id", "i.item_title", "i.friendly_url"),
					array("order" => " ORDER BY i.item_order, i.item_title",
						"where" => " ac.category_id = " . $db->tosql($parent_id, INTEGER),
					)
				);
				foreach ($found_ads AS  $ad_id => $ad) {
					if ($ad['i.friendly_url'] && $friendly_urls) {
						$item_url = $ad['i.friendly_url'] . $friendly_extension;
					} else {
						$item_url = "ads_details.php?item_id=" . $ad['i.item_id'];
					}

					$t->set_var("categori_id",  $ad['i.item_id']);
					$t->set_var("sub_class", "ExpandLeaf");
					$t->set_var("item_url",  $settings["site_url"] . $item_url);
					$t->set_var("item_name", get_translation($ad['i.item_title']));
					$t->parse("sub_element");
				}

			}

		}
		if($type == "manuals" && $site_map_manual_categories){
			if(!$parent_id){
				$found_categories = VA_Manuals_Categories::find_all("c.category_id", 
					array("c.category_name", "c.friendly_url"),
					array("order" => " ORDER BY c.category_order, c.category_name")
				);
				foreach ($found_categories AS  $category_id => $category) {
					$the_expand = "ExpandLeaf";
					if($site_map_manual_articles){
						$found_items = VA_Manuals::find_all("", 
							array("ml.manual_id", "ml.manual_title", "ml.friendly_url", "c.category_id"),
							array(
								"where" => " c.category_id = " . $db->tosql($category_id, INTEGER),
								"order" => " ORDER BY ml.manual_order, ml.manual_title"
							)
						);
						if(sizeof($found_items)){
							$the_expand = "ExpandClosed";
						}
					}
					if ($category['c.friendly_url'] && $friendly_urls) {
						$item_url = $category['c.friendly_url'] . $friendly_extension;
					} else {
						$item_url = "manuals.php?category_id=" . $category_id;
					}

					$t->set_var("categori_id",  $category_id);
					$t->set_var("sub_class", $the_expand);
					$t->set_var("item_url",  $settings["site_url"] . $item_url);
					$t->set_var("item_name", get_translation($category['c.category_name']));
					$t->parse("sub_element");
				}
			}elseif($site_map_manual_articles && !$sub_parent_id){
				$found_items = VA_Manuals::find_all("", 
					array("ml.manual_id", "ml.manual_title", "ml.friendly_url", "c.category_id"),
					array(
						"where" => " c.category_id = " . $db->tosql($parent_id, INTEGER),
						"order" => " ORDER BY ml.manual_order, ml.manual_title"
					)
				);
				foreach ($found_items AS  $item_id => $item) {
					$the_expand = "ExpandLeaf";
					if($site_map_manual_articles){
						$sql_manual  = " SELECT article_id, article_title, parent_article_id, friendly_url, manual_id";
						$sql_manual .= " FROM " . $table_prefix . "manuals_articles ";
						$sql_manual .= " WHERE allowed_view=1";
						$sql_manual .= " AND manual_id =" . $db->tosql($item['ml.manual_id'], INTEGER);
						$sql_manual .= " ORDER BY article_order, article_title ";
						$db->query($sql_manual);
						if ($db->next_record()) {
							$the_expand = "ExpandClosed";
						}
					}
					if ($item['ml.friendly_url'] && $friendly_urls) {
						$item_url = $item['ml.friendly_url'] . $friendly_extension;
					} else {
						$item_url = "manuals_articles.php?manual_id=" . $item['ml.manual_id'];
					}

					$t->set_var("categori_id",  $parent_id."_".$item['ml.manual_id']);
					$t->set_var("sub_class", $the_expand);
					$t->set_var("item_url",  $settings["site_url"] . $item_url);
					$t->set_var("item_name", get_translation($item['ml.manual_title']));
					$t->parse("sub_element");
				}
			}
			if($site_map_manual_articles && $site_map_manuals && $sub_parent_id){
				$sql_manual  = " SELECT article_id, article_title, parent_article_id, friendly_url, manual_id";
				$sql_manual .= " FROM " . $table_prefix . "manuals_articles ";
				$sql_manual .= " WHERE allowed_view=1";
				$sql_manual .= " AND manual_id =" . $db->tosql($sub_parent_id, INTEGER);
				$sql_manual .= " ORDER BY article_order, article_title ";
				$db->query($sql_manual);
				while ($db->next_record()) {
					$article_id         = $db->f("article_id");
					$parent_article_id  = $db->f("parent_article_id");
					$friendly_url       = $db->f("friendly_url");
					$article_title      = $db->f("article_title");
					if ($friendly_url && $friendly_urls) {
						$item_url = $friendly_url . $friendly_extension;
					} else {
						$item_url = "manuals_article_details.php?article_id=" . $article_id;
					}

					$t->set_var("categori_id",  $article_id);
					$t->set_var("sub_class", "ExpandLeaf");
					$t->set_var("item_url",  $settings["site_url"] . $item_url);
					$t->set_var("item_name", get_translation($article_title));
					$t->parse("sub_element");
				}
			}
		}
		if($type == "manufacturers" && $site_map_manufacturers){
			$sql  = " SELECT manufacturer_id, manufacturer_name, friendly_url ";
			$sql .=	" FROM " . $table_prefix . "manufacturers  ";
			$sql .= " ORDER BY manufacturer_order ";
			$sql .= " , manufacturer_name ";
			$db->query($sql);
			$i = 0;
			while ($db->next_record()) {
				$i++;
				$manufacturer_id         = $db->f("manufacturer_id");
				$manufacturer_name  = $db->f("manufacturer_name");
				$friendly_url       = $db->f("friendly_url");
				if ($friendly_url && $friendly_urls) {
					$item_url = $friendly_url . $friendly_extension;
				} else {
					$item_url = "products_list.php?manf=" . $manufacturer_id;
				}

//				$t->set_var("categori_id",  $manufacturer_id);
//				$t->set_var("sub_class", "ExpandLeaf");
				$t->set_var("item_url",  $settings["site_url"] . $item_url);
				$t->set_var("item_name", get_translation($manufacturer_name));
				if($i%3){
					$t->parse("item_element");
				}else{
					$i=0;
					$t->parse("item_element");
					$t->parse("row_element");
					$t->set_var("item_element", "");
				}
			}
			while($i%3){
				$i++;
				if($i%3){
					$t->set_var("item_url",  "");
					$t->set_var("item_name", "");
					$t->parse("item_element");
				}else{
					$t->set_var("item_url",  "");
					$t->set_var("item_name", "");
					$t->parse("item_element");
					$t->parse("row_element");
					$t->set_var("item_element", "");
				}
			}
			$t->parse("manufacturers_element");
		}

	}
//*/

	$block_parsed = true;
?>