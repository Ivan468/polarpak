<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  check_savers.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Check Savers (www.checksavers.com) transaction handler by www.viart.com
 */

	$is_credit_card	= (isset($payment_parameters["cctype"]) && isset($payment_parameters["cc"]))? true: false;

	$xml  = '<xml>';
	$xml .= '	<header>';
	$xml .= '		<responsetype>' . $payment_parameters["responsetype"] . '</responsetype>';
	$xml .= '		<mid>' . $payment_parameters["mid"] . '</mid>';
	$xml .= '		<user>' . $payment_parameters["user"] . '</user>';
	$xml .= '		<password>' . $payment_parameters["password"] . '</password>';
	$xml .= '		<type>' . $payment_parameters["type"] . '</type>';
	$xml .= '	</header>';
	$xml .= '	<request>';
	if($is_credit_card){
		$xml .= '		<charge>';
	}else{
		$xml .= '		<check>';
	}
	$xml .= '			<etan>' . $payment_parameters["etan"] . '</etan>';
	if($is_credit_card){
		$xml .= '			<card>';
		$xml .= '				<cctype>' . $payment_parameters["cctype"] . '</cctype>';
		$xml .= '				<cc>' . $payment_parameters["cc"] . '</cc>';
		$xml .= '				<expire>' . $payment_parameters["expire"] . '</expire>';
		$xml .= '				<cvv>' . $payment_parameters["cvv"] . '</cvv>';
		$xml .= '			</card>';
	}else{
		$xml .= '			<check>';
		$xml .= '				<bankname>' . $payment_parameters["bankname"] . '</bankname>';
		$xml .= '				<checktype>' . $payment_parameters["checktype"] . '</checktype>';
		$xml .= '				<routingnumber>' . $payment_parameters["routingnumber"] . '</routingnumber>';
		$xml .= '				<accountnumber>' . $payment_parameters["accountnumber"] . '</accountnumber>';
		$xml .= '				<accounttype>' . $payment_parameters["accounttype"] . '</accounttype>';
		$xml .= '			</check>';
	}
	if($is_credit_card){
		$xml .= '		<cardholder>';
	}else{
		$xml .= '		<payer>';
	}
	if(!$is_credit_card && isset($payment_parameters["businessname"])){
		$xml .= '				<businessname>' . $payment_parameters["businessname"] . '</businessname>';
	}
	$xml .= '				<firstname>' . $payment_parameters["firstname"] . '</firstname>';
	$xml .= '				<lastname>' . $payment_parameters["lastname"] . '</lastname>';
	$xml .= '				<street>' . $payment_parameters["street"] . '</street>';
	$xml .= '				<housenumber>' . $payment_parameters["housenumber"] . '</housenumber>';
	$xml .= '				<zip>' . $payment_parameters["zip"] . '</zip>';
	$xml .= '				<zip4>' . $payment_parameters["zip4"] . '</zip4>';
	$xml .= '				<city>' . $payment_parameters["city"] . '</city>';
	$xml .= '				<country>' . $payment_parameters["country"] . '</country>';
	$xml .= '				<state>' . $payment_parameters["state"] . '</state>';
	$xml .= '				<telephone>' . $payment_parameters["telephone"] . '</telephone>';
	$xml .= '				<email>' . $payment_parameters["email"] . '</email>';
	$xml .= '				<ip>' . $payment_parameters["ip"] . '</ip>';
	if($is_credit_card){
		$xml .= '		</cardholder>';
	}else{
		$xml .= '		</payer>';
	}
	$xml .= '			<amount>';
	$xml .= '				<currency>' . $payment_parameters["currency"] . '</currency>';
	$xml .= '				<exponent>' . $payment_parameters["exponent"] . '</exponent>';
	$xml .= '				<value>' . $payment_parameters["amount"] . '</value>';
	$xml .= '			</amount>';
	if(!$is_credit_card){
		$xml .= '			<validation>';
		$xml .= '				<risknmodifier>' . $payment_parameters["risknmodifier"] . '</risknmodifier>';
		$xml .= '				<authenticationmodifier>' . $payment_parameters["authenticationmodifier"] . '</authenticationmodifier>';
		$xml .= '				<insurancemodifier>' . $payment_parameters["insurancemodifier"] . '</insurancemodifier>';
		$xml .= '			</validation>';
	}
	if(isset($payment_parameters["callbackurl"])){
		$xml .= '			<callbackurl>' . $payment_parameters["callbackurl"] . '</callbackurl>';
	}
	if($is_credit_card){
		$xml .= '		</charge>';
	}else{
		$xml .= '		</check>';
	}
	$xml .= '	</request>';
	$xml .= '</xml>';

	$ch = curl_init ();
	if ($ch)
	{
		
		curl_setopt ($ch, CURLOPT_URL, $advanced_url);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, 'xml=' . $xml);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
		set_curl_options ($ch, $payment_parameters);

		$payment_response = curl_exec($ch);
		if (curl_errno($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
			return;
		}
		curl_close ($ch);
		$payment_response = trim($payment_response);
		$t->set_var("payment_response", $payment_response);

		if ($payment_response) {
			preg_match_all("/<mid>(.*)\<\/mid>/Uis", $payment_response, $matches, PREG_SET_ORDER);
			$mid = $matches[0][1];
			$status = '';
			if(preg_match_all("/<status>(.*)\<\/status>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$status = $matches[0][1];
			}
			if(preg_match_all("/<tan>(.*)\<\/tan>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$transaction_id = $matches[0][1];
			}
			$etan = '';
			if(preg_match_all("/<etan>(.*)\<\/etan>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$etan = $matches[0][1];
			}
			if(preg_match_all("/<success>(.*)\<\/success>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				preg_match_all("/<message>(.*)\<\/message>/Uis", $payment_response, $matches, PREG_SET_ORDER);
				$success_message = $matches[0][1];
				if($status != 520){
					$error_message = "Status: " .$status. ", " . $success_message;
				}
			}elseif(preg_match_all("/<error>(.*)\<\/error>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				preg_match_all("/<errorcode>(.*)\<\/errorcode>/Uis", $payment_response, $matches, PREG_SET_ORDER);
				$errorcode = $matches[0][1];
				preg_match_all("/<errormessage>(.*)\<\/errormessage>/Uis", $payment_response, $matches, PREG_SET_ORDER);
				$errormessage = $matches[0][1];
				$error_message  = (strlen($status))? "Status: " .$status . ". ": "";;
				$error_message .= "Error code: " . $errorcode . ", " . $errormessage;
			}else{
				$error_message = "Not parse response.";
			}
		} else {
			$error_message = "Empty response from gateway. Please check your settings.";
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}
?>