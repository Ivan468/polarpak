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
	"DOWNLOAD_WRONG_PARAM" => "Pogrešan parametar(i) preuzimanja.",
	"DOWNLOAD_MISS_PARAM" => "Nedostaje parametar(i) preuzimanja.",
	"DOWNLOAD_INACTIVE" => "Neaktivno preuzimanje",
	"DOWNLOAD_EXPIRED" => "Isteklo je vaše vrijeme za preuizmanje",
	"DOWNLOAD_LIMITED" => "Prekoračili ste maksimalni broj preuzimanja.",
	"DOWNLOAD_PATH_ERROR" => "Nemože se pronaći putanja do proizvoda",
	"DOWNLOAD_RELEASE_ERROR" => "Izdanje nije pronađeno",
	"DOWNLOAD_USER_ERROR" => "Samo registrirani korisnici mogu preuzeti ovu datoteku.",
	"ACTIVATION_OPTIONS_MSG" => "Aktivacija opcije",
	"ACTIVATION_MAX_NUMBER_MSG" => "Najveći broj Aktivacije",
	"DOWNLOAD_OPTIONS_MSG" => "Opcije Preuzimanja/Software-a",
	"DOWNLOADABLE_MSG" => "Preuzimanje (software)",
	"DOWNLOADABLE_DESC" => "Za preuzimanje proizvod također možete odrediti \" Razdoblje Preuzimanja\", \"Put do Preuzimanja datoteka ' i ' Opcije Aktivacije '",
	"DOWNLOAD_PERIOD_MSG" => "Razdoblje preuzimanja",
	"DOWNLOAD_PATH_MSG" => "Putanja do proizvoda za preuzimanje",
	"DOWNLOAD_PATH_DESC" => "Možete dodati više putanja podijeljenih  točka-zarezom",
	"UPLOAD_SELECT_MSG" => "Odaberite datoteku za učitavanje, a zatim pritisnite gumb {button_name}",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Datoteka <b>{filename}</b> je učitana",
	"UPLOAD_SELECT_ERROR" => "Prvo odaberite datoteku",
	"UPLOAD_IMAGE_ERROR" => "Samo slikovne datoteke su dopuštene.",
	"UPLOAD_FORMAT_ERROR" => "Ova vrsta datoteke nije dopuštena.",
	"UPLOAD_SIZE_ERROR" => "Datoteke veće od {filesize} nije dopuštena.",
	"UPLOAD_DIMENSION_ERROR" => "Slike veće od {dimensions} nisu dopuštene.",
	"UPLOAD_CREATE_ERROR" => "Sustav ne može stvoriti datoteku.",
	"UPLOAD_ACCESS_ERROR" => "Nemate dozvolu za učitavanje  datoteka.",
	"DELETE_FILE_CONFIRM_MSG" => "Jeste li sigurni da želite izbrisati ovu datoteku?",
	"NO_FILES_MSG" => "Nema pronađenih datoteka",
	"SERIAL_GENERATE_MSG" => "Generiraj serijski broj",
	"SERIAL_DONT_GENERATE_MSG" => "Ne generiraju",
	"SERIAL_RANDOM_GENERATE_MSG" => "Generiranje slučajnih serijski za softverski proizvod",
	"SERIAL_FROM_PREDEFINED_MSG" => "Dobiji serijski broj iz popisa predefinirane liste",
	"SERIAL_PREDEFINED_MSG" => "Unaprijed definirani serijski brojevi",
	"SERIAL_NUMBER_COLUMN" => "Serijski Broj",
	"SERIAL_USED_COLUMN" => "Korišten",
	"SERIAL_DELETE_COLUMN" => "Izbrisati",
	"SERIAL_MORE_MSG" => "Dodajte više serijskih  brojeva",
	"SERIAL_PERIOD_MSG" => "Razdoblje Serijskog Broja",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Prikaži Uvjete i Pravila",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Za preuzimanje proizvoda korisnik se treba složiti s uvjetima korištenja",
	"DOWNLOAD_TERMS_USER_ERROR" => "Da biste preuzeli proizvod morate pročitati i prihvatiti naše Uvjete i pravila",

	"DOWNLOAD_TITLE_MSG" => "Naslov Preuzimanja",
	"DOWNLOADABLE_FILES_MSG" => "Datoteke za Preuzimanje",
	"DOWNLOAD_INTERVAL_MSG" => "Interval preuzimanja",
	"DOWNLOAD_LIMIT_MSG" => "Granica preuzimanja",
	"DOWNLOAD_LIMIT_DESC" => "Broj koliko se puta datoteka može preuzeti",
	"MAXIMUM_DOWNLOADS_MSG" => "Maksimalna Preuzimanja",
	"PREVIEW_TYPE_MSG" => "Tip pregleda",
	"PREVIEW_TITLE_MSG" => "Pregled naslova",
	"PREVIEW_PATH_MSG" => "Putanja za pregled datoteka",
	"PREVIEW_IMAGE_MSG" => "Prikaz slike",
	"MORE_FILES_MSG" => "Više datoteka",
	"UPLOAD_MSG" => "Učitaj",
	"USE_WITH_OPTIONS_MSG" => "Koristite samo s opcijama",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Pregled kao preuzimanje",
	"PREVIEW_USE_PLAYER_MSG" => "Koristite player za pregled",
	"PROD_PREVIEWS_MSG" => "Pregled",

);
$va_messages = array_merge($va_messages, $messages);
