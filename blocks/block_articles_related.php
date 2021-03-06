<?php
	include_once("./includes/articles_functions.php");

	$default_title = "{RELATED_ARTICLES_MSG}";

	$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	
	$item_id     = get_param("item_id");
	$article_id  = get_param("article_id");
	$thread_id   = get_param("thread_id");
	$category_id = (int) get_param("category_id");
	
	$related_type_join  = "";
	$related_type_where = "";
	$related_type_order = "";
	if ($cms_block_code == "articles_related") {
		$related_type_join  = " LEFT JOIN " . $table_prefix . "articles_related rel";
		$related_type_join .= " ON a.article_id=rel.related_id ";
		$related_type_where = " rel.article_id=" . $db->tosql($article_id, INTEGER);
		$related_type_order = " rel.related_order ";
		
		$product_page = "article.php";
		
		$sql  = " SELECT ac.category_path, ac.category_id FROM " . $table_prefix . "articles_categories ac ";
		$sql .= " INNER JOIN " . $table_prefix . "articles_assigned aas ON aas.category_id=ac.category_id ";
		$sql .= " WHERE aas.article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			$art_category_path = $db->f("category_path");
			if ("0," == $art_category_path) {
				$top_category_id = $category_id;
			} else {
				$category_path_parts = explode(",", $art_category_path);
				if (isset($category_path_parts[1])) {
					$top_category_id = $category_path_parts[1];
				} else {
					$top_category_id = $category_id;
				}
			}
		} else {
			$top_category_id = "0";
		}
			
		$articles_related_columns  = get_setting_value($vars, "articles_related_cols", 1);
		$articles_related_per_page = get_setting_value($vars, "articles_related_recs", 4);
		$articles_related_image = get_setting_value($vars, "articles_related_image", 0);
		$articles_related_desc  = get_setting_value($vars, "articles_related_desc", 0);
		$articles_related_date  = get_setting_value($vars, "articles_related_date", 0);
	} elseif ($cms_block_code == "forum_related_articles") {
		
		$related_type_join  = " LEFT JOIN " . $table_prefix . "articles_forum_topics rel";
		$related_type_join .= " ON a.article_id=rel.article_id ";
		$related_type_where = " rel.thread_id=" . $db->tosql($thread_id, INTEGER);
		$related_type_order = " rel.article_order ";
		
		$product_page = "forum_topic.php";
		$articles_related_columns  = get_setting_value($vars, "forum_articles_related_recs", 1);
		$articles_related_per_page = get_setting_value($vars, "forum_articles_related_cols", 4);
		$articles_related_image = get_setting_value($vars, "forum_articles_related_image", 0);
		$articles_related_desc  = get_setting_value($vars, "forum_articles_related_desc", 0);
		$articles_related_date  = get_setting_value($vars, "forum_articles_related_date", 0);
	} elseif ($cms_block_code == "product_related_articles") {
		
		$related_type_join  = " LEFT JOIN " . $table_prefix . "articles_items_related rel";
		$related_type_join .= " ON a.article_id=rel.article_id ";
		$related_type_where = " rel.item_id=" . $db->tosql($item_id, INTEGER);
		$related_type_order = " rel.article_order ";
		
		$product_page = "product_details.php";
		$articles_related_columns  = get_setting_value($vars, "articles_related_columns", 1);
		$articles_related_per_page = get_setting_value($vars, "articles_related_per_page", 4);
		$articles_related_image = get_setting_value($vars, "articles_related_image", 0);
		$articles_related_desc  = get_setting_value($vars, "articles_related_desc", 0);
		$articles_related_date  = get_setting_value($vars, "articles_related_date", 0);
	} else {
		$block_parsed = false;
		return;
	}
	
	$html_template = get_setting_value($block, "html_template", "block_articles_related.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("article_new_class", "");
	$t->set_var("articles_related_cols", "");
	$t->set_var("articles_related_rows", "");
	$t->set_var("articles_related_navigator_block", "");
	
	if ($friendly_urls && $page_friendly_url) {
		$pass_parameters = get_transfer_params($page_friendly_params);
		$main_page = $page_friendly_url . $friendly_extension;
	} else {
		$pass_parameters = get_transfer_params();
		$main_page = get_custom_friendly_url($product_page);
	}

	
	$sql_params = array();
	$sql_params["join"]   = $related_type_join;
	$sql_params["where"]  = $related_type_where;
	
	$articles_ids = VA_Articles::find_all_ids($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
	if(!$articles_ids) return;	
	
	$allowed_articles_ids = VA_Articles::find_all_ids("a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
	
	$total_records = count($articles_ids);
	
	$articles_related_desc_field  = "";
	$articles_related_image_field = "";
	$articles_related_image_alt   = "";
	$articles_related_date_field  = "";
	if ($articles_related_desc == 1) {
		$articles_related_desc_field = "short_description";
	} elseif ($articles_related_desc == 2) {
		$articles_related_desc_field = "full_description";
	} elseif ($articles_related_desc == 3) {
		$articles_related_desc_field = "hot_description";
	}
	if ($articles_related_image == 2) {
		$articles_related_image_field = "image_small";
		$articles_related_image_alt   = "image_small_alt";
	} elseif ($articles_related_image == 3) {
		$articles_related_image_field = "image_large";
		$articles_related_image_alt   = "image_large_alt";			
	}
	if ($articles_related_date == 1) {
		$articles_related_date_field = "article_date";
	} elseif ($articles_related_date == 2) {
		$articles_related_date_field = "date_end";
	}
		
	$pages_number = 5;
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $main_page);
	$page_number = $n->set_navigator("articles_related_navigator", "related_article_page", SIMPLE, $pages_number, $articles_related_per_page, $total_records, false, $pass_parameters);
	
	$db->RecordsPerPage = $articles_related_per_page;
	$db->PageNumber     = $page_number;

	$select  = " a.article_id, a.article_title, a.friendly_url, ";
	$select .= " a.short_description, a.full_description, a.hot_description, ";
	$select .= " a.image_small, a.image_small_alt, a.image_large, a.image_large_alt, ";
	$select .= " a.article_date, a.date_end ";

	$sql_params = array();
	VA_Query::prepare_sql($sql_params);
	$sql_params["select"][] = $select;
	$sql_params["from"][]   = $table_prefix."articles a ";
	$sql_params["join"][]   = $related_type_join;
	$sql_params["where"][]  = "a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")";	
	$sql_params["where"][]  = $related_type_where;
	$sql_params["order"][]  = $related_type_order;
	$sql = VA_Query::build_sql($sql_params);
		
	$t->set_var("articles_related_rows", "");
	$t->set_var("articles_related_column", (100 / $articles_related_columns) . "%");
	$t->set_var("columns_class", "cols-".$articles_related_columns);
	$articles_related_number = 0;
		
	$db->query($sql);
	while ($db->next_record()) {
		$articles_related_number++;
		$sub_article_id    = $db->f("article_id");
		$article_title     = get_translation($db->f("article_title"));
		$friendly_url      = $db->f("friendly_url");
		$article_image     = $db->f($articles_related_image_field);			
		$article_image_alt = $db->f($articles_related_image_alt);
		$article_desc      = get_translation($db->f($articles_related_desc_field));
		$article_date      = $db->f($articles_related_date_field, DATETIME);
			
		if ($friendly_urls && $friendly_url) {
			$t->set_var("details_url", $friendly_url . $friendly_extension);
		} else {
			$t->set_var("details_url", "article.php?article_id=" . $sub_article_id);
		}

		// parse description
		$desc_fields = 0;
		$t->set_var("short_description", "");
		$t->set_var("full_description", "");
		$t->set_var("hot_description", "");
		if ($articles_related_desc == 1) {
			$desc_fields++;
			$desc_text = $db->f("short_description");
			$t->set_var("desc_text", $desc_text);
			$t->sparse("short_description", false);
		} 
		if ($articles_related_desc == 2) {
			$desc_fields++;
			$desc_text = $db->f("full_description");
			$t->set_var("desc_text", $desc_text);
			$t->sparse("full_description", false);
		} 
		if ($articles_related_desc == 3) {
			$desc_fields++;
			$desc_text = $db->f("hot_description");
			$t->set_var("desc_text", $desc_text);
			$t->sparse("hot_description", false);
		}
		if ($desc_fields) {
			$t->sparse("article_desc_block", false);
		} else {
			$t->set_var("article_desc_block", "");
		}

		if ($articles_related_image && $article_image) {
			if (!preg_match("/^http\:\/\//", $article_image) && isset($restrict_articles_images) && $restrict_articles_images) { 
				$article_image = "image_show.php?article_id=". $sub_article_id . "&type=small"; 
			}
			if (!strlen($article_image_alt)) { $article_image_alt = $article_title; }
      $t->set_var("alt", htmlspecialchars($article_image_alt));
			$t->set_var("src", htmlspecialchars($article_image));
			$t->parse("article_image", false);
		} else {
			$t->set_var("article_image", "");
		}
		
		if (!$allowed_articles_ids || !in_array($sub_article_id, $allowed_articles_ids)) {
			$t->set_var("restricted_class", " restricted ");
		} else {
			$t->set_var("restricted_class", "");
		}		
			
		if ($articles_related_date && $article_date) {
			$article_date_string  = va_date($datetime_show_format, $article_date);
			$t->set_var("article_date", $article_date_string);
			$t->parse("article_date_block", false);
		} else {
			$t->set_var("article_date_block", "");
		}			
			
		$t->set_var("article_title", $article_title);
		$column_index = ($articles_related_number % $articles_related_columns) ? ($articles_related_number% $articles_related_columns) : $articles_related_columns;
		$t->set_var("column_class", "col-".$column_index);

		$t->parse("articles_related_cols");
		if ($articles_related_number % $articles_related_columns == 0) {
			$t->parse("articles_related_rows");
			$t->set_var("articles_related_cols", "");
		}
	}

	if ($articles_related_number % $articles_related_columns != 0) {
		$t->parse("articles_related_rows");
	}

	$block_parsed = true;
