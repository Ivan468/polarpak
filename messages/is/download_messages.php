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
	"DOWNLOAD_WRONG_PARAM" => "Röng/rangar niðurhalsbreyta/ur.",
	"DOWNLOAD_MISS_PARAM" => "Vantar niðurhalsbreytu/r",
	"DOWNLOAD_INACTIVE" => "Niðurhal liggur niðri",
	"DOWNLOAD_EXPIRED" => "Niðurhalstímabil þitt er runnið á enda.",
	"DOWNLOAD_LIMITED" => "Þú ert kominn framyfir hámarksfjölda niðurhala.",
	"DOWNLOAD_PATH_ERROR" => "Slóð að vöru finnst ekki.",
	"DOWNLOAD_RELEASE_ERROR" => "Útgáfa fannst ekki.",
	"DOWNLOAD_USER_ERROR" => "Aðeins skráðir notendur geta niðurhalað þessari skrá.",
	"ACTIVATION_OPTIONS_MSG" => "Stillingar fyrir gangsetningu",
	"ACTIVATION_MAX_NUMBER_MSG" => "Hámarksfjöldi gangsetninga",
	"DOWNLOAD_OPTIONS_MSG" => "Niðurhalanlegt / Hugbúnaðarstillingar",
	"DOWNLOADABLE_MSG" => "Niðurhalanlegt (hugbúnaður)",
	"DOWNLOADABLE_DESC" => "Fyrir niðurhalanlega vöru geturðu líka tilgreint \"Niðurhalstímabil\", \"Leið að niðurhalanlegri skrá\" og \"gangsetningarstillingar\".",
	"DOWNLOAD_PERIOD_MSG" => "Niðurhalstímabil",
	"DOWNLOAD_PATH_MSG" => "Slóð að niðurhalanlegri skrá",
	"DOWNLOAD_PATH_DESC" => "þú getur bætt inn fleiri slóðum og afmarkað þær með semikommu",
	"UPLOAD_SELECT_MSG" => "Veldu skrá til að hlaða upp/senda inn og smelltu á {button_name} hnappinn.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Skránni <b>{filename}</b> hefur verið upphlaðið.",
	"UPLOAD_SELECT_ERROR" => "Vinsamlegast veldu fyrst skrá",
	"UPLOAD_IMAGE_ERROR" => "Aðeins myndaskrár eru leyfðar",
	"UPLOAD_FORMAT_ERROR" => "Þessi skráartegund er ekki leyfð",
	"UPLOAD_SIZE_ERROR" => "Skrár stærri en {filesize} eru ekki leyfðar.",
	"UPLOAD_DIMENSION_ERROR" => "Myndir stærri en {dimension} eru ekki leyfðar.",
	"UPLOAD_CREATE_ERROR" => "Kerfið getur ekki búið til skránna.",
	"UPLOAD_ACCESS_ERROR" => "Þú hefur ekki réttindi til að hlaða upp skrám",
	"DELETE_FILE_CONFIRM_MSG" => "Ertu viss um að þú viljir eyða þessari skrá?",
	"NO_FILES_MSG" => "Engar skrár fundust",
	"SERIAL_GENERATE_MSG" => "Útbúa raðnúmer",
	"SERIAL_DONT_GENERATE_MSG" => "ekki útbúa",
	"SERIAL_RANDOM_GENERATE_MSG" => "útbúa slembiraðnúmer fyrir hugbúnað",
	"SERIAL_FROM_PREDEFINED_MSG" => "sækja raðnúmer í fyrirfram tilgreindan lista",
	"SERIAL_PREDEFINED_MSG" => "Fyrirfram tilgreind raðnúmer",
	"SERIAL_NUMBER_COLUMN" => "Raðnúmer",
	"SERIAL_USED_COLUMN" => "Notað",
	"SERIAL_DELETE_COLUMN" => "Eyða",
	"SERIAL_MORE_MSG" => "Bæta við fleiri raðnúmerum?",
	"SERIAL_PERIOD_MSG" => "Tímabil raðnúmer",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Sýna skilmála",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Til að niðurhala vörunni þarf notandi að hafa lesið og samþykkt skilmála okkar",
	"DOWNLOAD_TERMS_USER_ERROR" => "Til að niðurhala vörunni þarf þú að hafa lesið og samþykkt skilmála okkar",

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
