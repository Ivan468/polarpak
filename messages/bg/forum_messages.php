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
	//съобщения свързани с форума
	"FORUM_TITLE" => "Форум",
	"TOPIC_INFO_TITLE" => "Информация за темата",
	"TOPIC_MESSAGE_TITLE" => "Съобщение",

	"MY_FORUM_TOPICS_MSG" => "Моите теми",
	"ALL_FORUM_TOPICS_MSG" => "Всички теми",
	"MY_FORUM_TOPICS_DESC" => "Установили ли сте дали някой друг има проблем, с който вие сте се сблъскали? Бихте ли искали да споделите знанията и опитът си с други потребители? Защо не станете потребител на форума и да се присъедините към общността?",
	"NEW_TOPIC_MSG" => "Нова тема",
	"NO_TOPICS_MSG" => "Не са намерени теми",
	"FOUND_TOPICS_MSG" => "Намерихме <b>{found_records}</b> съвпадащи с '<b>{search_string}</b>'",
	"NO_FORUMS_MSG" => "Не са намерени форуми",

	"FORUM_NAME_COLUMN" => "Форум",
	"FORUM_TOPICS_COLUMN" => "Теми",
	"FORUM_REPLIES_COLUMN" => "Отговори",
	"FORUM_LAST_POST_COLUMN" => "Последно обновен",
	"FORUM_MODERATORS_MSG" => "Модератори",

	"TOPIC_NAME_COLUMN" => "Тема",
	"TOPIC_AUTHOR_COLUMN" => "Автор",
	"TOPIC_VIEWS_COLUMN" => "Прегледи",
	"TOPIC_REPLIES_COLUMN" => "Отговори",
	"TOPIC_UPDATED_COLUMN" => "Последно обновен",
	"TOPIC_ADDED_MSG" => "Благодаря<br>Вашата тема беше добавена",

	"TOPIC_ADDED_BY_FIELD" => "Добавено от",
	"TOPIC_ADDED_DATE_FIELD" => "Добавено",
	"TOPIC_UPDATED_FIELD" => "Последно обновено",
	"TOPIC_NICKNAME_FIELD" => "Прякор",
	"TOPIC_EMAIL_FIELD" => "Вашият e-mail адрес",
	"TOPIC_NAME_FIELD" => "Тема",
	"TOPIC_MESSAGE_FIELD" => "Съобщение",
	"TOPIC_NOTIFY_FIELD" => "Изпрати всички отговори на моя e-mail",

	"ADD_TOPIC_BUTTON" => "Добави тема",
	"TOPIC_MESSAGE_BUTTON" => "Добави съобщение",

	"TOPIC_MISS_ID_ERROR" => "Липсващ <b>Thread ID</b> параметър.",
	"TOPIC_WRONG_ID_ERROR" => "<b>Thread ID</b> параметърът има грешна стойност.",
	"FORUM_SEARCH_MESSAGE" => "Намерихме {search_count} съобщения съответстващи на критерия '{search_string}'.",
	"TOPIC_PREVIEW_BUTTON" => "Преглед",
	"TOPIC_SAVE_BUTTON" => "Запис",

	"LAST_POST_ON_SHORT_MSG" => "На:",
	"LAST_POST_IN_SHORT_MSG" => "В:",
	"LAST_POST_BY_SHORT_MSG" => "До:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "Последно обновен:",

);
$va_messages = array_merge($va_messages, $messages);
