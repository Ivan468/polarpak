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
	"DOWNLOAD_WRONG_PARAM" => "Parametri di download errati",
	"DOWNLOAD_MISS_PARAM" => "Parametri download mancanti",
	"DOWNLOAD_INACTIVE" => "Download non attivi.",
	"DOWNLOAD_EXPIRED" => "Il tuo periodo di download e' scaduto",
	"DOWNLOAD_LIMITED" => "Hai superato il massimo numero di download",
	"DOWNLOAD_PATH_ERROR" => "percorso al prodotto non trovato",
	"DOWNLOAD_RELEASE_ERROR" => "Versione non trovata",
	"DOWNLOAD_USER_ERROR" => "Solo gli utenti registrati possono scaricare questo file.",
	"ACTIVATION_OPTIONS_MSG" => "Opzioni di attivazione",
	"ACTIVATION_MAX_NUMBER_MSG" => "Massimo numero di attivazioni",
	"DOWNLOAD_OPTIONS_MSG" => "Opzioni software scaricabile",
	"DOWNLOADABLE_MSG" => "Software scaricabile",
	"DOWNLOADABLE_DESC" => "Per i prodotti software scaricabili si puo' anche specificare 'Periodo di download', 'Percorso al file scaricabile' ed 'Opzioni di attivazione'",
	"DOWNLOAD_PERIOD_MSG" => "Periodo di download",
	"DOWNLOAD_PATH_MSG" => "Path del file da scaricare",
	"DOWNLOAD_PATH_DESC" => "Puoi aggiungere path multipli separati da \"punti e vigola\"",
	"UPLOAD_SELECT_MSG" => "Seleziona il file da inviare e premi il bottone {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Il file <b>{filename}</b> e' stato inviato.",
	"UPLOAD_SELECT_ERROR" => "Seleziona prima un file. ",
	"UPLOAD_IMAGE_ERROR" => "Solo le immagini sono permesse.",
	"UPLOAD_FORMAT_ERROR" => "Questo tipo di file non e' permesso.",
	"UPLOAD_SIZE_ERROR" => "I files piu' grandi di {filesize} non sono permessi.",
	"UPLOAD_DIMENSION_ERROR" => "Le immagini piu' grandi di {dimension} non sono permesse.",
	"UPLOAD_CREATE_ERROR" => "Il sistema non puo' creare questo file.",
	"UPLOAD_ACCESS_ERROR" => "Tu non hai i permessi per inviare questi files.",
	"DELETE_FILE_CONFIRM_MSG" => "Sei sicuro di voler cancellare questo file?",
	"NO_FILES_MSG" => "Nessun file e' stato trovato",
	"SERIAL_GENERATE_MSG" => "Genera Numero di serie",
	"SERIAL_DONT_GENERATE_MSG" => "Non generare",
	"SERIAL_RANDOM_GENERATE_MSG" => "genera un numero seriale casuale per questo prodotto software",
	"SERIAL_FROM_PREDEFINED_MSG" => "Ottieni un numero seriale tra quelli predefiniti",
	"SERIAL_PREDEFINED_MSG" => "Numeri di serie predefiniti",
	"SERIAL_NUMBER_COLUMN" => "Numeri di Serie",
	"SERIAL_USED_COLUMN" => "Usati",
	"SERIAL_DELETE_COLUMN" => "Cancellati",
	"SERIAL_MORE_MSG" => "Aggiungere numeri di serie aggiuntivi ?",
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
