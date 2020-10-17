<?php                           

	include_once("./includes/articles_functions.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$default_title = "{current_category_name}";

	$top_id = $block["block_key"];
	$page_param = "ap".$top_id;
	$columns = get_setting_value($vars, "articles_cols", 1);

	// clear template block to parse new data
	$t->set_var("items_cols", "");
	$t->set_var("items_rows", "");
	$t->set_var("search_and_navigation", "");
	$t->set_var("navigator_block", "");
	$t->set_var("search_results", "");
	$t->set_var("no_items", "");
	$t->set_var("article_new_class", "");
	$t->set_var("columns_class", "cols-".$columns);

	// get articles reviews settings
	$articles_reviews_settings = get_settings("articles_reviews");
	$reviews_allowed_view = get_setting_value($articles_reviews_settings, "allowed_view", 0);
	$reviews_allowed_post = get_setting_value($articles_reviews_settings, "allowed_post", 0);

  // check columns settings and correct list order
	$sql  = " SELECT category_name, articles_order_column, articles_order_direction, article_list_fields, article_edit_fields ";
	$sql .= " FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$top_name = get_translation($db->f("category_name"));
		$articles_order_column = $db->f("articles_order_column");
		$articles_order_direction = $db->f("articles_order_direction");
		$list_fields = $db->f("article_list_fields");
		$edit_fields_data = $db->f("article_edit_fields");
		$edit_fields = ($edit_fields_data) ? json_decode($edit_fields_data, true) : array();
	}

	$article_date_type = get_setting_value($edit_fields, "article_date_format");
	$date_end_type = get_setting_value($edit_fields, "date_end_format");
	$article_date_format = ($article_date_type == "date") ? $date_show_format : $datetime_show_format;
	$date_end_format = ($date_end_type == "date") ? $date_show_format : $datetime_show_format;

	if (strlen($articles_order_column)) {
		$articles_order = " ORDER BY " . $articles_order_column . " " . $articles_order_direction;
	} else {
		$articles_order_column = "article_order";
		$articles_order = " ORDER BY article_order ";
	}

	$html_template = get_setting_value($block, "html_template", "block_articles_list.html"); 
  $t->set_file("block_body", $html_template);

	// it's possible to use variable to mark one of the item in any way in templates
	$selected_article_id = get_param("article_id");
	$t->set_var("selected_article_id", $selected_article_id);
	$t->set_var("list_href",   "articles.php");

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$pass_parameters = get_transfer_params($page_friendly_params);
		$articles_page = $page_friendly_url . $friendly_extension;
	} else if (isset($current_page) && $current_page) {
		$pass_parameters = get_transfer_params();
		$articles_page = $current_page;
	} else {
		$pass_parameters = get_transfer_params();
		$articles_page = "articles.php";
	}

	$category_id        = get_param("category_id");
	$allowed_all_articles = false;
	
	// check category_id and path
	$search_string = ""; $page = 1; $is_search          = false;
	$current_category_id = ""; $current_category_path = "";
	if ($cms_page_code == "articles_list" && $category_id && $category_id != $top_id) {
		$sql  = " SELECT category_path, category_name FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$art_category_path = $db->f("category_path");
			$art_category_name = get_translation($db->f("category_name"));
			$category_ids = explode(",", $art_category_path);
			if (in_array($top_id, $category_ids)) {
				$current_category_id = $category_id;
				$current_category_path = $art_category_path. $category_id . ",";
				$current_category = $art_category_name;
			}
		}
	} 
	// get top category if there is no category selected
	if (!$current_category_id) {
		$current_category_path = "0," . $top_id . ",";
		$current_category_id = $top_id;
		$current_category = $top_name;
	}
	// check other parameters
	$search_string = trim(get_param("search_string"));
	$sq = trim(get_param("sq"));
	$is_search     = (strlen($search_string) || strlen($sq)) ;
	$page = get_param($page_param);

	$t->set_var("current_category_name", htmlspecialchars($current_category));

	// 1. check if category is availiable
	$where = "";
	if ($is_search)	{
		$where .= " (c.category_id = " . $db->tosql($current_category_id, INTEGER);
		$where .= " OR c.category_path LIKE '" . $db->tosql($current_category_path, TEXT, false) . "%')";
	} else {
		$where .= " c.category_id = " . $db->tosql($current_category_id, INTEGER);
	}
	$categories_ids = VA_Articles_Categories::find_all_ids($where, VIEW_CATEGORIES_PERM);
	if (!$categories_ids) return;
	
	// 2. count total articles
	$where = "c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") "; 
	if (strlen($search_string) || strlen($sq)) {
		if (strlen($sq)) {
			$search_values = explode(" ", $sq);
		} else {
			$search_values = explode(" ", $search_string);
		}
		for ($si = 0; $si < sizeof($search_values); $si++) {
			$where .= " AND (a.short_description LIKE '%" . $db->tosql($search_values[$si], TEXT, false) . "%' ";
			$where .= " OR a.full_description LIKE '%" . $db->tosql($search_values[$si], TEXT, false) . "%' ";
			$where .= " OR a.article_title LIKE '%" . $db->tosql($search_values[$si], TEXT, false) . "%') ";
		}
	}	
	$articles_ids = VA_Articles::find_all_ids($where, VIEW_CATEGORIES_ITEMS_PERM);
	$total_records = count($articles_ids);

	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $articles_page);

	$records_per_page = get_setting_value($vars, "articles_recs", 10);
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", $page_param, CENTERED, $pages_number, $records_per_page, $total_records, false, $pass_parameters);
	$total_pages = ceil($total_records / $records_per_page);
	
	// generate page link with query parameters
	$pass_parameters["page"] = $page;
	$query_string = get_query_string($pass_parameters, "article_id", "", true);
	$rp  = $articles_page;
	$rp .= $query_string;

	$article_link  = "article.php" . $query_string;
	$article_link .= strlen($query_string) ? "&" : "?";
	$article_link .= "article_id=";

	$reviews_link  = "articles_reviews.php" . $query_string;
	$reviews_link .= strlen($query_string) ? "&" : "?";
	$reviews_link .= "article_id=";

	$t->set_var("rp_url", urlencode($rp));
	$t->set_var("rp", htmlspecialchars($rp));
	$t->set_var("total_records", $total_records);

	$list_fields = ",," . $list_fields . ",,";

	$article_fields = array(
		"author_name", "author_email", "author_url", "link_url", "download_url", 
		"short_description", "full_description", "keywords", "notes"
	);


	$set_video_js	= false;
	if ($total_records > 0) {
		$allowed_articles_ids = VA_Articles::find_all_ids("a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
		
		$articles_categories = array();
		if ($is_search) {
			$sql  = " SELECT a.article_id, a.category_id, c.category_name " ;
			$sql .= " FROM  (" . $table_prefix . "articles_assigned a ";
			$sql .= " LEFT JOIN " . $table_prefix . "articles_categories c ON c.category_id=a.category_id)";
			$sql .= " WHERE a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ") ";	
			$sql .= " AND c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";	
			$db->query($sql);
			$categories_ids = array();
			$allowed_categories = array();
			while ($db->next_record()) {
				$article_id = $db->f("article_id");
				$ic_id   = $db->f("category_id");
				$ic_name = get_translation($db->f("category_name"));
				$ic_name = get_currency_message($ic_name, $currency);
				$categories_ids[] = $ic_id;
				$articles_categories[$article_id][] = array($ic_id, $ic_name);
			}
			
			if ($categories_ids) {
				$allowed_categories = VA_Articles_Categories::find_all_ids("c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
			}
		}

		// use paging for showing articles list
		$db->RecordsPerPage = $records_per_page;
		$db->PageNumber = $page_number;
	
		$sql  = " SELECT a.article_id, a.article_title, a.friendly_url, a.article_date, a.date_end, ";
		$sql .= " a.author_name, a.author_email, a.author_url, a.link_url, a.link_title, a.download_url, ";
		$sql .= " a.short_description, a.is_html, a.full_description, a.is_remote_rss, a.details_remote_url, ";
		$sql .= " a.image_small, a.image_small_alt, a.image_large, a.image_large_alt, a.stream_video, ";
		$sql .= " a.stream_video_width, a.stream_video_height, a.stream_video_preview, ";
		$sql .= " a.rating, a.allowed_rate, ";
		$sql .= " a.keywords, a.notes FROM ";
		if ($articles_order_column == "article_order" && !$is_search ) {
			$sql .= " ( " ;
		}
		$sql .= $table_prefix . "articles a ";
		if ($articles_order_column == "article_order" && !$is_search) {
			$sql .= " LEFT JOIN " . $table_prefix . "articles_assigned aa ON aa.article_id=a.article_id)";
		}
		$sql .= " WHERE a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ") ";
		if ($articles_order_column == "article_order" && !$is_search) {
			$sql .= " AND aa.category_id = " . $db->tosql($current_category_id, INTEGER);
			$sql .= " ORDER BY aa.article_order ";
			if ($articles_order_direction) $sql .= $articles_order_direction;			
		} else {
			$sql .= $articles_order;			
		}

		$t->set_var("category_id", htmlspecialchars($current_category_id));
		$db->query($sql);
		if ($db->next_record())
		{
			$t->set_var("item_column", (100 / $columns) . "%");
			$t->set_var("total_columns", $columns);
			$item_number = 0;
			do
			{
				$item_number++;
				$article_id = $db->f("article_id");
				$a_name = $article_id;
				$article_title = get_translation($db->f("article_title"));
				$article_title = get_currency_message($article_title, $currency);
				$friendly_url = $db->f("friendly_url");
				$is_remote_rss = $db->f("is_remote_rss");
				$details_remote_url = $db->f("details_remote_url");
				$link_url   = $db->f("link_url");		
				$link_title = $db->f("link_title");		
				if (!$link_title) { $link_title = $link_url; }
				$t->set_var("link_title", htmlspecialchars($link_title));

			
				$t->set_var("article_id", $article_id);
				$tell_friend_href = "article.php?category_id=".urlencode($current_category_id)."&article_id=" . urlencode($article_id);
				$t->set_var("tell_friend_href", $tell_friend_href);
				
				if ($is_search) {
					$article_categories = $articles_categories[$article_id];
					$total_categories = sizeof($article_categories);
					$t->set_var("found_categories", "");
					for ($ic = 0; $ic < $total_categories; $ic++) {
						$ic_separator = ($ic != ($total_categories - 1)) ? "," : "";
						$ic_id = $article_categories[$ic][0];
						$ic_name = $article_categories[$ic][1];
						$t->set_var("ic_id", $ic_id);
						$t->set_var("article_category", $ic_name);
						$t->set_var("ic_separator", $ic_separator);
						if (!$allowed_categories || !in_array($ic_id, $allowed_categories)) {
							$t->set_var("restricted_cat_class", " restricted ");
						} else {
							$t->set_var("restricted_cat_class", "");
						}
						$t->sparse("found_categories", true);
					}
					$t->sparse("found_in_category", false);					
				} else {
					$t->set_var("found_in_category", "");
				}

				if (!$allowed_articles_ids || !in_array($article_id, $allowed_articles_ids)) {
					$t->set_var("restricted_class", " restricted ");
				} else {
					$t->set_var("restricted_class", "");
				}
					
				$t->set_var("a_name", $a_name);
				$t->set_var("article_title", $article_title);
	  
				
				
				if ($is_remote_rss == 0){
					if ($friendly_urls && $friendly_url) {
						$t->set_var("details_url", $friendly_url . $friendly_extension . $query_string);
					} else {
						$t->set_var("details_url", $article_link . $article_id);
					}
				} else {
					$t->set_var("details_url", $details_remote_url);
				}
	  
				// get fields values
				$article_date_string = ""; $date_end_string = "";
				if (strpos($list_fields, ",article_date,")) {
					$article_date = $db->f("article_date", DATETIME);
					$article_date_string  = va_date($article_date_format, $article_date);

					$t->set_var("article_date", $article_date_string);
					$t->global_parse("article_date_block", false, false, true);
				} else {
					$t->set_var("article_date_block", "");
				}
				if (strpos($list_fields, ",date_end,")) {
					$date_end = $db->f("date_end", DATETIME);
					$date_end_string = va_date($date_end_format, $date_end);

					$t->set_var("date_end", $date_end_string);
					$t->global_parse("date_end_block", false, false, true);
				} else {
					$t->set_var("date_end_block", "");
				}
				if (strlen($article_date_string) || strlen($date_end_string)) {
					$t->global_parse("date_block", false, false, true);
				}
	  
				for ($i = 0; $i < sizeof($article_fields); $i++) {
					$field_name = $article_fields[$i];
					$fields[$field_name] = get_currency_message(get_translation($db->f($field_name)), $currency);
					if (strlen($fields[$field_name]) && strpos($list_fields, "," . $field_name . ",")) {
						$t->set_var($field_name, $fields[$field_name]);
						$t->global_parse($field_name . "_block", false, false, true);
					} else {
						$fields[$field_name] = "";
						$t->set_var($field_name, "");
						$t->set_var($field_name . "_block", "");
					}
				}
	  
				if (strlen($fields["author_name"]) || strlen($fields["author_email"]) || strlen($fields["author_url"])) {
					$t->global_parse("author_block", false, false, true);
				} else {
					$t->set_var("author_block", false);
				}
	  
				if (strpos($list_fields, ",full_description,")) {
					$full_description = get_translation($db->f("full_description"));
					$full_description = get_currency_message($full_description, $currency);
					if ($db->f("is_html") != 1) {
						$full_description = nl2br(htmlspecialchars($full_description));
					}
					$t->set_var("full_description", $full_description);
				} else {
					$t->set_var("full_description", "");
				}
	  
				$image_small = $db->f("image_small");
				$image_small_alt = $db->f("image_small_alt");
				if (strpos($list_fields, ",image_small,") && strlen($image_small)) {
					if (preg_match("/^http\:\/\//", $image_small)) {
						$image_size = "";
					} else {
						$image_size = @getimagesize($image_small);
						if (isset($restrict_articles_images) && $restrict_articles_images) { $image_small = "image_show.php?article_id=".$article_id."&type=small"; }
					}
					if (!strlen($image_small_alt)) { $image_small_alt = $article_title; }
						$t->set_var("alt", htmlspecialchars($image_small_alt));
						$t->set_var("src", htmlspecialchars($image_small));
					if (is_array($image_size)) {
						$t->set_var("width", "width=\"" . $image_size[0] . "\"");
						$t->set_var("height", "height=\"" . $image_size[1] . "\"");
					} else {
						$t->set_var("width", "");
						$t->set_var("height", "");
					}
					$t->global_parse("image_small_block", false, false, true);
				} else {
					$t->set_var("image_small_block", "");
				}
	  
				$image_large = $db->f("image_large");
				$image_large_alt = $db->f("image_large_alt");
				if (strpos($list_fields, ",image_large,") && strlen($image_large)) {
					if (preg_match("/^http\:\/\//", $image_large)) {
						$image_size = "";
					} else {
						$image_size = @getimagesize($image_large);
						if (isset($restrict_articles_images) && $restrict_articles_images) { $image_large = "image_show.php?article_id=".$article_id."&type=large"; }
					}
					if (!strlen($image_large_alt)) { $image_large_alt = $article_title; }
						$t->set_var("alt", htmlspecialchars($image_large_alt));
						$t->set_var("src", htmlspecialchars($image_large));
					if (is_array($image_size)) {
						$t->set_var("width", "width=\"" . $image_size[0] . "\"");
						$t->set_var("height", "height=\"" . $image_size[1] . "\"");
					} else {
						$t->set_var("width", "");
						$t->set_var("height", "");
					}
					$t->global_parse("image_large_block", false, false, true);
				} else {
					$t->set_var("image_large_block", "");
				}
				
				$stream_video = $db->f("stream_video");
				$stream_video_width = $db->f("stream_video_width");
				$stream_video_height = $db->f("stream_video_height");
				$stream_video_preview = $db->f("stream_video_preview");
				if (strlen($stream_video) && strpos($list_fields, ",stream_video,")){
					$set_video_js = true;
					$path_parts = pathinfo($stream_video);
					$ext = strtolower($path_parts['extension']);
					if ($ext == "flv" || $ext == "mp4"){
						if (!strlen($stream_video_width) && !strlen($stream_video_height)){
						  $stream_video_width = '';
						  $stream_video_height = '';
						}				
							$t->set_var("stream_video_width", htmlspecialchars($stream_video_width));
							$t->set_var("stream_video_height", htmlspecialchars($stream_video_height));
							$t->set_var("stream_video_preview", htmlspecialchars($stream_video_preview));
							$t->set_var("stream_video", htmlspecialchars($stream_video));

						$t->global_parse("flash_player_block", false, false, true);
						$t->set_var("windows_media_block", "");
					} else {
						if (!strlen($stream_video_width) && !strlen($stream_video_height)){
						  $stream_video_width = 230;
						  $stream_video_height = 140;
						}
						
						if ($stream_video_width < 230){
							$stream_video_height = $stream_video_height * 230 / $stream_video_width;
							$stream_video_width = 230;
						}
							$stream_video_height += 70;
							$t->set_var("stream_video_width", htmlspecialchars($stream_video_width));
							$t->set_var("stream_video_height", htmlspecialchars($stream_video_height));
							$t->set_var("stream_video", htmlspecialchars($stream_video));

						$t->global_parse("windows_media_block", false, false, true);
						$t->set_var("flash_player_block", "");
					}
				} else {
					$t->set_var("flash_player_block", "");
					$t->set_var("windows_media_block", "");
				}
	  
				$allowed_rate = $db->f("allowed_rate");
				if ($allowed_rate && 
					($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id))
					|| $reviews_allowed_post == 1 || ($reviews_allowed_post == 2 && strlen($user_id)))) {
					$rating_float = $db->f("rating");
					$rating_int = round($rating_float, 0);
					if ($rating_int) {
						$rating_alt = $rating_float;
						$rating_image = "rating-" . $rating_int;
					} else {
						$rating_alt = RATE_IT_MSG;
						$rating_image = "not-rated";
					}
	  
					$t->set_var("reviews_url", $reviews_link . $article_id);
					$t->set_var("rating_image", $rating_image);
					$t->set_var("rating_alt", $rating_alt);
					$t->global_parse("reviews", false, false, true);
				} else {
					$t->set_var("reviews", "");
				}
								
				$column_index = ($item_number % $columns) ? ($item_number % $columns) : $columns;
				$t->set_var("column_class", "col-".$column_index);
				$t->parse("items_cols");
				$is_next_record = $db->next_record();
				if ($item_number % $columns == 0)
				{
					$t->parse("items_rows");
					$t->set_var("items_cols", "");
				}
			} while ($is_next_record);              	
	  
			if ($item_number % $columns != 0)
				$t->parse("items_rows");
			
			if ($total_pages > 1)
				$t->parse("search_and_navigation", false);
	  
			$block_parsed = true;
			$t->set_var("no_items", "");
		}
	} else {
		// show 'no articles' message only if there are no subcategories exists
		$where = " c.parent_category_id=" . $db->tosql($category_id, INTEGER);
		$sub_categories_ids = VA_Articles_Categories::find_all_ids($where, VIEW_CATEGORIES_PERM);
		if (count($sub_categories_ids) == 0) {
			$t->set_var("items_rows", "");
			$t->parse("no_items", false);
			$block_parsed = true;
		}
	}

	if ($set_video_js) {
		set_script_tag("js/video.js");
	}

	// show search results information
	if ($is_search) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_ARTICLES_MSG);
		if (strlen($sq)) {
			$found_message = str_replace("{search_string}", htmlspecialchars($sq), $found_message);
		} else {
			$found_message = str_replace("{search_string}", htmlspecialchars($search_string), $found_message);
		}
		$t->set_var("FOUND_ARTICLES_MSG", $found_message);
		$t->parse("search_results", false);
		$t->parse("search_and_navigation", false);
		$block_parsed = true;
	} 

?>