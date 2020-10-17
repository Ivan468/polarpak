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
	"MY_PROFILES_MSG" => "My Profiles",
	"MY_PROFILES_DESC" => "If you like to find someone, just submit your profile here.",
	"ADD_MY_PROFILE_MSG" => "Add My Profile",
	"FOUND_PROFILES_MSG" => "We've found <b>{found_records}</b> profiles matching your search criteria",
	"NO_PROFILES_MSG" => "No profiles were found",
	"PROFILE_TYPE_FIELD" => "I am a",
	"LOOKING_TYPE_FIELD" => "Looking for a",
	"PERSONAL_INFO_FIELD" => "A few words about you",
	"LOOKING_INFO_FIELD" => "What are you looking for",
	"ABOUT_ME_MSG" => "About me",
	"I_AM_LOOKING_MSG" => "I'm looking for a",
	"MALE_MSG" => "Male",
	"FEMALE_MSG" => "Female",
	"PROFILES_LIMIT_MSG" => "Profiles Limit",
	"PROFILES_LIMIT_DESC" => "number of profiles can be added by user",
	"PROFILES_LIMIT_ERROR" => "Sorry, but you are not allowed to add more than {profiles_limit} profiles.",
	"ETHNICITY_MSG" => "Ethnicity",
	"PROFILE_NEW_ERROR" => "You don't have permissions to create a new profile.",
	"PROFILE_EDIT_ERROR" => "You don't have permissions to edit this profile.",
	"PROFILE_DELETE_ERROR" => "You don't have permissions to delete this profile.",
	"PROFILE_TERMS_ERROR" => "To submit profile you have to read and agree to our terms and conditions",

	"PHOTO_RESTRICTIONS_MSG" => "Photo Restrictions",
	"PHOTO_TINY_MSG" => "Tiny Photo",
	"PHOTO_SMALL_MSG" => "Small Photo",
	"PHOTO_LARGE_MSG" => "Large Photo",
	"PHOTO_SUPER_MSG" => "Super-sized Photo",

	"GENERATE_TINY_PHOTO_MSG" => "generate tiny photo",
	"GENERATE_SMALL_PHOTO_MSG" => "generate small photo",
	"GENERATE_LARGE_PHOTO_MSG" => "generate large photo",
	"GENERATE_SUPER_PHOTO_MSG" => "generate super-sized photo",

);
$va_messages = array_merge($va_messages, $messages);
