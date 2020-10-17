<?php

	$default_title = "{top_category_name} &nbsp; {RECENTLY_VIEWED_TITLE}";

	$recent_records = get_setting_value($vars, "ads_recent_recs", 5);
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_ads_recently_viewed.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("compare_href",          "ads_compare.php");
	$t->set_var("compare_name",          "ads_recent");
	$t->set_var("top_category_name",     va_message("ADS_TITLE"));
	$t->set_var("recently_viewed_items", "");

	$recently_viewed = get_session("session_ads_recently_viewed");
	if (is_array($recently_viewed)) {
		$recent_number = 0;
		foreach ($recently_viewed as $key => $recent_info) {
			$recent_number++;
			if ($recent_number > $recent_records) {
				break;
			}
			list($item_id, $item_name, $friendly_url, $recent_price, $is_compared, $ad_currency) = $recently_viewed[$key];

			$t->set_var("item_id", $item_id);
			$t->set_var("top_position", $recent_number);
			$t->set_var("top_name", get_translation($item_name));
			if ($friendly_urls && $friendly_url) {
				$t->set_var("details_href", $friendly_url . $friendly_extension);
			} else {
				$t->set_var("details_href", "ads_details.php?item_id=" . urlencode($item_id));
			}
			$t->set_var("top_value", currency_format($recent_price, $ad_currency));
			$t->sparse("top_value_block", false);

			if ($is_compared) {
				$t->parse("recent_compare", false);
			} else {
				$t->set_var("recent_compare", "");
			}

			$t->parse("recently_viewed_items", true);
		}
		$block_parsed = true;
	}
