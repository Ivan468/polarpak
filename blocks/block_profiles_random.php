<?php

	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$default_title = "Random {PROFILES_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_profiles_random.html"); 
  $t->set_file("block_body", $html_template);
		
	// get global ads settings if they weren't set
	if (!isset($profiles_settings)) { $profiles_settings = get_settings("profiles"); }
	
	$profile_type_id = get_param("profile_type_id");
	$looking_type_id = get_param("looking_type_id");
	$country_id = get_param("country_id");
	$state_id = get_param("state_id");
	$city = get_param("city");
	$postal_code = get_param("postal_code");
	$page = get_param("page");
	$current_date = va_time();
	$current_ts = va_timestamp();
	$default_photo = get_setting_value($profiles_settings, "photo_small_default", "images/no_photo.gif");

	$pass_parameters = array(
		"profile_type_id",
		"looking_type_id",
		"country_id",
		"state_id",
		"city",
		"postal_code",
	);

	$t->set_var("profiles_list_href", "profiles_list.php");
	$t->set_var("profiles_details_href", "profiles_details.php");

	$sql_where  = "";
	$sql_where .= " WHERE p.is_approved=1 AND p.is_shown=1   ";
	$sql_where .= " AND p.photo_id IS NOT NULL ";
	// inverse profile and looking type fields
	//$sql_where .= " AND p.profile_type_id=" .$db->tosql($looking_type_id, INTEGER);
	//$sql_where .= " AND p.looking_type_id=" .$db->tosql($profile_type_id, INTEGER);
	// location criterions
	//$sql_where .= " AND p.country_id= " . $db->tosql($country_id, INTEGER);
	if (strlen($state_id)) {
		//$sql_where .= " AND p.state_id= " . $db->tosql($state_id, INTEGER);
	}
	if (strlen($city)) {
		//$sql_where .= " AND p.city= " . $db->tosql($city, TEXT);
	}
	if (strlen($postal_code)) {
		//$sql_where .= " AND p.postal_code LIKE '" . $db->tosql($postal_code, TEXT, false) . "%' ";
	}

	$recs = get_setting_value($vars, "recs", 5);
	$db->RecordsPerPage = $recs;
	$db->PageNumber = 1;
			
	$sql  = " SELECT p.profile_id, p.profile_name, p.birth_date, p.city, p.profile_info, ";
	$sql .= " p.photo_id, up.tiny_photo, up.small_photo, up.large_photo, ";
	$sql .= " c.country_name,s.state_name ";
	$sql .= " FROM " . $table_prefix . "profiles p ";
	$sql .= " LEFT JOIN " . $table_prefix . "users_photos up ON up.photo_id=p.photo_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=p.country_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=p.state_id ";
	$sql .= $sql_where;
	$sql .= " ORDER BY RAND() ";
	
	$db->query($sql);
	if ($db->next_record()) {
		$cols = get_setting_value($vars, "cols", 1);
		$t->set_var("item_column", (100 / $cols) . "%");
		$t->set_var("total_columns", $cols);
		$item_number = 0;
		do
		{
			$item_number++;
			$profile_id = $db->f("profile_id");
			$birth_date = $db->f("birth_date", DATETIME);
			$birth_date_ts = va_timestamp($birth_date);
			$age = $current_date[YEAR] - $birth_date[YEAR];
			if ($birth_date[MONTH] < $current_date[MONTH] || ($birth_date[MONTH] == $current_date[MONTH] && $birth_date[DAY] < $current_date[DAY])) {
				$age--;
			}

			$user_id = $db->f("user_id");
			$profile_name = $db->f("profile_name");
			$name = $db->f("profile_name");
			$country_name = $db->f("country_name");
			$state_name = $db->f("state_name");
			$city = $db->f("city");
			$personal_info = $db->f("profile_info");
			$profile_info = $db->f("profile_info");
	
			$t->set_var("profile_id", $profile_id);
			$t->set_var("name", htmlspecialchars($name));
			$t->set_var("profile_name", htmlspecialchars($profile_name));
			$t->set_var("age", $age);

			$t->set_var("country_name", htmlspecialchars($country_name));

			$t->set_var("state_block", "");
			if ($state_name) {
				$t->set_var("state_name", htmlspecialchars($state_name));
				$t->sparse("state_block", false);
			}
			$t->set_var("city_block", "");
			if ($city) {
				$t->set_var("city", htmlspecialchars($city));
				$t->sparse("city_block", false);
			}


			$t->set_var("profiles_view_url", "profiles_view.php?pid=". $profile_id);
	
			$photo_id = $db->f("photo_id");
			$tiny_photo = $db->f("tiny_photo");
			$small_photo = $db->f("small_photo");
			$large_photo = $db->f("large_photo");

			if (!strlen($small_photo) || !image_exists($small_photo)) {
				$image_exists = false;
				$small_photo = $default_photo;
			} else {
				$image_exists = true;
			}
			if ($small_photo) {
				$image_size = @GetImageSize($small_photo);
				if ($image_exists) {
					$photo_vc = md5($small_photo);
					$small_photo = "photo.php?id=".urlencode($photo_id)."&type=small&vc=".urlencode($photo_vc);
				}

     		$t->set_var("alt", htmlspecialchars($name));
     		$t->set_var("src", htmlspecialchars($small_photo));
				if(is_array($image_size)) {
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				} else {
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
				$t->parse("small_image", false);
			} else {
				$t->set_var("small_image", "");
			}
			
			$t->parse("profiles_cols");
			$is_next_record = $db->next_record();
			if($item_number % $cols == 0)
			{
				$t->parse("profiles_rows");
				$t->set_var("profiles_cols", "");
			}
		} while ($is_next_record);              	
	
		if ($item_number % $cols != 0) {
			$t->parse("profiles_rows");	
		}
	
		$block_parsed = true;
	}
	


?>