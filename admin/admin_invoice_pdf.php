<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_invoice_pdf.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "includes/invoice_functions.php");
	include_once($root_folder_path . "includes/packing_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$printable = get_settings("printable");
	$invoice_packing_slip = get_setting_value($printable, "invoice_packing_slip", 0);

	$order_id = get_param("order_id");	
	$ids = get_param("ids");
	if ($order_id) {
		$ids = $order_id;
	}

	if ($invoice_packing_slip) {
		pdf_invoice($ids, array("return_pdf" => 0));
		$buffer = pdf_packing_slip($ids, array("new_pdf" => 0));
		$length = strlen($buffer);
	} else {
		$buffer = pdf_invoice($ids);
		$length = strlen($buffer);
	}

	$pdf_filename = "invoice_" . str_replace(",", "_", $ids) . ".pdf";
	header("Pragma: private");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	header("Content-Length: " . $length);
	header("Content-Disposition: attachment; filename=" . $pdf_filename);
	header("Content-Transfer-Encoding: binary");

	echo $buffer;
