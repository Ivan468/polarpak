<?php
function admin_users_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type;
	
	$t->set_file("block_body", "admin_block_users.html");
	$t->set_var("admin_users_href",      "admin_users.php");
	$t->set_var("admin_user_href",       "admin_user.php");
	$t->set_var("admin_user_login_href", "admin_user_login.php");	
	
	$permissions = get_permissions();
	if (!get_permissions($permissions, "site_users", 0)) return;
	
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	$t->set_var("s", $s);
	if ($s) {
		$t->parse("s_title", false);
	}
	
	// build sqls
	$where    = "";
	if ($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$sa[$si] = str_replace("%","\%",$sa[$si]);
			$where .= " AND (u.email LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR u.login      LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR u.name       LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR u.first_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR u.last_name  LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			if (sizeof($sa) == 1 && preg_match("/^\d+$/", $sa[0])) {
				$where .= " OR u.user_id =" . $db->tosql($sa[0], INTEGER);
			}
			$where .= ")";
		}
	}
	
	// select count
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users u";
	$sql .= " WHERE 1=1 " . $where;
	$total_records = get_db_value($sql);
	
	if(!$total_records) return;
	$t->set_var("total_records", $total_records);
	
	// display items 
	$sql  = " SELECT u.user_id, u.login, u.name, u.first_name, u.last_name, u.email, u.is_approved, ut.type_name";
	$sql .= " FROM (" . $table_prefix . "users u ";
	$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON u.user_type_id=ut.type_id) ";
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " ORDER BY u.user_id ";
	$db->RecordsPerPage = isset($params['records_per_page']) ? $params['records_per_page'] : 5;
	$db->query($sql);
	$item_index = 1;
	$t->set_var("items_list", "");
	while ($db->next_record()) {
		$item_index++;
		$t->set_var("user_id",    $db->f("user_id"));	
		
		$user_names = array();
		$user_names[] = $db->f("login");	
		$user_names[] = $db->f("name");
		$user_names[] = $db->f("first_name") . " " . $db->f("last_name");
		$user_names = array_unique($user_names);
		$user_name = "";
		foreach ($user_names AS $tmp) {
			if (!trim($tmp)) continue;
			if ($user_name) $user_name .= " / ";
			$user_name .= $tmp;
		}		
		$title = htmlspecialchars($user_name);
		if (is_array($sa)) {
			for ($si = 0; $si < sizeof($sa); $si++) {
				$regexp = "";
				for ($si = 0; $si < sizeof($sa); $si++) {
					if (strlen($regexp)) $regexp .= "|";
					$regexp .= htmlspecialchars(str_replace(
					array( "/", "|",  "$", "^", "?", ".", "{", "}", "[", "]", "(", ")", "*"),
					array("\/","\|","\\$","\^","\?","\.","\{","\}","\[","\]","\(","\)","\*"),$sa[$si]));
				}
				if (strlen($regexp)) {
					$title = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $title);
				}
			}
		}
		$t->set_var("title",  $title);	
		
		$t->set_var("email", $db->f("email"));
		$t->set_var("is_approved", $db->f("is_approved") ? YES_MSG : NO_MSG);		
		$t->set_var("type_name", $db->f("type_name"));
		
		$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
		$t->set_var("row_style", $row_style);
		$t->parse("items_list");
	}
	
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
?>