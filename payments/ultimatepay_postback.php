<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ultimatepay_postback.php                                 ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * UltimatePay (http://www.ultimatepay.com/) transaction handler by www.viart.com
 */

 	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$login = get_param("login");
	$adminpwd = get_param("adminpwd");
	$commtype = get_param("commtype");
	$detail = get_param("detail");
	$userid = get_param("userid");
	$accountname = get_param("accountname");
	$dtdatetime = get_param("dtdatetime");
	$currency = get_param("currency");
	$amount = get_param("amount");
	$sepamount = get_param("sepamount"); // Amount collected from the user (default currency is US dollar).  Same as above. 
	$set_amount = get_param("set_amount"); // Amount to be settled to merchant.  Will be negative if reflecting a reversal or refund.
	$paymentid = get_param("paymentid");
	$pkgid = get_param("pkgid");
	$pbctrans = get_param("pbctrans");
	$merchtrans = get_param("merchtrans");
	$sn = get_param("sn");
	$developerid = get_param("developerid");
	$appid = get_param("appid");
	$mirror = get_param("mirror");
	$rescode = get_param("rescode");
	$appid = get_param("appid");
	$virtualamount = get_param("virtualamount");
	$virtualcurrency = get_param("virtualcurrency");
	$gwtid = get_param("gwtid");
	$hash = get_param("hash");
	$errorDetail = get_param("errorDetail");
	
	$order_id = $db->tosql($mirror, INTEGER, false);

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$our_login = (isset($payment_parameters['login']))? $payment_parameters['login']: "";
	$our_adminpwd = (isset($payment_parameters['adminpwd']))? $payment_parameters['adminpwd']: "";
	$our_secret = (isset($payment_parameters['secret']))? $payment_parameters['secret']: "";
	$our_sn = (isset($payment_parameters['sn']))? $payment_parameters['sn']: "";

	if ((strcasecmp($our_login, $login) != 0) ||
		(strcasecmp($our_adminpwd, $adminpwd) != 0) ||
		(strcasecmp($our_sn, $sn) != 0)) {
		// Unable to validate login and password values
		$responseString = '[ERROR]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= 'sn, login or adminpwd does not match.';
		echo $responseString;
		exit;
	}

	$hashString = $dtdatetime . $our_login . $our_adminpwd . $our_secret . $userid . $commtype . $set_amount . $amount . $sepamount . $currency . $our_sn . $mirror . $pbctrans . $developerid . $appid . $virtualamount . $virtualcurrency;

	$our_hash = md5($hashString);
	if (strcasecmp($our_hash, $hash) != 0) {
		// Unable to validate hash
		$responseString = '[ERROR]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= 'hash does not match.';
		echo $responseString;
		exit;
	}

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$status_error = '';

	if (strtoupper($commtype) == 'FORCED_REVERSAL') {
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql('The customer has denied the payment and forced return of his payment.', TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($variables["failure_status_id"], INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('System Status Updated', TEXT).", ";
		$sql .= $db->tosql('dtdatetime: '.$dtdatetime.' pbctrans: '.$pbctrans , TEXT);
		$sql .= " ) ";
		$db->query($sql);

		update_order_status($order_id, $variables["failure_status_id"], true, "", $status_error);

		$responseString = '[OK]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= '[N/A]';
		echo $responseString;
		exit;
	}
	if (strtoupper($commtype) == 'ADMIN_REVERSAL') {
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql('The payment has been administratively reversed.', TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($variables["failure_status_id"], INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('System Status Updated', TEXT).", ";
		$sql .= $db->tosql('dtdatetime: '.$dtdatetime.' pbctrans: '.$pbctrans , TEXT);
		$sql .= " ) ";
		$db->query($sql);
		update_order_status($order_id, $variables["failure_status_id"], true, "", $status_error);

		$responseString = '[OK]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= '[N/A]';
		echo $responseString;
		exit;
	}

	if (strtoupper($commtype) == 'ORDER_SUBMITTED') {
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET pending_message=" . $db->tosql('The client completed and confirmed his order in PaybyCash and the order is in process of verification.', TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($variables["pending_status_id"], INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('System Status Updated', TEXT).", ";
		$sql .= $db->tosql('dtdatetime: '.$dtdatetime.' pbctrans: '.$pbctrans , TEXT);
		$sql .= " ) ";
		$db->query($sql);
		update_order_status($order_id, $variables["pending_status_id"], true, "", $status_error);

		$responseString = '[OK]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= '[N/A]';
		echo $responseString;
		exit;
	}

	$error_message = check_payment($order_id, $sepamount, $currency);
	if (strlen($error_message)) {
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($variables["failure_status_id"], INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('System Status Updated', TEXT).", ";
		$sql .= $db->tosql('dtdatetime: '.$dtdatetime.' pbctrans: '.$pbctrans , TEXT);
		$sql .= " ) ";
		$db->query($sql);
		update_order_status($order_id, $variables["failure_status_id"], true, "", $status_error);

		$responseString = '[ERROR]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= $error_message;
		echo $responseString;
		exit;
	}
	
	if (strtoupper($commtype) == 'PAYMENT') {
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=''";
		$sql .= ", pending_message=''";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($variables["success_status_id"], INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('System Status Updated', TEXT).", ";
		$sql .= $db->tosql('dtdatetime: '.$dtdatetime.' pbctrans: '.$pbctrans , TEXT);
		$sql .= " ) ";
		$db->query($sql);
		update_order_status($order_id, $variables["success_status_id"], true, "", $status_error);

		$responseString = '[OK]|';
		$responseString .= date('YmdHis').'|';
		$responseString .= $pbctrans.'|';
		$responseString .= '[N/A]';
		echo $responseString;
		exit;
	}
?>