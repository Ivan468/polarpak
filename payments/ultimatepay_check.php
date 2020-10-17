<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ultimatepay_check.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * UltimatePay (http://www.ultimatepay.com/) transaction handler by www.viart.com
 */

	$return_action = get_param("return_action");
	if (strtolower($return_action) == 'cancel') {
		$error_message = "Your transaction has been cancelled.";
		return;
	}

	$token = get_param("token");
	$request = 'token='.$token.'&sn='.$payment_parameters['sn'].'&method=GetTransStatus';

	$ch = curl_init();
	if ($ch){
		curl_setopt($ch, CURLOPT_URL, $payment_parameters['notify_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		set_curl_options ($ch, $payment_parameters);
	
		$response = curl_exec ($ch);
		if (!$response) {
			$error_message = "Empty response from gateway.";
			return;
		}
		curl_close ($ch);
	
		$array_response = array();
		$array_response = explode('&', $response);
		$payment_response = array();
		foreach($array_response as $array_value){
			$value = array();
			$value = explode('=', $array_value);
			$payment_response[$value[0]] = html_entity_decode($value[1]);
		}
		$transaction_id = '';
		if(isset($payment_response['order_number'])){
			$transaction_id = $payment_response['order_number'];
		}
	
		if(isset($payment_response['pbctrans']) && strlen($payment_response['pbctrans'])){
			$event_description  = '';
			$event_description .= (isset($payment_response['dtdatetime']))? 'dtdatetime: '.$payment_response['dtdatetime'].' ':'';
			$event_description  = 'pbctrans: '.$payment_response['pbctrans'];
			$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
			$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
			$sql .= " VALUES( ";
			$sql .= $db->tosql($order_id, INTEGER).", ";
			$sql .= $db->tosql($variables["pending_status_id"], INTEGER).", ";
			$sql .= $db->tosql(va_time(), DATETIME).", ";
			$sql .= $db->tosql('System Status Updated', TEXT).", ";
			$sql .= $db->tosql($event_description, TEXT);
			$sql .= " ) ";
			$db->query($sql);
		}
	
		if(isset($payment_response['result'])){
			if(strtolower($payment_response['result']) == 'paid'){
				if(!strlen($transaction_id)){
					$pending_message = "Can't obtain transaction_id parameter. This order will be reviewed manually.";
				}
			}elseif(strtolower($payment_response['result']) == 'auth'){
				$pending_message = "System has reserved funds, but the payment is not yet complete.";
			}elseif(strtolower($payment_response['result']) == 'pending'){
				$pending_message = "The order is completed, but funds are not yet received.";
			}else{
				$error_message = "Your transaction has been declined.";
			}
		}else{
			$error_message = "Can't obtain 'result' of transaction.";
		}
	}else{
		$error_message .= "Can't initialize cURL.";
	}
?>