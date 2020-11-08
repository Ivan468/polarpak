<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_coupons.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	check_admin_security("coupons");

	// prepate URL
	$coupon_url = new VA_URL("admin_coupon.php");
	$coupon_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$coupon_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$coupon_url->add_parameter("s_a", REQUEST, "s_a");
	$coupon_url->add_parameter("s_dt", REQUEST, "s_dt");
	$coupon_url->add_parameter("s_n", REQUEST, "s_n");
	$coupon_url->add_parameter("page", REQUEST, "page");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_coupons.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_coupon_href", "admin_coupon.php");
	$t->set_var("admin_coupons_href", "admin_coupons.php");
	$t->set_var("admin_coupon_multiply_href", "admin_coupon_multiply.php");

	$s = get_param("s");
	$r = new VA_Record($table_prefix . "coupons");
	$search_values = 
		array( 
			array("", ALL_MSG), array(1, ACTIVE_MSG), array(2, INACTIVE_MSG), array(3, USED_MSG), array(4, EXPIRED_MSG), array(5, UPCOMING_MSG)
		);
	$discount_types = 
		array( 
			array("", ""), array(1, ORDER_COUPON_MSG), array(3, PRODUCT_COUPON_MSG), array(5, GIFT_VOUCHER_MSG), array(6, SALES_DISCOUNT_MSG), array(8, USER_VOUCHER_MSG),
		);

	// 1 - order percentage discount
	// 2 - order fixed amount discount
	// 3 - product percentage discount
	// 4 - product fixed amout discount
	// 5 - gift voucher
	// 6 - product percentage sales discount
	// 7 - product fixed amout sales discount
	// 8 - personal user voucher


	$r->add_textbox("s_n", TEXT);
	$r->add_radio("s_a", INTEGER, $search_values);
	$r->add_radio("s_dt", INTEGER, $discount_types);
	$r->get_form_values();

	$r->set_parameters();
	$where = "";
	if (!$r->is_empty("s_n")) {
		if ($where) { $where .= " AND "; }
		$where .= " (coupon_title LIKE '" . $db->tosql($r->get_value("s_n"), TEXT, false) . "%' ";
		$where .= " OR coupon_code LIKE '" . $db->tosql($r->get_value("s_n"), TEXT, false) . "%') ";
	}
	if (!$r->is_empty("s_a")) {
		$s_a = $r->get_value("s_a");
		$current_time = va_time();
		if ($s_a >= 1 && $s_a <= 5) {
			if ($where) { $where .= " AND "; }
			if ($s_a == 1) {
				$where .= " is_active=1 ";
				$where .= " AND (quantity_limit IS NULL OR quantity_limit=0 OR coupon_uses<quantity_limit) ";
				$where .= " AND NOT (discount_type=5 AND discount_amount=0) ";
				$where .= " AND (expiry_date IS NULL OR expiry_date>=" . $db->tosql($current_time, DATE) . ")";
			} else if ($s_a == 2) {
				$where .= " is_active=0 ";
			} else if ($s_a == 3) {
				$where .= " is_active=1 AND ((quantity_limit>0 AND coupon_uses>=quantity_limit) OR (discount_type=5 AND discount_amount=0)) ";
			} else if ($s_a == 4) {
				$where .= " is_active=1 ";
				$where .= " AND (expiry_date IS NOT NULL AND expiry_date<" . $db->tosql($current_time, DATE) . ")";
				$where .= " AND NOT (coupon_uses>0 AND coupon_uses>=quantity_limit) ";
			} else if ($s_a == 5) {
				$where .= " is_active=1 ";
				$where .= " AND (start_date IS NOT NULL AND start_date>" . $db->tosql($current_time, DATE) . ")";
				$where .= " AND NOT (coupon_uses>0 AND coupon_uses>=quantity_limit) ";
			}
		}
	}
	if (!$r->is_empty("s_dt")) {
		$s_dt = $r->get_value("s_dt");
		if ($s_dt >= 1 && $s_dt <= 8) {
			if ($where) { $where .= " AND "; }
			if ($s_dt == 1 || $s_dt == 2) {
				$where .= " (discount_type=1 OR discount_type=2) ";
			} else if ($s_dt == 3 || $s_dt == 4) {
				$where .= " (discount_type=3 OR discount_type=4) ";
			} else if ($s_dt == 5) {
				$where .= " (discount_type=5) ";
			} else if ($s_dt == 6 || $s_dt == 7) {
				$where .= " (discount_type=6 OR discount_type=7) ";
			} else if ($s_dt == 8) {
				$where .= " (discount_type=8) ";
			}
		}
	}
	if ($where) {
		$where = " WHERE " . $where;
	}

	$t->set_var("s_n_url", urlencode($r->get_value("s_n")));
	$t->set_var("s_a_url", urlencode($r->get_value("s_a")));
	$t->set_var("s_dt_url", urlencode($r->get_value("s_dt")));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_coupons.php");
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ID_MSG, "sorter_coupon_id", "1", "coupon_id");
	$s->set_sorter(COUPON_TITLE_MSG, "sorter_coupon_title", "2", "coupon_title");
	$s->set_sorter(CODE_MSG, "sorter_coupon_code", "3", "coupon_code");
	$s->set_sorter(EXPIRY_DATE_MSG, "sorter_expiry_date", "4", "expiry_date");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_coupons.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "coupons " . $where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "coupons " . $where . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$coupon_id = $db->f("coupon_id");

			$coupon_title = htmlspecialchars($db->f("coupon_title"));
			$coupon_code = htmlspecialchars($db->f("coupon_code"));

			if (!$r->is_empty("s_n")) {
				$s_n = $r->get_value("s_n");
				$coupon_title = preg_replace ("/(" . $s_n . ")/i", "<font color=blue><b>\\1</b></font>", $coupon_title);					
				$coupon_code = preg_replace ("/(" . $s_n . ")/i", "<font color=blue><b>\\1</b></font>", $coupon_code);					
			}

			$t->set_var("coupon_id", $coupon_id);
			$t->set_var("coupon_title", $coupon_title);
			$t->set_var("coupon_code", $coupon_code);

			$expiry_date = "";
			$is_expired = false;
			$expiry_date_db = $db->f("expiry_date", DATETIME);
			if(is_array($expiry_date_db)) {
				$expiry_date = va_date($date_show_format, $expiry_date_db);
				$expiry_date_ts = mktime (0,0,0, $expiry_date_db[MONTH], $expiry_date_db[DAY], $expiry_date_db[YEAR]);
				$current_date_ts = va_timestamp();
				if($current_date_ts > $expiry_date_ts) {
					$is_expired = true;
				}
			} 
			$t->set_var("expiry_date", $expiry_date);

			$start_date = "";
			$is_upcoming = false;
			$start_date_db = $db->f("start_date", DATETIME);
			if(is_array($start_date_db)) {
				$start_date = va_date($date_show_format, $start_date_db);
				$start_date_ts = mktime (0,0,0, $start_date_db[MONTH], $start_date_db[DAY], $start_date_db[YEAR]);
				$current_date_ts = va_timestamp();
				if($current_date_ts < $start_date_ts) {
					$is_upcoming = true;
				}
			} 
			$t->set_var("start_date", $start_date);

			$is_active = $db->f("is_active");
			$quantity_limit = $db->f("quantity_limit");
			$coupon_uses = $db->f("coupon_uses");
			$discount_type = $db->f("discount_type");
			$discount_amount = $db->f("discount_amount");

			if (!$is_active) {
				$coupon_status = "<font color=silver>" . INACTIVE_MSG . "</font>";
			} else if (($quantity_limit > 0 && $coupon_uses >= $quantity_limit) || ($discount_type == 5 && $discount_amount == 0)) {
				$coupon_status = "<font color=green>" . USED_MSG . "</font>";
			} else if ($is_expired) {
				$coupon_status = "<font color=red>" . EXPIRED_MSG . "</font>";
			} else if ($is_upcoming) {
				$coupon_status = "<font color=red>" . UPCOMING_MSG . "</font>";
			} else {
				$coupon_status = "<font color=blue>" . ACTIVE_MSG . "</font>";
			}
			$t->set_var("coupon_status", $coupon_status);

			$coupon_url->add_parameter("coupon_id", CONSTANT, $coupon_id);
			$t->set_var("coupon_edit_url", $coupon_url->get_url("admin_coupon.php"));
			$t->set_var("coupon_multiply_url", $coupon_url->get_url("admin_coupon_multiply.php"));
			if ($discount_type == 8) {
				$t->set_var("coupon_transfer_url", $coupon_url->get_url("admin_coupon_transfer.php"));
				$t->parse("transfer_link", false);
			} else {
				$t->set_var("transfer_link", "");
			}


			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");

