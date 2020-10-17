<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  newsletter_functions.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function count_newsletter_emails($newsletter_id = "") 
	{
		global $r, $db, $table_prefix;

		if (is_array($newsletter_id) || !strlen($newsletter_id)) { $newsletter_id = get_param("newsletter_id"); }

		if (strlen($newsletter_id)) {
			// count emails
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$emails_total = get_db_value($sql);

			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$sql .= " AND is_sent=1 " ;
			$emails_sent = get_db_value($sql);
			$emails_left = $emails_total - $emails_sent;
	
			// update table with emails qty
			$sql  = " UPDATE " . $table_prefix . "newsletters ";
			$sql .= " SET emails_total=".$db->tosql($emails_total, INTEGER);
			$sql .= " , emails_sent=" . $db->tosql($emails_sent, INTEGER);
			$sql .= " , emails_left=" . $db->tosql($emails_left, INTEGER);
			$sql .= " , is_prepared=1 ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$db->query($sql);
		}
	}


?>