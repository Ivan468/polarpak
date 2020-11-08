<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ads_properties.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/




	function show_ads_properties($item_id)

	{

	 	global $t, $db, $table_prefix;

		global $settings, $currency;



		// connection for properties

		$dbp = new VA_SQL();

		$dbp->DBType     = $db->DBType;

		$dbp->DBDatabase = $db->DBDatabase;

		$dbp->DBUser     = $db->DBUser;

		$dbp->DBPassword = $db->DBPassword;

		$dbp->DBHost     = $db->DBHost;

		$dbp->DBPort       = $db->DBPort;

		$dbp->DBPersistent = $db->DBPersistent;



		$t->set_var("properties", "");

		$sql  = " SELECT * ";

		$sql .= " FROM " . $table_prefix . "ads_properties WHERE item_id=" . $dbp->tosql($item_id, INTEGER);

		$sql .= " AND property_value<>'' AND property_value IS NOT NULL ";

		$sql .= " ORDER BY property_id ";

		$dbp->query($sql);

		if ($dbp->next_record())

		{

			do {

				$property_name = get_translation($dbp->f("property_name"));

				$property_value = get_translation($dbp->f("property_value"));

				$t->set_var("property_name", $property_name);

				$t->set_var("property_value", $property_value);

				$t->parse("properties", true);

			} while ($dbp->next_record());

		}

	}



?>