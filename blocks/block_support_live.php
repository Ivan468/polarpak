<?php

	include_once("./includes/record.php");
	include_once("./includes/support_functions.php");
	include_once("./messages/".$language_code."/support_messages.php");

	$default_title = "{SUPPORT_LIVE_MSG}";

	set_script_tag("js/chat.js");

	$html_template = get_setting_value($block, "html_template", "block_support_live.html"); 
  $t->set_file("block_body", $html_template);
	$errors = false;


	$admin_online_ts = va_timestamp() - 20;
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "admins ";
	$sql .= " WHERE support_online_date>=" . $db->tosql($admin_online_ts, DATETIME);
	$admins_online = get_db_value($sql);	

	if ($admins_online) {
		$t->set_var("support_onclick", "openPopup('support_chat.php', 600, 400);");
		$t->set_var("online_offline_class", "support-online");
		$t->set_var("support_offline", "");
		$t->parse("support_online", false);
	} else {
		$t->set_var("support_onclick", "window.location = 'support.php';");
		$t->set_var("online_offline_class", "support-offline");
		$t->set_var("support_online", "");
		$t->parse("support_offline", false);
	}

	if(!$layout_type) { $layout_type = "no"; }
	$block_parsed = true;

?>