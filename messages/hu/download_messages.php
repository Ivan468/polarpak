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
	//letöltés üzenetek
	"DOWNLOAD_WRONG_PARAM" => "Rossz letöltés paraméter (ek).",
	"DOWNLOAD_MISS_PARAM" => "Eltűnt letöltés paraméter (ek).",
	"DOWNLOAD_INACTIVE" => "Letöltés nem aktív.",
	"DOWNLOAD_EXPIRED" => "A letöltésed időszaka lejárt.",
	"DOWNLOAD_LIMITED" => "Elérted a maximális számú letöltést.",
	"DOWNLOAD_PATH_ERROR" => "At elérési útja a terméknek nem található.",
	"DOWNLOAD_RELEASE_ERROR" => "Publikálást nem található.",
	"DOWNLOAD_USER_ERROR" => "Csak regisztrált felhasználók tölthetik le ezt a fájlt.",
	"ACTIVATION_OPTIONS_MSG" => "Aktiválási opciók",
	"ACTIVATION_MAX_NUMBER_MSG" => "Az aktiválások számának maximuma",
	"DOWNLOAD_OPTIONS_MSG" => "Letölthető /Szoftver opciók",
	"DOWNLOADABLE_MSG" => "Letölthető (Szoftver)",
	"DOWNLOADABLE_DESC" => "Letölthető terméknek  meg tudod határozni a 'letöltési időszakot', szintén meg lehet határozni 'Letöltés befejeződése', 'Elérése a letölthető fájlnak' és az 'Aktiválás opciók' t.",
	"DOWNLOAD_PERIOD_MSG" => "Letöltési időszak",
	"DOWNLOAD_PATH_MSG" => "Letölthető fájl elérési útja",
	"DOWNLOAD_PATH_DESC" => "Több elérési utat is meg lehet adni, pontosvesszővel elválasztva.",
	"UPLOAD_SELECT_MSG" => "Válaszd ki a feltöltendő fájlt és nyomd meg a {button_name} gombot.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "<b>{filename}</b> fájl feltöltődött.",
	"UPLOAD_SELECT_ERROR" => "Kérek kiválasztani egy fájlt először.",
	"UPLOAD_IMAGE_ERROR" => "Csak kép fájl tölthető fel.",
	"UPLOAD_FORMAT_ERROR" => "Ez típusú fájl nem megengedett.",
	"UPLOAD_SIZE_ERROR" => "Nagyobb fájlok mint {filesize} nem megengedett.",
	"UPLOAD_DIMENSION_ERROR" => "Nagyobb kép mint {dimension} nem megengedett.",
	"UPLOAD_CREATE_ERROR" => "Rendszer nem tudta létrehozni a fájlt.",
	"UPLOAD_ACCESS_ERROR" => "Nem rendelkezel engedéllyel fájl feltöltésére.",
	"DELETE_FILE_CONFIRM_MSG" => "Biztos törli ezt a fájlt?",
	"NO_FILES_MSG" => "Fájl nem található",
	"SERIAL_GENERATE_MSG" => "Sorozatszám generálás",
	"SERIAL_DONT_GENERATE_MSG" => "Ne generáljon",
	"SERIAL_RANDOM_GENERATE_MSG" => "Generáljon véletlenszerű sorozatszámot a szoftvernek",
	"SERIAL_FROM_PREDEFINED_MSG" => "Vegye a sorozatszámot az előre meghatározott számokat tartalmazó listából",
	"SERIAL_PREDEFINED_MSG" => "Előre meghatározott sorozatszámok",
	"SERIAL_NUMBER_COLUMN" => "Sorozatszám",
	"SERIAL_USED_COLUMN" => "Használt",
	"SERIAL_DELETE_COLUMN" => "Törlés",
	"SERIAL_MORE_MSG" => "Több sorozatszám hozzáadása?",
	"SERIAL_PERIOD_MSG" => "Szériaszám periódus",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Szabályok és feltételek megjelenítése",
	"DOWNLOAD_SHOW_TERMS_DESC" => "A termék letöltéséhez a felhasználónak el kel olvasnia és el kell fogadnia a szabályoka és feltételeket.",
	"DOWNLOAD_TERMS_USER_ERROR" => "A termék letöltéséhez el kell olvasnod és el kell fogadnod a felhasználási feltételeket és szabályokat.",

	"DOWNLOAD_TITLE_MSG" => "Letöltés címe",
	"DOWNLOADABLE_FILES_MSG" => "Letölthető Fájlok",
	"DOWNLOAD_INTERVAL_MSG" => "Letöltési időszak",
	"DOWNLOAD_LIMIT_MSG" => "Letöltési limit",
	"DOWNLOAD_LIMIT_DESC" => "a fájl ennyiszer lesz letölthető",
	"MAXIMUM_DOWNLOADS_MSG" => "Maximum Letöltések száma",
	"PREVIEW_TYPE_MSG" => "Előnézet típusa",
	"PREVIEW_TITLE_MSG" => "Előnézet címe",
	"PREVIEW_PATH_MSG" => "Előnézeti fásjl URL",
	"PREVIEW_IMAGE_MSG" => "Előnézeti kép",
	"MORE_FILES_MSG" => "Több Fájl",
	"UPLOAD_MSG" => "Feltöltés",
	"USE_WITH_OPTIONS_MSG" => "Csak opciókkal használva",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Előnézet letöltésként",
	"PREVIEW_USE_PLAYER_MSG" => "Előnézet lejátszóval",
	"PROD_PREVIEWS_MSG" => "Bemutatók és Letöltések",
	//támogatás üzenetek
);
$va_messages = array_merge($va_messages, $messages);
