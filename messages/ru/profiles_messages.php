<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  profiles_messages.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// profile lookup messages
	"CAN_USER_ACCESS_PROFILES_MSG" => "Can user access to the profiles section",
	"CAN_USER_ADD_PROFILE_MSG" => "Can user add new profiles",
	"CAN_USER_EDIT_PROFILE_MSG" => "Can user edit his profiles",
	"CAN_USER_DELETE_PROFILE_MSG" => "Can user delete his profiles",
	"AUTO_APPROVE_PROFILE_MSG" => "Automatically approve submitted profiles",
	"MY_PROFILES_MSG" => "Мои анкеты",
	"MY_PROFILES_DESC" => "Если вы хотите найти кого-ты, просто добавьте вашу анкету здесь",
	"ADD_MY_PROFILE_MSG" => "Добавить мою анкету",
	"FOUND_PROFILES_MSG" => "Мы нашли <b>{found_records}</b> анкет удовлетворяющих заданым критериям",
	"NO_PROFILES_MSG" => "No profiles were found",
	"PROFILE_TYPE_FIELD" => "Я",
	"LOOKING_TYPE_FIELD" => "Ищу",
	"PERSONAL_INFO_FIELD" => "Несколько слов о вас",
	"LOOKING_INFO_FIELD" => "Что вы ищите",
	"ABOUT_ME_MSG" => "Або мне",
	"I_AM_LOOKING_MSG" => "Я ищу",
	"MALE_MSG" => "Мужчина",
	"FEMALE_MSG" => "Женщина",
	"PROFILES_LIMIT_MSG" => "Лимит анкет",
	"PROFILES_LIMIT_DESC" => "количество анкет пользователь может добавить",
	"PROFILES_LIMIT_ERROR" => "Извините, но вы не можете добавить больше чем {profiles_limit} анкет.",
	"ETHNICITY_MSG" => "Раса",
	"PROFILE_NEW_ERROR" => "You don't have permissions to create a new profile.",
	"PROFILE_EDIT_ERROR" => "You don't have permissions to edit this profile.",
	"PROFILE_DELETE_ERROR" => "You don't have permissions to delete this profile.",
	"PROFILE_TERMS_ERROR" => "Для добавления анкеты вы должны прочитать и принять наши правила и условия.",

	"PHOTO_RESTRICTIONS_MSG" => "Ограничения фото",
	"PHOTO_TINY_MSG" => "Мини фото",
	"PHOTO_SMALL_MSG" => "Маленькое фото",
	"PHOTO_LARGE_MSG" => "Большое фото",
	"PHOTO_SUPER_MSG" => "Огромное фото",

	"GENERATE_TINY_PHOTO_MSG" => "generate tiny photo",
	"GENERATE_SMALL_PHOTO_MSG" => "generate small photo",
	"GENERATE_LARGE_PHOTO_MSG" => "generate large photo",
	"GENERATE_SUPER_PHOTO_MSG" => "generate super-sized photo",

);
$va_messages = array_merge($va_messages, $messages);
