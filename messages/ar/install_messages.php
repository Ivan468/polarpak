<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// installation messages
	"INSTALL_TITLE" => "للتسوق ViArt تنصيب برنامج",

	"INSTALL_STEP_1_TITLE" => "التنصيب: المرحله الأولى",
	"INSTALL_STEP_1_DESC" => "للتسوق. لكي تكمل عملية التنصيب, الرجاء ملئ الفراغات المطلوبه في الأسفل. الرجاء ملاحظة انه يجب ان تكون هناك قاعدة بيانات موجوده للإستمرار.  اذا كنت تريد ViArt شكراً لك لاختيار برنامج .له قبل الإستمرار DSN يجب ان تنشء أولاً MS Access :مثل ODCB تنصيب البرنامج على قاعدة بيانات تستخدم",
	"INSTALL_STEP_2_TITLE" => "التنصيب: المرحله الثانيه",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "التنصيب: المرحله الثالثه",
	"INSTALL_STEP_3_DESC" => "الرجاء اختيار الشكل العام للموقع, يمكنك تغييره لاحقاً",
	"INSTALL_FINAL_TITLE" => "التنصيب: المرحله الأخيره",
	"SELECT_DATE_TITLE" => "اختر شكل عرض التاريخ",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "خيارات قاعدة البيانات",
	"DB_PROGRESS_MSG" => "Populating database structure progress",
	"SELECT_PHP_LIB_MSG" => "PHP اختر نوع مكتبة الـ",
	"SELECT_DB_TYPE_MSG" => "اختر نوع قاعدة البيانات",
	"ADMIN_SETTINGS_MSG" => "خيارات التحكم",
	"DATE_SETTINGS_MSG" => "اشكال عرض التاريخ",
	"NO_DATE_FORMATS_MSG" => "لا توجد اشكال للتاريخ",
	"INSTALL_FINISHED_MSG" => "الآن و في هذه المرحله, لقد قمت بتنصيب البرنامج بنجاح. الرجاء الثهاب لقسم التحكم للإداره للقيام بأية تعديلات مطلوبه",
	"ACCESS_ADMIN_MSG" => "للذهاب للقسم الإداري اضغط هنا",
	"ADMIN_URL_MSG" => "رابط الإداره",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => ".للتسوق <b>ViArt</b> شكراً لك لاختيار برنامج",

	"DB_TYPE_FIELD" => "نوع قاعدة البيانات",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP مكتبة الـ",
	"DB_HOST_FIELD" => "(HostName) المستضيف",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "(Port) المنفذ",
	"DB_NAME_FIELD" => "اسم قاعدة البيانات",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "اسم المستخدم",
	"DB_PASS_FIELD" => "كلمة المرور",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "الإتصال الدائم",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "نشر قاعدة البيانات",
	"DB_POPULATE_DESC" => "لإنشاء طاولة المعلومات و نشرها مع البيانات, اشر هنا",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "بريد الإداره",
	"ADMIN_LOGIN_FIELD" => "اسم المستخدم للمدير",
	"ADMIN_PASS_FIELD" => "كلمة المرور",
	"ADMIN_CONF_FIELD" => "تأكيد كلمة المرور",
	"DATETIME_SHOWN_FIELD" => "(شكل عرض الوقت(للموقع",
	"DATE_SHOWN_FIELD" => "(شكل عرض التاريخ (للموقع",
	"DATETIME_EDIT_FIELD" => "(شكل عرض الوقت (للتحرير",
	"DATE_EDIT_FIELD" => "(شكل عرض التاريخ (للتحرير",
	"DATE_FORMAT_COLUMN" => "شكل عرض التاريخ",

	"DB_LIBRARY_ERROR" => "PHP functions for {db_library} are not defined. Please check your database settings in your configuration file - php.ini.",
	"DB_CONNECT_ERROR" => "لم نستطع الإتصال بقاعدة البيانات, الرجاء التأكد من معلومات قاعدة بياناتك",
	"INSTALL_FINISHED_ERROR" => ".تم الإنتهاء من عملية التنصيب مسبقاً",
	"WRITE_FILE_ERROR" => ".الرجاء تعديل الصلاحيات قبل الاستمرار .<b>'includes/var_definition.php'</b> لا تملك صلاحيات الكتابه للملف",
	"WRITE_DIR_ERROR" => ".الرجاء تعديل الصلاحيات قبل الاستمرار .<b>'includes/'</b> لا تملك صلاحيات الكتابه للمجلد",
	"DUMP_FILE_ERROR" => ".المراد استخراجه '{file_name}' لم يتم ايجاد الملف",
	"DB_TABLE_ERROR" => ".الرجاء نشر قاعدة البيانات بالمعلومات المطلوبه '{table_name}' لم يتم ايجاد الطاوله",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to database server using specified port.",
	"DB_USER_PASS_ERROR" => "The username or password you specified is not correct.",
	"DB_NAME_ERROR" => "Login settings were correct, but the database '{db_name}' could not be found.",

	// upgrade messages
	"UPGRADE_TITLE" => "للتسوق ViArt تحديث برنامج",
	"UPGRADE_NOTE" => ".ملاحظه: الرجاء اخذ نسخه احتياطيه من قاعدة البيانات قبل الاستمرار",
	"UPGRADE_AVAILABLE_MSG" => "التحديث موجود",
	"UPGRADE_BUTTON" => "{version_number} التحديث الآن الى النسخه",
	"CURRENT_VERSION_MSG" => "النسخه المستخدمه حالياً هي",
	"LATEST_VERSION_MSG" => "النسخه المتوفره للتنصيب هي",
	"UPGRADE_RESULTS_MSG" => "نتائج التحديث",
	"SQL_SUCCESS_MSG" => "نجحت SQL معاملات الـ",
	"SQL_FAILED_MSG" => "فشلت SQL معاملات الـ",
	"SQL_TOTAL_MSG" => "هو SQL مجموع ما تم تنفيذه من معاملات الـ",
	"VERSION_UPGRADED_MSG" => "لقد تم تحديث نسختك الى",
	"ALREADY_LATEST_MSG" => "انت الآن تملك آخر اصداره متوفره حالياً",
	"DOWNLOAD_NEW_MSG" => "The new version was detected",
	"DOWNLOAD_NOW_MSG" => "Download version {version_number} now",
	"DOWNLOAD_FOUND_MSG" => "We have detected that the new {version_number} version is available to download. Please click the link below to start downloading. After completing the download and replacing the files don't forget to run Upgrade routine again.",
	"NO_XML_CONNECTION" => "Warning! No connection to 'http://www.viart.com/' available!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
