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
	// informacje dot. forum
	"FORUM_TITLE" => "Forum",
	"TOPIC_INFO_TITLE" => "Informacje o temacie",
	"TOPIC_MESSAGE_TITLE" => "Wiadomość",

	"MY_FORUM_TOPICS_MSG" => "Moje tematy na forum",
	"ALL_FORUM_TOPICS_MSG" => "Wszystkie tematy na forum",
	"MY_FORUM_TOPICS_DESC" => "Czy zastanawiałeś się kiedyś nad tym, że problem który chcesz rozwiązać, zdażył się już komuś innemu? Może chcesz podzielić się rozwiązaniem z nowymi użytkownikami? Dlaczego by nie zostać użytkownikiem Forum i dołączyć do społecznośći?",
	"NEW_TOPIC_MSG" => "Nowy temat",
	"NO_TOPICS_MSG" => "Nie znaleziono tematów",
	"FOUND_TOPICS_MSG" => "Znaleźliśmy <b>{found_records}</b> tematy pasujących do szukanego wyrażenia: '<b>{search_string}</b>'",
	"NO_FORUMS_MSG" => "No forums found",

	"FORUM_NAME_COLUMN" => "Forum",
	"FORUM_TOPICS_COLUMN" => "Temat",
	"FORUM_REPLIES_COLUMN" => "Odpowiedzi",
	"FORUM_LAST_POST_COLUMN" => "Ostatni wpis",
	"FORUM_MODERATORS_MSG" => "Moderators",

	"TOPIC_NAME_COLUMN" => "Temat",
	"TOPIC_AUTHOR_COLUMN" => "Autor",
	"TOPIC_VIEWS_COLUMN" => "Oglądany",
	"TOPIC_REPLIES_COLUMN" => "Odpowiedzi",
	"TOPIC_UPDATED_COLUMN" => "Ostatni wpis",
	"TOPIC_ADDED_MSG" => "Dziękujemy<br>Twój temat został dodany",

	"TOPIC_ADDED_BY_FIELD" => "Dodany przez",
	"TOPIC_ADDED_DATE_FIELD" => "Dodany",
	"TOPIC_UPDATED_FIELD" => "Ostatni wpis",
	"TOPIC_NICKNAME_FIELD" => "Użytkownik",
	"TOPIC_EMAIL_FIELD" => "Twój adres email",
	"TOPIC_NAME_FIELD" => "Temat",
	"TOPIC_MESSAGE_FIELD" => "Treść",
	"TOPIC_NOTIFY_FIELD" => "Wyślij wszystkie odpowiedzi na mój email",

	"ADD_TOPIC_BUTTON" => "Dodaj temat",
	"TOPIC_MESSAGE_BUTTON" => "Dodaj wpis",

	"TOPIC_MISS_ID_ERROR" => "Brakuje parametru <b>Identyfikującego Wątek</b>.",
	"TOPIC_WRONG_ID_ERROR" => "Parametr <b>Identyfikatora wątku</b> ma złą wartość.",
	"FORUM_SEARCH_MESSAGE" => "We've found {search_count} messages matching the term(s) '{search_string}'",
	"TOPIC_PREVIEW_BUTTON" => "Preview",
	"TOPIC_SAVE_BUTTON" => "Save",

	"LAST_POST_ON_SHORT_MSG" => "On:",
	"LAST_POST_IN_SHORT_MSG" => "In:",
	"LAST_POST_BY_SHORT_MSG" => "By:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "Last modified:",

);
$va_messages = array_merge($va_messages, $messages);
