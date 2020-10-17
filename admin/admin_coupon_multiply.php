<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_coupon_multiply.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	$operation   = get_param("operation");
	$coupon_id   = get_param("coupon_id");
	
	check_admin_security("coupons");

	$discount_types = array(
		"1" => PERCENTAGE_PER_ORDER_MSG,
		"2" => AMOUNT_PER_ORDER_MSG,
		"3" => PERCENTAGE_PER_PRODUCT_MSG, 
		"4" => AMOUNT_PER_PRODUCT_MSG,
	);
	
	$s = get_param("s");
	$s_a = get_param("s_a");
	$coupon_id = get_param("coupon_id");
	$discount_type = get_param("discount_type");
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_coupon_multiply.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("date_format_msg", $date_format_msg);
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("admin_coupon_multiply_href", "admin_coupon_multiply.php");

	// multiply record
	$new_code_types = 
		array( 
			array(1, YES_MSG), array(0, NO_MSG)
		);

	$mr = new VA_Record($table_prefix . "coupons");
	$mr->add_textbox("multiply_times", INTEGER, TIMES_TO_MULTIPLY_MSG); 
	$mr->change_property("multiply_times", REQUIRED, true);
	$mr->change_property("multiply_times", MIN_VALUE, 1);
	$mr->change_property("multiply_times", MAX_VALUE, 10000);
	$mr->add_textbox("new_coupon_code_mask", TEXT, NEW_COUPON_CODE_MASK_MSG); 
	$mr->change_property("new_coupon_code_mask", REQUIRED, true);
	$mr->change_property("new_coupon_code_mask", DEFAULT_VALUE, "********");
	$mr->return_page  = "admin_coupons.php?s=" . $s . "&s_a=" . $s_a;

	$r = new VA_Record($table_prefix . "coupons");
	$r->return_page  = "admin_coupons.php?s=" . $s . "&s_a=" . $s_a;

	$r->add_where("coupon_id", INTEGER);
	$r->add_textbox("order_id", INTEGER);
	$r->add_textbox("order_item_id", INTEGER, "");

	$r->add_textbox("is_active", INTEGER);
	$r->add_textbox("is_exclusive", NUMBER);
	$r->add_textbox("is_auto_apply", INTEGER);
	$r->add_textbox("apply_order", INTEGER); 
	$r->add_textbox("sites_all", INTEGER);

	$r->add_textbox("coupon_code", TEXT, COUPON_CODE_MSG);
	$r->change_property("coupon_code", REQUIRED, true);
	$r->change_property("coupon_code", UNIQUE, true);
	$r->change_property("coupon_code", TRIM, true);
	$r->change_property("coupon_code", MIN_LENGTH, 3);
	$r->change_property("coupon_code", MAX_LENGTH, 64);
	$r->add_textbox("coupon_title", TEXT);
	$r->add_textbox("discount_type", INTEGER);

	$r->add_textbox("discount_quantity", INTEGER, DISCOUNT_MULTIPLE_MSG); // new
	$r->add_textbox("discount_amount", NUMBER, DISCOUNT_AMOUNT_MSG);

	$r->add_textbox("free_postage", NUMBER);
	$r->add_textbox("free_postage_all", INTEGER);
	$r->add_textbox("free_postage_ids", TEXT);
	$r->add_textbox("coupon_tax_free", NUMBER);
	$r->add_textbox("order_tax_free", NUMBER);

	$r->add_textbox("start_date", DATETIME);
	$r->add_textbox("expiry_date", DATETIME);

	$r->add_textbox("users_use_limit", INTEGER);
	$r->add_textbox("quantity_limit", INTEGER);
	$r->add_textbox("coupon_uses", INTEGER);

	$r->add_textbox("min_quantity", NUMBER); 
	$r->add_textbox("max_quantity", NUMBER); 
	$r->add_textbox("minimum_amount", NUMBER);
	$r->add_textbox("maximum_amount", NUMBER); 

	// orders fields
	$r->add_textbox("orders_period", INTEGER);
	$r->add_textbox("orders_interval", INTEGER);
	$r->add_textbox("orders_min_goods", NUMBER);
	$r->add_textbox("orders_max_goods", NUMBER);

	// override order restrictions
	$r->add_textbox("order_min_goods_cost", FLOAT, ORDER_MIN_PRODUCTS_COST_FIELD);
	$r->add_textbox("order_max_goods_cost", FLOAT, ORDER_MAX_PRODUCTS_COST_FIELD);
	$r->add_textbox("order_min_weight", FLOAT, ORDER_MIN_WEIGHT_FIELD);
	$r->add_textbox("order_max_weight", FLOAT, ORDER_MAX_WEIGHT_FIELD);

	// products fields
	$r->add_textbox("items_all", INTEGER);
	$r->add_textbox("items_ids", TEXT);
	$r->add_textbox("items_types_ids", TEXT);

	// cart products fields
	$r->add_textbox("cart_items_all", INTEGER); 
	$r->add_textbox("cart_items_ids", TEXT); 
	$r->add_textbox("cart_items_types_ids", TEXT);

	// cart restrictions
	$r->add_textbox("min_cart_quantity", NUMBER, MIN_CART_QTY_MSG); 
	$r->add_textbox("max_cart_quantity", NUMBER, MAX_CART_QTY_MSG); 
	$r->add_textbox("min_cart_cost", NUMBER, MIN_CART_COST_MSG);
	$r->add_textbox("max_cart_cost", NUMBER, MAX_CART_COST_MSG); 

	// user fields
	$r->add_textbox("users_all", INTEGER);
	$r->add_textbox("users_ids", TEXT);
	$r->add_textbox("users_types_ids", TEXT);

	// friends fields
	$r->add_textbox("friends_discount_type", INTEGER);
	$r->add_textbox("friends_period", INTEGER);
	$r->add_textbox("friends_interval", INTEGER);
	$r->add_textbox("friends_min_goods", NUMBER);
	$r->add_textbox("friends_max_goods", NUMBER);
	$r->add_textbox("friends_all", INTEGER);
	$r->add_textbox("friends_ids", TEXT);

	// editing information
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->change_property("admin_id_modified_by", USE_IN_INSERT, false);
	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);
	$r->change_property("date_modified", USE_IN_INSERT, false);
	
	$mr->events[ON_CUSTOM_OPERATION] = "multiply_coupons";
	$mr->events[BEFORE_PROCESS] = "set_coupon_data";
	$mr->process();

	$t->set_var("s", $s);
	$t->set_var("s_a", $s_a);


	$t->set_var("date_added_format", join("", $date_edit_format));
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_coupons_href", "admin_coupons.php");

	$t->pparse("main");

	function multiply_coupons()
	{
		global $r, $mr, $db, $table_prefix;

		$is_valid = $mr->validate();
		if ($is_valid) {

			$old_coupon_id = $r->get_value("coupon_id");
			// get sites data for selected coupon
			$coupon_sites = array();
			$sql = "SELECT site_id FROM " . $table_prefix . "coupons_sites WHERE coupon_id=" . $db->tosql($old_coupon_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$coupon_sites[] = $db->f("site_id");
			}

			$coupon_code = $r->get_value("coupon_code");
			$mt = $mr->get_value("multiply_times");
			$mask = $mr->get_value("new_coupon_code_mask");
			// replace coupon_code tag with old coupon code
			$mask = str_replace("{coupon_code}", $coupon_code, $mask);
			// get mask parameters for futher generation
			$seq_mask = preg_replace("/[^\#]/", "", $mask);
			$seq_mask_length = strlen($seq_mask);
			$asterisks = preg_replace("/[^\*]/", "", $mask);
			$asterisks_length = strlen($asterisks);
			// increase mask random symbols if necessary
			if ($mt > 1 && !preg_match("/#/", $mask)) {
				$mt_length = strlen($mt);
				if ($mt_length > $asterisks_length) {
					for ($a = 0; $a < ($mt_length - $asterisks_length); $a++) {
						$mask .= "*";
						$asterisks_length++;
					}
				}
			}
			$sequence = 0;
			// generate new coupons
			for ($m = 0; $m < $mt; $m++) {
				// generate new coupon code
				$new_coupon_code = "";
				while ($new_coupon_code == "") {
					$sequence++;
	  
					$new_coupon_code = $mask;
					if ($seq_mask_length > 0) {
						// add sequence to new coupon code
						$sequence_string = strval($sequence);
						$sequence_length = strlen($sequence_string);
						for ($ch = 0; $ch < ($seq_mask_length - $sequence_length); $ch++) {
							$sequence_string = "0".$sequence_string;
						}
						for ($ch = 0; $ch < $seq_mask_length - 1; $ch++) {
							$new_coupon_code = preg_replace("/\#/", $sequence_string[0], $new_coupon_code, 1);
							$sequence_string = substr($sequence_string, 1);
						}
						$new_coupon_code = preg_replace("/\#/", $sequence_string, $new_coupon_code);
					}
					if ($asterisks_length > 0) {
						// add random symbols to new coupon code
						$random_string = "";
						while (strlen($random_string) < $asterisks_length) {
							$random_value = mt_rand();
							$random_hash  = strtoupper(md5($sequence . $random_value . va_timestamp()));
							$random_string .= $random_hash;
						}
						for ($ch = 0; $ch < $asterisks_length; $ch++) {
							$new_coupon_code = preg_replace("/\*/", $random_string[$ch], $new_coupon_code, 1);
						}
					}
					$sql = " SELECT coupon_id FROM " .$table_prefix. "coupons WHERE coupon_code=" . $db->tosql($new_coupon_code, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$new_coupon_code = "";
					}
				}
				// set new coupon code data and add it to database
				if ($db->DBType == "postgre") {
					$new_coupon_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "coupons') ");
					$r->change_property("coupon_id", USE_IN_INSERT, true);
					$r->set_value("coupon_id", $new_coupon_id);
				}
				$r->set_value("coupon_code", $new_coupon_code);
				$r->set_value("admin_id_added_by", get_session("session_admin_id"));
				$r->set_value("date_added", va_time());
				$r->set_value("admin_id_modified_by", "");
				$r->set_value("date_modified", "");

				$coupon_added = $r->insert_record();
				if ($coupon_added) {
					if ($db->DBType == "mysql") {
						$new_coupon_id = get_db_value(" SELECT LAST_INSERT_ID() ");
						$r->set_value("coupon_id", $new_coupon_id);
					} elseif ($db->DBType == "access") {
						$new_coupon_id = get_db_value(" SELECT @@IDENTITY ");
						$r->set_value("coupon_id", $new_coupon_id);
					} elseif ($db->DBType == "db2") {
						$new_coupon_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "coupons FROM " . $table_prefix . "coupons");
						$r->set_value("coupon_id", $new_coupon_id);
					}
					// add sites if they available 
					for ($st = 0; $st < sizeof($coupon_sites); $st++) {
						$site_id = $coupon_sites[$st];
						if (strlen($site_id)) {
							$sql  = " INSERT INTO " . $table_prefix . "coupons_sites (coupon_id, site_id) VALUES (";
							$sql .= $db->tosql($new_coupon_id, INTEGER) . ", ";
							$sql .= $db->tosql($site_id, INTEGER) . ") ";
							$db->query($sql);
						}
					}
				}
			}
		} else {
			$mr->redirect = false;
		}
	}

	function set_coupon_data()  
	{
		global $r, $t, $discount_types;
		$r->get_form_parameters();
		$is_coupon = $r->get_db_values();
		if ($is_coupon) {
			$r->set_form_parameters();
			$discount_type = $r->get_value("discount_type");
			if (isset($discount_types[$discount_type])) {
				$t->set_var("discount_type", "(".$discount_types[$discount_type].")");
			} else {
				$t->set_var("discount_type", "");
			}
		} else {
			header ("Location: admin_coupons.php");
			exit;
		}
	}

?>