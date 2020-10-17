<?php

	include_once("./includes/articles_functions.php");

	$default_title = "{top_category_name} &nbsp; {LATEST_TITLE}";

	$top_id = $block["block_key"];
	$top_name = "";
	
	if (!strlen($top_name) && VA_Articles_Categories::check_permissions($top_id, VIEW_CATEGORIES_ITEMS_PERM)) {
		$sql  = " SELECT category_name, friendly_url, article_list_fields, article_details_fields ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";				
		$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);			
						
		$db->query($sql);
		if ($db->next_record()) {
			$top_name         = get_translation($db->f("category_name"));
			$top_friendly_url = $db->f("friendly_url");
			$list_fields 			= $db->f("article_list_fields");
			$details_fields = $db->f("article_details_fields");
		} else {
			return false;
		}
	} else {
		$top_friendly_url = "";
	}
	$list_fields = ",," . $list_fields . ",,";
	$details_fields = ",," . $details_fields . ",,";

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$latest_group   = get_setting_value($vars, "articles_latest_group_by", 0);
	$latest_cats    = get_setting_value($vars, "articles_latest_cats", "");
	$latest_subcats = get_setting_value($vars, "articles_latest_subcats", 0);
	$latest_recs    = get_setting_value($vars, "articles_latest_recs", 10);
	$latest_subrecs = get_setting_value($vars, "articles_latest_subrecs", 0);
	$cols = get_setting_value($vars, "articles_latest_cols", 1);
	$latest_image   = get_setting_value($vars, "articles_latest_image",  0);
	$latest_desc    = get_setting_value($vars, "articles_latest_desc", 1);
	$va_timestamp   = va_timestamp();

	$html_template = get_setting_value($block, "html_template", "block_articles_latest.html"); 
  $t->set_file("block_body", $html_template);
