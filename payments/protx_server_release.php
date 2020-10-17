<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  protx_server_release.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VSP (www.protx.com) transaction handler by www.viart.com
 */

	$root_folder_path = "../";
	include_once ($root_folder_path ."payments/protx_server_functions.php");

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$advanced_url= $payment_parameters['releaseURL'];
	
	$sql  = " SELECT transaction_id FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$str_transaction_id = get_db_value($sql);
	$array_transaction_id = explode(' ', $str_transaction_id);
	$array_transaction_id = protx_vsp_get_associative_array('=', $array_transaction_id);

	$sql  = " SELECT authorization_code FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$TxAuthNo = get_db_value($sql);

	$post_params= 'VPSProtocol='.urlencode($payment_parameters['VPSProtocol']).'&TxType=RELEASE'.'&Vendor='.urlencode($payment_parameters['Vendor']).'&VendorTxCode='.urlencode($payment_parameters['VendorTxCode']).'&VPSTxId='.urlencode($array_transaction_id['VPSTxId']).'&SecurityKey='.urlencode($array_transaction_id['SecurityKey']).'&TxAuthNo='.urlencode($TxAuthNo);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $advanced_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 90);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	set_curl_options ($ch, $payment_parameters);

	$result=curl_exec ($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error_message = '';
	if (curl_errno($ch))
		$error_message = curl_errno($ch)." - ".curl_error($ch);
		curl_close ($ch);
	if (strlen($error_message)) {
		return;
	}
	if ($http_code != 200){
		$error_message = "http code:".$http_code.", please check your payment settings.";
		return;
	}
	if (!strlen($result)) {
		$error_message = "Empty response from ProTX Server, please check your payment settings.";
		return;
	}

	$output = explode(chr(10),$result);
	$response = array();
	$response = protx_vsp_get_associative_array('=', $output);
	
	if (!isset($response["Status"]) || $response["Status"] != 'OK'){
		if (isset($response['StatusDetail'])){
			$error_message .= ' '.$response['StatusDetail'].' ';
		}else{
			$error_message .= " Transaction could not be released ";
		}
	}
?>