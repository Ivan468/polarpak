<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_import_zencart.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	global $dbi,$dbi2, $db, $copy_images,$cart_path;
	$root = $_SERVER["DOCUMENT_ROOT"];
	$directory_zencart = $root.'/'.$cart_path.'/images/';
	$directory_c2s = $root.'/c2s/';
	
	// IMPORTING CATEGORIES
	$sqli = " SELECT COUNT(*) FROM categories ";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_categories = $dbi->f(0); // check the total number of records
	
	$cat2cat = array();
	$imported_categories = 0;
	$sqli = " SELECT * FROM categories c, categories_description cd WHERE c.categories_id=cd.categories_id";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_categories++; // save number of imported records
		importing_data("categories", $imported_categories, $total_categories); // output importings results to the page
		$old_cat_id = $dbi->f("categories_id");
		$category_name = $dbi->f("categories_name");
		if ($dbi->f("categories_image"))
		{
			$old_image = explode("/",$dbi->f("categories_image"));
			$image_size = sizeof($old_image);
			$category_image = "images/categories/".$old_image[$image_size-1];
			if ($copy_images)
				@copy($directory_zencart.$dbi->f("categories_image"), $directory_c2s.$category_image);
		}
			else $category_image = "";
		$parent_id = $dbi->f("parent_id");
		$category_order = $dbi->f("sort_order");
		$date_added = $dbi->f("date_added");
		
		$category_path = "0,";
		$parent_category_id = 0;
		
		if ($parent_id!=0)
		{
			$sqli2 = "SELECT categories_name FROM categories_description WHERE categories_id=".$dbi->tosql($parent_id,INTEGER);
			$dbi2->query($sqli2);
			$dbi2->next_record();
			$parent_name = 	$dbi2->f("categories_name");
			
			$sql = "SELECT category_path,category_id FROM ".$table_prefix."categories WHERE category_name = ".$db->tosql($parent_name,TEXT);
			$db->query($sql);
			$db->next_record();
			$parent_category_path = $db->f("category_path");
			$parent_category_id = $db->f("category_id");
			
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
	$sqli = " SELECT COUNT(*) FROM manufacturers";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_manuf = $dbi->f(0); // check the total number of records
	
	$imported_manuf = 0;
	$sqli = " SELECT * FROM manufacturers";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_manuf++; // save number of imported records
		importing_data("manuf", $imported_manuf, $total_manuf); // output importings results to the page
		$old_man_id = $dbi->f("manufacturers_id");
		$manufacturer_name = $dbi->f("manufacturers_name");
		if ($dbi->f("manufacturers_image"))
		{
			$image_small = "images/".$dbi->f("manufacturers_image");
			if ($copy_images)
				@copy($directory_zencart.$dbi->f("manufacturers_image"), $directory_c2s.$image_small);
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

	//IMPORTING PRODUCTS
	//options types
	$ptypes = array("LISTBOX","TEXTAREA","RADIOBUTTON","CHECKBOXLIST","IMAGEUPLOAD","LABEL");
	
	//products types
	$itypes2itypes = array();
	$sqli = "SELECT * FROM product_types";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$old_itype_id = $dbi->f("type_id");
		$type_name = $dbi->f("type_name");
		
		$sql = "INSERT INTO ".$table_prefix."item_types (item_type_id,item_type_name) VALUES (NULL,".$db->tosql($type_name,TEXT).")";
		$db->query($sql);
		
		$db->query("SELECT MAX(item_type_id) as item_type_id FROM ".$table_prefix."item_types");
		$db->next_record();
		$item_type_id = $db->f("item_type_id");
		$itypes2itypes[$old_itype_id] = $item_type_id;
	}
	
	$sqli = " SELECT COUNT(*) FROM products";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_items = $dbi->f(0); // check the total number of records
	
	$item2item = array();
	$imported_items = 0;
	
	//items
	$sqli = " SELECT * FROM products p, products_description pd WHERE p.products_id=pd.products_id";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_items++; // save number of imported records
		importing_data("items", $imported_items, $total_items); // output importings results to the page
		$old_item_id = $dbi->f("products_id");
		$item_name = $dbi->f("products_name");
		$item_code = $dbi->f("products_model");
		$short_description = $dbi->f("products_description");
		$total_views = $dbi->f("products_viewed");
		$price = $dbi->f("products_price");
		$date_added = $dbi->f("products_date_added");
		$weight = $dbi->f("products_weight");
		$item_order = $dbi->f("products_sort_order");
		$stock_level = $dbi->f("products_quantity");
		$is_shipping_free = $dbi->f("product_is_always_free_shipping");
		if ($dbi->f("products_image"))
		{
			$old_image = explode("/",$dbi->f("products_image"));
			$image_size = sizeof($old_image);
			$small_image = "images/small/".$old_image[$image_size-1];
			if ($copy_images)
				@copy($directory_zencart.$dbi->f("products_image"), $directory_c2s.$small_image);
		}
		else $small_image = "";
		
		$manufacturer_id_old = $dbi->f("manufacturers_id");
		if ($manufacturer_id_old>0) 
			$manufacturer_id = $manuf2manuf[$manufacturer_id_old];
		else $manufacturer_id = "";	
		$old_itype_id = $dbi->f("products_type");
		if ($old_itype_id>0) 
			$item_type_id = $itypes2itypes[$old_itype_id];
		else $item_type_id = 1;	
		
		$price_option = $dbi->f("products_priced_by_attribute");
		
		$sql  = " INSERT INTO ".$table_prefix."items ";
		$sql .= " (item_id,item_name,item_order,item_type_id,is_showing,small_image,date_added, ";
		$sql .= " short_description,total_views,price,weight,manufacturer_id,stock_level,use_stock_level,is_shipping_free) ";
		$sql .= " VALUES (NULL";
		$sql .= "," . $db->tosql($item_name,TEXT);
		$sql .= "," . $db->tosql($item_order,INTEGER);
		$sql .= "," . $db->tosql($item_type_id,INTEGER);
		$sql .= ",	1," . $db->tosql($small_image,TEXT).",'".$date_added."'";
		$sql .= "," . $db->tosql($short_description,TEXT);
		$sql .= "," . $db->tosql($total_views,INTEGER);
		$sql .= "," . $db->tosql($price,FLOAT);
		$sql .= "," . $db->tosql($weight,FLOAT);
		$sql .= "," . $db->tosql($manufacturer_id,INTEGER);
		$sql .= "," . $db->tosql($stock_level,INTEGER).",1";
		$sql .= "," . $db->tosql($is_shipping_free,INTEGER).")";
		$db->query($sql);
		
		$db->query("SELECT MAX(item_id) as item_id FROM ".$table_prefix."items");
		$db->next_record();
		$item_id = $db->f("item_id");
		$item2item[$old_item_id] = $item_id;
		
		//item properties
		$current_prop = -1;
		$prop2prop = array();
		$sqli2  = "SELECT * FROM products_attributes pa, products_options po, products_options_values pov ";
		$sqli2 .= " WHERE po.products_options_id=pa.options_id AND pa.options_values_id=pov.products_options_values_id";
		$sqli2 .= " AND pa.products_id=".$dbi->tosql($old_item_id,INTEGER);//." GROUP BY pa.options_id";
		$dbi2->query($sqli2);
		if ($dbi2->next_record())
		{
			do 
			{
				$old_prop_id = $dbi2->f("options_id");
				//$old_iprop_id = $dbi2->f("item_property_id");
				$property_name = $dbi2->f("products_options_name");
				$property_order = $dbi2->f("products_options_sort_order");
				$property_value = $dbi2->f("products_options_values_name");
				$value_order = $dbi2->f("products_options_values_sort_order");
				$is_default_value = $dbi2->f("attributes_default");
				$control_type_old = $dbi2->f("products_options_type");
				if (isset($ptypes[$control_type_old])) $control_type = $ptypes[$control_type_old];
				 else $control_type = "LISTBOX";
				$required = $dbi2->f("attributes_required");
				
				$price_prefix = $dbi2->f("price_prefix");
				$weight_prefix = $dbi2->f("products_attributes_weight_prefix");
				
				if ($price_prefix == "-") $additional_price = "-".$dbi2->f("options_values_price");
				else $additional_price = $dbi2->f("options_values_price");
				
				if ($weight_prefix == "+") $additional_weight = $dbi2->f("products_attributes_weight");
				
				if ($current_prop != $old_prop_id)
				{
					$sql  = " INSERT INTO ".$table_prefix."items_properties (property_id,property_type_id,usage_type,item_id,";
					$sql .= " property_order,property_name,use_on_list,use_on_details,control_type,required)";
					$sql .= " VALUES (NULL,1,1,".$db->tosql($item_id,INTEGER);
					$sql .= " ,".$db->tosql($property_order,INTEGER);
					$sql .= " ,".$db->tosql($property_name,TEXT);
					$sql .= " ,1,1,".$db->tosql($control_type,TEXT);
					$sql .= " ,".$db->tosql($required,INTEGER).")";
					$db->query($sql);
			
					$db->query("SELECT MAX(property_id) as property_id FROM ".$table_prefix."items_properties");
					$db->next_record();
					$property_id = $db->f("property_id");
					$prop2prop[$old_prop_id] = $property_id;
					
					//if ($control_type_old != 5) 
					$current_prop = $old_prop_id;
				}
				
				if ($control_type_old != 5)
				{
					$sql  = " INSERT INTO ".$table_prefix."items_properties_values (item_property_id,property_id,property_value,value_order,";
					$sql .= " additional_price,additional_weight,is_default_value)";
					$sql .= " VALUES (NULL, ".$db->tosql($property_id,INTEGER);
					$sql .= " ,".$db->tosql($property_value,TEXT);
					$sql .= " ,".$db->tosql($value_order,INTEGER);
					$sql .= " ,".$db->tosql($additional_price,FLOAT);
					$sql .= " ,".$db->tosql($additional_weight,FLOAT);
					$sql .= " ,".$db->tosql($is_default_value,INTEGER).")";
					$db->query($sql);	
				}
				else 
				{
					
					$property_name = $property_value;
					$sql  = " INSERT INTO ".$table_prefix."items_properties (property_id,property_type_id,usage_type,item_id,";
					$sql .= " property_order,property_name,use_on_list,use_on_details,control_type,required)";
					$sql .= " VALUES (NULL,1,1,".$db->tosql($item_id,INTEGER);
					$sql .= " ,".$db->tosql($property_order,INTEGER);
					$sql .= " ,".$db->tosql($property_name,TEXT);
					$sql .= " ,1,1,".$db->tosql($control_type,TEXT);
					$sql .= " ,".$db->tosql($required,INTEGER).")";
					$db->query($sql);
				}
				
							
				$db->query("SELECT MAX(item_property_id) as item_property_id FROM ".$table_prefix."items_properties_values");
				$db->next_record();
				$item_property_id = $db->f("item_property_id");
				//$iprop2iprop[$old_iprop_id] = $item_property_id;		
					
			} while ($dbi2->next_record());
		}
		
	}
	//items special offer
	$sqli = "SELECT * FROM specials";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_item_id = $dbi->f("products_id");
			$sales_price = $dbi->f("specials_new_products_price");
			
			if (isset($item2item[$old_item_id]))
			{
				$sql  = " UPDATE ".$table_prefix."items SET is_sales=1, is_special_offer=1,sales_price=".$db->tosql($sales_price,FLOAT);
				$sql .= " WHERE item_id=".$db->tosql($item2item[$old_item_id],INTEGER)." AND price!='0.00'";
				$db->query($sql);
			}
			
		} while($dbi->next_record());
	}
	
	//items categories
	$sqli = " SELECT * FROM products_to_categories";
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
	
	//IMPORTING USERS
	//countries 
	$co_codes = array();
	$co2co = array();
	$sqli  = " SELECT * FROM countries";
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
				//echo $country_id." - ".$old_co_id."-".$country_code."<br>";
			}
			
		
		} while($dbi->next_record());
	}
	
	//insert states
	$stat2stat = array();
	$state_codes = array();
	$sqli  = " SELECT * FROM zones";
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
	// IMPORTING CATEGORIES
	$sqli = " SELECT COUNT(*) FROM customers ";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_users = $dbi->f(0); // check the total number of records
	
	$imported_users = 0;
	$user2user = array();
	
	$sqli  = " SELECT * FROM customers c, address_book ab";
	$sqli .= " WHERE c.customers_default_address_id=ab.address_book_id";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$imported_users++; // save number of imported records
			importing_data("users", $imported_users, $total_users); // output importings results to the page
			$old_user_id = $dbi->f("customers_id");
			$dob = $dbi->f("customers_dob");
			$email = $dbi->f("customers_email_address");
			$company_name = $dbi->f("entry_company");
			$first_name = $dbi->f("entry_firstname");
			$last_name = $dbi->f("entry_lastname");
			$address1 = $dbi->f("entry_street_address");
			$province = $dbi->f("entry_suburb");
			$zip = $dbi->f("entry_postcode");
			$city = $dbi->f("entry_city");
			$old_co_id = $dbi->f("entry_country_id");
			$old_state_id = $dbi->f("entry_zone_id");
			$phone = $dbi->f("customers_telephone");
			$fax = $dbi->f("customers_fax");
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
			//var_dump($dob_array);
			
			
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
	$sqli = "SELECT *  FROM reviews re, reviews_description red WHERE re.reviews_id=red.reviews_id";
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
			$remote_address = "127.0.0.1";
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
	
	//IMPORTING ORDERS
	
	//importing coupons
	$coupon2coupon = array();
	$sqli  = " SELECT * FROM coupons co LEFT JOIN  coupons_description cd ON co.coupon_id=cd.coupon_id"; 
	$sqli .= " LEFT JOIN coupon_restrict cr ON co.coupon_id=cr.coupon_id";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$coupon_code = $dbi->f("coupon_code");
			$discount_amount = $dbi->f("coupon_amount");
			$orders_min_goods = $dbi->f("coupon_minimum_order");
			$start_date = $dbi->f("coupon_start_date");
			$expiry_date = $dbi->f("coupon_expire_date");
			$coupon_active = $dbi->f("coupon_active");
			$coupon_title = $dbi->f("coupon_name");
			$date_added = $dbi->f("date_created");
			
			if ($coupon_active == "Y") $is_active=1;
				else $is_active=0;
			
			
			$sql  = " INSERT INTO ".$table_prefix."coupons (coupon_id,coupon_code,coupon_title,is_active,apply_order,discount_type,discount_amount,";
			$sql .= " sites_all,users_all,orders_min_goods,start_date,expiry_date,date_added) VALUES (NULL";
			$sql .= ",".$db->tosql($coupon_code,TEXT);
			$sql .= ",".$db->tosql($coupon_title,TEXT);
			$sql .= ",".$db->tosql($is_active,INTEGER);
			$sql .= ",1,5,".$db->tosql($discount_amount,FLOAT);
			$sql .= ",1,1,".$db->tosql($orders_min_goods,FLOAT);
			$sql .= ",'".$start_date."'";
			$sql .= ",'".$expiry_date."'";
			$sql .= ",'".$date_added."')";
			$db->query($sql);
			
			$db->query("SELECT MAX(coupon_id) as coupon_id FROM ".$table_prefix."coupons");
			$db->next_record();
			$coupon_id = $db->f("coupon_id");
			$coupon2coupon[$coupon_id] = $coupon_code;
			
		} while($dbi->next_record());
	}
	
	$cc = array("1" => "Visa",
				"3" => "Mastercard",
				"4" => "AmericanExpress",
				"5" => "Switch",
				"6" => "Solo",
				"7" => "JCB",
				"8" => "Delta",
				"9" => "Eurocard",
				"10" => "Discover");
	
	$sqli = " SELECT COUNT(*) FROM orders";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_orders = $dbi->f(0); // check the total number of records
	
	$imported_orders = 0;
	$order2order = array();
	
	$orders = array();
	$sqli = "SELECT * FROM orders";
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
			$country = $dbi->f("customers_country");
			$state = $dbi->f("customers_state");
			$phone = $dbi->f("customers_telephone");
			$email = $dbi->f("customers_email_address");
			$ip_address = explode("-",$dbi->f("ip_address"));
			$remote_address = trim($ip_address[0]);
			
			$delivery_name = $dbi->f("delivery_name");
			$delivery_company_name = $dbi->f("delivery_company");
			$delivery_address1 = $dbi->f("delivery_street_address");
			$delivery_province = $dbi->f("delivery_suburb");
			$delivery_zip = $dbi->f("delivery_postcode");
			$delivery_city = $dbi->f("delivery_city");
			$delivery_country = $dbi->f("delivery_country");
			$delivery_state = $dbi->f("delivery_state");
			
			$billing_name = $dbi->f("billing_name");
			$billing_company_name = $dbi->f("billing_company");
			$billing_address1 = $dbi->f("billing_street_address");
			$billing_province = $dbi->f("billing_suburb");
			$billing_zip = $dbi->f("billing_postcode");
			$billing_city = $dbi->f("billing_city");
			$billing_country = $dbi->f("billing_country");
			$billing_state = $dbi->f("billing_state");
			
			$coupon_code = $dbi->f("coupon_code");
			$coupon_id = array_search($coupon_code,$coupon2coupon);
			if (!$coupon_id) $coupon_id = 0;
			$cc_type_old = $dbi->f("cc_type");
			$cc_name = $dbi->f("cc_owner");
			$cc_number = $dbi->f("cc_number");
			$cc_expires = $dbi->f("cc_expires");
			$cc_expires_month = intval(substr($cc_expires,0,2));
			$cc_expires_year = intval(substr($cc_expires,2,2));
			$cc_expiry_date = date("Y-m-d H:i:s", mktime(0,0,0,$cc_expires_month,1,$cc_expires_year));
			$cc_cvv = $dbi->f("cc_cvv");
			
			$cc_type = array_search($cc_type_old,$cc);
			if (!$cc_type) $cc_type = 1;
			
			$payment_method = $dbi->f("payment_method");
			if ($payment_method == "Credit Card") $payment_id=1;
			else $payment_id=0;
			$shipping_method = $dbi->f("shipping_method");
			$shipping_module_code = $dbi->f("shipping_module_code");
			$date_purchased = $dbi->f("date_purchased");
			$currency = $dbi->f("currency");
			$orders_status = $dbi->f("orders_status");
			
			if ($orders_status==1 || $orders_status==2) $order_status = 6;
			elseif ($orders_status==3) $order_status = 5;
			else $order_status = 1;
			
			$country_state = get_country_state($country,$state);
			$delivery_country_state = get_country_state($delivery_country,$delivery_state);
			$billing_country_state = get_country_state($billing_country,$billing_state);
			
			if (isset($user2user[$old_user_id]))
				$user_id = $user2user[$old_user_id];
				else $user_id = 0;
						
			$sql  = " INSERT INTO ".$table_prefix."orders (order_id,site_id,user_id,user_type_id,remote_address,affiliate_code,default_currency_code,currency_code,order_status,";
			$sql .= " name,first_name, last_name,company_name,email,address1,city,province,state_id,state_code,zip,country_id,country_code,phone,";
			$sql .= " delivery_name,delivery_first_name,delivery_last_name,delivery_company_name,delivery_address1,delivery_city,delivery_province,delivery_state_id,delivery_state_code,delivery_zip,delivery_country_id,delivery_country_code,";
			$sql .= " bill_name,bill_first_name,bill_last_name,bill_company_name,bill_address1,bill_city,bill_province,bill_state_id,bill_zip,bill_country_id,";
			$sql .= " order_placed_date,shipping_type_code,shipping_type_desc,payment_id, ";
			$sql .= " cc_name,cc_expiry_date,cc_type,cc_security_code,coupons_ids) VALUES (NULL,1,";
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
			$sql .= ",".$db->tosql($country_state["state_id"],TEXT);
			$sql .= ",".$db->tosql($country_state["state_code"],TEXT);
			$sql .= ",".$db->tosql($zip,TEXT);
			$sql .= ",".$db->tosql($country_state["country_id"],TEXT);
			$sql .= ",".$db->tosql($country_state["country_code"],TEXT);
			$sql .= ",".$db->tosql($phone,TEXT);
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_company_name,TEXT);
			$sql .= ",".$db->tosql($delivery_address1,TEXT);
			$sql .= ",".$db->tosql($delivery_city,TEXT);
			$sql .= ",".$db->tosql($delivery_province,TEXT);
			$sql .= ",".$db->tosql($delivery_country_state["state_id"],TEXT);
			$sql .= ",".$db->tosql($delivery_country_state["state_code"],TEXT);
			$sql .= ",".$db->tosql($delivery_zip,TEXT);
			$sql .= ",".$db->tosql($delivery_country_state["country_id"],TEXT);
			$sql .= ",".$db->tosql($delivery_country_state["country_code"],TEXT);
			$sql .= ",".$db->tosql($billing_name,TEXT);
			$sql .= ",".$db->tosql($billing_name,TEXT);
			$sql .= ",".$db->tosql($billing_name,TEXT);
			$sql .= ",".$db->tosql($billing_company_name,TEXT);
			$sql .= ",".$db->tosql($billing_address1,TEXT);
			$sql .= ",".$db->tosql($billing_city,TEXT);
			$sql .= ",".$db->tosql($billing_province,TEXT);
			$sql .= ",".$db->tosql($billing_country_state["state_id"],TEXT);
			$sql .= ",".$db->tosql($billing_zip,TEXT);
			$sql .= ",".$db->tosql($billing_country_state["country_id"],TEXT);
			$sql .= ",'".$date_purchased."'";
			$sql .= ",".$db->tosql($shipping_module_code,TEXT);
			$sql .= ",".$db->tosql($shipping_method,TEXT);
			$sql .= ",".$db->tosql($payment_id,INTEGER);
			$sql .= ",".$db->tosql($cc_name,TEXT);
			$sql .= ",'".$cc_expiry_date."'";
			$sql .= ",".$db->tosql($cc_type,INTEGER);
			$sql .= ",".$db->tosql($cc_cvv,TEXT);
			$sql .= ",".$db->tosql($coupon_id,TEXT).")";
			$db->query($sql);
			
			
			$db->query("SELECT MAX(order_id) as order_id FROM ".$table_prefix."orders");
			$db->next_record();
			$order_id = $db->f("order_id");
			$order2order[$old_order_id] = $order_id;
			
			//import orders items
			$oi2oi = array();
			$sqli2 = "SELECT * FROM orders_products WHERE orders_id=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_oi_id = $dbi2->f("orders_products_id");
					$old_order_id = $dbi2->f("orders_id");
					$old_item_id = $dbi2->f("products_id");
					$item_code = $dbi2->f("products_model");
					$item_name = $dbi2->f("products_name");
					$item_price = $dbi2->f("final_price");
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
			$sqli2  = "SELECT * FROM orders_products_attributes WHERE orders_id=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_oi_id = $dbi2->f("orders_products_id");
					$old_order_id = $dbi2->f("orders_id");
					$property_value = $dbi2->f("products_options_values");
					$property_name = $dbi2->f("products_options");
					
					$price_prefix = $dbi2->f("price_prefix");
					$weight_prefix = $dbi2->f("products_attributes_weight_prefix");
				
					if ($price_prefix == "-") $additional_price = "-".$dbi2->f("options_values_price");
						else $additional_price = $dbi2->f("options_values_price");
				
					if ($weight_prefix == "+") $additional_weight = $dbi2->f("products_attributes_weight");
					
					$order_id = $order2order[$old_order_id];
					if (isset($oi2oi[$old_oi_id])) $order_item_id =  $oi2oi[$old_oi_id];
						else $order_item_id = 0;
					
					$sql  = " INSERT INTO ".$table_prefix."orders_items_properties ";
					$sql .= " (item_property_id,order_id,order_item_id,property_name,property_value,additional_price,additional_weight)";
					$sql .= " VALUES (NULL ";
					$sql .= ",".$db->tosql($order_id,INTEGER);
					$sql .= ",".$db->tosql($order_item_id,INTEGER);
					$sql .= ",".$db->tosql($property_name,TEXT);
					$sql .= ",".$db->tosql($property_value,TEXT);
					$sql .= ",".$db->tosql($additional_price,FLOAT);
					$sql .= ",".$db->tosql($additional_weight,FLOAT).")";
					$db->query($sql);
					
				} while($dbi2->next_record());
			}
			
			//update order with total values
			$sqli2  = " SELECT * FROM orders_total WHERE orders_id=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_order_id = $dbi2->f("orders_id");
					$total_class = $dbi2->f("class");
					$total_value = $dbi2->f("value");
					
					$order_id = $order2order[$old_order_id];
					$sql_set = "";
					if ($total_class == "ot_shipping")
					{
						$sql_set = " shipping_cost = ".$db->tosql($total_value,FLOAT);
					}
					elseif ($total_class == "ot_total")
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
	
function get_country_state($country,$state)
{
	global $db;
	
	$sql = "SELECT country_id, country_code FROM ".$table_prefix."countries WHERE country_name=".$db->tosql($country,TEXT);
	$db->query($sql);
	$db->next_record();
	$country_id = $db->f("country_id");
	$country_code = $db->f("country_code");
	
	$sql = "SELECT state_id, state_code FROM ".$table_prefix."states WHERE state_name=".$db->tosql($state,TEXT);
	$db->query($sql);
	$db->next_record();
	$state_id = $db->f("state_id");
	$state_code = $db->f("state_code");
	
	$result = array("country_id" => $country_id,
					"country_code" => $country_code,
					"state_id" => $state_id,
					"state_code" => $state_code);
					
	return $result;
	
}
?>