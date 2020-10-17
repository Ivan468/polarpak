<?php

	$default_title = "{SMS_TEST_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_sms_test.html"); 
  $t->set_file("block_body", $html_template);

	$error_desc   = "";
	$phone_number  = get_param("phone_number");
	$query_string = transfer_params("", true);
	
	$t->set_var("query_string", $query_string);
	$sms_test_desc = str_replace("SEND_BUTTON", SEND_BUTTON, SMS_TEST_DESC);
	$t->set_var("SMS_TEST_DESC", $sms_test_desc);

	if (strlen($phone_number)) {
		if(preg_match("/^\+?\d+$/", $phone_number)) {
			sms_send($phone_number, get_setting_value($vars, "sms_test_message", ""), get_setting_value($vars, "sms_originator", ""));
		} else {
			$error_desc = INVALID_CELL_PHONE;
		}
	}

	if ($error_desc) {
		$t->set_var("phone_number", htmlspecialchars($phone_number));
		$t->set_var("error_desc", $error_desc);
		$t->parse("sms_error", false);
	} else {
		$t->set_var("phone_number", "");
	}

	$block_parsed = true;

?>