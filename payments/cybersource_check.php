<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cybersource_check.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Cybersource (www.cybersource.com) SOP/HOP response handler by ViArt Ltd. (www.viart.com)
 */

	$root_folder_path = "./";
	include_once($root_folder_path . "payments/cybersource_functions.php");

	$decision = get_param("decision", POST);
	$decision_publicSignature = get_param("decision_publicSignature", POST);
	$orderAmount = get_param("orderAmount", POST);
	$orderAmount_publicSignature = get_param("orderAmount_publicSignature", POST);
	$orderNumber = get_param("orderNumber", POST);
	$orderNumber_publicSignature = get_param("orderNumber_publicSignature", POST);
	$orderCurrency = get_param("orderCurrency", POST);
	$orderCurrency_publicSignature = get_param("orderCurrency_publicSignature", POST);
	$avs_message = get_param("ccAuthReply_avsCodeRaw", POST);
	$avs_response_code = get_param("ccAuthReply_avsCode", POST);
	$authorization_code = get_param("ccAuthReply_authorizationCode", POST);

	$signature_check = true; // use this parameter to update order info for cybersouce system

	$pub = isset($payment_parameters["PublicKey"]) ? $payment_parameters["PublicKey"] : "";

	$success_message = "";
	$transaction_id = get_param("requestID", POST);
	$reconciliationID = get_param("reconciliationID", POST);
	$reasonCode = get_param("reasonCode", POST);
	$event_description = "requestID: ".$transaction_id.", reconciliationID: ".$reconciliationID.", decision: ".$decision.", reasonCode: ".$reasonCode.".";
	if (strlen($pub)) {

		$avs_address_match = substr($avs_message, 0, 1);
		$avs_zip_match = substr($avs_message, 1, 1);
		$variables["authorization_code"] = $authorization_code;
		$variables["avs_response_code"] = $avs_response_code;
		$variables["avs_message"] = $avs_message;
		$variables["avs_address_match"] = $avs_address_match;
		$variables["avs_zip_match"] = $avs_zip_match;

		if (VerifySignature($decision, $decision_publicSignature, $pub)
			&& VerifySignature($orderAmount, $orderAmount_publicSignature, $pub)
			&& VerifySignature($orderNumber, $orderNumber_publicSignature, $pub)
			&& VerifySignature($orderCurrency, $orderCurrency_publicSignature, $pub))
		{
			if ($decision == "ACCEPT") {
				$success_message = "Your order has been accepted.";
			} elseif ($decision == "REVIEW") {
				$pending_message = "Your order will be reviewed.";
			} else {
				$error_message .= get_cybersource_error($reasonCode);
			}
		}
		else
		{
			$signature_check = false;
			if (!VerifySignature($decision, $decision_publicSignature, $pub)) {
				$error_message .= " Order decision is not valid.";
			}
			if (!VerifySignature($orderAmount, $orderAmount_publicSignature, $pub))	{
				$error_message .=  " Order amount is not valid.";
			}
			if (!VerifySignature($orderNumber, $orderNumber_publicSignature, $pub))	{
				$error_message .= " Order number is not valid.";
			}
			if (!VerifySignature($orderCurrency, $orderCurrency_publicSignature, $pub)) {
				$error_message .= " Order currency is not valid.";
			}
		}
	} else {
		$signature_check = false;
		$error_message .= " Public Key is empty.";
	}

	if ($signature_check) {
		// update some Credit Cart information from Cybersource system
		$card_cardtype = get_param("card_cardType", POST); // 001
		$billto_firstname = get_param("billTo_firstName", POST);
		$billto_lastname = get_param("billTo_lastName", POST);
		$cc_number = get_param("card_accountNumber", POST); // ############1111
		$cc_number = str_replace("#", "*", $cc_number); // convert to viart format
		$card_expirationMonth = get_param("card_expirationMonth", POST);
		$card_expirationYear = get_param("card_expirationYear", POST);

		$cc_types = array(
			"001" => "VISA",
			"002" => "MC",
			"003" => "AMEX",
			"004" => "DISCOVER",
			"007" => "JCB",
			"024" => "SOLO",
		);

		$cc_type = "";
		if ($card_cardtype && isset($cc_types[$card_cardtype])) {
			// check viart cc_type
			$cc_type_code = $cc_types[$card_cardtype];
			$sql  = " SELECT credit_card_id FROM " . $table_prefix . "credit_cards ";
			$sql .= " WHERE credit_card_code=" . $db->tosql($cc_type_code, TEXT);
			$sql .= " OR credit_card_name=" . $db->tosql($cc_type_code, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$cc_type = $db->f("credit_card_id");
			}
		}
		// update information
		if ($billto_firstname || $billto_lastname || $cc_type) {
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET cc_name=" . $db->tosql($billto_firstname." ".$billto_lastname, TEXT);
			if (strlen($cc_type)) {
				$sql .= " , cc_type=" . $db->tosql($cc_type, INTEGER);
			}
			if (strlen($cc_number)) {
				$sql .= " , cc_number=" . $db->tosql($cc_number, TEXT);
			}
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}
	}

	// save event 
	$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
	$sql .= " (order_id, event_date, event_name, event_description) ";
	$sql .= " VALUES( ";
	$sql .= $db->tosql($order_id, INTEGER).", ";
	$sql .= $db->tosql(va_time(), DATETIME).", ";
	$sql .= $db->tosql('Cybersource Final Checkout Validation', TEXT).", ";
	$sql .= $db->tosql($event_description , TEXT);
	$sql .= " ) ";
	$db->query($sql);

?>