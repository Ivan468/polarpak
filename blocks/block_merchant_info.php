<?php

	$default_title = "{MERCHANT_MSG}";

	if(!isset($merchant_id)) {
		$merchant_id = get_param("merchant_id");
	}
	if(!strlen($merchant_id)) {
		if ($cms_page_code == "product_details") {
			$item_id = get_param("item_id");
			$sql  = " SELECT user_id FROM " . $table_prefix . "items ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$merchant_id = get_db_value($sql);
		}
	}

	$desc_image = get_setting_value($vars, "merchant_info_image", 1);
	$desc_type = get_setting_value($vars, "merchant_info_desc", 2);
	$is_nickname = get_setting_value($vars, "merchant_info_nick", 1);
	$is_country_flag = get_setting_value($vars, "merchant_info_country_flag", 1);
	$is_online_status = get_setting_value($vars, "merchant_info_online", 1);
	$is_member_since = get_setting_value($vars, "merchant_info_member", 1);
	$is_prod_link = get_setting_value($vars, "merchant_info_prod_link", 1);
	// check online time
	$ctime = va_time();
	$online_time = get_setting_value($settings, "online_time", 5);
	$online_ts = mktime ($ctime[HOUR], $ctime[MINUTE] - $online_time, $ctime[SECOND], $ctime[MONTH], $ctime[DAY], $ctime[YEAR]);

	if (!isset($merchant_info) || !is_array($merchant_info) || !sizeof($merchant_info)) {
		$merchant_info = array();
		$sql  = " SELECT * FROM ". $table_prefix . "users "; 
		$sql .= " WHERE user_id=" . $db->tosql($merchant_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$merchant_info = $db->Record;
			$merchant_info["registration_date"] = $db->f("registration_date", DATETIME);
			// check last visit date
			$last_visit_ts = 0;
			$last_visit_date = $db->f("last_visit_date", DATETIME);
			if (is_array($last_visit_date)) {
				$last_visit_ts = va_timestamp($last_visit_date);
			}
			$merchant_info["last_visit_ts"] = $last_visit_ts;
		}
	}


	if (sizeof($merchant_info)) {

	  $t->set_file("block_body", "block_merchant_info.html");
		// check name
		$merchant_name = $merchant_info["nickname"];
		if (!strlen($merchant_name)) {
			$merchant_name = get_translation($merchant_info["company_name"]);
		}
		if (!strlen($merchant_name)) {
			$merchant_name = $merchant_info["name"];
		}
		if (!strlen($merchant_name)) {
			$merchant_name = trim($merchant_info["first_name"]." ".$merchant_info["last_name"]);
		}
		if (!strlen($merchant_name)) {
			$merchant_name = $merchant_info["login"];
		}
	
		// parse personal image
		$personal_image = $merchant_info["personal_image"];
		if ($desc_image && $personal_image) {
			if (preg_match("/^http\:\/\//", $personal_image)) {
				$image_size = "";
			} else {
				$image_size = @GetImageSize($personal_image);
			}
			if (is_array($image_size)) {
				$t->set_var("image_size", $image_size[3]);
			} else {
				$t->set_var("image_size", "");
			}
			$t->set_var("alt", htmlspecialchars($merchant_name));
			$t->set_var("personal_image_src", htmlspecialchars($personal_image));

			$t->sparse("personal_image_block", false);
		} else {
			$t->set_var("personal_image_block", "");
		}

		$merchant_description = "";
		if ($desc_type == 1) {
			$merchant_description = $merchant_info["short_description"];
		} else if ($desc_type == 2) {
			$merchant_description = $merchant_info["full_description"];
		}
		if ($merchant_description) {
			$t->set_var("merchant_description", $merchant_description);
			$t->sparse("merchant_description_block", false);
		} else {
			$t->set_var("merchant_description_block", "");
		}

		$t->set_var("country_block", "");
		$t->set_var("nickname_country_block", "");
		if ($is_country_flag) {
			$country_code = $merchant_info["country_code"];
			$country_image_src = "images/flags/".strtolower($country_code).".gif";
			if (file_exists($country_image_src)) {
				$image_size = @GetImageSize($country_image_src);
				if (is_array($image_size)) {
					$t->set_var("image_size", $image_size[3]);
				} else {
					$t->set_var("image_size", "");
				}
				$t->set_var("alt", htmlspecialchars($country_code));
				$t->set_var("country_image_src", $country_image_src);
				if ($is_nickname) {
					$t->sparse("nickname_country_block", false);
				} else {
					$t->sparse("country_block", false);
				}
			} 
		}

		if ($is_nickname) {
			$t->set_var("nickname", $merchant_name);
			$t->sparse("nickname_block", false);
		} else {
			$t->set_var("nickname_block", "");
		}


		if ($is_member_since) {
			$registration_date = $merchant_info["registration_date"];
			$t->set_var("member_since", va_date($date_show_format, $registration_date));
			$t->sparse("member_since_block", false);
		} else {
			$t->set_var("member_since_block", "");
		}

		if ($is_online_status) {
			$last_visit_ts = $merchant_info["last_visit_ts"];
			if ($last_visit_ts >= $online_ts) {
				$t->set_var("online_status", "<font color=\"green\">".ONLINE_MSG."</font>");
			} else {
				$t->set_var("online_status", "<font color=\"silver\">".OFFLINE_MSG."</font>");
			}
			$t->sparse("online_status_block", false);
		} else {
			$t->set_var("online_status_block", "");
		}
		
		if ($is_prod_link) {
			$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
			$friendly_extension = get_setting_value($settings, "friendly_extension", "");
			$friendly_url = $merchant_info["friendly_url"];
			if ($friendly_urls && strlen($friendly_url)) {
				$merchant_products_url = $friendly_url.$friendly_extension;
			} else {
				$merchant_products_url = "user_list.php?user=".$merchant_id;
			}
			$t->set_var("merchant_products_url", $merchant_products_url);
			$t->sparse("prod_link_block", false);
		} else {
			$t->set_var("prod_link_block", "");
		}

		$block_parsed = true;
	}

?>