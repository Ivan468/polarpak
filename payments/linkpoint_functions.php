<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  linkpoint_functions.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * LinkPoint functions by ViArt Ltd - http://www.viart.com/
 */

	function linkpoint_order_xml($pdata)
	{
		// ORDEROPTIONS NODE
		$xml = "<order><orderoptions>";
		if (isset($pdata["ordertype"]))
			$xml .= "<ordertype>" . xml_escape_string($pdata["ordertype"]) . "</ordertype>";
		if (isset($pdata["result"]))
			$xml .= "<result>" . xml_escape_string($pdata["result"]) . "</result>";
		$xml .= "</orderoptions>";


		// CREDITCARD NODE
		$xml .= "<creditcard>";
		if (isset($pdata["cardnumber"]))
			$xml .= "<cardnumber>" . xml_escape_string($pdata["cardnumber"]) . "</cardnumber>";
		if (isset($pdata["cardexpmonth"]))
			$xml .= "<cardexpmonth>" . xml_escape_string($pdata["cardexpmonth"]) . "</cardexpmonth>";
		if (isset($pdata["cardexpyear"]))
			$xml .= "<cardexpyear>" . xml_escape_string($pdata["cardexpyear"]) . "</cardexpyear>";
		if (isset($pdata["cvmvalue"]))
			$xml .= "<cvmvalue>" . xml_escape_string($pdata["cvmvalue"]) . "</cvmvalue>";
		if (isset($pdata["cvmindicator"]))
			$xml .= "<cvmindicator>" . xml_escape_string($pdata["cvmindicator"]) . "</cvmindicator>";
		if (isset($pdata["track"]))
			$xml .= "<track>" . xml_escape_string($pdata["track"]) . "</track>";
		$xml .= "</creditcard>";


		// BILLING NODE
		$xml .= "<billing>";
		if (isset($pdata["name"]))
			$xml .= "<name>" . xml_escape_string($pdata["name"]) . "</name>";
		if (isset($pdata["company"]))
			$xml .= "<company>" . xml_escape_string($pdata["company"]) . "</company>";
		if (isset($pdata["address1"]))
			$xml .= "<address1>" . xml_escape_string($pdata["address1"]) . "</address1>";
		elseif (isset($pdata["address"]))
			$xml .= "<address1>" . xml_escape_string($pdata["address"]) . "</address1>";
		if (isset($pdata["address2"]))
			$xml .= "<address2>" . xml_escape_string($pdata["address2"]) . "</address2>";
		if (isset($pdata["city"]))
			$xml .= "<city>" . xml_escape_string($pdata["city"]) . "</city>";
		if (isset($pdata["state"]))
			$xml .= "<state>" . xml_escape_string($pdata["state"]) . "</state>";
		if (isset($pdata["zip"]))
			$xml .= "<zip>" . xml_escape_string($pdata["zip"]) . "</zip>";
		if (isset($pdata["country"]))
			$xml .= "<country>" . xml_escape_string($pdata["country"]) . "</country>";
		if (isset($pdata["userid"]))
			$xml .= "<userid>" . xml_escape_string($pdata["userid"]) . "</userid>";
		if (isset($pdata["email"]))
			$xml .= "<email>" . xml_escape_string($pdata["email"]) . "</email>";

		$phone = isset($pdata["phone"]) ? $pdata["phone"] : "";
		if (strlen($phone)) {
			$phone = preg_replace("/[^\d]/", "", $phone);
			$xml .= "<phone>" . xml_escape_string($phone) . "</phone>";
		}
		$fax = isset($pdata["fax"]) ? $pdata["fax"] : "";
		if (strlen($fax)) {
			$fax = preg_replace("/[^\d]/", "", $fax);
			$xml .= "<fax>" . xml_escape_string($fax) . "</fax>";
		}
		if (isset($pdata["addrnum"]))
			$xml .= "<addrnum>" . xml_escape_string($pdata["addrnum"]) . "</addrnum>";
		$xml .= "</billing>";


		// SHIPPING NODE
		$xml .= "<shipping>";
		if (isset($pdata["sname"]))
			$xml .= "<name>" . xml_escape_string($pdata["sname"]) . "</name>";
		if (isset($pdata["saddress1"]))
			$xml .= "<address1>" . xml_escape_string($pdata["saddress1"]) . "</address1>";
		if (isset($pdata["saddress2"]))
			$xml .= "<address2>" . xml_escape_string($pdata["saddress2"]) . "</address2>";
		if (isset($pdata["scity"]))
			$xml .= "<city>" . xml_escape_string($pdata["scity"]) . "</city>";
		if (isset($pdata["sstate"]))
			$xml .= "<state>" . xml_escape_string($pdata["sstate"]) . "</state>";
		if (isset($pdata["szip"]))
			$xml .= "<zip>" . xml_escape_string($pdata["szip"]) . "</zip>";
		if (isset($pdata["scountry"]))
			$xml .= "<country>" . xml_escape_string($pdata["scountry"]) . "</country>";
		if (isset($pdata["scarrier"]))
			$xml .= "<carrier>" . xml_escape_string($pdata["scarrier"]) . "</carrier>";
		if (isset($pdata["sitems"]))
			$xml .= "<items>" . xml_escape_string($pdata["sitems"]) . "</items>";
		if (isset($pdata["sweight"]))
			$xml .= "<weight>" . xml_escape_string($pdata["sweight"]) . "</weight>";
		if (isset($pdata["stotal"]))
			$xml .= "<total>" . xml_escape_string($pdata["stotal"]) . "</total>";
		$xml .= "</shipping>";


		// TRANSACTIONDETAILS NODE
		$xml .= "<transactiondetails>";
		if (isset($pdata["oid"]))
			$xml .= "<oid>" . xml_escape_string($pdata["oid"]) . "</oid>";
		if (isset($pdata["ponumber"]))
			$xml .= "<ponumber>" . xml_escape_string($pdata["ponumber"]) . "</ponumber>";
		if (isset($pdata["recurring"]))
			$xml .= "<recurring>" . xml_escape_string($pdata["recurring"]) . "</recurring>";
		if (isset($pdata["taxexempt"]))
			$xml .= "<taxexempt>" . xml_escape_string($pdata["taxexempt"]) . "</taxexempt>";
		if (isset($pdata["terminaltype"]))
			$xml .= "<terminaltype>" . xml_escape_string($pdata["terminaltype"]) . "</terminaltype>";
		if (isset($pdata["ip"]))
			$xml .= "<ip>" . xml_escape_string($pdata["ip"]) . "</ip>";
		if (isset($pdata["reference_number"]))
			$xml .= "<reference_number>" . xml_escape_string($pdata["reference_number"]) . "</reference_number>";
		if (isset($pdata["transactionorigin"]))
			$xml .= "<transactionorigin>" . xml_escape_string($pdata["transactionorigin"]) . "</transactionorigin>";
		if (isset($pdata["tdate"]))
			$xml .= "<tdate>" . xml_escape_string($pdata["tdate"]) . "</tdate>";
		$xml .= "</transactiondetails>";


		// MERCHANTINFO NODE
		$xml .= "<merchantinfo>";
		if (isset($pdata["configfile"]))
			$xml .= "<configfile>" . xml_escape_string($pdata["configfile"]) . "</configfile>";
		if (isset($pdata["keyfile"]))
			$xml .= "<keyfile>" . xml_escape_string($pdata["keyfile"]) . "</keyfile>";
		if (isset($pdata["host"]))
			$xml .= "<host>" . xml_escape_string($pdata["host"]) . "</host>";
		if (isset($pdata["port"]))
			$xml .= "<port>" . xml_escape_string($pdata["port"]) . "</port>";
		if (isset($pdata["appname"]))
			$xml .= "<appname>" . xml_escape_string($pdata["appname"]) . "</appname>";
		$xml .= "</merchantinfo>";


		// PAYMENT NODE
		$xml .= "<payment>";
		if (isset($pdata["chargetotal"]))
			$xml .= "<chargetotal>" . xml_escape_string($pdata["chargetotal"]) . "</chargetotal>";
		if (isset($pdata["tax"]))
			$xml .= "<tax>" . xml_escape_string($pdata["tax"]) . "</tax>";
		if (isset($pdata["vattax"]))
			$xml .= "<vattax>" . xml_escape_string($pdata["vattax"]) . "</vattax>";
		if (isset($pdata["shipping"]))
			$xml .= "<shipping>" . xml_escape_string($pdata["shipping"]) . "</shipping>";
		if (isset($pdata["subtotal"]))
			$xml .= "<subtotal>" . xml_escape_string($pdata["subtotal"]) . "</subtotal>";
		$xml .= "</payment>";


		// CHECK NODE
		if (isset($pdata["voidcheck"]))
		{
			$xml .= "<telecheck><void>1</void></telecheck>";
		}
		elseif (isset($pdata["routing"]))
		{
			$xml .= "<telecheck>";
			$xml .= "<routing>" . xml_escape_string($pdata["routing"]) . "</routing>";
			if (isset($pdata["account"]))
				$xml .= "<account>" . xml_escape_string($pdata["account"]) . "</account>";
			if (isset($pdata["bankname"]))
				$xml .= "<bankname>" . xml_escape_string($pdata["bankname"]) . "</bankname>";
			if (isset($pdata["bankstate"]))
				$xml .= "<bankstate>" . xml_escape_string($pdata["bankstate"]) . "</bankstate>";
			if (isset($pdata["ssn"]))
				$xml .= "<ssn>" . xml_escape_string($pdata["ssn"]) . "</ssn>";
			if (isset($pdata["dl"]))
				$xml .= "<dl>" . xml_escape_string($pdata["dl"]) . "</dl>";
			if (isset($pdata["dlstate"]))
				$xml .= "<dlstate>" . xml_escape_string($pdata["dlstate"]) . "</dlstate>";
			if (isset($pdata["checknumber"]))
				$xml .= "<checknumber>" . xml_escape_string($pdata["checknumber"]) . "</checknumber>";
			if (isset($pdata["accounttype"]))
				$xml .= "<accounttype>" . xml_escape_string($pdata["accounttype"]) . "</accounttype>";
			$xml .= "</telecheck>";
		}

		// PERIODIC NODE
		if (isset($pdata["startdate"]))
		{
			$xml .= "<periodic>";
			$xml .= "<startdate>" . xml_escape_string($pdata["startdate"]) . "</startdate>";
			if (isset($pdata["installments"]))
				$xml .= "<installments>" . xml_escape_string($pdata["installments"]) . "</installments>";
			if (isset($pdata["threshold"]))
						$xml .= "<threshold>" . xml_escape_string($pdata["threshold"]) . "</threshold>";
			if (isset($pdata["periodicity"]))
						$xml .= "<periodicity>" . xml_escape_string($pdata["periodicity"]) . "</periodicity>";
			if (isset($pdata["pbcomments"]))
						$xml .= "<comments>" . xml_escape_string($pdata["pbcomments"]) . "</comments>";
			if (isset($pdata["action"]))
				$xml .= "<action>" . xml_escape_string($pdata["action"]) . "</action>";
			$xml .= "</periodic>";
		}

		// NOTES NODE
		if (isset($pdata["comments"]) || isset($pdata["referred"]))
		{
			$xml .= "<notes>";
			if (isset($pdata["comments"]))
				$xml .= "<comments>" . xml_escape_string($pdata["comments"]) . "</comments>";
			if (isset($pdata["referred"]))
				$xml .= "<referred>" . xml_escape_string($pdata["referred"]) . "</referred>";
			$xml .= "</notes>";
		}

		// ITEMS AND OPTIONS NODES
		if (isset($pdata["items"])) {
			$order_items = $pdata["items"];
			if (is_array($order_items)) {
				$xml .= "<items>";
				// start parsing items
				foreach ($order_items as $key => $item) {
					$xml .= "<item>";

					$item_id = $item["item_id"];
					$price = $item["price"];
					$quantity = $item["quantity"];
					$description = $item["item_name"];

					// added general fields
					$xml .= "<id>" . xml_escape_string($item_id) . "</id>";
					$xml .= "<description>" . xml_escape_string($description) . "</description>";
					$xml .= "<price>" . xml_escape_string($price) . "</price>";
					$xml .= "<quantity>" . xml_escape_string($quantity) . "</quantity>";

					// check if options available
					$options = isset($item["properties"]) ? $item["properties"] : "";
					if (is_array($options) && sizeof($options) > 0) {
						$xml .= "<options>";
						foreach ($options as $key => $option) {
							$xml .= "<option>";
							$xml .= "<name>" . xml_escape_string($option["name"]) . "</name>";
							$xml .= "<value>" . xml_escape_string($option["value"]) . "</value>";
							$xml .= "</option>";
						}
						$xml .= "</options>";
					}

					$xml .= "</item>";
				}
				$xml .= "</items>";
			}
		}
		$xml .= "</order>";

		return $xml;
	}

?>