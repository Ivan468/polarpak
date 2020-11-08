<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// installation messages
	"INSTALL_TITLE" => "ViArt SHOP Kurulumu",

	"INSTALL_STEP_1_TITLE" => "Kurulum: Adım 1",
	"INSTALL_STEP_1_DESC" => "ViArt SHOP 'u seçtiğiniz için teşekkür ederiz. Kurulum işlemini tamamlamak için lütfen aşağıda istenen bilgileri doldurunuz. Unutmayınız ki seçtiğiniz veri tabanı  önceden kurulmuş olmalıdır.  MS Access gibi bir ODBC kullanan veri tabanı kuruyorsanız , işleme devam etmeden, öncelikle bir DSN oluşturmanız gerekir.",
	"INSTALL_STEP_2_TITLE" => "Kurulum: Adım 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Kurulum: Adım 3",
	"INSTALL_STEP_3_DESC" => "Lütfen bir site planı seçiniz, sonradan planı değiştirebilirsiniz.",
	"INSTALL_FINAL_TITLE" => "Kurulum: Son Adım",
	"SELECT_DATE_TITLE" => "Tarih Formatını Seç",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Veri tabanı Ayarları",
	"DB_PROGRESS_MSG" => "Database yapılandırma işlemi.",
	"SELECT_PHP_LIB_MSG" => "PHP Kitaplığı Seç",
	"SELECT_DB_TYPE_MSG" => "Veritabanı Tipi Seç",
	"ADMIN_SETTINGS_MSG" => "Yönetim Ayarları",
	"DATE_SETTINGS_MSG" => "Tarih Formatları",
	"NO_DATE_FORMATS_MSG" => "Mevcut tarih formatı yok",
	"INSTALL_FINISHED_MSG" => "Bu noktada ana kurulumunuz tamamlanmış bulunuyor. Lütfen yönetim bölümü ayarlarını kontrol ettiğinizden ve gerekli değişiklikleri yaptığınızdan emin olun.",
	"ACCESS_ADMIN_MSG" => "Yönetim bölümüne ulaşmak için buraya tıklayınız.",
	"ADMIN_URL_MSG" => "Yönetim URL",
	"MANUAL_URL_MSG" => "Kılavuz URL",
	"THANKS_MSG" => "<b>ViArt SHOP</b> u seçtiğiniz için teşekkür ederiz.",

	"DB_TYPE_FIELD" => "Veri tabanı tipi",
	"DB_TYPE_DESC" => "Kullandığınız <b>veritabanının</b>tipini seçiniz. SQL Server veya Microsoft Access kullanıyorsanız ODBC'yi seçin.",
	"DB_PHP_LIB_FIELD" => "PHP Kitaplığı",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "ViArt veritabanınızın çalışacağı sunucunun <b>ad</b>nı veya <b>IP adresi</b>ni giriniz. Veritabanınızı yerel PC'nizde çalıştıyorsanız, bunu \"<b>localhost</b>\" şeklinde ve portu da boş olarak bırakabilirsiniz. Hosting firmasının veritabanını kullanmanız halinde, sunucu ayarları için firmanın belgelerine başvurun.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Veritabanı Adı / DSN",
	"DB_NAME_DESC" => "MySQL veya PostgreSQL gibi bir veritabanı kullanıyorsanız, ViArt'ın tabloları oluşturmasını istediğiniz <b>veritabanının adı</>nı girin. Bu veritabanı mutlaka mevcut olmalıdır. Eğer ViArt'ı yerel PC'nize deneme amacıyla kuruyorsanız, çoğu sistemde hazır bulunan bir \"<b>test</b>\" veritabanının olmaması durumunda, \"viart\" adıyla bir tane oluşturun. Ama eğer Microsoft Access veya SQL Server kullanıyorsanız, bu durumda Veritabanının Adı, Kontrol Paneli'nin Veri kaynakları (ODBC) bölümünde kurmuş olduğunuz <b>DSN'nin adı</b> olmalıdır.",
	"DB_USER_FIELD" => "Kullanıcı Adı",
	"DB_PASS_FIELD" => "Şifre",
	"DB_USER_PASS_DESC" => "Veritabanı <b>Kullanıcı adı</b> ve <b>Şifresini</b> giriniz.",
	"DB_PERSISTENT_FIELD" => "Kalıcı (persistent) bağlantı",
	"DB_PERSISTENT_DESC" => "MySQL yahut Postgre gibi sürekli bağlantı kullanmak için kutuyu işaretleyin. Bu konuda bilginiz yoksa, kutuyu boş bırakın.",
	"DB_CREATE_DB_FIELD" => "Veritabanı Oluştur",
	"DB_CREATE_DB_DESC" => "Veritabanı oluşturmak mümkünse, bu kutuyu işaretleyin. Sadece MySQL ve PostgreSQL için geçerlidir.",
	"DB_POPULATE_FIELD" => "Populate DB",
	"DB_POPULATE_DESC" => "veritabanı tablo yapısını oluşturmak ve içine veri yazmak için kutuyu işaretleyin",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "veritabanınıza test verisi eklemek için kutuyu işaretleyin",
	"ADMIN_EMAIL_FIELD" => "Yönetici E-posta",
	"ADMIN_LOGIN_FIELD" => "Yönetici girişi",
	"ADMIN_PASS_FIELD" => "Admin Şifre",
	"ADMIN_CONF_FIELD" => "Şifre Doğrula",
	"DATETIME_SHOWN_FIELD" => "Tarih Zaman Formatı (sitede görülecek)",
	"DATE_SHOWN_FIELD" => "Tarih Formatı (sitede görülecek)",
	"DATETIME_EDIT_FIELD" => "Tarih Zaman Formatı (for editing)",
	"DATE_EDIT_FIELD" => "Tarih Formatı (for editing)",
	"DATE_FORMAT_COLUMN" => "Tarih Formatı",

	"DB_LIBRARY_ERROR" => "PHP fonksiyonları {db_library} için belirlenmemiş. Lütfen configuration file - php.ini database ayarlarını kontrol ediniz.",
	"DB_CONNECT_ERROR" => "Database 'e ulaşılamıyor. Lütfen database parametrelerini kontrol edip tekrar deneyiniz.",
	"INSTALL_FINISHED_ERROR" => "Kurulum tamamlandı.",
	"WRITE_FILE_ERROR" => "<b>'includes/var_definition.php'</b> dosyası yazılabilir değil. Devam etmeden önce CHMOD ayarlarını düzeltiniz.",
	"WRITE_DIR_ERROR" => "<b>'includes'</b> klasörü yazılabilir değil. Devam etmeden önce CHMOD ayarlarını düzeltiniz.",
	"DUMP_FILE_ERROR" => "{file_name}' bulunamadı.",
	"DB_TABLE_ERROR" => "{table_name}' bulunamadı. Lütfen veritabanına gerekli veriyi yerleştiriniz.",
	"TEST_DATA_ERROR" => "Test verileri olan tabloları yayınlamadan önce <b>{POPULATE_DB_FIELD}</b>'yi kontrol edin",
	"DB_HOST_ERROR" => "Vermiş olduğunuz host adresi bulunamadı",
	"DB_PORT_ERROR" => "Belirtilen porttaki veritabanı sunucusuna bağlanılamadı",
	"DB_USER_PASS_ERROR" => "Belirtilen kullanıcı adı veya şifre hatalı",
	"DB_NAME_ERROR" => "Bağlantı ayarları doğru fakat '{db_name}' veritabanı bulunamadı",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Güncelleme",
	"UPGRADE_NOTE" => "Uyarı: Lütfen işleme başlamadan önce veritabanının yedeğini aldığınızdan emin olunuz.",
	"UPGRADE_AVAILABLE_MSG" => "Yeni Güncelleme Mevcut",
	"UPGRADE_BUTTON" => "{version_number} a yükselt",
	"CURRENT_VERSION_MSG" => "Kurulu Versiyon",
	"LATEST_VERSION_MSG" => "Kurulabilir Version",
	"UPGRADE_RESULTS_MSG" => "Yükseltme Sonuçları",
	"SQL_SUCCESS_MSG" => "SQL sorgusu başarılı",
	"SQL_FAILED_MSG" => "SQL sorgusu başarısız",
	"SQL_TOTAL_MSG" => "Yürütülen toplam SQL sorgusu",
	"VERSION_UPGRADED_MSG" => "Ürün versiyonu yükseltilmiştir.",
	"ALREADY_LATEST_MSG" => "Ürünün son versiyonu zaten yüklü.",
	"DOWNLOAD_NEW_MSG" => "Yeni versiyon bulundu",
	"DOWNLOAD_NOW_MSG" => "{version_number} 'ı indir",
	"DOWNLOAD_FOUND_MSG" => "Yüklenebilir yeni bir versiyon var {version_number}. Lütfen indirmek için tıklayınız. Dosyaları indirip sunucunuza yükledikten sonra yükseltme butonuna (upgrade) tıklamayı unutmayın.",
	"NO_XML_CONNECTION" => "Dikkat 'http://www.viart.com/' bağlanılamıyor!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
