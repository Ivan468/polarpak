<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  google_checkout.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Google Checkout (https://checkout.google.com/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."payments/google_functions.php");

	// settings for errors notifications 
	$eol = get_eol();
	$admin_email = get_setting_value($settings, "admin_email", "");
	$email_headers  = "From: ".$admin_email.$eol;
	$email_headers .= "Content-Type: text/plain";

	$compare_mer_id = ""; 
	$compare_mer_key = "";
	$error_message = ""; // save here non-standard errors to send by email

	if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		$compare_mer_id = $_SERVER['PHP_AUTH_USER']; 
		$compare_mer_key = $_SERVER['PHP_AUTH_PW'];
	}else if(isset($_SERVER['HTTP_AUTHORIZATION'])){
		list($compare_mer_id, $compare_mer_key) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'],strpos($_SERVER['HTTP_AUTHORIZATION'], " ") + 1)));
	} else if(isset($_SERVER['Authorization'])) {
		list($compare_mer_id, $compare_mer_key) = explode(':', base64_decode(substr($_SERVER['Authorization'], strpos($_SERVER['Authorization'], " ") + 1)));
	} else if(isset($_GET['auth'])) {
		$auth = $_GET['auth'];
		list($compare_mer_id, $compare_mer_key) = explode(':', base64_decode(substr($_GET['auth'], strpos($_GET['auth'], " ") + 1)));
	} else {
		// Authentication Failed
		$error_subject = "Google Checkout Error :: Authentication Failed";
		$error_message = "\$_SERVER[]:";
		foreach($_SERVER as $key => $var) {
			$error_message .= "\n".$key."=".$var;
		}
		$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
		$error_message .= $HTTP_RAW_POST_DATA;
		mail($admin_email, $error_subject, $error_message, $email_headers);
	}

	$status_error = '';
	$xml_response = $HTTP_RAW_POST_DATA;
	if (get_magic_quotes_gpc()) {
		$xml_response = stripslashes($xml_response);
	}

	// check serial number for handshake
	$google_serial_number = "";
	if (preg_match("/serial\-number\=\"([^\"]+)\"/i", $xml_response, $matches)) {
		$google_serial_number = $matches[1];
	} else if (preg_match("/<serial-number>(.*)\<\/serial-number>/Uis", $xml_response, $matches)) {
		$google_serial_number = $matches[1];
	}

	if (!$error_message) {
		// check new order notification message
		if (preg_match_all("/<new-order-notification(.*)\<\/new-order-notification>/Uis", $xml_response, $matches, PREG_SET_ORDER)){
  
			preg_match_all("/<merchant-note>(.*)\<\/merchant-note>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$order_id = (isset($value[0][1]))?intval($value[0][1]):0;
			preg_match_all("/<google-order-number>(.*)\<\/google-order-number>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$google_order_id = (isset($value[0][1]))?$value[0][1]:0;
			preg_match_all("/<order-total(.*)\<\/order-total>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$order_total = (isset($value[0][1]))?$value[0][1]:0;
			preg_match_all("/<financial-order-state>(.*)\<\/financial-order-state>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$order_status = (isset($value[0][1]))?$value[0][1]:0;
  
			preg_match_all("/<buyer-shipping-address>(.*)\<\/buyer-shipping-address>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$shipping_address = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<buyer-billing-address>(.*)\<\/buyer-billing-address>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$billing_address = (isset($value[0][1]))?($value[0][1]):'';
  
			preg_match_all("/<company-name>(.*)\<\/company-name>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$company_name = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<contact-name>(.*)\<\/contact-name>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$name = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<email>(.*)\<\/email>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$email = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<address1>(.*)\<\/address1>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$address1 = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<address2>(.*)\<\/address2>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$address2 = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<city>(.*)\<\/city>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$city = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<region>(.*)\<\/region>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$state_code = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<postal-code>(.*)\<\/postal-code>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$zip = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<country-code>(.*)\<\/country-code>/Uis", $billing_address, $value, PREG_SET_ORDER);
			$country_code = (isset($value[0][1]))?($value[0][1]):'';
  
			preg_match_all("/<company-name>(.*)\<\/company-name>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_company_name = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<contact-name>(.*)\<\/contact-name>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_name = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<email>(.*)\<\/email>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_email = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<address1>(.*)\<\/address1>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_address1 = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<address2>(.*)\<\/address2>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_address2 = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<city>(.*)\<\/city>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_city = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<region>(.*)\<\/region>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_state_code = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<postal-code>(.*)\<\/postal-code>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_zip = (isset($value[0][1]))?($value[0][1]):'';
			preg_match_all("/<country-code>(.*)\<\/country-code>/Uis", $shipping_address, $value, PREG_SET_ORDER);
			$delivery_country_code = (isset($value[0][1]))?($value[0][1]):'';
  
			if ($order_id){
  
				$payment_parameters = array();
				$pass_parameters = array();
				$post_parameters = '';
				$pass_data = array();
				$variables = array();
				get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
				
				if ($compare_mer_id != $payment_parameters["merchant_id"] || $compare_mer_key != $payment_parameters["merchant_key"]) {
					// order id wasn't found
					$error_subject = "Google Checkout Error :: Authentication Failed";
					$error_message  = "Authentication Parameters:";
					$error_message .= "\n\$compare_mer_id=$compare_mer_id";
					$error_message .= "\n\$compare_mer_key=$compare_mer_key";
					$error_message .= "\n\n\$_SERVER[]:";
					foreach($_SERVER as $key => $var) {
						$error_message .= "\n".$key."=".$var;
					}
					$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
					$error_message .= $HTTP_RAW_POST_DATA;
					mail($admin_email, $error_subject, $error_message, $email_headers);
				} else {
				
					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET name=" . $db->tosql($name, TEXT) ;
					$sql .= ", first_name=" . $db->tosql($name, TEXT) ;
					$sql .= ", company_name=" . $db->tosql($company_name, TEXT) ;
					$sql .= ", email=" . $db->tosql($email, TEXT) ;
					$sql .= ", address1=" . $db->tosql($address1, TEXT) ;
					$sql .= ", address2=" . $db->tosql($address2, TEXT) ;
					$sql .= ", city=" . $db->tosql($city, TEXT) ;
					$sql .= ", state_code=" . $db->tosql($state_code, TEXT) ;
					$sql .= ", zip=" . $db->tosql($zip, TEXT) ;
					$sql .= ", country_code=" . $db->tosql($country_code, TEXT) ;
					$sql .= ", delivery_name=" . $db->tosql($delivery_name, TEXT) ;
					$sql .= ", delivery_first_name=" . $db->tosql($delivery_name, TEXT) ;
					$sql .= ", delivery_company_name=" . $db->tosql($delivery_company_name, TEXT) ;
					$sql .= ", delivery_email=" . $db->tosql($delivery_email, TEXT) ;
					$sql .= ", delivery_address1=" . $db->tosql($delivery_address1, TEXT) ;
					$sql .= ", delivery_address2=" . $db->tosql($delivery_address2, TEXT) ;
					$sql .= ", delivery_city=" . $db->tosql($delivery_city, TEXT) ;
					$sql .= ", delivery_state_code=" . $db->tosql($delivery_state_code, TEXT) ;
					$sql .= ", delivery_zip=" . $db->tosql($delivery_zip, TEXT) ;
					$sql .= ", delivery_country_code=" . $db->tosql($delivery_country_code, TEXT) ;
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
					$db->query($sql);
					
					$order_status_id = 0;
					if ($google_order_id){
						if ($order_status == "PAYMENT_DECLINED" || $order_status == "CANCELLED" || $order_status == "CANCELLED_BY_GOOGLE"){
							$order_status_id = $variables["failure_status_id"];
							$sql  = " UPDATE " . $table_prefix . "orders ";
							$sql .= " SET transaction_id=" . $db->tosql($google_order_id, TEXT) ;
							$sql .= ", error_message=" . $db->tosql($order_status, TEXT) ;
							$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
							$db->query($sql);
						} else if ($order_status == "CHARGED"){
							$order_status_id = $variables["success_status_id"];
							$sql  = " UPDATE " . $table_prefix . "orders ";
							$sql .= " SET transaction_id=" . $db->tosql($google_order_id, TEXT) ;
							$sql .= ", success_message=" . $db->tosql($order_status, TEXT) ;
							$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
							$db->query($sql);
						} else {
							$pending_message = "Google status for this order: " . $order_status;
							$order_status_id = $variables["pending_status_id"];
							$sql  = " UPDATE " . $table_prefix . "orders ";
							$sql .= " SET transaction_id=" . $db->tosql($google_order_id, TEXT) ;
							$sql .= ", pending_message=" . $db->tosql($pending_message, TEXT) ;
							$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
							$db->query($sql);
						}
					} else{
						$order_status_id = $variables["failure_status_id"];
						g_c_set_error($order_id, "'google order number' was not received");
					}

					if ($order_status_id) {
						$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
						$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
						$sql .= " VALUES( ";
						$sql .= $db->tosql($order_id, INTEGER).", ";
						$sql .= $db->tosql($order_status_id, INTEGER).", ";
						$sql .= $db->tosql(va_time(), DATETIME).", ";
						$sql .= $db->tosql('Google Status Updated', TEXT).", ";
						$sql .= $db->tosql($order_status, TEXT);
						$sql .= " ) ";
						$db->query($sql);
        
						$t = new VA_Template('.'.$settings["templates_dir"]);
						update_order_status($order_id, $order_status_id, true, "", $status_error);
						if(strlen($status_error)){
							g_c_set_error($order_id, $status_error);
						}
					}

				}
			} else {
				// order id wasn't found
				$error_subject = "Google Checkout Error :: Order Number Missed";
				$error_message = "\$_SERVER[]:";
				foreach($_SERVER as $key => $var) {
					$error_message .= "\n".$key."=".$var;
				}
				$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
				$error_message .= $HTTP_RAW_POST_DATA;
				mail($admin_email, $error_subject, $error_message, $email_headers);
			}
		} else if (preg_match_all("/<order-state-change-notification(.*)\<\/order-state-change-notification>/Uis", $xml_response, $matches, PREG_SET_ORDER)){
			// check order state change
			preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", $xml_response, $matches, PREG_SET_ORDER);
			preg_match_all("/<google-order-number>(.*)\<\/google-order-number>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$google_order_id = (isset($value[0][1]))?$value[0][1]:0;
			preg_match_all("/<new-financial-order-state>(.*)\<\/new-financial-order-state>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$order_status = (isset($value[0][1]))?$value[0][1]:0;
			$order_id = 0;
			if ($google_order_id) {
				$sql  = " SELECT order_id ";
				$sql .= " FROM " . $table_prefix . "orders ";
				$sql .= " WHERE transaction_id=" . $db->tosql($google_order_id, TEXT);
				$db->query($sql);
				while ($db->next_record()) {
					$order_id = $db->f("order_id");
				}
			}
			if ($order_id){
  
				$payment_parameters = array();
				$pass_parameters = array();
				$post_parameters = '';
				$pass_data = array();
				$variables = array();
				get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
  
				if($compare_mer_id != $payment_parameters['merchant_id'] || $compare_mer_key != $payment_parameters['merchant_key']) {
					// order id wasn't found
					$error_subject = "Google Checkout Error :: Authentication Failed";
					$error_message  = "Authentication Parameters:";
					$error_message .= "\n\$compare_mer_id=$compare_mer_id";
					$error_message .= "\n\$compare_mer_key=$compare_mer_key";
					$error_message .= "\n\n\$_SERVER[]:";
					foreach($_SERVER as $key => $var) {
						$error_message .= "\n".$key."=".$var;
					}
					$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
					$error_message .= $HTTP_RAW_POST_DATA;
					mail($admin_email, $error_subject, $error_message, $email_headers);
				} else {
					$order_status_id = 0;
					if ($order_status == 'PAYMENT_DECLINED' || $order_status == 'CANCELLED' || $order_status == 'CANCELLED_BY_GOOGLE'){
						$order_status_id = $variables["failure_status_id"];
						$sql  = " UPDATE " . $table_prefix . "orders ";
						$sql .= " SET error_message=" . $db->tosql($order_status, TEXT) ;
						$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
						$db->query($sql);
					}else if ($order_status == 'CHARGED'){
						$order_status_id = $variables["success_status_id"];
						$sql  = " UPDATE " . $table_prefix . "orders ";
						$sql .= " SET success_message=" . $db->tosql($order_status, TEXT) ;
						$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
						$db->query($sql);
					}else{
						$order_status_id = $variables["pending_status_id"];
						$sql  = " UPDATE " . $table_prefix . "orders ";
						$sql .= " SET pending_message=" . $db->tosql($order_status, TEXT) ;
						$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
						$db->query($sql);
					}
					if ($order_status_id) {
						$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
						$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
						$sql .= " VALUES( ";
						$sql .= $db->tosql($order_id, INTEGER).", ";
						$sql .= $db->tosql($order_status_id, INTEGER).", ";
						$sql .= $db->tosql(va_time(), DATETIME).", ";
						$sql .= $db->tosql('Google Status Updated', TEXT).", ";
						$sql .= $db->tosql($order_status, TEXT);
						$sql .= " ) ";
						$db->query($sql);
        
						$t = new VA_Template('.'.$settings["templates_dir"]);
						update_order_status($order_id, $order_status_id, true, "", $status_error);
						if(strlen($status_error)){
							g_c_set_error($order_id, $status_error);
						}
					}
				}
			} else {
				// order number wasn't found
				$error_subject = "Google Checkout Error :: Order Number Missed";
				$error_message = "\$_SERVER[]:";
				foreach($_SERVER as $key => $var) {
					$error_message .= "\n".$key."=".$var;
				}
				$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
				$error_message .= $HTTP_RAW_POST_DATA;
				mail($admin_email, $error_subject, $error_message, $email_headers);
			}
		} else if (preg_match_all("/<risk-information-notification(.*)\<\/risk-information-notification>/Uis", $xml_response, $matches, PREG_SET_ORDER)){
			// check risk-information-notification
			preg_match_all("/<google-order-number>(.*)\<\/google-order-number>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$google_order_id = (isset($value[0][1]))?$value[0][1]:0;
			preg_match_all("/<avs-response>(.*)\<\/avs-response>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$avs_response_code = (isset($value[0][1]))?$value[0][1]:0;
			preg_match_all("/<cvn-response>(.*)\<\/cvn-response>/Uis", $xml_response, $value, PREG_SET_ORDER);
			$cvv2_match = (isset($value[0][1]))?$value[0][1]:0;
			$order_id = 0;
			if ($google_order_id) {
				$sql  = " SELECT order_id ";
				$sql .= " FROM " . $table_prefix . "orders ";
				$sql .= " WHERE transaction_id=" . $db->tosql($google_order_id, TEXT);
				$db->query($sql);
				while ($db->next_record()) {
					$order_id = $db->f("order_id");
				}
			}
			if ($order_id){
  
				$payment_parameters = array();
				$pass_parameters = array();
				$post_parameters = '';
				$pass_data = array();
				$variables = array();
				get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
  
				if($compare_mer_id != $payment_parameters['merchant_id'] || $compare_mer_key != $payment_parameters['merchant_key']) {
					// order id wasn't found
					$error_subject = "Google Checkout Error :: Authentication Failed";
					$error_message  = "Authentication Parameters:";
					$error_message .= "\n\$compare_mer_id=$compare_mer_id";
					$error_message .= "\n\$compare_mer_key=$compare_mer_key";
					$error_message .= "\n\n\$_SERVER[]:";
					foreach($_SERVER as $key => $var) {
						$error_message .= "\n".$key."=".$var;
					}
					$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
					$error_message .= $HTTP_RAW_POST_DATA;
					mail($admin_email, $error_subject, $error_message, $email_headers);
				} else {
					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET avs_response_code=" . $db->tosql($avs_response_code, TEXT) ;
					$sql .= ", cvv2_match=" . $db->tosql($cvv2_match, TEXT) ;
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
					$db->query($sql);
				}
			} else {
				// order number wasn't found
				$error_subject = "Google Checkout Error :: Order Number Missed";
				$error_message = "\$_SERVER[]:";
				foreach($_SERVER as $key => $var) {
					$error_message .= "\n".$key."=".$var;
				}
				$error_message .= "\n\n\$HTTP_RAW_POST_DATA:\n";
				$error_message .= $HTTP_RAW_POST_DATA;
				mail($admin_email, $error_subject, $error_message, $email_headers);
			}
		}
	}

	// Using a notification handshake to confirm successful processing
	header("HTTP/1.0 200 OK");
	header("Status: 200 OK");
	$handshake = "<notification-acknowledgment xmlns=\"http://checkout.google.com/schema/2\" serial-number=\"".$google_serial_number."\" />";
	echo $handshake;
	return;

?>