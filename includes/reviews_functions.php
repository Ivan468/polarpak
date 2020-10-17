<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  reviews_functions.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                                        

function update_product_rating($items_ids)
{
	global $db, $table_prefix;

	$ids = explode(",", $items_ids);
	for ($i = 0; $i < sizeof($ids); $i++) {
		$item_id = $ids[$i];

		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
		$sql .= " WHERE approved=1 AND rating <> 0 AND item_id=" . $db->tosql($item_id, INTEGER);
		$total_rating_votes = get_db_value($sql);
  
		$sql  = " SELECT SUM(rating) FROM " . $table_prefix . "reviews ";
		$sql .= " WHERE approved=1 AND rating <> 0 AND item_id=" . $db->tosql($item_id, INTEGER);
		$total_rating_sum = get_db_value($sql);
		if(!strlen($total_rating_sum)) $total_rating_sum = 0;
  
		$average_rating = $total_rating_votes ? $total_rating_sum / $total_rating_votes : 0;
  
		$sql  = " UPDATE " . $table_prefix . "items ";
		$sql .= " SET votes=" . $total_rating_votes . ", points=" . $total_rating_sum . ", ";
		$sql .= " rating=" . $db->tosql($average_rating, NUMBER);
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
	}
}

function update_article_rating($articles_ids)
{
	global $db, $table_prefix;

	$ids = explode(",", $articles_ids);
	for ($i = 0; $i < sizeof($ids); $i++) {
		$article_id = $ids[$i];

		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews ";
		$sql .= " WHERE approved=1 AND rating <> 0 AND article_id=" . $db->tosql($article_id, INTEGER);
		$total_rating_votes = get_db_value($sql);
  
		$sql  = " SELECT SUM(rating) FROM " . $table_prefix . "articles_reviews ";
		$sql .= " WHERE approved=1 AND rating <> 0 AND article_id=" . $db->tosql($article_id, INTEGER);
		$total_rating_sum = get_db_value($sql);
		if(!strlen($total_rating_sum)) $total_rating_sum = 0;
  
		$average_rating = $total_rating_votes ? $total_rating_sum / $total_rating_votes : 0;

		$sql  = " UPDATE " . $table_prefix . "articles ";
		$sql .= " SET total_votes=" . $total_rating_votes . ", total_points=" . $total_rating_sum . ", ";
		$sql .= " rating=" . $db->tosql($average_rating, NUMBER);
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
	}
}

function check_add_review($params)
{
	global $db, $table_prefix;

	$type = get_setting_value($params, "type");
	$item_id = get_setting_value($params, "item_id");
	$article_id = get_setting_value($params, "article_id");
	$review_id = get_setting_value($params, "review_id");

	if ($type == "article_review" || $type == "article") {
		$review_settings = get_settings("articles_reviews"); 
		$allowed_post = get_setting_value($review_settings, "allowed_post", 0);
		$reviews_per_user = get_setting_value($review_settings, "reviews_per_user", "");
		$reviews_interval = get_setting_value($review_settings, "reviews_interval", "");
		$reviews_period = get_setting_value($review_settings, "reviews_period", "");
		$admin_permission = "articles_reviews";
	} elseif ($type == "ptqn_comment" || $type == "ptqn_reply") {
		$review_settings = get_settings("product_questions"); 
		$allowed_post = get_setting_value($review_settings, "allowed_comment", 0);
		$reviews_per_user = get_setting_value($review_settings, "comments_per_review", "");
		$reviews_interval = get_setting_value($review_settings, "comments_interval", "");
		$reviews_period = get_setting_value($review_settings, "comments_period", "");
		$admin_permission = "products_reviews";
	} elseif ($type == "product_question" || $type == "pt_question") {
		$review_settings = get_settings("product_questions"); 
		$allowed_post = get_setting_value($review_settings, "allowed_post", 0);
		$reviews_per_user = get_setting_value($review_settings, "reviews_per_user", "");
		$reviews_interval = get_setting_value($review_settings, "reviews_interval", "");
		$reviews_period = get_setting_value($review_settings, "reviews_period", "");
		$admin_permission = "products_reviews";
	} elseif ($type == "ptrw_comment" || $type == "ptrw_reply") {
		$review_settings = get_settings("products_reviews"); 
		$allowed_post = get_setting_value($review_settings, "allowed_comment", 0);
		$reviews_per_user = get_setting_value($review_settings, "comments_per_review", "");
		$reviews_interval = get_setting_value($review_settings, "comments_interval", "");
		$reviews_period = get_setting_value($review_settings, "comments_period", "");
		$admin_permission = "products_reviews";
	} else {
		// type - product_review
		$review_settings = get_settings("products_reviews"); 
		$allowed_post = get_setting_value($review_settings, "allowed_post", 0);
		$reviews_per_user = get_setting_value($review_settings, "reviews_per_user", "");
		$reviews_interval = get_setting_value($review_settings, "reviews_interval", "");
		$reviews_period = get_setting_value($review_settings, "reviews_period", "");
		$admin_permission = "products_reviews";
	}
	$admin_id = get_session("session_admin_id");
	if ($admin_id) {
		// adminstrators allowed to add reviews and comments only if they have appropriate permissions
		$permissions = get_admin_permissions();
		$admin_reviews = get_setting_value($permissions, $admin_permission, 0);
		if ($admin_reviews) {
			return true;
		}
	}
	$user_id = get_session("session_user_id");
	if ($allowed_post == 2 && !strlen($user_id)) {
		// guests are not allowed to post reviews or comments
		return false;
	}

	$new_review = true;
	if (strlen($reviews_per_user)) {
		$ip_address = get_ip();
	  
		if ($type == "article_review" || $type == "article") {
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews ";
			$sql .= " WHERE article_id=" . $db->tosql($id, INTEGER);
		} elseif ($type == "ptqn_comment" || $type == "ptqn_reply") {
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE parent_review_id=" . $db->tosql($review_id, INTEGER);
		} elseif ($type == "product_question" || $type == "pt_question") {
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND review_type=3 ";
			$sql .= " AND (parent_review_id=0 OR parent_review_id IS NULL) " ;
		} elseif ($type == "ptrw_comment" || $type == "ptrw_reply") {
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE parent_review_id=" . $db->tosql($review_id, INTEGER);
		} else {
			// type - product_review
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND review_type=1 ";
			$sql .= " AND (parent_review_id=0 OR parent_review_id IS NULL) " ;
		}
		if ($reviews_period && $reviews_interval) {
			// check time restrictions
			$cd = va_time();
			if ($reviews_period == 1) {
				$rd = mktime (0, 0, 0, $cd[MONTH], $cd[DAY] - $reviews_interval, $cd[YEAR]);
			} elseif ($reviews_period == 2) {
				$rd = mktime (0, 0, 0, $cd[MONTH], $cd[DAY] - ($reviews_interval * 7), $cd[YEAR]);
			} elseif ($reviews_period == 3) {
				$rd = mktime (0, 0, 0, $cd[MONTH] - $reviews_interval, $cd[DAY], $cd[YEAR]);
			} else {
				$rd = mktime (0, 0, 0, $cd[MONTH], $cd[DAY], $cd[YEAR] - $reviews_interval);
			}
			$sql .= " AND date_added>" . $db->tosql($rd, DATETIME);		
		}
		if ($user_id) {
			$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);		
		} else {
			$sql .= " AND remote_address=" . $db->tosql($ip_address, TEXT);		
		}
		$posted_reviews = get_db_value($sql);
		if ($posted_reviews >= $reviews_per_user) {
			$new_review = false;
		}
	}
	return $new_review;
}

