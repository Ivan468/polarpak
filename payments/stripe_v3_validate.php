<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  stripe_v3_validate.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe v3 Checkout module for Viart Shop http://www.viart.com/
 */	

	if (file_exists(dirname(__FILE__)."/stripe_v3/init.php")) {
		require(dirname(__FILE__).'/stripe_v3/init.php');
	} else if (file_exists(dirname(__FILE__)."/stripe/init.php")) {
		require(dirname(__FILE__).'/stripe/init.php');
	} else {
		die("Please download Stripe module from https://github.com/stripe/stripe-php/releases and uploaded unzip files to payments/stripe/ folder.");
	}

	// check status parameter for cancelled transaction
	$va_status = get_param("va_status");
	if (strtolower($va_status) == "cancel") {
		// check if user has cancelled the order
		$error_message = "Your transaction has been cancelled.";
		return;
	} 

	// check payment_intent data from success_message
	$success_message = "";
	$sql  = " SELECT transaction_id, success_message, error_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$success_message = $db->f("success_message");
		$transaction_id = $db->f("transaction_id");
		$error_message = $db->f("error_message");
		$pending_message = $db->f("pending_message");
	}

	$stripe_session_id = "";
	$stripe_payment_intent = "";
	if ($success_message) {
		$stripe_data = json_decode($success_message, true);
		$stripe_session_id = get_setting_value($stripe_data, "session_id");
		$stripe_payment_intent = get_setting_value($stripe_data, "payment_intent");
	}

	if (!$stripe_payment_intent) {
		$error_message = "Can't find necessary information about Stripe payment intent.";
		return;
	}

	// get payment data
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables);

	// general payment data
	$publishable_key = get_setting_value($payment_parameters, "publishable_key"); 
	$secret_key = get_setting_value($payment_parameters, "secret_key"); 
	$currency_code = get_setting_value($payment_parameters, "currency_code", "USD"); 

	try {
		\Stripe\Stripe::setApiKey($secret_key);
		$payment_intent = \Stripe\PaymentIntent::retrieve($stripe_payment_intent);
  
		$payment_status = $payment_intent->status;
		if (strtolower($payment_status) != "succeeded") {
			$error_message = "The order wasn't paid yet and the current status is " . $payment_status;
			return;
		}
		$transaction_id = $payment_intent->id;

		// check charge object to save credit card data for order
		if(isset($payment_intent->charges) && isset($payment_intent->charges->data)) {
			$charge = $payment_intent->charges->data[0];
			if (isset($charge->payment_method_details) && isset($charge->payment_method_details->card)) {
				$card_data = $charge->payment_method_details->card;
				// update credit card information returned from Stripe 
				$cc_number = "";
				$cc_last4 = $card_data->last4;
				if ($cc_last4) {
					$cc_number = "************".$cc_last4;
				}
				$cc_type_code = $card_data->brand;
				$cc_name = (isset($charge->billing_details) && isset($charge->billing_details->name)) ? $charge->billing_details->name : "";
				$cc_expiry_date = "";
				$cc_exp_month = $card_data->exp_month;
				$cc_exp_year = $card_data->exp_year;
				if ($cc_exp_month && $cc_exp_year) {
					$cc_expiry_date = array($cc_exp_year, $cc_exp_month, 1, 0, 0, 0);
				}
    
				$cc_type = "";
				if ($cc_type_code) {
					// check viart cc_type
					$sql  = " SELECT credit_card_id FROM " . $table_prefix . "credit_cards ";
					$sql .= " WHERE credit_card_code=" . $db->tosql($cc_type_code, TEXT);
					$sql .= " OR credit_card_name=" . $db->tosql($cc_type_code, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$cc_type = $db->f("credit_card_id");
					}
				}
				// update information
				if ($cc_name || $cc_type || $cc_number) {
					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET cc_name=" . $db->tosql($cc_name, TEXT);
					if (strlen($cc_type)) {
						$sql .= " , cc_type=" . $db->tosql($cc_type, INTEGER);
					}
					if (strlen($cc_number)) {
						$sql .= " , cc_number=" . $db->tosql($cc_number, TEXT);
					}
					if (is_array($cc_expiry_date)) {
						$sql .= " , cc_expiry_date=" . $db->tosql($cc_expiry_date, DATETIME);
					}
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
					$db->query($sql);
				}
			}
		}

	} catch(Exception $e) {
		$error_message = $e->getMessage();
		return;
	}
