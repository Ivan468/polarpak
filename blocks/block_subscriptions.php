<?php

	// set necessary scripts
	set_script_tag("js/shopping.js");
	set_script_tag("js/ajax.js");
	set_script_tag("js/blocks.js");

	$default_title = "{SUBSCRIPTIONS_MSG}";

	$subscriptions_page = get_custom_friendly_url("subscriptions.php");
	$html_template = get_setting_value($block, "html_template", "block_subscriptions.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("subscriptions_href", $subscriptions_page);
	$t->set_var("rp", htmlspecialchars($subscriptions_page));
	$t->set_var("rp_url", urlencode($subscriptions_page));
	$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
	$t->set_var("sc_params", htmlspecialchars(json_encode($sc_params)));

	$confirm_add = get_setting_value($settings, "confirm_add", 1);
	$t->set_var("confirm_add", $confirm_add);

	$sql  = " SELECT s.*,sg.group_name ";
	$sql .= " FROM (" . $table_prefix . "subscriptions s ";
	$sql .= " LEFT JOIN " . $table_prefix . "subscriptions_groups sg ON s.group_id=sg.group_id)";
	$sql .= " WHERE (sg.is_active=1 OR sg.is_active IS NULL) ";
	$sql .= " AND (s.is_active=1 OR s.is_active IS NULL) ";
	$sql .= " AND (s.user_type_id IS NULL OR s.user_type_id=0) ";
	$sql .= " ORDER BY sg.group_id DESC, s.subscription_fee ";
	$db->query($sql);
	if ($db->next_record()) {
		$last_group_id = $db->f("group_id");
		$last_group_name = $db->f("group_name");
		do {
			$group_id = $db->f("group_id");
			$group_name = $db->f("group_name");
			if ($last_group_id && $group_id != $last_group_id) {
				subscription_form($last_group_id, $last_group_name, 0);
			}
			$subscription_id = $db->f("subscription_id");
			$subscription_name = get_translation($db->f("subscription_name"));
			$subscription_fee = $db->f("subscription_fee");
			$subscription_period = $db->f("subscription_period");
			$subscription_interval = $db->f("subscription_interval");
			$subscription_suspend = $db->f("subscription_suspend");
			$subscription_id_checked = ($db->f("is_default") == 1) ? " checked " : "";

			if ($subscription_interval == 1) {
				$subscription_periods = array(1 => DAY_MSG, 2 => WEEK_MSG, 3 => MONTH_MSG, 4 => YEAR_MSG);
				$period_message = "1 " . $subscription_periods[$subscription_period];
			} else {
				$subscription_periods = array(1 => DAYS_QTY_MSG, 2 => WEEKS_QTY_MSG, 3 => MONTHS_QTY_MSG, 4 => YEARS_QTY_MSG);
				$period_message = $subscription_periods[$subscription_period];
				$period_message = str_replace("{quantity}", $subscription_interval, $period_message);
			}
			
			$t->set_var("subscription_id_value", $subscription_id);
			$t->set_var("subscription_id_checked", $subscription_id_checked);
			$t->set_var("subscription_name", $subscription_name);
			$t->set_var("subscription_fee", currency_format($subscription_fee));
			$t->set_var("subscription_period", $period_message);

			if ($group_id) {
	      $t->parse("subscription_id", true);
			} else {
	      $t->parse("subscription_value", false);
				subscription_form(0, "", $subscription_id);
			}

			$last_group_id = $group_id;
			$last_group_name = $group_name;
		} while ($db->next_record());

		if ($last_group_name) {
			subscription_form($last_group_id, $last_group_name, 0);
		}
	}

	$block_parsed = true;

function subscription_form($group_id, $group_name, $subscription_id)
{
	global $t, $sc_subscription_id, $sc_subscription_name, $sc_group_id, $subscription_added, $subscriptions_page;

	$shopping_cart = get_session("shopping_cart");
	$empty_cart = (!is_array($shopping_cart) || sizeof($shopping_cart) == 0);
	$shop_hide_view_list = 0;
	$shop_hide_checkout_list = 0;

	$form_id = ($group_id) ? $group_id : $subscription_id;
	$t->set_var("form_id", $form_id);
	if ($group_id) {
		$t->set_var("group_id", $group_id);
		$t->set_var("group_name", $group_name);
		$t->parse("subscription_group", false);
		$t->set_var("buy_href", "javascript:document.form_" . $form_id . ".submit();");
	} else {
		$t->set_var("group_id", "");
		$t->set_var("group_name", "&nbsp;");
		$t->parse("subscription_group", false);
		$t->set_var("buy_href", "subscriptions.php?cart=SUBSCRIPTION&subscription_id=" . $subscription_id . "&rp=". urlencode($subscriptions_page). "#f" . $form_id);
	}

	$t->set_var("subscription_added", "");
	$t->set_var("sc_errors", "");
	if (($sc_subscription_id && $subscription_id == $sc_subscription_id) || ($sc_group_id && $group_id == $sc_group_id)) {
		if ($subscription_added) {
			$added_message = str_replace("{subscription_name}", $sc_subscription_name, ADDED_SUBSCRIPTION_MSG);
			$t->set_var("added_message", $added_message);
			$t->parse("subscription_added", false);
		} else {
			$t->set_var("errors_list", SELECT_SUBSCRIPTION_MSG);
			$t->parse("sc_errors", false);
		}
	}

	if ($t->block_exists("cart_add_button")) {
		$t->sparse("cart_add_button", false);
	} else {
		$t->sparse("add_button", false);
	}
	if ($shop_hide_view_list || $empty_cart) {
		$t->set_var("view_button", "");
	} else {
		$t->sparse("view_button", false);
	}
	if ($shop_hide_checkout_list || $empty_cart) {
		$t->set_var("checkout_button", "");
	} else {
		$t->sparse("checkout_button", false);
	}

	$t->parse("subscriptions_items", true);
	// clear parsed values
	$t->set_var("subscription_id", "");
	$t->set_var("subscription_value", "");
}

?>