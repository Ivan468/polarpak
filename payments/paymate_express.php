<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  paymate_express.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * PayMate (www.paymate.com.au) transaction handler by www.viart.com
 */

	$payment_response = "";
	foreach ($_POST as $key => $value) {
		$payment_response .= $key . "=" . $value . "\n";
		$t->set_var($key, $value);
	}
	$t->set_var("payment_response", $payment_response);

	$customer_ip      = get_ip();
	$transaction_id   = isset($_POST["transactionID"]) ? $_POST["transactionID"] : "";
	$response_code    = isset($_POST["responseCode"]) ? $_POST["responseCode"] : "";
	$payment_amount   = isset($_POST["paymentAmount"]) ? $_POST["paymentAmount"] : "";
	$payment_currency = isset($_POST["currency"]) ? $_POST["currency"] : "";

	// check parameters
	if (!strlen($transaction_id)) {
		$error_message = "Can't obtain transaction id parameter";
	} else if (!strlen($response_code)) {
		$error_message = "Can't obtain response code parameter";
	} else if (!strlen($payment_amount)) {
		$error_message = "Can't obtain payment amount parameter";
	} else if (!strlen($payment_currency)) {
		$error_message = "Can't obtain currency parameter";
	}

	if (strlen($error_message)) {
 		return;
	}

	// update transaction information
	$sql  = " UPDATE " . $table_prefix . "orders SET transaction_id=" . $db->tosql($transaction_id, TEXT);
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);

	if ($response_code == "PP") {
		$pending_message = "Payment is still processing"; // Await notification from Paymate prior to organising delivery of purchased items or service
	}

	if ($response_code == "PD") {
		$error_message = "Payment has been declined by Paymate or buyer's bank"; // Contact buyer to organise another means of payment or discontinue order
	} else if ($response_code == "PE") {
		$error_message = "System error occurred during payment process"; // Contact Paymate quoting transaction reference number and payment date
	} else if ($response_code == "PA" || $response_code == "PP") {
		// check that payment_amount and payment_currency are correct
		$error_message = check_payment($order_id, $payment_amount, $payment_currency);
	} else {
		$error_message = "Unknown system reponse code";
	}

	// Check the payment and proceed with organising delivery of items or provision of service immediately if no any messages.

?>