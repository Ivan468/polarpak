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
	// nedlastingsbeskjeder
	"DOWNLOAD_WRONG_PARAM" => "Feil nedlastingsverdi(er).",
	"DOWNLOAD_MISS_PARAM" => "Nedlastingsverdi(er) mangler.",
	"DOWNLOAD_INACTIVE" => "Nedlasting ikke aktivert.",
	"DOWNLOAD_EXPIRED" => "Din nedlastingsperiode har utgått.",
	"DOWNLOAD_LIMITED" => "Du har overskridet det tillatte antallet nedlastinger.",
	"DOWNLOAD_PATH_ERROR" => "Spor til vare kan ikke bi funnet.",
	"DOWNLOAD_RELEASE_ERROR" => "Utgivelse ble ikke funnet.",
	"DOWNLOAD_USER_ERROR" => "Kun registrerte brukere kan laste ned denne filen.",
	"ACTIVATION_OPTIONS_MSG" => "Aktiveringsvalg",
	"ACTIVATION_MAX_NUMBER_MSG" => "Maksimum antall aktiveringer",
	"DOWNLOAD_OPTIONS_MSG" => "Nedlastbar / Programvare valg",
	"DOWNLOADABLE_MSG" => "Nedlastbare (Programvare)",
	"DOWNLOADABLE_DESC" => "For nedlastbar produkt kan du også spesifisere 'Nedlastingsperiode', 'Spor til nedlastbar fil' og 'Aktiveringsvalg' ",
	"DOWNLOAD_PERIOD_MSG" => "Nedlastingsperiode",
	"DOWNLOAD_PATH_MSG" => "Spor til nedlastbar fil",
	"DOWNLOAD_PATH_DESC" => "du kan legge til flere spor skilt med semikolon",
	"UPLOAD_SELECT_MSG" => "Velg fil for opplasting og trykk {button_name} tasten.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Filen <b>{filename}</b> har blitt lastet opp.",
	"UPLOAD_SELECT_ERROR" => "Vennligst velg en fil først.",
	"UPLOAD_IMAGE_ERROR" => "Kun bildefiler er tillatt.",
	"UPLOAD_FORMAT_ERROR" => "Denne type filer er ikke tillatt.",
	"UPLOAD_SIZE_ERROR" => "Filer større enn {filesize} er ikke tillatt.",
	"UPLOAD_DIMENSION_ERROR" => "Bilder større enn {dimension} er ikke tillatt.",
	"UPLOAD_CREATE_ERROR" => "Systemet kan ikke opprette denne filen.",
	"UPLOAD_ACCESS_ERROR" => "Du har ikke tillatelse til å laste opp filer.",
	"DELETE_FILE_CONFIRM_MSG" => "Er du sikker på at du vil slette denne filen?",
	"NO_FILES_MSG" => "Ingen filer ble funnet",
	"SERIAL_GENERATE_MSG" => "Generer serienummer",
	"SERIAL_DONT_GENERATE_MSG" => "Ikke generer",
	"SERIAL_RANDOM_GENERATE_MSG" => "Generer tilfeldig serienummer for programvare produkter",
	"SERIAL_FROM_PREDEFINED_MSG" => "Få serienummer fra forhåndsbestemt liste",
	"SERIAL_PREDEFINED_MSG" => "Forhåndsbestemt serienmummer",
	"SERIAL_NUMBER_COLUMN" => "Serienummer",
	"SERIAL_USED_COLUMN" => "Brukt",
	"SERIAL_DELETE_COLUMN" => "Slett",
	"SERIAL_MORE_MSG" => "Tilføye flere serienumre?",
	"SERIAL_PERIOD_MSG" => "Serienummer periode",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Vis bestemmelser & betingelser",
	"DOWNLOAD_SHOW_TERMS_DESC" => "For å nedlaste dette produktet må brukeren lese og godta våre bestemmelser og betingelser",
	"DOWNLOAD_TERMS_USER_ERROR" => "For å nedlaste dette produktet må du lese og godta våre bestemmelser og betingelser",

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
