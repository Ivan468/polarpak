<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  kreditor.php                                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Kreditor (http://kreditor.se/) transaction handler by www.viart.com
 */

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path . "payments/kreditor_functions.php");

	$tax_prices_type = 0;
	$sql  = " SELECT tax_prices_type ";
	$sql .= " FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, TEXT);
	$db->query($sql);
	while ($db->next_record()) {
		$tax_prices_type = $db->f("tax_prices_type");
	}

	foreach ($variables["items"] as $number => $item) {
		if($tax_prices_type){
			$price = $item['price_incl_tax'];
			$flags = 32;
		}else{
			$price = $item['price_excl_tax'];
			$flags = 0;
		}
		$cart_tems[] = array(
			'artno' => xml_escape_string($item['manufacturer_code']),
			'title' => xml_escape_string($item['item_name']),
			'price' => xml_escape_string(number_format($price*100, 0, '', '')),
			'vat' => xml_escape_string($item['tax_percent']),
			'discount' => 0,
			'flags' => $flags,
			'qty' => xml_escape_string($item['quantity'])
		);
	}
	foreach ($variables["properties"] as $number => $property) {
		if($tax_prices_type){
			$price = $property['property_price_incl_tax'];
			$flags = 32;
		}else{
			$price = $property['property_price_excl_tax'];
			$flags = 0;
		}
		if($price != 0){
			$cart_tems[] = array(
				'artno' => '',
				'title' => xml_escape_string($property['property_name']),
				'price' => xml_escape_string(number_format($price*100, 0, '', '')),
				'vat' => xml_escape_string($property['property_tax_percent']),
				'discount' => 0,
				'flags' => $flags,
				'qty' => 1
			);
		}
	}
	if ($variables["shipping_type_desc"]) {
		if ($variables["shipping_taxable"] !=0) {
			$shipping_tax_percent = $variables["tax_percent"];
			if($tax_prices_type){
				$price = $variables['shipping_cost_incl_tax'];
				$flags = 32;
			}else{
				$price = $variables['shipping_cost_excl_tax'];
				$flags = 0;
			}
		}else{
			$shipping_tax_percent = 0;
			$price = $variables['shipping_cost_incl_tax'];
			$flags = 32;
		}
		$cart_tems[] = array(
			'artno' => '',
			'title' => xml_escape_string($variables["shipping_type_desc"]),
			'price' => xml_escape_string(number_format($price*100, 0, '', '')),
			'vat' => xml_escape_string($shipping_tax_percent),
			'discount' => 0,
			'flags' => $flags,
			'qty' => 1
		);
	}
	if (isset($variables["total_discount_incl_tax"]) && $variables["total_discount_incl_tax"] != 0) {
		$cart_tems[] = array(
			'artno' => '',
			'title' => xml_escape_string(TOTAL_DISCOUNT_MSG),
			'price' => xml_escape_string(number_format(-$variables["total_discount_incl_tax"]*100, 0, '', '')),
			'vat' => 0,
			'discount' => 0,
			'flags' => 32,
			'qty' => 1
		);
	}
	if ($variables["processing_fee"] != 0) {
		$cart_tems[] = array(
			'artno' => '',
			'title' => xml_escape_string(PROCESSING_FEE_MSG),
			'price' => xml_escape_string(number_format($variables["processing_fee"]*100, 0, '', '')),
			'vat' => 0,
			'discount' => 0,
			'flags' => 32,
			'qty' => 1
		);
	}

	$md5_base64_string = '';
	foreach ($cart_tems as $number => $item) {
		$md5_base64_string .= $item['title'] . ':';
	}
	$md5_base64_string .= $payment_parameters['secret'];
	$md5_base64_signature = base64_encode(pack("H*", md5($md5_base64_string)));

	$xml  = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n"; //<?
	$xml .= '<methodCall>'."\n";
	$xml .= '<methodName>add_invoice</methodName>'."\n";
	$xml .= '<params>'."\n";
	$xml .= '<param>'."\n";
	$xml .= '<value>'."\n";
	$xml .= '<array>'."\n";
	$xml .= '<data>'."\n";

	$xml .= '<value><string>'.xml_escape_string($payment_parameters['PROTO_VSN']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['CLIENT_VSN']).'</string></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['eid'])).'</int></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['estoreUser']).'</string></value>'."\n";
	$xml .= '<value><string>'.$md5_base64_signature.'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['estoreOrderNo']).'</string></value>'."\n";

