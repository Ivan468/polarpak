<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ccbill_check.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CCBill (http://ccbill.com/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$cb_order_id = get_param("va_order_id");
	$clientAccnum = get_param("clientAccnum");

	$cb_customer_fname = get_param("customer_fname");
	$cb_customer_lname = get_param("customer_lname");
	$cb_email = get_param("email");
	$cb_address1 = get_param("address1");
	$cb_city = get_param("city");
	$cb_state = get_param("state");
	$cb_country = get_param("country");
	$cb_phone_number = get_param("phone_number");
	$cb_zipcode = get_param("zipcode");
	$cb_start_date = get_param("start_date");

	$cb_price = get_param("price");
	$subscription_id = get_param("subscription_id");
	$denialId = get_param("denialId");
	$reasonForDeclineCode = get_param("reasonForDeclineCode");
	$reasonForDecline = get_param("reasonForDecline");
	$responseDigest = get_param("responseDigest");
	
	$status_error = "";
	$order_status_id = 0;
	$event_description = "";
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($cb_order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
	// calculate full and trimmed digest as we have no idea the correct way CCbill calculate it
	$salt = $payment_parameters["salt"];
	$full_denial_digest = md5($denialId."0".$salt);
	$trimmed_denial_digest = md5(ltrim($denialId, "0")."0".$salt);
	$full_success_digest = md5($subscription_id."1".$salt);
	$trimmed_success_digest = md5(ltrim($subscription_id, "0")."1".$salt);

	if(isset($payment_parameters["clientAccnum"]) && ($payment_parameters["clientAccnum"] == $clientAccnum)){
		if($reasonForDeclineCode){
			if($full_denial_digest == $responseDigest || $trimmed_denial_digest == $responseDigest){
				$order_status_id = $variables["failure_status_id"];
				$error_message = "Error code: ".$reasonForDeclineCode.", ".$reasonForDecline;
				$event_description = "denialId: ".$denialId;
				if(strlen($subscription_id)){
					$event_description .= ". subscription_id: ".$subscription_id;
				}
				$event_description .= ". ".$error_message;
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($denialId, TEXT) ;
				$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
				$sql .= " WHERE order_id=" . $db->tosql($cb_order_id, INTEGER) ;
				$db->query($sql);
			}else{
				echo "responseDigest is corrupted.";
			}
		}else{
			if($full_success_digest == $responseDigest || $trimmed_success_digest == $responseDigest){
				$order_status_id = $variables["success_status_id"];
				$event_description = "subscription_id: ".$subscription_id;
				if(strlen($denialId)){
					$event_description .= ". denialId: ".$denialId.". Error code: ".$reasonForDeclineCode.", ".$reasonForDecline;
				}
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($subscription_id, TEXT) ;
				$sql .= " WHERE order_id=" . $db->tosql($cb_order_id, INTEGER) ;
				$db->query($sql);
			}else{
				echo "responseDigest is corrupted.";
			}
		}
		if ($order_status_id) {
			$t = new VA_Template('.'.$settings["templates_dir"]);
			update_order_status($cb_order_id, $order_status_id, true, "", $status_error);

			$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
			$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
			$sql .= " VALUES( ";
			$sql .= $db->tosql($cb_order_id, INTEGER).", ";
			$sql .= $db->tosql($order_status_id, INTEGER).", ";
			$sql .= $db->tosql(va_time(), DATETIME).", ";
			$sql .= $db->tosql("CCBill Status Updated", TEXT).", ";
			$sql .= $db->tosql($event_description, TEXT);
			$sql .= " ) ";
			$db->query($sql);

			$sql  = " SELECT * FROM " . $table_prefix . "orders ";
			$sql .= " WHERE order_id=" . $db->tosql($cb_order_id, INTEGER) ;
			$db->query($sql);
			if ($db->next_record()) {
				$name = $db->f("name");
				$first_name = $db->f("first_name");
				$last_name = $db->f("last_name");
				$email = $db->f("email");
				$address1 = $db->f("address1");
				$city = $db->f("city");
				$state_code = $db->f("state_code");
				$zip = $db->f("zip");
				$country_code = $db->f("country_code");
				$phone = $db->f("phone");
				if(!strlen($name) && !strlen($first_name) && !strlen($last_name)){
					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET name=" . $db->tosql(($cb_customer_fname." ".$cb_customer_lname), TEXT) ;
					$sql .= ", first_name=" . $db->tosql($cb_customer_fname, TEXT) ;
					$sql .= ", last_name=" . $db->tosql($cb_customer_lname, TEXT) ;
					$sql .= ", email=" . $db->tosql($cb_email, TEXT) ;
					$sql .= ", address1=" . $db->tosql($cb_address1, TEXT) ;
					$sql .= ", city=" . $db->tosql($cb_city, TEXT) ;
					$sql .= ", state_code=" . $db->tosql($cb_state, TEXT) ;
					$sql .= ", zip=" . $db->tosql($cb_zipcode, TEXT) ;
					$sql .= ", country_code=" . $db->tosql($cb_country, TEXT) ;
					$sql .= ", phone=" . $db->tosql($cb_phone_number, TEXT) ;
					$sql .= " WHERE order_id=" . $db->tosql($cb_order_id, INTEGER) ;
					$db->query($sql);
				}
			}
		} else {
			// save event description to debug if order_id available
			if ($cb_order_id) {
				$event_description = var_export($_POST, true);

				$r->set_value("order_id", $cb_order_id);
				$r->set_value("status_id", 0);
				$r->set_value("event_date", va_time());
				$r->set_value("event_type", "payment_notification");
				$r->set_value("event_name", "CCBill Postback Error");
				$r->set_value("event_description", $event_description);

				$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_events ";
				$sql .= " WHERE order_id=" . $db->tosql($cb_order_id, INTEGER);
				$sql .= " AND event_type='payment_notification' ";
				$sql .= " AND event_name='CCBill Postback Error' ";
				$postback_errors = get_db_value($sql);
				if ($postback_errors <= 3) {
					$r->insert_record();
				}
			}
		}
	}
