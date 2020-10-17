<?php
function admin_bookmarks_block($block_name) {
	global $t, $db, $table_prefix;
	$t->set_file("block_body", "admin_block_bookmarks.html");
	$t->set_var("admin_bookmarks_href", "admin_bookmarks.php");
	$t->set_var("admin_bookmark_href", "admin_bookmark.php");
	
	$session_admin_id = get_session("session_admin_id");
	$current_version  = va_version();
	
	// bookmarks are available only from version 2.8.1
	if (comp_vers($current_version, "2.8.1") > 1) return;

	$t->set_var("bookmark", "");
	$bookmarks = array();
	$sql  = " SELECT bookmark_id, title, url, notes, is_popup, image_path ";
	$sql .= " FROM " . $table_prefix . "bookmarks ";
	$sql .= " WHERE admin_id = " . $db->tosql($session_admin_id, INTEGER, true, false);
	$sql .= " ORDER BY title ";
	$db->query($sql);
	while ($db->next_record()) {
		$bookmark_id = $db->f("bookmark_id");
		$url         = $db->f("url");
		$title       = $db->f("title");
		$notes       = $db->f("notes");
		$is_popup    = $db->f("is_popup");
		$image_path  = $db->f("image_path");
	
		$t->set_var("header_bookmark_id",    htmlspecialchars($bookmark_id));
		$t->set_var("header_bookmark_url",   htmlspecialchars($url));
		$t->set_var("header_bookmark_title", htmlspecialchars($title));
		$t->set_var("header_bookmark_notes", htmlspecialchars($notes));
	
		if ($image_path) {
			$t->set_var("src", htmlspecialchars($image_path));
		} else {
			$t->set_var("src", "../images/icons/no-img.gif");
		}
	
		if ($is_popup == 1) {
			$t->set_var("target", "_blank");
		} else {
			$t->set_var("target", "_self");
		}
	
		$t->sparse("bookmark", true);
	}
		
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
}
?>