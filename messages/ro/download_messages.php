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
	// mesaje descarcare
	"DOWNLOAD_WRONG_PARAM" => "Parametru(i) descarcare gresit(i).",
	"DOWNLOAD_MISS_PARAM" => "Lipsa parametru(i) descarcare.",
	"DOWNLOAD_INACTIVE" => "Descarcare inactiva.",
	"DOWNLOAD_EXPIRED" => "Perioada de descarcare a expirat.",
	"DOWNLOAD_LIMITED" => "Ati depasit numarul maxim de descarcari.",
	"DOWNLOAD_PATH_ERROR" => "Calea spre produs nu poate fi gasita.",
	"DOWNLOAD_RELEASE_ERROR" => "Versiunea nu a fost gasita.",
	"DOWNLOAD_USER_ERROR" => "Numai utilizatorii inregistrati pot descarca acest fiser.",
	"ACTIVATION_OPTIONS_MSG" => "Optiuni de activare.",
	"ACTIVATION_MAX_NUMBER_MSG" => "Numar maxim de activari",
	"DOWNLOAD_OPTIONS_MSG" => "Optiuni intangibile/software",
	"DOWNLOADABLE_MSG" => "Intangibile (Software)",
	"DOWNLOADABLE_DESC" => "pentru produse intangibile puteti specifica de asemenea 'Perioada de descarcare', 'Calea spre fisierul descarcabil' si 'Optiuni activare'",
	"DOWNLOAD_PERIOD_MSG" => "Perioada descarcare",
	"DOWNLOAD_PATH_MSG" => "Calea spre fisierul descarcabil",
	"DOWNLOAD_PATH_DESC" => "Puteti adauga cai multiple separate prin punct si virgula",
	"UPLOAD_SELECT_MSG" => "Selectati fisierul de incarcat si apasati butonul {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Fisierul <b>{filename}</b> a fost incarcat.",
	"UPLOAD_SELECT_ERROR" => "Va rugam sa selectati un fisier mai intai.",
	"UPLOAD_IMAGE_ERROR" => "Numai fisierele de tip imagine sunt permise.",
	"UPLOAD_FORMAT_ERROR" => "Acest tip de fisier nu este permis.",
	"UPLOAD_SIZE_ERROR" => "Fisierele mai mari decat {filesize} nu sunt permise.",
	"UPLOAD_DIMENSION_ERROR" => "Imaginile mai mari decat {dimension} nu sunt permise.",
	"UPLOAD_CREATE_ERROR" => "Sistemul nu poate crea fisierul.",
	"UPLOAD_ACCESS_ERROR" => "Nu aveti permisiunea de a incarca fisiere.",
	"DELETE_FILE_CONFIRM_MSG" => "Sunteti sigur ca vreti sa stergeti acest fisier?",
	"NO_FILES_MSG" => "Nici un fisier nu a fost gasit",
	"SERIAL_GENERATE_MSG" => "Genereaza Cod de Activare",
	"SERIAL_DONT_GENERATE_MSG" => "nu genera",
	"SERIAL_RANDOM_GENERATE_MSG" => "Genereaza cod de activare aleatoriu pentru produs software",
	"SERIAL_FROM_PREDEFINED_MSG" => "obtine codul de activare al produsului din lista predefinita",
	"SERIAL_PREDEFINED_MSG" => "Coduri de activare predefinite",
	"SERIAL_NUMBER_COLUMN" => "Cod de activare",
	"SERIAL_USED_COLUMN" => "Folosit",
	"SERIAL_DELETE_COLUMN" => "Sterge",
	"SERIAL_MORE_MSG" => "Adaugati mai multe coduri de activare?",
	"SERIAL_PERIOD_MSG" => "Perioada cod de activare",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Afiseaza Termeni & Conditii",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Pentru a descarca produsul utilizatorul trebuie sa citeasca si sa fie de acord cu termenii si conditiile noastre",
	"DOWNLOAD_TERMS_USER_ERROR" => "Pentru a descarca acest produs trebuie sa cititi si sa fiti de acord cu termenii si conditiile noastre",

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