// begin items
	$xml .= '<value>'."\n";
	$xml .= '<array>'."\n";
	$xml .= '<data>'."\n";
	foreach ($cart_tems as $number => $item) {
		$md5_base64_string .= $item['title'] . ':';
		$xml .= '<value><struct>'."\n";

		$xml .= '<member><name>goods</name>'."\n";
		$xml .= '<value><struct>'."\n";

		$xml .= '<member><name>artno</name>'."\n";
		$xml .= '<value><string>'.$item['artno'].'</string></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '<member><name>title</name>'."\n";
		$xml .= '<value><string>'.$item['title'].'</string></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '<member><name>price</name>'."\n";
		$xml .= '<value><int>'.$item['price'].'</int></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '<member><name>vat</name>'."\n";
		$xml .= '<value><double>'.$item['vat'].'</double></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '<member><name>discount</name>'."\n";
		$xml .= '<value><double>'.$item['discount'].'</double></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '<member><name>flags</name>'."\n";
		$xml .= '<value><int>'.$item['flags'].'</int></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '</struct></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '<member><name>qty</name>'."\n";
		$xml .= '<value><int>'.$item['qty'].'</int></value>'."\n";
		$xml .= '</member>'."\n";

		$xml .= '</struct></value>'."\n";
	}
	$xml .= '</data>'."\n";
	$xml .= '</array>'."\n";
	$xml .= '</value>'."\n";
