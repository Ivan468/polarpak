<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ebs_check.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * EBS (www.ebs.in) transaction handler by www.viart.com
 */

	$s = array();
	$i = 0;
	$j = 0;
	$DR = get_param("DR");
	$secret_key = $payment_parameters['secret_key']

	if(strlen($DR)){
		$DR = preg_replace("/\s/","+",$DR);
		$response_parameters = base64_decode($DR);

		$len= strlen($secret_key);
		for ($i = 0; $i < 256; $i++) {
			$s[$i] = $i;
		}

		$j = 0;
		for ($i = 0; $i < 256; $i++) {
			$j = ($j + $s[$i] + ord($secret_key[$i % $len])) % 256;
            $t = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $t;
        }
		$i = 0;
		$j = 0;

		$len= strlen($response_parameters);
		for ($c= 0; $c < $len; $c++) {
			$i = ($i + 1) % 256;
			$j = ($j + $s[$i]) % 256;
			$t = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $t;

			$t = ($s[$i] + $s[$j]) % 256;

			$response_parameters[$c] = chr(ord($response_parameters[$c]) ^ $s[$t]);
		}
		
		$transaction_id = (isset($response_parameters['PaymentID']))? $response_parameters['PaymentID']: "";
		if(isset($response_parameters['ResponseCode'])){
			if($response_parameters['ResponseCode'] != 0){
				$error_message = "Response Code: " . $response_parameters['ResponseCode'];
				$error_message .= (isset($response_parameters['ResponseMessage']))? ", " . $response_parameters['ResponseMessage']: "";
			}
		}else{
			$error_message = "The response_code doesn't exist, this is an error occurred during the transaction."
		}

	}else{
		$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";
	}

?>