function product_review_notify($review_data)
{
	global $t, $db, $settings, $table_prefix, $date_show_format, $datetime_show_format;

	// get global seite settigns 
	$site_url = get_setting_value($settings, "site_url", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	if (!isset($t)) { $t = new VA_Template("."); }

	$id = $review_data["id"];
	$notify_type = $review_data["type"];
	$notify_emails = isset($review_data["emails"]) ? $review_data["emails"] : array();

	$review_id = ""; $item_id = "";
	$comment_id = ""; $comment_admin_id = ""; $comment_user_id = ""; $comment_user_email = ""; $comment_user_name = ""; $comment_user_type = ""; 
	$comment_message = ""; $comment_date = ""; $comment_ip = ""; $comment_approved = 0; $comment_approved_desc = ""; $comment_notice_sent = 0;
	if ($notify_type == "comment" || $notify_type == "reply" || $notify_type == "notice") {
		// get comment/reply data
		$comment_id = $id; 
		$sql = " SELECT * FROM ".$table_prefix."reviews WHERE review_id=".$db->tosql($comment_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$review_id = $db->f("parent_review_id");
			$comment_admin_id = $db->f("admin_id");
			$comment_user_id = $db->f("user_id");
			$comment_message = $db->f("comments");
			$comment_date = $db->f("date_added", DATETIME);
			$comment_ip = $db->f("remote_address");
			$comment_approved = $db->f("approved");
			$comment_notice_sent = $db->f("notice_sent");
			if ($comment_approved == 1) {
				$comment_approved_desc = va_constant("YES_MSG");
			} else {
				$comment_approved_desc = va_constant("NO_MSG");
			}

			if ($comment_admin_id) {
				$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
				$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
				$sql .= " WHERE admin_id IN (".$db->tosql($comment_admin_id, INTEGER).")"; 
				$db->query($sql);
				if ($db->next_record()) {
					$comment_user_name = $db->f("nickname"); // show admin nickname for reviews if available
					if (!$comment_user_name) { $comment_user_name = $db->f("admin_name"); }
					$comment_user_email = $db->f("email");
					$comment_user_type = $db->f("privilege_name");
				}
			} else if ($comment_user_id) {
				$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
				$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
				$sql .= " WHERE user_id=" . $db->tosql($comment_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$comment_user_data = $db->Record;
					$comment_user_name = get_user_name($comment_user_data, "full");
					$comment_user_email = $db->f("email");
					$comment_user_type = $db->f("type_name");
				}
			} else {
				$comment_user_name = $db->f("user_name");
				$comment_user_email = $db->f("user_email");
				$comment_user_type = va_constant("GUEST_MSG");
			}
		} else {
			return;
		}
	} else if ($notify_type == "review") {
		$review_id = $id;
	}

	// get review data
	$sql = " SELECT * FROM ".$table_prefix."reviews WHERE review_id=".$db->tosql($review_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$review_data = $db->Record;
		$item_id = $db->f("item_id");
		$review_type = $db->f("review_type");
		$review_admin_id = $db->f("admin_id");
		$review_user_id = $db->f("user_id");
		$review_message = $db->f("comments");
		$review_date = $db->f("date_added", DATETIME);
		$review_ip = $db->f("remote_address");
		$review_approved = $db->f("approved");
		$review_recommended = $db->f("recommended");
		$review_rating = $db->f("rating");
		$review_summary = $db->f("summary");
		$review_impressions = "";
		if ($review_recommended == 1) {
			$review_impressions = va_constant("POSITIVE_MSG");
		} else if ($review_recommended == -1) {
			$review_impressions = va_constant("CRITICAL_MSG");
		} else if (strval($review_recommended) === strval("0")) {
			$review_impressions = va_constant("NEUTRAL_MSG");
		}
		if ($review_approved == 1) {
			$review_approved_desc = va_constant("YES_MSG");
		} else {
			$review_approved_desc = va_constant("NO_MSG");
		}

		if ($review_admin_id) {
			$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
			$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
			$sql .= " WHERE admin_id IN (".$db->tosql($review_admin_id, INTEGER).")"; 
			$db->query($sql);
			if ($db->next_record()) {
				$review_user_email = $db->f("email");
				$review_user_name = $db->f("nickname"); // show admin nickname for reviews if available
				if (!$review_user_name) { $review_user_name = $db->f("admin_name"); }
				$review_user_type = $db->f("privilege_name");
			}
		} else if ($review_user_id) {
			$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
			$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
			$sql .= " WHERE user_id=" . $db->tosql($review_user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$review_user_data = $db->Record;
				$review_user_name = get_user_name($review_user_data, "full");
				$review_user_email = $db->f("email");
				$review_user_type = $db->f("type_name");
			}
		} else {
			$review_user_name = $db->f("user_name");
			$review_user_email = $db->f("user_email");
			$review_user_type = va_constant("GUEST_MSG");
		}

		// check if we need to set notify email for reply notification
		if ($notify_type == "notice" && !$comment_notice_sent && count($notify_emails) == 0 && $review_user_email) {
			$notify_emails = array($review_user_email);
		}
	}

	// get and set product data
	$sql = " SELECT * FROM ".$table_prefix."items WHERE item_id=".$db->tosql($item_id, INTEGER);
	$db->query($sql); 	
	if ($db->next_record()) {
		$item_id = $db->f("item_id");
		$item_name = $db->f("item_name");

		$friendly_url = $db->f("friendly_url");
		if ($friendly_urls && strlen($friendly_url)) {
			$item_url = $site_url.$friendly_url.$friendly_extension;
		} else {
			$item_url = $site_url.get_custom_friendly_url("product_details.php")."?item_id=".urlencode($item_id);
		}
		$t->set_var("item_id", $item_id);
		$t->set_var("item_name", htmlspecialchars($item_name));
		$t->set_var("item_url", htmlspecialchars($item_url));
		$t->set_var("product_id", $item_id);
		$t->set_var("product_name", htmlspecialchars($item_name));
		$t->set_var("product_url", htmlspecialchars($item_url));
	}

	$t->set_var("site_url", htmlspecialchars($site_url));
	// set review/question variables for notifications
	$t->set_var("review_id", $item_id);
	$t->set_var("review_user_id", $review_user_id);
	$t->set_var("review_admin_id", $review_admin_id);
	$t->set_var("review_user_name", htmlspecialchars($review_user_name));
	$t->set_var("review_user_email", htmlspecialchars($review_user_email));
	$t->set_var("review_user_type", htmlspecialchars($review_user_type));
	$t->set_var("review_message", htmlspecialchars($review_message));
	$t->set_var("review_comment", htmlspecialchars($review_message));
	$t->set_var("review_comments", htmlspecialchars($review_message));
	$t->set_var("review_date", va_date($datetime_show_format, $review_date));
	$t->set_var("review_ip", htmlspecialchars($review_ip));
	$t->set_var("review_approved", htmlspecialchars($review_approved_desc));
	$t->set_var("review_rating", htmlspecialchars($review_rating));
	$t->set_var("review_impression", htmlspecialchars($review_impressions));
	$t->set_var("review_impressions", htmlspecialchars($review_impressions));
	$t->set_var("review_summary", htmlspecialchars($review_summary));

	$t->set_var("question_id", $item_id);
	$t->set_var("question_user_id", $review_user_id);
	$t->set_var("question_admin_id", $review_admin_id);
	$t->set_var("question_user_name", htmlspecialchars($review_user_name));
	$t->set_var("question_user_email", htmlspecialchars($review_user_email));
	$t->set_var("question_user_type", htmlspecialchars($review_user_type));
	$t->set_var("question_message", htmlspecialchars($review_message));
	$t->set_var("question_comment", htmlspecialchars($review_message));
	$t->set_var("question_comments", htmlspecialchars($review_message));
	$t->set_var("question_date", va_date($datetime_show_format, $review_date));
	$t->set_var("question_ip", htmlspecialchars($review_ip));
	$t->set_var("question_approved", htmlspecialchars($review_approved_desc));
	$t->set_var("question_rating", htmlspecialchars($review_rating));
	$t->set_var("question_impression", htmlspecialchars($review_impressions));
	$t->set_var("question_impressions", htmlspecialchars($review_impressions));
	$t->set_var("question_summary", htmlspecialchars($review_summary));

	// set some old variables for compatibility
	$t->set_var("date_added", va_date($datetime_show_format, $review_date));
	$t->set_var("user_id", $review_user_id);
	$t->set_var("admin_id", $review_admin_id);
	$t->set_var("user_name", htmlspecialchars($review_user_name));
	$t->set_var("user_email", htmlspecialchars($review_user_email));
	$t->set_var("user_type", htmlspecialchars($review_user_type));
	$t->set_var("rating", htmlspecialchars($review_rating));
	$t->set_var("summary", htmlspecialchars($review_summary));
	$t->set_var("message", htmlspecialchars($review_message));
	$t->set_var("comment", htmlspecialchars($review_message));
	$t->set_var("comments", htmlspecialchars($review_message));
	$t->set_var("remote_address", $review_ip);
	$t->set_var("is_recommended", $review_impressions);
	$t->set_var("recommended", $review_impressions);
	$t->set_var("is_approved", $review_approved_desc);
	$t->set_var("approved", $review_approved_desc);

	// set comment/reply variables
	$t->set_var("comment_id", $item_id);
	$t->set_var("comment_user_id", $comment_user_id);
	$t->set_var("comment_admin_id", $comment_admin_id);
	$t->set_var("comment_user_name", htmlspecialchars($comment_user_name));
	$t->set_var("comment_user_email", htmlspecialchars($comment_user_email));
	$t->set_var("comment_user_type", htmlspecialchars($comment_user_type));
	$t->set_var("comment_message", htmlspecialchars($comment_message));
	$t->set_var("comment_comment", htmlspecialchars($comment_message));
	$t->set_var("comment_comments", htmlspecialchars($comment_message));
	$t->set_var("comment_date", va_date($datetime_show_format, $comment_date));
	$t->set_var("comment_ip", htmlspecialchars($comment_ip));
	$t->set_var("comment_approved", htmlspecialchars($comment_approved_desc));

	$t->set_var("reply_id", $item_id);
	$t->set_var("reply_user_id", $comment_user_id);
	$t->set_var("reply_admin_id", $comment_admin_id);
	$t->set_var("reply_user_name", htmlspecialchars($comment_user_name));
	$t->set_var("reply_user_email", htmlspecialchars($comment_user_email));
	$t->set_var("reply_user_type", htmlspecialchars($comment_user_type));
	$t->set_var("reply_message", htmlspecialchars($comment_message));
	$t->set_var("reply_comment", htmlspecialchars($comment_message));
	$t->set_var("reply_comments", htmlspecialchars($comment_message));
	$t->set_var("reply_date", va_date($datetime_show_format, $comment_date));
	$t->set_var("reply_ip", htmlspecialchars($comment_ip));
	$t->set_var("reply_approved", htmlspecialchars($comment_approved_desc));

	// get product review settings to send notifications
	if ($review_type == 3 || $review_type == 4) {
		$setting_type = "product_questions";	
	} else {
		$setting_type = "products_reviews";	
	}
	$review_settings = get_settings($setting_type);

	if ($notify_type == "review" || $notify_type == "new_review" || $notify_type == "question" || $notify_type == "new_question") {
		$admin_notification = get_setting_value($review_settings, "admin_notification", 0);
		$user_notification = get_setting_value($review_settings, "user_notification", 0);

		// send email notification to administrator
		if ($admin_notification) {
			$mail_to = get_setting_value($review_settings, "admin_email", $settings["admin_email"]);
			$email_headers = array();
			$email_headers["from"] = get_setting_value($review_settings, "admin_mail_from", $settings["admin_email"]);
			$email_headers["cc"] = get_setting_value($review_settings, "admin_mail_cc");
			$email_headers["bcc"] = get_setting_value($review_settings, "admin_mail_bcc");
			$email_headers["reply_to"] = get_setting_value($review_settings, "admin_mail_reply_to");
			$email_headers["return_path"] = get_setting_value($review_settings, "admin_mail_return_path");
			$email_headers["mail_type"] = get_setting_value($review_settings, "admin_message_type");
  
			$admin_subject = get_setting_value($review_settings, "admin_subject", va_constant("NEW_REVIEW_NOTIFY_MSG"));
			$admin_message = get_setting_value($review_settings, "admin_message", $site_url."admin/admin_review.php?review_id=".$review_id);

			va_mail($mail_to, $admin_subject, $admin_message, $email_headers);
		}

		// send email notification to user
		if ($user_notification && $review_user_email) {
			$email_headers = array();
			$email_headers["from"] = get_setting_value($review_settings, "user_mail_from", $settings["admin_email"]);
			$email_headers["cc"] = get_setting_value($review_settings, "user_mail_cc");
			$email_headers["bcc"] = get_setting_value($review_settings, "user_mail_bcc");
			$email_headers["reply_to"] = get_setting_value($review_settings, "user_mail_reply_to");
			$email_headers["return_path"] = get_setting_value($review_settings, "user_mail_return_path");
			$email_headers["mail_type"] = get_setting_value($review_settings, "user_message_type");
  
			$user_subject = get_setting_value($review_settings, "user_subject", va_constant("NEW_REVIEW_NOTIFY_MSG"));
			$user_message = get_setting_value($review_settings, "user_message", $item_url);
  
			va_mail($review_user_email, $user_subject, $user_message, $email_headers);
		}
	}


	if ($notify_type == "comment" || $notify_type == "reply") {
		$admin_comment_notification = get_setting_value($review_settings, "admin_comment_notification", 0);
		$user_comment_notification = get_setting_value($review_settings, "user_comment_notification", 0);

		if ($admin_comment_notification) {
			$mail_to = get_setting_value($review_settings, "admin_comment_to", $settings["admin_email"]);
			$email_headers = array();
			$email_headers["from"] = get_setting_value($review_settings, "admin_comment_from", $settings["admin_email"]);
			$email_headers["cc"] = get_setting_value($review_settings, "admin_comment_cc");
			$email_headers["bcc"] = get_setting_value($review_settings, "admin_comment_bcc");
			$email_headers["reply_to"] = get_setting_value($review_settings, "admin_comment_reply_to");
			$email_headers["return_path"] = get_setting_value($review_settings, "admin_comment_return_path");
			$email_headers["mail_type"] = get_setting_value($review_settings, "admin_comment_message_type");
  
			$admin_subject = get_setting_value($review_settings, "admin_comment_subject", va_constant("NEW_COMMENT_NOTIFY_MSG"));
			$admin_message = get_setting_value($review_settings, "admin_comment_message", $site_url."admin/admin_review.php?review_id=".$review_id);

			va_mail($mail_to, $admin_subject, $admin_message, $email_headers);
		}

		// send email notification to user
		if ($user_comment_notification && $comment_user_email) {
			$email_headers = array();
			$email_headers["from"] = get_setting_value($review_settings, "user_comment_from", $settings["admin_email"]);
			$email_headers["cc"] = get_setting_value($review_settings, "user_comment_cc");
			$email_headers["bcc"] = get_setting_value($review_settings, "user_comment_bcc");
			$email_headers["reply_to"] = get_setting_value($review_settings, "user_comment_reply_to");
			$email_headers["return_path"] = get_setting_value($review_settings, "user_comment_return_path");
			$email_headers["mail_type"] = get_setting_value($review_settings, "user_comment_message_type");
  
			$user_subject = get_setting_value($review_settings, "user_comment_subject", va_constant("NEW_COMMENT_NOTIFY_MSG"));
			$user_message = get_setting_value($review_settings, "user_comment_message", $item_url);
  
			va_mail($comment_user_email, $user_subject, $user_message, $email_headers);
		}

		// check if we can send notification about reply to the reviewer
		if ($comment_approved && !$comment_notice_sent && $review_user_email) {
			$notify_type = "notice";
			$notify_emails = array($review_user_email);
		}
	}


	// send reply notification to the reviewer
	if ($notify_type == "notice" && is_array($notify_emails) && count($notify_emails) > 0) {
		if ($comment_admin_id) {
			$admin_reply_notification = get_setting_value($review_settings, "admin_reply_notification", 0);
			if ($admin_reply_notification) {
				$email_headers = array();
				$email_headers["from"] = get_setting_value($review_settings, "admin_reply_from", $settings["admin_email"]);
				$email_headers["cc"] = get_setting_value($review_settings, "admin_reply_cc");
				$email_headers["bcc"] = get_setting_value($review_settings, "admin_reply_bcc");
				$email_headers["reply_to"] = get_setting_value($review_settings, "admin_reply_reply_to");
				$email_headers["return_path"] = get_setting_value($review_settings, "admin_reply_return_path");
				$email_headers["mail_type"] = get_setting_value($review_settings, "admin_reply_message_type");
      
				$user_subject = get_setting_value($review_settings, "admin_reply_subject", va_constant("REVIEWER_NOTIFY_MSG"));
				$user_message = get_setting_value($review_settings, "admin_reply_message", $item_url);
      
				foreach ($notify_emails as $user_email) {
					$comment_notice_sent = true;
					va_mail($user_email, $user_subject, $user_message, $email_headers);
				}
			}
		} else {
			$user_reply_notification = get_setting_value($review_settings, "user_reply_notification", 0);
			if ($user_reply_notification) {
				$email_headers = array();
				$email_headers["from"] = get_setting_value($review_settings, "user_reply_from", $settings["admin_email"]);
				$email_headers["cc"] = get_setting_value($review_settings, "user_reply_cc");
				$email_headers["bcc"] = get_setting_value($review_settings, "user_reply_bcc");
				$email_headers["reply_to"] = get_setting_value($review_settings, "user_reply_reply_to");
				$email_headers["return_path"] = get_setting_value($review_settings, "user_reply_return_path");
				$email_headers["mail_type"] = get_setting_value($review_settings, "user_reply_message_type");
      
				$user_subject = get_setting_value($review_settings, "user_reply_subject", va_constant("REVIEWER_NOTIFY_MSG"));
				$user_message = get_setting_value($review_settings, "user_reply_message", $item_url);
      
				foreach ($notify_emails as $user_email) {
					$comment_notice_sent = true;
					va_mail($user_email, $user_subject, $user_message, $email_headers);
				}
			}
		}
		if ($comment_notice_sent) {
			// set notice_sent flag to exclude duplicated notifications
			$sql = " UPDATE ".$table_prefix."reviews SET notice_sent=1 WHERE review_id=".$db->tosql($comment_id, INTEGER);
			$db->query($sql);
		}
	}

}

function check_add_article_review($article_id)
{
	return check_add_review(array("article_id" => $article_id, "type" => "article"));
}

/*** reviews form functions ***/
function check_content($parameter)
{
	global $rr;
	$control_name = $parameter[CONTROL_NAME];
	if ($parameter[IS_VALID] && check_banned_content($parameter[CONTROL_VALUE])) {
		$rr->parameters[$control_name][IS_VALID] = false;
		$rr->parameters[$control_name][ERROR_DESC] = "<b>".$parameter[CONTROL_DESC]."</b>: ".BANNED_CONTENT_MSG;
	}
}

function additional_review_checks()
{
	global $rr, $review_type_name;
	if (blacklist_check("products_reviews") == "blocked") {
		$rr->errors = BLACK_IP_MSG."<br>";	
	} else if (!check_add_review(array("item_id" => $rr->get_value("item_id"), "type" => $review_type_name))) {
		$rr->errors = ALREADY_REVIEWED_MSG."<br>";
	}
}

function additional_article_review_checks()
{
	global $rr;
	if (blacklist_check("articles_reviews") == "blocked") {
		$rr->errors = BLACK_IP_MSG."<br>";	
	} else if (!check_add_article_review($rr->get_value("article_id"))) {
		$rr->errors = ALREADY_REVIEWED_MSG."<br>";
	}
}

/*
		$review_settings = get_settings("product_questions"); 
		$review_settings = get_settings("products_reviews"); 
		$review_type_name = "product_review";
		$reply_type = 2;
		$reply_type_name = "ptrw_comment";;
*/

function before_insert_review()
{
	global $rr, $db, $table_prefix, $review_type_name;
	if ($review_type_name == "product_question") {
		$review_settings = get_settings("product_questions"); 
	} else {
		$review_settings = get_settings("products_reviews"); 
	}
	$auto_approve = get_setting_value($review_settings, "auto_approve", 0);
	$approved = ($auto_approve == 1) ? 1 : 0;
	$rr->set_value("review_type", 1); // 1 - basic product review
	$rr->set_value("date_added", va_time());
	$rr->set_value("remote_address", get_ip());
	$rr->set_value("approved", $approved);
	$rr->set_value("user_id", get_session("session_user_id"));
	$user_id = get_session("session_user_id");
	if ($user_id) {	
		$user_info = get_session("session_user_info");
		$user_nickname = get_setting_value($user_info, "nickname", "");
		$user_email = get_setting_value($user_info, "email", "");
		if (strlen($user_nickname)) {
			$rr->set_value("user_name", $user_nickname);
		}
		if (strlen($user_email)) {
			$rr->set_value("user_email", $user_email);
		}
	}

	// set automatically recommended value based on rating if it wasn't set
	if ($rr->is_empty("recommended")) {
		$rating = $rr->get_value("rating");
		if ($rating == 4 || $rating == 5) {
			$rr->set_value("recommended", 1);
		} else if ($rating == 1 || $rating == 2) {
			$rr->set_value("recommended", -1);
		} else {
			$rr->set_value("recommended", 0);
		}		
	}

	// check if customer is verified buyer
	$item_id = $rr->get_value("item_id"); 
	$verified_buyer = 0;
	$sql  = " SELECT oi.order_item_id FROM (".$table_prefix."orders_items oi ";
	$sql .= " INNER JOIN ".$table_prefix."order_statuses os ON oi.item_status=os.status_id) ";
	$sql .= " WHERE oi.item_id=".$db->tosql($item_id, INTEGER);
	$sql .= " AND oi.user_id=".$db->tosql($user_id, INTEGER);
	$sql .= " AND os.paid_status=1 ";
	$db->query($sql);
	if ($db->next_record()) {
		$verified_buyer = 1;
	}
	$rr->set_value("verified_buyer", $verified_buyer);

}

function after_insert_review($params)
{
	global $rr, $db, $table_prefix, $t, $settings, $product_info, $datetime_show_format;

	// record was added clear validation passed variable
	$validation_id = $params["validation_id"];
	$validation_passed = get_session("session_validation_passed");
	if (isset($validation_passed[$validation_id])) {
		unset($validation_passed[$validation_id]);
	}
	set_session("session_validation_passed", $validation_passed);

	// if review was approved update it rating
	if ($rr->get_value("approved") == 1) {
		update_product_rating($rr->get_value("item_id"));
	}

	// get last review id
	$new_review_id = $db->last_insert_id();
	$rr->set_value("review_id", $new_review_id);

	product_review_notify(array("id" => $new_review_id, "type" => "review"));

	// clear values and set default
	$rr->empty_values();
	$rr->set_default_values();
}

function before_insert_article_review()
{
	global $rr, $db, $articles_reviews_settings;
	$auto_approve = get_setting_value($articles_reviews_settings, "auto_approve", 1);
	$approved = ($auto_approve == 1) ? 1 : 0;
	$rr->set_value("date_added", va_time());
	$rr->set_value("remote_address", get_ip());
	$rr->set_value("approved", $approved);
	$rr->set_value("user_id", get_session("session_user_id"));
	$user_id = get_session("session_user_id");
	if ($user_id) {	
		$user_info = get_session("session_user_info");
		$user_nickname = get_setting_value($user_info, "nickname", "");
		$user_email = get_setting_value($user_info, "email", "");
		if (strlen($user_nickname)) {
			$rr->set_value("user_name", $user_nickname);
		}
		if (strlen($user_email)) {
			$rr->set_value("user_email", $user_email);
		}
	}

	if ($db->DBType == "postgre") {
		$sql = " SELECT NEXTVAL('seq_" . $table_prefix . "articles_reviews ') ";
		$new_review_id = get_db_value($sql);
		$rr->set_value("review_id", $new_review_id);
		$rr->change_property("review_id", USE_IN_INSERT, true);
	}

}

function after_insert_article_review()
{
	global $rr, $db, $t, $settings, $articles_reviews_settings, $article_info, $datetime_show_format;

	// record was added clear validation variable
	set_session("session_validation_number", "");

	// if review was approved update it rating
	if ($rr->get_value("approved") == 1) {
		update_article_rating($rr->get_value("article_id"));
	}

	// get last review id
	if ($db->DBType == "mysql") {
		$sql = " SELECT LAST_INSERT_ID() ";
		$new_review_id = get_db_value($sql);
		$rr->set_value("review_id", $new_review_id);
	} else if ($db->DBType == "access") {
		$sql = " SELECT @@IDENTITY ";
		$new_review_id = get_db_value($sql);
		$rr->set_value("review_id", $new_review_id);
	} else if ($db->DBType == "db2") {
		$new_review_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "articles_reviews FROM " . $table_prefix . "articles_reviews ");
		$rr->set_value("review_id", $new_review_id);
	}

	// check settings to send notifications
	$eol = get_eol();
	$admin_notification = get_setting_value($articles_reviews_settings, "admin_notification", 0);
	$user_email = $rr->get_value("user_email");
	$user_notification = get_setting_value($articles_reviews_settings, "user_notification", 0);
	if ($admin_notification || ($user_notification && $user_email)) {
		// set variables for email notifications
		$t->set_vars($article_info);
		$t->set_var("review_id", $rr->get_value("review_id"));
		$t->set_var("user_id", $rr->get_value("user_id"));
		$date_added_formatted = va_date($datetime_show_format, $rr->get_value("date_added"));
		$t->set_var("date_added", $date_added_formatted);

		$t->set_var("remote_address", $rr->get_value("remote_address"));
		$approved = $rr->get_value("approved");
		if ($approved == 1) {
			$approved_desc = YES_MSG;
		} else {
			$approved_desc = NO_MSG;
		}
		$t->set_var("is_approved", $approved_desc);
		$t->set_var("approved", $approved_desc);

		$recommended = $rr->get_value("recommended");
		if ($recommended == 1) {
			$recommended_desc = YES_MSG;
		} else if ($recommended == -1) {
			$recommended_desc = NO_MSG;
		} else {
			$recommended_desc = "";
		}
		$t->set_var("is_recommended", $recommended_desc);
		$t->set_var("recommended", $recommended_desc);
		$t->set_var("rating", $rr->get_value("rating"));
		$t->set_var("user_name", $rr->get_value("user_name"));
		$t->set_var("user_email", $rr->get_value("user_email"));
		$t->set_var("summary", $rr->get_value("summary"));
		$t->set_var("comments", $rr->get_value("comments"));
	}

	// send email notification to admin
	if ($admin_notification)
	{
		$t->set_block("admin_subject", $articles_reviews_settings["admin_subject"]);
		$t->set_block("admin_message", $articles_reviews_settings["admin_message"]);

		$mail_to = get_setting_value($articles_reviews_settings, "admin_email", $settings["admin_email"]);
		$mail_to = str_replace(";", ",", $mail_to);
		$email_headers = array();
		$email_headers["from"] = get_setting_value($articles_reviews_settings, "admin_mail_from", $settings["admin_email"]);
		$email_headers["cc"] = get_setting_value($articles_reviews_settings, "admin_mail_cc");
		$email_headers["bcc"] = get_setting_value($articles_reviews_settings, "admin_mail_bcc");
		$email_headers["reply_to"] = get_setting_value($articles_reviews_settings, "admin_mail_reply_to");
		$email_headers["return_path"] = get_setting_value($articles_reviews_settings, "admin_mail_return_path");
		$email_headers["mail_type"] = get_setting_value($articles_reviews_settings, "admin_message_type");

		$t->parse("admin_subject", false);
		$t->parse("admin_message", false);

		$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
		va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
	}

	// send email notification to user
	if ($user_notification && $user_email)
	{
		$t->set_block("user_subject", $articles_reviews_settings["user_subject"]);
		$t->set_block("user_message", $articles_reviews_settings["user_message"]);

		$email_headers = array();
		$email_headers["from"] = get_setting_value($articles_reviews_settings, "user_mail_from", $settings["admin_email"]);
		$email_headers["cc"] = get_setting_value($articles_reviews_settings, "user_mail_cc");
		$email_headers["bcc"] = get_setting_value($articles_reviews_settings, "user_mail_bcc");
		$email_headers["reply_to"] = get_setting_value($articles_reviews_settings, "user_mail_reply_to");
		$email_headers["return_path"] = get_setting_value($articles_reviews_settings, "user_mail_return_path");
		$email_headers["mail_type"] = get_setting_value($articles_reviews_settings, "user_message_type");

		$t->parse("user_subject", false);
		$t->parse("user_message", false);

		$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
		va_mail($user_email, $t->get_var("user_subject"), $user_message, $email_headers);
	}

	// clear values and set default
	$rr->empty_values();
	$rr->set_default_values();
}