/*
	$t->set_var("latest_rows", "");
	$t->set_var("latest_cols", "");
	$t->set_var("articles_category", "");
	$t->set_var("articles_top", "");
	$t->set_var("articles_sub", "");
*/

	$t->set_var("article_rows", "");
	$t->set_var("article_cols", "");
	$t->set_var("article_list", "");
	$t->set_var("article_category", "");

	$t->set_var("top_category_name",$top_name);
	$t->set_var("columns_class", "cols-".intval($cols));

	$category_number = 0;

	$image_field = ""; $image_alt_field = ""; $desc_field = "";
	if ($latest_image == 1) {
		$image_field = "image_tiny";
		$image_alt_field = "image_tiny_alt";
	} else if ($latest_image == 2) {
		$image_field = "image_small";
		$image_alt_field = "image_small_alt";
	} else if ($latest_image == 3) {
		$image_field = "image_large";
		$image_alt_field = "image_large_alt";
	} else if ($latest_image == 4) {
		$image_field = "image_super";
		$image_alt_field = "image_large_alt";
	}
	if ($latest_desc == 1) {
		$desc_field = "short_description";
	} else if ($latest_desc == 2) {
		$desc_field = "full_description";
	} else if ($latest_desc == 3) {
		$desc_field = "hot_description";
	}	
	
	$latest_categories = array();
	if ($latest_group) {
		if ($latest_group == 3) {
			$cats_ids = explode(",", $latest_cats);
		} else {
			$cats_ids = array($top_id);
		}		
		if ($latest_group == 1) {
			$where = "";
			foreach ($cats_ids AS $cat_id) {
				if ($where) $where .= " OR ";
				$where .= " c.category_path LIKE '0," . $cat_id . ",' ";
			}
			$where = "(" . $where . ")";
		} else if ($latest_group == 2) {
			$where = "";
			foreach ($cats_ids AS $cat_id) {
				if ($where) $where .= " OR ";
				$where .= " c.category_path LIKE '0," . $cat_id . ",%' ";
			}
			$where = "(" . $where . ")";
		} else if ($latest_group == 3) {
			$where = " c.category_id IN (" . $db->tosql($cats_ids, INTEGERS_LIST) .")";
		}
			
		$latest_categories = VA_Articles_Categories::find_all(
			"c.category_id", 
			array("c.category_name", "c.category_path", "c.friendly_url"),
			$where,
			VIEW_CATEGORIES_ITEMS_PERM
		);
	} else {
		$latest_categories[$top_id] = array($top_name, "0,", $top_friendly_url);
		$latest_subcats = 1;
	}

	foreach ($latest_categories as $category_id => $latest_category) {
		list($art_category_name, $art_category_path, $category_friendly_url) = array_values($latest_category);
		
		$where  = " (c.category_id=" . $db->tosql($category_id, INTEGER);
		if ($latest_subcats) {
			$where .= " OR c.category_path LIKE '" . $art_category_path. $category_id . ",%') ";
		} else {
			$where .= " ) ";
		}
		
		$db->RecordsPerPage = $latest_recs + $latest_subrecs ;
		$db->PageNumber = 1;
		$articles_ids = VA_Articles::find_all_ids(
			array(
				"order" => " a.article_date DESC, a.article_order ",
				"group" => " a.article_id ",
				"where" => $where
			),
			VIEW_CATEGORIES_ITEMS_PERM
		);
		if (!$articles_ids) continue;
		
		$allowed_articles_ids = VA_Articles::find_all_ids("a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);	
		
		$sql  = " SELECT article_id, article_title, friendly_url, article_date, date_added, is_remote_rss, details_remote_url, ";
		$sql .= " short_description, full_description, hot_description, ";
		$sql .= " image_tiny, image_tiny_alt, image_small, image_small_alt, image_large, image_large_alt, image_super, image_super_alt ";
		$sql .= " FROM " . $table_prefix . "articles a ";
		$sql .= " WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")";
		if (strpos($list_fields, ",article_date,") || strpos($details_fields, ",article_date,")) {
			$sql .= " ORDER BY article_date DESC, article_order ";	
		} else {
			$sql .= " ORDER BY date_added DESC, article_order ";	
		}
		$db->query($sql);
		if($db->next_record()) {
			$category_number++;
			$latest_number = 0;
			if ($friendly_urls && $category_friendly_url) {
				$t->set_var("category_url", $category_friendly_url . $friendly_extension);
			} else {
				$t->set_var("category_url", "articles.php?category_id=" . $category_id);
			}
			$t->set_var("article_list", "");
			$t->set_var("article_category", "");
			do {
				$latest_number++;
				$article_id = $db->f("article_id");
				$article_title = get_translation($db->f("article_title"));
				$friendly_url = $db->f("friendly_url");
				$is_remote_rss = $db->f("is_remote_rss");
				$details_remote_url = $db->f("details_remote_url");

				$short_description = $db->f("short_description");
				$full_description = $db->f("full_description");
				$hot_description = $db->f("hot_description");

				$article_class = ""; 
	
				if ($is_remote_rss == 0){
					if ($friendly_urls && $friendly_url) {
						$t->set_var("details_url", $friendly_url . $friendly_extension);
					} else {
						$t->set_var("details_url", "article.php?article_id=" . $article_id);
					}
				} else {
					$t->set_var("details_url", $details_remote_url);
				}
				
				if (!$allowed_articles_ids || !in_array($article_id, $allowed_articles_ids)) {
					$article_class .= " article-restricted";
				}
				
				$t->set_var("article_id", $article_id);
				$t->set_var("article_title", $article_title);
	
				$article_image = ""; $article_image_alt = ""; $article_desc = "";
				if ($image_field) {
					$article_image = $db->f($image_field);	
					$article_image_alt = $db->f($image_alt_field);	
				}
				if ($desc_field) {
					$article_desc = get_translation($db->f($desc_field));
				}
	
				// parse image block
				$image_block = false;
				$t->set_var("image_tiny_block", "");
				$t->set_var("image_small_block", "");
				$t->set_var("image_large_block", "");
				$t->set_var("image_super_block", "");

				if (strlen($article_image)) {
					$image_block = true;
					if (!preg_match("/^http\:\/\//", $article_image)) {
						if (isset($restrict_articles_images) && $restrict_articles_images) { 
							$article_image = "image_show.php?article_id=".$article_id."&type=small"; 
						}
					}
					if (!strlen($article_image_alt)) {  $article_image_alt = $article_title;  }
					$t->set_var("alt", htmlspecialchars($article_image_alt));
					$t->set_var("src", htmlspecialchars($article_image));
					$t->sparse("image_small_block", false);
				}
				if ($image_block) {
					$t->parse("image_block", false);
				} else {
					$t->set_var("image_block", "");
				}

				// show description 
				$desc_block = false;
				$t->set_var("desc_short_block", "");
				$t->set_var("desc_full_block", "");
				$t->set_var("desc_hot_block", "");
				if ($latest_desc == 1) {
					$desc_block = true;
					$t->set_var("desc_text", $short_description);
					$t->set_var("short_description", $short_description);
					$t->sparse("desc_short_block", false);
				} else if ($latest_desc == 2) {
					$desc_block = true;
					$t->set_var("desc_text", $full_description);
					$t->set_var("full_description", $full_description);
					$t->sparse("desc_full_block", false);
				} else if ($latest_desc == 3) {
					$desc_block = true;
					$t->set_var("desc_text", $hot_description);
					$t->set_var("hot_description", $hot_description);
					$t->sparse("desc_hot_block", false);
				}	
				if ($desc_block) {
					$t->sparse("more_button", false);
					$t->sparse("desc_block", false);
				} else {
					$t->set_var("more_button", "");
					$t->set_var("desc_block", "");
				}

				if (strpos($list_fields, ",article_date,") || strpos($details_fields, ",article_date,")) {
					$article_date = $db->f("article_date", DATETIME);
				} else {
					$article_date = $db->f("date_added", DATETIME);
				}
				if (!is_array($article_date)) {
					$article_date = $db->f("date_added", DATETIME);
				}

				if (is_array($article_date)) {
					$aticle_ts = va_timestamp($article_date);
					$days = intval(($va_timestamp - $aticle_ts) / (60*60*24));
					if ($latest_number <= $latest_recs) {	
						$article_date_string  = va_date($datetime_show_format, $article_date);
					} else if (($va_timestamp - $aticle_ts) > (60*60*24*365)) {
						$article_date_string  = str_replace("{quantity}", intval($days/365), va_constant("YEARS_QTY_MSG"));
					} else if (($va_timestamp - $aticle_ts) > (60*60*24*90)) {
						$article_date_string  = str_replace("{quantity}", intval($days/7), va_constant("WEEKS_QTY_MSG"));
					} else if (($va_timestamp - $aticle_ts) > (60*60*24)) {
						$article_date_string  = str_replace("{quantity}", $days, va_constant("DAYS_QTY_MSG"));
					} else {
						$article_date_string  = va_date("H:mm AM", $article_date);
					}
					$t->set_var("article_date", $article_date_string);
				} else {
					$t->set_var("article_date", "");
				}
				if ($latest_number <= $latest_recs) {
					$article_class .= " article-top";
				} else {
					$article_class .= " article-sub";
				}
				$t->set_var("article_class", htmlspecialchars($article_class));
				$t->parse("article_list", true);
	  
				if (!$latest_group) { // parse columns for simple list
					$column_index = ($latest_number % $cols) ? ($latest_number % $cols) : $cols;
					$t->set_var("column_class", "col-".$column_index);
					$t->parse("article_cols");
					$t->set_var("article_list", "");
					if ($latest_number % $cols == 0) {
						$t->parse("article_rows");
						$t->set_var("article_cols", "");
					}
				}
	
			} while ($db->next_record());              	
	
			if ($latest_group) {
				$column_index = ($category_number % $cols) ? ($category_number % $cols) : $cols;
				$t->set_var("column_class", "col-".$column_index);
				$t->set_var("category_name", $art_category_name);
				$t->parse("article_category", false);
				$t->parse("article_cols");
				if($category_number % $cols == 0) {
					$t->parse("article_rows");
					$t->set_var("article_cols", "");
				}
			} else {	
				if ($latest_number % $cols != 0) {
					$t->parse("article_rows");
				}
			}
		}		
	}

	if ($latest_group && $category_number % $cols != 0) {
		$t->parse("article_rows");
	}

	if ($category_number) {
		$block_parsed = true;
	}


