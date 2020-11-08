<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  vxsbill_functions.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * VXSBill functions by ViArt Ltd - www.viart.com
 */

	function vxsbill_payment_request($pdata)
	{
		global $table_prefix, $db;

		$request_string = "";
		foreach ($pdata as $key => $value) {
			if ($key == "price") {
				$value = str_replace(".", "", $value);
			} elseif ($key == "country_code") {
				$value = get_db_value("SELECT country_code FROM " . $table_prefix . "countries WHERE country_name = " . $db->tosql($value, TEXT));
			}
			if (strlen($request_string)) $request_string .= "&";
			$request_string .= $key . "=" . urlencode($value);
		}

		$remote_address = get_ip();
		if (strlen($remote_address)) {
			$request_string .= "&ip=" . urlencode($remote_address);
		}

		$request_string = "?" . $request_string;
		return $request_string;
	}

	function vxsbill_payment_check()
	{
		global $table_prefix, $db, $payment_parameters, $variables, $error_message, $pending_message, $transaction_id;
		$sql  = " SELECT * ";
		$sql .= " FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($variables['order_id'], INTEGER);
		$db->query($sql);
		if($db->next_record()){
			$payment_order_id = $db->f("success_message");
			$ch = curl_init();
			if ($ch)
			{
				$post_params = 'order_id='.$payment_order_id;
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_URL, $payment_parameters['check_url']);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
				set_curl_options ($ch, $payment_parameters);
		
				$payment_response = curl_exec($ch);
		
				if (curl_error($ch)) {
					$error_message = curl_error($ch);
					return;
				}
		
				curl_close($ch);
				if ($payment_response) {
					$response_parameters = array();
					$response_parts = explode(":", $payment_response);
					if (sizeof($response_parts) == 1) {
						$error_message = "Bad response from gateway: " . $payment_response;
					} else {
						for($i = 0; $i < sizeof($response_parts); $i++) {
							$response_parameters[] = trim($response_parts[$i]);
						}
						if($response_parameters[0] == "OK"){
							$transaction_id = trim($response_parameters[1]);
							$status = trim($response_parameters[2]);
							$message = trim($response_parameters[3]);
							$email = trim($response_parameters[4]);
							if($status < 99){
								$pending_message = "Status:".$status." Message:".$message." This order will be reviewed later.";
							}elseif($status = 100){
								if(!strlen($transaction_id)){
									$pending_message = "Can't obtain transaction_id parameter. This order will be reviewed manually. Status:".$status." Message:".$message;
								}
							}else{
								$error_message = "Status:".$status." Message:".$message;
							}
						}else{
							$error_message = $payment_response;
						}
					}
				} else {
					$error_message = "Can't obtain data for your transaction.";
				}
			} else {
				$error_message = "Can't initialize cURL.";
			}
		}else{
			$error_message = "Can't obtain order_id from database.";
		}
		
	}

