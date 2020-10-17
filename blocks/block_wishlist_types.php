<?php

	if (!isset($saved_types_parsed)) { $saved_types_parsed = false; } 
	$saved_types_hidden = 1; $total_types = 0;
	if (!$saved_types_parsed) {
		$t->set_file("hidden_block", "popup_wishlist_types.html");
		$t->set_var("saved_types_options", "");
		$t->set_var("saved_types_descs", "");
		$t->set_var("saved_type_info", "");
		$t->set_var("saved_types_selection", "");
		$t->set_var("type_id", 0); // set default to zero if no types available

		// check saved types
		$sql  = " SELECT * FROM " .$table_prefix . "saved_types ";
		$sql .= " WHERE is_active=1 ";
		$db->query($sql);
		if ($db->next_record()) {
			$active_type_id = $db->f("type_id");
			do {
				$total_types++;
				$type_id = $db->f("type_id");
				$type_name = get_translation($db->f("type_name"));
				$type_desc = get_translation($db->f("type_desc"));
				$t->set_var("type_id", $type_id);
				$t->set_var("type_name", htmlspecialchars($type_name));
				$t->set_var("type_desc", $type_desc);
				if ($active_type_id == $type_id) {
					$active_type_id = $db->f("type_id");
					$t->set_var("type_id_selected", "selected");
					$t->set_var("type_desc_style", "display: block;");
				} else {
					$t->set_var("type_id_selected", "");
					$t->set_var("type_desc_style", "display: none;");
				}
				if ($total_types > 1 || $type_desc) {
					$saved_types_hidden = 0;
				}
	
				$t->parse("saved_types_options", true);
				$t->parse("saved_types_descs", true);
				
			} while ($db->next_record());
			$t->set_var("prev_type_id", $active_type_id);
		}

		if ($total_types > 1) {
			$t->parse("saved_types_selection", false);
		} else {
			$t->parse("saved_type_info", false);
		}
	
		$t->set_var("saved_types_total", $total_types);
		$t->set_var("saved_types_hidden", $saved_types_hidden);
		$t->parse_to("hidden_block", "hidden_blocks", true);
		$saved_types_parsed = true; // parse this block only once per page
	}

