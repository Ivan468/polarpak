<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  multisafepay_process.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * MultiSafePay (http://www.multisafepay.nl/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

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

	$account = isset($payment_parameters['account'])?xml_escape_utf8($payment_parameters['account']):'';
	$site_id = isset($payment_parameters['site_id'])?xml_escape_utf8($payment_parameters['site_id']):'';
	$site_secure_code = isset($payment_parameters['site_secure_code'])?xml_escape_utf8($payment_parameters['site_secure_code']):'';
	$notification_url = isset($payment_parameters['notification_url'])?xml_escape_utf8($payment_parameters['notification_url']):'';
	$cancel_url = isset($payment_parameters['cancel_url'])?xml_escape_utf8($payment_parameters['cancel_url']):'';
	$redirect_url = isset($payment_parameters['redirect_url'])?xml_escape_utf8($payment_parameters['redirect_url']):$notification_url;
	$close_window = isset($payment_parameters['close_window'])?xml_escape_utf8($payment_parameters['close_window']):'false';

	$locale = isset($payment_parameters['locale'])?xml_escape_utf8($payment_parameters['locale']):'';
	$ipaddress = isset($payment_parameters['ipaddress'])?xml_escape_utf8($payment_parameters['ipaddress']):'';
	$forwardedip = isset($payment_parameters['forwardedip'])?xml_escape_utf8($payment_parameters['forwardedip']):'';
	$firstname = isset($payment_parameters['firstname'])?xml_escape_utf8($payment_parameters['firstname']):'';
	$lastname = isset($payment_parameters['lastname'])?xml_escape_utf8($payment_parameters['lastname']):'';
	$address1 = isset($payment_parameters['address1'])?xml_escape_utf8($payment_parameters['address1']):'';
	$address2 = isset($payment_parameters['address2'])?xml_escape_utf8($payment_parameters['address2']):'';
	$housenumber = isset($payment_parameters['housenumber'])?xml_escape_utf8($payment_parameters['housenumber']):'';
	$zipcode = isset($payment_parameters['zipcode'])?xml_escape_utf8($payment_parameters['zipcode']):'';
	$city = isset($payment_parameters['city'])?xml_escape_utf8($payment_parameters['city']):'';
	$state = isset($payment_parameters['state'])?xml_escape_utf8($payment_parameters['state']):'';
	$country = isset($payment_parameters['country'])?xml_escape_utf8($payment_parameters['country']):'';
	$phone = isset($payment_parameters['phone'])?xml_escape_utf8($payment_parameters['phone']):'';
	$email = isset($payment_parameters['email'])?xml_escape_utf8($payment_parameters['email']):'';

	$id = isset($payment_parameters['id'])?xml_escape_utf8($payment_parameters['id']):'';
	$currency_param = isset($payment_parameters['currency'])?xml_escape_utf8($payment_parameters['currency']):'';
	$amount = isset($payment_parameters['amount'])?xml_escape_utf8($payment_parameters['amount']*100):0;
	$description = isset($payment_parameters['description'])?xml_escape_utf8($payment_parameters['description']):'';
	$items = $description;
	if (strlen($description) > 150) {
		$description = substr($description, 0, 145) . " ...";
	}
	$var1 = isset($payment_parameters['var1'])?xml_escape_utf8($payment_parameters['var1']):'';
	$var2 = isset($payment_parameters['var2'])?xml_escape_utf8($payment_parameters['var2']):'';
	$var3 = isset($payment_parameters['var3'])?xml_escape_utf8($payment_parameters['var3']):'';
	$manual = isset($payment_parameters['manual'])?xml_escape_utf8($payment_parameters['manual']):'';
	$gateway = isset($payment_parameters['gateway'])?xml_escape_utf8($payment_parameters['gateway']):'';

	// build items parameter
	$items  = "<ul>";
	$payment_items = $variables["payment_items"];
	foreach ($payment_items as $items_index => $item_info) {
		$item_price = $item_info["price"];
		$item_name = $item_info["name"];
		$item_quantity = $item_info["quantity"];
		$items .= "<li>".$item_name." ".$item_quantity." x ".currency_format($item_price)."</li>";
	}
	$items .= "</ul>";
	$items = xml_escape_utf8($items);


	$signature = md5($amount . $currency_param . $account . $site_id . $id);


	$xml  = '<?xml version="1.0" encoding="utf-8"?>'; //<?
	$xml .= '<redirecttransaction ua="custom-1.1">';
	$xml .= '	<merchant>';
	$xml .= '		<account>'.$account.'</account>';
	$xml .= '		<site_id>'.$site_id.'</site_id>';
	$xml .= '		<site_secure_code>'.$site_secure_code.'</site_secure_code>';
	$xml .= '		<notification_url>'.$notification_url.'</notification_url>';
	$xml .= '		<redirect_url>'.$redirect_url.'</redirect_url>';
	if ($cancel_url) {
		$xml .= '		<cancel_url>'.$cancel_url.'</cancel_url>';
	}
	$xml .= '		<close_window>'.$close_window.'</close_window>';
	$xml .= '	</merchant>';
	$xml .= '	<customer>';
	$xml .= '		<locale>'.$locale.'</locale>';
	$xml .= '		<ipaddress>'.$ipaddress.'</ipaddress>';
	$xml .= '		<forwardedip>'.$forwardedip.'</forwardedip>';
	$xml .= '		<firstname>'.$firstname.'</firstname>';
	$xml .= '		<lastname>'.$lastname.'</lastname>';
	$xml .= '		<address1>'.$address1.'</address1>';
	$xml .= '		<address2>'.$address2.'</address2>';
	$xml .= '		<housenumber>'.$housenumber.'</housenumber>';
	$xml .= '		<zipcode>'.$zipcode.'</zipcode>';
	$xml .= '		<city>'.$city.'</city>';
	$xml .= '		<state>'.$state.'</state>';
	$xml .= '		<country>'.$country.'</country>';
	$xml .= '		<phone>'.$phone.'</phone>';
	$xml .= '		<email>'.$email.'</email>';
	$xml .= '	</customer>';
	$xml .= '	<transaction>';
	$xml .= '		<id>'.$id.'</id>';
	$xml .= '		<currency>'.$currency_param.'</currency>';
	$xml .= '		<amount>'.$amount.'</amount>';
	$xml .= '		<description>'.$description.'</description>';
	$xml .= '		<var1>'.$var1.'</var1>';
	$xml .= '		<var2>'.$var2.'</var2>';
	$xml .= '		<var3>'.$var3.'</var3>';
	$xml .= '		<items>'.$items.'</items>';
	$xml .= '		<manual>'.$manual.'</manual>';
	$xml .= '		<gateway>'.$gateway.'</gateway>';
	$xml .= '	</transaction>';
	$xml .= '	<signature>'.$signature.'</signature>';
	$xml .= '</redirecttransaction>';

	$ch = curl_init();
	if ($ch){
	
		$parsed_url = parse_url($payment_parameters['action_url']);
	
		if (empty($parsed_url['port'])) {
			$parsed_url['port'] = strtolower($parsed_url['scheme']) == 'https' ? 443 : 80;
		}
	
		$url = $parsed_url['scheme'] . "://" . $parsed_url['host'] . ":" . $parsed_url['port'] . "/";
		
		// generate request
		$header  = "POST " . $parsed_url['path'] ." HTTP/1.1\r\n";
		$header .= "Host: " . $parsed_url['host'] . "\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Content-Length: " . strlen($xml) . "\r\n";
		$header .= "Connection: close\r\n";
		$header .= "\r\n";
		$request = $header . $xml;

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,        30);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  $request);
		set_curl_options ($ch, $payment_parameters);
	
		$payment_response = curl_exec ($ch);
		if (curl_errno($ch)){
			echo curl_errno($ch)." - ".curl_error($ch);
			exit;
		}
		$response_info = curl_getinfo($ch);
		curl_close($ch);

		if ($response_info['http_code'] != 200) {
			echo 'HTTP code is ' . $response_info['http_code'] . ', expected 200';
			exit;
		}
		if (strstr($response_info['content_type'], "/xml") === false) {
			echo 'Content type is ' . $response_info['content_type'] . ', expected */xml';
			exit;
		}

		$matches = array();
		preg_match('/\<redirecttransaction result="(.*)"\>/U', $payment_response, $matches);
		if (count($matches) > 0 && $matches[1] == 'ok') {
			$matches = array();
			preg_match('/\<payment_url\>(.*)\<\/payment_url\>/U', $payment_response, $matches);
			if (count($matches) > 0) {
				header('Location: ' . $matches[1]);
				exit;
			} else {
				echo 'Unable to redirect user.';
				exit;
			}
		}

		$matches = array();
		preg_match('/\<error\>.*\<description\>(.*)\<\/description\>.*\<\/error\>/U', $payment_response, $matches);

		if ($matches > 0) {
			$error_code = '';
			$error_description = $matches[1];
			$matches = array();
			preg_match('/\<error\>.*\<code\>(.*)\<\/code\>.*\<\/error\>/U', $payment_response, $matches);
			if ($matches > 0) {
				$error_code = 'Error code: '.$matches[1].' ';
			}
			echo $error_code.$error_description;
		}

	}else{
		echo "Can't initialize cURL.";
	}

function xml_escape_utf8($str)
{
	if (strtolower(CHARSET) != "utf-8") {
		 $str = iconv(CHARSET, "utf-8", $str);
	}
	$str = xml_escape_string($str);
	return $str;
}

?>