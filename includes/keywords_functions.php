<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  keywords_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function keywords_fields($type = "items")
	{
		global $settings, $articles_settings;
		if ($type == "articles") {
			$keywords_settings = $articles_settings;
			$fields = array(
				"article_title" => 1,
				"author_name" => 2,
				"album_name" => 3,
				"short_description" => 4,
				"full_description" => 5,
				"highlights" => 6,
				"hot_description" => 7,
				"notes" => 8,
				"meta_title" => 9,
				"meta_description" => 10,
				"meta_keywords" => 11,
				"tag_name" => 12,
			);
		} else {
			$keywords_settings = $settings;
			$fields = array(
				"item_name" => 1,
				"item_code" => 2,
				"manufacturer_code" => 3,
				"short_description" => 4,
				"full_description" => 5,
				"highlights" => 6,
				"special_offer" => 7,
				"notes" => 8,
				"meta_title" => 9,
				"meta_description" => 10,
				"meta_keywords" => 11,
			);
		}
		$keywords_fields = array();
		foreach($fields as $field_name => $field_id) {
			$field_index = get_setting_value($keywords_settings, $field_name."_index", 0);
			if ($field_index) {
				$rank = get_setting_value($keywords_settings, $field_name."_rank", 0);
				$type = get_setting_value($keywords_settings, $field_name."_type", 1);
				$keywords_fields[$field_name] = array(
					"id" => $field_id, "rank" => $rank, "type" => $type,
				);
			}
		}
		return $keywords_fields;
	}

	function generate_keywords($data, $type = "items")
	{
		global $table_prefix, $db, $keywords_fields;

		// get PK id field: item_id / article_id
		if ($type == "articles") {
			$pk_id = $data["article_id"];
		} else {
			$pk_id = $data["item_id"];
		}

		// prepare keywords
		if (!is_array($keywords_fields)) {
			$keywords_fields = keywords_fields($type);
		}
		$keywords = array();
		foreach($keywords_fields as $field_name => $field_data) {
			$field_keywords = array();
			$field_value = $data[$field_name];
			$field_id = $field_data["id"];
			$field_rank = $field_data["rank"];
			$field_type = $field_data["type"];

			// before strip tags add space before tags
			$field_value = str_replace("<", " <", $field_value);
			$field_value = strip_tags($field_value);
			// strip language tags
			$field_value = preg_replace("/\[\/[a-z]{2}\]/", " ", $field_value);
			// strip all non-word characters
			$field_value = preg_replace(KEYWORD_REPLACE_REGEXP, " ", $field_value);
			$field_value = str_replace("_", " ", $field_value);

			// get all words
			$words = explode(" ", $field_value);
			// remove empty values from array
			foreach ($words as $id => $word) {
				if(function_exists("mb_strtolower")) {
					$word = mb_strtolower($word, "UTF-8");
				} else {
					$word = strtolower($word);
				}
				$word = trim($word, "'");
				if (strlen($word)) {
					$words[$id] = $word;
				} else {
					unset($words[$id]);
				}
			}
			// calculate words rank if there are any available
			$words_num = sizeof($words);
			if ($words_num > 0) {
				if ($field_type == 2) {
					$max_rank = $field_rank;
					$field_rank = ceil($max_rank / $words_num);
				} else {
					$max_rank = $field_rank * $words_num;
				}
				// prepare word for adding to DB
				$pos = 0;
				foreach ($words as $id => $word) {
					$pos++;
					// calculate keyword rank
					$word_rank = $field_rank;
					if ($word_rank > $max_rank) { $word_rank = $max_rank; }
					$max_rank -= $word_rank;
					// add keyword to array
					$field_keywords[] = array(
						"word" => $word,
						"pos" => $pos,
						"rank" => $word_rank,
						"field_id" => $field_id,
					);
				}
			}

			// added all keywords to one array
			$keywords = array_merge ($keywords, $field_keywords); 
		}

		// check keywords if they already exists in DB or should be added
		check_keywords($keywords, $type);
		// add keywords for products
		add_keywords($pk_id, $keywords, $type);
	}

	function check_keywords(&$keywords, $type = "items")
	{
		global $table_prefix, $db;

		//$keywords_ids = array();
		foreach($keywords as $key => $keyword_info) {
			$word = $keyword_info["word"];
			$sql  = " SELECT keyword_id FROM " . $table_prefix . "keywords ";
			$sql .= " WHERE keyword_name=" . $db->tosql($word, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$keyword_id = $db->f("keyword_id");
			} else {
				if ($db->DBType == "postgre") {
					$keyword_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "keywords') ");
					$sql  = " INSERT INTO " . $table_prefix . "keywords (keyword_id, keyword_name) VALUES (";
					$sql .= $db->tosql($keyword_id, INTEGER) . ", ";
					$sql .= $db->tosql($word, TEXT) . ")";
					$db->query($sql);
				} else {
					$sql  = " INSERT INTO " . $table_prefix . "keywords (keyword_name) VALUES (";
					$sql .= $db->tosql($word, TEXT) . ")";
					$db->query($sql);
					if ($db->DBType == "mysql") {
						$sql = " SELECT LAST_INSERT_ID() ";
					} else {
						$sql = " SELECT MAX(keyword_id) FROM ".$table_prefix."keywords ";
					}
					$keyword_id = get_db_value($sql);
				}
			}
			$keywords[$key]["id"] = $keyword_id;
		}
	}

	function add_keywords($pk_id, $keywords, $type = "items")
	{
		global $db, $table_prefix;

		if ($type == "articles") {
			$sql  = " DELETE FROM " . $table_prefix . "keywords_articles ";
			$sql .= " WHERE article_id=" . $db->tosql($pk_id, INTEGER);
			$db->query($sql);
	  
			foreach($keywords as $key => $keyword_info) {
				$sql  = " INSERT INTO " . $table_prefix . "keywords_articles ";
				$sql .= " (article_id, keyword_id, field_id, keyword_position, keyword_rank) VALUES (";
				$sql .= $db->tosql($pk_id, INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["id"], INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["field_id"], INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["pos"], INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["rank"], INTEGER) . ") ";
				$db->query($sql);
			}
	  
			$sql  = " UPDATE ".$table_prefix."articles SET is_keywords=1 ";
			$sql .= " WHERE article_id=".$db->tosql($pk_id, INTEGER);
			$db->query($sql);
		} else {
			$sql  = " DELETE FROM " . $table_prefix . "keywords_items ";
			$sql .= " WHERE item_id=" . $db->tosql($pk_id, INTEGER);
			$db->query($sql);
	  
			foreach($keywords as $key => $keyword_info) {
				$sql  = " INSERT INTO " . $table_prefix . "keywords_items ";
				$sql .= " (item_id, keyword_id, field_id, keyword_position, keyword_rank) VALUES (";
				$sql .= $db->tosql($pk_id, INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["id"], INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["field_id"], INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["pos"], INTEGER) . ", ";
				$sql .= $db->tosql($keyword_info["rank"], INTEGER) . ") ";
				$db->query($sql);
			}
	  
			$sql  = " UPDATE ".$table_prefix."items SET is_keywords=1 ";
			$sql .= " WHERE item_id=".$db->tosql($pk_id, INTEGER);
			$db->query($sql);
		}
	}

?>