<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  amazon_ipn.php                                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Amazon Checkout IPN handler by http://www.viart.com/
 * 
 * Set Instant Order Processing Notifications:
 * 1. In Seller Central, click Settings > Checkout Pipeline Settings.
 * The Checkout Pipeline Settings page appears.
 * 2. Click the first Edit button on the page (the one immediately under the Instant Order Processing Notification Settings heading).
 * 3. In the Merchant URL box, type the URL for your website 
 * http://www.yoursite.com/payments/amazon_ipn.php
 * (NOTE: replace www.yoursite.com with your real site URL and shop path)
 */




	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_STRICT);

	$is_admin_path = true;
	$root_folder_path = "../";
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/parameters.php");
	include_once ($root_folder_path . "includes/order_items.php");
	include_once ($root_folder_path . "includes/order_links.php");
	include_once ($root_folder_path . "includes/shopping_cart.php");
	include_once ($root_folder_path . "includes/date_functions.php");
	include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");

	// initialize template object to use in update_order_status() function
	$t = new VA_Template(".");

	// get IPN parameters
	$NotificationType = get_param("NotificationType"); // NewOrderNotification, OrderReadyToShipNotification, OrderCancelledNotification
	$NotificationData = get_param("NotificationData"); // XML Document
	$UUID = get_param("UUID"); // 256ec195-da62-4531-9602-32b585f7d780
	$Timestamp = get_param("Timestamp"); // 2013-06-24T23:37:26.615Z
	$Signature = get_param("Signature"); // lZJ7X1nnuhE89VJMKVmbjoxLEyI=
	$AWSAccessKeyId = get_param("AWSAccessKeyId"); // ABCDEFGHIJKLNOPQRSTU
	$OurSignature = "";

	// check if order_id available in request to process it
	if (preg_match("/\<ClientRequestId\>(\d+)\<\/ClientRequestId\>/isU", $NotificationData, $matches)) {
		// found order_id
		$order_id = $matches[1];
		// check transaction number

		$transaction_id = "";
		if (preg_match("/\<AmazonOrderID\>(.+)\<\/AmazonOrderID\>/isU", $NotificationData, $matches)) {
			$transaction_id = $matches[1];
		}
		// get payment parameters
		$payment_parameters = array(); $pass_parameters = array(); $post_parameters = ""; $pass_data = array(); $variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

		$sql  = " SELECT payment_id FROM " . $table_prefix . "orders WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$payment_id = get_db_value($sql);
		$order_final = array();

		$setting_type = "order_final_" . $payment_id;
		$sql  = "SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
		$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		$db->query($sql);
		while($db->next_record()) {
			$order_final[$db->f("setting_name")] = $db->f("setting_value");
		}
		$success_status_id = get_setting_value($order_final, "success_status_id", "");
		$failure_status_id = get_setting_value($order_final, "failure_status_id", "");
		$pending_status_id = get_setting_value($order_final, "pending_status_id", "");

		// get keys to calculate signature 
		$aws_access_key_id = get_setting_value($payment_parameters, "aws_access_key_id", ""); // Your AWS Access Key ID (public)
		$aws_secret_key_id = get_setting_value($payment_parameters, "aws_secret_key_id", ""); // Your AWS Secret Access Key (private)
		// calculate signature to compare it with Amazon value
		$OurSignature = base64_encode(hash_hmac("sha1", $UUID.$Timestamp, $aws_secret_key_id, true));
		if ($Signature == $OurSignature) {
		  // Signature is ok so we can proceed
			if (strtolower($NotificationType) == "newordernotification") {
				$pending_message = "We've received confirmation for new order from Amazon.";
				// update order information
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET success_message=" . $db->tosql($NotificationType, TEXT);
				$sql .= ", error_message='', pending_message=" . $db->tosql($pending_message, TEXT);
				if ($transaction_id) {
					$sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT);
				}
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);
				// update order status
				if ($pending_status_id) {
					update_order_status($order_id, $pending_status_id, true, "", $status_error);
				}
			} else if (strtolower($NotificationType) == "orderreadytoshipnotification") {
				// the order is ready to be shipped according to Checkout by Amazon order pipeline. 
				// update order information
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET success_message=" . $db->tosql($NotificationType, TEXT);;
				$sql .= ", pending_message='', error_message='' ";
				if ($transaction_id) {
					$sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT);
				}
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);

				// update order status
				if ($success_status_id) {
					update_order_status($order_id, $success_status_id, true, "", $status_error);
				}

			} else if (strtolower($NotificationType) == "ordercancellednotification") {
				$error_message = "Your transaction has been cancelled.";
				// update order information
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET success_message=" . $db->tosql($NotificationType, TEXT);
				$sql .= ", pending_message='' ";
				$sql .= ", error_message=" . $db->tosql($error_message, TEXT);
				if ($transaction_id) {
					$sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT);
				}
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);
				// update order status
				if ($failure_status_id) {
					update_order_status($order_id, $failure_status_id, true, "", $status_error);
				}
			} else {
				// unknown status returned
			}
		} // end signature checks
	}

?>

