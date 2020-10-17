<?php                           

	if (!isset($script_run_mode)) { $script_run_mode = ""; }
	if ($script_run_mode != "include") {
		$default_title = "{reviewed_item_name} :: {REVIEWS_MSG}";
	}

	include_once("./includes/record.php");
	include_once("./includes/navigator.php");
	include_once("./includes/reviews_functions.php");
	include_once("./includes/profile_functions.php");
	include_once("./messages/".$language_code."/reviews_messages.php");

	// get request parameters
	$ajax = get_param("ajax");
	$item_id = get_param("item_id");
	$category_id = get_param("category_id");
	$review_id = get_param("review_id");
	$param_pb_id = get_param("pb_id");
	$operation = get_param("operation");
	$filter = get_param("filter");
	$fr_emotion = get_param("fr_emotion");
	$fr_rating = get_param("fr_rating");
	$remote_address = get_ip();

	// get block settings
	$block_type = get_setting_value($vars, "block_type");
	$block_code = get_setting_value($vars, "block_code");
	if (!$block_code) { $block_code = $cms_block_code; }
	if ($block_type == "sub_block") {
		if ($block_code == "product_questions") {
			$html_template = "block_questions.html"; 
			$block_tag = "block_questions";
			$html_id = "questions_".$pb_id;
		} else {
			$html_template = "block_reviews.html"; 
			$block_tag = "block_reviews";
			$html_id = "reviews_".$pb_id;
		}
	} else {
		if ($block_code == "product_questions") {
			$html_template = get_setting_value($block, "html_template", "block_questions.html"); 
		} else {
			$html_template = get_setting_value($block, "html_template", "block_reviews.html"); 
		}
		$block_tag = "block_body";
		$html_id = "";
	}
	if ($block_code == "product_questions") {
		$review_type = 3;
		$review_type_name = "product_question";
		$reply_type = 4;
		$reply_type_name = "ptqn_reply";
		$reviews_tab = "questions";
		$setting_type = "product_questions";
		$pb_type = "product_questions";
		$review_validation_id = "ptqn-".$item_id;
		$pagination_param = "pgqn";
		$admin_permission = "products_reviews";
	} else {
		$review_type = 1;
		$review_type_name = "product_review";
		$reply_type = 2;
		$reply_type_name = "ptrw_comment";;
		$reviews_tab = "reviews";
		$setting_type = "products_reviews";
		$pb_type = "product_reviews";
		$review_validation_id = "ptrw-".$item_id;
		$pagination_param = "pgrw";
		$admin_permission = "products_reviews";
	}

	// get products reviews settings
	$reviews_settings = get_settings($setting_type);

	$user_id = get_session("session_user_id");
	$admin_id = get_session("session_admin_id");
	if ($admin_id) {
		// if adminstrator doesn't has necessary reviews permissions he can add review only as user
		$permissions = get_admin_permissions();
		$admin_reviews = get_setting_value($permissions, $admin_permission, 0);
		if (!$admin_reviews) {
			$admin_id = "";
		}
	}

	$allowed_view = get_setting_value($reviews_settings, "allowed_view", 0);
	$allowed_post = get_setting_value($reviews_settings, "allowed_post", 0);
	$allowed_like = get_setting_value($reviews_settings, "allowed_like", 0);
	$allowed_dislike = get_setting_value($reviews_settings, "allowed_dislike", 0);
	$allowed_comment = get_setting_value($reviews_settings, "allowed_comment", 0);
	$reviews_per_page = get_setting_value($reviews_settings, "reviews_per_page", 10);
	$review_random_image = get_setting_value($reviews_settings, "review_random_image", 1);
	$comment_random_image = get_setting_value($reviews_settings, "comment_random_image", 1);

	// check image validation 
	$validation_passed = get_session("session_validation_passed");
	if (!is_array($validation_passed)) { $validation_passed = array(); }
	if ($admin_id) {
		$review_image_validation = false;
	} else if (($review_random_image == 2) || ($review_random_image == 1 && !strlen($user_id))) { 
		$review_image_validation = true;
	} else {
		$review_image_validation = false;
	}
	if ($admin_id) {
		$comment_validation = false;
	} else if (($comment_random_image == 2) || ($comment_random_image == 1 && !strlen($user_id))) { 
		$comment_validation = true;
	} else {
		$comment_validation = false;
	}

	$recommended = 
		array( 
			array(1, va_message("POSITIVE_MSG")), array(0, va_message("NEUTRAL_MSG")), array(-1, va_message("NEGATIVE_MSG"))
			);
	$rating = 
		array( 
			array("", ""), array(1, BAD_MSG), array(2, POOR_MSG), 
			array(3, AVERAGE_MSG), array(4, GOOD_MSG), array(5, EXCELLENT_MSG),
			);

	$rr = new VA_Record($table_prefix . "reviews");
	//$rr->success_messages[INSERT_SUCCESS] = COMMENTS_SAVED_FOR_APPROVAL_MSG;
	//$rr->errors_class = "fd-error";

	// internal fields
	$rr->add_where("review_id", INTEGER);
	$rr->add_textbox("review_type", INTEGER);
	$rr->add_textbox("parent_review_id", INTEGER);
	$rr->add_textbox("item_id", INTEGER);
	$rr->change_property("item_id", DEFAULT_VALUE, $item_id);
	$rr->add_textbox("user_id", INTEGER);
	$rr->change_property("user_id", USE_SQL_NULL, false);
	$rr->add_textbox("admin_id", INTEGER);
	$rr->change_property("admin_id", USE_SQL_NULL, false);
	$rr->add_textbox("date_added", DATETIME);
	$rr->add_textbox("remote_address", TEXT);
	$rr->add_textbox("approved", INTEGER);
	$rr->add_textbox("verified_buyer", INTEGER);

	// predefined fields
	$rr->add_radio("recommended", INTEGER, $recommended, IMPRESSION_MSG);
	$rr->change_property("recommended", USE_SQL_NULL, false);
	$rr->add_textbox("rating", INTEGER, RATING_MSG);
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

	// validation number field
	$rr->add_textbox("validation_number", TEXT, VALIDATION_CODE_FIELD);
	$rr->change_property("validation_number", USE_IN_INSERT, false);
	$rr->change_property("validation_number", USE_IN_UPDATE, false);
	$rr->change_property("validation_number", USE_IN_SELECT, false);

	if ($ajax && $operation) {	
		$ajax_data = array(
			"pb_id" => $pb_id,
			"pb_type" => $pb_type,
			"parse" => false,
			"recommended_valid" => true,
			"rating_valid" => true,
			"user_name_valid" => true,
			"user_email_valid" => true,
			"summary_valid" => true,
			"comments_valid" => true,
			"validation_number_valid" => true,
		);

		if ($operation == "review") {	
			$ajax_data["item_id"] = $item_id;
			if (check_review_form($item_id, $errors)) {
				// check review parameters settings 
				$review_params = array(
					1 => "recommended", 2 => "rating", 3 => "user_name", 4 => "user_email",  5 => "summary", 6 => "comments",
				);    
				foreach ($review_params as $param_order => $param_name) {
					$param_order = get_setting_value($reviews_settings, $param_name . "_order", $param_order);
					$show_param = get_setting_value($reviews_settings, "show_".$param_name, false);
					$param_required = get_setting_value($reviews_settings, $param_name . "_required", false);
					$rr->change_property($param_name, SHOW, $show_param);
					$rr->change_property($param_name, CONTROL_ORDER, $param_order);
					$rr->change_property($param_name, REQUIRED, $param_required);
					$rr->change_property($param_name, TRIM, true);
				}
				// for user and administator set user fields as non required
				if ($user_id || $admin_id) {
					$rr->change_property("user_name", REQUIRED, false);
					$rr->change_property("user_email", REQUIRED, false);
				}
				// check image validation option
				if ($review_image_validation && !isset($validation_passed[$review_validation_id])) {
					$rr->change_property("validation_number", REQUIRED, true);
					$rr->change_property("validation_number", AFTER_VALIDATE, "check_validation_number", array("validation_id" => $review_validation_id));
				}

				// get review form parameters and validate them
				$rr->get_form_parameters();
				$is_valid = $rr->validate();
				$ajax_data["recommended_valid"] = $rr->get_property_value("recommended", IS_VALID);
				$ajax_data["rating_valid"] = $rr->get_property_value("rating", IS_VALID);
				$ajax_data["user_name_valid"] = $rr->get_property_value("user_name", IS_VALID);
				$ajax_data["user_email_valid"] = $rr->get_property_value("user_name", IS_VALID); 
				$ajax_data["summary_valid"] = $rr->get_property_value("summary", IS_VALID);
				$ajax_data["comments_valid"] = $rr->get_property_value("comments", IS_VALID);
				$ajax_data["validation_number_valid"] = $rr->get_property_value("validation_number", IS_VALID);
				if ($is_valid) {
					// check auto-approve option
					if ($admin_id) {
						$review_approved = 1; // approve administrator posts
					} else {
						$auto_approve = get_setting_value($reviews_settings, "auto_approve", 0);
						$review_approved = ($auto_approve == 1) ? 1 : 0;
					}
					// check if customer is verified buyer
					$verified_buyer = 0;
					if ($user_id && !$admin_id) {
						$sql  = " SELECT oi.order_item_id FROM (".$table_prefix."orders_items oi ";
						$sql .= " INNER JOIN ".$table_prefix."order_statuses os ON oi.item_status=os.status_id) ";
						$sql .= " WHERE oi.item_id=".$db->tosql($item_id, INTEGER);
						$sql .= " AND oi.user_id=".$db->tosql($user_id, INTEGER);
						$sql .= " AND os.paid_status=1 ";
						$db->query($sql);
						if ($db->next_record()) {
							$verified_buyer = 1;
						}
					}
					// set parameters
					$review_date_added = va_time();
					$rr->set_value("review_type", $review_type); // 1 - product review, 3 - product question
					$rr->set_value("date_added", $review_date_added);
					$rr->set_value("remote_address", get_ip());
					$rr->set_value("approved", $review_approved);
					$rr->set_value("verified_buyer", $verified_buyer);
					if ($admin_id) {
						$rr->set_value("admin_id", $admin_id);
					} else if ($user_id) {
						$rr->set_value("user_id", $user_id);
					}
					// add a new user review
					$db->HaltOnError = "no";
					$review_saved = $rr->insert_record();
					if ($review_saved) {
						// get last review id
						$new_review_id = $db->last_insert_id();
						product_review_notify(array("id" => $new_review_id, "type" => "review"));

						// comment was added clear validation passed variable
						if (isset($validation_passed[$review_validation_id])) {
							unset($validation_passed[$review_validation_id]);
						}
						set_session("session_validation_passed", $validation_passed);

						$more_reviews = check_add_review(array("item_id" => $item_id, "type" => $review_type_name)); // type - product_question or product_review
						$ajax_data["saved"] = 1;	
						$ajax_data["more_reviews"] = $more_reviews;	
						$ajax_data["already_reviewed_msg"] = va_message("ALREADY_REVIEWED_PRODUCT_MSG");	
						$ajax_data["already_asked_msg"] = va_message("ALREADY_ASKED_PRODUCT_MSG");	

						if (!$review_approved) {
							if ($review_type == 3) {
								$ajax_data["message"] = va_message("QUESTION_SAVED_FOR_APPROVAL_MSG");	
							} else {
								$ajax_data["message"] = va_message("REVIEW_SAVED_FOR_APPROVAL_MSG");	
							}
							$ajax_data["show"] = 0;	
						} else {
							$ajax_json = 1; // return parsed block data as JSON object
							if ($review_type == 3) {
								$ajax_data["message"] = va_message("QUESTION_SAVED_MSG");	
							} else {
								$ajax_data["message"] = va_message("REVIEW_SAVED_MSG");	
							}
							$ajax_data["show"] = 1;	
							$ajax_data["parse"] = true;	
						}
					} else {
						$errors  = va_message("DATABASE_ERROR_MSG");
						$errors .= "<br>".$db->error;
					}
				} else {
					$errors = $rr->errors;
				}
			}
			if ($errors) { 
				$ajax_data["errors"] = $errors;
			}
		} else if ($operation == "comment") {	
			$ajax_data["review_id"] = $review_id;
			$comment_validation_id = "rw-".$review_id;
			// check if user allowed to post comment to selected review first
			$comment_form = check_comment_form($review_id, $errors);
			if ($comment_form) {
				$comment_user_name = get_param("comment_user_name");
				$comment_user_email = get_param("comment_user_email");
				$comment_comments = get_param("comment_comments");
				$comment_validation_number = get_param("comment_validation_number");
				// check auto-approve option
				if ($admin_id) {
					$comments_approved = 1; // approve administrator posts
				} else {
					$comments_auto_approve = get_setting_value($reviews_settings, "comments_approve", 0);
					$comments_approved = ($comments_auto_approve == 1) ? 1 : 0;
				}

				// validate fields
				$rr->change_property("parent_review_id", REQUIRED, true);
				$rr->change_property("comments", REQUIRED, true);
				if ($reply_type == 4) {
					$rr->change_property("comments", CONTROL_DESC, va_message("YOUR_ANSWER_MSG"));
				} else {
					$rr->change_property("comments", CONTROL_DESC, va_message("YOUR_COMMENT_MSG"));
				}
				// for user and administator set user fields as non required
				if ($user_id || $admin_id) {
					$rr->change_property("user_name", REQUIRED, false);
					$rr->change_property("user_email", REQUIRED, false);
				} else {
					$rr->set_value("user_name", $comment_user_name);
					$rr->set_value("user_email", $comment_user_email);
				}
				if ($comment_validation && !isset($validation_passed["rw-".$review_id])) {
					$rr->change_property("validation_number", REQUIRED, true);
					$rr->change_property("validation_number", AFTER_VALIDATE, "check_validation_number", array("validation_id" => $comment_validation_id));
					$rr->set_value("validation_number", $comment_validation_number);
				}
				// check if customer is verified buyer
				$verified_buyer = 0;
				if ($user_id && !$admin_id) {
					$sql  = " SELECT oi.order_item_id FROM (".$table_prefix."orders_items oi ";
					$sql .= " INNER JOIN ".$table_prefix."order_statuses os ON oi.item_status=os.status_id) ";
					$sql .= " WHERE oi.item_id=".$db->tosql($item_id, INTEGER);
					$sql .= " AND oi.user_id=".$db->tosql($user_id, INTEGER);
					$sql .= " AND os.paid_status=1 ";
					$db->query($sql);
					if ($db->next_record()) {
						$verified_buyer = 1;
					}
				}

				$reply_date_added = va_time();
				$rr->set_value("review_type", $reply_type); // 2 - comment for review, 4 - answer for question
				$rr->set_value("parent_review_id", $review_id);
				$rr->set_value("item_id", $item_id);
				$rr->set_value("comments", $comment_comments);
				$rr->set_value("date_added", $reply_date_added);
				$rr->set_value("remote_address", get_ip());
				$rr->set_value("approved", $comments_approved);
				$rr->set_value("verified_buyer", $verified_buyer);
				if ($admin_id) {
					$rr->set_value("admin_id", $admin_id);
					$reply_user_class = "site-admin";
					$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
					$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
					$sql .= " WHERE admin_id=".$db->tosql($admin_id, INTEGER); 
					$db->query($sql);
					while ($db->next_record()) {
						$reply_user_type = $db->f("privilege_name");
						$reply_user_name = $db->f("nickname"); // show admin nickname for reviews if available
						if (!$reply_user_name) { $reply_user_name = $db->f("admin_name"); }
					}
				} else if ($user_id) {
					$rr->set_value("user_id", $user_id);
					$reply_user_class = "site-user";
					$reply_user_type = "";
					$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
					$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
					$sql .= " WHERE user_id=".$db->tosql($user_id, INTEGER); 
					$db->query($sql);
					while ($db->next_record()) {
						//$reply_user_type = $db->f("type_name"); // can show user type if necessary
						$reply_user_name = get_user_name($db->Record, "first");
					}
				} else {
					$reply_user_class = "site-guest";
					$reply_user_type = va_message("GUEST_MSG");
					$reply_user_name = $comment_user_name;
				}
				$is_valid = $rr->validate();
				if ($is_valid) {
					$db->HaltOnError = "no";
					$comment_saved = $rr->insert_record();
					if ($comment_saved) {
						// get last comment id
						$new_comment_id = $db->last_insert_id();
						product_review_notify(array("id" => $new_comment_id, "type" => "comment"));

						// comment was added clear validation passed variable
						if (isset($validation_passed[$comment_validation_id])) {
							unset($validation_passed[$comment_validation_id]);
						}
						set_session("session_validation_passed", $validation_passed);

						$more_comments = check_add_review(array("review_id" => $review_id, "type" => $reply_type_name)); // type - ptrw_comment or ptqn_reply

						$ajax_data["saved"] = 1;	
						$ajax_data["more_comments"] = $more_comments;	
						$ajax_data["comments"] = $comment_comments;	
						if (!$comments_approved) {
							$ajax_data["message"] = va_message("COMMENTS_SAVED_FOR_APPROVAL_MSG");	
							$ajax_data["show"] = 0;	
						} else {
							// set template
							$t->set_file($block_tag, $html_template);
							// parse reply template 
							$t->set_var("reply_comments", nl2br(htmlspecialchars($comment_comments)));
							$t->set_var("reply_user_class", $reply_user_class);
							$t->set_var("reply_user_name", htmlspecialchars($reply_user_name));
							$t->set_var("reply_user_type", $reply_user_type);
							$t->set_var("reply_date_added", va_date($datetime_show_format, $reply_date_added));
							if ($verified_buyer) {
								$t->sparse("reply_verified_buyer", false);
							}
							$t->parse("replies", false);
							$ajax_data["reply"] = $t->get_var("replies");	
							$ajax_data["show"] = 1;	
						}
					} else {
						$errors  = va_message("DATABASE_ERROR_MSG");
						$errors .= "<br>".$db->error;
					}

				} else {
					$errors = $rr->errors;
					$ajax_data["user_name_valid"] = $rr->get_property_value("user_name", IS_VALID);
					$ajax_data["user_email_valid"] = $rr->get_property_value("user_name", IS_VALID); 
					$ajax_data["comments_valid"] = $rr->get_property_value("comments", IS_VALID);
					$ajax_data["validation_number_valid"] = $rr->get_property_value("validation_number", IS_VALID);
				}
			}
			if ($errors) { 
				$ajax_data["errors"] = $errors;
			}
		} else if ($operation == "emotion") {	
			if ($user_id) {
				$emotion = get_param("emotion");
				$ajax_data["pb_id"] = $pb_id;
				$ajax_data["review_id"] = $review_id;
				// check if user already select some emotion
				$sql  = " SELECT emotion FROM ".$table_prefix."reviews_emotions "; 
				$sql .= " WHERE user_id=".$db->tosql($user_id, INTEGER); 
				$sql .= " AND review_id=".$db->tosql($review_id, INTEGER); 
				$review_emotion = get_db_value($sql);
				// delete old value
				$sql  = " DELETE FROM ".$table_prefix."reviews_emotions "; 
				$sql .= " WHERE user_id=".$db->tosql($user_id, INTEGER); 
				$sql .= " AND review_id=".$db->tosql($review_id, INTEGER); 
				$db->query($sql);
				// insert a new value if it's different from the old value				
				if ($emotion == $review_emotion) {
					$ajax_data["emotion"] = 0;
				} else {
					$ajax_data["emotion"] = $emotion;
					$sql  = " INSERT INTO ".$table_prefix."reviews_emotions (review_id, user_id, item_id, emotion, date_added) VALUES ("; 
					$sql .= $db->tosql($review_id, INTEGER).", "; 
					$sql .= $db->tosql($user_id, INTEGER).", "; 
					$sql .= $db->tosql($item_id, INTEGER).", "; 
					$sql .= $db->tosql($emotion, INTEGER).", "; 
					$sql .= $db->tosql(va_time(), DATETIME).") "; 
					$db->query($sql);
				}
				// calculate updated values for likes and dislikes
				$sql  = " SELECT COUNT(*) AS total FROM ".$table_prefix."reviews_emotions "; 
				$sql .= " WHERE review_id=".$db->tosql($review_id, INTEGER); 
				$sql .= " AND emotion=1 "; 
				$review_likes = get_db_value($sql);

				$sql  = " SELECT COUNT(*) AS total FROM ".$table_prefix."reviews_emotions "; 
				$sql .= " WHERE review_id=".$db->tosql($review_id, INTEGER); 
				$sql .= " AND emotion=-1 "; 
				$review_dislikes = get_db_value($sql);

				$ajax_data["likes"] = $review_likes;
				$ajax_data["dislikes"] = $review_dislikes;
			} else {
				$ajax_data["errors"] = va_message("SIGN_IN_FIRST_ERROR");
			}
		} else {
			$ajax_data["errors"] = "Unknown operation: ".$operation;
		}
		if(!$ajax_data["parse"]) {
			echo json_encode($ajax_data);	
			exit;
		}
	}

	if (!$ajax) {
		// set necessary scripts
		set_script_tag("js/shopping.js");
		set_script_tag("js/images.js");
		set_script_tag("js/ajax.js");
		set_script_tag("js/blocks.js");
	}

	$filters = array(
		"fr_emotion" => array(
			"selected" => array(),
			"values" => array("1" => va_message("POSITIVE_MSG"), "0" => va_message("NEUTRAL_MSG"), "-1" => va_message("CRITICAL_MSG")),
		),
		"fr_rating" => array(
			"selected" => array(),
			"values" => array(
				"1" => "1 - ".va_message("BAD_MSG"), "2" => "2 - ".va_message("POOR_MSG"), 
				"3" => "3 - ".va_message("AVERAGE_MSG"), "4" => "4 - ".va_message("GOOD_MSG"), "5" => "5 - ".va_message("EXCELLENT_MSG")
			),
		),
	);

	$pb_params = "";
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$reviews_url = new VA_URL("");
	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$reviews_url->page_name = $page_friendly_url.$friendly_extension;
		$pb_params = "item_id=".intval($item_id);
		$pagination_params = array(
			"tab" => $reviews_tab,
			"fr_emotion" => $fr_emotion,
			"fr_rating" => $fr_rating,
		);
	} else {
		if ($block_type == "sub_block") {
			$reviews_url->page_name = get_custom_friendly_url("product_details.php");
		} else {
			$reviews_url->page_name = get_custom_friendly_url("reviews.php");
		}
		$reviews_url->add_parameter("item_id", REQUEST, "item_id");
		$pagination_params = array(
			"item_id" => $item_id,
			"tab" => $reviews_tab,
			"fr_emotion" => $fr_emotion,
			"fr_rating" => $fr_rating,
		);
	}
	$reviews_page = $reviews_url->page_name;

	$reviews_url->add_parameter("tab", CONSTANT, $reviews_tab);
	$reviews_url->add_parameter("fr_emotion", REQUEST, "fr_emotion");
	$reviews_url->add_parameter("fr_rating", REQUEST, "fr_rating");

	$t->set_file($block_tag, $html_template);
	set_script_tag("js/reviews.js");

	$t->set_var("html_id", htmlspecialchars($html_id));
	$t->set_var("pb_params", htmlspecialchars($pb_params));
	$t->set_var("comment_form_class", "");
	$t->set_var("record", "");
	$t->set_var("reviews", "");
	$t->set_var("reviews_list", "");
	$t->set_var("not_rated", "");
	$t->set_var("rating_stats", "");
	$t->set_var("summary_statistic", "");
	$t->set_var("navigator_block", "");
	$t->set_var("positive_link", "");
	$t->set_var("neutral_link", "");
	$t->set_var("critical_link", "");
	$t->set_var("answered_link", "");
	$t->set_var("unanswered_link", "");
	$t->set_var("record_form", "");
	$t->set_var("record_fields", "");

	// set user sign data
	$sign_url = get_custom_friendly_url("user_login.php")."?return_page=".urlencode($reviews_url->get_url());
	$sign_user = ($user_id) ? 1 : 0;
	$t->set_var("sign_url", htmlspecialchars($sign_url));
	$t->set_var("sign_user", intval($sign_user));
	

	//$rr->operations[INSERT_ALLOWED] = (($allowed_post == 1) || ($allowed_post == 2 && get_session("session_user_id")));
	//$rr->success_messages[INSERT_SUCCESS] = COMMENTS_SAVED_FOR_APPROVAL_MSG;


	// parse selected filters
	$selected_filters = 0;
	foreach ($filters as $filter_key => $filter_data) {
		$param_value = trim(get_param($filter_key));
		$filter_values = $filter_data["values"];
		$filter_selected = array();
		if ($param_value) {
			$param_values = explode(",", $param_value);
			foreach ($param_values as $selected_value) {
				if (strlen($selected_value) && isset($filter_values[$selected_value])) {
					$filters[$filter_key]["selected"][$selected_value] = $selected_value;
				}
			}
		}
	}

	foreach ($filters as $filter_key => $filter_data) {
		$reviews_url->remove_parameter($filter_key);
		$filter_selected = $filter_data["selected"];
		$filter_values = $filter_data["values"];
		foreach ($filter_selected as $selected_value) {
			$filter_removed = $filter_selected;
			unset($filter_removed[$selected_value]);
			$reviews_url->add_parameter($filter_key, CONSTANT, implode(",", $filter_removed));
			$selected_filters++;
			$filter_desc = $filter_values[$selected_value];
			$t->set_var("filter_url", $reviews_url->get_url());
			$t->set_var("filter_desc", $filter_desc);
			$t->parse("selected_filters", true);
		}
		$reviews_url->add_parameter($filter_key, REQUEST, $filter_key);
	}
	if ($selected_filters) {
		$t->parse("filtered_by", false);
	}
	// end parse selected filters

	// check global product settings 
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$watermark_small_image = get_setting_value($settings, "watermark_small_image", 0);
	$watermark_big_image = get_setting_value($settings, "watermark_big_image", 0);
	$watermark_super_image = get_setting_value($settings, "watermark_super_image", 0);
	$open_large_image = get_setting_value($settings, "open_large_image", 0);

	// check if product exists 
	$sql  = " SELECT * FROM " . $table_prefix . "items ";
	$sql .= " WHERE is_showing=1 AND is_approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
	$db->query($sql);
	if($db->next_record())
	{
		$product_info = $db->Record;
		$review_item_name = get_translation($db->f("item_name"));
		$t->set_var("review_item_name", $review_item_name);
		$t->set_var("reviewed_item_name", $review_item_name);
		if ($script_run_mode != "include") {
			$auto_meta_title = REVIEWS_MSG.": ".$review_item_name;
		}

		// check super image
		$review_super_image = $db->f("super_image");
		if ($review_super_image) {
			// prepare JS for super image even if there is no super image
			if ($open_large_image) {
				$review_super_js = "onclick='popupImage(this); return false;'";
			} else {
				$review_super_js = "onclick='openImage(this); return false;'";
			}
			if (!preg_match("/^http(s)?:\/\//", $review_super_image) && ($watermark_super_image || $restrict_products_images)) {
				$review_super_image = "image_show.php?item_id=".$item_id."&type=super&vc=".md5($review_super_image);
			}
		} else {
			$review_super_image = "#";
			$review_super_js = "return false;";
		}
		$t->set_var("review_super_image", $review_super_image);
		$t->set_var("review_super_js", $review_super_js);

		// show large or small image if available
		$review_item_image = $db->f("big_image");
		if (!$review_item_image) { 
			$review_item_image = $db->f("small_image"); 
			$watermark = $watermark_small_image;
			$watermark_type = "small";
		} else {
			$watermark = $watermark_big_image;
			$watermark_type = "large";
		}
		if ($review_item_image) {
			if (!preg_match("/^http(s)?:\/\//", $review_item_image) && ($watermark || $restrict_products_images)) {
				$review_item_image = "image_show.php?item_id=".$item_id."&type=".$watermark_type."&vc=".md5($review_item_image); 
			}
			$t->set_var("alt", htmlspecialchars($review_item_name));
			$t->set_var("src", htmlspecialchars($review_item_image));
			
			$t->sparse("review_product_image", false);
		}	else {
			$t->set_var("review_product_image", "");
		}

		$is_item = true;
	}
	else
	{
		$review_item_name = ERRORS_MSG;
		$rr->errors = NO_PRODUCT_MSG;
		$t->set_var("reviewed_item_name", ERRORS_MSG);
		$is_item = false;
	}

	$t->set_var("rnd",           va_timestamp());
	$t->set_var("reviews_href",  $reviews_page);
	$t->set_var("reviews_url",   $reviews_page);
	$t->set_var("item_id",  htmlspecialchars($item_id));

	$rr = new VA_Record($table_prefix . "reviews", "");
	// global data
	$rr->operations[INSERT_ALLOWED] = (($allowed_post == 1) || ($allowed_post == 2 && get_session("session_user_id")));
	$rr->operations[UPDATE_ALLOWED] = false;
	$rr->operations[DELETE_ALLOWED] = false;
	$rr->operations[SELECT_ALLOWED] = false;
	$rr->redirect = false;
	$rr->success_messages[INSERT_SUCCESS] = COMMENTS_SAVED_FOR_APPROVAL_MSG;
	$rr->errors_class = "fd-error";

	// internal fields
	$rr->add_where("review_id", INTEGER);
	$rr->add_textbox("review_type", INTEGER);
	$rr->add_textbox("item_id", INTEGER);
	$rr->change_property("item_id", DEFAULT_VALUE, $item_id);
	$rr->add_textbox("user_id", INTEGER);
	$rr->change_property("user_id", USE_SQL_NULL, false);
	$rr->add_textbox("admin_id", INTEGER);
	$rr->change_property("admin_id", USE_SQL_NULL, false);
	$rr->add_textbox("date_added", DATETIME);
	$rr->add_textbox("remote_address", TEXT);
	$rr->add_textbox("approved", INTEGER);
	$rr->add_textbox("verified_buyer", INTEGER);

	// predefined fields
	$rr->add_radio("recommended", INTEGER, $recommended, IMPRESSION_MSG);
	$rr->change_property("recommended", USE_SQL_NULL, false);
	$rr->add_textbox("rating", INTEGER, RATING_MSG);
	$rr->add_textbox("user_name", TEXT, va_message("NAME_ALIAS_MSG"));
	$rr->change_property("user_name", REQUIRED, true);
	$rr->change_property("user_name", REGEXP_MASK, NAME_REGEXP);
	$rr->change_property("user_name", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$rr->set_control_event("user_name", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("user_email", TEXT, EMAIL_FIELD);
	$rr->change_property("user_email", REQUIRED, true);
	$rr->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
	$rr->set_control_event("user_email", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("summary", TEXT, va_message("ONE_LINE_SUMMARY_MSG"));
	$rr->set_control_event("summary", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("comments", TEXT, va_message("YOUR_REVIEW_MSG"));
	$rr->set_control_event("comments", AFTER_VALIDATE, "check_content");

	// validation number field
	$rr->add_textbox("validation_number", TEXT, VALIDATION_CODE_FIELD);
	$rr->change_property("validation_number", USE_IN_INSERT, false);
	$rr->change_property("validation_number", USE_IN_UPDATE, false);
	$rr->change_property("validation_number", USE_IN_SELECT, false);

	// check parameters properties
	$default_params = array(
		1 => "recommended", 2 => "rating", 
		3 => "user_name", 4 => "user_email", 
		5 => "summary", 6 => "comments");

	foreach ($default_params as $param_order => $param_name) {
		$param_order = get_setting_value($reviews_settings, $param_name . "_order", $param_order);
		$show_param = get_setting_value($reviews_settings, "show_".$param_name, $param_order);
		$param_required = get_setting_value($reviews_settings, $param_name . "_required", $param_order);
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
	// for question form always set to hide recommended and rating fields as those field blocks could be parsed from review template
	if ($review_type == 3) {
		$rr->change_property("recommended", SHOW, false);
		$rr->change_property("rating", SHOW, false);
	}

	// check if we need to use validation number field
	if (!$review_image_validation) {
		$rr->change_property("validation_number", SHOW, false);
	} else {
		$rr->change_property("validation_number", SHOW, true);
		if (isset($validation_passed[$review_validation_id])) {
			$rr->change_property("validation_number", BEFORE_SHOW, "rw_set_hide_block");
			$rr->change_property("validation_number", AFTER_SHOW, "rw_clear_hide_block");
		} else {
			$rr->change_property("validation_number", REQUIRED, true);
			$rr->change_property("validation_number", AFTER_VALIDATE, "check_validation_number", array("validation_id" => $review_validation_id));
		}
	}

	// set events
	$rr->set_event(ON_DOUBLE_SAVE, "review_double_save");
	$rr->set_event(BEFORE_INSERT, "before_insert_review");
	$rr->set_event(AFTER_INSERT, "after_insert_review", array("validation_id" => $review_validation_id));
	$rr->set_event(BEFORE_VALIDATE, "additional_review_checks");

	// check if customer allowed to submit review to show different messages and review form
	if (!$allowed_post) {
		$rr->record_show = false;	
		$t->set_var("write_review_error_desc", va_message("NOT_ALLOWED_ADD_REVIEW_MSG"));
		$t->sparse("write_review_error", false);
	} else if ($allowed_post == 2 && !get_session("session_user_id")) {
		$rr->record_show = false;	
		//$rr->success_message = va_message("REGISTERED_USERS_ADD_REVIEWS_MSG");
		$user_login_url = get_custom_friendly_url("user_login.php");
		$t->set_var("write_review_url", $user_login_url."?return_page=".urlencode($reviews_url->get_url()));
		$t->sparse("write_review_sign_in", false);
	} else if (blacklist_check("products_reviews") == "blocked") {
		$rr->record_show = false;	
		$t->set_var("write_review_error_desc", va_message("NOT_ALLOWED_ADD_REVIEW_MSG")." ".va_message("BLACK_IP_MSG"));
		$t->sparse("write_review_error", false);
	} else if (!check_add_review(array("item_id" => $item_id, "type" => $review_type_name))) {
		$rr->record_show = false;	
		$t->sparse("already_reviewed", false);
	} else {
		if ($operation == "save" && $pb_id == $param_pb_id) {
			$t->set_var("review_form_class", "expand-open"); // show form 
		}
		$t->sparse("write_review_form", false);
	}

	$rr->process();

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
	$sql .= " WHERE approved=1 AND rating <> 0 ";
	$sql .= " AND item_id=" . $db->tosql($item_id, INTEGER);
	$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
	$total_rating_votes = get_db_value($sql);

	$average_rating = 0;
	$total_rating_sum = 0;
	if($total_rating_votes) {
		$sql  = " SELECT SUM(rating) FROM " . $table_prefix . "reviews ";
		$sql .= " WHERE approved=1 AND rating <> 0 ";
		$sql .= " AND item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
		$total_rating_sum = get_db_value($sql);
		$average_rating = round($total_rating_sum / $total_rating_votes, 2);
	}

	$t->set_var("current_category_id", htmlspecialchars($category_id));
	$t->set_var("item_id", htmlspecialchars($item_id));

	if($is_item && ($allowed_view == 1 || ($allowed_view == 2 && strlen($user_id))))
	{
		$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $reviews_page);

		$n->set_data_js("pagination");
		if ($block_type == "sub_block") {
			//$n->set_data_type($pb_type);
			//$n->set_html_id($html_id);
		}

		// count all reviews
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
		$sql .= " WHERE approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
		$total_reviews = get_db_value($sql);

		// for main review type calculate positive critical and neutral review type
		$positive = 0; $critical = 0; $neutral = 0;
		if ($review_type == 1) {
			// count recommended reviews
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE recommended=1 AND approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
			$positive = get_db_value($sql);
			
			// count critical reviews
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE recommended=-1 AND approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
			$critical = get_db_value($sql);

			// count neutral reviews
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$sql .= " WHERE recommended=0 AND approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
			$neutral = get_db_value($sql);
		}

		$emotion = get_param("fr_emotion");
		$emotion_values = explode(",", $emotion);
		$emotion_data = array();
		foreach ($emotion_values as $emotion_value) {
			$emotion_data[$emotion_value] = $emotion_value;
		}
		if ($positive) {
			$filter_emotion = $emotion_data;
			$filter_emotion["1"] = "1";
			$reviews_url->add_parameter("fr_emotion", CONSTANT, implode(",", $filter_emotion));
			$t->set_var("positive_url", $reviews_url->get_url());
			$t->set_var("positive_percent", round($positive / $total_reviews * 100, 0)."%");
			$t->parse("positive_link", false);
		}
		if ($critical) {
			$filter_emotion = $emotion_data;
			$filter_emotion["-1"] = "-1";
			$reviews_url->add_parameter("fr_emotion", CONSTANT, implode(",", $filter_emotion));
			$t->set_var("critical_url", $reviews_url->get_url());
			$t->set_var("critical_percent", round($critical / $total_reviews * 100, 0)."%");
			$t->parse("critical_link", false);
		}

		if ($neutral) {
			$filter_emotion= $emotion_data;
			$filter_emotion["0"] = "0";
			$reviews_url->add_parameter("fr_emotion", CONSTANT, implode(",", $filter_emotion));
			$t->set_var("neutral_url", $reviews_url->get_url());
			$t->set_var("neutral_percent", round($neutral / $total_reviews * 100, 0)."%");
			$t->parse("neutral_link", false);
		}
		$reviews_url->add_parameter("fr_emotion", REQUEST, "fr_emotion");
	
		if ($total_reviews) {
			$based_on_message = str_replace("{total_votes}", $total_reviews, va_message("BASED_ON_REVIEWS_MSG"));
			$t->set_var("based_on_message", $based_on_message);
			$t->set_var("BASED_ON_REVIEWS_MSG", $based_on_message);

			// calculate and show rating stats for review type
			if ($review_type == 1) {
				$rating_stats = array();
				$sql  = " SELECT rating, COUNT(*) AS rating_total ";	
				$sql .= " FROM " . $table_prefix . "reviews ";
				$sql .= " WHERE approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND review_type=" . $db->tosql($review_type, INTEGER);
				$sql .= " GROUP BY rating ";
				$db->query($sql);
				while ($db->next_record()) {
					$rating_value = $db->f("rating");
					$rating_total = $db->f("rating_total");
					$rating_stats[$rating_value] = $rating_total;
				}
	    
				// prepare rating parameter for filters
				$fr_rating = get_param("fr_rating");
				$rating_values = explode(",", $fr_rating);
				$rating_data = array();
				foreach ($rating_values as $rating_value) {
					$rating_data[$rating_value] = $rating_value;
				}
	    
				$rating_values = $filters["fr_rating"]["values"];
				foreach ($rating_values as $rating_value => $rating_desc) {
          $rating_total = isset($rating_stats[$rating_value]) ? $rating_stats[$rating_value] : 0;
					$t->set_var("rating_desc", htmlspecialchars($rating_desc));
					$t->set_var("rating_percent", round($rating_total / $total_reviews * 100, 0)."%");
					$t->set_var("rating_total", intval($rating_total));
					// set filter url
					$rating_filter = $rating_data;
					$rating_filter[$rating_value] = $rating_value;
					$reviews_url->add_parameter("fr_rating", CONSTANT, implode(",", $rating_filter));
					$t->set_var("rating_url", $reviews_url->get_url());
	    
					$t->parse("rating_stats", true);
				}
			}

			// parse summary statistic
			$t->set_var("positive_percent", round($positive / $total_reviews * 100, 0)."%");
			$t->set_var("critical_percent", round($critical / $total_reviews * 100, 0)."%");
			$t->set_var("neutral_percent", round($neutral / $total_reviews * 100, 0)."%");
			$t->set_var("total_votes", $total_reviews);

			$average_rating_round = round($average_rating, 0);
			$average_int = intval($average_rating);
			$average_dec = round($average_rating, 2) - $average_int;
			if ($average_dec >= 0.75) {
				$average_rating_class = "ico-".($average_int+1)."-0-stars";
			} else if ($average_dec < 0.25) {
				$average_rating_class = "ico-".$average_int."-0-stars";
			} else {
				$average_rating_class = "ico-".$average_int."-5-stars";
			}

			$average_rating_image = $average_rating ? "rating-" . $average_rating_round: "not-rated";
			$rating_avg = round($db->f("rating"));

			$t->set_var("average_rating_image", $average_rating_image);
			$t->set_var("average_rating", $average_rating);
			$t->set_var("average_rating_alt", $average_rating);
			$t->set_var("average_rating_title", $average_rating);
			$t->set_var("average_rating_class", $average_rating_class);
			$t->set_var("total_questions", $total_reviews);

			$t->parse("summary_statistic", false);

			// build where for selected reviews
			$where  = " WHERE (summary IS NOT NULL OR comments IS NOT NULL) ";
			$where .= " AND approved=1 AND item_id=" . $db->tosql($item_id, INTEGER);
			$where .= " AND review_type=" . $db->tosql($review_type, INTEGER);
			$fr_rating = get_param("fr_rating");
			if (strlen($fr_rating)) {
				$where .= " AND rating IN (".$db->tosql($fr_rating, INTEGERS_LIST).")";
			}
			$fr_emotion = get_param("fr_emotion");
			if (strlen($fr_emotion)) {
				$where .= " AND recommended IN (".$db->tosql($fr_emotion, INTEGERS_LIST).")";
			}
		
			$sql    = " SELECT COUNT(*) FROM " . $table_prefix . "reviews ";
			$total_records = get_db_value($sql . $where);
			$t->set_var("total_records", $total_records);

			$record_number = 0;
			$records_per_page = $reviews_per_page ? $reviews_per_page : 10;
			$pages_number = 10;
			$page_number = $n->set_navigator("navigator", $pagination_param, MOVING, $pages_number, $records_per_page, $total_records, false, $pagination_params);
		 
			$reviews = array(); $users = array(); $admins = array(); $review_ids = array(); $user_ids = array(); $admin_ids = array();
			$sql = " SELECT * FROM " . $table_prefix . "reviews ";
			$order_by = " ORDER BY date_added DESC";  
			$db->RecordsPerPage = $records_per_page;
			$db->PageNumber = $page_number;
			$db->query($sql . $where . $order_by);
			while ($db->next_record()) {
				$review_id = $db->f("review_id");
				$date_added = $db->f("date_added", DATETIME);
				$user_email = $db->f("user_email");
				$review_user_id = $db->f("user_id");
				$review_admin_id = $db->f("admin_id");
    
				$reviews[$review_id] = $db->Record;
				$reviews[$review_id]["sub_comments"] = array();
				$reviews[$review_id]["user_emotion"] = 0; // if user like or dislike review
				$reviews[$review_id]["likes"] = 0;
				$reviews[$review_id]["dislikes"] = 0;
				$reviews[$review_id]["date_added"] = $date_added;
				// save reviews and users ids
				$review_ids[$review_id] = $review_id;
				if ($review_user_id) { $user_ids[$review_user_id] = $review_user_id; }
				if ($review_admin_id) { $admin_ids[$review_admin_id] = $review_admin_id; }
			}

			// check comments for reviews
			if (count($review_ids)) {
				$sql  = " SELECT * FROM ".$table_prefix."reviews r WHERE parent_review_id IN (".$db->tosql($review_ids, INTEGERS_LIST).")"; 
				$sql .= " AND review_type=" . $db->tosql($reply_type, INTEGER); 
				$sql .= " AND approved=1 ";
				$sql .= " ORDER BY date_added ASC ";
				$db->query($sql);
				while ($db->next_record()) {
					$parent_id = $db->f("parent_review_id");
					$comment_id = $db->f("review_id");
					$date_added = $db->f("date_added", DATETIME);

					$comment_data = $db->Record;
					$comment_data["date_added"] = $date_added;
					$reviews[$parent_id]["sub_comments"][$comment_id] = $comment_data;

					$review_user_id = $db->f("user_id");
					if ($review_user_id) { $user_ids[$review_user_id] = $review_user_id; }
					$review_admin_id = $db->f("admin_id");
					if ($review_admin_id) { $admin_ids[$review_admin_id] = $review_admin_id; }
				}
			}

			// calculate likes and dislikes for reviews
			if (count($review_ids)) {
				$sql  = " SELECT re.review_id, re.emotion, COUNT(*) AS emotion_total ";
				$sql .= " FROM ".$table_prefix."reviews_emotions re WHERE review_id IN (".$db->tosql($review_ids, INTEGERS_LIST).")"; 
				$sql .= " GROUP BY re.review_id, re.emotion ";
				$db->query($sql);
				while ($db->next_record()) {
					$review_id = $db->f("review_id");
					$emotion = $db->f("emotion");
					$emotion_total = $db->f("emotion_total");
					if ($emotion == 1) {
						$reviews[$review_id]["likes"] = $emotion_total;
					} else if ($emotion == -1) {
						$reviews[$review_id]["dislikes"] = $emotion_total;
					}
				}
			}

			// check user emotion for each review
			if ($user_id && count($review_ids)) {
				$sql  = " SELECT re.review_id, re.emotion ";
				$sql .= " FROM ".$table_prefix."reviews_emotions re ";	
				$sql .= " WHERE review_id IN (".$db->tosql($review_ids, INTEGERS_LIST).")"; 
				$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$review_id = $db->f("review_id");
					$user_emotion = $db->f("emotion");
					$reviews[$review_id]["user_emotion"] = $user_emotion;
				}
			}

			if (count($user_ids)) {
				$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
				$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
				$sql .= " WHERE user_id IN (".$db->tosql($user_ids, INTEGERS_LIST).")"; 
				$db->query($sql);
				while ($db->next_record()) {
					$review_user_id = $db->f("user_id");
					$review_user_email = $db->f("email");
					$review_user_type = $db->f("type_name");
					$review_user_name = get_user_name($db->Record, "first");
					$users[$review_user_id] = array("email" => $review_user_email, "name" => $review_user_name, "type" => $review_user_type);
				}
			}

			if (count($admin_ids)) {
				$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
				$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
				$sql .= " WHERE admin_id IN (".$db->tosql($admin_ids, INTEGERS_LIST).")"; 
				$db->query($sql);
				while ($db->next_record()) {
					$row_admin_id = $db->f("admin_id");
					$row_admin_email = $db->f("email");
					$privilege_name = $db->f("privilege_name");
					$row_admin_name = $db->f("nickname"); // show admin nickname for reviews if available
					if (!$row_admin_name) { $row_admin_name = $db->f("admin_name"); }
    
					$admins[$row_admin_id] = array("email" => $row_admin_email, "name" => $row_admin_name, "type" => $privilege_name);
				}
			}

			foreach($reviews as $review_id => $review_data) {
				$record_number++;
				$review_id = $review_data["review_id"];
				$review_user_id = $review_data["user_id"];
				$review_admin_id = $review_data["admin_id"];
				$review_user_name = $review_data["user_name"];
				$user_emotion = $review_data["user_emotion"];
				if ($review_admin_id && isset($admins[$review_admin_id])) {
					$review_user_class = "site-admin";
					$review_user_type = $admins[$review_admin_id]["type"];
					$review_user_name = $admins[$review_admin_id]["name"];
				} else if ($review_user_id && isset($users[$review_user_id])) {
					$review_user_class = "site-user";
					$review_user_type = "";
					$review_user_name = $users[$review_user_id]["name"];
				} else {
					$review_user_class = "site-guest";
					$review_user_type = va_message("GUEST_MSG");
					$review_user_name = $review_data["user_name"];
				}
				$verified_buyer = $review_data["verified_buyer"];
				if ($review_data["recommended"] == 1) {
					$recommended_image = "positive";
				} else if ($review_data["recommended"] == -1) {
					$recommended_image = "critical";
				} else {
					$recommended_image = "neutral";
				}
				$t->set_var("recommended_image", $recommended_image);
				$rating_avg = round($review_data["rating"]);
				$rating_int = intval($review_data["rating"]);
				$rating_dec = round($review_data["rating"], 2) - $rating_int;
				if ($rating_int == 0) {
					$rating_class = "ico-not-rated";
				} else if ($rating_dec >= 0.75) {
					$rating_class = "ico-".($rating_int+1)."-0-stars";
				} else if ($rating_dec < 0.25) {
					$rating_class = "ico-".$rating_int."-0-stars";
				} else {
					$rating_class = "ico-".$rating_int."-5-stars";
				}

				$rating_image = $rating ? "rating-" . $rating_avg: "not-rated";

				$t->set_var("review_id", $review_id);
				$t->set_var("rating_image", $rating_image);
				$t->set_var("rating_class", $rating_class);
				$t->set_var("review_user_class", $review_user_class);
				$t->set_var("review_user_type", $review_user_type);
				$t->set_var("review_user_name", htmlspecialchars($review_user_name));

				$date_added = $review_data["date_added"];
				$date_added_string = va_date($datetime_show_format, $date_added);
				$t->set_var("review_date_added", $date_added_string);
				if ($verified_buyer) {
					$t->parse("review_verified_buyer", false);
				} else {
					$t->set_var("review_verified_buyer", "");
				}

				$t->set_var("review_summary", htmlspecialchars($review_data["summary"]));
				$t->set_var("review_comments", nl2br(htmlspecialchars($review_data["comments"])));

				// show like and dislike buttons if they allowed and show appropriate statistics
				$like_class = ""; $dislike_class = "";
				if ($user_emotion == 1) {
					$like_class = "emotion-selected";
				} else if ($user_emotion == -1) {
					$dislike_class = "emotion-selected";
				}
				$review_likes = $review_data["likes"];
				$review_dislikes = $review_data["dislikes"];
				if ($allowed_like) {
					$t->set_var("like_class", $like_class);
					$t->set_var("review_likes", $review_likes);
					$t->sparse("like_button", false);
					$t->sparse("review_likes_block", false);
				} else {
					$t->set_var("like_button", "");
					$t->set_var("review_likes_block", "");
				}
				if ($allowed_dislike) {
					$t->set_var("dislike_class", $dislike_class);
					$t->set_var("review_dislikes", $review_dislikes);
					$t->sparse("dislike_button", false);
					$t->sparse("review_dislikes_block", false);
				} else {
					$t->set_var("dislike_button", "");
					$t->set_var("review_dislikes_block", "");
				}
				if ($allowed_like || $allowed_dislike) {
					$t->sparse("review_emotions", false);
				} else {
					$t->set_var("review_emotions", "");
				}
				// end like and dislike buttons

				// show replies if available
				$t->set_var("replies", "");
				$sub_comments = $review_data["sub_comments"];
				if (is_array($sub_comments)) {
					foreach ($sub_comments as $comment_id => $reply_data) {

						$reply_user_id = $reply_data["user_id"];
						$reply_admin_id = $reply_data["admin_id"];
						$reply_verified_buyer = $reply_data["verified_buyer"];

						if ($reply_admin_id && isset($admins[$reply_admin_id])) {
							$reply_user_class = "site-admin";
							$reply_user_type = $admins[$reply_admin_id]["type"];
							$reply_user_name = $admins[$reply_admin_id]["name"];
						} else if ($reply_user_id && isset($users[$reply_user_id])) {
							$reply_user_class = "site-user";
							$reply_user_type = "";
							$reply_user_name = $users[$reply_user_id]["name"];
					} else {
							$reply_user_class = "site-guest";
							$reply_user_type = va_message("GUEST_MSG");
							$reply_user_name = $reply_data["user_name"];
						}

						$reply_comments = $reply_data["comments"];
						$t->set_var("reply_user_class", $reply_user_class);
						$t->set_var("reply_user_type", $reply_user_type);
						$t->set_var("reply_user_name", htmlspecialchars($reply_user_name));
						$t->set_var("reply_comments", nl2br(htmlspecialchars($reply_comments)));

						$reply_added = $reply_data["date_added"];
						$reply_added_string = va_date($datetime_show_format, $reply_added);
						$t->set_var("reply_date_added", $reply_added_string);
						if ($reply_verified_buyer) {
							$t->sparse("reply_verified_buyer", false);
						} else {
							$t->set_var("reply_verified_buyer", "");
						}

						$t->parse("replies", true);
					}
				}
				if ($allowed_comment) {
					$t->sparse("comment_button", false);
					if ($user_id || $admin_id) {
						$t->set_var("comment_user_name_block", "");
						$t->set_var("comment_user_email_block", "");
					} else {
						$t->sparse("comment_user_name_block", false);
						$t->sparse("comment_user_email_block", false);
					} 

					if ($comment_validation) {
						if (isset($validation_passed["rw-".$review_id])) {
							$t->set_var("comment_validation_number_class", "hidden-block");
						} else {
							$t->set_var("comment_validation_number_class", "");
						}
						$t->sparse("comment_validation_number_block", false);
					} else {
						$t->set_var("comment_validation_number_block", "");
					}

					$t->sparse("comment_form", false);
				} else {
					$t->set_var("comment_form", "");
				}

				$t->sparse("replies_block", false);
				$t->parse("reviews_list", true);
				$t->parse("reviews", false);
			} 

		} else {
			$t->set_var("total_records", 0);
			$t->parse("no_reviews", false);
		}
	
		$t->parse("reviews_block", false);
	}
	$t->parse("reviews_data", false);

	$block_parsed = true;
	$script_run_mode = "";
