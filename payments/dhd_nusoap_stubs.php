<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  dhd_nusoap_stubs.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Segpay DHD (http://dhdmedia.com/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$status_error = '';

	$site_url = $settings["site_url"];
	$file_url = $site_url.'payments/dhd_nusoap_stubs.php';

	if (isset($_SERVER['QUERY_STRING'])) {
		$qs = $_SERVER['QUERY_STRING'];
	} elseif (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
		$qs = $HTTP_SERVER_VARS['QUERY_STRING'];
	} else {
		$qs = '';
	}

	$file_get_contents = file_get_contents('php://input');
	$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';

if(preg_match("/wsdl/", $qs) && !strlen($HTTP_RAW_POST_DATA)){
$xml_output = '<?xml version="1.0" encoding="ISO-8859-1"?>
<definitions xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:tns="http://soapinterop.org/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns="http://schemas.xmlsoap.org/wsdl/" targetNamespace="http://soapinterop.org/">
<types><xsd:schema targetNamespace="http://soapinterop.org/"
>
 <xsd:import namespace="http://schemas.xmlsoap.org/soap/encoding/" />
 <xsd:import namespace="http://schemas.xmlsoap.org/wsdl/" />
 <xsd:complexType name="dhd_postback_response">
  <xsd:all>
   <xsd:element name="crl_id" type="xsd:double"/>
   <xsd:element name="resp_code" type="xsd:double"/>
   <xsd:element name="resp_message" type="xsd:string"/>
  </xsd:all>
 </xsd:complexType>
 <xsd:complexType name="dhd_account">
  <xsd:all>
   <xsd:element name="merch_id" type="xsd:double" nillable="true"/>
   <xsd:element name="prod_id" type="xsd:string" nillable="true"/>
   <xsd:element name="sub_id" type="xsd:double" nillable="true"/>
   <xsd:element name="sub_password" type="xsd:string" nillable="true"/>
   <xsd:element name="sub_status" type="xsd:string" nillable="true"/>
   <xsd:element name="sub_username" type="xsd:string" nillable="true"/>
  </xsd:all>
 </xsd:complexType>
 <xsd:complexType name="ArrayOfdhd_account">
  <xsd:complexContent>
   <xsd:restriction base="SOAP-ENC:Array">
    <xsd:attribute ref="SOAP-ENC:arrayType" wsdl:arrayType="tns:dhd_account[]"/>
   </xsd:restriction>
  </xsd:complexContent>
 </xsd:complexType>
 <xsd:complexType name="dhd_address">
  <xsd:all>
   <xsd:element name="addr_address" type="xsd:string" nillable="true"/>
   <xsd:element name="addr_city" type="xsd:string" nillable="true"/>
   <xsd:element name="addr_country" type="xsd:string" nillable="true"/>
   <xsd:element name="addr_phone" type="xsd:string" nillable="true"/>
   <xsd:element name="addr_state" type="xsd:string" nillable="true"/>
   <xsd:element name="addr_type" type="xsd:string" nillable="true"/>
   <xsd:element name="addr_zip" type="xsd:string" nillable="true"/>
  </xsd:all>
 </xsd:complexType>
 <xsd:complexType name="ArrayOfdhd_address">
  <xsd:complexContent>
   <xsd:restriction base="SOAP-ENC:Array">
    <xsd:attribute ref="SOAP-ENC:arrayType" wsdl:arrayType="tns:dhd_address[]"/>
   </xsd:restriction>
  </xsd:complexContent>
 </xsd:complexType>
 <xsd:complexType name="dhd_line_item">
  <xsd:all>
   <xsd:element name="li_revoked" type="xsd:string" nillable="true"/>
   <xsd:element name="lit_name" type="xsd:string" nillable="true"/>
   <xsd:element name="med_id" type="xsd:string" nillable="true"/>
   <xsd:element name="med_id_ext" type="xsd:string" nillable="true"/>
   <xsd:element name="price_requested" type="xsd:string" nillable="true"/>
   <xsd:element name="price_settled" type="xsd:string" nillable="true"/>
   <xsd:element name="prod_id" type="xsd:string" nillable="true"/>
   <xsd:element name="prod_id_ext" type="xsd:string" nillable="true"/>
   <xsd:element name="quantity" type="xsd:string" nillable="true"/>
   <xsd:element name="ship_prod_desc" type="xsd:string" nillable="true"/>
   <xsd:element name="ship_vend_name" type="xsd:string" nillable="true"/>
  </xsd:all>
 </xsd:complexType>
 <xsd:complexType name="ArrayOfdhd_line_item">
  <xsd:complexContent>
   <xsd:restriction base="SOAP-ENC:Array">
    <xsd:attribute ref="SOAP-ENC:arrayType" wsdl:arrayType="tns:dhd_line_item[]"/>
   </xsd:restriction>
  </xsd:complexContent>
 </xsd:complexType>
 <xsd:complexType name="dhd_invoice">
  <xsd:all>
   <xsd:element name="bat_number_bank" type="xsd:string" nillable="true"/>
   <xsd:element name="curr_id_requested" type="xsd:string" nillable="true"/>
   <xsd:element name="curr_id_settled" type="xsd:string" nillable="true"/>
   <xsd:element name="dcd_id" type="xsd:string" nillable="true"/>
   <xsd:element name="dco_id" type="xsd:string" nillable="true"/>
   <xsd:element name="dcw_id" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_authno" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_date" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_id" type="xsd:double" nillable="true"/>
   <xsd:element name="inv_id_ext" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_ip" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_refid" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_reportdate" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_status" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_udf01" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_udf02" type="xsd:string" nillable="true"/>
   <xsd:element name="inv_value_requested" type="xsd:double" nillable="true"/>
   <xsd:element name="inv_value_settled" type="xsd:double" nillable="true"/>
   <xsd:element name="line_item" type="tns:ArrayOfdhd_line_item" nillable="true"/>
   <xsd:element name="merch_code" type="xsd:string" nillable="true"/>
   <xsd:element name="xsale_inv_id" type="xsd:double" nillable="true"/>
   <xsd:element name="xsale_merchant_name" type="xsd:string" nillable="true"/>
  </xsd:all>
 </xsd:complexType>
 <xsd:complexType name="dhd_pay">
  <xsd:all>
   <xsd:element name="cctype_name" type="xsd:string" nillable="true"/>
   <xsd:element name="pay_id" type="xsd:double" nillable="true"/>
   <xsd:element name="pay_num_l4" type="xsd:string" nillable="true"/>
   <xsd:element name="payt_name" type="xsd:string" nillable="true"/>
  </xsd:all>
 </xsd:complexType>
 <xsd:complexType name="dhd_customer">
  <xsd:all>
   <xsd:element name="Acct" type="tns:ArrayOfdhd_account" nillable="true"/>
   <xsd:element name="Addr" type="tns:ArrayOfdhd_address" nillable="true"/>
   <xsd:element name="Inv" type="tns:dhd_invoice" nillable="true"/>
   <xsd:element name="Pay" type="tns:dhd_pay" nillable="true"/>
   <xsd:element name="cust_email" type="xsd:string" nillable="true"/>
   <xsd:element name="cust_fname" type="xsd:string" nillable="true"/>
   <xsd:element name="cust_id" type="xsd:double" nillable="true"/>
   <xsd:element name="cust_id_ext" type="xsd:string" nillable="true"/>
   <xsd:element name="cust_lname" type="xsd:string" nillable="true"/>
   <xsd:element name="cust_mail_status" type="xsd:string" nillable="true"/>
  </xsd:all>
 </xsd:complexType>
</xsd:schema>
</types>
<message name="update_customerRequest"><part name="version" type="xsd:double" /><part name="crl_id" type="xsd:double" /><part name="type" type="xsd:string" /><part name="Cust" type="tns:dhd_customer" /></message>
<message name="update_customerResponse"><part name="response" type="tns:dhd_postback_response" /></message>
<message name="transactRequest"><part name="version" type="xsd:double" /><part name="crl_id" type="xsd:double" /><part name="type" type="xsd:string" /><part name="Cust" type="tns:dhd_customer" /></message>
<message name="transactResponse"><part name="response" type="tns:dhd_postback_response" /></message>
<message name="xsaleRequest"><part name="version" type="xsd:double" /><part name="crl_id" type="xsd:double" /><part name="type" type="xsd:string" /><part name="Cust" type="tns:dhd_customer" /></message>
<message name="xsaleResponse"><part name="response" type="tns:dhd_postback_response" /></message>
<message name="pingRequest"><part name="version" type="xsd:double" /><part name="crl_id" type="xsd:double" /><part name="message" type="xsd:string" /></message>
<message name="pingResponse"><part name="response" type="tns:dhd_postback_response" /></message>
<portType name="DHDPostBack_v1_forPHPv'.PHP_VERSION.'PortType"><operation name="update_customer"><documentation>update_customer methods</documentation><input message="tns:update_customerRequest"/><output message="tns:update_customerResponse"/></operation><operation name="transact"><documentation>transact methods</documentation><input message="tns:transactRequest"/><output message="tns:transactResponse"/></operation><operation name="xsale"><documentation>transact methods</documentation><input message="tns:xsaleRequest"/><output message="tns:xsaleResponse"/></operation><operation name="ping"><documentation>dhd ping test</documentation><input message="tns:pingRequest"/><output message="tns:pingResponse"/></operation></portType>
<binding name="DHDPostBack_v1_forPHPv'.PHP_VERSION.'Binding" type="tns:DHDPostBack_v1_forPHPv'.PHP_VERSION.'PortType"><soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/><operation name="update_customer"><soap:operation soapAction="'.$file_url.'/update_customer" style="rpc"/><input><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input><output><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output></operation><operation name="transact"><soap:operation soapAction="'.$file_url.'/transact" style="rpc"/><input><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input><output><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output></operation><operation name="xsale"><soap:operation soapAction="'.$file_url.'/xsale" style="rpc"/><input><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input><output><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output></operation><operation name="ping"><soap:operation soapAction="'.$file_url.'/ping" style="rpc"/><input><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></input><output><soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/></output></operation></binding>
<service name="DHDPostBack_v1_forPHPv'.PHP_VERSION.'"><port name="DHDPostBack_v1_forPHPv'.PHP_VERSION.'Port" binding="tns:DHDPostBack_v1_forPHPv'.PHP_VERSION.'Binding"><soap:address location="'.$file_url.'"/></port></service>
</definitions>';
echo $xml_output;
}elseif(strlen($HTTP_RAW_POST_DATA)){
	$matches = array();
	preg_match_all("/<inv_id_ext.*>(.*)\<\/inv_id_ext>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$order_id=trim($matches[0][1]);

	preg_match_all("/<bat_number_bank.*>(.*)\<\/bat_number_bank>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$number_bank=trim($matches[0][1]);

	preg_match_all("/<inv_id.*>(.*)\<\/inv_id>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$inv_id=trim($matches[0][1]);
	$pos_e = strpos($inv_id,"E");
	$base = substr($inv_id, 0, $pos_e);
	$exp = substr($inv_id, -(strlen($inv_id)-$pos_e-1));
	$transaction_id = $base * pow(10, $exp);

	preg_match_all("/<inv_status.*>(.*)\<\/inv_status>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$status=trim($matches[0][1]);

	preg_match_all("/<inv_authno.*>(.*)\<\/inv_authno>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$inv_authno=trim($matches[0][1]);

	preg_match_all("/<crl_id xsi:type=\"xsd:double\">(.*)\<\/crl_id>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$crl_id = trim($matches[0][1]);
	$pos_e = strpos($crl_id,"E");
	$base = substr($crl_id, 0, $pos_e);
	$exp = substr($crl_id, -(strlen($crl_id)-$pos_e-1));
	$crl_id = $base * pow(10, $exp);
	
	preg_match_all("/<type xsi:type=\"xsd:string\">(.*)\<\/type>/Uis", $HTTP_RAW_POST_DATA, $matches, PREG_SET_ORDER);
	$type = trim($matches[0][1]);

	if ($order_id){
		$payment_parameters = array();
		$pass_parameters = array();
		$post_parameters = '';
		$pass_data = array();
		$variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
		if(strtolower($status) == 'auth'){
			$order_status_id = $variables["success_status_id"];
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", success_message=" . $db->tosql($status . " bat_number_bank:" . $number_bank . " crl_id:" . $crl_id, TEXT) ;
			$sql .= ", authorization_code=" . $db->tosql($inv_authno, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}elseif(strtolower($status) == 'authonly' || strtolower($status) == 'preauth' || strtolower($status) == 'queueauth'){
			$order_status_id = $variables["pending_status_id"];
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", pending_message=" . $db->tosql($status . " bat_number_bank:" . $number_bank . " crl_id:" . $crl_id, TEXT) ;
			$sql .= ", authorization_code=" . $db->tosql($inv_authno, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}else{
			$order_status_id = $variables["failure_status_id"];
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", error_message=" . $db->tosql($status . " bat_number_bank:" . $number_bank . " crl_id:" . $crl_id, TEXT) ;
			$sql .= ", authorization_code=" . $db->tosql($inv_authno, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}
		$t = new VA_Template('.'.$settings["templates_dir"]);
		update_order_status($order_id, $order_status_id, true, "", $status_error);
	}

	$xml_output  = '<?xml version="1.0" encoding="UTF-8"?>';//<?
	$xml_output .= '<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:tns="http://soapinterop.org/">';
	$xml_output .= '	<SOAP-ENV:Body>';
	$xml_output .= '		<ns1:transactResponse xmlns:ns1="http://schemas.xmlsoap.org/soap/envelope/">';
	$xml_output .= '			<response xsi:type="tns:dhd_postback_response">';
	$xml_output .= '				<crl_id xsi:type="xsd:double">'.$crl_id.'</crl_id>';
	$xml_output .= '				<resp_code xsi:type="xsd:double">0</resp_code>';
	$xml_output .= '				<resp_message xsi:type="xsd:string">transact:'.$type.'</resp_message>';
	$xml_output .= '			</response>';
	$xml_output .= '		</ns1:transactResponse>';
	$xml_output .= '	</SOAP-ENV:Body>';
	$xml_output .= '</SOAP-ENV:Envelope>';
	echo $xml_output;
}

?>
