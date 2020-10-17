<?php

	$default_title = FRIEND_AFFILIATE_FORM_MSG;

	$html_template = get_setting_value($block, "html_template", "block_friend_form.html"); 
  $t->set_file("block_body", $html_template);

	$form_desc = str_replace("{APPLY_BUTTON}", APPLY_BUTTON, FRIEND_AFFILIATE_FORM_DESC);
	$t->set_var("FRIEND_AFFILIATE_FORM_DESC", $form_desc);

	$t->set_var("errors_block", "");
	$t->set_var("message_block", "");

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$query_string = transfer_params("", true);
	if ($is_ssl) {
		$current_url = $secure_url . $current_page . $query_string;
	} else {
		$current_url = $site_url . $current_page . $query_string;
	}

  $t->set_var("current_url", $current_url);

	$errors = "";
	$friend_id = "";
	$friend_code = get_param("friend_code");
	$operation = get_param("operation");
	$param_pb_id = get_param("pb_id");
	if($operation == "apply" && $param_pb_id == $pb_id)
	{
		if(!strlen($friend_code)) {
			$errors .= str_replace("{field_name}", AFFILIATE_CODE_FIELD." / ".NICKNAME_FIELD, REQUIRED_MESSAGE) . "<br />";
		}
	  
		if(!strlen($errors)) {
			$friend_info = get_friend_info(3, $friend_code);
			$friend_type= get_setting_value($friend_info, "type", "");
			$friend_id = get_setting_value($friend_info, "user_id", "");
			$friend_type_id= get_setting_value($friend_info, "user_type_id", "");
			if (!strlen($friend_id)) {
				$errors .= NO_USERS_MSG. "<br />";
			}
			if ($friend_type == "affiliate") {
				set_session("session_af", $friend_code);
				set_session("session_af_id", $friend_id);
				set_session("session_af_type_id", $friend_type_id);
			} else if ($friend_type == "friend") {
				set_session("session_friend", $friend_code);
				set_session("session_friend_id", $friend_id);
				set_session("session_friend_type_id", $friend_type_id);
			}
			check_coupons();
		}
	}


	if(strlen($errors)) {
		$t->set_var("friend_code", htmlspecialchars($friend_code));
		$t->set_var("errors", $errors);
		$t->parse("errors_block", false);
	} else if (strlen($friend_id)) {
		if ($friend_type == "affiliate") {
			$t->set_var("message", AFFILIATE_FOUND_MSG);
		} else {
			$t->set_var("message", FRIEND_FOUND_MSG);
		}
		$t->parse("message_block", false);
	}

	$block_parsed = true;

?>