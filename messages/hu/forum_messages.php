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
	//támogatás üzenetek
	"FORUM_TITLE" => "Fórum",
	"TOPIC_INFO_TITLE" => "Téma Információ",
	"TOPIC_MESSAGE_TITLE" => "Üzenet",

	"MY_FORUM_TOPICS_MSG" => "Fórum témáim",
	"ALL_FORUM_TOPICS_MSG" => "Összes Fórum Téma",
	"MY_FORUM_TOPICS_DESC" => "Mindig csodálkoztál ha  egy problémára megoldást keresve rájöttél , hogy másnál is előfordult ez a probléma , és tapasztalata segített neked? Szeretnéd megosztani tudásod az új felhasználókkal? Miért ne lennél egy fórum felhasználó becsatlakozva a közösségbe?",
	"NEW_TOPIC_MSG" => "Új Téma",
	"NO_TOPICS_MSG" => "Téma nem található.",
	"FOUND_TOPICS_MSG" => " <b>{found_records}</b> témát találtunk, ami megfelel a '<b>{search_string}</b>' keresésnek.",
	"NO_FORUMS_MSG" => "Fórum nem található",

	"FORUM_NAME_COLUMN" => "Fórum",
	"FORUM_TOPICS_COLUMN" => "Témák",
	"FORUM_REPLIES_COLUMN" => "Válaszok",
	"FORUM_LAST_POST_COLUMN" => "Utoljára frissítve",
	"FORUM_MODERATORS_MSG" => "Moderátorok",

	"TOPIC_NAME_COLUMN" => "Téma",
	"TOPIC_AUTHOR_COLUMN" => "Szerző",
	"TOPIC_VIEWS_COLUMN" => "Megtekintve",
	"TOPIC_REPLIES_COLUMN" => "Válaszok",
	"TOPIC_UPDATED_COLUMN" => "Utoljára frissítve",
	"TOPIC_ADDED_MSG" => "Köszönjük  <br> a témád elkészült",

	"TOPIC_ADDED_BY_FIELD" => "Hozzáadta",
	"TOPIC_ADDED_DATE_FIELD" => "Hozzáadva",
	"TOPIC_UPDATED_FIELD" => "Utoljára frissítve",
	"TOPIC_NICKNAME_FIELD" => "Becenév",
	"TOPIC_EMAIL_FIELD" => "Email címed",
	"TOPIC_NAME_FIELD" => "Téma",
	"TOPIC_MESSAGE_FIELD" => "Üzenet",
	"TOPIC_NOTIFY_FIELD" => "Küld el mindegyik választ az email címemre",

	"ADD_TOPIC_BUTTON" => "Téma hozzáadása",
	"TOPIC_MESSAGE_BUTTON" => "Üzenet hozzáadása",

	"TOPIC_MISS_ID_ERROR" => "Hiányzó <b>Thread ID</b> paraméter.",
	"TOPIC_WRONG_ID_ERROR" => "<b>Thread ID</b> paraméter értéke nem megfelelő.",
	"FORUM_SEARCH_MESSAGE" => " {search_count} üzenetet van ami megfelel a  '{search_string}' keresési feltételnek.",
	"TOPIC_PREVIEW_BUTTON" => "Előzetes",
	"TOPIC_SAVE_BUTTON" => "Mentés",

	"LAST_POST_ON_SHORT_MSG" => "Idő:",
	"LAST_POST_IN_SHORT_MSG" => "Téma:",
	"LAST_POST_BY_SHORT_MSG" => "Szerző:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "Utolsó hozzászólás:",

);
$va_messages = array_merge($va_messages, $messages);
