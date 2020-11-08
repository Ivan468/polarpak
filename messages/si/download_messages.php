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
	"DOWNLOAD_WRONG_PARAM" => "Napačno določeni parametri prenosa",
	"DOWNLOAD_MISS_PARAM" => "Pomanjkanje parametra (ov) prenosa.",
	"DOWNLOAD_INACTIVE" => "Prenos neaktiven",
	"DOWNLOAD_EXPIRED" => "Vaše obdobje nalaganja je poteklo.",
	"DOWNLOAD_LIMITED" => "Presegli ste največje dovoljeno število prenosov..",
	"DOWNLOAD_PATH_ERROR" => "Pot datoteke ni bilo mogoče najti.",
	"DOWNLOAD_RELEASE_ERROR" => "Sprostitve ni mogoče najti.",
	"DOWNLOAD_USER_ERROR" => "To datoteko lahko prenesejo samo registrirani uporabniki..",
	"ACTIVATION_OPTIONS_MSG" => "Opcije aktivacije",
	"ACTIVATION_MAX_NUMBER_MSG" => "Največja količina aktivacij",
	"DOWNLOAD_OPTIONS_MSG" => "Opcije digitalnih izdelkov",
	"DOWNLOADABLE_MSG" => "Digitalni izdelki",
	"DOWNLOADABLE_DESC" => "za digitalne izdelke lahko izberete 'Obdobje prenosa', 'Digitalna pot izdelka' in 'Opcije aktivacije",
	"DOWNLOAD_PERIOD_MSG" => "Obdobje nalaganja",
	"DOWNLOAD_PATH_MSG" => "Pot do digitalnega blaga",
	"DOWNLOAD_PATH_DESC" => "Več poti lahko dodate tako, da jih ločite s podpičjem",
	"UPLOAD_SELECT_MSG" => "Izberite datoteko, ki jo želite naložiti, in kliknite {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Ali pa določite pot do datoteke in pritisnite gumb 'Nadaljujte'.",
	"UPLOADED_FILE_MSG" => "Datoteka <b> {filename} </b> je naložen.",
	"UPLOAD_SELECT_ERROR" => "Najprej izberite datoteko, prosim.",
	"UPLOAD_IMAGE_ERROR" => "Dovoljene so samo slikovne datoteke.",
	"UPLOAD_FORMAT_ERROR" => "Ta vrsta datoteke ni veljavna.",
	"UPLOAD_SIZE_ERROR" => "Datoteke, večje od {filesize}, niso dovoljene..",
	"UPLOAD_DIMENSION_ERROR" => "Slike, večje od {dimension}, niso dovoljene.dimension.",
	"UPLOAD_CREATE_ERROR" => "Sistem ne more ustvariti datoteke..",
	"UPLOAD_ACCESS_ERROR" => "Nimate dovoljenja za nalaganje datotek",
	"DELETE_FILE_CONFIRM_MSG" => "Resnično izbrisati to datoteko?",
	"NO_FILES_MSG" => "Nobenih datotek ni bilo mogoče najti",
	"SERIAL_GENERATE_MSG" => "Ustvariti serijsko številko",
	"SERIAL_DONT_GENERATE_MSG" => "ne ustvariti",
	"SERIAL_RANDOM_GENERATE_MSG" => "ustvariti naključno serijsko številko za digitalni predmet",
	"SERIAL_FROM_PREDEFINED_MSG" => "vzemiti serijsko številko s pripravljenega seznama",
	"SERIAL_PREDEFINED_MSG" => "Pripravljene serijske številke",
	"SERIAL_NUMBER_COLUMN" => "Serijska številka",
	"SERIAL_USED_COLUMN" => "Rabljeni",
	"SERIAL_DELETE_COLUMN" => "Izbrisati",
	"SERIAL_MORE_MSG" => "Dodati serijske številke?",
	"SERIAL_PERIOD_MSG" => "Obdobje serijske številke",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Prikazati Pogoje in Sporazumi",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Za prenos izdelka mora uporabnik prebrati in potrditi Pogoje in Sporazumi",
	"DOWNLOAD_TERMS_USER_ERROR" => "Če želite prenesti izdelek, morate prebrati in potrditi Pogoje in Sporazumi",

	"DOWNLOAD_TITLE_MSG" => "Ime prenosa",
	"DOWNLOADABLE_FILES_MSG" => "Nalaganje datotek",
	"DOWNLOAD_INTERVAL_MSG" => "Interval prenosa",
	"DOWNLOAD_LIMIT_MSG" => "Omejitev nalaganja",
	"DOWNLOAD_LIMIT_DESC" => "kolikokrat lahko prenesem datoteko",
	"MAXIMUM_DOWNLOADS_MSG" => "Največje število prenosov",
	"PREVIEW_TYPE_MSG" => "Vrsta predogleda",
	"PREVIEW_TITLE_MSG" => "Naslov predogleda",
	"PREVIEW_PATH_MSG" => "Datoteka predogleda",
	"PREVIEW_IMAGE_MSG" => "Slika za predogled",
	"MORE_FILES_MSG" => "več datotek",
	"UPLOAD_MSG" => "Naložiti",
	"USE_WITH_OPTIONS_MSG" => "Predogled",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Preview as download",
	"PREVIEW_USE_PLAYER_MSG" => "Use player to preview",
	"PROD_PREVIEWS_MSG" => "Previews",

);
$va_messages = array_merge($va_messages, $messages);
