<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  stripe_alipay_checkout.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe Alipay Payment Gateway handler by http://www.viart.com/
 */
	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."includes/parameters.php");

	require_once (dirname(__FILE__) . '/stripe/init.php');

	$order_id = get_order_id();

	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	// prepare return url parameter
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);
	$return_url = get_setting_value($payment_parameters, "return_url", $secure_url."order_final.php"); 

	// get general order parameters 
	$secret_key = get_setting_value($payment_parameters, "secret_key", "");
	$order_amount = get_setting_value($payment_parameters, "amount", "");
	$order_currency = get_setting_value($payment_parameters, "currency", "");
	$order_description = get_setting_value($payment_parameters, "description", "");
	$metadata = array();

	// meta paremeters
	$meta_params = array(
		"order_id", "name", "first_name", "last_name", "email", 
		"address1", "address2", "city", "state_code", "province", "zip", "country_code",
		"phone", "daytime_phone", "evening_phone", "cell_phone", "fax",
	);

	$owner_address_params = array(
		"city" => "city",
		"country" => "country_code",
		"line1" => "address1",
		"line2" => "address2",
		"postal_code" => "zip",
		"state" => "state_code",
	);

	$owner = array(); $owner_address = array();

	// get order data
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		// populate owner data
		$email = $db->f("email");
		$name = $db->f("name");
		if (!$name) {
		 $name = $db->f("first_name")." ".$db->f("last_name");
		}
		$phone = $db->f("phone");
		if (!$phone) { $phone = $db->f("daytime_phone"); }
		if (!$phone) { $phone = $db->f("evening_phone"); }
		if (!$phone) { $phone = $db->f("cell_phone"); }

		if ($email) {
			$owner["email"] = $email;
		}
		$owner["name"] = $name;
		if ($phone) {
			$owner["phone"] = $phone;
		}

		$owner_address = array();
		foreach ($owner_address_params as $param_name => $db_name) {
			$param_value = $db->f($db_name);
			if (strlen($param_value)) {
				$owner_address[$param_name] = $param_value;
			}
		}
		$owner["address"] = $owner_address;

		// populate meta parameters
		foreach ($meta_params as $meta_name) {
			$meta_value = $db->f($meta_name);
			if (strlen($meta_value)) {
				$metadata[$meta_name] = $meta_value;
			}
		}

	} else {
		$error_message = ORDER_EXISTS_ERROR;
		echo $error_message;
		return;
	}

	$data = array(
	  "type" => "alipay",
	  "amount" => $order_amount,
  	"currency" => $order_currency,
		"redirect" => array( "return_url" => $return_url),
	  "owner" => $owner,
	  "metadata" => $metadata,
	);

	// save here error message to show
	$error_message = "";

	// Set your secret key: remember to change this to your live secret key in production
	// See your keys here https://dashboard.stripe.com/account/apikeys 
	\Stripe\Stripe::setApiKey($secret_key);

	try {
		$source = \Stripe\Source::create($data);

		$source_id = $source->id;
		$source_url = $source->redirect->url;
		$source_secret = $source->client_secret;

		header("Location: ".$source_url);
		exit;
  
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

	echo "Error occurred:\n<br/>";
	echo $error_message;
	exit;
