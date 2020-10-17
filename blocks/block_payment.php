<?php

	$html_template = get_setting_value($block, "html_template", "block_payment.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("payment_url","payment.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");
	$payment_id = get_session("session_payment_id");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_payments = get_setting_value($settings, "secure_payments", 0);

	$order_errors = check_order($order_id, $vc);
	if ($order_errors) {
		$t->set_var("errors_list", $order_errors);
		$t->parse("errors");
	} else {

		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $form_params, $pass_data, $variables);

		$payment_name = $variables["payment_name"];
		$payment_url = $variables["payment_url"];
		$payment_script = $variables["payment_script"];
		$is_advanced = $variables["is_advanced"];
		$submit_method = $variables["submit_method"];
		if (!$payment_url) { $payment_url = "credit_card_info.php"; }
		if ($secure_payments && !preg_match("/^http\:\/\//", $payment_url) && !preg_match("/^https\:\/\//", $payment_url)) {
			$payment_url = $secure_url . $payment_url;
		}

		if ($payment_script) {
			include($payment_script);
		} else {

			if ($is_advanced) {
				// for advanced orders we collect credit card info on our site
				$payment_url .= "?order_id=" . urlencode($order_id) . "&vc=" . urlencode($vc);
	  
				header("Location: " . $payment_url);
				exit;
			}
	  
			$t->set_var("payment_url", htmlspecialchars($payment_url));
			$t->set_var("payment_name", $payment_name);
			$t->set_var("submit_method", $submit_method);
	  
			$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
			$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
			$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	  
			if ($submit_method == "GET") {
				if ($form_params) {
					$payment_url .= strpos($payment_url,"?") ? "&" : "?";
					$payment_url .= $form_params;
				}
						
				if (preg_match("/credit_card_info\.php\s*$/", $payment_url)) {
					$payment_url .= "?order_id=" . urlencode($order_id) . "&vc=" . urlencode($vc);
				}
	  
				header("Location: " . $payment_url);
				exit;
			}
	  
			$params_pairs = explode("&", $form_params);
			for ($p = 0; $p < sizeof($params_pairs); $p++) {
				list($param_name, $param_value) = explode("=", $params_pairs[$p], 2);
				$param_name = urldecode($param_name);
				$param_value = urldecode($param_value);
				$t->set_var("parameter_name", htmlspecialchars($param_name));
				$t->set_var("parameter_value", htmlspecialchars($param_value));
				$t->parse("parameters", true);
			}
			$t->sparse("submit_payment", false);

		}
	}

	$block_parsed = true;

?>