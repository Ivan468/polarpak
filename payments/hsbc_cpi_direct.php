<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  hsbc_cpi_direct.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * The Cardholder Payment Interface (CPI) within HSBC Secure ePayments (http://www.hsbc.com/) 
 * transaction handler by www.viart.com
 */
	$is_admin_path = true;
	$root_folder_path = "../";
	define("INSTALLED", true);
	include_once($root_folder_path . "includes/common.php");	
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path . "payments/hsbc_cpi_functions.php");
	
	checkOrder();
	$t = new VA_Template($settings["templates_dir"]);
	
	if ($order_id && $variables) {
		$success_status_id = $variables["success_status_id"];
		$pending_status_id = $variables["pending_status_id"];
		$failure_status_id = $variables["failure_status_id"];	
		if (strlen($error_message)) {
			update_order_status($order_id, $failure_status_id , true, "", $error_message);
		} elseif (strlen($success_message)) {
			update_order_status($order_id, $success_status_id, true, "", "");
		} else {
			update_order_status($order_id, $pending_status_id , true, "", "");
		}
	}	
?>
<html></html>