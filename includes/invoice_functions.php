<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  invoice_functions.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$root_folder_path = (isset($is_admin_path) && $is_admin_path) ? "../" : "./";
	include_once($root_folder_path . "includes/pdflib.php");
	include_once($root_folder_path . "includes/pdf.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/barcode_functions.php");

	@ini_set("max_execution_time", 200);
	function pdf_invoice($orders_ids, $pdf_params = array())
	{
		global $db, $pdf, $table_prefix, $settings, $va_messages, $currency, $parameters, $site_id;
		global $is_admin_path, $root_folder_path, $invoice;

		// global settings if we create a new PDF document and return PDF in the end
		$new_pdf = get_setting_value($pdf_params, "new_pdf", true);
		$return_pdf = get_setting_value($pdf_params, "return_pdf", true);

		// additional connection
		$dbi = new VA_SQL();
		$dbi->DBType      = $db->DBType      ;
		$dbi->DBDatabase  = $db->DBDatabase  ;
		$dbi->DBUser      = $db->DBUser      ;
		$dbi->DBPassword  = $db->DBPassword  ;
		$dbi->DBHost      = $db->DBHost      ;
		$dbi->DBPort      = $db->DBPort      ;
		$dbi->DBPersistent= $db->DBPersistent;

		$tmp_dir  = get_setting_value($settings, "tmp_dir", ".");
		$show_item_code = get_setting_value($settings, "item_code_invoice", 0);
		$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_invoice", 0);
		$show_item_weight = get_setting_value($settings, "item_weight_invoice", 0);
		$show_actual_weight = get_setting_value($settings, "actual_weight_invoice", 0);
		$show_total_weight = get_setting_value($settings, "total_weight_invoice", 0);
		$show_total_actual_weight = get_setting_value($settings, "total_actual_weight_invoice", 0);
		$show_points_price = get_setting_value($settings, "points_price_invoice", 0);
		$show_reward_points = get_setting_value($settings, "reward_points_invoice", 0);
		$show_reward_credits = get_setting_value($settings, "reward_credits_invoice", 0);
		$item_name_column = get_setting_value($settings, "invoice_item_name", 1);
		$item_price_column = get_setting_value($settings, "invoice_item_price", 1);
		$item_tax_percent_column = get_setting_value($settings, "invoice_item_tax_percent", 0);
		$item_tax_column = get_setting_value($settings, "invoice_item_tax", 0);
		$item_price_incl_tax_column = get_setting_value($settings, "invoice_item_price_incl_tax", 0);
		$item_quantity_column = get_setting_value($settings, "invoice_item_quantity", 1);
		$item_price_total_column = get_setting_value($settings, "invoice_item_price_total", 1);
		$item_tax_total_column = get_setting_value($settings, "invoice_item_tax_total", 1);
		$item_price_incl_tax_total_column = get_setting_value($settings, "invoice_item_price_incl_tax_total", 1);
		$global_tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$global_tax_round = get_setting_value($settings, "tax_round", 1);
		$tax_prices = get_setting_value($settings, "tax_prices", 0);
		//$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$tax_note = get_translation(get_setting_value($settings, "tax_note", ""));
		$tax_note_incl = $tax_note;		
		$tax_note_excl = get_translation(get_setting_value($settings, "tax_note_excl", ""));		
		$points_decimals = get_setting_value($settings, "points_decimals", 0);
		$item_image_column = get_setting_value($settings, "invoice_item_image", 0);
		$weight_measure = get_setting_value($settings, "weight_measure", "");

		// option delimiter and price options
		$option_name_delimiter = strip_tags(get_setting_value($settings, "option_name_delimiter", ": ")); 
		$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
		$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
		$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
		$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");

		// image settings
		$item_image_width = 0;
		if ($item_image_column) {
			$site_url = get_setting_value($settings, "site_url", "");
			$product_no_image = get_setting_value($settings, "product_no_image", "");
			$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");		
			product_image_fields($item_image_column, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);			
			$item_image_tmp_dir  = get_setting_value($settings, "tmp_dir", $root_folder_path);
			$item_image_position = 0;
			if ($item_image_column == 1) {
				$item_image_width  = get_setting_value($settings, "tiny_image_max_width", 40);
				$item_image_height = get_setting_value($settings, "tiny_image_max_height", 40);
				$item_image_position = 1;
			} elseif ($item_image_column == 2) {
				$item_image_width  = get_setting_value($settings, "small_image_max_width", 100);
				$item_image_height = get_setting_value($settings, "small_image_max_height", 100);
				$item_image_position = 1;
			} elseif ($item_image_column == 3) {
				$item_image_width  = get_setting_value($settings, "big_image_max_width", 300);
				$item_image_height = get_setting_value($settings, "big_image_max_height", 300);
				$item_image_position = 2;
			}			
		}
		// get initial invoice settings
		$invoice = array();
		$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
		$sql .= " WHERE setting_type='printable'";
		if (isset($site_id)) {
			$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			$sql .= "ORDER BY site_id ASC";
		} else {
			$sql .= "AND site_id=1";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$invoice[$db->f("setting_name")] = $db->f("setting_value");
		}

		// get order profile settings
		$order_info = array();
		$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
		$sql .= " WHERE setting_type='order_info'";
		if (isset($site_id)) {
			$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			$sql .= "ORDER BY site_id ASC";
		} else {
			$sql .= "AND site_id=1";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$order_info[$db->f("setting_name")] = $db->f("setting_value");
		}
		$subcomponents_show_type = get_setting_value($order_info, "subcomponents_show_type", 0);

		// create a new PDF object
		if ($new_pdf || !isset($pdf) || !$pdf) {
			$pdf = new VA_PDF();
			$pdf->set_creator("www.viart.com");
			$pdf->set_author("Viart LLC");
			$pdf->set_title("Invoice No: " . $orders_ids);
			$pdf->set_font_encoding(CHARSET);
		}
		$page_number = 0;

		// general order fields settings
		$r = new VA_Record($table_prefix . "orders");
		$r->add_where("order_id", INTEGER);
		$r->add_textbox("site_id", INTEGER);
		for ($i = 0; $i < sizeof($parameters); $i++) {
			$r->add_textbox($parameters[$i], TEXT);
			$r->add_textbox("delivery_" . $parameters[$i], TEXT);
		}
		$r->add_textbox("invoice_number", TEXT);
		$r->add_textbox("invoice_copy_number", TEXT);
		$r->add_textbox("order_status", INTEGER);
		$r->add_hidden("order_status_type", TEXT);
		$r->add_textbox("user_id", INTEGER);
		$r->add_textbox("payment_id", INTEGER);
		$r->add_textbox("order_placed_date", DATETIME);
		$r->add_textbox("currency_code", TEXT);
		$r->add_textbox("currency_rate", NUMBER);
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
		$r->add_textbox("tax_name", TEXT);
		$r->add_textbox("tax_percent", NUMBER);
		$r->add_textbox("tax_total", NUMBER);
		$r->add_textbox("tax_prices_type", INTEGER);
		$r->add_textbox("tax_round", INTEGER);
		$r->change_property("tax_round", USE_IN_SELECT, false);
		$r->add_textbox("total_discount", NUMBER);
		$r->add_textbox("total_discount_tax", NUMBER);
		$r->add_textbox("shipping_type_desc", TEXT);
		$r->add_textbox("shipping_cost", NUMBER);
		$r->add_textbox("shipping_taxable", NUMBER);
		$r->add_textbox("shipping_points_amount", NUMBER);
		$r->add_textbox("credit_amount", NUMBER);
		$r->add_textbox("processing_fee", NUMBER);
		$r->add_textbox("processing_tax_free", NUMBER);
		$r->add_textbox("weight_total", NUMBER);
		$r->add_textbox("actual_weight_total", NUMBER);
		$r->add_textbox("order_total", NUMBER);

		$ids = explode(",", $orders_ids);
		if (isset($site_id)) {
			$previous_site_id = $site_id;
		} else {
			$previous_site_id = 1;
		}
		for ($id = 0; $id < sizeof($ids); $id++)
		{
			$order_id = $ids[$id];
			$r->set_value("order_id", $order_id);
			$r->get_db_values();
			$order_site_id = $r->get_value("site_id");
			$order_status = $r->get_value("order_status");
			$payment_id = $r->get_value("payment_id");

			// check order status type
			$sql = " SELECT status_type FROM " . $table_prefix ."order_statuses WHERE status_id=" . $db->tosql($order_status, INTEGER);
			$order_status_type = get_db_value($sql);
			$r->set_value("order_status_type", $order_status_type);

			if ($previous_site_id != $order_site_id) {
				// get invoice settings for current order
				$invoice = array();
				$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
				$sql .= " WHERE setting_type='printable'";
				if ($order_site_id) {
					$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
					$sql .= "ORDER BY site_id ASC";
				} else {
					$sql .= "AND site_id=1";
				}
				$db->query($sql);
				while ($db->next_record()) {
					$invoice[$db->f("setting_name")] = $db->f("setting_value");
				}

				// get order fields settings for current order
				$order_info = array();
				$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
				$sql .= " WHERE setting_type='order_info'";
				if (isset($order_site_id)) {
					$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
					$sql .= "ORDER BY site_id ASC";
				} else {
					$sql .= "AND site_id=1";
				}
				$db->query($sql);
				while ($db->next_record()) {
					$order_info[$db->f("setting_name")] = $db->f("setting_value");
				}
			}
			$previous_site_id = $order_site_id;

			// check parameters list to hide
			$personal_number = 0; $delivery_number = 0;
			for ($i = 0; $i < sizeof($parameters); $i++)
			{
				$personal_param = "show_" . $parameters[$i];
				$delivery_param = "show_delivery_" . $parameters[$i];
				if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
					$personal_number++;
					$r->parameters[$parameters[$i]][SHOW] = true;
				} else {
					$r->parameters[$parameters[$i]][SHOW] = false;
				}
				if (isset($order_info[$delivery_param]) && $order_info[$delivery_param] == 1) {
					$delivery_number++;
					$r->parameters["delivery_" . $parameters[$i]][SHOW] = true;
				} else {
					$r->parameters["delivery_" . $parameters[$i]][SHOW] = false;
				}
			}

			// get order tax rates
			$tax_available = false; $tax_percent_sum = 0; $tax_names = "";	$tax_column_names = "";	$taxes_total = 0; 
			$order_tax_rates = order_tax_rates($order_id);
			if (sizeof($order_tax_rates) > 0) {
				$tax_available = true;
				foreach ($order_tax_rates as $tax_id => $tax_info) {
					$show_type = $tax_info["show_type"];
					$tax_type = $tax_info["tax_type"];
					if ($tax_type == 1) {
						// sum only general tax 
						$tax_percent_sum += $tax_info["tax_percent"];
					}
					if ($show_type&1) {
						if ($tax_column_names) { $tax_column_names .= " & "; }
						$tax_column_names .= get_translation($tax_info["tax_name"]);
					}
					if ($tax_names) { $tax_names .= " & "; }
					$tax_names .= get_translation($tax_info["tax_name"]);
				}
			}

			// prepare cart titles
			$va_messages["tax_note"] = $tax_note_excl;
			$va_messages["tax_note_excl"] = $tax_note_excl;
			$va_messages["tax_note_incl"] = $tax_note_incl;
			$va_messages["tax_name"] = $tax_column_names;
			$image_col_name = get_setting_value($settings, "image_col_name", va_constant("IMAGE_MSG"));
			$name_col_name = get_setting_value($settings, "name_col_name", va_constant("PROD_TITLE_COLUMN"));
			$price_excl_col_name = get_setting_value($settings, "price_excl_col_name", va_constant("PROD_PRICE_COLUMN")." {tax_note_excl}");
			$tax_percent_col_name = get_setting_value($settings, "tax_percent_col_name", "{tax_name} (%)");
			$tax_col_name = get_setting_value($settings, "tax_col_name", "{tax_name}");
			$price_incl_col_name = get_setting_value($settings, "price_incl_col_name", va_constant("PROD_PRICE_COLUMN")." {tax_note_incl}");
			$quantity_col_name = get_setting_value($settings, "quantity_col_name", va_constant("PROD_QTY_COLUMN"));
			$total_excl_col_name = get_setting_value($settings, "total_excl_col_name", va_constant("PROD_TOTAL_COLUMN")." {tax_note_excl}");
			$tax_total_col_name = get_setting_value($settings, "tax_total_col_name", "{tax_name} ".va_constant("PROD_TAX_TOTAL_COLUMN"));
			$total_incl_col_name = get_setting_value($settings, "total_incl_col_name", va_constant("PROD_TOTAL_COLUMN")." {tax_note_incl}");
			parse_value($image_col_name);
			parse_value($name_col_name);
			parse_value($price_excl_col_name);
			parse_value($tax_percent_col_name);
			parse_value($tax_col_name);
			parse_value($price_incl_col_name);
			parse_value($quantity_col_name);
			parse_value($total_excl_col_name);
			parse_value($tax_total_col_name);
			parse_value($total_incl_col_name);
			// end cart titles

			$tax_available = sizeof($order_tax_rates);
			$tax_prices_type = $r->get_value("tax_prices_type");
			if (!strlen($tax_prices_type)) {
				$tax_prices_type = $global_tax_prices_type;
			}
			$tax_round = $r->get_value("tax_round");
			if (!strlen($tax_round)) {
				$tax_round = $global_tax_round;
			}

			$tax_total = $r->get_value("tax_total");
			$total_discount = $r->get_value("total_discount");
			$total_discount_tax = $r->get_value("total_discount_tax");
			// OLD SHIPPING PARAMETERS 
			$old_shipping_type_desc = strip_tags(get_translation($r->get_value("shipping_type_desc")));
			$old_shipping_cost = $r->get_value("shipping_cost");
			$old_shipping_taxable = $r->get_value("shipping_taxable");
			$old_shipping_points_amount = $db->f("shipping_points_amount");
			$old_shipping_tracking_id = $db->f("shipping_tracking_id");

			$credit_amount = $r->get_value("credit_amount");
			$processing_fee = $r->get_value("processing_fee");
			$processing_tax_free = $r->get_value("processing_tax_free");
			$weight_total = $r->get_value("weight_total");
			$actual_weight_total = $r->get_value("actual_weight_total");
			$order_total = $r->get_value("order_total");
	  
			// get order currency
			$order_currency_code = $r->get_value("currency_code");
			$order_currency_rate= $r->get_value("currency_rate");

	  	// get order currency
			$orders_currency = get_setting_value($settings, "orders_currency", 0);
			if ($orders_currency != 1) {
				$order_currency = $currency;
				$order_currency["rate"] = $order_currency_rate;
				if (strtolower($currency["code"]) != strtolower($order_currency_code)) {
					$order_currency["rate"] = $currency["rate"]; // in case if active currency different from the order was placed use current exchange rate
				}
			} else {
				$order_currency = get_currency($order_currency_code);
				$order_currency["rate"] = $order_currency_rate; // show order with exchange rate it was placed
			}

			// check what columns to show
			$goods_colspan = 0; $total_columns = 0;
			if ($item_image_column) {
				$goods_colspan++;
				$total_columns++;
			}
			if ($item_name_column) {
				$goods_colspan++;
				$total_columns++;
			}
			if ($item_price_column || ($item_price_incl_tax_column && !$tax_available)) {
				$item_price_column = true;
				$goods_colspan++;
				$total_columns++;
			}
			if ($item_tax_percent_column && $tax_available) {
				$goods_colspan++;
				$total_columns++;
			} else {
				$item_tax_percent_column = false;
			}
			if ($item_tax_column && $tax_available) {
				$goods_colspan++;
				$total_columns++;
			} else {
				$item_tax_column = false;
			}
			if ($item_price_incl_tax_column && $tax_available) {
				$goods_colspan++;
				$total_columns++;
			} else {
				$item_price_incl_tax_column = false;
			}
			if ($item_quantity_column) {
				$goods_colspan++;
				$total_columns++;
			}
			if ($item_price_total_column || ($item_price_incl_tax_total_column && !$tax_available)) {
				$item_price_total_column = true;
				$total_columns++;
			}
			if ($item_tax_total_column && $tax_available) {
				$total_columns++;
			} else {
				$item_tax_total_column = false;
			}
			if ($item_price_incl_tax_total_column && $tax_available) {
				$total_columns++;
			} else {
				$item_price_incl_tax_total_column = false;
			}

			$columns = array(
				"item_name" => array("name" => $name_col_name, "active" => $item_name_column, "align" => "left"), 
				"item_price" => array("name" => $price_excl_col_name, "active" => $item_price_column, "align" => "right"), 
				"item_tax_percent" => array("name" => $tax_percent_col_name, "active" => $item_tax_percent_column, "align" => "center"),
				"item_tax" => array("name" => $tax_col_name, "active" => $item_tax_column, "align" => "right"),
				"item_price_incl_tax" => array("name" => $price_incl_col_name, "active" => $item_price_incl_tax_column, "align" => "right"),
				"item_quantity" => array("name" => $quantity_col_name, "active" => $item_quantity_column, "align" => "center"),
				"item_price_total" => array("name" => $total_excl_col_name, "active" => $item_price_total_column, "align" => "right"),
				"item_tax_total" => array("name" => $tax_total_col_name, "active" => $item_tax_total_column, "align" => "right"),
				"item_price_incl_tax_total" => array("name" => $total_incl_col_name, "active" => $item_price_incl_tax_total_column, "align" => "right"),
			);
			foreach ($columns as $column_name => $column_values) {
				$columns[$column_name]["width"] = 0;
				$columns[$column_name]["start"] = 0;
			}

			$columns_left = $total_columns;
			$column_end = 40;
			
			// left space for image
			if ($item_image_column && $item_image_position == 1) {
				$column_end += $item_image_width + 2;
			}
			
			$item_name_column = true; // always show product title
			if ($item_name_column) {
				$columns["item_name"]["start"] = $column_end;
				if ($total_columns <= 5) {
					$columns["item_name"]["width"] = 240;
				} elseif ($total_columns == 6) {
					$columns["item_name"]["width"] = 200;
				} elseif ($total_columns == 7) {
					$columns["item_name"]["width"] = 160;
				} elseif ($total_columns >= 8) {
					$columns["item_name"]["width"] = 120;
				}
				$columns_left--;
				$column_end += $columns["item_name"]["width"];
			}
			$width_left = 515 - $columns["item_name"]["width"];
			// check if we need to deduct image column		
			if ($item_image_column && $item_image_position == 1) {
				$width_left -= $item_image_width;
			}

			$average_width = intval($width_left / $columns_left);
			if ($item_price_column) {
				if ($average_width > 50) {
					$columns["item_price"]["width"] = $average_width;
				} else {
					$columns["item_price"]["width"] = 50;
				}
				$columns["item_price"]["start"] = $column_end;
				$column_end += $columns["item_price"]["width"];
			}
			if ($item_tax_percent_column) {
				if ($average_width > 50) {
					$columns["item_tax_percent"]["width"] = $average_width;
				} else {
					$columns["item_tax_percent"]["width"] = 45;
				}
				$columns["item_tax_percent"]["start"] = $column_end;
				$column_end += $columns["item_tax_percent"]["width"];
			}
			if ($item_tax_column) {
				if ($average_width > 50) {
					$columns["item_tax"]["width"] = $average_width;
				} else {
					$columns["item_tax"]["width"] = 50;
				}
				$columns["item_tax"]["start"] = $column_end;
				$column_end += $columns["item_tax"]["width"];
			}
			if ($item_price_incl_tax_column) {
				if ($average_width > 50) {
					$columns["item_price_incl_tax"]["width"] = $average_width;
				} else {
					$columns["item_price_incl_tax"]["width"] = 50;
				}
				$columns["item_price_incl_tax"]["start"] = $column_end;
				$column_end += $columns["item_price_incl_tax"]["width"];
			}
			if ($item_quantity_column) {
				if ($average_width > 50) {
					$columns["item_quantity"]["width"] = $average_width;
				} else {
					$columns["item_quantity"]["width"] = 45;
				}
				$columns["item_quantity"]["start"] = $column_end;
				$column_end += $columns["item_quantity"]["width"];
			}
			if ($item_price_total_column) {
				if ($average_width > 50) {
					$columns["item_price_total"]["width"] = $average_width;
				} else {
					$columns["item_price_total"]["width"] = 50;
				}
				$columns["item_price_total"]["start"] = $column_end;
				$column_end += $columns["item_price_total"]["width"];
			}
			if ($item_tax_total_column) {
				if ($average_width > 50) {
					$columns["item_tax_total"]["width"] = $average_width;
				} else {
					$columns["item_tax_total"]["width"] = 50;
				}
				$columns["item_tax_total"]["start"] = $column_end;
				$column_end += $columns["item_tax_total"]["width"];
			}
			if ($item_price_incl_tax_total_column) {
				if ($average_width > 50) {
					$columns["item_price_incl_tax_total"]["width"] = $average_width;
				} else {
					$columns["item_price_incl_tax_total"]["width"] = 50;
				}
				$columns["item_price_incl_tax_total"]["start"] = $column_end;
				$column_end += $columns["item_price_incl_tax_total"]["width"];
			}
			$last_column_name = "item_name";
			foreach ($columns as $column_name => $values) {
				if ($values["active"]) {
					$last_column_name = $column_name;
				}
			}
			$columns[$last_column_name]["width"] = 555 - $columns[$last_column_name]["start"];

			// set values from list
			$r->set_value("company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("company_id"), INTEGER))));
			$r->set_value("state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), INTEGER))));
			$r->set_value("country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), INTEGER))));
			$r->set_value("delivery_company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("delivery_company_id"), INTEGER))));
			$r->set_value("delivery_state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("delivery_state_id"), INTEGER))));
			$r->set_value("delivery_country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("delivery_country_id"), INTEGER))));
			$r->set_value("cc_type", get_translation(get_db_value("SELECT credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($r->get_value("cc_type"), INTEGER))));

			// get all order properties
			$orders_properties = array(); $cart_properties = array(); $personal_properties = array();
			$delivery_properties = array(); $shipping_properties = array(); $payment_properties = array();
			$properties_total = 0; $properties_taxable = 0;
			$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, ";
			$sql .= " op.property_price, op.property_points_amount, op.tax_free ";
			$sql .= " FROM " . $table_prefix . "orders_properties op ";
			$sql .= " WHERE op.order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " ORDER BY op.property_order, op.property_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$property_type = $db->f("property_type");
				$property_name = strip_tags(get_translation($db->f("property_name")));
				$property_value = strip_tags(get_translation($db->f("property_value")));
				$property_price = $db->f("property_price");
				$property_points_amount = $db->f("property_points_amount");
				$property_tax_id = 0;
				$property_tax_free = $db->f("tax_free");
				$control_type = $db->f("control_type");
				$properties_total += $property_price;
				if ($property_tax_free != 1) {
					$properties_taxable += $property_price;
				}
    
				$property_tax_values = get_tax_amount($order_tax_rates, "properties", $property_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$property_tax = add_tax_values($order_tax_rates, $property_tax_values, "properties", $tax_round);
		  
				if ($tax_prices_type == 1) {
					$property_price_excl_tax = $property_price - $property_tax;
					$property_price_incl_tax = $property_price;
				} else {
					$property_price_excl_tax = $property_price;
					$property_price_incl_tax = $property_price + $property_tax;
				}

				if (isset($orders_properties[$property_id])) {
					$orders_properties[$property_id]["value"] .= "; " . $property_value;
					$orders_properties[$property_id]["price"] += $property_price;
					$orders_properties[$property_id]["points_amount"] += $property_points_amount;
				} else {
					$orders_properties[$property_id] = array(
						"type" => $property_type, "name" => $property_name, "value" => $property_value, 
						"price" => $property_price, "points_amount" => $property_points_amount, "tax_free" => $property_tax_free,
						"tax" => $property_tax, "property_price_excl_tax" => $property_price_excl_tax, "property_price_incl_tax" => $property_price_incl_tax,
					);
				}
	  
				// save data by arrays
				if ($property_type == 1) {
				  $cart_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 2) {
					$personal_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 3) {
					$delivery_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 4) {
					$payment_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 5 || $property_type == 6) {
					$shipping_properties[$property_id] = $orders_properties[$property_id];
				}
			}

			begin_new_page($pdf, $height_position, $page_number, $invoice);
			set_invoice_header($pdf, $height_position, $invoice, $r);
			set_user_info($pdf, $height_position, $r, $personal_number, $delivery_number, $personal_properties, $delivery_properties, $order_currency);
			set_table_header($pdf, $height_position, $r, $columns);

			// show order items
			$goods_total = 0; $goods_tax_total = 0; $goods_tax_total_show = 0;
			$goods_total_excl_tax = 0; $goods_total_incl_tax = 0;

			$orders_items = array();
			$sql  = " SELECT * FROM " . $table_prefix . "orders_items ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " ORDER BY order_item_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$order_item_id = $db->f("order_item_id");
				$top_order_item_id = $db->f("top_order_item_id");
				$item_type_id = $db->f("item_type_id");
				$price = $db->f("price");
				$quantity = $db->f("quantity");
				$item_weight = $db->f("weight");
				$actual_weight = $db->f("actual_weight");
				$item_tax_id = $db->f("tax_id");
				$item_tax_free = $db->f("tax_free");
				$item_total = $price * $quantity;
		  
				// new
				$item_tax = get_tax_amount($order_tax_rates, $item_type_id, $price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, "", 1, $tax_prices_type, $tax_round);
				$item_tax_values = get_tax_amount($order_tax_rates, $item_type_id, $price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$item_tax_total_values = get_tax_amount($order_tax_rates, $item_type_id, $item_total, $quantity, $item_tax_id, $item_tax_free, $item_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$item_tax_total = add_tax_values($order_tax_rates, $item_tax_total_values, "products", $tax_round);
		  
				if ($tax_prices_type == 1) {
					$price_excl_tax = $price - $item_tax;
					$price_incl_tax = $price;
					$price_excl_tax_total = $item_total - $item_tax_total;
					$price_incl_tax_total = $item_total;
				} else {
					$price_excl_tax = $price;
					$price_incl_tax = $price + $item_tax;
					$price_excl_tax_total = $item_total;
					$price_incl_tax_total = $item_total + $item_tax_total;
				}

				$orders_items[$order_item_id] = $db->Record;
				$orders_items[$order_item_id]["id"] = $order_item_id;
				$orders_items[$order_item_id]["item_total"] = $item_total;
				$orders_items[$order_item_id]["price_excl_tax"] = $price_excl_tax;
				$orders_items[$order_item_id]["price_incl_tax"] = $price_incl_tax;
				$orders_items[$order_item_id]["price_excl_tax_total"] = $price_excl_tax_total;
				$orders_items[$order_item_id]["price_incl_tax_total"] = $price_incl_tax_total;
				$orders_items[$order_item_id]["item_tax"] = $item_tax;
				$orders_items[$order_item_id]["item_tax_values"] = $item_tax_values;
				$orders_items[$order_item_id]["item_tax_total"] = $item_tax_total;
				$orders_items[$order_item_id]["item_tax_total_values"] = $item_tax_total_values;

				$orders_items[$order_item_id]["tax_percent"] = $item_tax_percent;
				if (!isset($orders_items[$order_item_id]["components"])) {
					$orders_items[$order_item_id]["components"] = array();
				}
				if ($top_order_item_id) {
					$orders_items[$top_order_item_id]["components"][] = $order_item_id;
				}
			}

			// check if all top order items exists and remove them if not
			foreach ($orders_items as $order_item_id => $item) {
				if (!isset($item["id"])) { 
					unset($orders_items[$order_item_id]); 
					// clear top order item as it's not exists anymore
					$components = $item["components"];
					foreach ($components as $order_item_id) {
						if (isset($orders_items[$order_item_id])) { $orders_items[$order_item_id]["top_order_item_id"] = ""; }
					}
				}
			}

			foreach ($orders_items as $order_item_id => $item) {
				if ($height_position < 200) {
					$pdf->end_page();
					begin_new_page($pdf, $height_position, $page_number, $invoice);
					set_table_header($pdf, $height_position, $r, $columns);
				}
	  
				$top_order_item_id = $item["top_order_item_id"];
				if ($subcomponents_show_type == 1 && $top_order_item_id && isset($orders_items[$top_order_item_id])) {
					// component already shown with parent product
					continue;
				}
				$item_id = $item["item_id"];
				$order_item_id = $item["order_item_id"];
				$quantity = $item["quantity"];
				$selection_name = get_translation($item["component_name"]);
				$item_name = strip_tags(get_translation($item["item_name"]));
				$item_code = $item["item_code"];
				$manufacturer_code = $item["manufacturer_code"];
				$item_weight = $item["weight"];
				$actual_weight = $item["actual_weight"];
	  
				$price = $item["price"];
				$item_tax_id = $item["tax_id"];
				$tax_free = $item["tax_free"];
				$item_tax_percent = $item["tax_percent"];
				$discount_amount = $item["discount_amount"];  
				$item_total = $item["item_total"];
				$item_tax = $item["item_tax"];
				$item_tax_total = $item["item_tax_total"];
				$item_tax_values = $item["item_tax_values"];
				$item_tax_total_values = $item["item_tax_total_values"];
				$price_excl_tax = $item["price_excl_tax"];
				$price_incl_tax = $item["price_incl_tax"];
				$price_excl_tax_total = $item["price_excl_tax_total"];
				$price_incl_tax_total = $item["price_incl_tax_total"];

				// points and credits total values
				$points_price = $item["points_price"] * $quantity;  
				$reward_points = $item["reward_points"] * $quantity;  
				$reward_credits = $item["reward_credits"] * $quantity;  

				$components_strings = array();
				$components = isset($item["components"]) ? $item["components"] : "";
				if ($subcomponents_show_type == 1 && is_array($components) && sizeof($components) > 0) {
					for ($c = 0; $c < sizeof($components); $c++) {
						$cc_id = $components[$c];
						$component = $orders_items[$cc_id];
						$component_id = $component["item_id"];
						$selection_name = get_translation($component["component_name"]);
						$component_name = get_translation($component["item_name"]);
						$component_price = $component["price"];
						$component_quantity = $component["quantity"];
						$component_sub_quantity = intval($component_quantity / $quantity);
						$component_item_code = $component["item_code"];
						$component_manufacturer_code = $component["manufacturer_code"];
		  
						$price += ($component["price"] * $component_sub_quantity);
						$item_total += $component["item_total"];
						$item_tax += ($component["item_tax"] * $component_sub_quantity);
						$item_tax_total += $component["item_tax_total"];
						$price_excl_tax += ($component["price_excl_tax"] * $component_sub_quantity);
						$price_incl_tax += ($component["price_incl_tax"] * $component_sub_quantity);
						$price_excl_tax_total += ($component["price_excl_tax_total"] );
						$price_incl_tax_total += ($component["price_incl_tax_total"] );
		  
						$points_price += ($component["points_price"] * $component_quantity);
						$reward_points += ($component["reward_points"] * $component_quantity);
						$reward_credits += ($component["reward_credits"] * $component_quantity);

						$component_string = "";
						if (strlen($selection_name)) {
							$component_string .= $selection_name . ": ";
						}
						$component_string .= $component_sub_quantity . " x " . $component_name;
						if ($component_price > 0) {
							$component_string .= $option_positive_price_right . currency_format($component_price) . $option_positive_price_left;
						} elseif ($component_price < 0) {
							$component_string .= $option_negative_price_right . currency_format(abs($component_price)) . $option_negative_price_left;
						}
						$components_strings[] = $component_string;
					}
				}



				// show tax information if column option selected
				$show_percentage = 0; $show_tax = 0; $show_tax_total = 0;
				foreach ($item_tax_values as $tax_id => $tax) {
					$show_type = $tax["show_type"];
					if ($show_type&1) {
						$show_percentage += $tax["tax_percent"];
						$show_tax += $tax["tax_amount"];
						$show_tax_total += $item_tax_total_values[$tax_id]["tax_amount"];
					}
				}

				$columns["item_name"]["value"] = "";
				$columns["item_price"]["value"] = currency_format($price_excl_tax, $order_currency);
				$columns["item_tax_percent"]["value"] = $show_percentage . "%";
				$columns["item_tax"]["value"] = currency_format($show_tax, $order_currency);
				$columns["item_price_incl_tax"]["value"] = currency_format($price_incl_tax, $order_currency);
				$columns["item_quantity" ]["value"] = $quantity;
				$columns["item_price_total"]["value"] = currency_format($price_excl_tax_total, $order_currency);
				$columns["item_tax_total"]["value"] = currency_format($show_tax_total, $order_currency);
				$columns["item_price_incl_tax_total"]["value"] = currency_format($price_incl_tax_total, $order_currency);
		  
				$goods_total += $item_total;
				$goods_tax_total += $item_tax_total;
				$goods_tax_total_show += $show_tax_total;
				$goods_total_excl_tax += $price_excl_tax_total;
				$goods_total_incl_tax += $price_incl_tax_total;

				$price_formatted = currency_format($price, $order_currency);
				$total_formatted = currency_format($item_total, $order_currency);
				$tax_formatted = currency_format($item_tax_total, $order_currency);
	  
	  
				$pdf->setfont("helvetica", "", 9);
				$fontsize = 9;
	  
				$item_height = $pdf->show_xy($item_name, $columns["item_name"]["start"] + 4, $height_position - 2, $columns["item_name"]["width"] - 6, 0);
				// set smaller font for product additional information
				$pdf->setfont("helvetica", "", 8);
				// show product code
				if ($show_item_code && strlen($item_code)) {
					$item_height += 2;
					$code_height = $pdf->show_xy(va_constant("PROD_CODE_MSG") .": " . $item_code, $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}
				// show manufacturer code
				if ($show_manufacturer_code && strlen($manufacturer_code)) {
					$item_height += 2;
					$code_height = $pdf->show_xy(va_constant("MANUFACTURER_CODE_MSG") .": " . $manufacturer_code, $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}
				// show item weight
				if ($show_item_weight && $item_weight > 0) {
					$item_height += 2;
					$item_weight = round($item_weight, 4);
					$code_height = $pdf->show_xy(va_constant("WEIGHT_MSG").": " . $item_weight.$weight_measure, $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}
				// show actual weight
				if ($show_actual_weight && $actual_weight > 0) {
					$item_height += 2;
					$actual_weight = round($actual_weight, 4);
					$code_height = $pdf->show_xy(va_constant("ACTUAL_WEIGHT_MSG").": " . $actual_weight.$weight_measure, $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}

				// new-spec begin
				// show specification information if it's available
				$sql  = " SELECT fg.group_id,fg.group_name,f.feature_name,f.feature_value ";
				$sql .= " FROM " . $table_prefix . "features f, " . $table_prefix . "features_groups fg ";
				$sql .= " WHERE f.group_id=fg.group_id ";
				$sql .= " AND f.item_id=" . intval($item_id);
				$sql .= " AND fg.show_on_invoice=1 ";
				$sql .= " AND (f.show_on_invoice=1 OR f.show_as_group=1) ";
				$sql .= " ORDER BY fg.group_order, f.feature_id ";
				$db->query($sql);
				if ($db->next_record()) {
					$last_group_id = "";
					do {
						$group_id = $db->f("group_id");
						$group_name = get_translation($db->f("group_name"));
						$feature_name = get_translation($db->f("feature_name"));
						$feature_value = get_translation($db->f("feature_value"));
						if ($group_id != $last_group_id) {
							// start showing group
							$pdf->setfont("helvetica", "BU", 8);
							$item_height += 2;
							$code_height = $pdf->show_xy($group_name, $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
							$item_height += $code_height;
						}
		  
						// show specification value				      
						$pdf->setfont("helvetica", "", 8);
						$item_height += 2;
						$code_height = $pdf->show_xy($feature_name.": ".$feature_value, $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
						$item_height += $code_height;
        
						$last_group_id = $group_id;
					} while ($db->next_record());
				} 
				// new-spec end

				// show tax below product if such option set
				foreach ($item_tax_total_values as $tax_id => $tax_info) {
					$show_type = $tax_info["show_type"];
					if ($show_type & 2) {
						$item_height += 2;
						$code_height = $pdf->show_xy(get_translation($tax_info["tax_name"]).": " . currency_format($tax_info["tax_amount"], $order_currency), $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
						$item_height += $code_height;
					}
				}
				// show points price
				if ($points_price > 0 && $show_points_price) {
					$item_height += 2;
					$code_height = $pdf->show_xy(va_constant("POINTS_PRICE_MSG").": " . number_format($points_price, $points_decimals), $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}
				// show reward points 
				if ($reward_points > 0 && $show_reward_points) {
					$item_height += 2;
					$code_height = $pdf->show_xy(va_constant("REWARD_POINTS_MSG").": " . number_format($reward_points, $points_decimals), $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}
				// show reward credits 
				if ($reward_credits > 0 && $show_reward_credits) {
					$item_height += 2;
					$code_height = $pdf->show_xy(va_constant("REWARD_CREDITS_MSG").": " . currency_format($reward_credits), $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}
				// show components 
				for ($cs = 0; $cs < sizeof($components_strings); $cs++) {
					$item_height += 2;
					$code_height = $pdf->show_xy($components_strings[$cs], $columns["item_name"]["start"] + 4, $height_position - $item_height - 2, $columns["item_name"]["width"] - 6, 0);
					$item_height += $code_height;
				}

				// return original font for product information
				$pdf->setfont("helvetica", "", 9);
		
				// show product image
				$item_image = "";
				if ($item_image_column && $image_field) { 
					$sql  = " SELECT " . $image_field; 
					$sql .= " FROM " . $table_prefix . "items";
					$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);				
					$dbi->query($sql);			
					$image_exists = false;
					if ($dbi->next_record()) {
						$item_image = $dbi->f($image_field);

						if (!strlen($item_image)) {
							//$item_image = $product_no_image;
						} else {
							$image_exists = true;
						}
					}
				}
				$item_image_tmp_created = false;
				if ($item_image) {
					$pos = strrpos($item_image, '.');
					if (!$pos) {
						$item_image_type = "jpg";
					}
					$item_image_type = substr($item_image, $pos+1);
					
					$item_image_tmp_name = $item_image_tmp_dir . $item_id . '-4pdf.' . $item_image_type;
					$item_image = str_replace($settings['site_url'], '', $item_image);
					if (preg_match("/^http\:\/\//", $item_image)) {
						$item_image  = "";
					} else {						
						if ($site_url && $image_exists && ($watermark || $restrict_products_images)) {
							if ($item_image_tmp_dir) {
								if (!file_exists($item_image_tmp_name)) {
									$item_image = $site_url . "image_show.php?item_id=".$item_id."&type=".$image_type_name."&vc=".md5($item_image);
									$out = fopen($item_image_tmp_name, 'wb');
									$item_image_tmp_created = true;
									if (function_exists("curl_init") && $out) {	
									    $ch = curl_init();
									    curl_setopt($ch, CURLOPT_FILE, $out);
									    curl_setopt($ch, CURLOPT_HEADER, 0);
									    curl_setopt($ch, CURLOPT_URL, $item_image);
										curl_exec($ch);
										if (curl_errno($ch)) {
											$item_image = "";
										} else {
											$item_image = $item_image_tmp_name;
										}
										curl_close($ch);
										fclose($out);
									} else {
										$item_image = "";
									}
								} else {
									$item_image  = $item_image_tmp_name;
								}
							} else {
								$item_image = "";
							}
						} else {
							if ($is_admin_path) {
								$item_image  = $root_folder_path . $item_image;
							}
						}
					}
				}
				$item_height += 6;	  
				$pdf->setfont("helvetica", "", 8);
				$image_height = 0;
				if ($item_image && $item_image_position == 1) {
					$image_size = @getimagesize($item_image);
					$image_height = $image_size[1];
					$pdf->place_image($item_image, 41, $height_position - $image_size[1] - 1, $item_image_type);	
				}
				foreach ($columns as $column_name => $values) {
					if ($values["active"] && strlen($values["value"])) {
						$pdf->show_xy($values["value"], $values["start"]+2, $height_position - 2, $values["width"]-4, 0, $values["align"]);
					}
				}

				$height_position -= $item_height;
				$pdf->setfont("helvetica", "", 8);
				$properties_height = 0;
								
				if ($item_image_tmp_created) {
					@unlink($item_image_tmp_name);
				}
	  
				$sql  = " SELECT property_name, hide_name, property_value, length_units FROM " . $table_prefix . "orders_items_properties ";
				$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
				$sql .= " ORDER BY property_order, property_id ";
				$dbi->query($sql);
				while ($dbi->next_record()) {
					$property_name = strip_tags(get_translation($dbi->f("property_name")));
					$hide_name = $dbi->f("hide_name");
					$property_value = strip_tags(get_translation($dbi->f("property_value")));
					$length_units = $dbi->f("length_units");
					$property_line = "";
					if (!$hide_name) {
						$property_line = $property_name . $option_name_delimiter;
					}
					$property_line .= $property_value;
					if (strlen($property_line)) {
						if ($length_units) {
							$property_line .= " ".strtoupper($length_units);
						}
						$property_height = $pdf->show_xy($property_line, $columns["item_name"]["start"] + 4, $height_position - $properties_height + 2, $columns["item_name"]["width"] - 14, 0);
						$properties_height += $property_height;
					}
				}
				// show information about coupons used
				$sql  = " SELECT coupon_id, coupon_code, coupon_title, discount_amount ";
				$sql .= " FROM " . $table_prefix . "orders_coupons ";
				$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
				$dbi->query($sql);
				while ($dbi->next_record()) {
					$coupon_id = $dbi->f("coupon_id");
					$coupon_code = $dbi->f("coupon_code");
					$coupon_title = $dbi->f("coupon_title");
					$coupon_discount = $dbi->f("discount_amount");
					$coupon_line = $coupon_title . " (".currency_format(-$coupon_discount, $order_currency).")";
					$property_height = $pdf->show_xy($coupon_line, $columns["item_name"]["start"] + 4, $height_position - $properties_height + 2, $columns["item_name"]["width"] - 14, 0);
					$properties_height += $property_height;
				}
				if ($properties_height > 0) {
					$properties_height += 2;
					$height_position -= $properties_height;
				}

				// show large image after product properties
				if ($item_image && $item_image_position == 2) {
					$image_size = @getimagesize($item_image);
					$pdf->place_image($item_image, 40, $height_position - $image_size[1], $item_image_type);
					$properties_height += $image_size[1];
					$height_position -= $image_size[1];
				}

				// check if we need add some additional pixels for image
				$additional_height = 0;
				if (($image_height + 2) > ($item_height + $properties_height)) {
					$additional_height = ($image_height - $item_height - $properties_height + 2);
					$height_position -= $additional_height;
				}

				$pdf->setlinewidth(1.0);
				$pdf->rect (40, $height_position, 515, $item_height + $properties_height + $additional_height);
				foreach ($columns as $column_name => $values) {
					if ($values["active"]) {
						$pdf->line( $values["start"], $height_position, $values["start"], $height_position + $item_height + $properties_height + $additional_height);
					}
				}
			}

			// set total fields
			$height_position -= 14;
			$goods_total_formatted = currency_format($goods_total, $order_currency);
			$goods_tax_formatted = currency_format($goods_tax_total, $order_currency);

			$total_name_width = $item_image_width;
			$total_name_width+= $columns["item_name"]["width"] + $columns["item_quantity"]["width"];
			$total_name_width+= $columns["item_price"]["width"] + $columns["item_tax_percent"]["width"];
			$total_name_width+= $columns["item_tax"]["width"] + $columns["item_price_incl_tax"]["width"];

			$pdf->setfont("helvetica", "B", 8);
			$pdf->rect (40, $height_position, 515, 14);
			$pdf->show_xy(va_constant("GOODS_TOTAL_MSG"), 40, $height_position + 14, $total_name_width - 5, 12, "right");
	  
			if ($item_price_total_column) {
				$pdf->line( $columns["item_price_total"]["start"], $height_position, $columns["item_price_total"]["start"], $height_position + 14);
				$pdf->show_xy(currency_format($goods_total_excl_tax, $order_currency), $columns["item_price_total"]["start"], $height_position + 14, $columns["item_price_total"]["width"] - 2, 12, "right");
			}
			if ($item_tax_total_column) {
				$pdf->line( $columns["item_tax_total"]["start"], $height_position, $columns["item_tax_total"]["start"], $height_position + 14);
				$pdf->show_xy(currency_format($goods_tax_total_show, $order_currency), $columns["item_tax_total"]["start"], $height_position + 14, $columns["item_tax_total"]["width"] - 2, 12, "right");
			}
			if ($item_price_incl_tax_total_column) {
				$pdf->line( $columns["item_price_incl_tax_total"]["start"], $height_position, $columns["item_price_incl_tax_total"]["start"], $height_position + 14);
				$pdf->show_xy(currency_format($goods_total_incl_tax, $order_currency), $columns["item_price_incl_tax_total"]["start"], $height_position + 14, $columns["item_price_incl_tax_total"]["width"] - 2, 12, "right");
			}

			$height_position -= 6;
			$desc_length = $total_name_width - 5;
			$after_cart_price_start = $total_name_width + 40;
			$after_cart_price_length = 508 - $desc_length;
	  
			// show order coupons
			$pdf->setfont("helvetica", "", 8);
			$sql  = " SELECT * FROM " . $table_prefix . "orders_coupons ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " AND (order_item_id=0 OR order_item_id IS NULL) ";
			$dbi->query($sql);
			if ($dbi->next_record()) {
				do {
					$coupon_id = $dbi->f("coupon_id");
					$coupon_code = $dbi->f("coupon_code");
					$coupon_title = $dbi->f("coupon_title");
					$discount_amount = $dbi->f("discount_amount");
					$discount_tax_amount = $dbi->f("discount_tax_amount");
					if ($tax_prices_type == 1) {
						$discount_amount_excl_tax = $discount_amount - $discount_tax_amount;
						$discount_amount_incl_tax = $discount_amount;
					} else {
						$discount_amount_excl_tax = $discount_amount;
						$discount_amount_incl_tax = $discount_amount + $discount_tax_amount;
					}

					$coupon_height = $pdf->show_xy($coupon_title, 40, $height_position, $desc_length, 0, "right");
					if ($item_price_total_column) {
						$pdf->show_xy("-".currency_format($discount_amount_excl_tax, $order_currency), $columns["item_price_total"]["start"], $height_position, $columns["item_price_total"]["width"] - 2, 0, "right");
					}
					if ($item_tax_total_column) {
						$pdf->show_xy("-".currency_format($discount_tax_amount, $order_currency), $columns["item_tax_total"]["start"], $height_position, $columns["item_tax_total"]["width"] - 2, 0, "right");
					}
					if ($item_price_incl_tax_total_column) {
						$pdf->show_xy("-".currency_format($discount_amount_incl_tax, $order_currency), $columns["item_price_incl_tax_total"]["start"], $height_position, $columns["item_price_incl_tax_total"]["width"] - 2, 0, "right");
					}

					$height_position -= $coupon_height + 4;

				} while ($dbi->next_record());
			} 

			$pdf->setfont("helvetica", "B", 8);
			if ($total_discount > 0) {
				if ($tax_prices_type == 1) {
					$total_discount_excl_tax = $total_discount - $total_discount_tax;
					$total_discount_incl_tax = $total_discount;
				} else {
					$total_discount_excl_tax = $total_discount;
					$total_discount_incl_tax = $total_discount + $total_discount_tax;
				}
	
				$total_discount_excl_tax_formatted = "-" . currency_format($total_discount_excl_tax, $order_currency);
				$total_discount_incl_tax_formatted = "-" . currency_format($total_discount_incl_tax, $order_currency);
				$total_discount_tax_formatted      = "-" . currency_format($total_discount_tax, $order_currency);
				$pdf->show_xy(va_constant("TOTAL_DISCOUNT_MSG"), 40, $height_position, $desc_length, 0, "right");
				if ($item_price_total_column) {
					$pdf->show_xy($total_discount_excl_tax_formatted, $columns["item_price_total"]["start"], $height_position, $columns["item_price_total"]["width"] - 2, 0, "right");
				}
				if ($item_tax_total_column) {
					$pdf->show_xy($total_discount_tax_formatted, $columns["item_tax_total"]["start"], $height_position, $columns["item_tax_total"]["width"] - 2, 0, "right");
				}
				if ($item_price_incl_tax_total_column) {
					$pdf->show_xy($total_discount_incl_tax_formatted, $columns["item_price_incl_tax_total"]["start"], $height_position, $columns["item_price_incl_tax_total"]["width"] - 2, 0, "right");
				}
				$height_position -= 12;
			}
	  
			$pdf->setfont("helvetica", "", 8);
	  
			foreach ($cart_properties as $property_id => $property_values) {
				$property_name = strip_tags($property_values["name"]);
				$property_value = strip_tags($property_values["value"]);
				$property_price = $property_values["price"];
				$property_tax = $property_values["tax"];
				$property_price_excl_tax = $property_values["property_price_excl_tax"];
				$property_price_incl_tax = $property_values["property_price_incl_tax"];

				$property_tax_id = 0;
				$property_tax_free = $property_values["tax_free"];
				$property_line  = $property_name . " (" . $property_value . ")";
				if (strlen($property_price)){
					$price_formatted = currency_format($property_price, $order_currency);
				} else {
					$price_formatted = "";
				}
	  
				$property_height = $pdf->show_xy($property_line, 40, $height_position, $desc_length, 0, "right");
				if ($item_price_total_column && $property_price_excl_tax) {
					$pdf->show_xy(currency_format($property_price_excl_tax, $order_currency), $columns["item_price_total"]["start"], $height_position, $columns["item_price_total"]["width"] - 2, 0, "right");
				}
				if ($item_tax_total_column && $property_tax) {
					$pdf->show_xy(currency_format($property_tax, $order_currency), $columns["item_tax_total"]["start"], $height_position, $columns["item_tax_total"]["width"] - 2, 0, "right");
				}
				if ($item_price_incl_tax_total_column && $property_price_incl_tax) {
					$pdf->show_xy(currency_format($property_price_incl_tax, $order_currency), $columns["item_price_incl_tax_total"]["start"], $height_position, $columns["item_price_incl_tax_total"]["width"] - 2, 0, "right");
				}
				$height_position -= ($property_height + 4);
			}
	  
			// NEW SHIPPING STRUCTURE
			$orders_shipments = array(); 
			$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$order_shipping_id = $db->f("order_shipping_id");
					$shipping_cost = $db->f("shipping_cost");
					$points_cost = $db->f("points_cost");
					$shipping_tax_free = $db->f("tax_free");
					$orders_shipments[$order_shipping_id] = $db->Record;
					// calculate tax and total values
					$shipping_tax_id = 0;
					$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type, $tax_round);
					$shipping_tax_total = add_tax_values($order_tax_rates, $shipping_tax_values, "shipping", $tax_round);
					if ($tax_prices_type == 1) {
						$shipping_cost_excl_tax = $shipping_cost - $shipping_tax_total;
						$shipping_cost_incl_tax = $shipping_cost;
					} else {
						$shipping_cost_excl_tax = $shipping_cost;
						$shipping_cost_incl_tax = $shipping_cost + $shipping_tax_total;
					}
					$orders_shipments[$order_shipping_id]["shipping_cost_excl_tax"] = $shipping_cost_excl_tax;
					$orders_shipments[$order_shipping_id]["shipping_tax"] = $shipping_tax_total;
					$orders_shipments[$order_shipping_id]["shipping_cost_incl_tax"] = $shipping_cost_incl_tax;
				} while ($db->next_record());
			} else if ($old_shipping_type_desc) {
				// OLD SHIPPING STRUCTURE
				$order_shipping_id = 0;
				$shipping_desc = $old_shipping_type_desc;
				$shipping_cost = $old_shipping_cost;
				$points_cost = $old_shipping_points_amount;
				$shipping_tax_free = ($old_shipping_taxable) ? 0 : 1;

				// calculate tax and total values
				$shipping_tax_id = 0;
				$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$shipping_tax_total = add_tax_values($order_tax_rates, $shipping_tax_values, "shipping", $tax_round);
				if ($tax_prices_type == 1) {
					$shipping_cost_excl_tax = $shipping_cost - $shipping_tax_total;
					$shipping_cost_incl_tax = $shipping_cost;
				} else {
					$shipping_cost_excl_tax = $shipping_cost;
					$shipping_cost_incl_tax = $shipping_cost + $shipping_tax_total;
				}
				$orders_shipments[$order_shipping_id] = array(
					"shipping_desc" => $shipping_desc,
					"shipping_cost" => $shipping_cost,
					"points_cost" => $points_cost,
					"tax_free" => $shipping_tax_free,
					"shipping_cost_excl_tax" => $shipping_cost_excl_tax,
					"shipping_tax" => $shipping_tax_total,
					"shipping_cost_incl_tax" => $shipping_cost_incl_tax,
					"tracking_id" => $old_shipping_tracking_id,
				);
			}


			foreach ($orders_shipments as $order_shipping_id => $shipment) {
				if ($item_price_total_column) {
					$pdf->show_xy(currency_format($shipment["shipping_cost_excl_tax"], $order_currency), $columns["item_price_total"]["start"], $height_position, $columns["item_price_total"]["width"] - 2, 0, "right");
				}
				if ($item_tax_total_column) {
					$pdf->show_xy(currency_format($shipment["shipping_tax"], $order_currency), $columns["item_tax_total"]["start"], $height_position, $columns["item_tax_total"]["width"] - 2, 0, "right");
				}
				if ($item_price_incl_tax_total_column) {
					$pdf->show_xy(currency_format($shipment["shipping_cost_incl_tax"], $order_currency), $columns["item_price_incl_tax_total"]["start"], $height_position, $columns["item_price_incl_tax_total"]["width"] - 2, 0, "right");
				}
				$pdf->show_xy($shipment["shipping_desc"], 40, $height_position, $desc_length, 0, "right");
				if ($shipment["tracking_id"]) {
					$pdf->show_xy("Tracking code: ". $shipment["tracking_id"], 40, $height_position, $desc_length, 0, "left");
				}
				$height_position -= 12;
			}
			// show shipping properties
			foreach ($shipping_properties as $property_id => $property_values) {
				$property_name = strip_tags($property_values["name"]);
				$property_value = strip_tags($property_values["value"]);
	  
				$name_height = $pdf->show_xy($property_name, 40, $height_position, $desc_length, 0, "right");
				$value_height = $pdf->show_xy($property_value, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
				$property_height = ($name_height > $value_height) ? $name_height : $value_height;

				$height_position -= ($property_height + 4);
			}

			// show total weight of order
			if ($show_total_weight && $weight_total > 0) {
				$weight_total = round($weight_total, 4);
				$pdf->show_xy(va_constant("WEIGHT_TOTAL_MSG"), 40, $height_position, $desc_length, 0, "right");
				$pdf->show_xy($weight_total.$weight_measure, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
				$height_position -= 12;
			}
			// show total weight of order
			if ($show_total_actual_weight && $actual_weight_total > 0) {
				$actual_weight_total = round($actual_weight_total, 4);
				$pdf->show_xy(va_constant("WEIGHT_TOTAL_MSG")." (".va_constant("ACTUAL_WEIGHT_MSG").")", 40, $height_position, $desc_length, 0, "right");
				$pdf->show_xy($actual_weight_total.$weight_measure, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
				$height_position -= 12;
			}

			$pdf->setfont("helvetica", "B", 8);
			$height_position -= 10;
			$taxes_total = 0;

			// calculate tax for processing fee before tax calculations
			$processing_tax_id = 0;
			$processing_tax_values = get_tax_amount($order_tax_rates, "processing", $processing_fee, 1, $processing_tax_id, $processing_tax_free, $fee_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$processing_tax = add_tax_values($order_tax_rates, $processing_tax_values, "processing", $tax_round);

			// calculate taxes and exclude discount taxes
			if ($tax_available) {
				// get taxes sums for further calculations
				$taxes_sum = 0; $discount_tax_sum = $total_discount_tax;
				foreach($order_tax_rates as $tax_id => $tax_info) {
					$tax_cost = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;
					$taxes_sum += va_round($tax_cost, $currency["decimals"]);
				}

				$tax_number = 0;
				foreach($order_tax_rates as $tax_id => $tax_info) {
					$tax_number++;
					$tax_name = get_translation($tax_info["tax_name"]);
					$current_tax_free = isset($tax_info["tax_free"]) ? $tax_info["tax_free"] : 0;
					//if ($tax_free) { $current_tax_free = true; }
					$tax_percent = $tax_info["tax_percent"];
					$shipping_tax_percent = $tax_info["types"]["shipping"]["tax_percent"];
					$tax_types = $tax_info["types"];
					$tax_cost = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;

					if ($total_discount_tax) {
						// in case of order coupons decrease taxes value 
						if ($tax_number == sizeof($order_tax_rates)) {
							$tax_discount = $discount_tax_sum;
						} else {
							$tax_discount = round(($tax_cost * $total_discount_tax) / $taxes_sum, 2);
						}
						$discount_tax_sum -= $tax_discount;
						$tax_cost -= $tax_discount;
					}

					$taxes_total += va_round($tax_cost, $currency["decimals"]);

					if ($tax_cost != 0) {
						$tax_cost_formatted = currency_format($tax_cost, $order_currency);
						$pdf->show_xy($tax_name, 40, $height_position, $desc_length, 0, "right");
						$pdf->show_xy($tax_cost_formatted, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
						$height_position -= 12;
					}
				}
			}
			// end tax calculation
	  
			if ($credit_amount != 0) {
				$credit_amount_formatted = "-".currency_format($credit_amount, $order_currency);
				$pdf->show_xy(va_constant("CREDIT_AMOUNT_MSG"), 40, $height_position, $desc_length, 0, "right");
				$pdf->show_xy($credit_amount_formatted, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
				$height_position -= 12;
			}
	  
			if ($processing_fee != 0) {
				$processing_fee_formatted = currency_format($processing_fee, $order_currency);
				$pdf->show_xy(va_constant("PROCESSING_FEE_MSG"), 40, $height_position, $desc_length, 0, "right");
				$pdf->show_xy($processing_fee_formatted, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
				$height_position -= 12;
			}
	  
			$height_position -= 12;
			$order_total_formatted = currency_format($order_total, $order_currency);
			$pdf->setfont("helvetica", "BU", 8);
			$pdf->show_xy(va_constant("PROD_TOTAL_COLUMN"), 40, $height_position, $desc_length, 0, "right");
			$pdf->show_xy($order_total_formatted, $after_cart_price_start, $height_position, $after_cart_price_length, 0, "right");
			$height_position -= 12;
	  
			set_invoice_footer($pdf, $height_position, $invoice, $payment_id);
			$pdf->end_page();
			// end of current order generation
		}
	
		$pdf_buffer = "";
		if ($return_pdf) {
			$pdf_buffer = $pdf->get_buffer();
		}
		return $pdf_buffer;
	}

	function begin_new_page(&$pdf, &$height_position, &$page_number, $invoice)
	{
		$page_number++;
		$pdf_library = isset($invoice["pdf_page_type"]) ? $invoice["pdf_page_type"] : "A4";
		if($pdf_library == "LETTER"){
			$pdf->begin_page(612, 792);
			$height_position = 750;
		}else{
			$pdf->begin_page(595, 842);
			$height_position = 800;
		}
	
		$invoice_page_number = get_setting_value($invoice, "invoice_page_number", 0);
		$pdf->setfont ("helvetica", "", 8);
		if ($invoice_page_number) {
			$pdf->show_xy("- " . $page_number . " -", 40, 20, 555, 0, "center");
		}
	}
	
	function set_invoice_header(&$pdf, &$height_position, $invoice, $r)
	{
		global $db, $table_prefix, $date_show_format, $settings;
	
		$tmp_dir  = get_setting_value($settings, "tmp_dir", ".");

		$order_id = $r->get_value("order_id");
		$invoice_number = $r->get_value("invoice_number");
		$invoice_copy_number = $r->get_value("invoice_copy_number");
		$invoice_copy_number++;
		// update invoice copy number
		$sql = " UPDATE " . $table_prefix . "orders ";
		$sql.= " SET invoice_copy_number=" . $db->tosql($invoice_copy_number, INTEGER);
		$sql.= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		if (!$invoice_number) { $invoice_number = $order_id; }
		$order_placed_date = $r->get_value("order_placed_date");
		$order_date = va_date($date_show_format, $order_placed_date);
		$order_status_type = $r->get_value("order_status_type");
	
		$copy_number_option = get_setting_value($invoice, "invoice_copy_number", 0);
		if ($copy_number_option) {
			$pdf->setfont("helvetica", "", 8);
			if ($invoice_copy_number == 1) {
				$pdf->show_xy(va_constant("ORIGINAL_COPY_MSG"), 40, $height_position, 515, 0, "right");
			} else {
				$copy_msg = str_replace("{copy_number}",  $invoice_copy_number, va_constant("COPY_NUMBER_MSG"));
				$pdf->show_xy($copy_msg, 40, $height_position, 515, 0, "right");
			}
			$height_position -= 14;
		}

		$logo_height = 0;
		$start_position = $height_position;
		$invoice_logo = get_setting_value($invoice, "invoice_logo");
		$invoice_logo_dpi = get_setting_value($invoice, "invoice_logo_dpi", 72);
		if ($invoice_logo_dpi <= 0) { $invoice_logo_dpi = 72; }
		if ($invoice_logo && !file_exists($invoice_logo)) {
			if (preg_match("/^\.\.\//", $invoice_logo) && @file_exists(preg_replace("/^\.\.\//", "", $invoice_logo))) {
				$invoice_logo = preg_replace("/^\.\.\//", "", $invoice_logo);
			} else if (@file_exists("../".$invoice_logo)) {
				$invoice_logo = "../" . $invoice_logo;
			} else {
				$invoice_logo = "";
			}
		}
		if ($invoice_logo) {
			$logo_size = @getimagesize($invoice_logo);
			if (is_array($logo_size)) {
				$logo_width = intval($logo_size[0]);
				$logo_height = intval($logo_size[1]);
				// convert image size accoridngly to selected DPI value
				$logo_width = $logo_width * (72 / $invoice_logo_dpi);
				$logo_height = $logo_height * (72 / $invoice_logo_dpi);
				if ($logo_width > 0 && $logo_height > 0) {
					if (preg_match("/((\.jpeg)|(\.jpg))$/i", $invoice_logo)) {
						$image_type = "jpeg";
					} elseif (preg_match("/(\.gif)$/i", $invoice_logo)) {
						$image_type = "gif";
					} elseif (preg_match("/((\.tif)|(\.tiff))$/i", $invoice_logo)) {
						$image_type = "tiff";
					} elseif (preg_match("/(\.png)$/i", $invoice_logo)) {
						$image_type = "png";
					}
				  $pdf->place_image($invoice_logo, 555 - $logo_width, $height_position - $logo_height, $image_type, "", $logo_width, $logo_height);
				}
			}
		}	

		if (isset($invoice["invoice_header"])) {
			$invoice_header = get_translation(strip_tags($invoice["invoice_header"]));
			if (strlen($invoice_header)) {
				$pdf->setfont("helvetica", "", 10);
				$header_lines = explode("\n", $invoice_header);
				for ($i = 0; $i < sizeof($header_lines); $i++) {
					$header_line = $header_lines[$i];
					$line_height = $pdf->show_xy($header_line, 40, $height_position, 200, 0, "left");
					$height_position -= ($line_height + 2);
				}
			}
		}
	
		if ($order_status_type == "CREDIT_NOTE") {
			$date_width = $pdf->stringwidth(va_constant("CREDIT_DATE_MSG").":");
			$number_width = $pdf->stringwidth(va_constant("CREDIT_NUMBER_MSG").":");
		} else {
			$date_width = $pdf->stringwidth(va_constant("INVOICE_DATE_MSG").":");
			$number_width = $pdf->stringwidth(va_constant("INVOICE_NUMBER_MSG").":");
		}
		$date_number_width = ($number_width > $date_width) ? $number_width : $date_width;
		$date_number_x = 60 + $date_number_width;

		$height_position -= 12;
		$pdf->setfont("helvetica", "B", 10);
		if ($order_status_type == "CREDIT_NOTE") {
			$pdf->show_xy(va_constant("CREDIT_DATE_MSG"). ":", 40, $height_position, 90, 0, "left");
		} else {
			$pdf->show_xy(va_constant("INVOICE_DATE_MSG"). ":", 40, $height_position, 90, 0, "left");
		}
		$pdf->setfont("helvetica", "", 10);
		$pdf->show_xy($order_date, $date_number_x, $height_position, 200, 0, "left");
	
		$height_position -= 16;
		$pdf->setfont("helvetica", "B", 10);
		if ($order_status_type == "CREDIT_NOTE") {
			$pdf->show_xy(va_constant("CREDIT_NUMBER_MSG"). ":", 40, $height_position, 90, 0, "left");
		} else {
			$pdf->show_xy(va_constant("INVOICE_NUMBER_MSG"). ":", 40, $height_position, 90, 0, "left");
		}
		$invoice_number_show = get_setting_value($invoice, "invoice_number_show");
		if ($invoice_number_show == 2) {
			$image_type = "png";
			$item_code_image = $tmp_dir . "tmp_" . md5(uniqid(rand(), true)) . "." . $image_type;
			save_barcode ($item_code_image, $invoice_number, $image_type, "code128");
			$image_size = @GetImageSize($item_code_image);
			$image_width = $image_size[0];
			$image_height = $image_size[1];
			$height_position -= $image_height;

		  $pdf->place_image($item_code_image, $date_number_x - 10, $height_position, $image_type, "", $image_width, $image_height);
			unlink($item_code_image);
		} else {
			$pdf->setfont("helvetica", "", 10);
			$pdf->show_xy($invoice_number, $date_number_x, $height_position, 200, 0, "left");
		}
	
		if ($height_position > ($start_position - $logo_height)) {
			$height_position = $start_position - $logo_height;
		}
	
	}
	
	function set_table_header(&$pdf, &$height_position, $r, $columns)
	{
		$tax_name = get_translation($r->get_value("tax_name"));
		$tax_percent = $r->get_value("tax_percent");
	
		$pdf->setlinewidth(1.0);
	
		$pdf->setfont("helvetica", "B", 8);
		$height_position -= 12;
	
		$max_height = 12;
		foreach ($columns as $column_name => $values) {
			if ($values["active"]) {
				$column_height = $pdf->show_xy($values["name"], $values["start"] + 1, $height_position - 2, $values["width"] - 2, 0, "center");
				if ($column_height > $max_height) {
					$max_height = $column_height;
				}
			}
		}
		$max_height += 6;
		$pdf->rect ( 40, $height_position - $max_height, 515, $max_height);
		foreach ($columns as $column_name => $values) {
			if ($values["active"]) {
				$pdf->line( $values["start"], $height_position - $max_height, $values["start"], $height_position);
			}
		}
		$height_position -= $max_height;
	}
	
	
	
	function set_user_info(&$pdf, &$height_position, $r, $personal_number, $delivery_number, $personal_properties, $delivery_properties, $currency)
	{
		$property_tax_percent = $r->get_value("tax_percent");
		$order_status_type = $r->get_value("order_status_type");

		$pdf->setfont("helvetica", "BU", 10);
		$height_position -= 24;

		if ($order_status_type == "CREDIT_NOTE") {
			$invoice_height = $pdf->show_xy(va_constant("CREDIT_TO_MSG").":", 40, $height_position, 250, 0, "left");
		} else {
			$invoice_height = $pdf->show_xy(va_constant("INVOICE_TO_MSG").":", 40, $height_position, 250, 0, "left");
		}
		$delivery_height = 0;
		if ($delivery_number > 0) {
			$delivery_height = $pdf->show_xy(va_constant("DELIVERY_TO_MSG").":", 300, $height_position, 250, 0, "left");
		}
		$max_height = max($invoice_height, $delivery_height);

		$height_position -= ($max_height + 2); // initial position for first row with data
		$personal_height = $height_position;
		$pdf->setfont("helvetica", "", 10);

		$name = "";
		if ($r->parameters["name"][SHOW]) {
			$name = $r->get_value("name");
		}
		if ($r->parameters["first_name"][SHOW] && $r->get_value("first_name")) {
			if ($name) { $name .= " "; }
			$name .= $r->get_value("first_name");
		}
		if ($r->parameters["last_name"][SHOW] && $r->get_value("last_name")) {
			if ($name) { $name .= " "; }
			$name .= $r->get_value("last_name");
		}
	
		if (strlen($name)) {
			$row_height = $pdf->show_xy($name, 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["company_id"][SHOW] && $r->get_value("company_id")) {
			$row_height = $pdf->show_xy($r->get_value("company_id"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["company_name"][SHOW] && $r->get_value("company_name")) {
			$row_height = $pdf->show_xy($r->get_value("company_name"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["address1"][SHOW] && $r->get_value("address1")) {
			$row_height = $pdf->show_xy($r->get_value("address1"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["address2"][SHOW] && $r->get_value("address2")) {
			$row_height = $pdf->show_xy($r->get_value("address2"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		$city = ""; $address_line = "";
		if ($r->parameters["city"][SHOW]) {
			$city = $r->get_value("city");
		}
		if ($r->parameters["province"][SHOW]) {
			$address_line = $r->get_value("province");
		}
		if ($r->parameters["state_id"][SHOW] && $r->get_value("state_id")) {
			if ($address_line) { $address_line .= " "; }
			$address_line .= $r->get_value("state_id");
		}
		if ($r->parameters["zip"][SHOW] && $r->get_value("zip")) {
			if ($address_line) { $address_line .= " "; }
			$address_line .= $r->get_value("zip");
		}
		if ($city && $address_line) {
			$address_line = $city . ", " . $address_line;
		} elseif ($city) {
			$address_line = $city;
		}
	
		if (strlen($address_line)) {
			$row_height = $pdf->show_xy($address_line, 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["country_id"][SHOW] && $r->get_value("country_id")) {
			$row_height = $pdf->show_xy($r->get_value("country_id"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["phone"][SHOW] && !$r->is_empty("phone")) {
			$row_height = $pdf->show_xy(PHONE_FIELD.": ".$r->get_value("phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["daytime_phone"][SHOW] && !$r->is_empty("daytime_phone")) {
			$row_height = $pdf->show_xy(DAYTIME_PHONE_FIELD.": ".$r->get_value("daytime_phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["evening_phone"][SHOW] && !$r->is_empty("evening_phone")) {
			$row_height = $pdf->show_xy(EVENING_PHONE_FIELD.": ".$r->get_value("evening_phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["cell_phone"][SHOW] && !$r->is_empty("cell_phone")) {
			$row_height = $pdf->show_xy(CELL_PHONE_FIELD.": ".$r->get_value("cell_phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["fax"][SHOW] && !$r->is_empty("fax")) {
			$row_height = $pdf->show_xy(FAX_FIELD.": ".$r->get_value("fax"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["email"][SHOW] && strlen($r->get_value("email"))) {
			$row_height = $pdf->show_xy(EMAIL_FIELD.": " . $r->get_value("email"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		foreach ($personal_properties as $property_id => $property_values) {
			$property_price = $property_values["price"];
			$property_tax_id = 0;
			$property_tax = get_tax_amount("", 0, $property_price, 1, $property_tax_id, $property_values["tax_free"], $property_tax_percent);
			if (floatval($property_price) != 0.0) {
				$property_price_text = " " . currency_format($property_price, $currency, $property_tax);
			} else {
				$property_price_text = "";
			}
			$property_height = $pdf->show_xy($property_values["name"] . ": " . $property_values["value"] . $property_price_text, 40, $personal_height, 250, 0, "left");
			$personal_height -= $property_height;
		}
	
	
		$delivery_height = $height_position;
	
		$delivery_name = "";
		if ($r->parameters["delivery_name"][SHOW]) {
			$delivery_name = $r->get_value("delivery_name");
		}
		if ($r->parameters["delivery_first_name"][SHOW] && $r->get_value("delivery_first_name")) {
			if ($delivery_name) { $delivery_name .= " "; }
			$delivery_name .= $r->get_value("delivery_first_name");
		}
		if ($r->parameters["delivery_last_name"][SHOW] && $r->get_value("delivery_last_name")) {
			if ($delivery_name) { $delivery_name .= " "; }
			$delivery_name .= $r->get_value("delivery_last_name");
		}
	
		if (strlen($delivery_name)) {
			$row_height = $pdf->show_xy($delivery_name, 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_company_id"][SHOW] && $r->get_value("delivery_company_id")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_company_id"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_company_name"][SHOW] && $r->get_value("delivery_company_name")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_company_name"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_address1"][SHOW] && $r->get_value("delivery_address1")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_address1"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_address2"][SHOW] && $r->get_value("delivery_address2")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_address2"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		$delivery_city = ""; $delivery_address = "";
		if ($r->parameters["delivery_city"][SHOW]) {
			$delivery_city = $r->get_value("delivery_city");
		}
		if ($r->parameters["delivery_province"][SHOW]) {
			$delivery_address = $r->get_value("delivery_province");
		}
		if ($r->parameters["delivery_state_id"][SHOW] && $r->get_value("delivery_state_id")) {
			if ($delivery_address) { $delivery_address .= " "; }
			$delivery_address .= $r->get_value("delivery_state_id");
		}
		if ($r->parameters["delivery_zip"][SHOW] && $r->get_value("delivery_zip")) {
			if ($delivery_address) { $delivery_address .= " "; }
			$delivery_address .= $r->get_value("delivery_zip");
		}
		if ($delivery_city && $delivery_address) {
			$delivery_address = $delivery_city . ", " . $delivery_address;
		} elseif ($delivery_city) {
			$delivery_address = $delivery_city;
		}
	
		if (strlen($delivery_address)) {
			$row_height = $pdf->show_xy($delivery_address, 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_country_id"][SHOW] && $r->get_value("delivery_country_id")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_country_id"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_phone"][SHOW] && !$r->is_empty("delivery_phone")) {
			$row_height = $pdf->show_xy(PHONE_FIELD.": ".$r->get_value("delivery_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_daytime_phone"][SHOW] && !$r->is_empty("delivery_daytime_phone")) {
			$row_height = $pdf->show_xy(DAYTIME_PHONE_FIELD.": ".$r->get_value("delivery_daytime_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_evening_phone"][SHOW] && !$r->is_empty("delivery_evening_phone")) {
			$row_height = $pdf->show_xy(EVENING_PHONE_FIELD.": ".$r->get_value("delivery_evening_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_cell_phone"][SHOW] && !$r->is_empty("delivery_cell_phone")) {
			$row_height = $pdf->show_xy(CELL_PHONE_FIELD.": ".$r->get_value("delivery_cell_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_fax"][SHOW] && !$r->is_empty("delivery_fax")) {
			$row_height = $pdf->show_xy(FAX_FIELD.": ".$r->get_value("delivery_fax"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_email"][SHOW] && strlen($r->get_value("delivery_email"))) {
			$row_height = $pdf->show_xy(EMAIL_FIELD.": " . $r->get_value("delivery_email"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		foreach ($delivery_properties as $property_id => $property_values) {
			$property_price = $property_values["price"];
			$property_tax = get_tax_amount("", 0, $property_price, 0, 1, $property_values["tax_free"], $property_tax_percent);
			if (floatval($property_price) != 0.0) {
				$property_price_text = " " . currency_format($property_price, $currency, $property_tax);
			} else {
				$property_price_text = "";
			}
			$property_height = $pdf->show_xy($property_values["name"] . ": " . $property_values["value"] . $property_price_text, 300, $delivery_height, 250, 0, "left");
			$delivery_height -= $property_height;
		}
	
	
		if ($personal_height > $delivery_height) {
			$height_position = $delivery_height;
		} else {
			$height_position = $personal_height;
		}
		$height_position -= 12;
	
	}
	
	
	function set_invoice_footer(&$pdf, &$height_position, $invoice, $payment_id)
	{
		global $db, $table_prefix; 

		$invoice_payment_gateway = get_setting_value($invoice, "invoice_payment_gateway", 0);
		$invoice_payment_info = get_setting_value($invoice, "invoice_payment_info", 0);
		if ($invoice_payment_gateway || $invoice_payment_info) {
			$sql  = " SELECT payment_name, user_payment_name, payment_info ";
			$sql .= " FROM ".$table_prefix."payment_systems ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$payment_name = get_translation(strip_tags($db->f("payment_name")));
				$user_payment_name = get_translation(strip_tags($db->f("user_payment_name")));
				$payment_info = get_translation(strip_tags($db->f("payment_info")));
				if ($invoice_payment_gateway) {
					$height_position -= 12;
					$pdf->setfont("helvetica", "b", 10);
					if (!$user_payment_name) { $user_payment_name = $payment_name; }
					$title_width = $pdf->stringwidth(va_constant("PAYMENT_GATEWAY_MSG").":");
					$pdf->show_xy(va_constant("PAYMENT_GATEWAY_MSG").":", 40, $height_position, 515, 0, "left");
					$pdf->setfont("helvetica", "", 10);
					$line_height = $pdf->show_xy($user_payment_name, 45+$title_width, $height_position, 515, 0, "left");
					$height_position -= ($line_height + 2);
				}

				if ($invoice_payment_info && $payment_info) {
					$height_position -= 12;
					$pdf->setfont("helvetica", "b", 10);
					$line_height = $pdf->show_xy(va_constant("PAYMENT_INFO_MSG").":", 40, $height_position, 515, 0, "left");
					$height_position -= ($line_height + 2);

					$pdf->setfont("helvetica", "", 9);
					$payment_info = str_replace(array("\r\n", "\r"), "\n", $payment_info);
					$info_lines = explode("\n", $payment_info);
					for ($i = 0; $i < sizeof($info_lines); $i++) {
						$info_line = $info_lines[$i];
						if ($info_line) {
							$line_height = $pdf->show_xy($info_line, 40, $height_position, 515, 0, "left");
							$height_position -= ($line_height + 2);
						} else {
							$height_position -= 12;
						}
					}
				}
			}
		}


		$pdf->setfont("helvetica", "", 8);
		if (isset($invoice["invoice_footer"])) {
			$invoice_footer = get_translation(strip_tags($invoice["invoice_footer"]));
			if (strlen($invoice_footer)) {
				$footer_lines = explode("\n", $invoice_footer);
				$height_position = 40 + sizeof($footer_lines) * 10;
				for ($i = 0; $i < sizeof($footer_lines); $i++) {
					$height_position -= 10;
					$footer_line = $footer_lines[$i];
					$pdf->show_xy($footer_line, 40, $height_position, 555, 0, "center");
				}
			}
		}
	
	}
