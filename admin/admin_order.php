<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_recalculate.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	$order_id = intval(get_param("order_id"));
	$operation = get_param("operation");
	$field = get_param("field");
	$currency = get_currency();
	$orders_currency = get_setting_value($settings, "orders_currency", 0);

	$permissions = get_permissions();
	$update_orders = get_setting_value($permissions, "update_orders", 0);
	$remove_orders = get_setting_value($permissions, "remove_orders", 0);
	$add_products = get_setting_value($permissions, "add_order_products", 0);

	// connection to delete items or event attachments
	$dbi = new VA_SQL();
	$dbi->DBType      = $db_type;
	$dbi->DBDatabase  = $db_name;
	$dbi->DBUser      = $db_user;
	$dbi->DBPassword  = $db_password;
	$dbi->DBHost      = $db_host;
	$dbi->DBPort      = $db_port;
	$dbi->DBPersistent= $db_persistent;


	// admin privileges info
	$admin_info = get_session("session_admin_info");
	$privilege_id = get_session("session_admin_privilege_id");
	$access_all_user_types = get_setting_value($admin_info, "user_types_all", 0); 
	$access_unreg_users = get_setting_value($admin_info, "non_logged_users", 0); 
	$access_user_types = get_setting_value($admin_info, "user_types_ids", ""); 
	$orders_currency = get_setting_value($settings, "orders_currency", 0);

	// check order site 
	$order_site_id = "";
	$sql = " SELECT o.site_id ";
	$sql.= " FROM " . $table_prefix . "orders o ";
	$sql.= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	if ($db->next_record()) {
		$order_site_id = $db->f("site_id");
	}

	// check statuses and their access levels
	$see_statuses = array();
	$see_statuses_ids = array();
	$set_statuses = array(array("", va_message("SELECT_ORDER_STATUS_MSG")));
	$set_statuses_ids = array();
	$update_statuses_ids = array();
	$sql = " SELECT os.*,oss.site_id ";
	$sql.= " FROM (" . $table_prefix . "order_statuses os ";
	$sql.= " LEFT JOIN " . $table_prefix . "order_statuses_sites oss ON os.status_id=oss.status_id) ";
	$sql.= " ORDER BY os.status_order, os.status_id";
	$db->query($sql);
	while ($db->next_record()) {
		$is_active = $db->f("is_active");
		$status_id = $db->f("status_id");
		$status_name = get_translation($db->f("status_name"));
		$status_sites_all = $db->f("sites_all");
		$status_site_id = $db->f("site_id");

		// check access levels
		$view_order_groups_all = $db->f("view_order_groups_all");
		$view_order_groups_ids = explode(",", $db->f("view_order_groups_ids"));
		$set_status_groups_all = $db->f("set_status_groups_all");
		$set_status_groups_ids = explode(",", $db->f("set_status_groups_ids"));
		$update_order_groups_all = $db->f("update_order_groups_all");
		$update_order_groups_ids = explode(",", $db->f("update_order_groups_ids"));
		if (!in_array($status_id, $see_statuses_ids) 
			&& ($view_order_groups_all || in_array($privilege_id, $view_order_groups_ids))
		) {
			$see_statuses[] = array($status_id, $status_name);
			$see_statuses_ids[] = $status_id;
		}
		if (
			!in_array($status_id, $set_statuses_ids) && $is_active 
			&& ($status_sites_all || $status_site_id == $order_site_id)
			&& ($set_status_groups_all || in_array($privilege_id, $set_status_groups_ids))
		) {
			$set_statuses[] = array($status_id, $status_name);
			$set_statuses_ids[] = $status_id;
		}
		if ($update_order_groups_all || in_array($privilege_id, $update_order_groups_ids)) {
			$update_statuses_ids[] = $status_id;
		}
	}

	// build access where accordingly to administrator access levels
	$access_where = ""; 
	if (strlen($access_where)) { $access_where.= " AND "; }
	if (is_array($see_statuses_ids) && sizeof($see_statuses_ids) > 0) {
		$access_where .= " (os.status_id IS NULL OR o.order_status IN (" . $db->tosql($see_statuses_ids, INTEGERS_LIST) . ")) ";
	} else {
		$access_where .= " (os.status_id IS NULL OR o.order_status IS NULL) ";
	}
	if (!$access_all_user_types || !$access_unreg_users) {
		if (strlen($access_where)) { $access_where .= " AND "; }
		$users_where = "";
		if ($access_unreg_users) {
			$users_where .= " o.user_type_id=0 OR o.user_type_id IS NULL ";
		} else if ($access_all_user_types) {            
			$users_where .= " o.user_type_id<>0 AND o.user_type_id IS NOT NULL ";
		}
		if (!$access_all_user_types && strlen($access_user_types)) {
			if ($users_where) { $users_where .= " OR "; }
			$users_where .= " o.user_type_id IN (" . $db->tosql($access_user_types, INTEGERS_LIST) . ")";
		}

		if ($users_where) {
			$access_where .= " (".$users_where.")";
		} else {
			$access_where .= " 1<>1 "; // no users groups selected
		}
	}

	// check order data and if admin can see it
	$sql = " SELECT o.site_id, o.payment_id, o.order_status, os.paid_status, os.status_name, o.user_type_id ";
	$sql.= " FROM (" . $table_prefix . "orders o ";
	$sql.= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql.= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	if ($access_where) { $sql .= " AND " . $access_where; }
	$db->query($sql);
	if ($db->next_record()) {
		$site_id = $db->f("site_id");
		$payment_id = $db->f("payment_id");
		$order_site_id = $db->f("site_id");
		$order_user_type = $db->f("user_type_id");
		$current_status_id = $db->f("order_status");
		$paid_status = $db->f("paid_status");
		$current_status_desc = get_translation($db->f("status_name"));
		if (!strlen($current_status_desc)) {
			$current_status_desc = va_message("ID_MSG").": [".$current_status_id."]";
		}
	} else {
		header("Location: admin_orders.php");
		exit;
	}

	if ( strlen($current_status_id) && !in_array($current_status_id, $update_statuses_ids)) {
		$update_orders = false;
	}

	$set_currency_code = get_param("set_currency_code");
	if ($set_currency_code && !$paid_status && $update_orders) {
		$sql = " SELECT * FROM ".$table_prefix."currencies WHERE currency_code=".$db->tosql($set_currency_code, TEXT);	
		$db->query($sql);
		if ($db->next_record()) {
			$exhange_rate = $db->f("exchange_rate");
			// get new currency but don't update session
			$new_currency = get_currency($set_currency_code, false);
			// check if new currency could be set as payment currency
			$payment_currency = get_payment_rate($payment_id, $new_currency);
			$payment_currency_code = $payment_currency["code"];
			$payment_currency_rate = $payment_currency["rate"];

			$sql  = " UPDATE ".$table_prefix."orders ";
			$sql .= " SET currency_code=".$db->tosql($set_currency_code, TEXT);
			$sql .= " , currency_rate=".$db->tosql($exhange_rate, NUMBER);
			$sql .= " , payment_currency_code=".$db->tosql($payment_currency_code, TEXT);
			$sql .= " , payment_currency_rate=".$db->tosql($payment_currency_rate, NUMBER);
			$sql .= " WHERE order_id=".$db->tosql($order_id, INTEGER);
			$db->query($sql);
		}
	}

	$order_info = array();
	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
	$sql .= " WHERE setting_type='order_info'";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id,INTEGER) . ") ";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_order.html");
	$t->set_var("order_id", htmlspecialchars($order_id));
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_message("ADMIN_ORDER_MSG"), va_message("CONFIRM_DELETE_MSG")));
	$t->set_var("admin_order_attachments_url", "admin_order_attachments.php?order_id=" . urlencode($order_id) . "&attachment_type=2");
	$t->set_var("confirm_cc_number", str_replace("{record_name}", va_message("CC_NUMBER_FIELD"), va_message("CONFIRM_DELETE_MSG")));
	$t->set_var("confirm_cc_code", str_replace("{record_name}", va_message("CC_SECURITY_CODE_FIELD"), va_message("CONFIRM_DELETE_MSG")));
	$t->set_var("confirm_cc_security_code", str_replace("{record_name}", va_message("CC_SECURITY_CODE_FIELD"), va_message("CONFIRM_DELETE_MSG")));
	$t->set_var("admin_order_item_href",    "admin_order_item.php");
	if ($update_orders) {
		$t->sparse("clear_cc_number", false);
		$t->sparse("clear_cc_security_code", false);
	}
	if ($add_products) {
		$t->sparse("add_product_button", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$r = new VA_Record($table_prefix . "orders");
	$r->return_page = $orders_list_site_url . "admin_orders.php";
	$r->add_hidden("s_on", TEXT);
	$r->add_hidden("s_ne", TEXT);
	$r->add_hidden("s_kw", TEXT);
	$r->add_hidden("s_sd", TEXT);
	$r->add_hidden("s_ed", TEXT);
	$r->add_hidden("s_os", TEXT);
	$r->add_hidden("s_cc", TEXT);
	$r->add_hidden("s_sc", TEXT);
	$r->add_hidden("s_ex", TEXT);
	$r->add_hidden("s_pd", TEXT);
	$r->add_hidden("s_ps", TEXT);
	$r->add_hidden("s_cct", TEXT);
	$r->add_hidden("page", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->add_hidden("sort_ord", TEXT);

	$r->add_where("order_id", INTEGER);
	$r->add_select("new_order_status", INTEGER, $set_statuses);
	$r->change_property("new_order_status", USE_IN_SELECT, false);

	$r->get_form_values();

	$operation = get_param("operation");
	$order_id = get_param("order_id");

	$admin_orders_url = $r->get_return_url();
	$return_page = $admin_orders_url;

	$t->set_var("admin_href",               "admin.php");
	$t->set_var("admin_orders_href",        $orders_list_site_url . "admin_orders.php");
	$t->set_var("admin_order_href",         $order_details_site_url . "admin_order.php");
	$t->set_var("admin_order_edit_href",    $order_details_site_url . "admin_order_edit.php");
	$t->set_var("admin_order_links_href",   "admin_order_links.php");
	$t->set_var("admin_order_serial_href",  "admin_order_serial.php");
	$t->set_var("admin_order_serials_href", "admin_order_serials.php");
	$t->set_var("admin_order_vouchers_href","admin_order_vouchers.php");
	$t->set_var("admin_user_href",          "admin_user.php");
	$t->set_var("admin_coupon_href",        "admin_coupon.php");
	$t->set_var("admin_coupons_href",       "admin_coupons.php");
	$t->set_var("admin_order_notes_href",   "admin_order_notes.php");
	$t->set_var("admin_order_email_href",   "admin_order_email.php");
	$t->set_var("admin_order_sms_href",     "admin_order_sms.php");
	$t->set_var("admin_invoice_html_href",  "admin_invoice_html.php");
	$t->set_var("admin_invoice_pdf_href",   "admin_invoice_pdf.php");
	$t->set_var("admin_packing_html_href",  "admin_packing_html.php");
	$t->set_var("admin_packing_pdf_href",   "admin_packing_pdf.php");
	$t->set_var("admin_order_shipping_href","admin_order_shipping.php");
	$t->set_var("admin_orders_url", 		    $admin_orders_url);
	
	if (strlen($operation))
	{
		$redirect = true;
		$new_order_status = $r->get_value("new_order_status");
		if (!strlen($new_order_status)) { $new_order_status = $current_status_id; }
		if ($operation == "delete") {
			if (!isset($permissions["remove_orders"]) || $permissions["remove_orders"] != 1) {
				$r->errors .= va_message("NOT_ALLOWED_REMOVE_ORDERS_INFO_MSG");
			} elseif (!strlen($order_id)) {
				$r->errors .= va_message("MISSING_ORDER_NUMBER_MSG")."<br>";
			} else {
				remove_orders($order_id);
			}
		} elseif ($operation == "save") {
			if (!isset($permissions["update_orders"]) || $permissions["update_orders"] != 1) {
				$r->errors .= va_message("NOT_ALLOWED_UPDATE_ORDERS_INFO_MSG");
			} elseif (!strlen($order_id)) {
				$r->errors .= va_message("MISSING_ORDER_NUMBER_MSG")."<br>";
			} else {

				update_order_status($order_id, $new_order_status, true, "", $status_error);
				$r->set_value("new_order_status", "");
			}
		} elseif ($operation == "update") {
			if (!isset($permissions["update_orders"]) || $permissions["update_orders"] != 1) {
				$r->errors .= va_message("NOT_ALLOWED_UPDATE_ORDERS_INFO_MSG");
			} else {
				$updated_fields = "";

				// update order status
				$order_items = get_param("order_items");
				$status_updated = update_order_status($order_id, $new_order_status, true, $order_items, $status_error);
				$r->set_value("new_order_status", "");
				if ($status_updated) {
					if ($updated_fields) { $updated_fields .= ", "; }
					$updated_fields .= va_message("STATUS_MSG");
				} 

				if (strlen($status_error)) {
					$r->errors .= $status_error . "<br>";
				} else {
					$return_page = "";
					if ($updated_fields) {
						$r->errors  = "<font color=blue><b>";
						$r->errors .= $updated_fields . "</b>";
						$r->errors .= " successfully updated.</font>";
					}
				}
			}
		} elseif ($operation == "add_ip") {
			$ip = get_param("ip");
			if (!strlen($ip)) {
				$r->errors .= va_message("MISSING_IP_ADDRESS_MSG");
			} elseif (!isset($permissions["update_orders"]) || $permissions["update_orders"] != 1) {
				$r->errors .= va_message("NOT_ALLOWED_UPDATE_ORDERS_INFO_MSG");
			} else {
				$sql  = "SELECT ip_address FROM " . $table_prefix . "black_ips WHERE ip_address=" . $db->tosql($ip, TEXT);
				$db->query($sql);
				if (!$db->next_record()) {
					$sql  = " INSERT INTO " . $table_prefix . "black_ips (ip_address, address_action) VALUES (";
					$sql .= $db->tosql($ip, TEXT) . ", 1)";
					$db->query($sql);
				}
				$return_page = "";
			}
		} elseif ($operation == "remove_ip") {
			$ip = get_param("ip");
			if (!strlen($ip)) {
				$r->errors .= va_message("MISSING_IP_ADDRESS_MSG");
			} elseif (!isset($permissions["update_orders"]) || $permissions["update_orders"] != 1) {
				$r->errors .= va_message("NOT_ALLOWED_UPDATE_ORDERS_INFO_MSG");
			} else {
				$sql  = " DELETE FROM " . $table_prefix . "black_ips WHERE ip_address=" . $db->tosql($ip, TEXT);
				$db->query($sql);
				$return_page = "";
			}
		} elseif ($operation == "delete_shipping") {
			$redirect = false;
			$order_shipping_id = get_param("order_shipping_id");
			delete_order_shipping($order_shipping_id);
		} else if ($operation == "clear") {
			$redirect = false;
			if ($field && $order_id && $update_orders) {
				if ($field == "cc_number") {
					$sql = " UPDATE ".$table_prefix."orders SET cc_number=NULL WHERE order_id=".$db->tosql($order_id, INTEGER);
					$db->query($sql);
				} else if ($field == "cc_security_code" || $field == "cc_code") {
					$sql = " UPDATE ".$table_prefix."orders SET cc_security_code=NULL WHERE order_id=".$db->tosql($order_id, INTEGER);
					$db->query($sql);
				}
			}
		}



		if (!strlen($r->errors) && strlen($return_page) && $redirect) {
			header("Location: " . $return_page);
			exit;
		}
	}

	// check order status after update operation
	$sql = " SELECT o.order_status, os.status_name ";
	$sql.= " FROM (" . $table_prefix . "orders o ";
	$sql.= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql.= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$current_status_id = $db->f("order_status");
		$current_status_desc = get_translation($db->f("status_name"));
		if (!strlen($current_status_desc)) {
			$current_status_desc = va_message("ID_MSG").": [".$current_status_id."]";
		}
	}

	// set file once more time to load basket tag properly
	$t->set_file("main", "admin_order.html");
	$items_text = show_order_items($order_id, true, "admin_order");

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_notes oc ";
	$sql .= " WHERE oc.order_id=" . $db->tosql($order_id, INTEGER);
	$total_notes = get_db_value($sql);
	$t->set_var("total_notes", $total_notes);
	if ($total_notes > 0) {
		$t->set_var("notes_style", "font-weight: bold; color: blue;");
	} else {
		$t->set_var("notes_style", "");
	}

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_downloads id ";
	$sql .= " WHERE id.order_id=" . $db->tosql($order_id, INTEGER);
	$total_links = get_db_value($sql);
	$t->set_var("total_links", $total_links);

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_downloads id ";
	$sql .= " WHERE id.order_id=" . $db->tosql($order_id, INTEGER);
	$total_links = get_db_value($sql);
	$t->set_var("total_links", $total_links);

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_items_serials ois ";
	$sql .= " WHERE ois.order_id=" . $db->tosql($order_id, INTEGER);
	$total_serials = get_db_value($sql);
	$t->set_var("total_serials", $total_serials);

	$sql  = " SELECT COUNT(*) FROM (" . $table_prefix . "orders_items oi ";
	$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON oi.item_type_id=it.item_type_id) ";
	$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " AND it.is_gift_voucher=1 ";
	$vouchers_number = get_db_value($sql);
	if ($vouchers_number) {
		$t->parse("vouchers_link", false);
	}

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

	$r->add_textbox("parent_order_id", INTEGER);
	$r->add_textbox("invoice_number", TEXT);
	$r->add_textbox("order_placed_date", DATETIME);
	$r->change_property("order_placed_date", VALUE_MASK, $datetime_show_format);
	$r->add_textbox("user_id", INTEGER);
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->add_textbox("payment_id", INTEGER);
	$r->add_textbox("transaction_id", TEXT);
	$r->add_textbox("affiliate_code", TEXT);
	$r->add_textbox("error_message", TEXT);
	$r->add_textbox("pending_message", TEXT);
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

	$r->get_db_values();

	$sql  = " SELECT payment_name ";
	$sql .= " FROM " . $table_prefix . "payment_systems ";
	$sql .= " WHERE payment_id=" . $db->tosql($r->get_value("payment_id"), INTEGER);
	$payment_name = get_db_value($sql);
	$payment_name = get_translation($payment_name);
	$t->set_var("payment_name", $payment_name);
	if (!$r->get_value("parent_order_id")) {
		$r->parameters["parent_order_id"][SHOW] = false;
	}
	if ($r->get_value("invoice_number") == $r->get_value("order_id") || $r->is_empty("invoice_number")) {
		$r->parameters["invoice_number"][SHOW] = false;
	}
	if ($r->is_empty("transaction_id")) {
		$r->parameters["transaction_id"][SHOW] = false;
	}
	if ($r->is_empty("shipping_tracking_id")) {
		$r->parameters["shipping_tracking_id"][SHOW] = false;
	}

	$cc_info = array();
	$setting_type = "credit_card_info_" . $r->get_value("payment_id");
	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id,INTEGER) . ") ";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$cc_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	$cc_number_security = get_setting_value($cc_info, "cc_number_security", 0);
	$cc_code_security = get_setting_value($cc_info, "cc_code_security", 0);
	if ($cc_number_security > 0) {
		$cc_number = $r->get_value("cc_number");
		if (!preg_match("/^[\d\s\*\-]+$/", $cc_number)) {
			$cc_number = va_decrypt($cc_number);
		}
		$r->set_value("cc_number", format_cc_number($cc_number));
	}
	if ($cc_code_security > 0) {
		$r->set_value("cc_security_code", va_decrypt($r->get_value("cc_security_code")));
	}

	$user_id = $r->get_value("user_id");
	$user_email = $r->get_value("email");
	$sql  = " SELECT COUNT(*) AS status_orders, SUM(goods_total) AS status_goods, o.order_status, os.status_name ";
	$sql .= " FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id)";
	$sql .= " WHERE order_id<>" . $db->tosql($order_id, INTEGER);
	if ($user_id > 0) {
		$sql .= " AND (user_id=" . $db->tosql($user_id, INTEGER) . " OR email=" . $db->tosql($user_email, TEXT) . ") ";
	} else {
		$sql .= " AND email=" . $db->tosql($user_email, TEXT);
	}
	if ($access_where)	 { $sql .= " AND " . $access_where; } 
	$sql .= " GROUP BY o.order_status, os.status_name ";
	$db->query($sql);
	if ($db->next_record()) {
		$total_orders = 0; $total_goods = 0;
		do {
			$user_status = get_translation($db->f("status_name"));
			if (!$user_status) { $user_status = $db->f("order_status"); }
			$status_orders = $db->f("status_orders");
			$status_goods = $db->f("status_goods");
			$total_orders += $status_orders; $total_goods += $status_goods;
			$t->set_var("user_status", $user_status);
			$t->set_var("status_orders", $status_orders);
			$t->set_var("status_goods", $currency["left"] . number_format($status_goods * $currency["rate"], 2) . $currency["right"]);
			$t->parse("user_statuses", true);
		} while ($db->next_record());

		$t->set_var("total_orders", $total_orders);
		$t->set_var("total_goods", $currency["left"] . number_format($total_goods * $currency["rate"], 2) . $currency["right"]);
		$t->parse("user_stats", false);
		$t->sparse("user_stats_summary", false);
	}

	// check if order was added by administrator
	$admin_id_added_by = $r->get_value("admin_id_added_by");
	if ($admin_id_added_by) {
		$sql  = " SELECT admin_name, nickname FROM " . $table_prefix . "admins ";
		$sql .= " WHERE admin_id=" . $db->tosql($admin_id_added_by, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$nickname = $db->f("nickname");
			if (!strlen($nickname)) { $nickname = $db->f("admin_name"); }
			$t->set_var("admin_added_name", $nickname);
			$t->sparse("admin_added", false);
		}
	}
	

	// parse url to change currency
	$t->set_var("currency_url", "");
	if ($orders_currency != 1) {
		$order_currency_code = $r->get_value("currency_code");
		if (strlen($order_currency_code) && $currency["code"] != $order_currency_code) {
			$admin_order_currency_url = new VA_URL($order_details_site_url . "admin_order.php", true, array("currency_code", "operation"));
			$admin_order_currency_url->add_parameter("currency_code", CONSTANT, $order_currency_code);
			$t->set_var("currency_code", $order_currency_code);
			$t->set_var("admin_order_currency_url", $admin_order_currency_url->get_url());
			$t->parse("currency_url", false);
		} else {
			$sql = "SELECT currency_code FROM " . $table_prefix . "currencies WHERE is_default=1 ";
			$db->query($sql);
			if ($db->next_record()) {
				$default_currency = $db->f("currency_code");
				if ($currency["code"] != $default_currency) {
					$admin_order_currency_url = new VA_URL($order_details_site_url . "admin_order.php", true, array("currency_code", "operation"));
					$admin_order_currency_url->add_parameter("currency_code", CONSTANT, $default_currency);
					$t->set_var("currency_code", $default_currency);
					$t->set_var("admin_order_currency_url", $admin_order_currency_url->get_url());
					$t->parse("currency_url", false);
				}
			}
		}
	}

	if ($r->is_empty("remote_address")) {
		$r->parameters["remote_address"][SHOW] = false;
	}
	if ($r->get_value("user_id") == 0) {
		$r->parameters["user_id"][SHOW] = false;
	}
	if ($r->is_empty("affiliate_code")) {
		$r->parameters["affiliate_code"][SHOW] = false;
	}

	$payment_params = 0;
	for ($i = 0; $i < sizeof($cc_parameters); $i++) { 
		if ($r->is_empty($cc_parameters[$i])) {
			$r->parameters[$cc_parameters[$i]][SHOW] = false;
		} else {
			$payment_params++;
		}
	}

	$t->set_var("current_tracking_id", $r->get_value("shipping_tracking_id"));
	$t->set_var("current_status_id", htmlspecialchars($current_status_id));
	$t->set_var("current_status_desc", htmlspecialchars($current_status_desc));

	$r->set_value("company_id", get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("company_id"), INTEGER, true, false)));
	$r->set_value("delivery_company_id", get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("delivery_company_id"), INTEGER)));
	
	if ($r->parameter_exists("state_id") && $r->get_value("state_id")) {
		$state_name = get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), INTEGER, true, false));
	} elseif ($r->parameter_exists("state_code") && $r->get_value("state_code")) {
		$state_name = get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_code=" . $db->tosql($r->get_value("state_code"), TEXT, true, false));	
	} else {
		$state_name = "";
	}
	
	if ($r->parameter_exists("delivery_state_id") && $r->get_value("delivery_state_id")) {
		$delivery_state_name = get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("delivery_state_id"), INTEGER, true, false));
	} elseif ($r->parameter_exists("delivery_state_code") && $r->get_value("delivery_state_code")) {
		$delivery_state_name = get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_code=" . $db->tosql($r->get_value("delivery_state_code"), TEXT, true, false));	
	} else {
		$delivery_state_name = "";
	}
		
	if ($r->parameter_exists("country_id") && $r->get_value("country_id")) {
		$country_name = get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), INTEGER, true, false));
	} elseif ($r->parameter_exists("country_code") && $r->get_value("country_code")) {
		$country_name = get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_code=" . $db->tosql($r->get_value("country_code"), TEXT, true, false));	
	} else {
		$country_name = "";
	}
	
	if ($r->parameter_exists("delivery_country_id") && $r->get_value("delivery_country_id")) {
		$delivery_country_name = get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("delivery_country_id"), INTEGER, true, false));
	} elseif ($r->parameter_exists("delivery_country_code") && $r->get_value("delivery_country_code")) {
		$delivery_country_name = get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_code=" . $db->tosql($r->get_value("delivery_country_code"), TEXT, true, false));	
	} else {
		$delivery_country_name = "";
	}
	
	$r->set_value("state_id", get_translation($state_name));	
	$r->set_value("country_id", get_translation($country_name));
	$r->set_value("delivery_state_id", get_translation($delivery_state_name));
	$r->set_value("delivery_country_id", get_translation($delivery_country_name));
	$r->change_property("delivery_country_id", SHOW, true);
	// set parameters in upper case
	foreach ($parameters as $param_name) {	
		$param_value = $r->get_value($param_name);
		$delivery_value = $r->get_value("delivery_".$param_name);
		if (function_exists("mb_strtoupper")) {
			$param_value = mb_strtoupper($param_value, "UTF-8");
			$delivery_value = mb_strtoupper($delivery_value, "UTF-8");
		} else {
			$param_value = strtoupper($param_value);
			$delivery_value = strtoupper($delivery_value);
		}
		$t->set_var($param_name."_up", htmlspecialchars($param_value));
		$t->set_var("delivery_".$param_name."_up", htmlspecialchars($delivery_value));
	}

	
	$r->set_value("cc_type", get_translation(get_db_value("SELECT credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($r->get_value("cc_type"), INTEGER))));

	// get payment info if available
	$sql = "SELECT payment_info FROM " . $table_prefix . "payment_systems WHERE payment_id=" . $db->tosql($r->get_value("payment_id"), INTEGER);
	$payment_info = get_db_value($sql);
	$payment_info = get_translation($payment_info);
	$payment_info = get_currency_message($payment_info, $currency);
	if (trim($payment_info)) {
		$payment_params++;
		$t->set_block("payment_info", $payment_info);
		$t->parse("payment_info", false);
		$t->global_parse("payment_info_block", false, false, true);
	} else {
		$t->set_var("payment_info_block", "");
	}
	
	$r->set_parameters();

	if ($r->is_empty("error_message")) {
		$t->set_var("error_message_block", "");
	} else {
		$t->set_var("error_message", $r->get_value("error_message"));
		$t->parse("error_message_block", false);
	}
	if ($r->is_empty("pending_message")) {
		$t->set_var("pending_message_block", "");
	} else {
		$t->set_var("pending_message", $r->get_value("pending_message"));
		$t->parse("pending_message_block", false);
	}


	$remote_address = $r->get_value("remote_address");
	if (strlen($remote_address)) {
		$admin_order_black_url = new VA_URL($order_details_site_url . "admin_black_ip.php", true, array("ip", "operation", "currency_code"));
		$admin_order_black_url->add_parameter("popup", CONSTANT, 1);
		$admin_order_black_url->add_parameter("module", CONSTANT, "orders");
		$admin_order_black_url->add_parameter("ip_address", CONSTANT, $remote_address);
		$t->set_var("admin_order_black_url", $admin_order_black_url->get_url());

		$ip_data = blacklist_check("orders", array($remote_address));
		if (isset($ip_data[$remote_address]) && $ip_data[$remote_address]["range"] == $remote_address) {
			$module_rule = $ip_data[$remote_address]["rule"];
			if ($module_rule == "blocked" || $module_rule == "block") {
				$t->set_var("ip_class", "blocked-ip");
			} else if ($module_rule == "warn" || $module_rule == "warning") {
				$t->set_var("ip_class", "warning-ip");
			} else {
				$t->set_var("ip_class", "blacklist-ip");
			}
			$t->set_var("ip_edit_text", va_constant("EDIT_BUTTON")." / ".va_constant("REMOVE_BUTTON"));
		} else {
			$t->set_var("ip_edit_text", va_message("ADD_TO_BLACK_LIST_MSG"));
		}
	}


	if ($personal_number > 0 || $personal_properties) {
		$t->parse("personal", false);
	}

	if ($delivery_number > 0 || $delivery_properties) {
		$t->parse("delivery", false);
	}
	
	$payment_details = get_setting_value($permissions, "order_payment", 0);
	if (isset($va_hide_payment_details) && $va_hide_payment_details) { 
		$payment_details = false; // hide payment details
	}
	if ($payment_details && ($payment_params > 0 || $payment_properties)) {
		$t->parse("payment", false);
	}

	if (isset($permissions["remove_orders"]) && $permissions["remove_orders"] == 1) {
		$t->parse("remove_order_link", false);
	}

	// check attachments
	$attachments_files = "";
	$sql  = " SELECT attachment_id, file_name, file_path FROM " . $table_prefix . "orders_attachments ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " AND admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
	$sql .= " AND event_id=0 ";
	$sql .= " AND attachment_type=2 ";
	$db->query($sql);
	while ($db->next_record()) {
		$attachment_id = $db->f("attachment_id");
		$filename = $db->f("file_name");
		$file_path = $db->f("file_path");
		if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
			$file_path = "../".$file_path;
		}
		$filesize = filesize($file_path);
		if ($attachments_files) { $attachments_files .= "; "; }
		$attachments_files .= "<a href=\"admin_support_attachment.php?atid=" .$attachment_id. "\" target=\"_blank\">" . $filename . "</a> (" . get_nice_bytes($filesize) . ")";
	}
	$t->set_var("attached_files", $attachments_files);


	$events_types = array(
		"activation_added" => va_message("ACTIVATION_ADDED_MSG"),
		"activation_updated" => va_message("ACTIVATION_UPDATED_MSG"),
		"activation_removed" => va_message("ACTIVATION_REMOVED_MSG"),
		"links_sent" => va_message("LINKS_SENT_MSG"),
		"serials_sent" => va_message("SERIAL_NUMBERS_SENT_MSG"),
		"vouchers_sent" => va_message("GIFT_VOUCHERS_SENT_MSG"),
		"sms_sent" => va_message("SEND_SMS_MESSAGE_MSG"),
		"email_sent" => va_message("SEND_EMAIL_MESSAGE_MSG"),
		"add_product" => va_message("EVENT_ADDED_MSG"),
		"added_product" => va_message("EVENT_ADDED_MSG"),
		"new_product" => va_message("EVENT_ADDED_MSG"),
		"update_product" => va_message("EVENT_UPDATED_MSG"),
		"updated_product" => va_message("EVENT_UPDATED_MSG"),
		"delete_product" => va_message("EVENT_REMOVED_MSG"),
		"remove_product" => va_message("EVENT_REMOVED_MSG"),
		"deleted_product" => va_message("EVENT_REMOVED_MSG"),
		"removed_product" => va_message("EVENT_REMOVED_MSG"),
		"cancel_subscription" => va_message("SUBSCRIPTION_CANCELLATION_MSG"),
  
		"update_order" => va_message("EVENT_UPDATED_MSG"),
		"update_order_status" => va_message("CHANGE_STATUS_MSG"), 
		"update_items_status" => va_message("CHANGE_STATUS_MSG"), 
		"update_order_shipping" => va_message("EVENT_UPDATED_MSG"), 
		"delete_shipping" => va_message("EVENT_REMOVED_MSG"), 
		"remove_shipping" => va_message("EVENT_REMOVED_MSG"), 

		"update_shipping_tracking" => va_message("UPDATE_TRACKING_NUMBER_MSG"), 
		"remove_shipping_tracking" => va_message("UPDATE_TRACKING_NUMBER_MSG"), 
  
		"notification_sent" => va_message("NOTIFICATION_SENT_MSG"),
		"status_notification_sent" => va_message("NOTIFICATION_SENT_MSG"),
		"status_sms_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"status_merchant_email_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"status_merchant_sms_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"status_supplier_email_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"status_supplier_sms_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"status_admin_email_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"status_admin_sms_sent" => va_message("NOTIFICATION_SENT_MSG"), 
		"product_notification_sent" => va_message("NOTIFICATION_SENT_MSG"),
		"product_sms_sent" => va_message("NOTIFICATION_SENT_MSG"),
	);

	$sql  = " SELECT oe.event_id, oe.event_date, oe.event_type, oe.event_name, oe.event_description, a.admin_name ";
	$sql .= " FROM (" . $table_prefix . "orders_events oe ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id=oe.admin_id) ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " ORDER BY oe.event_date ASC ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$event_id = $db->f("event_id");
			$event_date = $db->f("event_date", DATETIME);
			$t->set_var("event_date", va_date($datetime_show_format, $event_date));

			$event_type = $db->f("event_type");
			$event_name = get_translation($db->f("event_name"));
			$event_description = get_translation($db->f("event_description"));
			$event_type_desc = isset($events_types[$event_type]) ? $events_types[$event_type] : va_message("OTHER_MSG");
			if ($event_type_desc == va_message("OTHER_MSG")) {
				// strip tags for non-standard events
				$event_description = strip_tags($event_description);
			}
			$t->set_var("event_id", $event_id);
			$t->set_var("event_type", $event_type_desc);
			$t->set_var("event_name", $event_name);
			if (preg_match("/<div|<br|<table/i", $event_description)) {
				$t->set_var("event_description", $event_description);
			} else {
				$t->set_var("event_description", nl2br($event_description));
			}
			$t->set_var("admin_name", $db->f("admin_name"));

			if ($event_description) {
				$t->parse("event_more", false);
			} else {
				$t->set_var("event_more", "");
			}

			$t->set_var("event_attachments", "");
			$t->set_var("event_attachments_block", "");
			$sql  = " SELECT * FROM " . $table_prefix . "orders_attachments ";
			$sql .= " WHERE event_id=" . $db->tosql($event_id, INTEGER);
			$dbi->query($sql);
			if ($dbi->next_record()) {
				do {
					$attachment_id = $dbi->f("attachment_id");
					$file_name = $dbi->f("file_name");
					$file_path = $dbi->f("file_path");
					if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
						// if it's not absolute path check if file exists in one folder up
						if (preg_match("/^\.\.\//", $file_path) && file_exists("../".$file_path)) {
							$file_path = "../".$file_path;
						} 
					}
					$admin_order_attachment_url = "admin_order_attachment.php?atid=".urlencode($attachment_id);
					$file_size = 0;
					if (file_exists($file_path)) {
						$file_size = filesize($file_path);
					}
					$t->set_var("file_name", htmlspecialchars($file_name));
					$t->set_var("file_size", get_nice_bytes($file_size));
					$t->set_var("admin_order_attachment_url", htmlspecialchars($admin_order_attachment_url));
					$t->sparse("event_attachments", true);
				} while ($dbi->next_record());
				$t->sparse("event_attachments_block", false);
			}

			$t->parse("events", true);
		} while ($db->next_record());
		$t->sparse("order_log", false);
	} else {
		$t->set_var("order_log", "");
	}

	$t->pparse("main");

?>