function article_review_form_check()
{
	global $rr, $articles_reviews_settings;
	$allowed_post = get_setting_value($articles_reviews_settings, "allowed_post", 0);

	if (!$allowed_post) {
		$rr->record_show = false;	
		$rr->success_message = NOT_ALLOWED_ADD_REVIEW_MSG;
	} else if ($allowed_post == 2 && !get_session("session_user_id")) {
		$rr->record_show = false;	
		$rr->success_message = REGISTERED_USERS_ADD_REVIEWS_MSG;
	} else if (blacklist_check("articles_reviews") == "blocked") {
		$rr->record_show = false;	
		$rr->errors = BLACK_IP_MSG;	
	} else if (!check_add_article_review($rr->get_value("article_id"))) {
		$rr->record_show = false;	
		if (!$rr->success_message) {
			$rr->success_message = ALREADY_REVIEWED_MSG;
		}
	}
}

function review_double_save()
{
	global $rr;
	$rr->operation = "double";
	$rr->success_message = COMMENTS_SAVED_FOR_APPROVAL_MSG;
	$rr->empty_values();
	$rr->set_default_values();
}

function check_validation_number($params)
{
	global $db, $rr;
	if($rr->get_property_value("validation_number", IS_VALID)) {
		$validation_id = isset($params["validation_id"]) ? $params["validation_id"] : "";
		$validated_number = check_image_validation($rr->get_value("validation_number"), $validation_id);
		if (!$validated_number) {
			$error_message = str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
			$rr->change_property("validation_number", IS_VALID, false);
			$rr->change_property("validation_number", ERROR_DESC, $error_message);
		} else {
			// saved validated number for following submits	and delete this value in case of success
			$validation_passed = get_session("session_validation_passed");
			if (!is_array($validation_passed)) { $validation_passed = array(); }
			$validation_passed[$validation_id] = $validated_number;
			set_session("session_validation_passed", $validation_passed);
		}
	}
}

