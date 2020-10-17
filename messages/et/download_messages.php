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
	// download messages
	"DOWNLOAD_WRONG_PARAM" => "Vale(d) allalaadimise parameeter(-id).",
	"DOWNLOAD_MISS_PARAM" => "Puuduv(ad) allalaadimise parameeter(-id).",
	"DOWNLOAD_INACTIVE" => "Allalaadimine passiivne.",
	"DOWNLOAD_EXPIRED" => "Sinu allalaadimise periood on lõppenud.",
	"DOWNLOAD_LIMITED" => "Oled ületanud maksimaalse allalaadimiste arvu.",
	"DOWNLOAD_PATH_ERROR" => "Ei leita teed tooteni ",
	"DOWNLOAD_RELEASE_ERROR" => "Uuendust ei leitud.",
	"DOWNLOAD_USER_ERROR" => "Seda faili saavad alla laadida ainult registreerunud kasutajad.",
	"ACTIVATION_OPTIONS_MSG" => "Aktiveerimise valikud",
	"ACTIVATION_MAX_NUMBER_MSG" => "Aktiveerimiste maksimaalne arv",
	"DOWNLOAD_OPTIONS_MSG" => "Allalaetav / tarkvara valikud",
	"DOWNLOADABLE_MSG" => "Allalaetav (tarkvara)",
	"DOWNLOADABLE_DESC" => "Allalaetavatel toodetel saab täpsustada ka 'Allalaadimise perioodi', 'Teed allalaetava failini' ja 'Aktiveerimise valikuid'",
	"DOWNLOAD_PERIOD_MSG" => "Allalaadimise periood",
	"DOWNLOAD_PATH_MSG" => "Tee allalaetava failini",
	"DOWNLOAD_PATH_DESC" => "Võid lisada mitu teed eraldades need semikoolonitega",
	"UPLOAD_SELECT_MSG" => "Vali fail üleslaadimiseks ja vajuta {button_name} nuppu.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Fail <b>{filename}</b> on üles laetud.",
	"UPLOAD_SELECT_ERROR" => "Esmalt palun vali fail.",
	"UPLOAD_IMAGE_ERROR" => "Lubatud on ainult pildifailid.",
	"UPLOAD_FORMAT_ERROR" => "Seda tüüpi fail ei ole lubatud.",
	"UPLOAD_SIZE_ERROR" => "Failid suuremad kui {filesize} ei ole lubatud.",
	"UPLOAD_DIMENSION_ERROR" => "Pildid suuremad kui  {dimension}  ei ole lubatud.",
	"UPLOAD_CREATE_ERROR" => "Süsteem ei saa luua faili.",
	"UPLOAD_ACCESS_ERROR" => "Sul ei ole luba failide üleslaadimiseks.",
	"DELETE_FILE_CONFIRM_MSG" => "Kas oled kindel, et tahad seda faili kustutada?",
	"NO_FILES_MSG" => "Ei leitud ühtegi faili",
	"SERIAL_GENERATE_MSG" => "Genereeri seerianumber",
	"SERIAL_DONT_GENERATE_MSG" => "Ära genereeri",
	"SERIAL_RANDOM_GENERATE_MSG" => "Genereeri juhuslik seeria tarkvara tootele",
	"SERIAL_FROM_PREDEFINED_MSG" => "Võta seerianumber eelnevalt määratud loetelust",
	"SERIAL_PREDEFINED_MSG" => "Eelnevalt määratud seerianumbrid",
	"SERIAL_NUMBER_COLUMN" => "Seerianumber",
	"SERIAL_USED_COLUMN" => "Kasutatud",
	"SERIAL_DELETE_COLUMN" => "Kustuta",
	"SERIAL_MORE_MSG" => "Lisa veel seerianumbreid?",
	"SERIAL_PERIOD_MSG" => "Seerianumbri periood",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Näita tingimusi ja nõudmisi",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Toote allalaadimiseks peab kasutaja läbi lugema ning nõustuma meie tingimuste ja nõudmistega",
	"DOWNLOAD_TERMS_USER_ERROR" => "Toote allalaadimiseks peate Teie läbi lugema ning nõustuma meie tingimuste ja nõudmistega",

	"DOWNLOAD_TITLE_MSG" => "Allalaadimise pealkiri (1305)",
	"DOWNLOADABLE_FILES_MSG" => "Allalaetavad failid",
	"DOWNLOAD_INTERVAL_MSG" => "Allalaadimise intervall",
	"DOWNLOAD_LIMIT_MSG" => "Allalaadimiste limiit",
	"DOWNLOAD_LIMIT_DESC" => "arv kordi, mil faili saab alla laadida",
	"MAXIMUM_DOWNLOADS_MSG" => "Maksimaalne allalaadimiste arv",
	"PREVIEW_TYPE_MSG" => "Eelvaate tüüp",
	"PREVIEW_TITLE_MSG" => "Eelvaate pealkiri",
	"PREVIEW_PATH_MSG" => "Tee eelvaate failini",
	"PREVIEW_IMAGE_MSG" => "Pildi eelvaade (1314)",
	"MORE_FILES_MSG" => "Veel faile",
	"UPLOAD_MSG" => "Lae üles ",
	"USE_WITH_OPTIONS_MSG" => "Kasuta ainult koos valikutega",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Eelvaade alla laadimisel (1318)",
	"PREVIEW_USE_PLAYER_MSG" => "Kasuta mängijat eelvaateks",
	"PROD_PREVIEWS_MSG" => "Eelvaated",

);
$va_messages = array_merge($va_messages, $messages);
