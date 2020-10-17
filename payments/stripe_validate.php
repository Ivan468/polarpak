<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  stripe_validate.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe Checkout Payment Gateway handler by http://www.viart.com/
 */

	$stripe_new = false;
	if (file_exists(dirname(__FILE__)."/stripe/init.php")) {
		// new PHP 5.3.3
		$stripe_new = true;
		require(dirname(__FILE__) . '/stripe/init.php');
	} else {
		// old PHP 5.2
		require(dirname(__FILE__) . '/stripe/lib/Stripe.php');
	}

	// get payments parameters for validation
	$dataSecretKey = get_setting_value($payment_parameters, "data-secret-key", "");
	$dataCurrency = get_setting_value($payment_parameters, "data-currency", "");

	// Get the credit card details submitted by the Stripe
	$stripeToken = get_param("stripeToken");
	$stripeEmail = get_param("stripeEmail");
	// other paremeters
	// stripeBillingName, stripeBillingAddressLine1, stripeBillingAddressZip, stripeBillingAddressCity, stripeBillingAddressCountry, 
	// stripeShippingName, stripeShippingAddressLine1, stripeShippingAddressZip, stripeShippingAddressCity, stripeShippingAddressCountry,

	// check parameters
	if (!strlen($stripeToken)) {
		$error_message = str_replace("{param_name}", "stripeToken", CANNOT_OBTAIN_PARAMETER_MSG);
	}

	if (strlen($error_message)) {
 		return;
	}

	// get order data
	$order_id = get_order_id();
	$order_desc = ""; $metadata = array(); 
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$order_total = $db->f("order_total");						
		$payment_currency_rate = $db->f("payment_currency_rate");
		$payment_currency_code = $db->f("payment_currency_code");
		$payment_total = round($order_total * $payment_currency_rate * 100);
		if (!$stripeEmail) {
			$stripeEmail = $db->f("email");
		}
		$order_desc  = "Order #".$order_id;
		$name = $db->f("name");
		$first_name = $db->f("first_name");
		$last_name = $db->f("last_name");
		if ($name) { $order_desc .= " :: ".$name; }
		else if ($first_name) { $order_desc .= " :: ".$first_name . " " .$last_name;  }
		if ($stripeEmail) { $order_desc .= " :: ".$stripeEmail; }

		// save metadata
		$metadata["order_id"] = $order_id;

	} else {
		$error_message = ORDER_EXISTS_ERROR;
		return;
	}


	// Set your secret key: remember to change this to your live secret key in production
	// See your keys here https://manage.stripe.com/account
	if ($stripe_new) {
		\Stripe\Stripe::setApiKey($dataSecretKey);
	} else {
		Stripe::setApiKey($dataSecretKey);
	}

	// Create the charge on Stripe's servers - this will charge the user's card
	$data = array(
	  	"amount" => $payment_total, // amount in cents
		  "currency" => $payment_currency_code,
  		"card" => $stripeToken,
		  "description" => $order_desc,
		  "metadata" => $metadata,
		);

	if ($stripe_new) {
		try {
			$charge = \Stripe\Charge::create($data);
			$transaction_id = $charge->id;
  
			// update credit card information returned from Stripe if it was returned
			$cc_number = ""; $cc_name = ""; $cc_type_code = "";
			if ($charge->card) {
				$cc_last4 = $charge->card->last4;
				if ($cc_last4) {
					$cc_number = "************".$cc_last4;
				}
				$cc_type_code = $charge->card->brand;
				$cc_name = $charge->card->name;
				$cc_expiry_date = "";
				$cc_exp_month = $charge->card->exp_month;
				$cc_exp_year = $charge->card->exp_year;
				if ($cc_exp_month && $cc_exp_year) {
					$cc_expiry_date = array($cc_exp_year, $cc_exp_month, 1, 0, 0, 0);
				}
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
  
		} catch(\Stripe\Error\Card $e) {
		  // The card has been declined
			$error_message = $e->getMessage();
		} catch(\Stripe\Error\InvalidRequest $e) {
			$error_message = $e->getMessage();
		} catch(\Stripe\Error\Api $e) {
			$error_message = $e->getMessage();
		} catch(\Stripe\Error\Authentication $e) {
			$error_message = $e->getMessage();
		}
	} else {
		try {
			$charge = Stripe_Charge::create($data);
			$transaction_id = $charge->id;
  
			// update credit card information returned from Stripe 
			$cc_number = "";
			$cc_last4 = $charge->card->last4;
			if ($cc_last4) {
				$cc_number = "************".$cc_last4;
			}
			$cc_type_code = $charge->card->brand;
			$cc_name = $charge->card->name;
			$cc_expiry_date = "";
			$cc_exp_month = $charge->card->exp_month;
			$cc_exp_year = $charge->card->exp_year;
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
  
		} catch(Stripe_CardError $e) {
		  // The card has been declined
			$error_message = $e->getMessage();
		} catch(Stripe_InvalidRequestError $e) {
			$error_message = $e->getMessage();
		} catch(Stripe_ApiError $e) {
			$error_message = $e->getMessage();
		} catch(Stripe_AuthenticationError $e) {
			$error_message = $e->getMessage();
		}
	}

