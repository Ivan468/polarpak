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
	"DOWNLOAD_WRONG_PARAM" => "Forkert download parameter (s).",
	"DOWNLOAD_MISS_PARAM" => "Manglende download parameter (s).",
	"DOWNLOAD_INACTIVE" => "Download inaktive.",
	"DOWNLOAD_EXPIRED" => "Din download er udløbet.",
	"DOWNLOAD_LIMITED" => "You have exceed the maximum number of downloads. Du har overskedet det maksimale antal downloads.",
	"DOWNLOAD_PATH_ERROR" => "Path to product cannot be found. Sti til produktet ikke kan findes.",
	"DOWNLOAD_RELEASE_ERROR" => "Release blev ikke fundet.",
	"DOWNLOAD_USER_ERROR" => "Kun registrerede brugere kan hente denne fil.",
	"ACTIVATION_OPTIONS_MSG" => "Aktivering Valg",
	"ACTIVATION_MAX_NUMBER_MSG" => "Max antal aktiveringer",
	"DOWNLOAD_OPTIONS_MSG" => "Downloadable / Software Valg",
	"DOWNLOADABLE_MSG" => "Downloadable (Software)",
	"DOWNLOADABLE_DESC" => "for download produkter, kan du også angive 'download periode', 'Sti til download filer' og 'aktiveringes valg'",
	"DOWNLOAD_PERIOD_MSG" => "Download periode",
	"DOWNLOAD_PATH_MSG" => "Sti til download filer",
	"DOWNLOAD_PATH_DESC" => "du kan tilføje flere stier delt med semikolon",
	"UPLOAD_SELECT_MSG" => "Vælg fil til at uploade, og tryk på {button_name} knappen.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "File <b> {filename} </ b> blev uploadet.",
	"UPLOAD_SELECT_ERROR" => "Vælg en fil først.",
	"UPLOAD_IMAGE_ERROR" => "Kun billedfiler er tilladt.",
	"UPLOAD_FORMAT_ERROR" => "Denne filtype er ikke tilladt.",
	"UPLOAD_SIZE_ERROR" => "Filer større end {filesize} er ikke tilladt.",
	"UPLOAD_DIMENSION_ERROR" => "Billeder, der er større end {dimension} er ikke tilladt.",
	"UPLOAD_CREATE_ERROR" => "Systemet kan ikke oprette filen.",
	"UPLOAD_ACCESS_ERROR" => "Du har ikke tilladelse til at uploade filer.",
	"DELETE_FILE_CONFIRM_MSG" => "Er du sikker på du vil slette denne fil?",
	"NO_FILES_MSG" => "Ingen filer blev fundet",
	"SERIAL_GENERATE_MSG" => "Generer serienummer",
	"SERIAL_DONT_GENERATE_MSG" => "generer ikke ",
	"SERIAL_RANDOM_GENERATE_MSG" => "generere tilfældigt serienummer til software produktet",
	"SERIAL_FROM_PREDEFINED_MSG" => "få serienummer fra foruddefineret liste",
	"SERIAL_PREDEFINED_MSG" => "Foruddefinerede nummerering",
	"SERIAL_NUMBER_COLUMN" => "Serienummer",
	"SERIAL_USED_COLUMN" => "Brugt",
	"SERIAL_DELETE_COLUMN" => "Delete Slet",
	"SERIAL_MORE_MSG" => "Tilføj flere serienumre?",
	"SERIAL_PERIOD_MSG" => "Serienummer periode",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Vis vilkår & betingelser",
	"DOWNLOAD_SHOW_TERMS_DESC" => "For at hente denne vare skal brugeren læse og acceptere vores betingelser og vilkår",
	"DOWNLOAD_TERMS_USER_ERROR" => "For at downloade det produkt skal du læse og acceptere vores betingelser og vilkår",

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
