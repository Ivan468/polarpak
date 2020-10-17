<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order_printable.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	check_admin_security("order_profile");

	$pdf_libraries = 
		array( 
			array(1, PHP_BASED_MSG), 
			array(2, PDFLIB_BASED_MSG)
			);

	$pdf_page_types = 
		array( 
			array("A4", "A4"), 
			array("LETTER", "Letter")
			);

	$message_types = 
		array( 
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$code_options = 
		array( 
			array(0, DONT_SHOW_MSG), 
			array(1, SHOW_AS_TEXT_MSG),
			array(2, SHOW_AS_BARCODE_MSG),
		);

	$show_types = 
		array( 
			array(1, SHOW_AS_TEXT_MSG),
			array(2, SHOW_AS_BARCODE_MSG),
		);

	$prod_image_types =
		array(
			array(0, DONT_SHOW_IMAGE_MSG),
			array(1, IMAGE_TINY_MSG),
			array(2, IMAGE_SMALL_MSG),
		);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_order_printable.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_orders_href", "admin_orders.php");
	$t->set_var("admin_order_printable_href", "admin_order_printable.php");

	$r = new VA_Record($table_prefix . "global_settings");

	$r->add_radio("pdf_lib", INTEGER, $pdf_libraries);
	$r->add_radio("pdf_page_type", TEXT, $pdf_page_types);

	// invoice settings
	$r->add_textbox("invoice_logo",   TEXT);
	$r->add_textbox("invoice_logo_dpi",   INTEGER, va_message("IMAGE_LOGO_DPI_MSG"));
	$r->change_property("invoice_logo_dpi", MIN_VALUE, 36);
	$r->change_property("invoice_logo_dpi", MAX_VALUE, 1200);
	$r->add_radio("invoice_number_show", INTEGER, $show_types);
	$r->add_textbox("invoice_header", TEXT);
	$r->add_textbox("invoice_side_header", TEXT);
	$r->add_textbox("invoice_footer", TEXT);

	$r->add_checkbox("invoice_copy_number", INTEGER);
	$r->add_checkbox("invoice_page_number", INTEGER);
	$r->add_checkbox("invoice_packing_slip", INTEGER);
	$r->add_checkbox("invoice_payment_gateway", INTEGER);
	$r->add_checkbox("invoice_payment_info", INTEGER);

	// packing slip settings
	$r->add_textbox("packing_logo",   TEXT);
	$r->add_textbox("packing_logo_dpi",   INTEGER, va_message("IMAGE_LOGO_DPI_MSG"));
	$r->change_property("packing_logo_dpi", MIN_VALUE, 36);
	$r->change_property("packing_logo_dpi", MAX_VALUE, 1200);
	$r->add_radio("packing_number_show", INTEGER, $show_types);
	$r->add_select("packing_image", INTEGER, $prod_image_types);
	$r->add_radio("item_code_packing", INTEGER, $code_options);
	$r->add_radio("manufacturer_code_packing", INTEGER, $code_options);
	$r->add_checkbox("item_weight_packing", INTEGER);
	$r->add_checkbox("actual_weight_packing", INTEGER);
	$r->add_checkbox("total_weight_packing", INTEGER);
	$r->add_checkbox("total_actual_weight_packing", INTEGER);
	$r->add_checkbox("sc_properties_packing", INTEGER);
	$r->add_checkbox("shipping_method_packing", INTEGER);
	$r->add_checkbox("sw_orders_coupons_ps", INTEGER);

	$r->add_textbox("packing_header", TEXT);
	$r->add_textbox("packing_footer", TEXT);

	// global products settings
	$pr = new VA_Record($table_prefix . "global_settings");
	$pr->add_checkbox("item_code_invoice", INTEGER);
	$pr->add_checkbox("manufacturer_code_invoice", INTEGER);
	$pr->add_checkbox("item_weight_invoice", INTEGER);
	$pr->add_checkbox("actual_weight_invoice", INTEGER);
	$pr->add_checkbox("total_weight_invoice", INTEGER);
	$pr->add_checkbox("total_actual_weight_invoice", INTEGER);
	// columns for invoice page
	$pr->add_checkbox("invoice_ordinal_number", INTEGER);
	$pr->add_select("invoice_item_image", INTEGER, $prod_image_types);
	$pr->add_checkbox("invoice_item_name", INTEGER);
	$pr->add_checkbox("invoice_item_price", INTEGER);
	$pr->add_checkbox("invoice_item_tax_percent", INTEGER);
	$pr->add_checkbox("invoice_item_tax", INTEGER);
	$pr->add_checkbox("invoice_item_price_incl_tax", INTEGER);
	$pr->add_checkbox("invoice_item_quantity", INTEGER);
	$pr->add_checkbox("invoice_item_price_total", INTEGER);
	$pr->add_checkbox("invoice_item_tax_total", INTEGER);
	$pr->add_checkbox("invoice_item_price_incl_tax_total", INTEGER);


	$r->get_form_values();
	$pr->get_form_values();

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$param_site_id = get_session("session_site_id");
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if(!strlen($return_page)) $return_page = "admin_orders.php";
	if(strlen($operation))
	{
		if($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();
		if(!strlen($r->errors))
		{
			$sql  = " DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='printable'";
			$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
			$db->query($sql);

			$setting_name_where = "";
			foreach($pr->parameters as $key => $value) {
				if ($setting_name_where) { $setting_name_where .= " OR "; }
				$setting_name_where .= "setting_name=" . $db->tosql($key, TEXT);
			}
			if ($setting_name_where) {
				$sql  = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='products'";
				$sql .= " AND (" . $setting_name_where . ") ";
				$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
				$db->query($sql);
			}
			foreach($r->parameters as $key => $value) {
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'printable', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
				$sql .= $db->tosql($param_site_id,INTEGER) . ") ";

				$db->query($sql);
			}
			// addded global settings
			foreach($pr->parameters as $key => $value)
			{
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'products', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
				$sql .= $db->tosql($param_site_id,INTEGER) . ") ";

				$db->query($sql);
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	else // get order_info settings
	{
		foreach($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='printable' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
		foreach($pr->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='products' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$pr->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();
	$pr->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));
	
	// set styles for tabs
	$tabs = array(
		"general" => array("title" => PDF_SETTINGS_MSG), 
		"invoice" => array("title" => INVOICE_PAGE_SETTINGS_MSG), 
		"packing" => array("title" => PACKING_SLIP_PAGE_SETTINGS_MSG), 
	);

	parse_admin_tabs($tabs, $tab, 5);

	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist", false);
	}	

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>