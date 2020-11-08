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
	"DOWNLOAD_WRONG_PARAM" => "Fel nerladdningsparameter(s).",
	"DOWNLOAD_MISS_PARAM" => "Saknade nerladdningsparameter(s).",
	"DOWNLOAD_INACTIVE" => "Nerladdning inaktiv.",
	"DOWNLOAD_EXPIRED" => "Din nerladdningtid har gått ut.",
	"DOWNLOAD_LIMITED" => "Du har gått över antalet tillåtna nerladdningar.",
	"DOWNLOAD_PATH_ERROR" => "Sökväg till produkten kan inte hittas.",
	"DOWNLOAD_RELEASE_ERROR" => "Utgåvan hittades inte.",
	"DOWNLOAD_USER_ERROR" => "Endast registrerade användare kan ladda ner denna fil.",
	"ACTIVATION_OPTIONS_MSG" => "Aktiveringsval",
	"ACTIVATION_MAX_NUMBER_MSG" => "Högst antal aktiveringar",
	"DOWNLOAD_OPTIONS_MSG" => "Nedladdningsbart / mjukvaruval",
	"DOWNLOADABLE_MSG" => "Nedladdningsbart (mjukvara)",
	"DOWNLOADABLE_DESC" => "För nedladdningsbar produkt kan du också specificera 'nedladdningsperiod', 'Sökväg till nedladdningsbar fil' och 'Aktiveringsval'",
	"DOWNLOAD_PERIOD_MSG" => "Nedladdningsperiod",
	"DOWNLOAD_PATH_MSG" => "Sökväg till nedladdningsbar fil",
	"DOWNLOAD_PATH_DESC" => "Du kan lägga till flera sökvägar genom att åtskilja dem med semikolon.",
	"UPLOAD_SELECT_MSG" => "Välj fil att ladda upp och klicka på  {button_name}-knappen.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Filen <b>{filename}</b> har laddats upp.",
	"UPLOAD_SELECT_ERROR" => "Var vänlig välj en fil först.",
	"UPLOAD_IMAGE_ERROR" => "Endast bilder är tillåtna.",
	"UPLOAD_FORMAT_ERROR" => "Den här filtypen är inte tillåten.",
	"UPLOAD_SIZE_ERROR" => "Filer större än {filesize} är inte tillåtna.",
	"UPLOAD_DIMENSION_ERROR" => "Bilder större än {dimension} är inte tillåtna.",
	"UPLOAD_CREATE_ERROR" => "Systemet kan inte skapa filen.",
	"UPLOAD_ACCESS_ERROR" => "Du har inte tillåtelse att ladda upp filer.",
	"DELETE_FILE_CONFIRM_MSG" => "Är du säker på att du vill radera den här filen?",
	"NO_FILES_MSG" => "Inga filer hittades.",
	"SERIAL_GENERATE_MSG" => "Skapa serienummer.",
	"SERIAL_DONT_GENERATE_MSG" => "Skapa inte",
	"SERIAL_RANDOM_GENERATE_MSG" => "Skapa slumpat serienummer för mjukvaruprodukt",
	"SERIAL_FROM_PREDEFINED_MSG" => "Hämta serienummer från fördefinierad lista.",
	"SERIAL_PREDEFINED_MSG" => "Fördefinierade serienummer",
	"SERIAL_NUMBER_COLUMN" => "Serienummer",
	"SERIAL_USED_COLUMN" => "Använd",
	"SERIAL_DELETE_COLUMN" => "Raderad",
	"SERIAL_MORE_MSG" => "Lägg till fler serienummer?",
	"SERIAL_PERIOD_MSG" => "Serienummerperiod",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Visa allmänna villkor",
	"DOWNLOAD_SHOW_TERMS_DESC" => "För att kunna ladda ner produkten måste kunden godkänna våra allmänna villkor.",
	"DOWNLOAD_TERMS_USER_ERROR" => "För att kunna ladda ner produkten måste du godkänna våra allmänna villkor.",

	"DOWNLOAD_TITLE_MSG" => "Download Title",
	"DOWNLOADABLE_FILES_MSG" => "Downloadable Files",
	"DOWNLOAD_INTERVAL_MSG" => "Download Interval",
	"DOWNLOAD_LIMIT_MSG" => "Downloads Limit",
	"DOWNLOAD_LIMIT_DESC" => "Antal gånger som filen kan laddas ner",
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
