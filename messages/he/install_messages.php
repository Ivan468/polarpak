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
	"INSTALL_TITLE" => "התקנת חנות ViArt",

	"INSTALL_STEP_1_TITLE" => "התקנה: צעד מס' 1",
	"INSTALL_STEP_1_DESC" => "תודה שבחרת בחנות ViArt. כדי להמשיך בהתקנה, נא למלא את הפרטים הנדרשים בהמשך. שים לב שבסיס הנתונים שבחרת חייב להיות קיים. אם בחרת בסיס נתונים המשתמש ב-ODBC, לדוגמא MS Access, לפני שתמשיך עליך ליצור עבורו DSN.",
	"INSTALL_STEP_2_TITLE" => "התקנה צעד מס' 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "התקנה צעד מס' 3",
	"INSTALL_STEP_3_DESC" => "נא לבחור מבנה אתר. תוכל לשנות את המבנה מאוחר יותר.",
	"INSTALL_FINAL_TITLE" => "התקנה: סיום",
	"SELECT_DATE_TITLE" => "בחר בתבנית תאריך",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "הגדרות בסיס נתונים",
	"DB_PROGRESS_MSG" => "התקדמות איוש מבנה בסיס הנתונים",
	"SELECT_PHP_LIB_MSG" => "בחר בספרית PHP",
	"SELECT_DB_TYPE_MSG" => "בחר סוג בסיס נתונים",
	"ADMIN_SETTINGS_MSG" => "הגדרות מנהליות",
	"DATE_SETTINGS_MSG" => "תבנית תאריך",
	"NO_DATE_FORMATS_MSG" => "אין תבניות תאריך",
	"INSTALL_FINISHED_MSG" => "בנקודה זו הושלמה התקנה בסיסית. נא בדוק את ההגדרות בחלק המנהלי ובצע שנויים נדרשים",
	"ACCESS_ADMIN_MSG" => "הקש כאן כדי להגיע לחלק המינהלי",
	"ADMIN_URL_MSG" => "URL של המנהלה",
	"MANUAL_URL_MSG" => "URL ידני",
	"THANKS_MSG" => "תודה שבחרת <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "סוג בסיס נתונים",
	"DB_TYPE_DESC" => "נא לבחור ב- <b>type of database</b> . אם אתה משתמש ב- SQL Server או Microsoft Access, נא לבחור ODBC.",
	"DB_PHP_LIB_FIELD" => "סיפרית PHP",
	"DB_HOST_FIELD" => "שם מארח",
	"DB_HOST_DESC" => "נא הכנס את <b>name</b> או <b>IP address of the server</b> שעליו בסיס הנתונים של ה- ViArt ירוץ. אם אתה מריץ את בסיס הנתונים שלך על PC מקומי תוכל להשאיר את  \"<b>localhost</b>\" ואת ה-port ריק. אם אתה משתמש בבסיס הנתונים של החברה המארחת, נא לראות את הגדרות השרת בתעוד שלה",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "שם בסיס הנתונים / DSN",
	"DB_NAME_DESC" => "אם אתה משתמש בבסיס נתונים מסוג s MySQL או PostgreSQL נא הכנס את <b>name of the database</b> במקום בו הינך רוצה ש- ViArt יצור את הטבלאות שלו. בסיס נתונים זה חייב להיות קיים. אם אתה משתמש ב- ViArt למטרת בדיקה ב-PC המקומי שלך אזי לרוב המערכות יש בסיס נתונים \"<b>test</b>\" שאתה יכול להשתמש. אם לא, נא צור בסיס נתונים כגון \"viart\" והשתמש בו. אם אתה משתמש ב- Microsoft Access or SQL Server אזי שם בסיס הנתונים צריך להיות ה-  <b>name of the DSN</b> ששמת בחלק ה-Data Sources בלוח הבקרה שלך.",
	"DB_USER_FIELD" => "שם משתמש",
	"DB_PASS_FIELD" => "סיסמא",
	"DB_USER_PASS_DESC" => "<b>Username</b> ו- <b>Password</b> - נא להכניס את שם המשתמש והסיסמא לגישה לבסיס הנתונים. אם אתה משתמש בהתקנה מקומית אזי שם המשתמש יהיה בוודאי \"<b>root</b>\" והסיסמא ריקה. זה מתאים למטרות בדיקה אבל לא בטוח לשימוש בשרת הסופי.",
	"DB_PERSISTENT_FIELD" => "חיבור מתמיד",
	"DB_PERSISTENT_DESC" => "כדי להשתמש בחיבור המתמיד של MySQL או Postgre סמן תיבה זו.אם אינך יודע את המשמעות השאר את התיבה בלתי מסומנת.",
	"DB_CREATE_DB_FIELD" => "צור DB",
	"DB_CREATE_DB_DESC" => "כדי ליצור בסיס נתונים, אם זה אפשרי, סמן תיבה זו. מתאים לשימוש רק עם MySQL ו- Postgre",
	"DB_POPULATE_FIELD" => "אייש DB",
	"DB_POPULATE_DESC" => "כדי ליצור את מבנה הטבלא של בסיס הנתונים ולמלא אותה במידע, סמן תיבה זו.",
	"DB_TEST_DATA_FIELD" => "מידע נסיון",
	"DB_TEST_DATA_DESC" => "כדי להוסיף מידע נסיון לבסיס הנתונים שלך, סמן תיבה זו",
	"ADMIN_EMAIL_FIELD" => "אי-מייל של המנהל",
	"ADMIN_LOGIN_FIELD" => "כניסת המנהל",
	"ADMIN_PASS_FIELD" => "סיסמת המנהל",
	"ADMIN_CONF_FIELD" => "אשר סיסמא",
	"DATETIME_SHOWN_FIELD" => "תבנית תאריך ושעה (נראה באתר)",
	"DATE_SHOWN_FIELD" => "תבנית תאריך ושעה (נראה באתר)",
	"DATETIME_EDIT_FIELD" => "תבנית תאריך ושעה (לעריכה)",
	"DATE_EDIT_FIELD" => "תבנית תאריך (לעריכה)",
	"DATE_FORMAT_COLUMN" => "תבנית תאריך",

	"DB_LIBRARY_ERROR" => "פונקציות PHP עבור {db_library} לא הוגדרו. נא לבדוק את ערכי בסיס הנתונים בקובץ ההגדרות - php.ini.",
	"DB_CONNECT_ERROR" => "לא מצליח להתחבר לבסיס הנתונים. נא לבדוק את הגדרות בסיס הנתונים.",
	"INSTALL_FINISHED_ERROR" => "תהליך ההתקנה הסתיים",
	"WRITE_FILE_ERROR" => "אין לך אשורי כתיבה לקובץ <b>'includes/var_definition.php'</b>. נא לשנות את אשורי הקובץ לפני שאתה ממשיך.",
	"WRITE_DIR_ERROR" => "אין לך אישור כתיבה לתיקיה <b>'includes/'</b>. נא לשנות את אשורי התיקיה לפני שאתה ממשיך.",
	"DUMP_FILE_ERROR" => "קובץ '{file_name}' לא נמצא.",
	"DB_TABLE_ERROR" => "טבלאת '{table_name}' לא נמצאה. נא לאייש את בסיס הנתונים במידע הנדרש.",
	"TEST_DATA_ERROR" => "בדוק את <b>{POPULATE_DB_FIELD}</b> לפני איוש הטבלא במידע נסיון",
	"DB_HOST_ERROR" => "אין אפשרות למצוא את שם המארח שסיפקת ",
	"DB_PORT_ERROR" => "לא ניתן להתחבר לשרת בסיס הנתונים ע\"י ה-Port שסיפקת",
	"DB_USER_PASS_ERROR" => "שם המשתמש או הסיסמא שסיפקת אינם נכונים",
	"DB_NAME_ERROR" => "ערכי הכניסה הם נכונים, אך בסיס הנתונים '{db_name}' לא נמצא.",

	// upgrade messages
	"UPGRADE_TITLE" => "שדרוג חנות ViArt",
	"UPGRADE_NOTE" => "הערה: כדאי לבצע גבוי לבסיס הנתונים לפני ההמשך",
	"UPGRADE_AVAILABLE_MSG" => "שדרוג בסיס הנתונים זמין",
	"UPGRADE_BUTTON" => "שדרג את בסיס הנתונים ל- {version_number} עכשיו",
	"CURRENT_VERSION_MSG" => "גירסא מותקנת נוכחית",
	"LATEST_VERSION_MSG" => "גירסא זמינה להתקנה",
	"UPGRADE_RESULTS_MSG" => "תוצאות שדרוג",
	"SQL_SUCCESS_MSG" => "שאלת SQL הצליחה",
	"SQL_FAILED_MSG" => "שאלת SQL נכשלה",
	"SQL_TOTAL_MSG" => "סך כל שאלות SQL שבוצעו",
	"VERSION_UPGRADED_MSG" => "בסיס הנתונים שלך שודרג ל-",
	"ALREADY_LATEST_MSG" => "ברשותך הגירסא האחרונה",
	"DOWNLOAD_NEW_MSG" => "נמצאה גירסא חדשה",
	"DOWNLOAD_NOW_MSG" => "הורד גירסא {version_number} עכשיו",
	"DOWNLOAD_FOUND_MSG" => "נמצא שגירסה חדשה מס' {version_number} זמינה להורדה. נא הקש על הקישור שלמטה להתחלת ההורדה. לאחר השלמת ההורדה והחלפת הקבצים אל תשכח להריץ את שיגרת השדרוג מחדש.",
	"NO_XML_CONNECTION" => "אזהרה! אין קשר אל 'http://www.viart.com/' !",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
