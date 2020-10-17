<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  payson_invoice116_process.php                            ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Payson Invoice version 1.16 (https://www.payson.se/) transaction handler by www.viart.com
 */
	ini_set("display_errors", "1");

	error_reporting(E_ALL & ~E_STRICT);
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
mail ($mail_to, $site_url . " payson_invoice116_process.php", "Start script order: $order_id", $mail_headers);

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
/* debug email */
mail ($mail_to, $site_url . " payson_invoice116_process errors", $order_errors, $mail_headers);
		exit;
	}

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	$inputs_array = array();
	$order_amount = 0;
	$merchant_email = get_db_value("SELECT u.email FROM va_users u INNER JOIN va_items i ON u.user_id = i.user_id INNER JOIN va_orders_items oi ON i.item_id=oi.item_id WHERE order_id = " . $order_id);
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	//transaction type
	$pass_data["fundingList.fundingConstraint(0).constraint"] = "INVOICE";

	switch (strtoupper($pass_data["currencyCode"])) {
		case "EUR":
			$pass_data["currencyCode"] = "EUR";
			break;
		default:
			$pass_data["currencyCode"] = "SEK";
 	}

 	$payment_items = $variables["payment_items"];
	
	$i = 0;
$holder_commission = 0;
	foreach ($payment_items as $key => $payment_item) {

		$name = get_translation($payment_item["name"]);

		$price = $payment_item["price"];

		$quantity = $payment_item["quantity"];

		$sku = $payment_item["type"] . "_" . $i;
/*artizan special*/
if($payment_item["type"] == "item"){

		$holder_commission += $payment_item["price_incl_tax"] / 10;

}

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

	/**
	 * prepare reciever(s) info
	 * check money quota
	 */

	/**
	 * @var money_parce_error : Integer Wrong data flag
	 */
	$money_parce_error = 0;

	if(strlen($payment_parameters['divide_among'])){
		//$money_arr = explode(';', $payment_parameters['divide_among']);
		/**
		 * @var reciever_prepared_list : Array Prepared Array with receiver list
		 */
		$reciever_prepared_list = array();

		/**
		 * @var used_quota_symbol : Char "%" or "A" Flag will be checked for mixed values error
		 */
		$used_quota_symbol = null;

		/**
		 * @var used_R : Boolean flag will be check if Rest of amount value is used
		 */
		$used_R = false;

		/**
		 * @var money_only_list : Array Temporary array for money correlation between accounts
		 */
		$money_only_list = array();
/*
		for ($i = 0; $i < count($money_arr); $i++){
			if($i === 0){
				$primary_reciever_ammount = $money_arr[$i];
				if(!paysonCheckMoneyValue($primary_reciever_ammount)){
					$money_parce_error = 1;
					break;
				} else {
					$reciever_prepared_list[0][0] = $payment_parameters['reciever_email'];
				}
			} elseif($i == count($money_arr) - 1){
				if(!validate_money_input()){
					$money_parce_error = 1;
				}
			} else {
				$cur_data = explode(',', $money_arr[$i]);
				$reciever_prepared_list[$i][0] = $cur_data[0];
				$reciever_prepared_list[$i][1] = $cur_data[1];
				if($cur_data[0] != "R" && $used_quota_symbol === null){
					$used_quota_symbol = strtoupper(substr($val, -1));
				}
				if($cur_data[0] == "R"){
					$used_R = true;
				}
			}
		}
*/
	}
$money_parce_error = 1;

if($merchant_email){

		$item_amount = round($order_amount, 2);

		$holder_commission  = round($holder_commission, 2);
		$to_items_owner = $item_amount - $holder_commission;



		$pass_data["receiverList.receiver(0).email"] = $payment_parameters['reciever_email'];

		$pass_data["receiverList.receiver(0).amount"] = $holder_commission;

		$pass_data["receiverList.receiver(0).primary"] = "false";

		$pass_data["receiverList.receiver(1).email"] = $merchant_email;

		$pass_data["receiverList.receiver(1).amount"] = $to_items_owner;

		$pass_data["receiverList.receiver(1).primary"] = "true";
} else {
	/**
	 * default all money to one account
	 */
	if(!strlen($payment_parameters['divide_among']) || $money_parce_error === 1){

		$pass_data["receiverList.receiver(0).email"] = $payment_parameters['reciever_email'];

		$pass_data["receiverList.receiver(0).amount"] = $item_amount = round($order_amount, 2);

		$pass_data["receiverList.receiver(0).primary"] = "true";
	}
}
	//payson invoice params
	$pass_data["shippingAddress.name"] = $payment_parameters['delivery_first_name'] . " " . $payment_parameters['delivery_last_name'];
	$pass_data["shippingAddress.streetAddress"] = $payment_parameters['delivery_address'];
	$pass_data["shippingAddress.postalCode"] = $payment_parameters['delivery_zip'];
	$pass_data["shippingAddress.city"] = $payment_parameters['delivery_city'];
	$pass_data["shippingAddress.country"] = $payment_parameters['delivery_country'];

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
mail ($mail_to, $site_url . " payson_invoice116_process curl", "Pass data: $pass_data \n Result: $result" , $mail_headers);

	if ($result === false) {
		die('Curl error: ' . curl_error($ch));
	}
	$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	parse_str($result, $response_array);
	curl_close($ch);

//var_dump($result);
	if ($response_code == 200 &&  $response_array["responseEnvelope_ack"] === "SUCCESS") {
		$token = $response_array["TOKEN"];
		$db->query("UPDATE " . $table_prefix . "orders SET authorization_code='" . $token . "' WHERE order_id = ". $order_id);
		header("Location: " . $payment_parameters['forward_url'] . "?token=" . $token);
	} else {
		echo "Error sending data to payson";
	}
	exit;
/*
	function paysonCheckMoneyValue($val){
		if(strtoupper($val) == "R" || strtoupper(substr($val, -1)) == "A" || substr($val, -1) == "%"){
			return true;
		} else {
			return false;
		}
	}

	function validate_money_input(){
		return true;
	}
*/
	?>