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
	"TOPIC_INFO_TITLE" => "Trådinformation",
	"TOPIC_MESSAGE_TITLE" => "Meddelande",

	"MY_FORUM_TOPICS_MSG" => "Mina forumtrådar",
	"ALL_FORUM_TOPICS_MSG" => "Alla forumtrådar",
	"MY_FORUM_TOPICS_DESC" => "Har du någonsin undrat om någon annan har upplevt samma problem som du har? Skulle du vilja berätta om dina erfarenheter för andra användare? Varför inte bli forumanvändare och bli delaktig i webbplatsen?",
	"NEW_TOPIC_MSG" => "Ny tråd",
	"NO_TOPICS_MSG" => "Inga trådar funna",
	"FOUND_TOPICS_MSG" => "Vi har hittat <b>{found_records}</b> forumtrådar som matchar sökterm(erna) '<b>{search_string}</b>'",
	"NO_FORUMS_MSG" => "Inga forum hittades",

	"FORUM_NAME_COLUMN" => "Forum",
	"FORUM_TOPICS_COLUMN" => "Ämne",
	"FORUM_REPLIES_COLUMN" => "Svar",
	"FORUM_LAST_POST_COLUMN" => "Senast uppdaterad",
	"FORUM_MODERATORS_MSG" => "Moderatorer",

	"TOPIC_NAME_COLUMN" => "Ämne",
	"TOPIC_AUTHOR_COLUMN" => "Trådstartare",
	"TOPIC_VIEWS_COLUMN" => "Visad",
	"TOPIC_REPLIES_COLUMN" => "Svar",
	"TOPIC_UPDATED_COLUMN" => "Senast uppdaterad",
	"TOPIC_ADDED_MSG" => "Tack!<br>Din tråd har lagts till",

	"TOPIC_ADDED_BY_FIELD" => "Startad av",
	"TOPIC_ADDED_DATE_FIELD" => "Startad",
	"TOPIC_UPDATED_FIELD" => "Senast uppdaterad",
	"TOPIC_NICKNAME_FIELD" => "Användarnamn",
	"TOPIC_EMAIL_FIELD" => "Din epostadress",
	"TOPIC_NAME_FIELD" => "Ämne",
	"TOPIC_MESSAGE_FIELD" => "Meddelande",
	"TOPIC_NOTIFY_FIELD" => "Skicka alla svar till min epost",

	"ADD_TOPIC_BUTTON" => "Ny tråd",
	"TOPIC_MESSAGE_BUTTON" => "Nytt meddelande",

	"TOPIC_MISS_ID_ERROR" => "Saknar <b>tråd-ID</b> parameter.",
	"TOPIC_WRONG_ID_ERROR" => "<b>Tråd-ID</b> parameter har fel värde.",
	"FORUM_SEARCH_MESSAGE" => "Vi har hittat {search_count} meddelanden som matchar termen '{search_string}'",
	"TOPIC_PREVIEW_BUTTON" => "Förhandsvisa",
	"TOPIC_SAVE_BUTTON" => "Spara",

	"LAST_POST_ON_SHORT_MSG" => "På:",
	"LAST_POST_IN_SHORT_MSG" => "I:",
	"LAST_POST_BY_SHORT_MSG" => "Av:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "Senast ändrad:",

);
$va_messages = array_merge($va_messages, $messages);
