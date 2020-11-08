<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  linkpoint_check.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Linkpoint (www.linkpoint.com) transaction handler by www.viart.com
 */

	// get parameters from linkpoint response
	$status         = get_param("status", POST); // status
	$order_id       = get_param("oid", POST); // our order id 
	$approval_code  = get_param("approval_code", POST); // get approval code
	$fail_reason    = get_param("failReason", POST); // get fail reason
	$merchant       = get_param("merchant", POST); 
	$merchantphone  = get_param("merchantphone", POST); 
	$merchantemail  = get_param("merchantemail", POST); 
	$chargetotal    = get_param("chargetotal", POST); 

	$codes = array(); $tran_status = "";
	if (strlen($approval_code)) {
		$codes = explode(":", $approval_code);
		if (sizeof($codes) > 3) {
			$tran_status = $codes[0];
			$transaction_id = $codes[3];
			$avs_codes = $codes[2];
			if (strlen($avs_codes) == 4) {
				$variables["avs_response_code"] = substr($avs_codes, 0, 3);
				$variables["avs_address_match"] = $avs_codes[0];
				$variables["avs_zip_match"] = $avs_codes[1];
				$variables["cvv2_match"] = $avs_codes[3];
			}
		}
	}

	// check parameters
	if (!strlen($status)) {
		$error_message = "Can't obtain transaction status.";
	} elseif (!strlen($order_id)) {
		$error_message = "Can't obtain order number parameter.";
	} elseif (strlen($fail_reason)) {
		$error_message = $fail_reason . " (" . $status . ")";
	} elseif ($status != "APPROVED" || $tran_status != "Y") {
		$error_message = "Your transaction has been declined. (" . $status . ")";
	} elseif (sizeof($codes) < 4) {
		$error_message = "Approval code has wrong value.";
	} else {
		//check amount and order id
		$error_message = check_payment($order_id, $chargetotal, "");
	}


	// update credit card information returned from Linkpoint
	$session_order_id = get_session("session_order_id");
	$card_type = ""; $cc_number = "";
	$cardnumber = get_param("cardnumber"); // (Visa)  ....1111
	if (preg_match("/\((\w+)\)\s*([\.\d]+)/i", $cardnumber, $matches)) {
		$card_type = $matches[1];
		$cc_number = $matches[2];
		$cc_number = str_replace(".", "*", $cc_number); // convert to viart format
	}
	

	if ($session_order_id && $cc_number) {
		$exp_ts = 0;
		$expmonth = get_param("expmonth"); // MM
		$expyear = get_param("expyear"); // YYYY
		if ($expyear) {
			$exp_ts =	mktime (0, 0, 0, $expmonth, 1, $expyear);
		}

		$bname = get_param("bname"); // John Marshall

		// check viart cc_type
		$cc_type = "";
		$sql  = " SELECT credit_card_id FROM " . $table_prefix . "credit_cards ";
		$sql .= " WHERE credit_card_code=" . $db->tosql($card_type, TEXT);
		$sql .= " OR credit_card_name=" . $db->tosql($card_type, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$cc_type = $db->f("credit_card_id");
		}

		// update information
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET cc_number=" . $db->tosql($cc_number, TEXT);
		if (strlen($cc_type)) {
			$sql .= " , cc_type=" . $db->tosql($cc_type, INTEGER);
		}
		if (strlen($bname)) {
			$sql .= " , cc_name=" . $db->tosql($bname, TEXT);
		}
		if ($exp_ts > 0) {
			$sql .= " , cc_expiry_date=" . $db->tosql($exp_ts, DATETIME);
		}
		$sql .= " WHERE order_id=" . $db->tosql($session_order_id, INTEGER) ;
		$db->query($sql);
	}

?>