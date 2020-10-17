<?php

	$default_title = "{PAYMENT_DETAILS_MSG}";

	check_user_security("my_orders");

	$user_type_id = get_session("session_user_type_id");
	if (!$user_type_id) { $user_type_id = get_session("session_new_user_type_id"); }

	$html_template = get_setting_value($block, "html_template", "block_user_psd_list.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_home_href",   get_custom_friendly_url("user_home.php"));
	$t->set_var("user_order_payment_href", get_custom_friendly_url("user_order_payment.php"));
	$t->set_var("user_psd_update_href",   get_custom_friendly_url("user_psd_update.php"));

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("user_orders.php"));

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users_ps_details upd ";
	$sql .= " WHERE upd.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT upd.psd_id, ps.payment_name, ps.user_payment_name, upd.cc_name, upd.cc_number, ";
	$sql .= " upd.cc_expiry_date, cc.credit_card_name, upd.is_default, upd.is_active ";
	$sql .= " FROM ((" . $table_prefix . "users_ps_details upd ";
	$sql .= " LEFT JOIN " . $table_prefix . "payment_systems ps ON ps.payment_id=upd.payment_id) ";			
	$sql .= " LEFT JOIN " . $table_prefix . "credit_cards cc ON cc.credit_card_id=upd.cc_type) ";
	$sql .= " WHERE upd.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= " ORDER BY upd.psd_id DESC ";
	$db->query($sql);
	if ($db->next_record())
	{
		$expiry_date_format = array("MM", " / ", "YYYY");
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$psd_id = $db->f("psd_id");
			$payment_type = get_translation($db->f("user_payment_name"));
			if (!$payment_type) {
				$payment_type = get_translation($db->f("payment_name"));
			}
			$cc_name = $db->f("cc_name");
			$cc_number = $db->f("cc_number");
			$cc_number = va_decrypt($cc_number);
			$cc_number = format_cc_number($cc_number, "-", true);
			$cc_expiry_date = $db->f("cc_expiry_date", DATETIME);
			$cc_type = $db->f("credit_card_name");
			$is_default = $db->f("is_default");
			$is_active = $db->f("is_active");
			if ($is_default) {
				$is_default = "<font color=\"blue\"><b>" . YES_MSG . "</b></font>";
			} else  {
				$is_default = "<font color=\"silver\">" . NO_MSG . "</font>";
			} 
			if ($is_active) {
				$is_active = "<font color=\"blue\"><b>" . YES_MSG . "</b></font>";
			} else  {
				$is_active = "<font color=\"silver\">" . NO_MSG . "</font>";
			} 

			$t->set_var("psd_id", $psd_id);
			$t->set_var("payment_type", $payment_type);
			$t->set_var("cc_name", $cc_name);
			$t->set_var("cc_type", $cc_type);
			$t->set_var("cc_number", $cc_number);
			$t->set_var("cc_expiry_date", va_date($expiry_date_format, $cc_expiry_date));
			$t->set_var("is_default", $is_default);
			$t->set_var("is_active", $is_active);

			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	// parse 'add new' links
	$payment_index = 0;
	$sql  = " SELECT ps.payment_id, ps.payment_name, ps.user_payment_name ";
	$sql .= " FROM ((" . $table_prefix . "payment_systems ps ";
	$sql .= " LEFT JOIN " . $table_prefix . "payment_systems_sites s ON s.payment_id=ps.payment_id) ";			
	$sql .= " LEFT JOIN " . $table_prefix . "payment_user_types ut ON ut.payment_id=ps.payment_id) ";
	$sql .= " WHERE ps.is_active=1 AND ps.allowed_user_edit=1 ";
	if (isset($site_id)) {
		$sql .= " AND (ps.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER) . ")";			
	} else {
		$sql .= " AND ps.sites_all=1";
	}
	if (strlen($user_type_id)) {
		$sql .= " AND (ps.user_types_all=1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER) . ")";			
	} else {
		$sql .= " AND ps.user_types_all=1";
	}
	$db->query($sql);
	while ($db->next_record()) {

		$payment_index++;
		$payment_id = $db->f("payment_id");
		$payment_type = get_translation($db->f("user_payment_name"));
		if (!$payment_type) {
			$payment_type = get_translation($db->f("payment_name"));
		}
		$new_url  = get_custom_friendly_url("user_psd_update.php");
		$new_url .= "?payment_id=".urlencode($payment_id);

		if ($payment_index > 1) {
			$t->set_var("link_separator", " | ");
		}
		$t->set_var("payment_type", $payment_type);
		$t->set_var("user_psd_new_url", $new_url);
		$t->parse("add_new_links", true);
	}
	
	$block_parsed = true;

?>