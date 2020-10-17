<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  stripe_direct.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe Direct Payment Gateway handler by http://www.viart.com/
 */
	$stripe_new = false;
	if (file_exists(dirname(__FILE__)."/stripe.new/init.php")) {
		// new PHP 5.3.3
		$stripe_new = true;
		require(dirname(__FILE__) . '/stripe.new/init.php');
	} else {
		// old PHP 5.2
		require(dirname(__FILE__) . '/stripe/lib/Stripe.php');
	}

	// get general order parameters 
	$secret_key = get_setting_value($payment_parameters, "secret_key", "");
	$order_amount = get_setting_value($payment_parameters, "amount", "");
	$order_currency = get_setting_value($payment_parameters, "currency", "");
	$order_description = get_setting_value($payment_parameters, "description", "");
	$metadata = array();

	// card parameters
	$card_number = get_setting_value($pass_data, "card_number", "");
	$card_exp_month = get_setting_value($pass_data, "card_exp_month", "");
	$card_exp_year = get_setting_value($pass_data, "card_exp_year", "");
	$card_cvc = get_setting_value($pass_data, "card_cvc", "");
	$card_name = get_setting_value($pass_data, "card_name", "");

	// address parameters
	$address_line1 = get_setting_value($pass_data, "address_line1", "");
	$address_line2 = get_setting_value($pass_data, "address_line2", "");
	$address_city = get_setting_value($pass_data, "address_city", "");
	$address_zip = get_setting_value($pass_data, "address_zip", "");
	$address_state = get_setting_value($pass_data, "address_state", "");
	$address_country = get_setting_value($pass_data, "address_country", "");

	// meta paremeters
	$meta_params = array(
		"order_id", "name", "first_name", "last_name", "email", 
		"address1", "address2", "city", "state_code", "province", "zip", "country_code",
		"phone", "daytime_phone", "evening_phone", "cell_phone", "fax",
	);

	// get order data
	$order_id = get_order_id();
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		// populate meta parameters
		foreach ($meta_params as $meta_name) {
			$meta_value = $db->f($meta_name);
			if (strlen($meta_value)) {
				$metadata[$meta_name] = $meta_value;
			}
		}

	} else {
		$error_message = ORDER_EXISTS_ERROR;
		return;
	}

	// prepare card data
	$card = array(
		"number" => $card_number,
		"exp_month" => $card_exp_month,
		"exp_year" => $card_exp_year,
		"cvc" => $card_cvc,
		"name" => $card_name,
	);
	if ($address_line1) { $card["address_line1"] = $address_line1; }
	if ($address_line2) { $card["address_line2"] = $address_line2; }
	if ($address_city) { $card["address_city"] = $address_city; }
	if ($address_zip) { $card["address_zip"] = $address_zip; }
	if ($address_state) { $card["address_state"] = $address_state; }
	if ($address_country) { $card["address_country"] = $address_country; }

	$data = array(
		"amount" => $order_amount, 
	  "currency" => $order_currency,
 		"card" => $card,
	  "description" => $order_description,
	  "metadata" => $metadata,
	);

	// Set your secret key: remember to change this to your live secret key in production
	// See your keys here https://manage.stripe.com/account
	if ($stripe_new) {
		\Stripe\Stripe::setApiKey($secret_key);

		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$charge = \Stripe\Charge::create($data);
			$transaction_id = $charge->id;
		} catch(\Stripe\Error\Card $e) {
		  // The card has been declined
			$error_message = $e->getMessage();
		} catch(\Stripe\Error\InvalidRequest $e) {
			$error_message = $e->getMessage();
		} catch(\Stripe\Error\Api$e) {
			$error_message = $e->getMessage();
		} catch(\Stripe\Error\Authentication $e) {
			$error_message = $e->getMessage();
		}

	} else {
		Stripe::setApiKey($secret_key);

		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			$charge = Stripe_Charge::create($data);
			$transaction_id = $charge->id;
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

?>