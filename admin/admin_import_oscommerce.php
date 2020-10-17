<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_import_oscommerce.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	global $dbi,$dbi2, $db, $copy_images,$cart_path;
	$root = $_SERVER["DOCUMENT_ROOT"];
	$directory_osc = $root.'/'.$cart_path.'/images/';
	$directory_c2s = $root.'/c2s/';
	
	// importing categories
	$sqli = " SELECT COUNT(*) FROM osc_categories ";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_categories = $dbi->f(0); // check the total number of records
	
	$cat2cat = array();
	$imported_categories = 0;
	$sqli = " SELECT * FROM osc_categories oc, osc_categories_description ocd WHERE oc.categories_id=ocd.categories_id";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_categories++; // save number of imported records
		importing_data("categories", $imported_categories, $total_categories); // output importings results to the page
		$old_cat_id = $dbi->f("categories_id");
		$category_name = $dbi->f("categories_name");
		if ($dbi->f("categories_image"))
		{
			$category_image = "images/categories/".$dbi->f("categories_image");
			if ($copy_images)
				@copy($directory_osc.'categories/'.$dbi->f("categories_image"), $directory_c2s.$category_image);
		}
			else $category_image = "";
		$parent_id = $dbi->f("parent_id");
		$category_order = $dbi->f("sort_order");
		$date_added = $dbi->f("date_added");
		
		$category_path = "0,";
		$parent_category_id = 0;
		
		if ($parent_id!=0)
		{
			$sqli2 = "SELECT categories_name FROM osc_categories_description WHERE categories_id=".$dbi->tosql($parent_id,INTEGER);
			$dbi2->query($sqli2);
			$dbi2->next_record();
			$parent_name = 	$dbi2->f("categories_name");
			
			$sql = "SELECT category_path,category_id FROM ".$table_prefix."categories WHERE category_name = ".$db->tosql($parent_name,TEXT);
			$db->query($sql);
			$db->next_record();
			$parent_category_path = 	$db->f("category_path");
			$parent_category_id = 	$db->f("category_id");
			
			$category_path = $parent_category_path.$parent_category_id.",";
		}
		
		$sql  = " INSERT INTO ".$table_prefix."categories ";
		$sql .= " (category_id,parent_category_id,category_path,category_name,category_order,is_showing,image,date_added) ";
		$sql .= " VALUES (NULL, ";
		$sql .= $db->tosql($parent_category_id,INTEGER);
		$sql .= "," . $db->tosql($category_path,TEXT);
		$sql .= "," . $db->tosql($category_name,TEXT);
		$sql .= "," . $db->tosql($category_order,INTEGER);
		$sql .= ",1," . $db->tosql($category_image,TEXT).",'".$date_added."')";	
		$db->query($sql);
		
		$db->query("SELECT MAX(category_id) as cat_id FROM ".$table_prefix."categories");
		$db->next_record();
		$cat2cat[$old_cat_id] = $db->f("cat_id");
	}
	
	//importing manufacturers
	$manuf2manuf = array();
	$sqli = " SELECT COUNT(*) FROM osc_manufacturers";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_manuf = $dbi->f(0); // check the total number of records
	
	$imported_manuf = 0;
	$sqli = " SELECT * FROM osc_manufacturers";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_manuf++; // save number of imported records
		importing_data("manuf", $imported_manuf, $total_manuf); // output importings results to the page
		$old_man_id = $dbi->f("manufacturers_id");
		$manufacturer_name = $dbi->f("manufacturers_name");
		if ($dbi->f("manufacturers_image"))
		{
			$image_small = "images/manufacturers/".$dbi->f("manufacturers_image");
			if ($copy_images)
				@copy($directory_osc.'manufacturers/'.$dbi->f("manufacturers_image"), $directory_c2s.$image_small);
		}
		else $image_small = "";
			
		$sql  = " INSERT INTO ".$table_prefix."manufacturers ";
		$sql .= " (manufacturer_id,manufacturer_name,image_small) ";
		$sql .= " VALUES (NULL, ";
		$sql .= $db->tosql($manufacturer_name,TEXT);
		$sql .= "," . $db->tosql($image_small,TEXT).")";	
		$db->query($sql);
		
		$db->query("SELECT MAX(manufacturer_id) as manuf_id FROM ".$table_prefix."manufacturers");
		$db->next_record();
		$manuf2manuf[$old_man_id] = $db->f("manuf_id");
		
	}
	
	// importing products
	$sqli = " SELECT COUNT(*) FROM osc_products WHERE parent_id=0";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_items = $dbi->f(0); // check the total number of records
	
	$item2item = array();
	$imported_items = 0;
	
	//items
	$sqli = " SELECT * FROM osc_products op, osc_products_description opd WHERE op.products_id=opd.products_id AND op.parent_id=0";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_items++; // save number of imported records
		importing_data("items", $imported_items, $total_items); // output importings results to the page
		$old_item_id = $dbi->f("products_id");
		$item_name = $dbi->f("products_name");
		$short_description = $dbi->f("products_description");
		$meta_keywords = $dbi->f("products_keyword");
		$total_views = $dbi->f("products_viewed");
		$price = $dbi->f("products_price");
		$date_added = $dbi->f("products_date_added");
		$weight = $dbi->f("products_weight");
		$item_order = $dbi->f("products_ordered");
		$stock_level = $dbi->f("products_quantity");
		$manufacturer_id_old = $dbi->f("manufacturers_id");
		if ($manufacturer_id_old>0) 
			$manufacturer_id = $manuf2manuf[$manufacturer_id_old];
		else $manufacturer_id = "";	
		
		$sql  = " INSERT INTO ".$table_prefix."items ";
		$sql .= " (item_id,item_name,item_order,item_type_id,is_showing,date_added, ";
		$sql .= " short_description,meta_keywords,total_views,price,weight,manufacturer_id,stock_level,use_stock_level) ";
		$sql .= " VALUES (NULL";
		$sql .= "," . $db->tosql($item_name,TEXT);
		$sql .= "," . $db->tosql($item_order,INTEGER);
		$sql .= ",1,1,'".$date_added."'";
		$sql .= "," . $db->tosql($short_description,TEXT);
		$sql .= "," . $db->tosql($meta_keywords,TEXT);
		$sql .= "," . $db->tosql($total_views,INTEGER);
		$sql .= "," . $db->tosql($price,FLOAT);
		$sql .= "," . $db->tosql($weight,FLOAT);
		$sql .= "," . $db->tosql($manufacturer_id,INTEGER);
		$sql .= "," . $db->tosql($stock_level,INTEGER).",1)";
		$db->query($sql);
		
		$db->query("SELECT MAX(item_id) as item_id FROM ".$table_prefix."items");
		$db->next_record();
		$item_id = $db->f("item_id");
		$item2item[$old_item_id] = $item_id;
		
		
		//items_properties begin 
		$prop_array=array();
		$prop2prop = array();
		$iprop2iprop = array();
		$sqli2 = " SELECT * FROM osc_products op WHERE op.parent_id=".$db->tosql($old_item_id,INTEGER);
		$dbi2->query($sqli2);
		if ($dbi2->next_record()) {
			do 
			{
				$products_id = $dbi2->f("products_id");
				$property_code = $dbi2->f("products_model");
				$property_price = $dbi2->f("products_price");
				$property_weight = $dbi2->f("products_weight");
				$stock_level = $dbi2->f("products_quantity");
				
				$add_price = $property_price - $price;
				$add_weight = $property_weight - $weight;
		
				$prop_array[] = array("products_id" => $products_id, "property_code" => $property_code, "add_price" => $add_price, "add_weight" => $add_weight, "stock_level" => $stock_level);
		
			} while ($dbi2->next_record());
		}
		
		if (sizeof($prop_array)>0)
		{
			foreach($prop_array as $prop)
			{
				
				$sqli2  = " SELECT opvg.id as id, opvv.id as item_property_id, opvv.title as property_value,opvv.sort_order as value_order, ";
				$sqli2 .= " opv.default_combo, opvg.sort_order as property_order, opvg.title as property_name, opvg.module";
				$sqli2 .= " FROM osc_products_variants opv, osc_products_variants_values opvv, osc_products_variants_groups opvg ";
				$sqli2 .= " WHERE opv.products_variants_values_id=opvv.id AND opvv.products_variants_groups_id=opvg.id ";
				$sqli2 .= " AND opv.products_id = ".$db->tosql($prop["products_id"],INTEGER);
				$dbi2->query($sqli2);
				if ($dbi2->next_record())
				{
					do 
					{
						$old_prop_id = $dbi2->f("id");
						$old_iprop_id = $dbi2->f("item_property_id");
						$property_value = $dbi2->f("property_value");
						$property_name = $dbi2->f("property_name");
						$property_order = $dbi2->f("property_order");
						$value_order = $dbi2->f("value_order");
						$is_default_value = $dbi2->f("default_combo");
						$control_type_old = $dbi2->f("module");
						if ($control_type_old == "pull_down_menu") $control_type = "LISTBOX";
							elseif ($control_type_old == "radio_buttons") $control_type = "RADIOBUTTON";
									elseif ($control_type_old == "text_field") $control_type = "TEXTBOX";
										else $control_type = "LISTBOX";
						
						if (isset($prop2prop[$old_prop_id]))	
						{			
							$property_id = $prop2prop[$old_prop_id];
						}
						else 
						{
							$sql  = " INSERT INTO ".$table_prefix."items_properties (property_id,property_type_id,usage_type,item_id,";
							$sql .= " property_order,property_name,use_on_list,use_on_details,control_type)";
							$sql .= " VALUES (NULL,1,1,".$db->tosql($item_id,INTEGER);
							$sql .= " ,".$db->tosql($property_order,INTEGER);
							$sql .= " ,".$db->tosql($property_name,TEXT);
							$sql .= " ,1,1,".$db->tosql($control_type,TEXT).")";
							$db->query($sql);
			
							$db->query("SELECT MAX(property_id) as property_id FROM ".$table_prefix."items_properties");
							$db->next_record();
							$property_id = $db->f("property_id");
							$prop2prop[$old_prop_id] = $property_id;	
						}
						
						$sql  = " INSERT INTO ".$table_prefix."items_properties_values (item_property_id,property_id,property_value,value_order,";
						$sql .= " item_code,additional_price,additional_weight,use_stock_level,stock_level,is_default_value)";
						$sql .= " VALUES (NULL, ".$db->tosql($property_id,INTEGER);
						$sql .= " ,".$db->tosql($property_value,TEXT);
						$sql .= " ,".$db->tosql($value_order,INTEGER);
						$sql .= " ,".$db->tosql($prop["property_code"],TEXT);
						$sql .= " ,".$db->tosql($prop["add_price"],FLOAT);
						$sql .= " ,".$db->tosql($prop["add_weight"],FLOAT);
						$sql .= " ,1,".$db->tosql($prop["stock_level"],INTEGER);
						$sql .= " ,".$db->tosql($is_default_value,INTEGER).")";
						$db->query($sql);
						
						$db->query("SELECT MAX(item_property_id) as item_property_id FROM ".$table_prefix."items_properties_values");
						$db->next_record();
						$item_property_id = $db->f("item_property_id");
						$iprop2iprop[$old_iprop_id] = $item_property_id;
					}
					while ($dbi2->next_record());
				}
			}
		}
		
		//items images begin
		$sqli2 = " SELECT * FROM osc_products_images WHERE products_id=".$db->tosql($old_item_id,INTEGER);;
		$dbi2->query($sqli2);
		if ($dbi2->next_record()) {
			$i=0;
			do 
			{
				$image = $dbi2->f("image");
				$default_flag = $dbi2->f("default_flag");
				$sort_order= $dbi2->f("sort_order");
				$small = 'images/small/'.$image;
				$big = 'images/big/'.$image;
				$super = 'images/super/'.$image;
				$tiny = 'images/tiny/'.$image;
				
				if ($copy_images)
				{
					@copy($directory_osc.'products/mini/'.$image, $directory_c2s.$tiny);
					@copy($directory_osc.'products/thumbnails/'.$image, $directory_c2s.$small);
					@copy($directory_osc.'products/product_info/'.$image, $directory_c2s.$big);
					@copy($directory_osc.'products/large/'.$image, $directory_c2s.$super);
					
				}
				
				if ($i==0)
				{
					$sql  = "UPDATE ".$table_prefix."items SET small_image=".$db->tosql($small,TEXT);
					$sql .= " , big_image=".$db->tosql($big,TEXT);
					$sql .= " , super_image=".$db->tosql($super,TEXT);
					$sql .= " , tiny_image=".$db->tosql($tiny,TEXT);
					$sql .= " WHERE item_id=".$db->tosql($item_id,INTEGER);
					$db->query($sql);
				}
				if ($i>0)
				{
					$sql =  "INSERT INTO ".$table_prefix."items_images (image_id,item_id,image_title,image_small, image_large,image_super) ";
					$sql .= " VALUES (NULL, ".$db->tosql($item_id,INTEGER);
					$sql .= " ,".$db->tosql($image,TEXT);
					$sql .= " ,".$db->tosql($small,TEXT);
					$sql .= " ,".$db->tosql($big,TEXT);
					$sql .= " ,".$db->tosql($super,TEXT)." )";
					$db->query($sql);
				}
				$i++;
			} while($dbi2->next_record());
			
		}
	}
	//items special offer
	$sqli = "SELECT * FROM osc_specials WHERE expires_date<NOW()";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_item_id = $dbi->f("products_id");
			$sales_price = $dbi->f("specials_new_products_price");
			
			if (isset($item2item[$old_item_id]))
			{
				$sql  = " UPDATE ".$table_prefix."items SET is_sales=1, is_special_offer=1,sales_price=".$db->tosql($sales_price,FLOAT);
				$sql .= " WHERE item_id=".$db->tosql($item2item[$old_item_id],INTEGER);
				$db->query($sql);
			}
			
		} while($dbi->next_record());
	}
	
	//items categories
	$sqli = " SELECT * FROM osc_products_to_categories";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$old_item_id = $dbi->f("products_id");
		$old_cat_id = $dbi->f("categories_id");
		
		if (isset($item2item[$old_item_id]))
		{
			$item_id = $item2item[$old_item_id];
			$category_id = $cat2cat[$old_cat_id];
			
			$sql = " INSERT INTO ".$table_prefix."items_categories (item_id,category_id) VALUES (";
			$sql .= $db->tosql($item_id,INTEGER).",";
			$sql .= $db->tosql($category_id,INTEGER).")";
			$db->query($sql);
		}
	}
	
	// importing users
	//countries 
	$co_codes = array();
	$co2co = array();
	$sqli  = " SELECT * FROM osc_countries";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_co_id = $dbi->f("countries_id");
			$country_code = $dbi->f("countries_iso_code_2");
			
			$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_code=".$db->tosql($country_code,TEXT);
			$db->query($sql);
			if ($db->next_record()) 
			{
				$country_id = $db->f("country_id");
				$co2co[$old_co_id] = $country_id;
				$co_codes[$country_id] = $country_code;
			}
			
		
		} while($dbi->next_record());
	}
	
	//insert states
	$stat2stat = array();
	$state_codes = array();
	$sqli  = " SELECT * FROM osc_zones";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_st_id = $dbi->f("zone_id");
			$state_code = $dbi->f("zone_code");
			
			$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_code=".$db->tosql($state_code,TEXT);
			$db->query($sql);
			if ($db->next_record()) 
			{
				$state_id = $db->f("state_id");
				$stat2stat[$old_st_id] = $state_id;
				$state_codes[$state_id] = $state_code;
			}
			
		
		} while($dbi->next_record());
	}
	
	
	$sqli = " SELECT COUNT(*) FROM osc_customers";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_users = $dbi->f(0); // check the total number of records
	
	$imported_users = 0;
	$user2user = array();
	$sqli  = " SELECT * FROM osc_customers oc, osc_address_book oab";
	$sqli .= " WHERE oc.customers_default_address_id=oab.address_book_id";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$imported_users++; // save number of imported records
			importing_data("users", $imported_users, $total_users); // output importings results to the page
			$old_user_id = $dbi->f("customers_id");
			$dob = $dbi->f("customers_dob");
			$email = $dbi->f("customers_email_address");
			$last_logged_date = $dbi->f("date_last_logon");
			$modified_date = $dbi->f("date_account_last_modified");
			$company_name = $dbi->f("entry_company");
			$first_name = $dbi->f("entry_firstname");
			$last_name = $dbi->f("entry_lastname");
			$address1 = $dbi->f("entry_street_address");
			$province = $dbi->f("entry_suburb");
			$zip = $dbi->f("entry_postcode");
			$city = $dbi->f("entry_city");
			$old_co_id = $dbi->f("entry_country_id");
			$old_state_id = $dbi->f("entry_zone_id");
			$phone = $dbi->f("entry_telephone");
			$fax = $dbi->f("entry_fax");
			$password = rand();
			$name = $first_name." ".$last_name;
			if (isset($stat2stat[$old_state_id]))
			{
				$state_id = $stat2stat[$old_state_id];
				$state_code = $state_codes[$state_id];	
			}
			else 
			{
				$state_id = 0;
				$state_code = "";
			}
			if (isset($co2co[$old_co_id]))
			{
				$country_id = $co2co[$old_co_id];
				$country_code = $co_codes[$country_id];
			}
			else 
			{
				$country_id = 0;
				$country_code = "";
			}
			$dob_array = explode("-",$dob);
			
			$sql  = "INSERT INTO ".$table_prefix."users (user_id,user_type_id,login,password,";
			$sql .= " name,first_name,last_name,company_name,email,address1,city,province,state_id,state_code,zip,country_id,country_code,phone,fax,";
			$sql .= " delivery_name,delivery_first_name,delivery_last_name,delivery_company_name,delivery_email,delivery_address1,delivery_city,delivery_province,delivery_state_id,delivery_state_code,delivery_zip,delivery_country_id,delivery_country_code,delivery_phone,delivery_fax,";
			$sql .= " birth_year,birth_month,birth_day) ";
			$sql .= " VALUES (NULL,1,".$db->tosql($email,TEXT).",".$db->tosql($password,TEXT);
			$sql .= ",".$db->tosql($name,TEXT);
			$sql .= ",".$db->tosql($first_name,TEXT);
			$sql .= ",".$db->tosql($last_name,TEXT);
			$sql .= ",".$db->tosql($company_name,TEXT);
			$sql .= ",".$db->tosql($email,TEXT);
			$sql .= ",".$db->tosql($address1,TEXT);
			$sql .= ",".$db->tosql($city,TEXT);
			$sql .= ",".$db->tosql($province,TEXT);
			$sql .= ",".$db->tosql($state_id,TEXT);
			$sql .= ",".$db->tosql($state_code,TEXT);
			$sql .= ",".$db->tosql($zip,TEXT);
			$sql .= ",".$db->tosql($country_id,TEXT);
			$sql .= ",".$db->tosql($country_code,TEXT);
			$sql .= ",".$db->tosql($phone,TEXT);
			$sql .= ",".$db->tosql($fax,TEXT);
			$sql .= ",".$db->tosql($name,TEXT);
			$sql .= ",".$db->tosql($first_name,TEXT);
			$sql .= ",".$db->tosql($last_name,TEXT);
			$sql .= ",".$db->tosql($company_name,TEXT);
			$sql .= ",".$db->tosql($email,TEXT);
			$sql .= ",".$db->tosql($address1,TEXT);
			$sql .= ",".$db->tosql($city,TEXT);
			$sql .= ",".$db->tosql($province,TEXT);
			$sql .= ",".$db->tosql($state_id,TEXT);
			$sql .= ",".$db->tosql($state_code,TEXT);
			$sql .= ",".$db->tosql($zip,TEXT);
			$sql .= ",".$db->tosql($country_id,TEXT);
			$sql .= ",".$db->tosql($country_code,TEXT);
			$sql .= ",".$db->tosql($phone,TEXT);
			$sql .= ",".$db->tosql($fax,TEXT);
			$sql .= ",".$db->tosql($dob_array[0],INTEGER);
			$sql .= ",".$db->tosql($dob_array[1],INTEGER);
			$sql .= ",".$db->tosql($dob_array[2],INTEGER)." )";
			$db->query($sql);
			
			$db->query("SELECT MAX(user_id) as user_id FROM ".$table_prefix."users");
			$db->next_record();
			$user_id = $db->f("user_id");
			$user2user[$old_user_id] = $user_id;	
			
		} while($dbi->next_record());
	}
	
	//items reviews
	$sqli = "SELECT ore.*,oc.customers_ip_address  FROM osc_reviews ore, osc_customers oc WHERE ore.customers_id=oc.customers_id";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_item_id = $dbi->f("products_id");
			$old_user_id = $dbi->f("customers_id");
			$user_name = $dbi->f("customers_name");
			$rating = $dbi->f("reviews_rating");
			$comments = $dbi->f("reviews_text");
			$date_added = $dbi->f("date_added");
			$remote_address = $dbi->f("customers_ip_address");
			if (!strlen($remote_address)) $remote_address = "127.0.0.1";
			$summary = trancate_to_word($comments,25);
			$item_id = $item2item[$old_item_id];
			$user_id = $user2user[$old_user_id];
			
			
			$sql  = " INSERT INTO ".$table_prefix."reviews (review_id,item_id,user_id,remote_address,approved,recommended,rating,summary,user_name,comments,date_added)";
			$sql .= " VALUES (NULL";
			$sql .= ",".$db->tosql($item_id,INTEGER);
			$sql .= ",".$db->tosql($user_id,INTEGER);
			$sql .= ",".$db->tosql($remote_address,TEXT);
			$sql .= ",1,1,".$db->tosql($rating,INTEGER);
			$sql .= ",".$db->tosql($summary,TEXT);
			$sql .= ",".$db->tosql($user_name,TEXT);
			$sql .= ",".$db->tosql($comments,TEXT);
			$sql .= ",'".$date_added."')";
			$db->query($sql);
			
		} while($dbi->next_record());
	}
	
	// importing orders
	$sqli = " SELECT COUNT(*) FROM osc_orders";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_orders = $dbi->f(0); // check the total number of records
	
	$imported_orders = 0;
	$order2order = array();
	
	$orders = array();
	$sqli = "SELECT * FROM osc_orders";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$imported_orders++; // save number of imported records
			importing_data("orders", $imported_orders, $total_orders); // output importings results to the page
			$old_order_id = $dbi->f("orders_id");
			$old_user_id = $dbi->f("customers_id");
			$name = $dbi->f("customers_name");
			$company_name = $dbi->f("customers_company");
			$address1 = $dbi->f("customers_street_address");
			$province = $dbi->f("customers_suburb");
			$zip = $dbi->f("customers_postcode");
			$city = $dbi->f("customers_city");
			$country_code = $dbi->f("customers_country_iso2");
			$state_code = $dbi->f("customers_state_code");
			$phone = $dbi->f("customers_telephone");
			$email = $dbi->f("customers_email_address");
			$remote_address = $dbi->f("customers_ip_address");
			
			$delivery_name = $dbi->f("delivery_name");
			$delivery_company_name = $dbi->f("delivery_company");
			$delivery_address1 = $dbi->f("delivery_street_address");
			$delivery_province = $dbi->f("delivery_suburb");
			$delivery_zip = $dbi->f("delivery_postcode");
			$delivery_city = $dbi->f("delivery_city");
			$delivery_country_code = $dbi->f("delivery_country_iso2");
			$delivery_state_code = $dbi->f("delivery_state_code");
			$delivery_phone = $dbi->f("delivery_telephone");
			
			$billing_name = $dbi->f("billing_name");
			$billing_company_name = $dbi->f("billing_company");
			$billing_address1 = $dbi->f("billing_street_address");
			$billing_province = $dbi->f("billing_suburb");
			$billing_zip = $dbi->f("billing_postcode");
			$billing_city = $dbi->f("billing_city");
			$billing_country_code = $dbi->f("billing_country_iso2");
			$billing_state_code = $dbi->f("billing_state_code");
			$billing_phone = $dbi->f("billing_telephone");
			
			$payment_method = $dbi->f("payment_method");
			$date_purchased = $dbi->f("date_purchased");
			$currency = $dbi->f("currency");
			$orders_status_name = $dbi->f("orders_status_name");
			
			if ($orders_status_name=="Pending" || $orders_status_name=="Preparing" || $orders_status_name=="Processing") $order_status = 6;
			elseif ($orders_status_name=="Delivered") $order_status = 5;
			else $order_status = 1;
			
			$country_id = array_search($country_code,$co_codes);
			$state_id = array_search($state_code,$state_codes);
			
			$delivery_country_id = array_search($delivery_country_code,$co_codes);
			$delivery_state_id = array_search($delivery_state_code,$state_codes);
			
			$billing_country_id = array_search($billing_country_code,$co_codes);
			$billing_state_id = array_search($billing_state_code,$state_codes);
			
			if (isset($user2user[$old_user_id]))
				$user_id = $user2user[$old_user_id];
				else $user_id = 0;
						
			$sql  = " INSERT INTO ".$table_prefix."orders (order_id,site_id,user_id,user_type_id,remote_address,affiliate_code,default_currency_code,currency_code,order_status,";
			$sql .= " name,first_name, last_name,company_name,email,address1,city,province,state_id,state_code,zip,country_id,country_code,phone,";
			$sql .= " delivery_name,delivery_first_name,delivery_last_name,delivery_company_name,delivery_address1,delivery_city,delivery_province,delivery_state_id,delivery_state_code,delivery_zip,delivery_country_id,delivery_country_code,delivery_phone,";
			$sql .= " bill_name,bill_first_name,bill_last_name,bill_company_name,bill_address1,bill_city,bill_province,bill_state_id,bill_zip,bill_country_id,bill_phone,";
			$sql .= " order_placed_date) VALUES (NULL,1,";
			$sql .= $db->tosql($user_id,INTEGER).",1";
			$sql .= ",".$db->tosql($remote_address,TEXT);
			$sql .= ",'',".$db->tosql($currency,TEXT);
			$sql .= ",".$db->tosql($currency,TEXT);
			$sql .= ",".$db->tosql($order_status,INTEGER);
			$sql .= ",".$db->tosql($name,TEXT);
			$sql .= ",".$db->tosql($name,TEXT);
			$sql .= ",".$db->tosql($name,TEXT);
			$sql .= ",".$db->tosql($company_name,TEXT);
			$sql .= ",".$db->tosql($email,TEXT);
			$sql .= ",".$db->tosql($address1,TEXT);
			$sql .= ",".$db->tosql($city,TEXT);
			$sql .= ",".$db->tosql($province,TEXT);
			$sql .= ",".$db->tosql($state_id,TEXT);
			$sql .= ",".$db->tosql($state_code,TEXT);
			$sql .= ",".$db->tosql($zip,TEXT);
			$sql .= ",".$db->tosql($country_id,TEXT);
			$sql .= ",".$db->tosql($country_code,TEXT);
			$sql .= ",".$db->tosql($phone,TEXT);
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_company_name,TEXT);
			$sql .= ",".$db->tosql($delivery_address1,TEXT);
			$sql .= ",".$db->tosql($delivery_city,TEXT);
			$sql .= ",".$db->tosql($delivery_province,TEXT);
			$sql .= ",".$db->tosql($delivery_state_id,TEXT);
			$sql .= ",".$db->tosql($delivery_state_code,TEXT);
			$sql .= ",".$db->tosql($delivery_zip,TEXT);
			$sql .= ",".$db->tosql($delivery_country_id,TEXT);
			$sql .= ",".$db->tosql($delivery_country_code,TEXT);
			$sql .= ",".$db->tosql($delivery_phone,TEXT);
			$sql .= ",".$db->tosql($billing_name,TEXT);
			$sql .= ",".$db->tosql($billing_name,TEXT);
			$sql .= ",".$db->tosql($billing_name,TEXT);
			$sql .= ",".$db->tosql($billing_company_name,TEXT);
			$sql .= ",".$db->tosql($billing_address1,TEXT);
			$sql .= ",".$db->tosql($billing_city,TEXT);
			$sql .= ",".$db->tosql($billing_province,TEXT);
			$sql .= ",".$db->tosql($billing_state_id,TEXT);
			$sql .= ",".$db->tosql($billing_zip,TEXT);
			$sql .= ",".$db->tosql($billing_country_id,TEXT);
			$sql .= ",".$db->tosql($billing_phone,TEXT).",'".$date_purchased."')";
			$db->query($sql);
			
			
			$db->query("SELECT MAX(order_id) as order_id FROM ".$table_prefix."orders");
			$db->next_record();
			$order_id = $db->f("order_id");
			$order2order[$old_order_id] = $order_id;
			
			//import orders items
			$oi2oi = array();
			$sqli2 = "SELECT * FROM osc_orders_products WHERE orders_id=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_oi_id = $dbi2->f("orders_products_id");
					$old_order_id = $dbi2->f("orders_id");
					if ($dbi2->f("parent_id")>0) $old_item_id = $dbi2->f("parent_id");
						else $old_item_id = $dbi2->f("products_id");
					$item_code = $dbi2->f("products_model");
					$item_name = $dbi2->f("products_name");
					$item_price = $dbi2->f("products_price");
					$item_tax = $dbi2->f("products_tax");
					$item_quantity = $dbi2->f("products_quantity");
						
					$order_id = $order2order[$old_order_id];
					if (isset($item2item[$old_item_id])) $item_id = $item2item[$old_item_id];
						else $item_id=0;
						
					$sql  = " INSERT INTO ".$table_prefix."orders_items (order_item_id,order_id,site_id,user_type_id,user_id,";
					$sql .= " item_id,item_code,item_status,item_name,price,quantity)";
					$sql .= " VALUES (NULL";
					$sql .= ",".$db->tosql($order_id,INTEGER);	
					$sql .= ",1,1,".$db->tosql($user_id,INTEGER);	
					$sql .= ",".$db->tosql($item_id,INTEGER);	
					$sql .= ",".$db->tosql($item_code,TEXT);	
					$sql .= ",".$db->tosql($order_status,INTEGER);	
					$sql .= ",".$db->tosql($item_name,TEXT);	
					$sql .= ",".$db->tosql($item_price,FLOAT);	
					$sql .= ",".$db->tosql($item_quantity,INTEGER).")";
					$db->query($sql);
					
					$db->query("SELECT MAX(order_item_id) as order_item_id FROM ".$table_prefix."orders_items");
					$db->next_record();
					$order_item_id = $db->f("order_item_id");
					$oi2oi[$old_oi_id] = $order_item_id;
										
				} while($dbi2->next_record());
			}
			
			//import items properties
			$sqli2  = " SELECT oopv.orders_id, oopv.orders_products_id, oopv.value_title, oopv.group_title, opvv.id as item_property_id";
			$sqli2 .= " FROM osc_orders_products_variants oopv, osc_products_variants_values opvv, osc_products_variants_groups opvg  where opvg.title=oopv.group_title AND opvv.title=oopv.value_title AND oopv.orders_id=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_oi_id = $dbi2->f("orders_products_id");
					$old_order_id = $dbi2->f("orders_id");
					$property_value = $dbi2->f("value_title");
					$property_name = $dbi2->f("group_title");
					$old_iprop_id = $dbi2->f("item_property_id");
					$old_prop_id = $dbi2->f("item_property_id");
					
					$order_id = $order2order[$old_order_id];
					if (isset($item2item[$old_item_id])) $item_id = $item2item[$old_item_id];
						else $item_id=0;
					if (isset($oi2oi[$old_oi_id])) $order_item_id =  $oi2oi[$old_oi_id];
						else $order_item_id = 0;
					if (isset($prop2prop[$old_prop_id])) $property_id =  $prop2prop[$old_prop_id];
						else $property_id = 0;
					
					$sql  = " INSERT INTO ".$table_prefix."orders_items_properties ";
					$sql .= " (item_property_id,order_id,order_item_id,property_id,property_name,property_value)";
					$sql .= " VALUES (NULL ";
					$sql .= ",".$db->tosql($order_id,INTEGER);
					$sql .= ",".$db->tosql($order_item_id,INTEGER);
					$sql .= ",".$db->tosql($property_id,INTEGER);
					$sql .= ",".$db->tosql($property_name,TEXT);
					$sql .= ",".$db->tosql($property_value,TEXT).")";
					$db->query($sql);
					
				} while($dbi2->next_record());
			}
			
			//update order with total values
			$sqli2  = " SELECT * FROM osc_orders_total WHERE orders_id=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_order_id = $dbi2->f("orders_id");
					$total_class = $dbi2->f("class");
					$total_value = $dbi2->f("value");
					
					$order_id = $order2order[$old_order_id];
					$sql_set = "";
					if ($total_class == "shipping")
					{
						$sql_set = " shipping_cost = ".$db->tosql($total_value,FLOAT).", shipping_type_desc = 'Shipping'";
					}
					elseif ($total_class == "total")
					{
						$sql_set = " order_total = ".$db->tosql($total_value,FLOAT);
					}
					
					if (strlen($sql_set))
					{
						$sql = "UPDATE ".$table_prefix."orders SET " . $sql_set . " WHERE order_id=".$db->tosql($order_id,INTEGER);
						$db->query($sql);
					}
						
	
				} while($dbi2->next_record());
			}
			

		} while($dbi->next_record());
	}
?>