<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_export_payment_system.php                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/shopping_cart.php");
	include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("import_export");

	$dbd = new VA_SQL();
	$dbd->DBType       = $db->DBType;
	$dbd->DBDatabase   = $db->DBDatabase;
	$dbd->DBUser       = $db->DBUser;
	$dbd->DBPassword   = $db->DBPassword;
	$dbd->DBHost       = $db->DBHost;
	$dbd->DBPort       = $db->DBPort;
	$dbd->DBPersistent = $db->DBPersistent;

	$eol = get_eol();

	$payment_id = get_param("payment_id");

	$csv_filename = "payment_system_".$payment_id.".xml";
	header("Pragma: private");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream"); 
	header("Content-Disposition: attachment; filename=" . $csv_filename); 
	header("Content-Transfer-Encoding: binary"); 

	echo '<?xml version="1.0" ?>' . $eol;
	echo '<PAYMENT_SYSTEM_SETTINGS>' . $eol;

	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "payment_systems ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$db->query($sql);
	if($db->next_record()) {
		echo '	<payment_system>' . $eol;
		echo '		<payment_order>' .xml_escape_string($db->f("payment_order")). '</payment_order>' . $eol;
		echo '		<payment_name>' .xml_escape_string($db->f("payment_name")). '</payment_name>' . $eol;
		echo '		<user_payment_name>' .xml_escape_string($db->f("user_payment_name")). '</user_payment_name>' . $eol;
		echo '		<payment_info>' .xml_escape_string($db->f("payment_info")). '</payment_info>' . $eol;
		echo '		<payment_notes>' .xml_escape_string($db->f("payment_notes")). '</payment_notes>' . $eol;
		echo '		<payment_url>' .xml_escape_string($db->f("payment_url")). '</payment_url>' . $eol;
		echo '		<processing_time>' .xml_escape_string($db->f("processing_time")). '</processing_time>' . $eol;
		echo '		<processing_fee>' .xml_escape_string($db->f("processing_fee")). '</processing_fee>' . $eol;
		echo '		<fee_type>' .xml_escape_string($db->f("fee_type")). '</fee_type>' . $eol;
		echo '		<fee_min_amount>' .xml_escape_string($db->f("fee_min_amount")). '</fee_min_amount>' . $eol;
		echo '		<fee_max_amount>' .xml_escape_string($db->f("fee_max_amount")). '</fee_max_amount>' . $eol;
		echo '		<submit_method>' .xml_escape_string($db->f("submit_method")). '</submit_method>' . $eol;
		echo '		<recurring_method>' .xml_escape_string($db->f("recurring_method")). '</recurring_method>' . $eol;
		echo '		<is_advanced>' .xml_escape_string($db->f("is_advanced")). '</is_advanced>' . $eol;
		echo '		<advanced_url>' .xml_escape_string($db->f("advanced_url")). '</advanced_url>' . $eol;
		echo '		<advanced_php_lib>' .xml_escape_string($db->f("advanced_php_lib")). '</advanced_php_lib>' . $eol;
		echo '		<capture_php_lib>' .xml_escape_string($db->f("capture_php_lib")). '</capture_php_lib>' . $eol;
		echo '		<refund_php_lib>' .xml_escape_string($db->f("refund_php_lib")). '</refund_php_lib>' . $eol;
		echo '		<void_php_lib>' .xml_escape_string($db->f("void_php_lib")). '</void_php_lib>' . $eol;
		echo '		<success_status_id>' .xml_escape_string($db->f("success_status_id")). '</success_status_id>' . $eol;
		echo '		<pending_status_id>' .xml_escape_string($db->f("pending_status_id")). '</pending_status_id>' . $eol;
		echo '		<failure_status_id>' .xml_escape_string($db->f("failure_status_id")). '</failure_status_id>' . $eol;
		echo '		<failure_action>' .xml_escape_string($db->f("failure_action")). '</failure_action>' . $eol;
		echo '		<is_active>' .xml_escape_string($db->f("is_active")). '</is_active>' . $eol;
		echo '		<is_default>' .xml_escape_string($db->f("is_default")). '</is_default>' . $eol;
		echo '		<is_call_center>' .xml_escape_string($db->f("is_call_center")). '</is_call_center>' . $eol;
		echo '		<sites_all>' .xml_escape_string($db->f("sites_all")). '</sites_all>' . $eol;
		echo '		<user_types_all>' .xml_escape_string($db->f("user_types_all")). '</user_types_all>' . $eol;
		echo '		<image_small>' .xml_escape_string($db->f("image_small")). '</image_small>' . $eol;
		echo '		<image_small_alt>' .xml_escape_string($db->f("image_small_alt")). '</image_small_alt>' . $eol;
		echo '		<image_large>' .xml_escape_string($db->f("image_large")). '</image_large>' . $eol;
		echo '		<image_large_alt>' .xml_escape_string($db->f("image_large_alt")). '</image_large_alt>' . $eol;
		echo '		<fast_checkout_active>' .xml_escape_string($db->f("fast_checkout_active")). '</fast_checkout_active>' . $eol;
		echo '		<fast_checkout_image>' .xml_escape_string($db->f("fast_checkout_image")). '</fast_checkout_image>' . $eol;
		echo '		<fast_checkout_width>' .xml_escape_string($db->f("fast_checkout_width")). '</fast_checkout_width>' . $eol;
		echo '		<fast_checkout_height>' .xml_escape_string($db->f("fast_checkout_height")). '</fast_checkout_height>' . $eol;
		echo '		<fast_checkout_alt>' .xml_escape_string($db->f("fast_checkout_alt")). '</fast_checkout_alt>' . $eol;
		echo '		<order_total_min>' .xml_escape_string($db->f("order_total_min")). '</order_total_min>' . $eol;
		echo '		<order_total_max>' .xml_escape_string($db->f("order_total_max")). '</order_total_max>' . $eol;
	} else {
		echo '</PAYMENT_SYSTEM_SETTINGS>' . $eol;
		exit;
	}
	echo '	</payment_system>' . $eol;

	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "payment_parameters ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$db->query($sql);
	if($db->next_record()) {
		echo '	<payment_parameters>' . $eol;
		do {
			echo '		<parameter>' . $eol;
			echo '			<parameter_name>' .xml_escape_string($db->f("parameter_name")). '</parameter_name>' . $eol;
			echo '			<parameter_type>' .xml_escape_string($db->f("parameter_type")). '</parameter_type>' . $eol;
			echo '			<parameter_source>' .xml_escape_string($db->f("parameter_source")). '</parameter_source>' . $eol;
			echo '			<not_passed>' .xml_escape_string($db->f("not_passed")). '</not_passed>' . $eol;
			echo '		</parameter>' . $eol;
		} while ($db->next_record());
		echo '	</payment_parameters>' . $eol;
	}

	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "payment_systems_sites ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$db->query($sql);
	if($db->next_record()) {
		echo '		<sites>' . $eol;
		do {
			echo '			<site_id>' .xml_escape_string($db->f("site_id")). '</site_id>' . $eol;
		} while ($db->next_record());
		echo '		</sites>' . $eol;
	}
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "payment_user_types ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$db->query($sql);
	if($db->next_record()) {
		echo '		<user_types>' . $eol;
		do {
			echo '			<user_type_id>' .xml_escape_string($db->f("user_type_id")). '</user_type_id>' . $eol;
		} while ($db->next_record());
		echo '		</user_types>' . $eol;
	}

	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "order_custom_properties ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);

	$db->query($sql);

	if($db->next_record()) {

		echo '	<custom_properties>' . $eol;

		do {
			echo '		<custom_property>' . $eol;
			echo '			<site_id>' .xml_escape_string($db->f("site_id")). '</site_id>' . $eol;
			echo '			<property_order>' .xml_escape_string($db->f("property_order")). '</property_order>' . $eol;
			echo '			<property_name>' .xml_escape_string($db->f("property_name")). '</property_name>' . $eol;
			echo '			<property_description>' .xml_escape_string($db->f("property_description")). '</property_description>' . $eol;
			echo '			<default_value>' .xml_escape_string($db->f("default_value")). '</default_value>' . $eol;
			echo '			<property_class>' .xml_escape_string($db->f("property_class")). '</property_class>' . $eol;
			echo '			<property_style>' .xml_escape_string($db->f("property_style")). '</property_style>' . $eol;
			echo '			<property_type>' .xml_escape_string($db->f("property_type")). '</property_type>' . $eol;
			echo '			<property_show>' .xml_escape_string($db->f("property_show")). '</property_show>' . $eol;
			echo '			<tax_free>' .xml_escape_string($db->f("tax_free")). '</tax_free>' . $eol;
			echo '			<control_type>' .xml_escape_string($db->f("control_type")). '</control_type>' . $eol;
			echo '			<control_style>' .xml_escape_string($db->f("control_style")). '</control_style>' . $eol;
			echo '			<control_code>' .xml_escape_string($db->f("control_code")). '</control_code>' . $eol;
			echo '			<onchange_code>' .xml_escape_string($db->f("onchange_code")). '</onchange_code>' . $eol;
			echo '			<onclick_code>' .xml_escape_string($db->f("onclick_code")). '</onclick_code>' . $eol;
			echo '			<required>' .xml_escape_string($db->f("required")). '</required>' . $eol;
			echo '			<before_name_html>' .xml_escape_string($db->f("before_name_html")). '</before_name_html>' . $eol;
			echo '			<after_name_html>' .xml_escape_string($db->f("after_name_html")). '</after_name_html>' . $eol;
			echo '			<before_control_html>' .xml_escape_string($db->f("before_control_html")). '</before_control_html>' . $eol;
			echo '			<after_control_html>' .xml_escape_string($db->f("after_control_html")). '</after_control_html>' . $eol;
			echo '			<validation_regexp>' .xml_escape_string($db->f("validation_regexp")). '</validation_regexp>' . $eol;
			echo '			<regexp_error>' .xml_escape_string($db->f("regexp_error")). '</regexp_error>' . $eol;
			echo '			<options_values_sql>' .xml_escape_string($db->f("options_values_sql")). '</options_values_sql>' . $eol;

			$property_id = $db->f("property_id");

			$sql  = " SELECT * ";

			$sql .= " FROM " . $table_prefix . "order_custom_values ";

			$sql .= " WHERE property_id=" . $dbd->tosql($property_id, INTEGER);

			$dbd->query($sql);

			while($dbd->next_record()) {

				echo '			<custom_value>' . $eol;

				echo '				<property_value>' .xml_escape_string($dbd->f("property_value")). '</property_value>' . $eol;

				echo '				<property_price>' .xml_escape_string($dbd->f("property_price")). '</property_price>' . $eol;

				echo '				<property_weight>' .xml_escape_string($dbd->f("property_weight")). '</property_weight>' . $eol;

				echo '				<hide_value>' .xml_escape_string($dbd->f("hide_value")). '</hide_value>' . $eol;

				echo '				<is_default_value>' .xml_escape_string($dbd->f("is_default_value")). '</is_default_value>' . $eol;

				echo '			</custom_value>' . $eol;

			}

			echo '		</custom_property>' . $eol;

		} while ($db->next_record());

		echo '	</custom_properties>' . $eol;

	}

	$sql  = " SELECT site_id ";

	$sql .= " FROM " . $table_prefix . "global_settings ";

	$sql .= " WHERE setting_type = 'credit_card_info_" . $db->tosql($payment_id, INTEGER) . "' ";

	$sql .= " GROUP BY site_id ";

	$db->query($sql);

	while($db->next_record()) {

		$site_id = $db->f("site_id");

		echo '	<credit_card_info>' . $eol;

		echo '		<site_id>' .xml_escape_string($site_id). '</site_id>' . $eol;

		$sql  = " SELECT * ";

		$sql .= " FROM " . $table_prefix . "global_settings ";

		$sql .= " WHERE setting_type = 'credit_card_info_" . $db->tosql($payment_id, INTEGER) . "' ";

		$sql .= " AND site_id=" . $dbd->tosql($site_id, INTEGER);

		$dbd->query($sql);

		while($dbd->next_record()) {

			echo '		<' . $dbd->f("setting_name") . '>' .xml_escape_string($dbd->f("setting_value")). '</' . $dbd->f("setting_name") . '>' . $eol;

		}

		echo '	</credit_card_info>' . $eol;

	}

	$sql  = " SELECT site_id ";

	$sql .= " FROM " . $table_prefix . "global_settings ";

	$sql .= " WHERE setting_type = 'order_final_" . $db->tosql($payment_id, INTEGER) . "' ";

	$sql .= " GROUP BY site_id ";

	$db->query($sql);

	while($db->next_record()) {

		$site_id = $db->f("site_id");

		echo '	<order_final>' . $eol;

		echo '		<site_id>' .xml_escape_string($site_id). '</site_id>' . $eol;

		$sql  = " SELECT * ";

		$sql .= " FROM " . $table_prefix . "global_settings ";

		$sql .= " WHERE setting_type = 'order_final_" . $db->tosql($payment_id, INTEGER) . "' ";

		$sql .= " AND site_id=" . $dbd->tosql($site_id, INTEGER);

		$dbd->query($sql);

		while($dbd->next_record()) {

			echo '		<' . $dbd->f("setting_name") . '>' .xml_escape_string($dbd->f("setting_value")). '</' . $dbd->f("setting_name") . '>' . $eol;

		}

		echo '	</order_final>' . $eol;

	}

	$sql  = " SELECT site_id ";

	$sql .= " FROM " . $table_prefix . "global_settings ";

	$sql .= " WHERE setting_type = 'recurring_" . $db->tosql($payment_id, INTEGER) . "' ";

	$sql .= " GROUP BY site_id ";

	$db->query($sql);

	while($db->next_record()) {

		$site_id = $db->f("site_id");

		echo '	<recurring>' . $eol;

		echo '		<site_id>' .xml_escape_string($site_id). '</site_id>' . $eol;

		$sql  = " SELECT * ";

		$sql .= " FROM " . $table_prefix . "global_settings ";

		$sql .= " WHERE setting_type = 'recurring_" . $db->tosql($payment_id, INTEGER) . "' ";

		$sql .= " AND site_id=" . $dbd->tosql($site_id, INTEGER);

		$dbd->query($sql);

		while($dbd->next_record()) {

			echo '		<' . $dbd->f("setting_name") . '>' .xml_escape_string($dbd->f("setting_value")). '</' . $dbd->f("setting_name") . '>' . $eol;

		}

		echo '	</recurring>' . $eol;

	}

	echo '</PAYMENT_SYSTEM_SETTINGS>' . $eol;



?>