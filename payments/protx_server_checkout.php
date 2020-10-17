<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  protx_server_checkout.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * SagePay VSP (www.sagepay.com) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once($root_folder_path ."includes/common.php");
	include_once($root_folder_path ."includes/shopping_cart.php");
	include_once($root_folder_path ."includes/order_links.php");
	include_once($root_folder_path ."includes/record.php");
	include_once($root_folder_path ."includes/order_items.php");
	include_once($root_folder_path ."includes/date_functions.php");
	include_once($root_folder_path ."includes/parameters.php");
	include_once($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path ."payments/protx_server_functions.php");

	$VPSProtocol    = get_param("VPSProtocol");
	$TxType         = get_param("TxType");
	$VendorTxCode   = get_param("VendorTxCode");
	$VPSTxId        = get_param("VPSTxId");
	$Status         = get_param("Status");
	$StatusDetail   = get_param("StatusDetail");
	$TxAuthNo       = get_param("TxAuthNo");
	$AVSCV2         = get_param("AVSCV2");
	$AddressResult  = get_param("AddressResult");
	$PostCodeResult = get_param("PostCodeResult");
	$CV2Result      = get_param("CV2Result");
	$GiftAid        = get_param("GiftAid");
	$D3SecureStatus = get_param("3DSecureStatus");
	$CAVV           = get_param("CAVV");
	$VPSSignature   = get_param("VPSSignature");
	// new parameters
	$AddressStatus = get_param("AddressStatus");
	$PayerStatus = get_param("PayerStatus");
	$CardType = get_param("CardType");
	$Last4Digits = get_param("Last4Digits");

	$sql  = " SELECT setting_type FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_value LIKE '%protx_server_check.php'";
	$order_final = get_db_value($sql);
   	$idx = strrpos($order_final, "_");
   	$payment_id = substr($order_final, $idx+1);

	if (!strlen($VendorTxCode)) {
		$sql  = " SELECT parameter_source FROM " . $table_prefix . "payment_parameters ";
		$sql .= " WHERE payment_id=".$db->tosql($payment_id, INTEGER)." and parameter_name='RedirectURL'";
		$redirect_url = get_db_value($sql);
		$redirect_url = str_replace("{site_url}", $settings["site_url"], $redirect_url);

		$error_message = "'VendorTxCode' doesn't exist.";
		if (strlen($TxAuthNo)) {
			$error_message .= ' TxAuthNo:' . $TxAuthNo;
		}
		if (strlen($Status)) {
			$error_message .= ' Status:' . $Status;
		}
		if (strlen($StatusDetail)) {
			$error_message .= ' StatusDetail:' . $StatusDetail;
		}
		if (strlen($VPSTxId)) {
			$error_message .= ' VPSTxId:' . $VPSTxId;
		}
		$response  = "Status=INVALID\r\n";
		$response .= "RedirectURL=" . $redirect_url;
		$response .= "\r\n";
		$response .= "StatusDetail=" . $error_message;
		echo $response;

		exit;
	}

	$sql  = " SELECT parameter_source FROM " . $table_prefix . "payment_parameters ";
	$sql .= " WHERE payment_id=".$db->tosql($payment_id, INTEGER)." and parameter_name='VendorTxCode'";
	$VendorTxCodeMask = get_db_value($sql);

	$order_id = "";
	if ($VendorTxCodeMask == "order_id" || $VendorTxCodeMask == "{order_id}") {
   	$order_id = $VendorTxCode;
	} else if (preg_match("/\{order_id\}/", $VendorTxCodeMask)) {
		// prepare reqular expression to get order_id parameter
		$VendorTxCodeRegExp = preg_quote(trim($VendorTxCodeMask), "/");
		$VendorTxCodeRegExp = str_replace(preg_quote("{order_id}", "/"), "(\d+)", $VendorTxCodeRegExp);
		if (preg_match( "/^".$VendorTxCodeRegExp."/", $VendorTxCode, $matches)) {
			$order_id = $matches[1];
		}
	}

	$error_message = '';

	$post_parameters = "";
	$payment_params = array();
	$pass_parameters = array(); 
	$pass_data = array(); 
	$variables = array();
	get_payment_parameters($order_id, $payment_params, $pass_parameters, $post_parameters, $pass_data, $variables);

	$sql  = " SELECT transaction_id FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$str_transaction_id = get_db_value($sql);
	$array_transaction_id = explode(' ', $str_transaction_id);
	$array_transaction_id = protx_vsp_get_associative_array('=', $array_transaction_id);

	$signature_src  = $VPSTxId.$VendorTxCode.$Status.$TxAuthNo.$payment_params["Vendor"].$AVSCV2.$array_transaction_id["SecurityKey"];
	$signature_src .= $AddressResult.$PostCodeResult.$CV2Result.$GiftAid.$D3SecureStatus.$CAVV;
	// new parameters
	$signature_src .= $AddressStatus.$PayerStatus.$CardType.$Last4Digits;

	/**** Signature calculation *****
		VPSTxId + VendorTxCode + Status + TxAuthNo + VendorName+ AVSCV2 + 
		SecurityKey + AddressResult + PostCodeResult +CV2Result + GiftAid + 
		3DSecureStatus + CAVV + AddressStatus + PayerStatus + CardType + Last4Digits
	//*/
	$str_VPSSignature = strtoupper(md5($signature_src));

	if($str_VPSSignature == $VPSSignature) {
		$status_error = '';
		$t = new VA_Template('.'.$settings["templates_dir"]);

		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET authorization_code=" . $db->tosql($TxAuthNo, TEXT);
		$sql .= ", avs_message=" . $db->tosql($AVSCV2, TEXT);
		$sql .= ", avs_address_match=" . $db->tosql($AddressResult, TEXT);
		$sql .= ", avs_zip_match=" . $db->tosql($PostCodeResult, TEXT);
		$sql .= ", cvv2_match=" . $db->tosql($CV2Result, TEXT);
		$sql .= ", secure_3d_status=" . $db->tosql($D3SecureStatus, TEXT);
		$sql .= ", secure_3d_cavv=" . $db->tosql($CAVV, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		if ($Status == 'OK') {
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET success_message='OK'";
			$sql .= ", error_message=''";
			$sql .= ", pending_message=''";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);

			update_order_status($order_id, $variables['success_status_id'] , true, "", $status_error);

			$response  = "Status=OK\r\n";
			$response .= "RedirectURL=" . $payment_params['RedirectURL'];
			$response .= "?oid=" . $order_id;
			$response .= "\r\n";
			$response .= "StatusDetail=";
			echo $response;
		} else {
			$error_message = "";
			if (strlen($StatusDetail)) {
				$error_message .= $StatusDetail;
			}else{
				$error_message .= 'Transaction could not be authorised';
			}
			$error_message .= ' ';
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);

			update_order_status($order_id, $variables['failure_status_id'] , true, "", $status_error);

			$response  = "Status=INVALID\r\n";
			$response .= "RedirectURL=" . $payment_params['RedirectURL'];
			if (strlen($order_id)) {
				$response .= "?oid=" . $order_id;
			}
			$response .= "\r\n";
			$response .= "StatusDetail=" . $error_message;
			echo $response;
		}
	} else {
		$error_message = "HASH is corrupted ";
		$response  = "Status=INVALID\r\n";
		$response .= "RedirectURL=" . $payment_params['RedirectURL'];
		if (strlen($order_id)){
			$response .= "?oid=" . $order_id;
		}
		$response .= "\r\n";
		$response .= "StatusDetail=" . $error_message;

		// save error for order
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		echo $response;
	}

?>