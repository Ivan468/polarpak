<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  securetrading_notification.php                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/**
 * SecureTrading STPP Shopping Carts
 * Viart 4.1
 * Module Version 2.5.7
 * Last Updated 01 August 2013
 * Written by Peter Barrow for SecureTrading Ltd.
 * http://www.securetrading.com
 */

?><?php

require_once(dirname(__FILE__) . '/securetrading_stpp_lib/securetrading_stpp/STPPLoader.php');
require_once(dirname(__FILE__) . '/securetrading_stpp_lib/ViArtPPages.class.php');

$is_admin_path = true;
$root_folder_path = "../";
include_once ($root_folder_path ."includes/common.php");
$is_admin_path = false;
include_once ($root_folder_path ."includes/record.php");
include_once ($root_folder_path ."includes/order_links.php");
include_once ($root_folder_path ."includes/order_items.php");
include_once ($root_folder_path ."includes/parameters.php");
include_once ($root_folder_path ."includes/common_functions.php");
include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

$trans_id       = get_param("transactionreference", POST);
$order_id       = get_param("orderreference", POST);
$amount         = get_param("mainamount", POST);
$currency_code  = get_param("currencyiso3a", POST);
$errorcode 		= get_param("errorcode", POST);

$payment_type = get_param("paymenttypedescription", POST); // ST Addition
$masked_pan = urldecode(get_param("maskedpan", POST)); // ST Addition

			$cc_number = $masked_pan;
			$cc_number_len = strlen($cc_number);
			if ($cc_number_len > 6) {
				$cc_number_first = substr($cc_number, 0, 6);
			} else {
				$cc_number_first = $cc_number;
			}
			if ($cc_number_len > 4) {
				$cc_number_last = substr($cc_number, $cc_number_len - 4);
			} else {
				$cc_number_last = $cc_number;
			}
			set_session("session_cc_number", $cc_number);
			set_session("session_cc_number_first", $cc_number_first);
			set_session("session_cc_number_last", $cc_number_last);


$payment_parameters = array();
$pass_parameters = array();
$post_parameters = '';
$pass_data = array();
$variables = array();
get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);	

$ppages = new ViArtPPages();

if (!$trans_id) {
	$ppages->createException(new Exception('The transactionreference was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

if (!$order_id) {
	$ppages->createException(new Exception('The orderreference was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

if (!$amount) {
	$ppages->createException(new Exception('The mainamount was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

if (!$currency_code) {
	$ppages->createException(new Exception('The currencycodeiso3a code was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

if ($errorcode === "") {
	$ppages->createException(new Exception('The errorcode was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

// ST Addition: Start
if (!$payment_type) {
	$ppages->createException(new Exception('The paymenttype was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

if (!$masked_pan) {
	$ppages->createException(new Exception('The maskedpan was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}
// ST Addition:End

if (!isset($_POST['responsesitesecurity']) && $payment_parameters['use_notification_hash']) {
	$ppages->createException(new Exception('The notification hash was enabled but the responsesitesecurity field was not returned to the notification script.'), __FILE__, __CLASS__, __LINE__);
}

// ST Modification: Start (Commented Out)
/*
if (isset($_POST['responsesitesecurity']) && $_POST['responsesitesecurity'] !== hash('sha256', $currency_code . $errorcode . $amount . $order_id . $trans_id . $payment_parameters['notificationhash'])) {
	$ppages->createException(new Exception('The regenerated notification hash did not match the returned hash.'), __FILE__, __CLASS__, __LINE__);
}
*/
// ST Modification: End

// ST Addition: Start
if (isset($_POST['responsesitesecurity']) && $_POST['responsesitesecurity'] !== hash('sha256', $currency_code . $errorcode . $amount . str_replace('%23', '0', $masked_pan) . $order_id . $payment_type . $trans_id . $payment_parameters['notificationhash'])) {
	$ppages->createException(new Exception('The regenerated notification hash did not match the returned hash.'), __FILE__, __CLASS__, __LINE__);
}
// ST Addition: End

if ($errorcode !== "0") {
	exit('Notification run for non-zero errorcode.');
}

$failure_status_id = 0;
$success_status_id = 0;

$db->query("SELECT * FROM " . $table_prefix . "orders WHERE order_id = " . $db->tosql($order_id, INTEGER));

if ($db->next_record()) {
	$payment_id = $db->f("payment_id");
	$sql = "SELECT setting_value FROM " . $table_prefix . "global_settings WHERE setting_type = " . $db->tosql("order_final_".$payment_id, TEXT) . " AND setting_name = 'failure_status_id'";

	$db->query($sql);
	if ($db->next_record()) {
		$failure_status_id = $db->f("setting_value");
	}
	$sql = "SELECT setting_value FROM " . $table_prefix . "global_settings WHERE setting_type = " . $db->tosql("order_final_".$payment_id, TEXT) . " AND setting_name = 'success_status_id'";

	$db->query($sql);
	if ($db->next_record()) {
		$success_status_id = $db->f("setting_value");
	}
}

$payment_parameters = array();
$pass_parameters = array();
$post_parameters = '';
$pass_data = array();
$variables = array();
get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

$error_message = check_payment($order_id, $amount, $currency_code);

$order_status = 0;

//$sql  = " UPDATE " . $table_prefix . "orders SET transaction_id = " . $db->tosql($trans_id, TEXT); // ST Modification: Commented out
$sql = "UPDATE " . $table_prefix . "orders SET "; // ST Addition

if (!$error_message) {
	// ST Modification: Start Addition
	$fields = array(
		'transaction_id' => $db->tosql($trans_id, TEXT),
		'success_message' => $db->tosql('OK', TEXT),
		'cc_number' => $db->tosql($masked_pan, TEXT),
		//'cc_type' => $db->tosql($payment_type, TEXT), // See admin_order.php, line 566 (ViArt 4.1).  cc_type expected to be int.  Mapping to string in table credit_cards.
	);
	// ST Modification: End Addition
	
	//$sql .= ", success_message = 'OK'"; // ST Modification: Commented out
	
	if ($success_status_id) {
		$order_status = $success_status_id;
	}
}
else {
	// ST Modification: Start Addition
	$fields = array(
		'error_message' => $db->tosql($error_message, TEXT)
	);
	// ST Modification: End Addition
	
	//$sql .= ", error_message = " . $db->tosql($error_message, TEXT); // ST Modification: Commented Out
	
	if ($failure_status_id) {
		$order_status = $failure_status_id;
	}
}

// ST Modification: Start addition
foreach($fields as $column => $value) {
	$sql .= sprintf("%s = %s, ", $column, $value);
}
$sql = substr($sql, 0, -2);
// ST Modification: End addition
	
$sql .= " WHERE order_id = " . $db->tosql($order_id, INTEGER);
$db->query($sql);

if ($order_status) {
	$t = new VA_Template('.'.$settings["templates_dir"]);
	$status_error = $failure_status_id;
	update_order_status($order_id, $order_status, true, '', $status_error); // last param was ''
}

exit('Notification complete');