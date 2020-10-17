<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_reviews.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/sorter.php");
	include_once($root_folder_path."includes/navigator.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."includes/reviews_functions.php");
	include_once($root_folder_path."includes/profile_functions.php");
	include_once($root_folder_path."messages/".$language_code."/reviews_messages.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("products_reviews");

	// global settings
	$site_url = get_setting_value($settings, "site_url", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	// begin delete selected reviews
	$operation = get_param("operation");
	$ajax = get_param("ajax");
	$existed_ids = get_param("existed_ids");
	$ignored_ids = strlen($existed_ids) ? array_flip(explode(",", $existed_ids)) : array();

	// list values
	$change_statuses = array(
		"0" => va_constant("NEW_MSG"),
		"1" => va_constant("APPROVED_MSG"),
		"-1" => va_constant("STATUS_DECLINED_MSG"),
	);

	$status_classes = array(
		"0" => "status-new",
		"1" => "status-approved",
		"-1" => "status-declined",
	);

	// update and remove operations
	if (strlen($operation)) {
		$review_ids = get_param("review_ids");
		if (!strlen($review_ids)) { $review_ids = get_param("reviews_ids"); }
		$items_ids = get_param("items_ids");
		$status_id = get_param("status_id");

		if ($operation == "remove_reviews") {
			$sql  = " DELETE FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE review_id IN (" . $db->tosql($review_ids, INTEGERS_LIST) . ")";
			$db->query($sql);
			if ($items_ids) { update_product_rating($items_ids); }
			if ($ajax) {
			  $data = array(
					"operation" => $operation,
					"review_ids" => $review_ids,
				);
				echo json_encode($data);
				return;
			}

		} else if ($operation == "update_status" && strlen($status_id)) {
			$sql  = " UPDATE " . $table_prefix . "reviews ";
			$sql .= " SET approved=" . $db->tosql($status_id, INTEGER);
			$sql .= " WHERE review_id IN (" . $db->tosql($review_ids, INTEGERS_LIST) . ")";
			$db->query($sql);
			if ($items_ids) { update_product_rating($items_ids); }
			if ($status_id == 1) {
				// if comments was approved sent reply notifications for them if they weren't sent before
				$reply_ids = array();
				$sql  = " SELECT * FROM ".$table_prefix."reviews ";
				$sql .= " WHERE review_id IN (" . $db->tosql($review_ids, INTEGERS_LIST) . ")";
				$sql .= " AND parent_review_id > 0 ";
				$db->query($sql);
				while ($db->next_record()) {
					$reply_comment_id = $db->f("review_id");
					$reply_sent = $db->f("reply_sent");
					if (!$reply_sent) {
						$reply_ids[] = $reply_comment_id;
					}
				}
				foreach ($reply_ids as $reply_comment_id) {
					product_review_notify(array("id" => $reply_comment_id, "type" => "notice"));
				}
			}


			if ($ajax) {
				$status_name = isset($change_statuses[$status_id]) ? $change_statuses[$status_id] : "";
				$status_class = isset($status_classes[$status_id]) ? $status_classes[$status_id] : "";

			  $data = array(
					"operation" => $operation,
					"review_ids" => $review_ids,
					"status_id" => $status_id,
					"status_name" => $status_name,
					"status_class" => $status_class,
				);
				echo json_encode($data);
				return;
			}
		}
	}
	// end operations

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_reviews.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_reviews_href", "admin_reviews.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");

	// prepare dates for stats
	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY];
	$today_ts = mktime (0, 0, 0, $cmonth, $cday, $cyear);
	$tomorrow_ts = mktime (0, 0, 0, $cmonth, $cday + 1, $cyear);
	$yesterday_ts = mktime (0, 0, 0, $cmonth, $cday - 1, $cyear);
	$week_ts = mktime (0, 0, 0, $cmonth, $cday - 6, $cyear);
	$month_ts = mktime (0, 0, 0, $cmonth, 1, $cyear);
	$last_month_ts = mktime (0, 0, 0, $cmonth - 1, 1, $cyear);
	$last_month_days = date("t", $last_month_ts);
	$last_month_end = mktime (0, 0, 0, $cmonth - 1, $last_month_days, $cyear);

	$t->set_var("date_edit_format", join("", $date_edit_format));

	// show reviews statistics
	$reviews_types = array(
		array("0", va_message("NEW_MSG")),
		array("1", va_message("APPROVED_MSG")),
		array("-1", va_message("STATUS_DECLINED_MSG")),
	);

	$stats = array(
		array("title" => va_message("TODAY_MSG"), "date_start" => $today_ts, "date_end" => $today_ts),
		array("title" => va_message("YESTERDAY_MSG"), "date_start" => $yesterday_ts, "date_end" => $yesterday_ts),
		array("title" => va_message("LAST_SEVEN_DAYS_MSG"), "date_start" => $week_ts, "date_end" => $today_ts),
		array("title" => va_message("THIS_MONTH_MSG"), "date_start" => $month_ts, "date_end" => $today_ts),
		array("title" => va_message("LAST_MONTH_MSG"), "date_start" => $last_month_ts, "date_end" => $last_month_end),
	);

	$reviews_total_online = 0; 
	// get reviews stats
	for($i = 0; $i < sizeof($reviews_types); $i++) {
		// set general constants
		$type_id = $reviews_types[$i][0];
		$type_name = $reviews_types[$i][1];

		$t->set_var("type_id",   $type_id);
		$t->set_var("type_name", $type_name);

		// get registration stats
		$t->set_var("stats_periods", "");
		foreach($stats as $key => $stat_info) {
			$start_date = $stat_info["date_start"];
			$end_date = va_time($stat_info["date_end"]);
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE approved=" . $db->tosql($type_id, INTEGER);
			$sql .= " AND date_added>=" . $db->tosql($start_date, DATE);
			$sql .= " AND date_added<" . $db->tosql($day_after_end, DATE);
			$period_reviews = get_db_value($sql);
			if (isset($stats[$key]["total"])) {
				$stats[$key]["total"] += $period_reviews;
			} else {
				$stats[$key]["total"] = $period_reviews;
			}
			if($period_reviews > 0) {
				$period_reviews = "<a href=\"admin_reviews.php?s_ap=".$type_id."&s_sd=".va_date($date_edit_format, $start_date)."&s_ed=".va_date($date_edit_format, $end_date)."\"><b>" . $period_reviews."</b></a>";
			}
			$t->set_var("period_reviews", $period_reviews);
			$t->parse("stats_periods", true);
		}

		$t->parse("types_stats", true);
	}
  
	foreach($stats as $key => $stat_info) {
		$t->set_var("start_date", va_date($date_edit_format, $stat_info["date_start"]));
		$t->set_var("end_date", va_date($date_edit_format, $stat_info["date_end"]));
		$t->set_var("stat_title", $stat_info["title"]);
		$t->set_var("period_total", $stat_info["total"]);
		$t->parse("stats_titles", true);
		$t->parse("stats_totals", true);
	}
	// end statistics

	// search form
	$approved_options = array(array("", va_message("")), array("0", va_message("NEW_MSG")), array("1", va_message("APPROVED_MSG")), array("-1", va_message("STATUS_DECLINED_MSG")));

	$rating_options = 
		array( 
			array("", ""), array(1, "1 - ".va_message("BAD_MSG")), array(2, "2 - ".va_message("POOR_MSG")), 
			array(3, "3 - ".va_message("AVERAGE_MSG")), array(4, "4 - ".va_message("GOOD_MSG")), array(5, "5 - ".va_message("EXCELLENT_MSG")),
			);

	$recommended_options = 
		array( 
			array("", va_message("")), array(1, va_message("POSITIVE_MSG")), array(0, va_message("NEUTRAL_MSG")), array(-1, va_message("NEGATIVE_MSG")), 
			);


	$r = new VA_Record($table_prefix . "reviews");
	$r->add_textbox("s_ne", TEXT);
	$r->change_property("s_ne", TRIM, true);
	$r->add_textbox("s_sd", DATE, va_message("FROM_DATE_MSG"));
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, va_message("END_DATE_MSG"));
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);
	$r->add_select("s_rt", INTEGER, $rating_options);
	$r->add_select("s_rc", INTEGER, $recommended_options);
	$r->add_select("s_ap", TEXT, $approved_options);
	$r->get_form_parameters();
	$r->validate();

	$status_values = array(array("", ""), array("0", va_message("NEW_MSG")), array("1", va_message("APPROVED_MSG")), array("-1", va_message("STATUS_DECLINED_MSG")));
	$r->add_select("status_id", TEXT, $status_values);
	$r->change_property("status_id", PARSE_NAME, "status_values");
	$r->set_form_parameters();
	// end search form

	// build where condition
	$where = "";
	if (!$r->errors)
	{
		if (!$r->is_empty("s_ne")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ne_sql = $db->tosql($r->get_value("s_ne"), TEXT, false);
			$where .= " (r.user_email LIKE '%" . $s_ne_sql . "%'";
			$where .= " OR r.user_name LIKE '%" . $s_ne_sql . "%')";
		}

		if (!$r->is_empty("s_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " r.date_added>=" . $db->tosql($r->get_value("s_sd"), DATE);
		}

		if (!$r->is_empty("s_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("s_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " r.date_added<" . $db->tosql($day_after_end, DATE);
		}

		if (!$r->is_empty("s_rt")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " r.rating=" . $db->tosql($r->get_value("s_rt"), INTEGER);
		}

		if (!$r->is_empty("s_rc")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " r.recommended=" . $db->tosql($r->get_value("s_rc"), INTEGER);
		}

		if (!$r->is_empty("s_ap")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " r.approved=" . $db->tosql($r->get_value("s_ap"), INTEGER);
		}
	}
	$where_sql = ""; $where_and_sql = "";
	if (strlen($where)) {
		$where_sql = " WHERE " . $where;
		$where_and_sql = " AND " . $where;
	}

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_reviews.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(va_message("ID_MSG"), "sorter_review_id", "1", "review_id");
	$s->set_sorter(va_message("USER_NAME_MSG"), "sorter_user_name", "2", "user_name");
	$s->set_sorter(va_message("SUMMARY_MSG"), "sorter_summary", "3", "summary");
	$s->set_sorter(va_message("RATING_MSG"), "sorter_rating", "4", "approved");
	$s->set_sorter(va_message("DATE_MSG"), "sorter_date_added", "5", "date_added");
	$s->set_sorter(va_message("STATUS_MSG"), "sorter_status", "6", "approved");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_reviews.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "reviews r ". $where_sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);
	
	$reviews = array(); $items = array(); $users = array(); $admins = array(); $parent_review_ids = array(); $item_ids = array(); $user_ids = array(); $admin_ids = array();
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "reviews r " . $where_sql . $s->order_by);
	while ($db->next_record()) {
		$review_id = $db->f("review_id");
		$date_added = $db->f("date_added", DATETIME);
		$user_email = $db->f("user_email");

		$reviews[$review_id] = $db->Record;
		$reviews[$review_id]["show"] = true;
		$reviews[$review_id]["date_added"] = $date_added;
		// check parent reviews, products and users data
		$parent_review_id = $db->f("parent_review_id");
		if ($parent_review_id) { $parent_review_ids[$parent_review_id] = $parent_review_id; }
		$item_id = $db->f("item_id");
		if ($item_id) { $item_ids[$item_id] = $item_id; }
		$user_id = $db->f("user_id");
		if ($user_id) { $user_ids[$user_id] = $user_id; }
		$admin_id = $db->f("admin_id");
		if ($admin_id) { $admin_ids[$admin_id] = $admin_id; }
	}

	if (count($parent_review_ids)) {
		$sql = " SELECT * FROM ".$table_prefix."reviews r WHERE review_id IN (".$db->tosql($parent_review_ids, INTEGERS_LIST).")"; 
		$db->query($sql);
		while ($db->next_record()) {
			$review_id = $db->f("review_id");
			$date_added = $db->f("date_added", DATETIME);
			if (!isset($reviews[$review_id])) {
				$reviews[$review_id] = $db->Record;
				$reviews[$review_id]["show"] = false;
				$reviews[$review_id]["date_added"] = $date_added;
			}
		}
	}

	if (count($item_ids)) {
		$sql = " SELECT * FROM ".$table_prefix."items WHERE item_id IN (".$db->tosql($item_ids, INTEGERS_LIST).")"; 
		$db->query($sql);
		while ($db->next_record()) {
			$item_id = $db->f("item_id");
			$item_name = $db->f("item_name");
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && strlen($friendly_url)) {
				$site_product_url = $site_url.$friendly_url.$friendly_extension."?tab=reviews";
			} else {
				$site_product_url = $site_url."product_details.php?item_id=".urlencode($item_id)."&tab=reviews";
			}
			$items[$item_id] = array("id" => $item_id, "name" => $item_name, "url" => $site_product_url);
		}
	}

	if (count($user_ids)) {
		$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
		$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
		$sql .= " WHERE user_id IN (".$db->tosql($user_ids, INTEGERS_LIST).")"; 
		$db->query($sql);
		while ($db->next_record()) {
			$user_id = $db->f("user_id");
			$user_email = $db->f("email");
			$user_type = $db->f("type_name");
			$user_name = get_user_name($db->Record, "full");
			$users[$user_id] = array("email" => $user_email, "name" => $user_name, "type" => $user_type);
		}
	}

	if (count($admin_ids)) {
		$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
		$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
		$sql .= " WHERE admin_id IN (".$db->tosql($admin_ids, INTEGERS_LIST).")"; 
		$db->query($sql);
		while ($db->next_record()) {
			$admin_id = $db->f("admin_id");
			$admin_email = $db->f("email");
			$privilege_name = $db->f("privilege_name");
			$admin_name = $db->f("nickname"); // show admin nickname for reviews if available
			if (!$admin_name) { $admin_name = $db->f("admin_name"); }

			$admins[$admin_id] = array("email" => $admin_email, "name" => $admin_name, "type" => $privilege_name);
		}
	}

	$data = array("reviews" => array(), "navigator" => ""); // json object for ajax load
	$review_index = 0;
	if(count($reviews))
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");

		$admin_review_url = new VA_URL("admin_review.php", false);
		$admin_review_url->add_parameter("s_ne", REQUEST, "s_ne");
		$admin_review_url->add_parameter("s_sd", REQUEST, "s_sd");
		$admin_review_url->add_parameter("s_ed", REQUEST, "s_ed");
		$admin_review_url->add_parameter("s_rt", REQUEST, "s_rt");
		$admin_review_url->add_parameter("s_rc", REQUEST, "s_rc");
		$admin_review_url->add_parameter("s_ap", REQUEST, "s_ap");
		$admin_review_url->add_parameter("page", REQUEST, "page");
		$admin_review_url->add_parameter("sort_ord", REQUEST, "sort_ord");
		$admin_review_url->add_parameter("sort_dir", REQUEST, "sort_dir");

		$t->set_var("filter_url", $admin_review_url->get_url("admin_reviews.php"));
		
		foreach ($reviews as $review_id => $rw_data) {
			$review_index++;
			$t->set_var("review_index", $review_index);
			$review_id = $rw_data["review_id"];
			$parent_review_id = $rw_data["parent_review_id"];
			if (isset($ignored_ids[$review_id])) {	continue; }

			$review_type = $rw_data["review_type"];
			if ($review_type == 2) {
				$review_type_desc = va_constant("COMMENT_MSG");
			} else if ($review_type == 3) {
				$review_type_desc = va_constant("QUESTION_MSG");
			} else if ($review_type == 4) {
				$review_type_desc = va_constant("ANSWER_MSG");
			} else {
				$review_type_desc = va_constant("REVIEW_MSG");
			}

			$item_id = $rw_data["item_id"];
			$rating = $rw_data["rating"];
			$rating_desc = get_array_value($rw_data["rating"], $rating_options);
			if ($rating == 0) {
				$rating_class = "ico-not-rated";
			} else {
				$rating_class = "ico-".$rating."-0-stars";
			}

			$t->set_var("review_id", $review_id);
			$t->set_var("review_type", $review_type_desc);
			// parse product name
			if (isset($items[$item_id])) {
				$item_name = $items[$item_id]["name"];
				$site_product_url = $items[$item_id]["url"];
				$t->set_var("item_na", "");
				$t->set_var("item_name", $item_name);
				$t->set_var("site_product_url", htmlspecialchars($site_product_url));
				$t->sparse("item_block", false);
			} else {
				$t->set_var("item_block", "");
				$t->sparse("item_na", false);
			}
			// parse review summarry
			$summary = $rw_data["summary"];
			if (strlen($summary)) {
				$t->set_var("summary", htmlspecialchars($summary));
				$t->sparse("summary_block", false);
			} else {
				$t->set_var("summary_block", "");
			}
			// parse comments 
			$comments = $rw_data["comments"];
			$comments_more = "";
			if (strlen($comments) > 50) { 
				$comments_more = substr($comments, 50);
				$comments = substr($comments, 0, 50);
			}

			$t->set_var("comments_block", "");
			$t->set_var("comments_more_block", "");
			if (strlen($comments)) {
				$t->set_var("comments", htmlspecialchars($comments));
				if (strlen($comments_more)) {
					$t->set_var("comments_more", htmlspecialchars($comments_more));
					$t->sparse("comments_more_block", false);
				}
				$t->sparse("comments_block", false);
			}

			// check parent review
			$t->set_var("parent_review", "");
			$t->set_var("parent_comments_more_block", "");
			$parent_review_id = $rw_data["parent_review_id"];
			if ($parent_review_id && isset($reviews[$parent_review_id])) {
				$parent_comments = $reviews[$parent_review_id]["comments"];
				$parent_type = $reviews[$parent_review_id]["review_type"];
				if ($parent_type == 2) {
					$parent_type_desc = va_constant("COMMENT_MSG");
				} else if ($parent_type == 3) {
					$parent_type_desc = va_constant("QUESTION_MSG");
				} else if ($parent_type == 4) {
					$parent_type_desc = va_constant("ANSWER_MSG");
				} else {
					$parent_type_desc = va_constant("REVIEW_MSG");
				}
				$parent_comments_more = "";
				if (strlen($parent_comments) > 50) { 
					$parent_comments_more = substr($parent_comments, 50);
					$parent_comments = substr($parent_comments, 0, 50);
				}

				$t->set_var("parent_type", htmlspecialchars($parent_type_desc));
				$t->set_var("parent_comments", htmlspecialchars($parent_comments));
				if (strlen($parent_comments_more)) {
					$t->set_var("parent_comments_more", htmlspecialchars($parent_comments_more));
					$t->sparse("parent_comments_more_block", false);
				}
				$t->parse("parent_review", false);
			}

			// parse user 
			$admin_id = $rw_data["admin_id"];
			$user_id = $rw_data["user_id"];
			if ($admin_id && isset($admins[$admin_id])) {
				$review_user_type = $admins[$admin_id]["type"];
				$review_user_class = "site-admin";
				$review_user_name = $admins[$admin_id]["name"];
				$review_user_email = $admins[$admin_id]["email"];
			} else if ($user_id && isset($users[$user_id])) {
				$review_user_type = $users[$user_id]["type"];
				$review_user_class = "site-user";
				$review_user_name = $users[$user_id]["name"];
				$review_user_email = $users[$user_id]["email"];
			} else {
				$review_user_type = va_constant("GUEST_MSG");
				$review_user_class = "site-guest";
				$review_user_name = $rw_data["user_name"];
				$review_user_email = $rw_data["user_email"];
				if (!strlen($review_user_name)) { 
					$review_user_name = va_constant("NOT_AVAILABLE_MSG");
				}
			}
			$t->set_var("review_user_type", htmlspecialchars($review_user_type));
			$t->set_var("review_user_class", htmlspecialchars($review_user_class));
			$t->set_var("review_user_name", htmlspecialchars($review_user_name));
			if ($review_user_email) {
				$t->set_var("review_user_email", htmlspecialchars($review_user_email));
				$t->sparse("review_user_email_block", false);
			} else {
				$t->set_var("review_user_email_block", "");
			}

			$verified_buyer = $rw_data["verified_buyer"];
			if ($verified_buyer) {
				$t->sparse("verified_buyer_block", false);
			} else {
				$t->set_var("verified_buyer_block", "");
			}

			$admin_review_url->remove_parameter("parent_review_id");
			$admin_review_url->remove_parameter("parent_comment_id");
			$admin_review_url->add_parameter("review_id", CONSTANT, $review_id);
			$t->set_var("admin_review_url", $admin_review_url->get_url("admin_review.php"));

			$admin_review_url->remove_parameter("review_id");
			if ($parent_review_id) {
				$admin_review_url->add_parameter("parent_review_id", CONSTANT, $parent_review_id);
				$admin_review_url->add_parameter("parent_comment_id", CONSTANT, $review_id);
			} else {
				$admin_review_url->add_parameter("parent_review_id", CONSTANT, $review_id);
			}
			$t->set_var("admin_review_reply_url", $admin_review_url->get_url("admin_review.php"));

			//$row_style = "rowWarn"; // to be used for IP addresses from black list

			$t->set_var("item_id", $item_id);
			$t->set_var("user_name", htmlspecialchars($rw_data["user_name"]));
			$t->set_var("rating", htmlspecialchars($rating));
			$t->set_var("rating_desc", htmlspecialchars($rating_desc));
			$t->set_var("rating_class", htmlspecialchars($rating_class));

			$date_added = $rw_data["date_added"];
			$t->set_var("date_added", va_date($datetime_show_format, $date_added));

			// set active class
			$approved = $rw_data["approved"];
			if ($approved > 0) {
				$status_class = "status-approved";
				$review_status = va_constant("APPROVED_MSG");
			} else  if ($approved < 0) {
				$status_class = "status-declined"; 
				$review_status = va_constant("STATUS_DECLINED_MSG");
			} else {
				$status_class = "status-new"; 
				$review_status = va_constant("NEW_MSG");
			}
			$t->set_var("status_id", htmlspecialchars($approved));
			$t->set_var("status_key", htmlspecialchars($approved));
			$t->set_var("status_class", htmlspecialchars($status_class));
			$t->set_var("review_status", htmlspecialchars($review_status));
			// set options to change current status
			$t->set_var("change_statuses", "");
			foreach ($change_statuses as $change_key => $change_name) {
				$change_class = ($change_key == $approved) ? "status-selected" : "";
				$t->set_var("change_key", $change_key);
				$t->set_var("change_class", $change_class);
				$t->set_var("change_name", htmlspecialchars($change_name));
				$t->parse("change_statuses", true);
			}

			if ($ajax) {
				$t->parse("records", false);
				$review_data = $t->get_var("records");
				$data["reviews"][] = $review_data;
			} else {
				$t->parse("records", true);
			}
		} 
		$t->set_var("reviews_number", $review_index);
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	if ($ajax) {
		echo json_encode($data);
		return;
	}


	$t->set_var("s_rt_search", $r->get_value("s_rt"));
	$t->set_var("s_rc_search", $r->get_value("s_rc"));
	$t->set_var("s_ap_search", $r->get_value("s_ap"));

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");
