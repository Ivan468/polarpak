<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payson115_process.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Payson version 1.15 (https://www.payson.se/) transaction handler by www.viart.com [ViArt SHOP 4.2.1]
 */



	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

/* general debug data */
$mail_to = "enquiries@viart.com";
$mail_headers = "From: " . $admin_email;
$admin_email = get_setting_value($settings, "admin_email", "");
$site_url = get_setting_value($settings, "site_url", "");

/* debug email */
mail ($mail_to, $site_url . " payson115_process.php", "Start script", $mail_headers);

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
/* debug email */
mail ($mail_to, $site_url . " payson115_process.php errors", $order_errors, $mail_headers);
		exit;
	}

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	$inputs_array = array();
	$order_amount = 0;

	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	switch (strtoupper($pass_data["currencyCode"])) {
		case "EUR":
			$pass_data["currencyCode"] = "EUR";
			break;
		default:
			$pass_data["currencyCode"] = "SEK";
 	}

 	$payment_items = $variables["payment_items"];
	$i = 0;
	foreach ($payment_items as $key => $payment_item) {
		$name = get_translation($payment_item["name"]);
		$price = $payment_item["price"];
		$quantity = $payment_item["quantity"];
		$sku = $payment_item["type"] . "_" . $i;
		if($payment_item["type"] == "correction" && $price < 1){
			continue;
		}
		$pass_data["orderItemList.orderItem(" . $i . ").description"] = $name;
		$pass_data["orderItemList.orderItem(" . $i . ").sku"] = $sku;
		$pass_data["orderItemList.orderItem(" . $i . ").quantity"] = $quantity;
		$pass_data["orderItemList.orderItem(" . $i . ").unitPrice"] = $price;
		$pass_data["orderItemList.orderItem(" . $i . ").taxPercentage"] = 0;
		$order_amount += $price * $quantity;
		$i++;
	}
	
	//reciever info
	$pass_data["receiverList.receiver(0).email"] = $payment_parameters['reciever_email'];
	$pass_data["receiverList.receiver(0).amount"] = $item_amount = round($order_amount, 2);;
	$pass_data["receiverList.receiver(0).primary"] = "true";

	//payson api headers
	$payson_headers = array();
	$payson_headers[] = 'PAYSON-SECURITY-USERID:   ' . $payment_parameters["api_user_id"];
	$payson_headers[] = 'PAYSON-SECURITY-PASSWORD: ' . $payment_parameters["api_pwd"];
	$payson_headers[] = 'PAYSON-APPLICATION-ID:    ' . null;
	$payson_headers[] = 'PAYSON-MODULE-INFO:       ' . "Viart payson 1.0";
	$payson_headers[] = 'Content-type:       ' . "application/x-www-form-urlencoded";

	$pass_data = http_build_query($pass_data);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HTTPHEADER, $payson_headers);
	curl_setopt($ch, CURLOPT_URL, $variables['advanced_url']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pass_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$result = curl_exec($ch);

/* debug */
mail ($mail_to, $site_url . " payson115_process.php curl", "Pass data: $pass_data \n Result: $result" , $mail_headers);

	if ($result === false) {
		die('Curl error: ' . curl_error($ch));
	}

	$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	parse_str($result, $response_array);
	curl_close($ch);
	if ($response_code == 200 &&  $response_array["responseEnvelope_ack"] === "SUCCESS") {
		$token = $response_array["TOKEN"];
		$db->query("UPDATE " . $table_prefix . "orders SET authorization_code='" . $token . "' WHERE order_id = ". $order_id);
		header("Location: " . $payment_parameters['forward_url'] . "?token=" . $token);
	} else {
		echo "Error sending data to payson";
	}
	exit;

?>