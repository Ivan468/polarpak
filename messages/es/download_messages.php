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
	// mensajes de la descarga
	"DOWNLOAD_WRONG_PARAM" => "El(Los) parametro(s) de la descarga es(son) erroneo(s).",
	"DOWNLOAD_MISS_PARAM" => "Falta el(los) parametro(s) de la descarga.",
	"DOWNLOAD_INACTIVE" => "La descarga no esta activa.",
	"DOWNLOAD_EXPIRED" => "Su periodo de descarga ha expirado.",
	"DOWNLOAD_LIMITED" => "Ha sobrepasado la cantidad máxima de descargas.",
	"DOWNLOAD_PATH_ERROR" => "No se puede encontrar la ruta al producto.",
	"DOWNLOAD_RELEASE_ERROR" => "El nuevo producto no se ha encontrado.",
	"DOWNLOAD_USER_ERROR" => "Solo Usuarios Registrados pueden descargar este producto.",
	"ACTIVATION_OPTIONS_MSG" => "Opciones de Activación",
	"ACTIVATION_MAX_NUMBER_MSG" => "Max número de activaciones",
	"DOWNLOAD_OPTIONS_MSG" => "Descargable/Opciones de Software",
	"DOWNLOADABLE_MSG" => "Descargable (Software)",
	"DOWNLOADABLE_DESC" => "Para descargar el producto, puede también especificar 'Período de Descarga', 'Ruta a el Archivo Descargable' y 'Opciones de Activaciones'",
	"DOWNLOAD_PERIOD_MSG" => "Período de Descarga",
	"DOWNLOAD_PATH_MSG" => "Ruta a el Archivo Descargable",
	"DOWNLOAD_PATH_DESC" => "Puede añadir multiples rutas separadas por coma",
	"UPLOAD_SELECT_MSG" => "Seleccione un archivo a subir y pulse el botón {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "El archivo <b>{filename}</b> fue subido.",
	"UPLOAD_SELECT_ERROR" => "Por favor seleccione primero un archivo.",
	"UPLOAD_IMAGE_ERROR" => "Sólo se permiten archivos de imágenes.",
	"UPLOAD_FORMAT_ERROR" => "Este tipo de archivo no está permitido.",
	"UPLOAD_SIZE_ERROR" => "Archivos mayores de {filesize} no están permitidos.",
	"UPLOAD_DIMENSION_ERROR" => "Imágenes mayores de {dimension} no están permitidas.",
	"UPLOAD_CREATE_ERROR" => "El sistema no puede crear el archivo.",
	"UPLOAD_ACCESS_ERROR" => "No tiene permisos para subir archivos.",
	"DELETE_FILE_CONFIRM_MSG" => "Está seguro de que desea borrar este archivo?",
	"NO_FILES_MSG" => "No se encontraron archivos",
	"SERIAL_GENERATE_MSG" => "Generar Número de Serie",
	"SERIAL_DONT_GENERATE_MSG" => "No generar",
	"SERIAL_RANDOM_GENERATE_MSG" => "generar serial de producto al azar de Software",
	"SERIAL_FROM_PREDEFINED_MSG" => "Obtener el número de serie de lista predefinida",
	"SERIAL_PREDEFINED_MSG" => "Números de Serie Predefinidos",
	"SERIAL_NUMBER_COLUMN" => "Número de Serie",
	"SERIAL_USED_COLUMN" => "Usado",
	"SERIAL_DELETE_COLUMN" => "Eliminar",
	"SERIAL_MORE_MSG" => "Añadir más números de Serie?",
	"SERIAL_PERIOD_MSG" => "Período de Número de Serie",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Mostrar Términos y Condiciones",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Para descargar el producto, el usuario ha de leer y aceptar nuestros términos y condiciones",
	"DOWNLOAD_TERMS_USER_ERROR" => "Para descargar el producto, usted tiene que leer y aceptar nuestros términos y condiciones",

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
