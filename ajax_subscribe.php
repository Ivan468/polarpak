<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ajax_subscribe.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	include_once("./includes/common.php");

	$operation = get_param("operation");
	$email = trim(get_param("email"));

	$error_desc = ""; $message_desc = "";
	if ($operation == "subscribe") {
		if (strlen($email)) {
			if(preg_match(EMAIL_REGEXP, $email)) {
				$sql  = " SELECT email_id, site_id FROM " . $table_prefix . "newsletters_users ";
				$sql .= " WHERE email=" . $db->tosql($email, TEXT);
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
		} else {
			$error_desc = INVALID_EMAIL_MSG;
		}
	} else if ($operation == "unsubscribe") {
		if (strlen($email)) {
			if(preg_match(EMAIL_REGEXP, $email)) {
				$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_users ";
				$sql .= " WHERE email=" . $db->tosql($email, TEXT);
				$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
				$db->query($sql);
				$db->next_record();
				$email_count = $db->f(0);
				if($email_count > 0) {
					$sql  = " DELETE FROM " . $table_prefix . "newsletters_users ";
					$sql .= " WHERE email=" . $db->tosql($email, TEXT);
					$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
					$db->query($sql);
					$message_desc = UNSUBSCRIBED_MSG;
				} else {
					$error_desc = UNSUBSCRIBED_ERROR_MSG;
				}
			} else {
				$error_desc = INVALID_EMAIL_MSG;
			}
		} else {
			$error_desc = INVALID_EMAIL_MSG;
		}
	} else {
		$error_desc = "Unknown operation";
	}

	if ($error_desc) {
		echo json_encode(array("result" => "error", "message" => $error_desc));
	} else {
		echo json_encode(array("result" => "success", "message" => $message_desc));
	}

