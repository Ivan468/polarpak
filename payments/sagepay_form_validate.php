<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  sagepay_form_validate.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Sage Pay Form (www.sagepay.com) transaction handler by www.viart.com
 */

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";

	include_once($root_folder_path . "payments/sagepay_form_functions.php");

	// get payments parameters for validation
	$crypt = get_param("crypt");
	// to work around bug in PHP function base64_decode
	$EncryptionPassword = isset($payment_params["EncryptionPassword"]) ? $payment_params["EncryptionPassword"] : "";

	// check parameters
	if (!strlen($crypt)) {
		$error_message = "Can't obtain data from Sage Pay.";
	} elseif (!strlen($EncryptionPassword)) {
		$error_message = "Can't obtain encryption password.";
	}

	if (strlen($error_message)) {
		return;
	}

	//$payment_response = simple_xor(base64_decode($crypt), $EncryptionPassword);
	$payment_response = decryptAes($crypt, $EncryptionPassword);
	$t->set_var("payment_response", $payment_response);

	$response_parameters = array();
	$response_parts = explode("&", $payment_response);
	for ($i = 0; $i < sizeof($response_parts); $i++) {
		$response_part = explode('=', $response_parts[$i]);
		if (sizeof($response_part) == 1) { $response_part[1] = ""; }
		$response_parameters[$response_part[0]] = urldecode($response_part[1]);
		$response_parameters[strtolower($response_part[0])] = urldecode($response_part[1]);
		$t->set_var($response_part[0], $response_part[1]);
	}

	$status   = isset($response_parameters["status"]) ? $response_parameters["status"] : "";
	$status_detail = isset($response_parameters["statusdetail"]) ? $response_parameters["statusdetail"] : "";
	$order_id = isset($response_parameters["vendortxcode"]) ? $response_parameters["vendortxcode"] : "";
	$transaction_id = isset($response_parameters["vpstxid"]) ? $response_parameters["vpstxid"] : "";
	$txauthno = isset($response_parameters["txauthno"]) ? $response_parameters["txauthno"] : "";
	$avscv2 = isset($response_parameters["avscv2"]) ? $response_parameters["avscv2"] : "";
	$amount = isset($response_parameters["amount"]) ? $response_parameters["amount"] : "";

	// protocol 2.22 fields
	$addressresult = isset($response_parameters["addressresult"]) ? $response_parameters["addressresult"] : "";
	$postcoderesult = isset($response_parameters["postcoderesult"]) ? $response_parameters["postcoderesult"] : "";
	$cv2result = isset($response_parameters["cv2result"]) ? $response_parameters["cv2result"] : "";
	$giftaid = isset($response_parameters["giftaid"]) ? $response_parameters["giftaid"] : "";
	$vbvsecurestatus = isset($response_parameters["3dsecurestatus"]) ? $response_parameters["3dsecurestatus"] : "";
	$cavv = isset($response_parameters["cavv"]) ? $response_parameters["cavv"] : "";

	if (strtoupper($status) == "NOTAUTHED") {
		$error_message = "The bank has declined the transaction.";
	} elseif (strtoupper($status) == "MALFORMED") {
		$error_message = "Transaction Registration POST is poorly formatted.";
	} elseif (strtoupper($status) == "INVALID") {
		$error_message = "Transaction Registration POST contains illegal data.";
	} elseif (strtoupper($status) == "ABORT") {
		$error_message = "User cancel transaction.";
	} elseif (strtoupper($status) == "REJECTED") {
		$error_message = "Transaction failed to one of the security rules.";
	} elseif (strtoupper($status) == "ERROR") {
		$error_message = "Sage Pay can't handle this transaction.";
	} elseif (strtoupper($status) != "OK") {
		$error_message = "Unknown transaction type.";
	}

	if ($error_message && $status_detail) {
		$error_message .= " ".$status_detail;
	}

	// update transaction information
	$sql  = " UPDATE " . $table_prefix . "orders SET transaction_id=" . $db->tosql($transaction_id, TEXT);
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);

?>