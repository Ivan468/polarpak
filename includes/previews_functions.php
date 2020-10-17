<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  previews_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
	

	class VA_Previews {

		var $file_id;
		var $item_id;
		var $preview_title;
		var $preview_image;
		var $preview_path;
		/**
		 * 0 - not availiable / hidden
		 * 1 - as downloadable
		 * 2 - with player
		 */
		var $preview_type;
		
		/**
		 * 0 - not availiable / hidden
		 * 1 - in separate section
		 * 2 - under large image on product details
		 * 3 - under large image on products list
		 */
		var $preview_position;
		
		var $file_extension;
		var $preview_width;
		var $preview_height;
		var $preview_external;

		public function __construct() {
			$this->preview_width  = 250;
			$this->preview_height = 200;
		}

		function get_sql() {
			global $table_prefix, $db;
			$sql  = " SELECT item_id, file_id, preview_type, preview_title, preview_path, preview_image, preview_position ";
			$sql .= " FROM " . $table_prefix . "items_files ";
			$where = "";
			if (strlen($this->file_id)) {
				if ($where) $where .= " AND ";
				$where .= " file_id=" . $db->tosql($this->file_id, INTEGER);
			}
			if (strlen($this->item_id)) {
				if ($where) $where .= " AND ";
				$where .= " item_id=" . $db->tosql($this->item_id, INTEGER);
			}
			if (is_array($this->preview_type) && count($this->preview_type)) {
				if ($where) $where .= " AND ";
				$where .= " preview_type IN (" . $db->tosql($this->preview_type, INTEGERS_LIST) . ")";			
			} elseif (strlen($this->preview_type)) {
				if ($where) $where .= " AND ";
				$where .= " preview_type=" . $db->tosql($this->preview_type, INTEGER);
			}
			if (strlen($this->preview_title)) {
				if ($where) $where .= " AND ";
				$where .= " preview_title=" . $db->tosql($this->preview_title, TEXT);
			}
			if (strlen($this->preview_path)) {
				if ($where) $where .= " AND ";
				$where .= " preview_path=" . $db->tosql($this->preview_path, TEXT);
			}
			if (strlen($this->preview_image)) {
				if ($where) $where .= " AND ";
				$where .= " preview_image=" . $db->tosql($this->preview_image, TEXT);
			}
			if (strlen($this->preview_position)) {
				if ($where) $where .= " AND ";
				$where .= " preview_position=" . $db->tosql($this->preview_position, INTEGER);
			}
			if (strlen($where)) {
				$sql .= " WHERE " . $where;
			}			
			return $sql;
		}

		function showAll($block_name) {
			global $t, $db;
			$db_rsi = $db->set_rsi("s");

			$t->set_var($block_name, "");
			$db->query($this->get_sql());
			$index = 0;
			while ($db->next_record()) {
				$index ++;

				$item_id = $db->f("item_id");
				$file_id = $db->f("file_id"); 
				$preview_type = $db->f("preview_type");
				$preview_title = get_translation($db->f("preview_title"));
				$preview_path = $db->f("preview_path");
				$preview_image = $db->f("preview_image");
				$preview_position  = $db->f("preview_position");
				if (strlen($preview_title)) { $preview_title = $preview_path; }

				// start on get
				$tmp = explode(".", $preview_path);
				$file_extension = strtolower(array_pop($tmp));	

				$preview_width    = $this->preview_width;
				$preview_height   = $this->preview_height;
				if (!preg_match("/^http(s)?:\/\//", $preview_path)) {
					$preview_size = @getimagesize($preview_path);
					if (is_array($preview_size)) {
						$preview_width  = $preview_size[0];
						$preview_height = $preview_size[1];
					}
					$preview_external = false;
				} else {
					$preview_external = true;
				}
				// end on get

				// __getOne end 

				// start showOne
				$t->set_file("preview_body", "previews.html");
				$previews_type_block = $preview_image ? "preview_simple_link_with_image" : "preview_simple_link";

				if ($preview_type == 2) {
					switch ($file_extension) {
						case "3gp": case "3g2": case "aac": case "m4a": case "flv": case "f4v": case "mp4": case "mov": case "wma": case "swf":
							$previews_type_block = "preview_swf";
						break;
						case "avi":case "mpg": case "mpeg": case "wmv":
							$previews_type_block = "preview_avi";
						break;
						case "mp3": 
							$previews_type_block = "preview_mp3";
						break;					
					}
				}
				
				$t->set_var("file_id",        $file_id);
				$t->set_var("item_id",        $item_id);
				$t->set_var("preview_title",  $preview_title);			
				$t->set_var("preview_path",   $preview_path);
				$t->set_var("preview_image",  $preview_image);
				$t->set_var("preview_width",  $preview_width);	
				$t->set_var("preview_height", $preview_height);	
									
				$t->parse_to($previews_type_block, $block_name, true);		
				// end showOne 
			}

			$db->set_rsi($db_rsi);
			return $index;
		}
	}
