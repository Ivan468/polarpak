<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  mistercash_check.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	/**
	 *
	 * Mollie's Mistercash (https://www.mollie.nl/) validation handler by www.viart.com
	 * @documentation https://www.mollie.nl/files/documentatie/payments-api-en.html
	 *
	 */

	include_once($root_folder_path . "includes/var_definition.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/date_functions.php");
	include_once($root_folder_path . "includes/common_functions.php");
	include_once($root_folder_path . "includes/va_functions.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");

	$is_admin_path = true;
	$root_folder_path = "../";


	$t = new VA_Template($settings["templates_dir"]);

	$id = $_POST["id"];

	$check_url = "https://api.mollie.nl/v1/payments/" . $id;

	$ch = curl_init();

	// Set query data here with the URL
	curl_setopt($ch, CURLOPT_URL, $check_url); 

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, '10');
	$data = trim(curl_exec($ch));
	curl_close($ch);

	if (!($response_object = @json_decode($data))){
		$error_message = 'Cannot read payment system answer.';
	}

	//if data returned correctly:
	$order_id = $response_object->metadata->order_id;
	$post_parameters = ""; 
	$payment_params  = array(); 
	$pass_parameters = array(); 
	$pass_data       = array(); 
	$variables       = array();
	get_payment_parameters($order_id, $payment_params, $pass_parameters, $post_parameters, $pass_data, $variables, "final");
	$error_message = "";

	$t->set_vars($variables);
	
	$order_final = array();
	
	$setting_type = "order_final_" . $payment_id;
	
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	
	if ($order_site_id) {
	
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
		
		$sql .= " ORDER BY site_id ASC ";
		
	} else {
	
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	
	while ($db->next_record()) {
	
		$order_final[$db->f("setting_name")] = $db->f("setting_value");
		
	}

	if ($order_id) {   
		// get statuses
		$success_status_id = get_setting_value($order_final, "success_status_id", "");
		$pending_status_id = get_setting_value($order_final, "pending_status_id", "");
		$failure_status_id = get_setting_value($order_final, "failure_status_id", "");

		
		//update status
		$payment_status = $response_object->status;
		if ($payment_status == "paid" || $payment_status == "paidout" || $payment_status == "refunded") { 

			//success                            
		} elseif ($payment_status == "cancelled" || $payment_status == "expired")  {
			//error   
			 $error_message = "Molly: Order cancelled or expired";
		} else {
			//unknown   
			$pending_message = "Molly: Order unknown response";
		}


		// update order status
		$is_failed = false; $is_pending = false; $is_success = false;
		if (strlen($error_message)) {
			$is_failed = true;
			$message_type = "failure";
			$t->set_var("error_desc", $error_message);
			$t->set_var("error_message", $error_message);
			$order_status = $failure_status_id;
			if (strtolower($payment_status) == "refunded") {
				$sql  = " SELECT status_id FROM " . $table_prefix . "order_statuses ";
				$sql .= " WHERE status_type='REFUNDED' ";
				$db->query($sql);
				if ($db->next_record()) {
					$order_status = $db->f("status_id");
				}
			}
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
			$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " , is_placed=1 ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;

		} else if (strlen($pending_message)) {
			$is_pending = true;
			$message_type = "pending";
			$t->set_var("pending_desc", $pending_message);
			$t->set_var("pending_message", $pending_message);
			$order_status = $pending_status_id;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET pending_message=" . $db->tosql($pending_message, TEXT);
			$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " , is_placed=1 ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		} else {
			$is_success = true;
			$message_type = "success";
			$order_status = $success_status_id;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET error_message=''";
			$sql .= " , pending_message=''";
			$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " , is_placed=1 ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		}
		$db->query($sql);

		// update order status
		update_order_status($order_id, $order_status, true, "", $status_error);

	} else {
		$error_message = "Molly: No order found";
		exit;
	}
	
?>