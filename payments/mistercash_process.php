<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  mistercash_process.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	/**
	 *
	 * Mollie's Mistercash (https://www.mollie.nl/) transaction handler by www.viart.com
	 * @documentation https://www.mollie.nl/files/documentatie/payments-api-en.html
	 *
	 **/

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	  
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	// get some variables from our payment settings
	$api_key    = isset($payment_parameters["api_key"]) ? $payment_parameters["api_key"] : false;
	if(!$api_key || $api_key == "WRITE_API_KEY_HERE"){
		$error_message = 'Payment parameters api key is missed!';
		mrcash_set_error($order_id, $error_message);
		echo $error_message;
		exit;
	}
	
	//set proper url
	if($payment_parameters["mode"] == "live"){
		$payment_url = "https://api.mollie.nl/v1/payments";
	} else if($payment_parameters["mode"] == "test"){
		$payment_url = "https://api.mollie.nl/v1/payments";
	} else {
		$error_message = 'Payment parameters mode should be "test" or "live"!';
		mrcash_set_error($order_id, $error_message);
		echo $error_message;
		exit;
	}

	//create pass paramenters manually to create correct json object.
	$pass_data = array();
	$pass_data["redirectUrl"] = $payment_parameters["redirectUrl"];
	$pass_data["method"] = "mistercash";
	$pass_data["metadata"] = array();
	$pass_data["metadata"]["order_id"] = $order_id;
	$pass_data["amount"] = $payment_parameters["amount"];
	$pass_data["description"] = $payment_parameters["description"];

	$pass_data_json = json_encode($pass_data);

	$request_headers = array(
	  "Accept: application/json",
	  "Authorization: Bearer " . $api_key,
	  "User-Agent: Viart Transaction Hendler",
	  "X-Mollie-Client-Info: " . php_uname(),
	  "Content-Type: application/json"
	);


	$ch = curl_init($payment_url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);


	if ($pass_data_json){

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $pass_data_json);
	}else{
		$error_message = 'Cannot prepare correct data!';
		mrcash_set_error($order_id, $error_message);
		echo $error_message;
		exit;
	}


	curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);


	$payment_response = curl_exec ($ch);

	if (curl_errno($ch) == CURLE_SSL_CACERT || curl_errno($ch) == CURLE_SSL_PEER_CERTIFICATE || curl_errno($ch) == 77 /* CURLE_SSL_CACERT_BADFILE (constant not defined in PHP though) */){
		/*
		 * On some servers, the list of installed certificates is outdated or not present at all (the ca-bundle.crt
		 * is not installed). So we tell cURL which certificates we trust. Then we retry the requests.
		 */
		$request_headers[] = "X-Mollie-Debug: used shipped root certificates";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($ch, CURLOPT_CAINFO, $payment_parameters["ssl_path"]);
		$payment_response = curl_exec($ch);
	}
	if (strpos(curl_error($ch), "certificate subject name 'mollie.nl' does not match target host") !== FALSE){
		/*
		 * On some servers, the wildcard SSL certificate is not processed correctly. This happens with OpenSSL 0.9.7
		 * from 2003.
		 */
		$request_headers[] = "X-Mollie-Debug: old OpenSSL found";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$payment_response = curl_exec($ch);
	}

	if (!($response_object = @json_decode($payment_response))){
		$error_message = 'Payment system answer is wrong!';
		mrcash_set_error($order_id, $error_message);
		echo $error_message;
		exit;
	}

	if (!empty($response_object->error)){
		$error_message = "Error executing API call ({$response_object->error->type}): {$response_object->error->message}. ";

		if (!empty($response_object->error->field)){
			$error_message .=  $object->error->field;
		}
		mrcash_set_error($order_id, $error_message);
		echo $error_message;
		exit;
	}
	if (curl_errno($ch)){
	  echo curl_errno($ch)." - ".curl_error($ch);
	  exit;
	}

	curl_close($ch);

	header("Location: " . $response_object->links->paymentUrl);
  

	function mrcash_set_error($order_id, $error_message){
		global $db, $table_prefix;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
	}

?>