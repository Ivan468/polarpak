<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  settings_product_reviews.php                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


// change
// reviews_availability -> reviews_allowed
// reviews_availability -> allowed_view, allowed_post
// auto_approve -> auto_approve
// reviews_per_page -> reviews_per_page
// reviews_per_page -> reviews_per_page
// review_random_image -> review_random_image
// review_per_user
// reviews_interval 
// reviews_period 

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	check_admin_security("products_reviews_settings");

	$setting_type = "products_reviews";

	$va_trail = array(
		"admin_global_settings.php" => va_constant("SETTINGS_MSG"),
		"admin_products_settings.php" => va_constant("PRODUCTS_MSG"),
		"settings_product_reviews.php" => va_constant("REVIEWS_SETTINGS_MSG"),
	);

	$yes_no =
		array(
			array(1, va_constant("YES_MSG")), array(0, va_constant("NO_MSG") . " (". va_constant("APPROVE_BEFORE_PUBLISHING_ON_SITE_MSG") .")")
			);

	$comments_approve_values =
		array(
			array(1, va_constant("YES_MSG")), array(0, va_constant("NO_MSG") . " (".va_constant("ADMIN_APPROVE_COMMENTS_MSG").")")
			);

	$allowed_options = 
		array( 
			array(0, va_constant("NOBODY_MSG")), array(1, va_constant("FOR_ALL_USERS_MSG")), array(2, va_constant("REGISTERED_CUSTOMERS_MSG")), 
		);

	$like_options = 
		array( 
			array(0, va_constant("NOBODY_MSG")), array(2, va_constant("REGISTERED_CUSTOMERS_MSG")), 
		);

	$validation_types = 
		array( 
			array(2, va_constant("FOR_ALL_USERS_MSG")), array(1, va_constant("UNREGISTERED_USER_ONLY_MSG")), array(0, va_constant("NOT_USED_MSG"))
		);

	$message_types =
		array(
			array(1, va_constant("HTML_MSG")), array(0, va_constant("PLAIN_TEXT_MSG"))
		);

	$time_periods =
		array(
			array("", ""), array(1, va_constant("DAY_MSG")), array(2, va_constant("WEEK_MSG")), array(3, va_constant("MONTH_MSG")), array(4, va_constant("YEAR_MSG"))
		);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "settings_product_reviews.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("settings_product_reviews_href", "settings_product_reviews.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");
	$t->set_var("admin_email_tags_help_href", "admin_email_tags_help.php");
	$t->set_var("ON_PROD_DETAILS_MSG", ucwords(va_constant("ON_PROD_DETAILS_MSG")));

	$t->set_var("date_edit_format", join("", $date_edit_format));
	
	$html_editor = get_setting_value($settings, "html_editor_email", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);
	$editors_list = 'am,um,am_comment,um_comment';
	add_html_editors($editors_list, $html_editor);
	
	$r = new VA_Record($table_prefix . "global_settings");
	// global reviews settings
	$r->add_radio("allowed_view", INTEGER, $allowed_options);
	$r->add_radio("allowed_post", INTEGER, $allowed_options);
	$r->add_radio("auto_approve", INTEGER, $yes_no);
	$r->add_radio("allowed_like", INTEGER, $like_options);
	$r->add_radio("allowed_dislike", INTEGER, $like_options);
	$r->add_textbox("reviews_per_page", INTEGER, va_constant("REVIEWS_PER_PAGE_MSG"));
	$r->add_textbox("reviews_per_product", INTEGER, va_constant("REVIEWS_PER_PAGE_MSG"));
	$r->add_radio("review_random_image", TEXT, $validation_types);		

	// reviews restrictions
	$r->add_textbox("reviews_per_user", INTEGER);		
	$r->add_textbox("reviews_interval", INTEGER);		
	$r->add_select("reviews_period", INTEGER, $time_periods);		

	// review comments settings
	$r->add_radio("allowed_comment", INTEGER, $allowed_options);
	$r->add_radio("comments_approve", INTEGER, $comments_approve_values);
	$r->add_textbox("comments_per_review", INTEGER);
	$r->add_textbox("comments_interval", INTEGER);		
	$r->add_select("comments_period", INTEGER, $time_periods);		
	$r->add_radio("comment_random_image", TEXT, $validation_types);		


	// predefined fields
	$r->add_checkbox("show_recommended", INTEGER);
	$r->add_checkbox("recommended_required", INTEGER);
	$r->add_textbox("recommended_order", INTEGER, va_constant("RECOMMEND_PRODUCT_MSG"));
	$r->add_checkbox("show_rating", INTEGER);
	$r->add_checkbox("rating_required", INTEGER);
	$r->add_textbox("rating_order", INTEGER, va_constant("RATE_IT_MSG"));
	$r->add_checkbox("show_user_name", INTEGER);
	$r->add_checkbox("user_name_required", INTEGER);
	$r->add_textbox("user_name_order", INTEGER, va_constant("NAME_ALIAS_MSG"));
	$r->add_checkbox("show_user_email", INTEGER);
	$r->add_checkbox("user_email_required", INTEGER);
	$r->add_textbox("user_email_order", INTEGER, va_constant("EMAIL_MSG"));
	$r->add_checkbox("show_summary", INTEGER);
	$r->add_checkbox("summary_required", INTEGER);
	$r->add_textbox("summary_order", INTEGER, va_constant("ONE_LINE_SUMMARY_MSG"));
	$r->add_checkbox("show_comments", INTEGER);
	$r->add_checkbox("comments_required", INTEGER);
	$r->add_textbox("comments_order", INTEGER, va_constant("YOUR_REVIEW_MSG"));


	// notification fields
	$r->add_checkbox("admin_notification", INTEGER);
	$r->add_textbox("admin_email", TEXT);
	$r->add_textbox("admin_mail_from", TEXT);
	$r->add_textbox("admin_mail_cc", TEXT);
	$r->add_textbox("admin_mail_bcc", TEXT);
	$r->add_textbox("admin_mail_reply_to", TEXT);
	$r->add_textbox("admin_mail_return_path", TEXT);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_message_type", TEXT, $message_types);
	$r->add_textbox("admin_message", TEXT);

	$r->add_checkbox("user_notification", INTEGER);
	$r->add_textbox("user_mail_from", TEXT);
	$r->add_textbox("user_mail_cc", TEXT);
	$r->add_textbox("user_mail_bcc", TEXT);
	$r->add_textbox("user_mail_reply_to", TEXT);
	$r->add_textbox("user_mail_return_path", TEXT);
	$r->add_textbox("user_subject", TEXT);
	$r->add_radio("user_message_type", TEXT, $message_types);
	$r->add_textbox("user_message", TEXT);

	// comment notification fields
	$r->add_checkbox("admin_comment_notification", INTEGER);
	$r->add_textbox("admin_comment_to", TEXT);
	$r->add_textbox("admin_comment_from", TEXT);
	$r->add_textbox("admin_comment_cc", TEXT);
	$r->add_textbox("admin_comment_bcc", TEXT);
	$r->add_textbox("admin_comment_reply_to", TEXT);
	$r->add_textbox("admin_comment_return_path", TEXT);
	$r->add_textbox("admin_comment_subject", TEXT);
	$r->add_radio("admin_comment_message_type", TEXT, $message_types);
	$r->add_textbox("admin_comment_message", TEXT);

	$r->add_checkbox("user_comment_notification", INTEGER);
	$r->add_textbox("user_comment_from", TEXT);
	$r->add_textbox("user_comment_cc", TEXT);
	$r->add_textbox("user_comment_bcc", TEXT);
	$r->add_textbox("user_comment_reply_to", TEXT);
	$r->add_textbox("user_comment_return_path", TEXT);
	$r->add_textbox("user_comment_subject", TEXT);
	$r->add_radio("user_comment_message_type", TEXT, $message_types);
	$r->add_textbox("user_comment_message", TEXT);

	// comment notification fields send to reviewer
	$r->add_checkbox("admin_reply_notification", INTEGER);
	$r->add_textbox("admin_reply_from", TEXT);
	$r->add_textbox("admin_reply_cc", TEXT);
	$r->add_textbox("admin_reply_bcc", TEXT);
	$r->add_textbox("admin_reply_reply_to", TEXT);
	$r->add_textbox("admin_reply_return_path", TEXT);
	$r->add_textbox("admin_reply_subject", TEXT);
	$r->add_radio("admin_reply_message_type", TEXT, $message_types);
	$r->add_textbox("admin_reply_message", TEXT);

	$r->add_checkbox("user_reply_notification", INTEGER);
	$r->add_textbox("user_reply_from", TEXT);
	$r->add_textbox("user_reply_cc", TEXT);
	$r->add_textbox("user_reply_bcc", TEXT);
	$r->add_textbox("user_reply_reply_to", TEXT);
	$r->add_textbox("user_reply_return_path", TEXT);
	$r->add_textbox("user_reply_subject", TEXT);
	$r->add_radio("user_reply_message_type", TEXT, $message_types);
	$r->add_textbox("user_reply_message", TEXT);
	
	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	//$tab = get_param("tab");
	//if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();

		if (!strlen($r->errors))
		{
			$new_settings = array();
			foreach ($r->parameters as $key => $value) {
				$new_settings[$key] = $value[CONTROL_VALUE];
			}
			update_settings($setting_type, $param_site_id, $new_settings);
			set_session("session_settings", "");

			// show success message
			$t->parse("success_block", false);			
		}
	}
	else // get products_reviews settings
	{
		foreach ($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='products_reviews' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// multi-site settings
	multi_site_settings();

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => va_constant("ADMIN_GENERAL_MSG")), 
		"predefined_fields" => array("title" => va_constant("REVIEW_FIELDS_MSG")), 
		"review_notify" => array("title" => va_constant("NEW_REVIEW_NOTIFY_MSG")), 
		"comment_notify" => array("title" => va_constant("NEW_COMMENT_NOTIFY_MSG")), 
		"reviewer_notify" => array("title" => va_constant("REVIEWER_NOTIFY_MSG")), 
	);
	parse_tabs($tabs);

	include_once("./admin_footer.php");
	
	$t->pparse("main");
