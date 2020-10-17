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
	"DOWNLOAD_WRONG_PARAM" => "Parâmetro(s) de download errado(s)",
	"DOWNLOAD_MISS_PARAM" => "Parâmetro(s) de download ausente(s)",
	"DOWNLOAD_INACTIVE" => "Download inativo",
	"DOWNLOAD_EXPIRED" => "Seu periodo de download expirou.",
	"DOWNLOAD_LIMITED" => "Você excedeu o número máximo de downloads",
	"DOWNLOAD_PATH_ERROR" => "O caminho para o produto não pôde ser encontrado",
	"DOWNLOAD_RELEASE_ERROR" => "Versão não encontrada",
	"DOWNLOAD_USER_ERROR" => "Somente usuàrios registrados podem baixar este arquivo",
	"ACTIVATION_OPTIONS_MSG" => "Opções de ativação",
	"ACTIVATION_MAX_NUMBER_MSG" => "Número máximo de ativações",
	"DOWNLOAD_OPTIONS_MSG" => "Opções de produto a baixar / software",
	"DOWNLOADABLE_MSG" => "Produto a baixar (software)",
	"DOWNLOADABLE_DESC" => "Para produtos a baixar, você pode especificar também \"Periodo de download\", \"Caminho para o arquivo a baixar\" e \"Opções de ativação\"",
	"DOWNLOAD_PERIOD_MSG" => "Periodo de download",
	"DOWNLOAD_PATH_MSG" => "Caminho para o arquivo a baixar",
	"DOWNLOAD_PATH_DESC" => "você pode adicionar vários caminhos divididos por ponto-e-vírgula",
	"UPLOAD_SELECT_MSG" => "Selecione a arquivo para upload e clique em {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "O arquivo <b>{filename}</b> foi transferido.",
	"UPLOAD_SELECT_ERROR" => "Por favor, selecione primeiro o arquivo.",
	"UPLOAD_IMAGE_ERROR" => "Somente arquivos de imagens são permitidos.",
	"UPLOAD_FORMAT_ERROR" => "Esse tipo de arquivo não é autorizado.",
	"UPLOAD_SIZE_ERROR" => "Arquivos maiores do que {filesize} não são permitidos.",
	"UPLOAD_DIMENSION_ERROR" => "Arquivos maiores do que {dimension} não são permitidos.",
	"UPLOAD_CREATE_ERROR" => "O sistema não pode criar o arquivo.",
	"UPLOAD_ACCESS_ERROR" => "Você não possui permissões para fazer upload de arquivos",
	"DELETE_FILE_CONFIRM_MSG" => "Você tem certeza que quer deletar esse arquivo?",
	"NO_FILES_MSG" => "Nenhum arquivo foi encontrado",
	"SERIAL_GENERATE_MSG" => "Gerar número de série",
	"SERIAL_DONT_GENERATE_MSG" => "Não gerar",
	"SERIAL_RANDOM_GENERATE_MSG" => "gerar número de série aleatório para produto de software",
	"SERIAL_FROM_PREDEFINED_MSG" => "obter número de série a partir de uma lista predefinida",
	"SERIAL_PREDEFINED_MSG" => "Números de série predefinidos",
	"SERIAL_NUMBER_COLUMN" => "Número de série",
	"SERIAL_USED_COLUMN" => "Utilizado",
	"SERIAL_DELETE_COLUMN" => "Deletar",
	"SERIAL_MORE_MSG" => "Adicionar mais números de série?",
	"SERIAL_PERIOD_MSG" => "Periodo de número de série",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Exibir Termos e Condições",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Para baixar o produto, o usuário deve ler e concordar com nosso termos e condições",
	"DOWNLOAD_TERMS_USER_ERROR" => "Para baixar o produto, você deve ler e concordar com nosso termos e condições",

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
