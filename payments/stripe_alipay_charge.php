<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  stripe_alipay_charge.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe Alipay Payment Gateway handler by http://www.viart.com/
 */

	require_once (dirname(__FILE__) . '/stripe/init.php');

	// get payments parameters for validation
	$secret_key = get_setting_value($payment_parameters, "secret_key", "");
	$order_amount = get_setting_value($payment_parameters, "amount", "");
	$order_currency = get_setting_value($payment_parameters, "currency", "");

	// Get the credit card details submitted by the Stripe
	$source_id = get_param("source");
	$livemode = get_param("livemode");
	$client_secret = get_param("client_secret");

	// check parameters
	if (!strlen($source_id)) {
		$error_message = str_replace("{param_name}", "source", CANNOT_OBTAIN_PARAMETER_MSG);
	}

	if (strlen($error_message)) {
 		return;
	}

	// meta paremeters
	$meta_params = array(
		"order_id", "name", "first_name", "last_name", "email", 
		"address1", "address2", "city", "state_code", "province", "zip", "country_code",
		"phone", "daytime_phone", "evening_phone", "cell_phone", "fax",
	);

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
		$email = $db->f("email");

		$order_desc  = "Charge for Order #".$order_id;
		if ($email) { $order_desc .= " (".$email.")"; }

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


	// Set your secret key: remember to change this to your live secret key in production
	// See your keys here https://manage.stripe.com/account
	\Stripe\Stripe::setApiKey($secret_key);

	// Create the charge on Stripe's servers - this will charge the user's source
	$data = array(
	  	"amount" => $payment_total, // amount in cents
		  "currency" => $payment_currency_code,
  		"source" => $source_id,
		  "description" => $order_desc,
		  "metadata" => $metadata,
		);

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
