<?php                           

	include_once("./includes/record.php");
	include_once("./includes/reviews_functions.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$default_title = "{article_title} {REVIEWS_MSG}";

	$html_template = get_setting_value($block, "html_template", "block_articles_reviews.html"); 
  $t->set_file("block_body", $html_template);

	$top_id = $block["block_key"];

	if (!isset($current_category_id)) {
		$current_category_id = $top_id;
	}
	// set urls
	$reviews_url = new VA_URL("articles_reviews.php");
	$reviews_url->add_parameter("category_id", CONSTANT, $current_category_id);
	$reviews_url->add_parameter("article_id", REQUEST, "article_id");
	$t->set_var("all_reviews_url", $reviews_url->get_url());
	$reviews_url->add_parameter("filter", CONSTANT, "1");
	$t->set_var("positive_reviews_url", $reviews_url->get_url());
	$reviews_url->add_parameter("filter", CONSTANT, "-1");
	$t->set_var("negative_reviews_url", $reviews_url->get_url());

	$user_id = get_session("session_user_id");
	$articles_reviews_settings = get_settings("articles_reviews");
	$allowed_view = get_setting_value($articles_reviews_settings, "allowed_view", 0);
	$allowed_post = get_setting_value($articles_reviews_settings, "allowed_post", 0);
	$reviews_per_page = get_setting_value($articles_reviews_settings, "reviews_per_page", 10);
	$review_random_image = get_setting_value($articles_reviews_settings, "review_random_image", 1);

	if (($review_random_image == 2) || ($review_random_image == 1 && !strlen(get_session("session_user_id")))) { 
		$use_validation = true;
	} else {
		$use_validation = false;
	}

	$article_id = get_param("article_id");
	
	if (!VA_Articles::check_exists($article_id)) {
		$t->set_var("item", "");
		$t->set_var("NO_ARTICLE_MSG", NO_ARTICLE_MSG);
		$t->parse("no_item", false);
		$block_parsed = true;
		return;
	}
	
	if (!VA_Articles::check_permissions($article_id, false, VIEW_ITEMS_PERM)) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}

	$articles_reviews_href = "articles_reviews.php";

	$recommended = 
		array( 
			array(1, YES_MSG), array(-1, NO_MSG)
			);

	$rating = 
		array( 
			array("", ""), array(1, BAD_MSG), array(2, POOR_MSG), 
			array(3, AVERAGE_MSG), array(4, GOOD_MSG), array(5, EXCELLENT_MSG),
			);

	$rr = new VA_Record($table_prefix . "articles_reviews");
	// global data
	$rr->operations[INSERT_ALLOWED] = (($allowed_post == 1) || ($allowed_post == 2 && get_session("session_user_id")));
	$rr->operations[UPDATE_ALLOWED] = false;
	$rr->operations[DELETE_ALLOWED] = false;
	$rr->operations[SELECT_ALLOWED] = false;
	$rr->redirect = false;
	$rr->success_messages[INSERT_SUCCESS] = REVIEW_SAVED_FOR_APPROVAL_MSG;

	// internal fields
	$rr->add_where("review_id", INTEGER);
	$rr->add_textbox("article_id", INTEGER);
	$rr->change_property("article_id", DEFAULT_VALUE, $article_id);
	$rr->add_textbox("user_id", INTEGER);
	$rr->change_property("user_id", USE_SQL_NULL, false);
	$rr->add_textbox("admin_id", INTEGER);
	$rr->change_property("admin_id", USE_SQL_NULL, false);
	$rr->add_textbox("date_added", DATETIME);
	$rr->add_textbox("remote_address", TEXT);
	$rr->add_textbox("approved", INTEGER);

	// predefined fields
	$rr->add_radio("recommended", INTEGER, $recommended, RECOMMEND_ARTICLE_MSG);
	$rr->change_property("recommended", USE_SQL_NULL, false);
	$rr->add_select("rating", INTEGER, $rating, RATE_IT_MSG);
	$rr->add_textbox("user_name", TEXT, NAME_ALIAS_MSG);
	$rr->change_property("user_name", REQUIRED, true);
	$rr->change_property("user_name", REGEXP_MASK, NAME_REGEXP);
	$rr->change_property("user_name", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$rr->set_control_event("user_name", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("user_email", TEXT, EMAIL_FIELD);
	$rr->change_property("user_email", REQUIRED, true);
	$rr->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
	$rr->set_control_event("user_email", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("summary", TEXT, ONE_LINE_SUMMARY_MSG);
	$rr->set_control_event("summary", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("comments", TEXT, YOUR_REVIEW_MSG);
	$rr->set_control_event("comments", AFTER_VALIDATE, "check_content");

	// check parameters properties
	$default_params = array(
		1 => "recommended", 2 => "rating", 
		3 => "user_name", 4 => "user_email", 
		5 => "summary", 6 => "comments");

	foreach ($default_params as $param_order => $param_name) {
		$param_order = get_setting_value($articles_reviews_settings, $param_name . "_order", $param_order);
		$show_param = get_setting_value($articles_reviews_settings, "show_".$param_name, $param_order);
		$param_required = get_setting_value($articles_reviews_settings, $param_name . "_required", $param_order);
		$rr->change_property($param_name, SHOW, $show_param);
		$rr->change_property($param_name, CONTROL_ORDER, $param_order);
		$rr->change_property($param_name, REQUIRED, $param_required);
		$rr->change_property($param_name, TRIM, true);
	}
	if ($user_id) {	
		$user_info = get_session("session_user_info");
		$user_nickname = get_setting_value($user_info, "nickname", "");
		$user_email = get_setting_value($user_info, "email", "");
		if (strlen($user_nickname)) {
			$rr->change_property("user_name", SHOW, false);
		}
		if (strlen($user_email)) {
			$rr->change_property("user_email", SHOW, false);
		}
	}

	$rr->add_textbox("validation_number", TEXT, VALIDATION_CODE_FIELD);
	$rr->change_property("validation_number", USE_IN_INSERT, false);
	$rr->change_property("validation_number", USE_IN_UPDATE, false);
	$rr->change_property("validation_number", USE_IN_SELECT, false);
	if ($use_validation) {
		$rr->change_property("validation_number", REQUIRED, true);
		$rr->change_property("validation_number", SHOW, true);
		$rr->change_property("validation_number", AFTER_VALIDATE, "check_validation_number");
	} else {
		$rr->change_property("validation_number", SHOW, false);
	}

	// set events
	$rr->set_event(ON_DOUBLE_SAVE, "review_double_save");
	$rr->set_event(BEFORE_INSERT, "before_insert_article_review");
	$rr->set_event(AFTER_INSERT, "after_insert_article_review");
	$rr->set_event(BEFORE_VALIDATE, "additional_article_review_checks");
	$rr->set_event(BEFORE_SHOW, "article_review_form_check");

	// check if article exists
	$sql  = " SELECT a.* ";
	$sql .= " FROM " . $table_prefix . "articles a, " . $table_prefix . "articles_statuses st ";
	$sql .= " WHERE st.allowed_view=1 ";
	$sql .= " AND a.article_id = " . $db->tosql($article_id, INTEGER);
	$db->query($sql);
	if($db->next_record())
	{
		$article_info = $db->Record;
		$article_title = get_translation($db->f("article_title"));
		$t->set_var("article_title", $article_title);
		if (!isset($meta_title) || !strlen($meta_title)) {
			$meta_title = REVIEWS_MSG.": ".$article_title;
		}
		$is_article = true;
	}
	else
	{
		$article_title = ERRORS_MSG;
		$rr->errors = NO_ARTICLE_MSG;
		$t->set_var("article_title", ERRORS_MSG);
		$is_article = false;
	}

	$t->set_var("rnd",           va_timestamp());
	$t->set_var("articles_reviews_href",  $articles_reviews_href);
	$t->set_var("article_id",  htmlspecialchars($article_id));
	//$t->set_var("recommend_msg", $recommend_msg);

	$filter = get_param("filter");
	$remote_address = get_ip();

	$rr->process();

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews WHERE approved=1 AND rating <> 0 AND article_id=" . $db->tosql($article_id, INTEGER);
	$total_rating_votes = get_db_value($sql);

	$average_rating_float = 0;
	$total_rating_sum = 0;
	if($total_rating_votes)
	{
		$sql = " SELECT SUM(rating) FROM " . $table_prefix . "articles_reviews WHERE approved=1 AND rating <> 0 AND article_id=" . $db->tosql($article_id, INTEGER);
		$total_rating_sum = get_db_value($sql);
		$average_rating_float = round($total_rating_sum / $total_rating_votes, 2);
	}

	$t->set_var("current_category_id", htmlspecialchars($current_category_id));
	$t->set_var("article_id", htmlspecialchars($article_id));

	if($is_article && ($allowed_view == 1 || ($allowed_view == 2 && strlen($user_id))))
	{
		$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $articles_reviews_href);

		// count all reviews
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews WHERE approved=1 AND article_id=" . $db->tosql($article_id, INTEGER);
		$total_reviews = get_db_value($sql);

		// count recommended reviews
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews WHERE recommended=1 AND approved=1 AND article_id=" . $db->tosql($article_id, INTEGER);
		$commend = get_db_value($sql);

		// count discommend reviews
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews WHERE recommended=-1 AND approved=1 AND article_id=" . $db->tosql($article_id, INTEGER);
		$discommend = get_db_value($sql);

		$total_commend_discommend = $commend + $discommend;

		if ($filter == 1) {
			$t->parse("all_reviews_link", false);
			$t->parse("positive_reviews", false);
			$t->parse("negative_reviews_link", false);
		} else if ($filter == -1) {
			$t->parse("all_reviews_link", false);
			$t->parse("positive_reviews_link", false);
			$t->parse("negative_reviews", false);
		} else {
			$t->parse("all_reviews", false);
			$t->parse("positive_reviews_link", false);
			$t->parse("negative_reviews_link", false);
		}
		
		if ($total_reviews) {
			// parse summary statistic
			$t->set_var("commend_percent", round($commend / $total_reviews * 100, 0));
			$t->set_var("discommend_percent", round($discommend / $total_reviews * 100, 0));
			$t->set_var("total_votes", $total_reviews);

			$average_rating = round($average_rating_float, 0);
			$average_rating_image = $average_rating ? "rating-" . $average_rating : "not-rated";
			$t->set_var("average_rating_image", $average_rating_image);
			$t->set_var("average_rating_alt", $average_rating_float);

			$t->parse("summary_statistic", false);

			$sql    = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews ";
			$where  = " WHERE (summary IS NOT NULL OR comments IS NOT NULL) ";
			$where .= " AND approved=1 AND article_id=" . $db->tosql($article_id, INTEGER);
			if (strlen($filter)) {
				$where .= " AND recommended=" . $db->tosql($filter, INTEGER);
			}
		
			$total_records = get_db_value($sql . $where);
			$t->set_var("total_records", $total_records);
			

			$record_number = 0;
			$records_per_page = $reviews_per_page ? $reviews_per_page : 10;
			$pages_number = 5;
			$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
  
			$sql = " SELECT * FROM " . $table_prefix . "articles_reviews ";
			$order_by = " ORDER BY date_added DESC";  
			$db->RecordsPerPage = $records_per_page;
			$db->PageNumber = $page_number;
			$db->query($sql . $where . $order_by);
			if($db->next_record())
			{
				$latest_comments = $db->f("comments");
				do 
				{
					$record_number++;
					$review_user_id = $db->f("user_id");
					$review_user_name = htmlspecialchars($db->f("user_name"));
					if (!$review_user_id) {
						$review_user_name .= " (" . GUEST_MSG . ")";
					}
					$review_user_class = $review_user_id ? "forumUser" : "forumGuest";
      
					if ($db->f("recommended") == 1) {
						$recommended_image = "commend";
					} else if ($db->f("recommended") == -1) {
						$recommended_image = "discommend";
					} else {
						$recommended_image = "neutral";
					}
					$t->set_var("recommended_image", $recommended_image);
					$rating = round($db->f("rating"), 0);
					$rating_image = $rating ? "rating-" . $rating : "not-rated";
					$t->set_var("rating_image", $rating_image);
					$t->set_var("review_user_class", $review_user_class);
					$t->set_var("review_user_name", $review_user_name);
					$date_added = $db->f("date_added", DATETIME);
					$date_added_string = va_date($datetime_show_format, $date_added);
					$t->set_var("review_date_added", $date_added_string);
					$t->set_var("review_summary", htmlspecialchars($db->f("summary")));
					$t->set_var("review_comments", nl2br(htmlspecialchars($db->f("comments"))));
      
					$t->parse("reviews_list", true);
				} while ($db->next_record());
				$t->parse("reviews", false);
			}
			else
				$t->parse("no_reviews", false);
		}
		else
		{
			$t->set_var("total_records", 0);
			$t->parse("no_reviews", false);
		}
	
		$t->parse("reviews_block", false);
	}

	$t->parse("item", false);
	$t->set_var("no_item", "");
	
	$block_parsed = true;

function check_validation_number()
{
	global $db, $rr;
	if($rr->get_property_value("validation_number", IS_VALID)) {
		$validated_number = check_image_validation($rr->get_value("validation_number"));
		if (!$validated_number) {
			$error_message = str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
			$rr->change_property("validation_number", IS_VALID, false);
			$rr->change_property("validation_number", ERROR_DESC, $error_message);
		} else {
			// saved validated number for following submits	and delete this value in case of success
			set_session("session_validation_number", $validated_number);
		}
	}
}

?>