function rw_set_hide_block($params)
{
	global $rr;
	$rr->default_class = "hide-block";
}
function rw_clear_hide_block($params)
{
	global $rr;
	$rr->default_class = "";
}

function check_comment_form($review_id, &$errors)
{
	global $reply_type_name;
	if ($reply_type_name == "ptqn_reply" || $reply_type_name == "ptqn_comment") {
		$review_settings = get_settings("product_questions"); 
	} else {
		$review_settings = get_settings("products_reviews"); 
	}
	$allowed_comment = get_setting_value($review_settings, "allowed_comment", 0);
	$user_id = get_session("session_user_id");
	$admin_id = get_session("session_admin_id");

	$comment_form = true;
	if (!$allowed_comment) {
		$comment_form = false;	
		$errors = va_constant("NOT_ALLOWED_ADD_COMMENTS_MSG");
	} else if ($allowed_comment == 2 && !$user_id && !$admin_id) {
		$comment_form = false;	
		$errors  = va_constant("ONLY_USERS_ADD_COMMENTS_MSG");
	} else if (blacklist_check("products_reviews") == "blocked") {
		$comment_form = false;	
		$errors = va_constant("BLACK_IP_MSG");	
	} else if (!check_add_review(array("review_id" => $review_id, "type" => $reply_type_name))) {
		$comment_form = false;	
		$errors = va_constant("NOT_ALLOWED_MORE_COMMENTS_MSG");
	}
	return $comment_form;
}

