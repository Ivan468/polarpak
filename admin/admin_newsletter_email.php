<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletter_email.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once("../includes/common.php");
	include_once("../includes/record.php");
	include_once("../includes/newsletter_functions.php");

	include_once("./admin_common.php");

	check_admin_security("newsletter");

	$newsletter_id = get_param("newsletter_id");
	$email_id = get_param("email_id");

	$sql  = " SELECT newsletter_id FROM " . $table_prefix . "newsletters ";
	$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
	$db->query($sql);
	if($db->next_record()) {
		$newsletter_id = $db->f("newsletter_id");
	} else {
		die(OBJECT_NO_EXISTS_MSG);
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_newsletter_email.html");
	$t->set_var("admin_user_href",   "admin_user.php");
	$t->set_var("admin_users_href",  "admin_users.php");
	$t->set_var("admin_user_login_href", "admin_user_login.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", EMAIL_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "newsletters_emails");
	$r->return_page = "admin_newsletter_emails.php";

	$r->add_where("email_id", INTEGER);
	$r->add_textbox("newsletter_id", INTEGER);
	$r->change_property("newsletter_id", USE_IN_INSERT, true);
	$r->change_property("newsletter_id", USE_IN_UPDATE, false);
	$r->change_property("newsletter_id", REQUIRED, true);
	$r->change_property("newsletter_id", TRANSFER, true);
	$r->change_property("newsletter_id", DEFAULT_VALUE, $newsletter_id);

	$r->add_textbox("user_email", TEXT, EMAIL_FIELD);
	$r->change_property("user_email", REQUIRED, true);
	$r->change_property("user_email", TRIM, true);
	$r->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->change_property("user_email", AFTER_VALIDATE, "validate_unique_email");

	$r->add_textbox("user_name", TEXT);
	$r->change_property("user_name", TRIM, true);

	$r->events[AFTER_INSERT] = "count_newsletter_emails";
	$r->events[AFTER_UPDATE] = "count_newsletter_emails";
	$r->events[AFTER_DELETE] = "count_newsletter_emails";

	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function validate_unique_email()
	{
		global $r, $db, $table_prefix;
		
		if ($r->parameters["user_email"][IS_VALID]) {
			$email_id = $r->get_value("email_id");
			$newsletter_id = $r->get_value("newsletter_id");
			$user_email = $r->get_value("user_email");

			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$sql .= " AND user_email=" . $db->tosql($user_email, TEXT);
			if (strlen($email_id)) {
				$sql .= " AND email_id<>" . $db->tosql($email_id, INTEGER);
			}
			$is_email_exists = get_db_value($sql);
			if ($is_email_exists) {
				$error_message = str_replace("{field_name}", $r->parameters["user_email"][CONTROL_DESC], UNIQUE_MESSAGE);
				$r->parameters["user_email"][IS_VALID] = false;
				$r->parameters["user_email"][ERROR_DESC] = $error_message;
			}
		}
	}

?>