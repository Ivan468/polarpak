<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  download_messages.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// siuntimų žinutės
	"DOWNLOAD_WRONG_PARAM" => "Blogas(i) siuntimo parametras(ai)",
	"DOWNLOAD_MISS_PARAM" => "Trūkstamas(i) siuntimo parametras(ai)",
	"DOWNLOAD_INACTIVE" => "Siuntimas neaktyvus.",
	"DOWNLOAD_EXPIRED" => "Jūsų siuntimo laikas baigėsi.",
	"DOWNLOAD_LIMITED" => "Jūs viršijote didžiausią siuntimų skaičių.",
	"DOWNLOAD_PATH_ERROR" => "Kelias iki prekės nerastas.",
	"DOWNLOAD_RELEASE_ERROR" => "Leidimas nerastas.",
	"DOWNLOAD_USER_ERROR" => "Tik registruoti vartotojai gali siųstis šią bylą.",
	"ACTIVATION_OPTIONS_MSG" => "Aktyvacijos parinktys",
	"ACTIVATION_MAX_NUMBER_MSG" => "Didžiausias aktyvacijų kiekis",
	"DOWNLOAD_OPTIONS_MSG" => "Siunčiamų / Programinių Parinktys",
	"DOWNLOADABLE_MSG" => "Siunčiamas (Programinis)",
	"DOWNLOADABLE_DESC" => "siunčiamam gaminiui jūs galite nurodyti \"Siuntimosi periodą\", \"Kelią iki siunčiamos bylos\" ir \"Aktyvavimo Parinktis\"",
	"DOWNLOAD_PERIOD_MSG" => "Siuntimosi periodas",
	"DOWNLOAD_PATH_MSG" => "Kelias iki siuntimosi bylos",
	"DOWNLOAD_PATH_DESC" => "Jūs galite pridėti daug kelių atskirtų kabliataškiais",
	"UPLOAD_SELECT_MSG" => "Rinkis bylą nusiųsti ir spausk {button_name} mygtuką.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Byla <b>{filename}</b> buvo užkrautas.",
	"UPLOAD_SELECT_ERROR" => "Prašome pirmiau rinktis bylą.",
	"UPLOAD_IMAGE_ERROR" => "Tik vaizdų bylos leidžiamos.",
	"UPLOAD_FORMAT_ERROR" => "Šis bylos tipas neleidžiamas.",
	"UPLOAD_SIZE_ERROR" => "Bylos didesnės kaip {filesize} neleidžiamos.",
	"UPLOAD_DIMENSION_ERROR" => "Vaizdai didesni kaip {dimension} neleidžiami.",
	"UPLOAD_CREATE_ERROR" => "Sistema negali sukurti bylos.",
	"UPLOAD_ACCESS_ERROR" => "Jūs neturite teisių užkrauti bylų.",
	"DELETE_FILE_CONFIRM_MSG" => "Ar jūs tikrai norite trinti šią bylą?",
	"NO_FILES_MSG" => "Bylų nerasta",
	"SERIAL_GENERATE_MSG" => "Generuok serijinį numerį",
	"SERIAL_DONT_GENERATE_MSG" => "Negeneruok",
	"SERIAL_RANDOM_GENERATE_MSG" => "Generuok atsitiktinį serijinį nr programiniam gaminiui",
	"SERIAL_FROM_PREDEFINED_MSG" => "gauk serijinį nr iš priešnustatyto sąrašo",
	"SERIAL_PREDEFINED_MSG" => "Priešnustatyti serijiniai numeriai",
	"SERIAL_NUMBER_COLUMN" => "Serijinis numeris",
	"SERIAL_USED_COLUMN" => "Naudotas",
	"SERIAL_DELETE_COLUMN" => "Trink",
	"SERIAL_MORE_MSG" => "Pridėti daugiau serijinių numerių?",
	"SERIAL_PERIOD_MSG" => "Serial Number Period",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Show Terms & Conditions",
	"DOWNLOAD_SHOW_TERMS_DESC" => "To download the product user has to read and agree to our terms and conditions",
	"DOWNLOAD_TERMS_USER_ERROR" => "To download the product you have to read and agree to our terms and conditions",

	"DOWNLOAD_TITLE_MSG" => "Download Title",
	"DOWNLOADABLE_FILES_MSG" => "Downloadable Files",
	"DOWNLOAD_INTERVAL_MSG" => "Download Interval",
	"DOWNLOAD_LIMIT_MSG" => "Downloads Limit",
	"DOWNLOAD_LIMIT_DESC" => "number of times file can be downloaded",
	"MAXIMUM_DOWNLOADS_MSG" => "Maximum Downloads",
	"PREVIEW_TYPE_MSG" => "Preview Type",
	"PREVIEW_TITLE_MSG" => "Preview Title",
	"PREVIEW_PATH_MSG" => "Path to Preview File",
	"PREVIEW_IMAGE_MSG" => "Preview Image",
	"MORE_FILES_MSG" => "More Files",
	"UPLOAD_MSG" => "Upload",
	"USE_WITH_OPTIONS_MSG" => "Use with options only",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Preview as download",
	"PREVIEW_USE_PLAYER_MSG" => "Use player to preview",
	"PROD_PREVIEWS_MSG" => "Previews",

);
$va_messages = array_merge($va_messages, $messages);
