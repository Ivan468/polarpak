<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  vxsbill_process.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * VXSBill (www.vxsbill.com) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."includes/date_functions.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}

	$error_message = '';
	
	$payment_parameters = array();
	$pass_parameters = array();
	$post_params = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables);

	$ch = curl_init();
	if ($ch)
	{
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_URL, $payment_parameters['create_order_url']);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		set_curl_options ($ch, $payment_parameters);

		$payment_response = curl_exec($ch);

		if (curl_error($ch)) {
			$error_message = curl_error($ch);
			echo $error_message;
			exit;
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
					if(isset($response_parameters[1]) && strlen($response_parameters[1])){
						$pending_message = "A client is redirected to the '".$payment_parameters['payment_url'].'?site='.$payment_parameters['site'].'&order_id='.$response_parameters[1]."' to complete a purchase.";
						$sql  = " UPDATE " . $table_prefix . "orders ";
						$sql .= " SET success_message=" . $db->tosql($response_parameters[1], TEXT) ;
						$sql .= ", pending_message=" . $db->tosql($pending_message, TEXT) ;
						$sql .= " WHERE order_id=" . $db->tosql($variables['order_id'], INTEGER) ;
						$db->query($sql);
						if(strpos($payment_parameters['payment_url'], "?")){
							header("Location: " . $payment_parameters['payment_url'].'&site='.$payment_parameters['site'].'&order_id='.$response_parameters[1]);
						}else{
							header("Location: " . $payment_parameters['payment_url'].'?site='.$payment_parameters['site'].'&order_id='.$response_parameters[1]);
						}
					}else{
						$error_message = "Can't obtain order_id for your transaction.";
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

	if(strlen($error_message)){
		echo $error_message;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($variables['order_id'], INTEGER) ;
		$db->query($sql);
		update_order_status($variables['order_id'], $variables["failure_status_id"] , true, "", $error_message);
	}
	exit;
?>