// end items

	$shipmentfee = (isset($payment_parameters['shipmentfee']))? $payment_parameters['shipmentfee']: 0;
	$xml .= '<value><int>'.xml_escape_string($shipmentfee).'</int></value>'."\n";
	$shipmenttype = (isset($payment_parameters['shipmenttype']))? $payment_parameters['shipmenttype']: 1;
	$xml .= '<value><int>'.xml_escape_string($shipmenttype).'</int></value>'."\n";
	$handlingfee = (isset($payment_parameters['handlingfee']))? $payment_parameters['handlingfee']: 0;
	$xml .= '<value><int>'.xml_escape_string($handlingfee).'</int></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['pno']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['fname']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['lname']).'</string></value>'."\n";

	switch(strtoupper($payment_parameters['country'])){
	case "AF":
		$country = 1;
		break;
	case "AX":
		$country = 2;
		break;
	case "AL":
		$country = 3;
		break;
	case "DZ":
		$country = 4;
		break;
	case "AS":
		$country = 5;
		break;
	case "AD":
		$country = 6;
		break;
	case "AO":
		$country = 7;
		break;
	case "AI":
		$country = 8;
		break;
	case "AQ":
		$country = 9;
		break;
	case "AG":
		$country = 10;
		break;
	case "AR":
		$country = 11;
		break;
	case "AM":
		$country = 12;
		break;
	case "AW":
		$country = 13;
		break;
	case "AU":
		$country = 14;
		break;
	case "AT":
		$country = 15;
		break;
	case "AZ":
		$country = 16;
		break;
	case "BS":
		$country = 17;
		break;
	case "BH":
		$country = 18;
		break;
	case "BD":
		$country = 19;
		break;
	case "BB":
		$country = 20;
		break;
	case "BY":
		$country = 21;
		break;
	case "BE":
		$country = 22;
		break;
	case "BZ":
		$country = 23;
		break;
	case "BJ":
		$country = 24;
		break;
	case "BM":
		$country = 25;
		break;
	case "BT":
		$country = 26;
		break;
	case "BO":
		$country = 27;
		break;
	case "BA":
		$country = 28;
		break;
	case "BW":
		$country = 29;
		break;
	case "BV":
		$country = 30;
		break;
	case "BR":
		$country = 31;
		break;
	case "IO":
		$country = 32;
		break;
	case "BN":
		$country = 33;
		break;
	case "BG":
		$country = 34;
		break;
	case "BF":
		$country = 35;
		break;
	case "BI":
		$country = 36;
		break;
	case "KH":
		$country = 37;
		break;
	case "CM":
		$country = 38;
		break;
	case "CA":
		$country = 39;
		break;
	case "CV":
		$country = 40;
		break;
	case "KY":
		$country = 41;
		break;
	case "CF":
		$country = 42;
		break;
	case "TD":
		$country = 43;
		break;
	case "CL":
		$country = 44;
		break;
	case "CN":
		$country = 45;
		break;
	case "CX":
		$country = 46;
		break;
	case "CC":
		$country = 47;
		break;
	case "CO":
		$country = 48;
		break;
	case "KM":
		$country = 49;
		break;
	case "CG":
		$country = 50;
		break;
	case "CD":
		$country = 51;
		break;
	case "CK":
		$country = 52;
		break;
	case "CR":
		$country = 53;
		break;
	case "CI":
		$country = 54;
		break;
	case "HR":
		$country = 55;
		break;
	case "CU":
		$country = 56;
		break;
	case "CY":
		$country = 57;
		break;
	case "CZ":
		$country = 58;
		break;
	case "DK":
		$country = 59;
		break;
	case "DJ":
		$country = 60;
		break;
	case "DM":
		$country = 61;
		break;
	case "DO":
		$country = 62;
		break;
	case "EC":
		$country = 63;
		break;
	case "EG":
		$country = 64;
		break;
	case "SV":
		$country = 65;
		break;
	case "GQ":
		$country = 66;
		break;
	case "ER":
		$country = 67;
		break;
	case "EE":
		$country = 68;
		break;
	case "ET":
		$country = 69;
		break;
	case "FK":
		$country = 70;
		break;
	case "FO":
		$country = 71;
		break;
	case "FJ":
		$country = 72;
		break;
	case "FI":
		$country = 73;
		break;
	case "FR":
		$country = 74;
		break;
	case "GF":
		$country = 75;
		break;
	case "PF":
		$country = 76;
		break;
	case "TF":
		$country = 77;
		break;
	case "GA":
		$country = 78;
		break;
	case "GM":
		$country = 79;
		break;
	case "GE":
		$country = 80;
		break;
	case "DE":
		$country = 81;
		break;
	case "GH":
		$country = 82;
		break;
	case "GI":
		$country = 83;
		break;
	case "GR":
		$country = 84;
		break;
	case "GL":
		$country = 85;
		break;
	case "GD":
		$country = 86;
		break;
	case "GP":
		$country = 87;
		break;
	case "GU":
		$country = 88;
		break;
	case "GT":
		$country = 89;
		break;
	case "GG":
		$country = 90;
		break;
	case "GN":
		$country = 91;
		break;
	case "GW":
		$country = 92;
		break;
	case "GY":
		$country = 93;
		break;
	case "HT":
		$country = 94;
		break;
	case "HM":
		$country = 95;
		break;
	case "VA":
		$country = 96;
		break;
	case "HN":
		$country = 97;
		break;
	case "HK":
		$country = 98;
		break;
	case "HU":
		$country = 99;
		break;
	case "IS":
		$country = 100;
		break;
	case "IN":
		$country = 101;
		break;
	case "ID":
		$country = 102;
		break;
	case "IR":
		$country = 103;
		break;
	case "IQ":
		$country = 104;
		break;
	case "IE":
		$country = 105;
		break;
	case "IM":
		$country = 106;
		break;
	case "IL":
		$country = 107;
		break;
	case "IT":
		$country = 108;
		break;
	case "JM":
		$country = 109;
		break;
	case "JP":
		$country = 110;
		break;
	case "JE":
		$country = 111;
		break;
	case "JO":
		$country = 112;
		break;
	case "KZ":
		$country = 113;
		break;
	case "KW":
		$country = 118;
		break;
	case "KG":
		$country = 119;
		break;
	case "LA":
		$country = 120;
		break;
	case "LV":
		$country = 121;
		break;
	case "LB":
		$country = 122;
		break;
	case "LS":
		$country = 123;
		break;
	case "LR":
		$country = 124;
		break;
	case "LY":
		$country = 125;
		break;
	case "LI":
		$country = 126;
		break;
	case "LT":
		$country = 127;
		break;
	case "LU":
		$country = 128;
		break;
	case "MO":
		$country = 129;
		break;
	case "MK":
		$country = 130;
		break;
	case "MG":
		$country = 131;
		break;
	case "MW":
		$country = 132;
		break;
	case "MY":
		$country = 133;
		break;
	case "MV":
		$country = 134;
		break;
	case "ML":
		$country = 135;
		break;
	case "MT":
		$country = 136;
		break;
	case "MH":
		$country = 137;
		break;
	case "MQ":
		$country = 138;
		break;
	case "MR":
		$country = 139;
		break;
	case "MU":
		$country = 140;
		break;
	case "YT":
		$country = 141;
		break;
	case "MX":
		$country = 142;
		break;
	case "FM":
		$country = 143;
		break;
	case "MD":
		$country = 144;
		break;
	case "MC":
		$country = 145;
		break;
	case "MN":
		$country = 146;
		break;
	case "MS":
		$country = 147;
		break;
	case "MA":
		$country = 148;
		break;
	case "MZ":
		$country = 149;
		break;
	case "MM":
		$country = 150;
		break;
	case "NA":
		$country = 151;
		break;
	case "NR":
		$country = 152;
		break;
	case "NP":
		$country = 153;
		break;
	case "NL":
		$country = 154;
		break;
	case "AN":
		$country = 155;
		break;
	case "NC":
		$country = 156;
		break;
	case "NZ":
		$country = 157;
		break;
	case "NI":
		$country = 158;
		break;
	case "NE":
		$country = 159;
		break;
	case "NG":
		$country = 160;
		break;
	case "NU":
		$country = 161;
		break;
	case "NF":
		$country = 162;
		break;
	case "MP":
		$country = 163;
		break;
	case "NO":
		$country = 164;
		break;
	case "OM":
		$country = 165;
		break;
	case "PK":
		$country = 166;
		break;
	case "PW":
		$country = 167;
		break;
	case "PS":
		$country = 168;
		break;
	case "PA":
		$country = 169;
		break;
	case "PG":
		$country = 170;
		break;
	case "PY":
		$country = 171;
		break;
	case "PE":
		$country = 172;
		break;
	case "PH":
		$country = 173;
		break;
	case "PN":
		$country = 174;
		break;
	case "PL":
		$country = 175;
		break;
	case "PT":
		$country = 176;
		break;
	case "PR":
		$country = 177;
		break;
	case "QA":
		$country = 178;
		break;
	case "RE":
		$country = 179;
		break;
	case "RO":
		$country = 180;
		break;
	case "RU":
		$country = 181;
		break;
	case "RW":
		$country = 182;
		break;
	case "SH":
		$country = 183;
		break;
	case "KN":
		$country = 184;
		break;
	case "LC":
		$country = 185;
		break;
	case "PM":
		$country = 186;
		break;
	case "VC":
		$country = 187;
		break;
	case "WS":
		$country = 188;
		break;
	case "SM":
		$country = 189;
		break;
	case "ST":
		$country = 190;
		break;
	case "SA":
		$country = 191;
		break;
	case "SN":
		$country = 192;
		break;
	case "CS":
		$country = 193;
		break;
	case "SC":
		$country = 194;
		break;
	case "SL":
		$country = 195;
		break;
	case "SG":
		$country = 196;
		break;
	case "SK":
		$country = 197;
		break;
	case "SI":
		$country = 198;
		break;
	case "SB":
		$country = 199;
		break;
	case "SO":
		$country = 200;
		break;
	case "ZA":
		$country = 201;
		break;
	case "GS":
		$country = 202;
		break;
	case "ES":
		$country = 203;
		break;
	case "LK":
		$country = 204;
		break;
	case "SD":
		$country = 205;
		break;
	case "SR":
		$country = 206;
		break;
	case "SJ":
		$country = 207;
		break;
	case "SZ":
		$country = 208;
		break;
	case "SE":
		$country = 209;
		break;
	case "CH":
		$country = 210;
		break;
	case "SY":
		$country = 211;
		break;
	case "TW":
		$country = 212;
		break;
	case "TJ":
		$country = 213;
		break;
	case "TZ":
		$country = 214;
		break;
	case "TH":
		$country = 215;
		break;
	case "TL":
		$country = 216;
		break;
	case "TG":
		$country = 217;
		break;
	case "TK":
		$country = 218;
		break;
	case "TO":
		$country = 219;
		break;
	case "TT":
		$country = 220;
		break;
	case "TN":
		$country = 221;
		break;
	case "TR":
		$country = 222;
		break;
	case "TM":
		$country = 223;
		break;
	case "TC":
		$country = 224;
		break;
	case "TV":
		$country = 225;
		break;
	case "UG":
		$country = 226;
		break;
	case "UA":
		$country = 227;
		break;
	case "AE":
		$country = 228;
		break;
	case "GB":
		$country = 229;
		break;
	case "US":
		$country = 230;
		break;
	case "UM":
		$country = 231;
		break;
	case "UY":
		$country = 232;
		break;
	case "UZ":
		$country = 233;
		break;
	case "VU":
		$country = 234;
		break;
	case "VE":
		$country = 235;
		break;
	case "VN":
		$country = 236;
		break;
	case "VG":
		$country = 237;
		break;
	case "VI":
		$country = 238;
		break;
	case "WF":
		$country = 239;
		break;
	case "EH":
		$country = 240;
		break;
	case "YE":
		$country = 241;
		break;
	case "ZM":
		$country = 242;
		break;
	case "ZW":
		$country = 243;
		break;
	default:
		$country = $payment_parameters['country'];
	}
