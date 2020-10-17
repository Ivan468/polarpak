<?php
	include_once("./includes/articles_functions.php");

	$default_title = "{top_category_name} &nbsp; {HOT_TITLE}";
	$top_id = $block["block_key"];
	$top_name = "";

	if (!strlen($top_name) && VA_Articles_Categories::check_permissions($top_id, VIEW_CATEGORIES_ITEMS_PERM)) {
		$sql  = " SELECT category_name, article_list_fields, article_edit_fields, articles_order_column, articles_order_direction ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";				
		$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);			

		$db->query($sql);

		if ($db->next_record()) {
			$top_name                 = get_translation($db->f("category_name"));
			$articles_order_column    = $db->f("articles_order_column");
			$articles_order_direction = $db->f("articles_order_direction");
			$list_fields              = $db->f("article_list_fields");
			$edit_fields_data         = $db->f("article_edit_fields");
			$edit_fields = ($edit_fields_data) ? json_decode($edit_fields_data, true) : array();
		} else {
			return false;
		}
	} else {
		return false;
	}

	$article_date_type = get_setting_value($edit_fields, "article_date_format");
	$date_end_type = get_setting_value($edit_fields, "date_end_format");
	$article_date_format = ($article_date_type == "date") ? $date_show_format : $datetime_show_format;
	$date_end_format = ($date_end_type == "date") ? $date_show_format : $datetime_show_format;
	$authors_filter = get_setting_value($edit_fields, "authors_filter", 0);

	if (strlen($articles_order_column)) {
		$articles_order = " ORDER BY a.hot_order, a." . $articles_order_column . " " . $articles_order_direction;
	} else {
		$articles_order_column = "article_order";
		$articles_order = " ORDER BY a.hot_order, a.article_order ";
	}

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$pass_parameters = get_transfer_params($page_friendly_params);
		$current_page = $page_friendly_url . $friendly_extension;
	} else {
		$pass_parameters = get_transfer_params();
	}

	$html_template = get_setting_value($block, "html_template", "block_articles_hot.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("hot_rows", "");
	$t->set_var("hot_cols", "");
	$t->set_var("data", "");
	$t->set_var("data_style", "");
	$t->set_var("top_category_name",$top_name);

	// check category_id and path
	$current_category_id = ""; $current_category_path = "";
	if ($script_name == "articles.php" && $category_id && $category_id != $top_id) {
		$sql  = " SELECT category_path FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$art_category_path = get_db_value($sql);
		$category_ids = explode(",", $art_category_path);
		if (in_array($top_id, $category_ids)) {
			$current_category_id = $category_id;
			$current_category_path = $art_category_path. $category_id . ",";
		}
	} 

	// get top category if there is no category selected
	if (!$current_category_id) {
		$current_category_path = "0," . $top_id . ",";
		$current_category_id = $top_id;
	}

	$where  = " (aa.category_id = " . $db->tosql($current_category_id, INTEGER);
	$where .= " OR c.category_path LIKE '" . $db->tosql($current_category_path, TEXT, false) . "%')";
	$where .= " AND a.is_hot = 1";
	$sql_params = array();
	$sql_params["where"][] = " (aa.category_id = " . $db->tosql($current_category_id, INTEGER)." OR c.category_path LIKE '" . $db->tosql($current_category_path, TEXT, false) . "%')";
	$sql_params["where"][] = " a.is_hot = 1";
	$sql_params["authors"] = $authors_filter;

	$articles_ids = VA_Articles::find_all_ids($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
	if (count($articles_ids) == 0) { return false; }
	
	$allowed_articles_ids = VA_Articles::find_all_ids("a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
	$total_records = count($articles_ids);

	// check settings 
	$hot_columns = get_setting_value($vars, "articles_hot_cols", 1);
	$records_per_page = get_setting_value($vars, "articles_hot_recs", 10);
	$t->set_var("columns_class", "cols-".$hot_columns);

	//compare and fix if columns bigger than rpp
	if ($records_per_page < $hot_columns) {
		$hot_columns = $records_per_page;
	}

	// set up variables for navigator
	$pages_number = 5;
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $current_page);
	$page_number = $n->set_navigator("hot_navigator", "hot_page", SIMPLE, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	// check slider settings
	$data_style = ""; $slider_class = ""; $col_style = "";
	$slider_type = get_setting_value($vars, "slider_type", 0);
	$slider_width = get_setting_value($vars, "slider_width", "");		
	$slider_height = get_setting_value($vars, "slider_height", "");	
	$slider_style = get_setting_value($vars, "slider_style", "");
	$data_js = ($slider_type) ? "slideshow" : ""; 
	$t->set_var("data_js", htmlspecialchars($data_js));
	$t->set_var("slider_type", htmlspecialchars($slider_type));
	if ($slider_type > 0) { 
		if (strlen($slider_width)) {
			$data_style .= "width: " . get_css_dim($slider_width) . "; ";
		}
		if (strlen($slider_height)) {
			$data_style .= "height: " . get_css_dim($slider_height). "; ";
		}
		if (strlen($slider_style)) {
			$data_style .= $slider_style;
		}
	}
	if ($slider_type == 1 || $slider_type == 3) { // vertical
		$slider_class = "sr-vertical";
		$hot_columns =  1;
		$data_width = "100%";
		$column_width = "100%";
		$col_style = "width: ".$column_width.";";
	} else if ($slider_type == 2 || $slider_type == 4) { // horizontal
		$slider_class = "sr-horizontal";
		$hot_columns = $records_per_page; 
		$column_width = "300px";
		$records_left = $total_records - ($page_number - 1) * $records_per_page;
		if ($records_left > $records_per_page) {
			$data_width = (300*$records_per_page)."px";
		} else {
			$data_width = (300*$records_left)."px";
		}
		$col_style = "width: ".$column_width.";";
	} else if ($slider_type == 5) {
		$slider_class = "sr-slideshow";
		$hot_columns = 1;
		$column_width = "100%";
		$data_width = "100%";
		$col_style = "width: ".$column_width.";";
	} else {
		$column_width = round(100 / $hot_columns, 2)."%";
		$data_width = "100%";
	}
	$t->set_var("slider_class", $slider_class);
	$t->set_var("data_style", $data_style);
	$t->set_var("col_style", $col_style);
	$t->set_var("row_style", "");
	$t->set_var("data_width_style", "width: ".$data_width.";");

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT a.article_id, a.article_title, a.friendly_url, a.article_date, a.short_description, ";
	$sql .= " a.image_small, a.image_small_alt, a.hot_description, a.is_remote_rss, a.details_remote_url ";
	$sql .= " FROM " .  $table_prefix . "articles a ";
	$sql .= " WHERE a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")";
	$sql .= $articles_order;
	$db->query($sql);
	$hot_number = 0;
	while ($db->next_record()){
		$hot_number++;
		$article_id         = $db->f("article_id");
		$article_title      = get_translation($db->f("article_title"));
		$friendly_url       = $db->f("friendly_url");
		$is_remote_rss      = $db->f("is_remote_rss");
		$details_remote_url = $db->f("details_remote_url");
		$image_small        = $db->f("image_small");
		$image_small_alt    = $db->f("image_small_alt");
		$hot_description    = get_translation($db->f("hot_description"));
		if (!strlen($hot_description)) {
			$hot_description = get_translation($db->f("short_description"));
		}
		if ($is_remote_rss == 0){
			if ($friendly_urls && $friendly_url) {
				$t->set_var("details_href", $friendly_url . $friendly_extension);
			} else {
				$t->set_var("details_href", "article.php?article_id=" . $article_id);
			}
		} else {
			$t->set_var("details_href", $details_remote_url);
		}

		$t->set_var("article_id", $article_id);
		$t->set_var("data_id", "data_".$pb_id."_".$hot_number);
		$t->set_var("hot_item_name", $article_title);
		$t->set_var("hot_description", $hot_description);

		if (strpos(",," . $list_fields . ",,", ",article_date,")) {
			$article_date = $db->f("article_date", DATETIME);
			$article_date_string  = va_date($article_date_format, $article_date);

			$t->set_var("article_date", $article_date_string);
			$t->global_parse("article_date_block", false, false, true);
		} else {
			$t->set_var("article_date_block", "");
		}

		if($image_small) {
			if (preg_match("/^http\:\/\//", $image_small)) {
				$image_size = "";
			} else {
				$image_size = @GetImageSize($image_small);
				if (isset($restrict_articles_images) && $restrict_articles_images) { 
					$image_small = "image_show.php?article_id=" . $article_id . "&type=small"; 
				}
			}
			if (!strlen($image_small_alt)) { 
				$image_small_alt = $article_title;
			}
			$t->set_var("alt", htmlspecialchars($image_small_alt));
			$t->set_var("src", htmlspecialchars($image_small));
			if(is_array($image_size)) {
				$t->set_var("width", "width=\"" . $image_size[0] . "\"");
				$t->set_var("height", "height=\"" . $image_size[1] . "\"");
			} else {
				$t->set_var("width", "");
				$t->set_var("height", "");
			}
			$t->parse("image_small", false);
		} else {
			$t->set_var("image_small", "");
		}

		if (!$allowed_articles_ids || !in_array($article_id, $allowed_articles_ids)) {
			$t->set_var("restricted_class", " restricted ");
		} else {
			$t->set_var("restricted_class", "");
		}

		if ($slider_type == 5) {
			if ($hot_number == 1) {
				$t->set_var("row_style", "display: block; ");
			} else {
				$t->set_var("row_style", "display: none; ");
			}
		}
		$t->parse("hot_cols");
		if($hot_number % $hot_columns == 0) {
			$t->parse("hot_rows");
			$t->set_var("hot_cols", "");
		}
	}

	if ($hot_number % $hot_columns != 0) {
		$t->parse("hot_rows");
	}

	if ($hot_number) {
		if ($slider_type != 5) {
			$t->set_var("data_id", "data_".$pb_id);
		}
		$block_parsed = true;
	}

