<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_invoice_html.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$includes_path = "./includes/";
	include_once($includes_path . "common.php");
	include_once($includes_path . "record.php");
	include_once($includes_path . "order_items.php");
	include_once($includes_path . "parameters.php");
	include_once($includes_path . "products_functions.php");
	include_once($includes_path . "shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/admin_messages.php");	
	
	check_user_security("my_orders");

	$currency = get_currency();

	$order_id = get_param("order_id");
	$sql  = " SELECT os.user_invoice_activation ";
	$sql .= " FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
	$user_invoice_activation = get_db_value($sql);
	// check if user can access the invoice
	if (!$user_invoice_activation) {
		header("Location: user_orders.php");
		exit;
	}

	$invoice = array();
	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='printable'";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$invoice[$db->f("setting_name")] = $db->f("setting_value");
	}

	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='order_info'";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "user_invoice_html.html");

	$r = new VA_Record($table_prefix . "orders");
	$r->add_where("order_id", INTEGER);
	$r->set_value("order_id", $order_id);

	$items_text = show_order_items($order_id, true, "user_invoice_html");

	$personal_number = 0;
	$delivery_number = 0;
	for ($i = 0; $i < sizeof($parameters); $i++)
	{                                    
		$personal_param = "show_" . $parameters[$i];
		$delivery_param = "show_delivery_" . $parameters[$i];
		$r->add_textbox($parameters[$i], TEXT);
		$r->add_textbox("delivery_" . $parameters[$i], TEXT);
		if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
			$personal_number++;
		} else {
			$r->parameters[$parameters[$i]][SHOW] = false;
		}
		if (isset($order_info[$delivery_param]) && $order_info[$delivery_param] == 1) {
			$delivery_number++;
		} else {
			$r->parameters["delivery_" . $parameters[$i]][SHOW] = false;
		}
	}

	$r->add_textbox("invoice_number", TEXT);
	$r->add_textbox("invoice_copy_number", INTEGER);
	$r->add_where("user_id", INTEGER);
	$r->set_value("user_id", get_session("session_user_id"));
	$r->add_textbox("payment_id", INTEGER);
	$r->add_textbox("transaction_id", TEXT);
	$r->add_textbox("currency_code", TEXT);
	$r->add_textbox("shipping_tracking_id", TEXT);
	$r->add_textbox("remote_address", TEXT);
	$r->add_textbox("cc_name", TEXT);
	$r->add_textbox("cc_first_name", TEXT);
	$r->add_textbox("cc_last_name", TEXT);
	$r->add_textbox("cc_number", TEXT);
	$r->add_textbox("cc_start_date", DATETIME);
	$r->change_property("cc_start_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->add_textbox("cc_expiry_date", DATETIME);
	$r->change_property("cc_expiry_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->add_textbox("cc_type", INTEGER);
	$r->add_textbox("cc_issue_number", INTEGER);
	$r->add_textbox("cc_security_code", TEXT);
	$r->add_textbox("pay_without_cc", TEXT);

	// check user's attempt to get alien order
	if (!$r->get_db_values()) {
		//echo "You are trying to get information about somebody else's order.";
		header("Location: user_orders.php");
		exit;
	}

	$sql  = " SELECT payment_name ";
	// check payment properties
	$invoice_payment_gateway = get_setting_value($invoice, "invoice_payment_gateway", 0);
	$invoice_payment_info = get_setting_value($invoice, "invoice_payment_info", 0);

	$t->set_var("payment_name", "");
	$t->set_var("payment_system_name", "");
	$t->set_var("payment_gateway_block", "");

	$sql  = " SELECT payment_name, user_payment_name, payment_info ";
	$sql .= " FROM " . $table_prefix . "payment_systems ";
	$sql .= " WHERE payment_id=" . $db->tosql($r->get_value("payment_id"), INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$payment_name = get_translation($db->f("user_payment_name"));
		if (!$payment_name) { $payment_name = get_translation($db->f("payment_name")); } 
		$payment_info = $db->f("payment_info");
		$t->set_var("payment_name", $payment_name);
		$t->set_var("payment_system_name", $payment_name);
		if ($invoice_payment_gateway) {
			$t->sparse("payment_gateway_block", false);
		}

		if ($invoice_payment_info && $payment_info) {
			$payment_info = strip_tags($payment_info);
			$payment_info = str_replace(array("\r\n", "\r"), "\n", $payment_info);
			$t->set_var("payment_info", nl2br($payment_info));
			$t->sparse("payment_info_block", false);
		}
	}
	if ($r->is_empty("transaction_id")) {
		$r->parameters["transaction_id"][SHOW] = false;
	}
	if ($r->is_empty("invoice_number")) {
		$r->set_value("invoice_number", $r->get_value("order_id"));
	}

	$r->set_value("company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("company_id"), INTEGER, true, false))));
	$r->set_value("state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), TEXT))));
	$r->set_value("country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), TEXT))));
	$r->set_value("delivery_company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("delivery_company_id"), INTEGER, true, false))));
	$r->set_value("delivery_state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("delivery_state_id"), TEXT))));
	$r->set_value("delivery_country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("delivery_country_id"), TEXT))));
	$r->set_value("cc_type", get_translation(get_db_value("SELECT credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($r->get_value("cc_type"), INTEGER))));

	for ($i = 0; $i < sizeof($parameters); $i++)
	{                                    
		$personal_param = $parameters[$i];
		$delivery_param = "delivery_" . $parameters[$i];
		if ($r->is_empty($personal_param)) {
			$r->parameters[$personal_param][SHOW] = false;
		}
		if ($r->is_empty($delivery_param)) {
			$r->parameters[$delivery_param][SHOW] = false;
		}
	}
	
	$r->set_parameters();

	if ($personal_number > 0) {
		$t->parse("personal", false);
	}

	if ($delivery_number > 0) {
		$t->parse("delivery", false);
	}
	// show invoice copy number
	$invoice_copy_number = $r->get_value("invoice_copy_number");
	$invoice_copy_number++;
	// update invoice copy number
	$sql = " UPDATE " . $table_prefix . "orders ";
	$sql.= " SET invoice_copy_number=" . $db->tosql($invoice_copy_number, INTEGER);
	$sql.= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);

	$copy_number_option = get_setting_value($invoice, "invoice_copy_number", 0);
	if ($copy_number_option) {
		if ($invoice_copy_number == 1) {
			$copy_message = ORIGINAL_COPY_MSG;
		} else {
			$copy_message = str_replace("{copy_number}",  $invoice_copy_number, COPY_NUMBER_MSG);
		}
		$t->set_var("copy_message", $copy_message);
		$t->sparse("copy_message_block", false);
	}


	if (isset($invoice["invoice_header"])) {
		$t->set_var("invoice_header", nl2br($invoice["invoice_header"]));
	}
	if (isset($invoice["invoice_logo"]) && strlen($invoice["invoice_logo"])) {
		$image_path = $invoice["invoice_logo"];
		$image_path = preg_replace("/^\.\.\/(.*)/", "./\$1", $image_path);
		if (preg_match("/^http\:\/\//", $image_path)) {
			$image_size = "";
		} else {
			$image_size = @getimagesize($image_path);
		}
		$t->set_var("image_path", htmlspecialchars($image_path));
		if (is_array($image_size)) {
			$t->set_var("image_width", "width=\"" . $image_size[0] . "\"");
			$t->set_var("image_height", "height=\"" . $image_size[1] . "\"");
		} else {
			$t->set_var("image_width", "");
			$t->set_var("image_height", "");
		}
		$t->parse("invoice_logo", false);
	}
	if (isset($invoice["invoice_footer"])) {
		$t->set_var("invoice_footer", nl2br($invoice["invoice_footer"]));
	}

	$t->pparse("main");

?>