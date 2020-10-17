<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cybersource_get_tax.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Cybersource (www.cybersource.com) SOP/HOP response handler by ViArt Ltd. (www.viart.com)
 */
	$cs_add_tax_amount = 0;
	$cs_error = "";
	
	$payment_params = array();
	$sql  = " SELECT parameter_name, parameter_source ";
	$sql .= " FROM " . $table_prefix . "payment_parameters ";
	$sql .= " WHERE payment_id=" . $db->tosql("95", INTEGER);
	$sql .= " AND (parameter_name = " . $db->tosql("merchantID", TEXT);
	$sql .= " OR parameter_name = " . $db->tosql("tax_url", TEXT);
	$sql .= " OR parameter_name = " . $db->tosql("TRANSACTION_KEY", TEXT) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$parameter_name = $db->f("parameter_name");
		$parameter_source = $db->f("parameter_source");
		$payment_params[$parameter_name] = $parameter_source;
	}
	if(!isset($payment_params["merchantID"]) || !isset($payment_params["TRANSACTION_KEY"])){
		$cs_error = 'Please check the settings of system "CyberSource".';
		return;
	}

	$state = "";
	$sql  = " SELECT state_code ";
	$sql .= " FROM " . $table_prefix . "states ";
	$sql .= " WHERE state_id=" . $db->tosql($cs_state_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$cs_state = $db->f("state_code");
	}

	$country = "";
	$sql  = " SELECT country_code ";
	$sql .= " FROM " . $table_prefix . "countries ";
	$sql .= " WHERE country_id=" . $db->tosql($cs_country_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$cs_country = $db->f("country_code");
	}

	$xml = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="urn:schemas-cybersource-com:transaction-data-1.26">
	<SOAP-ENV:Header xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
		<wsse:Security SOAP-ENV:mustUnderstand="1">
			<wsse:UsernameToken>
				<wsse:Username>'.xml_escape_string($payment_params["merchantID"]).'</wsse:Username>
				<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.xml_escape_string($payment_params["TRANSACTION_KEY"]).'</wsse:Password>
			</wsse:UsernameToken>
		</wsse:Security>
	</SOAP-ENV:Header>
	<SOAP-ENV:Body>
		<ns1:requestMessage>
			<ns1:merchantID>'.xml_escape_string($payment_params["merchantID"]).'</ns1:merchantID>
			<ns1:merchantReferenceCode>0</ns1:merchantReferenceCode>
			<ns1:billTo>
				<ns1:street1>'.xml_escape_string($cs_address1).'</ns1:street1>
				<ns1:street2>'.xml_escape_string($cs_address2).'</ns1:street2>
				<ns1:city>'.xml_escape_string($cs_city).'</ns1:city>
				<ns1:state>'.xml_escape_string($cs_state).'</ns1:state>
				<ns1:postalCode>'.xml_escape_string($cs_postal_code).'</ns1:postalCode>
				<ns1:country>'.xml_escape_string($cs_country).'</ns1:country>
			</ns1:billTo>
			<ns1:item id="0">
				<ns1:unitPrice>'.xml_escape_string($order_total).'</ns1:unitPrice>
				<ns1:quantity>1</ns1:quantity>
				<ns1:productCode>default</ns1:productCode>
				<ns1:productName>Bascet</ns1:productName>
				<ns1:productSKU>default</ns1:productSKU>
			</ns1:item>
			<ns1:taxService run="true"/>
		</ns1:requestMessage>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
//<?

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	// send the string to LSGS
	$xml_response = curl_exec($ch);
	if (curl_errno($ch)){
		$cs_error = curl_errno($ch)." - ".curl_error($ch) . "<br>\n Please check the settings.";
		return;
	}
	curl_close($ch);
	
	if(!strlen($xml_response)){
		$cs_error = "Can't obtain data for your transaction.";
		return;
	}
	if (preg_match_all("/<c:totalTaxAmount>(.*)\<\/c:totalTaxAmount>/Uis", $xml_response, $value, PREG_SET_ORDER)){
		$cs_add_tax_amount = (isset($value[0][1]))?$value[0][1]:0;
		return;
	}
	if (preg_match_all("/<faultstring>(.*)\<\/faultstring>/Uis", $xml_response, $value, PREG_SET_ORDER)){
		$faultstring = (isset($value[0][1]))?$value[0][1]:0;
		$cs_error = $faultstring;
		return;
	}
	if (preg_match_all("/<c:invalidField>(.*)\<\/c:invalidField>/Uis", $xml_response, $value, PREG_SET_ORDER)){
		$faultstring = (isset($value[0][1]))?$value[0][1]:0;
		$cs_error = "field " . $faultstring . " is invalid";
		return;
	}
	if (preg_match_all("/<c:missingField>(.*)\<\/c:missingField>/Uis", $xml_response, $value, PREG_SET_ORDER)){
		$faultstring = (isset($value[0][1]))?$value[0][1]:0;
		$cs_error = "field " . $faultstring . " is invalid";
		return;
	}

$cs_error = "Undefined error.";
?>