<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  forum_messages.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// forum messages
	"FORUM_TITLE" => "Forum",
	"TOPIC_INFO_TITLE" => "Πληροφορίες θέματος",
	"TOPIC_MESSAGE_TITLE" => "Μήνυμα",

	"MY_FORUM_TOPICS_MSG" => "Τα δικά μου θέματα",
	"ALL_FORUM_TOPICS_MSG" => "Όλα τα θέματα",
	"MY_FORUM_TOPICS_DESC" => "Έχετε αναρωτηθεί εάν το πρόβλημα που έχετε είναι ένα που κάποιος άλλος έχει περάσει; Θα επιθυμούσατε να μοιραστείτε την εμπειρία σας με τους νέους χρήστες; Γιατί δεν γίνεστε χρήστης φόρουμ και δεν γίνεστε ένα μέρος της κοινότητας;",
	"NEW_TOPIC_MSG" => "Νέο θέμα",
	"NO_TOPICS_MSG" => "Δεν βρέθηκαν θέματα",
	"FOUND_TOPICS_MSG" => "βρέθηκαν <b>{found_records}</b> θέματα με αυτούς τους όρους '<b>{search_string}</b>'",
	"NO_FORUMS_MSG" => "No forums found",

	"FORUM_NAME_COLUMN" => "Forum",
	"FORUM_TOPICS_COLUMN" => "θέμα",
	"FORUM_REPLIES_COLUMN" => "Απαντήσεις",
	"FORUM_LAST_POST_COLUMN" => "Τελευταία ανανέωση",
	"FORUM_MODERATORS_MSG" => "Moderators",

	"TOPIC_NAME_COLUMN" => "θέμα",
	"TOPIC_AUTHOR_COLUMN" => "Συγγραφέας",
	"TOPIC_VIEWS_COLUMN" => "Το είδαν",
	"TOPIC_REPLIES_COLUMN" => "Απαντήσεις",
	"TOPIC_UPDATED_COLUMN" => "Τελευταία ανανέωση",
	"TOPIC_ADDED_MSG" => "ευχαριστούμε το άρθρο σας Έχει προστεθεί",

	"TOPIC_ADDED_BY_FIELD" => "Προστέθηκε από",
	"TOPIC_ADDED_DATE_FIELD" => "Προστέθηκε από",
	"TOPIC_UPDATED_FIELD" => "Τελευταία ανανέωση",
	"TOPIC_NICKNAME_FIELD" => "Ψευδώνυμο",
	"TOPIC_EMAIL_FIELD" => "Το e-mail σας",
	"TOPIC_NAME_FIELD" => "θέμα",
	"TOPIC_MESSAGE_FIELD" => "Μήνυμα",
	"TOPIC_NOTIFY_FIELD" => "Στείλε όλες τις απαντήσεις στο ηλεκτρονικό ταχυδρομείο μου",

	"ADD_TOPIC_BUTTON" => "Πρόσθεσε θέμα ",
	"TOPIC_MESSAGE_BUTTON" => "Πρόσθεσε Μήνυμα",

	"TOPIC_MISS_ID_ERROR" => "Λείπει το <b>ID</b>",
	"TOPIC_WRONG_ID_ERROR" => "Το ID Έχει <b>Λάθος</b> παράμετρο",
	"FORUM_SEARCH_MESSAGE" => "We've found {search_count} messages matching the term(s) '{search_string}'",
	"TOPIC_PREVIEW_BUTTON" => "Preview",
	"TOPIC_SAVE_BUTTON" => "Save",

	"LAST_POST_ON_SHORT_MSG" => "On:",
	"LAST_POST_IN_SHORT_MSG" => "In:",
	"LAST_POST_BY_SHORT_MSG" => "By:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "Last modified:",

);
$va_messages = array_merge($va_messages, $messages);
