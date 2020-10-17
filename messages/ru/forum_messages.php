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
	// сообщения форума
	"FORUM_TITLE" => "Форум",
	"TOPIC_INFO_TITLE" => "Информация о теме",
	"TOPIC_MESSAGE_TITLE" => "Сообщение",

	"MY_FORUM_TOPICS_MSG" => "Мои темы на форуме",
	"ALL_FORUM_TOPICS_MSG" => "Все темы форума",
	"MY_FORUM_TOPICS_DESC" => "Возможно, Ваш вопрос уже обсуждался и имеет решение. Возможно, Вы хотите поделиться своими мнением и знаниями с другими  пользователями. Приглашаем на наш Форум!",
	"NEW_TOPIC_MSG" => "Новая тема",
	"NO_TOPICS_MSG" => "Тема не найдена",
	"FOUND_TOPICS_MSG" => "Найдено <b> {found_records} </b> тем, соответствующих запросу ' <b> {search_string} </b> '",
	"NO_FORUMS_MSG" => "Форумы не найдены",

	"FORUM_NAME_COLUMN" => "Форум",
	"FORUM_TOPICS_COLUMN" => "Тема",
	"FORUM_REPLIES_COLUMN" => "Ответов",
	"FORUM_LAST_POST_COLUMN" => "Последнее обновление",
	"FORUM_MODERATORS_MSG" => "Модераторы",

	"TOPIC_NAME_COLUMN" => "Тема",
	"TOPIC_AUTHOR_COLUMN" => "Автор",
	"TOPIC_VIEWS_COLUMN" => "Просмотры",
	"TOPIC_REPLIES_COLUMN" => "Ответы",
	"TOPIC_UPDATED_COLUMN" => "Обновлено",
	"TOPIC_ADDED_MSG" => "Спасибо<br>Ваша тема добавлена",

	"TOPIC_ADDED_BY_FIELD" => "Добавил",
	"TOPIC_ADDED_DATE_FIELD" => "Добавлено",
	"TOPIC_UPDATED_FIELD" => "Последнее обновление",
	"TOPIC_NICKNAME_FIELD" => "Ник (NICKNAME)",
	"TOPIC_EMAIL_FIELD" => "Ваш электронный адрес",
	"TOPIC_NAME_FIELD" => "Тема",
	"TOPIC_MESSAGE_FIELD" => "Сообщение",
	"TOPIC_NOTIFY_FIELD" => "Прислать все ответы на мой электронный адрес",

	"ADD_TOPIC_BUTTON" => "Добавить тему",
	"TOPIC_MESSAGE_BUTTON" => "Добавить сообщение",

	"TOPIC_MISS_ID_ERROR" => "Пропущен параметр <b>ID сообщения</b>.",
	"TOPIC_WRONG_ID_ERROR" => "Параметр <b>ID сообщения</b имеет неправильное значение",
	"FORUM_SEARCH_MESSAGE" => "Найдено {search_count} сообщений, соответствующих условию '{search_string}'",
	"TOPIC_PREVIEW_BUTTON" => "Пред просмотр",
	"TOPIC_SAVE_BUTTON" => "Сохранить",

	"LAST_POST_ON_SHORT_MSG" => "На:",
	"LAST_POST_IN_SHORT_MSG" => "В:",
	"LAST_POST_BY_SHORT_MSG" => "По:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "Последние изменения:",

);
$va_messages = array_merge($va_messages, $messages);
