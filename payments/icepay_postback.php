<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  icepay_postback.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 *  IcePay (www.icepay.eu) transaction handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");

	$status                = get_param("Status");
	$statuscode            = get_param("StatusCode");
	$merchant              = get_param("Merchant");
	$orderid               = get_param("OrderID");
	$paymentid             = get_param("PaymentID");
	$reference             = get_param("Reference");
	$transactionid         = get_param("TransactionID");
	$ConsumerName          = get_param("ConsumerName");
	$ConsumerAccountNumber = get_param("ConsumerAccountNumber");
	$ConsumerAddress       = get_param("ConsumerAddress");
	$ConsumerHouseNumber   = get_param("ConsumerHouseNumber");
	$ConsumerCity          = get_param("ConsumerCity");
	$ConsumerCountry       = get_param("ConsumerCountry");
	$ConsumerEmail         = get_param("ConsumerEmail");
	$ConsumerPhoneNumber   = get_param("ConsumerPhoneNumber");
	$ConsumerIPAddress     = get_param("ConsumerIPAddress");
	$Amount                = get_param("Amount");
	$Currency              = get_param("Currency");
	$Duration              = get_param("Duration");
	$PaymentMethod         = get_param("PaymentMethod");
	$checksum              = get_param("Checksum");

	if ($orderid) {
		$sql  = " SELECT order_id, success_message, pending_message, error_message FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($orderid, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id", INTEGER);
			$success_message = $db->f("success_message");
			$pending_message = $db->f("pending_message");
			$error_message = $db->f("error_message");

			$post_parameters = "";
			$payment_parameters = array();
			$pass_parameters = array(); 
			$pass_data = array(); 
			$variables = array();
			get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

			$source  = $payment_parameters['Encryptioncode']."|";
			$source .= $payment_parameters['IC_Merchant']."|";
			$source .= $status."|";
			$source .= $statuscode."|";
			$source .= $orderid."|";
			$source .= $paymentid."|";
			$source .= $reference."|";
			$source .= $transactionid."|";
			$source .= $Amount."|";
			$source .= $Currency."|";
			$source .= $Duration."|";
			$source .= $ConsumerIPAddress;

			$checksum_check = sha1($source);

			if (strtoupper($checksum_check) != strtoupper($checksum)) {
				echo "Order Checksum is not valid.";
				exit;
			}
			$transaction_id = $transactionid;

			if(strtoupper($status) == 'OPEN' || strtoupper($status) == 'VALIDATE'){
				$order_status_id = $variables["pending_status_id"];
				$pending_message = "The payment is waiting validation. Status: ".$status.' '.$statuscode;
				$event_description = $pending_message." Payment ID: ".$paymentid." Transaction ID: ".$transactionid;
				$success_message = '';
				$error_message = '';
			}elseif(strtoupper($status) != 'OK'){
				$order_status_id = $variables["failure_status_id"];
				$error_message = "The payment is invalid or declined. Status: ".$status.' '.$statuscode;
				$event_description = $error_message." Payment ID: ".$paymentid." Transaction ID: ".$transactionid;
				$success_message = '';
				$pending_message = '';
			}else{
				$order_status_id = $variables["success_status_id"];
				$success_message = "The payment has been completed. Status: ".$status.' '.$statuscode;
				$event_description = $success_message." Payment ID: ".$paymentid." Transaction ID: ".$transactionid;
				$pending_message = '';
				$error_message = '';
			}

			$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
			$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
			$sql .= " VALUES( ";
			$sql .= $db->tosql($order_id, INTEGER).", ";
			$sql .= $db->tosql($order_status_id, INTEGER).", ";
			$sql .= $db->tosql(va_time(), DATETIME).", ";
			$sql .= $db->tosql('IcePay Status Updated (postback)', TEXT).", ";
			$sql .= $db->tosql($event_description, TEXT);
			$sql .= " ) ";
			$db->query($sql);

			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) . ", ";
			$sql .= " success_message=" . $db->tosql($success_message, TEXT) . ", ";
			$sql .= " pending_message=" . $db->tosql($pending_message, TEXT) . ", ";
			$sql .= " error_message=" . $db->tosql($error_message, TEXT) . " ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);

			$status_error = '';
			$t = new VA_Template($settings["templates_dir"]);
			update_order_status($order_id, $order_status_id , true, "", $status_error);
			if (strlen($status_error)) {
				echo $status_error;
				exit;
			}
		}
	}
?>