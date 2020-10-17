<?php

	$default_title = "{SUBSCRIBE_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_subscribe.html"); 
  $t->set_file("block_body", $html_template);

	$error_desc   = "";
	$message_desc = "";
	$unsubscribe  = get_param("unsubscribe");

	$query_string = transfer_params("", true);
	$t->set_var("query_string", htmlspecialchars($query_string));

	$subscribed_email = trim(get_param("subscribed_email"));

	$subscribe_desc = str_replace("{button_name}", SUBSCRIBE_BUTTON, SUBSCRIBE_FORM_MSG);
	$t->set_var("SUBSCRIBE_FORM_MSG", $subscribe_desc);

	if (strlen($subscribed_email)) {
		if(preg_match(EMAIL_REGEXP, $subscribed_email)) {
			$sql  = " SELECT email_id, site_id FROM " . $table_prefix . "newsletters_users ";
			$sql .= " WHERE email=" . $db->tosql($subscribed_email, TEXT);
			$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
			$db->query($sql);
			if ($db->next_record()) {
				$email_id = $db->f("email_id");
				$email_site_id = $db->f("site_id");
				if (!$email_site_id) {
					$sql  = " UPDATE " . $table_prefix . "newsletters_users ";
					$sql .= " SET site_id=" . $db->tosql($site_id, INTEGER);
					$sql .= " WHERE email_id=" . $db->tosql($email_id, INTEGER);
					$db->query($sql);
				}
				$message_desc = ALREADY_SUBSCRIBED_MSG;
			} else {
				$sql  = " INSERT INTO " . $table_prefix . "newsletters_users (site_id, email, date_added) ";
				$sql .= " VALUES (";
				$sql .= $db->tosql($site_id, INTEGER) . ", ";
				$sql .= $db->tosql($user_email, TEXT) . ", ";
				$sql .= $db->tosql(va_time(), DATETIME) . ") ";
				$db->query($sql);
				$message_desc = SUBSCRIBED_MSG;
			}
		} else {
			$error_desc = INVALID_EMAIL_MSG;
		}
	}

	if ($message_desc) {
		$t->set_var("message_desc", $message_desc);
		$t->parse("subscribe_message", false);
	}
	if ($error_desc) {
		$t->set_var("subscribed_email", htmlspecialchars($subscribed_email));
		$t->set_var("error_desc", $error_desc);
		$t->parse("subscribe_error", false);
	} else {
		$t->set_var("subscribed_email", "");
	}

	$block_parsed = true;