// begin addrs
	$xml .= '<value>'."\n";
	$xml .= '<struct>'."\n";
	$xml .= '<member><name>careof</name>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['careof']).'</string></value></member>'."\n";
	$xml .= '<member><name>street</name>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['street']).'</string></value></member>'."\n";
	$xml .= '<member><name>postno</name>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['postno'])).'</int></value></member>'."\n";
	$xml .= '<member><name>city</name>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['city']).'</string></value></member>'."\n";
	$xml .= '<member><name>country</name>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($country)).'</int></value></member>'."\n";
	$xml .= '<member><name>telno</name>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['telno']).'</string></value></member>'."\n";
	$xml .= '<member><name>cellno</name>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['cellno']).'</string></value></member>'."\n";
	$xml .= '<member><name>email</name>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['email']).'</string></value></member>'."\n";
	$xml .= '</struct>'."\n";
	$xml .= '</value>'."\n";
// end addrs

	$xml .= '<value><string>'.xml_escape_string($payment_parameters['passwd']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['clientIp']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['newPasswd']).'</string></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['flags'])).'</int></value>'."\n"; // 0, 1, 2, 8
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['comment']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['ready_date']).'</string></value>'."\n";
	$xml .= '<value><string>'.xml_escape_string($payment_parameters['rand_string']).'</string></value>'."\n";
	switch(strtoupper($payment_parameters['currency'])){
	case "SEK":
		$currency = 0;
		break;
	case "NOK":
		$currency = 1;
		break;
	case "EUR":
		$currency = 2;
		break;
	case "DKK":
		$currency = 3;
		break;
	default:
		$currency = $payment_parameters['currency'];
	}
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($currency)).'</int></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['ecountry'])).'</int></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['language'])).'</int></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['pno_encoding'])).'</int></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['pclass'])).'</int></value>'."\n";
	$xml .= '<value><int>'.xml_escape_string(kreditor_settype_integer($payment_parameters['ysalary'])).'</int></value>'."\n";

	$xml .= '</data>'."\n";
	$xml .= '</array>'."\n";
	$xml .= '</value>'."\n";
	$xml .= '</param>'."\n";
	$xml .= '</params>'."\n";
	$xml .= '</methodCall>'."\n";

	$ch = curl_init();
	if ($ch){
		
		$parsed_url = parse_url($advanced_url);
		
		if(!isset($parsed_url['scheme'])){
			$parsed_url['scheme'] = "";
		}else{
			$parsed_url['scheme'] .= "://";
		}
		if(!isset($parsed_url['host'])){
			$error_message = "Please check parameter 'Advanced URL'.";
			return;
		}
		if(!isset($parsed_url['port'])){
			if(strtolower($parsed_url['scheme']) == 'http://'){
				$parsed_url['port'] = ":80";
			}elseif(strtolower($parsed_url['scheme']) == 'https://'){
				$parsed_url['port'] = ":443";
			}else{
				$parsed_url['port'] = "";
			}
		}else{
			$parsed_url['port'] = ":".$parsed_url['port'];
		}
		if(!isset($parsed_url['path'])){
			$parsed_url['path'] = "/";
		}
	
		$header  = "POST " . $parsed_url['path'] ." HTTP/1.1\r\n";
		$header .= "User-Agent: PEAR XML_RPC\r\n";
		$header .= "Host: " . $parsed_url['host'] . "\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Content-Length: " . strlen($xml) . "\r\n";
		$header .= "\r\n";
		$request = $header . $xml;

		$url = $parsed_url['scheme'] . $parsed_url['host'] . $parsed_url['port'] . $parsed_url['path'];

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,        $payment_parameters['timeout']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  $request);
		set_curl_options ($ch, $payment_parameters);

		$payment_response = curl_exec ($ch);
		if (curl_errno($ch)){
			$error_message = curl_errno($ch)." - ".curl_error($ch);
			return;
		}
		curl_close($ch);


		if(preg_match_all("/<param>(.*)\<\/param>/Uis", $payment_response, $matches, PREG_SET_ORDER) || preg_match_all("/<member>(.*)\<\/member>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
			if(preg_match_all("/<param>(.*)\<\/param>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				preg_match_all("/<value><string>(.*)\<\/string><\/value>/Uis", $matches[0][1], $value, PREG_SET_ORDER);
				$transaction_id = $value[0][1];
			}
			if(preg_match_all("/<member>(.*)\<\/member>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				preg_match_all("/<name>(.*)\<\/name>/Uis", $matches[0][1], $value, PREG_SET_ORDER);
				$code_name = $value[0][1];
				preg_match_all("/<value><int>(.*)\<\/int><\/value>/Uis", $matches[0][1], $value, PREG_SET_ORDER);
				$code_value = $value[0][1];
				preg_match_all("/<name>(.*)\<\/name>/Uis", $matches[1][1], $value, PREG_SET_ORDER);
				$string_name = $value[0][1];
				preg_match_all("/<value><string>(.*)\<\/string><\/value>/Uis", $matches[1][1], $value, PREG_SET_ORDER);
				$string_value = $value[0][1];
				$error_message = $code_name . $code_value . ' : ' . $string_value;
			}
		}else{
			$error_message = "Not parse response.";
		}

	}else{
		$error_message = "Can't initialize cURL.";
	}

?>