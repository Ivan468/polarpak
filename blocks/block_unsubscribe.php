<?php

	$default_title = "{UNSUBSCRIBE_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_unsubscribe.html"); 
  $t->set_file("block_body", $html_template);

	$error_desc   = "";
	$message_desc = "";
	$operation = get_param("operation");
	$eid = get_param("eid");
	$unsubscribe  = get_param("unsubscribe");
//57416

	$query_string = transfer_params("", true);
	$t->set_var("query_string", htmlspecialchars($query_string));

	$unsubscribed_email = get_param("unsubscribed_email");

	$unsubscribe_desc = str_replace("{button_name}", UNSUBSCRIBE_BUTTON, UNSUBSCRIBE_FORM_MSG);
	$t->set_var("UNSUBSCRIBE_FORM_MSG", $unsubscribe_desc);

	if (strlen($unsubscribed_email)) {
		if(preg_match(EMAIL_REGEXP, $unsubscribed_email)) {
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_users ";
			$sql .= " WHERE email=" . $db->tosql($unsubscribed_email, TEXT);
			$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
			$db->query($sql);
			$db->next_record();
			$email_count = $db->f(0);
			if($email_count > 0) {
				$sql  = " DELETE FROM " . $table_prefix . "newsletters_users ";
				$sql .= " WHERE email=" . $db->tosql($unsubscribed_email, TEXT);
				$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
				$db->query($sql);
				$message_desc = UNSUBSCRIBED_MSG;
			} else {
				$error_desc = UNSUBSCRIBED_ERROR_MSG;
			}
		} else {
			$error_desc = INVALID_EMAIL_MSG;
		}
	}
	if ($operation == "unsubscribe") {
		if ($eid) {
			$sql  = " SELECT * FROM " . $table_prefix . "newsletters_emails ";
			$sql .= " WHERE email_id=" . $db->tosql($eid, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$user_email = $db->f("user_email");
				$email_type = intval($db->f("email_type"));
				$order_id = $db->f("order_id");
				$user_id = $db->f("user_id");
				// unsubscribed from 
				if ($email_type & 1) {
					$sql  = " DELETE FROM " . $table_prefix . "newsletters_users ";
					$sql .= " WHERE email=" . $db->tosql($user_email, TEXT);
					$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
					$db->query($sql);
					$message_desc = UNSUBSCRIBED_MSG;
				}
				if ($user_id) {
					$sql  = " UPDATE " . $table_prefix . "users ";
					$sql .= " SET is_unsubscribed=1 ";
					$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
					$db->query($sql);
				}
				if ($order_id) {
					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET is_unsubscribed=1 ";
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$db->query($sql);
					$message_desc = "You were successfully unsubscribed from this campaign.";
				}

				// mark unsubscribed email  for newsletter 
				$sql  = " UPDATE " . $table_prefix . "newsletters_emails ";
				$sql .= " SET is_unsubscribed=1 ";
				$sql .= " WHERE email_id=" . $db->tosql($eid, INTEGER);
				$db->query($sql);

			} else {
				$error_desc = "Couldn't find email in DB.";
			}
		}
	}

	if ($message_desc) {
		$t->set_var("message_desc", $message_desc);
		$t->parse("unsubscribe_message", false);
	}
	if ($error_desc) {
		$t->set_var("unsubscribed_email", htmlspecialchars($unsubscribed_email));
		$t->set_var("error_desc", $error_desc);
		$t->parse("unsubscribe_error", false);
	} else {
		$t->set_var("unsubscribed_email", "");
	}


	$block_parsed = true;

?>