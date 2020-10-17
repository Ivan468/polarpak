<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_review.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
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

	// get product review settings to send notifications
	$review_settings = get_settings("products_reviews");
	$admin_reply_notification = get_setting_value($review_settings, "admin_reply_notification", 0);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_review.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_review_href", "admin_review.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_user_href", "admin_user.php");
	$t->set_var("admin_users_select_href", "admin_users_select.php");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_constant("REVIEW_MSG"), va_constant("CONFIRM_DELETE_MSG")));

	$reviews_href  = "admin_reviews.php";

	$t->set_var("admin_reviews_href", $reviews_href);

	$r = new VA_Record($table_prefix."reviews");
	$r->return_page = $reviews_href;

	$no_yes = 
		array( 
			array(0, va_constant("NO_MSG")), array(1, va_constant("YES_MSG"))
		);

	$approved_values = 
		array( 
			array(0, va_constant("NEW_MSG")), array(1, va_constant("APPROVED_MSG")), array(-1, va_constant("STATUS_DECLINED_MSG"))
		);

	$impression_values = 
		array( 
			array(-1, va_constant("NEGATIVE_MSG")), array(0, va_constant("NEUTRAL_MSG")), array(1, va_constant("POSITIVE_MSG"))
		);


	$rating_options = 
		array( 
			array("", ""), array(1, BAD_MSG), array(2, POOR_MSG), 
			array(3, AVERAGE_MSG), array(4, GOOD_MSG), array(5, EXCELLENT_MSG),
			);
	$recommended_options = 
		array( 
			array("", ""), array(1, YES_MSG), array(-1, NO_MSG), 
			);


	$r->add_where("review_id", INTEGER);
	$r->add_textbox("parent_review_id", INTEGER);
	$r->add_textbox("review_type", INTEGER);
	$r->add_textbox("item_id", INTEGER);
	$r->change_property("item_id", USE_IN_UPDATE, false);
	$r->add_textbox("user_id", INTEGER);
	$r->change_property("user_id", USE_IN_UPDATE, false);
	$r->change_property("user_id", USE_SQL_NULL, false);
	$r->add_textbox("admin_id", INTEGER);
	$r->change_property("admin_id", USE_IN_UPDATE, false);
	$r->add_textbox("notice_sent", INTEGER);
	$r->change_property("notice_sent", USE_IN_UPDATE, false);
	$r->change_property("notice_sent", DEFAULT_VALUE, 0);
	$r->add_radio("approved", INTEGER, $approved_values);
	$r->add_radio("verified_buyer", INTEGER, $no_yes, va_constant("VERIFIED_BUYER_MSG"));
	$r->add_textbox("date_added", DATETIME, va_constant("REVIEW_DATE_MSG"));
	$r->change_property("date_added", VALUE_MASK, $datetime_show_format);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("summary", TEXT);
	$r->add_textbox("comments", TEXT, va_constant("COMMENT_MSG"));
	$r->add_textbox("user_name", TEXT, va_constant("USER_NAME_MSG"));
	$r->change_property("user_name", PARSE_NAME, "guest_name");
	$r->add_textbox("user_email", TEXT, va_constant("EMAIL_MSG"));
	$r->change_property("user_email", PARSE_NAME, "guest_email");
	$r->add_textbox("remote_address", TEXT);
	$r->change_property("remote_address", USE_IN_UPDATE, false);
	$r->add_radio("recommended", INTEGER, $impression_values, va_constant("IMPRESSION_MSG"));
	$r->change_property("recommended", USE_SQL_NULL, false);
	$r->add_radio("rating", INTEGER, $rating_options);

	$r->get_form_values();

	$review_id = get_param("review_id");
	$parent_review_id = get_param("parent_review_id");
	if (!strlen($review_id)) {
		$r->change_property("date_added", SHOW, false);
		$r->change_property("remote_address", SHOW, false);
		$r->change_property("approved", REQUIRED, true);
		$r->change_property("verified_buyer", REQUIRED, true);
		$r->change_property("comments", REQUIRED, true);
	}

	if(!strlen($review_id) && !strlen($parent_review_id))	
	{
		header("Location: " . $r->return_page);
		exit;
	}

	$operation = get_param("operation");
	if(strlen($operation))
	{
		if($operation == "cancel")
		{
			header("Location: " . $r->return_page);
			exit;
		}
		else if($operation == "delete" && $review_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "reviews WHERE review_id=" . $db->tosql($review_id, INTEGER));		

			update_product_rating($r->get_value("item_id"));

			header("Location: " . $r->return_page);
			exit;
		}

		$is_valid = $r->validate();

		if($is_valid)
		{
			$notice_sent = $r->get_value("notice_sent");
			$approved = $r->get_value("approved");
			if (strlen($r->get_value("review_id"))) {
				$r->update_record();
			} else {
				// save reply to review or question 
				$r->set_value("date_added", va_time());
				$r->set_value("remote_address", get_ip());
				$r->set_value("notice_sent", 0);
				$r->insert_record();
				$review_id = $db->last_insert_id();
				$r->set_value("review_id", $review_id);
			}

			// check if need to send reply
			if ($parent_review_id && $approved == 1 && !$notice_sent) {
				product_review_notify(array("id" => $review_id, "type" => "notice"));
				$r->set_value("notice_sent", 1);
			}

			update_product_rating($r->get_value("item_id"));

			header("Location: " . $r->return_page);
			exit;
		}
	} else if($review_id) {
		$r->get_db_values();
	} else if (strlen($parent_review_id)) {
		// prepare form for admin reply
		$sql  = " SELECT * FROM ".$table_prefix."reviews ";
		$sql .= " WHERE review_id=" . $db->tosql($parent_review_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$item_id = $db->f("item_id");
			$parent_review_type = $db->f("review_type");
		}
		if ($parent_review_type == 3) {
			$review_type = 4; // answer to question
		} else {
			$review_type = 2; // reply to review 
		}

		$r->set_value("item_id", $item_id);
		$r->set_value("parent_review_id", $parent_review_id);
		$r->set_value("review_type", $review_type);
		$r->set_value("admin_id", get_session("session_admin_id"));
		$r->set_value("notice_sent", 0);
		$r->set_value("approved", 1);
		$r->set_value("verified_buyer", 0);
	}

	$parent_review_id = $r->get_value("parent_review_id");
	if ($parent_review_id) {
		$sql  = " SELECT * FROM ".$table_prefix."reviews ";
		$sql .= " WHERE review_id=".$db->tosql($parent_review_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$parent_type = $db->f("review_type");
			if ($parent_type == 2) {
				$parent_type_desc = va_constant("COMMENT_MSG");
			} else if ($parent_type == 3) {
				$parent_type_desc = va_constant("QUESTION_MSG");
			} else if ($parent_type == 4) {
				$parent_type_desc = va_constant("ANSWER_MSG");
			} else {
				$parent_type_desc = va_constant("REVIEW_MSG");
			}
			$parent_comments = $db->f("comments");
			$t->set_var("parent_type", nl2br(htmlspecialchars($parent_type_desc)));
			$t->set_var("parent_comments", nl2br(htmlspecialchars($parent_comments)));

			// check for parent review customer
			$parent_admin_id = $db->f("admin_id");
			$parent_user_id = $db->f("user_id");
			if ($parent_admin_id) {
				$parent_user_class = "site-admin";
				$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
				$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
				$sql .= " WHERE admin_id IN (".$db->tosql($parent_admin_id, INTEGER).")"; 
				$db->query($sql);
				if ($db->next_record()) {
					$parent_user_email = $db->f("email");
					$parent_user_type = $db->f("privilege_name");
					$parent_user_name = $db->f("nickname"); // show admin nickname for reviews if available
					if (!$parent_user_name) { $parent_user_name = $db->f("admin_name"); }
				}
			} else if ($parent_user_id) {
				$parent_user_class = "site-user";
				$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
				$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
				$sql .= " WHERE user_id=" . $db->tosql($parent_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$parent_user_data = $db->Record;
					$parent_user_name = get_user_name($parent_user_data, "full");
					$parent_user_email = $db->f("email");
					$parent_user_type = $db->f("type_name");
				}
			} else {
				$parent_user_class = "site-guest";
				$parent_user_type = va_constant("GUEST_MSG");
				$parent_user_name = $db->f("user_name"); 
				$parent_user_email = $db->f("user_email");  
				if (!strlen($parent_user_name)) { 
					$parent_user_name = va_constant("NOT_AVAILABLE_MSG");
				}
			}

			$t->set_var("parent_user_class", htmlspecialchars($parent_user_class));
			$t->set_var("parent_user_type", htmlspecialchars($parent_user_type));
			$t->set_var("parent_user_name", htmlspecialchars($parent_user_name));
			if ($parent_user_email) {
				$t->set_var("parent_user_email", htmlspecialchars($parent_user_email));
				$t->sparse("parent_user_email_block", false);

				if (!strlen($review_id)) {
					//$t->sparse("parent_notification", false);
				}
			}
		}
	} else {
		$r->change_property("parent_review_id", SHOW, false);
	}

	// parse user data
	$user_id = $r->get_value("user_id");
	if ($user_id) {
		$r->change_property("user_name", SHOW, false);
		$r->change_property("user_email", SHOW, false);
		$sql  = " SELECT u.*, ut.type_name FROM (".$table_prefix."users u ";
		$sql .= " INNER JOIN ".$table_prefix."user_types ut ON u.user_type_id=ut.type_id) ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$user_data = $db->Record;
			$user_name = get_user_name($db->Record, "full");
			$user_email = $db->f("email");
			$user_type = $db->f("type_name");
		}

		$t->set_var("user_id", $user_id);
		$t->set_var("user_name", $user_name);
		$t->set_var("user_email", $user_email);
		$t->set_var("user_type", $user_type);
		$t->parse_to("user_template", "selected_user", false);
	} else {
		$r->change_property("user_id", SHOW, false);
	}
	// parse user template
	$t->set_var("user_id", "[user_id]");
	$t->set_var("user_name", "[user_name]");
	$t->set_var("user_email", "[user_email]");
	$t->parse("user_template", false);

	// parse admin data
	$admin_id = $r->get_value("admin_id");
	if ($admin_id) {
		$r->change_property("user_name", SHOW, false);
		$r->change_property("user_email", SHOW, false);
		$sql  = " SELECT a.*, ap.privilege_name FROM (".$table_prefix."admins a ";
		$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
		$sql .= " WHERE admin_id IN (".$db->tosql($admin_id, INTEGER).")"; 
		$db->query($sql);
		if ($db->next_record()) {
			$admin_id = $db->f("admin_id");
			$admin_email = $db->f("email");
			$privilege_name = $db->f("privilege_name");
			$admin_name = $db->f("nickname"); // show admin nickname for reviews if available
			if (!$admin_name) { $admin_name = $db->f("admin_name"); }
		}

		$t->set_var("admin_id", $admin_id);
		$t->set_var("admin_name", $admin_name);
		$t->set_var("admin_type", $privilege_name);
		$t->set_var("admin_email", $admin_email);
		$t->parse_to("user_template", "selected_user", false);
	} else {
		$r->change_property("admin_id", SHOW, false);
	}


	$review_type = $r->get_value("review_type");
	if ($review_type == 2) {
		$review_type_desc = va_constant("COMMENT_MSG");
	} else if ($review_type == 3) {
		$review_type_desc = va_constant("QUESTION_MSG");
	} else if ($review_type == 4) {
		$review_type_desc = va_constant("ANSWER_MSG");
	} else {
		$review_type_desc = va_constant("REVIEW_MSG");
	}
	$t->set_var("review_type_desc", $review_type_desc);
	// for comments and answers hide rating and recommended field
	if ($review_type == 2 || $review_type == 4) {
		$r->change_property("rating", SHOW, false);
		$r->change_property("recommended", SHOW, false);
		$r->change_property("summary", SHOW, false);
		if (!strlen($review_id)) {
			$t->parse("reply_button", false);	
		}
	}
	if (strlen($review_id)) {
		$t->parse("update_button", false);	
		$t->parse("delete_button", false);	
	}
	$r->set_parameters();

	$item_id = $r->get_value("item_id");
	$sql = "SELECT * FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($item_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$item_name = $db->f("item_name");
		$friendly_url = $db->f("friendly_url");
		if ($friendly_urls && strlen($friendly_url)) {
			$site_product_url = $site_url.$friendly_url.$friendly_extension."?tab=reviews";
		} else {
			$site_product_url = $site_url."product_details.php?item_id=".urlencode($item_id)."&tab=reviews";
		}
		$t->set_var("item_name", htmlspecialchars($item_name));	
		$t->set_var("site_product_url", htmlspecialchars($site_product_url));	
	}


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("date_added_format", join("", $datetime_edit_format));
	$t->pparse("main");


