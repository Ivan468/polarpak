<?php

	$default_title = "{COUPON_MSG}";

	$html_template = get_setting_value($block, "html_template", "block_coupon_form.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("coupon_errors", "");
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

	$coupons_applied = 0;
	$coupon_code = get_param("coupon_code");
	$coupon_operation = get_param("coupon_operation");
	$coupon_errors = ""; 
	if($coupon_operation == "add") {
		
		if(!strlen($coupon_code)) {
			$error_message = str_replace("{field_name}", COUPON_CODE_FIELD, REQUIRED_MESSAGE);
			$coupon_errors .= $error_message . "<br>";
		}
	  
		if(!strlen($coupon_errors)) {
			$coupons_applied = check_add_coupons(true, $coupon_code, $coupon_errors);
		}
	}


	if(strlen($coupon_errors))
	{
		$t->set_var("coupon_code", htmlspecialchars($coupon_code));
		$t->set_var("errors_list", $coupon_errors);
		$t->parse("coupon_errors", false);
	} else if ($coupons_applied) {
		$t->set_var("coupon_message", COUPON_ADDED_MSG);
		$t->parse("message_block", false);
	}

	$block_parsed = true;

?>