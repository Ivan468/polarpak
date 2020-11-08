<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payment.php                                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$cms_page_code = "payment";

	include_once("./includes/common.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/order_items.php");
	include_once("./includes/parameters.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("payment_url","payment.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");
	$payment_id = get_session("session_payment_id");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_payments = get_setting_value($settings, "secure_payments", 0);

	// check general order errors
	$order_errors = check_order($order_id, $vc);

	// check 'prevent repurchase' option
	$order_info = get_settings("order_info");
	$prevent_repurchase = get_setting_value($order_info, "prevent_repurchase", 0);
	$repurchase_period = get_setting_value($order_info, "repurchase_period", "");

	if ($prevent_repurchase && !$order_errors) {

		// check user_id and user email address
		$order_user_id = ""; $order_email = "";
		$sql  = " SELECT user_id, email FROM " . $table_prefix ."orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_user_id = $db->f("user_id"); 
			$order_email = $db->f("email"); 
		}

		// check submitted order items
		$order_items = array();
		$sql  = " SELECT item_id, item_name FROM " . $table_prefix ."orders_items ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$item_id = $db->f("item_id");
			$item_name = get_translation($db->f("item_name"));
			if ($item_id > 0) {
				$order_items[$item_id] = $item_name;
			}
		}

		$current_ts = va_timestamp();
		$repurchase_ts = $current_ts - ($repurchase_period * 86400);
		// start repurchase check
		if (($order_user_id || $order_email) && sizeof($order_items)) {
			foreach ($order_items as $item_id => $item_name) {
				$sql  = " SELECT o.order_placed_date ";
				$sql .= " FROM ((" . $table_prefix . "orders_items oi ";
				$sql .= " INNER JOIN " . $table_prefix . "orders o ON o.order_id=oi.order_id) ";
				$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
				$sql .= " WHERE oi.item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND os.paid_status=1 ";
				if ($repurchase_period > 0) {
					$sql .= " AND o.order_placed_date>" . $db->tosql($repurchase_ts, DATETIME);
				}
				$sql .= " AND (";
				if ($order_user_id) {
					$sql .= " o.user_id=" . $db->tosql($order_user_id, INTEGER);
				}
				if ($order_email) {
					if ($order_user_id) { $sql .= " OR "; }
					$sql .= " o.email=" . $db->tosql($order_email, TEXT);
				}
				$sql .= ") ";
				$sql .= " ORDER BY o.order_placed_date DESC ";
				$db->RecordsPerPage = 1; $db->PageNumber = 1;
				$db->query($sql);
				if ($db->next_record()) {
					if ($repurchase_period > 0) {
						$item_purchased = $db->f("order_placed_date", DATETIME);
						$item_purchased_ts = va_timestamp($item_purchased);
						$days_number = ceil($repurchase_period - (($current_ts - $item_purchased_ts) / 86400));
						$sc_error = str_replace("{product_name}", $item_name, PURCHASED_PRODUCT_DAYS_ERROR);
						$sc_error = str_replace("{days_number}", $days_number, $sc_error);
						$order_errors .= $sc_error."<br>".$eol;
					} else {
						$sc_error = str_replace("{product_name}", $item_name, PURCHASED_PRODUCT_ERROR);
						$order_errors .= $sc_error."<br>".$eol;
					}
				}
			}
		} // end repurchase check
	}

	if ($order_errors) {
		$t->set_var("errors_list", $order_errors);
		$t->parse("errors");

		// clear session from order variables
		set_session("session_vc", "");
		set_session("session_order_id", "");
		set_session("session_user_order_id", "");
		set_session("session_payment_id", "");
	} else {

		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $form_params, $pass_data, $variables);

		$payment_name = get_setting_value($variables, "payment_name", "");
		$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);
		$user_payment_name = get_translation($user_payment_name);

		$payment_url = $variables["payment_url"];
		$is_advanced = $variables["is_advanced"];
		$submit_method = $variables["submit_method"];
		if (!$payment_url) { $payment_url = "credit_card_info.php"; }
		if ($secure_payments && !preg_match("/^http\:\/\//", $payment_url) && !preg_match("/^https\:\/\//", $payment_url)) {
			$payment_url = $secure_url . $payment_url;
		}

		if ($is_advanced) {
			// for advanced orders we collect credit card info on our site
			$payment_url .= "?order_id=" . urlencode($order_id) . "&vc=" . urlencode($vc);

			header("Location: " . $payment_url);
			exit;
		}


		$t->set_var("payment_url", htmlspecialchars($payment_url));
		$t->set_var("payment_name", $user_payment_name);
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

		if ($form_params) {
			$params_pairs = explode("&", $form_params);
			for ($p = 0; $p < sizeof($params_pairs); $p++) {
				list($param_name, $param_value) = explode("=", $params_pairs[$p], 2);
				$param_name = urldecode($param_name);
				$param_value = urldecode($param_value);
				$t->set_var("parameter_name", htmlspecialchars($param_name));
				$t->set_var("parameter_value", htmlspecialchars($param_value));
				$t->parse("parameters", true);
			}
		}
		$t->sparse("submit_payment", false);
	}
	$t->pparse("main");
