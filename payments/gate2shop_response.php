<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  gate2shop_response.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Gate2Shop (www.g2s.com) handler by ViArt Ltd (http://www.viart.com/)
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$par = array("nameOnCard", "cardNumber", "expMonth", "expYear", "first_name", "last_name", "address1", "address2", "city", 
	"country", "email", "state", "zip", "phone1", "phone2", "phone3", "currency", "customField1", "customField2", "customField3", 
	"customField4", "customField5", "merchant_unique_id", "merchant_site_id", "merchant_id", "requestVersion", "PPP_TransactionID", 
	"productId", "userid", "message", "Status", "ExErrCode", "ErrCode", "AuthCode", "Reason", "ReasonCode", 
	"Token", "tokenId", "responsechecksum", "totalAmount", "TransactionID", "ppp_status", "invoice_id", "payment_method", "unknownParameters", 
	"merchantLocale", "customData", "return_action",
	"shippingCountry", "shippingState", "shippingCity", "shippingAddress", "shippingZip", "shippingFirstName", "shippingLastName",
	"shippingPhone", "shippingCell", "shippingMail", "total_discount", "total_shipping", "total_tax", "buyButtonProductBundleId",
	"ID", "responseTimeStamp", "buyButtonProductId", "rebillingPriceDescriptor", "rebillingProductName", "membershipId", "memberId",
	"rebillingMessage", "rebillingTemplateId", "initial_amount", "rebilling_amount", "rebilling_currency");
	
	for ($i=0;$i<count($par);$i++){
		$answer[$par[$i]] = get_param($par[$i]);
	}
	
	$order_id = $answer["invoice_id"];
	$error_message = "";
	$post_parameters = ""; 
	$payment_params = array(); 
	$pass_parameters = array(); 
	$pass_data = array(); 
	$variables = array();
	get_payment_parameters($order_id, $payment_params, $pass_parameters, $post_parameters, $pass_data, $variables, "");

	if (isset($variables["transaction_id"])){
		$transaction_id = $answer["TransactionID"];
	
		$checksum = $answer["ppp_status"].$answer["PPP_TransactionID"];
		if(!isset($payment_parameters["secret"])){
			$error_message = "Need Secret Code.";
			echo $error_message;
			exit;
		}else{
			$secret = $payment_parameters["secret"];
			$checksum = $secret.$checksum;
		}
		$checksum = md5($checksum);
		if (!strlen($answer["responsechecksum"]) || $checksum != $answer["responsechecksum"]){
			$error_message = "Checksum don't consist with response.";
			echo $error_message;
			exit;
		}
		$order_status_id = $variables["success_status_id"];
		$pending_message = '';
		$error_message = '';
		$status_error = '';
		$order_status = $answer["ppp_status"]." ".$answer["message"];
		if (!strlen($transaction_id)) {
			$error_message = "Can't obtain Transaction ID parameter.";
			$order_status_id = $variables["failure_status_id"];
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}elseif (strval($answer["ErrCode"])=='0' && strval($answer["ExErrCode"])=='-2'){
			$pending_message = "Your order will be reviewed manually.";
			$order_status_id = $variables["pending_status_id"];
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", pending_message=" . $db->tosql($pending_message, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}elseif (strval($answer["ErrCode"])!='0' || strval($answer["ExErrCode"])!='0'){
			if(strlen($answer["Error"])){
				$error_message = "ErrCode: ".$answer["ErrCode"].", ExErrCode: ".$answer["ExErrCode"].", ".$answer["Error"];
				$order_status_id = $variables["failure_status_id"];
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
				$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
				$db->query($sql);
			}else{
				$error_message = "ErrCode: ".$answer["ErrCode"].", ExErrCode: ".$answer["ExErrCode"].", "."Your transaction has been declined.";
				$order_status_id = $variables["failure_status_id"];
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
				$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
				$db->query($sql);
			}
		}
		if ($order_status_id) {
			$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
			$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
			$sql .= " VALUES( ";
			$sql .= $db->tosql($order_id, INTEGER).", ";
			$sql .= $db->tosql($order_status_id, INTEGER).", ";
			$sql .= $db->tosql(va_time(), DATETIME).", ";
			$sql .= $db->tosql('Gate2Shop Status Updated', TEXT).", ";
			$sql .= $db->tosql($order_status, TEXT);
			$sql .= " ) ";
			$db->query($sql);
			$t = new VA_Template('.'.$settings["templates_dir"]);
			update_order_status($order_id, $order_status_id, true, "", $status_error);
		}

	}
?>