function check_review_form($item_id, &$errors)
{
	global $review_type_name;
	if ($review_type_name == "product_question" || $review_type_name == "pt_question") {
		$review_settings = get_settings("product_questions"); 
	} else {
		$review_settings = get_settings("products_reviews"); 
	}
	$allowed_post = get_setting_value($review_settings, "allowed_post", 0);
	$user_id = get_session("session_user_id");
	$admin_id = get_session("session_admin_id");

	$review_form = true;
	if (!$allowed_post) {
		$review_form = false;	
		$errors = va_constant("NOT_ALLOWED_ADD_REVIEW_MSG");
	} else if ($allowed_post == 2 && !$user_id && !$admin_id) {
		$review_form = false;	
		$errors  = va_constant("REGISTERED_USERS_ADD_REVIEWS_MSG");
	} else if (blacklist_check("products_reviews") == "blocked") {
		$review_form = false;	
		$errors = va_constant("NOT_ALLOWED_ADD_REVIEW_MSG")." ".va_constant("BLACK_IP_MSG");	
	} else if (!check_add_review(array("item_id" => $item_id, "type" => $review_type_name))) {
		$review_form = false;	
		$errors = va_constant("ALREADY_REVIEWED_PRODUCT_MSG");
	}
	return $review_form;
}
