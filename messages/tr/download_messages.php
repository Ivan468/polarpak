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
	"DOWNLOAD_WRONG_PARAM" => "Yanlış download parametresi.",
	"DOWNLOAD_MISS_PARAM" => "Eksik download parametresi",
	"DOWNLOAD_INACTIVE" => "Download aktif değil.",
	"DOWNLOAD_EXPIRED" => "Download süresi dolmuş.",
	"DOWNLOAD_LIMITED" => "Azami download etme sayısını doldurmuşsunuz.",
	"DOWNLOAD_PATH_ERROR" => "Yazdığınız ürün adresi bulunamadı.",
	"DOWNLOAD_RELEASE_ERROR" => "Model bulunamadı.",
	"DOWNLOAD_USER_ERROR" => "Bu dosyayı sadece kayıtlı üyeler indirebilir.",
	"ACTIVATION_OPTIONS_MSG" => "Etkinleştirme Seçenekleri",
	"ACTIVATION_MAX_NUMBER_MSG" => "Azami Etkinleştirme Sayısı",
	"DOWNLOAD_OPTIONS_MSG" => "İndirilebilir / Yazılım Seçenekleri",
	"DOWNLOADABLE_MSG" => "İndirilebilir (Yazılım)",
	"DOWNLOADABLE_DESC" => "İndirilebilir ürün için aynı zamanda 'İndirme Süresi', 'İndirilebilir Dosyanın Yolu' ve 'Aktivasyon Seçenekleri'ni belirtebilirsiniz",
	"DOWNLOAD_PERIOD_MSG" => "İndirme Süresi",
	"DOWNLOAD_PATH_MSG" => "İndirilebilir Dosyaya ulaşım",
	"DOWNLOAD_PATH_DESC" => "Noktalı virgül ile ayırarak birden çok yol girebilirsiniz.",
	"UPLOAD_SELECT_MSG" => "Yüklenecek dosyayı seçip {button_name} butonuna tıklayınız.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "<b>{filename}</b> dosyası yüklendi.",
	"UPLOAD_SELECT_ERROR" => "Lütfen önce bir dosya seçiniz.",
	"UPLOAD_IMAGE_ERROR" => "Sadece resim dosyaları yüklenebilir.",
	"UPLOAD_FORMAT_ERROR" => "Bu tür dosya yükleme izni yoktur.",
	"UPLOAD_SIZE_ERROR" => "{filesize} ndan geniş dosyaları yükleme izni yoktur.",
	"UPLOAD_DIMENSION_ERROR" => "{dimension} den daha geniş resimleri yükleme izni yoktur.",
	"UPLOAD_CREATE_ERROR" => "Sistem dosyayı oluşturamadı",
	"UPLOAD_ACCESS_ERROR" => "Dosya yükleme izniniz yok",
	"DELETE_FILE_CONFIRM_MSG" => "Bu dosyayı silmek istediğinizden emin misiniz?",
	"NO_FILES_MSG" => "Dosya bulunamadı",
	"SERIAL_GENERATE_MSG" => "Seri Numarası Üret",
	"SERIAL_DONT_GENERATE_MSG" => "üretme",
	"SERIAL_RANDOM_GENERATE_MSG" => "Yazılım ürünü için rasgele seri numarası üret",
	"SERIAL_FROM_PREDEFINED_MSG" => "Tanımlanmış listeden seri numarası al",
	"SERIAL_PREDEFINED_MSG" => "Tanımlanmış seri numaraları",
	"SERIAL_NUMBER_COLUMN" => "Seri Numarası",
	"SERIAL_USED_COLUMN" => "Kullanılmış",
	"SERIAL_DELETE_COLUMN" => "Sil",
	"SERIAL_MORE_MSG" => "Daha çok seri numarası eklensin mi?",
	"SERIAL_PERIOD_MSG" => "seri numarası aralığı",
	"DOWNLOAD_SHOW_TERMS_MSG" => "şart ve Koşulları göster",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Yazılımı indirebilmesi için kullanıcının şart ve koşulları okumuş ve onaylıyor olması gerekiyor",
	"DOWNLOAD_TERMS_USER_ERROR" => "Ürünü indirebilmek için şartlarımızı ve koşullarımızı okumuş ve onaylıyor olmanız gerekiyor",

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
