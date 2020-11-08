<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forum_messages.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// forum messages
	"FORUM_TITLE" => "פורום",
	"TOPIC_INFO_TITLE" => "מידע נושא",
	"TOPIC_MESSAGE_TITLE" => "הודעה",

	"MY_FORUM_TOPICS_MSG" => "נושאי הפורום שלי",
	"ALL_FORUM_TOPICS_MSG" => "כל נושאי הפורום",
	"MY_FORUM_TOPICS_DESC" => "האם שאלת את עצמך אם הבעיות שנתקלת בהם, גם אחרים נתקלו בהם?, האם ברצונך לחלוק את המומחיות של עם אחרים?, מדוע שלא תצטרף לפורום ולקהילה.",
	"NEW_TOPIC_MSG" => "נושא חדש",
	"NO_TOPICS_MSG" => "לא נמצאו נושאים",
	"FOUND_TOPICS_MSG" => "נמצאו <b>{found_records}</b> רשומות התואמות ל- '<b>{search_string}</b>'",
	"NO_FORUMS_MSG" => "לא נמצאו רשומות",

	"FORUM_NAME_COLUMN" => "פורום",
	"FORUM_TOPICS_COLUMN" => "נושאים",
	"FORUM_REPLIES_COLUMN" => "תשובות",
	"FORUM_LAST_POST_COLUMN" => "עדכון אחרון",
	"FORUM_MODERATORS_MSG" => "מתווכים",

	"TOPIC_NAME_COLUMN" => "נושא",
	"TOPIC_AUTHOR_COLUMN" => "מחבר",
	"TOPIC_VIEWS_COLUMN" => "דעות",
	"TOPIC_REPLIES_COLUMN" => "תשובות",
	"TOPIC_UPDATED_COLUMN" => "Last updated",
	"TOPIC_ADDED_MSG" => "תודה<br>הנושא שלך הוסף",

	"TOPIC_ADDED_BY_FIELD" => "הוסף ע\"י",
	"TOPIC_ADDED_DATE_FIELD" => "הוסף ע\"י",
	"TOPIC_UPDATED_FIELD" => "עדכון אחרון",
	"TOPIC_NICKNAME_FIELD" => "שם חיבה",
	"TOPIC_EMAIL_FIELD" => "כתובת המייל שלך",
	"TOPIC_NAME_FIELD" => "נושא",
	"TOPIC_MESSAGE_FIELD" => "הודעה",
	"TOPIC_NOTIFY_FIELD" => "שלח את כל התגובות באי-מייל",

	"ADD_TOPIC_BUTTON" => "הוסף נושא",
	"TOPIC_MESSAGE_BUTTON" => "הוסיף הודעה",

	"TOPIC_MISS_ID_ERROR" => "חסרים <b>Thread ID</b> ערכים",
	"TOPIC_WRONG_ID_ERROR" => "<b>Thread ID</b> ערכים עם ערך שגוי",
	"FORUM_SEARCH_MESSAGE" => "נמצאו {search_count} הודעות התואמות ל- '{search_string}'",
	"TOPIC_PREVIEW_BUTTON" => "צפיה מראש",
	"TOPIC_SAVE_BUTTON" => "שמור",

	"LAST_POST_ON_SHORT_MSG" => "על:",
	"LAST_POST_IN_SHORT_MSG" => "ב:",
	"LAST_POST_BY_SHORT_MSG" => "על ידי:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "שוני אחרון:",

);
$va_messages = array_merge($va_messages, $messages);
