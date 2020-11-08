<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_import_xcart.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	global $dbi,$dbi2, $db, $copy_images,$cart_path;
	$root = $_SERVER["DOCUMENT_ROOT"];

	$table_prefix = "xcart_";
	// IMPORTING CATEGORIES
	$sqli = " SELECT COUNT(*) FROM ".$table_prefix."categories ";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_categories = $dbi->f(0); // check the total number of records
	
	$cat2cat = array();
	$imported_categories = 0;
	$sqli = " SELECT * FROM ".$table_prefix."categories ORDER BY categoryid";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_categories++; // save number of imported records
		importing_data("categories", $imported_categories, $total_categories); // output importings results to the page
		$old_cat_id = $dbi->f("categoryid");
		$category_name = $dbi->f("category");
		$short_description = $dbi->f("description");
		$meta_description = $dbi->f("meta_description");
		$meta_keywords = $dbi->f("meta_keywords");
		
		$category_image = "";
		$sqli2 = "SELECT filename FROM ".$table_prefix."images_c WHERE id=".$db->tosql($old_cat_id,INTEGER);
		$dbi2->query($sqli2);
		if ($dbi2->next_record())
		{
			$category_image = "images/categories/".$dbi2->f("filename");
		}
			 
		$old_parent_id = $dbi->f("parentid");
		if (isset($cat2cat[$old_parent_id])) $parent_id = $cat2cat[$old_parent_id];
			else $parent_id = 0;
			
		$category_path = "0,";
		
		if ($parent_id!=0)
		{
			$sql = "SELECT category_path FROM ".$table_prefix."categories WHERE category_id = ".$db->tosql($parent_id,INTEGER);
			$db->query($sql);
			$db->next_record();
			$parent_category_path = 	$db->f("category_path");
			$category_path = $parent_category_path.$parent_id.",";
		}
			
		$category_order = $dbi->f("order_by");
		$date_added = $dbi->f("date_added");
		$avail = $dbi->f("avail");
		$total_views = $dbi->f("views_stats");
		
		if ($avail == "Y") $is_showing = 1; 
			else $is_showing = 0;
		
		
		$sql  = " INSERT INTO ".$table_prefix."categories ";
		$sql .= " (category_id,parent_category_id,category_path,category_name,category_order,is_showing,image,short_description,meta_description,meta_keywords) ";
		$sql .= " VALUES (NULL, ";
		$sql .= $db->tosql($parent_id,INTEGER);
		$sql .= "," . $db->tosql($category_path,TEXT);
		$sql .= "," . $db->tosql($category_name,TEXT);
		$sql .= "," . $db->tosql($category_order,INTEGER);
		$sql .= "," . $db->tosql($is_showing,INTEGER);
		$sql .= "," . $db->tosql($category_image,TEXT);	
		$sql .= "," . $db->tosql($short_description,TEXT);	
		$sql .= "," . $db->tosql($meta_description,TEXT);	
		$sql .= "," . $db->tosql($meta_keywords,TEXT).")";	
		$db->query($sql);
		
		$db->query("SELECT MAX(category_id) as cat_id FROM ".$table_prefix."categories");
		$db->next_record();
		$cat2cat[$old_cat_id] = $db->f("cat_id");
	}
	
	//copy categories images 
	if ($copy_images)
	{
		$directory_xcart = $root.'/'.$cart_path.'/images/C/';
		$directory_c2s = $root.'/c2s/images/categories/';
		$files = array();
		
		if ($handle = opendir($directory_xcart)) {
		    while (false !== ($file = readdir($handle))) {
		        $files[] = $file;
		    }
		    closedir($handle);
		}
		
		foreach ($files as $file)
		{
			$newfile = $directory_c2s.$file;
			@copy($directory_xcart.$file, $newfile);
		}
	}
	
	//importing manufacturers
	$manuf2manuf = array();
	$sqli = " SELECT COUNT(*) FROM ".$table_prefix."manufacturers";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_manuf = $dbi->f(0); // check the total number of records
	
	$imported_manuf = 0;
	$sqli = " SELECT * FROM ".$table_prefix."manufacturers m, ".$table_prefix."images_m im WHERE m.manufacturerid=im.id ";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$imported_manuf++; // save number of imported records
		importing_data("manuf", $imported_manuf, $total_manuf); // output importings results to the page
		$old_man_id = $dbi->f("manufacturerid");
		$manufacturer_name = $dbi->f("manufacturer");
		$short_description = $dbi->f("descr");
		$manufacturer_order = $dbi->f("orderby");
		if ($dbi->f("filename"))
			$image_small = "images/manufacturers/".$dbi->f("filename");
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
	$sqli = " SELECT COUNT(*) FROM ".$table_prefix."products";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_items = $dbi->f(0); // check the total number of records
	
	$item2item = array();
	$imported_items = 0;
	
	//items
	$sqli = " SELECT * FROM ".$table_prefix."products";
	$dbi->query($sqli);
	while ($dbi->next_record()) 
	{
		$imported_items++; // save number of imported records
		importing_data("items", $imported_items, $total_items); // output importings results to the page
		$old_item_id = $dbi->f("productid");
		$item_name = $dbi->f("product");
		$item_code = $dbi->f("productcode");
		$length = $dbi->f("length");
		$width = $dbi->f("width");
		$height = $dbi->f("height");
		$short_description = $dbi->f("descr");
		$full_description = $dbi->f("fulldescr");
		$total_views = $dbi->f("views_stats");
		$price = $dbi->f("list_price");
		$weight = $dbi->f("weight");
		$stock_level = $dbi->f("avail");
		$is_shipping_free = ($dbi->f("free_shipping")=='Y') ? 1 : 0;
		$is_showing = ($dbi->f("forsale")=='Y') ? 1 : 0;
		$tax_free = ($dbi->f("free_tax")=='Y') ? 1 : 0;
		$meta_description = $dbi->f("meta_description");
		$meta_keywords = $dbi->f("meta_keywords");
		
		$small_image = "";
		$sqli2 = "SELECT filename FROM ".$table_prefix."images_t WHERE id=".$db->tosql($old_item_id,INTEGER);
		$dbi2->query($sqli2);
		if ($dbi2->next_record())
		{
			$small_image = "images/small/".$dbi2->f("filename");
		}
			
		$manufacturer_id_old = $dbi->f("manufacturerid");
		if ($manufacturer_id_old>0) 
			$manufacturer_id = $manuf2manuf[$manufacturer_id_old];
		else $manufacturer_id = "";	
		$item_type_id = 1;	
		
		$price_option = $dbi->f("products_priced_by_attribute");
		
		$sql  = " INSERT INTO ".$table_prefix."items ";
		$sql .= " (item_id,item_name,item_type_id,is_showing,small_image,short_description,full_description,total_views,price,";
		$sql .= " weight,length,width,height,manufacturer_id,stock_level,use_stock_level,is_shipping_free,tax_free,meta_keywords,meta_description) ";
		$sql .= " VALUES (NULL";
		$sql .= "," . $db->tosql($item_name,TEXT);
		$sql .= "," . $db->tosql($item_type_id,INTEGER);
		$sql .= "," . $db->tosql($is_showing,INTEGER);
		$sql .= "," . $db->tosql($small_image,TEXT);
		$sql .= "," . $db->tosql($short_description,TEXT);
		$sql .= "," . $db->tosql($full_description,TEXT);
		$sql .= "," . $db->tosql($total_views,INTEGER);
		$sql .= "," . $db->tosql($price,FLOAT);
		$sql .= "," . $db->tosql($weight,FLOAT);
		$sql .= "," . $db->tosql($length,FLOAT);
		$sql .= "," . $db->tosql($width,FLOAT);
		$sql .= "," . $db->tosql($height,FLOAT);
		$sql .= "," . $db->tosql($manufacturer_id,INTEGER);
		$sql .= "," . $db->tosql($stock_level,INTEGER).",1";
		$sql .= "," . $db->tosql($is_shipping_free,INTEGER);
		$sql .= "," . $db->tosql($tax_free,INTEGER);
		$sql .= "," . $db->tosql($meta_keywords,TEXT);
		$sql .= "," . $db->tosql($meta_description,TEXT).")";
		$db->query($sql);
		
		$db->query("SELECT MAX(item_id) as item_id FROM ".$table_prefix."items");
		$db->next_record();
		$item_id = $db->f("item_id");
		$item2item[$old_item_id] = $item_id;
	

		$item_prices = array();
		$sqli2 = "SELECT * FROM ".$table_prefix."pricing WHERE productid=".$db->tosql($old_item_id,INTEGER)." AND variantid=0 ORDER BY quantity";	
		$dbi2->query($sqli2);
		while ($dbi2->next_record())
		{
			
			$item_prices[] = array("quantity" => $dbi2->f("quantity"), "price" => $dbi2->f("price"));
		}
		
		for ($i=0; $i < sizeof($item_prices); $i++)
		{
			$this_price = $item_prices[$i]["price"];
			$this_quantity = $item_prices[$i]["quantity"];
			
			if ($this_quantity == 1)
			{
				$sql  = " UPDATE ".$table_prefix."items SET is_sales=1,sales_price=".$db->tosql($this_price,FLOAT);
				$sql .= " WHERE item_id=".$db->tosql($item2item[$old_item_id],INTEGER)." AND price!=0.00";
				$do = $db->query($sql);
						
				$sql  = " UPDATE ".$table_prefix."items SET price=".$db->tosql($this_price,FLOAT);
				$sql .= " WHERE item_id=".$db->tosql($item2item[$old_item_id],INTEGER)." AND price=0.00";
				$db->query($sql);
			}
			else 
			{
				$min_quantity = $this_quantity;
				if (isset($item_prices[$i+1]["quantity"]))
				{
					$max_quantity = $item_prices[$i+1]["quantity"];
				}
				else $max_quantity = 100;
				
				$sql  = " INSERT INTO ".$table_prefix."items_prices(price_id,item_id,is_active,min_quantity,max_quantity,price,discount_action)";
				$sql .= " VALUES (NULL ";
				$sql .= ",".$db->tosql($item2item[$old_item_id],INTEGER);
				$sql .= ",1,".$db->tosql($min_quantity,INTEGER);
				$sql .= ",".$db->tosql($max_quantity,INTEGER);
				$sql .= ",".$db->tosql($this_price,FLOAT);
				$sql .= ",2)";
			}
				
		}
	
	}
	
	//copy items images 
	if ($copy_images)
	{
		$directory_xcart = $root.'/'.$cart_path.'/images/T/';
		$directory_c2s = $root.'/c2s/images/small/';
		$files = array();
		
		if ($handle = opendir($directory_xcart)) {
		    while (false !== ($file = readdir($handle))) {
		        $files[] = $file;
		    }
		    closedir($handle);
		}
		
		foreach ($files as $file)
		{
			$newfile = $directory_c2s.$file;
			@copy($directory_xcart.$file, $newfile);
		}
	}
	
	//item properties
	$prop2prop = array();
	$sqli  = "SELECT * FROM ".$table_prefix."classes";
	$dbi->query($sqli);
	if ($dbi->next_record())
	{
		do 
		{
				$old_prop_id = $dbi->f("classid");
				$old_item_id = $dbi->f("productid");
				$property_name = $dbi->f("classtext");
				$control_type = "LISTBOX";
				if (isset($item2item[$old_item_id]))
					$item_id = $item2item[$old_item_id];
					else  $item_id = 0;
				
				$sql  = " INSERT INTO ".$table_prefix."items_properties (property_id,property_type_id,usage_type,item_id,";
				$sql .= " property_name,use_on_list,use_on_details,control_type)";
				$sql .= " VALUES (NULL,1,1,".$db->tosql($item_id,INTEGER);
				$sql .= " ,".$db->tosql($property_name,TEXT);
				$sql .= " ,1,1,".$db->tosql($control_type,TEXT).")";
				$db->query($sql);
			
				$db->query("SELECT MAX(property_id) as property_id FROM ".$table_prefix."items_properties");
				$db->next_record();
				$property_id = $db->f("property_id");
				$prop2prop[$old_prop_id] = $property_id;
		} while ($dbi->next_record());

	}		

	//items properties values
	$iprop2iprop = array();
	$sqli  = "SELECT * FROM ".$table_prefix."class_options";
	$dbi->query($sqli);
	if ($dbi->next_record())
	{
		do 
		{	
			
			$old_prop_id = $dbi->f("classid");
			$old_iprop_id = $dbi->f("optionid");
			$property_value = $dbi->f("option_name");
			$additional_price = $dbi->f("price_modifier");
			
			if (isset($prop2prop[$old_prop_id]))
					$property_id = $prop2prop[$old_prop_id];
					else  $property_id = 0;
			
			$sql  = " INSERT INTO ".$table_prefix."items_properties_values (item_property_id,property_id,property_value,additional_price)";
			$sql .= " VALUES (NULL, ".$db->tosql($property_id,INTEGER);
			$sql .= " ,".$db->tosql($property_value,TEXT);
			$sql .= " ,".$db->tosql($additional_price,FLOAT).")";
			$db->query($sql);	
								
			$db->query("SELECT MAX(item_property_id) as item_property_id FROM ".$table_prefix."items_properties_values");
			$db->next_record();
			$item_property_id = $db->f("item_property_id");
			$iprop2iprop[$old_iprop_id] = $item_property_id;
							
		} while ($dbi->next_record());

	}		

	//related items
	$sqli = "SELECT * FROM ".$table_prefix."product_links";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_item_id1 = $dbi->f("productid1");	
			$old_item_id2 = $dbi->f("productid2");	
			$related_order = $dbi->f("order_by");	
			
			if (isset($item2item[$old_item_id1])) $item_id = $item2item[$old_item_id1]; 
				else $item_id = 0;
			
			
			if (isset($item2item[$old_item_id2])) $related_id = $item2item[$old_item_id2]; 
				else $related_id = 0;
				
			if ($item_id>0 && $related_id>0)
			{
				$sql  = "INSERT INTO ".$table_prefix."items_related VALUES( ";
				$sql .= $db->tosql($item_id,INTEGER);
				$sql .= ",".$db->tosql($related_id,INTEGER);
				$sql .= ",1)";
				$db->query($sql); 
			}
			
		} while($dbi->next_record());
	}
	
	//items categories
	$sqli = " SELECT * FROM ".$table_prefix."products_categories";
	$dbi->query($sqli);
	while ($dbi->next_record()) {
		$old_item_id = $dbi->f("productid");
		$old_cat_id = $dbi->f("categoryid");
		
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
	
	
	// IMPORTING USERS
	$sqli = " SELECT COUNT(*) FROM ".$table_prefix."customers WHERE usertype='C'";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_users = $dbi->f(0); // check the total number of records
	
	$imported_users = 0;
	$user2user = array();
	
	$sqli  = " SELECT * FROM ".$table_prefix."customers WHERE usertype='C'";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$imported_users++; // save number of imported records
			importing_data("users", $imported_users, $total_users); // output importings results to the page
			$email = $dbi->f("email");
			$company_name = $dbi->f("company");
			$first_name = $dbi->f("firstname");
			$last_name = $dbi->f("lastname");
			$address1 = $dbi->f("b_address");
			$province = $dbi->f("b_county");
			$zip = $dbi->f("b_zipcode");
			$city = $dbi->f("b_city");
			$country_code = $dbi->f("b_country");
			$state_code = $dbi->f("b_state");
			$phone = $dbi->f("phone");
			$fax = $dbi->f("fax");
			$login = $dbi->f("login");
			$password = $dbi->f("login");
			$name = $first_name." ".$last_name;
			
			$delivery_first_name = (strlen($dbi->f("s_firstname"))>0) ? $dbi->f("s_firstname") : $first_name;
			$delivery_last_name = (strlen($dbi->f("s_lastname"))>0) ? $dbi->f("s_lastname") : $last_name;
			$delivery_address1 = (strlen($dbi->f("s_address"))>0) ? $dbi->f("s_address") : $address1;
			$delivery_city = (strlen($dbi->f("s_city"))>0) ? $dbi->f("s_city") : $city;
			$delivery_province = (strlen($dbi->f("s_county"))>0) ? $dbi->f("s_county") : $province;
			$delivery_state_code = (strlen($dbi->f("s_state"))>0) ? $dbi->f("s_state") : $state_code;
			$delivery_country_code = (strlen($dbi->f("s_country"))>0) ? $dbi->f("s_country") : $country_code;
			$delivery_zip = (strlen($dbi->f("s_zipcode"))>0) ? $dbi->f("s_zipcode") : $zip;
			$delivery_name = $delivery_first_name." ".$delivery_last_name;
			
			$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_code=".$db->tosql($state_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$state_id = $db->f("state_id");
			
			$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_code=".$db->tosql($delivery_state_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$delivery_state_id = $db->f("state_id");
			
			$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_code=".$db->tosql($country_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$country_id = $db->f("country_id");
			
			$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_code=".$db->tosql($delivery_country_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$delivery_country_id = $db->f("country_id");
			
			$sql  = "INSERT INTO ".$table_prefix."users (user_id,user_type_id,login,password,";
			$sql .= " name,first_name,last_name,company_name,email,address1,city,province,state_id,state_code,zip,country_id,country_code,phone,fax,";
			$sql .= " delivery_name,delivery_first_name,delivery_last_name,delivery_company_name,delivery_email,delivery_address1,delivery_city,delivery_province,delivery_state_id,delivery_state_code,delivery_zip,delivery_country_id,delivery_country_code,delivery_phone) ";
			$sql .= " VALUES (NULL,1,".$db->tosql($login,TEXT).",".$db->tosql($password,TEXT);
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
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_first_name,TEXT);
			$sql .= ",".$db->tosql($delivery_last_name,TEXT);
			$sql .= ",".$db->tosql($company_name,TEXT);
			$sql .= ",".$db->tosql($email,TEXT);
			$sql .= ",".$db->tosql($delivery_address1,TEXT);
			$sql .= ",".$db->tosql($delivery_city,TEXT);
			$sql .= ",".$db->tosql($delivery_province,TEXT);
			$sql .= ",".$db->tosql($delivery_state_id,TEXT);
			$sql .= ",".$db->tosql($delivery_state_code,TEXT);
			$sql .= ",".$db->tosql($delivery_zip,TEXT);
			$sql .= ",".$db->tosql($delivery_country_id,TEXT);
			$sql .= ",".$db->tosql($delivery_country_code,TEXT);
			$sql .= ",".$db->tosql($phone,TEXT)." )";
			$db->query($sql);
			
		} while($dbi->next_record());
	}
	
	//items reviews
	$sqli  = "SELECT *  FROM ".$table_prefix."product_reviews pr, ".$table_prefix."product_votes pv"; 
	$sqli .= " WHERE pr.remote_ip=pv.remote_ip AND pr.productid=pv.productid";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$old_item_id = $dbi->f("productid");
			$user_name = $dbi->f("email");
			$rating = $dbi->f("vote_value");
			$comments = $dbi->f("message");
			$date_added = date("Y-m-d H:i:s");
			$remote_address = $dbi->f("remote_ip");
			$summary = trancate_to_word($comments,25);
			$item_id = $item2item[$old_item_id];
			
			
			$sql  = " INSERT INTO ".$table_prefix."reviews (review_id,item_id,remote_address,approved,recommended,rating,summary,user_name,comments,date_added)";
			$sql .= " VALUES (NULL";
			$sql .= ",".$db->tosql($item_id,INTEGER);
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
	$sqli  = " SELECT * FROM ".$table_prefix."discount_coupons";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$coupon_code = $dbi->f("coupon");
			$discount_amount = $dbi->f("discount");
			$orders_min_goods = $dbi->f("minimum");
			$coupon_title = $dbi->f("coupon");
			$coupon_uses = $dbi->f("times_used");
			$productid = $dbi->f("productid");
			$category_id = $dbi->f("categoryid");
			$is_active=1;
			$items_ids = "";
			
			if ($category_id)
			{
				$sqli2 = "SELECT productid FROM ".$table_prefix."products_categories WHERE categoryid=".$db->tosql($category_id,INTEGER);
				$dbi2->query($sqli2);
				if ($dbi2->next_record()) {
					do 
					{
						$old_item_id = $dbi->f("productid");
						$item_id = $item2item[$old_item_id];
						$items_ids .= $item_id.",";
					} while($dbi2->next_record());
				}  
			}
			elseif($productid)
			{
				$item_id = $item2item[$productid];
				$items_ids = $item_id;
			}
			
			$sql  = " INSERT INTO ".$table_prefix."coupons (coupon_id,coupon_code,coupon_title,is_active,apply_order,discount_type,discount_amount,";
			$sql .= " sites_all,users_all,orders_min_goods,items_ids) VALUES (NULL";
			$sql .= ",".$db->tosql($coupon_code,TEXT);
			$sql .= ",".$db->tosql($coupon_title,TEXT);
			$sql .= ",".$db->tosql($is_active,INTEGER);
			$sql .= ",1,5,".$db->tosql($discount_amount,FLOAT);
			$sql .= ",1,1,".$db->tosql($orders_min_goods,FLOAT);
			$sql .= ",".$db->tosql($items_ids,TEXT)."')";
			$db->query($sql);
			
			$db->query("SELECT MAX(coupon_id) as coupon_id FROM ".$table_prefix."coupons");
			$db->next_record();
			$coupon_id = $db->f("coupon_id");
			$coupon2coupon[$coupon_id] = $coupon_code;
			
		} while($dbi->next_record());
	}
	
	$sqli = " SELECT COUNT(*) FROM ".$table_prefix."orders";
	$dbi->query($sqli);
	$dbi->next_record($sqli);
	$total_orders = $dbi->f(0); // check the total number of records
	
	$imported_orders = 0;
	$order2order = array();
	
	$orders = array();
	$sqli = "SELECT * FROM ".$table_prefix."orders";
	$sqli .= " ";
	$dbi->query($sqli);
	if ($dbi->next_record()) {
		do 
		{
			$imported_orders++; // save number of imported records
			importing_data("orders", $imported_orders, $total_orders); // output importings results to the page
			$old_order_id = $dbi->f("orderid");
			//$old_user_id = $dbi->f("customers_id");
			$login = $dbi->f("login");
			$firstname = $dbi->f("firstname");
			$lastname = $dbi->f("lastname");
			$name = $firstname." ".$lastname;
			
			$company_name = $dbi->f("company");
			$address1 = $dbi->f("b_address");
			$province = $dbi->f("b_county");
			$zip = $dbi->f("b_zipcode");
			$city = $dbi->f("b_city");
			$country_code = $dbi->f("b_country");
			$state_code = $dbi->f("b_state");
			$phone = $dbi->f("phone");
			$fax = $dbi->f("fax");
			$email = $dbi->f("email");
			
			$delivery_firstname = (strlen($dbi->f("s_firstname"))>0) ? $dbi->f("s_firstname") : $firstname;
			$delivery_lastname = (strlen($dbi->f("s_lastname"))>0) ? $dbi->f("s_lastname") : $lastname;
			$delivery_name = $delivery_firstname." ".$delivery_lastname;
			$delivery_address1 = $dbi->f("s_address");
			$delivery_province = $dbi->f("s_county");
			$delivery_zip = $dbi->f("s_zipcode");
			$delivery_city = $dbi->f("s_city");
			$delivery_country_code = $dbi->f("s_country");
			$delivery_state_code = $dbi->f("s_state");
			
			$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_code=".$db->tosql($state_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$state_id = $db->f("state_id");
			
			$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_code=".$db->tosql($delivery_state_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$delivery_state_id = $db->f("state_id");
			
			$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_code=".$db->tosql($country_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$country_id = $db->f("country_id");
			
			$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_code=".$db->tosql($delivery_country_code,TEXT);
			$db->query($sql);
			$db->next_record();
			$delivery_country_id = $db->f("country_id");
			
			$sql = "SELECT user_id FROM ".$table_prefix."users WHERE login=".$db->tosql($login,TEXT);
			$db->query($sql);
			$db->next_record();
			$user_id = $db->f("user_id");
			
			$order_total = $dbi->f("total");
			
			$coupon_code = $dbi->f("coupon");
			$coupon_discount = $dbi->f("coupon_discount");
			$coupon_id = array_search($coupon_code,$coupon2coupon);
			if (!$coupon_id) $coupon_id = 0;
			
			$payment_method = $dbi->f("payment_method");
			if ($payment_method == "Credit Card") $payment_id=1;
			else $payment_id=0;
			$shipping_method = $dbi->f("shipping");
			$shipping_cost = $dbi->f("shipping_cost");
			$date_purchased = date("Y-m-d H:i:s", $dbi->f("date"));
			$order_status = 1;
						
			$sql  = " INSERT INTO ".$table_prefix."orders (order_id,site_id,user_id,user_type_id,affiliate_code,default_currency_code,currency_code,order_status,";
			$sql .= " name,first_name, last_name,company_name,email,address1,city,province,state_id,state_code,zip,country_id,country_code,phone,fax,";
			$sql .= " delivery_name,delivery_first_name,delivery_last_name,delivery_address1,delivery_city,delivery_province,delivery_state_id,delivery_state_code,delivery_zip,delivery_country_id,delivery_country_code,";
			$sql .= " order_placed_date,shipping_cost,shipping_type_desc,payment_id, coupons_ids,order_total) VALUES (NULL,1,";
			$sql .= $db->tosql($user_id,INTEGER).",1";
			$sql .= ",'','GBP','GBP'";
			$sql .= ",".$db->tosql($order_status,INTEGER);
			$sql .= ",".$db->tosql($name,TEXT);
			$sql .= ",".$db->tosql($firstname,TEXT);
			$sql .= ",".$db->tosql($lastname,TEXT);
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
			$sql .= ",".$db->tosql($delivery_name,TEXT);
			$sql .= ",".$db->tosql($delivery_firstname,TEXT);
			$sql .= ",".$db->tosql($delivery_lastname,TEXT);
			$sql .= ",".$db->tosql($delivery_address1,TEXT);
			$sql .= ",".$db->tosql($delivery_city,TEXT);
			$sql .= ",".$db->tosql($delivery_province,TEXT);
			$sql .= ",".$db->tosql($delivery_state_id,TEXT);
			$sql .= ",".$db->tosql($delivery_state_code,TEXT);
			$sql .= ",".$db->tosql($delivery_zip,TEXT);
			$sql .= ",".$db->tosql($delivery_country_id,TEXT);
			$sql .= ",".$db->tosql($delivery_country_code,TEXT);
			$sql .= ",'".$date_purchased."'";
			$sql .= ",".$db->tosql($shipping_cost,FLOAT);
			$sql .= ",".$db->tosql($shipping_method,TEXT);
			$sql .= ",".$db->tosql($payment_id,INTEGER);
			$sql .= ",".$db->tosql($coupon_id,TEXT);
			$sql .= ",".$db->tosql($order_total,FLOAT).")";
			$db->query($sql);
			
			$db->query("SELECT MAX(order_id) as order_id FROM ".$table_prefix."orders");
			$db->next_record();
			$order_id = $db->f("order_id");
			$order2order[$old_order_id] = $order_id;
			
			$sql  = "INSERT INTO ".$table_prefix."orders_coupons VALUES (NULL";
			$sql .= ",".$db->tosql($order_id,INTEGER); 
			$sql .= ",".$db->tosql($coupon_id,INTEGER); 
			$sql .= ",".$db->tosql($coupon_code,TEXT); 
			$sql .= ",".$db->tosql($coupon_code,TEXT); 
			$sql .= ",".$db->tosql($coupon_discount,FLOAT).",'0.00')"; 
			$db->query($sql);
			
			//import orders items
			$oi2oi = array();
			$sqli2 = "SELECT * FROM ".$table_prefix."order_details WHERE orderid=".$db->tosql($old_order_id,INTEGER);
			$dbi2->query($sqli2);
			if ($dbi2->next_record()) {
				do 
				{
					$old_order_id = $dbi2->f("orderid");
					$old_item_id = $dbi2->f("productid");
					$item_code = $dbi2->f("productcode");
					$item_name = $dbi2->f("product");
					$item_price = $dbi2->f("price");
					$item_quantity = $dbi2->f("amount");
					$item_options = $dbi2->f("product_options");
					$property = explode(":",$item_options);
						
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
					//$oi2oi[$old_oi_id] = $order_item_id;
					
					$sql  = " INSERT INTO ".$table_prefix."orders_items_properties ";
					$sql .= " (item_property_id,order_id,order_item_id,property_name,property_value)";
					$sql .= " VALUES (NULL ";
					$sql .= ",".$db->tosql($order_id,INTEGER);
					$sql .= ",".$db->tosql($order_item_id,INTEGER);
					$sql .= ",".$db->tosql(trim($property[0]),TEXT);
					$sql .= ",".$db->tosql(trim($property[1]),TEXT).")";
					$db->query($sql);					
				} while($dbi2->next_record());
			}
					
		} while($dbi2->next_record());
	}
?>