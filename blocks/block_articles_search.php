<?php

	include_once("./includes/articles_functions.php");

	$default_title = "{search_name} &nbsp; {SEARCH_TITLE}";
	
	// check if top_id is a parent of category_id parameter 
	$top_id = $block["block_key"];
	$category_id = get_param("category_id");
	$articles_category_id = ""; $articles_top_name = "";
	if (($cms_page_code == "articles_list" || $cms_page_code == "article_details" || $cms_page_code == "article_reviews") 
		&& $category_id && $top_id != $category_id) {
		$sql  = " SELECT category_path FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$category_path = get_db_value($sql);
		$category_ids = explode(",", $category_path);
		if (in_array($top_id, $category_ids)) {
			$articles_category_id = $category_id;
			$articles_top_name = $top_name;
		}
	}

	if (!strlen($articles_top_name) && VA_Articles_Categories::check_permissions($top_id, VIEW_CATEGORIES_PERM)) {
		$sql  = " SELECT category_name ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";				
		$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);			
		$db->query($sql);
		if ($db->next_record()) {
			$articles_top_name = get_translation($db->f("category_name"));
		} else {
			return false;
		}
	}

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$html_template = get_setting_value($block, "html_template", "block_search.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("search_href",   "articles.php");
	$t->set_var("search_name",   $articles_top_name);

	$search_string = trim(get_param("search_string"));
	$is_search = strlen($search_string);

	// clear tags before parse
	$t->set_var("search_categories", "");
	$t->set_var("category_id", "");
	$t->set_var("no_search_categories", "");
	$t->set_var("advanced_search", "");

	$search_categories = array();
	$search_categories[] = array($top_id, SEARCH_IN_ALL_MSG);
  
	if($top_id != $articles_category_id && $articles_category_id != 0) {
		$search_categories[] = array($articles_category_id, SEARCH_IN_CURRENT_MSG);
	}
	
	if ($articles_category_id) {	
		$where = "c.parent_category_id = " . $db->tosql($articles_category_id, INTEGER);
	} else {
		$where = "c.parent_category_id = " . $db->tosql($top_id, INTEGER);
	}
	$categories_ids = VA_Articles_Categories::find_all_ids($where, VIEW_CATEGORIES_ITEMS_PERM);
	if ($categories_ids) {

		$sql  = " SELECT category_id, category_name FROM " . $table_prefix . "articles_categories ";	
		$sql .= " WHERE  category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
		$sql .= " ORDER BY category_order ";
		$db->query($sql);
		while ($db->next_record()) {
			$show_category_id  = $db->f("category_id");
			$category_name  = get_translation($db->f("category_name"));
			$search_categories[] = array($show_category_id, $category_name);
		}
	}

	// set up search form parameters
	if (sizeof($search_categories) > 1) {
		set_options($search_categories, $articles_category_id, "category_id");
		$t->global_parse("search_categories", false, false, true);
		$t->set_var("no_search_categories", "");
	} else {
		$t->set_var("search_categories", "");
		$t->set_var("top_id", $top_id);
		$t->sparse("no_search_categories", false);
	}

	$t->set_var("search_string", htmlspecialchars($search_string));
	if ($articles_category_id > 0) {
		$t->set_var("current_category_id", htmlspecialchars($articles_category_id));
	} else {
		$t->set_var("current_category_id", htmlspecialchars($top_id));
	}

	$block_parsed = true;

?>