<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  icepay_check.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 *  IcePay (www.icepay.eu) transaction handler by http://www.viart.com/
 */
	$status        = get_param("Status");
	$statuscode    = get_param("StatusCode");
	$merchant      = get_param("Merchant");
	$orderid       = get_param("OrderID");
	$paymentid     = get_param("PaymentID");
	$reference     = get_param("Reference");
	$transactionid = get_param("TransactionID");
	$checksum      = get_param("Checksum");

	$source  = $payment_parameters['Encryptioncode']."|";
	$source .= $payment_parameters['IC_Merchant']."|";
	$source .= $status."|";
	$source .= $statuscode."|";
	$source .= $orderid."|";
	$source .= $paymentid."|";
	$source .= $reference."|";
	$source .= $transactionid;
	
	$checksum_check = sha1($source);

	if (strtoupper($checksum_check) != strtoupper($checksum)) {
		$error_message =  "Order Checksum is not valid.";
	} elseif($order_id != $orderid) {	
		$error_message =  "Order ID is not valid.";
	} else {
		$transaction_id = $transactionid;

		if(strtoupper($status) == 'OPEN' || strtoupper($status) == 'VALIDATE'){
			$order_status_id = $variables["pending_status_id"];
			$pending_message = "The payment is waiting validation. Status: ".$status.' '.$statuscode;
			$event_description = $pending_message." Payment ID: ".$paymentid." Transaction ID: ".$transactionid;
		}elseif(strtoupper($status) != 'OK'){
			$order_status_id = $variables["failure_status_id"];
			$error_message = "The payment is invalid or declined. Status: ".$status.' '.$statuscode;
			$event_description = $error_message." Payment ID: ".$paymentid." Transaction ID: ".$transactionid;
		}else{
			$order_status_id = $variables["success_status_id"];
			$success_message = "The payment has been completed. Status: ".$status.' '.$statuscode;
			$event_description = $success_message." Payment ID: ".$paymentid." Transaction ID: ".$transactionid;
		}
		
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($order_status_id, INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('IcePay Status Updated', TEXT).", ";
		$sql .= $db->tosql($event_description, TEXT);
		$sql .= " ) ";
		$db->query($sql);
	}
?>