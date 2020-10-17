<?php

	$default_title = "{MY_ADS_MSG}";

	check_user_security("add_ad");

	// get ads settings
	$ads_settings = get_settings("ads");
	$funds_item_id = get_setting_value($ads_settings, "funds_item_id", "");

	$errors = "";
	$user_id = get_session("session_user_id");
	$item_id = get_param("item_id");
	$operation = get_param("operation");
	$ad_rnd = get_param("ad_rnd");
	$session_rnd = get_session("session_ad_rnd");

	// check if user click to pay for ad
	if ($operation == "pay" && strlen($item_id) && $ad_rnd != $session_rnd) {
		// save random value into session to prevent double click
		set_session("session_ad_rnd", $ad_rnd);

		$sql  = " SELECT is_paid, publish_price ";
		$sql .= " FROM " . $table_prefix . "ads_items ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$is_paid = $db->f("is_paid");
			if (!$is_paid) {
				$publish_price = $db->f("publish_price");
				// check user credit balance
				$sql = " SELECT credit_balance FROM " . $table_prefix . "users WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$credit_balance = get_db_value($sql);
				if ($credit_balance >= $publish_price) {
					VA_Ads::subtract_credits($user_id, $publish_price);
					$sql  = " UPDATE " . $table_prefix . "ads_items ";
					$sql .= " SET is_paid=1, publish_price=0 ";
					$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
					$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
					$db->query($sql);
				} else if ($funds_item_id) {
					$credit_amount = abs($credit_balance - $publish_price);
					VA_Ads::add_funds($funds_item_id, $credit_amount);
				} else {
					$errors = str_replace("{more_credits}", currency_format($publish_price - $credit_balance), AD_CREDITS_BALANCE_ERROR);
				}
			}
		}
	}
	
	$html_template = get_setting_value($block, "html_template", "block_user_ads.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_ads_href",  get_custom_friendly_url("user_ads.php"));
	$t->set_var("user_ad_href",   get_custom_friendly_url("user_ad.php"));
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
	if ($errors) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	} 

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", get_custom_friendly_url("user_ads.php"));
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "i.item_id");
	$s->set_sorter(TITLE_MSG, "sorter_title", "2", "i.item_title");
	$s->set_sorter(PRICE_MSG, "sorter_price", "3", "i.price");
	$s->set_sorter(STATUS_MSG, "sorter_status", "4", "i.is_approved");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("user_ads.php"));

	// set up variables for navigator
	$sql  = " SELECT i.item_id FROM (((";
	$sql .= $table_prefix . "ads_items i";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_assigned ac ON ac.item_id = i.item_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_categories c ON ac.category_id = c.category_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_categories_sites s ON s.category_id=c.category_id)";
	$sql .= " WHERE i.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	$sql .= " GROUP BY i.item_id ";
  $count_sql = "SELECT COUNT(*) FROM (".$sql.") count_sql";
	$total_records = get_db_value($count_sql);
	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT i.item_id, i.item_title, i.price, i.publish_price, i.is_paid, i.is_approved, i.date_start, i.date_end, ";
	$sql .= " i.currency_code, c.exchange_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	$sql .= " FROM ((((";
	$sql .= $table_prefix . "ads_items i";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_assigned aa ON aa.item_id=i.item_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_categories ac ON aa.category_id=ac.category_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON c.currency_code=i.currency_code)";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_categories_sites s ON s.category_id=ac.category_id)";
	$sql .= " WHERE i.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= " AND (ac.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	if ($db_type != "mysql") {
		$sql .= " GROUP BY i.item_id, i.item_title, i.price, i.publish_price, i.is_paid, i.is_approved, i.date_start, i.date_end,  ";
		$sql .= " i.currency_code, c.exchange_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	} else {
		$sql .= " GROUP BY i.item_id, i.item_title, i.price, i.is_approved, i.date_start, i.date_end  ";
	}
	$db->query($sql . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");

		$ad_pay_url = new VA_URL("user_ads.php");
		$ad_pay_url->add_parameter("operation", CONSTANT, "pay");
		$ad_pay_url->add_parameter("item_id", DB, "item_id");
		$ad_pay_url->add_parameter("ad_rnd", CONSTANT, time());

		do
		{
			$item_id = $db->f("item_id");
			$item_title = get_translation($db->f("item_title"));
			$price = $db->f("price");

			// get ad currency
			$ad_currency = array();
			$ad_currency_code = $db->f("currency_code");
			$ad_currency["code"] = $db->f("currency_code");
			$ad_currency["rate"] = 1;
			$ad_currency["left"] = $db->f("symbol_left");
			$ad_currency["right"] = $db->f("symbol_right");
			$ad_currency["decimals"] = $db->f("decimals_number");
			$ad_currency["point"] = $db->f("decimal_point");
			$ad_currency["separator"] = $db->f("thousands_separator");
			if (!strlen($ad_currency_code)) {
				// use default currency in case currency wasn't selected for this ad
				$ad_currency = $currency;
			}

			$t->set_var("item_id", $item_id);
			$t->set_var("item_title", $item_title);
			$t->set_var("price", currency_format($price, $ad_currency));

			$publish_price = $db->f("publish_price");
			$is_paid = $db->f("is_paid");
			$is_approved = $db->f("is_approved");
			$date_start = $db->f("date_start", DATETIME);
			$date_end = $db->f("date_end", DATETIME);
			$date_start_ts = mktime(0,0,0, $date_start[MONTH], $date_start[DAY], $date_start[YEAR]);
			$date_end_ts = mktime(0,0,0, $date_end[MONTH], $date_end[DAY], $date_end[YEAR]);
			$date_now_ts = va_timestamp();

			if ($is_paid != 1) {
				$status = "<font color=\"red\">".NOT_PAID_MSG."</font>";
			} else if ($is_approved != 1) {
				$status = "<font color=\"red\">".NOT_APPROVED_MSG."</font>";
			} else if ($date_now_ts >= $date_start_ts && $date_now_ts < $date_end_ts) {
				$status = "<font color=\"blue\">".AD_RUNNING_MSG."</font>";
			} else if ($date_start_ts == $date_end_ts) {
				$status = "<font color=\"silver\">".AD_CLOSED_MSG."</font>";
			} else if ($date_now_ts >= $date_end_ts) {
				$status = "<font color=\"silver\">".EXPIRED_MSG."</font>";
			}	else if ($date_now_ts < $date_start_ts) {
				$status = AD_NOT_STARTED_MSG;
			}
			$t->set_var("status", $status);
			if ($publish_price != 0) {
				$t->set_var("publish_price", currency_format($publish_price));
				$t->set_var("ad_pay_url", $ad_pay_url->get_url());
				$t->parse("pay_link", false);
			} else {
				$t->set_var("publish_price", "");
				$t->set_var("pay_link", "");
			}

			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$type_index = 0;
	$sql  = " SELECT type_id, type_name ";
	$sql .= " FROM " . $table_prefix . "ads_types ";
	$db->query($sql);
	while ($db->next_record()) {
		$type_index++;
		$type_id = $db->f("type_id");
		$type_name = get_translation($db->f("type_name"));
		$delimiter = ($type_index > 1) ? " | " : "";

		$t->set_var("type_id", $type_id);
		$t->set_var("type_name", $type_name);
		$t->set_var("delimiter", $delimiter);

		$t->parse("ads_types", true);
	}

	$block_parsed = true;

?>