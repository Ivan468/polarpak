<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  download_messages.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// download messages
	"DOWNLOAD_WRONG_PARAM" => "Väärät lataus parametrit",
	"DOWNLOAD_MISS_PARAM" => "Puuttuvat latausparametrit",
	"DOWNLOAD_INACTIVE" => "Lataus ei aktiivinen",
	"DOWNLOAD_EXPIRED" => "Latausaikasi on loppunut",
	"DOWNLOAD_LIMITED" => "Olet ladannut sinulle varatun määrän",
	"DOWNLOAD_PATH_ERROR" => "Polkua tuotteeseen ei löydy",
	"DOWNLOAD_RELEASE_ERROR" => "Julkaisua ei löytynyt",
	"DOWNLOAD_USER_ERROR" => "Vain rekisteröityneille käyttäjille",
	"ACTIVATION_OPTIONS_MSG" => "Aktivointi tiedot",
	"ACTIVATION_MAX_NUMBER_MSG" => "Max kpl aktivointeja",
	"DOWNLOAD_OPTIONS_MSG" => "Ladattavat ohjelmistot / asetukset",
	"DOWNLOADABLE_MSG" => "Ladattavat (ohjelmat)",
	"DOWNLOADABLE_DESC" => "ladattaville tuotteille voit asettaa mm. aktivoinnin, max lataukset ja latauspolun",
	"DOWNLOAD_PERIOD_MSG" => "Latausaika ",
	"DOWNLOAD_PATH_MSG" => "Polku tiedostolle",
	"DOWNLOAD_PATH_DESC" => "Voit lisätä enemmän polkuja puolipisteellä",
	"UPLOAD_SELECT_MSG" => "Please select a file and press 'Upload' button.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Tiedosto <b>{filename}</b> ladattiin.",
	"UPLOAD_SELECT_ERROR" => "Valitse ensin tiedosto",
	"UPLOAD_IMAGE_ERROR" => "Vain kuvat ovat sallittuja",
	"UPLOAD_FORMAT_ERROR" => "Tämä tiedosto ei ole sallittu",
	"UPLOAD_SIZE_ERROR" => "Tiedoston suurin koko on {filesize} .",
	"UPLOAD_DIMENSION_ERROR" => "Kuvan suurin koko on {dimension} .",
	"UPLOAD_CREATE_ERROR" => "Tiedostoa ei voi luoda",
	"UPLOAD_ACCESS_ERROR" => "Sinulla ei ole oikeuksia ladata tiedostoja",
	"DELETE_FILE_CONFIRM_MSG" => "Poistetaan tiedosto varmasti?",
	"NO_FILES_MSG" => "Ei tiedostoja",
	"SERIAL_GENERATE_MSG" => "Tee sarjanumero",
	"SERIAL_DONT_GENERATE_MSG" => "Älä tee",
	"SERIAL_RANDOM_GENERATE_MSG" => "tee satunnainen sarjanumero tuotteelle",
	"SERIAL_FROM_PREDEFINED_MSG" => "hae sarjanumero listalta",
	"SERIAL_PREDEFINED_MSG" => "Ennalta määritellyt sarjanumerot",
	"SERIAL_NUMBER_COLUMN" => "Sarjanumero",
	"SERIAL_USED_COLUMN" => "Käytetty",
	"SERIAL_DELETE_COLUMN" => "Poista",
	"SERIAL_MORE_MSG" => "Lisää sarjanumeroja",
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
