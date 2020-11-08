<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletter_emails.php                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once("../includes/common.php");
	include_once("../includes/sorter.php");
	include_once("../includes/navigator.php");
	include_once("../includes/record.php");
	include_once("../includes/newsletter_functions.php");
	include_once("../messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("newsletter");

	$ids = get_param("ids");
	$operation = get_param("operation");
	$newsletter_id = get_param("newsletter_id");
	$permissions = get_permissions();

	// get campaign data
	$sql  = " SELECT nc.campaign_id, nc.campaign_name FROM " . $table_prefix . "newsletters_campaigns nc ";
	$sql .= " INNER JOIN  " . $table_prefix . "newsletters n ON n.campaign_id=nc.campaign_id ";
	$sql .= " WHERE n.newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$campaign_id = $db->f("campaign_id");
		$campaign_name = $db->f("campaign_name");
	} else {
		header ("Location: admin_newsletter_campaigns.php");
		exit;
	}


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_newsletter_emails.html");

	$t->set_var("newsletter_id", htmlspecialchars($newsletter_id));
	$t->set_var("campaign_id", htmlspecialchars($campaign_id));
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_user_href", "admin_user.php");
	$t->set_var("admin_users_href", "admin_users.php");
	$t->set_var("admin_user_login_href", "admin_user_login.php");
	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_export_href", "admin_export.php");
	$t->set_var("admin_newsletter_email_href", "admin_newsletter_email.php");
	$t->set_var("admin_newsletter_emails_href", "admin_newsletter_emails.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_newsletter_emails.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ID_MSG, "sorter_email_id", "1", "email_id");
	$s->set_sorter(EMAIL_MSG, "sorter_user_email", "2", "user_email");
	$s->set_sorter(NAME_MSG, "sorter_user_name", "3", "user_name");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_newsletter_emails.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$operation = get_param("operation");
	$users_ids = get_param("users_ids");
	$rnd = get_param("rnd");

	srand((double) microtime() * 1000000);
	$new_rnd = rand();

	$users_messages = ""; $users_errors = "";
	$birth_messages = ""; $birth_errors = ""; 

	$r = new VA_Record($table_prefix . "users");
	$r->add_hidden("newsletter_id", TEXT);
	$r->add_textbox("s_ne", TEXT);
	$r->change_property("s_ne", TRIM, true);
	$r->get_form_parameters();
	$r->validate();
	$r->set_form_parameters();

	if ($operation == "delete_emails" && strlen($ids)){
		$sql = "DELETE FROM " . $table_prefix . "newsletters_emails WHERE email_id IN (" . $db->tosql($ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		count_newsletter_emails($newsletter_id);
	}

	$where = " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
	if (!$r->is_empty("s_ne")) {
		$s_ne_sql = $db->tosql($r->get_value("s_ne"), TEXT, false);
		$where .= " AND (user_name LIKE '%" . $s_ne_sql . "%'";
		$where .= " OR user_email LIKE '%" . $s_ne_sql . "%')";
	}

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
	$sql .= $where;
	$total_records = get_db_value($sql);

	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$users = array();

	$sql  = " SELECT email_id, user_name, user_email ";
	$sql .= " FROM " . $table_prefix . "newsletters_emails ";
	$sql .= $where;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql . $s->order_by);
	if ($db->next_record()) {
		$admin_user_points = new VA_URL("admin_user_points.php", false);
		$admin_user_points->add_parameter("s_ne", REQUEST, "s_ne");
		$admin_user_points->add_parameter("s_ad", REQUEST, "s_ad");
		$admin_user_points->add_parameter("s_sd", REQUEST, "s_sd");
		$admin_user_points->add_parameter("s_ed", REQUEST, "s_ed");
		$admin_user_points->add_parameter("s_ut", REQUEST, "s_ut");
		$admin_user_points->add_parameter("s_ap", REQUEST, "s_ap");
		$admin_user_points->add_parameter("s_on", REQUEST, "s_on");
		$admin_user_points->add_parameter("page", REQUEST, "page");
		$admin_user_points->add_parameter("sort_ord", REQUEST, "sort_ord");
		$admin_user_points->add_parameter("sort_dir", REQUEST, "sort_dir");

		$user_index = 0;
		do {
			$email_id = $db->f("email_id");
			$user_name = $db->f("user_name");
			$user_email = $db->f("user_email");
	  
			$user_index++;
			$row_style = ($user_index % 2 == 0) ? "row1" : "row2";
			$t->set_var("row_style", $row_style);
	  
			$t->set_var("user_index", $user_index);
			$t->set_var("email_id", $email_id);
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("user_email", $user_email);

			$admin_user_points->add_parameter("email_id", CONSTANT, $email_id);
			$t->set_var("admin_user_change_type_url", $admin_user_points->get_url("admin_user_change_type.php"));
			$t->set_var("admin_user_points_url", $admin_user_points->get_url("admin_user_points.php"));
			$t->set_var("admin_user_credits_url", $admin_user_points->get_url("admin_user_credits.php"));
	  
			$t->parse("records", true);

		} while ($db->next_record());

		$t->set_var("users_number", $user_index);
		$t->parse("sorters", false);

		$t->set_var("no_records", "");
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->set_var("rnd", $new_rnd);

	if (strlen($users_errors)) {
		$t->set_var("errors_list", $users_errors);
		$t->parse("users_errors", false);
	}

	if (strlen($users_messages)) {
		$t->set_var("messages_list", $users_messages);
		$t->parse("users_messages", false);
	}

	$t->pparse("main");

?>